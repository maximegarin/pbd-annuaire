<?php
/**
 * PBD — Annuaire CSS
 * Injecté en wp_head uniquement sur les pages contenant le shortcode [annuaire].
 * Icônes : SVG inline (.pbd-icon) — fini Material Icons et Font Awesome.
 */
add_action('wp_head', function() {
    global $post;
    if (!is_a($post, 'WP_Post')) return;
    if (!has_shortcode($post->post_content, 'annuaire')) return;
    ?>
<style id="annuaire-hub-css">

.annuaire-hub,
.annuaire-hub * { box-sizing: border-box; }
.annuaire-hub { max-width: 100%; overflow-x: hidden; }
.annuaire-hub img { max-width: 100%; display: block; }
.annuaire-hub a { text-decoration: none; }

.annuaire-hub {
  --accent: #2D6BE4;
  --accent-soft: #EBF0FD;
  --text: #111;
  --muted: #6B7280;
  --border: #E5E7EB;
  --bg: #FFFFFF;
  --bg-soft: #F9FAFB;

  display: flex;
  gap: 40px;
  align-items: flex-start;
  font-family: 'DM Sans', sans-serif;
}

/* ================= ICÔNES SVG (base) ================= */
/* Taille par défaut alignée sur le défaut Material Icons (24px) — préserve l'UI 2.0 */
.annuaire-hub .pbd-icon {
  width: 24px;
  height: 24px;
  fill: currentColor;
  flex-shrink: 0;
  display: inline-block;
  vertical-align: middle;
}

/* ================= SIDEBAR ================= */
.annuaire-sidebar {
  width: 260px;
  flex-shrink: 0;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 22px;
  position: sticky;
  top: 24px;
}

.annuaire-sidebar > * { width: 100%; }

/* ================= MAIN ================= */
.annuaire-main {
  flex: 1;
  padding-left: 10px;
}

/* ================= GRID ================= */
.annuaire-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 28px;
}

/* ================= CARD ================= */
.annuaire-card {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 18px;
  overflow: hidden;
  transition: all .25s ease;
  box-shadow: 0 4px 14px rgba(0,0,0,0.04);
  display: flex;
  flex-direction: column;
  position: relative;
}

.annuaire-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 14px 40px rgba(0,0,0,0.12);
}

/* ================= LIEN CARD CLIQUABLE ================= */
.annuaire-card-link {
  position: absolute;
  inset: 0;
  z-index: 1;
  text-indent: -9999px;
  overflow: hidden;
}

.annuaire-card-link:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: -2px;
  border-radius: 18px;
}

.annuaire-card-avatar-wrap,
.annuaire-card-liens,
.annuaire-card-liens a {
  position: relative;
  z-index: 2;
}

/* ================= COVER ================= */
.annuaire-card-cover {
  height: 132px;
  background-size: cover;
  background-position: center;
  position: relative;
}

/* Anti-téléchargement (friction) sur les covers — désactive sélection/drag */
.annuaire-card-cover[data-protected] {
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  -webkit-user-drag: none;
  -webkit-touch-callout: none;
}

.annuaire-card-cover::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.2));
}

/* ================= AVATAR ================= */
.annuaire-card-avatar-wrap {
  margin: -60px 0 0 20px;
  display: inline-block;
}

.annuaire-card-logo {
  width: 96px;
  height: 96px;
  aspect-ratio: 1/1;
  border-radius: 14px;
  object-fit: contain;
  padding: 8px;
  background: #fff;
  border: 3px solid #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  display: block;
}

/* ================= BODY ================= */
.annuaire-card-body {
  padding: 4px 20px 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

/* ================= TEXT ================= */
.annuaire-card-nom {
  font-size: 16px;
  font-weight: 600;
  margin: 4px 0 6px;
  color: var(--text);
}

.annuaire-card-categories {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 8px;
}

.annuaire-card-categorie {
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 999px;
  background: #EDE9FE;
  color: #5b21b6;
  display: inline-block;
}

/* ================= TAGS ================= */
.annuaire-card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 4px 0 12px;
}

.annuaire-tag {
  font-size: 12px;
  padding: 5px 12px;
  border-radius: 999px;
  background: #F3F4F6;
  color: #374151;
}

/* ================= FOOTER ================= */
.annuaire-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid var(--border);
  padding-top: 12px;
  margin-top: auto;
}

.annuaire-card-ville {
  font-size: 13px;
  color: var(--muted);
  display: flex;
  align-items: center;
  gap: 4px;
}

/* SVG compactés dans le footer des cards — compense la différence
   de proportions entre Material Icons (padding interne au glyphe)
   et SVG strict (remplit toute la box) */
.annuaire-card-ville .pbd-icon { width: 16px; height: 16px; }

/* ================= LIENS ================= */
.annuaire-card-liens {
  display: flex !important;
  flex-direction: row !important;
  gap: 6px;
  align-items: center;
}

.annuaire-card-liens a {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-soft);
  border: 1px solid var(--border);
  color: var(--muted);
  transition: all .2s ease;
}

.annuaire-card-liens a:hover {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

.annuaire-card-liens a .pbd-icon { width: 18px; height: 18px; }

/* ================= SIDEBAR UPGRADE ================= */
.annuaire-sidebar-title {
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--muted);
  margin-bottom: 18px;
}

.annuaire-sb-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--muted);
  margin: 18px 0 8px;
}

.annuaire-search {
  width: 100%;
  height: 42px;
  border-radius: 10px;
  border: 1px solid var(--border);
  padding: 0 12px 0 36px;
  background: var(--bg-soft);
  font-size: 13px;
  transition: all .2s ease;
}

