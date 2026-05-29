# PBD Annuaire — Présentation et guide de gestion

> Document à destination de la direction et de l'équipe administrative de Pays Basque Digital.
> Deux parties : d'abord une présentation du travail réalisé (sans jargon technique), ensuite le guide pour créer et gérer les fiches adhérents.

---

# Partie 1 — Le projet en quelques mots

## À quoi sert cet annuaire

C'est la vitrine en ligne des adhérents de Pays Basque Digital. Une page liste toutes les entreprises membres avec une recherche et des filtres (par catégorie, par ville). Chaque adhérent dispose en plus de sa propre fiche détaillée : présentation, dirigeant, clients, photos.

Deux objectifs concrets :
- **Visibilité** : chaque entreprise membre est mise en avant publiquement.
- **Mise en relation** : un visiteur (client potentiel, partenaire, journaliste) trouve rapidement les compétences qu'il cherche, même sans connaître le nom des entreprises.

## Ce qui a été refait, expliqué simplement

L'annuaire précédent reposait sur un plugin WordPress générique : recherche peu fiable, cartes de tailles irrégulières selon les logos, catégories aux libellés datés, et des images très lourdes qui ralentissaient les pages.

La refonte a repris l'ensemble sur mesure. Sans entrer dans le code, voici ce qui a changé côté coulisses :

- **Une recherche instantanée.** Le filtrage (nom, ville, catégorie) se fait directement dans le navigateur du visiteur, sans rechargement de page. C'est immédiat.
- **Des fiches homogènes.** Toutes les cartes ont les mêmes dimensions, quel que soit le logo. La grille reste régulière sur ordinateur comme sur mobile, et les sections vides sont gérées proprement (mention "Informations à venir") plutôt que de laisser des trous.
- **Des pages beaucoup plus légères.** Les pages sont mises en cache (mémorisées) pour s'afficher instantanément, et les images ne se téléchargent qu'au moment où elles deviennent visibles à l'écran. Résultat : une page d'annuaire qui pesait environ 25 Mo en pèse aujourd'hui 3 à 4, soit près de six fois moins.
- **Une mise à jour automatique.** Quand une fiche est modifiée, la version publique se met à jour toute seule en quelques secondes. Aucun cache à vider à la main.
- **Une protection raisonnable des images.** Les photos de couverture et d'ambiance ne se téléchargent plus d'un simple clic droit. Les logos, eux, restent libres : c'est volontaire, pour que la presse et les partenaires puissent les réutiliser facilement.

## Conformité RGPD

C'est l'un des points les plus importants du projet. L'ancien site chargeait, à chaque visite, des icônes et des polices d'écriture depuis des serveurs externes (Google, Cloudflare). Ces simples chargements transmettaient l'adresse IP de chaque visiteur à des sociétés américaines, ce qui constitue une non-conformité au RGPD dès lors qu'il n'y a pas de bannière de consentement.

Désormais, tout est servi depuis nos propres serveurs : les icônes ont été recréées directement dans le site, et les polices sont hébergées localement. Plus aucune donnée de visiteur ne part vers un tiers. Le site est conforme sans avoir besoin d'imposer une bannière de cookies pour ces éléments.

## Accessibilité

Le site a été construit pour rester consultable par tous les publics, y compris les personnes en situation de handicap : navigation possible au clavier, descriptions associées aux images, structure de page lisible par les lecteurs d'écran, contrastes et zones cliquables suffisants. Au-delà du principe d'inclusion, c'est aussi un argument concret lorsqu'un adhérent répond à un appel d'offres qui interroge l'accessibilité de ses partenaires.

## Numérique responsable

Allègement des pages, suppression des dépendances externes, images compressées et chargées seulement quand elles sont utiles : l'ensemble réduit la consommation de données et l'empreinte du site. Cette sobriété est cohérente avec l'identité de numérique responsable portée par le cluster, et elle profite directement aux visiteurs sur mobile ou en zone à connexion limitée.

