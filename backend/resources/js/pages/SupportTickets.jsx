import { useState, useEffect } from 'react';
import client from '../api/client';
import Modal from '../components/Modal';

const statusColors = {
    open: 'bg-blue-100 text-blue-700 border-blue-200',
    assigned: 'bg-purple-100 text-purple-700 border-purple-200',
    in_progress: 'bg-yellow-100 text-yellow-700 border-yellow-200',
    resolved: 'bg-green-100 text-green-700 border-green-200',
    closed: 'bg-gray-100 text-gray-700 border-gray-200',
};

const priorityColors = {
    low: 'bg-gray-100 text-gray-600',
    medium: 'bg-blue-100 text-blue-600',
    high: 'bg-orange-100 text-orange-600',
    urgent: 'bg-red-100 text-red-600',
};

const tabs = ['all', 'open', 'in_progress', 'resolved', 'closed'];

export default function SupportTickets() {
    const [tickets, setTickets] = useState([]);
    const [activeTab, setActiveTab] = useState('all');
    const [selected, setSelected] = useState(null);
    const [messages, setMessages] = useState([]);
    const [newMsg, setNewMsg] = useState('');
    const [sending, setSending] = useState(false);
    const [createOpen, setCreateOpen] = useState(false);
    const [form, setForm] = useState({ subject: '', category: '', priority: 'medium', message: '' });
    const [creating, setCreating] = useState(false);

    const load = () => {
        const params = activeTab === 'all' ? {} : { status: activeTab };
        client.get('/support/tickets', { params }).then(({ data }) => setTickets(data));
    };

    useEffect(() => { load(); }, [activeTab]);

    const openDetail = async (ticket) => {
        setSelected(ticket);
        const { data } = await client.get(`/support/tickets/${ticket.id}`);
        setMessages(data.messages ?? []);
    };

    const sendMessage = async () => {
        if (!newMsg.trim()) return;
        setSending(true);
        try {
            const { data } = await client.post(`/support/tickets/${selected.id}/messages`, { message: newMsg });
            setMessages([...messages, data]);
            setNewMsg('');
            load();
        } catch (err) { alert(err.response?.data?.message || 'Error sending message'); }
        finally { setSending(false); }
    };

    const changeStatus = async (status) => {
        try {
            await client.put(`/support/tickets/${selected.id}/status`, { status });
            const { data } = await client.get(`/support/tickets/${selected.id}`);
            setMessages(data.messages ?? []);
            setSelected(data);
            load();
        } catch (err) { alert(err.response?.data?.message || 'Error updating status'); }
    };

    const createTicket = async (e) => {
        e.preventDefault();
        if (!form.subject.trim() || !form.message.trim()) return;
        setCreating(true);
        try {
            const { data } = await client.post('/support/tickets', form);
            setCreateOpen(false);
            setForm({ subject: '', category: '', priority: 'medium', message: '' });
            setSelected(data);
            setMessages(data.messages ?? []);
            load();
        } catch (err) { alert(err.response?.data?.message || 'Error creating ticket'); }
        finally { setCreating(false); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-4">
                <h2 className="text-2xl font-bold text-gray-800">Support Tickets</h2>
                <button onClick={() => setCreateOpen(true)} className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">New Ticket</button>
            </div>

            <div className="flex gap-2 mb-4 border-b">
                {tabs.map((tab) => (
                    <button key={tab} onClick={() => setActiveTab(tab)}
                        className={`px-4 py-2 text-sm font-medium capitalize border-b-2 -mb-px ${activeTab === tab ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}>{tab.replace('_', ' ')}</button>
                ))}
            </div>

            {!selected ? (
                <div className="space-y-2">
                    {tickets.map((t) => (
                        <div key={t.id} onClick={() => openDetail(t)} className="p-4 bg-white rounded-lg border hover:shadow cursor-pointer flex items-center justify-between">
                            <div className="flex-1 min-w-0">
                                <p className="font-medium text-gray-800 truncate">{t.subject}</p>
                                <p className="text-sm text-gray-500">{t.category && `${t.category} · `}{new Date(t.created_at).toLocaleDateString()}</p>
                            </div>
                            <div className="flex items-center gap-2 ml-4">
                                <span className={`px-2 py-0.5 rounded text-xs font-medium ${priorityColors[t.priority] || 'bg-gray-100 text-gray-600'}`}>{t.priority}</span>
                                <span className={`px-2 py-0.5 rounded text-xs font-medium ${statusColors[t.status] || 'bg-gray-100 text-gray-600'}`}>{t.status.replace('_', ' ')}</span>
                            </div>
                        </div>
                    ))}
                    {tickets.length === 0 && <p className="text-gray-500 text-center py-8">No tickets found.</p>}
                </div>
            ) : (
                <div>
                    <button onClick={() => setSelected(null)} className="text-sm text-blue-600 hover:underline mb-3">&larr; Back to tickets</button>
                    <div className="bg-white rounded-lg border p-4 mb-4">
                        <div className="flex items-start justify-between mb-2">
                            <div>
                                <h3 className="text-lg font-semibold text-gray-800">{selected.subject}</h3>
                                <p className="text-sm text-gray-500">{selected.category && `${selected.category} · `}Created {new Date(selected.created_at).toLocaleDateString()}</p>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className={`px-2 py-0.5 rounded text-xs font-medium ${priorityColors[selected.priority]}`}>{selected.priority}</span>
                                <span className={`px-2 py-0.5 rounded text-xs font-medium ${statusColors[selected.status]}`}>{selected.status.replace('_', ' ')}</span>
                            </div>
                        </div>
                        <div className="flex gap-2 mt-3">
                            {['open', 'assigned', 'in_progress', 'resolved', 'closed'].map((s) => (
                                <button key={s} onClick={() => changeStatus(s)} disabled={s === selected.status}
                                    className={`px-3 py-1 text-xs rounded border ${s === selected.status ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-50 text-gray-600'}`}>{s.replace('_', ' ')}</button>
                            ))}
                        </div>
                    </div>

                    <div className="space-y-3 mb-4">
                        {messages.map((m) => (
                            <div key={m.id} className={`p-3 rounded-lg border ${m.is_staff_reply ? 'bg-blue-50 border-blue-200 ml-6' : 'bg-white'}`}>
                                <div className="flex items-center justify-between mb-1">
                                    <span className="text-sm font-medium text-gray-700">{m.user?.name || 'User'}</span>
                                    <span className="text-xs text-gray-400">{new Date(m.created_at).toLocaleString()}</span>
                                </div>
                                <p className="text-sm text-gray-700 whitespace-pre-wrap">{m.message}</p>
                                {m.attachments?.length > 0 && (
                                    <div className="mt-2 flex gap-2">
                                        {m.attachments.map((a, i) => <span key={i} className="text-xs text-blue-600 underline">{a}</span>)}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>

                    <div className="flex gap-2">
                        <textarea value={newMsg} onChange={(e) => setNewMsg(e.target.value)} placeholder="Type your reply..." rows={3}
                            className="flex-1 border rounded-lg p-3 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        <button onClick={sendMessage} disabled={sending || !newMsg.trim()}
                            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium self-end disabled:opacity-50">{sending ? 'Sending...' : 'Send'}</button>
                    </div>
                </div>
            )}

            <Modal open={createOpen} onClose={() => setCreateOpen(false)} title="New Support Ticket">
                <form onSubmit={createTicket} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input value={form.subject} onChange={(e) => setForm({ ...form, subject: e.target.value })} required
                            className="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <input value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })} placeholder="e.g. Billing, Technical"
                            className="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select value={form.priority} onChange={(e) => setForm({ ...form, priority: e.target.value })}
                            className="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea value={form.message} onChange={(e) => setForm({ ...form, message: e.target.value })} required rows={4}
                            className="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <button type="submit" disabled={creating}
                        className="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium disabled:opacity-50">{creating ? 'Creating...' : 'Create Ticket'}</button>
                </form>
            </Modal>
        </div>
    );
}
