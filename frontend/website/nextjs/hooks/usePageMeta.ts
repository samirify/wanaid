"use client";

import { useLocale } from "next-intl";
import { usePathname } from "next/navigation";
import { useAppData } from "@/context/AppContext";
import { SEO_DEFAULTS, PATH_META } from "@/lib/seo-defaults";

export interface PageMeta {
  title?: string;
  description?: string;
  keywords?: string;
}

/**
 * Page meta for <PageHead>. Landing (main) uses API meta when available.
 * All other routes use path-based defaults from PATH_META so every page
 * has a proper title and description for SEO.
 */
export function usePageMeta(): PageMeta {
  const pathname = usePathname();
  const locale = useLocale();
  const { pageContents } = useAppData();

  const pagePath = pathname
    .replace(new RegExp(`^/${locale}/?`), "")
    .replace(/^\/+|\/+$/g, "") || "main";

  // Landing: prefer API meta
  if (pagePath === "main") {
    const landing = pageContents?.LANDING?.META;
    if (landing?.title || landing?.description || landing?.keywords) {
      return {
        title: landing.title ?? SEO_DEFAULTS.title,
        description: landing.description ?? SEO_DEFAULTS.description,
        keywords: landing.keywords ?? SEO_DEFAULTS.keywords,
      };
    }
  }

  // Per-route defaults (gallery, about, contact, etc.) with page-specific keywords
  const pathMeta = PATH_META[pagePath];
  if (pathMeta) {
    return {
      title: pathMeta.title,
      description: pathMeta.description ?? SEO_DEFAULTS.description,
      keywords: pathMeta.keywords ?? SEO_DEFAULTS.keywords,
    };
  }

  // Fallback so every page has at least a title
  return {
    title: SEO_DEFAULTS.title,
    description: SEO_DEFAULTS.description,
    keywords: SEO_DEFAULTS.keywords,
  };
}
