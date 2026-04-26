import { cn } from "@/lib/utils"

interface ProgressBarProps {
  value: number // 0-100
  color?: "green" | "red" | "blue" | "amber"
  className?: string
  showLabel?: boolean
}

const colorMap = {
  green: "var(--color-gain)",
  red: "var(--color-debt)",
  blue: "var(--color-accent)",
  amber: "var(--color-warning)",
}

export function ProgressBar({ value, color = "green", className, showLabel }: ProgressBarProps) {
  const pct = Math.min(100, Math.max(0, value))
  const bg = colorMap[color]

  return (
    <div className={cn("relative", className)}>
      <div
        className="w-full rounded-full overflow-hidden"
        style={{ height: 6, background: "var(--border-color)" }}
      >
        <div
          className="h-full rounded-full transition-all duration-500"
          style={{ width: `${pct}%`, background: bg }}
        />
      </div>
      {showLabel && (
        <span className="text-xs mt-1 block" style={{ color: bg }}>
          {pct.toFixed(0)}%
        </span>
      )}
    </div>
  )
}
