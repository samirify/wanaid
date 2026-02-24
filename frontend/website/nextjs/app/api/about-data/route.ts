import { NextResponse } from "next/server";

const getApiUrl = (): string =>
  process.env.CMS_API_URL ||
  process.env.API_URL ||
  process.env.NEXT_PUBLIC_API_URL ||
  "https://api.wanaid.org/api";

function getApiKey(): string | undefined {
  const raw =
    process.env.CLIENT_API_KEY ||
    process.env.NEXT_PUBLIC_API_KEY ||
    process.env.CMS_API_KEY ||
    process.env.API_KEY;
  if (!raw) return undefined;
  return String(raw).replace(/^['"]|['"]$/g, "").trim() || undefined;
}

/** Proxies GET /api/v1/pages/about from the backend (about page data). */
export async function GET(request: Request) {
  try {
    const { searchParams } = new URL(request.url);
    const locale = searchParams.get("locale") ?? undefined;
    const query = locale ? `?locale=${locale}` : "";
    const base = getApiUrl().replace(/\/api\/?$/, "");
    const url = `${base}/api/v1/pages/about${query}`;
    const headers: Record<string, string> = {
      Accept: "application/json",
      Referer:
        process.env.NEXT_PUBLIC_SITE_URL ||
        (process.env.VERCEL_URL ? `https://${process.env.VERCEL_URL}` : "http://localhost:9331"),
    };
    const apiKey = getApiKey();
    if (apiKey) headers["x-api-key"] = apiKey;

    const res = await fetch(url, { method: "GET", headers, cache: "no-store" });

    if (!res.ok) {
      const text = await res.text().catch(() => res.statusText);
      return NextResponse.json(
        { error: text || `Request failed (${res.status})` },
        { status: res.status }
      );
    }

    const json = (await res.json()) as { success?: boolean; data?: Record<string, unknown> };
    const data = json?.data ?? {};
    // Backend returns main_header_img, meta, headers, pillars; frontend may expect teams
    if (!("teams" in data)) (data as Record<string, unknown>).teams = [];
    return NextResponse.json({ data });
  } catch (err) {
    console.error("About-data proxy error:", err);
    return NextResponse.json(
      { error: "Failed to load about page data" },
      { status: 500 }
    );
  }
}
