import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { receivables } from "@/lib/db/schema"
import { eq } from "drizzle-orm"

export async function PUT(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { id } = await params
  const body = await req.json()
  const { name, reason, totalAmount, receivedAmount, expectedDate, note, whatsappNumber } = body

  const total = parseFloat(String(totalAmount))
  const received = parseFloat(String(receivedAmount || 0))
  const status = received >= total ? "received" : received > 0 ? "partial" : "pending"

  const [updated] = await db
    .update(receivables)
    .set({
      ...(name !== undefined && { name }),
      ...(reason !== undefined && { reason }),
      ...(totalAmount !== undefined && { totalAmount: String(totalAmount) }),
      ...(receivedAmount !== undefined && { receivedAmount: String(receivedAmount) }),
      ...(expectedDate !== undefined && { expectedDate }),
      ...(note !== undefined && { note }),
      ...(whatsappNumber !== undefined && { whatsappNumber }),
      status,
    })
    .where(eq(receivables.id, id))
    .returning()

  return NextResponse.json(updated)
}

export async function DELETE(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { id } = await params
  await db.delete(receivables).where(eq(receivables.id, id))
  return NextResponse.json({ success: true })
}
