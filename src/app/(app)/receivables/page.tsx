"use client"

import { useState, useEffect } from "react"
import { Plus, Trash2, CheckCircle } from "lucide-react"
import { Card } from "@/components/ui/card"
import { Modal } from "@/components/ui/modal"
import { Input, Textarea } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { ProgressBar } from "@/components/ui/progress-bar"
import { formatCurrency, formatDateFR } from "@/lib/utils"
import type { Receivable } from "@/lib/db/schema"

const STATUS_LABELS: Record<string, { label: string; color: string }> = {
  pending: { label: "En attente", color: "var(--color-warning)" },
  partial: { label: "Partiel", color: "var(--color-accent)" },
  received: { label: "Reçu", color: "var(--color-gain)" },
}

export default function ReceivablesPage() {
  const [items, setItems] = useState<Receivable[]>([])
  const [loading, setLoading] = useState(true)
  const [modalOpen, setModalOpen] = useState(false)
  const [editing, setEditing] = useState<Receivable | null>(null)
  const [saving, setSaving] = useState(false)

  const [name, setName] = useState("")
  const [reason, setReason] = useState("")
  const [totalAmount, setTotalAmount] = useState("")
  const [receivedAmount, setReceivedAmount] = useState("0")
  const [expectedDate, setExpectedDate] = useState("")
  const [note, setNote] = useState("")

  async function fetchItems() {
    setLoading(true)
    const res = await fetch("/api/receivables")
    const data = await res.json()
    setItems(Array.isArray(data) ? data : [])
    setLoading(false)
  }

  useEffect(() => { fetchItems() }, [])

  function openAdd() {
    setEditing(null)
    setName(""); setReason(""); setTotalAmount(""); setReceivedAmount("0"); setExpectedDate(""); setNote("")
    setModalOpen(true)
  }

  function openEdit(item: Receivable) {
    setEditing(item)
    setName(item.name); setReason(item.reason || ""); setTotalAmount(item.totalAmount)
    setReceivedAmount(item.receivedAmount); setExpectedDate(item.expectedDate || ""); setNote(item.note || "")
    setModalOpen(true)
  }

  async function handleSave() {
    setSaving(true)
    const payload = { name, reason, totalAmount, receivedAmount, expectedDate: expectedDate || null, note }
    const url = editing ? `/api/receivables/${editing.id}` : "/api/receivables"
    const method = editing ? "PUT" : "POST"
    await fetch(url, { method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) })
    setSaving(false)
    setModalOpen(false)
    fetchItems()
  }

  async function markReceived(item: Receivable) {
    await fetch(`/api/receivables/${item.id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ...item, receivedAmount: item.totalAmount }),
    })
    fetchItems()
  }

  async function handleDelete(id: string) {
    if (!confirm("Supprimer ?")) return
    await fetch(`/api/receivables/${id}`, { method: "DELETE" })
    fetchItems()
  }

  const totalExpected = items.reduce((s, i) => s + parseFloat(i.totalAmount), 0)
  const totalReceived = items.reduce((s, i) => s + parseFloat(i.receivedAmount), 0)
  const totalPending = totalExpected - totalReceived

  return (
    <div className="p-4 md:p-6 max-w-2xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>On me doit</h1>
        <Button onClick={openAdd} size="sm">
          <Plus size={16} /> Ajouter
        </Button>
      </div>

      {items.length > 0 && (
        <Card className="mb-5">
          <div className="grid grid-cols-3 gap-4">
            <div>
              <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Total attendu</p>
              <p className="text-lg font-bold" style={{ color: "var(--text-primary)" }}>{formatCurrency(totalExpected)}</p>
            </div>
            <div>
              <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Déjà reçu</p>
              <p className="text-lg font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(totalReceived)}</p>
            </div>
            <div>
              <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>En attente</p>
              <p className="text-lg font-bold" style={{ color: "var(--color-warning)" }}>{formatCurrency(totalPending)}</p>
            </div>
          </div>
        </Card>
      )}

      {loading ? (
        <p className="text-sm text-center py-8" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
      ) : items.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-4xl mb-3">🤝</p>
          <p className="font-medium" style={{ color: "var(--text-primary)" }}>Personne ne te doit rien</p>
          <p className="text-sm mt-1" style={{ color: "var(--text-secondary)" }}>Ajoute ce qu'on te doit pour le suivre</p>
        </div>
      ) : (
        <div className="flex flex-col gap-3">
          {items.map((item) => {
            const total = parseFloat(item.totalAmount)
            const received = parseFloat(item.receivedAmount)
            const remaining = total - received
            const pct = total > 0 ? (received / total) * 100 : 0
            const status = STATUS_LABELS[item.status] || STATUS_LABELS.pending

            return (
              <Card key={item.id}>
                <div className="flex items-start justify-between mb-3">
                  <div>
                    <div className="flex items-center gap-2">
                      <h3 className="font-semibold" style={{ color: "var(--text-primary)" }}>{item.name}</h3>
                      <span className="text-xs px-2 py-0.5 rounded-full" style={{ background: "var(--bg-hover)", color: status.color }}>
                        {status.label}
                      </span>
                    </div>
                    {item.reason && <p className="text-xs mt-0.5" style={{ color: "var(--text-secondary)" }}>{item.reason}</p>}
                    {item.expectedDate && (
                      <p className="text-xs mt-0.5" style={{ color: "var(--text-secondary)" }}>
                        Prévu le : {formatDateFR(item.expectedDate)}
                      </p>
                    )}
                  </div>
                  <div className="text-right">
                    <p className="font-bold" style={{ color: "var(--color-warning)" }}>{formatCurrency(remaining)}</p>
                    <p className="text-xs" style={{ color: "var(--text-secondary)" }}>/ {formatCurrency(total)}</p>
                  </div>
                </div>
                <ProgressBar value={pct} color="amber" showLabel className="mb-3" />
                <div className="flex gap-2">
                  {item.status !== "received" && (
                    <Button variant="success" size="sm" onClick={() => markReceived(item)}>
                      <CheckCircle size={14} /> Reçu en totalité
                    </Button>
                  )}
                  <Button variant="ghost" size="sm" onClick={() => openEdit(item)}>Modifier</Button>
                  <Button variant="ghost" size="sm" onClick={() => handleDelete(item.id)}>
                    <Trash2 size={14} />
                  </Button>
                </div>
              </Card>
            )
          })}
        </div>
      )}

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? "Modifier" : "Ce qu'on me doit"}>
        <div className="flex flex-col gap-3">
          <Input label="Nom (qui me doit ?)" value={name} onChange={(e) => setName(e.target.value)} placeholder="Ex: Client Dupont, Frère, ..." />
          <Input label="Motif (optionnel)" value={reason} onChange={(e) => setReason(e.target.value)} placeholder="Ex: Site web, prêt, ..." />
          <div className="grid grid-cols-2 gap-3">
            <Input label="Montant dû" type="number" min="0" value={totalAmount} onChange={(e) => setTotalAmount(e.target.value)} suffix="€" />
            <Input label="Déjà reçu" type="number" min="0" value={receivedAmount} onChange={(e) => setReceivedAmount(e.target.value)} suffix="€" />
          </div>
          <Input label="Date prévue (optionnel)" type="date" value={expectedDate} onChange={(e) => setExpectedDate(e.target.value)} />
          <Textarea label="Note (optionnel)" value={note} onChange={(e) => setNote(e.target.value)} />
          <Button onClick={handleSave} loading={saving}>{editing ? "Mettre à jour" : "Ajouter"}</Button>
        </div>
      </Modal>
    </div>
  )
}
