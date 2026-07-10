'use strict';

// ============================================================
// CAMADA DE API
// Cada método aponta para seu arquivo PHP específico
// ============================================================
const API = {

  async req(url, method = 'GET', body = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    };
    if (body) opts.body = JSON.stringify(body);
    const res  = await fetch(url, opts);
    const data = await res.json();
    return { ok: res.ok, status: res.status, data };
  },

  auth: {
    registro: (d) => API.req('api/auth/registro.php',  'POST', d),
    login:    (d) => API.req('api/auth/login.php',     'POST', d),
    logout:   ()  => API.req('api/auth/logout.php',    'POST'),
    sessao:   ()  => API.req('api/auth/sessao.php'),
  },

  servicos: {
    listar:   (cat) => API.req(`api/servicos/listar.php${cat ? '?categoria=' + cat : ''}`),
    horarios: (id, data) => API.req(`api/servicos/horarios.php?servico_id=${id}&data=${data}`),
  },

  agendamentos: {
    criar:   (d)  => API.req('api/agendamentos/criar.php',   'POST', d),
    listar:  ()   => API.req('api/agendamentos/listar.php'),
    cancelar:(id) => API.req('api/agendamentos/cancelar.php','POST', { agendamento_id: id }),
  },

  avaliacoes: {
    listar: ()  => API.req('api/avaliacoes/listar.php'),
    enviar: (d) => API.req('api/avaliacoes/enviar.php', 'POST', d),
  },

  cupons: {
    listar:  ()    => API.req('api/cupons/listar.php'),
    validar: (cod) => API.req('api/cupons/validar.php', 'POST', { codigo: cod }),
  },

  planos: {
    listar: () => API.req('api/planos/listar.php'),
  },
};

// ============================================================
// ESTADO GLOBAL
// ============================================================
const State = {
  cliente:             null,
  servicoSelecionado:  null,
  dataSelecionada:     null,
  horaSelecionada:     null,
  formaPagamento:      null,
  cupomAplicado:       null,
};

// ============================================================
// UTILITÁRIOS
// ============================================================
function toast(msg, tipo = 'info') {
  const container = document.getElementById('toastContainer');
  const el = document.createElement('div');
  el.className = `toast ${tipo}`;
  const icons = { success: '✓', error: '✗', info: 'ℹ' };
  el.innerHTML = `<span>${icons[tipo] || 'ℹ'}</span> <span>${msg}</span>`;
  container.appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

function mostrarBannerDemo(ligado) {
  const id = 'bannerDemo';
  if (!ligado) { document.getElementById(id)?.remove(); return; }
  if (document.getElementById(id)) return;
  const b = document.createElement('div');
  b.id = id;
  b.style.cssText = 'position:fixed;bottom:0;left:0;right:0;background:#1A1A2E;color:#E8D08A;text-align:center;padding:.55rem 1rem;font-size:.82rem;font-weight:600;z-index:998;letter-spacing:.04em;border-top:2px solid #C9A84C;';
  b.innerHTML = '⚡ Modo Demo — configure <code style="background:rgba(255,255,255,.1);padding:.1rem .3rem;border-radius:3px">includes/config.php</code> com suas credenciais MySQL para ativar todos os recursos.';
  document.body.appendChild(b);
}

function setLoading(btn, loading) {
  if (!btn) return;
  if (loading) {
    btn.dataset.original = btn.innerHTML;
    btn.innerHTML = `<span class="spinner"></span>`;
    btn.disabled = true;
  } else {
    btn.innerHTML = btn.dataset.original || btn.innerHTML;
    btn.disabled = false;
  }
}

function formatDate(str) {
  if (!str) return '';
  const [y, m, d] = str.split('-');
  const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
  return `${d} ${meses[parseInt(m) - 1]} ${y}`;
}

function inicialNome(nome) {
  if (!nome) return '?';
  return nome.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
}

// ============================================================
// NAVEGAÇÃO SPA
// ============================================================
function navegar(pagina) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

  const pg  = document.getElementById('pg-' + pagina);
  if (pg) pg.classList.add('active');

  const nav = document.querySelector(`.nav-link[data-page="${pagina}"]`);
  if (nav) nav.classList.add('active');

  window.scrollTo({ top: 0, behavior: 'smooth' });

  const loaders = {
    home:       carregarAvaliacoesHome,
    servicos:   () => carregarServicos(),
    avaliacoes: carregarAvaliacoes,
    cupons:     carregarCupons,
    planos:     carregarPlanos,
    perfil:     carregarPerfil,
  };
  if (loaders[pagina]) loaders[pagina]();
}

