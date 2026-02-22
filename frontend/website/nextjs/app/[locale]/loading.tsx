"use client";

import { usePathname } from "next/navigation";

const SKIP_KEY = "skipLocaleLoading";

function getAndClearSkipFlag(): boolean {
  if (typeof window === "undefined") return false;
  try {
    if (sessionStorage.getItem(SKIP_KEY) === "1") {
      sessionStorage.removeItem(SKIP_KEY);
      return true;
    }
  } catch {
    /* ignore */
  }
  return false;
}

export default function Loading() {
  const pathname = usePathname() ?? "";
  const skipSpinner = getAndClearSkipFlag();
  const isOpenCausesOrBlog =
    pathname.endsWith("/open-causes") || pathname.endsWith("/blog");

  if (skipSpinner || isOpenCausesOrBlog) {
    return null;
  }

  return (
    <div className="area-loader-container">
      <div className="area-loader" />
    </div>
  );
}
