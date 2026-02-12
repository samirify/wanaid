"use client";

import { useState, useEffect, use } from "react";
import { useLocale } from "next-intl";
import { api } from "@/lib/api";
import { Loader } from "@/components/shared/Loader";
import TeamMemberContent from "./TeamMemberContent";
import type { TeamMember, PageHeaders, Pillar } from "@/lib/types";

interface PageProps {
  params: Promise<{ title: string; locale: string }>;
}

interface FullPageData {
  main_header_img?: string;
  meta: { title?: string; description?: string; keywords?: string };
  headers: PageHeaders;
  pillars: Pillar[];
}

export default function TeamMemberPage({ params }: PageProps) {
  const { title } = use(params);
  const locale = useLocale();

  const [pageData, setPageData] = useState<FullPageData | null>(null);
  const [memberData, setMemberData] = useState<TeamMember | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchData() {
      try {
        setLoading(true);

        // Try /pages/team/{slug} first (full HTML like the React reference app)
        try {
          const result = await api.getPageData(`team/${title}`, locale);
          if (result?.pillars?.length > 0) {
            setPageData(result);
            return;
          }
        } catch {
          // Fall through to team-member endpoint
        }

        // Fallback: /team-member/{slug}
        const result = await api.getTeamMemberDetails(title);
        setMemberData(result.teamMember);
      } catch {
        // Both failed — memberData stays null, error shown
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, [title, locale]);

  if (loading) return <Loader fullPage />;

  return (
    <TeamMemberContent
      pageData={pageData}
      memberData={memberData}
      locale={locale}
    />
  );
}
