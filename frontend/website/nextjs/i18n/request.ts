import { getRequestConfig } from "next-intl/server";
import { routing } from "./routing";

export default getRequestConfig(async ({ requestLocale }) => {
  let locale = await requestLocale;

  if (!locale || !routing.locales.includes(locale as "en" | "ar")) {
    locale = routing.defaultLocale;
  }

  let messages: Record<string, string> = {};

  try {
    const translationsUrl =
      process.env.TRANSLATIONS_URL || "https://www.wanaid.org";
    const url = `${translationsUrl}/translations/${locale}.json?_t=${Date.now()}`;

    const response = await fetch(url, { cache: "no-cache" });

    if (!response.ok) {
      throw new Error(`Translations fetch returned ${response.status}`);
    }

    const data = await response.json();

    if (data && typeof data === "object") {
      messages = data;
    }
  } catch (error) {
    console.error("Translation fetch failed:", error);
    messages = {
      WEBSITE_ERROR_LABEL: "Opps! An error occurred",
      WEBSITE_ERRORS_INITIALISATION_FAILED_MESSAGE:
        "Sorry! The website failed to initialise properly! Please check your internet connection and refresh the page",
    };
  }

  return {
    locale,
    messages,
    onError(error: { code: string }) {
      if (error.code === "MISSING_MESSAGE") return;
      console.error(error);
    },
    getMessageFallback({ key }: { key: string; namespace?: string }) {
      return key;
    },
  };
});
