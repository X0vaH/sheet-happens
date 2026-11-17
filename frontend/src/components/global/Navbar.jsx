import creationMenu from '../../assets/creation-menu.svg';
import '../../styles/Navbar.css';
import UserIcon from '../../assets/svg/UserIcon.jsx';
import { useEffect, useId, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth.js';
import { useCharacterLimit } from '../../hooks/useCharacterLimit.js';
import { CHARACTER_LIMIT_MESSAGE } from '../../configs/characterLimit.js';

function Navbar({ creation = false }) {
    const navigate = useNavigate();
    const { user, logout } = useAuth();
    const { limitReached, loading: limitLoading } = useCharacterLimit();
    const [mobileOpen, setMobileOpen] = useState(false);
    const navRef = useRef(null);
    const burgerRef = useRef(null);
    const menuId = useId();
    const limitMessageId = useId();

    // Creation stays disabled while the count loads, so the link is never active just before
    // it turns off; the explanation only shows once the limit is known to be reached.
    const disabledCreateButton = (className, suffix) => (
        <button
            type="button"
            disabled
            className={className}
            aria-describedby={
                limitReached ? `${limitMessageId}-${suffix}` : undefined
            }
        >
            Créer un personnage
            {limitReached && (
                <span
                    id={`${limitMessageId}-${suffix}`}
                    className="navbar-limit-message"
                >
                    {CHARACTER_LIMIT_MESSAGE}
                </span>
            )}
        </button>
    );

    const closeMobile = () => setMobileOpen(false);

    const handleLogout = async () => {
        closeMobile();
        await logout();
        navigate('/login');
    };

    useEffect(() => {
        if (!mobileOpen) return;

        const handleKeyDown = (e) => {
            if (e.key === 'Escape') {
                closeMobile();
                burgerRef.current?.focus();
            }
        };
        const handleClickOutside = (e) => {
            if (navRef.current && !navRef.current.contains(e.target)) {
                closeMobile();
            }
        };
        const mq = window.matchMedia('(min-width: 701px)');
        const handleResize = (e) => {
            if (e.matches) closeMobile();
        };

        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('mousedown', handleClickOutside);
        mq.addEventListener('change', handleResize);
        return () => {
            document.removeEventListener('keydown', handleKeyDown);
            document.removeEventListener('mousedown', handleClickOutside);
            mq.removeEventListener('change', handleResize);
        };
    }, [mobileOpen]);

    const burgerLabel = mobileOpen ? 'Fermer le menu' : 'Ouvrir le menu';

    const mobileMenu = (
        <div
            id={menuId}
            className={`navbar-mobile-menu${mobileOpen ? ' open' : ''}`}
        >
            <Link to="/" onClick={closeMobile}>
                Accueil
            </Link>
            {limitLoading || limitReached ? (
                disabledCreateButton('navbar-create-disabled', 'mobile')
            ) : (
                <Link to="/character/create" onClick={closeMobile}>
                    Créer un personnage
                </Link>
            )}
            <Link to="/settings" onClick={closeMobile}>
                {user?.name || 'Profil'}
                <UserIcon />
            </Link>
            <button type="button" onClick={handleLogout}>
                Déconnexion
            </button>
        </div>
    );

    if (creation) {
        return (
            <nav
                className="creation-mobile-header"
                aria-label="Navigation principale"
                ref={navRef}
            >
                <a className="creation-mobile-brand" href="/">
                    Sheet Happens
                </a>
                <button
                    type="button"
                    className="creation-mobile-menu"
                    ref={burgerRef}
                    aria-label={burgerLabel}
                    aria-expanded={mobileOpen}
                    aria-controls={menuId}
                    onClick={() => setMobileOpen((open) => !open)}
                >
                    {mobileOpen ? (
                        <span aria-hidden="true">{'✕'}</span>
                    ) : (
                        <img src={creationMenu} alt="" width="38" height="38" />
                    )}
                </button>
                {mobileMenu}
            </nav>
        );
    }

    return (
        <nav className="navbar" ref={navRef}>
            <div className="navbar-center">
                <span className="navbar-brand">Sheet Happens</span>

                <span className="navbar-link" onClick={() => navigate('/')}>
                    Accueil
                </span>

                <span className="navbar-separator" />

                {limitLoading || limitReached ? (
                    disabledCreateButton(
                        'navbar-link navbar-create-disabled',
                        'desktop',
                    )
                ) : (
                    <Link className="navbar-link" to="/character/create">
                        Créer un personnage
                    </Link>
                )}

                <span className="navbar-separator" />

                <span className="navbar-link" onClick={handleLogout}>
                    Déconnexion
                </span>

                <div
                    className="navbar-user"
                    onClick={() => navigate('/settings')}
                >
                    <span className="navbar-username">{user?.name}</span>
                    <UserIcon />
                </div>
            </div>

            <button
                type="button"
                className="navbar-menu-button"
                ref={burgerRef}
                aria-label={burgerLabel}
                aria-expanded={mobileOpen}
                aria-controls={menuId}
                onClick={() => setMobileOpen((open) => !open)}
            >
                {mobileOpen ? '✕' : '☰'}
            </button>

            {mobileMenu}
        </nav>
    );
}

export default Navbar;
