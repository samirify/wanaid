"use client";

import { motion, AnimatePresence } from "framer-motion";
import { CheckCircle, AlertTriangle, X } from "lucide-react";

const LINE_STYLES = {
  success: {
    background:
      "linear-gradient(to right, transparent 0%, rgb(34 197 94) 30%, rgb(74 222 128) 50%, rgb(34 197 94) 70%, transparent 100%)",
  },
  error: {
    background:
      "linear-gradient(to right, transparent 0%, rgb(185 28 28) 30%, rgb(239 68 68) 50%, rgb(185 28 28) 70%, transparent 100%)",
  },
} as const;

export interface FullScreenNotificationToastProps {
  variant: "success" | "error";
  message: string;
  title?: string;
  visible: boolean;
  /** When provided, a close button is shown and this is called when it (or backdrop) is clicked */
  onClose?: () => void;
}

export function FullScreenNotificationToast({
  variant,
  message,
  title,
  visible,
  onClose,
}: FullScreenNotificationToastProps) {
  const lineStyle = LINE_STYLES[variant];
  const isSuccess = variant === "success";

  return (
    <AnimatePresence>
      {visible && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.2 }}
          className="fixed inset-0 z-[70] flex items-center justify-center"
        >
          {onClose ? (
            <button
              type="button"
              onClick={onClose}
              className="absolute inset-0 z-0 bg-black/50 dark:bg-black/60 cursor-pointer"
              aria-label="Close"
            />
          ) : (
            <div className="absolute inset-0 z-0 bg-black/50 dark:bg-black/60" aria-hidden />
          )}
          <motion.div
            role="dialog"
            aria-live="polite"
            initial={{ opacity: 0, y: 120 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: 80 }}
            transition={{
              type: "spring",
              damping: 22,
              stiffness: 280,
              mass: 0.8,
            }}
            className="relative z-10 w-full flex flex-col gap-0 pointer-events-auto"
          >
            <motion.div
              initial={{ scaleX: 0 }}
              animate={{ scaleX: 1 }}
              exit={{ scaleX: 0 }}
              transition={{ type: "spring", damping: 24, stiffness: 320, delay: 0.05 }}
              className="h-0.5 w-full origin-center shrink-0"
              style={lineStyle}
              aria-hidden
            />
            <motion.div
              initial={{ y: 16 }}
              animate={{ y: 0 }}
              exit={{ opacity: 0 }}
              transition={{ type: "spring", damping: 22, stiffness: 260, delay: 0.08 }}
              className="relative w-full bg-white dark:bg-[#0c0c0c] py-6 px-4 flex items-center justify-center gap-4 shadow-2xl"
            >
              {/* Close + notification icon grouped (same size, right next to each other); strip stays centered */}
              <div className="flex items-center gap-2 shrink-0">
                {onClose && (
                  <button
                    type="button"
                    onClick={onClose}
                    className="w-12 h-12 rounded-full flex items-center justify-center border-2 border-slate-200 dark:border-slate-600 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-500 hover:bg-white dark:hover:bg-slate-700/80 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-slate-900"
                    aria-label="Close"
                  >
                    <X className="w-6 h-6" strokeWidth={2.5} />
                  </button>
                )}
                {isSuccess ? (
                  <div className="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                    <CheckCircle className="w-7 h-7 text-green-600 dark:text-green-400" strokeWidth={2.5} />
                  </div>
                ) : (
                  <div className="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                    <AlertTriangle className="w-7 h-7 text-red-600 dark:text-red-500" strokeWidth={2.5} />
                  </div>
                )}
              </div>
              <div className="flex flex-col items-start text-start gap-0.5 min-w-0">
                {title && !isSuccess && (
                  <p className="font-semibold text-base sm:text-lg text-slate-900 dark:text-white">
                    {title}
                  </p>
                )}
                <p className="font-semibold text-base sm:text-lg leading-snug text-slate-900 dark:text-white">
                  {message}
                </p>
              </div>
            </motion.div>
            <motion.div
              initial={{ scaleX: 0 }}
              animate={{ scaleX: 1 }}
              exit={{ scaleX: 0 }}
              transition={{ type: "spring", damping: 24, stiffness: 320, delay: 0.05 }}
              className="h-0.5 w-full origin-center shrink-0"
              style={lineStyle}
              aria-hidden
            />
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
