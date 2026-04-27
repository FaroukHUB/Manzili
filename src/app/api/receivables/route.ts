import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { receivables, USER_ID } from "@/lib/db/schema"
import { eq, desc } from "drizzle-orm"

export async function GET() {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const rows = await db
    .select()
    .from(receivables)
    .where(eq(receivables.userId, USER_ID))
    .orderBy(desc(receivables.createdAt))

  return NextResponse.json(rows)
}

export async function POST(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const body = await req.json()
  const { name, reason, totalAmount, receivedAmount = "0", expectedDate, note, whatsappNumber } = body

  if (!name || !totalAmount) {
    return NextResponse.json({ error: "Champs manquants" }, { status: 400 })
  }

  const [created] = await db
    .insert(receivables)
    .values({
      userId: USER_ID,
      name,
      reason: reason || null,
      totalAmount: String(totalAmount),
      receivedAmount: String(receivedAmount),
      expectedDate: expectedDate || null,
      status: Number(receivedAmount) >= Number(totalAmount) ? "received" : Number(receivedAmount) > 0 ? "partial" : "pending",
      note: note || null,
      whatsappNumber: whatsappNumber || null,
    })
    .returning()

  return NextResponse.json(created, { status: 201 })
}
