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

      {/* Page Hero */}
      <div className="page-hero !overflow-visible">
        {cause.header_img_url && (
          <img
            src={mediaUrl(cause.header_img_url)}
            alt=""
            className="absolute inset-0 w-full h-full object-cover opacity-30"
          />
        )}
        <div className="relative z-10 container-custom w-full py-20 pt-32 text-start">
          <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            {/* Left: headers — same style as other page heroes */}
            <div className="lg:flex-1 text-white">
              {headers?.main_header_top && (
                <motion.p
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  className="text-primary-200 font-medium mb-3"
                >
                  {rawT(headers.main_header_top)}
                </motion.p>
              )}
              <motion.h1
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.1 }}
                className="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4"
              >
                {rawT(cause.title)}
              </motion.h1>
              {headers?.main_header_bottom && (
                <motion.p
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.2 }}
                  className="text-white/80 text-lg max-w-2xl"
                >
                  {rawT(headers.main_header_bottom)}
                </motion.p>
              )}
            </div>

            {/* Right: PayPal widget — inside hero, aligned with container */}
            <div className="w-full lg:w-[370px] shrink-0 relative z-[100]">
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.3 }}
                className="rounded-2xl shadow-xl overflow-visible"
              >
                <PayPalPaymentWidget cause={cause} />
              </motion.div>
            </div>
          </div>
        </div>
      </div>

      <section className="py-24">
        <div className="container-custom">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
            {/* Main Content — body text */}
            <div className="lg:col-span-2">
              <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">
                {rawT(cause.title)}
              </h3>
              <hr className="mb-6 border-slate-200 dark:border-slate-700" />
              <div
                className="prose prose-lg dark:prose-invert max-w-none mb-8"
                dangerouslySetInnerHTML={{ __html: rawT(cause.body) }}
              />
            </div>

            {/* Sidebar — image */}
            <div className="lg:col-span-1">
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
