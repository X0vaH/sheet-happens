import Navbar from '../global/Navbar.jsx';
import background from '../../assets/settings_background.jpg';
import '../../styles/account-settings/SettingsPage.css';

function SettingsPage({ children }) {
    return (
        <div className="home">
            <img className="home-background" src={background} alt="" />
            <div className="home-content">
                <Navbar />
                <div className="settings-page">{children}</div>
            </div>
        </div>
    );
}

export default SettingsPage;
