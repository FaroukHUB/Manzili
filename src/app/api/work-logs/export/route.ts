import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import { db } from "@/lib/db"
import { workLogs, USER_ID } from "@/lib/db/schema"
import { eq, and, gte, lt } from "drizzle-orm"

export async function GET(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  const { searchParams } = new URL(req.url)
  const month = searchParams.get("month")

  let rows

  if (month) {
    const [year, m] = month.split("-").map(Number)
    const start = `${year}-${String(m).padStart(2, "0")}-01`
    const nextM = m === 12 ? 1 : m + 1
    const nextY = m === 12 ? year + 1 : year
    const end = `${nextY}-${String(nextM).padStart(2, "0")}-01`
    rows = await db
      .select()
      .from(workLogs)
      .where(and(eq(workLogs.userId, USER_ID), gte(workLogs.date, start), lt(workLogs.date, end)))
      .orderBy(workLogs.date)
  } else {
    rows = await db.select().from(workLogs).where(eq(workLogs.userId, USER_ID)).orderBy(workLogs.date)
  }

  const header = "Date,Début,Fin,Pause (min),Heures travaillées,Taux horaire (€),Gain (€),Source,Note\n"
  const csvRows = rows
    .map((r) =>
      [
        r.date,
        r.startTime,
        r.endTime,
        r.breakMinutes,
        parseFloat(r.hoursWorked).toFixed(2),
        parseFloat(r.hourlyRate).toFixed(2),
        parseFloat(r.earnings).toFixed(2),
        r.source,
        (r.note || "").replace(/,/g, ";"),
      ].join(",")
    )
    .join("\n")

  const csv = header + csvRows
  const filename = month ? `heures-${month}.csv` : "heures-export.csv"

  return new NextResponse(csv, {
    headers: {
      "Content-Type": "text/csv; charset=utf-8",
      "Content-Disposition": `attachment; filename="${filename}"`,
    },
  })
}
