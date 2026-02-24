/* ───────────────────────── Language & Localization ───────────────────────── */

export interface LanguageInfo {
  id?: number;
  country_code: string;
  locale: string;
  name: string;
  direction: "ltr" | "rtl";
  default: string | number;
}

export interface Languages {
  default: LanguageInfo;
  countries: Record<string, string>;
  data: Record<string, LanguageInfo>;
}

/* ───────────────────────────── Currency ──────────────────────────────────── */

export interface Currency {
  id?: number;
  code: string;
  name: string;
  default: string | number;
}

export interface Currencies {
  available: Currency[];
  default: Currency;
}

/* ─────────────────────────── Blog ────────────────────────────────────────── */

export interface BlogSummary {
  id: string;
  unique_title: string;
  title: string;
  page_title: string;
  short_description: string;
  filter: string[];
  img_url: string;
}

export interface BlogDetail {
  id: string;
  unique_title: string;
  page_title: string;
  title: string;
  short_description: string;
  body: string;
  author: string;
  active: string;
  published_at: string;
  last_modified: string;
  media_store_id: string;
  mime_type: string;
  header_media_store_id: string;
  header_mime_type: string;
  pages_id: string;
  pages_code: string;
  created_at: string;
  updated_at: string;
  img_url: string;
  header_img_url: string;
}

/* ─────────────────────────── Cause ───────────────────────────────────────── */

export interface CauseSummary {
  id: string;
  unique_title: string;
  title: string;
  target: number;
  currencies_id: string;
  currencies_code: string;
  page_title: string;
  short_description: string;
  order: string;
  filter: string[];
  img_url: string;
  contributers: number;
  paid_so_far: number;
  target_progress: number;
}

export interface CauseDetail {
  id: string;
  unique_title: string;
  page_title: string;
  title: string;
  target: string;
  currencies_id: string;
  currencies_code: string;
  short_description: string;
  body: string;
  order: string;
  author: string;
  active: string;
  published_at: string;
  last_modified: string;
  media_store_id: string;
  mime_type: string;
  header_media_store_id: string;
  header_mime_type: string;
  pages_id: string;
  pages_code: string;
  created_at: string;
  updated_at: string;
  img_url: string;
  header_img_url: string;
}

/* ─────────────────────────── Team ────────────────────────────────────────── */

export interface TeamMember {
  id: string;
  unique_title: string;
  full_name: string;
  position: string;
  short_description: string;
  body?: string;
  img_url: string;
  media_store_id?: string;
  mime_type?: string;
  facebook_url: string | null;
  twitter_url: string | null;
  linkedin_url: string | null;
  instagram_url: string | null;
  youtube_url: string | null;
  department_unique_title?: string;
  department_name?: string;
  department_sub_header?: string;
}

export interface TeamDepartment {
  unique_title: string;
  team: {
    title: string;
    sub_header: string;
    members: TeamMember[];
  };
}

/* ─────────────────────────── Gallery ─────────────────────────────────────── */

export interface GalleryItem {
  id: string;
  img_url: string;
  title?: string;
  description?: string;
  /** When "video", item is a YouTube (or similar) video; lightbox shows embed. */
  type?: "image" | "video";
  /** YouTube embed URL, or watch URL (will be normalized to embed). API may send embedUrl. */
  video_url?: string | null;
  embed_url?: string | null;
  /** Legacy: same as embed_url (original React gallery used this). */
  embedUrl?: string | null;
}

/* ─────────────────────────── Settings ────────────────────────────────────── */

/** New backend: social_media is array; main_contacts has emails/addresses/phones. Legacy flat fields still supported. */
export interface AppSettings {
  google_maps_iframe_url?: string;
  organisation?: string;
  social_media?: Array<{ code: string; url: string }>;
  main_contacts?: {
    emails?: Array<{ id: number; value: string }>;
    addresses?: Array<{ id: number; value: string }>;
    phones?: Array<{ id: number; value: string }>;
  };
  main_contact_phone_number?: string;
  main_contact_address?: string;
  main_contact_email?: string;
  social_media_facebook?: string | null;
  social_media_instagram?: string | null;
  social_media_linkedin?: string | null;
  social_media_twitter?: string | null;
  social_media_youtube?: string | null;
  static_button_get_started_label?: string;
  static_button_get_started_url?: string;
  static_button_get_started_url_type?: string;
  static_button_get_started_ga_label?: string;
  static_button_get_started_ga_action?: string;
  static_button_get_started_ga_category?: string;
  static_page_home_title?: string | null;
  static_page_home_meta_keywords?: string;
  static_page_home_meta_description?: string;
  static_page_open_causes_title?: string | null;
  static_page_open_causes_meta_keywords?: string;
  static_page_open_causes_meta_description?: string;
  static_page_blog_title?: string | null;
  static_page_blog_meta_keywords?: string;
  static_page_blog_meta_description?: string;
  static_page_contact_title?: string | null;
  static_page_contact_meta_keywords?: string;
  static_page_contact_meta_description?: string;
  [key: string]: unknown;
}

/* ───────────────────── Page Contents / Headers ──────────────────────────── */

