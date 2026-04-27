"use client"

import { useState } from "react"
import { signIn } from "next-auth/react"
import { useRouter } from "next/navigation"

export default function LoginPage() {
  const [password, setPassword] = useState("")
  const [error, setError] = useState("")
  const [loading, setLoading] = useState(false)
  const router = useRouter()

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setError("")

    const result = await signIn("credentials", {
      password,
      redirect: false,
    })

    if (result?.error) {
      setError("Mot de passe incorrect.")
      setLoading(false)
    } else {
      router.push("/dashboard")
      router.refresh()
    }
  }

  return (
    <div className="min-h-dvh flex items-center justify-center px-4" style={{ background: "var(--bg-primary)" }}>
      <div className="w-full max-w-sm">
        <div className="text-center mb-8">
          <div className="text-4xl mb-3">💰</div>
          <h1 className="text-2xl font-bold" style={{ color: "var(--text-primary)" }}>Manzili</h1>
          <p className="text-sm mt-1" style={{ color: "var(--text-secondary)" }}>
            Ton tableau de bord personnel
          </p>
        </div>

        <div
          className="rounded-2xl p-6 border"
          style={{ background: "var(--bg-card)", borderColor: "var(--border-color)" }}
        >
          <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            <div>
              <label
                className="block text-sm font-medium mb-2"
                style={{ color: "var(--text-secondary)" }}
              >
                Mot de passe
              </label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="••••••••"
                autoFocus
                required
                className="w-full rounded-xl px-4 py-3 text-sm outline-none border transition-colors"
                style={{
                  background: "var(--bg-input)",
                  color: "var(--text-primary)",
                  borderColor: error ? "var(--color-debt)" : "var(--border-color)",
                }}
                onFocus={(e) => (e.target.style.borderColor = "var(--color-accent)")}
                onBlur={(e) =>
                  (e.target.style.borderColor = error
                    ? "var(--color-debt)"
                    : "var(--border-color)")
                }
              />
            </div>

            {error && (
              <p className="text-sm text-center" style={{ color: "var(--color-debt)" }}>
                {error}
              </p>
            )}

            <button
              type="submit"
              disabled={loading}
              className="w-full py-3 rounded-xl font-semibold text-sm transition-opacity disabled:opacity-50"
              style={{ background: "var(--color-accent)", color: "white" }}
            >
              {loading ? "Connexion..." : "Se connecter"}
            </button>
          </form>
        </div>
      </div>
    </div>
  )
}
