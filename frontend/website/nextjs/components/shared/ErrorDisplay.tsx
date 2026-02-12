"use client";

import { motion } from "framer-motion";
import { AlertTriangle, RefreshCw, Home } from "lucide-react";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { cn } from "@/lib/utils";

interface ErrorDisplayProps {
  title?: string;
  message?: string;
  onRetry?: () => void;
  showHomeButton?: boolean;
  variant?: "inline" | "page" | "toast";
  className?: string;
}

export function ErrorDisplay({
  title,
  message,
  onRetry,
  showHomeButton = false,
  variant = "inline",
  className,
}: ErrorDisplayProps) {
  const t = useTranslations();

  if (variant === "toast") {
    return (
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        exit={{ opacity: 0, y: -20 }}
        className={cn(
          "fixed top-20 start-1/2 -translate-x-1/2 rtl:translate-x-1/2 z-[70]",
          "glass-strong rounded-xl px-6 py-4 max-w-md",
          "border-l-4 rtl:border-l-0 rtl:border-r-4 border-red-500",
          className
        )}
      >
        <div className="flex items-start gap-3">
          <AlertTriangle className="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
          <div>
            {title && (
              <p className="font-semibold text-slate-900 dark:text-white text-sm">
                {title}
              </p>
            )}
            <p className="text-sm text-slate-600 dark:text-slate-400">
              {message || t("WEBSITE_ERRORS_INITIALISATION_FAILED_MESSAGE")}
            </p>
          </div>
        </div>
      </motion.div>
    );
  }

  if (variant === "page") {
    return (
      <div
        className={cn(
          "min-h-[60vh] flex items-center justify-center px-4",
          className
        )}
      >
        <motion.div
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          className="text-center max-w-md"
        >
          <div className="w-20 h-20 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center mx-auto mb-6">
            <AlertTriangle className="w-10 h-10 text-red-500" />
          </div>
          <h2 className="text-2xl font-bold text-slate-900 dark:text-white mb-3">
            {title || t("WEBSITE_ERRORS_SERVER_ERROR_HEADER")}
          </h2>
          <p className="text-slate-600 dark:text-slate-400 mb-8">
            {message || t("WEBSITE_ERRORS_SERVER_ERROR_MESSAGE")}
          </p>
          <div className="flex items-center justify-center gap-3">
            {onRetry && (
              <button onClick={onRetry} className="btn-primary">
                <RefreshCw className="w-4 h-4" />
                Try Again
              </button>
            )}
            {showHomeButton && (
              <Link href="/" className="btn-outline">
                <Home className="w-4 h-4" />
                {t("TOP_NAV_HOME_LABEL")}
              </Link>
            )}
          </div>
        </motion.div>
      </div>
    );
  }

  // Inline variant
  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      className={cn(
        "rounded-xl p-4 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800",
        className
      )}
    >
      <div className="flex items-start gap-3">
        <AlertTriangle className="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
        <div className="flex-1">
          {title && (
            <p className="font-semibold text-red-800 dark:text-red-300 text-sm mb-1">
              {title}
            </p>
          )}
          <p className="text-sm text-red-700 dark:text-red-400">
            {message || t("WEBSITE_ERRORS_INITIALISATION_FAILED_MESSAGE")}
          </p>
          {onRetry && (
            <button
              onClick={onRetry}
              className="mt-3 text-sm text-red-600 dark:text-red-400 hover:underline font-medium flex items-center gap-1"
            >
              <RefreshCw className="w-3 h-3" /> Try again
            </button>
          )}
        </div>
      </div>
    </motion.div>
  );
}
