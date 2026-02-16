"use client";

import { useState, useEffect, useRef, use } from "react";
import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { Link } from "@/i18n/navigation";
import { api } from "@/lib/api";
import { formatDate, mediaUrl } from "@/lib/utils";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { usePageTitleOverride } from "@/context/PageTitleContext";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { PageHero } from "@/components/shared/PageHero";
import { PageSections } from "@/components/landing/PageSections";
import { SectionSeparator } from "@/components/shared/SectionSeparator";
import { Calendar, User, ArrowLeft } from "lucide-react";
import { SEO_DEFAULTS } from "@/lib/seo-defaults";
import type { BlogDetailResponse } from "@/lib/types";

interface PageProps {
  params: Promise<{ title: string; locale: string }>;
}

export default function BlogDetailPage({ params }: PageProps) {
  const { title } = use(params);
  const t = useTranslations();
  const rawT = useRawTranslation();
  const locale = useLocale();
  const { setPageTitleOverride } = usePageTitleOverride();
  const [data, setData] = useState<BlogDetailResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchBlog() {
      try {
        setLoading(true);
        const result = await api.getBlogDetails(title);
        setData(result);
      } catch (err) {
        if (err && typeof err === "object" && "status" in err && (err as { status: number }).status === 404) {
          setError("Page not found.");
        } else {
          setError("Server error.");
        }
      } finally {
        setLoading(false);
      }
    }
    fetchBlog();
  }, [title, locale]);

  const docTitleRef = useRef<string | null>(null);
  useEffect(() => {
    if (typeof document === "undefined") return;
    const v = docTitleRef.current;
    const plain = v && String(v).replace(/<[^>]*>/g, "").trim();
    document.title = plain ? `${plain} | ${SEO_DEFAULTS.title}` : SEO_DEFAULTS.title;
  });

  if (loading) {
    docTitleRef.current = null;
    return <Loader fullPage />;
  }
  if (error || !data || !data.blog) {
    docTitleRef.current = null;
    const is404 = error === "Page not found.";
    return (
      <ErrorDisplay
        variant="page"
        errorCode={is404 ? "404" : "500"}
        title={is404 ? rawT("WEBSITE_ERRORS_PAGE_NOT_FOUND_HEADER") : undefined}
        message={is404 ? rawT("WEBSITE_ERRORS_PAGE_NOT_FOUND_MESSAGE") : undefined}
        showHomeButton
      />
    );
  }

  const { blog, headers, pillars } = data;

  const heroTitle = rawT(headers?.main_header_middle_big || blog.title);

  const looksLikeKey = (s: string) => /^[A-Z0-9_]+$/.test(String(s).trim());
  const resolve = (key: string | null | undefined): string => {
    if (key == null || key === "") return "";
    const r = rawT(key) || t(key) || "";
    return (r && !looksLikeKey(r) ? r : key).replace(/<[^>]*>/g, "").trim();
  };
  /** For optional headers: only use rawT so missing message keys don't throw (next-intl t() throws on MISSING_MESSAGE). */
  const resolveOptional = (key: string | null | undefined): string => {
    if (key == null || key === "") return "";
    const r = rawT(key) || "";
    return (r && !looksLikeKey(r) ? r : "").replace(/<[^>]*>/g, "").trim();
  };
  const fromMiddle = resolve(headers?.main_header_middle_big ?? undefined);
  const fromBlogTitle = resolve(blog.title ?? undefined);
  const fromTop = resolveOptional(headers?.main_header_top ?? undefined);
  const fromBottom = resolveOptional(headers?.main_header_bottom ?? undefined);
  const slugToTitle = (slug: string) =>
    slug.replace(/-/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());

  const docTitleOnly =
    (fromMiddle && !looksLikeKey(fromMiddle) ? fromMiddle : null) ||
    (fromBlogTitle && !looksLikeKey(fromBlogTitle) ? fromBlogTitle : null) ||
    (fromTop && !looksLikeKey(fromTop) ? fromTop : null) ||
    (fromBottom && !looksLikeKey(fromBottom) ? fromBottom : null) ||
    slugToTitle(title);

  docTitleRef.current = docTitleOnly || null;

  return (
    <>
      <PageHero
        title={heroTitle}
        onTitleResolved={setPageTitleOverride}
        topLine={headers?.main_header_top ? (rawT(headers.main_header_top) || undefined) : undefined}
        bottomLine={headers?.main_header_bottom ? (rawT(headers.main_header_bottom) || undefined) : undefined}
        headerImageUrl={blog.header_img_url}
        variant="auto"
        align="center"
        showCurve
        asHeader
      />

      <section className="py-12 md:py-16 bg-white dark:bg-slate-900">
        <div className="container-custom">
          <div className="lg:grid lg:grid-cols-[1fr_minmax(300px,400px)] lg:gap-14 lg:items-start">
            {/* Main content — reading starts here without scrolling past image */}
            <div className="min-w-0">
              {/* Back link */}
              <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 0.2 }}
                className="mb-8"
              >
                <Link
                  href="/blog"
                  className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                >
                  <ArrowLeft className="w-4 h-4" />
                  {t("LANDING_PAGE_BLOG_HEADER_LABEL")}
                </Link>
              </motion.div>

              {/* Meta */}
              <div className="flex flex-wrap items-center gap-x-6 gap-y-2 mb-8 pb-8 border-b border-slate-200 dark:border-slate-700 text-sm text-slate-500 dark:text-slate-400">
                {blog.author && (
                  <span className="flex items-center gap-1.5">
                    <User className="w-4 h-4 shrink-0" />
                    {blog.author}
                  </span>
                )}
                {blog.published_at && (
                  <span className="flex items-center gap-1.5">
                    <Calendar className="w-4 h-4 shrink-0" />
                    {formatDate(blog.published_at, locale)}
                  </span>
                )}
              </div>

              {/* Body — constrained prose with blog-specific overrides */}
              <article
                className="blog-body prose prose-lg dark:prose-invert max-w-none"
                dangerouslySetInnerHTML={{ __html: rawT(blog.body) }}
              />
            </div>

            {/* Sidebar — featured image beside content on desktop; below meta on mobile with limited height */}
            {blog.img_url && (
              <motion.aside
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.15 }}
                className="lg:sticky lg:top-24"
              >
                <div className="rounded-2xl overflow-hidden shadow-lg border-2 border-slate-200/90 dark:border-slate-500/60 ring-2 ring-slate-200/60 dark:ring-slate-400/25">
                  <img
                    src={mediaUrl(blog.img_url)}
                    alt={rawT(blog.title)}
                    className="w-full h-auto object-cover lg:min-h-64 object-top"
                  />
                </div>
              </motion.aside>
            )}
          </div>
        </div>
      </section>

      {/* Pillars — clear separation */}
      {pillars && pillars.length > 0 && (
        <>
          <SectionSeparator />
          <PageSections pillars={pillars} />
        </>
      )}
    </>
  );
}
