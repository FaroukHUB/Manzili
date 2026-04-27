"use client"

import { useState, useEffect } from "react"
import Link from "next/link"
import { ArrowRight, Calendar, TrendingDown, HandCoins, Plus } from "lucide-react"
import { Card } from "@/components/ui/card"
import { ProgressBar } from "@/components/ui/progress-bar"
import {
  formatCurrency,
  formatHours,
  getMotivationMessage,
  getDaysUntilDebtFree,
  toISODate,
} from "@/lib/utils"
import type { WorkLog, Debt, FixedExpense, Settings, Receivable } from "@/lib/db/schema"

export default function DashboardPage() {
  const [loading, setLoading] = useState(true)
  const [todayLog, setTodayLog] = useState<WorkLog | null>(null)
  const [monthLogs, setMonthLogs] = useState<WorkLog[]>([])
  const [debts, setDebts] = useState<Debt[]>([])
  const [expenses, setExpenses] = useState<FixedExpense[]>([])
  const [receivables, setReceivables] = useState<Receivable[]>([])
  const [settings, setSettings] = useState<Settings | null>(null)

  useEffect(() => {
    async function fetchAll() {
      const now = new Date()
      const monthStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`
      const todayStr = toISODate(now)

      const [logsRes, debtsRes, expensesRes, settingsRes, receivablesRes] = await Promise.all([
        fetch(`/api/work-logs?month=${monthStr}`),
        fetch("/api/debts"),
        fetch("/api/expenses"),
        fetch("/api/settings"),
        fetch("/api/receivables"),
      ])

      const [logs, dbts, exps, sett, recvs] = await Promise.all([
        logsRes.json(), debtsRes.json(), expensesRes.json(), settingsRes.json(), receivablesRes.json()
      ])

      const allLogs: WorkLog[] = Array.isArray(logs) ? logs : []
      setMonthLogs(allLogs)
      setTodayLog(allLogs.find((l) => l.date === todayStr) ?? null)
      setDebts(Array.isArray(dbts) ? dbts.filter((d: Debt) => d.isActive) : [])
      setExpenses(Array.isArray(exps) ? exps.filter((e: FixedExpense) => e.isActive) : [])
      setSettings(sett)
      setReceivables(Array.isArray(recvs) ? recvs : [])
      setLoading(false)
    }
    fetchAll()
  }, [])

  // Calculations
  const monthlyEarnings = monthLogs.reduce((s, l) => s + parseFloat(l.earnings), 0)
  const monthlyHours = monthLogs.reduce((s, l) => s + parseFloat(l.hoursWorked), 0)
  const totalExpenses = expenses.reduce((s, e) => s + parseFloat(e.amount), 0)
  const availableForDebt = Math.max(0, monthlyEarnings - totalExpenses)

  const totalDebt = debts.reduce((s, d) => s + parseFloat(d.totalAmount), 0)
  const totalPaid = debts.reduce((s, d) => s + parseFloat(d.paidAmount), 0)
  const totalRemaining = totalDebt - totalPaid
  const debtProgress = totalDebt > 0 ? (totalPaid / totalDebt) * 100 : 0

  const topDebt = debts.sort((a, b) => a.priority - b.priority)[0]
  const topDebtRemaining = topDebt ? parseFloat(topDebt.totalAmount) - parseFloat(topDebt.paidAmount) : 0

  const totalPending = receivables
    .filter((r) => r.status !== "received")
    .reduce((s, r) => s + (parseFloat(r.totalAmount) - parseFloat(r.receivedAmount)), 0)

  const avgDailyContrib = monthLogs.length > 0
    ? availableForDebt / new Date().getDate()
    : 0

  const daysToFree = getDaysUntilDebtFree(totalRemaining, avgDailyContrib)

  const motivationMsg = getMotivationMessage({
    totalDebt,
    paidDebt: totalPaid,
    dailyEarnings: todayLog ? parseFloat(todayLog.earnings) : 0,
    daysToPayoff: daysToFree ?? undefined,
    topDebtName: topDebt?.name,
    topDebtRemaining,
  })

  const dailyGoal = parseFloat(settings?.dailyGoal || "80")
  const todayEarnings = todayLog ? parseFloat(todayLog.earnings) : 0
  const todayFamilyShare = todayLog ? (totalExpenses / 30) : 0
  const todayNetForDebt = Math.max(0, todayEarnings - todayFamilyShare)

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-dvh">
        <p className="text-sm" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
      </div>
    )
  }

  return (
    <div className="p-4 md:p-6 max-w-3xl mx-auto">
      {/* Header */}
      <div className="mb-6">
        <h1 className="text-2xl font-bold" style={{ color: "var(--text-primary)" }}>
          Bonjour 👋
        </h1>
        <p className="text-sm mt-1" style={{ color: "var(--text-secondary)" }}>
          {new Date().toLocaleDateString("fr-FR", { weekday: "long", day: "numeric", month: "long" })}
        </p>
      </div>

      {/* Today block */}
      <Card className="mb-4">
        <div className="flex items-center justify-between mb-3">
          <p className="text-xs font-semibold uppercase tracking-wide" style={{ color: "var(--text-secondary)" }}>
            Aujourd'hui
          </p>
          <Link href="/calendar" className="flex items-center gap-1 text-xs" style={{ color: "var(--color-accent)" }}>
            Calendrier <ArrowRight size={12} />
          </Link>
        </div>

        {todayLog ? (
          <div>
            <div className="grid grid-cols-2 gap-4 mb-3">
              <div>
                <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Heures travaillées</p>
                <p className="text-2xl font-bold" style={{ color: "var(--text-primary)" }}>
                  {formatHours(parseFloat(todayLog.hoursWorked))}
                </p>
              </div>
              <div>
                <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Gain du jour</p>
                <p className="text-2xl font-bold" style={{ color: "var(--color-gain)" }}>
                  {formatCurrency(todayEarnings)}
                </p>
              </div>
            </div>
            <div
              className="rounded-xl p-3 grid grid-cols-3 gap-2 text-center"
              style={{ background: "var(--bg-hover)" }}
            >
              <div>
                <p className="text-[10px] mb-0.5" style={{ color: "var(--text-secondary)" }}>Famille</p>
                <p className="text-sm font-semibold" style={{ color: "var(--color-debt)" }}>
                  −{formatCurrency(todayFamilyShare)}
                </p>
              </div>
              <div>
                <p className="text-[10px] mb-0.5" style={{ color: "var(--text-secondary)" }}>Perso</p>
                <p className="text-sm font-semibold" style={{ color: "var(--color-warning)" }}>
                  −{formatCurrency(totalExpenses / 30 * 0.3)}
                </p>
              </div>
              <div>
                <p className="text-[10px] mb-0.5" style={{ color: "var(--text-secondary)" }}>Dettes</p>
                <p className="text-sm font-semibold" style={{ color: "var(--color-gain)" }}>
                  +{formatCurrency(todayNetForDebt)}
                </p>
              </div>
            </div>
            {todayEarnings >= dailyGoal && (
              <p className="text-xs mt-2 font-medium" style={{ color: "var(--color-gain)" }}>
                ✓ Objectif journalier atteint ({formatCurrency(dailyGoal)}) !
              </p>
            )}
          </div>
        ) : (
          <div className="flex items-center justify-between">
            <p className="text-sm" style={{ color: "var(--text-secondary)" }}>
              Pas encore enregistré aujourd'hui
            </p>
            <Link
              href="/calendar"
              className="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium"
              style={{ background: "var(--color-accent)", color: "white" }}
            >
              <Plus size={14} /> Ajouter
            </Link>
          </div>
        )}
      </Card>

      {/* Month block */}
      <Card className="mb-4">
        <div className="flex items-center justify-between mb-3">
          <p className="text-xs font-semibold uppercase tracking-wide" style={{ color: "var(--text-secondary)" }}>
            Ce mois-ci
          </p>
          <span className="text-xs" style={{ color: "var(--text-secondary)" }}>
            {monthLogs.length} jour{monthLogs.length > 1 ? "s" : ""} travaillé{monthLogs.length > 1 ? "s" : ""}
          </span>
        </div>
        <div className="grid grid-cols-2 gap-4 mb-3">
          <div>
            <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Total gagné</p>
            <p className="text-xl font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(monthlyEarnings)}</p>
          </div>
          <div>
            <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Heures totales</p>
            <p className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>{formatHours(monthlyHours)}</p>
          </div>
          <div>
            <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Dépenses fixes</p>
            <p className="text-xl font-bold" style={{ color: "var(--color-debt)" }}>{formatCurrency(totalExpenses)}</p>
          </div>
          <div>
            <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Dispo pour dettes</p>
            <p className="text-xl font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(availableForDebt)}</p>
          </div>
        </div>
        {settings && (
          <div>
            <div className="flex justify-between text-xs mb-1" style={{ color: "var(--text-secondary)" }}>
              <span>Progression vers objectif mensuel</span>
              <span>{Math.min(100, (monthlyEarnings / parseFloat(settings.monthlyGoal)) * 100).toFixed(0)}%</span>
            </div>
            <ProgressBar value={(monthlyEarnings / parseFloat(settings.monthlyGoal)) * 100} color="blue" />
          </div>
        )}
      </Card>

      {/* Debts block */}
      <Card className="mb-4">
        <div className="flex items-center justify-between mb-3">
          <p className="text-xs font-semibold uppercase tracking-wide" style={{ color: "var(--text-secondary)" }}>
            Mes dettes
          </p>
          <Link href="/debts" className="flex items-center gap-1 text-xs" style={{ color: "var(--color-accent)" }}>
            Détail <ArrowRight size={12} />
          </Link>
        </div>
        {debts.length === 0 ? (
          <p className="text-sm" style={{ color: "var(--text-secondary)" }}>Aucune dette enregistrée</p>
        ) : (
          <div>
            <div className="grid grid-cols-3 gap-4 mb-4">
              <div>
                <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Total</p>
                <p className="text-lg font-bold" style={{ color: "var(--color-debt)" }}>{formatCurrency(totalDebt)}</p>
              </div>
              <div>
                <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Remboursé</p>
                <p className="text-lg font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(totalPaid)}</p>
              </div>
              <div>
                <p className="text-xs mb-0.5" style={{ color: "var(--text-secondary)" }}>Reste</p>
                <p className="text-lg font-bold" style={{ color: "var(--text-primary)" }}>{formatCurrency(totalRemaining)}</p>
              </div>
            </div>
            <div className="mb-2">
              <div className="flex justify-between text-xs mb-1" style={{ color: "var(--text-secondary)" }}>
                <span>Progression globale</span>
                <span style={{ color: "var(--color-gain)" }}>{debtProgress.toFixed(0)}%</span>
              </div>
              <ProgressBar value={debtProgress} color="green" />
            </div>
            {daysToFree && (
              <p className="text-xs mt-2" style={{ color: "var(--text-secondary)" }}>
                À ce rythme : libre dans ~{daysToFree} jours
              </p>
            )}
          </div>
        )}
      </Card>

      {/* Receivables */}
      {totalPending > 0 && (
        <Card className="mb-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide mb-1" style={{ color: "var(--text-secondary)" }}>
                On me doit encore
              </p>
              <p className="text-xl font-bold" style={{ color: "var(--color-warning)" }}>{formatCurrency(totalPending)}</p>
            </div>
            <Link href="/receivables" className="flex items-center gap-1 text-xs" style={{ color: "var(--color-accent)" }}>
              Voir <ArrowRight size={12} />
            </Link>
          </div>
        </Card>
      )}

      {/* Motivation block */}
      <div
        className="rounded-2xl p-4 border"
        style={{ background: "var(--color-accent-bg)", borderColor: "var(--color-accent)" }}
      >
        <p className="text-xs font-semibold mb-2" style={{ color: "var(--color-accent)" }}>
          Message du jour
        </p>
        <p className="text-sm leading-relaxed" style={{ color: "var(--text-primary)" }}>
          {motivationMsg}
        </p>
      </div>
    </div>
  )
}
