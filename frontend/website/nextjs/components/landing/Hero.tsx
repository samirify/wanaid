"use client";

import { useTranslations } from "next-intl";
import { motion } from "framer-motion";
import { useAppData } from "@/context/AppContext";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { Link } from "@/i18n/navigation";
import { Heart, Users, BookOpen, Target, Facebook } from "lucide-react";
import { HeroAnimatedPattern } from "./HeroAnimatedPattern";
import { HeroCards } from "./HeroCards";
import type { HeroCardData } from "./HeroCards";

const fadeInUp = {
  hidden: { opacity: 0, y: 20 },
  visible: (i: number) => ({
    opacity: 1,
    y: 0,
    transition: { delay: i * 0.08, duration: 0.4, ease: "easeOut" as const },
  }),
};

export function Hero() {
  const t = useTranslations();
  const rawT = useRawTranslation();
  const { pageContents, settings, openCauses, blogs } = useAppData();
  const facebookUrl = settings?.social_media_facebook || "https://www.facebook.com/Womenaccessnetwork/";

  const header = pageContents?.LANDING?.HEADER;
  const ctas = header?.ctas || [];

  // Hero cards: open causes, blog, Facebook members (external)
  const heroCards: HeroCardData[] = [
    {
      icon: Target,
      value: openCauses?.length ?? 0,
      label: t("OPEN_CAUSES_HEADER_LABEL"),
      href: "/open-causes",
      external: false,
    },
    {
      icon: BookOpen,
      value: blogs?.length ?? 0,
      label: t("LANDING_PAGE_BLOG_HEADER_LABEL"),
      href: "/blog",
      external: false,
    },
    {
      icon: Facebook,
      valueDisplay: "2.6k+",
      label: t("FACEBOOK_MEMBERS_LABEL"),
      href: facebookUrl,
      external: true,
    },
  ];

  return (
    <section className="relative min-h-screen flex items-center overflow-hidden">
      {/* Rich gradient background — reacts to dark mode via CSS variables */}
      <div
        className="absolute inset-0 transition-[background] duration-500"
        style={{ background: "var(--hero-bg)" }}
      />
      {/* Animated gradient overlay; logical direction so it flips in RTL */}
      <div
        className="absolute inset-0 opacity-40 dark:opacity-30 transition-opacity duration-500 animated-gradient"
        style={{
          background: "linear-gradient(to bottom inline-end, rgba(199,28,105,0.25) 0%, transparent 40%, rgba(231,62,133,0.15) 70%, transparent 100%)",
          backgroundSize: "200% 200%",
        }}
      />
      {/* Radial spotlight behind content */}
      <div
        className="absolute inset-0 opacity-60 transition-opacity duration-500"
        style={{ background: "var(--hero-spotlight)" }}
      />
      {/* Animated children's charity pattern — replaces hero image */}
      <HeroAnimatedPattern />
      {/* Decorative orbs — light mode: brighter; dark mode: subtler */}
      <div className="absolute top-10 -start-20 w-[420px] h-[420px] bg-primary-400/30 dark:bg-primary-500/15 rounded-full blur-[100px] animate-float transition-colors duration-500" />
      <div
        className="absolute bottom-10 -end-20 w-[480px] h-[480px] bg-primary-500/25 dark:bg-primary-600/20 rounded-full blur-[120px] animate-float transition-colors duration-500"
        style={{ animationDelay: "2s" }}
      />
      <div
        className="absolute top-1/2 end-1/4 w-[320px] h-[320px] bg-white/5 dark:bg-slate-400/5 rounded-full blur-3xl animate-float transition-colors duration-500"
        style={{ animationDelay: "1s" }}
      />
      <div className="absolute top-1/3 end-1/3 w-[200px] h-[200px] bg-primary-300/15 dark:bg-primary-500/10 rounded-full blur-2xl animate-float transition-colors duration-500" style={{ animationDelay: "0.5s" }} />

      {/* Subtle grid texture */}
      <div
        className="absolute inset-0 opacity-[0.04] dark:opacity-[0.06] transition-opacity duration-500"
        style={{
          backgroundImage:
            "radial-gradient(circle at 1px 1px, white 1px, transparent 0)",
          backgroundSize: "32px 32px",
        }}
      />
      {/* Soft noise overlay for texture */}
      <div
        className="absolute inset-0 opacity-[0.03] dark:opacity-[0.04] pointer-events-none transition-opacity duration-500"
        style={{
          backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E")`,
        }}
      />
      {/* Curved bottom edge into next section */}
      <svg className="absolute bottom-0 left-0 right-0 w-full h-20 sm:h-24 text-white dark:text-slate-900" viewBox="0 0 1440 96" fill="none" preserveAspectRatio="none" aria-hidden>
        <path d="M0 96V70C240 20 480 0 720 0s480 20 720 70V96H0z" fill="currentColor" />
      </svg>

      {/* On mobile: less top padding so hero content sits higher and doesn't overlap the scroll arrow */}
      <div className="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-20 sm:pt-12 sm:pb-16 sm:py-16 lg:py-0">
        <div className="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center min-h-0 lg:min-h-screen">
          {/* Text Content */}
          <div className="text-white hero-text-shadow">
            <motion.div
              custom={0}
              initial="hidden"
              animate="visible"
              variants={fadeInUp}
              className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/30 shadow-lg shadow-black/20 mb-5 sm:mb-6 md:mb-8 font-display font-semibold"
            >
              <Heart className="w-4 h-4 text-accent-50" />
              <span className="text-sm text-white/95">
                {rawT(header?.main_header_top || "LANDING_MAIN_HEADER_TOP")}
              </span>
            </motion.div>

            <motion.h1
              custom={1}
              initial="hidden"
              animate="visible"
              variants={fadeInUp}
              className="display-headline text-[2.5rem] sm:text-5xl lg:text-6xl xl:text-7xl leading-[1.45] mb-4 sm:mb-5 md:mb-6 text-white shine-sweep"
            >
              {rawT(
                header?.main_header_middle_big ||
                  "LANDING_MAIN_HEADER_MIDDLE_BIG"
              )}
            </motion.h1>

            <motion.p
              custom={2}
              initial="hidden"
              animate="visible"
              variants={fadeInUp}
              className="text-lg sm:text-xl text-white/85 leading-relaxed mb-7 sm:mb-8 md:mb-10 max-w-lg font-medium"
            >
              {rawT(header?.main_header_bottom || "LANDING_MAIN_HEADER_BOTTOM")}
            </motion.p>

            <motion.div
              custom={3}
              initial="hidden"
              animate="visible"
              variants={fadeInUp}
              className="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 sm:gap-4"
            >
              {ctas.length > 0 ? (
                ctas.map((cta) => {
                  const isInternalLink = cta.url_type === "internal";
                  const linkHref = isInternalLink ? `/${cta.url}` : cta.url;

                  return cta.style === "dark" ? (
                    <Link
                      key={cta.id}
                      href={linkHref}
                      className="btn font-display font-semibold bg-accent-500 text-white border-2 border-white/30 hover:bg-accent-600 shadow-xl shadow-black/30 hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] active:scale-[0.98] px-8 py-4 text-base transition-all duration-300 rounded-2xl w-full sm:w-auto justify-center"
                    >
                      <Heart className="w-5 h-5" />
                      {rawT(cta.label)}
                    </Link>
                  ) : (
                    <Link
                      key={cta.id}
                      href={linkHref}
                      className="btn font-display font-semibold bg-white/10 backdrop-blur-md text-white border-2 border-white/30 hover:bg-white/20 hover:border-white/50 shadow-lg shadow-black/10 hover:shadow-xl hover:-translate-y-1 hover:scale-[1.02] active:scale-[0.98] px-8 py-4 text-base transition-all duration-300 inline-flex items-center justify-center gap-2 rounded-2xl w-full sm:w-auto"
                    >
                      <Users className="w-5 h-5 shrink-0" />
                      {rawT(cta.label)}
                    </Link>
                  );
                })
              ) : (
                <>
                  <Link
                    href={
                      settings.static_button_get_started_url || "/cause/help-us"
                    }
                    className="btn font-display font-semibold bg-accent-500 text-white border-2 border-white/30 hover:bg-accent-600 shadow-xl shadow-black/30 hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] active:scale-[0.98] px-8 py-4 text-base transition-all duration-300 rounded-2xl w-full sm:w-auto justify-center"
                  >
                    <Heart className="w-5 h-5" />
                    {t("OPEN_CAUSES_DONATE_NOW_LABEL")}
                  </Link>
                  <Link
                    href="/about"
                    className="btn font-display font-semibold bg-white/10 backdrop-blur-md text-white border-2 border-white/30 hover:bg-white/20 hover:border-white/50 shadow-lg shadow-black/10 hover:shadow-xl hover:-translate-y-1 hover:scale-[1.02] active:scale-[0.98] px-8 py-4 text-base transition-all duration-300 inline-flex items-center justify-center gap-2 rounded-2xl w-full sm:w-auto"
                  >
                    <Users className="w-5 h-5 shrink-0" />
                    {t("TOP_NAV_ABOUT_LABEL")}
                  </Link>
                </>
              )}
            </motion.div>
          </div>

          {/* Hero cards — creative animated cards */}
          <motion.div
            initial={{ opacity: 0, x: 30 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
            className="hidden lg:block"
          >
            <HeroCards cards={heroCards} />
          </motion.div>
        </div>

      </div>

      {/* Scroll indicator — centered with left-0 right-0 flex (RTL-safe) */}
      <motion.div
        initial={{ opacity: 0, scale: 0.6 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ delay: 1.2, duration: 0.7, ease: "easeOut" }}
        className="absolute bottom-28 sm:bottom-32 left-0 right-0 flex justify-center z-20"
      >
        <a
          href="#content"
          className="group relative flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 focus:outline-none"
          aria-label="Scroll to content"
        >
          {/* Ripple rings radiating outward */}
          <span className="absolute inset-0 rounded-full border border-white/25 hero-scroll-ripple" />
          <span className="absolute -inset-3 sm:-inset-4 rounded-full border border-primary-300/20 hero-scroll-ripple" style={{ animationDelay: "0.6s" }} />
          <span className="absolute -inset-6 sm:-inset-8 rounded-full border border-primary-400/12 hero-scroll-ripple" style={{ animationDelay: "1.2s" }} />

          {/* Orbiting sparkle dots */}
          <span className="absolute w-full h-full hero-scroll-orbit">
            <span className="absolute -top-1.5 left-1/2 -translate-x-1/2 w-2 h-2 rounded-full bg-yellow-300/80 shadow-[0_0_8px_rgba(253,224,71,0.7)]" />
          </span>
          <span className="absolute w-full h-full hero-scroll-orbit" style={{ animationDelay: "-1.5s", animationDuration: "5s" }}>
            <span className="absolute top-1/2 -right-2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-pink-300/70 shadow-[0_0_6px_rgba(249,168,201,0.6)]" />
          </span>
          <span className="absolute w-full h-full hero-scroll-orbit" style={{ animationDelay: "-3s", animationDuration: "6s" }}>
            <span className="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-white/50 shadow-[0_0_5px_rgba(255,255,255,0.4)]" />
          </span>

          {/* Soft glow */}
          <span className="absolute inset-0 rounded-full bg-white/10 blur-xl group-hover:bg-white/15 transition-all duration-500 hero-scroll-glow" />

          {/* Bouncing double-chevron */}
          <div
            className="relative z-10 flex flex-col items-center gap-0"
            style={{ animation: "heroChevronBounce 2s ease-in-out infinite" }}
          >
            <svg
              width="32"
              height="32"
              viewBox="0 0 24 24"
              fill="none"
              className="sm:w-[38px] sm:h-[38px] drop-shadow-[0_0_10px_rgba(255,255,255,0.25)] group-hover:drop-shadow-[0_0_16px_rgba(255,255,255,0.4)] transition-[filter] duration-300"
            >
              <path
                d="M7 8.5L12 13.5L17 8.5"
                stroke="rgba(255,255,255,0.55)"
                strokeWidth="2.5"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
              <path
                d="M7 14L12 19L17 14"
                stroke="rgba(255,255,255,0.3)"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          </div>
        </a>
      </motion.div>
    </section>
  );
}
