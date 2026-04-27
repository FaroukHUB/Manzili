"use client"

import { useState, useEffect } from "react"
import { Plus, Trash2, CreditCard } from "lucide-react"
import { Card } from "@/components/ui/card"
import { Modal } from "@/components/ui/modal"
import { Input, Textarea, Select } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { ProgressBar } from "@/components/ui/progress-bar"
import { formatCurrency, formatDateFR } from "@/lib/utils"
import type { Debt } from "@/lib/db/schema"

export default function DebtsPage() {
  const [debts, setDebts] = useState<Debt[]>([])
  const [loading, setLoading] = useState(true)
  const [modalOpen, setModalOpen] = useState(false)
  const [payModalOpen, setPayModalOpen] = useState(false)
  const [editing, setEditing] = useState<Debt | null>(null)
  const [paying, setPaying] = useState<Debt | null>(null)

  // Form state
  const [name, setName] = useState("")
  const [totalAmount, setTotalAmount] = useState("")
  const [paidAmount, setPaidAmount] = useState("0")
  const [priority, setPriority] = useState("1")
  const [dueDate, setDueDate] = useState("")
  const [note, setNote] = useState("")

  // Pay form state
  const [payAmount, setPayAmount] = useState("")
  const [payDate, setPayDate] = useState(new Date().toISOString().split("T")[0])
  const [payNote, setPayNote] = useState("")
  const [saving, setSaving] = useState(false)

  async function fetchDebts() {
    setLoading(true)
    const res = await fetch("/api/debts")
    const data = await res.json()
    setDebts(Array.isArray(data) ? data.filter((d: Debt) => d.isActive) : [])
    setLoading(false)
  }

  useEffect(() => { fetchDebts() }, [])

  function openAdd() {
    setEditing(null)
    setName(""); setTotalAmount(""); setPaidAmount("0"); setPriority("1"); setDueDate(""); setNote("")
    setModalOpen(true)
  }

  function openEdit(debt: Debt) {
    setEditing(debt)
    setName(debt.name); setTotalAmount(debt.totalAmount); setPaidAmount(debt.paidAmount)
    setPriority(String(debt.priority)); setDueDate(debt.dueDate || ""); setNote(debt.note || "")
    setModalOpen(true)
  }

  function openPay(debt: Debt) {
    setPaying(debt)
    setPayAmount("")
    setPayDate(new Date().toISOString().split("T")[0])
    setPayNote("")
    setPayModalOpen(true)
  }

  async function handleSave() {
    setSaving(true)
    const payload = { name, totalAmount, paidAmount, priority, dueDate: dueDate || null, note }
    const url = editing ? `/api/debts/${editing.id}` : "/api/debts"
    const method = editing ? "PUT" : "POST"
    await fetch(url, { method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) })
    setSaving(false)
    setModalOpen(false)
    fetchDebts()
  }

  async function handleDelete(id: string) {
    if (!confirm("Supprimer cette dette ?")) return
    await fetch(`/api/debts/${id}`, { method: "DELETE" })
    fetchDebts()
  }

  async function handlePay() {
    if (!paying) return
    setSaving(true)
    await fetch(`/api/debts/${paying.id}/pay`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ amount: payAmount, paymentDate: payDate, note: payNote }),
    })
    setSaving(false)
    setPayModalOpen(false)
    fetchDebts()
  }

  const totalDebt = debts.reduce((s, d) => s + parseFloat(d.totalAmount), 0)
  const totalPaid = debts.reduce((s, d) => s + parseFloat(d.paidAmount), 0)
  const totalRemaining = totalDebt - totalPaid
  const globalProgress = totalDebt > 0 ? (totalPaid / totalDebt) * 100 : 0

  return (
    <div className="p-4 md:p-6 max-w-7xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>Mes dettes</h1>
        <Button onClick={openAdd} size="sm">
          <Plus size={16} /> Ajouter
        </Button>
      </div>

      {/* Global summary */}
      {debts.length > 0 && (
        <Card className="mb-5">
          <div className="grid grid-cols-3 gap-4 mb-4">
            <div>
              <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Total dettes</p>
              <p className="text-lg font-bold" style={{ color: "var(--color-debt)" }}>{formatCurrency(totalDebt)}</p>
            </div>
            <div>
              <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Déjà remboursé</p>
              <p className="text-lg font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(totalPaid)}</p>
            </div>
            <div>
              <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Reste à payer</p>
              <p className="text-lg font-bold" style={{ color: "var(--text-primary)" }}>{formatCurrency(totalRemaining)}</p>
            </div>
          </div>
          <div>
            <div className="flex justify-between text-xs mb-1.5" style={{ color: "var(--text-secondary)" }}>
              <span>Progression globale</span>
              <span style={{ color: "var(--color-gain)" }}>{globalProgress.toFixed(0)}%</span>
            </div>
            <ProgressBar value={globalProgress} color="green" />
          </div>
        </Card>
      )}

      {/* Debts list */}
      {loading ? (
        <p className="text-sm text-center py-8" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
      ) : debts.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-4xl mb-3">🎉</p>
          <p className="font-medium" style={{ color: "var(--text-primary)" }}>Aucune dette enregistrée</p>
          <p className="text-sm mt-1" style={{ color: "var(--text-secondary)" }}>Ajoute tes dettes pour suivre ta progression</p>
        </div>
      ) : (
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
          {debts.sort((a, b) => a.priority - b.priority).map((debt) => {
            const total = parseFloat(debt.totalAmount)
            const paid = parseFloat(debt.paidAmount)
            const remaining = total - paid
            const pct = total > 0 ? (paid / total) * 100 : 0
            const isPaid = remaining <= 0

            return (
              <Card key={debt.id} className={`flex flex-col gap-2 ${isPaid ? "opacity-60" : ""}`}>
                <div className="flex items-center justify-between">
                  <span className="text-[10px] px-1.5 py-0.5 rounded-full font-medium" style={{ background: "var(--bg-hover)", color: "var(--text-secondary)" }}>#{debt.priority}</span>
                  {isPaid && <span className="text-[10px]" style={{ color: "var(--color-gain)" }}>✓</span>}
                </div>
                <p className="font-semibold text-sm leading-tight" style={{ color: "var(--text-primary)" }}>{debt.name}</p>
                <div>
                  <p className="text-xl font-bold" style={{ color: isPaid ? "var(--color-gain)" : "var(--color-debt)" }}>{formatCurrency(remaining)}</p>
                  <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>sur {formatCurrency(total)}</p>
                </div>
                <ProgressBar value={pct} color={isPaid ? "green" : "red"} className="my-1" />
                {debt.dueDate && <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>Éch. {formatDateFR(debt.dueDate)}</p>}
                <div className="flex gap-1 mt-auto pt-1">
                  {!isPaid && <Button variant="success" size="sm" className="flex-1 text-xs py-1" onClick={() => openPay(debt)}><CreditCard size={12} /></Button>}
                  <Button variant="ghost" size="sm" className="flex-1 text-xs py-1" onClick={() => openEdit(debt)}>✏️</Button>
                  <Button variant="ghost" size="sm" className="text-xs py-1 px-2" onClick={() => handleDelete(debt.id)}><Trash2 size={12} /></Button>
                </div>
              </Card>
            )
          })}
        </div>
      )}

      {/* Add/Edit modal */}
      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? "Modifier la dette" : "Nouvelle dette"}>
        <div className="flex flex-col gap-3">
          <Input label="Nom (personne ou organisme)" value={name} onChange={(e) => setName(e.target.value)} placeholder="Ex: Famille, Banque, ..." />
          <div className="grid grid-cols-2 gap-3">
            <Input label="Montant total" type="number" min="0" value={totalAmount} onChange={(e) => setTotalAmount(e.target.value)} suffix="€" />
            <Input label="Déjà payé" type="number" min="0" value={paidAmount} onChange={(e) => setPaidAmount(e.target.value)} suffix="€" />
          </div>
          <Select
            label="Priorité"
            value={priority}
            onChange={(e) => setPriority(e.target.value)}
            options={[1,2,3,4,5].map(n => ({ value: String(n), label: `Priorité ${n}` }))}
          />
          <Input label="Date limite (optionnel)" type="date" value={dueDate} onChange={(e) => setDueDate(e.target.value)} />
          <Textarea label="Note (optionnel)" value={note} onChange={(e) => setNote(e.target.value)} />
          <Button onClick={handleSave} loading={saving}>{editing ? "Mettre à jour" : "Ajouter"}</Button>
        </div>
      </Modal>

      {/* Pay modal */}
      <Modal open={payModalOpen} onClose={() => setPayModalOpen(false)} title={`Enregistrer un paiement — ${paying?.name}`}>
        <div className="flex flex-col gap-3">
          {paying && (
            <div className="rounded-xl p-3" style={{ background: "var(--color-gain-bg)" }}>
              <p className="text-xs" style={{ color: "var(--color-gain)" }}>
                Reste à payer : {formatCurrency(parseFloat(paying.totalAmount) - parseFloat(paying.paidAmount))}
              </p>
            </div>
          )}
          <Input label="Montant payé" type="number" min="0" value={payAmount} onChange={(e) => setPayAmount(e.target.value)} suffix="€" autoFocus />
          <Input label="Date du paiement" type="date" value={payDate} onChange={(e) => setPayDate(e.target.value)} />
          <Textarea label="Note (optionnel)" value={payNote} onChange={(e) => setPayNote(e.target.value)} />
          <Button onClick={handlePay} loading={saving}>Confirmer le paiement</Button>
        </div>
      </Modal>
    </div>
  )
}
