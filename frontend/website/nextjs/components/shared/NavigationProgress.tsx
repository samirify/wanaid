"use client";

import { usePathname } from "@/i18n/navigation";
import { useEffect, useRef, useState } from "react";

/**
 * Shows the same full-page preloader (area-loader) immediately when the user
 * clicks an internal link, so there is no delay before any loading UI appears.
 * Hides when the route has changed.
 */
export function NavigationProgress() {
  const pathname = usePathname();
  const [isNavigating, setIsNavigating] = useState(false);
  const prevPathnameRef = useRef(pathname);

  useEffect(() => {
    if (pathname !== prevPathnameRef.current) {
      prevPathnameRef.current = pathname;
      setIsNavigating(false);
    }
  }, [pathname]);

  useEffect(() => {
    function handleClick(e: MouseEvent) {
      const target = e.target as HTMLElement;
      const anchor = target.closest("a");
      if (!anchor || anchor.target === "_blank" || anchor.hasAttribute("download")) return;
      const href = anchor.getAttribute("href");
      if (!href || !href.startsWith("/") || href.startsWith("//")) return;
      // Same-origin internal navigation
      setIsNavigating(true);
    }

    document.documentElement.addEventListener("click", handleClick, true);
    return () => document.documentElement.removeEventListener("click", handleClick, true);
  }, []);

  if (!isNavigating) return null;

  return (
    <div className="area-loader-container" aria-hidden="true">
      <div className="area-loader" />
    </div>
  );
}
