import { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import client from '../api/client';

export default function Profile() {
    const { user } = useAuth();
    const [form, setForm] = useState({ name: user?.name || '', email: user?.email || '', phone: user?.phone || '' });
    const [passwordForm, setPasswordForm] = useState({ current_password: '', password: '', password_confirmation: '' });
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState('');

    const updateProfile = async (e) => {
        e.preventDefault();
        setSaving(true);
        setMessage('');
        try {
            await client.put('/auth/profile', form);
            setMessage('Profile updated.');
        } catch (err) {
            setMessage(err.response?.data?.message || 'Error updating profile');
        } finally { setSaving(false); }
    };

    const updatePassword = async (e) => {
        e.preventDefault();
        setSaving(true);
        setMessage('');
        try {
            await client.put('/auth/profile', passwordForm);
            setPasswordForm({ current_password: '', password: '', password_confirmation: '' });
            setMessage('Password updated.');
        } catch (err) {
            setMessage(err.response?.data?.message || 'Error updating password');
        } finally { setSaving(false); }
    };

    return (
        <div className="max-w-2xl">
            <h2 className="text-2xl font-bold text-gray-800 mb-6">Profile</h2>
            {message && <div className="mb-4 p-3 bg-indigo-50 text-indigo-700 text-sm rounded-lg">{message}</div>}

            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <h3 className="text-lg font-semibold text-gray-800 mb-4">Account Details</h3>
                <form onSubmit={updateProfile} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <button type="submit" disabled={saving} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                        {saving ? 'Saving...' : 'Save Changes'}
                    </button>
                </form>
            </div>

            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 className="text-lg font-semibold text-gray-800 mb-4">Change Password</h3>
                <form onSubmit={updatePassword} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" value={passwordForm.current_password} onChange={(e) => setPasswordForm({ ...passwordForm, current_password: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" value={passwordForm.password} onChange={(e) => setPasswordForm({ ...passwordForm, password: e.target.value })} required minLength={8} className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" value={passwordForm.password_confirmation} onChange={(e) => setPasswordForm({ ...passwordForm, password_confirmation: e.target.value })} required className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>
                    <button type="submit" disabled={saving} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                        {saving ? 'Saving...' : 'Update Password'}
                    </button>
                </form>
            </div>
        </div>
    );
}
