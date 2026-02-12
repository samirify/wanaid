"use client";

import { useState, useEffect, useCallback } from "react";
import type { CookiePreferences } from "@/lib/types";
import {
  getCookieConsent,
  setCookieConsent,
  hasGivenConsent,
  defaultPreferences,
} from "@/lib/cookieConsent";

export function useCookieConsent() {
  const [consent, setConsent] = useState<CookiePreferences>(defaultPreferences);
  const [showBanner, setShowBanner] = useState(false);

  useEffect(() => {
    const stored = getCookieConsent();
    if (stored) {
      setConsent(stored);
      setShowBanner(false);
    } else {
      setShowBanner(true);
    }

    // Listen for cross-tab updates
    const handleStorage = (e: StorageEvent) => {
      if (e.key === "charity-cookie-consent") {
        const updated = getCookieConsent();
        if (updated) {
          setConsent(updated);
          setShowBanner(false);
        }
      }
    };

    // Listen for same-tab updates
    const handleConsentUpdate = (e: Event) => {
      const customEvent = e as CustomEvent<CookiePreferences>;
      setConsent(customEvent.detail);
      setShowBanner(false);
    };

    window.addEventListener("storage", handleStorage);
    window.addEventListener("cookieConsentUpdated", handleConsentUpdate);

    return () => {
      window.removeEventListener("storage", handleStorage);
      window.removeEventListener("cookieConsentUpdated", handleConsentUpdate);
    };
  }, []);

  const updateConsent = useCallback((prefs: CookiePreferences) => {
    setCookieConsent(prefs);
    setConsent(prefs);
    setShowBanner(false);
  }, []);

  const shouldShowBanner = useCallback(() => {
    return !hasGivenConsent();
  }, []);

  return {
    consent,
    showBanner,
    setShowBanner,
    updateConsent,
    shouldShowBanner,
    canUseAnalytics: consent.analytics,
    canUseFunctional: consent.functional,
  };
}
