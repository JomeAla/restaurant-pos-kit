import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import client from '../api/client';
import { formatCurrency, setCurrency } from '../utils/currency';

export default function Dashboard() {
    const navigate = useNavigate();
    const [stats, setStats] = useState({ ordersToday: 0, revenue: 0, activeTables: 0, staffOnline: 0 });
    const [recentOrders, setRecentOrders] = useState([]);
    const [paymentsToday, setPaymentsToday] = useState([]);
    const [methods, setMethods] = useState({});

    useEffect(() => {
        const today = new Date().toISOString().split('T')[0];

        client.get(`/orders?date=${today}&per_page=5`).then(({ data }) => {
            const orders = data.data ?? data;
            setRecentOrders(orders);
            const total = orders.reduce((s, o) => s + parseFloat(o.total || 0), 0);
            setStats((prev) => ({ ...prev, ordersToday: orders.filter((o) => o.status !== 'voided').length, revenue: total }));
        }).catch(() => {});

        client.get('/tables').then(({ data }) => {
            const tables = data;
            setStats((prev) => ({ ...prev, activeTables: tables.filter((t) => t.status === 'occupied').length }));
        }).catch(() => {});

        client.get('/users?per_page=100').then(({ data }) => {
            const users = data.data ?? data;
            setStats((prev) => ({ ...prev, staffOnline: Array.isArray(users) ? users.length : 0 }));
        }).catch(() => {});

        client.get('/settings').then(({ data }) => {
            const currency = data.restaurant?.currency || 'USD';
            setCurrency(currency);
        }).catch(() => {});
    }, []);

    const statusColors = { pending: 'bg-yellow-100 text-yellow-700', sent: 'bg-blue-100 text-blue-700', preparing: 'bg-purple-100 text-purple-700', ready: 'bg-green-100 text-green-700', served: 'bg-teal-100 text-teal-700', paid: 'bg-gray-100 text-gray-700', closed: 'bg-gray-200 text-gray-500', voided: 'bg-red-100 text-red-700' };

    return (
        <div>
            <h2 className="text-2xl font-bold text-gray-800 mb-6">Dashboard</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {[
                    { label: 'Orders Today', value: stats.ordersToday.toString(), color: 'bg-blue-500' },
                    { label: 'Revenue Today', value: formatCurrency(stats.revenue), color: 'bg-green-500' },
                    { label: 'Active Tables', value: stats.activeTables.toString(), color: 'bg-amber-500' },
                    { label: 'Staff Accounts', value: stats.staffOnline.toString(), color: 'bg-purple-500' },
                ].map((card) => (
                    <div key={card.label} className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div className={`w-3 h-3 rounded-full ${card.color} mb-3`} />
                        <p className="text-sm text-gray-500">{card.label}</p>
                        <p className="text-2xl font-bold text-gray-800 mt-1">{card.value}</p>
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 className="text-lg font-semibold text-gray-800 mb-4">Recent Orders</h3>
                    {recentOrders.length === 0 ? (
                        <p className="text-gray-500 text-sm">No orders today. Start by creating an order from the POS terminal.</p>
                    ) : (
                        <div className="space-y-2">
                            {recentOrders.map((order) => (
                                <div key={order.id} onClick={() => navigate('/orders')} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                    <div>
                                        <p className="text-sm font-medium text-gray-800">{order.order_number}</p>
                                        <p className="text-xs text-gray-500">{order.type} {order.table ? `· Table #${order.table.table_number}` : ''}</p>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-sm font-bold text-gray-800">{formatCurrency(order.total)}</p>
                                        <span className={`inline-block px-2 py-0.5 text-xs rounded-full font-medium ${statusColors[order.status] || 'bg-gray-100 text-gray-600'}`}>{order.status}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 className="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    <div className="grid grid-cols-2 gap-3">
                        <button onClick={() => navigate('/pos')} className="p-4 bg-indigo-50 rounded-xl text-left hover:bg-indigo-100 transition-colors">
                            <span className="text-2xl">🛒</span>
                            <p className="text-sm font-medium text-gray-800 mt-2">New Order</p>
                            <p className="text-xs text-gray-500">Create a POS order</p>
                        </button>
                        <button onClick={() => navigate('/kds')} className="p-4 bg-green-50 rounded-xl text-left hover:bg-green-100 transition-colors">
                            <span className="text-2xl">👨‍🍳</span>
                            <p className="text-sm font-medium text-gray-800 mt-2">Kitchen</p>
                            <p className="text-xs text-gray-500">View KDS</p>
                        </button>
                        <button onClick={() => navigate('/tables')} className="p-4 bg-amber-50 rounded-xl text-left hover:bg-amber-100 transition-colors">
                            <span className="text-2xl">🪑</span>
                            <p className="text-sm font-medium text-gray-800 mt-2">Tables</p>
                            <p className="text-xs text-gray-500">Floor plan view</p>
                        </button>
                        <button onClick={() => navigate('/orders')} className="p-4 bg-purple-50 rounded-xl text-left hover:bg-purple-100 transition-colors">
                            <span className="text-2xl">📋</span>
                            <p className="text-sm font-medium text-gray-800 mt-2">All Orders</p>
                            <p className="text-xs text-gray-500">View & manage</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
