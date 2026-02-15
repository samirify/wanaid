"use client";

import { useTranslations } from "next-intl";
import { motion } from "framer-motion";
import { Link } from "@/i18n/navigation";
import { mediaUrl } from "@/lib/utils";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { PageHead } from "@/components/shared/PageHead";
import { SectionSeparator } from "@/components/shared/SectionSeparator";
import { ArrowLeft, Facebook, Instagram, Linkedin, Twitter, Youtube } from "lucide-react";
import type { TeamMember, PageHeaders, Pillar } from "@/lib/types";

interface FullPageData {
  main_header_img?: string;
  meta: { title?: string; description?: string; keywords?: string };
  headers: PageHeaders;
  pillars: Pillar[];
}

interface Props {
  pageData: FullPageData | null;
  memberData: TeamMember | null;
  locale: string;
}

export default function TeamMemberContent({ pageData, memberData, locale }: Props) {
  const t = useTranslations();
  const rawT = useRawTranslation();

  if (!pageData && !memberData) {
    return <ErrorDisplay variant="page" message="Failed to load team member details." showHomeButton />;
  }

  // ── Render with /pages/ data (full translated HTML like the React reference app) ──
  if (pageData) {
    const { headers, pillars } = pageData;
    return (
      <>
        <PageHead />

        <div className="page-hero">
          {pageData.main_header_img && (
            <img
              src={mediaUrl(pageData.main_header_img)}
              alt=""
              className="absolute inset-0 w-full h-full object-cover opacity-20"
            />
          )}
          <div className="page-hero-content">
            {headers?.main_header_middle_big && (
              <motion.h1
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4"
              >
                {headers.main_header_middle_big}
              </motion.h1>
            )}
            {headers?.main_header_bottom && (
              <motion.p
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.1 }}
                className="text-primary-200 text-lg"
              >
                {headers.main_header_bottom}
              </motion.p>
            )}
          </div>
        </div>

        <SectionSeparator />

        <section className="py-24">
          <div className="container-custom max-w-5xl">
            <div className="mb-6">
              <Link
                href="/about"
                className="inline-flex items-center gap-2 text-primary-500 dark:text-primary-400 hover:underline text-sm font-medium"
              >
                <ArrowLeft className="w-4 h-4 rtl:rotate-180" />
                {t("TOP_NAV_ABOUT_LABEL")}
              </Link>
            </div>

            {pillars.map((pillar, index) => {
              const isEven = index % 2 === 0;
              return (
                <div
                  key={pillar.code}
                  className={`flex flex-col-reverse ${isEven ? "md:flex-row" : "md:flex-row-reverse"} gap-12 items-start mb-16 last:mb-0`}
                >
                  <div className="flex-1">
                    <div
                      className="prose prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed"
                      dangerouslySetInnerHTML={{ __html: pillar.value }}
                    />
                  </div>
                  {pillar.img && (
                    <div className="w-full md:w-80 shrink-0">
                      <div className="rounded-2xl overflow-hidden shadow-xl">
                        <img
                          src={mediaUrl(pillar.img)}
                          alt=""
                          className="w-full h-auto object-cover"
                          loading="lazy"
                        />
                      </div>
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </section>
      </>
    );
  }

  // ── Fallback render with /team-member/ data ──
  const tm = memberData!;
  const hasSocial =
    tm.facebook_url || tm.twitter_url || tm.linkedin_url || tm.instagram_url || tm.youtube_url;

  return (
    <>
      <PageHead />

      <div className="page-hero">
        <div className="page-hero-content">
          <motion.h1
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4"
          >
            {rawT(tm.full_name)}
          </motion.h1>
          {tm.position && (
            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.1 }}
              className="text-primary-200 text-lg"
            >
              {rawT(tm.position)}
            </motion.p>
          )}
        </div>
      </div>

      <SectionSeparator />

      <section className="py-24">
        <div className="container-custom max-w-5xl">
          <div className="mb-6">
            <Link
              href="/about"
              className="inline-flex items-center gap-2 text-primary-500 dark:text-primary-400 hover:underline text-sm font-medium"
            >
              <ArrowLeft className="w-4 h-4 rtl:rotate-180" />
              {t("TOP_NAV_ABOUT_LABEL")}
            </Link>
          </div>

          <div className="flex flex-col-reverse md:flex-row gap-12 items-start">
            <div className="flex-1">
              <h2 className="text-3xl font-bold text-slate-900 dark:text-white mb-2">
                {rawT(tm.full_name)}
              </h2>
              {tm.position && (
                <p className="text-lg text-primary-500 dark:text-primary-400 mb-4">
                  {rawT(tm.position)}
                </p>
              )}
              {hasSocial && (
                <div className="flex items-center gap-3 mb-6">
                  {tm.facebook_url && (
                    <a href={tm.facebook_url} target="_blank" rel="noopener noreferrer" className="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-500 dark:text-primary-400 flex items-center justify-center hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors">
                      <Facebook className="w-4 h-4" />
                    </a>
                  )}
                  {tm.twitter_url && (
                    <a href={tm.twitter_url} target="_blank" rel="noopener noreferrer" className="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-500 dark:text-primary-400 flex items-center justify-center hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors">
                      <Twitter className="w-4 h-4" />
                    </a>
                  )}
                  {tm.linkedin_url && (
                    <a href={tm.linkedin_url} target="_blank" rel="noopener noreferrer" className="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-500 dark:text-primary-400 flex items-center justify-center hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors">
                      <Linkedin className="w-4 h-4" />
                    </a>
                  )}
                  {tm.instagram_url && (
                    <a href={tm.instagram_url} target="_blank" rel="noopener noreferrer" className="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-500 dark:text-primary-400 flex items-center justify-center hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors">
                      <Instagram className="w-4 h-4" />
                    </a>
                  )}
                  {tm.youtube_url && (
                    <a href={tm.youtube_url} target="_blank" rel="noopener noreferrer" className="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-500 dark:text-primary-400 flex items-center justify-center hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors">
                      <Youtube className="w-4 h-4" />
                    </a>
                  )}
                </div>
              )}
              {/* Full description from translations: TEAM_MEMBER_{id}_DESCRIPTION */}
              {(() => {
                const descKey = `TEAM_MEMBER_${tm.id}_DESCRIPTION`;
                const descHtml = rawT(descKey);
                // If rawT returns the key itself, the translation doesn't exist — fall back to short_description
                if (descHtml && descHtml !== descKey) {
                  return (
                    <div
                      className="prose prose-lg dark:prose-invert max-w-none"
                      dangerouslySetInnerHTML={{ __html: descHtml }}
                    />
                  );
                }
                // Fallback: short description + body
                return (
                  <>
                    {tm.short_description && (
                      <p className="text-lg text-slate-600 dark:text-slate-400 mb-6 leading-relaxed">
                        {rawT(tm.short_description)}
                      </p>
                    )}
                    {tm.body && (
                      <div
                        className="prose prose-lg dark:prose-invert max-w-none"
                        dangerouslySetInnerHTML={{ __html: rawT(tm.body) }}
                      />
                    )}
                  </>
                );
              })()}
            </div>

            {tm.img_url && (
              <div className="w-full md:w-80 shrink-0">
                <div className="rounded-2xl overflow-hidden shadow-xl">
                  <img
                    src={mediaUrl(tm.img_url)}
                    alt={rawT(tm.full_name)}
                    className="w-full h-auto object-cover"
                    loading="lazy"
                  />
                </div>
              </div>
            )}
          </div>
        </div>
      </section>
    </>
  );
}
