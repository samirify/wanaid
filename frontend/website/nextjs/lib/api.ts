import type {
  InitializeResponse,
  AboutPageResponse,
  CauseDetailResponse,
  BlogDetailResponse,
  TeamMemberResponse,
  GenericPageResponse,
  SupportPageResponse,
  DocumentResponse,
  ContactFormData,
  ClinicFormData,
  GalleryItem,
} from "./types";

/* ─────────────────────── Configuration ───────────────────────────────────── */

const getApiUrl = (): string =>
  process.env.NEXT_PUBLIC_API_URL || "https://api.wanaid.org/api";

/* ─────────────────────── Error Class ─────────────────────────────────────── */

export class ApiError extends Error {
  status: number;

  constructor(message: string, status: number = 500) {
    super(message);
    this.name = "ApiError";
    this.status = status;
  }
}

/* ─────────────────────── Core Fetch Wrapper ──────────────────────────────── */

interface RequestOptions {
  method?: "GET" | "POST";
  body?: Record<string, unknown>;
  cache?: RequestCache;
  revalidate?: number;
}

async function request<T>(
  endpoint: string,
  options: RequestOptions = {}
): Promise<T> {
  const {
    method = "GET",
    body,
    cache = "no-cache",
    revalidate,
  } = options;

  const url = `${getApiUrl()}${endpoint}`;

  const headers: Record<string, string> = {
    "Content-Type": "application/json",
    Accept: "application/json",
  };

  const init: RequestInit & { next?: { revalidate: number } } = {
    method,
    headers,
    cache,
  };

  if (body) {
    init.body = JSON.stringify(body);
  }

  if (typeof revalidate === "number") {
    init.next = { revalidate };
  }

  const response = await fetch(url, init);

  if (!response.ok) {
    const text = await response.text().catch(() => response.statusText);
    throw new ApiError(text || `Request failed (${response.status})`, response.status);
  }

  return response.json() as Promise<T>;
}

/* ─────────────────────── Public API ──────────────────────────────────────── */

export const api = {
  /** Fetch all initial app data (languages, settings, blogs, causes, etc.) */
  initialize(locale?: string) {
    return request<InitializeResponse>("/initialize", {
      method: "POST",
      body: locale ? { locale } : undefined,
    }).then((res) => res.data);
  },

  /** About page: team members, pillars, header image. */
  getAboutData(locale?: string) {
    const params = locale ? `?locale=${locale}` : "";
    return request<AboutPageResponse>(`/about-data${params}`).then(
      (res) => res.data
    );
  },

  /** Single cause by slug. */
  getCauseDetails(slug: string) {
    return request<CauseDetailResponse>(`/cause/${encodeURIComponent(slug)}`);
  },

  /** Single blog post by slug. */
  getBlogDetails(slug: string) {
    return request<BlogDetailResponse>(`/blogs/${encodeURIComponent(slug)}`);
  },

  /** Single team member by slug. */
  getTeamMemberDetails(slug: string) {
    return request<TeamMemberResponse>(`/team-member/${encodeURIComponent(slug)}`);
  },

  /** Generic page data (used by the reference app for all pages including team member detail). */
  async getPageData(path: string, locale: string) {
    const res = await request<Record<string, unknown>>(
      `/pages/${path}?locale=${locale}&t=${Date.now()}`
    );
    // API may return { data: { ... } } or { meta, headers, pillars, ... }
    const data = (res as { data?: Record<string, unknown> }).data ?? res;
    return data as {
      main_header_img?: string;
      meta: { title?: string; description?: string; keywords?: string };
      headers: import("./types").PageHeaders;
      pillars: import("./types").Pillar[];
    };
  },

  /** Gallery images for a locale. */
  getGalleryData(locale: string) {
    return request<{ gallery: GalleryItem[] }>(
      `/media/gallery?locale=${locale}`
    ).then((res) => res?.gallery ?? []);
  },

  /** Dynamic support / content page. */
  getSupportPageData(pageCode: string) {
    return request<SupportPageResponse>(
      `/support-page/${encodeURIComponent(pageCode)}`
    );
  },

  /** Legal documents (privacy policy, terms, disclaimer). */
  getDocument(code: string) {
    return request<DocumentResponse>(
      `/documents/${encodeURIComponent(code)}`
    );
  },

  /** Submit the general contact form. */
  submitContactForm(data: ContactFormData) {
    return request<{ success: boolean; message: string }>("/contact", {
      method: "POST",
      body: data as unknown as Record<string, unknown>,
    });
  },

  /** Submit the therapy-clinic inquiry form. */
  submitClinicForm(data: ClinicFormData) {
    return request<{ success: boolean; message: string }>(
      "/marital-therapy-clinic/contact",
      { method: "POST", body: data as unknown as Record<string, unknown> }
    );
  },

  /** Search / list causes. */
  searchCauses(params: Record<string, string | number | undefined>) {
    const qs = new URLSearchParams();
    Object.entries(params).forEach(([k, v]) => {
      if (v !== undefined) qs.set(k, String(v));
    });
    return request<{
      data: CauseDetailResponse["cause"][];
      meta: { total: number; per_page: number; current_page: number };
    }>(`/causes/search?${qs.toString()}`);
  },
};
