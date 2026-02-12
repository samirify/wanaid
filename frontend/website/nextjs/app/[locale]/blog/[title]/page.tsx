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
      } catch {
        setError("Failed to load blog post.");
      } finally {
        setLoading(false);
      }
    }
    fetchBlog();
  }, [title, locale]);

  if (loading) return <Loader fullPage />;
  if (error || !data || !data.blog) {
    return (
      <ErrorDisplay variant="page" message={error ?? undefined} showHomeButton />
    );
  }

  const { blog, headers, pillars } = data;

  return (
    <>
      {/* Page Hero */}
      <div className="page-hero">
        {blog.header_img_url && (
          <img
            src={mediaUrl(blog.header_img_url)}
            alt=""
            className="absolute inset-0 w-full h-full object-cover opacity-20"
          />
        )}
        <div className="page-hero-content">
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
            className="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4"
          >
            {rawT(blog.title)}
          </motion.h1>
        </div>
      </div>

      <section className="py-24">
        <div className="container-custom max-w-4xl">
          {/* Featured Image */}
          {blog.img_url && (
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="rounded-2xl overflow-hidden shadow-xl mb-8"
            >
              <img
                src={mediaUrl(blog.img_url)}
                alt={rawT(blog.title)}
                className="w-full h-auto object-cover"
              />
            </motion.div>
          )}

          {/* Meta */}
          <div className="flex flex-wrap items-center gap-4 mb-8 text-sm text-slate-500 dark:text-slate-400">
            {blog.author && (
              <span className="flex items-center gap-1.5">
                <User className="w-4 h-4" />
                {blog.author}
              </span>
            )}
            {blog.published_at && (
              <span className="flex items-center gap-1.5">
                <Calendar className="w-4 h-4" />
                {formatDate(blog.published_at, locale)}
              </span>
            )}
          </div>

          {/* Body */}
          <article
            className="prose prose-lg dark:prose-invert max-w-none mb-12"
            dangerouslySetInnerHTML={{ __html: rawT(blog.body) }}
          />

          
        </div>
      </section>

      {/* Pillars */}
      {pillars && pillars.length > 0 && <PageSections pillars={pillars} />}
    </>
  );
}
