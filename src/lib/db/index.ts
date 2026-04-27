import { neon } from "@neondatabase/serverless"
import { drizzle } from "drizzle-orm/neon-http"
import * as schema from "./schema"

type DrizzleDb = ReturnType<typeof drizzle<typeof schema>>

let _db: DrizzleDb | null = null

export function getDb(): DrizzleDb {
  if (!_db) {
    if (!process.env.DATABASE_URL) {
      throw new Error("DATABASE_URL n'est pas définie. Configure ta base de données Neon.")
    }
    const url = new URL(process.env.DATABASE_URL)
    url.searchParams.delete("channel_binding")
    const sql = neon(url.toString())
    _db = drizzle(sql, { schema })
  }
  return _db
}

// Alias for convenience
export const db = new Proxy({} as DrizzleDb, {
  get(_, prop: string) {
    return getDb()[prop as keyof DrizzleDb]
  },
})
