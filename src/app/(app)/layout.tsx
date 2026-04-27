"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import { LayoutDashboard, Calendar, TrendingDown, HandCoins, Wallet, Settings } from "lucide-react"
import { cn } from "@/lib/utils"

const navItems = [
  { href: "/dashboard", icon: LayoutDashboard, label: "Dashboard" },
  { href: "/calendar", icon: Calendar, label: "Calendrier" },
  { href: "/debts", icon: TrendingDown, label: "Dettes" },
  { href: "/receivables", icon: HandCoins, label: "On me doit" },
  { href: "/expenses", icon: Wallet, label: "Dépenses" },
  { href: "/settings", icon: Settings, label: "Paramètres" },
]

export default function AppLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname()

  return (
    <div className="min-h-dvh flex flex-col md:flex-row">
      {/* Sidebar desktop */}
      <aside
        className="hidden md:flex flex-col w-56 shrink-0 border-r fixed h-full"
        style={{ background: "var(--bg-card)", borderColor: "var(--border-color)" }}
      >
        <div className="px-5 py-6 border-b" style={{ borderColor: "var(--border-color)" }}>
          <div className="flex items-center gap-2">
            <span className="text-xl">💰</span>
            <span className="font-bold text-sm" style={{ color: "var(--text-primary)" }}>
              Manzili
            </span>
          </div>
        </div>
        <nav className="flex-1 px-3 py-4 flex flex-col gap-1">
          {navItems.map(({ href, icon: Icon, label }) => {
            const active = pathname === href
            return (
              <Link
                key={href}
                href={href}
                className={cn(
                  "flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors",
                  active
                    ? "text-white"
                    : "hover:bg-[var(--bg-hover)]"
                )}
                style={
                  active
                    ? { background: "var(--color-accent)", color: "white" }
                    : { color: "var(--text-secondary)" }
                }
              >
                <Icon size={18} />
                {label}
              </Link>
            )
          })}
        </nav>
      </aside>

      {/* Main content */}
      <main className="flex-1 md:ml-56 pb-20 md:pb-0 min-h-dvh">
        {children}
      </main>

      {/* Bottom nav mobile */}
      <nav
        className="md:hidden fixed bottom-0 left-0 right-0 border-t z-40"
        style={{ background: "var(--bg-card)", borderColor: "var(--border-color)" }}
      >
        <div className="flex items-center justify-around px-2 py-2">
          {navItems.slice(0, 5).map(({ href, icon: Icon, label }) => {
            const active = pathname === href
            return (
              <Link
                key={href}
                href={href}
                className="flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-colors"
                style={{ color: active ? "var(--color-accent)" : "var(--text-secondary)" }}
              >
                <Icon size={20} />
                <span className="text-[10px] font-medium">{label}</span>
              </Link>
            )
          })}
        </div>
      </nav>
    </div>
  )
}
