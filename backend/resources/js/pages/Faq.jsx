import { useState, useEffect } from 'react';
import client from '../api/client';

export default function Faq() {
    const [faqs, setFaqs] = useState([]);
    const [openId, setOpenId] = useState(null);
    const [categories, setCategories] = useState([]);
    const [activeCategory, setActiveCategory] = useState('');

    useEffect(() => {
        client.get('/support/faq').then(({ data }) => {
            setFaqs(data);
            const cats = [...new Set(data.map((f) => f.category).filter(Boolean))];
            setCategories(cats);
        });
    }, []);

    const filtered = activeCategory ? faqs.filter((f) => f.category === activeCategory) : faqs;

    return (
        <div>
            <h2 className="text-2xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>

            {categories.length > 0 && (
                <div className="flex gap-2 mb-4 flex-wrap">
                    <button onClick={() => setActiveCategory('')}
                        className={`px-3 py-1.5 rounded-lg text-sm font-medium border ${!activeCategory ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:bg-gray-50'}`}>All</button>
                    {categories.map((c) => (
                        <button key={c} onClick={() => setActiveCategory(c)}
                            className={`px-3 py-1.5 rounded-lg text-sm font-medium border ${activeCategory === c ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:bg-gray-50'}`}>{c}</button>
                    ))}
                </div>
            )}

            <div className="space-y-2">
                {filtered.map((faq) => (
                    <div key={faq.id} className="bg-white rounded-lg border overflow-hidden">
                        <button onClick={() => setOpenId(openId === faq.id ? null : faq.id)}
                            className="w-full px-4 py-3 text-left flex items-center justify-between hover:bg-gray-50">
                            <span className="font-medium text-gray-800">{faq.question}</span>
                            <svg className={`w-5 h-5 text-gray-400 transition-transform ${openId === faq.id ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        {openId === faq.id && (
                            <div className="px-4 pb-3 text-sm text-gray-600 whitespace-pre-wrap border-t pt-3">{faq.answer}</div>
                        )}
                    </div>
                ))}
                {filtered.length === 0 && <p className="text-gray-500 text-center py-8">No FAQs found.</p>}
            </div>
        </div>
    );
}
