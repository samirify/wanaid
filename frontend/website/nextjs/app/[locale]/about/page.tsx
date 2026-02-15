"use client";

import { useState, useEffect } from "react";
import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { Link } from "@/i18n/navigation";
import { api } from "@/lib/api";
import { mediaUrl } from "@/lib/utils";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { PageHead } from "@/components/shared/PageHead";
import { PageHero } from "@/components/shared/PageHero";
import { PageSections } from "@/components/landing/PageSections";
import { SectionSeparator } from "@/components/shared/SectionSeparator";
import { Users, Facebook, Instagram, Linkedin, Twitter, Youtube } from "lucide-react";
import type { PageHeaders, Pillar, TeamDepartment, TeamMember, MetaData } from "@/lib/types";

interface AboutData {
  main_header_img: string;
  meta: MetaData;
  headers: PageHeaders;
  pillars: Pillar[];
  teams: TeamDepartment[];
}

function SocialLink({ url, icon: Icon }: { url: string | null; icon: React.ComponentType<{ className?: string }> }) {
  if (!url) return null;
  return (
    <a
      href={url}
      target="_blank"
      rel="noopener noreferrer"
      className="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 flex items-center justify-center hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors"
    >
      <Icon className="w-3.5 h-3.5" />
    </a>
  );
}

function MemberCard({ member, index, rawT }: { member: TeamMember; index: number; rawT: (key: string) => string }) {
  const hasSocial = member.facebook_url || member.twitter_url || member.linkedin_url || member.instagram_url || member.youtube_url;

  return (
    <motion.div
      key={member.id || index}
      initial={{ opacity: 0, y: 30 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ delay: index * 0.08 }}
      className="card-elevated text-center overflow-hidden group h-full flex flex-col"
    >
      <div className="relative h-64 overflow-hidden shrink-0">
        {member.img_url ? (
          <img
            src={mediaUrl(member.img_url)}
            alt={rawT(member.full_name)}
            className="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            loading="lazy"
          />
        ) : (
          <div className="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center">
            <Users className="w-16 h-16 text-primary-400" />
          </div>
        )}
        <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
      </div>
      <div className="p-5 flex-1 flex flex-col">
        <Link
          href={`/team/${member.unique_title}`}
          className="font-bold text-slate-900 dark:text-white mb-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
        >
          {rawT(member.full_name)}
        </Link>
        {member.position && (
          <p className="text-sm text-primary-600 dark:text-primary-400 mb-2">
            {rawT(member.position)}
          </p>
        )}
        {member.short_description && (
          <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-3 mb-3">
            {rawT(member.short_description)}
          </p>
        )}
        {hasSocial && (
          <div className="flex items-center justify-center gap-2 mt-auto pt-2">
            <SocialLink url={member.facebook_url} icon={Facebook} />
            <SocialLink url={member.twitter_url} icon={Twitter} />
            <SocialLink url={member.linkedin_url} icon={Linkedin} />
            <SocialLink url={member.instagram_url} icon={Instagram} />
            <SocialLink url={member.youtube_url} icon={Youtube} />
          </div>
        )}
      </div>
    </motion.div>
  );
}

export default function AboutPage() {
  const t = useTranslations();
  const rawT = useRawTranslation();
  const locale = useLocale();
  const [data, setData] = useState<AboutData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchData() {
      try {
        setLoading(true);
        const result = await api.getAboutData(locale);
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
    fetchData();
  }, [locale]);

  if (loading) return <Loader fullPage />;
  if (error || !data) {
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

  return (
    <>
      <PageHead />

      <PageHero
        title={rawT(
          data.headers.main_header_middle_big || "ABOUT_MAIN_HEADER_MIDDLE_BIG"
        )}
        topLine={rawT(data.headers.main_header_top || "ABOUT_MAIN_HEADER_TOP")}
        bottomLine={
          data.headers.main_header_bottom
            ? rawT(data.headers.main_header_bottom)
            : undefined
        }
        headerImageUrl={data.main_header_img || null}
        variant="auto"
        align="center"
        showCurve
        asHeader
      />

      {/* Pillars */}
      {data.pillars.length > 0 && <PageSections pillars={data.pillars} />}

      {data.pillars.length > 0 && data.teams && data.teams.length > 0 && <SectionSeparator />}

      {/* Team Sections — grouped by department */}
      {data.teams && data.teams.length > 0 && (
        <section className="py-24 bg-slate-50 dark:bg-slate-800/50">
          <div className="container-custom">
            {/* Overall team header */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              className="text-center mb-16"
            >
              <span className="badge-primary mb-4 inline-flex">
                <Users className="w-3 h-3 me-1" />
                {t("ABOUT_PAGE_TEAM_HEADER")}
              </span>
              <h2 className="section-heading text-slate-900 dark:text-white mb-4">
                {t("ABOUT_PAGE_TEAM_SUB_HEADER")}
              </h2>
              <div className="divider mb-4" />
            </motion.div>

            {/* Departments */}
            {data.teams.map((dept) => (
              <div key={dept.unique_title} className="mb-16 last:mb-0">
                {/* Department header */}
                <motion.div
                  initial={{ opacity: 0, y: 15 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  className="text-center mb-10"
                >
                  <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                    {t(dept.team.title)}
                  </h3>
                  {dept.team.sub_header && (
                    <p className="text-slate-600 dark:text-slate-400">
                      {t(dept.team.sub_header)}
                    </p>
                  )}
                </motion.div>

                {/* Members grid — centered when less than full row */}
                <div className="flex flex-wrap justify-center gap-8">
                  {dept.team.members.map((member, index) => (
                    <div key={member.id} className="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.5rem)] xl:w-[calc(25%-1.5rem)]">
                      <MemberCard
                        member={member}
                        index={index}
                        rawT={rawT}
                      />
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </section>
      )}
    </>
  );
}
