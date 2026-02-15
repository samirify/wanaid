/**
 * Default SEO values aligned with the live client site (https://wanaid.org).
 * Used when the API does not provide page-specific meta. Update these if the
 * live site’s meta changes.
 */

export const SEO_DEFAULTS = {
  /** Site title (used as default <title>) */
  title: "WAN Aid",

  /** Default meta description (homepage text from wanaid.org) */
  description:
    "WAN Aid is led by a group of Sudanese ladies from around the globe. So far we have over hundred members all over U.K. We also have active motivated members in Sudan who carry out our projects in Sudan and connect us with our target groups.",

  /** Default meta keywords (from wanaid.org) */
  keywords:
    "WAN Aid, wanaid, children, hygiene, clean drinking water, sanitation facilities, improve health, preventable disease with high prevalence",

  /** Theme color for browser UI (from wanaid.org) */
  themeColor: "#0858bb",
} as const;

/** Per-route default title, description, and keywords for SEO when API provides none. */
export const PATH_META: Record<
  string,
  { title: string; description?: string; keywords?: string }
> = {
  main: {
    title: SEO_DEFAULTS.title,
    description: SEO_DEFAULTS.description,
    keywords: SEO_DEFAULTS.keywords,
  },
  gallery: {
    title: `Gallery | ${SEO_DEFAULTS.title}`,
    description: `Photo and video gallery from ${SEO_DEFAULTS.title}.`,
    keywords:
      "WAN Aid gallery, photos, videos, Sudan, charity, humanitarian, WAN Aid media",
  },
  about: {
    title: `About Us | ${SEO_DEFAULTS.title}`,
    description: `Learn about ${SEO_DEFAULTS.title} – who we are and our mission.`,
    keywords:
      "WAN Aid about, who we are, team, mission, Sudan, UK charity, Sudanese diaspora",
  },
  contact: {
    title: `Contact | ${SEO_DEFAULTS.title}`,
    description: `Get in touch with ${SEO_DEFAULTS.title}.`,
    keywords: "WAN Aid contact, get in touch, email, address, phone, charity contact",
  },
  blog: {
    title: `Blog | ${SEO_DEFAULTS.title}`,
    description: `News and updates from ${SEO_DEFAULTS.title}.`,
    keywords:
      "WAN Aid blog, news, updates, Sudan, charity news, humanitarian updates",
  },
  "open-causes": {
    title: `Open Causes | ${SEO_DEFAULTS.title}`,
    description: `Support our current causes and make a difference.`,
    keywords:
      "WAN Aid causes, donate, fundraising, open causes, Sudan, charity, support",
  },
  "privacy-policy": {
    title: `Privacy Policy | ${SEO_DEFAULTS.title}`,
    description: `Privacy policy of ${SEO_DEFAULTS.title}.`,
    keywords: "WAN Aid privacy policy, data protection, GDPR, privacy",
  },
  "terms-of-use": {
    title: `Terms of Use | ${SEO_DEFAULTS.title}`,
    description: `Terms of use for ${SEO_DEFAULTS.title} website.`,
    keywords: "WAN Aid terms of use, terms and conditions, legal",
  },
  disclaimer: {
    title: `Disclaimer | ${SEO_DEFAULTS.title}`,
    description: `Disclaimer for ${SEO_DEFAULTS.title} website.`,
    keywords: "WAN Aid disclaimer, legal disclaimer",
  },
  "marital-therapy-clinic": {
    title: `Marital Therapy Clinic | ${SEO_DEFAULTS.title}`,
    description: `Marital therapy clinic information.`,
    keywords: "WAN Aid marital therapy, therapy clinic, counselling, Sudan",
  },
  "causes/search": {
    title: `Search Causes | ${SEO_DEFAULTS.title}`,
    description: `Search our causes.`,
    keywords: "WAN Aid search causes, find causes, donate, fundraising",
  },
};
