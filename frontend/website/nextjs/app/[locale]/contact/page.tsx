"use client";

import { useState, useRef } from "react";
import { useTranslations, useLocale } from "next-intl";
import { motion } from "framer-motion";
import ReCAPTCHA from "react-google-recaptcha";
import { useAppData } from "@/context/AppContext";
import { api } from "@/lib/api";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import {
  MapPin,
  Phone,
  Mail,
  Send,
  CheckCircle,
  Loader2,
} from "lucide-react";

export default function ContactPage() {
  const t = useTranslations();
  const locale = useLocale();
  const { settings } = useAppData();

  const [formData, setFormData] = useState({
    full_name: "",
    email: "",
    subject: "",
    message: "",
  });
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [captchaToken, setCaptchaToken] = useState<string | null>(null);
  const recaptchaRef = useRef<ReCAPTCHA>(null);

  const recaptchaSiteKey = process.env.NEXT_PUBLIC_GOOGLE_RECAPTCHA_SITE_KEY || "";

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
  ) => {
    setFormData((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (recaptchaSiteKey && !captchaToken) {
      setError(t("WEBSITE_CONTACT_WRITE_TO_FORM_CAPTCHA_REQUIRED") || "Please complete the CAPTCHA");
      return;
    }

    setSubmitting(true);
    setError(null);
    setSuccess(false);

    try {
      await api.submitContactForm({
        ...formData,
        lang: locale,
        "g-recaptcha-response": captchaToken || undefined,
      });
      setSuccess(true);
      setFormData({ full_name: "", email: "", subject: "", message: "" });
      setCaptchaToken(null);
      recaptchaRef.current?.reset();
    } catch {
      setError(t("WEBSITE_ERRORS_SERVER_ERROR_MESSAGE"));
    } finally {
      setSubmitting(false);
    }
  };

  const contactInfo = [
    {
      icon: Phone,
      value: settings.main_contact_phone_number,
      href: `tel:${settings.main_contact_phone_number}`,
      dir: "ltr" as const,
    },
    {
      icon: Mail,
      value: settings.main_contact_email,
      href: `mailto:${settings.main_contact_email}`,
      dir: "ltr" as const,
    },
    {
      icon: MapPin,
      value: settings.main_contact_address,
    },
  ].filter((item) => item.value);

  return (
    <>
      {/* Page Hero */}
      <div className="page-hero">
        <div className="page-hero-content">
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-primary-200 font-medium mb-3"
          >
            {t("WEBSITE_CONTACT_HEADER_MAIN_TOP")}
          </motion.p>
          <motion.h1
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4"
          >
            {t("WEBSITE_CONTACT_HEADER_LABEL")}
          </motion.h1>
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 }}
            className="text-white/80 text-lg"
          >
            {t("WEBSITE_CONTACT_SUB_HEADER_MESSAGE")}
          </motion.p>
        </div>
      </div>

      <section className="overflow-hidden py-16 md:py-20 lg:py-28">
        <div className="container-custom">
          <div className="-mx-4 flex flex-wrap">
            {/* Contact Form – 7/12 on lg */}
            <div className="w-full px-4 lg:w-7/12 xl:w-8/12">
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="mb-12 rounded-sm bg-white dark:bg-slate-800 px-8 py-11 shadow-lg sm:p-[55px] lg:mb-5 lg:px-8 xl:p-[55px]"
              >
                <h2 className="mb-3 text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl lg:text-2xl xl:text-3xl">
                  {t("WEBSITE_CONTACT_WRITE_TO_US_HEADER")}
                </h2>
                <p className="mb-12 text-base font-medium text-slate-600 dark:text-slate-400">
                  {t("WEBSITE_CONTACT_WRITE_TO_US_SUB_HEADER")}
                </p>

                {success ? (
                  <motion.div
                    initial={{ opacity: 0, scale: 0.9 }}
                    animate={{ opacity: 1, scale: 1 }}
                    className="text-center py-12"
                  >
                    <div className="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                      <CheckCircle className="w-8 h-8 text-green-500" />
                    </div>
                    <h3 className="text-xl font-bold text-slate-900 dark:text-white mb-2">
                      {t("WEBSITE_CONTACT_WRITE_TO_FORM_THANK_YOU_LABEL")}
                    </h3>
                  </motion.div>
                ) : (
                  <form onSubmit={handleSubmit}>
                    {error && (
                      <div className="mb-6">
                        <ErrorDisplay variant="inline" message={error} />
                      </div>
                    )}

                    <div className="-mx-4 flex flex-wrap">
                      <div className="w-full px-4 md:w-1/2">
                        <div className="mb-8">
                          <label className="mb-3 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {t("WEBSITE_CONTACT_WRITE_TO_FORM_NAME_LABEL")}
                          </label>
                          <input
                            type="text"
                            name="full_name"
                            value={formData.full_name}
                            onChange={handleChange}
                            required
                            placeholder={t("WEBSITE_CONTACT_WRITE_TO_FORM_NAME_PLACEHOLDER")}
                            className="w-full rounded-sm border border-slate-200 dark:border-transparent bg-slate-50 dark:bg-slate-700 px-6 py-3 text-base text-slate-700 dark:text-slate-300 outline-none focus:border-primary dark:focus:border-primary transition-colors"
                          />
                        </div>
                      </div>
                      <div className="w-full px-4 md:w-1/2">
                        <div className="mb-8">
                          <label className="mb-3 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {t("WEBSITE_CONTACT_WRITE_TO_FORM_EMAIL_LABEL")}
                          </label>
                          <input
                            type="email"
                            name="email"
                            value={formData.email}
                            onChange={handleChange}
                            required
                            placeholder={t("WEBSITE_CONTACT_WRITE_TO_FORM_EMAIL_PLACEHOLDER")}
                            className="w-full rounded-sm border border-slate-200 dark:border-transparent bg-slate-50 dark:bg-slate-700 px-6 py-3 text-base text-slate-700 dark:text-slate-300 outline-none focus:border-primary dark:focus:border-primary transition-colors"
                          />
                        </div>
                      </div>
                      <div className="w-full px-4">
                        <div className="mb-8">
                          <label className="mb-3 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {t("WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_LABEL")}
                          </label>
                          <select
                            name="subject"
                            value={formData.subject}
                            onChange={handleChange}
                            required
                            className="w-full rounded-sm border border-slate-200 dark:border-transparent bg-slate-50 dark:bg-slate-700 px-6 py-3 text-base text-slate-700 dark:text-slate-300 outline-none focus:border-primary dark:focus:border-primary transition-colors"
                          >
                            <option value="">
                              -- {t("WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_SELECT_OPTION_LABEL")} --
                            </option>
                            <option value="general_feedback">
                              {t("WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_GENERAL_FEEDBACK_OPTION_LABEL")}
                            </option>
                            <option value="join_us_request">
                              {t("WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_JOIN_US_OPTION_LABEL")}
                            </option>
                            <option value="technical_issues">
                              {t("WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_TECH_ISSUES_OPTION_LABEL")}
                            </option>
                          </select>
                        </div>
                      </div>
                      <div className="w-full px-4">
                        <div className="mb-8">
                          <label className="mb-3 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {t("WEBSITE_CONTACT_WRITE_TO_FORM_MESSAGE_LABEL")}
                          </label>
                          <textarea
                            name="message"
                            value={formData.message}
                            onChange={handleChange}
                            required
                            rows={5}
                            placeholder={t("WEBSITE_CONTACT_WRITE_TO_FORM_MESSAGE_PLACEHOLDER")}
                            className="w-full resize-none rounded-sm border border-slate-200 dark:border-transparent bg-slate-50 dark:bg-slate-700 px-6 py-3 text-base text-slate-700 dark:text-slate-300 outline-none focus:border-primary dark:focus:border-primary transition-colors"
                          />
                        </div>
                      </div>
                      {recaptchaSiteKey && (
                        <div className="w-full px-4 mb-8">
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
                      <div className="w-full px-4">
                        <button
                          type="submit"
                          disabled={submitting || (!!recaptchaSiteKey && !captchaToken)}
                          className="rounded-sm bg-primary px-9 py-4 text-base font-medium text-white shadow-md duration-300 hover:bg-primary/90 disabled:opacity-50 inline-flex items-center gap-2"
                        >
                          {submitting ? (
                            <Loader2 className="w-4 h-4 animate-spin" />
                          ) : (
                            <Send className="w-4 h-4" />
                          )}
                          {t("WEBSITE_CONTACT_WRITE_TO_FORM_SUBMIT_BTN_LABEL")}
                        </button>
                      </div>
                    </div>
                  </form>
                )}
              </motion.div>
            </div>

            {/* Contact Info Sidebar – 5/12 on lg */}
            <div className="w-full px-4 lg:w-5/12 xl:w-4/12">
              <motion.div
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                className="space-y-6"
              >
                {contactInfo.map((item, index) => (
                  <motion.div
                    key={index}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: index * 0.1 }}
                    className="text-center"
                  >
                    <item.icon className="w-8 h-8 text-primary-600 dark:text-primary-400 mx-auto mb-3" />
                    {item.href ? (
                      <a
                        href={item.href}
                        className="text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                        dir={"dir" in item ? item.dir : undefined}
                      >
                        {item.value}
                      </a>
                    ) : (
                      <div className="text-primary-600 dark:text-primary-400">
                        {item.value}
                      </div>
                    )}
                  </motion.div>
                ))}

                {/* Map */}
                {settings.google_maps_iframe_url && (
                  <div className="mt-8 rounded-2xl overflow-hidden shadow-lg border border-slate-200 dark:border-slate-700">
                    <iframe
                      src={settings.google_maps_iframe_url}
                      width="100%"
                      height="300"
                      style={{ border: 0 }}
                      allowFullScreen
                      loading="lazy"
                      referrerPolicy="no-referrer-when-downgrade"
                      title="Location map"
                    />
                  </div>
                )}
              </motion.div>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
