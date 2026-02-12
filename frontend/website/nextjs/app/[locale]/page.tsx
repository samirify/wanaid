"use client";

import { useEffect } from "react";
import { Hero } from "@/components/landing/Hero";
import { OpenCauses } from "@/components/landing/OpenCauses";
import { BlogSection } from "@/components/landing/BlogSection";
import { PageSections } from "@/components/landing/PageSections";
import { useAppData } from "@/context/AppContext";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";

export default function LandingPage() {
  const { pageContents, isLoading, error, refetch } = useAppData();

  // Scroll to hash section after page loads (e.g. /#open-causes, /#blog)
  useEffect(() => {
    if (!isLoading && typeof window !== "undefined" && window.location.hash) {
      const id = window.location.hash.replace("#", "");
      // Small delay to let sections render
      setTimeout(() => {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: "smooth" });
      }, 300);
    }
  }, [isLoading]);

  if (isLoading) return <Loader fullPage />;

  if (error) {
    return <ErrorDisplay variant="page" onRetry={refetch} showHomeButton />;
  }

  const pillars = pageContents?.LANDING?.PILLARS || [];

  return (
    <>
      <Hero />
      {pillars.length > 0 && <PageSections pillars={pillars} />}
      <OpenCauses />
      <BlogSection />
    </>
  );
}
