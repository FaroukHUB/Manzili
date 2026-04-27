import { cn } from "@/lib/utils"
import { forwardRef } from "react"

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string
  error?: string
  suffix?: string
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ label, error, suffix, className, ...props }, ref) => {
    return (
      <div className="flex flex-col gap-1.5">
        {label && (
          <label className="text-xs font-medium" style={{ color: "var(--text-secondary)" }}>
            {label}
          </label>
        )}
        <div className="relative">
          <input
            ref={ref}
            className={cn(
              "w-full rounded-xl px-3 py-2.5 text-sm outline-none border transition-colors",
              suffix && "pr-10",
              className
            )}
            style={{
              background: "var(--bg-input)",
              color: "var(--text-primary)",
              borderColor: error ? "var(--color-debt)" : "var(--border-color)",
            }}
            onFocus={(e) => {
              e.target.style.borderColor = "var(--color-accent)"
            }}
            onBlur={(e) => {
              e.target.style.borderColor = error ? "var(--color-debt)" : "var(--border-color)"
            }}
            {...props}
          />
          {suffix && (
            <span
              className="absolute right-3 top-1/2 -translate-y-1/2 text-sm"
              style={{ color: "var(--text-secondary)" }}
            >
              {suffix}
            </span>
          )}
        </div>
        {error && (
          <p className="text-xs" style={{ color: "var(--color-debt)" }}>
            {error}
          </p>
        )}
      </div>
    )
  }
)
Input.displayName = "Input"

interface SelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  label?: string
  options: { value: string; label: string }[]
}

export function Select({ label, options, className, ...props }: SelectProps) {
  return (
    <div className="flex flex-col gap-1.5">
      {label && (
        <label className="text-xs font-medium" style={{ color: "var(--text-secondary)" }}>
          {label}
        </label>
      )}
      <select
        className={cn("w-full rounded-xl px-3 py-2.5 text-sm outline-none border transition-colors appearance-none", className)}
        style={{
          background: "var(--bg-input)",
          color: "var(--text-primary)",
          borderColor: "var(--border-color)",
        }}
        onFocus={(e) => { e.target.style.borderColor = "var(--color-accent)" }}
        onBlur={(e) => { e.target.style.borderColor = "var(--border-color)" }}
        {...props}
      >
        {options.map((opt) => (
          <option key={opt.value} value={opt.value} style={{ background: "var(--bg-card)" }}>
            {opt.label}
          </option>
        ))}
      </select>
    </div>
  )
}

interface TextareaProps extends React.TextareaHTMLAttributes<HTMLTextAreaElement> {
  label?: string
}

export function Textarea({ label, className, ...props }: TextareaProps) {
  return (
    <div className="flex flex-col gap-1.5">
      {label && (
        <label className="text-xs font-medium" style={{ color: "var(--text-secondary)" }}>
          {label}
        </label>
      )}
      <textarea
        rows={3}
        className={cn("w-full rounded-xl px-3 py-2.5 text-sm outline-none border transition-colors resize-none", className)}
        style={{
          background: "var(--bg-input)",
          color: "var(--text-primary)",
          borderColor: "var(--border-color)",
        }}
        onFocus={(e) => { e.target.style.borderColor = "var(--color-accent)" }}
        onBlur={(e) => { e.target.style.borderColor = "var(--border-color)" }}
        {...props}
      />
    </div>
  )
}
