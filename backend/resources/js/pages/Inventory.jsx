import { useState, useEffect } from 'react';
import client from '../api/client';
import { formatCurrency, setCurrency } from '../utils/currency';

const TABS = ['items', 'transactions', 'purchase-orders', 'recipes'];

export default function Inventory() {
    const [tab, setTab] = useState('items');
    const [items, setItems] = useState([]);
    const [menuItems, setMenuItems] = useState([]);
    const [recipes, setRecipes] = useState([]);
    const [transactions, setTransactions] = useState([]);
    const [purchaseOrders, setPurchaseOrders] = useState([]);
    const [search, setSearch] = useState('');

    const load = () => {
        client.get(`/inventory?per_page=200`).then(({ data }) => setItems(data.data ?? data));
        client.get('/menu-items?per_page=200').then(({ data }) => setMenuItems(data.data ?? data));
        client.get('/recipe-items').then(({ data }) => setRecipes(data));
        client.get('/purchase-orders?per_page=50').then(({ data }) => setPurchaseOrders(data.data ?? data));
    };
    useEffect(() => { load(); client.get('/settings').then(({ data }) => { setCurrency(data.restaurant?.currency || 'USD'); }).catch(() => {}); }, []);

    const loadTransactions = (itemId) => {
        const params = itemId ? `?item_id=${itemId}` : '';
        client.get(`/inventory-transactions${params}`).then(({ data }) => setTransactions(data.data ?? data));
    };
    useEffect(() => { if (tab === 'transactions') loadTransactions(); }, [tab]);

    const filteredItems = items.filter((i) => !search || i.name.toLowerCase().includes(search.toLowerCase()) || i.sku.toLowerCase().includes(search.toLowerCase()));

    return (
        <div>
            <div className="flex items-center justify-between mb-4">
                <h2 className="text-2xl font-bold text-gray-800">Inventory</h2>
            </div>

            <div className="flex gap-2 mb-4 flex-wrap">
                {TABS.map((t) => (
                    <button key={t} onClick={() => setTab(t)} className={`px-4 py-2 rounded-lg text-sm font-medium capitalize transition-colors ${tab === t ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'}`}>{t.replace('-', ' ')}</button>
                ))}
            </div>

            {tab === 'items' && (
                <div>
                    <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search by name or SKU..." className="mb-4 px-3 py-2 border border-gray-300 rounded-lg text-sm w-full max-w-md focus:ring-2 focus:ring-indigo-500 outline-none" />
                    <div className="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-100 bg-gray-50">
                                    <th className="text-left p-3 font-medium text-gray-600">Name</th>
                                    <th className="text-left p-3 font-medium text-gray-600">SKU</th>
                                    <th className="text-left p-3 font-medium text-gray-600">Category</th>
                                    <th className="text-right p-3 font-medium text-gray-600">Stock</th>
                                    <th className="text-right p-3 font-medium text-gray-600">Min</th>
                                    <th className="text-right p-3 font-medium text-gray-600">Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredItems.length === 0 ? (
                                    <tr><td colSpan={6} className="p-4 text-gray-500 text-center">No inventory items</td></tr>
                                ) : filteredItems.map((item) => (
                                    <tr key={item.id} className={`border-b border-gray-50 hover:bg-gray-50 ${item.current_stock <= item.min_stock ? 'bg-red-50' : ''}`}>
                                        <td className="p-3 font-medium text-gray-800">{item.name} {item.current_stock <= item.min_stock && item.min_stock > 0 && <span className="ml-1 px-1.5 py-0.5 text-xs rounded bg-red-100 text-red-700">Low</span>}</td>
                                        <td className="p-3 text-gray-500">{item.sku}</td>
                                        <td className="p-3 text-gray-500">{item.category || '-'}</td>
                                        <td className={`p-3 text-right font-bold ${item.current_stock <= item.min_stock ? 'text-red-600' : 'text-gray-800'}`}>{item.current_stock}</td>
                                        <td className="p-3 text-right text-gray-500">{item.min_stock}</td>
                                        <td className="p-3 text-right text-gray-500">{item.unit}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            )}

            {tab === 'transactions' && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 bg-gray-50">
                                <th className="text-left p-3 font-medium text-gray-600">Item</th>
                                <th className="text-center p-3 font-medium text-gray-600">Type</th>
                                <th className="text-right p-3 font-medium text-gray-600">Qty</th>
                                <th className="text-left p-3 font-medium text-gray-600">Reason</th>
                                <th className="text-left p-3 font-medium text-gray-600">User</th>
                                <th className="text-right p-3 font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            {transactions.length === 0 ? (
                                <tr><td colSpan={6} className="p-4 text-gray-500 text-center">No transactions yet</td></tr>
                            ) : transactions.map((t) => (
                                <tr key={t.id} className="border-b border-gray-50">
                                    <td className="p-3 font-medium text-gray-800">{t.item?.name}</td>
                                    <td className="p-3 text-center"><span className={`px-2 py-0.5 text-xs rounded-full font-medium ${t.type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>{t.type}</span></td>
                                    <td className="p-3 text-right font-medium text-gray-800">{t.quantity}</td>
                                    <td className="p-3 text-gray-500 text-sm">{t.reason}</td>
                                    <td className="p-3 text-gray-500">{t.user?.name}</td>
                                    <td className="p-3 text-right text-gray-500 text-xs">{new Date(t.created_at).toLocaleString()}</td>
                                </tr>
                            ))}
                    </tbody>
                </table>
                </div>
            </div>
            )}

            {tab === 'purchase-orders' && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 bg-gray-50">
                                <th className="text-left p-3 font-medium text-gray-600">PO #</th>
                                <th className="text-left p-3 font-medium text-gray-600">Supplier</th>
                                <th className="text-right p-3 font-medium text-gray-600">Total</th>
                                <th className="text-center p-3 font-medium text-gray-600">Status</th>
                                <th className="text-right p-3 font-medium text-gray-600">Ordered</th>
                            </tr>
                        </thead>
                        <tbody>
                            {purchaseOrders.length === 0 ? (
                                <tr><td colSpan={5} className="p-4 text-gray-500 text-center">No purchase orders</td></tr>
                            ) : purchaseOrders.map((po) => (
                                <tr key={po.id} className="border-b border-gray-50">
                                    <td className="p-3 font-medium text-gray-800">PO-{po.id}</td>
                                    <td className="p-3 text-gray-500">{po.supplier}</td>
                                    <td className="p-3 text-right font-medium text-gray-800">{formatCurrency(po.total_cost)}</td>
                                    <td className="p-3 text-center"><span className={`px-2 py-0.5 text-xs rounded-full font-medium ${po.status === 'received' ? 'bg-green-100 text-green-700' : po.status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'}`}>{po.status}</span></td>
                                    <td className="p-3 text-right text-gray-500 text-xs">{new Date(po.ordered_at).toLocaleDateString()}</td>
                                </tr>
                            ))}
                    </tbody>
                </table>
                </div>
            </div>
            )}

            {tab === 'recipes' && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 bg-gray-50">
                                <th className="text-left p-3 font-medium text-gray-600">Menu Item</th>
                                <th className="text-left p-3 font-medium text-gray-600">Ingredient</th>
                                <th className="text-right p-3 font-medium text-gray-600">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            {recipes.length === 0 ? (
                                <tr><td colSpan={3} className="p-4 text-gray-500 text-center">No recipe links.</td></tr>
                            ) : recipes.map((r) => (
                                <tr key={r.id} className="border-b border-gray-50">
                                    <td className="p-3 font-medium text-gray-800">{r.menu_item?.name}</td>
                                    <td className="p-3 text-gray-500">{r.inventory_item?.name}</td>
                                    <td className="p-3 text-right text-gray-800">{r.quantity} {r.inventory_item?.unit}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                </div>
            )}
        </div>
    );
}
