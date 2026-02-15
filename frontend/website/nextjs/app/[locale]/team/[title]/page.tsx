"use client";

import { useState, useEffect, use } from "react";
import { useLocale } from "next-intl";
import { api } from "@/lib/api";
import { Loader } from "@/components/shared/Loader";
import TeamMemberContent from "./TeamMemberContent";
import type { TeamMember } from "@/lib/types";

interface PageProps {
  params: Promise<{ title: string; locale: string }>;
}

export default function TeamMemberPage({ params }: PageProps) {
  const { title } = use(params);
  const locale = useLocale();

  const [memberData, setMemberData] = useState<TeamMember | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchData() {
      try {
        setLoading(true);
        const result = await api.getTeamMemberDetails(title);
        setMemberData(result.teamMember);
      } catch {
        setMemberData(null);
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, [title]);

  if (loading) return <Loader fullPage />;

  return (
    <TeamMemberContent
      pageData={null}
      memberData={memberData}
      locale={locale}
    />
  );
}
