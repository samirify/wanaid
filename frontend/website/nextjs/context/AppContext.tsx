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
import type {
  AppData,
  Languages,
  Currencies,
  BlogSummary,
  CauseSummary,
  AppSettings,
  PageContents,
  ClientIdentity,
  AppNavigation,
  AppTheme,
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

/** Initial data from server; matches new backend initialize response shape */
export type InitialAppData = {
  languages: Languages;
  currencies: Currencies;
  available_modules?: unknown[];
  identity?: ClientIdentity;
  settings: AppSettings;
  page_contents: PageContents;
  navigation?: AppNavigation;
  theme?: AppTheme;
  blogs?: BlogSummary[];
  gallery_count?: number;
  categories?: unknown[];
  open_causes?: CauseSummary[];
};

/** Normalize page_contents: new backend uses `main`, components expect `LANDING`. */
function normalizePageContents(pc: PageContents): PageContents {
  if (!pc) return pc;
  const landing = pc.LANDING ?? pc.main;
  if (!landing) return pc;
  return { ...pc, LANDING: landing };
}

function toAppData(init: InitialAppData): AppData {
  return {
    languages: init.languages,
    currencies: init.currencies,
    available_modules: init.available_modules,
    identity: init.identity,
    settings: init.settings,
    pageContents: normalizePageContents(init.page_contents),
    navigation: init.navigation,
    theme: init.theme,
    blogs: init.blogs ?? [],
    galleryCount: init.gallery_count ?? 0,
    categories: init.categories ?? [],
    openCauses: init.open_causes ?? [],
    isInitialized: true,
    isLoading: false,
    error: null,
  };
}

export function AppProvider({
  children,
  initialData,
}: {
  children: ReactNode;
  initialData?: InitialAppData | null;
}) {
  const locale = useLocale();
  const [data, setData] = useState<AppData>(() =>
    initialData ? toAppData(initialData) : defaultAppData
  );

  const fetchData = useCallback(async () => {
    setData((prev) => ({ ...prev, isLoading: true, error: null }));
    try {
      const res = await fetch("/api/initialize", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(locale ? { locale } : {}),
      });
      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err?.error || `Request failed (${res.status})`);
      }
      const result = (await res.json()) as InitialAppData;
      setData(toAppData(result));
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
    if (initialData) {
      setData(toAppData(initialData));
    } else {
      fetchData();
    }
  }, [locale, initialData, fetchData]);

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
