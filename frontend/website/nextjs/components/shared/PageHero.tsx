"use client";

import { useId, useLayoutEffect } from "react";
import { motion } from "framer-motion";
import { mediaUrl } from "@/lib/utils";

const curveSvg = (
  <svg
    className="page-hero-wave-fill w-full h-16 shrink-0 rtl:-scale-x-100"
    viewBox="0 0 1440 64"
    fill="none"
    preserveAspectRatio="none"
    aria-hidden
  >
    <path d="M0 64V32C240 0 480 0 720 32s480 32 720 32v32H0z" fill="currentColor" />
  </svg>
);

export type PageHeroVariant = "fixed" | "auto";
export type PageHeroAlign = "start" | "center";

export interface PageHeroProps {
  /** Main heading (required) */
  title: React.ReactNode;
  /** Optional line above the title (e.g. category or label) */
  topLine?: React.ReactNode;
  /** Optional line below the title (e.g. description) */
  bottomLine?: React.ReactNode;
  /** Optional background image URL (faded behind gradient) */
  headerImageUrl?: string | null;
  /** "fixed" = fixed height like cause detail; "auto" = min-height, grows with content like blog */
  variant?: PageHeroVariant;
  /** Text alignment: "start" (left) or "center" */
  align?: PageHeroAlign;
  /** Show curved bottom edge (wave) */
  showCurve?: boolean;
  /** Optional slot on the right when align=start (e.g. spacer for overlapping widget) */
  trailingSlot?: React.ReactNode;
  /** Use <header> when true (e.g. blog), <div> when false (e.g. cause with overlapping section) */
  asHeader?: boolean;
  /** Extra class names for the outer wrapper */
  className?: string;
  /** Called with the title string when rendered (for document title). Plain string only, no HTML. */
  onTitleResolved?: (title: string | null) => void;
}

const motionProps = {
  initial: { opacity: 0, y: 16 },
  animate: { opacity: 1, y: 0 },
  transition: { duration: 0.4, ease: "easeOut" },
} as const;

