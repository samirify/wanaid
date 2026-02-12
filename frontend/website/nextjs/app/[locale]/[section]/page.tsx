"use client";

import { useState, useEffect, use } from "react";
import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
// Using native <img> for external API images
import { api } from "@/lib/api";
import { mediaUrl } from "@/lib/utils";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { PageSections } from "@/components/landing/PageSections";
import type { SupportPageResponse } from "@/lib/types";

interface PageProps {
  params: Promise<{ section: string; locale: string }>;
}

export default function SupportPage({ params }: PageProps) {
  const { section } = use(params);
  const t = useTranslations();
  const rawT = useRawTranslation();
  const locale = useLocale();
  const [data, setData] = useState<SupportPageResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchPage() {
      try {
        setLoading(true);
        const result = await api.getSupportPageData(section);
        if (!result.success) {
          setError("Page not found.");
          return;
        }
        setData(result);
      } catch {
        setError("Failed to load page.");
      } finally {
        setLoading(false);
      }
    }
    fetchPage();
  }, [section, locale]);

  if (loading) return <Loader fullPage />;
  if (error || !data) {
    return (
      <ErrorDisplay variant="page" message={error ?? undefined} showHomeButton />
    );
  }

  return (
    <>
      {/* Page Hero */}
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
            {data.headers?.main_header_middle_big
              ? rawT(data.headers.main_header_middle_big)
              : section}
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

      {/* Pillars */}
      {data.pillars && data.pillars.length > 0 && (
        <PageSections pillars={data.pillars} />
      )}
    </>
  );
}
