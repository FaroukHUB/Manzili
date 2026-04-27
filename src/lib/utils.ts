import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatCurrency(amount: number | string, currency = "€"): string {
  const num = typeof amount === "string" ? parseFloat(amount) : amount
  if (isNaN(num)) return `0${currency}`
  return `${num.toFixed(2).replace(".", ",")}${currency}`
}

export function formatHours(hours: number | string): string {
  const h = typeof hours === "string" ? parseFloat(hours) : hours
  if (isNaN(h)) return "0h00"
  const totalMinutes = Math.round(h * 60)
  const hrs = Math.floor(totalMinutes / 60)
  const mins = totalMinutes % 60
  return `${hrs}h${mins.toString().padStart(2, "0")}`
}

export function calculateHoursWorked(
  startTime: string,
  endTime: string,
  breakMinutes: number
): number {
  const [startH, startM] = startTime.split(":").map(Number)
  const [endH, endM] = endTime.split(":").map(Number)
  const totalMinutes = (endH * 60 + endM) - (startH * 60 + startM) - breakMinutes
  return Math.max(0, totalMinutes / 60)
}

export function calculateEarnings(hoursWorked: number, hourlyRate: number): number {
  return Math.round(hoursWorked * hourlyRate * 100) / 100
}

export function getMotivationMessage(params: {
  totalDebt: number
  paidDebt: number
  dailyEarnings: number
  daysToPayoff?: number
  topDebtName?: string
  topDebtRemaining?: number
}): string {
  const { totalDebt, paidDebt, dailyEarnings, daysToPayoff, topDebtName, topDebtRemaining } = params
  const progressPercent = totalDebt > 0 ? (paidDebt / totalDebt) * 100 : 0

  if (progressPercent >= 100) return "🎉 Toutes tes dettes sont remboursées. Félicitations !"
  if (progressPercent >= 75) return `💪 Tu es à ${progressPercent.toFixed(0)}% ! La ligne d'arrivée est proche. Ne lâche pas.`
  if (progressPercent >= 50) return `🔥 Mi-chemin atteint ! ${progressPercent.toFixed(0)}% remboursé. Continue comme ça.`
  if (progressPercent >= 25) return `📈 Tu as passé le quart du chemin (${progressPercent.toFixed(0)}%). Chaque jour compte.`

  if (topDebtRemaining && dailyEarnings > 0) {
    const daysLeft = Math.ceil(topDebtRemaining / dailyEarnings)
    if (daysLeft <= 7) return `⚡ Encore ${daysLeft} jour${daysLeft > 1 ? "s" : ""} comme aujourd'hui et ${topDebtName} est remboursée.`
    if (daysLeft <= 30) return `🎯 ${topDebtName} remboursée en ${daysLeft} jours si tu maintiens ce rythme.`
  }

  if (daysToPayoff) {
    return `📅 À ce rythme, tes dettes seront remboursées dans ${daysToPayoff} jours. Un client web = plusieurs jours gagnés.`
  }

  return "💡 Un client web signé peut valoir presque 2 jours de pizzeria. Pense-y."
}

export function getDaysUntilDebtFree(
  totalRemainingDebt: number,
  averageDailyContribution: number
): number | null {
  if (averageDailyContribution <= 0 || totalRemainingDebt <= 0) return null
  return Math.ceil(totalRemainingDebt / averageDailyContribution)
}

export function formatDateFR(date: string | Date): string {
  const d = typeof date === "string" ? new Date(date) : date
  return d.toLocaleDateString("fr-FR", { day: "2-digit", month: "2-digit", year: "numeric" })
}

export function getMonthLabel(year: number, month: number): string {
  return new Date(year, month, 1).toLocaleDateString("fr-FR", { month: "long", year: "numeric" })
}

export function toISODate(date: Date): string {
  return date.toISOString().split("T")[0]
}

export function getDaysInMonth(year: number, month: number): Date[] {
  const days: Date[] = []
  const date = new Date(year, month, 1)
  while (date.getMonth() === month) {
    days.push(new Date(date))
    date.setDate(date.getDate() + 1)
  }
  return days
}

export function getFirstDayOfWeek(year: number, month: number): number {
  // Returns 0=Monday ... 6=Sunday (French calendar)
  const day = new Date(year, month, 1).getDay()
  return (day + 6) % 7
}

const WEEK_DAYS_FR = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"]
export { WEEK_DAYS_FR }
