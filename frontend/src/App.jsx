import './App.css';
import './styles/Main.css';
import Home from './pages/Home.jsx';
import './styles/characterCreation/CharacterCreation.css';
import CreateCharacter from './pages/characterCreation/CreateCharacter.jsx';
import ChooseRace from './pages/characterCreation/ChooseRace.jsx';
import ChooseClass from './pages/characterCreation/ChooseClass.jsx';
import ChooseEquipment from './pages/characterCreation/ChooseEquipment.jsx';
import ChooseAbilities from './pages/characterCreation/ChooseAbilities.jsx';
import ChooseSkills from './pages/characterCreation/ChooseSkills.jsx';
import ChooseSpells from './pages/characterCreation/ChooseSpells.jsx';
import CharacterSummary from './pages/characterCreation/CharacterSummary.jsx';
import ChooseBackground from './pages/characterCreation/ChooseBackground.jsx';
import CharacterSheet from './pages/CharacterSheet.jsx';
import CharacterLevelUpChoice from './pages/characterLevelUpChoice/CharacterLevelUpChoice.jsx';
import LoginPage from './pages/LoginPage.jsx';
import SignupPage from './pages/SignupPage.jsx';
import AccountSettingsPage from './pages/AccountSettingsPage.jsx';
import ChangePasswordPage from './pages/ChangePasswordPage.jsx';
import ChangeUsernamePage from './pages/ChangeUsernamePage.jsx';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import { AuthProvider } from './context/AuthProvider.jsx';
import { CreationOptionsProvider } from './context/CreationOptionsProvider.jsx';
import { CharacterLimitProvider } from './context/CharacterLimitProvider.jsx';
import CharacterLimitGuard from './components/CharacterLimitGuard.jsx';
import {
    ProtectedRoute,
    PublicOnlyRoute,
} from './components/ProtectedRoute.jsx';

function App() {
    return (
        <Router>
            <AuthProvider>
                <CharacterLimitProvider>
                    <Routes>
                        <Route
                            path="/"
                            element={
                                <ProtectedRoute>
                                    <Home />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/character/create"
                            element={
                                <ProtectedRoute>
                                    <CharacterLimitGuard>
                                        <CreationOptionsProvider />
                                    </CharacterLimitGuard>
                                </ProtectedRoute>
                            }
                        >
                            <Route index element={<CreateCharacter />} />
                            <Route path="race" element={<ChooseRace />} />
                            <Route path="class" element={<ChooseClass />} />
                            <Route
                                path="equipment"
                                element={<ChooseEquipment />}
                            />
                            <Route
                                path="abilities"
                                element={<ChooseAbilities />}
                            />
                            <Route
                                path="background"
                                element={<ChooseBackground />}
                            />
                            <Route path="skills" element={<ChooseSkills />} />
                            <Route path="spells" element={<ChooseSpells />} />
                            <Route
                                path="summary"
                                element={<CharacterSummary />}
                            />
                        </Route>
                        <Route
                            path="/character/:characterId/level-up"
                            element={
                                <ProtectedRoute>
                                    <CharacterLevelUpChoice />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/character/:characterId"
                            element={
                                <ProtectedRoute>
                                    <CharacterSheet />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/settings"
                            element={
                                <ProtectedRoute>
                                    <AccountSettingsPage />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/settings/password"
                            element={
                                <ProtectedRoute>
                                    <ChangePasswordPage />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/settings/username"
                            element={
                                <ProtectedRoute>
                                    <ChangeUsernamePage />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/login"
                            element={
                                <PublicOnlyRoute>
                                    <LoginPage />
                                </PublicOnlyRoute>
                            }
                        />
                        <Route
                            path="/signup"
                            element={
                                <PublicOnlyRoute>
                                    <SignupPage />
                                </PublicOnlyRoute>
                            }
                        />
                    </Routes>
                </CharacterLimitProvider>
            </AuthProvider>
        </Router>
    );
}

export default App;