export interface HeaderCta {
  id: number | string;
  name?: string;
  label: string;
  url?: string;
  url_id?: number | null;
  url_type: "internal" | "external";
  style: "dark" | "light";
  order: number | string;
  active: number | string;
  ga_actions_id?: string | null;
  ga_label?: string | null;
  ga_action?: string | null;
  ga_category?: string | null;
}

export interface PageHeaders {
  size_code?: string;
  size_name?: string;
  ctas: HeaderCta[];
  main_header_top: string;
  main_header_middle_big: string;
  main_header_bottom?: string | null;
  /** Optional hero image URL (from CMS or relative path e.g. /images/hero.jpg) */
  main_header_img?: string | null;
}

export interface Pillar {
  code: string;
  value: string;
  img: string;
  cta: boolean | string;
}

/** New backend uses `main`; legacy uses `LANDING`. */
export interface PageContents {
  id: string | number;
  main?: {
    META?: {
      title: string | null;
      description: string | null;
      keywords: string | null;
    };
    HEADER?: PageHeaders;
    PILLARS?: Pillar[];
  };
  LANDING?: {
    META?: { title: string | null; description: string | null; keywords: string | null };
    HEADER?: PageHeaders;
    PILLARS?: Pillar[];
  };
}

/* ─────────────────────── Identity & Theme (new backend) ──────────────────── */

export interface ClientIdentity {
  name: string;
  slogan: string;
  short_description: string;
}

export interface ThemeColors {
  name: string;
  red: string;
  green: string;
  blue: string;
  hex: string;
}

export interface ThemeLogos {
  logo_coloured_light?: string;
  logo_coloured_dark?: string;
}

export interface AppTheme {
  colors?: {
    primary: ThemeColors;
    secondary: ThemeColors;
  };
  logos?: ThemeLogos;
}

export interface NavItem {
  key: string;
  label: string;
  pathId?: number | null;
  nodeStyle: string;
  path?: string;
  pathLocation?: string;
  children?: NavItem[];
  translations?: Record<string, Record<string, string>>;
}

export interface NavSection {
  code: string;
  name: string;
  items: NavItem[];
}

export interface AppNavigation {
  top?: NavSection;
  "top-right"?: NavSection;
  footer?: NavSection;
  [key: string]: NavSection | undefined;
}

/* ─────────────────────── Meta ────────────────────────────────────────────── */

export interface MetaData {
  title?: string;
  description?: string;
  keywords?: string;
}

/* ─────────────────────── API Responses ───────────────────────────────────── */

export interface InitializeResponse {
  data: {
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
  user?: {
    access_token: string;
    user_id: string;
    user_username: string;
    user_full_name: string;
    user_img: string;
    session_expires_at: string;
  } | null;
}

export interface AboutPageResponse {
  data: {
    main_header_img: string;
    meta: MetaData;
    headers: PageHeaders;
    pillars: Pillar[];
    teams: TeamDepartment[];
  };
}

export interface CauseDetailResponse {
  success: boolean;
  message: string;
  cause: CauseDetail;
  headers: PageHeaders;
  pillars: Pillar[];
}

export interface BlogDetailResponse {
  success: boolean;
  message: string;
  blog: BlogDetail;
  headers: PageHeaders;
  pillars: Pillar[];
}

export interface TeamMemberResponse {
  success: boolean;
  teamMember: TeamMember;
  meta: MetaData;
}

export interface GenericPageResponse {
  data: {
    main_header_img?: string;
    meta: MetaData;
    headers: PageHeaders;
    pillars: Pillar[];
  };
}

export interface SupportPageResponse {
  success: boolean;
  main_header_img: string;
  meta: MetaData;
  headers: PageHeaders;
  pillars: Pillar[];
}

export interface DocumentResponse {
  document?: {
    title?: string;
    /** Raw HTML when API returns body directly */
    body?: string;
    /** Translation key for HTML content (e.g. LEGAL_DOCS_DOCUMENT_DISCLAIMER); resolve via messages for client-formatted HTML */
    value?: string;
    meta?: MetaData;
  };
}

export interface ContactFormData {
  full_name: string;
  email: string;
  subject: string;
  message: string;
  "g-recaptcha-response"?: string;
  lang: string;
}

export interface ClinicFormData {
  nickname: string;
  full_name: string;
  tel_mobile: string;
  country: string;
  email: string;
  notes: string;
  lang: string;
}

/* ─────────────────────── Cookie Consent ──────────────────────────────────── */

export interface CookiePreferences {
  essential: boolean;
  analytics: boolean;
  functional: boolean;
}

/* ─────────────────────── App Data (Context) ──────────────────────────────── */

export interface AppData {
  languages: Languages;
  currencies: Currencies;
  available_modules?: unknown[];
  identity?: ClientIdentity;
  settings: AppSettings;
  pageContents: PageContents;
  navigation?: AppNavigation;
  theme?: AppTheme;
  blogs: BlogSummary[];
  galleryCount: number;
  categories: unknown[];
  openCauses: CauseSummary[];
  isInitialized: boolean;
  isLoading: boolean;
  error: string | null;
}
