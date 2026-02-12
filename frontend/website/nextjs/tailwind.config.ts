import type { Config } from "tailwindcss";

const config: Config = {
  darkMode: "class",
  content: [
    "./app/**/*.{ts,tsx}",
    "./components/**/*.{ts,tsx}",
    "./context/**/*.{ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: "#fdf2f7",
          100: "#fce7f1",
          200: "#fad0e4",
          300: "#f7a8cd",
          400: "#e63e85",
          500: "#c71c69",
          600: "#b31960",
          700: "#9e1554",
          800: "#8e1249",
          900: "#77133f",
          950: "#480521",
          DEFAULT: "#c71c69",
        },
        accent: {
          50: "#e8e8e8",
          100: "#d4d4d4",
          200: "#a3a3a3",
          300: "#737373",
          400: "#2c2c2c",
          500: "#222222",
          600: "#1e1e1e",
          700: "#1a1a1a",
          800: "#151515",
          900: "#111111",
          950: "#0a0a0a",
          DEFAULT: "#000000",
        },
      },
      fontFamily: {
        sans: ["var(--font-sans)", "system-ui", "sans-serif"],
        arabic: ["var(--font-arabic)", "system-ui", "sans-serif"],
      },
      animation: {
        "fade-in": "fadeIn 0.6s ease-out",
        "fade-in-up": "fadeInUp 0.6s ease-out",
        "fade-in-down": "fadeInDown 0.6s ease-out",
        "slide-in-left": "slideInLeft 0.6s ease-out",
        "slide-in-right": "slideInRight 0.6s ease-out",
        "scale-in": "scaleIn 0.4s ease-out",
        float: "float 6s ease-in-out infinite",
        "pulse-glow": "pulseGlow 2s ease-in-out infinite",
        shimmer: "shimmer 2s linear infinite",
      },
      keyframes: {
        fadeIn: {
          "0%": { opacity: "0" },
          "100%": { opacity: "1" },
        },
        fadeInUp: {
          "0%": { opacity: "0", transform: "translateY(30px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        fadeInDown: {
          "0%": { opacity: "0", transform: "translateY(-30px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        slideInLeft: {
          "0%": { opacity: "0", transform: "translateX(-30px)" },
          "100%": { opacity: "1", transform: "translateX(0)" },
        },
        slideInRight: {
          "0%": { opacity: "0", transform: "translateX(30px)" },
          "100%": { opacity: "1", transform: "translateX(0)" },
        },
        scaleIn: {
          "0%": { opacity: "0", transform: "scale(0.9)" },
          "100%": { opacity: "1", transform: "scale(1)" },
        },
        float: {
          "0%, 100%": { transform: "translateY(0px)" },
          "50%": { transform: "translateY(-20px)" },
        },
        pulseGlow: {
          "0%, 100%": { boxShadow: "0 0 20px color-mix(in srgb, var(--color-primary) 30%, transparent)" },
          "50%": { boxShadow: "0 0 40px color-mix(in srgb, var(--color-primary) 60%, transparent)" },
        },
        shimmer: {
          "0%": { backgroundPosition: "-200% 0" },
          "100%": { backgroundPosition: "200% 0" },
        },
      },
      backgroundImage: {
        "gradient-radial": "radial-gradient(var(--tw-gradient-stops))",
        "hero-pattern":
          "linear-gradient(135deg, color-mix(in srgb, var(--color-primary) 95%, transparent) 0%, color-mix(in srgb, var(--color-primary) 90%, black) 40%, color-mix(in srgb, var(--color-primary) 60%, black) 100%)",
        "hero-pattern-dark":
          "linear-gradient(135deg, color-mix(in srgb, var(--color-primary) 50%, black) 0%, rgba(15, 23, 42, 0.95) 50%, color-mix(in srgb, var(--color-primary) 30%, black) 100%)",
      },
    },
  },
  plugins: [require("@tailwindcss/typography")],
};

export default config;
