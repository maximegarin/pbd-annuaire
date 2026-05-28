# PBD Annuaire 2.1 — README Dev

> Passation technique. Tout ce qu'il faut pour reprendre le code sans douleur.

---

## 🗂️ Architecture

Plugin distribué sous forme de **7 snippets** (Code Snippets WP), chacun autonome et activable indépendamment.

```
01-cpt-adherent.php          CPT adherent + ACF + helper pbd_icon() + 14 SVG inline
02-annuaire-shortcode.php    [annuaire] — grille filtrable, cache HTML, lazy logos
03-annuaire-js.php           Filtrage client-side + pagination + anti-download
04-annuaire-css.php          CSS scoped .annuaire-hub (injection conditionnelle)
05-page-pro-shortcode.php    Fiche pro single, cache per-post, injection main
06-page-pro-css.php          CSS scoped .fiche-pro
07-page-pro-js.php           Carrousel + presse-papier + anti-download
```

Conventions :
- **Pas de fichier WP plugin** classique — Code Snippets pour itération rapide en prod
- **Pas de dépendance externe** (icônes SVG inline, fonts via OMGF)
- **CSS injecté en `wp_head` uniquement** sur pages contenant le shortcode (via `has_shortcode()`) → zéro pollution
- **JS injecté en `wp_footer`** avec les mêmes garde-fous
- **Sanitization systématique** : `esc_html`, `esc_attr`, `esc_url`, `sanitize_text_field`, `sanitize_email`, `esc_url_raw`, `absint`, whitelist sur les enum

---

## ⚡ Optimisations perf

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

**Bypass utilisateurs connectés** : les admins voient toujours le HTML frais en édition. Le cache ne se peuple que sur les visites anonymes (= 99% du trafic réel).

**Versioning clé** (`_v7`, `_v1`) : permet d'invalider tout le cache existant en bumpant la version sans toucher à la DB.

### 2. Invalidation auto

Hooks WordPress qui suppriment le transient quand un adhérent change d'état :

```php
add_action('save_post_adherent', function($post_id) {
    delete_transient('pbd_fiche_pro_html_v1_' . $post_id);
});
add_action('trashed_post', /* idem si get_post_type === 'adherent' */);
add_action('untrashed_post', /* … */);
add_action('delete_post', /* … */);
```

Côté annuaire (cache global), idem mais sans `$post_id` puisque la clé est unique.

### 3. Images sized + detection hard-crop

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

**Idée** : WordPress (ou un plugin tipo Regenerate Thumbnails) peut hard-cropper une size, transformant un logo 600×300 en carré 300×300 (perte d'info irrécupérable). On compare les ratios **largeur/hauteur** entre original et version générée. Écart > 1% = hard-crop détecté → fallback URL d'origine. Sinon on prend la version optimisée.

Mapping appliqué :

| Image | Size tentée | Affichée | Gain typique |
|---|---|---|---|
| Logo annuaire | medium (300px) | 96×96 | ~75% |
| Cover annuaire | medium_large (768px) | ~300×132 | ~80% |
| Logo fiche pro | medium (300px) | 116×116 | ~70% |
| Cover fiche pro | large (1024px) | full-width × 240 | ~60% |
| Photo ambiance | large (1024px) | carousel 16:9 | ~70% |

### 4. Lazy loading natif

```html
<img loading="lazy" decoding="async" width="96" height="96" …>
```

- `loading="lazy"` → navigateur ne charge l'image que quand elle entre dans le viewport. Combiné avec `display:none` du paginateur client-side, ça veut dire **seules les 12 cards de la page courante chargent leurs logos**.
- `decoding="async"` → décodage hors thread principal.
- `width`/`height` HTML → réserve l'espace au layout (anti-CLS).

⚠️ Pas de `loading="lazy"` sur le logo de la fiche pro : il est au-dessus du fold, on ne veut pas le différer.

### 5. Filtrage client-side

Toutes les cards sont sérialisées avec `data-nom`, `data-ville`, `data-cat` sur l'`<article>`. Filtre = `cards.filter(c => matchNom && matchCat && matchVille)`. Aucune requête réseau après le chargement initial. Pagination = `display:none` sur les cards hors page.

Trade-off accepté : tout le HTML des 84 cards est dans le DOM initial (~60-80 Ko gzippé). Mais on évite des roundtrips AJAX et le filtre est instantané.

---

## 🛡️ Anti-téléchargement

Attribut HTML `data-protected="true"` sur cover annuaire, cover fiche pro, photos d'ambiance. **Pas sur les logos** (volontairement libres — usage promo souhaité par l'asso).

