<?php

namespace App\Filament\Pages;

use App\Models\BusinessHour;
use App\Models\Setting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class SettingsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings-page';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public array $data = [];

    public array $businessHours = [];

    public function mount(): void
    {
        $this->form->fill([
            'restaurant_name' => Setting::getValue('restaurant_name', 'My Restaurant'),
            'restaurant_address' => Setting::getValue('restaurant_address', ''),
            'restaurant_phone' => Setting::getValue('restaurant_phone', ''),
            'restaurant_email' => Setting::getValue('restaurant_email', ''),
            'currency' => Setting::getValue('currency', 'USD'),
            'tax_rate' => Setting::getValue('tax_rate', '0'),
            'tax_label' => Setting::getValue('tax_label', 'VAT'),
            'tax_inclusive' => Setting::getValue('tax_inclusive', true),
            'receipt_footer' => Setting::getValue('receipt_footer', ''),
            'receipt_show_logo' => Setting::getValue('receipt_show_logo', true),
            'receipt_show_qr' => Setting::getValue('receipt_show_qr', false),
            'timezone' => Setting::getValue('timezone', 'UTC'),
            'date_format' => Setting::getValue('date_format', 'M j, Y'),
            'order_prefix' => Setting::getValue('order_prefix', 'POS'),
            'locale' => Setting::getValue('locale', 'en'),
        ]);

        $this->businessHours = BusinessHour::orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")->get()->toArray();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Restaurant Settings')->schema([
                TextInput::make('restaurant_name')->label('Restaurant Name'),
                Textarea::make('restaurant_address')->label('Address')->rows(2),
                TextInput::make('restaurant_phone')->label('Phone'),
                TextInput::make('restaurant_email')->label('Email'),
                Select::make('currency')->label('Currency')->options(function () {
                    $world = ['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)', 'JPY' => 'JPY (¥)', 'CAD' => 'CAD (C$)', 'AUD' => 'AUD (A$)', 'CHF' => 'CHF (Fr)', 'CNY' => 'CNY (¥)', 'INR' => 'INR (₹)', 'BRL' => 'BRL (R$)', 'MXN' => 'MXN (Mex$)'];
                    $africa = [
                        'NGN' => 'NGN (₦) - Nigeria', 'ZAR' => 'ZAR (R) - South Africa', 'EGP' => 'EGP (E£) - Egypt',
                        'KES' => 'KES (KSh) - Kenya', 'GHS' => 'GHS (GH₵) - Ghana', 'TZS' => 'TZS (TSh) - Tanzania',
                        'UGX' => 'UGX (USh) - Uganda', 'MAD' => 'MAD (DH) - Morocco', 'DZD' => 'DZD (DA) - Algeria',
                        'XAF' => 'XAF (FCFA) - Central Africa', 'XOF' => 'XOF (CFA) - West Africa',
                        'ETB' => 'ETB (Br) - Ethiopia', 'AOA' => 'AOA (Kz) - Angola', 'MZN' => 'MZN (MT) - Mozambique',
                        'ZMW' => 'ZMW (ZK) - Zambia', 'RWF' => 'RWF (FRw) - Rwanda', 'TND' => 'TND (DT) - Tunisia',
                        'SDG' => 'SDG (SDG) - Sudan', 'LYD' => 'LYD (LD) - Libya', 'BWP' => 'BWP (P) - Botswana',
                        'NAD' => 'NAD (N$) - Namibia', 'MWK' => 'MWK (MK) - Malawi', 'MUR' => 'MUR (Rs) - Mauritius',
                        'GMD' => 'GMD (D) - Gambia', 'CDF' => 'CDF (FC) - DR Congo', 'MGA' => 'MGA (Ar) - Madagascar',
                        'GNF' => 'GNF (FG) - Guinea', 'SOS' => 'SOS (Sh) - Somalia', 'BIF' => 'BIF (FBu) - Burundi',
                        'SCR' => 'SCR (SR) - Seychelles', 'SZL' => 'SZL (E) - Eswatini', 'LSL' => 'LSL (L) - Lesotho',
                        'CVE' => 'CVE (Esc) - Cape Verde', 'MRU' => 'MRU (UM) - Mauritania', 'DJF' => 'DJF (Fdj) - Djibouti',
                        'KMF' => 'KMF (CF) - Comoros', 'SSP' => 'SSP (£) - South Sudan', 'SLE' => 'SLE (Le) - Sierra Leone',
                        'STN' => 'STN (Db) - São Tomé', 'ERN' => 'ERN (Nfk) - Eritrea',
                    ];
                    return array_merge(['' => '— Select —'], $world, $africa);
                })->searchable(),
            ])->columns(2),
            Section::make('Tax Settings')->schema([
                TextInput::make('tax_rate')->label('Tax Rate (%)')->numeric(),
                TextInput::make('tax_label')->label('Tax Label'),
                Toggle::make('tax_inclusive')->label('Tax inclusive'),
            ])->columns(2),
            Section::make('Receipt Settings')->schema([
                Textarea::make('receipt_footer')->label('Footer Message')->rows(2),
                Toggle::make('receipt_show_logo')->label('Show logo'),
                Toggle::make('receipt_show_qr')->label('Show QR code'),
            ])->columns(2),
            Section::make('General Settings')->schema([
                TextInput::make('timezone')->label('Timezone'),
                TextInput::make('date_format')->label('Date Format'),
                TextInput::make('order_prefix')->label('Order Prefix'),
                Select::make('locale')->label('Language')->options([
                    'en' => 'English', 'fr' => 'Français', 'es' => 'Español', 'de' => 'Deutsch',
                    'pt' => 'Português', 'it' => 'Italiano', 'nl' => 'Nederlands', 'pl' => 'Polski',
                    'ru' => 'Русский', 'zh' => '中文', 'ja' => '日本語', 'ko' => '한국어',
                    'ar' => 'العربية', 'tr' => 'Türkçe',
                ]),
            ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        try {
            $state = $this->form->getState();
            \Illuminate\Support\Facades\Log::info('Settings save', ['state' => $state]);
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
            \Illuminate\Support\Facades\Log::error('Settings save validation error', ['error' => $e->getMessage()]);
            return;
        }

        $localeChanged = false;

        foreach ($state as $key => $value) {
            $group = match (true) {
                in_array($key, ['restaurant_name', 'restaurant_address', 'restaurant_phone', 'restaurant_email', 'currency']) => 'restaurant',
                in_array($key, ['tax_rate', 'tax_label', 'tax_inclusive']) => 'tax',
                in_array($key, ['receipt_footer', 'receipt_show_logo', 'receipt_show_qr']) => 'receipt',
                $key === 'locale' => 'general',
                default => 'general',
            };
            Setting::setValue($key, $value, $group);

            if ($key === 'locale') {
                $localeChanged = true;
                App::setLocale($value);
            }
        }

        // Verify after save
        $check = Setting::getValue('currency', 'FALLBACK');
        \Illuminate\Support\Facades\Log::info('Settings saved, currency re-read', ['currency' => $check]);

        foreach ($this->businessHours as $hours) {
            BusinessHour::where('day_of_week', $hours['day_of_week'])->update([
                'open_time' => $hours['open_time'] ?: null,
                'close_time' => $hours['close_time'] ?: null,
                'is_closed' => $hours['is_closed'] ?? false,
            ]);
        }

        if ($localeChanged) {
            Notification::make()->title(__('Language changed. Reloading...'))->success()->send();
            $this->redirect(request()->url());
            return;
        }

        Notification::make()->title('Settings saved. Currency: ' . $check)->success()->send();
    }

    public function createBackup(): void
    {
        $exitCode = Artisan::call('pos:db-backup');
        if ($exitCode === 0) {
            Notification::make()->title('Backup created successfully.')->success()->send();
        } else {
            Notification::make()->title('Backup failed.')->danger()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('backup')->label('Create Backup')->action('createBackup')->color('gray'),
        ];
    }
}
