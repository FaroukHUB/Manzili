import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { assets, USER_ID } from "@/lib/db/schema"
import { eq, and } from "drizzle-orm"

export async function POST(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })
  const { id } = await params
  const { delta } = await req.json()
  if (typeof delta !== "number") return NextResponse.json({ error: "delta requis" }, { status: 400 })

  const [current] = await db.select({ amount: assets.amount })
    .from(assets).where(and(eq(assets.id, id), eq(assets.userId, USER_ID)))
  if (!current) return NextResponse.json({ error: "Non trouvé" }, { status: 404 })

  const newAmount = Math.max(0, parseFloat(current.amount) + delta)
  const [updated] = await db.update(assets)
    .set({ amount: String(newAmount), updatedAt: new Date() })
    .where(and(eq(assets.id, id), eq(assets.userId, USER_ID)))
    .returning()
  return NextResponse.json(updated)
}
