export const fightingStyles = [
    {
        name: 'Archerie',
        description: '+2 aux jets d’attaque avec les armes à distance.',
    },
    {
        name: 'Défense',
        description: '+1 CA lorsque le personnage porte une armure.',
    },
    {
        name: 'Duel',
        description:
            '+2 dégâts avec une arme de mêlée tenue à une main lorsque l’autre main ne tient pas d’autre arme.',
    },
    {
        name: 'Combat à deux armes',
        description:
            'Ajoutez le modificateur de caractéristique aux dégâts de l’attaque effectuée avec la seconde arme.',
    },
    {
        name: 'Combat avec arme à deux mains',
        description:
            'Permet de relancer certains résultats faibles sur les dés de dégâts d’une arme utilisée à deux mains.',
    },
    {
        name: 'Protection',
        description:
            'Utilisez votre réaction avec un bouclier pour gêner une attaque visant un allié proche.',
    },
];

export function getFightingStyle(name) {
    return fightingStyles.find((style) => style.name === name);
}
