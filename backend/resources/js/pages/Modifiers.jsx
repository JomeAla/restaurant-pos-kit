import { useState, useEffect } from 'react';
import client from '../api/client';
import { formatCurrency, setCurrency as setCurr } from '../utils/currency';

export default function Modifiers() {
    const [modifiers, setModifiers] = useState([]);

    const load = () => {
        client.get('/modifiers').then(({ data }) => setModifiers(data));
        client.get('/settings').then(({ data }) => { const c = data.restaurant?.currency || 'USD'; setCurr(c); }).catch(() => {});
    };
    useEffect(() => { load(); }, []);

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-800">Modifiers</h2>
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
                        </div>
                        {mod.options?.length > 0 ? (
                            <div className="border-t border-gray-100 pt-2 space-y-1">
                                {mod.options.map((opt) => (
                                    <div key={opt.id} className="flex items-center justify-between text-sm pl-4 py-1.5 rounded group">
                                        <div className="flex items-center gap-2">
                                            <span className="text-gray-600">{opt.name}</span>
                                            {opt.is_default && <span className="text-xs text-indigo-500 font-medium">default</span>}
                                        </div>
                                        <span className="text-gray-500">{parseFloat(opt.price_adjustment) > 0 ? `+${formatCurrency(opt.price_adjustment)}` : 'Free'}</span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-gray-400 pl-4">No options</p>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}
