import type { CookiePreferences } from "./types";

const CONSENT_KEY = "charity-cookie-consent";
const CONSENT_DATE_KEY = "charity-cookie-consent-date";
const CONSENT_EXPIRY_DAYS = 365;

/**
 * Default cookie preferences – only essentials enabled.
 */
export const defaultPreferences: CookiePreferences = {
  essential: true,
  analytics: false,
  functional: false,
};

/**
 * Retrieve stored cookie preferences.
 */
export function getCookieConsent(): CookiePreferences | null {
  if (typeof window === "undefined") return null;

  try {
    const stored = localStorage.getItem(CONSENT_KEY);
    if (!stored) return null;
    return JSON.parse(stored) as CookiePreferences;
  } catch {
    return null;
  }
}

/**
 * Save cookie preferences to localStorage.
 */
export function setCookieConsent(preferences: CookiePreferences): void {
  if (typeof window === "undefined") return;

  // Essential cookies are always enabled
  const normalized: CookiePreferences = {
    ...preferences,
    essential: true,
  };

  localStorage.setItem(CONSENT_KEY, JSON.stringify(normalized));
  localStorage.setItem(CONSENT_DATE_KEY, new Date().toISOString());

  // Dispatch event so other components/tabs can react
  window.dispatchEvent(
    new CustomEvent("cookieConsentUpdated", { detail: normalized })
  );
}

/**
 * Check whether the user has already given consent.
 */
export function hasGivenConsent(): boolean {
  return getCookieConsent() !== null && !isConsentExpired();
}

/**
 * Check if consent has expired.
 */
export function isConsentExpired(): boolean {
  if (typeof window === "undefined") return true;

  const dateStr = localStorage.getItem(CONSENT_DATE_KEY);
  if (!dateStr) return true;

  const consentDate = new Date(dateStr);
  const now = new Date();
  const diffDays =
    (now.getTime() - consentDate.getTime()) / (1000 * 60 * 60 * 24);
  return diffDays > CONSENT_EXPIRY_DAYS;
}

/**
 * Check if a specific cookie category is allowed.
 */
export function hasConsentFor(
  category: keyof CookiePreferences
): boolean {
  const consent = getCookieConsent();
  if (!consent) return category === "essential";
  return consent[category] ?? false;
}

/**
 * Accept all cookies.
 */
export function acceptAll(): void {
  setCookieConsent({
    essential: true,
    analytics: true,
    functional: true,
  });
}

/**
 * Accept only essential cookies.
 */
export function acceptEssentialOnly(): void {
  setCookieConsent(defaultPreferences);
}

/**
 * Remove all consent (for testing / reset).
 */
export function clearConsent(): void {
  if (typeof window === "undefined") return;
  localStorage.removeItem(CONSENT_KEY);
  localStorage.removeItem(CONSENT_DATE_KEY);
}
