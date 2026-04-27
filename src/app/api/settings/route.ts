import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { settings, USER_ID } from "@/lib/db/schema"
import { eq } from "drizzle-orm"

const DEFAULT_SETTINGS = {
  hourlyRate: "10",
  dailyGoal: "80",
  weeklyGoal: "480",
  monthlyGoal: "1920",
  familyExpense: "250",
  personalExpense: "80",
  currency: "€",
  defaultStartTime: "10:00",
  defaultEndTime: "18:00",
  defaultBreakMinutes: 0,
}

export async function GET() {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const rows = await db.select().from(settings).where(eq(settings.userId, USER_ID)).limit(1)

  if (rows.length === 0) {
    const [created] = await db.insert(settings).values({ userId: USER_ID, ...DEFAULT_SETTINGS }).returning()
    return NextResponse.json(created)
  }

  return NextResponse.json(rows[0])
}

export async function PUT(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const body = await req.json()

  const rows = await db.select().from(settings).where(eq(settings.userId, USER_ID)).limit(1)

  if (rows.length === 0) {
    const [created] = await db.insert(settings).values({ userId: USER_ID, ...body }).returning()
    return NextResponse.json(created)
  }

  const [updated] = await db
    .update(settings)
    .set({ ...body, updatedAt: new Date() })
    .where(eq(settings.userId, USER_ID))
    .returning()

  return NextResponse.json(updated)
}
