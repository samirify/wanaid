"use client";

import { motion } from "framer-motion";

/* ─── Floating shape data ──────────────────────────────────────────────── */

interface FloatingShape {
  id: number;
  type: "heart" | "star" | "circle" | "hand" | "plane" | "sparkle";
  x: string; // CSS left %
  y: string; // CSS top %
  size: number; // px
  delay: number; // animation delay (s)
  duration: number; // full cycle (s)
  opacity: number;
  color: string;
  rotate?: number;
}

const SHAPES: FloatingShape[] = [
  // Hearts — the core charity symbol
  { id: 1, type: "heart", x: "8%", y: "15%", size: 38, delay: 0, duration: 7, opacity: 0.35, color: "#f170a0" },
  { id: 2, type: "heart", x: "72%", y: "22%", size: 26, delay: 1.5, duration: 9, opacity: 0.25, color: "#e73e85" },
  { id: 3, type: "heart", x: "45%", y: "70%", size: 32, delay: 3, duration: 8, opacity: 0.3, color: "#ff8ab5" },
  { id: 4, type: "heart", x: "88%", y: "55%", size: 22, delay: 0.8, duration: 10, opacity: 0.2, color: "#f9a8c9" },
  { id: 5, type: "heart", x: "25%", y: "82%", size: 28, delay: 2.2, duration: 7.5, opacity: 0.28, color: "#c71c69" },
  { id: 6, type: "heart", x: "60%", y: "10%", size: 20, delay: 4, duration: 11, opacity: 0.22, color: "#ff6b9d" },

  // Stars — childhood dreams & wonder
  { id: 10, type: "star", x: "18%", y: "35%", size: 18, delay: 0.5, duration: 5, opacity: 0.4, color: "#ffd700" },
  { id: 11, type: "star", x: "55%", y: "45%", size: 14, delay: 2, duration: 4, opacity: 0.35, color: "#ffe44d" },
  { id: 12, type: "star", x: "82%", y: "18%", size: 20, delay: 1, duration: 6, opacity: 0.3, color: "#ffc107" },
  { id: 13, type: "star", x: "35%", y: "60%", size: 16, delay: 3.5, duration: 4.5, opacity: 0.38, color: "#ffd700" },
  { id: 14, type: "star", x: "92%", y: "75%", size: 12, delay: 0.3, duration: 5.5, opacity: 0.32, color: "#ffe082" },

  // Sparkles — magical touch
  { id: 20, type: "sparkle", x: "15%", y: "55%", size: 10, delay: 0, duration: 3, opacity: 0.5, color: "#ffffff" },
  { id: 21, type: "sparkle", x: "40%", y: "25%", size: 8, delay: 1.5, duration: 2.5, opacity: 0.45, color: "#ffe4f0" },
  { id: 22, type: "sparkle", x: "68%", y: "65%", size: 12, delay: 2.5, duration: 3.5, opacity: 0.4, color: "#ffffff" },
  { id: 23, type: "sparkle", x: "90%", y: "40%", size: 9, delay: 0.8, duration: 2.8, opacity: 0.42, color: "#ffd6e8" },
  { id: 24, type: "sparkle", x: "50%", y: "88%", size: 11, delay: 3.2, duration: 3.2, opacity: 0.38, color: "#ffffff" },

  // Circles / bubbles — playful feel
  { id: 30, type: "circle", x: "12%", y: "72%", size: 40, delay: 1, duration: 12, opacity: 0.12, color: "#ff8ab5" },
  { id: 31, type: "circle", x: "78%", y: "35%", size: 55, delay: 2.5, duration: 14, opacity: 0.08, color: "#e73e85" },
  { id: 32, type: "circle", x: "48%", y: "15%", size: 35, delay: 0, duration: 10, opacity: 0.1, color: "#ffd700" },
  { id: 33, type: "circle", x: "30%", y: "45%", size: 45, delay: 3, duration: 13, opacity: 0.09, color: "#f9a8c9" },

  // Hands — children reaching out
  { id: 40, type: "hand", x: "22%", y: "20%", size: 30, delay: 1, duration: 9, opacity: 0.18, color: "#f9a8c9", rotate: -15 },
  { id: 41, type: "hand", x: "75%", y: "70%", size: 26, delay: 3, duration: 11, opacity: 0.15, color: "#ff8ab5", rotate: 20 },

  // Paper planes — hope taking flight
  { id: 50, type: "plane", x: "5%", y: "40%", size: 24, delay: 0, duration: 16, opacity: 0.25, color: "#ffffff", rotate: -30 },
  { id: 51, type: "plane", x: "65%", y: "80%", size: 20, delay: 5, duration: 18, opacity: 0.2, color: "#ffe4f0", rotate: -20 },
];

