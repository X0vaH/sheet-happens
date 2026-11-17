import StepActions from '../../components/characterCreation/StepActions.jsx';
import CreationStep from '../../components/characterCreation/CreationStep.jsx';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import {
    automaticEquipmentGroups,
    equipmentChoiceGroups,
} from '../../utils/characterCreation/catalog.js';
import ItemList from '../../components/characterCreation/ItemList.jsx';
import IdentityAside from '../../components/characterCreation/IdentityAside.jsx';
import { useCreationDraft } from '../../hooks/useCreationDraft.js';
import OptionGroupPanel from '../../components/characterCreation/OptionGroupPanel.jsx';
import { useClassDraftArray } from '../../hooks/useDraftState.js';

export default function ChooseEquipment() {
    const options = useCreationOptions();
    const { characterClass } = useCreationDraft();
    const groups = equipmentChoiceGroups(options, characterClass);
    const automatic = automaticEquipmentGroups(options, characterClass);
    const [equipment, setEquipment] = useClassDraftArray(
        'equipment',
        characterClass,
        (saved) =>
            groups.map((group, index) =>
                group.options.some((option) => option.id === saved[index])
                    ? saved[index]
                    : null,
            ),
    );

    function selectOption(index, optionId) {
        const next = equipment.map((current, slot) =>
            slot === index ? optionId : current,
        );
        setEquipment(next);
    }

    return (
        <CreationStep step="equipment">
            <main
                className="equipment-layout"
                aria-labelledby="equipment-title"
            >
                <IdentityAside className="equipment-summary" />
                <div className="equipment-content">
                    {groups.map((group, index) => (
                        <OptionGroupPanel
                            key={group.id}
                            id={`equipment-group-${group.id}`}
                            className="equipment-section"
                            title={group.label}
                            options={group.options.map((option) => ({
                                id: option.id,
                                name: option.label,
                                ariaLabel: option.label,
                                detail: (
                                    <ItemList
                                        items={option.items}
                                        label={option.label}
                                    />
                                ),
                            }))}
                            isSelected={(option) =>
                                equipment[index] === option.id
                            }
                            onSelect={(option) =>
                                selectOption(index, option.id)
                            }
                        />
                    ))}
                    {automatic.length > 0 && (
                        <section
                            className="race-panel equipment-section"
                            aria-labelledby="equipment-automatic-title"
                        >
                            <h2 id="equipment-automatic-title">
                                Équipement de départ
                            </h2>
                            <div className="equipment-detail-grid">
                                {automatic.map((group) => (
                                    <div
                                        className="equipment-detail-card"
                                        key={group.id}
                                    >
                                        <strong>{group.label}</strong>
                                        <ItemList
                                            items={group.options[0].items}
                                            label={group.label}
                                        />
                                    </div>
                                ))}
                            </div>
                        </section>
                    )}
                </div>
            </main>
            <StepActions className="equipment-actions" />
        </CreationStep>
    );
}
