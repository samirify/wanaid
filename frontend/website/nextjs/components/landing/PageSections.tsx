"use client";

import { FC } from "react";
import { motion } from "framer-motion";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import type { Pillar } from "@/lib/types";

interface PageSectionsProps {
  pillars: Pillar[];
}

export function PageSections({ pillars }: PageSectionsProps) {
  if (!pillars || pillars.length === 0) return null;

  return (
    <>
      {pillars.map((pillar, index) => (
        <FreeText key={pillar.code} pillar={pillar} index={index} />
      ))}
    </>
  );
}

const FreeText: FC<{ pillar: Pillar; index: number }> = ({ pillar, index }) => {
  const rawT = useRawTranslation();

  const htmlContent = rawT(pillar.value) || pillar.value || "";
  const hasImage = !!pillar.img;
  const isOdd = (index + 1) % 2 !== 0; // 1st=odd, 2nd=even, 3rd=odd...

  const isEven = index % 2 === 0;
  const isFirst = index === 0;
  return (
    <section
      className={
        isFirst
          ? "pt-12 md:pt-16 lg:pt-20 pb-8 md:pb-10 lg:pb-12 bg-white dark:bg-slate-900"
          : isEven
            ? "pt-8 md:pt-10 lg:pt-12 pb-8 md:pb-10 lg:pb-12 bg-white dark:bg-slate-900"
            : "pt-8 md:pt-10 lg:pt-12 pb-8 md:pb-10 lg:pb-12 bg-slate-200 dark:bg-slate-800"
      }
    >
      <div className="container-custom">
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          className={!isFirst ? "border-t border-slate-200 dark:border-slate-700 pt-8 md:pt-10 lg:pt-12" : ""}
        >
          {hasImage ? (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 items-center">
              {isOdd ? (
                <>
                  {/* Odd: content LEFT on desktop; on mobile content first, then image */}
                  <div
                    className="prose prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed order-1"
                    dangerouslySetInnerHTML={{ __html: htmlContent }}
                  />
                  <div className="flex justify-center md:justify-end order-2">
                    <img
                      src={pillar.img}
                      alt=""
                      className="w-full max-w-sm md:max-w-full max-h-[280px] md:max-h-[300px] object-contain mx-auto md:mx-0"
                      loading="lazy"
                    />
                  </div>
                </>
              ) : (
                <>
                  {/* Even: image LEFT on desktop; on mobile image first, then content */}
                  <div className="flex justify-center md:justify-start order-1">
                    <img
                      src={pillar.img}
                      alt=""
                      className="w-full max-w-sm md:max-w-full max-h-[280px] md:max-h-[300px] object-contain mx-auto md:mx-0"
                      loading="lazy"
                    />
                  </div>
                  <div
                    className="prose prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed order-2"
                    dangerouslySetInnerHTML={{ __html: htmlContent }}
                  />
                </>
              )}
            </div>
          ) : (
            <div
              className="prose prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed"
              dangerouslySetInnerHTML={{ __html: htmlContent }}
            />
          )}
        </motion.div>
      </div>
    </section>
  );
};

