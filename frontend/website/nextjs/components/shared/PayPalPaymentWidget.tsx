"use client";

import { useState, useEffect, Component, type ReactNode } from "react";
import { useRawTranslation } from "@/hooks/useRawTranslation";
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
  { children: ReactNode },
  { hasError: boolean }
> {
  constructor(props: { children: ReactNode }) {
    super(props);
    this.state = { hasError: false };
  }
  static getDerivedStateFromError() {
    return { hasError: true };
  }
  render() {
    if (this.state.hasError) return null;
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
}: {
  donationAmount: number;
  currencyCode: string;
  currencySymbol: string;
  causeId: string;
  locale: string;
  rawT: (key: string) => string;
  setProcessing: (v: boolean) => void;
  processing: boolean;
}) {
  const [PayPalModule, setPayPalModule] = useState<{
    PayPalScriptProvider: React.ComponentType<Record<string, unknown>>;
    PayPalButtons: React.ComponentType<Record<string, unknown>>;
  } | null>(null);

  const clientId = process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID || "";

  useEffect(() => {
    if (!clientId) return;
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
  }, [clientId]);

  if (!clientId || !PayPalModule) return null;

  const { PayPalScriptProvider, PayPalButtons } = PayPalModule;

  return (
    <PayPalErrorBoundary>
      <PayPalScriptProvider
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

              alert(
                rawT("WEBSITE_CONTACT_WRITE_TO_FORM_THANK_YOU_LABEL") ||
                  "Thank you for your donation!"
              );
              window.location.href = `/${locale}`;
            } catch {
              alert(
                rawT(
                  "WEBSITE_PAYMENT_PROCESSED_SUCCESS_WITH_INTERNAL_ERROR_MESSAGE"
                ) || "Payment processed. Thank you!"
              );
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

  const currencySymbol =
    getSymbolFromCurrency(cause.currencies_code) || "£";
  const presetAmounts = [1, 5, 10, 20];

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

      {/* White area — PayPal buttons */}
      <div className="bg-white rounded-b-2xl p-4">
        <PayPalButtonsWrapper
          donationAmount={donationAmount}
          currencyCode={cause.currencies_code || "GBP"}
          currencySymbol={currencySymbol}
          causeId={cause.id}
          locale={locale}
          rawT={rawT}
          setProcessing={setProcessing}
          processing={processing}
        />
      </div>
    </div>
  );
}
