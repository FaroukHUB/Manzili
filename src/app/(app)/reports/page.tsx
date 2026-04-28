"use client"

import { useState, useEffect } from "react"
import { ChevronLeft, ChevronRight, FileText, TrendingUp, Briefcase, Clock } from "lucide-react"
import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { formatCurrency, formatHours } from "@/lib/utils"

const MONTHS_FR = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"]

interface ReportRow {
  month: string
  salary: number
  hours: number
  days: number
  ca: number
  contractsCount: number
  total: number
}

interface ReportData {
  year: number
  rows: ReportRow[]
  totals: { salary: number; hours: number; days: number; ca: number; total: number }
}

export default function ReportsPage() {
  const [year, setYear] = useState(new Date().getFullYear())
  const [data, setData] = useState<ReportData | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    setLoading(true)
    fetch(`/api/reports?year=${year}`)
      .then(r => r.json())
      .then(d => { setData(d); setLoading(false) })
      .catch(() => setLoading(false))
  }, [year])

  async function exportPDF() {
    if (!data) return
    const [{ default: jsPDF }, { default: autoTable }] = await Promise.all([
      import("jspdf"),
      import("jspdf-autotable"),
    ])
    const doc = new jsPDF()

    doc.setFontSize(18)
    doc.setTextColor(59, 37, 16)
    doc.text(`Rapport financier ${data.year}`, 14, 20)

    doc.setFontSize(10)
    doc.setTextColor(122, 92, 63)
    doc.text(`Salaire total : ${formatCurrency(data.totals.salary)}`, 14, 32)
    doc.text(`CA Contrats : ${formatCurrency(data.totals.ca)}`, 14, 39)
    doc.text(`Revenus totaux : ${formatCurrency(data.totals.total)}`, 14, 46)
    doc.text(`Heures travaillées : ${formatHours(data.totals.hours)}`, 14, 53)

    autoTable(doc, {
      startY: 62,
      head: [["Mois", "Salaire", "Heures", "Jours", "CA Contrats", "Total"]],
      body: data.rows.map((r, i) => [
        MONTHS_FR[i],
        r.salary > 0 ? formatCurrency(r.salary) : "—",
        r.hours > 0 ? formatHours(r.hours) : "—",
        r.days > 0 ? String(r.days) : "—",
        r.ca > 0 ? formatCurrency(r.ca) : "—",
        r.total > 0 ? formatCurrency(r.total) : "—",
      ]),
      foot: [[
        "TOTAL",
        formatCurrency(data.totals.salary),
        formatHours(data.totals.hours),
        String(data.totals.days),
        formatCurrency(data.totals.ca),
        formatCurrency(data.totals.total),
      ]],
      headStyles: { fillColor: [139, 94, 60] },
      alternateRowStyles: { fillColor: [253, 248, 242] },
      footStyles: { fillColor: [237, 228, 216], textColor: [59, 37, 16], fontStyle: "bold" },
    })

    doc.save(`rapport-${data.year}.pdf`)
  }

  const maxTotal = data ? Math.max(...data.rows.map(r => r.total), 1) : 1

  return (
    <div className="p-4 md:p-6 max-w-4xl mx-auto">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>Rapports</h1>
        <Button size="sm" onClick={exportPDF} variant="ghost">
          <FileText size={14} className="mr-1" /> Export PDF
        </Button>
      </div>

      {/* Year navigation */}
      <div className="flex items-center justify-center gap-4 mb-6">
        <button onClick={() => setYear(y => y - 1)} className="p-2 rounded-xl border" style={{ borderColor: "var(--border-color)", color: "var(--text-secondary)" }}>
          <ChevronLeft size={18} />
        </button>
        <span className="text-lg font-bold w-16 text-center" style={{ color: "var(--text-primary)" }}>{year}</span>
        <button onClick={() => setYear(y => y + 1)} className="p-2 rounded-xl border" style={{ borderColor: "var(--border-color)", color: "var(--text-secondary)" }}>
          <ChevronRight size={18} />
        </button>
      </div>

      {loading ? (
        <p className="text-sm text-center py-8" style={{ color: "var(--text-secondary)" }}>Chargement...</p>
      ) : !data ? null : (
        <>
          {/* Summary cards */}
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <Card>
              <div className="flex items-center gap-1.5 mb-1">
                <Clock size={12} style={{ color: "var(--text-secondary)" }} />
                <p className="text-xs" style={{ color: "var(--text-secondary)" }}>Salaire {year}</p>
              </div>
              <p className="text-lg font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(data.totals.salary)}</p>
              <p className="text-[10px] mt-0.5" style={{ color: "var(--text-secondary)" }}>{formatHours(data.totals.hours)} · {data.totals.days}j</p>
            </Card>
            <Card>
              <div className="flex items-center gap-1.5 mb-1">
                <Briefcase size={12} style={{ color: "var(--text-secondary)" }} />
                <p className="text-xs" style={{ color: "var(--text-secondary)" }}>CA Contrats</p>
              </div>
              <p className="text-lg font-bold" style={{ color: "var(--color-accent)" }}>{formatCurrency(data.totals.ca)}</p>
              <p className="text-[10px] mt-0.5" style={{ color: "var(--text-secondary)" }}>contrats livrés</p>
            </Card>
            <Card>
              <div className="flex items-center gap-1.5 mb-1">
                <TrendingUp size={12} style={{ color: "var(--text-secondary)" }} />
                <p className="text-xs" style={{ color: "var(--text-secondary)" }}>Total {year}</p>
              </div>
              <p className="text-lg font-bold" style={{ color: "var(--text-primary)" }}>{formatCurrency(data.totals.total)}</p>
            </Card>
            <Card>
              <p className="text-xs mb-1" style={{ color: "var(--text-secondary)" }}>Moyenne/mois</p>
              <p className="text-lg font-bold" style={{ color: "var(--text-primary)" }}>
                {formatCurrency(data.totals.total / 12)}
              </p>
              <p className="text-[10px] mt-0.5" style={{ color: "var(--text-secondary)" }}>tous revenus</p>
            </Card>
          </div>

          {/* Bar chart */}
          <Card className="mb-6">
            <p className="text-xs font-semibold uppercase tracking-wide mb-4" style={{ color: "var(--text-secondary)" }}>
              Revenus par mois
            </p>
            <div className="flex items-end gap-1.5 h-32">
              {data.rows.map((r, i) => {
                const salaryH = data.totals.total > 0 ? (r.salary / maxTotal) * 100 : 0
                const caH = data.totals.total > 0 ? (r.ca / maxTotal) * 100 : 0
                const isEmpty = r.total === 0
                return (
                  <div key={r.month} className="flex-1 flex flex-col items-center gap-1">
                    <div className="w-full flex flex-col justify-end gap-0.5" style={{ height: "100px" }}>
                      {!isEmpty ? (
                        <>
                          {r.ca > 0 && (
                            <div className="w-full rounded-t-sm" style={{ height: `${caH}%`, background: "var(--color-accent)", minHeight: 3 }} />
                          )}
                          {r.salary > 0 && (
                            <div className={`w-full ${r.ca === 0 ? "rounded-t-sm" : ""} rounded-b-sm`}
                              style={{ height: `${salaryH}%`, background: "var(--color-gain)", minHeight: 3 }} />
                          )}
                        </>
                      ) : (
                        <div className="w-full rounded-sm" style={{ height: "2px", background: "var(--border-color)" }} />
                      )}
                    </div>
                    <span className="text-[9px]" style={{ color: "var(--text-secondary)" }}>{MONTHS_FR[i]}</span>
                  </div>
                )
              })}
            </div>
            <div className="flex gap-4 mt-3">
              <div className="flex items-center gap-1.5">
                <div className="w-3 h-3 rounded-sm" style={{ background: "var(--color-gain)" }} />
                <span className="text-xs" style={{ color: "var(--text-secondary)" }}>Salaire</span>
              </div>
              <div className="flex items-center gap-1.5">
                <div className="w-3 h-3 rounded-sm" style={{ background: "var(--color-accent)" }} />
                <span className="text-xs" style={{ color: "var(--text-secondary)" }}>CA Contrats</span>
              </div>
            </div>
          </Card>

          {/* Monthly table */}
          <Card>
            <p className="text-xs font-semibold uppercase tracking-wide mb-4" style={{ color: "var(--text-secondary)" }}>
              Détail mensuel
            </p>
            <div className="overflow-x-auto">
              <table className="w-full text-xs">
                <thead>
                  <tr style={{ color: "var(--text-secondary)" }}>
                    <th className="text-left pb-2 font-medium">Mois</th>
                    <th className="text-right pb-2 font-medium">Salaire</th>
                    <th className="text-right pb-2 font-medium">Heures</th>
                    <th className="text-right pb-2 font-medium">CA</th>
                    <th className="text-right pb-2 font-medium font-bold">Total</th>
                  </tr>
                </thead>
                <tbody>
                  {data.rows.map((r, i) => {
                    const isCurrent = r.month === `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, "0")}`
                    return (
                      <tr key={r.month}
                        className="border-t"
                        style={{
                          borderColor: "var(--border-color)",
                          background: isCurrent ? "var(--color-accent-bg)" : "transparent",
                        }}
                      >
                        <td className="py-2 font-medium" style={{ color: isCurrent ? "var(--color-accent)" : "var(--text-primary)" }}>
                          {MONTHS_FR[i]}{isCurrent && " ●"}
                        </td>
                        <td className="py-2 text-right" style={{ color: r.salary > 0 ? "var(--color-gain)" : "var(--text-muted)" }}>
                          {r.salary > 0 ? formatCurrency(r.salary) : "—"}
                        </td>
                        <td className="py-2 text-right" style={{ color: "var(--text-secondary)" }}>
                          {r.hours > 0 ? formatHours(r.hours) : "—"}
                        </td>
                        <td className="py-2 text-right" style={{ color: r.ca > 0 ? "var(--color-accent)" : "var(--text-muted)" }}>
                          {r.ca > 0 ? formatCurrency(r.ca) : "—"}
                        </td>
                        <td className="py-2 text-right font-bold" style={{ color: r.total > 0 ? "var(--text-primary)" : "var(--text-muted)" }}>
                          {r.total > 0 ? formatCurrency(r.total) : "—"}
                        </td>
                      </tr>
                    )
                  })}
                </tbody>
                <tfoot>
                  <tr className="border-t-2" style={{ borderColor: "var(--border-active)" }}>
                    <td className="py-2 font-bold" style={{ color: "var(--text-primary)" }}>Total</td>
                    <td className="py-2 text-right font-bold" style={{ color: "var(--color-gain)" }}>{formatCurrency(data.totals.salary)}</td>
                    <td className="py-2 text-right font-bold" style={{ color: "var(--text-secondary)" }}>{formatHours(data.totals.hours)}</td>
                    <td className="py-2 text-right font-bold" style={{ color: "var(--color-accent)" }}>{formatCurrency(data.totals.ca)}</td>
                    <td className="py-2 text-right font-bold" style={{ color: "var(--text-primary)" }}>{formatCurrency(data.totals.total)}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </Card>
        </>
      )}
    </div>
  )
}
