import { useAuth } from '../../hooks/useAuth.js';

function Header() {
    const { user } = useAuth();

    return (
        <header className="header">
            <div className="header-text">
                <h1>Bienvenue, {user?.name}</h1>
                <p>
                    Gères tes héros, consulte leurs fiche et prépares-toi pour
                    la prochaine aventure
                </p>
            </div>
        </header>
    );
}

export default Header;
