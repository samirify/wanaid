<?php

namespace Modules\Client\Database\Seeders;

use App\Traits\AppHelperTrait;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\ApplicationCode;
use Modules\Core\Models\ApplicationCodeType;
use Modules\Core\Models\Payment;
use Modules\Core\Models\Setting;

class PaymentsSeeder extends Seeder
{
    use AppHelperTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Setting::create([
        //     'name' => 'payment_success_emails',
        //     'value' => 'soiswis@gmail.com;info@samirify.com'
        // ]);

        // Setting::create([
        //     'name' => 'payment_failure_emails',
        //     'value' => 'soiswis@gmail.com;info@samirify.com'
        // ]);

        ApplicationCodeType::create([
            'code' => 'PAYMENT_METHOD',
            'name' => 'Payment Method',
        ]);

        $paymentMethodType = DB::table('application_code_type')->where('code', 'PAYMENT_METHOD')->first();

        $paymentMethodTypes = [
            'CA' => 'Cash',
            'CK' => 'Cheque',
            'PP' => 'PayPal',
            'CC' => 'Credit Card',
            'DM' => 'Data Migration',
        ];

        foreach ($paymentMethodTypes as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $paymentMethodType->id,
                'code' => $code,
                'name' => $value,
            ]);
        }

        ApplicationCodeType::create([
            'code' => 'PAYMENT_STATUS',
            'name' => 'Payment Status',
        ]);

        $paymentStatusType = DB::table('application_code_type')->where('code', 'PAYMENT_STATUS')->first();

        $paymentStatusTypes = [
            'CO' => 'Completed',
            'PD' => 'Pending',
            'RJ' => 'Rejected'
        ];

        foreach ($paymentStatusTypes as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $paymentStatusType->id,
                'code' => $code,
                'name' => $value,
            ]);
        }

        DB::table('payments')->delete();

        $paymentStatuses = DB::table('application_code AS ac')
            ->select('ac.id', 'ac.code')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->where([
                'act.code' => 'PAYMENT_STATUS',
            ])
            ->pluck('ac.id', 'ac.code')
            ->toArray();

        $paymentMethods = DB::table('application_code AS ac')
            ->select('ac.id', 'ac.code')
            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
            ->where([
                'act.code' => 'PAYMENT_METHOD',
            ])
            ->pluck('ac.id', 'ac.code')
            ->toArray();


        for ($i = 0; $i < 1000; $i++) {
            $date = Carbon::today()->subDays(rand(0, 400))->addSeconds(rand(0, 86400));

            Payment::create([
                'code' => generateRandomString(10, 'upper'),
                'entity_name' => "SomePayment",
                'entity_id' => (string)$i,
                'amount' => rand(1, 99999),
                'payment_method_id' => $paymentMethods[array_rand($paymentMethods)],
                'status_id' => $paymentStatuses[array_rand($paymentStatuses)],
                'created_at' => $date->format('Y-m-d H:i:s'),
                'updated_at' => $date->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
