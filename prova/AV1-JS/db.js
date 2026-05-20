/* ============================================================
   db.js — camada de dados com localStorage
   Equivalente a: functions.php
============================================================ */
const DB = {
  _kp: 'wf_perguntas',
  _ku: 'wf_usuarios',

  /* ── Perguntas ── */
  lerPerguntas() {
    try { return JSON.parse(localStorage.getItem(this._kp) || '[]'); }
    catch { return []; }
  },
  salvarPerguntas(arr) {
    localStorage.setItem(this._kp, JSON.stringify(arr));
  },
  gerarId() {
    return 'Q' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
  },
  buscarPergunta(id) {
    return this.lerPerguntas().find(p => p.id === id) || null;
  },

  criarMultipla(enunciado, alternativas, correta) {
    const arr = this.lerPerguntas();
    const nova = {
      id: this.gerarId(),
      tipo: 'multipla',
      enunciado: enunciado.trim(),
      alternativas: [...alternativas],
      correta: Number(correta),
      criado_em: new Date().toLocaleString('pt-BR')
    };
    arr.push(nova);
    this.salvarPerguntas(arr);
    return nova;
  },

  criarTexto(enunciado, resposta_modelo) {
    const arr = this.lerPerguntas();
    const nova = {
      id: this.gerarId(),
      tipo: 'texto',
      enunciado: enunciado.trim(),
      resposta_modelo: resposta_modelo.trim(),
      criado_em: new Date().toLocaleString('pt-BR')
    };
    arr.push(nova);
    this.salvarPerguntas(arr);
    return nova;
  },

  alterarMultipla(id, enunciado, alternativas, correta) {
    const arr = this.lerPerguntas();
    const i = arr.findIndex(p => p.id === id && p.tipo === 'multipla');
    if (i === -1) return null;
    arr[i].enunciado    = enunciado.trim();
    arr[i].alternativas = [...alternativas];
    arr[i].correta      = Number(correta);
    arr[i].atualizado_em = new Date().toLocaleString('pt-BR');
    this.salvarPerguntas(arr);
    return arr[i];
  },

  alterarTexto(id, enunciado, resposta_modelo) {
    const arr = this.lerPerguntas();
    const i = arr.findIndex(p => p.id === id && p.tipo === 'texto');
    if (i === -1) return null;
    arr[i].enunciado       = enunciado.trim();
    arr[i].resposta_modelo = resposta_modelo.trim();
    arr[i].atualizado_em   = new Date().toLocaleString('pt-BR');
    this.salvarPerguntas(arr);
    return arr[i];
  },

  excluirPergunta(id) {
    const arr  = this.lerPerguntas();
    const nova = arr.filter(p => p.id !== id);
    if (nova.length === arr.length) return false;
    this.salvarPerguntas(nova);
    return true;
  },

  /* ── Usuários ── */
  lerUsuarios() {
    try { return JSON.parse(localStorage.getItem(this._ku) || '[]'); }
    catch { return []; }
  },

  salvarUsuario(nome, email, senha) {
    const arr = this.lerUsuarios();
    if (arr.find(u => u.email === email)) return { erro: 'E-mail já cadastrado.' };
    const novo = {
      id: this.gerarId(),
      nome: nome.trim(),
      email: email.trim(),
      senha_hash: btoa(senha),
      criado_em: new Date().toLocaleString('pt-BR')
    };
    arr.push(novo);
    localStorage.setItem(this._ku, JSON.stringify(arr));
    return novo;
  }
};
