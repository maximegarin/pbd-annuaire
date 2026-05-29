<?php
/**
 * PBD — Annuaire JS
 * Filtrage client-side (nom, catégorie, ville, reset) + pagination.
 * Injecté en footer uniquement sur les pages contenant le shortcode [annuaire].
 * Icônes pagination : SVG inline (zéro dépendance externe).
 */
add_action('wp_footer', function() {
    global $post;
    if (!is_a($post, 'WP_Post')) return;
    if (!has_shortcode($post->post_content, 'annuaire')) return;
    ?>
    <script id="annuaire-hub-js">
    (function() {
        var PER_PAGE = 12;

        // SVG inline pour les chevrons (cohérent avec pbd_icon() côté PHP)
        var ICON_CHEVRON_LEFT  = '<svg xmlns="http://www.w3.org/2000/svg" class="pbd-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>';
        var ICON_CHEVRON_RIGHT = '<svg xmlns="http://www.w3.org/2000/svg" class="pbd-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>';

        var grid        = document.getElementById('annuaire-grid');
        if (!grid) return;

        var inputNom    = document.getElementById('annuaire-s-nom');
        var pillsCat    = document.querySelectorAll('#annuaire-pills-cat .annuaire-sb-pill');
        var pillsVille  = document.querySelectorAll('#annuaire-pills-ville .annuaire-sb-pill');
        var resetBtn    = document.getElementById('annuaire-btn-reset');
        var countEl     = document.getElementById('annuaire-count');
        var emptyEl     = document.getElementById('annuaire-vide');
        var paginationEl = document.getElementById('annuaire-pagination');
        var cards       = Array.prototype.slice.call(grid.querySelectorAll('.annuaire-card'));

        var activeCat   = '';
        var activeVille = '';
        var currentPage = 0;
        var filtered    = [];

        /* ---- ÉTAT PERSISTÉ (retour depuis une fiche → on retrouve filtres + page) ---- */
        var STORAGE_KEY = 'pbd_annuaire_state';
        function saveState() {
            try {
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
                    nom: inputNom ? inputNom.value : '',
                    cat: activeCat,
                    ville: activeVille,
                    page: currentPage
                }));
            } catch (e) {}
        }
        function loadState() {
            try { return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) || null; }
            catch (e) { return null; }
        }

        /* ---- FILTRAGE ---- */
        function applyFilter() {
            var term = (inputNom ? inputNom.value : '').toLowerCase().trim();
            filtered = [];

            cards.forEach(function(card) {
                var nom   = card.getAttribute('data-nom')   || '';
                var ville = card.getAttribute('data-ville') || '';
                var cats  = (card.getAttribute('data-cat') || '').split(',').filter(Boolean);

                var matchNom   = !term || nom.indexOf(term) !== -1;
                var matchCat   = !activeCat || cats.indexOf(activeCat) !== -1;
                var matchVille = !activeVille || ville === activeVille;

                if (matchNom && matchCat && matchVille) filtered.push(card);
            });

            currentPage = 0;
            renderPage();
            saveState();
        }

        /* ---- PAGINATION ---- */
        function renderPage() {
            var start = currentPage * PER_PAGE;
            var end   = start + PER_PAGE;

            cards.forEach(function(c) { c.style.display = 'none'; });
            filtered.forEach(function(c, i) {
                c.style.display = (i >= start && i < end) ? '' : 'none';
            });

            if (countEl) {
                var n = filtered.length;
                countEl.innerHTML = '<strong>' + n + '</strong> adhérent' + (n > 1 ? 's' : '');
            }
            if (emptyEl) emptyEl.style.display = filtered.length === 0 ? '' : 'none';

            renderPagination();
        }

        function renderPagination() {
            if (!paginationEl) return;
            var totalPages = Math.ceil(filtered.length / PER_PAGE);

            if (totalPages <= 1) { paginationEl.innerHTML = ''; return; }

            var html = '';

            html += '<button class="annuaire-page-btn annuaire-page-prev"' +
                    (currentPage === 0 ? ' disabled' : '') +
                    ' aria-label="Page précédente">' +
                    ICON_CHEVRON_LEFT + '</button>';

            getPageNumbers(currentPage, totalPages).forEach(function(p) {
                if (p === '...') {
                    html += '<span class="annuaire-page-ellipsis">…</span>';
                } else {
                    html += '<button class="annuaire-page-btn annuaire-page-num' +
                            (p === currentPage ? ' active' : '') +
                            '" data-page="' + p + '" aria-label="Page ' + (p + 1) + '"' +
                            (p === currentPage ? ' aria-current="page"' : '') + '>' +
                            (p + 1) + '</button>';
                }
            });

            html += '<button class="annuaire-page-btn annuaire-page-next"' +
                    (currentPage === totalPages - 1 ? ' disabled' : '') +
                    ' aria-label="Page suivante">' +
                    ICON_CHEVRON_RIGHT + '</button>';

            paginationEl.innerHTML = html;

            paginationEl.querySelectorAll('.annuaire-page-num').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    goToPage(parseInt(btn.getAttribute('data-page'), 10));
                });
            });

            var prevBtn = paginationEl.querySelector('.annuaire-page-prev');
            var nextBtn = paginationEl.querySelector('.annuaire-page-next');
            if (prevBtn) prevBtn.addEventListener('click', function() { goToPage(currentPage - 1); });
            if (nextBtn) nextBtn.addEventListener('click', function() { goToPage(currentPage + 1); });
        }

        function goToPage(page) {
            var totalPages = Math.ceil(filtered.length / PER_PAGE);
            if (page < 0 || page >= totalPages) return;
            currentPage = page;
            renderPage();
            saveState();
            // Remonte tout en haut de la page (au-dessus du header sticky du thème)
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function getPageNumbers(current, total) {
            if (total <= 7) {
                var arr = [];
                for (var i = 0; i < total; i++) arr.push(i);
                return arr;
            }
            var pages = [0];
            if (current > 2) pages.push('...');
            for (var i = Math.max(1, current - 1); i <= Math.min(total - 2, current + 1); i++) {
                pages.push(i);
            }
            if (current < total - 3) pages.push('...');
            pages.push(total - 1);
            return pages;
        }

        /* ---- PILLS ---- */
        function setActivePill(pills, target) {
            pills.forEach(function(p) { p.classList.remove('active'); });
            target.classList.add('active');
        }

        if (inputNom) inputNom.addEventListener('input', applyFilter);

        pillsCat.forEach(function(pill) {
            var handler = function() {
                activeCat = pill.getAttribute('data-cat') || '';
                setActivePill(pillsCat, pill);
                applyFilter();
            };
            pill.addEventListener('click', handler);
            pill.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); handler(); }
            });
        });

        pillsVille.forEach(function(pill) {
            var handler = function() {
                activeVille = pill.getAttribute('data-ville') || '';
                setActivePill(pillsVille, pill);
                applyFilter();
            };
            pill.addEventListener('click', handler);
            pill.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); handler(); }
            });
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (inputNom) inputNom.value = '';
                activeCat = '';
                activeVille = '';
                if (pillsCat.length)   setActivePill(pillsCat,   pillsCat[0]);
                if (pillsVille.length) setActivePill(pillsVille, pillsVille[0]);
                applyFilter();
            });
        }

        /* ---- TOGGLE FILTRES MOBILE ---- */
        var filterToggle = document.getElementById('annuaire-filter-toggle');
        var sidebar      = document.getElementById('annuaire-sidebar');
        if (filterToggle && sidebar) {
            filterToggle.addEventListener('click', function() {
                var isOpen = sidebar.classList.toggle('is-open');
                filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        /* ---- ANTI-TÉLÉCHARGEMENT IMAGES (friction) ----
           Sur les éléments [data-protected] (covers) : bloque clic droit + drag.
           Les logos restent libres (objectif promo de l'asso). */
        document.addEventListener('contextmenu', function(e) {
            if (e.target.closest('[data-protected]')) e.preventDefault();
        });
        document.addEventListener('dragstart', function(e) {
            if (e.target.closest('[data-protected]')) e.preventDefault();
        });

        // Init — restaure l'état précédent (filtres + page) au retour depuis une fiche.
        var saved = loadState();
        if (saved) {
            if (inputNom && saved.nom) inputNom.value = saved.nom;
            activeCat   = saved.cat   || '';
            activeVille = saved.ville || '';
            pillsCat.forEach(function(p) {
                if ((p.getAttribute('data-cat') || '') === activeCat) setActivePill(pillsCat, p);
            });
            pillsVille.forEach(function(p) {
                if ((p.getAttribute('data-ville') || '') === activeVille) setActivePill(pillsVille, p);
            });
        }

        // Retire le masque preload (CSS) puis pagine : seules 12 covers chargent.
        grid.classList.remove('annuaire-grid--preload');
        applyFilter();

        // Restaure la page courante après le filtrage (applyFilter repart à 0).
        if (saved && saved.page > 0) {
            var totalPages = Math.ceil(filtered.length / PER_PAGE);
            if (saved.page < totalPages) {
                currentPage = saved.page;
                renderPage();
            }
            saveState();
        }
    })();
    </script>
    <?php
}, 100);
