"use client";

import { useState, useEffect, use } from "react";
import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
// Using native <img> for external API images
import { Link } from "@/i18n/navigation";
import { api } from "@/lib/api";
import { formatDate, mediaUrl } from "@/lib/utils";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { PageHead } from "@/components/shared/PageHead";
import { PageSections } from "@/components/landing/PageSections";
import { Calendar, User, ArrowLeft } from "lucide-react";
import type { BlogDetailResponse } from "@/lib/types";

interface PageProps {
  params: Promise<{ title: string; locale: string }>;
}

export default function BlogDetailPage({ params }: PageProps) {
  const { title } = use(params);
  const t = useTranslations();
  const rawT = useRawTranslation();
  const locale = useLocale();
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

  if (loading) return <Loader fullPage />;
  if (error || !data || !data.blog) {
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

  return (
    <>
      <PageHead />

      {/* Page Hero — content starts below nav, title constrained */}
      <header className="page-hero flex flex-col items-center justify-start pt-24 md:pt-28">
        {blog.header_img_url && (
          <>
            <img
              src={mediaUrl(blog.header_img_url)}
              alt=""
              className="absolute inset-0 w-full h-full object-cover opacity-[0.1] pointer-events-none"
            />
            <div className="absolute inset-0 bg-black/45 pointer-events-none" aria-hidden />
          </>
        )}
        <div className="page-hero-content relative z-10 w-full max-w-4xl mx-auto text-start pt-8 pb-16 md:pt-10 md:pb-20 px-4 sm:px-6">
          {headers?.main_header_top && (
            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="text-primary-200 font-medium mb-3"
            >
              {rawT(headers.main_header_top)}
            </motion.p>
          )}
          <motion.h1
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold text-white"
            style={{ lineHeight: 1.5 }}
          >
            {rawT(headers?.main_header_middle_big || blog.title)}
          </motion.h1>
          {headers?.main_header_bottom && (
            <>
              <hr className="divider my-4 w-20 border-0 h-0.5 bg-white/50 rounded-full" aria-hidden />
              <motion.p
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.15 }}
                className="text-white/80 font-light text-lg mt-4"
              >
                {rawT(headers.main_header_bottom)}
              </motion.p>
            </>
          )}
        </div>
      </header>

      <section className="py-12 md:py-16 bg-white dark:bg-slate-900">
        <div className="container-custom max-w-6xl">
          <div className="lg:grid lg:grid-cols-[1fr_minmax(280px,360px)] lg:gap-12 lg:items-start">
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
                <div className="rounded-2xl overflow-hidden shadow-lg border border-slate-200/50 dark:border-slate-700/50">
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
        <div className="border-t border-slate-200 dark:border-slate-700">
          <PageSections pillars={pillars} />
        </div>
      )}
    </>
  );
}
