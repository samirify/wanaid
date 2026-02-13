"use client";

import { useTranslations } from "next-intl";
import { useAppData } from "@/context/AppContext";
import { CauseStrip } from "./CauseStrip";

export function OpenCauses() {
  const t = useTranslations();
  const { openCauses } = useAppData();

  if (!openCauses || openCauses.length === 0) return null;

  return (
    <section
      id="open-causes"
      className="relative py-20 sm:py-28 overflow-hidden bg-gradient-to-b from-primary-100/90 via-primary-50/80 to-white dark:from-primary-950/80 dark:via-primary-950/50 dark:to-slate-900"
      aria-label={t("OPEN_CAUSES_HEADER_LABEL")}
    >
      {/* Ambient orbs */}
      <div
        className="absolute inset-0 pointer-events-none"
        aria-hidden
      >
        <div className="absolute top-1/4 -left-40 w-80 h-80 rounded-full bg-primary-500/15 dark:bg-primary-500/20 blur-3xl animate-float" />
        <div className="absolute bottom-1/4 -right-40 w-96 h-96 rounded-full bg-primary-400/15 dark:bg-primary-500/15 blur-3xl animate-float" style={{ animationDelay: "1.5s" }} />
      </div>

      <div className="container-custom relative">
        <header className="mb-14 sm:mb-16 text-center">
          <h2 className="section-heading text-slate-900 dark:text-white max-w-2xl mx-auto">
            {t("OPEN_CAUSES_HEADER_LABEL")}
          </h2>
          <div className="mt-5 h-1 w-20 bg-gradient-to-r from-primary-500 to-primary-400 rounded-full mx-auto" />
        </header>

        <div className="max-w-6xl mx-auto space-y-8 sm:space-y-10">
          {openCauses.map((cause, index) => (
            <CauseStrip
              key={cause.id}
              cause={cause}
              index={index}
              imageOnLeft={index % 2 === 0}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
