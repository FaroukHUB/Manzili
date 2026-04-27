"use client"

import { useState, useEffect } from "react"
import { Plus, Trash2, CheckCircle, XCircle, Clock } from "lucide-react"
import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Modal } from "@/components/ui/modal"
import { Input, Textarea, Select } from "@/components/ui/input"
import { formatCurrency } from "@/lib/utils"
import type { Contract } from "@/lib/db/schema"

const STATUS_LABELS: Record<string, { label: string; color: string }> = {
  signed:    { label: "Signé",    color: "var(--color-warning)" },
  delivered: { label: "Livré",    color: "var(--color-gain)" },
  cancelled: { label: "Annulé",  color: "var(--color-debt)" },
}

export default function ContractsPage() {
  const [contracts, setContracts] = useState<Contract[]>([])
  const [loading, setLoading] = useState(true)
  const [modalOpen, setModalOpen] = useState(false)
  const [editing, setEditing] = useState<Contract | null>(null)

  const [clientName, setClientName] = useState("")
  const [description, setDescription] = useState("")
  const [totalAmount, setTotalAmount] = useState("")
  const [depositReceived, setDepositReceived] = useState("0")
  const [status, setStatus] = useState("signed")
  const [expectedDate, setExpectedDate] = useState("")
  const [note, setNote] = useState("")
  const [saving, setSaving] = useState(false)

  async function fetchData() {
    try {
      const res = await fetch("/api/contracts")
      if (res.ok) setContracts(await res.json())
    } catch (e) {
      console.error(e)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { fetchData() }, [])

  function openNew() {
    setEditing(null)
    setClientName(""); setDescription(""); setTotalAmount("")
    setDepositReceived("0"); setStatus("signed"); setExpectedDate(""); setNote("")
    setModalOpen(true)
  }

  function openEdit(c: Contract) {
    setEditing(c)
    setClientName(c.clientName); setDescription(c.description || "")
    setTotalAmount(c.totalAmount); setDepositReceived(c.depositReceived)
    setStatus(c.status); setExpectedDate(c.expectedDate || ""); setNote(c.note || "")
    setModalOpen(true)
  }

  async function handleSave() {
    if (!clientName || !totalAmount) return
    setSaving(true)
    const payload = { clientName, description, totalAmount, depositReceived, status, expectedDate: expectedDate || null, note }
    const url = editing ? `/api/contracts/${editing.id}` : "/api/contracts"
    const method = editing ? "PUT" : "POST"
    const res = await fetch(url, { method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) })
    setSaving(false)
    if (res.ok) { await fetchData(); setModalOpen(false) }
  }

  async function handleDelete(id: string) {
    await fetch(`/api/contracts/${id}`, { method: "DELETE" })
    await fetchData()
  }

  const active = contracts.filter(c => c.status === "signed")
  const totalPending = active.reduce((s, c) => s + parseFloat(c.totalAmount) - parseFloat(c.depositReceived), 0)
  const totalDeposits = active.reduce((s, c) => s + parseFloat(c.depositReceived), 0)

  return (
    <div className="p-4 md:p-6 max-w-3xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold" style={{ color: "var(--text-primary)" }}>Contrats</h1>
          <p className="text-sm mt-0.5" style={{ color: "var(--text-secondary)" }}>Contrats signés en attente de paiement</p>
        </div>
        <Button onClick={openNew}><Plus size={16} className="mr-1" />Ajouter</Button>
      </div>

      {/* Summary */}
      {active.length > 0 && (
        <div className="grid grid-cols-3 gap-3 mb-5">
          <Card>
            <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Contrats actifs</p>
            <p className="text-2xl font-bold" style={{ color: "var(--text-primary)" }}>{active.length}</p>
          </Card>
          <Card>
            <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Acomptes reçus</p>
            <p className="text-2xl font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(totalDeposits)}</p>
          </Card>
          <Card>
            <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Reste à encaisser</p>
            <p className="text-2xl font-bold" style={{ color: "var(--color-warning)" }}>{formatCurrency(totalPending)}</p>
          </Card>
        </div>
      )}

      {loading ? (
        <p className="text-sm text-center py-8" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
      ) : contracts.length === 0 ? (
        <Card>
          <p className="text-sm text-center py-4" style={{ color: "var(--text-secondary)" }}>Aucun contrat enregistré</p>
        </Card>
      ) : (
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
          {contracts.map(c => {
            const remaining = parseFloat(c.totalAmount) - parseFloat(c.depositReceived)
            const st = STATUS_LABELS[c.status] || { label: c.status, color: "var(--text-secondary)" }
            return (
              <Card key={c.id} className="flex flex-col gap-2 cursor-pointer" onClick={() => openEdit(c)}>
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-medium px-1.5 py-0.5 rounded-full border" style={{ color: st.color, borderColor: st.color }}>{st.label}</span>
                  <button onClick={e => { e.stopPropagation(); handleDelete(c.id) }} className="p-1 rounded transition-colors hover:bg-red-500/10">
                    <Trash2 size={11} style={{ color: "var(--color-debt)" }} />
                  </button>
                </div>
                <p className="font-semibold text-sm leading-tight" style={{ color: "var(--text-primary)" }}>{c.clientName}</p>
                {c.description && <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>{c.description}</p>}
                <div>
                  <p className="text-xl font-bold" style={{ color: "var(--color-warning)" }}>{formatCurrency(remaining)}</p>
                  <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>acompte {formatCurrency(parseFloat(c.depositReceived))}</p>
                </div>
                {c.expectedDate && <p className="text-[10px] flex items-center gap-1" style={{ color: "var(--text-secondary)" }}><Clock size={9} />{new Date(c.expectedDate).toLocaleDateString("fr-FR")}</p>}
              </Card>
            )
          })}
        </div>
      )}

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? "Modifier le contrat" : "Nouveau contrat"}>
        <div className="flex flex-col gap-4">
          <Input label="Client" value={clientName} onChange={e => setClientName(e.target.value)} placeholder="Nom du client" />
          <Input label="Prestation" value={description} onChange={e => setDescription(e.target.value)} placeholder="Site web, logo..." />
          <div className="grid grid-cols-2 gap-3">
            <Input label="Montant total" type="number" value={totalAmount} onChange={e => setTotalAmount(e.target.value)} suffix="€" />
            <Input label="Acompte reçu" type="number" value={depositReceived} onChange={e => setDepositReceived(e.target.value)} suffix="€" />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <Select label="Statut" value={status} onChange={e => setStatus(e.target.value)} options={[
              { value: "signed", label: "Signé" },
              { value: "delivered", label: "Livré" },
              { value: "cancelled", label: "Annulé" },
            ]} />
            <Input label="Date de livraison" type="date" value={expectedDate} onChange={e => setExpectedDate(e.target.value)} />
          </div>
          <Textarea label="Note" value={note} onChange={e => setNote(e.target.value)} placeholder="Détails..." />
          <Button onClick={handleSave} loading={saving}>{editing ? "Mettre à jour" : "Enregistrer"}</Button>
        </div>
      </Modal>
    </div>
  )
}
