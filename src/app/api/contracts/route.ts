import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { contracts, USER_ID } from "@/lib/db/schema"
import { eq, desc } from "drizzle-orm"

export async function GET() {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })
  const rows = await db.select().from(contracts).where(eq(contracts.userId, USER_ID)).orderBy(desc(contracts.createdAt))
  return NextResponse.json(rows)
}

export async function POST(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })
  const body = await req.json()
  const { clientName, description, totalAmount, depositReceived = 0, status = "signed", expectedDate, note } = body
  if (!clientName || totalAmount === undefined) return NextResponse.json({ error: "Champs manquants" }, { status: 400 })
  const [created] = await db.insert(contracts).values({
    userId: USER_ID, clientName, description: description || null,
    totalAmount: String(totalAmount), depositReceived: String(depositReceived),
    status, expectedDate: expectedDate || null, note: note || null,
  }).returning()
  return NextResponse.json(created, { status: 201 })
}
