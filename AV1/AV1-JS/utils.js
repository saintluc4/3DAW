/* ============================================================
   utils.js — funções auxiliares compartilhadas
   Equivalente a: helpers inline do layout.php
============================================================ */
const Utils = {

  /* Escapa HTML — substitui htmlspecialchars() do PHP */
  esc(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  },

  /* Trunca string com reticências — substitui mb_strimwidth() */
  trunc(s, n) {
    return s.length > n ? s.slice(0, n) + '…' : s;
  },

  /* Quebras de linha em <br> — substitui nl2br() */
  nl2br(s) {
    return this.esc(s).replace(/\n/g, '<br>');
  },

  /* Badge colorido por tipo de pergunta */
  badgeTipo(tipo) {
    return tipo === 'multipla'
      ? `<span class="badge badge-multipla">Múltipla</span>`
      : `<span class="badge badge-texto">Dissertativa</span>`;
  },

  /* Gera bloco de alerta */
  alertHtml(msg, tipo) {
    return `<div class="alert alert-${tipo}">${this.esc(msg)}</div>`;
  },

  /* Renderiza lista de mensagens (erros / sucessos) */
  renderMsgs(msgs = []) {
    return msgs.map(m => this.alertHtml(m.txt, m.tipo)).join('');
  }
};

/* Letras das alternativas — usado nas views de múltipla escolha */
const LETRAS = ['A', 'B', 'C', 'D', 'E'];
