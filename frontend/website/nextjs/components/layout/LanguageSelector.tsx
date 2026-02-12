"use client";

import { useLocale } from "next-intl";
import { useRouter, usePathname } from "@/i18n/navigation";
import { routing } from "@/i18n/routing";
import { Globe } from "lucide-react";
import { cn } from "@/lib/utils";

const languageLabels: Record<string, string> = {
  en: "EN",
  ar: "عربي",
};

export function LanguageSelector() {
  const locale = useLocale();
  const router = useRouter();
  const pathname = usePathname();

  const switchLocale = (newLocale: string) => {
    router.replace(pathname, { locale: newLocale });
  };

  return (
    <div className="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-xl p-1 h-10">
      <Globe className="w-4 h-4 text-slate-500 dark:text-slate-400 ms-1.5" />
      {routing.locales.map((loc) => (
        <button
          key={loc}
          onClick={() => switchLocale(loc)}
          className={cn(
            "px-2.5 py-1 rounded-lg text-xs font-semibold transition-all duration-200",
            locale === loc
              ? "bg-white dark:bg-slate-700 text-primary-600 dark:text-primary-400 shadow-sm"
              : "text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300"
          )}
          aria-label={`Switch to ${loc}`}
        >
          {languageLabels[loc] || loc.toUpperCase()}
        </button>
      ))}
    </div>
  );
}
