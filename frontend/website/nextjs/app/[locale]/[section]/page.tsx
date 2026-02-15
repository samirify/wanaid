"use client";

import { useState, useEffect, use, useRef } from "react";
import { useLocale } from "next-intl";
import { motion } from "framer-motion";
import { api } from "@/lib/api";
import { mediaUrl } from "@/lib/utils";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { useAppData } from "@/context/AppContext";
import { Hero } from "@/components/landing/Hero";
import { OpenCauses } from "@/components/landing/OpenCauses";
import { BlogSection } from "@/components/landing/BlogSection";
import { PageSections } from "@/components/landing/PageSections";
import { SectionSeparator } from "@/components/shared/SectionSeparator";
import { PageHead } from "@/components/shared/PageHead";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import type { PageHeaders, Pillar } from "@/lib/types";

interface PageProps {
  params: Promise<{ section: string; locale: string }>;
}

/** Sections that live on the landing page (scroll to ref) — same as React app */
const LANDING_SECTIONS = ["blog", "open-causes"];

interface SupportPageData {
  main_header_img?: string;
  meta: { title?: string; description?: string; keywords?: string };
  headers: PageHeaders;
  pillars: Pillar[];
}

/**
 * Matches the React app's routing logic:
 *
 * - /blog → renders the full landing page, scrolls to the blog section
 * - /open-causes → renders the full landing page, scrolls to the open causes section
 * - /anything-else → fetches /pages/{section} and renders hero + pillars (support page)
 */
export default function SectionPage({ params }: PageProps) {
  const { section } = use(params);

  if (LANDING_SECTIONS.includes(section)) {
    return <LandingWithScroll section={section} />;
  }

  return <SupportPage section={section} />;
}

/* ── Landing page with scroll to section (blog, open-causes) ────────────── */

function LandingWithScroll({ section }: { section: string }) {
  const { pageContents, settings, isLoading, error, refetch } = useAppData();
  const scrollTargetRef = useRef<HTMLDivElement>(null);
  const hasScrolled = useRef(false);

  const clientName = process.env.NEXT_PUBLIC_CLIENT_NAME || "Client Name";

  // Determine title from settings — same logic as React Landing.jsx
  let pageTitle: string | null = null;
  if (section === "blog") {
    pageTitle = settings.static_page_blog_title || `${clientName} | Our Blog`;
  } else if (section === "open-causes") {
    pageTitle = settings.static_page_open_causes_title || `${clientName} | Open Causes`;
  }

  // Scroll to the section after content loads
  useEffect(() => {
    if (!isLoading && scrollTargetRef.current && !hasScrolled.current) {
      hasScrolled.current = true;
      setTimeout(() => {
        if (scrollTargetRef.current) {
          const top = scrollTargetRef.current.offsetTop - 70;
          window.scrollTo({ top, left: 0, behavior: "smooth" });
        }
      }, 100);
    }
  }, [isLoading]);

  if (isLoading) return <Loader fullPage />;
  if (error) return <ErrorDisplay variant="page" errorCode="500" onRetry={refetch} showHomeButton />;

  const pillars = pageContents?.LANDING?.PILLARS || [];

  return (
    <>
      {pageTitle && <title>{pageTitle}</title>}
      <Hero />
      {pillars.length > 0 && <PageSections pillars={pillars} />}
      <SectionSeparator />
      <div ref={section === "open-causes" ? scrollTargetRef : undefined}>
        <OpenCauses />
      </div>
      <SectionSeparator />
      <div ref={section === "blog" ? scrollTargetRef : undefined}>
        <BlogSection />
      </div>
    </>
  );
}

/* ── Support page (unknown sections) — fetches /pages/{section} ─────────── */

function SupportPage({ section }: { section: string }) {
  const rawT = useRawTranslation();
  const locale = useLocale();
  const [data, setData] = useState<SupportPageData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchPage() {
      try {
        setLoading(true);
        const result = await api.getPageData(section, locale);
        if (!result?.meta) {
          setError("Page not found.");
          return;
        }
        setData(result);
      } catch (err) {
        // Check if the API returned 404
        if (err && typeof err === "object" && "status" in err && (err as { status: number }).status === 404) {
          setError("Page not found.");
        } else {
          setError("Server error.");
        }
      } finally {
        setLoading(false);
      }
    }
    fetchPage();
  }, [section, locale]);

  if (loading) return <Loader fullPage />;
  if (error || !data) {
    const is404 = error === "Page not found.";
    return (
      <ErrorDisplay
        variant="page"
        errorCode={is404 ? "404" : "500"}
        title={is404 ? rawT("WEBSITE_ERRORS_PAGE_NOT_FOUND_HEADER") : undefined}
        message={is404 ? rawT("WEBSITE_ERRORS_PAGE_NOT_FOUND_MESSAGE") : undefined}
        showHomeButton
      />
    );
  }

  return (
    <>
      <PageHead />

      {/* Hero — only if headers exist (same as React Dynamic) */}
      {data.headers?.main_header_middle_big && (
        <div className="page-hero">
          {data.main_header_img && (
            <img
              src={mediaUrl(data.main_header_img)}
              alt=""
              className="absolute inset-0 w-full h-full object-cover opacity-20"
            />
          )}
          <div className="page-hero-content">
            {data.headers?.main_header_top && (
              <motion.p
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="text-primary-200 font-medium mb-3"
              >
                {rawT(data.headers.main_header_top)}
              </motion.p>
            )}
            <motion.h1
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.1 }}
              className="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4"
            >
              {rawT(data.headers.main_header_middle_big)}
            </motion.h1>
            {data.headers?.main_header_bottom && (
              <motion.p
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.2 }}
                className="text-white/80 text-lg max-w-2xl mx-auto"
              >
                {rawT(data.headers.main_header_bottom)}
              </motion.p>
            )}
          </div>
        </div>
      )}

      {data.pillars && data.pillars.length > 0 && (
        <>
          {data.headers?.main_header_middle_big && <SectionSeparator />}
          <PageSections pillars={data.pillars} />
        </>
      )}

      {/* No hero and no pillars = error (same as React Dynamic) */}
      {!data.headers?.main_header_middle_big && (!data.pillars || data.pillars.length === 0) && (
        <ErrorDisplay variant="page" showHomeButton />
      )}
    </>
  );
}
