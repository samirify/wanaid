"use client";

import { useState, useEffect, use } from "react";
import { useLocale } from "next-intl";
import { motion } from "framer-motion";
import { api } from "@/lib/api";
import { mediaUrl } from "@/lib/utils";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { usePageTitleOverride } from "@/context/PageTitleContext";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { PageHero } from "@/components/shared/PageHero";
import { PayPalPaymentWidget } from "@/components/shared/PayPalPaymentWidget";
import { PageSections } from "@/components/landing/PageSections";
import { SectionSeparator } from "@/components/shared/SectionSeparator";
import { ArrowLeft } from "lucide-react";
import type { CauseDetailResponse } from "@/lib/types";

interface PageProps {
  params: Promise<{ title: string; locale: string }>;
}

export default function CauseDetailPage({ params }: PageProps) {
  const { title } = use(params);
  const rawT = useRawTranslation();
  const locale = useLocale();
  const { setPageTitleOverride } = usePageTitleOverride();
  const [data, setData] = useState<CauseDetailResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchCause() {
      try {
        setLoading(true);
        const result = await api.getCauseDetails(title);
        setData(result);
      } catch (err) {
        if (err && typeof err === "object" && "status" in err && (err as { status: number }).status === 404) {
          setError("Page not found.");
        } else {
          setError("Server error.");
        }
      } finally {
        setLoading(false);
      }
    }
    fetchCause();
  }, [title, locale]);

  if (loading) return <Loader fullPage />;
  if (error || !data || !data.cause) {
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

  const { cause, headers, pillars } = data;
  const progress = cause.target
    ? Math.min(
        Math.round((0 / parseFloat(cause.target)) * 100),
        100
      )
    : 0;

  return (
    <>
      <PageHero
        title={rawT(cause.title)}
        onTitleResolved={setPageTitleOverride}
        topLine={headers?.main_header_top ? (rawT(headers.main_header_top) || undefined) : undefined}
        bottomLine={headers?.main_header_bottom ? (rawT(headers.main_header_bottom) || undefined) : undefined}
        headerImageUrl={cause.header_img_url}
        variant="fixed"
        align="start"
        showCurve
        trailingSlot={<div className="hidden lg:block lg:w-[380px] shrink-0" aria-hidden />}
      />

      <section className="relative py-24">
        <div className="container-custom">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:items-start">
            {/* Main Content — body text; on mobile appears below donate */}
            <div className="lg:col-span-2 min-w-0 order-last lg:order-none">
              <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">
                {rawT(cause.title)}
              </h3>
              <hr className="mb-6 border-slate-200 dark:border-slate-700" />
              <div
                className="prose prose-lg dark:prose-invert max-w-none mb-8"
                dangerouslySetInnerHTML={{ __html: rawT(cause.body) }}
              />
            </div>

            {/* Sidebar — PayPal widget (pulled up into hero); on mobile first (above content); z-20 so it's above hero and clickable */}
            <div className="lg:col-span-1 order-first lg:order-none lg:sticky lg:top-24 lg:-mt-[31rem] lg:z-20 relative">
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.2 }}
                className="rounded-2xl shadow-2xl shadow-black/20 overflow-hidden mb-8 lg:mb-10 border-2 border-white/25 dark:border-slate-500/60 ring-2 ring-white/15 dark:ring-slate-400/25"
              >
                <PayPalPaymentWidget cause={cause} />
              </motion.div>
              {cause.img_url && (
                <div className="rounded-2xl overflow-hidden shadow-xl border-2 border-white/25 dark:border-slate-500/60 ring-2 ring-white/15 dark:ring-slate-400/25">
                  <img
                    src={mediaUrl(cause.img_url)}
                    alt={rawT(cause.title)}
                    className="w-full h-auto object-cover"
                  />
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      {pillars && pillars.length > 0 && (
        <>
          <SectionSeparator />
          <PageSections pillars={pillars} />
        </>
      )}
    </>
  );
}
