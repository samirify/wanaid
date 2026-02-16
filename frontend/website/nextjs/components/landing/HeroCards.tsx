"use client";

import { motion, animate } from "framer-motion";
import { useEffect, useLayoutEffect, useRef, useState } from "react";
import type { ElementType } from "react";
import { Link } from "@/i18n/navigation";

/* ─── RTL detection hook ──────────────────────────────────────────────── */

function useIsRtl() {
  const [isRtl, setIsRtl] = useState(false);
  useLayoutEffect(() => {
    setIsRtl(document.documentElement.dir === "rtl");
  }, []);
  return isRtl;
}

/* ─── Types ───────────────────────────────────────────────────────────── */

export interface HeroCardData {
  icon: ElementType;
  value?: number;
  valueDisplay?: string;
  label: string;
  href: string;
  external: boolean;
}

/* ─── Animated Number Counter ─────────────────────────────────────────── */

function AnimatedNumber({
  value,
  delay = 0,
}: {
  value: number;
  delay?: number;
}) {
  const ref = useRef<HTMLSpanElement>(null);

  useEffect(() => {
    const node = ref.current;
    if (!node) return;
    let controls: ReturnType<typeof animate> | undefined;

    const timeout = setTimeout(() => {
      controls = animate(0, value, {
        duration: 2.2,
        ease: [0.16, 1, 0.3, 1],
        onUpdate(v) {
          node.textContent = String(Math.round(v));
        },
      });
    }, delay * 1000);

    return () => {
      clearTimeout(timeout);
      controls?.stop();
    };
  }, [value, delay]);

  return <span ref={ref}>0</span>;
}

/* ─── Mini SVG Decorations ────────────────────────────────────────────── */

function MiniShape({
  type,
  size,
  color,
}: {
  type: string;
  size: number;
  color: string;
}) {
  switch (type) {
    case "heart":
      return (
        <svg width={size} height={size} viewBox="0 0 24 24" fill={color}>
          <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
      );
    case "star":
      return (
        <svg width={size} height={size} viewBox="0 0 24 24" fill={color}>
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
        </svg>
      );
    case "sparkle":
      return (
        <svg width={size} height={size} viewBox="0 0 24 24" fill={color}>
          <path d="M12 0L14.59 8.41 20 12l-5.41 3.59L12 24l-2.59-8.41L4 12l5.41-3.59L12 0z" />
        </svg>
      );
    case "circle":
      return (
        <svg width={size} height={size} viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" fill={color} />
        </svg>
      );
    default:
      return null;
  }
}

/* ─── Card Configurations — unique personality per card ────────────────── */

interface DecoConfig {
  type: string;
  x: string;
  y: string;
  size: number;
  delay: number;
  duration: number;
}

interface CardConfig {
  gradient: string;
  iconGradient: string;
  ringColor: string;
  glowColor: string;
  accentColor: string;
  decos: DecoConfig[];
  rotation: number;
  offsetX: number;
  patternSize: string;
}

