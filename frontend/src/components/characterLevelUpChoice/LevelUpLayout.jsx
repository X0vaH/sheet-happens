import Navbar from '../global/Navbar.jsx';
import CreationBackground from '../characterCreation/CreationBackground.jsx';
import background from '../../assets/level-up-background.png';
import '../../styles/characterCreation/CreateCharacter.css';
import '../../styles/characterCreation/CreationTypography.css';
import '../../styles/characterLevelUpChoice/LevelUpChoice.css';

export default function LevelUpLayout({ children }) {
    return (
        <div className="level-up-page">
            <CreationBackground src={background} />
            <Navbar />
            {children}
        </div>
    );
}
