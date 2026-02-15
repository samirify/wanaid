"use client";

import { useState, useEffect, Component, type ReactNode } from "react";
import { useRawTranslation } from "@/hooks/useRawTranslation";
import { DonationSuccessToast } from "@/components/shared/DonationSuccessToast";
import { useLocale } from "next-intl";
import getSymbolFromCurrency from "currency-symbol-map";

interface PayPalPaymentWidgetProps {
  cause: {
    id: string;
    currencies_code: string;
    unique_title: string;
  };
}

/* ── Error boundary to catch PayPal SDK crashes ── */
class PayPalErrorBoundary extends Component<
  { children: ReactNode; message?: string },
  { hasError: boolean }
> {
  constructor(props: { children: ReactNode; message?: string }) {
    super(props);
    this.state = { hasError: false };
  }
  static getDerivedStateFromError() {
    return { hasError: true };
  }
  render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-[100px] flex items-center justify-center rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 text-center">
          <p className="text-sm text-amber-800 dark:text-amber-200">
            {this.props.message || "PayPal failed to load. Check your connection or try again."}
          </p>
        </div>
      );
    }
    return this.props.children;
  }
}

/* ── Lazy-loaded PayPal buttons (only imported client-side) ── */
function PayPalButtonsWrapper({
  donationAmount,
  currencyCode,
  currencySymbol,
  causeId,
  locale,
  rawT,
  setProcessing,
  processing,
  onDonationSuccess,
}: {
  donationAmount: number;
  currencyCode: string;
  currencySymbol: string;
  causeId: string;
  locale: string;
  rawT: (key: string) => string;
  setProcessing: (v: boolean) => void;
  processing: boolean;
  onDonationSuccess?: () => void;
}) {
  const [PayPalModule, setPayPalModule] = useState<{
    PayPalScriptProvider: React.ComponentType<Record<string, unknown>>;
    PayPalButtons: React.ComponentType<Record<string, unknown>>;
  } | null>(null);
  const [readyToMount, setReadyToMount] = useState(false);

  const clientId = process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID || "";

  // Defer loading so we skip React Strict Mode / HMR unmount and avoid "zoid destroyed all components"
  useEffect(() => {
    if (!clientId) return;
    const stableTimer = window.setTimeout(() => setReadyToMount(true), 150);
    return () => window.clearTimeout(stableTimer);
  }, [clientId]);

  useEffect(() => {
    if (!clientId || !readyToMount) return;
    import("@paypal/react-paypal-js").then((mod) => {
      setPayPalModule({
        PayPalScriptProvider:
          mod.PayPalScriptProvider as unknown as React.ComponentType<
            Record<string, unknown>
          >,
        PayPalButtons:
          mod.PayPalButtons as unknown as React.ComponentType<
            Record<string, unknown>
          >,
      });
    });
  }, [clientId, readyToMount]);

  if (!clientId) {
    return (
      <div className="min-h-[120px] flex flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 p-4 text-center">
        <p className="text-sm text-slate-600 dark:text-slate-400 mb-1">
          PayPal not configured
        </p>
        <p className="text-xs text-slate-500 dark:text-slate-500">
          Set <code className="bg-slate-200 dark:bg-slate-700 px-1 rounded">NEXT_PUBLIC_PAYPAL_CLIENT_ID</code> in your environment.
        </p>
      </div>
    );
  }

  if (!readyToMount || !PayPalModule) {
    return (
      <div className="min-h-[120px] flex flex-col items-center justify-center rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 p-4">
        <div className="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mb-2" aria-hidden />
        <p className="text-sm text-slate-600 dark:text-slate-400">
          {rawT("WEBSITE_PROCESSING_PAYMENT_MESSAGE") || "Loading PayPal..."}
        </p>
      </div>
    );
  }

  const { PayPalScriptProvider, PayPalButtons } = PayPalModule;

  return (
    <PayPalErrorBoundary>
      <PayPalScriptProvider
        key={`paypal-${causeId}`}
        options={{ clientId, currency: currencyCode }}
      >
        {processing && (
          <p className="text-sm text-center text-slate-500 animate-pulse mb-2">
            {rawT("WEBSITE_PROCESSING_PAYMENT_MESSAGE") ||
              "Processing payment..."}
          </p>
        )}
        <PayPalButtons
          style={{
            layout: "vertical",
            shape: "rect",
            label: "paypal",
          }}
          createOrder={(
            _data: Record<string, unknown>,
            actions: {
              order: {
                create: (o: Record<string, unknown>) => Promise<string>;
              };
            }
          ) => {
            return actions.order.create({
              purchase_units: [
                {
                  amount: {
                    value: String(donationAmount),
                    currency_code: currencyCode,
                  },
                },
              ],
            });
          }}
          onApprove={async (
            _data: { orderID: string },
            actions: {
              order: {
                capture: () => Promise<Record<string, unknown>>;
              };
            }
          ) => {
            setProcessing(true);
            try {
              const details = await actions.order.capture();
              const apiUrl =
                process.env.NEXT_PUBLIC_API_URL ||
                "https://api.wanaid.org/api";

              await fetch(`${apiUrl}/payments/create`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                  code: _data.orderID,
                  amount: donationAmount,
                  formatted_amount: currencySymbol + donationAmount,
                  entity_name: "CausePayment",
                  entity_id: causeId,
                  payment_method_code: "PP",
                  status_code: "CO",
                  lang: locale,
                  details,
                }),
              });

              onDonationSuccess?.();
            } catch {
              onDonationSuccess?.();
            } finally {
              setProcessing(false);
            }
          }}
          onError={() => {
            setProcessing(false);
          }}
        />
      </PayPalScriptProvider>
    </PayPalErrorBoundary>
  );
}

