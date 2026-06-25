import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function PinLogin() {
    const { pinLogin } = useAuth();
    const [pin, setPin] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleKey = (key) => {
        if (key === 'clear') { setPin(''); return; }
        if (key === 'backspace') { setPin((prev) => prev.slice(0, -1)); return; }
        if (pin.length < 4) { setPin((prev) => prev + key); }
    };

    const handleSubmit = async () => {
        if (pin.length !== 4) return;
        setError('');
        setLoading(true);
        try {
            await pinLogin(pin);
            window.location.href = '/dashboard';
        } catch (err) {
            setError(err.response?.data?.message || 'Invalid PIN');
            setPin('');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-100 p-4">
            <div className="w-full max-w-sm bg-white rounded-2xl shadow-xl p-8">
                <div className="text-center mb-6">
                    <h1 className="text-2xl font-bold text-indigo-600">PIN Login</h1>
                    <p className="text-gray-500 text-sm mt-1">Enter your 4-digit PIN</p>
                </div>
                {error && <div className="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg text-center">{error}</div>}
                <div className="flex justify-center gap-3 mb-6">
                    {[0, 1, 2, 3].map((i) => (
                        <div key={i} className={`w-4 h-4 rounded-full border-2 ${pin[i] ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300'}`} />
                    ))}
                </div>
                <div className="grid grid-cols-3 gap-3 mb-4">
                    {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
                        <button key={num} onClick={() => handleKey(num.toString())} className="py-4 text-xl font-semibold bg-gray-100 rounded-xl hover:bg-gray-200 active:bg-gray-300 transition-colors">
                            {num}
                        </button>
                    ))}
                    <button onClick={() => handleKey('clear')} className="py-4 text-sm font-medium bg-gray-100 rounded-xl hover:bg-gray-200 active:bg-gray-300">
                        Clear
                    </button>
                    <button onClick={() => handleKey('0')} className="py-4 text-xl font-semibold bg-gray-100 rounded-xl hover:bg-gray-200 active:bg-gray-300">
                        0
                    </button>
                    <button onClick={() => handleKey('backspace')} className="py-4 text-sm font-medium bg-gray-100 rounded-xl hover:bg-gray-200 active:bg-gray-300">
                        ⌫
                    </button>
                </div>
                <button onClick={handleSubmit} disabled={pin.length !== 4 || loading} className="w-full py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                    {loading ? 'Signing in...' : 'Sign In'}
                </button>
                <div className="mt-4 text-center">
                    <Link to="/login" className="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        Sign in with Email
                    </Link>
                </div>
            </div>
        </div>
    );
}
