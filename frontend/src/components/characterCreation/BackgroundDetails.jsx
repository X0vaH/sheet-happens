import DetailList from './DetailList.jsx';

// Description of the background picked on the background step (`profile` is its catalog entry).
export default function BackgroundDetails({ name, profile }) {
    return (
        <div className="background-description" aria-live="polite">
            <h2>{name || 'Détails de l’historique'}</h2>
            {profile ? (
                <>
                    <p>{profile.description}</p>
                    <dl>
                        <DetailList
                            label="Compétences maîtrisées"
                            items={profile.skills}
                        />
                        <DetailList
                            label="Outils maîtrisés"
                            items={profile.tools}
                        />
                        <DetailList
                            label="Don d’origine"
                            items={profile.feats}
                            describe={(feat) => feat.description}
                        />
                        <DetailList
                            label="Caractéristiques associées"
                            items={profile.abilities}
                        />
                    </dl>
                </>
            ) : (
                <p>Choisissez un historique pour consulter ses informations.</p>
            )}
        </div>
    );
}
