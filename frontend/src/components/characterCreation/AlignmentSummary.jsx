import { draftFields } from '../../configs/characterStorage.js';
import { readDraft } from '../../utils/characterCreation/creationDraft.js';

export default function AlignmentSummary() {
    return (
        <p className="race-summary-choice">
            Alignement : {readDraft(draftFields.alignment) || '—'}
        </p>
    );
}
