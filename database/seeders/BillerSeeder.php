<?php

namespace Database\Seeders;

use App\Models\Biller;
use Illuminate\Database\Seeder;

class BillerSeeder extends Seeder
{
    /**
     * Seed the billers table.
     */
    public function run(): void
    {
        $mockApiUrl = 'http://127.0.0.1:8000/api/mock/bill/pay';

        Biller::updateOrCreate(
            ['code' => 'MPT'],
            [
                'name' => 'MPT',
                'category' => 'mobile',
                'description' =>
                'Myanmar Posts and Telecommunications mobile top-up and bill payment.',
                'api_url' => $mockApiUrl,
                'is_active' => true,
            ]
        );

        Biller::updateOrCreate(
            ['code' => 'ATOM'],
            [
                'name' => 'ATOM Myanmar',
                'category' => 'mobile',
                'description' =>
                'ATOM Myanmar mobile top-up and bill payment.',
                'api_url' => $mockApiUrl,
                'is_active' => true,
            ]
        );

        Biller::updateOrCreate(
            ['code' => 'OOREDOO'],
            [
                'name' => 'Ooredoo Myanmar',
                'category' => 'mobile',
                'description' =>
                'Ooredoo Myanmar mobile top-up and bill payment.',
                'api_url' => $mockApiUrl,
                'is_active' => true,
            ]
        );

        Biller::updateOrCreate(
            ['code' => 'YESC'],
            [
                'name' => 'Yangon Electricity Supply Corporation',
                'category' => 'electricity',
                'description' =>
                'Yangon electricity bill payment.',
                'api_url' => $mockApiUrl,
                'is_active' => true,
            ]
        );

        Biller::updateOrCreate(
            ['code' => 'MESC'],
            [
                'name' => 'Mandalay Electricity Supply Corporation',
                'category' => 'electricity',
                'description' =>
                'Mandalay electricity bill payment.',
                'api_url' => $mockApiUrl,
                'is_active' => true,
            ]
        );

        Biller::updateOrCreate(
            ['code' => 'YPS'],
            [
                'name' => 'Yangon Public Bus',
                'category' => 'transport',
                'description' =>
                'Mock public transport payment service.',
                'api_url' => $mockApiUrl,
                'is_active' => true,
            ]
        );

        Biller::updateOrCreate(
            ['code' => 'CANALPLUS'],
            [
                'name' => 'CANAL+ Myanmar',
                'category' => 'entertainment',
                'description' =>
                'CANAL+ subscription and bill payment.',
                'api_url' => $mockApiUrl,
                'is_active' => true,
            ]
        );

        Biller::updateOrCreate(
            ['code' => 'INTERNET'],
            [
                'name' => 'Internet Service',
                'category' => 'internet',
                'description' =>
                'Mock internet service provider bill payment.',
                'api_url' => $mockApiUrl,
                'is_active' => true,
            ]
        );
    }
}
