"use client";

import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { useAppData } from "@/context/AppContext";
import { CauseCard } from "./CauseCard";
import { useScrollAnimation } from "@/hooks/useScrollAnimation";

export function OpenCauses() {
  const t = useTranslations();
  const locale = useLocale();
  const { openCauses } = useAppData();
  const [ref, isInView] = useScrollAnimation();

  if (!openCauses || openCauses.length === 0) return null;

  return (
    <section
      ref={ref}
      id="open-causes"
      className="py-24 bg-slate-50 dark:bg-slate-800/50"
    >
      <div className="container-custom">
        {/* Section Header */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="text-center mb-16"
        >
          <h2 className="section-heading text-slate-900 dark:text-white mb-4">
            {t("OPEN_CAUSES_HEADER_LABEL")}
          </h2>
          <div className="divider mb-4" />
        </motion.div>

        {/* Causes Grid — centered when fewer than full row */}
        <div className="flex flex-wrap justify-center gap-8">
          {openCauses.map((cause, index) => (
            <div key={cause.id} className="w-full md:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.5rem)]">
              <CauseCard cause={cause} index={index} />
            </div>
          ))}
        </div>

        
      </div>
    </section>
  );
}