const CARD_CONFIGS: CardConfig[] = [
  {
    // Open Causes — warm, compassionate rose
    gradient: "from-rose-500/20 via-pink-400/15 to-fuchsia-400/10",
    iconGradient: "from-rose-400 to-pink-500",
    ringColor: "rgba(251,113,133,0.35)",
    glowColor: "rgba(244,63,94,0.35)",
    accentColor: "#fb7185",
    decos: [
      { type: "heart", x: "82%", y: "8%", size: 12, delay: 0, duration: 4.5 },
      {
        type: "heart",
        x: "6%",
        y: "70%",
        size: 9,
        delay: 1.2,
        duration: 3.8,
      },
      {
        type: "sparkle",
        x: "90%",
        y: "65%",
        size: 8,
        delay: 0.6,
        duration: 3.2,
      },
      {
        type: "sparkle",
        x: "14%",
        y: "15%",
        size: 7,
        delay: 2.1,
        duration: 4,
      },
    ],
    rotation: -2,
    offsetX: -10,
    patternSize: "16px 16px",
  },
  {
    // Blog — golden, inspiring amber
    gradient: "from-amber-400/20 via-yellow-400/15 to-orange-300/10",
    iconGradient: "from-amber-400 to-yellow-500",
    ringColor: "rgba(251,191,36,0.35)",
    glowColor: "rgba(245,158,11,0.35)",
    accentColor: "#fbbf24",
    decos: [
      {
        type: "star",
        x: "85%",
        y: "12%",
        size: 11,
        delay: 0.3,
        duration: 4,
      },
      {
        type: "star",
        x: "8%",
        y: "62%",
        size: 8,
        delay: 1.5,
        duration: 3.5,
      },
      {
        type: "sparkle",
        x: "80%",
        y: "70%",
        size: 7,
        delay: 0.9,
        duration: 3.8,
      },
      {
        type: "sparkle",
        x: "18%",
        y: "20%",
        size: 9,
        delay: 2.4,
        duration: 3.3,
      },
    ],
    rotation: 1.5,
    offsetX: 18,
    patternSize: "18px 18px",
  },
  {
    // Community — cool, connected sky blue
    gradient: "from-sky-400/20 via-blue-400/15 to-indigo-400/10",
    iconGradient: "from-sky-400 to-blue-500",
    ringColor: "rgba(56,189,248,0.35)",
    glowColor: "rgba(14,165,233,0.35)",
    accentColor: "#38bdf8",
    decos: [
      {
        type: "circle",
        x: "84%",
        y: "10%",
        size: 10,
        delay: 0.2,
        duration: 4.2,
      },
      {
        type: "circle",
        x: "10%",
        y: "66%",
        size: 7,
        delay: 1.3,
        duration: 3.6,
      },
      {
        type: "sparkle",
        x: "90%",
        y: "60%",
        size: 8,
        delay: 0.7,
        duration: 3.4,
      },
      {
        type: "circle",
        x: "16%",
        y: "22%",
        size: 9,
        delay: 2.6,
        duration: 4.4,
      },
    ],
    rotation: -1,
    offsetX: -6,
    patternSize: "20px 20px",
  },
];

/* ─── Individual Hero Card ────────────────────────────────────────────── */

