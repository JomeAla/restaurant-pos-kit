import { useState, useEffect } from 'react';
import client from '../api/client';
import Modal from '../components/Modal';
import { formatCurrency, setCurrency as setCurr } from '../utils/currency';

export default function Modifiers() {
    const [modifiers, setModifiers] = useState([]);
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState({ name: '', type: 'single', is_required: false, min_selection: 0, max_selection: 0 });
    const [saving, setSaving] = useState(false);

    const [optionModal, setOptionModal] = useState({ open: false, modifier: null, option: null, name: '', price_adjustment: '0', is_default: false });

    const load = () => {
        client.get('/modifiers').then(({ data }) => setModifiers(data));
        client.get('/settings').then(({ data }) => { const c = data.restaurant?.currency || 'USD'; setCurr(c); }).catch(() => {});
    };
    useEffect(() => { load(); }, []);

    const openCreate = () => { setEditing(null); setForm({ name: '', type: 'single', is_required: false, min_selection: 0, max_selection: 0 }); setModalOpen(true); };
    const openEdit = (mod) => { setEditing(mod); setForm({ name: mod.name, type: mod.type, is_required: mod.is_required, min_selection: mod.min_selection, max_selection: mod.max_selection }); setModalOpen(true); };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editing) await client.put(`/modifiers/${editing.id}`, form);
            else await client.post('/modifiers', form);
            setModalOpen(false);
            await load();
        } catch (err) { alert(err.response?.data?.message || 'Error saving modifier'); } finally { setSaving(false); }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this modifier?')) return;
        try { await client.delete(`/modifiers/${id}`); await load(); } catch { alert('Cannot delete'); }
    };

    const openOption = (mod, opt = null) => {
        setOptionModal({
            open: true, modifier: mod,
            option: opt,
            name: opt?.name || '',
            price_adjustment: opt?.price_adjustment || '0',
            is_default: opt?.is_default || false,
        });
    };

    const saveOption = async () => {
        const { modifier, option, name, price_adjustment, is_default } = optionModal;
        try {
            if (option) {
                await client.put(`/modifier-options/${option.id}`, { name, price_adjustment, is_default });
            } else {
                await client.post('/modifier-options', { modifier_id: modifier.id, name, price_adjustment, is_default });
            }
            setOptionModal((p) => ({ ...p, open: false }));
            await load();
        } catch (err) { alert(err.response?.data?.message || 'Error saving option'); }
    };

    const deleteOption = async (optId) => {
        if (!confirm('Delete this option?')) return;
        try { await client.delete(`/modifier-options/${optId}`); await load(); } catch { alert('Cannot delete'); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-800">Modifiers</h2>
                <button onClick={openCreate} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">+ New Modifier</button>
            </div>
            <div className="space-y-4">
                {modifiers.length === 0 ? (<p className="text-gray-500">No modifiers yet.</p>) : modifiers.map((mod) => (
                    <div key={mod.id} className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div className="flex items-center justify-between mb-3">
                            <div>
                                <h3 className="font-semibold text-gray-800">{mod.name}</h3>
                                <div className="flex gap-2 mt-1">
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${mod.type === 'single' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'}`}>
                                        {mod.type === 'single' ? 'Single' : 'Multi'}
                                    </span>
                                    {mod.is_required && <span className="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">Required</span>}
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <button onClick={() => openOption(mod)} className="px-3 py-1 text-xs font-medium text-indigo-600 border border-indigo-200 rounded-lg hover:bg-indigo-50">+ Option</button>
                                <button onClick={() => openEdit(mod)} className="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                                <button onClick={() => handleDelete(mod.id)} className="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                            </div>
                        </div>
                        {mod.options?.length > 0 ? (
                            <div className="border-t border-gray-100 pt-2 space-y-1">
                                {mod.options.map((opt) => (
                                    <div key={opt.id} className="flex items-center justify-between text-sm pl-4 py-1.5 hover:bg-gray-50 rounded group">
                                        <div className="flex items-center gap-2">
                                            <span className="text-gray-600">{opt.name}</span>
                                            {opt.is_default && <span className="text-xs text-indigo-500 font-medium">default</span>}
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <span className="text-gray-500">{parseFloat(opt.price_adjustment) > 0 ? `+${formatCurrency(opt.price_adjustment)}` : 'Free'}</span>
                                            <button onClick={() => openOption(mod, opt)} className="text-indigo-500 hover:text-indigo-700 text-xs opacity-0 group-hover:opacity-100">Edit</button>
                                            <button onClick={() => deleteOption(opt.id)} className="text-red-500 hover:text-red-700 text-xs opacity-0 group-hover:opacity-100">Del</button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-gray-400 pl-4">No options</p>
                        )}
                    </div>
                ))}
            </div>

            <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Edit Modifier' : 'New Modifier'}>
                <form onSubmit={handleSave} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="single">Single Select</option>
                            <option value="multi">Multi Select</option>
                        </select>
                    </div>
                    <div className="flex items-center gap-2">
                        <input type="checkbox" id="is_required" checked={form.is_required} onChange={(e) => setForm({ ...form, is_required: e.target.checked })} className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        <label htmlFor="is_required" className="text-sm text-gray-700">Required</label>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Min Selection</label>
                            <input type="number" min="0" value={form.min_selection} onChange={(e) => setForm({ ...form, min_selection: parseInt(e.target.value) || 0 })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Max Selection</label>
                            <input type="number" min="0" value={form.max_selection} onChange={(e) => setForm({ ...form, max_selection: parseInt(e.target.value) || 0 })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
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

            <Modal open={optionModal.open} onClose={() => setOptionModal((p) => ({ ...p, open: false }))} title={optionModal.option ? 'Edit Option' : 'New Option'}>
                <div className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input value={optionModal.name} onChange={(e) => setOptionModal((p) => ({ ...p, name: e.target.value }))} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Price Adjustment</label>
                        <input type="number" step="0.01" min="0" value={optionModal.price_adjustment} onChange={(e) => setOptionModal((p) => ({ ...p, price_adjustment: e.target.value }))} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div className="flex items-center gap-2">
                        <input type="checkbox" id="is_default" checked={optionModal.is_default} onChange={(e) => setOptionModal((p) => ({ ...p, is_default: e.target.checked }))} className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        <label htmlFor="is_default" className="text-sm text-gray-700">Default option</label>
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button onClick={() => setOptionModal((p) => ({ ...p, open: false }))} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                        <button onClick={saveOption} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                            {optionModal.option ? 'Update' : 'Add'} Option
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    );
}
