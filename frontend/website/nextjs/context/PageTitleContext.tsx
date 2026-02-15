"use client";

import { createContext, useContext, useState, useCallback, type ReactNode } from "react";
import { SEO_DEFAULTS } from "@/lib/seo-defaults";

interface PageTitleContextValue {
  pageTitleOverride: string | null;
  setPageTitleOverride: (title: string | null) => void;
}

const PageTitleContext = createContext<PageTitleContextValue | null>(null);

export function PageTitleProvider({ children }: { children: ReactNode }) {
  const [pageTitleOverride, setPageTitleOverrideState] = useState<string | null>(null);
  const setPageTitleOverride = useCallback((title: string | null) => {
    setPageTitleOverrideState(title);
    if (typeof document !== "undefined") {
      const plain = title && String(title).replace(/<[^>]*>/g, "").trim();
      document.title = plain ? `${plain} | ${SEO_DEFAULTS.title}` : SEO_DEFAULTS.title;
    }
  }, []);
  return (
    <PageTitleContext.Provider value={{ pageTitleOverride, setPageTitleOverride }}>
      {children}
    </PageTitleContext.Provider>
  );
}

export function usePageTitleOverride(): PageTitleContextValue {
  const ctx = useContext(PageTitleContext);
  if (!ctx) {
    throw new Error("usePageTitleOverride must be used within PageTitleProvider");
  }
  return ctx;
}
