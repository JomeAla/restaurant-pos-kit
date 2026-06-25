const SYMBOLS = { USD: '$', EUR: '€', GBP: '£', NGN: '₦', JPY: '¥', CAD: 'C$', AUD: 'A$', CHF: 'Fr', CNY: '¥', INR: '₹', BRL: 'R$', MXN: 'Mex$' };
const DEFAULT = { symbol: '$', code: 'USD' };

let _currency = null;

export function setCurrency(code) {
    _currency = { symbol: SYMBOLS[code] || '$', code };
}

export function getCurrency() {
    if (_currency) return _currency;
    try {
        const raw = localStorage.getItem('pos_currency');
        if (raw) return JSON.parse(raw);
    } catch {}
    return DEFAULT;
}

export function formatCurrency(amount) {
    const c = getCurrency();
    return `${c.symbol}${parseFloat(amount || 0).toFixed(2)}`;
}
