"use client";

import { useTranslations } from "next-intl";
import { motion, AnimatePresence } from "framer-motion";
import { Shield } from "lucide-react";
import { Link } from "@/i18n/navigation";
import { useCookieConsent } from "@/hooks/useCookieConsent";
import { acceptAll } from "@/lib/cookieConsent";

export function CookieConsentBanner() {
  const t = useTranslations();
  const { showBanner, updateConsent } = useCookieConsent();

  const handleAccept = () => {
    acceptAll();
    updateConsent({ essential: true, analytics: true, functional: true });
  };

  return (
    <AnimatePresence>
      {showBanner && (
        <motion.div
          initial={{ y: "100%", opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          exit={{ y: "100%", opacity: 0 }}
          transition={{ type: "spring", damping: 25, stiffness: 200 }}
          className="fixed bottom-0 start-0 end-0 z-[60] p-4"
        >
          <div className="container-custom">
            <div className="glass-strong rounded-2xl overflow-hidden border-t-2 border-primary-500">
              <div className="p-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                  <div className="flex-1 flex items-center gap-3">
                    <div className="p-2 rounded-xl bg-primary-100 dark:bg-primary-900/30 shrink-0">
                      <Shield className="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <p className="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                      {t("WEBSITE_COOKIE_ALERT_MESSAGE")}{" "}
                      <Link
                        href="/privacy-policy"
                        className="text-primary-600 dark:text-primary-400 hover:underline font-medium"
                      >
                        {t("WEBSITE_COOKIE_ALERT_MESSAGE_PRIVACY_POLICY_LABEL")}
                      </Link>
                    </p>
                  </div>

                  <button
                    onClick={handleAccept}
                    className="btn-primary text-sm px-6 py-2.5 shrink-0"
                  >
                    {t("WEBSITE_COOKIE_ALERT_MESSAGE_BTN_LABEL")}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
