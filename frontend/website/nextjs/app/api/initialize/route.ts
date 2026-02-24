import { NextResponse } from "next/server";
import type { InitialAppData } from "@/context/AppContext";
import type { InitializeResponse } from "@/lib/types";

/** Prefer server-side URL so Docker can use host.docker.internal to reach backend. */
const getApiUrl = (): string =>
  process.env.CMS_API_URL ||
  process.env.API_URL ||
  process.env.NEXT_PUBLIC_API_URL ||
  "https://api.wanaid.org/api";

const getSiteOrigin = (): string =>
  process.env.NEXT_PUBLIC_SITE_URL ||
  (process.env.VERCEL_URL ? `https://${process.env.VERCEL_URL}` : "https://www.wanaid.org");

/** Backend expects GET /api/v1/initialize and x-api-key (CLIENT_API_KEY). */
const API_VERSION = "v1";

/**
 * Proxies initialize to the backend so the external API URL and full response
 * (e.g. user/session data) are never exposed to the client. Only app data is returned.
 */
export async function POST(request: Request) {
  try {
    const body = await request.json().catch(() => ({}));
    const locale = typeof body?.locale === "string" ? body.locale : undefined;

    const params = new URLSearchParams();
    if (locale) params.set("locale", locale);
    const query = params.toString();
    const url = `${getApiUrl()}/${API_VERSION}/initialize${query ? `?${query}` : ""}`;
    const headers: Record<string, string> = {
      Accept: "application/json",
    };
    // Prefer CLIENT_API_KEY (from backend .env when using Docker) so key matches exactly
    let apiKey =
      process.env.CLIENT_API_KEY ||
      process.env.NEXT_PUBLIC_API_KEY ||
      process.env.CMS_API_KEY ||
      process.env.API_KEY;
    if (apiKey) {
      apiKey = String(apiKey).replace(/^['"]|['"]$/g, "").trim();
      if (apiKey) headers["x-api-key"] = apiKey;
    }
    const cookie = request.headers.get("cookie");
    const authorization = request.headers.get("authorization");
    const referer = request.headers.get("referer") ?? request.headers.get("referrer") ?? getSiteOrigin();
    if (cookie) headers["Cookie"] = cookie;
    if (authorization) headers["Authorization"] = authorization;
    headers["Referer"] = referer;

    const res = await fetch(url, {
      method: "GET",
      headers,
    });

    if (!res.ok) {
      const text = await res.text().catch(() => res.statusText);
      let errorMessage: string;
      try {
        const errJson = JSON.parse(text) as { errors?: string[]; message?: string };
        if (Array.isArray(errJson.errors) && errJson.errors.length > 0) {
          errorMessage = errJson.errors[0];
        } else if (typeof errJson.message === "string") {
          errorMessage = errJson.message;
        } else {
          errorMessage = text || `Request failed (${res.status})`;
        }
      } catch {
        errorMessage = text || `Request failed (${res.status})`;
      }
      return NextResponse.json({ error: errorMessage }, { status: res.status });
    }

    const json = (await res.json()) as InitializeResponse;
    const data = json?.data;
    if (data == null) {
      return NextResponse.json(
        { error: "Invalid initialize response (missing data)" },
        { status: 502 }
      );
    }
    // Rewrite host.docker.internal in URLs so the browser can load them (e.g. theme logos)
    const publicApiOrigin =
      process.env.NEXT_PUBLIC_API_URL?.replace(/\/api\/?$/, "") ||
      "http://localhost:9332";
    const replaceUrl = (s: string) =>
      typeof s === "string" && s.includes("host.docker.internal")
        ? s.replace(/https?:\/\/host\.docker\.internal(:\d+)?/, publicApiOrigin)
        : s;
    if (data.theme?.logos) {
      const logos = data.theme.logos as Record<string, string>;
      for (const k of Object.keys(logos)) {
        logos[k] = replaceUrl(logos[k]);
      }
    }
    return NextResponse.json(data as InitialAppData);
  } catch (err) {
    console.error("Initialize proxy error:", err);
    return NextResponse.json(
      { error: "Failed to load app data" },
      { status: 500 }
    );
  }
}
