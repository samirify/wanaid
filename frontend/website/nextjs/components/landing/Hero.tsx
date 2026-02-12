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
    transition: { delay: i * 0.15, duration: 0.6, ease: "easeOut" },
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

  return (
    <section className="relative min-h-screen flex items-center overflow-hidden">
      {/* Animated Background — deep indigo gradient */}
      <div className="absolute inset-0 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 dark:from-slate-900 dark:via-primary-950/60 dark:to-slate-900">
        {/* Decorative orbs */}
        <div className="absolute top-20 start-10 w-72 h-72 bg-primary-400/20 rounded-full blur-3xl animate-float" />
        <div
          className="absolute bottom-20 end-10 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl animate-float"
          style={{ animationDelay: "3s" }}
        />
        <div className="absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary-500/5 rounded-full blur-3xl" />

        {/* Grid pattern overlay */}
        <div
          className="absolute inset-0 opacity-[0.03]"
          style={{
            backgroundImage:
              "radial-gradient(circle at 1px 1px, white 1px, transparent 0)",
            backgroundSize: "40px 40px",
          }}
        />
      </div>

      <div className="container-custom relative z-10 py-32 lg:py-0">
        <div className="grid lg:grid-cols-2 gap-12 items-center min-h-screen">
          {/* Text Content */}
          <div className="text-white">
            <motion.div
              custom={0}
              initial="hidden"
              animate="visible"
              variants={fadeInUp}
              className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-8"
            >
              <Heart className="w-4 h-4 text-accent-50" />
              <span className="text-sm font-medium text-white/90">
                {rawT(header?.main_header_top || "LANDING_MAIN_HEADER_TOP")}
              </span>
            </motion.div>

            <motion.h1
              custom={1}
              initial="hidden"
              animate="visible"
              variants={fadeInUp}
              className="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight mb-6"
            >
              <span className="text-white">
                {rawT(
                  header?.main_header_middle_big ||
                    "LANDING_MAIN_HEADER_MIDDLE_BIG"
                )}
              </span>
            </motion.h1>

            <motion.p
              custom={2}
              initial="hidden"
              animate="visible"
              variants={fadeInUp}
              className="text-lg sm:text-xl text-white/80 leading-relaxed mb-10 max-w-lg"
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
                      className="btn bg-accent-500 text-white hover:bg-accent-600 shadow-lg shadow-accent-500/30 hover:shadow-xl hover:shadow-accent-500/40 px-8 py-4 text-base"
                    >
                      <Heart className="w-5 h-5" />
                      {rawT(cta.label)}
                    </Link>
                  ) : (
                    <Link
                      key={cta.id}
                      href={linkHref}
                      className="btn bg-white/10 backdrop-blur-sm text-white border-2 border-white/30 hover:bg-white/20 px-8 py-4 text-base"
                    >
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
                    className="btn bg-accent-500 text-white hover:bg-accent-600 shadow-lg shadow-accent-500/30 hover:shadow-xl hover:shadow-accent-500/40 px-8 py-4 text-base"
                  >
                    <Heart className="w-5 h-5" />
                    {t("OPEN_CAUSES_DONATE_NOW_LABEL")}
                  </Link>
                  <Link
                    href="/about"
                    className="btn bg-white/10 backdrop-blur-sm text-white border-2 border-white/30 hover:bg-white/20 px-8 py-4 text-base"
                  >
                    {t("TOP_NAV_ABOUT_LABEL")}
                  </Link>
                </>
              )}
            </motion.div>
          </div>

          {/* Stats / Visual Side */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8, delay: 0.4 }}
            className="hidden lg:block"
          >
            <div className="relative">
              {/* Floating stats cards */}
              <div className="grid grid-cols-1 gap-6 max-w-sm ms-auto">
                {statsData.map((stat, index) => (
                  <motion.div
                    key={stat.label}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.6 + index * 0.2, duration: 0.5 }}
                    className="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6 hover:bg-white/15 transition-all duration-300 group"
                  >
                    <div className="flex items-center gap-4">
                      <div className="w-14 h-14 rounded-xl bg-white/10 flex items-center justify-center group-hover:bg-accent-500/20 transition-colors">
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