/* ─── SVG shape renderers ──────────────────────────────────────────────── */

function HeartSVG({ size, color }: { size: number; color: string }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill={color}>
      <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
    </svg>
  );
}

function StarSVG({ size, color }: { size: number; color: string }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill={color}>
      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
    </svg>
  );
}

function SparkleSVG({ size, color }: { size: number; color: string }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill={color}>
      <path d="M12 0L14.59 8.41 20 12l-5.41 3.59L12 24l-2.59-8.41L4 12l5.41-3.59L12 0z" />
    </svg>
  );
}

function CircleSVG({ size, color }: { size: number; color: string }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="11" fill="none" stroke={color} strokeWidth="1.5" opacity="0.6" />
      <circle cx="12" cy="12" r="7" fill={color} opacity="0.3" />
    </svg>
  );
}

function HandSVG({ size, color }: { size: number; color: string }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill={color}>
      <path d="M12 2C9.24 2 7 4.24 7 7v4H5.5C4.12 11 3 12.12 3 13.5v.5c0 4.42 3.58 8 8 8h2c4.42 0 8-3.58 8-8v-3c0-1.38-1.12-2.5-2.5-2.5H17V7c0-2.76-2.24-5-5-5zm0 2c1.66 0 3 1.34 3 3v1h-6V7c0-1.66 1.34-3 3-3z" />
    </svg>
  );
}

function PlaneSVG({ size, color }: { size: number; color: string }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill={color}>
      <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z" />
    </svg>
  );
}

const shapeRenderers: Record<FloatingShape["type"], React.FC<{ size: number; color: string }>> = {
  heart: HeartSVG,
  star: StarSVG,
  sparkle: SparkleSVG,
  circle: CircleSVG,
  hand: HandSVG,
  plane: PlaneSVG,
};

/* ─── Animated floating shapes layer ───────────────────────────────────── */

function FloatingElement({ shape }: { shape: FloatingShape }) {
  const Renderer = shapeRenderers[shape.type];

  const floatY = shape.type === "plane" ? [-20, -40, -20] : [0, -18, 0];
  const floatX =
    shape.type === "plane"
      ? [0, 60, 120]
      : shape.type === "heart"
        ? [0, 8, -6, 0]
        : [0, 5, -5, 0];
  const scaleAnim =
    shape.type === "sparkle"
      ? [0.6, 1.2, 0.6]
      : shape.type === "heart"
        ? [1, 1.12, 1]
        : [1, 1.05, 1];

  return (
    <motion.div
      className="absolute pointer-events-none"
      style={{
        left: shape.x,
        top: shape.y,
        opacity: shape.opacity,
        rotate: shape.rotate ?? 0,
      }}
      animate={{
        y: floatY,
        x: floatX,
        scale: scaleAnim,
        rotate: shape.type === "sparkle" ? [0, 180, 360] : (shape.rotate ?? 0),
      }}
      transition={{
        duration: shape.duration,
        delay: shape.delay,
        repeat: Infinity,
        ease: "easeInOut",
      }}
    >
      <Renderer size={shape.size} color={shape.color} />
    </motion.div>
  );
}

/* ─── Connecting lines / ribbon trail ──────────────────────────────────── */

