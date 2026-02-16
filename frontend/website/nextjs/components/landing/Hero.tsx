"use client";

import { useTranslations } from "next-intl";
import { motion } from "framer-motion";
import { useAppData } from "@/context/AppContext";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { Link } from "@/i18n/navigation";
import { Heart, ArrowDown, Users, BookOpen, Target, Facebook } from "lucide-react";

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
  // Hero image: from CMS, then local wallpaper, then placeholder
  const heroImageUrl =
    header?.main_header_img ||
    "/images/home-wallpaper-bg.jpg";
  const fallbackHeroUrl = "https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=900&q=80";

  // Hero cards: open causes, blog, Facebook members (external)
  const heroCards = [
    {
      icon: Target,
      value: openCauses?.length ?? 0,
      labelKey: "OPEN_CAUSES_HEADER_LABEL",
      href: "/open-causes",
      external: false,
    },
    {
      icon: BookOpen,
      value: blogs?.length ?? 0,
      labelKey: "LANDING_PAGE_BLOG_HEADER_LABEL",
      href: "/blog",
      external: false,
    },
    {
      icon: Facebook,
      valueDisplay: "2.6k+",
      labelKey: "FACEBOOK_MEMBERS_LABEL",
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
      {/* Hero image on inline-end; on mobile softer left-edge blend to avoid a hard split line */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden>
        <div className="absolute inset-0 flex justify-end">
          <div className="relative w-full max-w-[55%] min-w-0 sm:min-w-[200px] md:min-w-[280px] h-full">
            <img
              src={heroImageUrl}
              alt=""
              width={1200}
              height={800}
              className="absolute inset-0 w-full h-full object-cover object-center opacity-30 dark:opacity-25"
              loading="eager"
              fetchPriority="high"
              onError={(e) => {
                const el = e.currentTarget;
                if (el.src !== fallbackHeroUrl) {
                  el.src = fallbackHeroUrl;
                } else {
                  el.style.display = "none";
                }
              }}
            />
            {/* Gradient fade: on mobile add soft blend on left edge so no harsh line; on desktop standard fade to inline-end */}
            <div
              className="absolute inset-0 w-full"
              style={{
                background: "linear-gradient(to inline-end, rgba(45,3,20,0.75) 0%, rgba(45,3,20,0.4) 25%, rgba(26,2,12,0.97) 85%, rgba(26,2,12,1) 100%)",
              }}
            />
          </div>
        </div>
      </div>
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

      {/* On mobile: less top padding so hero content sits higher and doesn’t overlap the scroll arrow */}
      <div className="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-20 sm:pt-12 sm:pb-16 sm:py-16 lg:py-0">
        <div className="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center min-h-0 lg:min-h-screen">
          {/* Text Content */}
          <div className="text-white">
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
                      className="btn font-display font-semibold bg-white/10 backdrop-blur-md text-white border-2 border-white/30 hover:bg-white/20 hover:border-white/50 shadow-lg shadow-black/10 hover:shadow-xl px-8 py-4 text-base transition-all duration-300 inline-flex items-center justify-center gap-2 rounded-2xl w-full sm:w-auto"
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
                    className="btn font-display font-semibold bg-white/10 backdrop-blur-md text-white border-2 border-white/30 hover:bg-white/20 hover:border-white/50 shadow-lg shadow-black/10 hover:shadow-xl px-8 py-4 text-base transition-all duration-300 inline-flex items-center justify-center gap-2 rounded-2xl w-full sm:w-auto"
                  >
                    <Users className="w-5 h-5 shrink-0" />
                    {t("TOP_NAV_ABOUT_LABEL")}
                  </Link>
                </>
              )}
            </motion.div>
          </div>

          {/* Hero cards — open causes, blog, Facebook members (sexier glass + glow) */}
          <motion.div
            initial={{ opacity: 0, x: 30 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
            className="hidden lg:block"
          >
            <div className="grid grid-cols-1 gap-5 max-w-sm ms-auto">
              {heroCards.map((card, index) => {
                const displayValue = "valueDisplay" in card && card.valueDisplay != null ? card.valueDisplay : String(card.value);
                const resolvedLabel = "label" in card && card.label != null ? card.label : t(card.labelKey);
                const displayLabel: string =
                  typeof resolvedLabel === "string" ? resolvedLabel : "";
                const cardContent = (
                  <motion.div
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.25 + index * 0.1, duration: 0.35 }}
                    whileHover={{ scale: 1.02, y: -4 }}
                    className="relative overflow-hidden rounded-2xl p-6
                      bg-white/[0.12] backdrop-blur-2xl
                      border border-white/25 shadow-2xl shadow-black/25
                      hover:bg-white/[0.18] hover:border-white/40 hover:shadow-accent-500/20 hover:shadow-[0_0_40px_-8px_rgba(231,62,133,0.5)]
                      transition-all duration-300 ease-out group"
                  >
                    <div className="absolute inset-0 bg-gradient-to-br from-white/[0.08] to-transparent pointer-events-none" />
                    <div className="relative flex items-center gap-5">
                      <div className="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center shadow-lg border border-white/20 group-hover:bg-accent-500/30 group-hover:border-accent-300/40 transition-all duration-300 shrink-0">
                        <card.icon className="w-8 h-8 text-white drop-shadow-sm" />
                      </div>
                      <div className="min-w-0">
                        <div className="text-3xl font-bold tabular-nums text-white tracking-tight drop-shadow-sm">
                          {displayValue}
                        </div>
                        <div className="text-sm font-medium text-white/70 mt-0.5">
                          {displayLabel}
                        </div>
                      </div>
                    </div>
                  </motion.div>
                );
                return card.external ? (
                  <a
                    key={card.labelKey}
                    href={card.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="block focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent rounded-2xl"
                  >
                    {cardContent}
                  </a>
                ) : (
                  <Link key={card.labelKey} href={card.href}>
                    {cardContent}
                  </Link>
                );
              })}
            </div>
          </motion.div>
        </div>

        {/* Scroll indicator — clickable, smooth-scrolls to first section */}
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.8 }}
          className="absolute bottom-6 sm:bottom-8 start-1/2 -translate-x-1/2 rtl:translate-x-1/2 text-white/60"
        >
          <a
            href="#content"
            className="inline-flex items-center justify-center p-2 rounded-full text-white/60 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent"
            aria-label="Scroll to content"
          >
            <motion.div
              animate={{ y: [0, 8, 0] }}
              transition={{ repeat: Infinity, duration: 2 }}
            >
              <ArrowDown className="w-6 h-6" />
            </motion.div>
          </a>
        </motion.div>
      </div>
    </section>
  );
}
