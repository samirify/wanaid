"use client";

import {
  createContext,
  useContext,
  useEffect,
  useState,
  useCallback,
  type ReactNode,
} from "react";
import { useLocale } from "next-intl";
import { api } from "@/lib/api";
import type {
  AppData,
  Languages,
  Currencies,
  BlogSummary,
  CauseSummary,
  AppSettings,
  PageContents,
} from "@/lib/types";

const defaultLanguages: Languages = {
  default: {
    country_code: "GB",
    locale: "en",
    name: "English",
    direction: "ltr",
    default: "1",
  },
  countries: { GB: "English" },
  data: {
    en: {
      country_code: "GB",
      locale: "en",
      name: "English",
      direction: "ltr",
      default: "1",
    },
    ar: {
      country_code: "SD",
      locale: "ar",
      name: "العربية",
      direction: "rtl",
      default: "0",
    },
  },
};

const defaultCurrencies: Currencies = {
  available: [{ code: "GBP", name: "Pound sterling", default: "0" }],
  default: { code: "GBP", name: "Pound sterling", default: "0" },
};

const defaultSettings: AppSettings = {
  main_contact_phone_number: "",
  main_contact_address: "",
  google_maps_iframe_url: "",
  main_contact_email: "",
  social_media_facebook: null,
  social_media_instagram: null,
  social_media_linkedin: null,
  social_media_twitter: null,
  social_media_youtube: null,
  static_button_get_started_label: "TOP_NAV_STATIC_BUTTON_GET_STARTED_LABEL",
  static_button_get_started_url: "/cause/help-us",
  static_button_get_started_url_type: "internal",
  static_button_get_started_ga_label: "",
  static_button_get_started_ga_action: "",
  static_button_get_started_ga_category: "",
  static_page_home_title: null,
  static_page_home_meta_keywords: "",
  static_page_home_meta_description: "",
  static_page_open_causes_title: null,
  static_page_open_causes_meta_keywords: "",
  static_page_open_causes_meta_description: "",
  static_page_blog_title: null,
  static_page_blog_meta_keywords: "",
  static_page_blog_meta_description: "",
  static_page_contact_title: null,
  static_page_contact_meta_keywords: "",
  static_page_contact_meta_description: "",
};

const defaultPageContents: PageContents = {
  id: "",
  LANDING: {
    META: { title: null, description: null, keywords: null },
    HEADER: {
      ctas: [],
      main_header_top: "LANDING_MAIN_HEADER_TOP",
      main_header_middle_big: "LANDING_MAIN_HEADER_MIDDLE_BIG",
      main_header_bottom: "LANDING_MAIN_HEADER_BOTTOM",
    },
    PILLARS: [],
  },
};

const defaultAppData: AppData = {
  languages: defaultLanguages,
  currencies: defaultCurrencies,
  blogs: [],
  galleryCount: 0,
  categories: [],
  openCauses: [],
  settings: defaultSettings,
  pageContents: defaultPageContents,
  isInitialized: false,
  isLoading: true,
  error: null,
};

interface AppContextValue extends AppData {
  refetch: () => Promise<void>;
}

const AppContext = createContext<AppContextValue>({
  ...defaultAppData,
  refetch: async () => {},
});

export function AppProvider({ children }: { children: ReactNode }) {
  const locale = useLocale();
  const [data, setData] = useState<AppData>(defaultAppData);

  const fetchData = useCallback(async () => {
    setData((prev) => ({ ...prev, isLoading: true, error: null }));
    try {
      const result = await api.initialize(locale);
      setData({
        languages: result.languages,
        currencies: result.currencies,
        blogs: result.blogs as BlogSummary[],
        galleryCount: result.gallery_count,
        categories: result.categories,
        openCauses: result.open_causes as CauseSummary[],
        settings: result.settings,
        pageContents: result.page_contents,
        isInitialized: true,
        isLoading: false,
        error: null,
      });
    } catch (err) {
      console.error("Failed to initialize app:", err);
      setData((prev) => ({
        ...prev,
        isLoading: false,
        error: "Failed to load data. Please check your connection.",
      }));
    }
  }, [locale]);

  useEffect(() => {
    fetchData();
  }, [fetchData, locale]);

  return (
    <AppContext.Provider value={{ ...data, refetch: fetchData }}>
      {children}
    </AppContext.Provider>
  );
}

export function useAppData(): AppContextValue {
  const context = useContext(AppContext);
  if (!context) {
    throw new Error("useAppData must be used within an AppProvider");
  }
  return context;
}