## Quelques repères chiffrés

| Indicateur | Annuaire précédent | Refonte |
| --- | --- | --- |
| Poids d'une page d'annuaire | ~25 Mo | ~3-4 Mo |
| Requêtes vers des serveurs externes | 4 à 6 | 0 |
| Conformité RGPD sans bannière | Non | Oui |
| Expérience mobile | Lente | Fluide |

## Pérennité et maintenance

- Le code est **modulaire** : chaque fonctionnalité est isolée, donc si une partie pose problème, le reste continue de fonctionner.
- Il est **documenté** pour trois publics (technique, direction, adhérents), afin qu'un autre développeur puisse reprendre le projet sans repartir de zéro.
- Il ne dépend d'**aucune plateforme tierce critique** qui pourrait disparaître.

L'objectif visé : un site qui tienne plusieurs années sans intervention majeure, et que n'importe quel développeur WordPress puisse reprendre.

---

# Partie 2 — Créer et gérer les fiches adhérents

La direction est en charge de la création et de la mise à jour des fiches (~84 entreprises). Cette partie est la référence à suivre pour un remplissage homogène et professionnel.

## Principe directeur

Chaque fiche est une vitrine pour l'adhérent et pour l'image de l'association. Trois principes à garder en tête :

1. **Cohérence visuelle** entre toutes les fiches (formats, longueurs, styles).
2. **Précision factuelle** : ne jamais inventer ni broder.
3. **Ancrage Pays Basque** valorisé systématiquement quand l'adhérent y est implanté.

Règle de secours valable partout : **toujours préférer "vide" à "inventé"**. Une fiche partielle mais juste vaut mieux qu'une fiche pleine d'approximations.

## Dimensions des images

**Logo**
- Dimensions : 400 × 400 px (carré).
- Format : PNG transparent (idéal sur fond clair).
- Poids cible : moins de 100 Ko.
- Rendu : image centrée, ni rognée ni déformée. Si le logo est très compact (texte tassé), demander une version avec un peu de marge interne.

**Cover (photo de couverture)**
- Dimensions : 1600 × 400 px (paysage très large, ratio ~4:1).
- Format : JPG ou WebP.
- Poids cible : moins de 300 Ko.
- Rendu : l'image remplit toute la zone et le surplus est rogné. Placer l'élément important au centre vertical ; sinon, ajuster le champ `cover_position` (top / center / bottom). Sur mobile, la couverture est volontairement moins haute pour préserver la largeur des bannières.

**Photos d'ambiance (3 maximum)**
- Dimensions : 1600 × 900 px (16:9).
- Format : JPG ou WebP.
- Poids cible : moins de 250 Ko par photo.
- Rendu : carrousel, sujet à centrer.

Outils de compression : Squoosh (squoosh.app) ou TinyPNG (tinypng.com).

## Catégories (champ specialites)

Onze catégories disponibles : Éditeur de logiciel ; Agence digitale ; Studio immersif et XR ; ESN et intégrateur ; Conseil et expertise ; Formation et enseignement ; Infrastructure et cloud ; Cybersécurité ; IA et data ; Objets connectés et industrie ; E-commerce et marketplace.

Règle d'or : **les cases décrivent le métier-cœur de l'entreprise, pas un service annexe.**

Combien cocher :
- PME locale : 1 à 3 cases maximum (rester focalisé).
- ETI ou grand groupe : jusqu'à 4 cases si plusieurs piliers réels.

Pièges fréquents :
- Une entreprise qui vend du matériel IT en ligne en parallèle de son ESN → cocher "ESN et intégrateur", pas "E-commerce".
- Une agence qui crée des sites e-commerce → cocher "Agence digitale", pas "E-commerce".
- Une ESN qui forme ses clients aux outils livrés → cocher "ESN et intégrateur", pas "Formation".
- Un éditeur qui fait du sur-mesure autour de ses produits → cocher "Éditeur de logiciel", pas "ESN".

