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

/** Proxies GET /api/v1/translate/{locale} from the backend. */
export async function GET(
  _request: Request,
  { params }: { params: Promise<{ locale: string }> }
) {
  try {
    const { locale } = await params;
    if (!locale) {
      return NextResponse.json(
        { error: "Missing locale" },
        { status: 400 }
      );
    }
    const url = `${getApiUrl().replace(/\/api\/?$/, "")}/api/v1/translate/${locale}`;
    const headers: Record<string, string> = {
      Accept: "application/json",
      Referer:
        process.env.NEXT_PUBLIC_SITE_URL ||
        process.env.VERCEL_URL
          ? `https://${process.env.VERCEL_URL}`
          : "http://localhost:9331",
    };
    const apiKey = getApiKey();
    if (apiKey) headers["x-api-key"] = apiKey;

    const res = await fetch(url, { method: "GET", headers, cache: "no-store" });

    if (!res.ok) {
      const text = await res.text().catch(() => res.statusText);
      return NextResponse.json(
        { error: text || `Translate failed (${res.status})` },
        { status: res.status }
      );
    }

    const data = await res.json();
    return NextResponse.json(data);
  } catch (err) {
    console.error("Translations proxy error:", err);
    return NextResponse.json(
      { error: "Failed to load translations" },
      { status: 500 }
    );
  }
}
