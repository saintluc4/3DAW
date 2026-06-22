/* ============================================================
   router.js — controla qual view renderizar
   Substitui: navegação via arquivos .php separados
============================================================ */
const Router = {
  currentPage: 'dashboard',
  params: {},

  /* Navega para uma página passando parâmetros opcionais */
  go(page, params = {}) {
    this.currentPage = page;
    this.params      = params;
    this._render();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  },

  /* Atualiza destaque do link ativo na navbar */
  _updateNav() {
    document.querySelectorAll('.nav-link[data-page]').forEach(el => {
      el.classList.toggle('active', el.dataset.page === this.currentPage);
    });
  },

  /* Renderiza a view correspondente à página atual */
  _render() {
    this._updateNav();

    const app = document.getElementById('app');
    app.innerHTML = '';

    const viewFn = Views[this.currentPage];
    if (!viewFn) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'page-view';
    wrapper.innerHTML = viewFn(this.params);
    app.appendChild(wrapper);

    /* Registra eventos da view recém-renderizada */
    Events.attach(this.currentPage, wrapper);
  }
};

/* Atalho global para uso inline nos botões HTML (onclick="navigate(...)") */
function navigate(page, params = {}) {
  Router.go(page, params);
}
