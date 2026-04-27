"use client"

import { useState, useEffect } from "react"
import { Plus, Trash2 } from "lucide-react"
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

  async function fetchItems() {
    setLoading(true)
    const res = await fetch("/api/expenses")
    const data = await res.json()
    setItems(Array.isArray(data) ? data.filter((i: FixedExpense) => i.isActive) : [])
    setLoading(false)
  }

  useEffect(() => { fetchItems() }, [])

  function openAdd() {
    setEditing(null); setName(""); setAmount(""); setCategory("other")
    setModalOpen(true)
  }

  function openEdit(item: FixedExpense) {
    setEditing(item); setName(item.name); setAmount(item.amount); setCategory(item.category)
    setModalOpen(true)
  }

  async function handleSave() {
    setSaving(true)
    const url = editing ? `/api/expenses/${editing.id}` : "/api/expenses"
    const method = editing ? "PUT" : "POST"
    await fetch(url, { method, headers: { "Content-Type": "application/json" }, body: JSON.stringify({ name, amount, category }) })
    setSaving(false); setModalOpen(false); fetchItems()
  }

  async function handleDelete(id: string) {
    if (!confirm("Supprimer cette dépense ?")) return
    await fetch(`/api/expenses/${id}`, { method: "DELETE" })
    fetchItems()
  }

  const totalMonthly = items.reduce((s, i) => s + parseFloat(i.amount), 0)
  const byCategory = items.reduce((acc, i) => {
    acc[i.category] = (acc[i.category] || 0) + parseFloat(i.amount)
    return acc
  }, {} as Record<string, number>)

  return (
    <div className="p-4 md:p-6 max-w-2xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>Dépenses fixes</h1>
        <Button onClick={openAdd} size="sm"><Plus size={16} /> Ajouter</Button>
      </div>

      {items.length > 0 && (
        <Card className="mb-5">
          <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Total mensuel</p>
          <p className="text-2xl font-bold mb-4" style={{ color: "var(--color-debt)" }}>{formatCurrency(totalMonthly)}</p>
          <div className="flex flex-wrap gap-3">
            {Object.entries(byCategory).map(([cat, amt]) => (
              <div key={cat} className="flex items-center gap-1.5">
                <div className="w-2 h-2 rounded-full" style={{ background: CATEGORY_COLORS[cat] }} />
                <span className="text-xs" style={{ color: "var(--text-secondary)" }}>
                  {CATEGORY_LABELS[cat] || cat} : {formatCurrency(amt)}
                </span>
              </div>
            ))}
          </div>
        </Card>
      )}

      {loading ? (
        <p className="text-sm text-center py-8" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
      ) : items.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-4xl mb-3">💳</p>
          <p className="font-medium" style={{ color: "var(--text-primary)" }}>Aucune dépense fixe</p>
          <p className="text-sm mt-1" style={{ color: "var(--text-secondary)" }}>Ajoute tes dépenses mensuelles obligatoires</p>
        </div>
      ) : (
        <div className="flex flex-col gap-2">
          {items.map((item) => (
            <Card key={item.id} className="flex items-center justify-between py-3 px-4">
              <div className="flex items-center gap-3">
                <div className="w-2 h-2 rounded-full shrink-0" style={{ background: CATEGORY_COLORS[item.category] }} />
                <div>
                  <p className="font-medium text-sm" style={{ color: "var(--text-primary)" }}>{item.name}</p>
                  <p className="text-xs" style={{ color: "var(--text-secondary)" }}>{CATEGORY_LABELS[item.category] || item.category}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <p className="font-bold" style={{ color: "var(--color-debt)" }}>{formatCurrency(parseFloat(item.amount))}</p>
                <div className="flex gap-1">
                  <Button variant="ghost" size="sm" onClick={() => openEdit(item)}>Modifier</Button>
                  <Button variant="ghost" size="sm" onClick={() => handleDelete(item.id)}><Trash2 size={14} /></Button>
                </div>
              </div>
            </Card>
          ))}
        </div>
      )}

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? "Modifier la dépense" : "Nouvelle dépense fixe"}>
        <div className="flex flex-col gap-3">
          <Input label="Nom" value={name} onChange={(e) => setName(e.target.value)} placeholder="Ex: Famille, Loyer, ..." />
          <Input label="Montant mensuel" type="number" min="0" value={amount} onChange={(e) => setAmount(e.target.value)} suffix="€/mois" />
          <Select
            label="Catégorie"
            value={category}
            onChange={(e) => setCategory(e.target.value)}
            options={[
              { value: "family", label: "Famille" },
              { value: "personal", label: "Personnel" },
              { value: "other", label: "Autre" },
            ]}
          />
          <Button onClick={handleSave} loading={saving}>{editing ? "Mettre à jour" : "Ajouter"}</Button>
        </div>
      </Modal>
    </div>
  )
}
