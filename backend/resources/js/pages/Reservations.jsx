import { useState, useEffect } from 'react';
import client from '../api/client';
import Modal from '../components/Modal';

export default function Reservations() {
    const [reservations, setReservations] = useState([]);
    const [tables, setTables] = useState([]);
    const [filterDate, setFilterDate] = useState(new Date().toISOString().split('T')[0]);
    const [filterStatus, setFilterStatus] = useState('');
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState({ customer_name: '', customer_phone: '', customer_email: '', party_size: 2, table_id: '', date: new Date().toISOString().split('T')[0], time_slot: '18:00', notes: '', walk_in: false });
    const [saving, setSaving] = useState(false);
    const [walkInOpen, setWalkInOpen] = useState(false);
    const [walkForm, setWalkForm] = useState({ customer_name: '', party_size: 2, table_id: '', time_slot: '18:00' });

    const timeSlots = ['11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00'];

    const load = () => {
        const params = new URLSearchParams();
        if (filterDate) params.set('date', filterDate);
        if (filterStatus) params.set('status', filterStatus);
        client.get(`/reservations?${params.toString()}`).then(({ data }) => setReservations(data.data ?? data));
        client.get('/tables').then(({ data }) => setTables(data));
    };
    useEffect(() => { load(); }, [filterDate, filterStatus]);

    const statusColors = { pending: 'bg-yellow-100 text-yellow-700 border-yellow-200', confirmed: 'bg-blue-100 text-blue-700 border-blue-200', seated: 'bg-green-100 text-green-700 border-green-200', cancelled: 'bg-red-100 text-red-700 border-red-200' };

    const openCreate = () => {
        setEditing(null);
        setForm({ customer_name: '', customer_phone: '', customer_email: '', party_size: 2, table_id: '', date: filterDate, time_slot: '18:00', notes: '', walk_in: false });
        setModalOpen(true);
    };

    const openEdit = (r) => {
        setEditing(r);
        setForm({ customer_name: r.customer_name, customer_phone: r.customer_phone || '', customer_email: r.customer_email || '', party_size: r.party_size, table_id: r.table_id || '', date: r.date, time_slot: r.time_slot, notes: r.notes || '', walk_in: r.walk_in });
        setModalOpen(true);
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editing) {
                await client.put(`/reservations/${editing.id}`, form);
            } else {
                await client.post('/reservations', form);
            }
            setModalOpen(false);
            load();
        } catch (err) { alert(err.response?.data?.message || 'Error saving reservation'); } finally { setSaving(false); }
    };

    const handleCancel = async (id) => {
        if (!confirm('Cancel this reservation?')) return;
        try { await client.delete(`/reservations/${id}`); load(); } catch { alert('Error'); }
    };

    const handleWalkIn = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await client.post('/reservations', { ...walkForm, date: filterDate, walk_in: true, customer_phone: '', customer_email: '', notes: 'Walk-in' });
            setWalkInOpen(false);
            setWalkForm({ customer_name: '', party_size: 2, table_id: '', time_slot: '18:00' });
            load();
        } catch (err) { alert(err.response?.data?.message || 'Error'); } finally { setSaving(false); }
    };

    const updateStatus = async (id, status) => {
        try { await client.put(`/reservations/${id}`, { status }); load(); } catch { alert('Error'); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-4 flex-wrap gap-2">
                <h2 className="text-2xl font-bold text-gray-800">Reservations</h2>
                <div className="flex gap-2">
                    <button onClick={() => setWalkInOpen(true)} className="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Walk-in</button>
                    <button onClick={openCreate} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">+ New Reservation</button>
                </div>
            </div>

            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label className="block text-xs font-medium text-gray-500 mb-1">Date</label>
                    <input type="date" value={filterDate} onChange={(e) => setFilterDate(e.target.value)} className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                </div>
                <div>
                    <label className="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)} className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="seated">Seated</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b border-gray-100 bg-gray-50">
                            <th className="text-left p-4 font-medium text-gray-600">Customer</th>
                            <th className="text-left p-4 font-medium text-gray-600">Contact</th>
                            <th className="text-center p-4 font-medium text-gray-600">Party</th>
                            <th className="text-center p-4 font-medium text-gray-600">Time</th>
                            <th className="text-center p-4 font-medium text-gray-600">Table</th>
                            <th className="text-center p-4 font-medium text-gray-600">Status</th>
                            <th className="text-right p-4 font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {reservations.length === 0 ? (
                            <tr><td colSpan={7} className="p-4 text-gray-500 text-center">No reservations for this date</td></tr>
                        ) : reservations.map((r) => (
                            <tr key={r.id} className="border-b border-gray-50 hover:bg-gray-50">
                                <td className="p-4">
                                    <p className="font-medium text-gray-800">{r.customer_name}</p>
                                    {r.walk_in && <span className="text-xs text-green-600 font-medium">Walk-in</span>}
                                </td>
                                <td className="p-4 text-gray-500">
                                    {r.customer_phone && <p>{r.customer_phone}</p>}
                                    {r.customer_email && <p className="text-xs">{r.customer_email}</p>}
                                </td>
                                <td className="p-4 text-center text-gray-800 font-medium">{r.party_size}</td>
                                <td className="p-4 text-center text-gray-800">{r.time_slot}</td>
                                <td className="p-4 text-center text-gray-500">{r.table ? `#${r.table.table_number}` : '-'}</td>
                                <td className="p-4 text-center">
                                    <span className={`inline-block px-2 py-1 text-xs rounded-full font-medium border ${statusColors[r.status] || 'bg-gray-100 text-gray-600'}`}>{r.status}</span>
                                </td>
                                <td className="p-4 text-right space-x-2">
                                    {r.status === 'pending' && <button onClick={() => updateStatus(r.id, 'confirmed')} className="text-blue-600 hover:text-blue-800 text-xs font-medium">Confirm</button>}
                                    {r.status === 'confirmed' && <button onClick={() => updateStatus(r.id, 'seated')} className="text-green-600 hover:text-green-800 text-xs font-medium">Seat</button>}
                                    <button onClick={() => openEdit(r)} className="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                                    {!['cancelled', 'seated'].includes(r.status) && <button onClick={() => handleCancel(r.id)} className="text-red-600 hover:text-red-800 text-sm font-medium">Cancel</button>}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Edit Reservation' : 'New Reservation'}>
                <form onSubmit={handleSave} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input value={form.customer_name} onChange={(e) => setForm({ ...form, customer_name: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Party Size *</label>
                            <input type="number" min="1" value={form.party_size} onChange={(e) => setForm({ ...form, party_size: parseInt(e.target.value) || 1 })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input value={form.customer_phone} onChange={(e) => setForm({ ...form, customer_phone: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" value={form.customer_email} onChange={(e) => setForm({ ...form, customer_email: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                            <input type="date" value={form.date} onChange={(e) => setForm({ ...form, date: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Time *</label>
                            <select value={form.time_slot} onChange={(e) => setForm({ ...form, time_slot: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                {timeSlots.map((t) => <option key={t} value={t}>{t}</option>)}
                            </select>
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Table</label>
                        <select value={form.table_id} onChange={(e) => setForm({ ...form, table_id: parseInt(e.target.value) || '' })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">Auto-assign</option>
                            {tables.map((t) => <option key={t.id} value={t.id}>Table #{t.table_number} ({t.capacity} seats)</option>)}
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} rows={2} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={() => setModalOpen(false)} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                        <button type="submit" disabled={saving} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                            {saving ? 'Saving...' : editing ? 'Update' : 'Create'}
                        </button>
                    </div>
                </form>
            </Modal>

            <Modal open={walkInOpen} onClose={() => setWalkInOpen(false)} title="Walk-in Booking">
                <form onSubmit={handleWalkIn} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input value={walkForm.customer_name} onChange={(e) => setWalkForm({ ...walkForm, customer_name: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Party Size *</label>
                            <input type="number" min="1" value={walkForm.party_size} onChange={(e) => setWalkForm({ ...walkForm, party_size: parseInt(e.target.value) || 1 })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Time</label>
                            <select value={walkForm.time_slot} onChange={(e) => setWalkForm({ ...walkForm, time_slot: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                {timeSlots.map((t) => <option key={t} value={t}>{t}</option>)}
                            </select>
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Table</label>
                        <select value={walkForm.table_id} onChange={(e) => setWalkForm({ ...walkForm, table_id: parseInt(e.target.value) || '' })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">Auto-assign</option>
                            {tables.map((t) => <option key={t.id} value={t.id}>Table #{t.table_number} ({t.capacity} seats)</option>)}
                        </select>
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={() => setWalkInOpen(false)} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                        <button type="submit" disabled={saving} className="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50">
                            {saving ? 'Saving...' : 'Book Walk-in'}
                        </button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
