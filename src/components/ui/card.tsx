import { cn } from "@/lib/utils"

interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  children: React.ReactNode
  className?: string
  onClick?: () => void
}

export function Card({ children, className, onClick, style, ...rest }: CardProps) {
  return (
    <div
      className={cn(
        "rounded-2xl border p-4",
        onClick && "cursor-pointer transition-colors hover:border-[var(--border-active)]",
        className
      )}
      style={{ background: "var(--bg-card)", borderColor: "var(--border-color)", ...style }}
      onClick={onClick}
      {...rest}
    >
      {children}
    </div>
  )
}
