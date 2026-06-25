import { useState, useEffect } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, CardElement, useStripe, useElements } from '@stripe/react-stripe-js';
import client from '../api/client';
import { formatCurrency, setCurrency } from '../utils/currency';

function StripeCheckoutForm({ amount, orderId, onSuccess, onError, setSubmitting }) {
    const stripe = useStripe();
    const elements = useElements();

    const handleStripePay = async (e) => {
        e.preventDefault();
        if (!stripe || !elements) return;
        setSubmitting(true);
        try {
            const { data } = await client.post('/payments/create-intent', {
                order_id: orderId,
                amount,
            });

            const { error, paymentIntent } = await stripe.confirmCardPayment(data.client_secret, {
                payment_method: { card: elements.getElement(CardElement) },
            });

            if (error) {
                onError(error.message);
                setSubmitting(false);
            } else if (paymentIntent.status === 'succeeded') {
                const { data: paymentData } = await client.post('/payments', {
                    order_id: orderId,
                    amount,
                    method: 'card',
                    reference: paymentIntent.id,
                });
                onSuccess(paymentData);
            }
        } catch (err) {
            onError(err.response?.data?.message || 'Payment failed');
            setSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleStripePay}>
            <div className="border border-gray-300 rounded-lg p-4 mb-4">
                <CardElement options={{
                    style: {
                        base: { fontSize: '16px', color: '#424770', '::placeholder': { color: '#aab7c4' } },
                        invalid: { color: '#9e2146' },
                    }
                }} />
            </div>
            <button type="submit" disabled={!stripe || submitting} className="w-full py-3 bg-indigo-600 text-white font-semibold rounded-lg text-sm hover:bg-indigo-700 disabled:opacity-50">
                Pay {formatCurrency(amount)} with Card
            </button>
        </form>
    );
}

