import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import client from '../api/client';
import Modal from '../components/Modal';

export default function Tables() {
    const navigate = useNavigate();
    const [tables, setTables] = useState([]);
    const [floorPlans, setFloorPlans] = useState([]);
    const [selectedPlan, setSelectedPlan] = useState(null);
    const [modalOpen, setModalOpen] = useState(false);
    const [form, setForm] = useState({ table_number: '', capacity: 2, section: '', status: 'free', shape: 'rectangle', floor_plan_id: '', pos_x: 0, pos_y: 0, width: 80, height: 80 });
    const [saving, setSaving] = useState(false);

    const load = () => {
        client.get('/floor-plans').then(({ data }) => {
            setFloorPlans(data);
            if (!selectedPlan && data.length > 0) setSelectedPlan(data[0]);
        });
        client.get('/tables').then(({ data }) => setTables(data));
    };
    useEffect(() => { load(); }, []);

    const planTables = selectedPlan ? tables.filter((t) => t.floor_plan_id === selectedPlan.id) : tables;
    const statusColors = { free: 'bg-green-500', occupied: 'bg-red-500', reserved: 'bg-amber-500', dirty: 'bg-gray-400' };

    const openCreate = () => {
        setForm({ table_number: (tables.length + 1), capacity: 2, section: '', status: 'free', shape: 'rectangle', floor_plan_id: selectedPlan?.id || '', pos_x: 0, pos_y: 0, width: 80, height: 80 });
        setModalOpen(true);
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await client.post('/tables', form);
            setModalOpen(false);
            await load();
        } catch (err) { alert(err.response?.data?.message || 'Error saving table'); } finally { setSaving(false); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-800">Floor Plan</h2>
                <button onClick={openCreate} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">+ New Table</button>
            </div>
            {floorPlans.length > 0 && (
                <div className="mb-4 flex gap-2 flex-wrap">
                    {floorPlans.map((fp) => (
                        <button key={fp.id} onClick={() => setSelectedPlan(fp)} className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${selectedPlan?.id === fp.id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'}`}>
                            {fp.name}
                        </button>
                    ))}
                </div>
            )}
            {selectedPlan ? (
                <div className="relative bg-white rounded-xl shadow-sm border border-gray-100 p-6" style={{ minHeight: '400px' }}>
                    <div className="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-4">
                        {planTables.length === 0 ? (
                            <p className="col-span-full text-gray-500 text-center py-12">No tables in this floor plan.</p>
                        ) : planTables.map((table) => (
                            <div key={table.id} onClick={() => navigate('/pos')} className={`aspect-square rounded-xl border-2 flex flex-col items-center justify-center cursor-pointer transition-transform hover:scale-105 ${table.shape === 'circle' ? 'rounded-full' : ''}`}>
                                <span className="text-lg font-bold text-gray-800">{table.table_number}</span>
                                <span className="text-xs text-gray-500">{table.capacity} seats</span>
                                <span className={`mt-1 px-2 py-0.5 text-xs rounded-full text-white font-medium ${statusColors[table.status] || 'bg-gray-400'}`}>
                                    {table.status}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            ) : (
                <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100 text-center">
                    <p className="text-gray-500 mb-4">No floor plans created yet.</p>
                    <p className="text-sm text-gray-400">Create one in the admin panel at <a href="/admin" className="text-indigo-600 hover:underline">/admin</a></p>
                </div>
            )}

            <Modal open={modalOpen} onClose={() => setModalOpen(false)} title="New Table">
                <form onSubmit={handleSave} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Table #</label>
                            <input type="number" value={form.table_number} onChange={(e) => setForm({ ...form, table_number: parseInt(e.target.value) || '' })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                            <input type="number" min="1" value={form.capacity} onChange={(e) => setForm({ ...form, capacity: parseInt(e.target.value) || 1 })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Section</label>
                        <input value={form.section} onChange={(e) => setForm({ ...form, section: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="e.g. Window, Patio, Bar" />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="free">Free</option>
                                <option value="occupied">Occupied</option>
                                <option value="reserved">Reserved</option>
                                <option value="dirty">Dirty</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Shape</label>
                            <select value={form.shape} onChange={(e) => setForm({ ...form, shape: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="rectangle">Rectangle</option>
                                <option value="circle">Circle</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Floor Plan</label>
                        <select value={form.floor_plan_id} onChange={(e) => setForm({ ...form, floor_plan_id: parseInt(e.target.value) || '' })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">Select...</option>
                            {floorPlans.map((fp) => <option key={fp.id} value={fp.id}>{fp.name}</option>)}
                        </select>
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={() => setModalOpen(false)} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                        <button type="submit" disabled={saving} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                            {saving ? 'Saving...' : 'Create Table'}
                        </button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
