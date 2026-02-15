"use client";

import { useTranslations } from "next-intl";
import { motion } from "framer-motion";
import { useAppData } from "@/context/AppContext";
import { Link } from "@/i18n/navigation";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { ArrowRight } from "lucide-react";
import type { BlogSummary } from "@/lib/types";

function FeaturedBlog({ blog, index }: { blog: BlogSummary; index: number }) {
  const t = useTranslations();
  const rawT = useRawTranslation();

  return (
    <motion.article
      initial={{ opacity: 0, y: 24 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ duration: 0.5 }}
      className="flex flex-col lg:flex-row min-h-[320px] lg:min-h-[400px] bg-slate-900 dark:bg-slate-950 overflow-hidden rounded-3xl border border-slate-700/50 shadow-2xl shadow-black/20 hover:shadow-2xl hover:shadow-primary-500/15 hover:-translate-y-1 transition-all duration-300"
    >
      <Link
        href={`/blog/${blog.unique_title}`}
        className="block w-full lg:w-[46%] relative min-h-[280px] lg:min-h-full shrink-0 group"
      >
        {blog.img_url ? (
          <img
            src={blog.img_url}
            alt={rawT(blog.title)}
            className="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500"
            loading="eager"
          />
        ) : (
          <div className="absolute inset-0 bg-slate-700 flex items-center justify-center text-slate-500">
            <svg className="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14" />
            </svg>
          </div>
        )}
        <div className="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-r from-slate-900/90 via-slate-900/40 to-transparent" />
      </Link>
      <div className="relative w-full lg:w-[54%] flex flex-col justify-center p-8 sm:p-10 lg:p-12 border-t-4 lg:border-t-0 lg:border-l-4 border-primary-500">
        <h3 className="font-display text-[1.4rem] sm:text-[1.8rem] lg:text-[2.1rem] font-bold text-white mb-4 leading-tight">
          <Link href={`/blog/${blog.unique_title}`} className="text-white hover:text-primary-400 transition-colors">
            {rawT(blog.title)}
          </Link>
        </h3>
        <p className="text-slate-400 text-base lg:text-lg leading-relaxed line-clamp-3 mb-6">
          {rawT(blog.short_description)}
        </p>
        <Link
          href={`/blog/${blog.unique_title}`}
          className="inline-flex items-center gap-2 text-primary-400 font-semibold text-lg hover:text-primary-300 hover:gap-3 transition-all"
        >
          {t("LANDING_PAGE_BLOG_MORE_BTN_LABEL")}
          <ArrowRight className="w-5 h-5 rtl:rotate-180" />
        </Link>
      </div>
    </motion.article>
  );
}

function BlogListItem({ blog, index }: { blog: BlogSummary; index: number }) {
  const t = useTranslations();
  const rawT = useRawTranslation();

  return (
    <motion.li
      initial={{ opacity: 0, x: -16 }}
      whileInView={{ opacity: 1, x: 0 }}
      viewport={{ once: true }}
      transition={{ duration: 0.4, delay: index * 0.05 }}
    >
      <Link
        href={`/blog/${blog.unique_title}`}
        className="flex gap-5 sm:gap-6 py-5 sm:py-6 border-b border-slate-200 dark:border-slate-700 last:border-0 group"
      >
        <div className="shrink-0 w-24 h-24 sm:w-28 sm:h-28 rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-700">
          {blog.img_url ? (
            <img
              src={blog.img_url}
              alt={rawT(blog.title)}
              className="w-full h-full object-cover group-hover:scale-105 transition-transform"
              loading="lazy"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-slate-400">
              <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16" />
              </svg>
            </div>
          )}
        </div>
        <div className="min-w-0 flex-1 flex flex-col justify-center">
          <h4 className="text-lg sm:text-xl font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
            {rawT(blog.title)}
          </h4>
          <p className="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-1 line-clamp-2">
            {rawT(blog.short_description)}
          </p>
          <span className="inline-flex items-center gap-1.5 mt-2 text-primary-600 dark:text-primary-400 font-semibold text-sm">
            {t("LANDING_PAGE_BLOG_MORE_BTN_LABEL")}
            <ArrowRight className="w-4 h-4 rtl:rotate-180 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5 transition-transform" />
          </span>
        </div>
      </Link>
    </motion.li>
  );
}

export function BlogSection() {
  const t = useTranslations();
  const { blogs } = useAppData();

  if (!blogs || blogs.length === 0) return null;

  const [featured, ...rest] = blogs;

  return (
    <section id="blog" className="relative py-20 sm:py-28 overflow-hidden bg-gradient-to-b from-slate-200 via-slate-100 to-slate-200 dark:from-slate-800 dark:via-slate-800 dark:to-slate-900" aria-label={t("LANDING_PAGE_BLOG_HEADER_LABEL")}>
      <div className="absolute inset-0 pointer-events-none" aria-hidden />
      <div className="container-custom relative">
        <header className="mb-14 sm:mb-16 text-center">
          <h2 className="section-heading text-slate-900 dark:text-white max-w-2xl mx-auto">
            {t("LANDING_PAGE_BLOG_HEADER_LABEL")}
          </h2>
          <p className="mt-3 text-slate-600 dark:text-slate-400 text-lg max-w-xl mx-auto">
            {t("LANDING_PAGE_BLOG_LATEST_FROM_US_LABEL")}
          </p>
          <div className="mt-5 h-1 w-20 bg-gradient-to-r from-primary-500 to-primary-400 rounded-full mx-auto" />
        </header>

        <div className="max-w-6xl mx-auto space-y-10 sm:space-y-12">
          <FeaturedBlog blog={featured} index={0} />
          {rest.length > 0 && (
            <div className="rounded-3xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-xl shadow-slate-200/50 dark:shadow-black/30 overflow-hidden px-4 sm:px-6">
              <ul className="divide-y-0">
                {rest.map((blog, index) => (
                  <BlogListItem key={blog.id} blog={blog} index={index} />
                ))}
              </ul>
            </div>
          )}
        </div>
      </div>
    </section>
  );
}
