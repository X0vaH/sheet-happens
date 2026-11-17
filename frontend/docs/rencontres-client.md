# Rencontres client — Sheet Happens

Ce document consigne les décisions prises avec le client (Éthan) au fil
des rencontres.

> **Source de vérité.** En cas de conflit entre ce document et le
> [cahier des charges](cahier-des-charges.md), **ce document fait foi**.
> Le cahier des charges décrit la portée telle qu'établie au démarrage du
> projet, mais le client peut faire évoluer ses attentes en cours de
> route — les décisions les plus récentes prises en rencontre l'emportent
> toujours sur le document initial.

## Rencontre 1 — Réponses et préférences du client

Le client a semblé à l'aise et a apprécié les questions posées ainsi que
le déroulement de la rencontre. Il répondait avec facilité et comprenait
que l'équipe aurait beaucoup de travail ; il ne s'attendait donc pas à ce
que toutes les règles de D&D soient implémentées.

**Public cible.** Les membres des clubs du client, dont la majorité sont
des novices en D&D.

**Création de compte.** Un courriel et un mot de passe seulement. Des
champs additionnels sont possibles pour la clarté, mais doivent rester
optionnels. Aucune vérification du courriel n'est requise.

**Montée de niveau (1 à 5).** Un bouton « Monter de niveau » est présent
sur la fiche de personnage. Le site s'adapte automatiquement lorsque des
choix sont requis. La montée de niveau ne va que vers le haut (pas de
redescente) et une confirmation est demandée avant chaque montée.

**Génération des caractéristiques.** Deux méthodes disponibles :

- **Array standard** : valeurs prédéfinies, assignation libre par le
  joueur.
- **4d6** : lancer de dés.

**Modification manuelle des caractéristiques.** Libre et sans restriction
— implémentation jugée simple par le client. C'est important : les
rencontres de jeu ont lieu en présentiel, et le Dungeon Master peut
demander à un joueur d'ajuster ses statistiques à la main.

**Calcul des statistiques.** Calculées à la fin seulement (pas en temps
réel) — ce n'est pas jugé critique par le client.

**Sous-classes.** Une seule sous-classe **par classe**. Aucune préférence
du client sur le choix des sous-classes proposées.

**Règles à faire respecter.** L'application doit empêcher les choix
inutiles ou handicapants, par exemple :

- ne pas permettre d'attribuer un point de compétence à une maîtrise déjà
  acquise via la race ;
- ne pas permettre de choisir un équipement (ex. une armure) sans
  posséder la maîtrise correspondante.

**Création de personnage.** Séquentielle (étape par étape), mais
l'utilisateur doit pouvoir revenir en arrière.

**Descriptions pédagogiques.** Le client souhaite des descriptions pour
les sorts, les classes et les autres éléments de création, afin de bien
expliquer ces notions aux nouveaux joueurs.

### Choix artistiques

- Palette de couleurs retenue (tons bleu-vert sourds, du foncé au pâle) :
  `#1F363D`, `#40798C`, `#70A9A1`, `#9EC1A3`, `#CFE0C3`.
- Le style d'interface proposé convient au client ; il apprécie
  particulièrement l'étoile affichée sur la caractéristique dominante du
  personnage.
- Le client préfère afficher les 16 compétences (skills) plutôt que les
  armes et armures, jugées moins importantes.
- Le style d'assignation des caractéristiques a été adapté selon ses
  attentes (voir maquettes).
- Il n'est pas obligatoire de multiplier les icônes si le temps manque,
  mais les info-bulles (« overs ») d'explication sont très appréciées et
  doivent être conservées.

## Rencontre 2 — Maquettes et retours visuels

**Points du jour** : présenter les maquettes, poser des questions
supplémentaires, formuler les promesses pour la semaine suivante.

**Inspiration** : DnD Beyond.

**Opinion générale sur les maquettes.** Interface jugée un peu basique —
trop uniforme, fond « flat », très vide. Les couleurs se ressemblent
trop. Le client demande une palette noir/gris avec des couleurs
d'accent.

**Page de connexion/création de compte.** Un peu vide — ajouter une
image en fond, en transparence.

**Page d'accueil.** Arrondir les coins de la navbar pour correspondre au
reste du style visuel.

**Fiche de personnage.**

- Ajouter la perception passive, la vitesse, les feats et les
  modificateurs dans la section des caractéristiques.
- Le carrousel de personnages ne doit pas être « swipable » s'il n'y a
  qu'un seul personnage.

**Page de paramètres.** Ne pas inclure le changement de courriel.
Réduire le nombre de champs et de boutons affichés.

**Choix de montée de niveau.**

- Déplacer l'interface de montée de niveau directement sur la fiche de
  personnage.
- Ajouter le choix des points de vie gagnés (ils ne montent pas
  automatiquement).

**Choix de sous-classe.** L'affichage est trop condensé pour le mobile —
le présenter à la verticale sur petit écran.

**Choix de sort.** Ajouter la description du sort (et faire de même pour
les feats et les bonus de race).

**Choix au niveau 4.** Afficher également le choix de feats à ce niveau.

### Questions supplémentaires

**Q : Où ont lieu les rencontres de jeu — en présentiel ou en ligne ?**
R : En personne. Les joueurs créent leur personnage seuls sur
l'application, puis se rendent chez quelqu'un pour la rencontre D&D. Il
est donc important de permettre à l'utilisateur de modifier ses
statistiques à la main si le Dungeon Master le demande (voir aussi
Rencontre 1).

### Engagements pour la semaine suivante

- Affichage/entité du personnage (pas encore la création).
- Authentification, si le temps le permet.
- Redesign des maquettes pour correspondre aux attentes du client.

## Rencontre 3 — Feedback sur les maquettes et démo

**Affichage général.** L'affichage est jugé plus propre et moins
« flat » qu'avant.

**Fiche de personnage.**

- Les capacités et traits devraient avoir leur propre case, car il peut
  y en avoir plusieurs.
- Les autres maîtrises (langues / équipement) et les capacités
  devraient être regroupées au même endroit : les capacités et traits
  restent à leur emplacement actuel, le reste descend en bas des autres
  cases, dans la boîte à boutons.
- Un nouvel onglet pour les actions possibles (attaques, sorts, etc.)
  est souhaité — si le temps le permet.
- Au minimum, afficher les sorts sur la fiche est suffisant ; ce n'est
  plus une obligation stricte (contrairement au souhait initial du
  client).

**Démo.** La « slot » vide pour un personnage non existant (emplacement
libre dans la liste de personnages) est approuvée telle quelle.

**Maquette de création de personnage.**

- Les méthodes de génération de statistiques à prévoir sont : array
  standard et 4d6 (les dés ne sont pas prioritaires), ainsi qu'un mode
  manuel.
- Les choix de compétences à maîtriser doivent apparaître à la fois dans
  la page de race et dans la page de classe.

### Promesses pour la suite

- Une page de création de personnage permettant de se déplacer à travers
  les différents onglets.
- Une authentification fonctionnelle.

## Rencontre 4

### Problèmes identifiés

- La boîte d'informations du personnage (partie saisie/input) est trop
  grosse.
- Le format de page varie et change la taille de l'image de fond selon
  la page.
- Lors du lancer de dés (aléatoire ou standard), le joueur doit pouvoir
  choisir où placer chacun de ses chiffres.
- Permettre le choix d'une langue dans la section historique du
  personnage.

### Pour la prochaine rencontre

- Sauvegarder un personnage dans la base de données.
