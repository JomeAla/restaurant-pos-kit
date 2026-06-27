import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';

export default function Login() {
    const { t } = useTranslation();
    const { login } = useAuth();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);
        try {
            await login(email, password);
            window.location.href = '/dashboard';
        } catch (err) {
            setError(err.response?.data?.message || t('auth.invalid_credentials'));
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-100 p-4">
            <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
                <div className="text-center mb-8">
                    <h1 className="text-3xl font-bold text-indigo-600">{t('app.name')}</h1>
                    <p className="text-gray-500 mt-1">{t('app.tagline')}</p>
                </div>
                {error && <div className="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg">{error}</div>}
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">{t('auth.email')}</label>
                        <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" placeholder="admin@restaurantpos.com" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">{t('auth.password')}</label>
                        <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" placeholder="••••••••" />
                    </div>
                    <button type="submit" disabled={loading} className="w-full py-2 px-4 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                        {loading ? t('common.signing_in') : t('auth.sign_in')}
                    </button>
                </form>
                <div className="mt-6 space-y-2 text-center">
                    <div>
                        <Link to="/pin-login" className="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            {t('auth.sign_in_with_pin')}
                        </Link>
                    </div>
                    <div>
                        <Link to="/forgot-password" className="text-sm text-gray-500 hover:text-gray-700">
                            {t('auth.forgot_password')}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
