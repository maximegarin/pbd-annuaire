# PBD Annuaire — Refonte de l'annuaire du cluster Pays Basque Digital

Refonte complète de l'annuaire en ligne des ~84 entreprises adhérentes du cluster numérique **Pays Basque Digital**. Conçu et développé en autonomie : architecture, UX, intégration, optimisations performance, conformité RGPD, accessibilité.

Solution validée en assemblée générale par la direction de l'association.

[![WordPress](https://img.shields.io/badge/WordPress-6.x-21759B?logo=wordpress&logoColor=white)](#)
[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white)](#)
[![ACF](https://img.shields.io/badge/ACF-Pro-00D3AE)](#)
[![JavaScript](https://img.shields.io/badge/JavaScript-vanilla-F7DF1E?logo=javascript&logoColor=black)](#)
[![RGPD](https://img.shields.io/badge/RGPD-conforme-success)](#)
[![A11y](https://img.shields.io/badge/WCAG-AA-success)](#)

**En production** : [pays-basque-digital.fr/annuaire](https://pays-basque-digital.fr/annuaire/)

---

## Contexte

Pays Basque Digital fédère ~84 entreprises du numérique régional. L'annuaire en ligne est leur vitrine collective et le principal point d'entrée pour qu'un visiteur (client, partenaire, presse) découvre les acteurs du territoire.

L'annuaire existant — un plugin répertoire WordPress générique — présentait des limites fonctionnelles et UX qui nuisaient à cet objectif de visibilité.

---

## Existant vs refonte

| Axe | Annuaire existant | Refonte |
| --- | --- | --- |
| **Recherche** | Champ peu fiable, peu visible | Filtrage client-side instantané (nom, ville, catégorie), zéro rechargement |
| **Catégorisation** | Catégories seules, aux libellés datés et jargonneux ("Pure Player", "Partenaire") | **Deux niveaux** : 11 catégories métier claires **+ tags d'activité concrets** (ex : "WordPress", "Cybersécurité", "Camping") |
| **Lisibilité des cartes** | Logo + nom + catégorie générique → on ne sait pas *ce que fait précisément* l'entreprise avant de cliquer | Logo, catégories, **tags d'activité**, ville, liens directs (email/LinkedIn/site) — l'activité se lit d'un coup d'œil |
| **Régularité de la grille** | Taille des cartes **variable selon le logo** → grille irrégulière, accentuée en responsive | Cartes à **dimensions fixes** (cover + avatar normalisés) → grille uniforme sur tous les écrans |
| **Fiches vides** | Certaines cartes blanches (ni logo ni titre) | États vides gérés ("Informations à venir"), fallback initiales colorées |
| **Responsive** | Layout bureau, adaptation mobile incertaine | Mobile-first, filtres repliables, grille fluide |
| **Identité visuelle** | Design générique de plugin | Design system propre (Syne + Inter, palette, composants cohérents) |
| **Dépendances externes** | Material Icons + Font Awesome + Google Fonts (CDN) | **Zéro CDN** : SVG inline + fonts self-hostées (OMGF) |
| **Performance** | Toutes les images chargées d'emblée | Cache HTML + lazy loading + images dimensionnées |

L'objectif n'était pas un simple "reskin" mais une refonte qui adresse chaque friction par une décision technique mesurable.

---

## Stack

| Couche | Choix |
| --- | --- |
| CMS | WordPress 6.x |
| Données | ACF Pro (CPT `adherent`, 21 champs : Repeater, Checkbox, Image, etc.) |
| Back | PHP 7.4+ (shortcodes, hooks, Transient API) |
| Front | JavaScript vanilla (filtrage, pagination, carrousel) — zéro framework |
| Styles | CSS3 scoped, injecté conditionnellement via `has_shortcode()` |
| Icônes | SVG inline (helper `pbd_icon()`) — zéro icon-font |
| Distribution | 7 snippets autonomes (plugin Code Snippets) |

---

## Architecture

Plugin distribué en **7 snippets indépendants**, chacun activable/désactivable isolément :

```
01-cpt-adherent.php        CPT adherent + helper pbd_icon() + 14 SVG inline
02-annuaire-shortcode.php  Shortcode [annuaire] : grille filtrable + cache HTML
03-annuaire-js.php         Filtrage client-side + pagination + anti-download
04-annuaire-css.php        CSS scoped annuaire
05-page-pro-shortcode.php  Fiche détaillée adhérent (SSR via template_redirect)
06-page-pro-css.php        CSS scoped fiche pro
07-page-pro-js.php         Carrousel photos + copie presse-papier
```

Principe : chaque snippet a une responsabilité unique, le CSS/JS n'est injecté que sur les pages concernées (`has_shortcode`, `is_singular`), pas de pollution globale du thème.

---

## Points techniques notables

### 1. Cache HTML via Transient API + invalidation automatique

La grille (84 cartes) et chaque fiche pro sont rendues une fois puis mises en cache (`set_transient`, 1h). Le cache est **bypassé pour les admins connectés** (édition = rendu frais) et **invalidé automatiquement** sur tout changement d'état d'un adhérent :

```php
add_action('save_post_adherent', /* delete_transient */);
add_action('trashed_post',       /* idem si CPT adherent */);
add_action('untrashed_post',     /* … */);
add_action('delete_post',        /* … */);
```

Résultat : **0 requête SQL** sur cache hit (visiteur anonyme), vs ~85 sans cache. Versioning de la clé (`_v7`) pour invalider proprement lors des évolutions de structure.

### 2. Filtrage et pagination 100% client-side

Les attributs `data-nom`, `data-ville`, `data-cat` sérialisés sur chaque carte permettent un filtrage instantané en JS pur, sans roundtrip réseau. Pagination par `display:none`. Combiné au lazy loading, **seules les images des 12 cartes visibles se téléchargent**.

Trade-off assumé : tout le HTML est dans le DOM initial (~60-80 Ko gzippé) en échange d'une réactivité totale du filtre.

L'état du filtre et la page courante sont mémorisés (`sessionStorage`) : revenir à l'annuaire depuis une fiche pro réaffiche exactement la sélection et la page que le visiteur consultait. Sur mobile, toute la surface de la carte est cliquable — le tap sur le logo comme sur le reste de la carte ouvre la fiche, sans zone morte.

### 3. Optimisation d'images avec détection de hard-crop

WordPress (ou un plugin de régénération) peut générer des tailles **hard-croppées** qui massacrent les logos rectangulaires. Un helper compare le ratio largeur/hauteur entre l'original et la taille générée, et ne sert la version optimisée que si elle préserve les proportions :

```php
$pick_size = function($img, $size) {
    if (/* ratio original ≈ ratio généré, tolérance 1% */) {
        return $img['sizes'][$size];   // optimisé
    }
    return $img['url'];                // fallback original, intact
};
```

→ Logos servis en `medium` (–75% de poids) quand c'est sûr, original sinon. Zéro logo recadré de travers.

### 4. Rendu serveur de la fiche pro + maîtrise du paint mobile

Le thème custom n'appelle pas `the_content()` sur les CPT : la fiche est rendue côté serveur via `template_redirect` (`get_header()` + HTML + `get_footer()`), le HTML est donc présent dès le premier byte de la réponse.

Une attention particulière a été portée au *first paint* sur Chrome/Safari mobile, sensible sur les fiches à cover lourde. La cover est traitée comme image LCP :
- servie en `<img>` (et non `background-image`) avec `fetchpriority="high"` + `<link rel="preload">` + `width/height` (anti-CLS) ;
- `transform: translateZ(0)` sur `.fiche-pro-cover` : la cover est isolée dans son propre layer GPU, le reste de la fiche peint indépendamment ;
- `history.scrollRestoration = 'manual'` + `window.scrollTo(0, 0)` : l'arrivée sur la fiche se fait toujours en haut de page.

Sur mobile, la hauteur de la cover est réduite pour limiter le recadrage horizontal des bannières larges — logos et texte placés sur les côtés restent visibles, sans recourir à des bandes.

### 5. Conformité RGPD : zéro dépendance externe

Suppression de **Material Icons + Font Awesome + Google Fonts** (toutes des requêtes vers des CDN US qui exposent l'IP des visiteurs) :
- Icônes → **SVG inline** via `pbd_icon()` (~130 Ko → ~5 Ko)
- Fonts (Syne, Inter) → self-hostées via OMGF

→ Aucune fuite de données visiteur vers un tiers, cohérent avec l'identité "numérique responsable" du cluster.

### 6. Sécurité

- Échappement systématique en sortie : `esc_html`, `esc_attr`, `esc_url`, `esc_url_raw`
- Sanitization en entrée : `sanitize_text_field`, `sanitize_email`, `absint`
- Validation whitelist sur les valeurs enum (`cover_position`)
- Suppression d'un script d'import présentant un risque CSRF (principe de surface d'attaque minimale)

### 7. Accessibilité

HTML sémantique, rôles ARIA, navigation clavier, `aria-live` sur le compteur de résultats, `alt` sur les images, focus states visibles.

---

## Résultats

| Métrique | Avant | Après |
| --- | --- | --- |
| Icônes (poids) | ~130 Ko (Material + FA) | ~5 Ko (SVG inline) |
| Requêtes CDN externes | 4+ | 0 |
| Requêtes SQL (cache hit) | ~85 | 0 |
| Poids page annuaire (charge initiale) | ~25 Mo | ~3-4 Mo |
| Poids fiche pro | ~3-5 Mo | ~600-800 Ko |
| Conformité RGPD sans bannière | Non | Oui |

---

## Documentation

Le projet est documenté pour **3 publics distincts** :

- **[README-DEV.md](README-DEV.md)** — passation technique (architecture, décisions, pièges connus, tests)
- **[README-DIRECTION.md](README-DIRECTION.md)** — vue d'ensemble pour la direction de l'association (pédagogique, sans jargon)
- **[README-ADHERENTS.md](README-ADHERENTS.md)** — guide pour les adhérents qui complètent leur fiche

---

## Captures


| Annuaire (grille filtrable) | Fiche pro (desktop) | Fiche pro (mobile) |
| --- | --- | --- |
<img width="1900" height="917" alt="image" src="https://github.com/user-attachments/assets/45174eb5-0c13-4967-99b9-164d1ef8623a" />
<img width="1899" height="916" alt="image" src="https://github.com/user-attachments/assets/2cebb9f5-dfd3-4f76-ad8b-d62e6c0fd2cd" />
<img width="262" height="466" alt="image" src="https://github.com/user-attachments/assets/202934c9-564b-4155-a2e3-72efee70c162" />
 |

---

## Auteur

**Maxime Garin** — [github.com/maximegarin](https://github.com/maximegarin)

Développé en autonomie pendant mon stage chez Pays Basque Digital de 10 semaines dans le cadre de ma formation Titre Pro Développeur Web (AFEC Bayonne, 2024–2026).
