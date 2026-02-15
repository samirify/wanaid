"use client";

import { useTranslations } from "next-intl";
import { motion } from "framer-motion";
import { Link } from "@/i18n/navigation";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { ProgressBar } from "@/components/shared/ProgressBar";
import { Heart, Users } from "lucide-react";
import { formatCurrency } from "@/lib/utils";
import type { CauseSummary } from "@/lib/types";

interface CauseStripProps {
  cause: CauseSummary;
  index: number;
  imageOnLeft: boolean;
}

export function CauseStrip({ cause, index, imageOnLeft }: CauseStripProps) {
  const t = useTranslations();
  const rawT = useRawTranslation();

  const imageBlock = (
    <div className="w-full md:w-[46%] relative min-h-[260px] md:min-h-[360px] overflow-hidden">
      {cause.img_url ? (
        <>
          <img
            src={cause.img_url}
            alt={rawT(cause.title)}
            className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
        </>
      ) : (
        <div className="absolute inset-0 flex items-center justify-center bg-slate-200 dark:bg-slate-700 text-slate-400">
          <Heart className="w-16 h-16" />
        </div>
      )}
      <div className="absolute top-4 start-4">
        <span className="px-3 py-1.5 rounded-lg bg-white/95 dark:bg-slate-900/95 text-slate-800 dark:text-slate-200 text-sm font-semibold shadow-sm">
          {cause.currencies_code}
        </span>
      </div>
      <div className="absolute bottom-4 start-4 flex items-center gap-2 px-3 py-2 rounded-lg bg-black/50 text-white text-sm font-medium">
        <Users className="w-5 h-5 shrink-0" />
        <span>{cause.contributers} {t("OPEN_CAUSES_CONTRIBUTERS_LABEL")}</span>
      </div>
    </div>
  );

  const contentBlock = (
    <div className="w-full md:w-[54%] flex flex-col justify-center py-10 md:py-12 px-6 sm:px-8 lg:px-12 xl:px-16">
      <h3 className="font-display text-2xl sm:text-3xl font-bold mb-3 leading-tight">
        <Link
          href={`/cause/${cause.unique_title}`}
          className="text-slate-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 rounded"
        >
          {rawT(cause.title)}
        </Link>
      </h3>
      <p className="text-slate-600 dark:text-slate-400 text-base sm:text-lg leading-relaxed mb-6">
        {rawT(cause.short_description)}
      </p>
      <ProgressBar progress={cause.target_progress} size="md" showLabel={true} />
      <div className="flex items-center justify-between gap-4 mt-3 text-sm">
        <span className="font-bold text-primary-600 dark:text-primary-400 text-base">
          {formatCurrency(cause.paid_so_far, cause.currencies_code)}
        </span>
        <span className="text-slate-500 dark:text-slate-400 text-end">
          {t("OPEN_CAUSES_TARGET_LABEL")} {formatCurrency(cause.target, cause.currencies_code)}
        </span>
      </div>
      <div className="mt-8">
        <Link
          href={`/cause/${cause.unique_title}`}
          className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-display font-semibold py-3.5 px-6 text-base shadow-lg shadow-primary-500/25 hover:shadow-primary-500/35 hover:-translate-y-0.5 transition-all duration-200"
        >
          <Heart className="w-5 h-5" />
          {t("OPEN_CAUSES_DONATE_NOW_LABEL")}
        </Link>
      </div>
    </div>
  );

  return (
    <motion.div
      initial={{ opacity: 0, y: 32 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: "-80px" }}
      transition={{ duration: 0.5, delay: index * 0.1 }}
      className={`group flex flex-col md:flex-row min-h-0 rounded-3xl overflow-hidden bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-xl shadow-slate-200/50 dark:shadow-black/30 hover:shadow-2xl hover:shadow-primary-500/10 dark:hover:shadow-primary-500/15 hover:-translate-y-1 hover:border-primary-200/80 dark:hover:border-primary-800/80 transition-all duration-300 ${!imageOnLeft ? "md:flex-row-reverse" : ""}`}
    >
      {imageBlock}
      {contentBlock}
    </motion.div>
  );
}
