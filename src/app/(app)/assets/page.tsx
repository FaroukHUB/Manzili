"use client"

import { useState, useEffect } from "react"
import { Plus, Trash2 } from "lucide-react"
import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Modal } from "@/components/ui/modal"
import { Input, Textarea } from "@/components/ui/input"
import { formatCurrency } from "@/lib/utils"
import type { Asset } from "@/lib/db/schema"

export default function AssetsPage() {
  const [assets, setAssets] = useState<Asset[]>([])
  const [loading, setLoading] = useState(true)
  const [modalOpen, setModalOpen] = useState(false)
  const [editing, setEditing] = useState<Asset | null>(null)
  const [name, setName] = useState("")
  const [amount, setAmount] = useState("")
  const [note, setNote] = useState("")
  const [saving, setSaving] = useState(false)

  async function fetchData() {
    try {
      const res = await fetch("/api/assets")
      if (res.ok) setAssets(await res.json())
    } catch (e) {
      console.error(e)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { fetchData() }, [])

  function openNew() {
    setEditing(null); setName(""); setAmount(""); setNote("")
    setModalOpen(true)
  }

  function openEdit(a: Asset) {
    setEditing(a); setName(a.name); setAmount(a.amount); setNote(a.note || "")
    setModalOpen(true)
  }

  async function handleSave() {
    if (!name || !amount) return
    setSaving(true)
    const payload = { name, amount, note }
    const url = editing ? `/api/assets/${editing.id}` : "/api/assets"
    const method = editing ? "PUT" : "POST"
    const res = await fetch(url, { method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) })
    setSaving(false)
    if (res.ok) { await fetchData(); setModalOpen(false) }
  }

  async function handleDelete(id: string) {
    await fetch(`/api/assets/${id}`, { method: "DELETE" })
    await fetchData()
  }

  const total = assets.reduce((s, a) => s + parseFloat(a.amount), 0)

  return (
    <div className="p-4 md:p-6 max-w-7xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold" style={{ color: "var(--text-primary)" }}>Actif</h1>
          <p className="text-sm mt-0.5" style={{ color: "var(--text-secondary)" }}>Ce que tu as en ta possession</p>
        </div>
        <Button onClick={openNew}><Plus size={16} className="mr-1" />Ajouter</Button>
      </div>

      {assets.length > 0 && (
        <Card className="mb-5">
          <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Total actif disponible</p>
          <p className="text-3xl font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(total)}</p>
        </Card>
      )}

      {loading ? (
        <p className="text-sm text-center py-8" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
      ) : assets.length === 0 ? (
        <Card>
          <p className="text-sm text-center py-4" style={{ color: "var(--text-secondary)" }}>Aucun actif enregistré</p>
        </Card>
      ) : (
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
          {assets.map(a => (
            <Card key={a.id} className="flex flex-col gap-2 cursor-pointer" onClick={() => openEdit(a)}>
              <div className="flex items-center justify-between">
                <span className="text-[10px] font-medium px-1.5 py-0.5 rounded-full border" style={{ color: "var(--color-gain)", borderColor: "var(--color-gain)" }}>Actif</span>
                <button onClick={e => { e.stopPropagation(); handleDelete(a.id) }} className="p-1 rounded transition-colors hover:bg-red-500/10">
                  <Trash2 size={11} style={{ color: "var(--color-debt)" }} />
                </button>
              </div>
              <p className="font-semibold text-sm leading-tight" style={{ color: "var(--text-primary)" }}>{a.name}</p>
              {a.note && <p className="text-[10px]" style={{ color: "var(--text-secondary)" }}>{a.note}</p>}
              <p className="text-xl font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(parseFloat(a.amount))}</p>
            </Card>
          ))}
        </div>
      )}

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? "Modifier" : "Nouvel actif"}>
        <div className="flex flex-col gap-4">
          <Input label="Nom" value={name} onChange={e => setName(e.target.value)} placeholder="Ex: Compte courant, Espèces..." />
          <Input label="Montant" type="number" value={amount} onChange={e => setAmount(e.target.value)} suffix="€" />
          <Textarea label="Note (optionnel)" value={note} onChange={e => setNote(e.target.value)} />
          <Button onClick={handleSave} loading={saving}>{editing ? "Mettre à jour" : "Enregistrer"}</Button>
        </div>
      </Modal>
    </div>
  )
}
