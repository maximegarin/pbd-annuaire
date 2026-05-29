<?php
/**
 * PBD — Page Pro CSS
 * Injecté en wp_head uniquement sur les singles CPT adherent.
 * Icônes : SVG inline (.pbd-icon) — fini Material Icons et Font Awesome.
 */
add_action('wp_head', function() {
    if (!is_singular('adherent')) return;
    ?>
<style id="fiche-pro-css">

.fiche-pro,
.fiche-pro * { box-sizing: border-box; }
.fiche-pro { max-width: 1100px; overflow-x: hidden; }
.fiche-pro img { max-width: 100%; display: block; }
.fiche-pro a { text-decoration: none; }

/* ================= VARIABLES ================= */
.fiche-pro {
  --accent: #2D6BE4;
  --accent-soft: #EBF0FD;
  --text: #111;
  --muted: #6B7280;
  --border: #E5E7EB;
  --bg: #FFFFFF;
  --bg-soft: #F9FAFB;

  font-family: 'Inter', sans-serif;
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px 20px 80px;
  color: var(--text);
  line-height: 1.6;
}

.fiche-pro h1 {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  margin: 0;
}

/* ================= ICÔNES SVG (base) ================= */
/* Taille par défaut alignée sur le défaut Material Icons (24px) — préserve l'UI 2.0 */
.fiche-pro .pbd-icon {
  width: 24px;
  height: 24px;
  fill: currentColor;
  flex-shrink: 0;
  display: inline-block;
  vertical-align: middle;
}

/* ================= RETOUR ================= */
.fiche-pro-retour {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--muted);
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 20px;
  padding: 8px 12px 8px 6px;
  border-radius: 8px;
  transition: all .2s ease;
}

.fiche-pro-retour:hover {
  color: var(--accent);
  background: var(--accent-soft);
}

.fiche-pro-retour .pbd-icon { width: 18px; height: 18px; }

/* ================= COVER ================= */
.fiche-pro-cover {
  height: 240px;
  border-radius: 18px;
  background-color: var(--bg-soft);
  position: relative;
  overflow: hidden;
  transform: translateZ(0); /* layer GPU isolé : fix paint mobile cover lourde */
}

.fiche-pro-cover-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}

/* Anti-téléchargement (friction) sur cover + photos d'ambiance */
.fiche-pro-cover[data-protected],
.fiche-pro-slide[data-protected],
.fiche-pro-cover-img,
.fiche-pro-slide-img {
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  -webkit-user-drag: none;
  -webkit-touch-callout: none;
  pointer-events: none;
}

.fiche-pro-cover::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.25));
}

.fiche-pro-cover-defaut {
  background: linear-gradient(135deg, #2D6BE4 0%, #5b21b6 100%);
}

/* ================= MAIN ================= */
.fiche-pro-main {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 18px;
  margin-top: -40px;
  position: relative;
  padding: 0 32px 32px;
  box-shadow: 0 4px 14px rgba(0,0,0,0.04);
}

/* ================= AVATAR ================= */
.fiche-pro-avatar-wrap {
  margin-top: -58px;
  margin-bottom: 16px;
  display: inline-block;
}

.fiche-pro-logo {
  width: 116px;
  height: 116px;
  border-radius: 18px;
  object-fit: contain;
  padding: 4px;
  background: #fff;
  border: 4px solid #fff;
  box-shadow: 0 4px 14px rgba(0,0,0,0.15);
  display: block;
}

.fiche-pro-initiales {
  width: 116px;
  height: 116px;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 34px;
  letter-spacing: 0.03em;
  border: 4px solid #fff;
  box-shadow: 0 4px 14px rgba(0,0,0,0.15);
  background: #EBF0FD;
  color: #1a4db5;
}

/* ================= HEADER ================= */
.fiche-pro-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 20px;
  flex-wrap: wrap;
  margin-bottom: 24px;
}

