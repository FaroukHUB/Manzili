"use client"

import { useState, useEffect } from "react"
import { Plus, Trash2, Clock } from "lucide-react"
import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Modal } from "@/components/ui/modal"
import { Input, Textarea, Select } from "@/components/ui/input"
import { formatCurrency } from "@/lib/utils"
import type { Contract, Asset } from "@/lib/db/schema"

const STATUS_LABELS: Record<string, { label: string; color: string }> = {
  signed:    { label: "Signé",    color: "var(--color-warning)" },
  delivered: { label: "Livré",    color: "var(--color-gain)" },
  cancelled: { label: "Annulé",  color: "var(--color-debt)" },
}

function WhatsAppIcon() {
  return (
    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
  )
}

export default function ContractsPage() {
  const [contracts, setContracts] = useState<Contract[]>([])
  const [assets, setAssets] = useState<Asset[]>([])
  const [loading, setLoading] = useState(true)
  const [modalOpen, setModalOpen] = useState(false)
  const [editing, setEditing] = useState<Contract | null>(null)

  const [clientName, setClientName] = useState("")
  const [description, setDescription] = useState("")
  const [totalAmount, setTotalAmount] = useState("")
  const [depositReceived, setDepositReceived] = useState("0")
  const [depositSourceId, setDepositSourceId] = useState("")
  const [status, setStatus] = useState("signed")
  const [expectedDate, setExpectedDate] = useState("")
  const [note, setNote] = useState("")
  const [whatsappNumber, setWhatsappNumber] = useState("")
  const [saving, setSaving] = useState(false)

  async function fetchData() {
    try {
      const [contractRes, assetRes] = await Promise.all([fetch("/api/contracts"), fetch("/api/assets")])
      if (contractRes.ok) setContracts(await contractRes.json())
      if (assetRes.ok) setAssets(await assetRes.json())
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
    setDepositReceived("0"); setDepositSourceId(assets[0]?.id || ""); setStatus("signed"); setExpectedDate(""); setNote(""); setWhatsappNumber("")
    setModalOpen(true)
  }

  function openEdit(c: Contract) {
    setEditing(c)
    setClientName(c.clientName); setDescription(c.description || "")
    setTotalAmount(c.totalAmount); setDepositReceived(c.depositReceived)
    setDepositSourceId(assets[0]?.id || "")
    setStatus(c.status); setExpectedDate(c.expectedDate || ""); setNote(c.note || "")
    setWhatsappNumber(c.whatsappNumber || "")
    setModalOpen(true)
  }

  async function handleSave() {
    if (!clientName || !totalAmount) return
    setSaving(true)
    const prevDeposit = parseFloat(editing?.depositReceived || "0")
    const newDeposit = parseFloat(depositReceived || "0")
    const payload = { clientName, description, totalAmount, depositReceived, status, expectedDate: expectedDate || null, note, whatsappNumber: whatsappNumber || null }
    const url = editing ? `/api/contracts/${editing.id}` : "/api/contracts"
    const method = editing ? "PUT" : "POST"
    const res = await fetch(url, { method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) })
    const delta = newDeposit - prevDeposit
    if (res.ok && depositSourceId && delta > 0) {
      await fetch(`/api/assets/${depositSourceId}/adjust`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ delta }),
      })
    }
    setSaving(false)
    if (res.ok) { await fetchData(); setModalOpen(false) }
  }

  async function handleDelete(id: string) {
    await fetch(`/api/contracts/${id}`, { method: "DELETE" })
    await fetchData()
  }

  function sendFollowUp(c: Contract) {
    const num = c.whatsappNumber?.replace(/\s/g, "") || ""
    const remaining = parseFloat(c.totalAmount) - parseFloat(c.depositReceived)
    const msg = `Bonjour ${c.clientName} 👋\n\nJ'espère que vous allez bien ! Je souhaitais faire un point sur notre projet${c.description ? ` (${c.description})` : ""}.\n\nIl reste ${formatCurrency(remaining)} à régler. N'hésitez pas à me contacter si vous avez des questions ou souhaitez fixer une date de règlement 😊\n\nBonne journée !`
    const url = num
      ? `https://wa.me/${num}?text=${encodeURIComponent(msg)}`
      : `https://wa.me/?text=${encodeURIComponent(msg)}`
    window.open(url, "_blank")
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
              <Card key={c.id} className="flex flex-col gap-2">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-medium px-1.5 py-0.5 rounded-full border" style={{ color: st.color, borderColor: st.color }}>{st.label}</span>
                  <button onClick={() => handleDelete(c.id)} className="p-1 rounded transition-colors hover:bg-red-500/10">
                    <Trash2 size={11} style={{ color: "var(--color-debt)" }} />
                  </button>
                </div>
                <p className="font-semibold text-sm leading-tight cursor-pointer" style={{ color: "var(--text-primary)" }} onClick={() => openEdit(c)}>{c.clientName}</p>
                {c.description && <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>{c.description}</p>}
                <div>
                  <p className="text-xl font-bold" style={{ color: "var(--color-warning)" }}>{formatCurrency(remaining)}</p>
                  <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>acompte {formatCurrency(parseFloat(c.depositReceived))}</p>
                </div>
                {c.expectedDate && <p className="text-[10px] flex items-center gap-1" style={{ color: "var(--text-secondary)" }}><Clock size={9} />{new Date(c.expectedDate).toLocaleDateString("fr-FR")}</p>}
                <div className="flex gap-1 mt-auto pt-1">
                  <Button variant="ghost" size="sm" className="flex-1 text-xs py-1" onClick={() => openEdit(c)}>✏️</Button>
                  {c.status === "signed" && c.whatsappNumber && (
                    <button
                      onClick={() => sendFollowUp(c)}
                      className="flex items-center justify-center px-2 py-1 rounded-lg"
                      style={{ background: "#25d366", color: "white" }}
                      title="Relancer le client"
                    >
                      <WhatsAppIcon />
                    </button>
                  )}
                </div>
              </Card>
            )
          })}
        </div>
      )}

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? "Modifier le contrat" : "Nouveau contrat"}>
        <div className="flex flex-col gap-4">
          <Input label="Client" value={clientName} onChange={e => setClientName(e.target.value)} placeholder="Nom du client" />
          <Input label="N° WhatsApp client (optionnel)" value={whatsappNumber} onChange={e => setWhatsappNumber(e.target.value)} placeholder="+33612345678" />
          <Input label="Prestation" value={description} onChange={e => setDescription(e.target.value)} placeholder="Site web, logo..." />
          <div className="grid grid-cols-2 gap-3">
            <Input label="Montant total" type="number" value={totalAmount} onChange={e => setTotalAmount(e.target.value)} suffix="€" />
            <Input label="Acompte reçu" type="number" value={depositReceived} onChange={e => setDepositReceived(e.target.value)} suffix="€" />
          </div>
          {assets.length > 0 && parseFloat(depositReceived || "0") > parseFloat(editing?.depositReceived || "0") && (
            <Select label="Acompte crédité sur" value={depositSourceId} onChange={e => setDepositSourceId(e.target.value)}
              options={[{ value: "", label: "— Ne pas créditer —" }, ...assets.map(a => ({ value: a.id, label: `${a.name} (${formatCurrency(parseFloat(a.amount))})` }))]} />
          )}
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
