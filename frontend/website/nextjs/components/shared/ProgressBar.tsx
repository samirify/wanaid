"use client";

import { useEffect, useState } from "react";
import { useScrollAnimation } from "@/hooks/useScrollAnimation";
import { cn } from "@/lib/utils";

interface ProgressBarProps {
  progress: number;
  className?: string;
  showLabel?: boolean;
  size?: "sm" | "md" | "lg";
  animated?: boolean;
  color?: "primary" | "accent";
}

export function ProgressBar({
  progress,
  className,
  showLabel = true,
  size = "md",
  animated = true,
  color = "primary",
}: ProgressBarProps) {
  const [ref, isInView] = useScrollAnimation<HTMLDivElement>();
  const [displayProgress, setDisplayProgress] = useState(0);

  useEffect(() => {
    if (isInView && animated) {
      const timer = setTimeout(() => {
        setDisplayProgress(progress);
      }, 200);
      return () => clearTimeout(timer);
    } else if (!animated) {
      setDisplayProgress(progress);
    }
  }, [isInView, progress, animated]);

  const sizeClasses = {
    sm: "h-1.5",
    md: "h-2.5",
    lg: "h-4",
  };

  const colorClasses = {
    primary:
      "bg-gradient-to-r from-primary-500 to-primary-400",
    accent:
      "bg-gradient-to-r from-accent-500 to-accent-600",
  };

  return (
    <div ref={ref} className={cn("w-full", className)}>
      {showLabel && (
        <div className="flex items-center justify-between mb-2">
          <span className="text-sm font-semibold text-slate-700 dark:text-slate-300">
            {Math.round(displayProgress)}%
          </span>
        </div>
      )}
      <div
        className={cn(
          "w-full rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden",
          sizeClasses[size]
        )}
      >
        <div
          className={cn(
            "h-full rounded-full transition-all duration-1000 ease-out",
            colorClasses[color]
          )}
          style={{ width: `${displayProgress}%` }}
        >
          {size === "lg" && (
            <div className="w-full h-full shimmer rounded-full" />
          )}
        </div>
      </div>
    </div>
  );
}
