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
      className="relative py-20 sm:py-28 overflow-hidden border-t-4 border-primary-500 bg-gradient-to-b from-primary-100 via-primary-50 to-white dark:from-primary-950/80 dark:via-primary-950/50 dark:to-slate-900"
      aria-label={t("OPEN_CAUSES_HEADER_LABEL")}
    >
      {/* Ambient orbs — keep subtle glow */}
      <div
        className="absolute inset-0 pointer-events-none"
        aria-hidden
      >
        <div className="absolute top-1/4 -left-40 w-80 h-80 rounded-full bg-primary-500/15 dark:bg-primary-500/20 blur-3xl" />
        <div className="absolute bottom-1/4 -right-40 w-96 h-96 rounded-full bg-primary-400/15 dark:bg-primary-500/15 blur-3xl" />
      </div>

      <div className="container-custom relative">
        <header className="mb-14 sm:mb-16 text-center">
          <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 dark:text-white tracking-tight max-w-2xl mx-auto">
            {t("OPEN_CAUSES_HEADER_LABEL")}
          </h2>
          <div className="mt-4 h-1 w-16 bg-primary-500 rounded-full mx-auto" />
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