function HeroCard({
  card,
  config,
  index,
  rtlFlip,
}: {
  card: HeroCardData;
  config: CardConfig;
  index: number;
  /** 1 for LTR, -1 for RTL — mirrors offsets and rotations */
  rtlFlip: 1 | -1;
}) {
  const Icon = card.icon;
  const displayValue = card.valueDisplay ?? card.value;
  const isNumber = typeof displayValue === "number";

  const content = (
    <motion.div
      initial={{ opacity: 0, y: 40, rotate: 0, scale: 0.92 }}
      animate={{
        opacity: 1,
        y: 0,
        rotate: config.rotation * rtlFlip,
        scale: 1,
        x: config.offsetX * rtlFlip,
      }}
      transition={{
        delay: 0.35 + index * 0.18,
        duration: 0.65,
        ease: [0.16, 1, 0.3, 1],
      }}
      whileHover={{
        scale: 1.06,
        y: -10,
        rotate: 0,
        x: 0,
        transition: { type: "spring", stiffness: 300, damping: 20 },
      }}
      className="relative overflow-hidden rounded-[1.75rem] p-[1.5px] group cursor-pointer"
    >
      {/* Animated rainbow/gradient border */}
      <div
        className="absolute inset-0 rounded-[1.75rem] opacity-30 group-hover:opacity-90 transition-opacity duration-500 hero-card-border-spin"
        style={{
          background: `conic-gradient(from 0deg, ${config.accentColor}, #fbbf24, #34d399, #38bdf8, #a78bfa, ${config.accentColor})`,
        }}
      />

      {/* Card inner body */}
      <div className="relative rounded-[calc(1.75rem-1.5px)] bg-white/[0.06] backdrop-blur-2xl overflow-hidden">
        {/* Unique gradient overlay */}
        <div
          className={`absolute inset-0 bg-gradient-to-br ${config.gradient} opacity-80 group-hover:opacity-100 transition-opacity duration-300`}
        />

        {/* Moving shimmer on hover */}
        <div className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700">
          <div
            className="absolute inset-0 hero-card-shimmer"
            style={{
              background: `linear-gradient(105deg, transparent 35%, ${config.accentColor}20 50%, transparent 65%)`,
              backgroundSize: "300% 100%",
            }}
          />
        </div>

        {/* Subtle dot texture */}
        <div
          className="absolute inset-0 opacity-[0.03] group-hover:opacity-[0.06] transition-opacity duration-300"
          style={{
            backgroundImage:
              "radial-gradient(circle at 1.5px 1.5px, white 0.75px, transparent 0)",
            backgroundSize: config.patternSize,
          }}
        />

        {/* Outer glow on hover */}
        <div
          className="absolute -inset-6 rounded-[3rem] opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-3xl pointer-events-none"
          style={{
            background: `radial-gradient(ellipse at 50% 50%, ${config.glowColor}, transparent 70%)`,
          }}
        />

        {/* Floating micro-decorations */}
        {config.decos.map((deco, i) => (
          <motion.div
            key={i}
            className="absolute pointer-events-none z-20"
            style={{ left: deco.x, top: deco.y }}
            animate={{
              y: [0, -10, 3, 0],
              x: [0, 5, -4, 0],
              scale: [0.55, 1.2, 0.7, 0.55],
              opacity: [0.2, 0.65, 0.3, 0.2],
              rotate: [0, 25, -15, 0],
            }}
            transition={{
              duration: deco.duration,
              delay: deco.delay + index * 0.15,
              repeat: Infinity,
              ease: "easeInOut",
            }}
          >
            <MiniShape
              type={deco.type}
              size={deco.size}
              color={config.accentColor}
            />
          </motion.div>
        ))}

        {/* ── Card content ── */}
        <div className="relative z-10 p-6 flex items-center gap-5">
          {/* Icon with animated concentric rings */}
          <div className="relative shrink-0">
            {/* Outer pulse ring */}
            <motion.div
              className="absolute -inset-2.5 rounded-2xl"
              style={{ border: `2px solid ${config.ringColor}` }}
              animate={{
                scale: [1, 1.4, 1],
                opacity: [0.5, 0, 0.5],
              }}
              transition={{
                duration: 2.8,
                repeat: Infinity,
                ease: "easeInOut",
                delay: index * 0.4,
              }}
            />
            {/* Inner pulse ring */}
            <motion.div
              className="absolute -inset-1 rounded-xl"
              style={{ border: `1.5px solid ${config.ringColor}` }}
              animate={{
                scale: [1, 1.25, 1],
                opacity: [0.4, 0, 0.4],
              }}
              transition={{
                duration: 2.2,
                repeat: Infinity,
                ease: "easeInOut",
                delay: index * 0.4 + 0.7,
              }}
            />

            {/* Icon box */}
            <div
              className="w-16 h-16 rounded-2xl flex items-center justify-center border border-white/25 shadow-lg group-hover:shadow-xl group-hover:border-white/40 transition-all duration-300"
              style={{
                background: `linear-gradient(135deg, ${config.accentColor}44, ${config.accentColor}22)`,
              }}
            >
              <motion.div
                animate={{ rotate: [0, 6, -6, 0] }}
                transition={{
                  duration: 5,
                  repeat: Infinity,
                  ease: "easeInOut",
                  delay: index * 0.5,
                }}
              >
                <Icon className="w-7 h-7 text-white drop-shadow-lg" />
              </motion.div>
            </div>
          </div>

          {/* Number + label */}
          <div className="min-w-0">
            <div className="text-[2rem] font-extrabold tabular-nums text-white tracking-tight drop-shadow-lg leading-tight font-display">
              {isNumber ? (
                <AnimatedNumber
                  value={displayValue as number}
                  delay={0.5 + index * 0.25}
                />
              ) : (
                <motion.span
                  initial={{ opacity: 0, scale: 0.5 }}
                  animate={{ opacity: 1, scale: 1 }}
                  transition={{
                    delay: 0.8 + index * 0.2,
                    duration: 0.5,
                    type: "spring",
                    stiffness: 200,
                  }}
                >
                  {displayValue}
                </motion.span>
              )}
            </div>
            <div className="text-sm font-semibold text-white/65 mt-1 tracking-wide group-hover:text-white/85 transition-colors duration-300">
              {card.label}
            </div>
          </div>
        </div>

        {/* Bottom accent gradient line */}
        <div
          className="absolute bottom-0 inset-x-0 h-[2px] opacity-20 group-hover:opacity-80 transition-opacity duration-400"
          style={{
            background: `linear-gradient(90deg, transparent, ${config.accentColor}, transparent)`,
          }}
        />
      </div>
    </motion.div>
  );

  if (card.external) {
    return (
      <a
        href={card.href}
        target="_blank"
        rel="noopener noreferrer"
        className="block focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent rounded-[1.75rem]"
      >
        {content}
      </a>
    );
  }

  return (
    <Link href={card.href} className="block">
      {content}
    </Link>
  );
}