export default function PaymentDialog({ order, onClose, onPaid }) {
    const [step, setStep] = useState('method');
    const [method, setMethod] = useState('cash');
    const [amountTendered, setAmountTendered] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [result, setResult] = useState(null);
    const [splitMode, setSplitMode] = useState(false);
    const [splits, setSplits] = useState([{ label: '', amount: '', method: 'cash' }]);
    const [stripeConfig, setStripeConfig] = useState(null);
    const [cardError, setCardError] = useState(null);

    const total = parseFloat(order.total);

    useEffect(() => {
        client.get('/payment-gateways/stripe/config')
            .then(res => setStripeConfig(res.data))
            .catch(() => setStripeConfig({ configured: false }));
        client.get('/settings').then(({ data }) => {
            const currency = data.restaurant?.[0]?.value || 'USD';
            setCurrency(currency);
        }).catch(() => {});
    }, []);

    const handlePay = async () => {
        setSubmitting(true);
        try {
            const { data } = await client.post('/payments', {
                order_id: order.id,
                amount: total,
                method,
                amount_tendered: method === 'cash' ? parseFloat(amountTendered) || total : null,
            });
            setResult(data);
            setStep('confirmation');
        } catch (err) { alert(err.response?.data?.message || 'Payment error'); } finally { setSubmitting(false); }
    };

    const handleSplitPay = async () => {
        const totalSplit = splits.reduce((s, sp) => s + parseFloat(sp.amount || 0), 0);
        if (Math.abs(totalSplit - total) > 0.01) {
            alert(`Split amounts must total ${formatCurrency(total)}. Currently: ${formatCurrency(totalSplit)}`);
            return;
        }
        setSubmitting(true);
        try {
            const { data } = await client.post('/payments/split', {
                order_id: order.id,
                split_type: 'by_person',
                splits: splits.map((sp) => ({ label: sp.label, amount: parseFloat(sp.amount), method: sp.method })),
            });
            setResult(data);
            setStep('confirmation');
        } catch (err) { alert(err.response?.data?.message || 'Split error'); } finally { setSubmitting(false); }
    };

    const addSplit = () => setSplits([...splits, { label: '', amount: '', method: 'cash' }]);
    const updateSplit = (i, field, value) => {
        const next = [...splits];
        next[i][field] = value;
        setSplits(next);
    };
    const removeSplit = (i) => { if (splits.length > 1) setSplits(splits.filter((_, idx) => idx !== i)); };

    const handleStripeSuccess = (paymentData) => {
        setResult({ payment: paymentData.payment, order_status: paymentData.order_status, change_due: paymentData.change_due, remaining: paymentData.remaining });
        setStep('confirmation');
    };

    const handleStripeError = (msg) => {
        setCardError(msg);
    };

    if (step === 'confirmation' && result) {
        return (
            <div className="p-6 text-center">
                <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg className="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
                </div>
                <h3 className="text-xl font-bold text-gray-800 mb-2">Payment Complete</h3>
                {result.change_due > 0 && <p className="text-lg text-green-600 font-bold mb-2">Change Due: {formatCurrency(result.change_due)}</p>}
                <p className="text-gray-500 mb-4">Order #{order.order_number}</p>
                <button onClick={onPaid} className="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">Done</button>
            </div>
        );
    }

    return (
        <div className="p-6">
            <h3 className="text-lg font-semibold text-gray-800 mb-2">
                {splitMode ? 'Split Bill' : 'Take Payment'}
            </h3>
            <p className="text-3xl font-bold text-indigo-600 mb-6">{formatCurrency(total)}</p>

            {!splitMode && step === 'method' && (
                <div className="space-y-4">
                    <div className="grid grid-cols-2 gap-3">
                        {[
                            { key: 'cash', label: 'Cash', icon: '\u{1F4B5}' },
                            { key: 'card', label: 'Card', icon: '\u{1F4B3}' },
                            { key: 'pos', label: 'POS', icon: '\u{1F5A5}\uFE0F' },
                            { key: 'transfer', label: 'Transfer', icon: '\u{1F4F1}' },
                        ].map((m) => (
                            <button key={m.key} onClick={() => { setMethod(m.key); setCardError(null); }} className={`p-4 rounded-xl border-2 text-center transition-colors ${method === m.key ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300'}`}>
                                <span className="text-2xl">{m.icon}</span>
                                <p className="text-sm font-medium text-gray-700 mt-1">{m.label}</p>
                            </button>
                        ))}
                    </div>

                    {method === 'cash' && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Amount Tendered</label>
                            <input type="number" step="0.01" min={total} value={amountTendered} onChange={(e) => setAmountTendered(e.target.value)} placeholder={`Min: ${formatCurrency(total)}`} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-lg font-bold focus:ring-2 focus:ring-indigo-500 outline-none" />
                            {parseFloat(amountTendered) >= total && (
                                <p className="text-sm text-green-600 font-medium mt-1">Change: {formatCurrency(parseFloat(amountTendered) - total)}</p>
                            )}
                        </div>
                    )}

                    {method === 'card' && (
                        <>
                            {stripeConfig === null && (
                                <p className="text-sm text-gray-500 text-center py-4">Loading payment options...</p>
                            )}
                            {stripeConfig && !stripeConfig.configured && (
                                <p className="text-sm text-yellow-600 text-center py-4 bg-yellow-50 rounded-lg">Card payments not configured</p>
                            )}
                            {stripeConfig && stripeConfig.configured && (
                                <Elements stripe={loadStripe(stripeConfig.publishable_key)}>
                                    <StripeCheckoutForm amount={total} orderId={order.id} onSuccess={handleStripeSuccess} onError={handleStripeError} setSubmitting={setSubmitting} />
                                </Elements>
                            )}
                            {cardError && (
                                <p className="text-sm text-red-600 mt-2 text-center">{cardError}</p>
                            )}
                        </>
                    )}

                    {(method === 'cash' || method === 'pos' || method === 'transfer') && (
                        <div className="flex gap-3">
                            <button onClick={onClose} className="flex-1 py-3 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg">Cancel</button>
                            <button onClick={handlePay} disabled={submitting} className="flex-1 py-3 bg-indigo-600 text-white font-semibold rounded-lg text-sm hover:bg-indigo-700 disabled:opacity-50">
                                {submitting ? 'Processing...' : `Pay ${formatCurrency(total)}`}
                            </button>
                        </div>
                    )}
                    <button onClick={() => setSplitMode(true)} className="w-full text-sm text-indigo-600 hover:text-indigo-800">Split bill instead</button>
                </div>
            )}

            {splitMode && (
                <div className="space-y-3">
                    <p className="text-sm text-gray-500">Split total must equal {formatCurrency(total)}</p>
                    {splits.map((sp, i) => (
                        <div key={i} className="flex gap-2 items-start bg-gray-50 p-3 rounded-lg">
                            <div className="flex-1 space-y-1">
                                <input value={sp.label} onChange={(e) => updateSplit(i, 'label', e.target.value)} placeholder="Name" className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                                <div className="flex gap-2">
                                    <input type="number" step="0.01" min="0" value={sp.amount} onChange={(e) => updateSplit(i, 'amount', e.target.value)} placeholder="Amount" className="w-24 px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                                    <select value={sp.method} onChange={(e) => updateSplit(i, 'method', e.target.value)} className="px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="pos">POS</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>
                            </div>
                            {splits.length > 1 && (
                                <button onClick={() => removeSplit(i)} className="text-red-500 hover:text-red-700 text-xs p-1">{'\u2715'}</button>
                            )}
                        </div>
                    ))}
                    <button onClick={addSplit} className="text-sm text-indigo-600 hover:text-indigo-800">+ Add person</button>
                    <div className="flex gap-3 pt-2">
                        <button onClick={() => setSplitMode(false)} className="flex-1 py-3 text-sm text-gray-600 border border-gray-200 rounded-lg hover:text-gray-800">Back</button>
                        <button onClick={handleSplitPay} disabled={submitting} className="flex-1 py-3 bg-indigo-600 text-white font-semibold rounded-lg text-sm hover:bg-indigo-700 disabled:opacity-50">
                            {submitting ? 'Processing...' : 'Process Split'}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
