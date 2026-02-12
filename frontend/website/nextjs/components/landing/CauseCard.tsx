"use client";

import { useTranslations, useLocale } from "next-intl";
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
  const locale = useLocale();

  return (
    <motion.div
      initial={{ opacity: 0, y: 30 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ delay: index * 0.1, duration: 0.5 }}
      className="group card-elevated overflow-hidden"
    >
      {/* Image — native img for external API URLs */}
      <div className="relative h-56 overflow-hidden bg-slate-100 dark:bg-slate-700">
        {cause.img_url ? (
          <img
            src={cause.img_url}
            alt={rawT(cause.title)}
            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            loading="lazy"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center text-slate-400">
            <Heart className="w-12 h-12" />
          </div>
        )}
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />

        {/* Badge */}
        <div className="absolute top-4 start-4">
          <span className="badge bg-accent-500 text-white shadow-lg">
            {cause.currencies_code}
          </span>
        </div>

        {/* Supporters count */}
        <div className="absolute bottom-4 start-4 flex items-center gap-2 text-white/90 text-sm">
          <Users className="w-4 h-4" />
          <span>
            {cause.contributers} {t("OPEN_CAUSES_CONTRIBUTERS_LABEL")}
          </span>
        </div>
      </div>

      {/* Content */}
      <div className="p-6">
        <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
          {rawT(cause.title)}
        </h3>

        <p className="text-sm text-slate-600 dark:text-slate-400 mb-4 line-clamp-2">
          {rawT(cause.short_description)}
        </p>

        {/* Progress */}
        <ProgressBar progress={cause.target_progress} size="sm" />

        {/* Stats */}
        <div className="flex items-center justify-between mt-4 text-sm">
          <div>
            <span className="font-bold text-primary-600 dark:text-primary-400">
              {formatCurrency(cause.paid_so_far, cause.currencies_code)}
            </span>
            <span className="text-slate-500 dark:text-slate-400 ms-1">
              {t("OPEN_CAUSES_RAISED_LABEL")}
            </span>
          </div>
          <div className="text-slate-500 dark:text-slate-400">
            {t("OPEN_CAUSES_TARGET_LABEL")}:{" "}
            <span className="font-semibold text-slate-700 dark:text-slate-300">
              {formatCurrency(cause.target, cause.currencies_code)}
            </span>
          </div>
        </div>

        {/* Actions */}
        <div className="flex items-center gap-3 mt-5 pt-5 border-t border-slate-100 dark:border-slate-700">
          <Link
            href={`/cause/${cause.unique_title}`}
            className="flex-1 btn-outline text-sm py-2"
          >
            {t("OPEN_CAUSES_DONATE_BTN_LABEL")}
          </Link>
          <Link
            href={`/cause/${cause.unique_title}`}
            className="btn-accent text-sm py-2 px-4"
          >
            <Heart className="w-4 h-4" />
            {t("OPEN_CAUSES_DONATE_NOW_LABEL")}
          </Link>
        </div>
      </div>
    </motion.div>
  );
}
