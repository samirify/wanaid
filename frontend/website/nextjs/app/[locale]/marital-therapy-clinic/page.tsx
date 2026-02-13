"use client";

import { useState, useEffect, useRef } from "react";
import { useLocale } from "next-intl";
import { motion } from "framer-motion";
import ReCAPTCHA from "react-google-recaptcha";
import { api } from "@/lib/api";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { PageHead } from "@/components/shared/PageHead";
import { PageHero } from "@/components/shared/PageHero";
import { AlertCircle, CheckCircle, Loader2, Send } from "lucide-react";

export default function ClinicPage() {
  const rawT = useRawTranslation();
  const locale = useLocale();

  const [showForm, setShowForm] = useState(false);
  const [countries, setCountries] = useState<
    { id: string | number; name: string }[]
  >([]);
  const [formData, setFormData] = useState<Record<string, string>>({});
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [statusMessages, setStatusMessages] = useState<string[]>([]);
  const [statusSuccess, setStatusSuccess] = useState(false);
  const [captchaToken, setCaptchaToken] = useState<string | null>(null);
  const recaptchaRef = useRef<ReCAPTCHA>(null);

  const recaptchaSiteKey = process.env.NEXT_PUBLIC_GOOGLE_RECAPTCHA_SITE_KEY || "";

  // Fetch countries on mount (same as old React)
  useEffect(() => {
    const apiUrl =
      process.env.NEXT_PUBLIC_API_URL || "https://api.wanaid.org/api";
    fetch(`${apiUrl}/marital-therapy-clinic/countries/${locale}`, {
      cache: "no-cache",
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.success) {
          setCountries(res.countries || []);
        }
      })
      .catch(() => {});
  }, [locale]);

  const handleChange = (
    e: React.ChangeEvent<
      HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
    >
  ) => {
    setFormData((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (recaptchaSiteKey && !captchaToken) {
      setError(rawT("WEBSITE_CONTACT_WRITE_TO_FORM_CAPTCHA_REQUIRED") || "Please complete the CAPTCHA");
      return;
    }

    setSubmitting(true);
    setError(null);
    setStatusMessages([]);

    try {
      await api.submitClinicForm({
        ...formData,
        lang: locale,
        "g-recaptcha-response": captchaToken || undefined,
      } as any);
      setSuccess(true);
      setStatusSuccess(true);
      setCaptchaToken(null);
      recaptchaRef.current?.reset();
    } catch {
      setError(rawT("WEBSITE_ERRORS_SERVER_ERROR_MESSAGE"));
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <>
      <PageHead />

      <PageHero
        title={rawT("DR_MAGDI_CLINIC_TOP_HEADER")}
        variant="auto"
        align="center"
        showCurve
        asHeader
      />

      {/* Content section */}
      <section className="py-16">
        <div className="container-custom max-w-4xl">
          {/* Title */}
          <h2 className="text-3xl font-bold text-center text-slate-900 dark:text-white mb-2">
            {rawT("DR_MAGDI_CLINIC_FORM_TITLE_TXT")}
          </h2>
          <div className="h-1 w-16 bg-primary-500 mx-auto mb-8" />

          {/* Description */}
          <p className="text-slate-700 dark:text-slate-300 text-justify mb-8 leading-relaxed">
            {rawT("DR_MAGDI_CLINIC_FORM_SUB_TITLE_TXT")}
          </p>

          {/* Disclaimer box with "I agree" button */}
          <div className="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-6 mb-10">
            <div
              className="text-slate-800 dark:text-slate-200 mb-4"
              dangerouslySetInnerHTML={{
                __html: rawT("DR_MAGDI_CLINIC_FORM_WAN_DISCLAIMER_TXT"),
              }}
            />
            <hr className="border-amber-200 dark:border-amber-600 mb-4" />
            <div className="flex justify-center">
              <button
                onClick={() => setShowForm(true)}
                className={`inline-flex items-center gap-2 px-8 py-3 rounded-lg text-white font-semibold transition-all ${
                  showForm
                    ? "bg-green-600 hover:bg-green-700"
                    : "bg-slate-700 hover:bg-slate-800"
                }`}
              >
                {showForm ? (
                  <CheckCircle className="w-5 h-5" />
                ) : (
                  <AlertCircle className="w-5 h-5" />
                )}
                {rawT("WEBSITE_ALERT_MESSAGE_BTN_LABEL")}
              </button>
            </div>
          </div>

          {/* Form — only shown after clicking "I agree" */}
          {showForm && (
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="card p-8"
            >
              {success ? (
                <div className="text-center py-12">
                  <div className="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                    <CheckCircle className="w-8 h-8 text-green-500" />
                  </div>
                  <h3 className="text-xl font-bold text-slate-900 dark:text-white mb-2">
                    {rawT("DR_MAGDI_CLINIC_FORM_THANK_YOU_LABEL")}
                  </h3>
                </div>
              ) : (
                <form onSubmit={handleSubmit} className="space-y-5">
                  {error && <ErrorDisplay variant="inline" message={error} />}

                  {statusMessages.length > 0 && (
                    <div
                      className={`p-4 rounded-lg ${
                        statusSuccess
                          ? "bg-green-50 text-green-800 dark:bg-green-900/20 dark:text-green-300"
                          : "bg-red-50 text-red-800 dark:bg-red-900/20 dark:text-red-300"
                      }`}
                    >
                      <ul className="list-none space-y-1">
                        {statusMessages.map((msg, i) => (
                          <li key={i}>{rawT(msg) || msg}</li>
                        ))}
                      </ul>
                    </div>
                  )}

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                      <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        {rawT("DR_MAGDI_CLINIC_FORM_Q1_LABEL")}
                      </label>
                      <input
                        type="text"
                        name="nickname"
                        required
                        placeholder={rawT("DR_MAGDI_CLINIC_FORM_Q1_PLACEHOLDER")}
                        onChange={handleChange}
                        className="input"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        {rawT("DR_MAGDI_CLINIC_FORM_Q2_LABEL")}
                      </label>
                      <input
                        type="text"
                        name="full_name"
                        placeholder={rawT("DR_MAGDI_CLINIC_FORM_Q2_PLACEHOLDER")}
                        onChange={handleChange}
                        className="input"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                      <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        {rawT("DR_MAGDI_CLINIC_FORM_Q3_LABEL")}
                      </label>
                      <input
                        type="text"
                        name="tel_mobile"
                        required
                        placeholder={rawT("DR_MAGDI_CLINIC_FORM_Q3_PLACEHOLDER")}
                        onChange={handleChange}
                        className="input"
                        dir="ltr"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        {rawT("DR_MAGDI_CLINIC_FORM_Q4_LABEL")}
                      </label>
                      <select
                        name="country_id"
                        required
                        onChange={handleChange}
                        className="input"
                      >
                        {countries.map((country) => (
                          <option key={country.id} value={country.id}>
                            {rawT(country.name) || country.name}
                          </option>
                        ))}
                      </select>
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                      {rawT("DR_MAGDI_CLINIC_FORM_Q5_LABEL")}
                    </label>
                    <input
                      type="email"
                      name="email"
                      required
                      placeholder={rawT("DR_MAGDI_CLINIC_FORM_Q5_PLACEHOLDER")}
                      onChange={handleChange}
                      className="input"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                      {rawT("DR_MAGDI_CLINIC_FORM_Q6_LABEL")}
                    </label>
                    <textarea
                      name="notes"
                      rows={5}
                      placeholder={rawT("DR_MAGDI_CLINIC_FORM_Q6_PLACEHOLDER")}
                      onChange={handleChange}
                      className="textarea"
                    />
                  </div>

                  {recaptchaSiteKey && (
                    <div className="mb-2">
                      <ReCAPTCHA
                        ref={recaptchaRef}
                        sitekey={recaptchaSiteKey}
                        hl={locale}
                        onChange={(token) => setCaptchaToken(token)}
                        onExpired={() => setCaptchaToken(null)}
                        onErrored={() => setCaptchaToken(null)}
                      />
                    </div>
                  )}

                  <div className="flex justify-end">
                    <button
                      type="submit"
                      disabled={submitting || (!!recaptchaSiteKey && !captchaToken)}
                      className="btn-primary px-8"
                    >
                      {submitting ? (
                        <Loader2 className="w-4 h-4 animate-spin" />
                      ) : (
                        <Send className="w-4 h-4" />
                      )}
                      {submitting
                        ? rawT("DR_MAGDI_CLINIC_FORM_SUBMIT_BTN_LABEL") + "..."
                        : rawT("DR_MAGDI_CLINIC_FORM_SUBMIT_BTN_LABEL")}
                    </button>
                  </div>
                </form>
              )}
            </motion.div>
          )}
        </div>
      </section>
    </>
  );
}
