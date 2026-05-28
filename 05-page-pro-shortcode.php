<?php
/**
 * PBD — Page Pro SHORTCODE [page_pro]
 * Fiche détaillée adhérent : cover, logo, catégories, infos, tags,
 * description, verbatim, carrousel photos.
 * Injection JS dans le <main> du thème (quand the_content() n'est pas appelé).
 * Icônes : SVG inline via helper pbd_icon() défini dans snippet 01.
 *
 * OPTIMISATIONS PERF (v2.1+) :
 *  - Cache HTML transient 1h PAR adhérent (clé : pbd_fiche_pro_html_v1_{$post_id})
 *  - Bypass cache pour utilisateurs connectés (admin voit toujours frais)
 *  - Logo : detection du hard-crop pour préserver les logos rectangulaires
 *  - Cover : taille large (1024px) au lieu de l'originale
 *  - Photos ambiance : taille large (1024px) au lieu de l'originale
 *  - Attributs width/height + decoding async sur le logo (anti CLS)
 *  - Anti-téléchargement (friction) sur cover + photos d'ambiance via data-protected
 */
function pbd_get_fiche_pro_html($post_id) {

    // ============================================================
    // CACHE TRANSIENT — bypass si admin connecté pour voir le frais
    // ============================================================
    $cache_key = 'pbd_fiche_pro_html_v1_' . $post_id;
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

    $titre           = get_the_title($post_id);
    $categories      = get_field('specialites', $post_id) ?: [];
    $tags            = get_field('tags', $post_id) ?: [];
    $dirigeant       = sanitize_text_field(get_field('dirigeant', $post_id));
    $clients         = get_field('clients', $post_id) ?: [];
    $ville           = sanitize_text_field(get_field('ville', $post_id));
    $adresse         = sanitize_text_field(get_field('adresse', $post_id));
    $code_postal     = sanitize_text_field(get_field('code_postal', $post_id));
    $email           = sanitize_email(get_field('email', $post_id));
    $telephone       = sanitize_text_field(get_field('telephone', $post_id));
    $linkedin        = esc_url_raw(get_field('linkedin', $post_id));
    $site            = esc_url_raw(get_field('site_web', $post_id));
    $logo            = get_field('logo', $post_id);
    $cover           = get_field('cover', $post_id);
    $cover_pos_raw   = get_field('cover_position', $post_id) ?: 'center';
    $cover_position  = in_array($cover_pos_raw, ['top','center','bottom']) ? $cover_pos_raw : 'center';
    $nb_emp          = absint(get_field('nb_employes', $post_id));
    $description     = get_field('description', $post_id);
    $verbatim        = get_field('verbatim', $post_id);
    $verbatim_auteur = sanitize_text_field(get_field('verbatim_auteur', $post_id));
    $photo_1         = get_field('photo_ambiance_1', $post_id);
    $photo_2         = get_field('photo_ambiance_2', $post_id);
    $photo_3         = get_field('photo_ambiance_3', $post_id);
    $photos          = array_values(array_filter([$photo_1, $photo_2, $photo_3]));

    // ============================================================
    // TAILLES D'IMAGES OPTIMISÉES
    // ============================================================
    // Helper : choisit la taille demandée UNIQUEMENT si elle préserve le ratio d'origine
    //          (détecte le hard-crop éventuel — WP ou plugin — pour éviter de couper
    //          les images rectangulaires, ex logos 600x300 ou covers 1600x400)
    //          Sinon fallback sur l'URL d'origine.
    $pick_size = function($img, $size) {
        if (!$img) return '';
        if (!empty($img['width']) && !empty($img['height'])
            && !empty($img['sizes'][$size . '-width']) && !empty($img['sizes'][$size . '-height'])) {
            $orig_ratio = $img['width'] / $img['height'];
            $size_ratio = $img['sizes'][$size . '-width'] / $img['sizes'][$size . '-height'];
            // tolérance 1% pour les arrondis pixel
            if (abs($orig_ratio - $size_ratio) < 0.01) {
                return $img['sizes'][$size];
            }
        }
        return $img['url'];
    };

    // Logo  : medium (300px)   — fallback URL d'origine si hard-crop
    // Cover : large  (1024px)  — fallback URL d'origine si hard-crop
    $logo_src  = $pick_size($logo,  'medium');
    // Cover : medium_large (768px) — divisé par ~2 vs large pour réduire le poids
    //         + résoudre le bug de paint mobile sur les fiches avec cover lourde
    $cover_src = $pick_size($cover, 'medium_large');

    $url_annuaire = home_url('/annuaire/');

    ob_start(); ?>

    <div class="fiche-pro">

        <a class="fiche-pro-retour" href="<?= esc_url($url_annuaire) ?>" aria-label="Retour à l'annuaire">
            <?= pbd_icon('arrow_back') ?>
            Retour à l'annuaire
        </a>

        <div class="fiche-pro-cover <?= $cover ? '' : 'fiche-pro-cover-defaut' ?>"
             data-protected="true"
             role="presentation"
             aria-hidden="true">
            <?php if ($cover): ?>
                <img class="fiche-pro-cover-img"
                     src="<?= esc_url($cover_src) ?>"
                     alt=""
                     decoding="async"
                     fetchpriority="high"
                     width="1100" height="240"
                     style="object-position: center <?= esc_attr($cover_position) ?>">
            <?php endif; ?>
        </div>

        <div class="fiche-pro-main">

            <div class="fiche-pro-avatar-wrap">
                <?php if ($logo): ?>
                    <img class="fiche-pro-logo"
                         src="<?= esc_url($logo_src) ?>"
                         alt="Logo de <?= esc_attr($titre) ?>"
                         decoding="async"
                         width="116" height="116">
                <?php else: ?>
                    <div class="fiche-pro-initiales" aria-hidden="true">
                        <?= esc_html(mb_strtoupper(mb_substr($titre, 0, 2))) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="fiche-pro-header">
                <div class="fiche-pro-header-gauche">
                    <h1 class="fiche-pro-nom"><?= esc_html($titre) ?></h1>
                    <?php if (!empty($categories)): ?>
                        <div class="fiche-pro-categories">
                            <?php foreach ($categories as $cat): ?>
                                <span class="fiche-pro-cat"><?= esc_html($specialites_labels[$cat] ?? $cat) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="fiche-pro-liens">
                    <?php if ($email): ?>
                        <a href="mailto:<?= esc_attr($email) ?>"
                           class="fiche-pro-lien"
                           aria-label="Envoyer un email à <?= esc_attr($titre) ?>">
                            <?= pbd_icon('email') ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($telephone): ?>
                        <a href="tel:<?= esc_attr(preg_replace('/\s+/', '', $telephone)) ?>"
                           class="fiche-pro-lien"
                           aria-label="Appeler <?= esc_attr($titre) ?>">
                            <?= pbd_icon('phone') ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($linkedin): ?>
                        <a href="<?= esc_url($linkedin) ?>" target="_blank"
                           rel="noopener noreferrer"
                           class="fiche-pro-lien"
                           aria-label="Voir le profil LinkedIn de <?= esc_attr($titre) ?>">
                            <?= pbd_icon('linkedin') ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($site): ?>
                        <a href="<?= esc_url($site) ?>" target="_blank"
                           rel="noopener noreferrer"
                           class="fiche-pro-lien"
                           aria-label="Visiter le site de <?= esc_attr($titre) ?>">
                            <?= pbd_icon('language') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="fiche-pro-sep"></div>

            <div class="fiche-pro-cols">

                <div class="fiche-pro-col-gauche">
                    <span class="fiche-pro-section-label">Informations</span>

                    <?php if (!$dirigeant && !$adresse && !$ville && !$nb_emp): ?>
                        <p class="fiche-pro-empty-info">Informations à venir.</p>
                    <?php endif; ?>

                    <?php if ($dirigeant): ?>
                        <div class="fiche-pro-info">
                            <?= pbd_icon('person') ?>
                            <?= esc_html($dirigeant) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($adresse || $ville): ?>
                        <div class="fiche-pro-info fiche-pro-info--adresse">
                            <?= pbd_icon('location_on') ?>
                            <span>
                                <?php if ($adresse): ?>
                                    <?= esc_html($adresse) ?><br>
                                <?php endif; ?>
                                <?php if ($code_postal || $ville): ?>
                                    <?= esc_html(trim($code_postal . ' ' . $ville)) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if ($nb_emp): ?>
                        <div class="fiche-pro-info">
                            <?= pbd_icon('group') ?>
                            <strong><?= esc_html($nb_emp) ?></strong> collaborateurs
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($tags)): ?>
                        <div class="fiche-pro-sep"></div>
                        <span class="fiche-pro-section-label">Spécialités</span>
                        <div class="fiche-pro-tags">
                            <?php foreach ($tags as $tag_row):
                                $tag_val = is_array($tag_row) ? ($tag_row['tag'] ?? '') : $tag_row;
                                if (!$tag_val) continue;
                            ?>
                                <span class="fiche-pro-tag"><?= esc_html($tag_val) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    $linkedin_display = $linkedin ? preg_replace('#^https?://#', '', rtrim($linkedin, '/')) : '';
                    $site_display     = $site     ? preg_replace('#^https?://#', '', rtrim($site, '/'))     : '';
                    $has_contact      = $email || $telephone || $linkedin || $site;
                    ?>
                    <div class="fiche-pro-sep"></div>
                    <span class="fiche-pro-section-label">Contact & liens</span>
                    <?php if (!$has_contact): ?>
                        <p class="fiche-pro-empty-info">Contact à venir.</p>
                    <?php else: ?>
                        <div class="fiche-pro-cta-liens">
                            <?php if ($email): ?>
                                <a href="mailto:<?= esc_attr($email) ?>"
                                   class="fiche-pro-cta-lien"
                                   data-copy="<?= esc_attr($email) ?>"
                                   aria-label="Copier l'email de <?= esc_attr($titre) ?>">
                                    <?= pbd_icon('email') ?>
                                    <span class="fiche-pro-cta-label"><?= esc_html($email) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($telephone): ?>
                                <a href="tel:<?= esc_attr(preg_replace('/\s+/', '', $telephone)) ?>"
                                   class="fiche-pro-cta-lien"
                                   data-copy="<?= esc_attr($telephone) ?>"
                                   aria-label="Copier le téléphone de <?= esc_attr($titre) ?>">
                                    <?= pbd_icon('phone') ?>
                                    <span class="fiche-pro-cta-label"><?= esc_html($telephone) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($linkedin): ?>
                                <a href="<?= esc_url($linkedin) ?>"
                                   class="fiche-pro-cta-lien"
                                   data-copy="<?= esc_attr($linkedin) ?>"
                                   aria-label="Copier le lien LinkedIn de <?= esc_attr($titre) ?>">
                                    <?= pbd_icon('linkedin') ?>
                                    <span class="fiche-pro-cta-label"><?= esc_html($linkedin_display) ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($site): ?>
                                <a href="<?= esc_url($site) ?>"
                                   class="fiche-pro-cta-lien"
                                   data-copy="<?= esc_attr($site) ?>"
                                   aria-label="Copier le site de <?= esc_attr($titre) ?>">
                                    <?= pbd_icon('language') ?>
                                    <span class="fiche-pro-cta-label"><?= esc_html($site_display) ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="fiche-pro-col-droite">

                    <span class="fiche-pro-section-label">À propos</span>
                    <?php if ($description): ?>
                        <div class="fiche-pro-description">
                            <?= wp_kses_post(wpautop($description)) ?>
                        </div>
                    <?php else: ?>
                        <p class="fiche-pro-empty-info">Description à venir.</p>
                    <?php endif; ?>

                    <?php if ($verbatim): ?>
                        <span class="fiche-pro-section-label">Le mot du fondateur</span>
                        <blockquote class="fiche-pro-verbatim">
                            <p class="fiche-pro-verbatim-texte">« <?= esc_html($verbatim) ?> »</p>
                            <?php if ($verbatim_auteur): ?>
                                <footer class="fiche-pro-verbatim-auteur">— <?= esc_html($verbatim_auteur) ?></footer>
                            <?php endif; ?>
                        </blockquote>
                    <?php endif; ?>

                    <?php if (!empty($clients)): ?>
                        <span class="fiche-pro-section-label">Nos clients</span>
                        <div class="fiche-pro-clients">
                            <?php foreach ($clients as $client_row):
                                $nom_client = is_array($client_row) ? ($client_row['nom_client'] ?? '') : $client_row;
                                if (!$nom_client) continue;
                            ?>
                                <span class="fiche-pro-client"><?= esc_html($nom_client) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($photos)): ?>
                        <span class="fiche-pro-section-label">Ambiance de travail</span>
                        <div class="fiche-pro-carrousel" role="region" aria-label="Photos d'ambiance">
                            <div class="fiche-pro-carrousel-btns">
                                <button class="fiche-pro-carrousel-btn" id="fiche-prev" type="button" aria-label="Photo précédente">
                                    <?= pbd_icon('chevron_left') ?>
                                </button>
                                <button class="fiche-pro-carrousel-btn" id="fiche-next" type="button" aria-label="Photo suivante">
                                    <?= pbd_icon('chevron_right') ?>
                                </button>
                            </div>
                            <div class="fiche-pro-carrousel-track" id="fiche-track">
                                <?php foreach ($photos as $index => $photo):
                                    $alt = !empty($photo['alt']) ? $photo['alt'] : 'Photo d\'ambiance ' . ($index + 1);
                                    $photo_src = $pick_size($photo, 'large');
                                ?>
                                    <div class="fiche-pro-slide <?= $index === 0 ? 'active' : '' ?>"
                                         data-protected="true"
                                         role="img"
                                         aria-label="<?= esc_attr($alt) ?>">
                                        <img class="fiche-pro-slide-img"
                                             src="<?= esc_url($photo_src) ?>"
                                             alt=""
                                             loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                                             decoding="async">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($photos) > 1): ?>
                                <div class="fiche-pro-carrousel-nav" role="tablist">
                                    <?php foreach ($photos as $index => $photo): ?>
                                        <button class="fiche-pro-dot <?= $index === 0 ? 'active' : '' ?>"
                                                data-slide="<?= esc_attr($index) ?>"
                                                type="button"
                                                role="tab"
                                                aria-label="Photo <?= esc_attr($index + 1) ?>"
                                                aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <?php
    $html = ob_get_clean();

    // ============================================================
    // Stocker le HTML dans le cache transient (1h) pour les visiteurs
    // ============================================================
    if (!is_user_logged_in()) {
        set_transient($cache_key, $html, HOUR_IN_SECONDS);
    }

    return $html;
}

