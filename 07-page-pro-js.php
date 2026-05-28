<?php
/**
 * PBD — Page Pro JS
 * Carrousel photos (prev/next, dots, navigation clavier).
 * Copie presse-papier sur les liens CTA avec toast de confirmation.
 * Retry sur injection différée du HTML de la fiche.
 * (Aucune icône utilisée ici — pas de modif vs version 2.0)
 */
add_action('wp_footer', function() {
    if (!is_singular('adherent')) return;
    ?>
    <script id="fiche-pro-js">
    (function() {

        /* ---- COPIE PRESSE-PAPIER ---- */
        function initCopy() {
            var liens = document.querySelectorAll('.fiche-pro-cta-lien[data-copy]');
            if (!liens.length) return false;

            var toast = document.createElement('div');
            toast.className = 'fiche-pro-copy-toast';
            document.body.appendChild(toast);
            var toastTimer;

            function showToast(msg) {
                clearTimeout(toastTimer);
                toast.textContent = msg;
                toast.classList.add('show');
                toastTimer = setTimeout(function() {
                    toast.classList.remove('show');
                }, 2000);
            }

            liens.forEach(function(lien) {
                lien.addEventListener('click', function(e) {
                    e.preventDefault();
                    var val = lien.getAttribute('data-copy');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(val).then(function() {
                            showToast('Copié dans le presse-papier ✓');
                        });
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = val;
                        ta.style.cssText = 'position:fixed;opacity:0';
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        document.body.removeChild(ta);
                        showToast('Copié dans le presse-papier ✓');
                    }
                });
            });

            return true;
        }

        /* ---- CARROUSEL ---- */
        function init() {
            var track = document.getElementById('fiche-track');
            if (!track) return true;

            var slides = track.querySelectorAll('.fiche-pro-slide');
            var dots   = document.querySelectorAll('.fiche-pro-dot');
            var prev   = document.getElementById('fiche-prev');
            var next   = document.getElementById('fiche-next');
            if (slides.length === 0) return true;

            var current = 0;

            function goTo(i) {
                current = (i + slides.length) % slides.length;
                slides.forEach(function(s, idx) {
                    s.classList.toggle('active', idx === current);
                });
                dots.forEach(function(d, idx) {
                    d.classList.toggle('active', idx === current);
                    d.setAttribute('aria-selected', idx === current ? 'true' : 'false');
                });
            }

            if (prev) prev.addEventListener('click', function() { goTo(current - 1); });
            if (next) next.addEventListener('click', function() { goTo(current + 1); });

            dots.forEach(function(d) {
                d.addEventListener('click', function() {
                    var idx = parseInt(d.getAttribute('data-slide'), 10);
                    if (!isNaN(idx)) goTo(idx);
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft')  goTo(current - 1);
                if (e.key === 'ArrowRight') goTo(current + 1);
            });

            return true;
        }

        /* ---- ANTI-TÉLÉCHARGEMENT IMAGES (friction) ----
           Sur cover + photos d'ambiance ([data-protected]) : bloque clic droit + drag.
           Le logo reste libre (objectif promo de l'asso). */
        document.addEventListener('contextmenu', function(e) {
            if (e.target.closest('[data-protected]')) e.preventDefault();
        });
        document.addEventListener('dragstart', function(e) {
            if (e.target.closest('[data-protected]')) e.preventDefault();
        });

        // Tente d'initialiser tout de suite, sinon attend l'injection
        if (!init() || !initCopy()) {
            var attempts = 0;
            var interval = setInterval(function() {
                attempts++;
                var done = init() && initCopy();
                if (done || attempts > 20) {
                    clearInterval(interval);
                }
            }, 100);
        }
    })();
    </script>
    <?php
}, 200);
