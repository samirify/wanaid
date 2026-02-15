import { NextIntlClientProvider } from "next-intl";
import { getMessages } from "next-intl/server";
import { ThemeProvider } from "next-themes";
import { notFound } from "next/navigation";
import { Inter, Sora, Noto_Kufi_Arabic } from "next/font/google";
import { routing } from "@/i18n/routing";
import { AppProvider } from "@/context/AppContext";
import { Navigation } from "@/components/layout/Navigation";
import { Footer } from "@/components/layout/Footer";
import { CookieConsentBanner } from "@/components/shared/CookieConsentBanner";
import { ScrollToTop } from "@/components/shared/ScrollToTop";
// import { NotificationDemo } from "@/components/shared/NotificationDemo";
import { cookies, headers } from "next/headers";
import { getDirection } from "@/lib/utils";
import { api } from "@/lib/api";

const inter = Inter({
  subsets: ["latin"],
  variable: "--font-sans",
  display: "swap",
});

const sora = Sora({
  subsets: ["latin"],
  variable: "--font-display",
  weight: ["400", "500", "600", "700", "800"],
  display: "swap",
});

const notoKufiArabic = Noto_Kufi_Arabic({
  subsets: ["arabic"],
  variable: "--font-arabic",
  weight: ["300", "400", "500", "600", "700"],
  display: "swap",
});

interface LocaleLayoutProps {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}

export default async function LocaleLayout({
  children,
  params,
}: LocaleLayoutProps) {
  const { locale } = await params;

  if (!routing.locales.includes(locale as "en" | "ar")) {
    notFound();
  }

  const messages = await getMessages();

  let direction: "ltr" | "rtl" = "ltr";
  let initialData: Awaited<ReturnType<typeof api.initialize>> | null = null;
  const cookieStore = await cookies();
  const headerList = await headers();
  const cookieHeader = cookieStore.toString();
  const referer =
    headerList.get("referer") ??
    headerList.get("referrer") ??
    process.env.NEXT_PUBLIC_SITE_URL ??
    (process.env.VERCEL_URL ? `https://${process.env.VERCEL_URL}` : undefined);
  try {
    initialData = await api.initialize(locale, { cookie: cookieHeader, referer: referer ?? undefined });
    direction = getDirection(locale, initialData.languages);
  } catch {
    direction = getDirection(locale, null);
  }

  return (
    <html
      lang={locale}
      dir={direction}
      suppressHydrationWarning
      className={`${inter.variable} ${sora.variable} ${notoKufiArabic.variable}`}
    >
      <body className="min-h-screen flex flex-col font-sans">
        <ThemeProvider
          attribute="class"
          defaultTheme="light"
          enableSystem
          disableTransitionOnChange={false}
        >
          <NextIntlClientProvider messages={messages}>
            <AppProvider initialData={initialData}>
              <Navigation />
              <main className="flex-1">{children}</main>
              <Footer />
              <ScrollToTop />
              <CookieConsentBanner />
              {/* <NotificationDemo /> */}
            </AppProvider>
          </NextIntlClientProvider>
        </ThemeProvider>
      </body>
    </html>
  );
}