.annuaire-search:focus {
  border-color: var(--accent);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(45,107,228,0.15);
}

.annuaire-search-wrap { position: relative; }

.annuaire-search-wrap .pbd-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: var(--muted);
}

.annuaire-sb-pills {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

#annuaire-pills-ville {
  max-height: 280px;
  overflow-y: auto;
  padding-right: 4px;
  scrollbar-width: thin;
  scrollbar-color: var(--border) transparent;
}

#annuaire-pills-ville::-webkit-scrollbar {
  width: 6px;
}

#annuaire-pills-ville::-webkit-scrollbar-thumb {
  background: var(--border);
  border-radius: 3px;
}

#annuaire-pills-ville::-webkit-scrollbar-thumb:hover {
  background: var(--muted);
}

.annuaire-sb-pill {
  padding: 8px 12px;
  border-radius: 10px;
  background: var(--bg-soft);
  border: 1px solid transparent;
  font-size: 13px;
  color: var(--muted);
  cursor: pointer;
  transition: all .2s ease;
}

.annuaire-sb-pill:hover {
  background: #fff;
  border-color: var(--border);
  color: var(--text);
}

.annuaire-sb-pill.active {
  background: var(--accent-soft);
  color: var(--accent);
  border-color: rgba(45,107,228,0.3);
  font-weight: 500;
}

.annuaire-sb-sep {
  height: 1px;
  background: var(--border);
  margin: 18px 0;
}

.annuaire-sb-reset {
  width: 100%;
  margin-top: 20px;
  padding: 10px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: #fff;
  font-size: 13px;
  color: var(--muted);
  cursor: pointer;
  transition: all .2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.annuaire-sb-reset:hover {
  background: var(--accent-soft);
  color: var(--accent);
  border-color: var(--accent);
}

/* ================= INITIALES (NO LOGO) ================= */
.annuaire-card-initiales {
  width: 96px;
  height: 96px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 22px;
  letter-spacing: 0.03em;
  border: 3px solid #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  background: #EBF0FD;
  color: #1a4db5;
}

.annuaire-initiales-1 { background: #EBF0FD; color: #1a4db5; }
.annuaire-initiales-2 { background: #E1F5EE; color: #0F6E56; }
.annuaire-initiales-3 { background: #FAECE7; color: #712B13; }
.annuaire-initiales-4 { background: #EDE9FE; color: #5b21b6; }
.annuaire-initiales-5 { background: #FEF9C3; color: #854d0e; }
.annuaire-initiales-6 { background: #FCE7F3; color: #9d174d; }
.annuaire-initiales-7 { background: #E0F2FE; color: #0369a1; }
.annuaire-initiales-8 { background: #F0FDF4; color: #166534; }

/* ================= COMPTEUR ================= */
.annuaire-count {
  font-size: 14px;
  color: var(--muted);
  margin-bottom: 20px;
}

.annuaire-count strong {
  color: var(--text);
  font-weight: 700;
}

/* ================= EMPTY STATE ================= */
.annuaire-vide {
  text-align: center;
  color: var(--muted);
  padding: 40px;
  background: var(--bg-soft);
  border-radius: 12px;
  margin-top: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

/* ================= PAGINATION ================= */
.annuaire-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 32px;
  flex-wrap: wrap;
}

.annuaire-page-btn {
  min-width: 40px;
  height: 40px;
  padding: 0 10px;
  border-radius: 10px;
  border: 1px solid #111;
  background: #fff;
  color: #111;
  font-size: 14px;
  font-weight: 500;
  font-family: 'DM Sans', sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .2s ease, color .2s ease;
}

.annuaire-page-btn:hover:not(:disabled) {
  background: #111;
  color: #fff;
}

.annuaire-page-btn.active {
  background: #111;
  color: #fff;
  pointer-events: none;
}

.annuaire-page-btn:disabled {
  border-color: var(--border);
  color: var(--muted);
  cursor: default;
}

.annuaire-page-btn .pbd-icon {
  width: 20px;
  height: 20px;
}

.annuaire-page-ellipsis {
  width: 32px;
  text-align: center;
  color: var(--muted);
  font-size: 14px;
  line-height: 40px;
}

/* ================= FILTER TOGGLE (mobile uniquement) ================= */
.annuaire-filter-toggle {
  display: none;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 12px 16px;
  background: #fff;
  border: 1px solid #111;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  font-family: 'DM Sans', sans-serif;
  color: #111;
  cursor: pointer;
  transition: background .2s ease, color .2s ease;
}

.annuaire-filter-toggle:hover {
  background: #111;
  color: #fff;
}

.annuaire-filter-toggle-label { flex: 1; text-align: left; }

.annuaire-filter-toggle .pbd-icon { width: 20px; height: 20px; }

.annuaire-filter-toggle-chevron {
  transition: transform .25s ease;
}

.annuaire-filter-toggle[aria-expanded="true"] .annuaire-filter-toggle-chevron {
  transform: rotate(180deg);
}

/* ================= RESPONSIVE ================= */
@media (max-width: 900px) {
  .annuaire-hub { flex-direction: column; }
  .annuaire-sidebar { width: 100%; position: static; display: none; }
  .annuaire-sidebar.is-open { display: block; }
  .annuaire-filter-toggle { display: flex; }
}

@media (max-width: 480px) {
  .annuaire-grid { grid-template-columns: 1fr; }
}

</style>
<?php
}, 100);
