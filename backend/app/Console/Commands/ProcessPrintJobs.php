<?php

namespace App\Console\Commands;

use App\Models\PrintJob;
use App\Models\Setting;
use Illuminate\Console\Command;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class ProcessPrintJobs extends Command
{
    protected $signature = 'pos:process-print-jobs';
    protected $description = 'Process pending print jobs using ESC/POS thermal printer';

    public function handle(): int
    {
        $jobs = PrintJob::where('status', 'pending')->where('attempts', '<', 3)->get();

        if ($jobs->isEmpty()) {
            $this->info('No pending print jobs.');
            return Command::SUCCESS;
        }

        $printerIP = Setting::getValue('receipt_printer_ip', '');
        $printerPort = (int) Setting::getValue('receipt_printer_port', '9100');

        if (empty($printerIP)) {
            $this->warn('No printer IP configured. Skipping print jobs.');
            return Command::SUCCESS;
        }

        foreach ($jobs as $job) {
            try {
                $job->update(['attempts' => $job->attempts + 1]);

                if ($job->type === 'receipt') {
                    $this->renderReceipt($job);
                } else {
                    $this->renderKitchenTicket($job);
                }

                if ($job->status === 'sent') {
                    $this->info("Print job #{$job->id} sent.");
                }

            } catch (\Exception $e) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $this->error("Print job #{$job->id} failed: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }

    protected function renderReceipt(PrintJob $job): void
    {
        $order = $job->order;
        if (!$order) return;

        $header = Setting::getValue('receipt_header', '');
        $footer = Setting::getValue('receipt_footer', 'Thank you!');
        $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'NGN' => '₦'];
        $cur = $symbols[Setting::getValue('currency', 'USD')] ?? '$';

        $printer = $this->connect();
        if (!$printer) return;

        try {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            if ($header) { $printer->text($header . "\n"); $printer->feed(); }
            $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
            $printer->text("Order: {$order->order_number}\n");
            $printer->text("Date: " . now()->format('Y-m-d H:i') . "\n");
            $printer->setEmphasis(false);
            $printer->feed();
            $printer->setEmphasis(true);
            $printer->text(str_repeat('-', 32) . "\n");
            $printer->setEmphasis(false);

            foreach ($order->items as $item) {
                $printer->text($item->menuItem?->name . ' x' . $item->quantity . '  ' . $cur . number_format($item->total_price, 2) . "\n");
            }

            $printer->setEmphasis(true);
            $printer->text(str_repeat('-', 32) . "\n");
            $printer->text("Total: {$cur}" . number_format($order->total, 2) . "\n");
            $printer->setEmphasis(false);
            $printer->feed();
            if ($footer) $printer->text($footer . "\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            $job->update(['status' => 'sent']);

        } catch (\Exception $e) {
            $job->update(['error_message' => $e->getMessage(), 'status' => 'failed']);
        }
    }

    protected function renderKitchenTicket(PrintJob $job): void
    {
        $order = $job->order;
        if (!$order) return;

        $printer = $this->connect();
        if (!$printer) return;

        try {
            $printer->setEmphasis(true);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("*** KITCHEN ORDER ***\n");
            $printer->setEmphasis(false);
            $printer->feed();
            $printer->setTextSize(2, 2);
            $printer->text("Order: {$order->order_number}\n");
            $printer->setTextSize(1, 1);
            $printer->text("Table: " . ($order->table?->table_number ?? 'Takeaway') . "\n");
            $printer->feed();
            $printer->text(str_repeat('-', 24) . "\n");

            foreach ($order->items as $item) {
                $course = $item->course ? '[' . ucfirst($item->course) . '] ' : '';
                $printer->setEmphasis(true);
                $printer->text($course . $item->menuItem?->name . ' x' . $item->quantity . "\n");
                $printer->setEmphasis(false);
                if ($item->modifier_summary) {
                    $mods = is_string($item->modifier_summary) ? json_decode($item->modifier_summary, true) : $item->modifier_summary;
                    if ($mods) {
                        foreach ($mods as $m) {
                            $printer->text('  - ' . ($m['option_name'] ?? '') . "\n");
                        }
                    }
                }
            }

            $printer->feed();
            $printer->text(str_repeat('-', 24) . "\n");
            $printer->text(now()->format('H:i') . "\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            $job->update(['status' => 'sent']);

        } catch (\Exception $e) {
            $job->update(['error_message' => $e->getMessage(), 'status' => 'failed']);
        }
    }

    protected function connect(): ?Printer
    {
        $ip = Setting::getValue('receipt_printer_ip', '');
        $port = (int) Setting::getValue('receipt_printer_port', '9100');
        if (empty($ip)) {
            $this->warn('No printer IP configured.');
            return null;
        }
        try {
            $connector = new NetworkPrintConnector($ip, $port, 15);
            return new Printer($connector);
        } catch (\Exception $e) {
            $this->error("Cannot connect to printer at {$ip}:{$port} — {$e->getMessage()}");
            return null;
        }
    }
}
