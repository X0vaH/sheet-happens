import { Navigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth.js';

export function ProtectedRoute({ children }) {
    const { isAuthenticated, loading } = useAuth();

    if (loading) return null;
    if (!isAuthenticated) return <Navigate to="/login" replace />;

    return children;
}

export function PublicOnlyRoute({ children }) {
    const { isAuthenticated, loading } = useAuth();

    if (loading) return null;
    if (isAuthenticated) return <Navigate to="/" replace />;

    return children;
}
