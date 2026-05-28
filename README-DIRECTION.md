# PBD Annuaire — Vue d'ensemble

> Pour la direction et les membres administratifs. Pas besoin d'être technicien pour comprendre.

---

## 🎯 À quoi sert cet annuaire ?

C'est la **vitrine en ligne des adhérents** de Pays Basque Digital. Une page liste toutes les entreprises membres avec un système de recherche et de filtres (par catégorie, par ville). Chaque adhérent dispose en plus de sa **propre fiche détaillée** avec présentation, équipe, clients, photos.

Concrètement, ça remplit deux objectifs :
- **Visibilité** : chaque entreprise membre est mise en avant publiquement
- **Mise en relation** : un visiteur (client potentiel, partenaire, journaliste) trouve rapidement les compétences qu'il cherche

---

## ✅ Ce qui a été amélioré dans la version actuelle (2.1)

### 1. Conformité et indépendance

Avant, le site chargeait des icônes et polices depuis **Google et Cloudflare** à chaque visite. Problème : ces requêtes envoient l'adresse IP des visiteurs vers des serveurs américains → **non-conformité RGPD** sans bannière de consentement.

Aujourd'hui, **tout est servi depuis nos propres serveurs**. Plus aucune fuite de données utilisateurs vers des tiers. C'est cohérent avec l'identité "numérique responsable" portée par l'association.

### 2. Vitesse de chargement

Les pages sont devenues **beaucoup plus rapides** :
- Les pages sont **mises en cache** pendant 1 heure (au lieu de recalculer à chaque visite)
- Les **images sont chargées intelligemment** : seules celles visibles à l'écran se téléchargent
- Les **icônes sont allégées** (130 Ko → 5 Ko)

**Résultat concret** : une page qui pesait ~25 Mo en pleine charge pèse aujourd'hui ~3-4 Mo. C'est environ **6 fois moins lourd**, donc une expérience plus rapide même sur mobile ou en zone à faible connexion.

### 3. Mise à jour automatique

Quand un adhérent modifie sa fiche, **le site se met à jour tout seul**. Pas besoin de prévenir, de vider un cache manuellement, ou de redémarrer quoi que ce soit. Le visiteur suivant voit la nouvelle version immédiatement.

### 4. Protection des images

Les **photos de couverture et photos d'ambiance** des fiches adhérents ne peuvent plus être téléchargées d'un simple clic droit. C'est une protection raisonnable qui décourage la réutilisation sauvage, sans pour autant rendre le site inutilisable.

**Les logos restent libres** : c'est volontaire — l'objectif promotionnel de l'asso veut justement que les logos circulent et soient partagés (presse, partenaires, réseaux sociaux).

### 5. Présentation cohérente

Toutes les fiches respectent la même mise en page : couverture en bandeau, logo, présentation, équipe, clients, photos d'ambiance. Quelles que soient les infos remplies par l'adhérent, le rendu reste propre — même si certaines sections sont vides (mention "Informations à venir").

---

## 🔧 Comment ça fonctionne au quotidien ?

### Ajouter un nouvel adhérent

1. Aller dans l'admin WordPress → **Adhérents** → **Ajouter**
2. Remplir les champs (nom, logo, couverture, catégories, description, etc. — il y a une notice de complétion dédiée)
3. Publier

L'adhérent apparaît immédiatement dans l'annuaire public.

### Modifier une fiche existante

Idem : modifier les champs → enregistrer → la fiche publique se met à jour automatiquement.

### Si quelque chose semble bloqué

99% du temps, c'est un cache navigateur. Solution : **Ctrl+F5** (PC) ou **Cmd+Shift+R** (Mac) pour forcer le rechargement.

Si vraiment rien ne bouge après une modif : voir avec un dev. Cas très rare.

---

## 📊 Quelques chiffres

| Indicateur | Avant 2.1 | Après 2.1 |
|---|---|---|
| Poids d'une page annuaire | ~25 Mo | ~3-4 Mo |
| Requêtes vers serveurs externes (Google, etc.) | 4-6 | 0 |
| Conformité RGPD sans bannière | ❌ | ✅ |
| Temps de chargement perçu | Lent sur mobile | Fluide |

---

## 🤝 Confiance et maintenance

- Le code est **modulaire** : chaque fonctionnalité est isolée. Si une partie casse, le reste continue de fonctionner.
- Le code est **documenté** : un autre développeur peut reprendre le projet sans repartir de zéro.
- Les **anciennes versions sont conservées** côté serveur : retour arrière possible en quelques minutes si besoin.
- **Aucune dépendance critique** à une plateforme tierce qui pourrait disparaître.

L'objectif : que ce site **tienne plusieurs années sans intervention majeure**, et que **n'importe quel dev WordPress** puisse reprendre la main si besoin.

---

## ❓ Questions fréquentes

**On peut héberger combien d'adhérents ?**
Le système gère sans problème **200+ adhérents** avec la même fluidité que les ~84 actuels. La pagination (12 par page) protège la performance.

**Les visiteurs voient toujours les bonnes infos ?**
Oui. Le système de cache se rafraîchit automatiquement dès qu'une fiche est modifiée. Et toutes les heures sans modif, il se renouvelle de toute façon.

**On est en sécurité côté piratage ?**
Toutes les données utilisateur sont nettoyées avant affichage (anti-XSS), les URLs sont validées, et le script d'import qui posait un risque de sécurité a été supprimé. Standard WordPress sain.

**Et si on veut changer le design plus tard ?**
Le style est isolé dans 2 fichiers (un pour l'annuaire, un pour les fiches). Un dev peut modifier les couleurs, espacements, polices sans toucher au reste.
