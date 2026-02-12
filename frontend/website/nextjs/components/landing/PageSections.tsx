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
  return (
    <section
      className={
        isEven
          ? "pt-8 md:pt-10 lg:pt-12 bg-white dark:bg-slate-900"
          : "pt-8 md:pt-10 lg:pt-12 bg-slate-50/50 dark:bg-slate-800/30"
      }
    >
      <div className="container-custom">
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          className="border-b border-slate-200 dark:border-slate-700 pb-8 md:pb-10 lg:pb-12"
        >
          {hasImage ? (
            <div
              style={{
                display: "grid",
                gridTemplateColumns: "1fr 1fr",
                gap: "2rem",
                alignItems: "center",
              }}
            >
              {isOdd ? (
                <>
                  {/* Odd: content LEFT, image RIGHT */}
                  <div
                    className="prose prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed"
                    style={{ gridColumn: "1" }}
                    dangerouslySetInnerHTML={{ __html: htmlContent }}
                  />
                  <div style={{ gridColumn: "2", textAlign: "end" }}>
                    <img
                      src={pillar.img}
                      alt=""
                      style={{ display: "inline-block", maxWidth: "100%", maxHeight: "300px", objectFit: "contain" }}
                      loading="lazy"
                    />
                  </div>
                </>
              ) : (
                <>
                  {/* Even: image LEFT, content RIGHT */}
                  <div style={{ gridColumn: "1", textAlign: "start" }}>
                    <img
                      src={pillar.img}
                      alt=""
                      style={{ display: "inline-block", maxWidth: "100%", maxHeight: "300px", objectFit: "contain" }}
                      loading="lazy"
                    />
                  </div>
                  <div
                    className="prose prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed"
                    style={{ gridColumn: "2" }}
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
