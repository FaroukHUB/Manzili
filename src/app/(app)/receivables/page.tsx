"use client"

import { useState, useEffect } from "react"
import { Plus, Trash2, CheckCircle } from "lucide-react"
import { Card } from "@/components/ui/card"
import { Modal } from "@/components/ui/modal"
import { Input, Textarea, Select } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { ProgressBar } from "@/components/ui/progress-bar"
import { formatCurrency, formatDateFR } from "@/lib/utils"
import type { Receivable, Asset } from "@/lib/db/schema"

const STATUS_LABELS: Record<string, { label: string; color: string }> = {
  pending: { label: "En attente", color: "var(--color-warning)" },
  partial: { label: "Partiel", color: "var(--color-accent)" },
  received: { label: "Reçu", color: "var(--color-gain)" },
}

function WhatsAppIcon() {
  return (
    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
  )
}

export default function ReceivablesPage() {
  const [items, setItems] = useState<Receivable[]>([])
  const [assets, setAssets] = useState<Asset[]>([])
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
  const [whatsappNumber, setWhatsappNumber] = useState("")

  const [receiveModalOpen, setReceiveModalOpen] = useState(false)
  const [receiving, setReceiving] = useState<Receivable | null>(null)
  const [receiveSourceId, setReceiveSourceId] = useState("")

  async function fetchItems() {
    setLoading(true)
    const [itemRes, assetRes] = await Promise.all([fetch("/api/receivables"), fetch("/api/assets")])
    const itemData = await itemRes.json()
    const assetData = await assetRes.json()
    setItems(Array.isArray(itemData) ? itemData : [])
    setAssets(Array.isArray(assetData) ? assetData : [])
    setLoading(false)
  }

  useEffect(() => { fetchItems() }, [])

  function openAdd() {
    setEditing(null)
    setName(""); setReason(""); setTotalAmount(""); setReceivedAmount("0"); setExpectedDate(""); setNote(""); setWhatsappNumber("")
    setModalOpen(true)
  }

  function openEdit(item: Receivable) {
    setEditing(item)
    setName(item.name); setReason(item.reason || ""); setTotalAmount(item.totalAmount)
    setReceivedAmount(item.receivedAmount); setExpectedDate(item.expectedDate || ""); setNote(item.note || "")
    setWhatsappNumber(item.whatsappNumber || "")
    setModalOpen(true)
  }

  async function handleSave() {
    setSaving(true)
    const payload = { name, reason, totalAmount, receivedAmount, expectedDate: expectedDate || null, note, whatsappNumber: whatsappNumber || null }
    const url = editing ? `/api/receivables/${editing.id}` : "/api/receivables"
    const method = editing ? "PUT" : "POST"
    await fetch(url, { method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) })
    setSaving(false); setModalOpen(false); fetchItems()
  }

  function openReceive(item: Receivable) {
    setReceiving(item)
    setReceiveSourceId(assets[0]?.id || "")
    setReceiveModalOpen(true)
  }

  async function confirmReceive() {
    if (!receiving) return
    setSaving(true)
    const delta = parseFloat(receiving.totalAmount) - parseFloat(receiving.receivedAmount)
    await fetch(`/api/receivables/${receiving.id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ...receiving, receivedAmount: receiving.totalAmount }),
    })
    if (receiveSourceId && delta > 0) {
      await fetch(`/api/assets/${receiveSourceId}/adjust`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ delta }),
      })
    }
    setSaving(false)
    setReceiveModalOpen(false)
    fetchItems()
  }

  async function handleDelete(id: string) {
    if (!confirm("Supprimer ?")) return
    await fetch(`/api/receivables/${id}`, { method: "DELETE" })
    fetchItems()
  }

  function sendReminder(item: Receivable) {
    const num = item.whatsappNumber?.replace(/\s/g, "") || ""
    const remaining = parseFloat(item.totalAmount) - parseFloat(item.receivedAmount)
    const msg = `Salam ${item.name} 👋\n\nJ'espère que tu vas bien ! Petit rappel : tu me dois encore ${formatCurrency(remaining)}${item.reason ? ` (${item.reason})` : ""}.\n\nPas d'urgence, mais tiens-moi au courant dès que tu peux 🙏`
    const url = num
      ? `https://wa.me/${num}?text=${encodeURIComponent(msg)}`
      : `https://wa.me/?text=${encodeURIComponent(msg)}`
    window.open(url, "_blank")
  }

  const totalExpected = items.reduce((s, i) => s + parseFloat(i.totalAmount), 0)
  const totalReceived = items.reduce((s, i) => s + parseFloat(i.receivedAmount), 0)
  const totalPending = totalExpected - totalReceived

  return (
    <div className="p-4 md:p-6 max-w-7xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>On me doit</h1>
        <Button onClick={openAdd} size="sm"><Plus size={16} /> Ajouter</Button>
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
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
          {items.map((item) => {
            const total = parseFloat(item.totalAmount)
            const received = parseFloat(item.receivedAmount)
            const remaining = total - received
            const pct = total > 0 ? (received / total) * 100 : 0
            const status = STATUS_LABELS[item.status] || STATUS_LABELS.pending
            return (
              <Card key={item.id} className="flex flex-col gap-2">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] px-1.5 py-0.5 rounded-full font-medium border" style={{ color: status.color, borderColor: status.color }}>{status.label}</span>
                </div>
                <p className="font-semibold text-sm leading-tight" style={{ color: "var(--text-primary)" }}>{item.name}</p>
                {item.reason && <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>{item.reason}</p>}
                <div>
                  <p className="text-xl font-bold" style={{ color: "var(--color-warning)" }}>{formatCurrency(remaining)}</p>
                  <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>sur {formatCurrency(total)}</p>
                </div>
                <ProgressBar value={pct} color="amber" className="my-1" />
                {item.expectedDate && <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>Prévu {formatDateFR(item.expectedDate)}</p>}
                <div className="flex gap-1 mt-auto pt-1">
                  {item.status !== "received" && (
                    <Button variant="success" size="sm" className="flex-1 text-xs py-1" onClick={() => openReceive(item)}>
                      <CheckCircle size={12} />
                    </Button>
                  )}
                  {item.status !== "received" && item.whatsappNumber && (
                    <button
                      onClick={() => sendReminder(item)}
                      className="flex items-center justify-center px-2 py-1 rounded-lg text-xs font-medium"
                      style={{ background: "#25d366", color: "white" }}
                      title="Relancer sur WhatsApp"
                    >
                      <WhatsAppIcon />
                    </button>
                  )}
                  <Button variant="ghost" size="sm" className="flex-1 text-xs py-1" onClick={() => openEdit(item)}>✏️</Button>
                  <Button variant="ghost" size="sm" className="text-xs py-1 px-2" onClick={() => handleDelete(item.id)}><Trash2 size={12} /></Button>
                </div>
              </Card>
            )
          })}
        </div>
      )}

      <Modal open={receiveModalOpen} onClose={() => setReceiveModalOpen(false)} title={`Encaisser — ${receiving?.name}`}>
        <div className="flex flex-col gap-3">
          {receiving && (
            <div className="rounded-xl p-3" style={{ background: "var(--color-gain-bg)" }}>
              <p className="text-sm font-semibold" style={{ color: "var(--color-gain)" }}>
                + {formatCurrency(parseFloat(receiving.totalAmount) - parseFloat(receiving.receivedAmount))}
              </p>
              <p className="text-xs mt-0.5" style={{ color: "var(--color-gain)" }}>Montant à encaisser</p>
            </div>
          )}
          {assets.length > 0 && (
            <Select label="Créditer sur" value={receiveSourceId} onChange={(e) => setReceiveSourceId(e.target.value)}
              options={[{ value: "", label: "— Ne pas créditer —" }, ...assets.map(a => ({ value: a.id, label: `${a.name} (${formatCurrency(parseFloat(a.amount))})` }))]} />
          )}
          <Button onClick={confirmReceive} loading={saving}>Confirmer la réception</Button>
        </div>
      </Modal>

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? "Modifier" : "Ce qu'on me doit"}>
        <div className="flex flex-col gap-3">
          <Input label="Nom (qui me doit ?)" value={name} onChange={(e) => setName(e.target.value)} placeholder="Ex: Client Dupont, Frère, ..." />
          <Input label="N° WhatsApp (optionnel)" value={whatsappNumber} onChange={(e) => setWhatsappNumber(e.target.value)} placeholder="+33612345678" />
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
