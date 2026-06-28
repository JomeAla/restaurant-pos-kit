import { useState, useEffect } from 'react';
import client from '../api/client';
import Modal from '../components/Modal';
import { formatCurrency, setCurrency as setCurr } from '../utils/currency';

export default function MenuItems() {
    const [items, setItems] = useState([]);
    const [categories, setCategories] = useState([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState({ category_id: '', name: '', slug: '', description: '', price: '', cost: '', tax_rate: 0, is_active: true, is_available: true });
    const [saving, setSaving] = useState(false);
    const [imageFile, setImageFile] = useState(null);
    const [preview, setPreview] = useState(null);
    const [toggling, setToggling] = useState(null);

    const load = () => {
        client.get('/menu-items').then(({ data }) => setItems(data.data ?? data));
        client.get('/categories').then(({ data }) => setCategories(data));
        client.get('/settings').then(({ data }) => { const c = data.restaurant?.currency || 'USD'; setCurr(c); }).catch(() => {});
    };
    useEffect(() => { load(); }, []);

    const openCreate = () => {
        setEditing(null);
        setForm({ category_id: categories[0]?.id || '', name: '', slug: '', description: '', price: '', cost: '', tax_rate: 0, is_active: true, is_available: true });
        setImageFile(null);
        setPreview(null);
        setModalOpen(true);
    };

    const openEdit = (item) => {
        setEditing(item);
        setForm({ category_id: item.category_id, name: item.name, slug: item.slug, description: item.description || '', price: item.price, cost: item.cost || '', tax_rate: item.tax_rate || 0, is_active: item.is_active, is_available: item.is_available });
        setImageFile(null);
        setPreview(item.image || null);
        setModalOpen(true);
    };

    const toggleAvailable = async (item) => {
        setToggling(item.id);
        try {
            const { data } = await client.put(`/menu-items/${item.id}/toggle-availability`);
            setItems((prev) => prev.map((i) => i.id === item.id ? { ...i, is_available: data.is_available } : i));
        } catch { /* ignore */ } finally { setToggling(null); }
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            const payload = { ...form };
            if (imageFile) {
                const fd = new FormData();
                fd.append('file', imageFile);
                const { data } = await client.post('/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                payload.image = data.url;
            }
            if (editing) {
                await client.put(`/menu-items/${editing.id}`, payload);
            } else {
                await client.post('/menu-items', payload);
            }
            setModalOpen(false);
            await load();
        } catch (err) {
            alert(err.response?.data?.message || 'Error saving menu item');
        } finally { setSaving(false); }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this menu item?')) return;
        try { await client.delete(`/menu-items/${id}`); await load(); } catch { alert('Cannot delete'); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-800">Menu Items</h2>
                <button onClick={openCreate} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">+ New Item</button>
            </div>
            <div className="bg-white rounded-xl shadow-sm border border-gray-100">
                <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b border-gray-100 bg-gray-50">
                            <th className="text-left p-4 font-medium text-gray-600">Name</th>
                            <th className="text-left p-4 font-medium text-gray-600">Category</th>
                            <th className="text-left p-4 font-medium text-gray-600">Price</th>
                            <th className="text-left p-4 font-medium text-gray-600">Stock</th>
                            <th className="text-right p-4 font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {items.length === 0 ? (
                            <tr><td colSpan={5} className="p-4 text-gray-500 text-center">No menu items yet</td></tr>
                        ) : items.map((item) => (
                            <tr key={item.id} className="border-b border-gray-50 hover:bg-gray-50">
                                <td className="p-4 font-medium text-gray-800">{item.name}</td>
                                <td className="p-4 text-gray-500">{item.category?.name ?? '-'}</td>
                                <td className="p-4 text-gray-800">{formatCurrency(item.price)}</td>
                                <td className="p-4">
                                    <button onClick={() => toggleAvailable(item)} disabled={toggling === item.id} className={`px-2 py-1 text-xs rounded-full font-medium border transition-colors ${item.is_available ? 'bg-green-100 text-green-700 border-green-200 hover:bg-green-200' : 'bg-red-100 text-red-700 border-red-200 hover:bg-red-200'}`}>
                                        {toggling === item.id ? '...' : item.is_available ? 'In Stock' : 'Out of Stock'}
                                    </button>
                                </td>
                                <td className="p-4 text-right space-x-2">
                                    <button onClick={() => openEdit(item)} className="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                                    <button onClick={() => handleDelete(item.id)} className="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                </div>
            </div>
            <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Edit Menu Item' : 'New Menu Item'}>
                <form onSubmit={handleSave} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select value={form.category_id} onChange={(e) => setForm({ ...form, category_id: parseInt(e.target.value) })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">Select...</option>
                            {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                            <input value={form.slug} onChange={(e) => setForm({ ...form, slug: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} rows={2} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                            <input type="number" step="0.01" min="0" value={form.price} onChange={(e) => setForm({ ...form, price: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Cost ($)</label>
                            <input type="number" step="0.01" min="0" value={form.cost} onChange={(e) => setForm({ ...form, cost: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" value={form.tax_rate} onChange={(e) => setForm({ ...form, tax_rate: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        <input type="file" accept="image/*" onChange={(e) => { const f = e.target.files[0]; setImageFile(f); if (f) setPreview(URL.createObjectURL(f)); }} className="w-full text-sm" />
                        {preview && <img src={preview} alt="preview" className="mt-2 h-20 w-20 object-cover rounded-lg border" />}
                    </div>
                    <div className="flex gap-4">
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked={form.is_active} onChange={(e) => setForm({ ...form, is_active: e.target.checked })} className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            <span className="text-sm text-gray-700">Active</span>
                        </label>
                        <label className="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked={form.is_available} onChange={(e) => setForm({ ...form, is_available: e.target.checked })} className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            <span className="text-sm text-gray-700">Available</span>
                        </label>
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={() => setModalOpen(false)} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                        <button type="submit" disabled={saving} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                            {saving ? 'Saving...' : editing ? 'Update' : 'Create'}
                        </button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
