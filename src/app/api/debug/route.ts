import { NextResponse } from "next/server"
import { neon } from "@neondatabase/serverless"

export async function GET() {
  try {
    const rawUrl = process.env.DATABASE_URL || ""
    const url = new URL(rawUrl)
    url.searchParams.delete("channel_binding")
    const sql = neon(url.toString())
    const result = await sql`SELECT 1 as test`
    return NextResponse.json({ ok: true, result, urlHost: url.hostname })
  } catch (e: unknown) {
    const err = e as Record<string, unknown>
    return NextResponse.json({
      ok: false,
      message: err?.message,
      cause: String(err?.cause),
      code: err?.code,
    }, { status: 500 })
  }
}
