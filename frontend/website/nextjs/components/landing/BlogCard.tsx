"use client";

import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { Link } from "@/i18n/navigation";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { ArrowRight } from "lucide-react";
import type { BlogSummary } from "@/lib/types";

interface BlogCardProps {
  blog: BlogSummary;
  index?: number;
}

export function BlogCard({ blog, index = 0 }: BlogCardProps) {
  const t = useTranslations();
  const rawT = useRawTranslation();
  const locale = useLocale();

  return (
    <motion.div
      initial={{ opacity: 0, y: 30 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ delay: index * 0.1, duration: 0.5 }}
      className="group card-elevated overflow-hidden"
    >
      {/* Image — use native <img> since API images are unoptimised */}
      <div className="relative h-52 overflow-hidden bg-slate-100 dark:bg-slate-700">
        {blog.img_url ? (
          <img
            src={blog.img_url}
            alt={rawT(blog.title)}
            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            loading="lazy"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center text-slate-400">
            <svg
              className="w-12 h-12"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={1}
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
          </div>
        )}
        <div className="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent" />
      </div>

      {/* Content */}
      <div className="p-6">
        <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
          {rawT(blog.title)}
        </h3>

        <p className="text-sm text-slate-600 dark:text-slate-400 mb-4 line-clamp-3">
          {rawT(blog.short_description)}
        </p>

        <Link
          href={`/blog/${blog.unique_title}`}
          className="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors group/link"
        >
          {t("LANDING_PAGE_BLOG_MORE_BTN_LABEL")}
          <ArrowRight className="w-4 h-4 group-hover/link:translate-x-1 transition-transform rtl:rotate-180 rtl:group-hover/link:-translate-x-1" />
        </Link>
      </div>
    </motion.div>
  );
}
