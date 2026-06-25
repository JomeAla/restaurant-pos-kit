<?php

namespace App\Filament\Pages;

use App\Models\PaymentGateway;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class PaymentGatewaySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'filament.pages.payment-gateway-settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    public ?array $paystackData = [];
    public ?array $stripeData = [];

    public function mount(): void
    {
        $paystackGateway = PaymentGateway::where('gateway', 'paystack')->first();
        $stripeGateway = PaymentGateway::where('gateway', 'stripe')->first();

        $this->paystackData = [
            'label' => $paystackGateway?->label ?? 'Paystack',
            'public_key' => $paystackGateway?->decrypted_credentials['public_key'] ?? '',
            'secret_key' => $paystackGateway?->decrypted_credentials['secret_key'] ?? '',
            'is_sandbox' => $paystackGateway?->is_sandbox ?? true,
            'is_active' => $paystackGateway?->is_active ?? false,
            'webhook_url' => $paystackGateway?->webhook_url ?? url('/api/v1/webhooks/paystack'),
        ];

        $this->stripeData = [
            'label' => $stripeGateway?->label ?? 'Stripe',
            'publishable_key' => $stripeGateway?->decrypted_credentials['publishable_key'] ?? '',
            'secret_key' => $stripeGateway?->decrypted_credentials['secret_key'] ?? '',
            'is_sandbox' => $stripeGateway?->is_sandbox ?? true,
            'is_active' => $stripeGateway?->is_active ?? false,
            'webhook_url' => $stripeGateway?->webhook_url ?? url('/api/v1/webhooks/stripe'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Paystack Configuration')->schema([
                    TextInput::make('paystackData.label')->label('Label'),
                    TextInput::make('paystackData.public_key')->label('Public Key'),
                    TextInput::make('paystackData.secret_key')->label('Secret Key')->password(),
                    Toggle::make('paystackData.is_sandbox')->label('Sandbox Mode'),
                    Toggle::make('paystackData.is_active')->label('Active'),
                    TextInput::make('paystackData.webhook_url')->label('Webhook URL')->disabled(),
                ])->columns(2),
                Section::make('Stripe Configuration')->schema([
                    TextInput::make('stripeData.label')->label('Label'),
                    TextInput::make('stripeData.publishable_key')->label('Publishable Key'),
                    TextInput::make('stripeData.secret_key')->label('Secret Key')->password(),
                    Toggle::make('stripeData.is_sandbox')->label('Sandbox Mode'),
                    Toggle::make('stripeData.is_active')->label('Active'),
                    TextInput::make('stripeData.webhook_url')->label('Webhook URL')->disabled(),
                ])->columns(2),
            ]);
    }

    public function save(): void
    {
        foreach (['paystackData' => 'paystack', 'stripeData' => 'stripe'] as $prop => $gateway) {
            $config = $this->$prop;
            $record = PaymentGateway::updateOrCreate(
                ['gateway' => $gateway],
                [
                    'label' => $config['label'] ?? Str::title($gateway),
                    'is_sandbox' => $config['is_sandbox'] ?? true,
                    'is_active' => $config['is_active'] ?? false,
                    'webhook_url' => $gateway === 'paystack'
                        ? url('/api/v1/webhooks/paystack')
                        : url('/api/v1/webhooks/stripe'),
                ]
            );

            $keyField = $gateway === 'paystack' ? 'public_key' : 'publishable_key';
            $record->decrypted_credentials = array_filter([
                $keyField => $config[$keyField] ?? '',
                'secret_key' => $config['secret_key'] ?? '',
            ]);
            $record->save();
        }

        Notification::make()->title('Payment gateway settings saved.')->success()->send();
    }
}
