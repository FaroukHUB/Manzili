"use client"

import { useState, useEffect, useCallback } from "react"
import { ChevronLeft, ChevronRight, Download, Plus } from "lucide-react"
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
          >
            <Download size={14} />
            <span className="hidden sm:inline">Export CSV</span>
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
