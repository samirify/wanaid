"use client";

import { useLocale } from "next-intl";
import { usePathname } from "next/navigation";
import { useAppData } from "@/context/AppContext";

interface PageMeta {
  title?: string;
  description?: string;
  keywords?: string;
}

/**
 * Page meta for <PageHead>. Only landing (main) has meta from the API (initialize).
 * All other pages use the fallback title in PageHead — no /pages/{path} calls here,
 * since the backend does not expose /pages/ for about, contact, blog, cause, etc.
 */
export function usePageMeta(): PageMeta | null {
  const pathname = usePathname();
  const locale = useLocale();
  const { pageContents } = useAppData();

  const pagePath = pathname
    .replace(new RegExp(`^/${locale}/?`), "")
    .replace(/^\/+|\/+$/g, "") || "main";

  if (pagePath !== "main") {
    return null;
  }

  const landing = pageContents?.LANDING?.META;
  if (!landing) return null;
  return {
    title: landing.title ?? undefined,
    description: landing.description ?? undefined,
    keywords: landing.keywords ?? undefined,
  };
}
