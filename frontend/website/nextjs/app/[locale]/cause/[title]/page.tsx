"use client";

import { useState, useEffect, use } from "react";
import { useLocale } from "next-intl";
import { motion } from "framer-motion";
// Using native <img> for external API images
import { Link } from "@/i18n/navigation";
import { api } from "@/lib/api";
import { mediaUrl } from "@/lib/utils";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { PageHead } from "@/components/shared/PageHead";
import { PayPalPaymentWidget } from "@/components/shared/PayPalPaymentWidget";
import { PageSections } from "@/components/landing/PageSections";
import { ArrowLeft } from "lucide-react";
import type { CauseDetailResponse } from "@/lib/types";

interface PageProps {
  params: Promise<{ title: string; locale: string }>;
}

export default function CauseDetailPage({ params }: PageProps) {
  const { title } = use(params);
  const rawT = useRawTranslation();
  const locale = useLocale();
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
      <PageHead />

      {/* Page Hero — text only, fixed height; widget lives in section below and overlaps here */}
      <div className="relative h-[min(36rem,36vh)] min-h-[280px] lg:h-[32rem] lg:min-h-[320px] flex flex-col justify-center overflow-hidden bg-gradient-to-br from-primary-700 via-primary-800 to-primary-900 dark:from-primary-950 dark:via-slate-900 dark:to-slate-900">
        {/* Header image as background flavour (faded) */}
        {cause.header_img_url && (
          <div className="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden>
            <img
              src={mediaUrl(cause.header_img_url)}
              alt=""
              className="absolute inset-0 w-full h-full object-cover opacity-20 dark:opacity-15"
            />
            <div
              className="absolute inset-0 bg-gradient-to-r from-primary-900/95 via-primary-900/70 to-transparent dark:from-slate-900/95 dark:via-slate-900/80"
              aria-hidden
            />
          </div>
        )}
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_80%_70%_at_50%_50%,rgba(199,28,105,0.2)_0%,transparent_50%)] pointer-events-none" aria-hidden />
        <div className="relative z-10 container-custom w-full py-16 pt-28 pb-20 text-start h-full flex flex-col justify-center">
          <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-10">
            <div className="lg:flex-1 text-white shrink-0">
              {headers?.main_header_top && (
                <motion.p
                  initial={{ opacity: 0, y: 16 }}
                  animate={{ opacity: 1, y: 0 }}
                  className="font-display text-primary-200 font-medium mb-3"
                >
                  {rawT(headers.main_header_top)}
                </motion.p>
              )}
              <motion.h1
                initial={{ opacity: 0, y: 16 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.05 }}
                className="display-headline text-4xl sm:text-5xl lg:text-6xl font-bold mb-4 text-white"
              >
                {rawT(cause.title)}
              </motion.h1>
              {headers?.main_header_bottom && (
                <motion.p
                  initial={{ opacity: 0, y: 16 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.1 }}
                  className="text-white/85 text-lg max-w-2xl"
                >
                  {rawT(headers.main_header_bottom)}
                </motion.p>
              )}
            </div>
            {/* Spacer on desktop so text doesn't sit under where the widget overlaps */}
            <div className="hidden lg:block lg:w-[380px] shrink-0" aria-hidden />
          </div>
        </div>
        <svg className="absolute bottom-0 left-0 right-0 w-full h-16 text-white dark:text-slate-900" viewBox="0 0 1440 64" fill="none" preserveAspectRatio="none" aria-hidden>
          <path d="M0 64V32C240 0 480 0 720 32s480 32 720 32v32H0z" fill="currentColor" />
        </svg>
      </div>

      <section className="relative py-24">
        <div className="container-custom">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:items-start">
            {/* Main Content — body text */}
            <div className="lg:col-span-2 min-w-0">
              <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">
                {rawT(cause.title)}
              </h3>
              <hr className="mb-6 border-slate-200 dark:border-slate-700" />
              <div
                className="prose prose-lg dark:prose-invert max-w-none mb-8"
                dangerouslySetInnerHTML={{ __html: rawT(cause.body) }}
              />
            </div>

            {/* Sidebar — PayPal widget (pulled up into hero); z-20 so it's above hero and clickable */}
            <div className="lg:col-span-1 order-last lg:order-none lg:sticky lg:top-24 lg:-mt-[31rem] lg:z-20 relative">
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.2 }}
                className="rounded-2xl shadow-2xl shadow-black/20 overflow-visible mb-8 lg:mb-10"
              >
                <PayPalPaymentWidget cause={cause} />
              </motion.div>
              {cause.img_url && (
                <img
                  src={mediaUrl(cause.img_url)}
                  alt={rawT(cause.title)}
                  className="w-full h-auto rounded-2xl shadow-xl"
                />
              )}
            </div>
          </div>
        </div>
      </section>

      {/* Pillars */}
      {pillars && pillars.length > 0 && <PageSections pillars={pillars} />}
    </>
  );
}
