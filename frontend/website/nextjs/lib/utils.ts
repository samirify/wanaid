import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

/**
 * Merge Tailwind CSS classes with clsx and tailwind-merge.
 */
export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs));
}

/**
 * Format a number as currency.
 */
export function formatCurrency(
  amount: number,
  currencyCode: string = "USD"
): string {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: currencyCode,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
}

/**
 * Format a date string into a readable format.
 */
export function formatDate(dateString: string, locale: string = "en"): string {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat(locale === "ar" ? "ar-EG" : "en-GB", {
    year: "numeric",
    month: "long",
    day: "numeric",
  }).format(date);
}

/**
 * Calculate donation progress percentage (capped at 100).
 */
export function calculateProgress(raised: number, target: number): number {
  if (target <= 0) return 0;
  return Math.min(Math.round((raised / target) * 100), 100);
}

/**
 * Truncate text to a maximum length with ellipsis.
 */
export function truncateText(text: string, maxLength: number = 150): string {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength).trimEnd() + "...";
}

/**
 * Strip HTML tags from a string.
 */
export function stripHtml(html: string): string {
  return html.replace(/<[^>]*>/g, "");
}

/**
 * Generate a media URL with optional width parameter.
 */
export function mediaUrl(url: string, width?: number): string {
  if (!url) return "";
  if (width) {
    return `${url}/${width}`;
  }
  return url;
}

/**
 * Check if a URL is internal (relative path).
 */
export function isInternalUrl(url: string): boolean {
  return !url.startsWith("http") && !url.startsWith("//");
}

/**
 * Smooth scroll to an element by ID.
 */
export function scrollToElement(elementId: string, offset: number = 80): void {
  const element = document.getElementById(elementId);
  if (element) {
    const top = element.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top, behavior: "smooth" });
  }
}
