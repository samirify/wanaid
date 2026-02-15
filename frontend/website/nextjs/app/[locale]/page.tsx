"use client";

import { Hero } from "@/components/landing/Hero";
import { OpenCauses } from "@/components/landing/OpenCauses";
import { BlogSection } from "@/components/landing/BlogSection";
import { PageSections } from "@/components/landing/PageSections";
import { PageHead } from "@/components/shared/PageHead";
import { SectionSeparator } from "@/components/shared/SectionSeparator";
import { useAppData } from "@/context/AppContext";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";

export default function LandingPage() {
  const { pageContents, isLoading, error, refetch } = useAppData();

  if (isLoading) return <Loader fullPage />;

  if (error) {
    return <ErrorDisplay variant="page" onRetry={refetch} showHomeButton />;
  }

  const pillars = pageContents?.LANDING?.PILLARS || [];

  return (
    <>
      <PageHead />
      <Hero />
      {pillars.length > 0 && <PageSections pillars={pillars} />}
      <SectionSeparator />
      <OpenCauses />
      <SectionSeparator />
      <BlogSection />
    </>
  );
}
