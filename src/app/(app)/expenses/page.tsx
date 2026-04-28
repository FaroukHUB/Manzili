"use client"

import { useState, useEffect } from "react"
import { Plus, Trash2, RefreshCw, Zap } from "lucide-react"
import { Card } from "@/components/ui/card"
import { Modal } from "@/components/ui/modal"
import { Input, Select } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { formatCurrency } from "@/lib/utils"
import type { FixedExpense } from "@/lib/db/schema"

const CATEGORY_LABELS: Record<string, string> = {
  family: "Famille",
  personal: "Perso",
  other: "Autre",
}

const CATEGORY_COLORS: Record<string, string> = {
  family: "var(--color-accent)",
  personal: "var(--color-warning)",
  other: "var(--text-secondary)",
}

export default function ExpensesPage() {
  const [items, setItems] = useState<FixedExpense[]>([])
  const [loading, setLoading] = useState(true)
  const [modalOpen, setModalOpen] = useState(false)
  const [editing, setEditing] = useState<FixedExpense | null>(null)
  const [saving, setSaving] = useState(false)

  const [name, setName] = useState("")
  const [amount, setAmount] = useState("")
  const [category, setCategory] = useState("other")
  const [frequency, setFrequency] = useState<"monthly" | "one_time">("monthly")
  const [expenseDate, setExpenseDate] = useState("")

  async function fetchItems() {
    setLoading(true)
    const res = await fetch("/api/expenses")
    const data = await res.json()
    setItems(Array.isArray(data) ? data.filter((i: FixedExpense) => i.isActive) : [])
    setLoading(false)
  }

  useEffect(() => { fetchItems() }, [])

  function openAdd() {
    setEditing(null)
    setName(""); setAmount(""); setCategory("other"); setFrequency("monthly"); setExpenseDate("")
    setModalOpen(true)
  }

  function openEdit(item: FixedExpense) {
    setEditing(item)
    setName(item.name); setAmount(item.amount); setCategory(item.category)
    setFrequency((item.frequency as "monthly" | "one_time") || "monthly")
    setExpenseDate(item.expenseDate || "")
    setModalOpen(true)
  }

  async function handleSave() {
    setSaving(true)
    const url = editing ? `/api/expenses/${editing.id}` : "/api/expenses"
    const method = editing ? "PUT" : "POST"
    await fetch(url, {
      method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name, amount, category, frequency, expenseDate: expenseDate || null }),
    })
    setSaving(false); setModalOpen(false); fetchItems()
  }

  async function handleDelete(id: string) {
    if (!confirm("Supprimer cette dépense ?")) return
    await fetch(`/api/expenses/${id}`, { method: "DELETE" })
    fetchItems()
  }

  const recurring = items.filter(i => !i.frequency || i.frequency === "monthly")
  const oneTime = items.filter(i => i.frequency === "one_time")

  const totalMonthly = recurring.reduce((s, i) => s + parseFloat(i.amount), 0)
  const totalOneTime = oneTime.reduce((s, i) => s + parseFloat(i.amount), 0)

  const byCategory = recurring.reduce((acc, i) => {
    acc[i.category] = (acc[i.category] || 0) + parseFloat(i.amount)
    return acc
  }, {} as Record<string, number>)

  function ExpenseCard({ item }: { item: FixedExpense }) {
    return (
      <Card className="flex flex-col gap-2 cursor-pointer" onClick={() => openEdit(item)}>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-1.5">
            <div className="w-2 h-2 rounded-full shrink-0" style={{ background: CATEGORY_COLORS[item.category] }} />
            <span className="text-[10px] font-medium" style={{ color: CATEGORY_COLORS[item.category] }}>
              {CATEGORY_LABELS[item.category] || item.category}
            </span>
          </div>
          <button onClick={e => { e.stopPropagation(); handleDelete(item.id) }} className="p-1 rounded transition-colors hover:bg-red-500/10">
            <Trash2 size={11} style={{ color: "var(--color-debt)" }} />
          </button>
        </div>
        <p className="font-semibold text-sm leading-tight" style={{ color: "var(--text-primary)" }}>{item.name}</p>
        <p className="text-xl font-bold" style={{ color: "var(--color-debt)" }}>{formatCurrency(parseFloat(item.amount))}</p>
        {item.frequency === "one_time" && item.expenseDate && (
          <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>
            {new Date(item.expenseDate).toLocaleDateString("fr-FR", { day: "numeric", month: "short" })}
          </p>
        )}
        {(!item.frequency || item.frequency === "monthly") && (
          <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>/ mois</p>
        )}
      </Card>
    )
  }

  return (
    <div className="p-4 md:p-6 max-w-7xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>Dépenses</h1>
        <Button onClick={openAdd} size="sm"><Plus size={16} /> Ajouter</Button>
      </div>

      {/* Summary */}
      {items.length > 0 && (
        <div className="grid grid-cols-2 gap-3 mb-5">
          <Card>
            <div className="flex items-center gap-2 mb-1">
              <RefreshCw size={12} style={{ color: "var(--color-accent)" }} />
              <p className="text-xs" style={{ color: "var(--text-secondary)" }}>Charges fixes/mois</p>
            </div>
            <p className="text-xl font-bold" style={{ color: "var(--color-debt)" }}>{formatCurrency(totalMonthly)}</p>
            <div className="flex flex-wrap gap-2 mt-2">
              {Object.entries(byCategory).map(([cat, amt]) => (
                <div key={cat} className="flex items-center gap-1">
                  <div className="w-1.5 h-1.5 rounded-full" style={{ background: CATEGORY_COLORS[cat] }} />
                  <span className="text-[10px]" style={{ color: "var(--text-secondary)" }}>{CATEGORY_LABELS[cat]} {formatCurrency(amt)}</span>
                </div>
              ))}
            </div>
          </Card>
          <Card>
            <div className="flex items-center gap-2 mb-1">
              <Zap size={12} style={{ color: "var(--color-warning)" }} />
              <p className="text-xs" style={{ color: "var(--text-secondary)" }}>Dépenses ponctuelles</p>
            </div>
            <p className="text-xl font-bold" style={{ color: "var(--color-warning)" }}>{formatCurrency(totalOneTime)}</p>
            <p className="text-[10px] mt-2" style={{ color: "var(--text-secondary)" }}>{oneTime.length} dépense{oneTime.length > 1 ? "s" : ""}</p>
          </Card>
        </div>
      )}

      {loading ? (
        <p className="text-sm text-center py-8" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
      ) : items.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-4xl mb-3">💳</p>
          <p className="font-medium" style={{ color: "var(--text-primary)" }}>Aucune dépense enregistrée</p>
          <p className="text-sm mt-1" style={{ color: "var(--text-secondary)" }}>Ajoute tes dépenses pour mieux gérer ton budget</p>
        </div>
      ) : (
        <div className="space-y-6">
          {/* Récurrentes */}
          {recurring.length > 0 && (
            <div>
              <div className="flex items-center gap-2 mb-3">
                <RefreshCw size={14} style={{ color: "var(--color-accent)" }} />
                <h2 className="text-sm font-semibold" style={{ color: "var(--text-primary)" }}>Récurrentes</h2>
                <span className="text-xs px-2 py-0.5 rounded-full" style={{ background: "var(--bg-hover)", color: "var(--text-secondary)" }}>{recurring.length}</span>
              </div>
              <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                {recurring.map(item => <ExpenseCard key={item.id} item={item} />)}
              </div>
            </div>
          )}

          {/* Ponctuelles */}
          {oneTime.length > 0 && (
            <div>
              <div className="flex items-center gap-2 mb-3">
                <Zap size={14} style={{ color: "var(--color-warning)" }} />
                <h2 className="text-sm font-semibold" style={{ color: "var(--text-primary)" }}>Ponctuelles</h2>
                <span className="text-xs px-2 py-0.5 rounded-full" style={{ background: "var(--bg-hover)", color: "var(--text-secondary)" }}>{oneTime.length}</span>
              </div>
              <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                {oneTime.map(item => <ExpenseCard key={item.id} item={item} />)}
              </div>
            </div>
          )}
        </div>
      )}

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? "Modifier la dépense" : "Nouvelle dépense"}>
        <div className="flex flex-col gap-3">
          <Select
            label="Type"
            value={frequency}
            onChange={e => setFrequency(e.target.value as "monthly" | "one_time")}
            options={[
              { value: "monthly", label: "Récurrente (chaque mois)" },
              { value: "one_time", label: "Ponctuelle (une seule fois)" },
            ]}
          />
          <Input label="Nom" value={name} onChange={e => setName(e.target.value)} placeholder={frequency === "monthly" ? "Ex: Abonnement, Loyer..." : "Ex: Réparation, Achat..."} />
          <Input label="Montant" type="number" min="0" value={amount} onChange={e => setAmount(e.target.value)} suffix={frequency === "monthly" ? "€/mois" : "€"} />
          <Select
            label="Catégorie"
            value={category}
            onChange={e => setCategory(e.target.value)}
            options={[
              { value: "family", label: "Famille" },
              { value: "personal", label: "Personnel" },
              { value: "other", label: "Autre" },
            ]}
          />
          {frequency === "one_time" && (
            <Input label="Date de la dépense" type="date" value={expenseDate} onChange={e => setExpenseDate(e.target.value)} />
          )}
          <Button onClick={handleSave} loading={saving}>{editing ? "Mettre à jour" : "Ajouter"}</Button>
        </div>
      </Modal>
    </div>
  )
}
