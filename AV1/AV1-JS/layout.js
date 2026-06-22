/* ============================================================
   layout.js — controla partes fixas da página (nav, header)
   Substitui: layout.php / header.php / footer.php
============================================================ */
const Layout = {

  /* Atualiza o bloco de cabeçalho de página — substitui o <h1> dinâmico do PHP */
  setHeader(title, sub = '') {
    document.getElementById('ph-title').innerHTML    = title;
    document.getElementById('ph-sub').textContent    = sub;
    document.title = title.replace(/<[^>]+>/g, '') + ' — WaterFalls Corp';
  },

  /* Inicializa o footer com o ano atual */
  initFooter() {
    const el = document.getElementById('year');
    if (el) el.textContent = new Date().getFullYear();
  }
};
