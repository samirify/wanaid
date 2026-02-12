"use client";

import { useTranslations, useLocale } from "next-intl";
import { Link } from "@/i18n/navigation";
import { useAppData } from "@/context/AppContext";
import {
  Heart,
  MapPin,
  Phone,
  Mail,
  Facebook,
  Instagram,
  Youtube,
  Linkedin,
  Twitter,
  ArrowRight,
} from "lucide-react";

export function Footer() {
  const t = useTranslations();
  const locale = useLocale();
  const { settings } = useAppData();

  const socialLinks = [
    { url: settings.social_media_facebook, icon: Facebook, label: "Facebook" },
    {
      url: settings.social_media_instagram,
      icon: Instagram,
      label: "Instagram",
    },
    { url: settings.social_media_youtube, icon: Youtube, label: "YouTube" },
    { url: settings.social_media_linkedin, icon: Linkedin, label: "LinkedIn" },
    { url: settings.social_media_twitter, icon: Twitter, label: "Twitter" },
  ].filter((link) => link.url);

  // "Pages" column — matches React footer exactly
  const quickLinks = [
    { href: "/about", label: t("WEBSITE_FOOTER_PAGES_ABOUT_LABEL") },
    { href: "/blog", label: t("WEBSITE_FOOTER_PAGES_BLOG_LABEL") },
    { href: "/contact", label: t("WEBSITE_FOOTER_PAGES_CONTACT_LABEL") },
  ];

  // "Useful Links" column — matches React footer exactly
  const usefulLinks = [
    { href: "/open-causes", label: t("WEBSITE_FOOTER_USEFUL_LINKS_OPEN_CAUSES_LABEL") },
  ];

  const legalLinks = [
    { href: "/privacy-policy", label: t("WEBSITE_PRIVACY_POLICY_LABEL") },
    { href: "/terms-of-use", label: t("WEBSITE_TERMS_OF_USE_LABEL") },
    { href: "/disclaimer", label: t("WEBSITE_DISCLAIMER_LABEL") },
  ];

  const tagline = t("LANDING_MAIN_HEADER_BOTTOM");

  const copyright = t("WEBSITE_FOOTER_COPYRIGHT_MESSAGE").replace(
    "[YEAR]",
    new Date().getFullYear().toString()
  );

  return (
    <footer className="relative bg-slate-900 text-slate-300">
      {/* Decorative top border */}
      <div className="h-1 bg-gradient-to-r from-primary-500 via-accent-500 to-primary-500" />

      {/* Wave separator */}
      <div className="relative -mt-1 overflow-hidden">
        <svg
          className="w-full h-12 text-slate-900"
          viewBox="0 0 1440 48"
          fill="none"
          preserveAspectRatio="none"
        >
          <path
            d="M0 48h1440V0C1200 32 960 48 720 48S240 32 0 0v48z"
            fill="currentColor"
          />
        </svg>
      </div>

      <div className="container-custom py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
          {/* Brand Column */}
          <div className="lg:col-span-1">
            <Link href="/" className="flex items-center gap-2 mb-4">
              <div className="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center">
                <Heart className="w-5 h-5" />
              </div>
            </Link>
            {tagline && (
              <p className="text-slate-400 text-sm leading-relaxed mb-6">
                {tagline}
              </p>
            )}
            {socialLinks.length > 0 && (
              <div className="flex items-center gap-3">
                {socialLinks.map((social) => (
                  <a
                    key={social.label}
                    href={social.url!}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="w-10 h-10 rounded-xl bg-slate-800 hover:bg-primary-600 text-slate-400 hover:text-white flex items-center justify-center transition-all duration-300"
                    aria-label={social.label}
                  >
                    <social.icon className="w-4 h-4" />
                  </a>
                ))}
              </div>
            )}
          </div>

          {/* Pages — same as React footer */}
          <div>
            <h3 className="text-white font-semibold text-lg mb-6">
              {t("WEBSITE_FOOTER_PAGES_LABEL")}
            </h3>
            <ul className="space-y-3">
              {quickLinks.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-slate-400 hover:text-primary-400 transition-colors duration-200 text-sm flex items-center gap-2 group"
                  >
                    <ArrowRight className="w-3 h-3 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all rtl:rotate-180" />
                    <span>{link.label}</span>
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Useful Links — same as React footer */}
          <div>
            <h3 className="text-white font-semibold text-lg mb-6">
              {t("WEBSITE_FOOTER_USEFUL_LINKS_LABEL")}
            </h3>
            <ul className="space-y-3">
              {usefulLinks.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-slate-400 hover:text-primary-400 transition-colors duration-200 text-sm flex items-center gap-2 group"
                  >
                    <ArrowRight className="w-3 h-3 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all rtl:rotate-180" />
                    <span>{link.label}</span>
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact Info */}
          <div>
            <h3 className="text-white font-semibold text-lg mb-6">
              {t("WEBSITE_FOOTER_FOLLOW_US_ON_LABEL")}
            </h3>
            <ul className="space-y-4">
              {settings.main_contact_address && (
                <li className="flex items-start gap-3 text-sm">
                  <MapPin className="w-4 h-4 text-primary-400 mt-0.5 shrink-0" />
                  <span className="text-slate-400">
                    {settings.main_contact_address}
                  </span>
                </li>
              )}
              {settings.main_contact_phone_number && (
                <li className="flex items-center gap-3 text-sm">
                  <Phone className="w-4 h-4 text-primary-400 shrink-0" />
                  <a
                    href={`tel:${settings.main_contact_phone_number}`}
                    className="text-slate-400 hover:text-primary-400 transition-colors"
                    dir="ltr"
                  >
                    {settings.main_contact_phone_number}
                  </a>
                </li>
              )}
              {settings.main_contact_email && (
                <li className="flex items-center gap-3 text-sm">
                  <Mail className="w-4 h-4 text-primary-400 shrink-0" />
                  <a
                    href={`mailto:${settings.main_contact_email}`}
                    className="text-slate-400 hover:text-primary-400 transition-colors"
                  >
                    {settings.main_contact_email}
                  </a>
                </li>
              )}
            </ul>
          </div>
        </div>
      </div>

      {/* Bottom Bar — copyright + legal links (same as React footer) */}
      <div className="border-t border-slate-800">
        <div className="container-custom py-6">
          <div className="flex flex-col items-center gap-4">
            <p className="text-slate-500 text-sm">
              &copy; {new Date().getFullYear()} {copyright}
            </p>
            <ul className="flex items-center gap-4">
              {legalLinks.map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-slate-500 hover:text-primary-400 transition-colors text-xs"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </footer>
  );
}
