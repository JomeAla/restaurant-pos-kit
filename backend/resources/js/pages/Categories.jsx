import { useState, useEffect } from 'react';
import client from '../api/client';

export default function Categories() {
    const [categories, setCategories] = useState([]);

    const load = () => client.get('/categories').then(({ data }) => setCategories(data));
    useEffect(() => { load(); }, []);

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-800">Categories</h2>
            </div>
            <div className="bg-white rounded-xl shadow-sm border border-gray-100">
                <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="border-b border-gray-100 bg-gray-50">
                            <th className="text-left p-4 font-medium text-gray-600">Name</th>
                            <th className="text-left p-4 font-medium text-gray-600">Slug</th>
                            <th className="text-left p-4 font-medium text-gray-600">Items</th>
                            <th className="text-left p-4 font-medium text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {categories.length === 0 ? (
                            <tr><td colSpan={4} className="p-4 text-gray-500 text-center">No categories yet</td></tr>
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
                            </tr>
                        ))}
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    );
}
