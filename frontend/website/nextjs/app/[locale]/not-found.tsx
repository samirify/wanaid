"use client";

import { ErrorDisplay } from "@/components/shared/ErrorDisplay";

export default function NotFound() {
  return (
    <ErrorDisplay
      variant="page"
      errorCode="404"
      showHomeButton
    />
  );
}
