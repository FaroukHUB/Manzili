"use client"

import { useState, useEffect } from "react"
import { Modal } from "@/components/ui/modal"
import { Input, Textarea, Select } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { formatCurrency, formatHours, calculateHoursWorked, calculateEarnings, formatDateFR } from "@/lib/utils"
import type { WorkLog, Settings } from "@/lib/db/schema"

interface WorkLogModalProps {
  open: boolean
  onClose: () => void
  date: Date | null
  existingLog: WorkLog | null
  settings: Settings | null
  previousLog: WorkLog | null
  onSaved: () => void
}

export function WorkLogModal({
  open,
  onClose,
  date,
  existingLog,
  settings,
  previousLog,
  onSaved,
}: WorkLogModalProps) {
  const [startTime, setStartTime] = useState("")
  const [endTime, setEndTime] = useState("")
  const [breakMinutes, setBreakMinutes] = useState("0")
  const [hourlyRate, setHourlyRate] = useState("")
  const [source, setSource] = useState("pizzeria")
  const [note, setNote] = useState("")
  const [loading, setLoading] = useState(false)
  const [deleting, setDeleting] = useState(false)

  useEffect(() => {
    if (open) {
      if (existingLog) {
        setStartTime(existingLog.startTime)
        setEndTime(existingLog.endTime)
        setBreakMinutes(String(existingLog.breakMinutes))
        setHourlyRate(existingLog.hourlyRate)
        setSource(existingLog.source)
        setNote(existingLog.note || "")
      } else {
        setStartTime(settings?.defaultStartTime || "10:00")
        setEndTime(settings?.defaultEndTime || "18:00")
        setBreakMinutes(String(settings?.defaultBreakMinutes || 0))
        setHourlyRate(settings?.hourlyRate || "10")
        setSource("pizzeria")
        setNote("")
      }
    }
  }, [open, existingLog, settings])

  const hoursWorked = startTime && endTime
    ? calculateHoursWorked(startTime, endTime, Number(breakMinutes) || 0)
    : 0
  const earnings = calculateEarnings(hoursWorked, Number(hourlyRate) || 0)

  async function handleSave() {
    if (!date) return
    setLoading(true)

    const dateStr = date.toISOString().split("T")[0]
    const payload = { date: dateStr, startTime, endTime, breakMinutes: Number(breakMinutes) || 0, hourlyRate, source, note }

    const url = existingLog ? `/api/work-logs/${existingLog.id}` : "/api/work-logs"
    const method = existingLog ? "PUT" : "POST"

    const res = await fetch(url, {
      method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })

    setLoading(false)
    if (res.ok) {
      await onSaved()
      onClose()
    } else {
      const err = await res.json().catch(() => ({}))
      console.error("Save error:", err)
    }
  }

  async function handleDelete() {
    if (!existingLog) return
    setDeleting(true)
    await fetch(`/api/work-logs/${existingLog.id}`, { method: "DELETE" })
    setDeleting(false)
    onSaved()
    onClose()
  }

  function copyFromPrevious() {
    if (!previousLog) return
    setStartTime(previousLog.startTime)
    setEndTime(previousLog.endTime)
    setBreakMinutes(String(previousLog.breakMinutes))
    setHourlyRate(previousLog.hourlyRate)
    setSource(previousLog.source)
  }

  const dateLabel = date ? formatDateFR(date) : ""
  const isToday = date ? date.toDateString() === new Date().toDateString() : false

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={`${isToday ? "Aujourd'hui — " : ""}${dateLabel}`}
    >
      <div className="flex flex-col gap-4">
        {/* Preview gains */}
        {hoursWorked > 0 && (
          <div
            className="rounded-xl p-3 flex items-center justify-between"
            style={{ background: "var(--color-gain-bg)", border: "1px solid var(--color-gain)" }}
          >
            <div>
              <p className="text-xs" style={{ color: "var(--color-gain)" }}>Gain estimé</p>
              <p className="text-xl font-bold" style={{ color: "var(--color-gain)" }}>
                {formatCurrency(earnings)}
              </p>
            </div>
            <div className="text-right">
              <p className="text-xs" style={{ color: "var(--color-gain)" }}>Heures</p>
              <p className="text-lg font-semibold" style={{ color: "var(--color-gain)" }}>
                {formatHours(hoursWorked)}
              </p>
            </div>
          </div>
        )}

        <div className="grid grid-cols-2 gap-3">
          <Input
            label="Début"
            type="time"
            value={startTime}
            onChange={(e) => setStartTime(e.target.value)}
          />
          <Input
            label="Fin"
            type="time"
            value={endTime}
            onChange={(e) => setEndTime(e.target.value)}
          />
        </div>

        <div className="grid grid-cols-2 gap-3">
          <Input
            label="Pause (min)"
            type="number"
            min="0"
            value={breakMinutes}
            onChange={(e) => setBreakMinutes(e.target.value)}
          />
          <Input
            label="Taux horaire"
            type="number"
            min="0"
            step="0.5"
            value={hourlyRate}
            suffix="€/h"
            onChange={(e) => setHourlyRate(e.target.value)}
          />
        </div>

        <Select
          label="Source"
          value={source}
          onChange={(e) => setSource(e.target.value)}
          options={[
            { value: "pizzeria", label: "🍕 Pizzeria" },
            { value: "web", label: "💻 Sites web" },
            { value: "other", label: "📦 Autre" },
          ]}
        />

        <Textarea
          label="Note (optionnel)"
          value={note}
          onChange={(e) => setNote(e.target.value)}
          placeholder="Journée particulière, notes..."
        />

        {previousLog && !existingLog && (
          <button
            type="button"
            onClick={copyFromPrevious}
            className="text-xs py-2 rounded-xl border transition-colors"
            style={{ color: "var(--text-secondary)", borderColor: "var(--border-color)" }}
          >
            Copier les horaires du jour précédent ({previousLog.startTime} → {previousLog.endTime})
          </button>
        )}

        <div className="flex gap-2 pt-1">
          {existingLog && (
            <Button variant="danger" size="sm" onClick={handleDelete} loading={deleting}>
              Supprimer
            </Button>
          )}
          <Button className="flex-1" onClick={handleSave} loading={loading}>
            {existingLog ? "Mettre à jour" : "Enregistrer"}
          </Button>
        </div>
      </div>
    </Modal>
  )
}
