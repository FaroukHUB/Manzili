"use client"

import { useState, useEffect } from "react"
import { Plus, Trash2, CreditCard } from "lucide-react"
import { Card } from "@/components/ui/card"
import { Modal } from "@/components/ui/modal"
import { Input, Textarea, Select } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { ProgressBar } from "@/components/ui/progress-bar"
import { formatCurrency, formatDateFR } from "@/lib/utils"
import type { Debt, Asset } from "@/lib/db/schema"

const PAYMENT_METHODS = [
  { value: "virement bancaire", label: "Virement bancaire" },
  { value: "PayPal", label: "PayPal" },
  { value: "Lydia", label: "Lydia" },
  { value: "Vinted", label: "Vinted" },
  { value: "espèces", label: "Espèces" },
  { value: "chèque", label: "Chèque" },
  { value: "autre", label: "Autre" },
]

function WhatsAppIcon() {
  return (
    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
  )
}

export default function DebtsPage() {
  const [debts, setDebts] = useState<Debt[]>([])
  const [assets, setAssets] = useState<Asset[]>([])
  const [loading, setLoading] = useState(true)
  const [modalOpen, setModalOpen] = useState(false)
  const [payModalOpen, setPayModalOpen] = useState(false)
  const [editing, setEditing] = useState<Debt | null>(null)
  const [paying, setPaying] = useState<Debt | null>(null)

  const [name, setName] = useState("")
  const [totalAmount, setTotalAmount] = useState("")
  const [paidAmount, setPaidAmount] = useState("0")
  const [priority, setPriority] = useState("1")
  const [dueDate, setDueDate] = useState("")
  const [note, setNote] = useState("")
  const [whatsappNumber, setWhatsappNumber] = useState("")
  const [saving, setSaving] = useState(false)

  const [payAmount, setPayAmount] = useState("")
  const [payDate, setPayDate] = useState(new Date().toISOString().split("T")[0])
  const [payNote, setPayNote] = useState("")
  const [payMethod, setPayMethod] = useState("virement bancaire")
  const [paySourceId, setPaySourceId] = useState("")
  const [paymentDone, setPaymentDone] = useState(false)
  const [lastPayAmount, setLastPayAmount] = useState("")

  async function fetchDebts() {
    setLoading(true)
    const [debtRes, assetRes] = await Promise.all([fetch("/api/debts"), fetch("/api/assets")])
    const debtData = await debtRes.json()
    const assetData = await assetRes.json()
    setDebts(Array.isArray(debtData) ? debtData.filter((d: Debt) => d.isActive) : [])
    setAssets(Array.isArray(assetData) ? assetData : [])
    setLoading(false)
  }

  useEffect(() => { fetchDebts() }, [])

  function openAdd() {
    setEditing(null)
    setName(""); setTotalAmount(""); setPaidAmount("0"); setPriority("1"); setDueDate(""); setNote(""); setWhatsappNumber("")
    setModalOpen(true)
  }

  function openEdit(debt: Debt) {
    setEditing(debt)
    setName(debt.name); setTotalAmount(debt.totalAmount); setPaidAmount(debt.paidAmount)
    setPriority(String(debt.priority)); setDueDate(debt.dueDate || ""); setNote(debt.note || "")
    setWhatsappNumber(debt.whatsappNumber || "")
    setModalOpen(true)
  }

  function openPay(debt: Debt) {
    setPaying(debt)
    setPayAmount(""); setPayDate(new Date().toISOString().split("T")[0])
    setPayNote(""); setPayMethod("virement bancaire"); setPaySourceId(assets[0]?.id || ""); setPaymentDone(false); setLastPayAmount("")
    setPayModalOpen(true)
  }

  async function handleSave() {
    setSaving(true)
    const payload = { name, totalAmount, paidAmount, priority, dueDate: dueDate || null, note, whatsappNumber: whatsappNumber || null }
    const url = editing ? `/api/debts/${editing.id}` : "/api/debts"
    const method = editing ? "PUT" : "POST"
    await fetch(url, { method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) })
    setSaving(false); setModalOpen(false); fetchDebts()
  }

  async function handleDelete(id: string) {
    if (!confirm("Supprimer cette dette ?")) return
    await fetch(`/api/debts/${id}`, { method: "DELETE" })
    fetchDebts()
  }

  async function handlePay() {
    if (!paying || !payAmount) return
    setSaving(true)
    await fetch(`/api/debts/${paying.id}/pay`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ amount: payAmount, paymentDate: payDate, note: payNote }),
    })
    if (paySourceId) {
      await fetch(`/api/assets/${paySourceId}/adjust`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ delta: -parseFloat(payAmount) }),
      })
    }
    setSaving(false)
    setLastPayAmount(payAmount)
    setPaymentDone(true)
    fetchDebts()
  }

  function sendWhatsApp() {
    if (!paying) return
    const num = paying.whatsappNumber?.replace(/\s/g, "") || ""
    const paid = parseFloat(lastPayAmount)
    const newPaid = parseFloat(paying.paidAmount) + paid
    const remaining = parseFloat(paying.totalAmount) - newPaid
    const isFullyPaid = remaining <= 0

    const msg = isFullyPaid
      ? `Salam ${paying.name} 👋\n\n🎉 Bonne nouvelle ! Je viens de solder ma dette via ${payMethod}. Le montant de ${formatCurrency(paid)} a été envoyé — c'est maintenant totalement remboursé !\n\nMerci infiniment pour ta patience et ta confiance 🙏✅`
      : `Salam ${paying.name} 👋\n\nJuste pour te prévenir que j'ai effectué un ${payMethod} de ${formatCurrency(paid)} aujourd'hui.\n\nIl me reste ${formatCurrency(remaining)} à te rembourser. Je fais de mon mieux pour rembourser au plus vite, merci pour ta patience 🙏`

    const url = num
      ? `https://wa.me/${num}?text=${encodeURIComponent(msg)}`
      : `https://wa.me/?text=${encodeURIComponent(msg)}`
    window.open(url, "_blank")
  }

  const totalDebt = debts.reduce((s, d) => s + parseFloat(d.totalAmount), 0)
  const totalPaid = debts.reduce((s, d) => s + parseFloat(d.paidAmount), 0)
  const totalRemaining = totalDebt - totalPaid
  const globalProgress = totalDebt > 0 ? (totalPaid / totalDebt) * 100 : 0

  return (
    <div className="p-4 md:p-6 max-w-7xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>Mes dettes</h1>
        <Button onClick={openAdd} size="sm"><Plus size={16} /> Ajouter</Button>
      </div>

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

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? "Modifier la dette" : "Nouvelle dette"}>
        <div className="flex flex-col gap-3">
          <Input label="Nom (personne ou organisme)" value={name} onChange={(e) => setName(e.target.value)} placeholder="Ex: Karim, Banque, ..." />
          <Input label="N° WhatsApp (optionnel)" value={whatsappNumber} onChange={(e) => setWhatsappNumber(e.target.value)} placeholder="+33612345678" />
          <div className="grid grid-cols-2 gap-3">
            <Input label="Montant total" type="number" min="0" value={totalAmount} onChange={(e) => setTotalAmount(e.target.value)} suffix="€" />
            <Input label="Déjà payé" type="number" min="0" value={paidAmount} onChange={(e) => setPaidAmount(e.target.value)} suffix="€" />
          </div>
          <Select label="Priorité" value={priority} onChange={(e) => setPriority(e.target.value)}
            options={[1,2,3,4,5].map(n => ({ value: String(n), label: `Priorité ${n}` }))} />
          <Input label="Date limite (optionnel)" type="date" value={dueDate} onChange={(e) => setDueDate(e.target.value)} />
          <Textarea label="Note (optionnel)" value={note} onChange={(e) => setNote(e.target.value)} />
          <Button onClick={handleSave} loading={saving}>{editing ? "Mettre à jour" : "Ajouter"}</Button>
        </div>
      </Modal>

      <Modal open={payModalOpen} onClose={() => { setPayModalOpen(false); setPaymentDone(false) }} title={`Paiement — ${paying?.name}`}>
        <div className="flex flex-col gap-3">
          {paying && (
            <div className="rounded-xl p-3" style={{ background: "var(--color-gain-bg)" }}>
              <p className="text-xs" style={{ color: "var(--color-gain)" }}>
                Reste à payer : {formatCurrency(parseFloat(paying.totalAmount) - parseFloat(paying.paidAmount))}
              </p>
            </div>
          )}
          {!paymentDone ? (
            <>
              <Input label="Montant payé" type="number" min="0" value={payAmount} onChange={(e) => setPayAmount(e.target.value)} suffix="€" autoFocus />
              <Select label="Mode de paiement" value={payMethod} onChange={(e) => setPayMethod(e.target.value)} options={PAYMENT_METHODS} />
              {assets.length > 0 && (
                <Select label="Déduire de" value={paySourceId} onChange={(e) => setPaySourceId(e.target.value)}
                  options={[{ value: "", label: "— Ne pas déduire —" }, ...assets.map(a => ({ value: a.id, label: `${a.name} (${formatCurrency(parseFloat(a.amount))})` }))]} />
              )}
              <Input label="Date du paiement" type="date" value={payDate} onChange={(e) => setPayDate(e.target.value)} />
              <Textarea label="Note (optionnel)" value={payNote} onChange={(e) => setPayNote(e.target.value)} />
              <Button onClick={handlePay} loading={saving}>Confirmer le paiement</Button>
            </>
          ) : (
            <div className="flex flex-col gap-3">
              <div className="rounded-xl p-3 text-center" style={{ background: "var(--color-gain-bg)" }}>
                <p className="font-semibold" style={{ color: "var(--color-gain)" }}>✓ Paiement de {formatCurrency(parseFloat(lastPayAmount))} enregistré !</p>
              </div>
              <button
                onClick={sendWhatsApp}
                className="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-medium text-sm"
                style={{ background: "#25d366", color: "white" }}
              >
                <WhatsAppIcon /> Prévenir {paying?.name} sur WhatsApp
              </button>
              <Button variant="ghost" onClick={() => { setPayModalOpen(false); setPaymentDone(false) }}>Fermer</Button>
            </div>
          )}
        </div>
      </Modal>
    </div>
  )
}
