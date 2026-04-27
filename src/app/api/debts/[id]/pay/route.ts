import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { debts, debtPayments } from "@/lib/db/schema"
import { eq } from "drizzle-orm"

export async function POST(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { id } = await params
  const body = await req.json()
  const { amount, paymentDate, note } = body

  if (!amount || !paymentDate) {
    return NextResponse.json({ error: "Champs manquants" }, { status: 400 })
  }

  // Get current debt
  const [debt] = await db.select().from(debts).where(eq(debts.id, id)).limit(1)
  if (!debt) return NextResponse.json({ error: "Dette introuvable" }, { status: 404 })

  // Record payment
  const [payment] = await db
    .insert(debtPayments)
    .values({
      debtId: id,
      amount: String(amount),
      paymentDate,
      note: note || null,
    })
    .returning()

  // Update paid amount
  const newPaidAmount = parseFloat(debt.paidAmount) + parseFloat(String(amount))
  await db
    .update(debts)
    .set({ paidAmount: String(Math.min(newPaidAmount, parseFloat(debt.totalAmount))) })
    .where(eq(debts.id, id))

  return NextResponse.json(payment, { status: 201 })
}
