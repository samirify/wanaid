"use client";

import { useState, useEffect } from "react";
import { useTranslations, useLocale } from "next-intl";
import { motion, AnimatePresence } from "framer-motion";
// Using native <img> for external API images to avoid Next.js proxy 401 issues
import { api } from "@/lib/api";
import { mediaUrl, youtubeEmbedUrl } from "@/lib/utils";
import { Loader } from "@/components/shared/Loader";
import { ErrorDisplay } from "@/components/shared/ErrorDisplay";
import { PageHero } from "@/components/shared/PageHero";
import { X, ZoomIn, Play } from "lucide-react";
import type { GalleryItem } from "@/lib/types";

function isVideo(item: GalleryItem): boolean {
  return item.type === "video" && !!(item.video_url ?? item.embed_url ?? item.embedUrl);
}

function getVideoEmbedUrl(item: GalleryItem): string {
  const raw = item.video_url ?? item.embed_url ?? item.embedUrl ?? "";
  return youtubeEmbedUrl(raw);
}

export default function GalleryPage() {
  const t = useTranslations();
  const locale = useLocale();
  const [images, setImages] = useState<GalleryItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedItem, setSelectedItem] = useState<GalleryItem | null>(null);

  useEffect(() => {
    async function fetchGallery() {
      try {
        setLoading(true);
        const data = await api.getGalleryData(locale);
        setImages(data);
      } catch {
        setError("Failed to load gallery.");
      } finally {
        setLoading(false);
      }
    }
    fetchGallery();
  }, [locale]);

  return (
    <>
      <PageHero
        title={t("GALLERY_TITLE_TXT")}
        topLine={t("GALLERY_TOP_HEADER")}
        bottomLine={t("GALLERY_SUB_TITLE_TXT")}
        variant="auto"
        align="center"
        showCurve
        asHeader
      />

      <section className="py-24">
        <div className="container-custom">
          {loading ? (
            <Loader />
          ) : error ? (
            <ErrorDisplay variant="inline" message={error} />
          ) : images.length === 0 ? (
            <p className="text-center text-slate-500 dark:text-slate-400 py-12">
              {t("WEBSITE_NO_RESULTS_LABEL")}
            </p>
          ) : (
            <div className="columns-1 sm:columns-2 lg:columns-3 gap-4 space-y-4">
              {images.map((image, index) => {
                const isVideoItem = isVideo(image);
                return (
                  <motion.div
                    key={image.id || index}
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true }}
                    transition={{ delay: index * 0.05 }}
                    className="break-inside-avoid group cursor-pointer relative rounded-xl overflow-hidden"
                    onClick={() => setSelectedItem(image)}
                  >
                    <img
                      src={mediaUrl(image.img_url)}
                      alt={image.title || `Gallery image ${index + 1}`}
                      className="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-105"
                      loading="lazy"
                    />
                    <div className="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                      {isVideoItem ? (
                        <Play className="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity fill-white" />
                      ) : (
                        <ZoomIn className="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                      )}
                    </div>
                  </motion.div>
                );
              })}
            </div>
          )}
        </div>
      </section>

      {/* Lightbox */}
      <AnimatePresence>
        {selectedItem && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center p-4"
            onClick={() => setSelectedItem(null)}
          >
            <button
              onClick={() => setSelectedItem(null)}
              className="absolute top-6 end-6 w-12 h-12 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition-colors z-10"
              aria-label="Close lightbox"
            >
              <X className="w-6 h-6" />
            </button>
            <motion.div
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.9, opacity: 0 }}
              className="max-w-5xl w-full max-h-[85vh] relative flex items-center justify-center"
              onClick={(e) => e.stopPropagation()}
            >
              {isVideo(selectedItem) ? (
                <iframe
                  src={getVideoEmbedUrl(selectedItem)}
                  title={selectedItem.title || "Gallery video"}
                  className="w-full aspect-video max-h-[85vh] rounded-xl"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                />
              ) : (
                <img
                  src={mediaUrl(selectedItem.img_url)}
                  alt={selectedItem.title || "Gallery image"}
                  className="max-h-[85vh] w-auto object-contain rounded-xl"
                />
              )}
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
