import StepActions from '../../components/characterCreation/StepActions.jsx';
import CreationStep from '../../components/characterCreation/CreationStep.jsx';
import CharacterSidebar from '../../components/characterCreation/CharacterSidebar.jsx';
import SkillButton from '../../components/characterCreation/SkillButton.jsx';
import { useSkillChoice } from '../../hooks/useSkillChoice.js';

export default function ChooseSkills() {
    const { limit, selected, skills, toggle } = useSkillChoice();

    return (
        <CreationStep step="skills">
            <main className="skills-layout" aria-labelledby="skills-title">
                <CharacterSidebar
                    className="skills-summary"
                    identityClassName="skills-identity"
                    withAbilities
                />
                <section className="race-panel skills-picker">
                    <h2 id="skills-title">
                        Choisissez {limit} compétences
                        <br />à maitriser
                    </h2>
                    <div
                        className="skills-choices"
                        role="group"
                        aria-label="Compétences à maîtriser"
                        aria-describedby="skills-count"
                    >
                        {skills.map((skill) => (
                            <SkillButton
                                key={skill.label}
                                {...skill}
                                onToggle={() => toggle(skill.label)}
                            />
                        ))}
                    </div>
                    <p
                        id="skills-count"
                        className="skills-count"
                        aria-live="polite"
                    >
                        {selected.length} / {limit} sélectionnées
                    </p>
                </section>
            </main>
            <StepActions className="skills-actions" />
        </CreationStep>
    );
}
