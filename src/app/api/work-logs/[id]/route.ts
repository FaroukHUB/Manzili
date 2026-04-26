import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { workLogs } from "@/lib/db/schema"
import { eq } from "drizzle-orm"
import { calculateHoursWorked, calculateEarnings } from "@/lib/utils"

export async function PUT(req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { id } = await params
  const body = await req.json()
  const { startTime, endTime, breakMinutes = 0, hourlyRate, source, note } = body

  const hoursWorked = calculateHoursWorked(startTime, endTime, Number(breakMinutes))
  const earnings = calculateEarnings(hoursWorked, Number(hourlyRate))

  const [updated] = await db
    .update(workLogs)
    .set({
      startTime,
      endTime,
      breakMinutes: Number(breakMinutes),
      hourlyRate: String(hourlyRate),
      hoursWorked: String(hoursWorked),
      earnings: String(earnings),
      source,
      note: note || null,
    })
    .where(eq(workLogs.id, id))
    .returning()

  return NextResponse.json(updated)
}

export async function DELETE(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { id } = await params
  await db.delete(workLogs).where(eq(workLogs.id, id))
  return NextResponse.json({ success: true })
}
