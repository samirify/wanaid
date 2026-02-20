"use client";

import { useEffect, useRef, useState, useCallback, Suspense } from "react";
import { usePathname, useSearchParams } from "next/navigation";

const MIN_DISPLAY_MS = 300;

/** Dispatched by LanguageSelector (and any programmatic nav) so the loader shows */
export const NAVIGATION_START_EVENT = "navigationstart";

function RouteChangeLoader() {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [active, setActive] = useState(false);
  const showTimeRef = useRef(0);
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const prevPathRef = useRef(pathname);
  const prevSearchRef = useRef(searchParams?.toString() ?? "");

  const clearTimers = useCallback(() => {
    if (timerRef.current) {
      clearTimeout(timerRef.current);
      timerRef.current = null;
    }
  }, []);

  useEffect(() => {
    const currentSearch = searchParams?.toString() ?? "";
    if (
      pathname !== prevPathRef.current ||
      currentSearch !== prevSearchRef.current
    ) {
      prevPathRef.current = pathname;
      prevSearchRef.current = currentSearch;

      // Scroll to top on navigation so the new page is shown from the top
      window.scrollTo(0, 0);

      if (active) {
        const elapsed = Date.now() - showTimeRef.current;
        const delay = Math.max(0, MIN_DISPLAY_MS - elapsed);
        clearTimers();
        timerRef.current = setTimeout(() => setActive(false), delay);
      }
    }
    return clearTimers;
  }, [pathname, searchParams, active, clearTimers]);

  useEffect(() => {
    const handleClick = (e: MouseEvent) => {
      if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey)
        return;

      const anchor = (e.target as HTMLElement).closest("a");
      if (
        !anchor ||
        !anchor.href ||
        anchor.target === "_blank" ||
        anchor.hasAttribute("download")
      )
        return;

      try {
        const url = new URL(anchor.href, window.location.origin);
        if (url.origin !== window.location.origin) return;
        if (
          url.pathname === window.location.pathname &&
          url.search === window.location.search
        )
          return;

        clearTimers();
        showTimeRef.current = Date.now();
        setActive(true);
      } catch {
        // ignore malformed URLs
      }
    };

    const handleNavigationStart = () => {
      clearTimers();
      showTimeRef.current = Date.now();
      setActive(true);
    };

    document.addEventListener("click", handleClick, { capture: true });
    document.addEventListener(NAVIGATION_START_EVENT, handleNavigationStart);
    return () => {
      document.removeEventListener("click", handleClick, { capture: true });
      document.removeEventListener(NAVIGATION_START_EVENT, handleNavigationStart);
    };
  }, [clearTimers]);

  return (
    <div
      className={`area-loader-container area-loader-nav${active ? " area-loader-nav-active" : ""}`}
      aria-hidden="true"
    >
      <div className="area-loader" />
    </div>
  );
}

export function NavigationProgress() {
  return (
    <Suspense fallback={null}>
      <RouteChangeLoader />
    </Suspense>
  );
}
