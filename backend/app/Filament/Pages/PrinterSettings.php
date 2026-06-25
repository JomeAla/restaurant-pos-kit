<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PrinterSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-printer';

    protected static string $view = 'filament.pages.printer-settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    public string $receipt_printer_ip = '';
    public string $receipt_printer_port = '9100';
    public bool $auto_print_receipt = false;
    public bool $auto_print_kitchen = true;
    public string $receipt_header = '';
    public string $receipt_footer = 'Thank you for your visit!';

    public function mount(): void
    {
        $this->receipt_printer_ip = Setting::getValue('receipt_printer_ip', '');
        $this->receipt_printer_port = Setting::getValue('receipt_printer_port', '9100');
        $this->auto_print_receipt = (bool) Setting::getValue('auto_print_receipt', false);
        $this->auto_print_kitchen = (bool) Setting::getValue('auto_print_kitchen', true);
        $this->receipt_header = Setting::getValue('receipt_header', '');
        $this->receipt_footer = Setting::getValue('receipt_footer', 'Thank you for your visit!');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Printer Connection')->schema([
                TextInput::make('receipt_printer_ip')->label('Printer IP Address')->placeholder('192.168.1.100'),
                TextInput::make('receipt_printer_port')->label('Printer Port')->numeric()->default(9100),
            ])->columns(2),
            Section::make('Auto-Print Behavior')->schema([
                Toggle::make('auto_print_receipt')->label('Auto-print receipt on payment'),
                Toggle::make('auto_print_kitchen')->label('Auto-print kitchen ticket on send to kitchen'),
            ])->columns(2),
            Section::make('Receipt Customization')->schema([
                TextInput::make('receipt_header')->label('Receipt Header')->placeholder('Restaurant Name'),
                TextInput::make('receipt_footer')->label('Receipt Footer'),
            ])->columns(2),
        ]);
    }

    public function save(): void
    {
        Setting::setValue('receipt_printer_ip', $this->receipt_printer_ip);
        Setting::setValue('receipt_printer_port', $this->receipt_printer_port);
        Setting::setValue('auto_print_receipt', $this->auto_print_receipt ? '1' : '0');
        Setting::setValue('auto_print_kitchen', $this->auto_print_kitchen ? '1' : '0');
        Setting::setValue('receipt_header', $this->receipt_header);
        Setting::setValue('receipt_footer', $this->receipt_footer);

        Notification::make()->title('Printer settings saved.')->success()->send();
    }
}
