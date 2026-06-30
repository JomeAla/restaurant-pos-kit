import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from '../contexts/AuthContext';
import ProtectedRoute from './ProtectedRoute';
import PosLayout from './PosLayout';
import Login from '../pages/Login';
import PinLogin from '../pages/PinLogin';
import ForgotPassword from '../pages/ForgotPassword';
import ResetPassword from '../pages/ResetPassword';
import Dashboard from '../pages/Dashboard';
import Categories from '../pages/Categories';
import MenuItems from '../pages/MenuItems';
import Modifiers from '../pages/Modifiers';
import Combos from '../pages/Combos';
import Profile from '../pages/Profile';
import Tables from '../pages/Tables';
import Inventory from '../pages/Inventory';
import KDS from '../pages/KDS';
import Orders from '../pages/Orders';
import PosTerminal from '../pages/PosTerminal';
import Reservations from '../pages/Reservations';
import SettingsPage from '../pages/Settings';
import SupportTickets from '../pages/SupportTickets';
import Faq from '../pages/Faq';

export default function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    <Route path="/login" element={<Login />} />
                    <Route path="/pin-login" element={<PinLogin />} />
                    <Route path="/forgot-password" element={<ForgotPassword />} />
                    <Route path="/reset-password" element={<ResetPassword />} />
                    <Route path="/" element={<ProtectedRoute><PosLayout /></ProtectedRoute>}>
                        <Route index element={<Navigate to="/dashboard" replace />} />
                        <Route path="dashboard" element={<Dashboard />} />
                        <Route path="categories" element={<ProtectedRoute permission="menu.view"><Categories /></ProtectedRoute>} />
                        <Route path="menu" element={<ProtectedRoute permission="menu.view"><MenuItems /></ProtectedRoute>} />
                        <Route path="modifiers" element={<ProtectedRoute permission="menu.view"><Modifiers /></ProtectedRoute>} />
                        <Route path="combos" element={<ProtectedRoute permission="menu.view"><Combos /></ProtectedRoute>} />
                        <Route path="profile" element={<Profile />} />
                        <Route path="settings" element={<SettingsPage />} />
                        <Route path="inventory" element={<ProtectedRoute permission="menu.view"><Inventory /></ProtectedRoute>} />
                        <Route path="reservations" element={<ProtectedRoute permission="order.view"><Reservations /></ProtectedRoute>} />
                        <Route path="tables" element={<ProtectedRoute permission="tables.view"><Tables /></ProtectedRoute>} />
                        <Route path="orders" element={<ProtectedRoute permission="order.view"><Orders /></ProtectedRoute>} />
                        <Route path="kds" element={<ProtectedRoute permission="order.view"><KDS /></ProtectedRoute>} />
                        <Route path="pos" element={<ProtectedRoute permission="order.create"><PosTerminal /></ProtectedRoute>} />
                        <Route path="support/tickets" element={<ProtectedRoute><SupportTickets /></ProtectedRoute>} />
                        <Route path="faq" element={<ProtectedRoute><Faq /></ProtectedRoute>} />
                    </Route>
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}
