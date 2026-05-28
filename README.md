# PBD — Annuaire 2.1 — SVG Inline

> Version **2.1** du plugin annuaire Pays Basque Digital.
> Cette version supprime toutes les dépendances aux assets externes (Material Icons + Font Awesome) en les remplaçant par des SVG inline.

---

## 🎯 Objectif de cette version

**Zéro dépendance externe** sur les icônes — uniquement les fonts Syne et DM Sans (hébergées localement via plugin OMGF côté WordPress).

Cohérent avec l'identité "numérique responsable" portée par l'asso et améliore :
- 🛡️ **RGPD** : plus aucune requête vers Google Fonts (`fonts.googleapis.com`) ou Cloudflare CDN
- ⚡ **Performance** : 4 requêtes réseau externes supprimées par page
- 📦 **Légèreté** : ~5 Ko de SVG inline vs ~130 Ko de Material Icons + Font Awesome
- 🔧 **Maintenance** : icônes centralisées dans une seule fonction PHP (`pbd_icon()`)

---

## 📋 Changements vs version 2.0

| Snippet | Changement |
|---|---|
| **01** | ➕ Ajout de la fonction helper `pbd_icon($name, $class)` avec 14 icônes SVG inline |
| **02** | ❌ Suppression des `wp_enqueue_style('material-icons', ...)` et `('font-awesome', ...)` <br>🔁 Remplacement de toutes les `<span class="material-icons">x</span>` et `<i class="fab fa-linkedin">` par `<?= pbd_icon('x') ?>` |
| **03** | 🔁 Remplacement des chevrons `chevron_left`/`chevron_right` dans le JS par leur SVG inline (constantes `ICON_CHEVRON_LEFT/RIGHT`) |
| **04** | 🔁 Sélecteurs CSS `.material-icons` → `.pbd-icon`, sizing via `width/height` au lieu de `font-size` |
| **05** | ❌ Suppression des enqueues Material Icons et Font Awesome <br>🔁 Toutes les icônes utilisent désormais `pbd_icon()` |
| **06** | 🔁 Sélecteurs CSS mis à jour, ajout du bloc base `.fiche-pro .pbd-icon` |
| **07** | ✅ Aucun changement (pas d'icônes utilisées dans ce snippet) |

---

## 🛠️ Procédure de migration depuis 2.0

1. **Dans Code Snippets WP**, désactive temporairement tous les snippets 01-07
2. Pour chaque snippet **01 à 07**, copie-colle le contenu du fichier correspondant dans cette version 2.1
3. Réactive les snippets dans l'ordre (01 → 07)
4. **Si tu utilisais OMGF** pour Google Fonts → vérifie qu'il continue de servir Syne et DM Sans (les seules fonts externes restantes)
5. Vide les caches (navigateur + plugin de cache éventuel) et teste

---

## ✅ Tests de validation

Après migration, vérifier dans le navigateur (F12 → Network → Filter "External") :

| Domaine | Avant 2.1 | Après 2.1 |
|---|---|---|
| `fonts.googleapis.com/icon?family=Material+Icons` | ⚠️ Requête présente | ✅ Aucune requête |
| `cdnjs.cloudflare.com/.../font-awesome` | ⚠️ Requête présente | ✅ Aucune requête |
| `fonts.googleapis.com/css2?family=Syne` | ⚠️ Vers Google | ✅ Servie en local (OMGF) |
| `fonts.googleapis.com/css2?family=DM+Sans` | ⚠️ Vers Google | ✅ Servie en local (OMGF) |

---

## 🎨 Liste des icônes disponibles

Définies dans `01-cpt-adherent.php` via `pbd_icons_library()` :

- `arrow_back` — flèche retour
- `search` — loupe
- `tune` — réglages (filtres)
- `chevron_left` / `chevron_right` — chevrons (carrousel + pagination)
- `expand_more` — flèche bas (toggle filtre mobile)
- `location_on` — pin localisation
- `email` — enveloppe
- `language` — globe (site web)
- `search_off` — recherche sans résultat
- `person` — silhouette (dirigeant)
- `group` — collaborateurs
- `phone` — téléphone
- `linkedin` — logo LinkedIn (brand)

**Pour ajouter une nouvelle icône** : édite le tableau `pbd_icons_library()` dans snippet 01.

---

## 📂 Files

```
01-cpt-adherent.php          CPT + helper pbd_icon() + 14 SVG icons
02-annuaire-shortcode.php    Shortcode [annuaire]
03-annuaire-js.php           Filtres + pagination JS
04-annuaire-css.php          CSS annuaire
05-page-pro-shortcode.php    Fiche pro adhérent
06-page-pro-css.php          CSS fiche pro
07-page-pro-js.php           Carrousel + presse-papier
```

Version 2.0 conservée à : `..\Pays-Basque-Digital-Annuaire-2.0\` (rollback possible).
