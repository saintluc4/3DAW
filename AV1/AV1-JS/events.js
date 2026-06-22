/* ============================================================
   events.js — registra event listeners após cada renderização
   Substitui: processamento de $_POST em cada arquivo .php
============================================================ */
const Events = {

  attach(page, container) {
    const methods = {
      criar_multipla:  () => this._criarMultipla(container),
      criar_texto:     () => this._criarTexto(container),
      alterar_multipla:() => this._alterarMultipla(container),
      alterar_texto:   () => this._alterarTexto(container),
      excluir:         () => this._excluir(container),
      listar:          () => this._listar(container),
      usuarios:        () => this._usuarios(container),
    };
    methods[page]?.();
  },

  /* ── listar.php — busca em tempo real ───────────────────── */
  _listar(container) {
    const input = container.querySelector('#busca-input');
    if (!input) return;
    input.addEventListener('input', () => {
      navigate('listar', {
        filtro: Router.params.filtro || 'todas',
        busca:  input.value
      });
    });
  },

  /* ── criar_multipla.php ─────────────────────────────────── */
  _criarMultipla(container) {
    container.querySelector('#form-cm')?.addEventListener('submit', e => {
      e.preventDefault();

      const enunciado = container.querySelector('#enunciado').value.trim();
      const alts      = LETRAS.map((_, i) => container.querySelector(`[name="alt${i}"]`)?.value.trim() || '');
      const cortadaEl = container.querySelector('[name="correta"]:checked');
      const correta   = cortadaEl ? Number(cortadaEl.value) : -1;

      const erros = this._validarMultipla(enunciado, alts, correta);
      if (erros.length) {
        navigate('criar_multipla', { msgs: erros, dados: { enunciado, alternativas: alts, correta } });
        return;
      }

      const { limpas, novoCorreta } = this._compactarAlts(alts, correta);
      DB.criarMultipla(enunciado, limpas, novoCorreta);
      navigate('listar', { msgs: [{ txt: 'Pergunta de múltipla escolha criada com sucesso!', tipo: 'success' }] });
    });
  },

  /* ── criar_texto.php ────────────────────────────────────── */
  _criarTexto(container) {
    container.querySelector('#form-ct')?.addEventListener('submit', e => {
      e.preventDefault();

      const enunciado       = container.querySelector('#enunciado').value.trim();
      const resposta_modelo = container.querySelector('#resposta_modelo').value.trim();

      const erros = this._validarTexto(enunciado, resposta_modelo);
      if (erros.length) {
        navigate('criar_texto', { msgs: erros, dados: { enunciado, resposta_modelo } });
        return;
      }

      DB.criarTexto(enunciado, resposta_modelo);
      navigate('listar', { msgs: [{ txt: 'Pergunta dissertativa criada com sucesso!', tipo: 'success' }] });
    });
  },

  /* ── alterar_multipla.php ───────────────────────────────── */
  _alterarMultipla(container) {
    container.querySelector('#form-am')?.addEventListener('submit', e => {
      e.preventDefault();

      const id        = container.querySelector('#edit-id').value;
      const enunciado = container.querySelector('#enunciado').value.trim();
      const alts      = LETRAS.map((_, i) => container.querySelector(`[name="alt${i}"]`)?.value.trim() || '');
      const cortadaEl = container.querySelector('[name="correta"]:checked');
      const correta   = cortadaEl ? Number(cortadaEl.value) : -1;

      const erros = this._validarMultipla(enunciado, alts, correta);
      if (erros.length) {
        navigate('alterar_multipla', {
          id,
          pergunta: DB.buscarPergunta(id),
          msgs: erros,
          dados: { enunciado, alternativas: alts, correta }
        });
        return;
      }

      const { limpas, novoCorreta } = this._compactarAlts(alts, correta);
      DB.alterarMultipla(id, enunciado, limpas, novoCorreta);
      navigate('visualizar', { id, msgs: [{ txt: 'Pergunta atualizada com sucesso!', tipo: 'success' }] });
    });
  },

  /* ── alterar_texto.php ──────────────────────────────────── */
  _alterarTexto(container) {
    container.querySelector('#form-at')?.addEventListener('submit', e => {
      e.preventDefault();

      const id              = container.querySelector('#edit-id').value;
      const enunciado       = container.querySelector('#enunciado').value.trim();
      const resposta_modelo = container.querySelector('#resposta_modelo').value.trim();

      const erros = this._validarTexto(enunciado, resposta_modelo);
      if (erros.length) {
        navigate('alterar_texto', {
          id,
          pergunta: DB.buscarPergunta(id),
          msgs: erros,
          dados: { enunciado, resposta_modelo }
        });
        return;
      }

      DB.alterarTexto(id, enunciado, resposta_modelo);
      navigate('visualizar', { id, msgs: [{ txt: 'Pergunta atualizada com sucesso!', tipo: 'success' }] });
    });
  },

  /* ── excluir.php — botão de confirmação inline ──────────── */
  _excluir(container) {
    container.querySelector('#btn-confirm-del')?.addEventListener('click', () => {
      DB.excluirPergunta(Router.params.id);
      navigate('listar', { msgs: [{ txt: 'Pergunta excluída com sucesso.', tipo: 'success' }] });
    });
  },

  /* ── usuarios.php ───────────────────────────────────────── */
  _usuarios(container) {
    container.querySelector('#form-user')?.addEventListener('submit', e => {
      e.preventDefault();

      const nome  = container.querySelector('#u-nome').value.trim();
      const email = container.querySelector('#u-email').value.trim();
      const senha = container.querySelector('#u-senha').value;
      const conf  = container.querySelector('#u-conf').value;

      const erros = [];
      if (!nome)  erros.push({ txt: 'Nome é obrigatório.', tipo: 'error' });
      if (!email) erros.push({ txt: 'E-mail é obrigatório.', tipo: 'error' });
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
        erros.push({ txt: 'E-mail inválido.', tipo: 'error' });
      if (senha.length < 6)
        erros.push({ txt: 'Senha deve ter pelo menos 6 caracteres.', tipo: 'error' });
      if (senha !== conf)
        erros.push({ txt: 'As senhas não coincidem.', tipo: 'error' });

      if (erros.length) {
        navigate('usuarios', { msgs: erros, dados: { nome, email } });
        return;
      }

      const res = DB.salvarUsuario(nome, email, senha);
      if (res.erro) {
        navigate('usuarios', { msgs: [{ txt: res.erro, tipo: 'error' }], dados: { nome, email } });
        return;
      }
      navigate('usuarios', { msgs: [{ txt: `Usuário "${res.nome}" cadastrado com sucesso!`, tipo: 'success' }] });
    });
  },

  /* ── Validações compartilhadas ───────────────────────────── */
  _validarMultipla(enunciado, alts, correta) {
    const erros = [];
    if (!enunciado) erros.push({ txt: 'O enunciado é obrigatório.', tipo: 'error' });
    if (alts.filter(a => a !== '').length < 2)
      erros.push({ txt: 'Informe pelo menos 2 alternativas.', tipo: 'error' });
    if (correta < 0 || !alts[correta])
      erros.push({ txt: 'Selecione a alternativa correta válida.', tipo: 'error' });
    return erros;
  },

  _validarTexto(enunciado, resposta_modelo) {
    const erros = [];
    if (!enunciado)       erros.push({ txt: 'O enunciado é obrigatório.', tipo: 'error' });
    if (!resposta_modelo) erros.push({ txt: 'A resposta modelo é obrigatória.', tipo: 'error' });
    return erros;
  },

  /* Remove alternativas vazias e recalcula índice da correta */
  _compactarAlts(alts, correta) {
    const limpas = [];
    let novoCorreta = 0, idx = 0;
    alts.forEach((a, i) => {
      if (a) {
        if (i === correta) novoCorreta = idx;
        limpas.push(a);
        idx++;
      }
    });
    return { limpas, novoCorreta };
  }
};
