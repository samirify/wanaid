<?php

namespace Modules\Core\Services;

// use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentService
{
    private const STATS_CODE = 'payments';
    private const STATS_MAX_PERIOD_MONTHS = 12;

    public function __construct(
        private readonly StatsService $statsService
    ) {}

    /**
     * Fetches payments 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getPayments($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'payments' => []
        ];

        try {
            $query = DB::table('payments AS p')
                ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.status_id')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->leftJoin('users AS u', 'u.id', '=', 'p.created_by')
                ->leftJoin('contacts AS con', 'con.id', '=', 'u.contact_id')
                ->leftJoin('persons AS per', 'per.contact_id', '=', 'con.id')
                ->select(
                    'p.id AS id',
                    'p.unique_title AS unique_title',
                    'p.title AS title',
                    'p.description AS description',
                    'p.due_date AS due_date',
                    'p.status_id AS status_id',
                    'ac.name AS status',
                    'p.created_by AS created_by',
                    'p.active AS active',
                    'p.created_at AS created_at',
                    'p.updated_at AS updated_at',
                    DB::raw('CONCAT(per.first_name," ", per.last_name) AS creator')
                );

            if (count($where) > 0) {
                $query->where($where);
            }

            $result['payments'] = $query->get();
            $result['success'] = true;
            $result['message'] = 'Results fetched successfully!';
        } catch (Exception $ex) {
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function getPaymentMethodIdByCode($code)
    {
        $query = DB::table('application_code AS ac')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->select(
                'ac.id AS id',
            )
            ->where([
                'ac.code' => $code,
                'act.code' => 'PAYMENT_METHOD'
            ]);

        $record = $query->first();

        return $record ? $record->id : null;
    }

    public function getPaymentStatusIdByCode($code)
    {
        $query = DB::table('application_code AS ac')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->select(
                'ac.id AS id',
            )
            ->where([
                'ac.code' => $code,
                'act.code' => 'PAYMENT_STATUS'
            ]);

        $record = $query->first();

        return $record ? $record->id : null;
    }

    public function getPayPalOrderDetails($orderId)
    {
        $payPalConfig = config('client.payment.paypal');
        $apiRoot = $payPalConfig['api_root_v2'];

        $accessToken = $this->getPayPalAccessToken();
        $curl = curl_init("{$apiRoot}/checkout/orders/{$orderId}");
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
            'Content-Type: application/json'
        ));
        $response = curl_exec($curl);
        return json_decode($response);
    }

    private function getPayPalAccessToken()
    {
        $payPalConfig = config('client.payment.paypal');
        $apiRoot = $payPalConfig['api_root_v1'];

        $ch = curl_init();
        $clientId = $payPalConfig['client_id']; //client Id
        $secret = $payPalConfig['secret']; // client secrete key
        curl_setopt($ch, CURLOPT_URL, "{$apiRoot}/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $result = curl_exec($ch);
        $accessToken = null;
        if (empty($result))
            die('invalid access token');
        else {
            $json = json_decode($result);
            $accessToken = $json->access_token;
        }
        curl_close($ch);

        return $accessToken;
    }

    public function sendPaymentConfirmationEmail($data = [])
    {
        $result = [
            'success' => false,
            'msg' => '',
        ];

        try {

            $emails = [];
            $emailsConfig = config('client.site.emails');

            $query = DB::table('settings AS s')
                ->select('s.name', 's.value')
                ->whereIn('s.name', array_keys($emailsConfig));

            $emailsSettingsList = $query->get()->toArray();

            foreach ($emailsSettingsList as $emailTarget) {
                if ($emailTarget->value) {
                    $emails[$emailTarget->name] = explode(';', trim($emailTarget->value));
                }
            }

            // throw new Exception(json_encode($data), 500);

            if (array_key_exists('payment_success_emails', $emails) && is_array($emails['payment_success_emails']) && count($emails['payment_success_emails']) > 0) {

                $environment = config('client.app_environment');

                $data['target_emails'] = $emails['payment_success_emails'];

                if ('production' !== $environment) {
                    $data['email'] = 'soiswis@gmail.com';
                }

                $data['signature'] = getLanguageTranslation('SITE_EMAIL_PAYMENT_USER_SIGNATURE', $data['lang_id']);

                // Send email to company
                $data['user_message'] = str_replace(
                    [
                        '[[full_name]]',
                        '[[order_id]]',
                    ],
                    [
                        $data['full_name'],
                        $data['order_id'],
                    ],
                    getLanguageTranslation('SITE_EMAIL_ADMIN_PAYPAL_CONTENT', $data['lang_id'])
                );
                Mail::send('core::emails/payment/recieved-email', $data, function ($message) use ($data) {
                    $message
                        ->from($data['email'], $data['full_name'])
                        ->to($data['target_emails'], env('APP_NAME'))
                        ->subject(getLanguageTranslation('SITE_EMAIL_ADMIN_PAYPAL_SUBJECT', $data['lang_id']));
                });

                // Send confirmation email to user
                $data['user_message'] = str_replace(
                    [
                        '[[full_name]]',
                        '[[order_id]]',
                    ],
                    [
                        $data['full_name'],
                        $data['order_id'],
                    ],
                    getLanguageTranslation('SITE_EMAIL_USER_PAYPAL_CONTENT', $data['lang_id'])
                );
                Mail::send('core::emails/payment/thank-you-no-reply-email', $data, function ($message) use ($data) {
                    $message
                        ->from('no-reply@samirify.com', $data['full_name'])
                        ->to($data['email'], $data['full_name'])
                        ->subject(getLanguageTranslation('SITE_EMAIL_USER_PAYPAL_SUBJECT', $data['lang_id']));
                });
            } else {
                throw new Exception('Opps! something went wront at our end, Sorry! Please try again or contact us with ref: Error 101', 500);
            }

            $result['success'] = true;
            $result['msg'] = 'Email sent successfully!';
        } catch (Exception $ex) {
            file_put_contents('err.txt', $ex->getMessage());
            $result['error'] = [
                'code' => $ex->getCode(),
                'msg' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function updatePaymentsStats(?int $months = 0): void
    {
        $periodMonths = (empty($months) ? self::STATS_MAX_PERIOD_MONTHS : $months) - 1;

        $payments = DB::table('payments AS p')
            ->select([
                'p.status_id as status_id',
                'ac.code as status_code',
                'ac.name as status_name',
                DB::raw('count(*) as count'),
                DB::raw('DATE_FORMAT(p.updated_at,\'%b\') as month')
            ])
            ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.status_id')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            // ->whereDate('p.updated_at', '<=', (new Carbon)->now()->endOfDay()->toDateString())
            // ->whereDate('p.updated_at', '>=', (new Carbon())->subDays(180)->startOfDay()->toDateString())
            ->where('p.updated_at', '>=', now()->subMonths($periodMonths))
            ->groupBy(['p.status_id', DB::raw('DATE_FORMAT(p.updated_at,\'%b\')')])
            ->get();

        $paymentsCounts = [];

        foreach ($payments as $value) {
            $paymentsCounts[$value->status_id]['code'] = $value->status_code;
            $paymentsCounts[$value->status_id]['name'] = $value->status_name;
            $paymentsCounts[$value->status_id]['months'][$value->month] = $value->count;
        }

        $this->statsService->updateStats([
            [
                'code' => 'payments',
                'value' => json_encode($paymentsCounts)
            ],
        ]);
    }

    public function getPaymentsStats(string $months, bool $formatted = true): array
    {
        $stats = $this->statsService->getStatsByCodes(['payments']);

        return $formatted ? $this->formatPaymentStats($stats, $months) : $stats;
    }

    public function formatPaymentStats(array $data, int $months): array
    {
        $formattedData = [];

        $periodMonths = $this->getPeriodMonths($months);

        $formattedData['last_updated'] = $data[self::STATS_CODE]['last_updated'] ?? null;
        $formattedData['labels'] = $periodMonths;

        $statsData = $data[self::STATS_CODE]['data'] ?? [];
        foreach ($statsData as $periodData) {
            $pData = [];
            $periodName = $periodData['name'];
            $pData['code'] = $periodData['code'];
            $pData['label'] = $periodName;
            foreach ($periodMonths as $month) {
                $pData['data'][] = $periodData['months'][$month] ?? 0;
            }

            $formattedData['datasets'][] = $pData;
        }

        return $formattedData;
    }

    private function getPeriodMonths(int $numOfMonths): array
    {
        $months = [];

        $maxNumOfMonths = $numOfMonths - 1;

        for ($i = $maxNumOfMonths; $i >= 0; $i--) {
            $months[] = substr(date('F', strtotime("-$i month")), 0, 3);
        }

        return $months;
    }
}