Cas "Formation et enseignement" — cocher **uniquement si les deux critères sont remplis** :
1. Certification Qualiopi (ou équivalent officiel).
2. Section "Formation" visible et structurée sur le site (pas une simple mention).
Exemples : écoles comme ESTIA, Simplon, UPPA, AFEC, GRETA, Digital Campus, ou Hizkia et Pic Digital (Qualiopi + section dédiée). À l'inverse, une entreprise qui forme ses clients à ses outils de façon accessoire ne coche pas cette case.

Cas "E-commerce et marketplace" — cocher uniquement si le cœur de métier est d'opérer une plateforme e-commerce (Poplidays, Wikicampers, Batcher.ia), pas si l'entreprise vend du matériel en ligne en complément d'une autre activité.

## Tags (champ tags)

Règle d'or : **3 tags maximum, aussi courts et parlants que possible.**

À privilégier : tags courts (1-2 mots), vocabulaire métier moderne (Observabilité, DevOps), stack technique différenciante (Magento, Power BI, WordPress), secteurs servis (Camping, Hôtellerie, Aéronautique).

À éviter : buzzwords datés (pure player, synergie), termes génériques (Innovation, Solutions, Performance), doublons avec les catégories déjà cochées, anglicismes inutiles.

Si aucune information fiable : laisser vide. Mieux vaut une fiche sans tag que des tags inventés.

## Description (champ description, "À propos")

Format type : 3 à 4 paragraphes, 140 à 200 mots.
- Paragraphe 1 : positionnement, identité, fondation, ancrage géographique.
- Paragraphe 2 : services, expertises, produits (précis et concrets).
- Paragraphe 3 : différenciation, proposition de valeur, clients prestige. Quatrième paragraphe optionnel.

À faire : mentionner l'ancrage Pays Basque dès le premier paragraphe si pertinent ; citer des chiffres concrets (effectifs, ancienneté, clients) ; mettre en gras les éléments différenciants ; conclure sur la proposition de valeur ; préférer des phrases fluides aux listes à puces.

À éviter : recopier tel quel le texte du site ou de LinkedIn (toujours reformuler) ; les emojis ; les superlatifs invérifiables ("leader incontesté") ; dépasser 200 mots.

## Verbatim (champs verbatim et verbatim_auteur)

Règle d'or : préférer une citation officielle attribuée à une personne nommée ; à défaut, l'attribuer à la voix collective. **Ne jamais inventer.**

Sources : page "À propos" ou "Qui sommes-nous", page "Équipe" ou "Direction", slogan officiel, articles de presse.

Attribution : "Prénom Nom, Fonction" si la citation est attribuée ; "Nom de l'entreprise" ou "L'équipe …" pour un slogan collectif ; pour un fondateur unique, la voix collective peut lui être attribuée.

Si rien d'exploitable : laisser le champ vide (la section "Le mot du fondateur" ne s'affiche pas). En dernier recours, un court mail à l'adhérent suffit souvent à obtenir une phrase authentique.

## Clients (champ clients, 5 maximum)

Règle d'or : 5 clients maximum, classés par impact décroissant, en privilégiant la diversité sectorielle.

Mix idéal pour une ESN, une agence ou un cabinet : 1 à 2 grands noms prestigieux, 1 à 2 acteurs sectoriels distincts, 1 acteur régional ou Pays Basque quand c'est pertinent.

Ordre : position 1 le plus reconnu (première impression), positions 2 à 4 la diversité sectorielle, position 5 l'ancrage local.

Sources : page "Nos clients" ou "Références", témoignages publics (Trustfolio, Sortlist), études de cas, presse.

