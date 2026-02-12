"use client";

import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { useAppData } from "@/context/AppContext";
import { BlogCard } from "./BlogCard";

export function BlogSection() {
  const t = useTranslations();
  const locale = useLocale();
  const { blogs } = useAppData();

  if (!blogs || blogs.length === 0) return null;

  const displayBlogs = blogs.slice(0, 3);

  return (
    <section id="blog" className="py-24">
      <div className="container-custom">
        {/* Section Header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="text-center mb-16"
        >
          <h2 className="section-heading text-slate-900 dark:text-white mb-4">
            {t("LANDING_PAGE_BLOG_HEADER_LABEL")}
          </h2>
          <div className="divider mb-4" />
          <p className="section-subheading mx-auto">
            {t("LANDING_PAGE_BLOG_LATEST_FROM_US_LABEL")}
          </p>
        </motion.div>

        {/* Blog Grid — centered when fewer than full row */}
        <div className="flex flex-wrap justify-center gap-8">
          {displayBlogs.map((blog, index) => (
            <div key={blog.id} className="w-full md:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.5rem)]">
              <BlogCard blog={blog} index={index} />
            </div>
          ))}
        </div>

        
      </div>
    </section>
  );
}
