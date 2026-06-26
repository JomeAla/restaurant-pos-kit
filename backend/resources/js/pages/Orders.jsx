import { useState, useEffect } from 'react';
import client from '../api/client';
import Modal from '../components/Modal';
import PaymentDialog from '../components/PaymentDialog';
import { formatCurrency, setCurrency } from '../utils/currency';

export default function Orders() {
    const [orders, setOrders] = useState([]);
    const [selectedOrder, setSelectedOrder] = useState(null);
    const [filters, setFilters] = useState({ date: '', status: '', type: '', table_id: '' });
    const [voidModal, setVoidModal] = useState(false);
    const [voidItem, setVoidItem] = useState(null);
    const [voidReason, setVoidReason] = useState('');
    const [voiding, setVoiding] = useState(false);
    const [paymentOpen, setPaymentOpen] = useState(false);

    const load = (f = filters) => {
        const params = new URLSearchParams();
        if (f.date) params.set('date', f.date);
        if (f.status) params.set('status', f.status);
        if (f.type) params.set('type', f.type);
        if (f.table_id) params.set('table_id', f.table_id);
        client.get(`/orders?${params.toString()}`).then(({ data }) => setOrders(data.data ?? data));
    };
    useEffect(() => { load(); client.get('/settings').then(({ data }) => { setCurrency(data.restaurant?.currency || 'USD'); }).catch(() => {}); }, []);

    const statusColors = { pending: 'bg-yellow-100 text-yellow-700 border-yellow-200', sent: 'bg-blue-100 text-blue-700 border-blue-200', preparing: 'bg-purple-100 text-purple-700 border-purple-200', ready: 'bg-green-100 text-green-700 border-green-200', served: 'bg-teal-100 text-teal-700 border-teal-200', paid: 'bg-gray-100 text-gray-700 border-gray-200', closed: 'bg-gray-200 text-gray-500 border-gray-300', voided: 'bg-red-100 text-red-700 border-red-200' };

    const openDetail = async (order) => {
        const { data } = await client.get(`/orders/${order.id}`);
        setSelectedOrder(data);
    };

    const handleVoidItem = async () => {
        if (!voidReason.trim()) return;
        setVoiding(true);
        try {
            await client.delete(`/orders/${selectedOrder.id}/items/${voidItem.id}`, { data: { void_reason: voidReason } });
            const { data } = await client.get(`/orders/${selectedOrder.id}`);
            setSelectedOrder(data);
            setVoidModal(false);
            setVoidItem(null);
            setVoidReason('');
        } catch (err) { alert(err.response?.data?.message || 'Error voiding item'); } finally { setVoiding(false); }
    };

    const handleSendKitchen = async () => {
        try {
            await client.post(`/orders/${selectedOrder.id}/send-kitchen`);
            const { data } = await client.get(`/orders/${selectedOrder.id}`);
            setSelectedOrder(data);
            load();
        } catch (err) { alert(err.response?.data?.message || 'Error'); }
    };

    const applyFilter = () => { load(filters); };

    return (
        <div>
            <h2 className="text-2xl font-bold text-gray-800 mb-4">Orders</h2>

            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
                <div className="flex flex-wrap gap-3 items-end">
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">Date</label>
                        <input type="date" value={filters.date} onChange={(e) => setFilters({ ...filters, date: e.target.value })} className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select value={filters.status} onChange={(e) => setFilters({ ...filters, status: e.target.value })} className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">All</option>
                            {['pending', 'sent', 'preparing', 'ready', 'served', 'paid', 'closed', 'voided'].map((s) => <option key={s} value={s}>{s}</option>)}
                        </select>
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-500 mb-1">Type</label>
                        <select value={filters.type} onChange={(e) => setFilters({ ...filters, type: e.target.value })} className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">All</option>
                            <option value="dine-in">Dine-in</option>
                            <option value="takeaway">Takeaway</option>
                            <option value="delivery">Delivery</option>
                        </select>
                    </div>
                    <button onClick={applyFilter} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Filter</button>
                    <button onClick={() => { setFilters({ date: '', status: '', type: '', table_id: '' }); load({ date: '', status: '', type: '', table_id: '' }); }} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Clear</button>
                </div>
            </div>

            {selectedOrder ? (
                <div>
                    <button onClick={() => { setSelectedOrder(null); load(); }} className="text-sm text-indigo-600 hover:text-indigo-800 mb-4 flex items-center gap-1">
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" /></svg>
                        Back to orders
                    </button>
                    <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div className="flex items-start justify-between mb-6">
                            <div>
                                <h3 className="text-2xl font-bold text-gray-800">{selectedOrder.order_number}</h3>
                                <p className="text-sm text-gray-500 mt-1">{new Date(selectedOrder.ordered_at).toLocaleString()} &middot; {selectedOrder.type}</p>
                                <span className={`inline-block mt-2 px-2 py-1 text-xs rounded-full font-medium border ${statusColors[selectedOrder.status] || 'bg-gray-100 text-gray-600'}`}>{selectedOrder.status}</span>
                            </div>
                            <div className="text-right">
                                {selectedOrder.table && <p className="text-sm text-gray-600">Table #{selectedOrder.table.table_number}</p>}
                                <p className="text-sm text-gray-500">Staff: {selectedOrder.user?.name}</p>
                            </div>
                        </div>

                        <div className="flex gap-2 mb-4">
                            {selectedOrder.status === 'pending' && (
                                <button onClick={handleSendKitchen} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Send to Kitchen</button>
                            )}
                            {['ready', 'served'].includes(selectedOrder.status) && (
                                <button onClick={() => setPaymentOpen(true)} className="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Take Payment</button>
                            )}
                        </div>

                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-100">
                                    <th className="text-left p-3 font-medium text-gray-600">Item</th>
                                    <th className="text-left p-3 font-medium text-gray-600">Course</th>
                                    <th className="text-center p-3 font-medium text-gray-600">Qty</th>
                                    <th className="text-right p-3 font-medium text-gray-600">Price</th>
                                    <th className="text-right p-3 font-medium text-gray-600">Status</th>
                                    <th className="text-right p-3 font-medium text-gray-600"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {selectedOrder.items?.map((item) => (
                                    <tr key={item.id} className="border-b border-gray-50 hover:bg-gray-50">
                                        <td className="p-3">
                                            <p className="font-medium text-gray-800">{item.menu_item?.name || 'Unknown'}</p>
                                            {item.modifier_summary && <p className="text-xs text-gray-500">{item.modifier_summary.map((m) => m.option_name).join(', ')}</p>}
                                            {item.notes && <p className="text-xs text-gray-400 italic">{item.notes}</p>}
                                        </td>
                                        <td className="p-3">
                                            {item.course ? <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">{item.course}</span> : '-'}
                                        </td>
                                        <td className="p-3 text-center text-gray-800">{item.quantity}</td>
                                        <td className="p-3 text-right text-gray-800 font-medium">{formatCurrency(item.total_price)}</td>
                                        <td className="p-3 text-right">
                                            {item.status === 'voided' ? <span className="text-xs text-red-600 font-medium">Voided</span> : <span className="text-xs text-gray-500">{item.status}</span>}
                                        </td>
                                        <td className="p-3 text-right">
                                            {item.status !== 'voided' && !['paid', 'closed', 'voided'].includes(selectedOrder.status) && (
                                                <button onClick={() => { setVoidItem(item); setVoidReason(''); setVoidModal(true); }} className="text-xs text-red-500 hover:text-red-700">Void</button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colSpan={3} className="p-3 text-right font-medium text-gray-600">Subtotal</td>
                                    <td className="p-3 text-right font-bold text-gray-800">{formatCurrency(selectedOrder.subtotal)}</td>
                                    <td></td><td></td>
                                </tr>
                                {parseFloat(selectedOrder.tax_total) > 0 && (
                                    <tr><td colSpan={3} className="p-3 text-right text-gray-600">Tax</td><td className="p-3 text-right text-gray-800">{formatCurrency(selectedOrder.tax_total)}</td><td></td><td></td></tr>
                                )}
                                <tr className="border-t border-gray-200">
                                    <td colSpan={3} className="p-3 text-right font-semibold text-gray-800">Total</td>
                                    <td className="p-3 text-right font-bold text-lg text-indigo-600">{formatCurrency(selectedOrder.total)}</td>
                                    <td></td><td></td>
                                </tr>
                            </tfoot>
                        </table>

                        {selectedOrder.notes && (
                            <div className="mt-4 p-3 bg-gray-50 rounded-lg">
                                <p className="text-xs font-medium text-gray-500 mb-1">Notes</p>
                                <p className="text-sm text-gray-700">{selectedOrder.notes}</p>
                            </div>
                        )}
                    </div>
                </div>
            ) : (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 bg-gray-50">
                                <th className="text-left p-4 font-medium text-gray-600">Order #</th>
                                <th className="text-left p-4 font-medium text-gray-600">Type</th>
                                <th className="text-left p-4 font-medium text-gray-600">Table</th>
                                <th className="text-left p-4 font-medium text-gray-600">Status</th>
                                <th className="text-right p-4 font-medium text-gray-600">Total</th>
                                <th className="text-right p-4 font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            {orders.length === 0 ? (
                                <tr><td colSpan={6} className="p-4 text-gray-500 text-center">No orders found</td></tr>
                            ) : orders.map((order) => (
                                <tr key={order.id} onClick={() => openDetail(order)} className="border-b border-gray-50 hover:bg-gray-50 cursor-pointer">
                                    <td className="p-4 font-medium text-gray-800">{order.order_number}</td>
                                    <td className="p-4 text-gray-500 capitalize">{order.type}</td>
                                    <td className="p-4 text-gray-500">{order.table ? `#${order.table.table_number}` : '-'}</td>
                                    <td className="p-4">
                                        <span className={`px-2 py-1 text-xs rounded-full font-medium border ${statusColors[order.status] || 'bg-gray-100 text-gray-600'}`}>{order.status}</span>
                                    </td>
                                    <td className="p-4 text-right font-medium text-gray-800">{formatCurrency(order.total)}</td>
                                    <td className="p-4 text-right text-gray-500 text-xs">{new Date(order.ordered_at).toLocaleString()}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            <Modal open={paymentOpen} onClose={() => setPaymentOpen(false)} title="">
                <PaymentDialog order={selectedOrder} onClose={() => setPaymentOpen(false)} onPaid={() => { setPaymentOpen(false); setSelectedOrder(null); load(); }} />
            </Modal>

            <Modal open={voidModal} onClose={() => setVoidModal(false)} title="Void Item">
                <p className="text-sm text-gray-600 mb-4">Voiding: <strong>{voidItem?.menu_item?.name || 'Unknown'}</strong></p>
                <label className="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                <select value={voidReason} onChange={(e) => setVoidReason(e.target.value)} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none mb-4">
                    <option value="">Select reason...</option>
                    <option value="Customer request">Customer request</option>
                    <option value="Wrong item">Wrong item</option>
                    <option value="Kitchen error">Kitchen error</option>
                    <option value="Quality issue">Quality issue</option>
                    <option value="Other">Other</option>
                </select>
                <div className="flex justify-end gap-3">
                    <button onClick={() => setVoidModal(false)} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                    <button onClick={handleVoidItem} disabled={!voidReason || voiding} className="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50">
                        {voiding ? 'Voiding...' : 'Void Item'}
                    </button>
                </div>
            </Modal>
        </div>
    );
}
