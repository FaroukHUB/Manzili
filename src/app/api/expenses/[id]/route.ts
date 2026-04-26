import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { fixedExpenses } from "@/lib/db/schema"
import { eq } from "drizzle-orm"

export async function PUT(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { id } = await params
  const body = await req.json()

  const [updated] = await db
    .update(fixedExpenses)
    .set(body)
    .where(eq(fixedExpenses.id, id))
    .returning()

  return NextResponse.json(updated)
}

export async function DELETE(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { id } = await params
  await db.update(fixedExpenses).set({ isActive: false }).where(eq(fixedExpenses.id, id))
  return NextResponse.json({ success: true })
}
