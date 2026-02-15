"use client";

import { useState, useEffect } from "react";
import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import { api } from "@/lib/api";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { Shield } from "lucide-react";

export default function PrivacyPolicyPage() {
  const t = useTranslations();
  const rawT = useRawTranslation();
  const locale = useLocale();
  const [content, setContent] = useState<string>("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchDocument() {
      try {
        setLoading(true);
        const data = await api.getDocument("document_privacy_policy");
        const doc = data.document;
        if (doc?.body) {
          setContent(doc.body);
        } else if (doc?.value) {
          setContent(rawT(doc.value) || "");
        } else {
          setContent("");
        }
      } catch {
        setError("Failed to load document.");
      } finally {
        setLoading(false);
      }
    }
    fetchDocument();
  }, [locale]);

  return (
    <>
      <div className="page-hero">
        <div className="page-hero-content">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="flex items-center justify-center gap-3 mb-4"
          >
            <Shield className="w-8 h-8 text-primary-300" />
          </motion.div>
          <motion.h1
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="text-4xl sm:text-5xl font-bold"
          >
            {t("WEBSITE_PRIVACY_POLICY_LABEL")}
          </motion.h1>
        </div>
      </div>

      <section className="py-24">
        <div className="container-custom max-w-4xl">
          {loading ? (
            <Loader />
          ) : error ? (
            <ErrorDisplay variant="inline" message={error} />
          ) : (
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="prose prose-lg dark:prose-invert max-w-none"
              dangerouslySetInnerHTML={{ __html: content }}
            />
          )}
        </div>
      </section>
    </>
  );
}
