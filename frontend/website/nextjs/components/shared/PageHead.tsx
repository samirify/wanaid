"use client";

/**
 * PageHead – page title and meta tags for SEO.
 * Rendered once in the layout. Dynamic pages (cause, blog, team) set a title
 * override via PageTitleContext (the hero main heading). When set, that is
 * used as the document title (with " | WAN Aid" suffix); otherwise use path meta.
 */

import { useEffect } from "react";
import { usePageMeta } from "@/hooks/usePageMeta";
import { usePageTitleOverride } from "@/context/PageTitleContext";
import { SEO_DEFAULTS } from "@/lib/seo-defaults";

export function PageHead() {
  const meta = usePageMeta();
  const { pageTitleOverride } = usePageTitleOverride();
  const override = pageTitleOverride?.trim();
  const baseTitle = meta.title || SEO_DEFAULTS.title;
  const pageTitle = override ? `${override} | ${SEO_DEFAULTS.title}` : baseTitle;
  const description = meta.description || SEO_DEFAULTS.description;
  const keywords = meta.keywords || SEO_DEFAULTS.keywords;

  useEffect(() => {
    document.title = pageTitle;
  }, [pageTitle]);

  return (
    <>
      <title>{pageTitle}</title>
      <meta name="description" content={description} />
      <meta name="keywords" content={keywords} />
    </>
  );
}
