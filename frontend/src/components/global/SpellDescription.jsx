export default function SpellDescription({ spell }) {
    return (
        <>
            <strong>{spell.name}</strong>
            {spell.school && <p>École : {spell.school}</p>}
            {spell.casting_time && (
                <p>Temps d’incantation : {spell.casting_time}</p>
            )}
            {spell.range && <p>Portée : {spell.range}</p>}
            {spell.duration && (
                <p>
                    Durée : {spell.duration}
                    {spell.concentration ? ' (concentration)' : ''}
                </p>
            )}
            {spell.description && <p>{spell.description}</p>}
        </>
    );
}
