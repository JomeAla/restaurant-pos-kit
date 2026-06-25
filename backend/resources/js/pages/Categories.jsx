import { useState, useEffect } from 'react';
import client from '../api/client';
import Modal from '../components/Modal';

export default function Categories() {
    const [categories, setCategories] = useState([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState({ name: '', slug: '', description: '', sort_order: 0, is_active: true });
    const [saving, setSaving] = useState(false);

    const load = () => client.get('/categories').then(({ data }) => setCategories(data));
    useEffect(() => { load(); }, []);

    const openCreate = () => { setEditing(null); setForm({ name: '', slug: '', description: '', sort_order: 0, is_active: true }); setModalOpen(true); };
    const openEdit = (cat) => { setEditing(cat); setForm({ name: cat.name, slug: cat.slug, description: cat.description || '', sort_order: cat.sort_order || 0, is_active: cat.is_active }); setModalOpen(true); };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editing) {
                await client.put(`/categories/${editing.id}`, form);
            } else {
                await client.post('/categories', form);
            }
            setModalOpen(false);
            await load();
        } catch (err) {
            alert(err.response?.data?.message || 'Error saving category');
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this category?')) return;
        try {
            await client.delete(`/categories/${id}`);
            await load();
        } catch { alert('Cannot delete category with items'); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-800">Categories</h2>
                <button onClick={openCreate} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">+ New Category</button>
            </div>
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b border-gray-100 bg-gray-50">
                            <th className="text-left p-4 font-medium text-gray-600">Name</th>
                            <th className="text-left p-4 font-medium text-gray-600">Slug</th>
                            <th className="text-left p-4 font-medium text-gray-600">Items</th>
                            <th className="text-left p-4 font-medium text-gray-600">Status</th>
                            <th className="text-right p-4 font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {categories.length === 0 ? (
                            <tr><td colSpan={5} className="p-4 text-gray-500 text-center">No categories yet</td></tr>
                        ) : categories.map((cat) => (
                            <tr key={cat.id} className="border-b border-gray-50 hover:bg-gray-50">
                                <td className="p-4 font-medium text-gray-800">{cat.name}</td>
                                <td className="p-4 text-gray-500">{cat.slug}</td>
                                <td className="p-4 text-gray-500">{cat.menu_items_count ?? 0}</td>
                                <td className="p-4">
                                    <span className={`px-2 py-1 text-xs rounded-full font-medium ${cat.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>
                                        {cat.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td className="p-4 text-right space-x-2">
                                    <button onClick={() => openEdit(cat)} className="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                                    <button onClick={() => handleDelete(cat.id)} className="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Edit Category' : 'New Category'}>
                <form onSubmit={handleSave} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input value={form.slug} onChange={(e) => setForm({ ...form, slug: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} rows={2} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" value={form.sort_order} onChange={(e) => setForm({ ...form, sort_order: parseInt(e.target.value) || 0 })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div className="flex items-end pb-2">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={form.is_active} onChange={(e) => setForm({ ...form, is_active: e.target.checked })} className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                <span className="text-sm text-gray-700">Active</span>
                            </label>
                        </div>
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
