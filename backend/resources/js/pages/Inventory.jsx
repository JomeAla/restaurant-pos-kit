import { useState, useEffect } from 'react';
import client from '../api/client';
import { formatCurrency, setCurrency } from '../utils/currency';
import Modal from '../components/Modal';

const TABS = ['items', 'transactions', 'purchase-orders', 'recipes'];

export default function Inventory() {
    const [tab, setTab] = useState('items');
    const [items, setItems] = useState([]);
    const [menuItems, setMenuItems] = useState([]);
    const [recipes, setRecipes] = useState([]);
    const [transactions, setTransactions] = useState([]);
    const [purchaseOrders, setPurchaseOrders] = useState([]);
    const [search, setSearch] = useState('');

    const [itemModal, setItemModal] = useState(false);
    const [editingItem, setEditingItem] = useState(null);
    const [itemForm, setItemForm] = useState({ name: '', sku: '', category: '', unit: 'pcs', current_stock: 0, min_stock: 0, cost_per_unit: 0, supplier: '' });

    const [stockModal, setStockModal] = useState(false);
    const [stockType, setStockType] = useState('in');
    const [stockForm, setStockForm] = useState({ item_id: '', quantity: '', reason: '', notes: '' });

    const [poModal, setPoModal] = useState(false);
    const [poForm, setPoForm] = useState({ supplier: '', items: [{ inventory_item_id: '', quantity: '', unit_cost: '' }] });

    const [recipeModal, setRecipeModal] = useState(false);
    const [recipeForm, setRecipeForm] = useState({ menu_item_id: '', inventory_item_id: '', quantity: '' });

    const [saving, setSaving] = useState(false);

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

    const openItemModal = (item = null) => {
        setEditingItem(item);
        setItemForm(item ? { name: item.name, sku: item.sku, category: item.category || '', unit: item.unit, current_stock: item.current_stock, min_stock: item.min_stock, cost_per_unit: item.cost_per_unit, supplier: item.supplier || '' } : { name: '', sku: '', category: '', unit: 'pcs', current_stock: 0, min_stock: 0, cost_per_unit: 0, supplier: '' });
        setItemModal(true);
    };

    const saveItem = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editingItem) await client.put(`/inventory/${editingItem.id}`, itemForm);
            else await client.post('/inventory', itemForm);
            setItemModal(false);
            load();
        } catch (err) { alert(err.response?.data?.message || 'Error'); } finally { setSaving(false); }
    };

    const handleStock = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (stockType === 'adjust') await client.post('/inventory/adjust', { item_id: stockForm.item_id, new_stock: stockForm.quantity, notes: stockForm.notes });
            else await client.post(`/inventory/stock-${stockType}`, stockForm);
            setStockModal(false);
            setStockForm({ item_id: '', quantity: '', reason: '', notes: '' });
            load();
        } catch (err) { alert(err.response?.data?.message || 'Error'); } finally { setSaving(false); }
    };

    const savePO = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await client.post('/purchase-orders', poForm);
            setPoModal(false);
            setPoForm({ supplier: '', items: [{ inventory_item_id: '', quantity: '', unit_cost: '' }] });
            load();
        } catch (err) { alert(err.response?.data?.message || 'Error'); } finally { setSaving(false); }
    };

    const receivePO = async (id) => {
        if (!confirm('Mark this purchase order as received?')) return;
        try { await client.post(`/purchase-orders/${id}/receive`); load(); } catch (err) { alert(err.response?.data?.message || 'Error'); }
    };

    const saveRecipe = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await client.post('/recipe-items', recipeForm);
            setRecipeModal(false);
            setRecipeForm({ menu_item_id: '', inventory_item_id: '', quantity: '' });
            client.get('/recipe-items').then(({ data }) => setRecipes(data));
        } catch (err) { alert(err.response?.data?.message || 'Error'); } finally { setSaving(false); }
    };

    const removeRecipe = async (id) => {
        if (!confirm('Remove this ingredient?')) return;
        try { await client.delete(`/recipe-items/${id}`); client.get('/recipe-items').then(({ data }) => setRecipes(data)); } catch { alert('Error'); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-4">
                <h2 className="text-2xl font-bold text-gray-800">Inventory</h2>
                <div className="flex gap-2">
                    {tab === 'items' && <button onClick={() => openItemModal()} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">+ New Item</button>}
                    {tab === 'items' && <button onClick={() => { setStockType('in'); setStockForm({ item_id: items[0]?.id || '', quantity: '', reason: '', notes: '' }); setStockModal(true); }} className="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Stock In/Out</button>}
                    {tab === 'purchase-orders' && <button onClick={() => setPoModal(true)} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">+ New PO</button>}
                    {tab === 'recipes' && <button onClick={() => setRecipeModal(true)} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">+ Link Ingredient</button>}
                </div>
            </div>

            <div className="flex gap-2 mb-4 flex-wrap">
                {TABS.map((t) => (
                    <button key={t} onClick={() => setTab(t)} className={`px-4 py-2 rounded-lg text-sm font-medium capitalize transition-colors ${tab === t ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'}`}>{t.replace('-', ' ')}</button>
                ))}
            </div>

            {tab === 'items' && (
                <div>
                    <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search by name or SKU..." className="mb-4 px-3 py-2 border border-gray-300 rounded-lg text-sm w-full max-w-md focus:ring-2 focus:ring-indigo-500 outline-none" />
                    <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-100 bg-gray-50">
                                    <th className="text-left p-3 font-medium text-gray-600">Name</th>
                                    <th className="text-left p-3 font-medium text-gray-600">SKU</th>
                                    <th className="text-left p-3 font-medium text-gray-600">Category</th>
                                    <th className="text-right p-3 font-medium text-gray-600">Stock</th>
                                    <th className="text-right p-3 font-medium text-gray-600">Min</th>
                                    <th className="text-right p-3 font-medium text-gray-600">Unit</th>
                                    <th className="text-right p-3 font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredItems.length === 0 ? (
                                    <tr><td colSpan={7} className="p-4 text-gray-500 text-center">No inventory items</td></tr>
                                ) : filteredItems.map((item) => (
                                    <tr key={item.id} className={`border-b border-gray-50 hover:bg-gray-50 ${item.current_stock <= item.min_stock ? 'bg-red-50' : ''}`}>
                                        <td className="p-3 font-medium text-gray-800">{item.name} {item.current_stock <= item.min_stock && item.min_stock > 0 && <span className="ml-1 px-1.5 py-0.5 text-xs rounded bg-red-100 text-red-700">Low</span>}</td>
                                        <td className="p-3 text-gray-500">{item.sku}</td>
                                        <td className="p-3 text-gray-500">{item.category || '-'}</td>
                                        <td className={`p-3 text-right font-bold ${item.current_stock <= item.min_stock ? 'text-red-600' : 'text-gray-800'}`}>{item.current_stock}</td>
                                        <td className="p-3 text-right text-gray-500">{item.min_stock}</td>
                                        <td className="p-3 text-right text-gray-500">{item.unit}</td>
                                        <td className="p-3 text-right space-x-2">
                                            <button onClick={() => openItemModal(item)} className="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {tab === 'transactions' && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
            )}

            {tab === 'purchase-orders' && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 bg-gray-50">
                                <th className="text-left p-3 font-medium text-gray-600">PO #</th>
                                <th className="text-left p-3 font-medium text-gray-600">Supplier</th>
                                <th className="text-right p-3 font-medium text-gray-600">Total</th>
                                <th className="text-center p-3 font-medium text-gray-600">Status</th>
                                <th className="text-right p-3 font-medium text-gray-600">Ordered</th>
                                <th className="text-right p-3 font-medium text-gray-600"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {purchaseOrders.length === 0 ? (
                                <tr><td colSpan={6} className="p-4 text-gray-500 text-center">No purchase orders</td></tr>
                            ) : purchaseOrders.map((po) => (
                                <tr key={po.id} className="border-b border-gray-50">
                                    <td className="p-3 font-medium text-gray-800">PO-{po.id}</td>
                                    <td className="p-3 text-gray-500">{po.supplier}</td>
                                    <td className="p-3 text-right font-medium text-gray-800">{formatCurrency(po.total_cost)}</td>
                                    <td className="p-3 text-center"><span className={`px-2 py-0.5 text-xs rounded-full font-medium ${po.status === 'received' ? 'bg-green-100 text-green-700' : po.status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'}`}>{po.status}</span></td>
                                    <td className="p-3 text-right text-gray-500 text-xs">{new Date(po.ordered_at).toLocaleDateString()}</td>
                                    <td className="p-3 text-right">{po.status === 'pending' && <button onClick={() => receivePO(po.id)} className="text-green-600 hover:text-green-800 text-sm font-medium">Receive</button>}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {tab === 'recipes' && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 bg-gray-50">
                                <th className="text-left p-3 font-medium text-gray-600">Menu Item</th>
                                <th className="text-left p-3 font-medium text-gray-600">Ingredient</th>
                                <th className="text-right p-3 font-medium text-gray-600">Qty</th>
                                <th className="text-right p-3 font-medium text-gray-600"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {recipes.length === 0 ? (
                                <tr><td colSpan={4} className="p-4 text-gray-500 text-center">No recipe links. Link menu items to inventory ingredients.</td></tr>
                            ) : recipes.map((r) => (
                                <tr key={r.id} className="border-b border-gray-50">
                                    <td className="p-3 font-medium text-gray-800">{r.menu_item?.name}</td>
                                    <td className="p-3 text-gray-500">{r.inventory_item?.name}</td>
                                    <td className="p-3 text-right text-gray-800">{r.quantity} {r.inventory_item?.unit}</td>
                                    <td className="p-3 text-right"><button onClick={() => removeRecipe(r.id)} className="text-red-500 hover:text-red-700 text-sm">Remove</button></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            <Modal open={itemModal} onClose={() => setItemModal(false)} title={editingItem ? 'Edit Inventory Item' : 'New Inventory Item'}>
                <form onSubmit={saveItem} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Name *</label><input value={itemForm.name} onChange={(e) => setItemForm({ ...itemForm, name: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">SKU *</label><input value={itemForm.sku} onChange={(e) => setItemForm({ ...itemForm, sku: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                    </div>
                    <div className="grid grid-cols-3 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Category</label><input value={itemForm.category} onChange={(e) => setItemForm({ ...itemForm, category: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Unit *</label><select value={itemForm.unit} onChange={(e) => setItemForm({ ...itemForm, unit: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"><option value="kg">kg</option><option value="g">g</option><option value="pcs">pcs</option><option value="ltr">ltr</option><option value="ml">ml</option></select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Supplier</label><input value={itemForm.supplier} onChange={(e) => setItemForm({ ...itemForm, supplier: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                    </div>
                    <div className="grid grid-cols-3 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Current Stock</label><input type="number" step="0.01" min="0" value={itemForm.current_stock} onChange={(e) => setItemForm({ ...itemForm, current_stock: parseFloat(e.target.value) || 0 })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Min Stock</label><input type="number" step="0.01" min="0" value={itemForm.min_stock} onChange={(e) => setItemForm({ ...itemForm, min_stock: parseFloat(e.target.value) || 0 })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Cost/Unit ($)</label><input type="number" step="0.01" min="0" value={itemForm.cost_per_unit} onChange={(e) => setItemForm({ ...itemForm, cost_per_unit: parseFloat(e.target.value) || 0 })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={() => setItemModal(false)} className="px-4 py-2 text-sm text-gray-600">Cancel</button>
                        <button type="submit" disabled={saving} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">{saving ? 'Saving...' : editingItem ? 'Update' : 'Create'}</button>
                    </div>
                </form>
            </Modal>

            <Modal open={stockModal} onClose={() => setStockModal(false)} title="Stock Adjustment">
                <form onSubmit={handleStock} className="space-y-4">
                    <div><label className="block text-sm font-medium text-gray-700 mb-1">Item</label><select value={stockForm.item_id} onChange={(e) => setStockForm({ ...stockForm, item_id: parseInt(e.target.value) })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"><option value="">Select...</option>{items.map((i) => <option key={i.id} value={i.id}>{i.name} ({i.current_stock} {i.unit})</option>)}</select></div>
                    <div className="flex gap-2">
                        {['in', 'out', 'adjust'].map((t) => (
                            <button key={t} type="button" onClick={() => setStockType(t)} className={`flex-1 py-2 rounded-lg text-sm font-medium capitalize ${stockType === t ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'}`}>{t}</button>
                        ))}
                    </div>
                    <div><label className="block text-sm font-medium text-gray-700 mb-1">{stockType === 'adjust' ? 'New Stock Value' : 'Quantity'}</label><input type="number" step="0.01" min="0" value={stockForm.quantity} onChange={(e) => setStockForm({ ...stockForm, quantity: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                    <div><label className="block text-sm font-medium text-gray-700 mb-1">Reason</label><input value={stockForm.reason} onChange={(e) => setStockForm({ ...stockForm, reason: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                    <div><label className="block text-sm font-medium text-gray-700 mb-1">Notes</label><textarea value={stockForm.notes} onChange={(e) => setStockForm({ ...stockForm, notes: e.target.value })} rows={2} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={() => setStockModal(false)} className="px-4 py-2 text-sm text-gray-600">Cancel</button>
                        <button type="submit" disabled={saving} className="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50">{saving ? 'Processing...' : stockType === 'adjust' ? 'Set Stock' : stockType === 'in' ? 'Add Stock' : 'Remove Stock'}</button>
                    </div>
                </form>
            </Modal>

            <Modal open={poModal} onClose={() => setPoModal(false)} title="New Purchase Order">
                <form onSubmit={savePO} className="space-y-4">
                    <div><label className="block text-sm font-medium text-gray-700 mb-1">Supplier *</label><input value={poForm.supplier} onChange={(e) => setPoForm({ ...poForm, supplier: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                    {poForm.items.map((poItem, i) => (
                        <div key={i} className="bg-gray-50 p-3 rounded-lg space-y-2">
                            <div className="flex justify-between"><span className="text-xs font-medium text-gray-500">Item #{i + 1}</span>{poForm.items.length > 1 && <button type="button" onClick={() => setPoForm({ ...poForm, items: poForm.items.filter((_, idx) => idx !== i) })} className="text-xs text-red-500">Remove</button>}</div>
                            <div className="grid grid-cols-3 gap-2">
                                <select value={poItem.inventory_item_id} onChange={(e) => { const next = [...poForm.items]; next[i].inventory_item_id = parseInt(e.target.value); setPoForm({ ...poForm, items: next }); }} required className="px-2 py-1.5 border border-gray-300 rounded text-sm outline-none"><option value="">Item</option>{items.map((inv) => <option key={inv.id} value={inv.id}>{inv.name}</option>)}</select>
                                <input type="number" step="0.01" min="0.01" placeholder="Qty" value={poItem.quantity} onChange={(e) => { const next = [...poForm.items]; next[i].quantity = e.target.value; setPoForm({ ...poForm, items: next }); }} required className="px-2 py-1.5 border border-gray-300 rounded text-sm outline-none" />
                                <input type="number" step="0.01" min="0" placeholder="Unit Cost" value={poItem.unit_cost} onChange={(e) => { const next = [...poForm.items]; next[i].unit_cost = e.target.value; setPoForm({ ...poForm, items: next }); }} required className="px-2 py-1.5 border border-gray-300 rounded text-sm outline-none" />
                            </div>
                        </div>
                    ))}
                    <button type="button" onClick={() => setPoForm({ ...poForm, items: [...poForm.items, { inventory_item_id: '', quantity: '', unit_cost: '' }] })} className="text-sm text-indigo-600 hover:text-indigo-800">+ Add item</button>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={() => setPoModal(false)} className="px-4 py-2 text-sm text-gray-600">Cancel</button>
                        <button type="submit" disabled={saving} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">{saving ? 'Saving...' : 'Create PO'}</button>
                    </div>
                </form>
            </Modal>

            <Modal open={recipeModal} onClose={() => setRecipeModal(false)} title="Link Ingredient to Menu Item">
                <form onSubmit={saveRecipe} className="space-y-4">
                    <div><label className="block text-sm font-medium text-gray-700 mb-1">Menu Item</label><select value={recipeForm.menu_item_id} onChange={(e) => setRecipeForm({ ...recipeForm, menu_item_id: parseInt(e.target.value) })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"><option value="">Select...</option>{menuItems.map((mi) => <option key={mi.id} value={mi.id}>{mi.name}</option>)}</select></div>
                    <div><label className="block text-sm font-medium text-gray-700 mb-1">Ingredient</label><select value={recipeForm.inventory_item_id} onChange={(e) => setRecipeForm({ ...recipeForm, inventory_item_id: parseInt(e.target.value) })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none"><option value="">Select...</option>{items.map((inv) => <option key={inv.id} value={inv.id}>{inv.name} ({inv.unit})</option>)}</select></div>
                    <div><label className="block text-sm font-medium text-gray-700 mb-1">Quantity</label><input type="number" step="0.01" min="0.01" value={recipeForm.quantity} onChange={(e) => setRecipeForm({ ...recipeForm, quantity: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" /></div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={() => setRecipeModal(false)} className="px-4 py-2 text-sm text-gray-600">Cancel</button>
                        <button type="submit" disabled={saving} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">{saving ? 'Saving...' : 'Link'}</button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
