"use client";

import { useState, useEffect } from "react";
import { useLocale } from "next-intl";
import { usePathname } from "next/navigation";
import { api } from "@/lib/api";
import { useAppData } from "@/context/AppContext";

interface PageMeta {
  title?: string;
  description?: string;
  keywords?: string;
}

/**
 * Resolves page metadata: landing (main) from app context; other pages from `/pages/{path}`.
 * Avoids calling /pages/main which returns 404 (landing data comes from initialize).
 */
export function usePageMeta(): PageMeta | null {
  const locale = useLocale();
  const pathname = usePathname();
  const { pageContents } = useAppData();
  const [meta, setMeta] = useState<PageMeta | null>(null);

  // Strip the locale prefix from the pathname to get the page code
  let pagePath = pathname
    .replace(new RegExp(`^/${locale}/?`), "")
    .replace(/^\/+|\/+$/g, "");
  if (!pagePath) pagePath = "main";

  // Landing page: use LANDING.META from initialize (no /pages/main — API returns 404)
  if (pagePath === "main") {
    const landing = pageContents?.LANDING?.META;
    if (!landing) return null;
    return {
      title: landing.title ?? undefined,
      description: landing.description ?? undefined,
      keywords: landing.keywords ?? undefined,
    };
  }

  // blog and open-causes: titles from settings; no /pages call
  if (pagePath === "blog" || pagePath === "open-causes") {
    return null;
  }

  useEffect(() => {
    setMeta(null);
    api
      .getPageData(pagePath, locale)
      .then((data) => {
        if (data?.meta) {
          setMeta(data.meta as PageMeta);
        }
      })
      .catch(() => {
        // Silently fail — PageHead will use fallback title
      });
  }, [pathname, locale, pagePath]);

  return meta;
}
