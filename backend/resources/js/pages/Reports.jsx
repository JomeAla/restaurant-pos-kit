import { useState, useEffect } from 'react';
import client from '../api/client';
import { formatCurrency, setCurrency } from '../utils/currency';

const TABS = ['sales', 'popular-items', 'profit-margins', 'staff-performance', 'payment-methods', 'peak-hours'];

export default function Reports() {
    const [tab, setTab] = useState('sales');
    const [dateFrom, setDateFrom] = useState(() => { const d = new Date(); d.setDate(d.getDate() - 30); return d.toISOString().split('T')[0]; });
    const [dateTo, setDateTo] = useState(() => new Date().toISOString().split('T')[0]);
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);

    const load = () => {
        setLoading(true);
        const params = new URLSearchParams({ date_from: dateFrom, date_to: dateTo });
        const endpoints = {
            'sales': `/reports/sales?${params}&group_by=day`,
            'popular-items': `/reports/popular-items?${params}`,
            'profit-margins': '/reports/profit-margins',
            'staff-performance': `/reports/staff-performance?${params}`,
            'payment-methods': `/reports/payment-methods?${params}`,
            'peak-hours': `/reports/peak-hours?${params}`,
        };
        client.get(endpoints[tab]).then(({ data: res }) => setData(res)).catch(() => setData([])).finally(() => setLoading(false));
    };
    useEffect(() => { load(); client.get('/settings').then(({ data }) => { setCurrency(data.restaurant?.[0]?.value || 'USD'); }).catch(() => {}); }, [tab]);

    const maxVal = Array.isArray(data) ? Math.max(...data.map((d) => parseFloat(d.total || d.total_revenue || d.total_qty || d.total_sales || d.order_count || 0)), 1) : 1;

    return (
        <div>
            <h2 className="text-2xl font-bold text-gray-800 mb-4">Reports</h2>

            <div className="flex flex-wrap gap-2 mb-4">
                {TABS.map((t) => (
                    <button key={t} onClick={() => setTab(t)} className={`px-4 py-2 rounded-lg text-sm font-medium capitalize transition-colors ${tab === t ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'}`}>{t.replace('-', ' ')}</button>
                ))}
            </div>

            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label className="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                </div>
                <div>
                    <label className="block text-xs font-medium text-gray-500 mb-1">To</label>
                    <input type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                </div>
                <button onClick={load} disabled={loading} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">{loading ? 'Loading...' : 'Refresh'}</button>
            </div>

            {loading ? (
                <div className="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">Loading...</div>
            ) : (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    {tab === 'sales' && (
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">Daily Sales</h3>
                            {data.length === 0 ? <p className="text-gray-500 text-sm">No sales data</p> : (
                                <div className="space-y-1">
                                    {data.map((d, i) => (
                                        <div key={i} className="flex items-center gap-3">
                                            <span className="text-xs text-gray-500 w-24 text-right">{d.period}</span>
                                            <div className="flex-1 bg-gray-100 rounded-full h-6 relative overflow-hidden">
                                                <div className="bg-indigo-500 h-full rounded-full transition-all" style={{ width: `${(parseFloat(d.total) / maxVal) * 100}%` }} />
                                            </div>
                                            <span className="text-sm font-medium text-gray-700 w-24">{formatCurrency(d.total)}</span>
                                            <span className="text-xs text-gray-400 w-16">{d.count} orders</span>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    {tab === 'popular-items' && (
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">Popular Items</h3>
                            {data.length === 0 ? <p className="text-gray-500 text-sm">No data</p> : (
                                <table className="w-full text-sm">
                                    <thead><tr className="border-b text-left"><th className="pb-2 font-medium text-gray-600">Item</th><th className="pb-2 text-right font-medium text-gray-600">Qty Sold</th><th className="pb-2 text-right font-medium text-gray-600">Revenue</th></tr></thead>
                                    <tbody>
                                        {data.map((item, i) => (
                                            <tr key={i} className="border-b border-gray-50"><td className="py-2 text-gray-800">{item.menu_item?.name || 'Unknown'}</td><td className="py-2 text-right font-medium">{item.total_qty}</td><td className="py-2 text-right">{formatCurrency(item.total_revenue)}</td></tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    )}

                    {tab === 'profit-margins' && (
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">Profit Margins</h3>
                            {data.length === 0 ? <p className="text-gray-500 text-sm">No data</p> : (
                                <div className="space-y-2">
                                    {data.map((item, i) => (
                                        <div key={i} className="flex items-center gap-3">
                                            <span className="text-sm text-gray-700 w-48 truncate">{item.name}</span>
                                            <div className="flex-1 bg-gray-100 rounded-full h-5 relative overflow-hidden">
                                                <div className={`h-full rounded-full transition-all ${item.margin_percent >= 50 ? 'bg-green-500' : item.margin_percent >= 20 ? 'bg-amber-500' : 'bg-red-500'}`} style={{ width: `${Math.max(item.margin_percent, 0)}%` }} />
                                            </div>
                                            <span className={`text-sm font-bold w-24 text-right ${item.margin_percent >= 50 ? 'text-green-600' : item.margin_percent >= 20 ? 'text-amber-600' : 'text-red-600'}`}>{item.margin_percent}%</span>
                                            <span className="text-xs text-gray-400 w-20 text-right">{formatCurrency(item.profit)}</span>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    {tab === 'staff-performance' && (
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">Staff Performance</h3>
                            {data.length === 0 ? <p className="text-gray-500 text-sm">No data</p> : (
                                <table className="w-full text-sm">
                                    <thead><tr className="border-b text-left"><th className="pb-2 font-medium text-gray-600">Staff</th><th className="pb-2 text-right font-medium text-gray-600">Orders</th><th className="pb-2 text-right font-medium text-gray-600">Total Sales</th><th className="pb-2 text-right font-medium text-gray-600">Avg Order</th></tr></thead>
                                    <tbody>
                                        {data.map((s, i) => (
                                            <tr key={i} className="border-b border-gray-50"><td className="py-2 text-gray-800">{s.name}</td><td className="py-2 text-right">{s.orders_taken}</td><td className="py-2 text-right font-medium">{formatCurrency(s.total_sales)}</td><td className="py-2 text-right">{formatCurrency(s.avg_order_value)}</td></tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    )}

                    {tab === 'payment-methods' && (
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h3>
                            {data.length === 0 ? <p className="text-gray-500 text-sm">No data</p> : (
                                <div className="space-y-3">
                                    {data.map((m, i) => (
                                        <div key={i} className="flex items-center gap-3">
                                            <span className="text-sm font-medium text-gray-700 capitalize w-24">{m.method}</span>
                                            <div className="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                                                <div className="bg-indigo-500 h-full rounded-full transition-all" style={{ width: `${(parseFloat(m.total) / maxVal) * 100}%` }} />
                                            </div>
                                            <span className="text-sm font-bold text-gray-700 w-24 text-right">{formatCurrency(m.total)}</span>
                                            <span className="text-xs text-gray-400 w-16 text-right">{m.count} txns</span>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    {tab === 'peak-hours' && (
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">Peak Hours</h3>
                            {data.length === 0 ? <p className="text-gray-500 text-sm">No data</p> : (
                                <div className="space-y-1">
                                    {data.map((h, i) => (
                                        <div key={i} className="flex items-center gap-3">
                                            <span className="text-xs text-gray-500 w-16 text-right">{h.hour}:00</span>
                                            <div className="flex-1 bg-gray-100 rounded-full h-6 relative overflow-hidden">
                                                <div className="bg-amber-500 h-full rounded-full transition-all" style={{ width: `${(parseInt(h.order_count) / Math.max(...data.map((x) => parseInt(x.order_count)))) * 100}%` }} />
                                            </div>
                                            <span className="text-sm font-medium text-gray-700 w-16 text-right">{h.order_count} orders</span>
                                            <span className="text-xs text-gray-400 w-20 text-right">{formatCurrency(h.revenue)}</span>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
