import { useState, useEffect } from 'react';
import client from '../api/client';

const TABS = ['restaurant', 'tax', 'receipt', 'general'];

export default function Settings() {
    const [tab, setTab] = useState('restaurant');
    const [settings, setSettings] = useState({});
    const [form, setForm] = useState({});
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState(false);

    useEffect(() => {
        client.get('/settings').then(({ data }) => {
            setSettings(data);
            const flat = {};
            Object.values(data).forEach((group) => {
                Object.entries(group).forEach(([key, val]) => { flat[key] = val; });
            });
            setForm({
                restaurant_name: flat.restaurant_name || 'My Restaurant',
                restaurant_address: flat.restaurant_address || '',
                restaurant_phone: flat.restaurant_phone || '',
                restaurant_email: flat.restaurant_email || '',
                currency: flat.currency || 'USD',
                tax_rate: flat.tax_rate || '0',
                tax_label: flat.tax_label || 'VAT',
                tax_inclusive: flat.tax_inclusive || 'true',
                receipt_footer: flat.receipt_footer || 'Thank you for your visit!',
                receipt_show_logo: flat.receipt_show_logo || 'true',
                receipt_show_qr: flat.receipt_show_qr || 'false',
                timezone: flat.timezone || 'UTC',
                date_format: flat.date_format || 'M j, Y',
                order_prefix: flat.order_prefix || 'POS',
            });
        });
    }, []);

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            const groupMap = {
                restaurant: ['restaurant_name', 'restaurant_address', 'restaurant_phone', 'restaurant_email', 'currency'],
                tax: ['tax_rate', 'tax_label', 'tax_inclusive'],
                receipt: ['receipt_footer', 'receipt_show_logo', 'receipt_show_qr'],
                general: ['timezone', 'date_format', 'order_prefix'],
            };
            const keys = groupMap[tab] || [];
            const payload = keys.map((key) => ({ key, value: form[key], group: tab }));
            await client.put('/settings', { settings: payload });
            setSaved(true);
            setTimeout(() => setSaved(false), 2000);
        } catch (err) { alert('Error saving settings'); } finally { setSaving(false); }
    };

    return (
        <div>
            <h2 className="text-2xl font-bold text-gray-800 mb-4">Settings</h2>

            <div className="flex gap-2 mb-4 flex-wrap">
                {TABS.map((t) => (
                    <button key={t} onClick={() => setTab(t)} className={`px-4 py-2 rounded-lg text-sm font-medium capitalize transition-colors ${tab === t ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'}`}>{t}</button>
                ))}
            </div>

            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form onSubmit={handleSave} className="space-y-4 max-w-lg">
                    {tab === 'restaurant' && (
                        <>
                            <div><label className="block text-sm font-medium text-gray-700 mb-1">Restaurant Name</label><input value={form.restaurant_name} onChange={(e) => setForm({ ...form, restaurant_name: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                            <div><label className="block text-sm font-medium text-gray-700 mb-1">Address</label><textarea value={form.restaurant_address} onChange={(e) => setForm({ ...form, restaurant_address: e.target.value })} rows={2} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Phone</label><input value={form.restaurant_phone} onChange={(e) => setForm({ ...form, restaurant_phone: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" value={form.restaurant_email} onChange={(e) => setForm({ ...form, restaurant_email: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                            </div>
                            <div><label className="block text-sm font-medium text-gray-700 mb-1">Currency</label><select value={form.currency} onChange={(e) => setForm({ ...form, currency: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"><option value="USD">USD ($)</option><option value="EUR">EUR (€)</option><option value="GBP">GBP (£)</option><option value="NGN">NGN (₦)</option></select></div>
                        </>
                    )}

                    {tab === 'tax' && (
                        <>
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label><input type="number" step="0.01" min="0" max="100" value={form.tax_rate} onChange={(e) => setForm({ ...form, tax_rate: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Tax Label</label><input value={form.tax_label} onChange={(e) => setForm({ ...form, tax_label: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                            </div>
                            <label className="flex items-center gap-2 cursor-pointer"><input type="checkbox" checked={form.tax_inclusive === 'true'} onChange={(e) => setForm({ ...form, tax_inclusive: e.target.checked ? 'true' : 'false' })} className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" /><span className="text-sm text-gray-700">Tax inclusive (prices include tax)</span></label>
                        </>
                    )}

                    {tab === 'receipt' && (
                        <>
                            <div><label className="block text-sm font-medium text-gray-700 mb-1">Footer Message</label><textarea value={form.receipt_footer} onChange={(e) => setForm({ ...form, receipt_footer: e.target.value })} rows={3} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                            <label className="flex items-center gap-2 cursor-pointer"><input type="checkbox" checked={form.receipt_show_logo === 'true'} onChange={(e) => setForm({ ...form, receipt_show_logo: e.target.checked ? 'true' : 'false' })} className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" /><span className="text-sm text-gray-700">Show logo on receipts</span></label>
                            <label className="flex items-center gap-2 cursor-pointer"><input type="checkbox" checked={form.receipt_show_qr === 'true'} onChange={(e) => setForm({ ...form, receipt_show_qr: e.target.checked ? 'true' : 'false' })} className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" /><span className="text-sm text-gray-700">Show QR code on receipts</span></label>
                        </>
                    )}

                    {tab === 'general' && (
                        <>
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Timezone</label><input value={form.timezone} onChange={(e) => setForm({ ...form, timezone: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Date Format</label><input value={form.date_format} onChange={(e) => setForm({ ...form, date_format: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                            </div>
                            <div><label className="block text-sm font-medium text-gray-700 mb-1">Order Number Prefix</label><input value={form.order_prefix} onChange={(e) => setForm({ ...form, order_prefix: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                        </>
                    )}

                    <div className="flex items-center gap-3 pt-2">
                        <button type="submit" disabled={saving} className="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">{saving ? 'Saving...' : 'Save Settings'}</button>
                        {saved && <span className="text-sm text-green-600 font-medium">Saved!</span>}
                    </div>
                </form>
            </div>
        </div>
    );
}
