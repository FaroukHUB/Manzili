import { NextResponse } from "next/server"
import { auth } from "@/lib/auth"
import Anthropic from "@anthropic-ai/sdk"

export async function POST(req: Request) {
  const session = await auth()
  if (!session) return NextResponse.json({ error: "Non autorisé" }, { status: 401 })

  if (!process.env.ANTHROPIC_API_KEY) {
    return NextResponse.json({ error: "Clé API Anthropic non configurée" }, { status: 500 })
  }

  const body = await req.json()
  const { assets, debts, receivables, contracts, monthlyEarnings, totalExpenses } = body

  const dispo = Math.max(0, monthlyEarnings - totalExpenses)

  const context = `Situation financière actuelle :

ACTIF DISPONIBLE : ${assets.total}€
${assets.items.map((a: { name: string; amount: string; note?: string }) => `  - ${a.name} : ${a.amount}€${a.note ? ` (${a.note})` : ""}`).join("\n")}

DETTES EN COURS :
Total restant : ${debts.remaining}€
${debts.items.map((d: { name: string; remaining: number; priority: number }) => `  - ${d.name} : reste ${d.remaining}€ (priorité ${d.priority})`).join("\n")}

ON ME DOIT (créances en attente) :
Total : ${receivables.total}€
${receivables.items.map((r: { name: string; remaining: number; reason?: string }) => `  - ${r.name} : ${r.remaining}€${r.reason ? ` — ${r.reason}` : ""}`).join("\n")}

CONTRATS SIGNÉS (revenus potentiels, non garantis) :
Total potentiel : ${contracts.total}€
${contracts.items.map((c: { clientName: string; remaining: number; description?: string }) => `  - ${c.clientName} : ${c.remaining}€ à encaisser${c.description ? ` (${c.description})` : ""}`).join("\n")}

REVENUS DU MOIS : ${monthlyEarnings}€
DÉPENSES FIXES : ${totalExpenses}€
DISPONIBLE APRÈS DÉPENSES : ${dispo}€`

  const client = new Anthropic({ apiKey: process.env.ANTHROPIC_API_KEY })

  const message = await client.messages.create({
    model: "claude-haiku-4-5-20251001",
    max_tokens: 500,
    system: "Tu es un coach financier personnel bienveillant et direct. Tu analyses la situation et donnes des conseils concrets et personnalisés, toujours avec les chiffres exacts. Tu notes clairement quand une action est possible maintenant vs. conditionnelle. Tu réponds en français, de façon concise.",
    messages: [
      {
        role: "user",
        content: `Analyse ma situation et donne-moi 2-3 conseils concrets. Dis-moi si je peux solder une dette avec mon actif disponible. Rappelle que les contrats sont potentiels (pas garantis).\n\n${context}`,
      },
    ],
  })

  const advice = message.content[0].type === "text" ? message.content[0].text : ""
  return NextResponse.json({ advice })
}
