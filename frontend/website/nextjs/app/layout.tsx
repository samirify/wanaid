import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Charity Organisation",
  description:
    "Making a difference in communities through compassion, action, and hope.",
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return children;
}
