"use client";

import { FullScreenNotificationToast } from "@/components/shared/FullScreenNotificationToast";

export function DonationSuccessToast({
  message,
  visible,
  onClose,
}: {
  message: string;
  visible: boolean;
  onClose?: () => void;
}) {
  return (
    <FullScreenNotificationToast
      variant="success"
      message={message}
      visible={visible}
      onClose={onClose}
    />
  );
}
