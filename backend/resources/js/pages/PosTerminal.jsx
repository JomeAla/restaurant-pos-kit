import { useState, useEffect } from 'react';
import client from '../api/client';
import { formatCurrency, setCurrency } from '../utils/currency';
import Modal from '../components/Modal';

export default function PosTerminal() {
    const [categories, setCategories] = useState([]);
    const [menuItems, setMenuItems] = useState([]);
    const [activeCategory, setActiveCategory] = useState(null);
    const [tables, setTables] = useState([]);
    const [cart, setCart] = useState([]);
    const [orderType, setOrderType] = useState('dine-in');
    const [selectedTable, setSelectedTable] = useState(null);
    const [customerName, setCustomerName] = useState('');
    const [customerPhone, setCustomerPhone] = useState('');
    const [notes, setNotes] = useState('');
    const [showTableSelect, setShowTableSelect] = useState(false);
    const [showModifierModal, setShowModifierModal] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [cartItemModifiers, setCartItemModifiers] = useState([]);
    const [cartItemQty, setCartItemQty] = useState(1);
    const [submitting, setSubmitting] = useState(false);
    const [currentOrder, setCurrentOrder] = useState(null);
    const [couponCode, setCouponCode] = useState('');
    const [couponData, setCouponData] = useState(null);
    const [couponError, setCouponError] = useState('');
    const [validatingCoupon, setValidatingCoupon] = useState(false);

    const load = () => {
        client.get('/categories').then(({ data }) => {
            setCategories(data);
            if (data.length > 0) setActiveCategory((prev) => prev || data[0].id);
        });
        client.get('/menu-items?per_page=200').then(({ data }) => {
            const items = data.data ?? data;
            setMenuItems(items);
        });
        client.get('/tables').then(({ data }) => setTables(data));
    };
    useEffect(() => { load(); }, []);
    useEffect(() => {
        client.get('/settings').then(({ data }) => {
            const currency = data.restaurant?.[0]?.value || 'USD';
            setCurrency(currency);
        }).catch(() => {});
    }, []);

    const filteredItems = menuItems.filter((i) => i.category_id === activeCategory && i.is_available);

    const addToCart = (item, qty, modifiers) => {
        const modPrice = modifiers.reduce((s, m) => s + parseFloat(m.price_adjustment || 0), 0);
        const unitPrice = parseFloat(item.price) + modPrice;
        const summary = modifiers.map((m) => ({ modifier_id: m.modifier_id, modifier_name: m.modifier_name, option_name: m.option_name, price_adjustment: m.price_adjustment }));
        setCart((prev) => [...prev, { id: Date.now() + Math.random(), menu_item_id: item.id, name: item.name, unit_price: unitPrice, quantity: qty, total_price: unitPrice * qty, modifier_summary: summary.length > 0 ? summary : null, notes: '', course: null }]);
        setShowModifierModal(false);
        setSelectedItem(null);
    };

    const openModifierModal = (item) => {
        setSelectedItem(item);
        setCartItemModifiers([]);
        setCartItemQty(1);
        setShowModifierModal(true);
    };

    const toggleModifierOption = (modifier, option) => {
        if (modifier.type === 'single') {
            setCartItemModifiers((prev) => [...prev.filter((m) => m.modifier_id !== modifier.id), { modifier_id: modifier.id, modifier_name: modifier.name, option_name: option.name, price_adjustment: option.price_adjustment }]);
        } else {
            setCartItemModifiers((prev) => {
                const exists = prev.find((m) => m.modifier_id === modifier.id && m.option_name === option.name);
                if (exists) return prev.filter((m) => !(m.modifier_id === modifier.id && m.option_name === option.name));
                return [...prev, { modifier_id: modifier.id, modifier_name: modifier.name, option_name: option.name, price_adjustment: option.price_adjustment }];
            });
        }
    };

    const isModifierSelected = (modifierId, optionName) => cartItemModifiers.some((m) => m.modifier_id === modifierId && m.option_name === optionName);

    const removeFromCart = (id) => setCart((prev) => prev.filter((i) => i.id !== id));

    const updateQty = (id, delta) => setCart((prev) => prev.map((i) => i.id === id ? { ...i, quantity: Math.max(1, i.quantity + delta), total_price: i.unit_price * Math.max(1, i.quantity + delta) } : i));

    const setItemCourse = (id, course) => setCart((prev) => prev.map((i) => i.id === id ? { ...i, course } : i));

    const subtotal = cart.reduce((s, i) => s + i.total_price, 0);
    const discount = couponData?.valid ? couponData.discount : 0;
    const totalAfterDiscount = subtotal - discount;

    const handleApplyCoupon = async () => {
        if (!couponCode.trim() || subtotal <= 0) return;
        setValidatingCoupon(true);
        setCouponError('');
        setCouponData(null);
        try {
            const { data } = await client.post('/coupons/validate', { code: couponCode.trim(), order_amount: subtotal });
            setCouponData(data);
        } catch (err) {
            setCouponError(err.response?.data?.message || 'Invalid coupon');
            setCouponData(null);
        } finally {
            setValidatingCoupon(false);
        }
    };

    const handleSubmit = async () => {
        if (cart.length === 0) return;
        setSubmitting(true);
        try {
            const { data } = await client.post('/orders', {
                type: orderType,
                table_id: orderType === 'dine-in' ? selectedTable?.id || selectedTable : null,
                customer_name: customerName || null,
                customer_phone: customerPhone || null,
                notes: notes || null,
                coupon_id: couponData?.coupon?.id || null,
                coupon_code: couponData?.coupon?.code || null,
                discount_total: discount,
                items: cart.map((i) => ({ menu_item_id: i.menu_item_id, quantity: i.quantity, unit_price: i.unit_price, modifier_summary: i.modifier_summary, notes: i.notes || null, course: i.course || null })),
            });
            setCurrentOrder(data);
            setCart([]);
            setSelectedTable(null);
            setCustomerName('');
            setCustomerPhone('');
            setNotes('');
            setCouponCode('');
            setCouponData(null);
            setCouponError('');
        } catch (err) { alert(err.response?.data?.message || 'Error creating order'); } finally { setSubmitting(false); }
    };

    const handleSendKitchen = async () => {
        if (!currentOrder) return;
        try { await client.post(`/orders/${currentOrder.id}/send-kitchen`); alert('Order sent to kitchen!'); setCurrentOrder(null); }
        catch (err) { alert(err.response?.data?.message || 'Error'); }
    };

    if (currentOrder) {
        return (
            <div className="max-w-lg mx-auto text-center py-12">
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                    <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h2 className="text-xl font-bold text-gray-800 mb-2">Order Created</h2>
                    <p className="text-3xl font-bold text-indigo-600 mb-2">{currentOrder.order_number}</p>
                    <p className="text-gray-500 mb-6">Total: {formatCurrency(currentOrder.total)}</p>
                    <div className="flex gap-3 justify-center">
                        <button onClick={() => { setCurrentOrder(null); load(); }} className="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">New Order</button>
                        <button onClick={handleSendKitchen} className="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Send to Kitchen</button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="flex flex-col lg:flex-row gap-4 h-full">
            <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
                    {categories.map((cat) => (
                        <button key={cat.id} onClick={() => setActiveCategory(cat.id)} className={`px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors ${activeCategory === cat.id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'}`}>
                            {cat.name}
                        </button>
                    ))}
                </div>
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    {filteredItems.map((item) => (
                        <button key={item.id} onClick={() => item.modifiers?.length > 0 ? openModifierModal(item) : addToCart(item, 1, [])} className="bg-white rounded-xl shadow-sm border border-gray-100 p-3 text-left hover:border-indigo-300 hover:shadow transition-all">
                            {item.image && <img src={item.image} alt={item.name} className="w-full h-24 object-cover rounded-lg mb-2" />}
                            <p className="font-medium text-gray-800 text-sm truncate">{item.name}</p>
                            <p className="text-indigo-600 font-bold text-sm mt-1">{formatCurrency(item.price)}</p>
                        </button>
                    ))}
                    {filteredItems.length === 0 && (
                        <p className="col-span-full text-gray-400 text-center py-8 text-sm">No items in this category</p>
                    )}
                </div>
            </div>

            <div className="w-full lg:w-96 flex-shrink-0">
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col h-full max-h-[calc(100vh-12rem)]">
                    <div className="flex gap-2 mb-3">
                        {['dine-in', 'takeaway', 'delivery'].map((t) => (
                            <button key={t} onClick={() => { setOrderType(t); if (t !== 'dine-in') setSelectedTable(null); }} className={`flex-1 py-2 rounded-lg text-xs font-medium capitalize transition-colors ${orderType === t ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}>
                                {t}
                            </button>
                        ))}
                    </div>

                    {orderType === 'dine-in' && (
                        <button onClick={() => setShowTableSelect(true)} className="mb-3 px-3 py-2 border border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-indigo-400 hover:text-indigo-600 transition-colors">
                            {selectedTable ? `Table #${selectedTable.table_number || selectedTable} (${selectedTable.capacity || ''} seats)` : '+ Select Table'}
                        </button>
                    )}

                    {(orderType === 'takeaway' || orderType === 'delivery') && (
                        <div className="space-y-2 mb-3">
                            <input value={customerName} onChange={(e) => setCustomerName(e.target.value)} placeholder="Customer name" className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                            <input value={customerPhone} onChange={(e) => setCustomerPhone(e.target.value)} placeholder="Phone (optional)" className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                    )}

                    <div className="flex-1 overflow-y-auto space-y-2 min-h-0 mb-3">
                        {cart.length === 0 ? (
                            <p className="text-gray-400 text-sm text-center py-6">Cart is empty. Tap items to add them.</p>
                        ) : cart.map((item) => (
                            <div key={item.id} className="bg-gray-50 rounded-lg p-3">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-medium text-gray-800 truncate">{item.name}</p>
                                        {item.modifier_summary && (
                                            <p className="text-xs text-gray-500 truncate">{item.modifier_summary.map((m) => m.option_name).join(', ')}</p>
                                        )}
                                        <div className="flex items-center gap-2 mt-1">
                                            <button onClick={() => updateQty(item.id, -1)} className="w-6 h-6 rounded bg-white border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-100">-</button>
                                            <span className="text-sm font-medium text-gray-700">{item.quantity}</span>
                                            <button onClick={() => updateQty(item.id, 1)} className="w-6 h-6 rounded bg-white border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-100">+</button>
                                        </div>
                                    </div>
                                    <div className="text-right flex flex-col items-end gap-1">
                                        <p className="text-sm font-bold text-gray-800">{formatCurrency(item.total_price)}</p>
                                        <select value={item.course || ''} onChange={(e) => setItemCourse(item.id, e.target.value || null)} className="text-xs border border-gray-200 rounded px-1 py-0.5 outline-none">
                                            <option value="">Course</option>
                                            <option value="starter">Starter</option>
                                            <option value="main">Main</option>
                                            <option value="dessert">Dessert</option>
                                            <option value="drink">Drink</option>
                                        </select>
                                        <button onClick={() => removeFromCart(item.id)} className="text-xs text-red-500 hover:text-red-700">Remove</button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="border-t border-gray-200 pt-3 space-y-2">
                        <input value={notes} onChange={(e) => setNotes(e.target.value)} placeholder="Order notes..." className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                        <div className="flex gap-2">
                            <input value={couponCode} onChange={(e) => { setCouponCode(e.target.value); setCouponData(null); setCouponError(''); }} placeholder="Coupon code" className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                            <button onClick={handleApplyCoupon} disabled={!couponCode.trim() || validatingCoupon || cart.length === 0} className="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                {validatingCoupon ? '...' : 'Apply'}
                            </button>
                        </div>
                        {couponError && <p className="text-red-500 text-xs">{couponError}</p>}
                        {couponData?.valid && <p className="text-green-600 text-xs">Coupon applied! Discount: -{formatCurrency(discount)}</p>}
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-gray-600">Subtotal ({cart.length} items)</span>
                            <span className="text-lg font-bold text-gray-800">{formatCurrency(subtotal)}</span>
                        </div>
                        {discount > 0 && (
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-green-600">Discount</span>
                                <span className="text-sm font-bold text-green-600">-{formatCurrency(discount)}</span>
                            </div>
                        )}
                        <button onClick={handleSubmit} disabled={cart.length === 0 || submitting} className="w-full py-3 bg-indigo-600 text-white font-semibold rounded-lg text-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            {submitting ? 'Creating Order...' : `Place Order — ${formatCurrency(totalAfterDiscount)}`}
                        </button>
                    </div>
                </div>
            </div>

            <Modal open={showModifierModal} onClose={() => setShowModifierModal(false)} title={`Customize - ${selectedItem?.name || ''}`}>
                {selectedItem?.modifiers?.map((mod) => (
                    <div key={mod.id} className="mb-4">
                        <p className="text-sm font-semibold text-gray-700 mb-2">{mod.name}{mod.is_required ? ' *' : ''}</p>
                        <div className="space-y-1">
                            {mod.options?.filter((o) => o.is_active).map((opt) => (
                                <label key={opt.id} className={`flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors ${isModifierSelected(mod.id, opt.name) ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:bg-gray-50'}`}>
                                    <input type={mod.type === 'single' ? 'radio' : 'checkbox'} name={`mod_${mod.id}`} checked={isModifierSelected(mod.id, opt.name)} onChange={() => toggleModifierOption(mod, opt)} className="text-indigo-600 focus:ring-indigo-500" />
                                    <span className="text-sm text-gray-700 flex-1">{opt.name}</span>
                                    {parseFloat(opt.price_adjustment) > 0 && <span className="text-xs text-indigo-600 font-medium">+{formatCurrency(opt.price_adjustment)}</span>}
                                </label>
                            ))}
                        </div>
                    </div>
                ))}
                <div className="flex items-center gap-3 mb-4">
                    <span className="text-sm text-gray-600">Quantity:</span>
                    <button onClick={() => setCartItemQty(Math.max(1, cartItemQty - 1))} className="w-8 h-8 rounded bg-gray-100 border text-gray-600 text-sm font-bold">-</button>
                    <span className="text-sm font-bold text-gray-800">{cartItemQty}</span>
                    <button onClick={() => setCartItemQty(cartItemQty + 1)} className="w-8 h-8 rounded bg-gray-100 border text-gray-600 text-sm font-bold">+</button>
                </div>
                <button onClick={() => addToCart(selectedItem, cartItemQty, cartItemModifiers)} className="w-full py-3 bg-indigo-600 text-white font-semibold rounded-lg text-sm hover:bg-indigo-700">
                    Add to Order — {formatCurrency((parseFloat(selectedItem?.price || 0) + cartItemModifiers.reduce((s, m) => s + parseFloat(m.price_adjustment || 0), 0)) * cartItemQty)}
                </button>
            </Modal>

            <Modal open={showTableSelect} onClose={() => setShowTableSelect(false)} title="Select Table">
                {tables.length === 0 ? (
                    <p className="text-gray-500 text-sm text-center py-4">No tables available</p>
                ) : (
                    <div className="grid grid-cols-4 gap-3">
                        {tables.filter((t) => t.status === 'free' || t.status === 'dirty').map((table) => (
                            <button key={table.id} onClick={() => { setSelectedTable(table); setShowTableSelect(false); }} className={`aspect-square rounded-xl border-2 flex flex-col items-center justify-center transition-colors ${selectedTable?.id === table.id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300'}`}>
                                <span className="text-lg font-bold text-gray-800">{table.table_number}</span>
                                <span className="text-xs text-gray-500">{table.capacity}</span>
                            </button>
                        ))}
                    </div>
                )}
            </Modal>
        </div>
    );
}
