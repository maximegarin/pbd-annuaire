<?php
/**
 * PBD — CPT Adhérent
 * Public mais non indexable.
 * - Visible dans l'admin pour créer/éditer les fiches
 * - URL native /adherent/{slug}/ existe mais n'est pas indexée par Google
 * - Exclu du sitemap WordPress
 * - Définit aussi le helper pbd_icon() (SVG inline) utilisé par snippets 02 et 05
 */
add_action('init', function() {
    register_post_type('adherent', [
        'labels' => [
            'name'          => 'Adhérents',
            'singular_name' => 'Adhérent',
            'add_new_item'  => 'Ajouter un adhérent',
            'edit_item'     => "Modifier l'adhérent",
            'menu_name'     => 'Adhérents',
        ],
        'public'       => true,
        'has_archive'  => false,
        'rewrite'      => ['slug' => 'adherent'],
        'supports'     => ['title'],
        'menu_icon'    => 'dashicons-networking',
        'show_in_rest' => true,
    ]);
});

// Empêche l'indexation des URLs /adherent/{slug}/ par les moteurs de recherche
add_action('wp_head', function() {
    if (is_singular('adherent')) {
        echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    }
}, 1);

// Exclut le CPT du sitemap WordPress natif
add_filter('wp_sitemaps_post_types', function($post_types) {
    unset($post_types['adherent']);
    return $post_types;
});

/**
 * HELPER : pbd_icon($name, $extra_class = '')
 * Retourne un SVG inline pour l'icône demandée.
 * Remplace Material Icons et Font Awesome (zéro dépendance externe).
 *
 * Usage côté template :
 *   <?= pbd_icon('search') ?>
 *   <?= pbd_icon('chevron_right', 'ma-classe-custom') ?>
 *
 * Côté JS (pour les icônes générées dynamiquement) :
 *   utiliser les constantes PBD_ICONS injectées via wp_localize_script
 */
if (!function_exists('pbd_icon')) {
    function pbd_icons_library() {
        return [
            'arrow_back'    => '<path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20z"/>',
            'search'        => '<path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l4.25 4.25c.41.41 1.08.41 1.49 0 .41-.41.41-1.08 0-1.49L15.5 14zm-6 0a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9z"/>',
            'tune'          => '<path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/>',
            'chevron_left'  => '<path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>',
            'chevron_right' => '<path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>',
            'expand_more'   => '<path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/>',
            'location_on'   => '<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>',
            'email'         => '<path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>',
            'language'      => '<path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM19 8h-2.95c-.32-1.25-.78-2.45-1.38-3.56A8.03 8.03 0 0 1 18.92 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56A7.987 7.987 0 0 1 5.08 16zm2.95-8H5.08a7.987 7.987 0 0 1 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/>',
            'search_off'    => '<path d="M9.5 3A6.5 6.5 0 0 1 16 9.5c0 1.61-.59 3.09-1.56 4.23l.27.27h.79l5 5-1.5 1.5-5-5v-.79l-.27-.27A6.516 6.516 0 0 1 9.5 16 6.5 6.5 0 0 1 3 9.5 6.5 6.5 0 0 1 9.5 3zM5.6 6.66 7 5.27a6.07 6.07 0 0 1 5.5-1.13l-1.6 1.6c-1.5.15-2.97.79-4.3 1.92zm8.74 7.74A6.07 6.07 0 0 1 12.5 15a6.05 6.05 0 0 1-3.74-1.27l1.62-1.62a4.5 4.5 0 0 0 5.59-5.59l1.62-1.62A6.06 6.06 0 0 1 14.34 14.4z"/>',
            'person'        => '<path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>',
            'group'         => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>',
            'phone'         => '<path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>',
            'linkedin'      => '<path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>',
        ];
    }

    function pbd_icon($name, $extra_class = '') {
        $icons = pbd_icons_library();
        if (!isset($icons[$name])) return '';
        $class = 'pbd-icon' . ($extra_class ? ' ' . $extra_class : '');
        return '<svg xmlns="http://www.w3.org/2000/svg" class="' . esc_attr($class) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $icons[$name] . '</svg>';
    }
}