/* ─── Ambient floating elements around the entire card stack ──────────── */

const AMBIENT_SHAPES = [
  {
    type: "heart",
    x: "-10%",
    y: "4%",
    size: 16,
    color: "#fb718577",
    delay: 0,
    duration: 7,
  },
  {
    type: "star",
    x: "106%",
    y: "28%",
    size: 14,
    color: "#fbbf2477",
    delay: 1.5,
    duration: 6,
  },
  {
    type: "sparkle",
    x: "112%",
    y: "68%",
    size: 11,
    color: "#38bdf877",
    delay: 0.8,
    duration: 5,
  },
  {
    type: "heart",
    x: "-7%",
    y: "88%",
    size: 13,
    color: "#f9a8c977",
    delay: 2.2,
    duration: 8,
  },
  {
    type: "star",
    x: "52%",
    y: "-7%",
    size: 12,
    color: "#fde04777",
    delay: 3,
    duration: 5.5,
  },
  {
    type: "sparkle",
    x: "42%",
    y: "106%",
    size: 10,
    color: "#a78bfa77",
    delay: 1,
    duration: 4.5,
  },
];

/* ─── Main export ─────────────────────────────────────────────────────── */

export function HeroCards({ cards }: { cards: HeroCardData[] }) {
  const isRtl = useIsRtl();
  const rtlFlip: 1 | -1 = isRtl ? -1 : 1;

  return (
    <div className="relative py-6">
      {/* Large ambient glow behind the stack */}
      <div className="absolute -inset-14 pointer-events-none">
        <div className="absolute inset-0 bg-gradient-to-b from-rose-500/[0.07] via-amber-400/[0.04] to-sky-500/[0.07] rounded-[4rem] blur-3xl" />
      </div>

      {/* Decorative connecting thread — mirror in RTL */}
      <svg
        className="absolute inset-0 w-full h-full pointer-events-none z-0 rtl:-scale-x-100"
        viewBox="0 0 320 480"
        preserveAspectRatio="none"
        aria-hidden
      >
        <defs>
          <linearGradient
            id="heroCardThread"
            x1="0%"
            y1="0%"
            x2="0%"
            y2="100%"
          >
            <stop offset="0%" stopColor="#fb7185" stopOpacity="0" />
            <stop offset="15%" stopColor="#fb7185" stopOpacity="0.2" />
            <stop offset="50%" stopColor="#fbbf24" stopOpacity="0.15" />
            <stop offset="85%" stopColor="#38bdf8" stopOpacity="0.2" />
            <stop offset="100%" stopColor="#38bdf8" stopOpacity="0" />
          </linearGradient>
        </defs>
        <motion.path
          d="M160,10 C80,90 240,130 160,220 C80,310 240,360 160,460"
          fill="none"
          stroke="url(#heroCardThread)"
          strokeWidth="2"
          strokeLinecap="round"
          strokeDasharray="8 6"
          initial={{ pathLength: 0, opacity: 0 }}
          animate={{ pathLength: 1, opacity: 0.6 }}
          transition={{ duration: 2.5, delay: 0.8, ease: "easeOut" }}
        />
      </svg>

      {/* Ambient floating shapes */}
      {AMBIENT_SHAPES.map((shape, i) => (
        <motion.div
          key={`ambient-${i}`}
          className="absolute pointer-events-none z-30"
          style={{ left: shape.x, top: shape.y }}
          animate={{
            y: [0, -16, 6, 0],
            x: [0, 8, -6, 0],
            scale: [0.65, 1.15, 0.75, 0.65],
            opacity: [0.25, 0.55, 0.3, 0.25],
            rotate: [0, 30, -20, 0],
          }}
          transition={{
            duration: shape.duration,
            delay: shape.delay,
            repeat: Infinity,
            ease: "easeInOut",
          }}
        >
          <MiniShape type={shape.type} size={shape.size} color={shape.color} />
        </motion.div>
      ))}

      {/* Card stack */}
      <div className="grid grid-cols-1 gap-7 max-w-sm ms-auto relative z-10">
        {cards.map((card, index) => (
          <HeroCard
            key={card.href}
            card={card}
            config={CARD_CONFIGS[index] ?? CARD_CONFIGS[0]}
            index={index}
            rtlFlip={rtlFlip}
          />
        ))}
      </div>
    </div>
  );
}
