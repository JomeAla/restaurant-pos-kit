import { useState } from 'react';
import { Link, Outlet, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';
import { useOffline } from '../hooks/useOffline';

const LANGUAGES = { en: 'English', fr: 'Français', es: 'Español', de: 'Deutsch', pt: 'Português', it: 'Italiano', nl: 'Nederlands', pl: 'Polski', ru: 'Русский', zh: '中文', ja: '日本語', ko: '한국어', ar: 'العربية', tr: 'Türkçe' };

const navItems = [
    { to: '/dashboard', labelKey: 'nav.dashboard', icon: '📊', permission: null },
    { to: '/pos', labelKey: 'nav.pos_terminal', icon: '🛒', permission: 'order.create' },
    { to: '/menu', labelKey: 'nav.menu_items', icon: '🍽️', permission: 'menu.view' },
    { to: '/categories', labelKey: 'nav.categories', icon: '📁', permission: 'menu.view' },
    { to: '/modifiers', labelKey: 'nav.modifiers', icon: '⚙️', permission: 'menu.view' },
    { to: '/combos', labelKey: 'nav.combos', icon: '🎁', permission: 'menu.view' },
    { to: '/inventory', labelKey: 'nav.inventory', icon: '📦', permission: 'menu.view' },
    { to: '/reservations', labelKey: 'nav.reservations', icon: '📅', permission: 'order.view' },
    { to: '/tables', labelKey: 'nav.tables', icon: '🪑', permission: 'tables.view' },
    { to: '/orders', labelKey: 'nav.orders', icon: '📋', permission: 'order.view' },
    { to: '/kds', labelKey: 'nav.kds', icon: '👨‍🍳', permission: 'order.view' },
    { to: '/reports', labelKey: 'nav.reports', icon: '📊', permission: 'order.view' },
    { to: '/settings', labelKey: 'nav.settings', icon: '⚙️', permission: null },
];

export default function PosLayout() {
    const { t } = useTranslation();
    const { user, logout, can, changeLocale } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { isOnline, pendingSync } = useOffline();

    const handleLogout = async () => {
        await logout();
        navigate('/login');
    };

    const currentLang = localStorage.getItem('i18nextLng') || 'en';
    const visibleItems = navItems.filter((item) => !item.permission || can(item.permission));

    return (
        <div className="min-h-screen bg-gray-50 flex">
            {!isOnline && (
                <div className="fixed top-0 left-0 right-0 z-50 bg-red-600 text-white text-center text-sm font-medium py-1.5 px-4">
                    You are offline — orders will be saved locally and synced when reconnected
                </div>
            )}
            {isOnline && pendingSync > 0 && (
                <div className="fixed top-0 left-0 right-0 z-50 bg-amber-500 text-white text-center text-sm font-medium py-1.5 px-4">
                    {pendingSync} pending order{pendingSync !== 1 ? 's' : ''} — syncing when possible
                </div>
            )}
            {sidebarOpen && (
                <div className="fixed inset-0 bg-black/50 z-20 lg:hidden" onClick={() => setSidebarOpen(false)} />
            )}
            <aside className={`fixed lg:static inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 transform transition-transform lg:transform-none ${sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'}`}>
                <div className="h-16 flex items-center px-6 border-b border-gray-200">
                    <h1 className="text-lg font-bold text-indigo-600">POS Kit</h1>
                </div>
                <nav className="p-4 space-y-1">
                    {visibleItems.map((item) => (
                        <Link
                            key={item.to}
                            to={item.to}
                            onClick={() => setSidebarOpen(false)}
                            className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${location.pathname.startsWith(item.to) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'}`}
                        >
                            <span>{item.icon}</span>
                            {t(item.labelKey)}
                        </Link>
                    ))}
                </nav>
                <div className="p-4 border-t border-gray-200">
                    <div className="flex items-center justify-between">
                        <Link to="/profile" onClick={() => setSidebarOpen(false)} className="hover:opacity-80">
                            <p className="text-sm font-medium text-gray-700">{user?.name}</p>
                            <p className="text-xs text-gray-500">{user?.role?.name}</p>
                        </Link>
                        <button onClick={handleLogout} className="text-sm text-red-600 hover:text-red-800">
                            {t('nav.logout')}
                        </button>
                    </div>
                </div>
            </aside>
            <div className="flex-1 flex flex-col min-h-screen">
                <header className="h-16 bg-white border-b border-gray-200 flex items-center px-3 sm:px-4 lg:px-6 gap-2 sm:gap-4">
                    <button className="lg:hidden mr-2 text-gray-600" onClick={() => setSidebarOpen(true)}>
                        <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 className="text-lg font-semibold text-gray-800 capitalize flex-1">
                        {location.pathname === '/' ? t('nav.dashboard') : t(`nav.${location.pathname.split('/')[1]}`, location.pathname.split('/')[1])}
                    </h2>
                    <select
                        value={currentLang}
                        onChange={(e) => changeLocale(e.target.value)}
                        className="text-xs sm:text-sm border border-gray-300 rounded-lg px-2 sm:px-3 py-1.5 bg-white text-gray-700 focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer max-w-[90px] sm:max-w-none"
                    >
                        {Object.entries(LANGUAGES).map(([code, name]) => (
                            <option key={code} value={code}>{name}</option>
                        ))}
                    </select>
                </header>
                <main className="flex-1 p-3 sm:p-4 lg:p-6 overflow-auto">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
