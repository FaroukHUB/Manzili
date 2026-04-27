import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { debts } from "@/lib/db/schema"
import { eq } from "drizzle-orm"

export async function PUT(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { id } = await params
  const body = await req.json()
  const { name, totalAmount, paidAmount, priority, dueDate, note, whatsappNumber, isActive } = body

  const [updated] = await db
    .update(debts)
    .set({
      ...(name !== undefined && { name }),
      ...(totalAmount !== undefined && { totalAmount: String(totalAmount) }),
      ...(paidAmount !== undefined && { paidAmount: String(paidAmount) }),
      ...(priority !== undefined && { priority: Number(priority) }),
      ...(dueDate !== undefined && { dueDate }),
      ...(note !== undefined && { note }),
      ...(whatsappNumber !== undefined && { whatsappNumber }),
      ...(isActive !== undefined && { isActive }),
    })
    .where(eq(debts.id, id))
    .returning()

  return NextResponse.json(updated)
}

export async function DELETE(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { id } = await params
  await db.update(debts).set({ isActive: false }).where(eq(debts.id, id))
  return NextResponse.json({ success: true })
}