CSS :
```css
.annuaire-card-cover[data-protected],
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

**Limites assumées** : DevTools + screenshot restent possibles (impossible à bloquer côté web). On ajoute juste de la friction pour le visiteur lambda.

Bonus : cover et photos d'ambiance utilisent déjà `background-image` (pas `<img>`), donc le clic droit → "Enregistrer l'image" n'apparaît pas nativement même sans JS.

---

## 🎨 SVG inline

Helper centralisé dans **snippet 01** :

```php
function pbd_icon($name, $extra_class = '') {
    $library = pbd_icons_library(); // tableau 14 SVG
    if (!isset($library[$name])) return '';
    $class = trim('pbd-icon ' . $extra_class);
    return str_replace('class="pbd-icon"', 'class="' . esc_attr($class) . '"', $library[$name]);
}
```

Usage côté templates : `<?= pbd_icon('email') ?>`, `<?= pbd_icon('chevron_left', 'ma-classe') ?>`.

Icônes : `arrow_back`, `search`, `tune`, `chevron_left/right`, `expand_more`, `location_on`, `email`, `language`, `search_off`, `person`, `group`, `phone`, `linkedin`.

**Pourquoi inline** : pas de requête réseau supplémentaire, `fill: currentColor` permet le restyling CSS sans modifier le SVG, taille via `width/height` CSS (pas `font-size` comme Material Icons).

Gain : ~130 Ko de Material Icons + Font Awesome → ~5 Ko de SVG inline.

---

## 🐛 Pièges connus

1. **`background-image` background dans `<style attr>`** — quotes : utilise `esc_url()` mais **pas** dans une string entre quotes simples côté CSS (utiliser `url(...)` sans quotes intérieures).

2. **`get_field()` sur Repeater ACF** — renvoie array d'arrays ou null. Toujours `?: []` et tester `is_array()` sur chaque row.

3. **Cache et orderby=rand** — le `rand` est gelé pendant 1h (tant que le transient existe). Trade-off accepté pour la perf.

4. **Injection dans `<main>` (snippet 05)** — le thème custom n'appelle pas `the_content()` sur les singles CPT.

5. **OMGF intercept des fonts** — les `wp_enqueue_style('syne-font', 'https://fonts.googleapis.com/…')` sont réécrits côté serveur. Si OMGF désinstallé : les fonts repartent vers Google → fail RGPD. Vérifier après chaque upgrade WP.

6. **`save_post_adherent` fire sur autosave** — le hook se déclenche aussi sur les autosaves WP. Pas critique (juste un `delete_transient` redondant), mais à savoir si tu rajoutes des side-effects.

---

## 🧪 Tests à faire avant prod

- F12 → Network → désactiver cache → recharger `/annuaire/` :
  - 0 requête vers `fonts.googleapis.com` (sauf si OMGF cassé)
  - 0 requête vers `cdnjs.cloudflare.com`
  - Logos : seules les 12 cards visibles téléchargent (vérifier `Initiator: lazy`)
  - Poids total < 5 Mo

- F12 → Lighthouse → Mobile :
  - Performance > 85
  - CLS < 0.1 (les `width/height` HTML font le job)
  - LCP < 2.5s

- Test manuel anti-download :
  - Clic droit sur cover/photo ambiance → menu contextuel absent
  - Drag → image fantôme absente
  - Clic droit sur logo → menu présent (volontaire)

- Édition adhérent :
  - Modifier une fiche → recharger `/annuaire/` sans login → modification visible immédiatement (cache invalidé)
  - Modifier une fiche → recharger `/adherent/xxx/` → modification visible (cache per-post invalidé)

---

## 📦 Stack

- WordPress 6.x
- ACF Pro (CPT + champs)
- Code Snippets (containers PHP)
- OMGF (Google Fonts → local)
- PHP 7.4+ (closures + spread + null coalescing utilisés)
- Vanilla JS (zéro framework, IE11-compatible avec polyfills déjà inclus dans le thème éventuel)

---

## 🔁 Migration depuis 2.0

Voir `README.md` historique : remplacement Material Icons + Font Awesome par SVG inline, mêmes hooks, mêmes CPT, mêmes champs ACF.

Rollback : `Pays-Basque-Digital-Annuaire-2.0/` est conservé intact côté disque.