// ============================================================
// SESSÃO / AUTH
// ============================================================
async function verificarSessao() {
  const { data } = await API.auth.sessao();
  if (data.logado) {
    State.cliente = data.cliente;
    renderUILogado();
    if (data.cliente.demo) mostrarBannerDemo(true);
  } else {
    renderUIDeslogado();
  }
}

function renderUILogado() {
  document.getElementById('btnLogin').style.display    = 'none';
  document.getElementById('btnRegistro').style.display = 'none';
  document.getElementById('navPerfil').classList.remove('navbar-usuario--hidden');
  document.getElementById('navPerfilLink').classList.remove('nav-link--hidden');
  document.getElementById('nomeNavbar').textContent = State.cliente.nome.split(' ')[0];
}

function renderUIDeslogado() {
  document.getElementById('btnLogin').style.display    = 'inline-flex';
  document.getElementById('btnRegistro').style.display = 'inline-flex';
  document.getElementById('navPerfil').classList.add('navbar-usuario--hidden');
  document.getElementById('navPerfilLink').classList.add('nav-link--hidden');
}

// ============================================================
// MODAIS
// ============================================================
function abrirModal(id)  { document.getElementById(id).classList.add('open'); }
function fecharModal(id) { document.getElementById(id).classList.remove('open'); }

async function submitLogin(e) {
  e.preventDefault();
  const btn   = document.getElementById('btnSubmitLogin');
  const email = document.getElementById('loginEmail').value.trim();
  const senha = document.getElementById('loginSenha').value;
  const err   = document.getElementById('loginErro');

  err.classList.remove('show');
  setLoading(btn, true);
  const { ok, data } = await API.auth.login({ email, senha });
  setLoading(btn, false);

  if (ok) {
    State.cliente = data.cliente;
    renderUILogado();
    fecharModal('modalLogin');
    if (data.demo) mostrarBannerDemo(true);
    toast(`Bem-vinda, ${data.cliente.nome.split(' ')[0]}! ✨`, 'success');
  } else {
    err.textContent = data.erro;
    err.classList.add('show');
  }
}

async function submitRegistro(e) {
  e.preventDefault();
  const btn      = document.getElementById('btnSubmitRegistro');
  const nome     = document.getElementById('regNome').value.trim();
  const email    = document.getElementById('regEmail').value.trim();
  const telefone = document.getElementById('regTelefone').value.trim();
  const senha    = document.getElementById('regSenha').value;
  const err      = document.getElementById('registroErro');

  err.classList.remove('show');
  setLoading(btn, true);
  const { ok, data } = await API.auth.registro({ nome, email, telefone, senha });
  setLoading(btn, false);

  if (ok) {
    State.cliente = data.cliente;
    renderUILogado();
    fecharModal('modalRegistro');
    if (data.demo) mostrarBannerDemo(true);
    toast(`Conta criada! Bem-vinda, ${nome.split(' ')[0]}! 🌟`, 'success');
  } else {
    err.textContent = data.erro;
    err.classList.add('show');
  }
}

async function logout() {
  await API.auth.logout();
  State.cliente = null;
  renderUIDeslogado();
  mostrarBannerDemo(false);
  navegar('home');
  toast('Até logo! 👋');
}

// ============================================================
// SERVIÇOS
// ============================================================
let todosServicos = [];

async function carregarServicos(categoria = '') {
  const grid = document.getElementById('servicosGrid');
  grid.innerHTML = '<div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>';

  const { ok, data } = await API.servicos.listar(categoria);
  if (!ok) {
    grid.innerHTML = '<div class="empty-state"><p>Erro ao carregar serviços.</p></div>';
    return;
  }
  if (data.demo) mostrarBannerDemo(true);
  todosServicos = data.servicos;
  renderServicos(data.servicos);
}

