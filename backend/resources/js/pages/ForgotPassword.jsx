import { useState } from 'react';
import { Link } from 'react-router-dom';

export default function ForgotPassword() {
    const [email, setEmail] = useState('');
    const [sent, setSent] = useState(false);
    const [token, setToken] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);
        try {
            const res = await fetch('/api/v1/auth/forgot-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email }),
            });
            const data = await res.json();
            if (!res.ok) { setError(data.message || 'Error'); return; }
            setToken(data.token);
            setSent(true);
        } catch { setError('Network error'); }
        finally { setLoading(false); }
    };

    if (sent) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-100 p-4">
                <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 text-center">
                    <div className="text-4xl mb-4">&#9989;</div>
                    <h1 className="text-2xl font-bold text-gray-800 mb-2">Check Your Email</h1>
                    <p className="text-gray-500 mb-4">If an account exists with that email, you'll receive a reset link.</p>
                    <div className="bg-gray-50 rounded-lg p-4 mb-4 text-left">
                        <p className="text-xs text-gray-500 mb-1">For development, use this token:</p>
                        <p className="text-sm font-mono text-indigo-600 break-all">{token}</p>
                    </div>
                    <Link to={`/reset-password?token=${token}&email=${encodeURIComponent(email)}`} className="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                        Continue to reset password &rarr;
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-100 p-4">
            <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
                <h1 className="text-2xl font-bold text-gray-800 mb-2 text-center">Forgot Password</h1>
                <p className="text-gray-500 text-sm mb-6 text-center">Enter your email to receive a reset link.</p>
                {error && <div className="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg">{error}</div>}
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <button type="submit" disabled={loading} className="w-full py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                        {loading ? 'Sending...' : 'Send Reset Link'}
                    </button>
                </form>
                <div className="mt-4 text-center">
                    <Link to="/login" className="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Back to Login</Link>
                </div>
            </div>
        </div>
    );
}
