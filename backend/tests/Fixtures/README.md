# Référence des descriptions de sorts

`srd-spell-descriptions.json` conserve les champs `index`, `desc` et
`higher_level` des 139 sorts de Clerc/Magicien de niveaux 0 à 3, extraits
directement de la révision 5e-bits indiquée dans le fichier. Cette référence
est indépendante du JSON transformé utilisé par les seeders.

Le test utilise les identifiants de cette référence pour vérifier la liste
des 139 sorts attendus. Les descriptions du catalogue sont des résumés :
le test vérifie qu’elles sont non vides et conservées par les seeders et
l’API de montée de niveau. Les textes complets restent disponibles ici
comme référence documentaire.
Les tests ne téléchargent aucune donnée. Une mise à jour de la source exige
une révision explicite de cette référence et du catalogue.

Les attributions SRD 5.1 et MIT figurent dans
[SRD-LICENSE.md](../../database/seeders/ruleset/data/SRD-LICENSE.md).
