# PBD Annuaire — README Dev

> Passation technique. Tout ce qu'il faut pour reprendre le code sans douleur.

---

## Architecture

Plugin distribué sous forme de **7 snippets** (Code Snippets WP), chacun autonome et activable indépendamment.

```
01-cpt-adherent.php          CPT adherent + ACF + helper pbd_icon() + 14 SVG inline
02-annuaire-shortcode.php    [annuaire] — grille filtrable, cache HTML, lazy logos
03-annuaire-js.php           Filtrage client-side + pagination + persistance d'état + anti-download
04-annuaire-css.php          CSS scoped .annuaire-hub (injection conditionnelle)
05-page-pro-shortcode.php    Fiche pro single, rendue côté serveur (template_redirect), cache per-post
06-page-pro-css.php          CSS scoped .fiche-pro
07-page-pro-js.php           Carrousel + presse-papier + anti-download
```

Conventions :
- **Pas de fichier WP plugin** classique — Code Snippets pour itération rapide en prod.
- **Pas de dépendance externe** (icônes SVG inline, fonts via OMGF).
- **CSS injecté en `wp_head`** uniquement sur les pages concernées (via `has_shortcode()` / `is_singular`).
- **JS injecté en `wp_footer`** avec les mêmes garde-fous.
- **Sanitization systématique** : `esc_html`, `esc_attr`, `esc_url`, `sanitize_text_field`, `sanitize_email`, `esc_url_raw`, `absint`, whitelist sur les enum.

---

## Optimisations perf

### 1. Cache HTML transient

**Snippet 02** (annuaire) :
```php
$cache_key = 'pbd_annuaire_html_v7';
if (!is_user_logged_in()) {
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;
}
// … rendu HTML …
if (!is_user_logged_in()) {
    set_transient($cache_key, $html, HOUR_IN_SECONDS);
}
```

**Snippet 05** (fiche pro) : même pattern mais **clé par adhérent** : `pbd_fiche_pro_html_v1_{$post_id}`.

