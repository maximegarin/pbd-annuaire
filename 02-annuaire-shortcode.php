<?php
/**
 * PBD — Annuaire SHORTCODE [annuaire]
 * Grille de cartes adhérents avec filtres sidebar (nom, catégorie, ville).
 * Icônes : SVG inline via helper pbd_icon() défini dans snippet 01.
 *
 * OPTIMISATIONS PERF (v2.1+) :
 *  - Cache HTML transient 1h (invalidé automatiquement sur save/delete d'un adhérent)
 *  - Bypass cache pour utilisateurs connectés (admin voit toujours frais)
 *  - Images optimisées : taille thumbnail (150px) pour logos, medium_large pour covers
 *  - Lazy loading natif + decoding async sur les <img>
 *  - Attributs width/height sur les images pour éviter le CLS (layout shift)
 */
add_shortcode('annuaire', function($atts) {

    // ============================================================
    // CACHE TRANSIENT — bypass si admin connecté pour voir le frais
    // ============================================================
    $cache_key = 'pbd_annuaire_html_v7';
    if (!is_user_logged_in()) {
        $cached = get_transient($cache_key);
        if ($cached !== false) return $cached;
    }

    // Mapping slug spécialité -> label (inline, autonome, pas de dépendance externe)
    $specialites_labels = [
        'editeur-logiciel'        => 'Éditeur de logiciel',
        'agence-digitale'         => 'Agence digitale',
        'studio-immersif-xr'      => 'Studio immersif et XR',
        'esn-integrateur'         => 'ESN et intégrateur',
        'conseil-expertise'       => 'Conseil et expertise',
        'formation-enseignement'  => 'Formation et enseignement',
        'infrastructure-cloud'    => 'Infrastructure et cloud',
        'cybersecurite'           => 'Cybersécurité',
        'ia-data'                 => 'IA et data',
        'iot-industrie'           => 'Objets connectés et industrie',
        'ecommerce-marketplace'   => 'E-commerce et marketplace',
    ];

    // Enqueue des fonts (servies en local via OMGF)
    wp_enqueue_style('syne-font',    'https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700&display=swap', [], null);
    wp_enqueue_style('dm-sans-font', 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&display=swap', [], null);

    $adherents = new WP_Query([
        'post_type'      => 'adherent',
        'posts_per_page' => -1,
        'orderby'        => 'rand',
        'post_status'    => 'publish',
    ]);

    if (!$adherents->have_posts()) {
        return '<p class="annuaire-vide">Aucun adhérent à afficher pour le moment.</p>';
    }

    $villes     = [];
    $categories = [];
    while ($adherents->have_posts()) {
        $adherents->the_post();
        $v = get_field('ville');
        $c = get_field('specialites') ?: [];
        if ($v) $villes[] = sanitize_text_field($v);
        if ($c) $categories = array_merge($categories, array_map('sanitize_text_field', $c));
    }
    // Tri des villes par nombre d'adhérents décroissant (alpha pour ex-aequo)
    $villes_count = array_count_values($villes);
    uksort($villes_count, function($a, $b) use ($villes_count) {
        if ($villes_count[$a] !== $villes_count[$b]) {
            return $villes_count[$b] - $villes_count[$a];
        }
        return strcmp($a, $b);
    });
    $villes = array_keys($villes_count);

    // Tri des catégories par label alphabétique
    $categories = array_values(array_unique($categories));
    usort($categories, function($a, $b) use ($specialites_labels) {
        $la = $specialites_labels[$a] ?? $a;
        $lb = $specialites_labels[$b] ?? $b;
        return strcmp($la, $lb);
    });
    $adherents->rewind_posts();

    ob_start(); ?>

    <div class="annuaire-hub">

        <button class="annuaire-filter-toggle" id="annuaire-filter-toggle" type="button" aria-expanded="false" aria-controls="annuaire-sidebar">
            <?= pbd_icon('tune') ?>
            <span class="annuaire-filter-toggle-label">Filtres</span>
            <?= pbd_icon('expand_more', 'annuaire-filter-toggle-chevron') ?>
        </button>

        <div class="annuaire-sidebar" id="annuaire-sidebar">
            <div class="annuaire-sidebar-title">Filtres</div>

            <div class="annuaire-sb-label">Nom d'entreprise</div>
            <div class="annuaire-search-wrap">
                <?= pbd_icon('search') ?>
                <input class="annuaire-search" type="text" id="annuaire-s-nom"
                       placeholder="Rechercher…"
                       aria-label="Rechercher par nom d'entreprise">
            </div>

            <div class="annuaire-sb-sep"></div>

            <div class="annuaire-sb-label">Catégorie</div>
            <div class="annuaire-sb-pills" id="annuaire-pills-cat" role="group" aria-label="Filtrer par catégorie">
                <div class="annuaire-sb-pill active" data-cat="" role="button" tabindex="0">Toutes</div>
                <?php foreach ($categories as $c): ?>
                    <div class="annuaire-sb-pill"
                         data-cat="<?= esc_attr(strtolower($c)) ?>"
                         role="button" tabindex="0">
                        <?= esc_html($specialites_labels[$c] ?? $c) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="annuaire-sb-sep"></div>

            <div class="annuaire-sb-label">Ville</div>
            <div class="annuaire-sb-pills" id="annuaire-pills-ville" role="group" aria-label="Filtrer par ville">
                <div class="annuaire-sb-pill active" data-ville="" role="button" tabindex="0">Toutes</div>
                <?php foreach ($villes as $v): ?>
                    <div class="annuaire-sb-pill"
                         data-ville="<?= esc_attr(strtolower($v)) ?>"
                         role="button" tabindex="0">
                        <?= esc_html($v) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="annuaire-sb-sep"></div>

            <button class="annuaire-sb-reset" id="annuaire-btn-reset" aria-label="Réinitialiser tous les filtres" type="button">
                <?= pbd_icon('tune') ?>
                Réinitialiser
            </button>
        </div>

        <div class="annuaire-main">
            <div class="annuaire-main-header">
                <p class="annuaire-count" id="annuaire-count" aria-live="polite">
                    <strong><?= esc_html($adherents->post_count) ?></strong> adhérents
                </p>
            </div>

            <div class="annuaire-grid" id="annuaire-grid">
            <?php
            $covers = [
                'annuaire-cover-1','annuaire-cover-2','annuaire-cover-3','annuaire-cover-4',
                'annuaire-cover-5','annuaire-cover-6','annuaire-cover-7','annuaire-cover-8'
            ];
            $initiales_classes = [
                'annuaire-initiales-1','annuaire-initiales-2','annuaire-initiales-3','annuaire-initiales-4',
                'annuaire-initiales-5','annuaire-initiales-6','annuaire-initiales-7','annuaire-initiales-8'
            ];
            $i = 0;

            while ($adherents->have_posts()): $adherents->the_post();
                $post_id          = get_the_ID();
                $titre            = get_the_title();
                $categories_fiche = get_field('specialites') ?: [];
                $categories_str   = implode(',', array_map('strtolower', array_map('sanitize_text_field', $categories_fiche)));
                $tags             = get_field('tags') ?: [];
                $ville            = sanitize_text_field(get_field('ville'));
                $email            = sanitize_email(get_field('email'));
                $linkedin         = esc_url_raw(get_field('linkedin'));
                $site             = esc_url_raw(get_field('site_web'));
                $logo             = get_field('logo');
                $cover            = get_field('cover');
                $cover_pos_raw    = get_field('cover_position') ?: 'center';
                $cover_position   = in_array($cover_pos_raw, ['top','center','bottom']) ? $cover_pos_raw : 'center';
                $nb_emp           = absint(get_field('nb_employes'));
                $cover_class      = $covers[$i % 8];
                $initiale_class   = $initiales_classes[$i % 8];
                $initiales        = mb_strtoupper(mb_substr($titre, 0, 2));
                $url_fiche        = get_permalink($post_id);

                // Tailles d'images optimisées
                // Helper : choisit la taille demandée UNIQUEMENT si elle préserve le ratio
                //          d'origine (sinon = hard-crop détecté → fallback URL d'origine).
                //          Évite les images recadrées n'importe comment par WP ou plugins.
                if (!isset($pick_size)) {
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
                }
                // Logo  : medium       (300px) — fallback URL si hard-crop
                // Cover : medium_large (768px) — fallback URL si hard-crop
                $logo_src  = $pick_size($logo,  'medium');
                $cover_src = $pick_size($cover, 'medium_large');

                $i++;
            ?>
                <article class="annuaire-card"
                         data-nom="<?= esc_attr(strtolower($titre)) ?>"
                         data-ville="<?= esc_attr(strtolower($ville)) ?>"
                         data-cat="<?= esc_attr($categories_str) ?>">

                    <a class="annuaire-card-link"
                       href="<?= esc_url($url_fiche) ?>"
                       aria-label="Voir la fiche de <?= esc_attr($titre) ?>"></a>

                    <div class="annuaire-card-cover <?= $cover ? '' : esc_attr($cover_class) ?>"
                         <?= $cover ? 'style="background-image:url(' . esc_url($cover_src) . ');background-position:center ' . esc_attr($cover_position) . '"' : '' ?>
                         data-protected="true"
                         role="presentation"
                         aria-hidden="true">
                    </div>

                    <div class="annuaire-card-avatar-wrap">
                        <?php if ($logo): ?>
                            <img class="annuaire-card-logo"
                                 src="<?= esc_url($logo_src) ?>"
                                 alt="Logo de <?= esc_attr($titre) ?>"
                                 loading="lazy" decoding="async"
                                 width="96" height="96">
                        <?php else: ?>
                            <div class="annuaire-card-initiales <?= esc_attr($initiale_class) ?>" aria-hidden="true">
                                <?= esc_html($initiales) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="annuaire-card-body">
                        <div class="annuaire-card-nom"><?= esc_html($titre) ?></div>

                        <?php if (!empty($categories_fiche)): ?>
                            <div class="annuaire-card-categories">
                                <?php foreach ($categories_fiche as $cat): ?>
                                    <span class="annuaire-card-categorie"><?= esc_html($specialites_labels[$cat] ?? $cat) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($tags)): ?>
                            <div class="annuaire-card-tags" aria-label="Tags">
                                <?php foreach ($tags as $tag_row):
                                    $tag_val = is_array($tag_row) ? ($tag_row['tag'] ?? '') : $tag_row;
                                    if (!$tag_val) continue;
                                ?>
                                    <span class="annuaire-tag"><?= esc_html($tag_val) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="annuaire-card-footer">
                            <span class="annuaire-card-ville">
                                <?= pbd_icon('location_on') ?>
                                <?= esc_html($ville) ?>
                            </span>
                            <div class="annuaire-card-liens">
                                <?php if ($email): ?>
                                    <a href="mailto:<?= esc_attr($email) ?>"
                                       class="annuaire-card-lien"
                                       aria-label="Envoyer un email à <?= esc_attr($titre) ?>">
                                        <?= pbd_icon('email') ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($linkedin): ?>
                                    <a href="<?= esc_url($linkedin) ?>" target="_blank"
                                       rel="noopener noreferrer"
                                       class="annuaire-card-lien"
                                       aria-label="Voir le profil LinkedIn de <?= esc_attr($titre) ?>">
                                        <?= pbd_icon('linkedin') ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($site): ?>
                                    <a href="<?= esc_url($site) ?>" target="_blank"
                                       rel="noopener noreferrer"
                                       class="annuaire-card-lien"
                                       aria-label="Visiter le site de <?= esc_attr($titre) ?>">
                                        <?= pbd_icon('language') ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </article>
            <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <p class="annuaire-vide" id="annuaire-vide" role="status" style="display:none;">
                <?= pbd_icon('search_off') ?>
                Aucun résultat pour cette recherche.
            </p>

            <div class="annuaire-pagination" id="annuaire-pagination" aria-label="Navigation entre les pages"></div>
        </div>

    </div>

    <?php
    $html = ob_get_clean();

    // ============================================================
    // Stocker le HTML dans le cache transient (1h) pour les visiteurs
    // Les admins connectés ne déclenchent pas la mise en cache
    // ============================================================
    if (!is_user_logged_in()) {
        set_transient($cache_key, $html, HOUR_IN_SECONDS);
    }

    return $html;
});

// ============================================================
// INVALIDATION AUTOMATIQUE DU CACHE
// Vide le cache transient quand un adhérent change d'état :
//   - création / modification → save_post_adherent
//   - mise à la corbeille     → trashed_post
//   - restauration            → untrashed_post
//   - suppression définitive  → delete_post
// ============================================================
add_action('save_post_adherent', function() {
    delete_transient('pbd_annuaire_html_v7');
});

add_action('trashed_post', function($id) {
    if (get_post_type($id) === 'adherent') {
        delete_transient('pbd_annuaire_html_v7');
    }
});

add_action('untrashed_post', function($id) {
    if (get_post_type($id) === 'adherent') {
        delete_transient('pbd_annuaire_html_v7');
    }
});

add_action('delete_post', function($id) {
    if (get_post_type($id) === 'adherent') {
        delete_transient('pbd_annuaire_html_v7');
    }
});
