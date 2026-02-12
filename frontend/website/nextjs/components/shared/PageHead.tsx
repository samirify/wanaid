"use client";

/**
 * PageHead – fetches meta from /pages/{path} (same as React app's Dynamic component)
 * and renders <title> + <meta> tags that React 19 hoists into <head>.
 *
 * Behaviour (matching React app):
 *   title  → pageData.meta.title  ||  "Welcome to {CLIENT_NAME}"
 *   desc   → pageData.meta.description  (omitted when empty)
 *   kw     → pageData.meta.keywords     (omitted when empty)
 */

import { usePageMeta } from "@/hooks/usePageMeta";

const clientName = process.env.NEXT_PUBLIC_CLIENT_NAME || "Client Name";

export function PageHead() {
  const meta = usePageMeta();

  const pageTitle = meta?.title || `Welcome to ${clientName}`;

  return (
    <>
      <title>{pageTitle}</title>
      {meta?.description && (
        <meta name="description" content={meta.description} />
      )}
      {meta?.keywords && <meta name="keywords" content={meta.keywords} />}
    </>
  );
}
