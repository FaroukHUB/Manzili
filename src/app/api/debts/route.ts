import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { debts, USER_ID } from "@/lib/db/schema"
import { eq, asc } from "drizzle-orm"

export async function GET() {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const rows = await db
    .select()
    .from(debts)
    .where(eq(debts.userId, USER_ID))
    .orderBy(asc(debts.priority))

  return NextResponse.json(rows)
}

export async function POST(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const body = await req.json()
  const { name, totalAmount, paidAmount = "0", priority = 1, dueDate, note, whatsappNumber } = body

  if (!name || !totalAmount) {
    return NextResponse.json({ error: "Champs manquants" }, { status: 400 })
  }

  const [created] = await db
    .insert(debts)
    .values({
      userId: USER_ID,
      name,
      totalAmount: String(totalAmount),
      paidAmount: String(paidAmount),
      priority: Number(priority),
      dueDate: dueDate || null,
      note: note || null,
      whatsappNumber: whatsappNumber || null,
    })
    .returning()

  return NextResponse.json(created, { status: 201 })
}
