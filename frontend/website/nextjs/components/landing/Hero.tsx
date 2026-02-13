"use client";

import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { useAppData } from "@/context/AppContext";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { Link } from "@/i18n/navigation";
import { Heart, ArrowDown, Users, Globe2, HandHeart } from "lucide-react";

const fadeInUp = {
  hidden: { opacity: 0, y: 30 },
  visible: (i: number) => ({
    opacity: 1,
    y: 0,
    transition: { delay: i * 0.15, duration: 0.6, ease: "easeOut" as const },
  }),
};

const statsData = [
  { icon: Users, label: "Community Members", value: "500+" },
  { icon: Globe2, label: "Countries Reached", value: "3+" },
  { icon: HandHeart, label: "Lives Impacted", value: "10K+" },
];

export function Hero() {
  const t = useTranslations();
  const rawT = useRawTranslation();
  const locale = useLocale();
  const { pageContents, settings } = useAppData();

  const header = pageContents?.LANDING?.HEADER;
  const ctas = header?.ctas || [];
  // Hero image: from CMS (e.g. your gallery) or placeholder for now
  const heroImageUrl =
    header?.main_header_img ||
    "https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=900&q=80";

  return (
    <section className="relative min-h-screen flex items-center overflow-hidden">
      {/* Rich gradient background — reacts to dark mode via CSS variables */}
      <div
        className="absolute inset-0 transition-[background] duration-500"
        style={{ background: "var(--hero-bg)" }}
      />
      {/* Animated gradient overlay for subtle movement */}
      <div
        className="absolute inset-0 opacity-40 dark:opacity-30 transition-opacity duration-500 animated-gradient"
        style={{
          background: "linear-gradient(135deg, rgba(199,28,105,0.25) 0%, transparent 40%, rgba(231,62,133,0.15) 70%, transparent 100%)",
          backgroundSize: "200% 200%",
        }}
      />
      {/* Radial spotlight behind content */}
      <div
        className="absolute inset-0 opacity-60 transition-opacity duration-500"
        style={{ background: "var(--hero-spotlight)" }}
      />
      {/* Hero image as background flavour — fades into gradient, no canvas */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden>
        <div className="absolute inset-0 flex justify-end">
          <div className="relative w-full max-w-[55%] min-w-[280px] h-full">
            <img
              src={heroImageUrl}
              alt=""
              className="absolute inset-0 w-full h-full object-cover object-center opacity-30 dark:opacity-25"
              loading="eager"
              onError={(e) => {
                e.currentTarget.style.display = "none";
              }}
            />
            {/* Fade image into background so it feels like a shade, not a card */}
            <div
              className="absolute inset-0 w-full"
              style={{
                background: "linear-gradient(to right, transparent 0%, rgba(45,3,20,0.4) 45%, rgba(26,2,12,0.97) 85%, rgba(26,2,12,1) 100%)",
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

      <div className="container-custom relative z-10 py-32 lg:py-0">
        <div className="grid lg:grid-cols-2 gap-12 items-center min-h-screen">
          {/* Text Content */}
          <div className="text-white">
            <motion.div
              custom={0}
              initial="hidden"
              animate="visible"
              variants={fadeInUp}
              className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/30 shadow-lg shadow-black/20 mb-8 font-display font-semibold"
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
              className="display-headline text-4xl sm:text-5xl lg:text-6xl xl:text-7xl leading-[1.1] mb-6 text-white shine-sweep"
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
              className="text-lg sm:text-xl text-white/85 leading-relaxed mb-10 max-w-lg font-medium"
            >
              {rawT(header?.main_header_bottom || "LANDING_MAIN_HEADER_BOTTOM")}
            </motion.p>

            <motion.div
              custom={3}
              initial="hidden"
              animate="visible"
              variants={fadeInUp}
              className="flex flex-wrap items-center gap-4"
            >
              {ctas.length > 0 ? (
                ctas.map((cta) => {
                  const isInternalLink = cta.url_type === "internal";
                  const linkHref = isInternalLink ? `/${cta.url}` : cta.url;

                  return cta.style === "dark" ? (
                    <Link
                      key={cta.id}
                      href={linkHref}
                      className="btn font-display font-semibold bg-accent-500 text-white border-2 border-white/30 hover:bg-accent-600 shadow-xl shadow-black/30 hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] active:scale-[0.98] px-8 py-4 text-base transition-all duration-300 rounded-2xl"
                    >
                      <Heart className="w-5 h-5" />
                      {rawT(cta.label)}
                    </Link>
                  ) : (
                    <Link
                      key={cta.id}
                      href={linkHref}
                      className="btn font-display font-semibold bg-white/10 backdrop-blur-md text-white border-2 border-white/30 hover:bg-white/20 hover:border-white/50 shadow-lg shadow-black/10 hover:shadow-xl px-8 py-4 text-base transition-all duration-300 inline-flex items-center gap-2 rounded-2xl"
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
                    className="btn font-display font-semibold bg-accent-500 text-white border-2 border-white/30 hover:bg-accent-600 shadow-xl shadow-black/30 hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] active:scale-[0.98] px-8 py-4 text-base transition-all duration-300 rounded-2xl"
                  >
                    <Heart className="w-5 h-5" />
                    {t("OPEN_CAUSES_DONATE_NOW_LABEL")}
                  </Link>
                  <Link
                    href="/about"
                    className="btn font-display font-semibold bg-white/10 backdrop-blur-md text-white border-2 border-white/30 hover:bg-white/20 hover:border-white/50 shadow-lg shadow-black/10 hover:shadow-xl px-8 py-4 text-base transition-all duration-300 inline-flex items-center gap-2 rounded-2xl"
                  >
                    <Users className="w-5 h-5 shrink-0" />
                    {t("TOP_NAV_ABOUT_LABEL")}
                  </Link>
                </>
              )}
            </motion.div>
          </div>

          {/* Stats */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8, delay: 0.4 }}
            className="hidden lg:block"
          >
            <div className="grid grid-cols-1 gap-6 max-w-sm ms-auto">
              {statsData.map((stat, index) => (
                <motion.div
                  key={stat.label}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.6 + index * 0.2, duration: 0.5 }}
                  className="bg-white/10 backdrop-blur-xl border border-white/30 rounded-2xl p-6 shadow-xl shadow-black/20 hover:bg-white/20 hover:shadow-2xl hover:-translate-y-2 hover:border-white/40 transition-all duration-300 group"
                >
                  <div className="flex items-center gap-4">
                    <div className="w-14 h-14 rounded-xl bg-white/15 flex items-center justify-center group-hover:bg-accent-500/25 shadow-inner border border-white/10 transition-colors">
                      <stat.icon className="w-7 h-7 text-accent-50" />
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-white">
                        {stat.value}
                      </div>
                      <div className="text-sm text-white/60">
                        {stat.label}
                      </div>
                    </div>
                  </div>
                </motion.div>
              ))}
            </div>
          </motion.div>
        </div>

        {/* Scroll indicator */}
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 1.5 }}
          className="absolute bottom-8 start-1/2 -translate-x-1/2 rtl:translate-x-1/2 text-white/60"
        >
          <motion.div
            animate={{ y: [0, 8, 0] }}
            transition={{ repeat: Infinity, duration: 2 }}
          >
            <ArrowDown className="w-6 h-6" />
          </motion.div>
        </motion.div>
      </div>
    </section>
  );
}