function RibbonTrail() {
  return (
    <svg
      className="absolute inset-0 w-full h-full pointer-events-none"
      viewBox="0 0 800 600"
      preserveAspectRatio="none"
      aria-hidden
    >
      <defs>
        <linearGradient id="ribbon1" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor="#e73e85" stopOpacity="0" />
          <stop offset="30%" stopColor="#e73e85" stopOpacity="0.15" />
          <stop offset="70%" stopColor="#ff8ab5" stopOpacity="0.15" />
          <stop offset="100%" stopColor="#ff8ab5" stopOpacity="0" />
        </linearGradient>
        <linearGradient id="ribbon2" x1="100%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" stopColor="#ffd700" stopOpacity="0" />
          <stop offset="40%" stopColor="#ffd700" stopOpacity="0.1" />
          <stop offset="60%" stopColor="#ffe082" stopOpacity="0.1" />
          <stop offset="100%" stopColor="#ffe082" stopOpacity="0" />
        </linearGradient>
      </defs>
      <motion.path
        d="M-50,200 C150,100 300,350 500,250 S700,100 850,200"
        fill="none"
        stroke="url(#ribbon1)"
        strokeWidth="3"
        strokeLinecap="round"
        initial={{ pathLength: 0, opacity: 0 }}
        animate={{ pathLength: 1, opacity: 1 }}
        transition={{ duration: 3, delay: 0.5, ease: "easeOut" }}
      />
      <motion.path
        d="M-50,400 C100,300 250,500 450,380 S650,300 850,420"
        fill="none"
        stroke="url(#ribbon2)"
        strokeWidth="2.5"
        strokeLinecap="round"
        initial={{ pathLength: 0, opacity: 0 }}
        animate={{ pathLength: 1, opacity: 1 }}
        transition={{ duration: 3.5, delay: 1, ease: "easeOut" }}
      />
      <motion.path
        d="M400,-20 C350,150 500,200 450,350 S400,500 420,620"
        fill="none"
        stroke="url(#ribbon1)"
        strokeWidth="2"
        strokeLinecap="round"
        initial={{ pathLength: 0, opacity: 0 }}
        animate={{ pathLength: 1, opacity: 0.6 }}
        transition={{ duration: 4, delay: 1.5, ease: "easeOut" }}
      />
    </svg>
  );
}

/* ─── Rainbow arc ──────────────────────────────────────────────────────── */

function RainbowArc() {
  const colors = [
    "rgba(255, 99, 132, 0.12)",
    "rgba(255, 159, 64, 0.10)",
    "rgba(255, 205, 86, 0.10)",
    "rgba(75, 192, 192, 0.08)",
    "rgba(153, 102, 255, 0.08)",
  ];

  return (
    <motion.div
      className="absolute -bottom-32 -end-20 w-[500px] h-[500px] pointer-events-none"
      initial={{ opacity: 0, scale: 0.8 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 2, delay: 0.8, ease: "easeOut" }}
    >
      {colors.map((color, i) => (
        <div
          key={i}
          className="absolute rounded-full"
          style={{
            inset: `${i * 20}px`,
            border: `${3 - i * 0.4}px solid ${color}`,
          }}
        />
      ))}
    </motion.div>
  );
}

/* ─── Main pattern component ───────────────────────────────────────────── */

export function HeroAnimatedPattern() {
  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden>
      {/* Soft radial glows to add depth */}
      <div className="absolute top-[10%] start-[15%] w-[300px] h-[300px] rounded-full bg-primary-400/10 blur-[80px] hero-pattern-pulse" />
      <div className="absolute bottom-[15%] end-[10%] w-[250px] h-[250px] rounded-full bg-yellow-400/8 blur-[70px] hero-pattern-pulse" style={{ animationDelay: "3s" }} />
      <div className="absolute top-[50%] end-[40%] w-[200px] h-[200px] rounded-full bg-pink-300/8 blur-[60px] hero-pattern-pulse" style={{ animationDelay: "1.5s" }} />

      {/* Ribbon trails connecting elements */}
      <RibbonTrail />

      {/* Rainbow arc in the corner */}
      <RainbowArc />

      {/* All floating shapes */}
      {SHAPES.map((shape) => (
        <FloatingElement key={shape.id} shape={shape} />
      ))}

      {/* Subtle dotted "constellation" connecting hearts */}
      <svg className="absolute inset-0 w-full h-full pointer-events-none opacity-[0.06]" aria-hidden>
        <motion.circle cx="8%" cy="15%" r="2" fill="white"
          animate={{ opacity: [0.3, 1, 0.3] }}
          transition={{ duration: 3, repeat: Infinity }}
        />
        <motion.circle cx="45%" cy="70%" r="2" fill="white"
          animate={{ opacity: [0.3, 1, 0.3] }}
          transition={{ duration: 3, delay: 1, repeat: Infinity }}
        />
        <motion.circle cx="72%" cy="22%" r="2" fill="white"
          animate={{ opacity: [0.3, 1, 0.3] }}
          transition={{ duration: 3, delay: 2, repeat: Infinity }}
        />
        <motion.line x1="8%" y1="15%" x2="45%" y2="70%" stroke="white" strokeWidth="0.5" strokeDasharray="4 4"
          style={{ opacity: 0.5 }}
        />
        <motion.line x1="45%" y1="70%" x2="72%" y2="22%" stroke="white" strokeWidth="0.5" strokeDasharray="4 4"
          style={{ opacity: 0.5 }}
        />
      </svg>
    </div>
  );
}
