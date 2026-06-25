import { useState, useEffect, useRef } from 'react';
import client from '../api/client';

const COLUMNS = [
    { key: 'pending', label: 'Pending', color: 'border-l-yellow-400 bg-yellow-50' },
    { key: 'preparing', label: 'Preparing', color: 'border-l-blue-400 bg-blue-50' },
    { key: 'ready', label: 'Ready', color: 'border-l-green-400 bg-green-50' },
];

export default function KDS() {
    const [tickets, setTickets] = useState([]);
    const [previousCount, setPreviousCount] = useState(0);
    const audioRef = useRef(null);

    const load = async () => {
        try {
            const { data } = await client.get('/kitchen/orders');
            if (data.length > previousCount && previousCount > 0) {
                audioRef.current?.play().catch(() => {});
            }
            setPreviousCount(data.length);
            setTickets(data);
        } catch { /* ignore */ }
    };

    useEffect(() => {
        load();
        const interval = setInterval(load, 5000);
        return () => clearInterval(interval);
    }, [previousCount]);

    useEffect(() => {
        audioRef.current = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACAf39/f4B/f3+AgH9/f3+Af3+AgH9/f3+Af39/gIB/f39/gH9/f4B/f3+Af39/gH+AgH9/f39/gH9/gH9/gH+AgH9/f39/gH+Af39/gH9/gH9/f3+Af3+AgH9/f39/gH+Af39/gH9/gH+AgH+AgH+Af39/gH+Af39/gH9/gH+Af39/gH+Af39/gH+AgH+Af39/gH+Af39/gH+AgH+Af39/gH+Af39/gH+Af4B/f3+Af4B/f3+Af39/gH+Af39/gH+Af39/gH+Af4B/f3+Af4B/f3+Af4B/f3+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af4B/gH9/gH+Af4B/gH9/gH+Af4B/gH9/gH+Af4B/gH9/gH+Af4B/gH+Af39/gH+Af39/gH+Af4B/f3+Af4B/f3+Af39/gH+Af39/gH+Af4B/gH9/gH+Af4B/gH9/gH+Af4B/gH9/gH+Af4B/gH9/gH+Af4B/gH+Af39/gH+Af39/gH+Af4B/f3+Af4B/f3+Af4B/f3+Af4B/f3+Af4B/gH9/gH+Af4B/gH9/gH+Af4B/gH+Af39/gH+Af39/gH+Af4B/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af4B/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39/gH+Af39');
    }, []);

    const updateStatus = async (ticketId, itemId, status) => {
        try {
            await client.put(`/kitchen/tickets/${ticketId}/items/${itemId}/status`, { status });
            await load();
        } catch (err) { alert(err.response?.data?.message || 'Error'); }
    };

    const bumpTicket = async (ticketId) => {
        try {
            await client.post(`/kitchen/tickets/${ticketId}/bump`);
            await load();
        } catch (err) { alert(err.response?.data?.message || 'Error'); }
    };

    const grouped = COLUMNS.map((col) => ({
        ...col,
        tickets: tickets.filter((t) => t.status === col.key),
    }));

    const allBumped = tickets.length === 0;

    if (allBumped) {
        return (
            <div className="flex items-center justify-center h-[calc(100vh-12rem)]">
                <div className="text-center">
                    <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-10 h-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h2 className="text-2xl font-bold text-gray-700 mb-2">All Clear</h2>
                    <p className="text-gray-500">No pending tickets. Waiting for new orders...</p>
                </div>
            </div>
        );
    }

    return (
        <div className="h-[calc(100vh-12rem)] -m-6 p-6">
            <div className="grid grid-cols-3 gap-4 h-full">
                {grouped.map((column) => (
                    <div key={column.key} className="flex flex-col h-full">
                        <div className={`flex items-center justify-between mb-3 px-4 py-2 rounded-lg border-l-4 ${column.color} bg-white shadow-sm`}>
                            <h3 className="font-semibold text-gray-700">{column.label}</h3>
                            <span className="text-sm font-bold text-gray-500">{column.tickets.length}</span>
                        </div>
                        <div className="flex-1 overflow-y-auto space-y-3 pr-1">
                            {column.tickets.length === 0 ? (
                                <div className="flex items-center justify-center h-32 text-gray-400 text-sm border-2 border-dashed border-gray-200 rounded-xl">No tickets</div>
                            ) : column.tickets.map((ticket) => (
                                <div key={ticket.id} className="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                    <div className="flex items-start justify-between mb-3">
                                        <div>
                                            <p className="font-bold text-lg text-gray-800">{ticket.order?.order_number || 'N/A'}</p>
                                            {ticket.order?.table && <p className="text-sm text-gray-500">Table #{ticket.order.table.table_number}</p>}
                                        </div>
                                        <div className="text-right">
                                            {ticket.course && <span className="px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-medium">{ticket.course}</span>}
                                            <p className="text-xs text-gray-400 mt-1">{getElapsed(ticket.sent_at)}</p>
                                        </div>
                                    </div>
                                    <div className="space-y-1">
                                        {ticket.items?.map((item) => (
                                            <div key={item.id} className="flex items-center justify-between py-1.5 border-b border-gray-50 last:border-0">
                                                <div className="flex items-center gap-2">
                                                    <span className={`w-2 h-2 rounded-full ${item.status === 'ready' ? 'bg-green-500' : item.status === 'preparing' ? 'bg-blue-500' : 'bg-yellow-400'}`} />
                                                    <span className="text-sm text-gray-700">{item.order_item?.menu_item?.name || 'Unknown'}</span>
                                                    <span className="text-xs text-gray-400">x{item.order_item?.quantity || 1}</span>
                                                </div>
                                                <div className="flex gap-1">
                                                    {column.key === 'pending' && (
                                                        <button onClick={() => updateStatus(ticket.id, item.id, 'preparing')} className="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700 hover:bg-blue-200 font-medium">Prep</button>
                                                    )}
                                                    {column.key === 'preparing' && (
                                                        <button onClick={() => updateStatus(ticket.id, item.id, 'ready')} className="px-2 py-1 text-xs rounded bg-green-100 text-green-700 hover:bg-green-200 font-medium">Done</button>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                    {column.key === 'ready' && (
                                        <button onClick={() => bumpTicket(ticket.id)} className="mt-3 w-full py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">Bump Ticket</button>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function getElapsed(dateStr) {
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    const hrs = Math.floor(mins / 60);
    return `${hrs}h ${mins % 60}m ago`;
}
