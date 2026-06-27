<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'restaurant_name', 'value' => 'My Restaurant', 'group' => 'restaurant'],
            ['key' => 'restaurant_address', 'value' => '', 'group' => 'restaurant'],
            ['key' => 'restaurant_phone', 'value' => '', 'group' => 'restaurant'],
            ['key' => 'restaurant_email', 'value' => '', 'group' => 'restaurant'],
            ['key' => 'currency', 'value' => 'USD', 'group' => 'restaurant'],
            ['key' => 'tax_rate', 'value' => '0', 'group' => 'tax'],
            ['key' => 'tax_label', 'value' => 'VAT', 'group' => 'tax'],
            ['key' => 'tax_inclusive', 'value' => 'true', 'group' => 'tax'],
            ['key' => 'receipt_footer', 'value' => 'Thank you for your visit!', 'group' => 'receipt'],
            ['key' => 'receipt_show_logo', 'value' => 'true', 'group' => 'receipt'],
            ['key' => 'receipt_show_qr', 'value' => 'false', 'group' => 'receipt'],
            ['key' => 'timezone', 'value' => 'UTC', 'group' => 'general'],
            ['key' => 'date_format', 'value' => 'M j, Y', 'group' => 'general'],
            ['key' => 'order_prefix', 'value' => 'POS', 'group' => 'general'],
            ['key' => 'locale', 'value' => 'en', 'group' => 'general'],
        ];

        foreach ($defaults as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
