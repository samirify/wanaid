"use client";

import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { useAppData } from "@/context/AppContext";
import { CauseCard } from "@/components/landing/CauseCard";
import { Loader } from "@/components/shared/Loader";
import { Search } from "lucide-react";

export default function CausesSearchPage() {
  const t = useTranslations();
  const locale = useLocale();
  const { openCauses, isLoading } = useAppData();

  return (
    <>
      {/* Page Hero */}
      <div className="page-hero">
        <div className="page-hero-content">
          <motion.h1
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4"
          >
            {t("CAUSES_SEARCH_HEADER")}
          </motion.h1>
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="text-white/80 text-lg max-w-2xl mx-auto"
          >
            {t("CAUSES_SEARCH_SUB_HEADER")}
          </motion.p>
        </div>
      </div>

      <section className="py-24">
        <div className="container-custom">
          {isLoading ? (
            <Loader />
          ) : openCauses.length === 0 ? (
            <div className="text-center py-12">
              <Search className="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
              <p className="text-slate-500 dark:text-slate-400">
                {t("WEBSITE_NO_RESULTS_LABEL")}
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {openCauses.map((cause, index) => (
                <CauseCard key={cause.id} cause={cause} index={index} />
              ))}
            </div>
          )}
        </div>
      </section>
    </>
  );
}
