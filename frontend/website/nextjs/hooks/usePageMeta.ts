"use client";

import { useState, useEffect } from "react";
import { useLocale } from "next-intl";
import { usePathname } from "next/navigation";
import { api } from "@/lib/api";

interface PageMeta {
  title?: string;
  description?: string;
  keywords?: string;
}

/**
 * Fetches page metadata from `/pages/{path}` — the same source the React app uses.
 * Returns { title, description, keywords } or null while loading.
 */
export function usePageMeta(): PageMeta | null {
  const locale = useLocale();
  const pathname = usePathname();
  const [meta, setMeta] = useState<PageMeta | null>(null);

  useEffect(() => {
    // Strip the locale prefix from the pathname to get the page code
    // e.g. "/en/about" → "about", "/ar/cause/help-us" → "cause/help-us", "/en" → "main"
    let pagePath = pathname
      .replace(new RegExp(`^/${locale}/?`), "")
      .replace(/^\/+|\/+$/g, "");

    // React app passes "main" for the landing page
    if (!pagePath) pagePath = "main";

    // blog and open-causes are landing page sections — their titles come from
    // settings, not from /pages/{path}. Skip the API call for those.
    if (pagePath === "blog" || pagePath === "open-causes") return;

    api
      .getPageData(pagePath, locale)
      .then((data) => {
        if (data?.meta) {
          setMeta(data.meta);
        }
      })
      .catch(() => {
        // Silently fail — PageHead will use fallback title
      });
  }, [pathname, locale]);

  return meta;
}
