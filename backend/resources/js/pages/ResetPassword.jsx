import { useState } from 'react';
import { Link, useSearchParams, useNavigate } from 'react-router-dom';

export default function ResetPassword() {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const [done, setDone] = useState(false);

    const token = searchParams.get('token') || '';
    const email = searchParams.get('email') || '';

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (password !== passwordConfirmation) { setError('Passwords do not match'); return; }
        setError('');
        setLoading(true);
        try {
            const res = await fetch('/api/v1/auth/reset-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email, token, password, password_confirmation: passwordConfirmation }),
            });
            const data = await res.json();
            if (!res.ok) { setError(data.message || 'Error'); return; }
            setDone(true);
        } catch { setError('Network error'); }
        finally { setLoading(false); }
    };

    if (done) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-100 p-4">
                <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 text-center">
                    <div className="text-4xl mb-4">&#9989;</div>
                    <h1 className="text-2xl font-bold text-gray-800 mb-2">Password Reset</h1>
                    <p className="text-gray-500 mb-6">Your password has been reset successfully.</p>
                    <Link to="/login" className="inline-block px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">
                        Sign In
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-100 p-4">
            <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
                <h1 className="text-2xl font-bold text-gray-800 mb-2 text-center">Reset Password</h1>
                <p className="text-gray-500 text-sm mb-6 text-center">Enter your new password.</p>
                {error && <div className="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg">{error}</div>}
                <form onSubmit={handleSubmit} className="space-y-4">
                    <input type="hidden" name="token" value={token} />
                    <input type="hidden" name="email" value={email} />
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8} className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" value={passwordConfirmation} onChange={(e) => setPasswordConfirmation(e.target.value)} required className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <button type="submit" disabled={loading} className="w-full py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                        {loading ? 'Resetting...' : 'Reset Password'}
                    </button>
                </form>
            </div>
        </div>
    );
}
