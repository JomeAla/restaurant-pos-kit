import { useState, useEffect } from 'react';
import client from '../api/client';
import { formatCurrency, setCurrency as setCurr } from '../utils/currency';

export default function Combos() {
    const [combos, setCombos] = useState([]);

    useEffect(() => {
        client.get('/combos').then(({ data }) => setCombos(data.data ?? data)).catch(() => {});
        client.get('/settings').then(({ data }) => { const c = data.restaurant?.currency || 'USD'; setCurr(c); }).catch(() => {});
    }, []);

    return (
        <div>
            <h2 className="text-2xl font-bold text-gray-800 mb-6">Combos</h2>
            <div className="space-y-4">
                {combos.length === 0 ? (
                    <p className="text-gray-500">No combos yet.</p>
                ) : combos.map((combo) => (
                    <div key={combo.id} className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="font-semibold text-gray-800">{combo.name}</h3>
                                <p className="text-sm text-gray-500">{combo.description}</p>
                            </div>
                            <span className="text-lg font-bold text-indigo-600">{formatCurrency(combo.price)}</span>
                        </div>
                        {combo.items?.length > 0 && (
                            <div className="mt-3 pl-4 border-l-2 border-indigo-200 space-y-1">
                                {combo.items.map((ci) => (
                                    <p key={ci.id} className="text-sm text-gray-600">
                                        {ci.quantity}x {ci.menu_item?.name ?? `Item #${ci.menu_item_id}`}
                                    </p>
                                ))}
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}
