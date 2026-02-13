import { NextResponse } from "next/server";
import type { InitialAppData } from "@/context/AppContext";
import type { InitializeResponse } from "@/lib/types";

const getApiUrl = (): string =>
  process.env.NEXT_PUBLIC_API_URL || process.env.API_URL || "https://api.wanaid.org/api";

const getSiteOrigin = (): string =>
  process.env.NEXT_PUBLIC_SITE_URL ||
  (process.env.VERCEL_URL ? `https://${process.env.VERCEL_URL}` : "https://www.wanaid.org");

/**
 * Proxies initialize to the backend so the external API URL and full response
 * (e.g. user/session data) are never exposed to the client. Only app data is returned.
 */
export async function POST(request: Request) {
  try {
    const body = await request.json().catch(() => ({}));
    const locale = typeof body?.locale === "string" ? body.locale : undefined;

    const url = `${getApiUrl()}/initialize`;
    const headers: Record<string, string> = {
      "Content-Type": "application/json",
      Accept: "application/json",
    };
    const cookie = request.headers.get("cookie");
    const authorization = request.headers.get("authorization");
    const referer = request.headers.get("referer") ?? request.headers.get("referrer") ?? getSiteOrigin();
    if (cookie) headers["Cookie"] = cookie;
    if (authorization) headers["Authorization"] = authorization;
    headers["Referer"] = referer;

    const res = await fetch(url, {
      method: "POST",
      headers,
      body: locale ? JSON.stringify({ locale }) : undefined,
    });

    if (!res.ok) {
      const text = await res.text().catch(() => res.statusText);
      return NextResponse.json(
        { error: text || `Request failed (${res.status})` },
        { status: res.status }
      );
    }

    const json = (await res.json()) as InitializeResponse;
    // Return only .data so user/session is never sent to the client
    const data: InitialAppData = json.data;
    return NextResponse.json(data);
  } catch (err) {
    console.error("Initialize proxy error:", err);
    return NextResponse.json(
      { error: "Failed to load app data" },
      { status: 500 }
    );
  }
}