Si aucun client public : laisser vide (la section ne s'affiche pas), compléter plus tard si l'adhérent transmet des noms, ne jamais inventer.

## Coordonnées (ville, adresse, code postal, téléphone, email)

Priorité à l'ancrage Pays Basque : pour une entreprise multi-bureaux, toujours privilégier le bureau local pour la ville, l'adresse et le code postal, et un numéro en 05 (ou à défaut le numéro principal).

Format :
- adresse : "1 Rue de Donzac" (sans virgule, sans code postal, sans ville).
- code_postal : 5 chiffres.
- ville : initiale en majuscule (Bayonne).
- telephone : espacé tous les deux chiffres (05 59 50 00 44).
- email : en minuscules.

## Dirigeant (champ dirigeant)

Format : une personne → "Prénom Nom" ; collectif de co-fondateurs → "Prénom Nom · Prénom Nom" (séparateur point médian) ; fonction optionnelle → "Prénom Nom, Fonction".

Logique : privilégier le dirigeant local si multi-bureaux ; pour une filiale, citer le directeur de la filiale et non le PDG du groupe ; en co-direction, citer tous les co-dirigeants.

Vérification : page "Équipe" du site, annuaire-entreprises.data.gouv.fr (donnée INSEE), societe.com ou pappers.fr, LinkedIn.

## Nombre de collaborateurs (champ nb_employes)

Source : le site de l'adhérent s'il communique le chiffre, sinon la tranche INSEE via annuaire-entreprises.data.gouv.fr. Saisir le milieu de fourchette (par exemple 30 pour la tranche 20-49), ou la valeur précise communiquée publiquement. Pour un indépendant, laisser vide (la ligne ne s'affiche pas, plus propre que "1 collaborateur").

## Position de la cover (champ cover_position)

Trois valeurs, "center" par défaut : "top" si le sujet important est en haut (visages, logo), "center" dans la plupart des cas, "bottom" si le sujet est en bas. Ne pas hésiter à tester les trois après publication.

## Ordre de remplissage des champs

Pour faciliter la saisie, suivre l'ordre des champs tel qu'il apparaît dans l'administration :

1. specialites (catégories)
2. tags (3 maximum)
3. ville
4. email
5. linkedin
6. site_web
7. logo
8. cover
9. nb_employes
10. description
11. verbatim
12. verbatim_auteur
13. photo_ambiance_1, photo_ambiance_2, photo_ambiance_3
14. adresse
15. code_postal
16. cover_position
17. telephone
18. dirigeant
19. clients (5 maximum)

## Au quotidien

- **Ajouter un adhérent** : administration WordPress → Adhérents → Ajouter → remplir les champs dans l'ordre ci-dessus → Publier. La fiche apparaît immédiatement dans l'annuaire.
- **Modifier une fiche** : modifier les champs → enregistrer. La version publique se met à jour automatiquement en quelques secondes.
- **Retirer temporairement une fiche** : la repasser en brouillon (invisible publiquement) sans la supprimer ; les données restent intactes.
- **Si rien ne bouge après une modification** : il s'agit presque toujours du cache du navigateur. Forcer le rechargement avec Ctrl+F5 (PC) ou Cmd+Shift+R (Mac). Si le problème persiste, voir avec un développeur (cas très rare).

## Check-list avant publication

- Logo carré 400 × 400 px présent.
- Cover 1600 × 400 px présente.
- Titre = nom exact de l'entreprise.
- 1 à 4 catégories cochées, cohérentes avec le métier-cœur.
- 3 tags maximum, courts et parlants.
- Ville renseignée (bureau Pays Basque en priorité).
- Description de 3 à 4 paragraphes (140 à 200 mots).
- Verbatim et auteur cohérents, ou les deux vides.
- Dirigeant renseigné.
- 5 clients maximum classés par impact, ou champ vide.
- Coordonnées de contact opérationnelles.

En cas de doute sur un choix délicat (catégorie ambiguë, sélection de clients, angle de la description), trancher avec le référent du projet avant de publier.
