import { useState, useEffect } from 'react';
import client from '../api/client';
import { formatCurrency, setCurrency as setCurr } from '../utils/currency';

export default function MenuItems() {
    const [items, setItems] = useState([]);
    const [categories, setCategories] = useState([]);
    const [toggling, setToggling] = useState(null);

    const load = () => {
        client.get('/menu-items').then(({ data }) => setItems(data.data ?? data));
        client.get('/categories').then(({ data }) => setCategories(data));
        client.get('/settings').then(({ data }) => { const c = data.restaurant?.currency || 'USD'; setCurr(c); }).catch(() => {});
    };
    useEffect(() => { load(); }, []);

    const toggleAvailable = async (item) => {
        setToggling(item.id);
        try {
            const { data } = await client.put(`/menu-items/${item.id}/toggle-availability`);
            setItems((prev) => prev.map((i) => i.id === item.id ? { ...i, is_available: data.is_available } : i));
        } catch { /* ignore */ } finally { setToggling(null); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-800">Menu Items</h2>
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
                        </tr>
                    </thead>
                    <tbody>
                        {items.length === 0 ? (
                            <tr><td colSpan={4} className="p-4 text-gray-500 text-center">No menu items yet</td></tr>
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
                            </tr>
                        ))}
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    );
}
