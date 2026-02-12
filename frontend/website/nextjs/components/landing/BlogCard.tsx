"use client";

import { useTranslations } from "next-intl";
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

  return (
    <motion.article
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ delay: index * 0.08, duration: 0.4 }}
      className="flex flex-col h-full rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow overflow-hidden"
    >
      <Link href={`/blog/${blog.unique_title}`} className="flex flex-col h-full group">
        <div className="relative h-56 overflow-hidden bg-slate-100 dark:bg-slate-700 shrink-0">
          {blog.img_url ? (
            <img
              src={blog.img_url}
              alt={rawT(blog.title)}
              className="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300"
              loading="lazy"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-slate-400">
              <svg className="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          )}
        </div>

        <div className="flex-1 flex flex-col p-5 sm:p-6 min-h-0">
          <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 leading-snug group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
            {rawT(blog.title)}
          </h3>
          <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed line-clamp-3 flex-1 min-h-0 mb-4">
            {rawT(blog.short_description)}
          </p>
          <span className="inline-flex items-center gap-1.5 text-primary-600 dark:text-primary-400 font-semibold text-sm">
            {t("LANDING_PAGE_BLOG_MORE_BTN_LABEL")}
            <ArrowRight className="w-4 h-4 rtl:rotate-180 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5 transition-transform" />
          </span>
        </div>
      </Link>
    </motion.article>
  );
}
