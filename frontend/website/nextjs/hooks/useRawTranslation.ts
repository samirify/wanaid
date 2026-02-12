"use client";

import { useMessages } from "next-intl";

/**
 * Returns a function that looks up translation values directly from
 * the messages object, bypassing next-intl's `t()` function.
 *
 * This is necessary because `t()` rejects values containing HTML tags
 * (INVALID_TAG error). Many API-provided translation keys resolve to
 * rich HTML content (e.g. PAGE_SECTION_10_VALUE).
 *
 * Usage:
 *   const rawT = useRawTranslation();
 *   rawT("PAGE_SECTION_10_VALUE"); // returns the HTML string
 */
export function useRawTranslation() {
  const messages = useMessages() as Record<string, string>;

  return (key: string): string => {
    if (!key) return "";
    return messages[key] ?? "";
  };
}
