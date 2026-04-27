import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { fixedExpenses, USER_ID } from "@/lib/db/schema"
import { eq } from "drizzle-orm"

export async function GET() {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const rows = await db
    .select()
    .from(fixedExpenses)
    .where(eq(fixedExpenses.userId, USER_ID))

  return NextResponse.json(rows)
}

export async function POST(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const body = await req.json()
  const { name, amount, category = "other" } = body

  if (!name || !amount) {
    return NextResponse.json({ error: "Champs manquants" }, { status: 400 })
  }

  const [created] = await db
    .insert(fixedExpenses)
    .values({ userId: USER_ID, name, amount: String(amount), category })
    .returning()

  return NextResponse.json(created, { status: 201 })
}
