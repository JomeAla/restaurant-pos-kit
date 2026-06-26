import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import client from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    const fetchUser = useCallback(async () => {
        const token = localStorage.getItem('token');
        if (!token) { setLoading(false); return; }
        try {
            const { data } = await client.get('/auth/me');
            setUser(data);
        } catch {
            localStorage.removeItem('token');
            setUser(null);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => { fetchUser(); }, [fetchUser]);

    const login = async (email, password) => {
        const { data } = await client.post('/auth/login', { email, password });
        localStorage.setItem('token', data.token);
        await fetchUser();
        return data;
    };

    const pinLogin = async (pin) => {
        const { data } = await client.post('/auth/pin', { pin });
        localStorage.setItem('token', data.token);
        await fetchUser();
        return data;
    };

    const logout = async () => {
        try { await client.post('/auth/logout'); } catch { /* ignore */ }
        localStorage.removeItem('token');
        setUser(null);
    };

    const can = (permission) => {
        if (!user?.permissions) return false;
        return user.permissions.includes('*') || user.permissions.includes(permission);
    };

    return (
        <AuthContext.Provider value={{ user, loading, login, pinLogin, logout, can }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const ctx = useContext(AuthContext);
    if (!ctx) throw new Error('useAuth must be used within AuthProvider');
    return ctx;
}
