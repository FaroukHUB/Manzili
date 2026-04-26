"use client"

import { useState, useEffect } from "react"
import { signOut } from "next-auth/react"
import { Card } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import type { Settings } from "@/lib/db/schema"

export default function SettingsPage() {
  const [settings, setSettings] = useState<Settings | null>(null)
  const [saving, setSaving] = useState(false)
  const [saved, setSaved] = useState(false)

  const [hourlyRate, setHourlyRate] = useState("")
  const [dailyGoal, setDailyGoal] = useState("")
  const [weeklyGoal, setWeeklyGoal] = useState("")
  const [monthlyGoal, setMonthlyGoal] = useState("")
  const [familyExpense, setFamilyExpense] = useState("")
  const [personalExpense, setPersonalExpense] = useState("")
  const [defaultStartTime, setDefaultStartTime] = useState("")
  const [defaultEndTime, setDefaultEndTime] = useState("")
  const [defaultBreakMinutes, setDefaultBreakMinutes] = useState("")

  useEffect(() => {
    fetch("/api/settings")
      .then((r) => r.json())
      .then((s) => {
        setSettings(s)
        setHourlyRate(s.hourlyRate)
        setDailyGoal(s.dailyGoal)
        setWeeklyGoal(s.weeklyGoal)
        setMonthlyGoal(s.monthlyGoal)
        setFamilyExpense(s.familyExpense)
        setPersonalExpense(s.personalExpense)
        setDefaultStartTime(s.defaultStartTime)
        setDefaultEndTime(s.defaultEndTime)
        setDefaultBreakMinutes(String(s.defaultBreakMinutes))
      })
  }, [])

  async function handleSave() {
    setSaving(true)
    await fetch("/api/settings", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        hourlyRate, dailyGoal, weeklyGoal, monthlyGoal,
        familyExpense, personalExpense,
        defaultStartTime, defaultEndTime,
        defaultBreakMinutes: Number(defaultBreakMinutes),
      }),
    })
    setSaving(false)
    setSaved(true)
    setTimeout(() => setSaved(false), 3000)
  }

  if (!settings) return (
    <div className="p-6 text-center">
      <p className="text-sm" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
    </div>
  )

  const availableForDebt =
    (Number(monthlyGoal) - Number(familyExpense) - Number(personalExpense)).toFixed(0)

  return (
    <div className="p-4 md:p-6 max-w-2xl mx-auto">
      <h1 className="text-xl font-bold mb-6" style={{ color: "var(--text-primary)" }}>Paramètres</h1>

      {/* Simulation */}
      <Card className="mb-5 border" style={{ borderColor: "var(--color-accent)", background: "var(--color-accent-bg)" }}>
        <p className="text-xs font-medium mb-2" style={{ color: "var(--color-accent)" }}>Simulation mensuelle</p>
        <div className="grid grid-cols-3 gap-4 text-sm">
          <div>
            <p className="text-xs" style={{ color: "var(--text-secondary)" }}>Revenu estimé</p>
            <p className="font-bold" style={{ color: "var(--text-primary)" }}>{Number(monthlyGoal).toFixed(0)}€</p>
          </div>
          <div>
            <p className="text-xs" style={{ color: "var(--text-secondary)" }}>Dépenses fixes</p>
            <p className="font-bold" style={{ color: "var(--color-debt)" }}>
              {(Number(familyExpense) + Number(personalExpense)).toFixed(0)}€
            </p>
          </div>
          <div>
            <p className="text-xs" style={{ color: "var(--text-secondary)" }}>Dispo pour dettes</p>
            <p className="font-bold" style={{ color: "var(--color-gain)" }}>{availableForDebt}€</p>
          </div>
        </div>
      </Card>

      <div className="flex flex-col gap-4">
        <Card>
          <p className="text-xs font-semibold uppercase tracking-wide mb-3" style={{ color: "var(--text-secondary)" }}>
            Taux & Objectifs
          </p>
          <div className="grid grid-cols-2 gap-3">
            <Input label="Taux horaire par défaut" type="number" min="0" step="0.5" value={hourlyRate} onChange={(e) => setHourlyRate(e.target.value)} suffix="€/h" />
            <Input label="Objectif journalier" type="number" min="0" value={dailyGoal} onChange={(e) => setDailyGoal(e.target.value)} suffix="€" />
            <Input label="Objectif hebdo" type="number" min="0" value={weeklyGoal} onChange={(e) => setWeeklyGoal(e.target.value)} suffix="€" />
            <Input label="Objectif mensuel" type="number" min="0" value={monthlyGoal} onChange={(e) => setMonthlyGoal(e.target.value)} suffix="€" />
          </div>
        </Card>

        <Card>
          <p className="text-xs font-semibold uppercase tracking-wide mb-3" style={{ color: "var(--text-secondary)" }}>
            Dépenses fixes mensuelles
          </p>
          <div className="grid grid-cols-2 gap-3">
            <Input label="Part famille" type="number" min="0" value={familyExpense} onChange={(e) => setFamilyExpense(e.target.value)} suffix="€/mois" />
            <Input label="Part personnelle" type="number" min="0" value={personalExpense} onChange={(e) => setPersonalExpense(e.target.value)} suffix="€/mois" />
          </div>
        </Card>

        <Card>
          <p className="text-xs font-semibold uppercase tracking-wide mb-3" style={{ color: "var(--text-secondary)" }}>
            Horaires par défaut
          </p>
          <div className="grid grid-cols-3 gap-3">
            <Input label="Début" type="time" value={defaultStartTime} onChange={(e) => setDefaultStartTime(e.target.value)} />
            <Input label="Fin" type="time" value={defaultEndTime} onChange={(e) => setDefaultEndTime(e.target.value)} />
            <Input label="Pause (min)" type="number" min="0" value={defaultBreakMinutes} onChange={(e) => setDefaultBreakMinutes(e.target.value)} />
          </div>
        </Card>

        <Button onClick={handleSave} loading={saving} size="lg">
          {saved ? "✓ Sauvegardé !" : "Sauvegarder"}
        </Button>

        <div className="pt-4 border-t" style={{ borderColor: "var(--border-color)" }}>
          <Button
            variant="danger"
            onClick={() => signOut({ callbackUrl: "/login" })}
          >
            Se déconnecter
          </Button>
        </div>
      </div>
    </div>
  )
}
