"use client";

import { useState, useEffect } from "react";
import { useTranslations, useLocale } from "next-intl";
import { Link } from "@/i18n/navigation";
import { useAppData } from "@/context/AppContext";
import { ThemeSwitcher } from "./ThemeSwitcher";
import { LanguageSelector } from "./LanguageSelector";
import { cn } from "@/lib/utils";
import { Menu, X, Heart } from "lucide-react";

interface NavLink {
  href: string;
  label: string;
}

function DesktopNavLink({
  link,
  isScrolled,
}: {
  link: NavLink;
  isScrolled: boolean;
}) {
  const cls = cn(
    "px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200",
    isScrolled
      ? "text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-primary-600 dark:hover:text-primary-400"
      : "text-white/90 hover:text-white hover:bg-white/10"
  );

  return (
    <Link href={link.href} className={cls}>
      {link.label}
    </Link>
  );
}

function MobileNavLink({
  link,
  onClose,
}: {
  link: NavLink;
  onClose: () => void;
}) {
  const cls = "flex items-center px-6 py-3 text-slate-700 dark:text-slate-300 hover:bg-primary-50 dark:hover:bg-slate-800 hover:text-primary-600 dark:hover:text-primary-400 transition-colors";

  return (
    <Link href={link.href} onClick={onClose} className={cls}>
      {link.label}
    </Link>
  );
}

export function Navigation() {
  const t = useTranslations();
  const locale = useLocale();
  const { settings, blogs, galleryCount } = useAppData();
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 20);
    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  useEffect(() => {
    if (isMobileMenuOpen) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "";
    }
    return () => {
      document.body.style.overflow = "";
    };
  }, [isMobileMenuOpen]);

  const navLinks: NavLink[] = [
    { href: "/", label: t("TOP_NAV_HOME_LABEL") },
    { href: "/about", label: t("TOP_NAV_ABOUT_LABEL") },
  ];

  if (blogs.length > 0) {
    navLinks.push({ href: "/blog", label: t("TOP_NAV_BLOG_LABEL") });
  }

  navLinks.push({ href: "/marital-therapy-clinic", label: t("DR_MAGDI_CLINIC_TOP_HEADER") });

  if (galleryCount > 0) {
    navLinks.push({ href: "/gallery", label: t("GALLERY_TOP_HEADER") });
  }

  navLinks.push({ href: "/contact", label: t("TOP_NAV_CONTACT_LABEL") });

  const donateUrl = settings.static_button_get_started_url || "/cause/help-us";
  const closeMobile = () => setIsMobileMenuOpen(false);

  return (
    <>
      <nav
        className={cn(
          "fixed top-0 start-0 end-0 z-50 transition-all duration-500",
          isScrolled
            ? "glass-strong py-2.5 shadow-lg shadow-black/10 dark:shadow-black/20 backdrop-blur-2xl"
            : "py-4 bg-gradient-to-b from-black/25 via-black/10 to-transparent dark:from-white/15 dark:via-white/5 dark:to-transparent"
        )}
      >
        <div className="container-custom">
          <div className="flex items-center justify-between">
            {/* Logo — client logo from React site */}
            <Link href="/" className="flex items-center gap-2 shrink-0">
              {isScrolled ? (
                <>
                  <img
                    src="/images/logo-dark.svg"
                    alt="WAN Aid"
                    width={120}
                    height={36}
                    className="h-9 w-auto dark:hidden"
                  />
                  <img
                    src="/images/logo-light.svg"
                    alt="WAN Aid"
                    width={120}
                    height={36}
                    className="h-9 w-auto hidden dark:block"
                  />
                </>
              ) : (
                <img
                  src="/images/logo-light.svg"
                  alt="WAN Aid"
                  width={120}
                  height={36}
                  className="h-9 w-auto"
                />
              )}
            </Link>

            {/* Desktop Navigation */}
            <div className="hidden lg:flex items-center gap-1">
              {navLinks.map((link) => (
                <DesktopNavLink
                  key={link.href}
                  link={link}
                  isScrolled={isScrolled}
                />
              ))}
            </div>

            {/* Desktop Actions */}
            <div className="hidden lg:flex items-center gap-3">
              <ThemeSwitcher />
              <LanguageSelector />
              <Link href={donateUrl} className="btn-accent text-sm h-10 px-5 py-2.5 rounded-xl font-display font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                <Heart className="w-4 h-4" />
                {t("TOP_NAV_STATIC_BUTTON_GET_STARTED_LABEL")}
              </Link>
            </div>

            {/* Mobile Menu Button */}
            <button
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className={cn(
                "lg:hidden p-2 rounded-xl transition-colors",
                isScrolled
                  ? "text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                  : "text-white hover:bg-white/10"
              )}
              aria-label="Toggle menu"
            >
              {isMobileMenuOpen ? (
                <X className="w-6 h-6" />
              ) : (
                <Menu className="w-6 h-6" />
              )}
            </button>
          </div>
        </div>
      </nav>

      {/* Mobile Menu Overlay */}
      <div
        className={cn(
          "fixed inset-0 z-40 lg:hidden transition-all duration-300",
          isMobileMenuOpen
            ? "opacity-100 pointer-events-auto"
            : "opacity-0 pointer-events-none"
        )}
      >
        {/* Backdrop — opaque so hero doesn’t show through */}
        <div
          className="absolute inset-0 bg-black/70 backdrop-blur-md"
          onClick={closeMobile}
        />

        {/* Slide-out panel */}
        <div
          className={cn(
            "absolute top-0 end-0 h-full w-[300px] max-w-[85vw]",
            "bg-white dark:bg-slate-900 shadow-2xl",
            "transition-transform duration-300 ease-out",
            "flex flex-col",
            isMobileMenuOpen
              ? "translate-x-0 rtl:-translate-x-0"
              : "translate-x-full rtl:-translate-x-full"
          )}
        >
          {/* Mobile Header */}
          <div className="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700">
            <Link href="/" onClick={closeMobile} className="flex items-center">
              <img
                src="/images/logo-dark.svg"
                alt="WAN Aid"
                width={100}
                height={32}
                className="h-8 w-auto dark:hidden"
              />
              <img
                src="/images/logo-light.svg"
                alt="WAN Aid"
                width={100}
                height={32}
                className="h-8 w-auto hidden dark:block"
              />
            </Link>
            <button
              onClick={closeMobile}
              className="p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
              aria-label="Close menu"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          {/* Mobile Nav Links */}
          <div className="flex-1 overflow-y-auto py-4">
            {navLinks.map((link) => (
              <MobileNavLink
                key={link.href}
                link={link}
                onClose={closeMobile}
              />
            ))}
          </div>

          {/* Mobile Actions */}
          <div className="p-4 space-y-3 border-t border-slate-200 dark:border-slate-700">
            <div className="flex items-center gap-2">
              <ThemeSwitcher />
              <LanguageSelector />
            </div>
            <Link
              href={donateUrl}
              onClick={closeMobile}
              className="btn-accent w-full text-sm"
            >
              <Heart className="w-4 h-4" />
              {t("TOP_NAV_STATIC_BUTTON_GET_STARTED_LABEL")}
            </Link>
          </div>
        </div>
      </div>
    </>
  );
}
