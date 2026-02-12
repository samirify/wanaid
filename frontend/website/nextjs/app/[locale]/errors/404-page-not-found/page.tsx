"use client";

import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { Link } from "@/i18n/navigation";
import { Home, Search } from "lucide-react";

export default function NotFoundPage() {
  const t = useTranslations();
  const locale = useLocale();

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 px-4">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="text-center max-w-md"
      >
        {/* Animated 404 */}
        <motion.div
          initial={{ scale: 0.5, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          transition={{ type: "spring", damping: 15 }}
          className="mb-8"
        >
          <span className="text-9xl font-black gradient-text">404</span>
        </motion.div>

        <h1 className="text-3xl font-bold text-slate-900 dark:text-white mb-4">
          {t("WEBSITE_ERRORS_PAGE_NOT_FOUND_HEADER")}
        </h1>
        <p className="text-slate-600 dark:text-slate-400 mb-8 text-lg">
          {t("WEBSITE_ERRORS_PAGE_NOT_FOUND_MESSAGE")}
        </p>

        <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
          <Link href="/" className="btn-primary">
            <Home className="w-4 h-4" />
            {t("TOP_NAV_HOME_LABEL")}
          </Link>
          <Link href="/causes/search" className="btn-outline">
            <Search className="w-4 h-4" />
            {t("TOP_NAV_CAUSES_LABEL")}
          </Link>
        </div>
      </motion.div>
    </div>
  );
}