export function PageHero({
  title,
  topLine,
  bottomLine,
  headerImageUrl,
  variant = "auto",
  align = "center",
  showCurve = true,
  trailingSlot,
  asHeader = false,
  className = "",
  onTitleResolved,
}: PageHeroProps) {
  const id = useId();
  const titleStr = typeof title === "string" ? title.replace(/<[^>]*>/g, "").trim() || null : null;
  useLayoutEffect(() => {
    onTitleResolved?.(titleStr ?? null);
    return () => onTitleResolved?.(null);
  }, [titleStr, onTitleResolved]);
  const isFixed = variant === "fixed";
  const isCenter = align === "center";
  const wave1 = `${id}-w1`;
  const wave2 = `${id}-w2`;
  const wave3 = `${id}-w3`;

  const wrapperClass =
    "relative flex flex-col overflow-hidden transition-[background] duration-300 " +
    (asHeader ? "pt-24 md:pt-28 " : "") +
    (isFixed
      ? "h-[min(36rem,36vh)] min-h-[280px] lg:h-[32rem] lg:min-h-[320px] justify-center"
      : "min-h-[260px]") +
    (className ? ` ${className}` : "");

  const contentPadding = isCenter
    ? "container-custom w-full mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24 md:pt-10 md:pb-28"
    : "container-custom w-full py-16 pt-28 pb-20";

  const contentInnerClass = isCenter
    ? "flex flex-col items-center text-center max-w-6xl mx-auto"
    : "flex flex-col lg:flex-row lg:items-center lg:justify-between gap-10";

  const titleClass = isCenter
    ? "display-headline text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold text-white leading-normal w-full"
    : "display-headline text-4xl sm:text-5xl lg:text-6xl font-bold mb-4 text-white leading-normal";

  const topLineClass = "font-display text-primary-200 font-medium mb-3";
  const bottomLineClass = isCenter
    ? "text-white/85 text-lg mt-4 max-w-4xl text-center leading-relaxed"
    : "text-white/85 text-lg max-w-2xl";

  const Wrapper = asHeader ? "header" : "div";

  return (
    <Wrapper
      className={wrapperClass}
      style={{ background: "var(--hero-bg)" }}
    >
      {/* Header image as background flavour (faded) */}
      {headerImageUrl && (
        <div className="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden>
          <img
            src={mediaUrl(headerImageUrl)}
            alt=""
            className="absolute inset-0 w-full h-full object-cover opacity-20 dark:opacity-15"
          />
          <div
            className="absolute inset-0 bg-gradient-to-r from-primary-900/95 via-primary-900/70 to-transparent dark:from-slate-900/95 dark:via-slate-900/80"
            aria-hidden
          />
        </div>
      )}
      {/* Radial spotlight — matches landing hero; respects dark mode and RTL */}
      <div
        className="absolute inset-0 pointer-events-none opacity-70 dark:opacity-60 transition-opacity duration-300"
        style={{ background: "var(--hero-spotlight)" }}
        aria-hidden
      />
      {/* Decorative orbs — soft lighting like landing hero; scaled for smaller hero */}
      <div className="absolute top-0 -start-16 w-64 h-64 sm:w-80 sm:h-80 bg-primary-400/25 dark:bg-primary-500/12 rounded-full blur-[80px] animate-float pointer-events-none transition-opacity duration-300" aria-hidden />
      <div
        className="absolute bottom-0 -end-16 w-72 h-72 sm:w-96 sm:h-96 bg-primary-500/20 dark:bg-primary-600/10 rounded-full blur-[90px] animate-float pointer-events-none transition-opacity duration-300"
        style={{ animationDelay: "2s" }}
        aria-hidden
      />
      <div
        className="absolute top-1/2 end-1/4 w-40 h-40 sm:w-52 sm:h-52 bg-white/5 dark:bg-slate-400/5 rounded-full blur-3xl animate-float pointer-events-none transition-opacity duration-300"
        style={{ animationDelay: "1s" }}
        aria-hidden
      />
      <div className="absolute top-1/3 end-1/3 w-32 h-32 sm:w-40 sm:h-40 bg-primary-300/15 dark:bg-primary-500/8 rounded-full blur-2xl animate-float pointer-events-none transition-opacity duration-300" style={{ animationDelay: "0.5s" }} aria-hidden />
      {/* Floating particles — gentle drift + twinkle */}
      <div
        className="page-hero-particles absolute inset-0 overflow-hidden pointer-events-none opacity-70 dark:opacity-60 transition-opacity duration-300 rtl:-scale-x-100"
        aria-hidden
      >
        <svg className="absolute inset-0 w-full h-full" viewBox="0 0 1440 400" fill="none" preserveAspectRatio="xMidYMid slice">
          {[
            [8, 12, 24, 48, 72, 96, 120, 144].flatMap((x) =>
              [40, 80, 120, 160, 200, 240, 280, 320, 360].map((y, i) => (
                <circle
                  key={`${x}-${y}-${i}`}
                  cx={(x * 17 + (y % 3) * 31) % 1440}
                  cy={(y + (x % 5) * 22) % 400}
                  r={1.2 + (x % 3) * 0.4}
                  fill="rgba(255,255,255,0.5)"
                  className="page-hero-particle"
                  style={{ animationDelay: `${(x + y) % 5 * 0.4}s` }}
                />
              ))
            ),
          ].flat()}
          {[
            [200, 400, 600, 800, 1000, 1200].flatMap((x) =>
              [60, 140, 220, 300].map((y, i) => (
                <circle
                  key={`p-${x}-${y}-${i}`}
                  cx={(x + (i * 47) % 80) % 1440}
                  cy={y}
                  r={2 + (i % 2)}
                  fill="rgba(231,62,133,0.35)"
                  className="page-hero-particle-accent dark:fill-[rgba(199,28,105,0.28)]"
                  style={{ animationDelay: `${(x + i) % 4 * 0.5}s` }}
                />
              ))
            ),
          ].flat()}
        </svg>
      </div>
      {/* Soft flowing waves + shimmer */}
      <div
        className="page-hero-waves-drift absolute inset-0 overflow-hidden pointer-events-none opacity-[0.18] dark:opacity-[0.12] transition-opacity duration-300 rtl:scale-x-[-1]"
        aria-hidden
      >
        <svg className="absolute bottom-0 left-0 w-[120%] h-full min-h-[280px]" viewBox="0 0 1200 280" fill="none" preserveAspectRatio="none">
          <path
            d="M0 120 Q300 80 600 120 T1200 120 V280 H0 Z"
            fill={`url(#${wave1})`}
          />
          <path
            d="M0 160 Q300 200 600 160 T1200 160 V280 H0 Z"
            fill={`url(#${wave2})`}
          />
          <path
            d="M0 200 Q400 140 800 200 T1200 200 V280 H0 Z"
            fill={`url(#${wave3})`}
            className="opacity-80"
          />
          <defs>
            <linearGradient id={wave1} x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stopColor="white" stopOpacity="0" />
              <stop offset="100%" stopColor="white" stopOpacity="0.5" />
            </linearGradient>
            <linearGradient id={wave2} x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stopColor="white" stopOpacity="0" />
              <stop offset="100%" stopColor="white" stopOpacity="0.32" />
            </linearGradient>
            <linearGradient id={wave3} x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stopColor="white" stopOpacity="0" />
              <stop offset="100%" stopColor="white" stopOpacity="0.2" />
            </linearGradient>
          </defs>
        </svg>
        {/* Moving shimmer across waves */}
        <div
          className="page-hero-wave-shimmer absolute inset-0 pointer-events-none rtl:scale-x-[-1]"
          style={{
            background: "linear-gradient(105deg, transparent 0%, rgba(255,255,255,0.15) 45%, rgba(255,255,255,0.35) 50%, rgba(255,255,255,0.15) 55%, transparent 100%)",
            backgroundSize: "60% 100%",
          }}
          aria-hidden
        />
        {/* Soft glow at bottom center */}
        <div
          className="absolute bottom-0 left-1/2 -translate-x-1/2 w-[80%] max-w-2xl h-32 pointer-events-none opacity-60 dark:opacity-40"
          style={{
            background: "radial-gradient(ellipse 80% 100% at 50% 100%, rgba(255,255,255,0.2) 0%, transparent 70%)",
          }}
          aria-hidden
        />
      </div>
      {/* Subtle dot grid texture — matches landing hero */}
      <div
        className="absolute inset-0 opacity-[0.04] dark:opacity-[0.06] pointer-events-none transition-opacity duration-300"
        style={{
          backgroundImage: "radial-gradient(circle at 1px 1px, white 1px, transparent 0)",
          backgroundSize: "32px 32px",
        }}
        aria-hidden
      />
      <div
        className={`relative z-10 ${contentPadding} ${isFixed ? "h-full flex flex-col justify-center" : ""}`}
      >
        <div className={contentInnerClass}>
          <div className={isCenter ? undefined : "lg:flex-1 text-white shrink-0"}>
            {topLine != null && topLine !== "" && (
              <motion.p {...motionProps} className={topLineClass}>
                {topLine}
              </motion.p>
            )}
            <motion.h1
              {...motionProps}
              transition={{ ...motionProps.transition, delay: 0.05 }}
              className={titleClass}
            >
              {title}
            </motion.h1>
            {bottomLine != null && bottomLine !== "" && (
              <>
                {isCenter && (
                  <div
                    className="mt-4 h-0.5 w-16 bg-white/50 rounded-full shrink-0 mx-auto"
                    aria-hidden
                  />
                )}
                <motion.p
                  {...motionProps}
                  transition={{ ...motionProps.transition, delay: 0.1 }}
                  className={bottomLineClass + (isCenter ? " mt-4" : "")}
                >
                  {bottomLine}
                </motion.p>
              </>
            )}
          </div>
          {!isCenter && trailingSlot != null ? trailingSlot : null}
        </div>
      </div>
      {showCurve &&
        (isFixed ? (
          <div className="absolute bottom-0 left-0 right-0 w-full pointer-events-none">
            {curveSvg}
          </div>
        ) : (
          <div className="relative w-full pointer-events-none">{curveSvg}</div>
        ))}
    </Wrapper>
  );
}
