"use client";

import { useTranslations } from "next-intl";
import { motion } from "framer-motion";
import { Link } from "@/i18n/navigation";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { ProgressBar } from "@/components/shared/ProgressBar";
import { Heart, Users } from "lucide-react";
import { formatCurrency } from "@/lib/utils";
import type { CauseSummary } from "@/lib/types";

interface CauseCardProps {
  cause: CauseSummary;
  index?: number;
}

export function CauseCard({ cause, index = 0 }: CauseCardProps) {
  const t = useTranslations();
  const rawT = useRawTranslation();

  return (
    <motion.article
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ delay: index * 0.08, duration: 0.4 }}
      className="group flex flex-col h-full rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg shadow-slate-200/50 dark:shadow-black/20 hover:shadow-xl hover:shadow-primary-500/10 dark:hover:shadow-primary-500/10 hover:-translate-y-1 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-300 overflow-hidden"
    >
      <div className="relative h-56 overflow-hidden bg-slate-100 dark:bg-slate-700">
        {cause.img_url ? (
          <>
            <img
              src={cause.img_url}
              alt={rawT(cause.title)}
              width={400}
              height={224}
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              loading="lazy"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
          </>
        ) : (
          <div className="w-full h-full flex items-center justify-center text-slate-400">
            <Heart className="w-12 h-12" />
          </div>
        )}
        <div className="absolute top-3 start-3">
          <span className="px-2 py-1 rounded bg-white/90 dark:bg-slate-900/90 text-slate-800 dark:text-slate-200 text-xs font-semibold">
            {cause.currencies_code}
          </span>
        </div>
        <div className="absolute bottom-3 start-3 flex items-center gap-1.5 text-white text-xs font-medium drop-shadow">
          <Users className="w-3.5 h-3.5" />
          <span>{cause.contributers} {t("OPEN_CAUSES_CONTRIBUTERS_LABEL")}</span>
        </div>
      </div>

      <div className="flex-1 flex flex-col p-5 sm:p-6">
        <h3 className="font-display text-lg font-bold mb-2 line-clamp-2 leading-snug">
          <Link
            href={`/cause/${cause.unique_title}`}
            className="text-slate-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 rounded"
          >
            {rawT(cause.title)}
          </Link>
        </h3>
        <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed line-clamp-3 mb-4 flex-1 min-h-0">
          {rawT(cause.short_description)}
        </p>

        <ProgressBar progress={cause.target_progress} size="sm" />

        <div className="flex justify-between items-center mt-3 text-sm">
          <span className="font-semibold text-primary-600 dark:text-primary-400">
            {formatCurrency(cause.paid_so_far, cause.currencies_code)}
          </span>
          <span className="text-slate-500 dark:text-slate-400">
            {t("OPEN_CAUSES_TARGET_LABEL")} {formatCurrency(cause.target, cause.currencies_code)}
          </span>
        </div>

        <div className="flex gap-3 mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
          <Link
            href={`/cause/${cause.unique_title}`}
            className="flex-1 text-center rounded-xl border-2 border-primary-600 dark:border-primary-500 text-primary-600 dark:text-primary-400 font-display font-semibold py-2.5 text-sm hover:bg-primary-50 dark:hover:bg-primary-950/30 transition-all duration-200"
          >
            {t("OPEN_CAUSES_DONATE_BTN_LABEL")}
          </Link>
          <Link
            href={`/cause/${cause.unique_title}`}
            className="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-display font-semibold py-2.5 text-sm shadow-lg shadow-primary-500/25 hover:shadow-primary-500/30 hover:-translate-y-0.5 transition-all duration-200"
          >
            <Heart className="w-4 h-4" />
            {t("OPEN_CAUSES_DONATE_NOW_LABEL")}
          </Link>
        </div>
      </div>
    </motion.article>
  );
}
