<?php

namespace Database\Seeders;

use App\Models\FraudRule;
use Illuminate\Database\Seeder;

class FraudRuleSeeder extends Seeder
{
    /**
     * Seed the fraud detection rules.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Rule 1: Transfer Frequency
        |--------------------------------------------------------------------------
        | More than 5 transfers within 1 minute
        | will create a fraud report.
        */
        FraudRule::updateOrCreate(
            [
                'code' => 'TRANSFER_FREQUENCY_5_PER_MINUTE',
            ],
            [
                'name' => 'More Than 5 Transfers Per Minute',

                'description' => 'More than 5 completed transfers within one minute triggers a fraud report.',

                'configuration_type' => 'transfer_frequency',

                'threshold' => 5,

                'severity' => 'medium',

                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Rule 2: Large Transfer OTP
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | This checks ONE individual transfer.
        |
        | Example:
        | 30,000 + 30,000 + 40,000 = 100,000 total
        | A new 20,000 transfer does NOT require OTP.
        |
        | But:
        | 60,000 in ONE transfer = OTP required.
        */
        FraudRule::updateOrCreate(
            [
                'code' => 'SINGLE_TRANSFER_50000_OTP',
            ],
            [
                'name' => 'Single Transfer Above 50,000 MMK',

                'description' => 'A single transfer above 50,000 MMK requires OTP verification.',

                'configuration_type' => 'large_transfer_otp',

                'threshold' => 50000,

                'severity' => 'high',

                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Rule 3: New Receiver + High Amount
        |--------------------------------------------------------------------------
        | If the sender has never transferred to this receiver
        | and the amount is above 50,000 MMK, flag the transaction.
        */
        FraudRule::updateOrCreate(
            [
                'code' => 'NEW_RECEIVER_HIGH_AMOUNT',
            ],
            [
                'name' => 'New Receiver With High Amount',

                'description' => 'A high-value transfer to a receiver who has never received money from this sender is flagged.',

                'configuration_type' => 'new_receiver_high_amount',

                'threshold' => 50000,

                'severity' => 'high',

                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Rule 4: New Account + Immediate Transfer
        |--------------------------------------------------------------------------
        | If the account was created within the last 10 minutes
        | and the user makes a transfer, flag the transaction.
        */
        FraudRule::updateOrCreate(
            [
                'code' => 'NEW_ACCOUNT_TRANSFER',
            ],
            [
                'name' => 'New Account Immediate Transfer',

                'description' => 'A transfer made within 10 minutes of account creation is flagged.',

                'configuration_type' => 'new_account_transfer',

                'threshold' => 10,

                'severity' => 'medium',

                'is_active' => true,
            ]
        );
    }
}
