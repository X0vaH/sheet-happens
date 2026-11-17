import StepActions from '../../components/characterCreation/StepActions.jsx';
import ChoicePanel from '../../components/characterCreation/ChoicePanel.jsx';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import {
    findByName,
    names,
    sizeLabels,
} from '../../utils/characterCreation/catalog.js';
import CreationStep from '../../components/characterCreation/CreationStep.jsx';
import IdentityAside from '../../components/characterCreation/IdentityAside.jsx';
import DetailList, {
    DetailRow,
} from '../../components/characterCreation/DetailList.jsx';
import DetailsPanel from '../../components/characterCreation/DetailsPanel.jsx';
import { useDraftChoice } from '../../hooks/useDraftState.js';

export default function ChooseRace() {
    const options = useCreationOptions();
    const races = names(options.species);
    const [race, selectRace] = useDraftChoice('race', races);

    const species = findByName(options.species, race);

    return (
        <CreationStep step="race">
            <main className="race-layout" aria-labelledby="race-title">
                <IdentityAside />
                <div className="race-content">
                    <ChoicePanel
                        title="Choix de la race"
                        titleId="race-title"
                        label="Race du personnage"
                        options={races}
                        selected={race}
                        onSelect={selectRace}
                    />
                    <DetailsPanel
                        id="race-description"
                        className="race-description"
                        title={race}
                        emptyTitle="Choisissez une race"
                        description={species?.description}
                        emptyDescription="Sélectionnez une race pour consulter ses caractéristiques."
                        selected={species}
                    >
                        <dl className="race-details">
                            <DetailRow label="Vitesse">
                                {species?.base_speed} m
                            </DetailRow>
                            <DetailRow label="Taille">
                                {sizeLabels[species?.size_category] ??
                                    species?.size_category}
                            </DetailRow>
                            <DetailList
                                label="Langues"
                                items={species?.languages ?? []}
                            />
                            <DetailList
                                label="Traits raciaux"
                                items={species?.features ?? []}
                                renderItem={(trait) => (
                                    <>
                                        <strong>{trait.name}</strong> :{' '}
                                        {trait.description}
                                    </>
                                )}
                            />
                        </dl>
                    </DetailsPanel>
                </div>
            </main>
            <StepActions />
        </CreationStep>
    );
}