// Shortcode [page_pro] (gardé pour compat éventuelle)
add_shortcode('page_pro', function($atts) {
    if (!is_singular('adherent')) {
        return '<p>Ce contenu est disponible uniquement sur une fiche adhérent.</p>';
    }
    return pbd_get_fiche_pro_html(get_the_ID());
});

// Enqueue des fonts sur le single CPT adherent
// (Material Icons + Font Awesome remplacés par SVG inline → plus de wp_enqueue_style pour eux)
add_action('wp_enqueue_scripts', function() {
    if (!is_singular('adherent')) return;

    wp_enqueue_style('syne-font',    'https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700&display=swap', [], null);
    wp_enqueue_style('dm-sans-font', 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&display=swap', [], null);
});

// ============================================================
// PRELOAD DE LA COVER dans le <head> de la fiche pro
// Force le navigateur à télécharger la cover en priorité maximale
// dès qu'il découvre le <head>, avant même de parser le <body>.
// → Résout le bug de paint mobile : la cover arrive plus vite,
//   donc le first paint n'est plus retardé.
// ============================================================
add_action('wp_head', function() {
    if (!is_singular('adherent')) return;

    $cover = get_field('cover', get_the_ID());
    if (!$cover) return;

    // Même logique pick_size que dans le shortcode (sans le hard-crop check
    // car ici on veut juste la bonne taille pour le preload, le rendu utilise sa propre logique)
    $cover_src = $cover['sizes']['medium_large']
                 ?? $cover['sizes']['large']
                 ?? $cover['url'];

    echo '<link rel="preload" as="image" href="' . esc_url($cover_src) . '" fetchpriority="high">' . "\n";
}, 2);

// ============================================================
// RENDU SERVEUR (SSR) DE LA FICHE PRO
// On intercepte la requête sur les singles CPT adherent et on génère
// directement la page complète : header.php du thème + notre HTML + footer.php.
// → Le HTML est dans la réponse serveur, présent au premier paint.
// → Élimine définitivement le bug de paint mobile qu'on avait avec l'injection JS.
// → Skip le single.php du thème (qui de toute façon n'appelait pas the_content
//   sur les CPT adherent, donc on ne perd rien).
// ============================================================
add_action('template_redirect', function() {
    if (!is_singular('adherent')) return;

    // Headers anti-cache (bfcache inclus)
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    // Output : header thème → fiche → footer thème
    get_header();
    echo pbd_get_fiche_pro_html(get_the_ID());
    get_footer();
    exit;
});

// ============================================================
// CACHE-CONTROL HEADERS sur les pages adhérents
// Empêche le navigateur (Chrome Android notamment) de mettre en cache
// le HTML des fiches pro entre les déploiements.
// → Évite les désynchros entre snippet mis à jour côté serveur et
//   ancien HTML servi depuis le cache disque du navigateur.
// → Les CSS/JS/images restent cachés normalement (Cache-Control ne touche que ce HTML).
// → Côté serveur, le transient répond en quelques ms : impact perf quasi nul.
// ============================================================
add_action('send_headers', function() {
    if (!is_singular('adherent')) return;
    // no-store : désactive aussi le bfcache (back-forward cache) de Chrome/Safari
    //            qui est SÉPARÉ du cache disque et survit aux "Effacer les données"
    //            → résout le bug de paint mobile quand on arrive depuis l'annuaire
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
});

// ============================================================
// INVALIDATION AUTOMATIQUE DU CACHE PAR ADHÉRENT
// Vide le cache transient de la fiche concernée quand elle change d'état
// ============================================================
add_action('save_post_adherent', function($post_id) {
    delete_transient('pbd_fiche_pro_html_v1_' . $post_id);
});

add_action('trashed_post', function($post_id) {
    if (get_post_type($post_id) === 'adherent') {
        delete_transient('pbd_fiche_pro_html_v1_' . $post_id);
    }
});

add_action('untrashed_post', function($post_id) {
    if (get_post_type($post_id) === 'adherent') {
        delete_transient('pbd_fiche_pro_html_v1_' . $post_id);
    }
});

add_action('delete_post', function($post_id) {
    if (get_post_type($post_id) === 'adherent') {
        delete_transient('pbd_fiche_pro_html_v1_' . $post_id);
    }
});
