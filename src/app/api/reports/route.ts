import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { workLogs, contracts, USER_ID } from "@/lib/db/schema"
import { eq, and, gte, lt } from "drizzle-orm"

export async function GET(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { searchParams } = new URL(req.url)
  const year = parseInt(searchParams.get("year") || String(new Date().getFullYear()))

  const start = `${year}-01-01`
  const end = `${year + 1}-01-01`

  const [logs, deliveredContracts] = await Promise.all([
    db.select().from(workLogs)
      .where(and(eq(workLogs.userId, USER_ID), gte(workLogs.date, start), lt(workLogs.date, end))),
    db.select().from(contracts)
      .where(and(eq(contracts.userId, USER_ID), eq(contracts.status, "delivered"))),
  ])

  // Build 12 months
  const months = Array.from({ length: 12 }, (_, i) => {
    const m = String(i + 1).padStart(2, "0")
    return `${year}-${m}`
  })

  const salaryByMonth: Record<string, { earnings: number; hours: number; days: number }> = {}
  for (const log of logs) {
    const m = log.date.slice(0, 7)
    if (!salaryByMonth[m]) salaryByMonth[m] = { earnings: 0, hours: 0, days: 0 }
    salaryByMonth[m].earnings += parseFloat(log.earnings)
    salaryByMonth[m].hours += parseFloat(log.hoursWorked)
    salaryByMonth[m].days += 1
  }

  const caByMonth: Record<string, { ca: number; count: number }> = {}
  for (const c of deliveredContracts) {
    // Use deliveredAt if set, fallback to expectedDate, then createdAt
    const dateStr = c.deliveredAt || c.expectedDate || (c.createdAt ? c.createdAt.toISOString().slice(0, 10) : null)
    if (!dateStr) continue
    const m = dateStr.slice(0, 7)
    if (m < `${year}-01` || m > `${year}-12`) continue
    if (!caByMonth[m]) caByMonth[m] = { ca: 0, count: 0 }
    caByMonth[m].ca += parseFloat(c.totalAmount)
    caByMonth[m].count += 1
  }

  const rows = months.map(m => ({
    month: m,
    salary: salaryByMonth[m]?.earnings || 0,
    hours: salaryByMonth[m]?.hours || 0,
    days: salaryByMonth[m]?.days || 0,
    ca: caByMonth[m]?.ca || 0,
    contractsCount: caByMonth[m]?.count || 0,
    total: (salaryByMonth[m]?.earnings || 0) + (caByMonth[m]?.ca || 0),
  }))

  const totals = rows.reduce((acc, r) => ({
    salary: acc.salary + r.salary,
    hours: acc.hours + r.hours,
    days: acc.days + r.days,
    ca: acc.ca + r.ca,
    total: acc.total + r.total,
  }), { salary: 0, hours: 0, days: 0, ca: 0, total: 0 })

  return NextResponse.json({ year, rows, totals })
}
