"use client";

import { cn } from "@/lib/utils";

interface LoaderProps {
  fullPage?: boolean;
  className?: string;
  size?: "sm" | "md" | "lg";
}

export function Loader({
  fullPage = false,
  className,
  size = "md",
}: LoaderProps) {
  const sizeClasses = {
    sm: "w-6 h-6",
    md: "w-10 h-10",
    lg: "w-16 h-16",
  };

  const spinner = (
    <div className={cn("relative", sizeClasses[size], className)}>
      <div className="absolute inset-0 rounded-full border-2 border-slate-200 dark:border-slate-700" />
      <div className="absolute inset-0 rounded-full border-2 border-transparent border-t-primary-600 animate-spin" />
    </div>
  );

  if (fullPage) {
    return (
      <div className="area-loader-container" aria-hidden="true">
        <div className="area-loader" />
      </div>
    );
  }

  return (
    <div className="flex items-center justify-center py-12">{spinner}</div>
  );
}

/* ────── Skeleton helpers ────── */

export function CardSkeleton() {
  return (
    <div className="card overflow-hidden">
      <div className="skeleton h-48 w-full" />
      <div className="p-5 space-y-3">
        <div className="skeleton h-4 w-3/4" />
        <div className="skeleton h-3 w-full" />
        <div className="skeleton h-3 w-5/6" />
      </div>
    </div>
  );
}

export function HeroSkeleton() {
  return (
    <div className="min-h-screen bg-slate-100 dark:bg-slate-800 animate-pulse flex items-center justify-center">
      <div className="text-center space-y-4">
        <div className="skeleton h-6 w-48 mx-auto" />
        <div className="skeleton h-12 w-96 mx-auto" />
        <div className="skeleton h-4 w-72 mx-auto" />
      </div>
    </div>
  );
}