**Bypass utilisateurs connectés** : les admins voient toujours le HTML frais en édition. Le cache ne se peuple que sur les visites anonymes (= l'essentiel du trafic réel).

**Versioning de la clé** : bumper le suffixe (`_v7` → `_v8`, etc.) invalide tout le cache existant sans toucher à la DB. À faire à chaque changement de structure du HTML rendu.

### 2. Invalidation auto

Hooks WordPress qui suppriment le transient quand un adhérent change d'état :

```php
add_action('save_post_adherent', function($post_id) {
    delete_transient('pbd_fiche_pro_html_v1_' . $post_id);
    delete_transient('pbd_annuaire_html_v7');
});
add_action('trashed_post',   /* idem si get_post_type === 'adherent' */);
add_action('untrashed_post', /* … */);
add_action('delete_post',    /* … */);
```

### 3. Images sized + détection hard-crop

Helper anonyme inline :

```php
$pick_size = function($img, $size) {
    if (!$img) return '';
    if (!empty($img['width']) && !empty($img['height'])
        && !empty($img['sizes'][$size . '-width']) && !empty($img['sizes'][$size . '-height'])) {
        $orig_ratio = $img['width'] / $img['height'];
        $size_ratio = $img['sizes'][$size . '-width'] / $img['sizes'][$size . '-height'];
        if (abs($orig_ratio - $size_ratio) < 0.01) {
            return $img['sizes'][$size];
        }
    }
    return $img['url'];
};
```

**Idée** : WordPress (ou un plugin type Regenerate Thumbnails) peut hard-cropper une size, transformant un logo 600×300 en carré 300×300 (perte d'info irrécupérable). On compare les ratios largeur/hauteur entre original et version générée. Écart > 1 % = hard-crop détecté → fallback URL d'origine. Sinon on sert la version optimisée.

Le logo passe par ce helper (fallback original si hard-crop). La cover de la fiche pro est en revanche **toujours bornée** à une size (`medium_large` → `large` → `url`) : `object-fit: cover` recadre déjà côté CSS, et on évite ainsi de servir une image de 2-3 Mo.

### 4. Lazy loading natif

```html
<img loading="lazy" decoding="async" width="96" height="96" …>
```

- `loading="lazy"` → l'image ne charge qu'à l'entrée dans le viewport. Combiné au `display:none` du paginateur client-side : **seules les 12 cards de la page courante chargent leurs logos**.
- `decoding="async"` → décodage hors thread principal.
- `width`/`height` HTML → réserve l'espace au layout (anti-CLS).

La cover de la fiche pro est l'exception : c'est l'image LCP, elle est servie en `fetchpriority="high"` + preload, **sans** `loading="lazy"` (voir section paint mobile).

### 5. Filtrage et pagination client-side

Toutes les cards sont sérialisées avec `data-nom`, `data-ville`, `data-cat` sur l'`<article>`. Filtre = `cards.filter(c => matchNom && matchCat && matchVille)`. Aucune requête réseau après le chargement initial. Pagination par `display:none` (12 cards par page).

Trade-off accepté : tout le HTML des 84 cards est dans le DOM initial (~60-80 Ko gzippé), en échange d'un filtre instantané sans roundtrip AJAX.

**Persistance d'état** (`sessionStorage`, clé `pbd_annuaire_state`) : le filtre actif (nom, catégorie, ville) et la page courante sont sauvegardés à chaque `applyFilter()` / `goToPage()`, puis restaurés à l'init. Effet : revenir à l'annuaire depuis une fiche pro réaffiche la sélection et la page que le visiteur consultait, au lieu de repartir page 1.

---

## Rendu serveur de la fiche pro + paint mobile

Le thème custom n'appelle pas `the_content()` sur les single CPT. La fiche est rendue **côté serveur** via `template_redirect` :

```php
add_action('template_redirect', function() {
    if (!is_singular('adherent')) return;
    get_header();
    echo pbd_get_fiche_pro_html(get_the_ID());
    get_footer();
    exit;
});
```

Le HTML est ainsi présent dès le premier byte de la réponse, sans injection JS différée.

Mesures dédiées au *first paint* sur Chrome/Safari mobile (sensible sur les fiches à cover lourde) :
- **Cover en `<img>`** (et non `background-image`), traitée comme image LCP : `fetchpriority="high"` + `<link rel="preload" as="image">` (même source que l'`<img>` pour ne pas charger deux fois) + `width/height` (anti-CLS).
- **`transform: translateZ(0)` sur `.fiche-pro-cover`** : isole la cover dans son propre layer GPU, le reste de la fiche peint indépendamment du téléchargement de la cover. C'est le correctif central du blocage de paint observé.
- **`history.scrollRestoration = 'manual'` + `window.scrollTo(0, 0)`** (snippet 07) : l'arrivée sur la fiche se fait toujours en haut de page.

Notes :
- `decoding="async"` est **conservé** sur la cover : testé, il ne nuit pas au paint une fois le layer GPU isolé.
- **Pas de `Cache-Control: no-store`** : le bfcache et le cache navigateur restent actifs (retour arrière et revisite instantanés).

---

## Interactions et responsive

### Zones de clic des cards (mobile)

Le bloc logo (`.annuaire-card-avatar-wrap`) est positionné au-dessus du lien overlay de la card. Sans correctif, un tap sur la zone du logo était intercepté par ce bloc (qui n'est pas un lien) → la card "réagissait" mais n'ouvrait pas la fiche (zone morte). Correctif :

```css
.annuaire-card-avatar-wrap { position: relative; z-index: 2; pointer-events: none; }
.annuaire-card-link { touch-action: manipulation; }
```

`pointer-events: none` laisse passer le tap vers le lien en dessous ; `touch-action: manipulation` retire le délai de double-tap-zoom mobile.

### Hauteur des covers en responsive

Les bannières adhérents sont souvent larges (logo/texte sur les côtés). Une box haute, en `cover`, recadre fortement sur les côtés. Sur mobile on **réduit la hauteur** de la cover pour montrer plus de largeur sans recourir à des bandes (`contain`) :

```css
@media (max-width: 480px) { .annuaire-card-cover { height: 95px; } }
@media (max-width: 600px) { .fiche-pro-cover   { height: 150px; } }
```

---

## Anti-téléchargement

Attribut HTML `data-protected="true"` sur la cover annuaire, la cover fiche pro et les photos d'ambiance. **Pas sur les logos** (volontairement libres — usage promo souhaité par l'asso).

CSS :
```css
[data-protected],
.fiche-pro-cover[data-protected],
.fiche-pro-slide[data-protected] {
  user-select: none;
  -webkit-user-drag: none;
  -webkit-touch-callout: none;
}
```

JS :
```js
document.addEventListener('contextmenu', function(e) {
    if (e.target.closest('[data-protected]')) e.preventDefault();
});
document.addEventListener('dragstart', function(e) {
    if (e.target.closest('[data-protected]')) e.preventDefault();
});
```

**Limites assumées** : DevTools + screenshot restent possibles (impossible à bloquer côté web). On ajoute juste de la friction pour le visiteur lambda. La cover de la carte annuaire est de plus en `background-image`, donc le clic droit "Enregistrer l'image" n'apparaît pas nativement.

---

## SVG inline

Helper centralisé dans **snippet 01** :

```php
function pbd_icon($name, $extra_class = '') {
    $library = pbd_icons_library(); // tableau 14 SVG
    if (!isset($library[$name])) return '';
    $class = trim('pbd-icon ' . $extra_class);
    return str_replace('class="pbd-icon"', 'class="' . esc_attr($class) . '"', $library[$name]);
}
```

Usage : `<?= pbd_icon('email') ?>`, `<?= pbd_icon('chevron_left', 'ma-classe') ?>`.

Icônes : `arrow_back`, `search`, `tune`, `chevron_left/right`, `expand_more`, `location_on`, `email`, `language`, `search_off`, `person`, `group`, `phone`, `linkedin`.

**Pourquoi inline** : zéro requête réseau, `fill: currentColor` permet le restyling CSS sans modifier le SVG, taille via `width/height` CSS. Gain : ~130 Ko (Material Icons + Font Awesome) → ~5 Ko.

---

## Fonts (Syne + Inter via OMGF)

Deux familles : **Syne** (titres et noms d'entreprise), **Inter** (corps de texte). Self-hostées via le plugin OMGF, qui intercepte les `wp_enqueue_style` pointant vers Google Fonts et sert une copie locale (zéro requête vers Google = conforme RGPD).

```php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('syne-font',  'https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap', [], null);
});
```

**Point critique** : l'enqueue doit se faire sur le hook `wp_enqueue_scripts`, **pas à l'intérieur d'un callback de shortcode** — un enqueue déclenché pendant le rendu du shortcode arrive après `wp_head`, et OMGF ne peut alors plus l'intercepter (la font repart vers Google ou ne charge pas du tout). Le bug est masqué sur un poste où la font est installée localement : il n'apparaît que sur mobile.

---

## Pièges connus

1. **`background-image` dans un attribut `style`** — utiliser `esc_url()` mais via `url(...)` sans quotes intérieures.
2. **`get_field()` sur Repeater ACF** — renvoie un array d'arrays ou `null`. Toujours `?: []` et tester `is_array()` sur chaque row.
3. **Cache et `orderby=rand`** — l'ordre aléatoire est gelé tant que le transient existe (1h). Trade-off accepté.
4. **Rendu single CPT** — le thème n'appelle pas `the_content()` sur les singles `adherent` : la fiche passe par `template_redirect` (voir section dédiée).
5. **OMGF et timing d'enqueue** — voir section Fonts. Vérifier aussi après chaque upgrade WP que les fonts ne repartent pas vers Google (F12 → Network). Après ajout/changement d'une font, lancer un refresh du cache OMGF pour qu'il télécharge la nouvelle font en local.
6. **`save_post_adherent` sur autosave** — le hook se déclenche aussi sur les autosaves. Non critique (juste un `delete_transient` redondant), mais à savoir avant d'ajouter des side-effects.

---

## Tests à faire avant prod

- F12 → Network → désactiver le cache → recharger `/annuaire/` :
  - 0 requête vers `fonts.googleapis.com` / `fonts.gstatic.com` (sinon OMGF cassé ou enqueue mal placé).
  - 0 requête vers un CDN d'icônes.
  - Logos : seules les 12 cards visibles téléchargent.
  - Poids total < 5 Mo.
- Test fonts sur **mobile en navigation privée** (un poste de dev a souvent la font installée localement, ce qui masque un échec de chargement web) : Syne sur les noms d'entreprise et le `<h1>` fiche pro, Inter sur le corps.
- F12 → Lighthouse → Mobile : Performance > 85, CLS < 0.1, LCP < 2.5 s.
- Anti-download : clic droit sur cover / photo d'ambiance → menu absent ; drag → image fantôme absente ; clic droit sur logo → menu présent (volontaire).
- Navigation : ouvrir une fiche depuis la page 2 de l'annuaire → revenir → on retombe bien page 2 avec les mêmes filtres.
- Édition adhérent : modifier une fiche → recharger sans login → changement visible immédiatement (cache invalidé), côté annuaire et côté fiche.

---

## Stack

- WordPress 6.x
- ACF Pro (CPT + champs)
- Code Snippets (containers PHP)
- OMGF (Google Fonts servies en local)
- PHP 7.4+ (closures, spread, null coalescing)
- JavaScript vanilla (zéro framework)