.fiche-pro-header-gauche {
  flex: 1;
  min-width: 0;
}

.fiche-pro-nom {
  font-size: clamp(24px, 3.5vw, 32px);
  line-height: 1.2;
  margin-bottom: 10px;
  word-wrap: break-word;
}

.fiche-pro-categories {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.fiche-pro-cat {
  font-size: 12px;
  font-weight: 600;
  padding: 5px 12px;
  border-radius: 999px;
  background: #EDE9FE;
  color: #5b21b6;
}

/* ================= LIENS HEADER ================= */
.fiche-pro-liens {
  display: flex;
  gap: 8px;
}

.fiche-pro-lien {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-soft);
  border: 1px solid var(--border);
  color: var(--muted);
  transition: all .2s ease;
}

.fiche-pro-lien:hover {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

.fiche-pro-lien .pbd-icon { width: 20px; height: 20px; }

/* ================= SEPARATOR ================= */
.fiche-pro-sep {
  height: 1px;
  background: var(--border);
  margin: 20px 0;
}

/* ================= COLS ================= */
.fiche-pro-cols {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 32px;
  margin-top: 8px;
}

.fiche-pro-col-gauche,
.fiche-pro-col-droite {
  min-width: 0;
}

.fiche-pro-col-droite > * {
  max-width: 100%;
}

@media (max-width: 760px) {
  .fiche-pro-cols { grid-template-columns: 1fr; }
  .fiche-pro-col-droite { order: 1; }
  .fiche-pro-col-gauche { order: 2; }
}

/* ================= COL GAUCHE ================= */
.fiche-pro-col-gauche {
  background: var(--bg-soft);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 20px;
  align-self: start;
}

.fiche-pro-section-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--muted);
  margin-bottom: 12px;
}

.fiche-pro-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: var(--text);
  margin-bottom: 10px;
}

.fiche-pro-info:last-child { margin-bottom: 0; }

.fiche-pro-info .pbd-icon {
  color: var(--accent);
  width: 18px;
  height: 18px;
}

.fiche-pro-info strong { font-weight: 600; }

.fiche-pro-info--adresse { align-items: flex-start; }
.fiche-pro-info--adresse .pbd-icon { margin-top: 1px; }

.fiche-pro-empty-info {
  font-size: 13px;
  color: var(--muted);
  font-style: italic;
  margin: 0 0 12px;
}

/* ================= TAGS COL ================= */
.fiche-pro-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.fiche-pro-tag {
  font-size: 11px;
  font-weight: 500;
  padding: 4px 10px;
  border-radius: 999px;
  background: #F3F4F6;
  color: #374151;
}

/* ================= COL DROITE ================= */
.fiche-pro-col-droite > .fiche-pro-section-label { margin-top: 20px; }
.fiche-pro-col-droite > .fiche-pro-section-label:first-child { margin-top: 0; }

.fiche-pro-description {
  font-size: 15px;
  color: var(--text);
  margin-bottom: 8px;
  word-wrap: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}

.fiche-pro-description p {
  margin: 0 0 12px !important;
  padding: 0 !important;
  max-width: 100%;
  text-align: left;
}
.fiche-pro-description p:last-child { margin-bottom: 0 !important; }

/* ================= VERBATIM ================= */
.fiche-pro-verbatim {
  background: var(--accent-soft);
  border-left: 4px solid var(--accent);
  border-radius: 10px;
  padding: 22px 24px;
  margin: 0 0 16px;
}

.fiche-pro-verbatim-texte {
  font-family: 'Syne', sans-serif;
  font-size: 17px;
  font-style: italic;
  font-weight: 500;
  margin: 0 0 8px;
  color: var(--text);
  line-height: 1.5;
}

.fiche-pro-verbatim-auteur {
  font-size: 13px;
  font-weight: 600;
  color: var(--muted);
}

/* ================= CLIENTS ================= */
.fiche-pro-clients {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 0 0 16px;
}

