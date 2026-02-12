"use client";

import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { Link } from "@/i18n/navigation";
import { Home, RefreshCw } from "lucide-react";

export default function ServerErrorPage() {
  const t = useTranslations();
  const locale = useLocale();

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 px-4">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="text-center max-w-md"
      >
        <motion.div
          initial={{ scale: 0.5, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          transition={{ type: "spring", damping: 15 }}
          className="mb-8"
        >
          <span className="text-9xl font-black gradient-text">500</span>
        </motion.div>

        <h1 className="text-3xl font-bold text-slate-900 dark:text-white mb-4">
          {t("WEBSITE_ERRORS_SERVER_ERROR_HEADER")}
        </h1>
        <p className="text-slate-600 dark:text-slate-400 mb-8 text-lg">
          {t("WEBSITE_ERRORS_SERVER_ERROR_MESSAGE")}
        </p>

        <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
          <Link href="/" className="btn-primary">
            <Home className="w-4 h-4" />
            {t("TOP_NAV_HOME_LABEL")}
          </Link>
          <button
            onClick={() => window.location.reload()}
            className="btn-outline"
          >
            <RefreshCw className="w-4 h-4" />
            Try Again
          </button>
        </div>
      </motion.div>
    </div>
  );
}