function renderServicos(lista) {
  const grid   = document.getElementById('servicosGrid');
  const icones = { cabelo:'✂️', manicure:'💅', maquiagem:'💄', massagem:'💆', sobrancelha:'🪄' };

  if (!lista.length) {
    grid.innerHTML = '<div class="empty-state"><div class="icon">🔍</div><p>Nenhum serviço nesta categoria.</p></div>';
    return;
  }

  grid.innerHTML = lista.map(s => `
    <div class="servico-card" onclick="iniciarAgendamento(${s.id})">
      <div class="servico-cat">${icones[s.categoria] || '✨'} ${s.categoria}</div>
      <div class="servico-nome">${s.nome}</div>
      <div class="servico-desc">${s.descricao}</div>
      <div class="servico-footer">
        <div class="servico-preco">R$ ${parseFloat(s.preco).toFixed(2).replace('.', ',')}</div>
        <div class="servico-duracao">⏱ ${s.duracao_minutos} min</div>
      </div>
      <button class="btn btn-primary btn-sm btn-full-mt">Agendar</button>
    </div>
  `).join('');
}

function filtrarCategoria(cat, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  carregarServicos(cat);
}

// ============================================================
// AGENDAMENTO — 3 STEPS
// ============================================================
let agStep = 1;
let calMes, calAno;