.fiche-pro-client {
  font-size: 13px;
  font-weight: 500;
  padding: 8px 14px;
  border-radius: 10px;
  background: var(--bg-soft);
  border: 1px solid var(--border);
  color: var(--text);
}

/* ================= CARROUSEL ================= */
.fiche-pro-carrousel {
  position: relative;
  border-radius: 14px;
  overflow: hidden;
  background: var(--bg-soft);
  aspect-ratio: 16 / 9;
  margin-top: 4px;
}

.fiche-pro-carrousel-track {
  position: relative;
  width: 100%;
  height: 100%;
}

.fiche-pro-slide {
  position: absolute;
  inset: 0;
  opacity: 0;
  transition: opacity .4s ease;
}

.fiche-pro-slide.active { opacity: 1; }

/* Photos d'ambiance en <img> natif (au lieu de background-image) :
   permet le lazy loading des slides non actives + paint immédiat. */
.fiche-pro-slide-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}

.fiche-pro-carrousel-btns {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 12px;
  pointer-events: none;
  z-index: 2;
}

.fiche-pro-carrousel-btn {
  pointer-events: auto;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.95);
  color: var(--text);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: all .2s ease;
}

.fiche-pro-carrousel-btn:hover {
  background: #fff;
  color: var(--accent);
  transform: scale(1.05);
}

.fiche-pro-carrousel-nav {
  position: absolute;
  bottom: 14px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 6px;
  z-index: 2;
}

.fiche-pro-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.55);
  cursor: pointer;
  padding: 0;
  transition: all .2s ease;
}

.fiche-pro-dot.active {
  background: #fff;
  width: 24px;
  border-radius: 4px;
}

/* ================= CTA LIENS (col gauche) ================= */
.fiche-pro-cta-liens {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.fiche-pro-cta-lien {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 11px 14px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  color: #111;
  background: #fff;
  border: 1px solid #111;
  text-decoration: none;
  transition: background .2s ease, color .2s ease;
  cursor: pointer;
  overflow: hidden;
}

.fiche-pro-cta-lien .pbd-icon {
  width: 15px;
  height: 15px;
  flex-shrink: 0;
  color: inherit;
}

.fiche-pro-cta-label {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.fiche-pro-cta-lien:hover {
  background: #111;
  color: #fff;
}

/* ================= TOAST COPIE ================= */
.fiche-pro-copy-toast {
  position: fixed;
  bottom: 28px;
  left: 50%;
  transform: translateX(-50%) translateY(6px);
  background: #111;
  color: #fff;
  font-size: 13px;
  font-weight: 500;
  padding: 10px 20px;
  border-radius: 8px;
  opacity: 0;
  pointer-events: none;
  transition: opacity .2s ease, transform .2s ease;
  z-index: 9999;
  white-space: nowrap;
}

.fiche-pro-copy-toast.show {
  opacity: 1;
  transform: translateX(-50%) translateY(0);
}

/* ================= ERREUR ================= */
.fiche-pro-erreur {
  text-align: center;
  padding: 60px 20px;
  font-size: 15px;
  color: var(--muted);
  background: var(--bg-soft);
  border-radius: 14px;
  margin: 24px auto;
  max-width: 600px;
}

.fiche-pro-erreur a {
  color: var(--accent);
  font-weight: 600;
}

/* ================= RESPONSIVE ================= */
@media (max-width: 600px) {
  /* Box plus courte sur mobile : cover zoome moins -> montre plus de largeur
     (logos/texte en bord de bannière) sans bandes vides. */
  .fiche-pro-cover { height: 130px; }
  .fiche-pro-main { padding: 0 20px 24px; }
  .fiche-pro-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  .fiche-pro-liens { width: 100%; }
  .fiche-pro-logo,
  .fiche-pro-initiales { width: 90px; height: 90px; }
  .fiche-pro-initiales { font-size: 28px; }
}

</style>
<?php
}, 100);
