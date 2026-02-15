"use client";

import { motion } from "framer-motion";
import { AlertTriangle, RefreshCw, Home } from "lucide-react";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { cn } from "@/lib/utils";
import { FullScreenNotificationToast } from "@/components/shared/FullScreenNotificationToast";

interface ErrorDisplayProps {
  title?: string;
  message?: string;
  errorCode?: string;
  onRetry?: () => void;
  showHomeButton?: boolean;
  variant?: "inline" | "page" | "toast";
  /** For variant="toast": when false, toast animates out. Default true. */
  visible?: boolean;
  /** For variant="toast": called when the user closes the toast. */
  onClose?: () => void;
  className?: string;
}

export function ErrorDisplay({
  title,
  message,
  errorCode,
  onRetry,
  showHomeButton = false,
  variant = "inline",
  visible = true,
  onClose,
  className,
}: ErrorDisplayProps) {
  const t = useTranslations();

  if (variant === "toast") {
    return (
      <FullScreenNotificationToast
        variant="error"
        title={title}
        message={message || t("WEBSITE_ERRORS_INITIALISATION_FAILED_MESSAGE")}
        visible={visible}
        onClose={onClose}
      />
    );
  }

  if (variant === "page") {
    const code = errorCode || "500";

    return (
      <div className={cn("relative", className)}>
        {/* Full-height dark section with creative design */}
        <div className="relative min-h-[80vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 dark:from-primary-900 dark:via-slate-900 dark:to-slate-900">

          {/* Animated background shapes */}
          <div className="absolute inset-0 pointer-events-none overflow-hidden">
            {/* Giant error code watermark */}
            <motion.div
              initial={{ opacity: 0, scale: 0.5 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ duration: 1.2, ease: "easeOut" }}
              className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
            >
              <span className="text-[16rem] sm:text-[22rem] lg:text-[30rem] font-black leading-none text-white/[0.03] select-none">
                {code}
              </span>
            </motion.div>

            {/* Floating orbs */}
            <motion.div
              animate={{ y: [-20, 20, -20], x: [-10, 10, -10] }}
              transition={{ duration: 8, repeat: Infinity, ease: "easeInOut" }}
              className="absolute top-[15%] start-[10%] w-72 h-72 rounded-full bg-white/5 blur-3xl"
            />
            <motion.div
              animate={{ y: [20, -20, 20], x: [10, -10, 10] }}
              transition={{ duration: 10, repeat: Infinity, ease: "easeInOut" }}
              className="absolute bottom-[15%] end-[10%] w-96 h-96 rounded-full bg-primary-400/10 blur-3xl"
            />
            <motion.div
              animate={{ y: [15, -15, 15] }}
              transition={{ duration: 6, repeat: Infinity, ease: "easeInOut" }}
              className="absolute top-[40%] end-[20%] w-48 h-48 rounded-full bg-accent-500/10 blur-2xl"
            />

            {/* Diagonal decorative lines */}
            <div className="absolute inset-0 opacity-[0.03]" style={{
              backgroundImage: `repeating-linear-gradient(
                -45deg,
                transparent,
                transparent 80px,
                white 80px,
                white 81px
              )`,
            }} />
          </div>

          {/* Content */}
          <div className="relative z-10 text-center px-6 max-w-3xl mx-auto">
            {/* Error code badge */}
            <motion.div
              initial={{ opacity: 0, scale: 0.8 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ type: "spring", stiffness: 200, damping: 15 }}
              className="mb-8 inline-block"
            >
              <div className="relative">
                {/* Glow ring */}
                <div className="absolute -inset-4 rounded-full bg-white/5 blur-xl" />
                <div className="relative w-28 h-28 sm:w-32 sm:h-32 rounded-full border-2 border-white/20 flex items-center justify-center backdrop-blur-sm bg-white/5">
                  <span className="text-4xl sm:text-5xl font-black text-white/90">
                    {code}
                  </span>
                </div>
              </div>
            </motion.div>

            {/* Title */}
            <motion.h1
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.15, duration: 0.6 }}
              className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-5 leading-tight"
            >
              {title || t("WEBSITE_ERRORS_SERVER_ERROR_HEADER")}
            </motion.h1>

            {/* Decorative divider */}
            <motion.div
              initial={{ scaleX: 0 }}
              animate={{ scaleX: 1 }}
              transition={{ delay: 0.3, duration: 0.5 }}
              className="w-20 h-1 bg-gradient-to-r from-primary-300 to-accent-400 rounded-full mx-auto mb-6"
            />

            {/* Description */}
            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.35 }}
              className="text-white/70 text-lg sm:text-xl max-w-xl mx-auto mb-10 leading-relaxed"
            >
              {message || t("WEBSITE_ERRORS_SERVER_ERROR_MESSAGE")}
            </motion.p>

            {/* Buttons */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.45 }}
              className="flex flex-wrap items-center justify-center gap-4"
            >
              {onRetry && (
                <button
                  onClick={onRetry}
                  className="group inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/20 text-white font-medium transition-all duration-300 hover:scale-105"
                >
                  <RefreshCw className="w-4 h-4 group-hover:animate-spin" />
                </button>
              )}
              {showHomeButton && (
                <Link
                  href="/"
                  className="group inline-flex items-center gap-2.5 px-8 py-3.5 rounded-full bg-white text-primary-700 font-semibold transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-white/20"
                >
                  <Home className="w-4 h-4" />
                  {t("TOP_NAV_HOME_LABEL")}
                </Link>
              )}
            </motion.div>
          </div>
        </div>
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
              <RefreshCw className="w-3 h-3" />
            </button>
          )}
        </div>
      </div>
    </motion.div>
  );
}
