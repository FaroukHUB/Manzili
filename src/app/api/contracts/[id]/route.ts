import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { contracts, USER_ID } from "@/lib/db/schema"
import { eq, and } from "drizzle-orm"

export async function PUT(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })
  const { id } = await params
  const body = await req.json()

  // Auto-set deliveredAt when status becomes "delivered" for the first time
  if (body.status === "delivered" && !body.deliveredAt) {
    const existing = await db.select({ status: contracts.status, deliveredAt: contracts.deliveredAt })
      .from(contracts).where(and(eq(contracts.id, id), eq(contracts.userId, USER_ID))).limit(1)
    if (existing[0] && existing[0].status !== "delivered" && !existing[0].deliveredAt) {
      body.deliveredAt = new Date().toISOString().slice(0, 10)
    }
  }

  const [updated] = await db.update(contracts).set(body).where(and(eq(contracts.id, id), eq(contracts.userId, USER_ID))).returning()
  return NextResponse.json(updated)
}

export async function DELETE(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })
  const { id } = await params
  await db.delete(contracts).where(and(eq(contracts.id, id), eq(contracts.userId, USER_ID)))
  return NextResponse.json({ success: true })
}
