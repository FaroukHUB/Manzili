import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { assets, USER_ID } from "@/lib/db/schema"
import { eq, and } from "drizzle-orm"

export async function PUT(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })
  const { id } = await params
  const body = await req.json()
  const [updated] = await db.update(assets).set({ ...body, updatedAt: new Date() }).where(and(eq(assets.id, id), eq(assets.userId, USER_ID))).returning()
  return NextResponse.json(updated)
}

export async function DELETE(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })
  const { id } = await params
  await db.delete(assets).where(and(eq(assets.id, id), eq(assets.userId, USER_ID)))
  return NextResponse.json({ success: true })
}