/* ── Main widget ── */
export function PayPalPaymentWidget({ cause }: PayPalPaymentWidgetProps) {
  const rawT = useRawTranslation();
  const locale = useLocale();
  const [donationAmount, setDonationAmount] = useState(10);
  const [processing, setProcessing] = useState(false);
  const [showSuccessToast, setShowSuccessToast] = useState(false);

  const currencySymbol =
    getSymbolFromCurrency(cause.currencies_code) || "£";
  const presetAmounts = [1, 5, 10, 20];

  const handleDonationSuccess = () => {
    setShowSuccessToast(true);
    setTimeout(() => {
      setShowSuccessToast(false);
      window.location.href = `/${locale}`;
    }, 8000);
  };

  const handleCloseToast = () => {
    setShowSuccessToast(false);
    setTimeout(() => {
      window.location.href = `/${locale}`;
    }, 350);
  };

  const successMessage =
    rawT("WEBSITE_CONTACT_WRITE_TO_FORM_THANK_YOU_LABEL") ||
    "Thank you for your donation!";

  return (
    <div>
      {/* Pink header area — preset buttons + custom input */}
      <div className="bg-primary-600 rounded-t-2xl p-4 space-y-3">
        <div className="grid grid-cols-4 gap-2">
          {presetAmounts.map((amount) => (
            <button
              key={amount}
              type="button"
              onClick={() => setDonationAmount(amount)}
              className={`py-2.5 rounded-lg font-semibold text-sm transition-all ${
                donationAmount === amount
                  ? "bg-white text-primary-700 shadow-md"
                  : "bg-primary-500 text-white hover:bg-primary-400 border border-primary-400"
              }`}
            >
              {currencySymbol}
              {amount}
            </button>
          ))}
        </div>

        <div className="flex items-center bg-white rounded-lg overflow-hidden">
          <span className="ps-3 text-sm text-slate-600 whitespace-nowrap shrink-0">
            {rawT("WEBSITE_PAYMENT_SELECT_OR_TYPE_AMOUNT_TXT") ||
              "Or type your amount"}{" "}
            {currencySymbol}
          </span>
          <input
            type="number"
            min="1"
            value={donationAmount}
            onChange={(e) =>
              setDonationAmount(Number(e.target.value) || 1)
            }
            className="py-2.5 px-2 text-slate-900 outline-none bg-transparent w-16 shrink-0"
          />
        </div>
      </div>

      {/* White area — PayPal buttons (min-height so layout doesn't collapse) */}
      <div className="bg-white dark:bg-slate-800 rounded-b-2xl p-4 min-h-[140px]">
        <PayPalButtonsWrapper
          donationAmount={donationAmount}
          currencyCode={cause.currencies_code || "GBP"}
          currencySymbol={currencySymbol}
          causeId={cause.id}
          locale={locale}
          rawT={rawT}
          setProcessing={setProcessing}
          processing={processing}
          onDonationSuccess={handleDonationSuccess}
        />
      </div>

      {/* On-screen success notification — shows for 8s (or close button), then redirects */}
      <DonationSuccessToast
        message={successMessage}
        visible={showSuccessToast}
        onClose={handleCloseToast}
      />
    </div>
  );
}
