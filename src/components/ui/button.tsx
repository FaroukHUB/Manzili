import { cn } from "@/lib/utils"

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: "primary" | "secondary" | "danger" | "ghost" | "success"
  size?: "sm" | "md" | "lg"
  loading?: boolean
}

const variants = {
  primary: { background: "var(--color-accent)", color: "white" },
  secondary: { background: "var(--bg-hover)", color: "var(--text-primary)", border: "1px solid var(--border-color)" },
  danger: { background: "var(--color-debt-bg)", color: "var(--color-debt)", border: "1px solid var(--color-debt)" },
  ghost: { background: "transparent", color: "var(--text-secondary)" },
  success: { background: "var(--color-gain-bg)", color: "var(--color-gain)", border: "1px solid var(--color-gain)" },
}

const sizes = {
  sm: "px-3 py-1.5 text-xs rounded-lg",
  md: "px-4 py-2.5 text-sm rounded-xl",
  lg: "px-5 py-3 text-sm rounded-xl font-semibold",
}

export function Button({
  variant = "primary",
  size = "md",
  loading,
  className,
  children,
  disabled,
  ...props
}: ButtonProps) {
  return (
    <button
      className={cn(
        "font-medium transition-opacity disabled:opacity-50 inline-flex items-center justify-center gap-2",
        sizes[size],
        className
      )}
      style={variants[variant]}
      disabled={disabled || loading}
      {...props}
    >
      {loading ? "Chargement..." : children}
    </button>
  )
}