function iniciarAgendamento(servicoId) {
  if (!State.cliente) {
    toast('Faça login para agendar.', 'error');
    abrirModal('modalLogin');
    return;
  }
  State.servicoSelecionado = todosServicos.find(s => s.id == servicoId) || null;
  if (!State.servicoSelecionado) { navegar('servicos'); return; }

  calMes = null; calAno = null;
  State.dataSelecionada = null;
  State.horaSelecionada = null;
  State.formaPagamento  = null;
  State.cupomAplicado   = null;

  document.getElementById('agServicoNome').textContent  = State.servicoSelecionado.nome;
  document.getElementById('agServicoPreco').textContent = 'R$ ' + parseFloat(State.servicoSelecionado.preco).toFixed(2).replace('.', ',');
  document.getElementById('cupomInfo').innerHTML = '';
  document.getElementById('inputCupom').value   = '';
  document.querySelectorAll('.pagamento-opt').forEach(o => o.classList.remove('selected'));
  document.querySelectorAll('.campos-pagamento').forEach(c => c.classList.remove('visivel'));
  // Limpa campos de cartão
  ['cc-nome','cc-numero','cc-validade','cc-cvv','db-nome','db-numero','db-validade','db-cvv'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  const pixChk = document.getElementById('pix-confirmado');
  const dinChk = document.getElementById('dinheiro-confirmado');
  if (pixChk) pixChk.checked = false;
  if (dinChk) dinChk.checked = false;

  agStep = 1;
  abrirModal('modalAgendar');
  irStep(1);
  renderCalendario();
}

function irStep(n) {
  agStep = n;
  document.querySelectorAll('.step-panel').forEach((p, i) => p.classList.toggle('active', i + 1 === n));
  document.querySelectorAll('.step-dot').forEach((d, i) => {
    d.classList.toggle('active', i + 1 === n);
    d.classList.toggle('done',   i + 1 < n);
  });
  // Step 1 = data/hora | Step 2 = pagamento | Step 3 = resumo
  if (n === 1) renderCalendario();
  if (n === 3) resumoAgendamento();
}

function renderCalendario() {
  const hoje = new Date();
  if (calMes == null) calMes = hoje.getMonth();
  if (calAno == null) calAno = hoje.getFullYear();

  const meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
  document.getElementById('calMesLabel').textContent = `${meses[calMes]} ${calAno}`;

  const primeiroDia = new Date(calAno, calMes, 1).getDay();
  const diasNoMes   = new Date(calAno, calMes + 1, 0).getDate();
  const hojeStr     = hoje.toISOString().slice(0, 10);

  let html = '';
  ['D','S','T','Q','Q','S','S'].forEach(d => { html += `<div class="date-header">${d}</div>`; });
  for (let i = 0; i < primeiroDia; i++) html += `<div class="date-cell empty"></div>`;

  for (let d = 1; d <= diasNoMes; d++) {
    const dataStr = `${calAno}-${String(calMes + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const isPast  = dataStr < hojeStr;
    const isSel   = dataStr === State.dataSelecionada;
    const isHoje  = dataStr === hojeStr;

    let cls = 'date-cell';
    if (isPast)  cls += ' past';
    else if (isSel)  cls += ' selected';
    else if (isHoje) cls += ' today';

    const click = isPast ? '' : `onclick="selecionarData('${dataStr}', this)"`;
    html += `<div class="${cls}" ${click}>${d}</div>`;
  }

  document.getElementById('calGrid').innerHTML = html;
  renderHorarios();
}

function mudarMes(delta) {
  calMes += delta;
  if (calMes > 11) { calMes = 0;  calAno++; }
  if (calMes < 0)  { calMes = 11; calAno--; }
  State.dataSelecionada = null;
  State.horaSelecionada = null;
  renderCalendario();
}

async function selecionarData(dataStr, el) {
  State.dataSelecionada = dataStr;
  State.horaSelecionada = null;
  document.querySelectorAll('.date-cell').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  renderHorarios();
}

async function renderHorarios() {
  const container = document.getElementById('horariosContainer');
  if (!State.dataSelecionada) {
    container.innerHTML = '<p class="horarios-placeholder">Selecione uma data acima.</p>';
    return;
  }
  container.innerHTML = '<p class="horarios-placeholder">⏳ Buscando horários...</p>';

  const { ok, data } = await API.servicos.horarios(State.servicoSelecionado.id, State.dataSelecionada);
  if (!ok) { container.innerHTML = '<p style="color:var(--error)">Erro ao buscar horários.</p>'; return; }

  container.innerHTML = `<div class="horarios-grid">${data.slots.map(s => `
    <button class="horario-btn ${s.disponivel ? '' : 'ocupado'}"
      ${s.disponivel ? `onclick="selecionarHora('${s.hora}', this)"` : 'disabled'}>
      ${s.hora}
    </button>
  `).join('')}</div>`;
}

function selecionarHora(hora, el) {
  State.horaSelecionada = hora;
  document.querySelectorAll('.horario-btn').forEach(b => b.classList.remove('selected'));
  el.classList.add('selected');
}

function avancarStep2() {
  if (!State.dataSelecionada) { toast('Selecione uma data.', 'error'); return; }
  if (!State.horaSelecionada) { toast('Selecione um horário.', 'error'); return; }
  irStep(2);
}

function selecionarPagamento(forma, el) {
  State.formaPagamento = forma;
  document.querySelectorAll('.pagamento-opt').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');

  // Esconde todos os campos, mostra só o da forma selecionada
  document.querySelectorAll('.campos-pagamento').forEach(c => c.classList.remove('visivel'));
  const campos = document.getElementById('campos-' + forma);
  if (campos) campos.classList.add('visivel');
}

// ── Máscaras de input ────────────────────────────────────────────────────────
function mascaraCartao(el) {
  let v = el.value.replace(/\D/g, '').slice(0, 16);
  el.value = v.replace(/(.{4})/g, '$1 ').trim();
}

function mascaraValidade(el) {
  let v = el.value.replace(/\D/g, '').slice(0, 4);
  if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
  el.value = v;
}

function copiarChavePix() {
  navigator.clipboard.writeText('contato@goldtouch.com.br')
    .then(() => toast('Chave Pix copiada!', 'success'))
    .catch(() => toast('Não foi possível copiar.', 'error'));
}

// ── Validação do Step 2 (pagamento) ─────────────────────────────────────────
function avancarStep3() {
  if (!State.formaPagamento) {
    toast('Selecione uma forma de pagamento.', 'error');
    return;
  }

  const forma = State.formaPagamento;

  if (forma === 'credito') {
    const nome     = document.getElementById('cc-nome').value.trim();
    const numero   = document.getElementById('cc-numero').value.replace(/\s/g, '');
    const validade = document.getElementById('cc-validade').value.trim();
    const cvv      = document.getElementById('cc-cvv').value.trim();

    if (!nome)                        { toast('Informe o nome impresso no cartão.', 'error'); return; }
    if (numero.length < 16)           { toast('Número do cartão inválido.', 'error'); return; }
    if (!/^\d{2}\/\d{2}$/.test(validade)) { toast('Validade inválida. Use MM/AA.', 'error'); return; }

    // Verifica se o cartão não está vencido
    const [mes, ano] = validade.split('/').map(Number);
    const venc = new Date(2000 + ano, mes - 1, 1);
    if (venc < new Date()) { toast('Cartão vencido.', 'error'); return; }

    if (cvv.length < 3)               { toast('CVV inválido.', 'error'); return; }
  }

  if (forma === 'debito') {
    const nome     = document.getElementById('db-nome').value.trim();
    const numero   = document.getElementById('db-numero').value.replace(/\s/g, '');
    const validade = document.getElementById('db-validade').value.trim();
    const cvv      = document.getElementById('db-cvv').value.trim();

    if (!nome)                        { toast('Informe o nome impresso no cartão.', 'error'); return; }
    if (numero.length < 16)           { toast('Número do cartão inválido.', 'error'); return; }
    if (!/^\d{2}\/\d{2}$/.test(validade)) { toast('Validade inválida. Use MM/AA.', 'error'); return; }

    const [mes, ano] = validade.split('/').map(Number);
    const venc = new Date(2000 + ano, mes - 1, 1);
    if (venc < new Date()) { toast('Cartão vencido.', 'error'); return; }

    if (cvv.length < 3)               { toast('CVV inválido.', 'error'); return; }
  }

  if (forma === 'pix') {
    const confirmado = document.getElementById('pix-confirmado').checked;
    if (!confirmado) { toast('Confirme que realizou o pagamento via Pix.', 'error'); return; }
  }

  if (forma === 'dinheiro') {
    const confirmado = document.getElementById('dinheiro-confirmado').checked;
    if (!confirmado) { toast('Confirme que está ciente do pagamento no local.', 'error'); return; }
  }

  irStep(3);
}

async function validarCupom() {
  const codigo = document.getElementById('inputCupom').value.trim().toUpperCase();
  if (!codigo) { toast('Digite um código de cupom.', 'error'); return; }

  const btn = document.getElementById('btnValidarCupom');
  setLoading(btn, true);
  const { ok, data } = await API.cupons.validar(codigo);
  setLoading(btn, false);

  const info = document.getElementById('cupomInfo');
  if (ok && data.valido) {
    State.cupomAplicado = codigo;
    info.innerHTML = `<span class="cupom-tag">✓ Cupom aplicado: <strong>${data.desconto} de desconto</strong></span>`;
    toast('Cupom aplicado!', 'success');
  } else {
    State.cupomAplicado = null;
    info.innerHTML = `<span class="form-error show">${data.erro || 'Cupom inválido.'}</span>`;
  }
}

function resumoAgendamento() {
  document.getElementById('resumoServico').textContent = State.servicoSelecionado?.nome || '-';
  document.getElementById('resumoData').textContent    = formatDate(State.dataSelecionada);
  document.getElementById('resumoHora').textContent    = State.horaSelecionada || '-';
  document.getElementById('resumoValor').textContent   = 'R$ ' + parseFloat(State.servicoSelecionado?.preco || 0).toFixed(2).replace('.', ',');
}

async function confirmarAgendamento() {
  if (!State.formaPagamento) { irStep(2); toast('Selecione a forma de pagamento.', 'error'); return; }

  const btn = document.getElementById('btnConfirmar');
  setLoading(btn, true);

  const { ok, data } = await API.agendamentos.criar({
    servico_id:      State.servicoSelecionado.id,
    data:            State.dataSelecionada,
    hora:            State.horaSelecionada,
    forma_pagamento: State.formaPagamento,
    cupom:           State.cupomAplicado || '',
  });

  setLoading(btn, false);

  if (ok) {
    fecharModal('modalAgendar');
    document.getElementById('confirmacaoMensagem').innerHTML = `
      <p><strong>${data.servico}</strong></p>
      <p>📅 ${formatDate(State.dataSelecionada)} às ${State.horaSelecionada}</p>
      <p>💰 R$ ${data.valor}${data.desconto !== '0,00' ? ` <em>(desconto: R$ ${data.desconto})</em>` : ''}</p>
      <p>⭐ +${data.pontos_ganhos} pontos ganhos!</p>
      <p class="resumo-aviso">${data.mensagem}</p>
    `;
    abrirModal('modalConfirmacao');
    if (State.cliente) State.cliente.pontos += data.pontos_ganhos;
    toast('Horário confirmado! ✨', 'success');
  } else {
    toast(data.erro || 'Erro ao agendar.', 'error');
  }
}

// ============================================================
// AVALIAÇÕES
// ============================================================
async function carregarAvaliacoes() {
  const grid = document.getElementById('avaliacoesGrid');
  grid.innerHTML = '<div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>';

  const { ok, data } = await API.avaliacoes.listar();
  if (!ok || !data.avaliacoes?.length) {
    grid.innerHTML = '<div class="empty-state"><div class="icon">💬</div><p>Nenhuma avaliação ainda.</p></div>';
    return;
  }
  grid.innerHTML = data.avaliacoes.map(a => `
    <div class="avaliacao-card">
      <div class="avaliacao-nota nota-${a.nota}"></div>
      <div class="avaliacao-texto">"${a.comentario}"</div>
      <div class="avaliacao-meta">
        <span><strong>${a.cliente}</strong> — ${a.servico}</span>
        <span>${new Date(a.criado_em).toLocaleDateString('pt-BR')}</span>
      </div>
    </div>
  `).join('');
}

async function carregarAvaliacoesHome() {
  const grid = document.getElementById('avaliacoesHomeGrid');
  if (!grid) return;
  const { ok, data } = await API.avaliacoes.listar();
  if (!ok || !data.avaliacoes?.length) return;
  grid.innerHTML = data.avaliacoes.slice(0, 3).map(a => `
    <div class="avaliacao-card">
      <div class="avaliacao-nota nota-${a.nota}"></div>
      <div class="avaliacao-texto">"${a.comentario}"</div>
      <div class="avaliacao-meta">
        <span><strong>${a.cliente}</strong></span>
        <span>${a.servico}</span>
      </div>
    </div>
  `).join('');
}

async function submitAvaliacao(e) {
  e.preventDefault();
  if (!State.cliente) { toast('Faça login para avaliar.', 'error'); return; }

  const ag_id    = document.getElementById('avAgendamentoId').value;
  const nota     = document.querySelector('input[name="nota"]:checked')?.value;
  const comentario = document.getElementById('avComentario').value.trim();

  if (!ag_id) { toast('Informe o número do agendamento.', 'error'); return; }
  if (!nota)  { toast('Selecione sua satisfação.', 'error'); return; }

  const btn = document.getElementById('btnEnviarAvaliacao');
  setLoading(btn, true);
  const { ok, data } = await API.avaliacoes.enviar({ agendamento_id: ag_id, nota, comentario });
  setLoading(btn, false);

  if (ok) {
    toast(data.mensagem, 'success');
    document.getElementById('formAvaliacao').reset();
    carregarAvaliacoes();
  } else {
    toast(data.erro, 'error');
  }
}

// ============================================================
// CUPONS
// ============================================================
async function carregarCupons() {
  const grid = document.getElementById('cuponsGrid');
  grid.innerHTML = '<div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>';

  const { ok, data } = await API.cupons.listar();
  if (!ok || !data.cupons?.length) {
    grid.innerHTML = '<div class="empty-state"><div class="icon">🏷️</div><p>Nenhum cupom disponível.</p></div>';
    return;
  }
  grid.innerHTML = data.cupons.map(c => {
    const desc = c.desconto_percent
      ? `${c.desconto_percent}% OFF`
      : `R$ ${parseFloat(c.desconto_valor).toFixed(2).replace('.', ',')} OFF`;
    const val = c.validade
      ? `Válido até ${new Date(c.validade + 'T00:00:00').toLocaleDateString('pt-BR')}`
      : 'Sem prazo';
    return `
      <div class="cupom-card">
        <div class="cupom-codigo">${c.codigo}</div>
        <div class="cupom-desconto">${desc}</div>
        <div class="cupom-info">${val}</div>
        <div class="cupom-pontos">${c.pontos_necessarios > 0 ? `⭐ Requer ${c.pontos_necessarios} pontos` : '🎁 Sem custo de pontos'}</div>
      </div>
    `;
  }).join('');
}

// ============================================================
// PLANOS
// ============================================================
async function carregarPlanos() {
  const grid = document.getElementById('planosGrid');
  if (!grid) return;
  grid.innerHTML = '<div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>';

  const { ok, data } = await API.planos.listar();
  if (!ok) return;

  const icones = ['🥈', '🥇', '💎'];
  grid.innerHTML = data.planos.map((p, i) => `
    <div class="plano-card ${i === 1 ? 'destaque' : ''}">
      ${i === 1 ? '<div class="plano-badge">Mais popular</div>' : ''}
      <div class="plano-icone">${icones[i] || '✨'}</div>
      <div class="plano-nome">${p.nome}</div>
      <div class="plano-preco">R$ ${parseFloat(p.preco).toFixed(2).replace('.', ',')} <span>/ mês</span></div>
      <div class="plano-desc">${p.descricao}<br><small>${p.servicos_inclusos}</small></div>
      <button class="btn ${i === 1 ? 'btn-primary' : 'btn-outline'} btn-lg btn-full"
        onclick="toast('Em breve! Entre em contato para assinar.', 'info')">Assinar</button>
    </div>
  `).join('');
}

// ============================================================
// PERFIL
// ============================================================
async function carregarPerfil() {
  if (!State.cliente) { navegar('home'); return; }

  document.getElementById('perfilAvatar').textContent = inicialNome(State.cliente.nome);
  document.getElementById('perfilNome').textContent   = State.cliente.nome;
  document.getElementById('perfilEmail').textContent  = State.cliente.email;
  document.getElementById('perfilPontos').textContent = `⭐ ${State.cliente.pontos} pontos`;

  const lista = document.getElementById('meusAgendamentos');
  lista.innerHTML = '<div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>';

  const { ok, data } = await API.agendamentos.listar();
  if (!ok || !data.agendamentos?.length) {
    lista.innerHTML = '<div class="empty-state"><div class="icon">📅</div><p>Nenhum agendamento ainda.<br><a href="#" onclick="navegar(\'servicos\')" class="link-dourado">Agende agora!</a></p></div>';
    return;
  }

  const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
  lista.innerHTML = data.agendamentos.map(a => {
    const dt   = new Date(a.data_hora);
    const dia  = String(dt.getDate()).padStart(2, '0');
    const mes  = meses[dt.getMonth()];
    const hora = dt.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    return `
      <div class="agendamento-item">
        <div class="agendamento-data">
          <div class="dia">${dia}</div>
          <div class="mes">${mes}</div>
        </div>
        <div class="agendamento-info">
          <div class="agendamento-servico">${a.servico}</div>
          <div class="agendamento-hora">🕐 ${hora} &nbsp;·&nbsp; 💳 ${a.forma_pagamento}</div>
        </div>
        <div class="agendamento-acoes">
          <span class="agendamento-status status-${a.status}">${a.status}</span>
          ${a.status === 'confirmado' ? `
            <button class="btn btn-danger btn-sm" onclick="cancelarAgendamento(${a.id})">Cancelar</button>
          ` : ''}
        </div>
      </div>
    `;
  }).join('');
}

async function cancelarAgendamento(id) {
  if (!confirm('Tem certeza que deseja cancelar este agendamento?')) return;
  const { ok, data } = await API.agendamentos.cancelar(id);
  if (ok) {
    toast('Agendamento cancelado.', 'success');
    carregarPerfil();
  } else {
    toast(data.erro, 'error');
  }
}

// ============================================================
// INICIALIZAÇÃO
// ============================================================
document.addEventListener('DOMContentLoaded', async () => {
  setTimeout(() => {
    const lo = document.getElementById('loadingOverlay');
    if (lo) lo.style.display = 'none';
  }, 600);

  await verificarSessao();
  navegar('home');

  document.querySelectorAll('.nav-link[data-page]').forEach(l => {
    l.addEventListener('click', (e) => {
      e.preventDefault();
      const pg = l.dataset.page;
      if (pg === 'perfil' && !State.cliente) {
        toast('Faça login para acessar seu perfil.', 'error');
        abrirModal('modalLogin');
        return;
      }
      navegar(pg);
    });
  });

  document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', (e) => { if (e.target === o) o.classList.remove('open'); });
  });

  document.getElementById('formLogin')?.addEventListener('submit', submitLogin);
  document.getElementById('formRegistro')?.addEventListener('submit', submitRegistro);
  document.getElementById('formAvaliacao')?.addEventListener('submit', submitAvaliacao);
});
