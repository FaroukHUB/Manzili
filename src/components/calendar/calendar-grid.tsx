"use client"

import { useState, useEffect, useCallback } from "react"
import { ChevronLeft, ChevronRight, Download, Plus, FileText } from "lucide-react"
import { WorkLogModal } from "./work-log-modal"
import { cn, getDaysInMonth, getFirstDayOfWeek, WEEK_DAYS_FR, formatCurrency, formatHours, getMonthLabel, toISODate } from "@/lib/utils"
import type { WorkLog, Settings } from "@/lib/db/schema"

export function CalendarGrid() {
  const now = new Date()
  const [year, setYear] = useState(now.getFullYear())
  const [month, setMonth] = useState(now.getMonth())
  const [workLogs, setWorkLogs] = useState<WorkLog[]>([])
  const [settings, setSettings] = useState<Settings | null>(null)
  const [selectedDate, setSelectedDate] = useState<Date | null>(null)
  const [modalOpen, setModalOpen] = useState(false)
  const [loading, setLoading] = useState(true)

  const fetchData = useCallback(async () => {
    setLoading(true)
    try {
      const monthStr = `${year}-${String(month + 1).padStart(2, "0")}`
      const logsRes = await fetch(`/api/work-logs?month=${monthStr}`)
      if (logsRes.ok) {
        const logs = await logsRes.json()
        setWorkLogs(Array.isArray(logs) ? logs : [])
      }
      const settingsRes = await fetch("/api/settings")
      if (settingsRes.ok) {
        const sett = await settingsRes.json()
        setSettings(sett?.id ? sett : null)
      }
    } catch (e) {
      console.error("Calendar fetch error:", e)
    } finally {
      setLoading(false)
    }
  }, [year, month])

  useEffect(() => { fetchData() }, [fetchData])

  const days = getDaysInMonth(year, month)
  const firstDayOffset = getFirstDayOfWeek(year, month)
  const logsByDate = workLogs.reduce((acc, log) => {
    acc[log.date] = log
    return acc
  }, {} as Record<string, WorkLog>)

  const totalEarnings = workLogs.reduce((sum, l) => sum + parseFloat(l.earnings), 0)
  const totalHours = workLogs.reduce((sum, l) => sum + parseFloat(l.hoursWorked), 0)
  const workedDays = workLogs.length

  const selectedLog = selectedDate ? logsByDate[toISODate(selectedDate)] ?? null : null
  const previousLog = (() => {
    if (!selectedDate) return null
    const sorted = [...workLogs].sort((a, b) => a.date.localeCompare(b.date))
    const selStr = toISODate(selectedDate)
    const before = sorted.filter((l) => l.date < selStr)
    return before[before.length - 1] ?? null
  })()

  function prevMonth() {
    if (month === 0) { setMonth(11); setYear(y => y - 1) } else setMonth(m => m - 1)
  }
  function nextMonth() {
    if (month === 11) { setMonth(0); setYear(y => y + 1) } else setMonth(m => m + 1)
  }

  function exportCSV() {
    const monthStr = `${year}-${String(month + 1).padStart(2, "0")}`
    window.open(`/api/work-logs/export?month=${monthStr}`)
  }

  async function exportPDF() {
    const [{ default: jsPDF }, { default: autoTable }] = await Promise.all([
      import("jspdf"),
      import("jspdf-autotable"),
    ])
    const monthStr = `${year}-${String(month + 1).padStart(2, "0")}`
    const monthLabel = getMonthLabel(year, month)
    const doc = new jsPDF()

    doc.setFontSize(18)
    doc.setTextColor(59, 37, 16)
    doc.text("Rapport de travail", 14, 20)
    doc.setFontSize(13)
    doc.text(monthLabel.charAt(0).toUpperCase() + monthLabel.slice(1), 14, 29)

    doc.setFontSize(10)
    doc.setTextColor(122, 92, 63)
    doc.text(`Jours travaillés : ${workedDays}`, 14, 40)
    doc.text(`Total heures : ${formatHours(totalHours)}`, 14, 47)
    doc.text(`Total gains : ${formatCurrency(totalEarnings)}`, 14, 54)

    autoTable(doc, {
      startY: 62,
      head: [["Date", "Début", "Fin", "Pause", "Heures", "Taux €/h", "Gain €", "Note"]],
      body: [...workLogs]
        .sort((a, b) => a.date.localeCompare(b.date))
        .map((l) => [
          l.date,
          l.startTime,
          l.endTime,
          `${l.breakMinutes} min`,
          parseFloat(l.hoursWorked).toFixed(2),
          parseFloat(l.hourlyRate).toFixed(2),
          parseFloat(l.earnings).toFixed(2),
          l.note || "",
        ]),
      headStyles: { fillColor: [139, 94, 60] },
      alternateRowStyles: { fillColor: [253, 248, 242] },
      foot: [["", "", "", "", formatHours(totalHours), "", formatCurrency(totalEarnings), ""]],
      footStyles: { fillColor: [237, 228, 216], textColor: [59, 37, 16], fontStyle: "bold" },
    })

    doc.save(`heures-${monthStr}.pdf`)
  }

  function shareWhatsApp() {
    const monthLabel = getMonthLabel(year, month)
    const lines = [
      `📊 *Rapport de travail — ${monthLabel.charAt(0).toUpperCase() + monthLabel.slice(1)}*`,
      "",
      `📅 Jours travaillés : ${workedDays}`,
      `⏱ Total heures : ${formatHours(totalHours)}`,
      `💰 Total gains : ${formatCurrency(totalEarnings)}`,
      "",
      "*Détail :*",
      ...[...workLogs]
        .sort((a, b) => a.date.localeCompare(b.date))
        .map((l) => {
          const d = new Date(l.date).toLocaleDateString("fr-FR", { day: "numeric", month: "short" })
          return `• ${d} : ${l.startTime}–${l.endTime} → ${parseFloat(l.hoursWorked).toFixed(1)}h = ${parseFloat(l.earnings).toFixed(2)}€`
        }),
    ]
    window.open(`https://wa.me/?text=${encodeURIComponent(lines.join("\n"))}`, "_blank")
  }

  function openToday() {
    setSelectedDate(new Date())
    setModalOpen(true)
  }

  return (
    <div className="p-4 md:p-6">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          <button
            onClick={prevMonth}
            className="p-2 rounded-xl border transition-colors"
            style={{ borderColor: "var(--border-color)", color: "var(--text-secondary)" }}
          >
            <ChevronLeft size={18} />
          </button>
          <h2 className="text-base font-semibold capitalize" style={{ color: "var(--text-primary)" }}>
            {getMonthLabel(year, month)}
          </h2>
          <button
            onClick={nextMonth}
            className="p-2 rounded-xl border transition-colors"
            style={{ borderColor: "var(--border-color)", color: "var(--text-secondary)" }}
          >
            <ChevronRight size={18} />
          </button>
        </div>
        <div className="flex gap-2">
          <button
            onClick={exportCSV}
            className="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs border transition-colors"
            style={{ borderColor: "var(--border-color)", color: "var(--text-secondary)" }}
            title="Exporter CSV"
          >
            <Download size={14} />
            <span className="hidden sm:inline">CSV</span>
          </button>
          <button
            onClick={exportPDF}
            className="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs border transition-colors"
            style={{ borderColor: "var(--border-color)", color: "var(--color-debt)" }}
            title="Exporter PDF"
          >
            <FileText size={14} />
            <span className="hidden sm:inline">PDF</span>
          </button>
          <button
            onClick={shareWhatsApp}
            className="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium border transition-colors"
            style={{ borderColor: "#25d366", color: "#25d366" }}
            title="Partager sur WhatsApp"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <span className="hidden sm:inline">WhatsApp</span>
          </button>
          <button
            onClick={openToday}
            className="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium"
            style={{ background: "var(--color-accent)", color: "white" }}
          >
            <Plus size={14} />
            Aujourd'hui
          </button>
        </div>
      </div>

      {/* Monthly summary */}
      <div
        className="grid grid-cols-3 gap-3 mb-5 rounded-2xl p-4 border"
        style={{ background: "var(--bg-card)", borderColor: "var(--border-color)" }}
      >
        <div>
          <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Jours travaillés</p>
          <p className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>{workedDays}</p>
        </div>
        <div>
          <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Total heures</p>
          <p className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>{formatHours(totalHours)}</p>
        </div>
        <div>
          <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Total gains</p>
          <p className="text-xl font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(totalEarnings)}</p>
        </div>
      </div>

      {/* Calendar grid */}
      <div
        className="rounded-2xl border overflow-hidden"
        style={{ background: "var(--bg-card)", borderColor: "var(--border-color)" }}
      >
        {/* Day headers */}
        <div className="grid grid-cols-7 border-b" style={{ borderColor: "var(--border-color)" }}>
          {WEEK_DAYS_FR.map((d) => (
            <div
              key={d}
              className="text-center py-2 text-xs font-medium"
              style={{ color: "var(--text-secondary)" }}
            >
              {d}
            </div>
          ))}
        </div>

        {/* Days */}
        <div className="grid grid-cols-7">
          {/* Empty cells before first day */}
          {Array.from({ length: firstDayOffset }).map((_, i) => (
            <div key={`empty-${i}`} className="aspect-square border-r border-b last:border-r-0" style={{ borderColor: "var(--border-color)" }} />
          ))}

          {days.map((day, i) => {
            const dateStr = toISODate(day)
            const log = logsByDate[dateStr]
            const isToday = day.toDateString() === new Date().toDateString()
            const isFuture = day > new Date()
            const colIndex = (firstDayOffset + i) % 7
            const isLastInRow = colIndex === 6

            return (
              <button
                key={dateStr}
                onClick={() => {
                  setSelectedDate(day)
                  setModalOpen(true)
                }}
                className={cn(
                  "aspect-square border-b flex flex-col items-center justify-center transition-colors relative",
                  !isLastInRow && "border-r",
                  !isFuture && "cursor-pointer",
                  isFuture ? "opacity-30" : "hover:bg-[var(--bg-hover)]"
                )}
                style={{ borderColor: "var(--border-color)" }}
                disabled={isFuture}
              >
                <span
                  className={cn(
                    "text-xs font-medium w-6 h-6 rounded-full flex items-center justify-center",
                    isToday && "text-white"
                  )}
                  style={isToday ? { background: "var(--color-accent)" } : { color: log ? "var(--text-primary)" : "var(--text-secondary)" }}
                >
                  {day.getDate()}
                </span>
                {log && (
                  <span
                    className="text-[9px] font-semibold mt-0.5"
                    style={{ color: "var(--color-gain)" }}
                  >
                    {formatCurrency(parseFloat(log.earnings))}
                  </span>
                )}
                {log && (
                  <div
                    className="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full"
                    style={{ background: "var(--color-gain)" }}
                  />
                )}
              </button>
            )
          })}
        </div>
      </div>

      {/* Loading overlay */}
      {loading && (
        <div className="text-center py-8">
          <p className="text-sm" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
        </div>
      )}

      <WorkLogModal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        date={selectedDate}
        existingLog={selectedLog}
        settings={settings}
        previousLog={previousLog}
        onSaved={fetchData}
      />
    </div>
  )
}
