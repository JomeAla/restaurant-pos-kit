<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Reports</h1>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach (['sales', 'popular-items', 'profit-margins', 'staff-performance', 'payment-methods', 'peak-hours'] as $t)
            <button wire:click="setTab('{{ $t }}')" class="px-4 py-2 rounded-lg text-sm font-medium capitalize transition-colors {{ $tab === $t ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' }}">
                {{ str_replace('-', ' ', $t) }}
            </button>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
            <input type="date" wire:model.lazy="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 outline-none" />
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
            <input type="date" wire:model.lazy="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 outline-none" />
        </div>
        <button wire:click="loadData" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Refresh</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @php $maxVal = count($reportData) > 0 ? max(array_map(fn($d) => (float) ($d['total'] ?? $d['total_revenue'] ?? $d['total_qty'] ?? $d['total_sales'] ?? $d['order_count'] ?? 0), $reportData), 1) : 1; @endphp

        @if ($tab === 'sales')
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Sales</h3>
            @if (count($reportData) === 0)
                <p class="text-gray-500 text-sm">No sales data</p>
            @else
                <div class="space-y-1">
                    @foreach ($reportData as $d)
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-500 w-24 text-right">{{ $d['period'] }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-6 relative overflow-hidden">
                                <div class="bg-primary-500 h-full rounded-full transition-all" style="width: {{ ((float) $d['total'] / $maxVal) * 100 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700 w-24 text-right">{{ number_format((float) $d['total'], 2) }}</span>
                            <span class="text-xs text-gray-400 w-16">{{ $d['count'] }} orders</span>
                        </div>
                    @endforeach
                </div>
            @endif

        @elseif ($tab === 'popular-items')
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Popular Items</h3>
            @if (count($reportData) === 0)
                <p class="text-gray-500 text-sm">No data</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b text-left"><th class="pb-2 font-medium text-gray-600">Item</th><th class="pb-2 text-right font-medium text-gray-600">Qty Sold</th><th class="pb-2 text-right font-medium text-gray-600">Revenue</th></tr></thead>
                        <tbody>
                            @foreach ($reportData as $item)
                                <tr class="border-b border-gray-50"><td class="py-2 text-gray-800">{{ $item['menu_item']['name'] ?? 'Unknown' }}</td><td class="py-2 text-right font-medium">{{ $item['total_qty'] }}</td><td class="py-2 text-right">{{ number_format((float) $item['total_revenue'], 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        @elseif ($tab === 'profit-margins')
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Profit Margins</h3>
            @if (count($reportData) === 0)
                <p class="text-gray-500 text-sm">No data</p>
            @else
                <div class="space-y-2">
                    @foreach ($reportData as $item)
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-700 w-48 truncate">{{ $item['name'] }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-5 relative overflow-hidden">
                                <div class="h-full rounded-full transition-all {{ $item['margin_percent'] >= 50 ? 'bg-green-500' : ($item['margin_percent'] >= 20 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ max($item['margin_percent'], 0) }}%"></div>
                            </div>
                            <span class="text-sm font-bold w-24 text-right {{ $item['margin_percent'] >= 50 ? 'text-green-600' : ($item['margin_percent'] >= 20 ? 'text-amber-600' : 'text-red-600') }}">{{ $item['margin_percent'] }}%</span>
                            <span class="text-xs text-gray-400 w-20 text-right">{{ number_format($item['profit'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

        @elseif ($tab === 'staff-performance')
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Staff Performance</h3>
            @if (count($reportData) === 0)
                <p class="text-gray-500 text-sm">No data</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b text-left"><th class="pb-2 font-medium text-gray-600">Staff</th><th class="pb-2 text-right font-medium text-gray-600">Orders</th><th class="pb-2 text-right font-medium text-gray-600">Total Sales</th><th class="pb-2 text-right font-medium text-gray-600">Avg Order</th></tr></thead>
                        <tbody>
                            @foreach ($reportData as $s)
                                <tr class="border-b border-gray-50"><td class="py-2 text-gray-800">{{ $s['name'] }}</td><td class="py-2 text-right">{{ $s['orders_taken'] }}</td><td class="py-2 text-right font-medium">{{ number_format($s['total_sales'], 2) }}</td><td class="py-2 text-right">{{ number_format($s['avg_order_value'], 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        @elseif ($tab === 'payment-methods')
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h3>
            @if (count($reportData) === 0)
                <p class="text-gray-500 text-sm">No data</p>
            @else
                <div class="space-y-3">
                    @foreach ($reportData as $m)
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-700 capitalize w-24">{{ $m['method'] }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                                <div class="bg-primary-500 h-full rounded-full transition-all" style="width: {{ ((float) $m['total'] / $maxVal) * 100 }}%"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-700 w-24 text-right">{{ number_format((float) $m['total'], 2) }}</span>
                            <span class="text-xs text-gray-400 w-16 text-right">{{ $m['count'] }} txns</span>
                        </div>
                    @endforeach
                </div>
            @endif

        @elseif ($tab === 'peak-hours')
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Peak Hours</h3>
            @if (count($reportData) === 0)
                <p class="text-gray-500 text-sm">No data</p>
            @else
                @php $peakMax = max(array_map(fn($h) => (int) $h['order_count'], $reportData), 1); @endphp
                <div class="space-y-1">
                    @foreach ($reportData as $h)
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-500 w-16 text-right">{{ $h['hour'] }}:00</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-6 relative overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full transition-all" style="width: {{ ((int) $h['order_count'] / $peakMax) * 100 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700 w-16 text-right">{{ $h['order_count'] }} orders</span>
                            <span class="text-xs text-gray-400 w-20 text-right">{{ number_format((float) ($h['revenue'] ?? 0), 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
