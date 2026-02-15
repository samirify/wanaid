"use client";

import { Heart } from "lucide-react";

/**
 * Full-width fancy separator between page sections. Fills the screen width,
 * stuck to the bottom section. Double line with gradient fade and central heart.
 */
export function SectionSeparator() {
  return (
    <div
      className="w-screen relative left-1/2 -translate-x-1/2 flex-shrink-0 h-2 flex flex-col justify-center overflow-visible"
      aria-hidden
    >
      {/* Double line: full-width gradient bars — no extra height below */}
      <div className="w-full relative h-0.5">
        <div
          className="absolute inset-0 w-full h-full"
          style={{
            background:
              "linear-gradient(to right, transparent 0%, rgb(199 28 105 / 0.2) 12%, rgb(199 28 105 / 0.6) 25%, rgb(199 28 105) 50%, rgb(199 28 105 / 0.6) 75%, rgb(199 28 105 / 0.2) 88%, transparent 100%)",
          }}
        />
      </div>
      <div
        className="w-full h-px mt-0.5"
        style={{
          background:
            "linear-gradient(to right, transparent 0%, rgb(199 28 105 / 0.15) 15%, rgb(199 28 105 / 0.5) 30%, rgb(199 28 105 / 0.8) 50%, rgb(199 28 105 / 0.5) 70%, rgb(199 28 105 / 0.15) 85%, transparent 100%)",
        }}
      />
      {/* Central ornament: heart — no ring so it doesn’t show a white arc below */}
      <span
        className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-primary-500 dark:text-primary-400 pointer-events-none flex items-center justify-center"
        aria-hidden
      >
        <Heart className="w-3 h-3 fill-current" />
      </span>
    </div>
  );
}
