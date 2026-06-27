const SYMBOLS = {
  USD: '$', EUR: '€', GBP: '£', JPY: '¥', CAD: 'C$', AUD: 'A$', CHF: 'Fr', CNY: '¥', INR: '₹', BRL: 'R$', MXN: 'Mex$',
  NGN: '₦', ZAR: 'R', EGP: 'E£', KES: 'KSh', GHS: 'GH₵', TZS: 'TSh', UGX: 'USh', MAD: 'DH', DZD: 'DA',
  XAF: 'FCFA', XOF: 'CFA', ETB: 'Br', AOA: 'Kz', MZN: 'MT', ZMW: 'ZK', RWF: 'FRw', TND: 'DT',
  SDG: 'SDG', LYD: 'LD', BWP: 'P', NAD: 'N$', MWK: 'MK', MUR: 'Rs', GMD: 'D', CDF: 'FC',
  MGA: 'Ar', GNF: 'FG', SOS: 'Sh', BIF: 'FBu', SCR: 'SR', SZL: 'E', LSL: 'L', CVE: 'Esc',
  MRU: 'UM', DJF: 'Fdj', KMF: 'CF', SSP: '£', SLE: 'Le', STN: 'Db', ERN: 'Nfk'
};
const DEFAULT = { symbol: '$', code: 'USD' };

let _currency = null;

export function setCurrency(code) {
    _currency = { symbol: SYMBOLS[code] || '$', code };
    localStorage.setItem('pos_currency', JSON.stringify(_currency));
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
