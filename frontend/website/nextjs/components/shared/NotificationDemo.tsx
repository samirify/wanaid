"use client";

import { useState, useEffect, useCallback } from "react";
import { useTranslations } from "next-intl";
import { AnimatePresence, motion } from "framer-motion";
import { Bell, X } from "lucide-react";
import { DonationSuccessToast } from "@/components/shared/DonationSuccessToast";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";

type DemoNotificationType = null | "donation-success" | "error-toast";

const AUTO_DISMISS_MS = 8000;

/**
 * Temporary floating button to demo all on-screen notifications.
 * Remove this component (and its import in layout) when no longer needed.
 */
export function NotificationDemo() {
  const t = useTranslations();
  const [panelOpen, setPanelOpen] = useState(false);
  const [activeNotification, setActiveNotification] =
    useState<DemoNotificationType>(null);

  const showNotification = useCallback((type: DemoNotificationType) => {
    setActiveNotification(type);
    setPanelOpen(false);
  }, []);

  useEffect(() => {
    if (activeNotification === null) return;
    const timer = setTimeout(() => setActiveNotification(null), AUTO_DISMISS_MS);
    return () => clearTimeout(timer);
  }, [activeNotification]);

  const donationMessage =
    t("WEBSITE_CONTACT_WRITE_TO_FORM_THANK_YOU_LABEL") ||
    "Thank you for your donation!";

  return (
    <>
      {/* Floating demo button */}
      <div className="fixed bottom-24 start-6 z-[65] rtl:start-auto rtl:end-6">
        <button
          type="button"
          onClick={() => setPanelOpen((o) => !o)}
          className="flex items-center gap-2 px-4 py-3 rounded-2xl bg-primary-600 text-white shadow-lg shadow-primary-600/30 hover:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-slate-900 text-sm font-semibold"
          aria-label="Demo notifications"
        >
          <Bell className="w-4 h-4" />
          Demo notifications
        </button>

        {/* Panel with notification list */}
        <AnimatePresence>
          {panelOpen && (
            <>
              <div
                className="fixed inset-0 z-[66]"
                aria-hidden
                onClick={() => setPanelOpen(false)}
              />
              <motion.div
                initial={{ opacity: 0, y: 8 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: 8 }}
                transition={{ duration: 0.2 }}
                className="absolute bottom-full start-0 rtl:start-auto rtl:end-0 mb-2 w-64 rounded-2xl glass-strong border border-slate-200 dark:border-slate-700 shadow-xl z-[67] overflow-hidden"
              >
                <div className="p-2 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                  <span className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Show notification
                  </span>
                  <button
                    type="button"
                    onClick={() => setPanelOpen(false)}
                    className="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500"
                    aria-label="Close"
                  >
                    <X className="w-4 h-4" />
                  </button>
                </div>
                <div className="p-2 space-y-1">
                  <button
                    type="button"
                    onClick={() => showNotification("donation-success")}
                    className="w-full text-left px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
                  >
                    Donation thank you
                  </button>
                  <button
                    type="button"
                    onClick={() => showNotification("error-toast")}
                    className="w-full text-left px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                  >
                    Error toast
                  </button>
                </div>
              </motion.div>
            </>
          )}
        </AnimatePresence>
      </div>

      {/* Rendered notifications (same as real ones) */}
      <DonationSuccessToast
        message={donationMessage}
        visible={activeNotification === "donation-success"}
        onClose={() => setActiveNotification(null)}
      />

      <ErrorDisplay
        variant="toast"
        visible={activeNotification === "error-toast"}
        onClose={() => setActiveNotification(null)}
        title={t("WEBSITE_ERROR_LABEL") || "Oops! An error occurred"}
        message={
          t("WEBSITE_ERRORS_INITIALISATION_FAILED_MESSAGE") ||
          "Something went wrong. Please try again."
        }
      />
    </>
  );
}
