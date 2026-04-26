import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { workLogs, USER_ID } from "@/lib/db/schema"
import { eq, and, gte, lte, desc } from "drizzle-orm"
import { calculateHoursWorked, calculateEarnings } from "@/lib/utils"

export async function GET(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { searchParams } = new URL(req.url)
  const month = searchParams.get("month") // format: YYYY-MM

  let rows

  if (month) {
    const [year, m] = month.split("-").map(Number)
    const start = `${year}-${String(m).padStart(2, "0")}-01`
    const end = `${year}-${String(m).padStart(2, "0")}-31`
    rows = await db
      .select()
      .from(workLogs)
      .where(and(eq(workLogs.userId, USER_ID), gte(workLogs.date, start), lte(workLogs.date, end)))
      .orderBy(workLogs.date)
  } else {
    rows = await db
      .select()
      .from(workLogs)
      .where(eq(workLogs.userId, USER_ID))
      .orderBy(desc(workLogs.date))
      .limit(100)
  }

  return NextResponse.json(rows)
}

export async function POST(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const body = await req.json()
  const { date, startTime, endTime, breakMinutes = 0, hourlyRate, source = "pizzeria", note } = body

  if (!date || !startTime || !endTime || !hourlyRate) {
    return NextResponse.json({ error: "Champs manquants" }, { status: 400 })
  }

  const hoursWorked = calculateHoursWorked(startTime, endTime, Number(breakMinutes))
  const earnings = calculateEarnings(hoursWorked, Number(hourlyRate))

  const [created] = await db
    .insert(workLogs)
    .values({
      userId: USER_ID,
      date,
      startTime,
      endTime,
      breakMinutes: Number(breakMinutes),
      hourlyRate: String(hourlyRate),
      hoursWorked: String(hoursWorked),
      earnings: String(earnings),
      source,
      note: note || null,
    })
    .returning()

  return NextResponse.json(created, { status: 201 })
}
