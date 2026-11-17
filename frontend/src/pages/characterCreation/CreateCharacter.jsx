import StepActions from '../../components/characterCreation/StepActions.jsx';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import {
    alignmentDescription,
    alignmentNames,
} from '../../utils/characterCreation/catalogExtras.js';
import { useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import CreationStep from '../../components/characterCreation/CreationStep.jsx';
import { useStepFlow } from '../../hooks/useStepFlow.js';
import { useDraftState, useDraftText } from '../../hooks/useDraftState.js';
import { useCharacterLimit } from '../../hooks/useCharacterLimit.js';
import { draftFields } from '../../configs/characterStorage.js';
import { MAX_CHARACTERS } from '../../configs/characterLimit.js';
import { readDraft } from '../../utils/characterCreation/creationDraft.js';
import { buildSlots } from '../../utils/characterMapping.js';

export default function CreateCharacter() {
    const navigate = useNavigate();
    const location = useLocation();
    const { missing, goNext } = useStepFlow('info');
    const options = useCreationOptions();
    const requestedSlot = location.state?.orderIndex;
    const [orderIndex, setOrderIndex] = useDraftState(
        draftFields.orderIndex,
        () =>
            Number.isInteger(requestedSlot)
                ? String(requestedSlot)
                : readDraft(draftFields.orderIndex),
    );
    const [name, setName] = useDraftText('name');
    const [alignment, setAlignment] = useDraftText('alignment');
    const [notes, setNotes] = useDraftText('notes');

    const { characters, loading: slotsLoading } = useCharacterLimit();
    const slots = buildSlots(
        (characters ?? []).map((character) => ({
            name: character.name,
            orderIndex: character.order_index,
        })),
        MAX_CHARACTERS,
    );
    const isFree = (index) => index !== '' && slots[Number(index)] === null;
    const firstFree = slots.indexOf(null);
    const slot = isFree(orderIndex)
        ? orderIndex
        : firstFree === -1
          ? ''
          : String(firstFree);

    useEffect(() => {
        if (!slotsLoading && slot !== orderIndex) setOrderIndex(slot);
    }, [slot, orderIndex, slotsLoading, setOrderIndex]);

    useEffect(() => {
        if (requestedSlot !== undefined)
            navigate(location.pathname, { replace: true, state: null });
    }, [requestedSlot, navigate, location.pathname]);

    function handleSubmit(event) {
        event.preventDefault();
        if (missing.length) return;
        setOrderIndex(slot);
        setName(name.trim());
        setNotes(notes.trim());
        goNext();
    }

    return (
        <CreationStep step="info">
            <main className="creation-card" aria-labelledby="creation-title">
                <h1 id="creation-title">Informations</h1>
                <form
                    id="character-information-form"
                    onSubmit={handleSubmit}
                    noValidate
                >
                    <div className="creation-alignment creation-slot">
                        <label htmlFor="character-slot">Emplacement</label>
                        <select
                            id="character-slot"
                            name="characterSlot"
                            value={slot}
                            disabled={slotsLoading}
                            onChange={(event) =>
                                setOrderIndex(event.target.value)
                            }
                        >
                            {slots.map((character, index) => (
                                <option
                                    key={index}
                                    value={String(index)}
                                    disabled={character !== null}
                                >
                                    {character
                                        ? `Emplacement ${index + 1} — ${character.name} (occupé)`
                                        : `Emplacement ${index + 1}`}
                                </option>
                            ))}
                        </select>
                    </div>
                    <label htmlFor="character-name">Nom du personnage</label>
                    <input
                        id="character-name"
                        name="characterName"
                        placeholder="Nom"
                        value={name}
                        onChange={(event) => setName(event.target.value)}
                        required
                    />
                    <div className="creation-alignment">
                        <label htmlFor="character-alignment">Alignement</label>
                        <select
                            id="character-alignment"
                            name="characterAlignment"
                            required
                            value={alignment}
                            onChange={(event) =>
                                setAlignment(event.target.value)
                            }
                            aria-describedby={
                                alignment ? 'alignment-description' : undefined
                            }
                        >
                            <option value="">Choisir un alignement</option>
                            {alignmentNames(options).map((option) => (
                                <option key={option} value={option}>
                                    {option}
                                </option>
                            ))}
                        </select>
                        {!alignmentNames(options).length && (
                            <p role="alert">Aucun alignement disponible.</p>
                        )}
                        {alignment && (
                            <p id="alignment-description" aria-live="polite">
                                {alignmentDescription(options, alignment)}
                            </p>
                        )}
                    </div>
                    <div className="creation-alignment creation-notes">
                        <label htmlFor="character-notes">
                            Notes (optionnel)
                        </label>
                        <textarea
                            id="character-notes"
                            name="characterNotes"
                            value={notes}
                            onChange={(event) => setNotes(event.target.value)}
                        />
                    </div>
                </form>
            </main>
            <StepActions
                className="creation-home-actions"
                back={
                    <button type="button" onClick={() => navigate('/')}>
                        Retour à l’accueil
                    </button>
                }
                type="submit"
                form="character-information-form"
            />
        </CreationStep>
    );
}
