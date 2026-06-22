/* =============================================================
   Gold Touch — JS principal
   Todas as requisições: fetch() → PHP API → JSON
   ============================================================= */

'use strict';

// ============================================================
// 1. CAMADA DE API — todas as chamadas ao back-end PHP
// ============================================================
const API = {
  base: 'api/',

  async req(endpoint, method = 'GET', body = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    };
    if (body) opts.body = JSON.stringify(body);
    const res = await fetch(endpoint, opts);
    const data = await res.json();
    return { ok: res.ok, status: res.status, data };
  },

  // -- Auth --
  auth: {
    registro:  (d)  => API.req('api/auth.php?action=registro',  'POST', d),
    login:     (d)  => API.req('api/auth.php?action=login',     'POST', d),
    logout:    ()   => API.req('api/auth.php?action=logout',    'POST'),
    sessao:    ()   => API.req('api/auth.php?action=sessao'),
  },

  // -- Serviços --
  servicos: {
    listar:   (cat) => API.req(`api/servicos.php?action=listar${cat ? '&categoria=' + cat : ''}`),
    horarios: (id, data) => API.req(`api/servicos.php?action=horarios&servico_id=${id}&data=${data}`),
  },

  // -- Agendamentos --
  agendamentos: {
    criar:    (d)  => API.req('api/agendamentos.php?action=criar',    'POST', d),
    meus:     ()   => API.req('api/agendamentos.php?action=meus'),
    cancelar: (id) => API.req('api/agendamentos.php?action=cancelar', 'POST', { agendamento_id: id }),
  },

  // -- Avaliações --
  avaliacoes: {
    enviar: (d) => API.req('api/extras.php?endpoint=avaliacoes&action=enviar', 'POST', d),
    listar: ()  => API.req('api/extras.php?endpoint=avaliacoes&action=listar'),
  },

  // -- Cupons --
  cupons: {
    listar:   ()    => API.req('api/extras.php?endpoint=cupons&action=listar'),
    validar:  (cod) => API.req('api/extras.php?endpoint=cupons&action=validar', 'POST', { codigo: cod }),
  },

  // -- Planos --
  planos: {
    listar: () => API.req('api/extras.php?endpoint=planos&action=listar'),
  },
};

// ============================================================
// 2. ESTADO GLOBAL
// ============================================================
const State = {
  cliente:       null,      // { id, nome, email, pontos }
  servicoSelecionado: null, // objeto serviço
  dataSelecionada: null,    // 'YYYY-MM-DD'
  horaSelecionada: null,    // 'HH:MM'
  formaPagamento: null,
  cupomAplicado:  null,
};

// ============================================================
// 3. UTILITÁRIOS
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
  return `${d} ${meses[parseInt(m)-1]} ${y}`;
}

function inicialNome(nome) {
  if (!nome) return '?';
  return nome.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
}

// ============================================================
// 4. NAVEGAÇÃO SPA
// ============================================================
function navegar(pagina) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

  const pg = document.getElementById('pg-' + pagina);
  if (pg) pg.classList.add('active');

  const nav = document.querySelector(`.nav-link[data-page="${pagina}"]`);
  if (nav) nav.classList.add('active');

  window.scrollTo({ top: 0, behavior: 'smooth' });

  // Carrega dados conforme a página
  const loaders = {
    home:        carregarAvaliacoesHome,
    servicos:    () => carregarServicos(),
    avaliacoes:  carregarAvaliacoes,
    cupons:      carregarCupons,
    planos:      carregarPlanos,
    perfil:      carregarPerfil,
  };
  if (loaders[pagina]) loaders[pagina]();
}

// ============================================================
// 5. SESSÃO / AUTH
// ============================================================
async function verificarSessao() {
  const { data } = await API.auth.sessao();
  if (data.logado) {
    State.cliente = data.cliente;
    renderUILogado();
  } else {
    renderUIDeslogado();
  }
}

function renderUILogado() {
  document.getElementById('btnLogin').style.display    = 'none';
  document.getElementById('btnRegistro').style.display = 'none';
  document.getElementById('navPerfil').style.display   = 'flex';
  document.getElementById('nomeNavbar').textContent    = State.cliente.nome.split(' ')[0];

  // Mostra link de perfil no nav
  document.getElementById('navPerfilLink').style.display = 'block';
}

function renderUIDeslogado() {
  document.getElementById('btnLogin').style.display    = 'inline-flex';
  document.getElementById('btnRegistro').style.display = 'inline-flex';
  document.getElementById('navPerfil').style.display   = 'none';
  document.getElementById('navPerfilLink').style.display = 'none';
}

// ============================================================
// 6. MODAIS DE AUTH
// ============================================================
function abrirModal(id) {
  document.getElementById(id).classList.add('open');
}
function fecharModal(id) {
  document.getElementById(id).classList.remove('open');
}

// --- LOGIN ---
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
    toast(`Bem-vinda, ${data.cliente.nome.split(' ')[0]}! ✨`, 'success');
  } else {
    err.textContent = data.erro;
    err.classList.add('show');
  }
}

// --- REGISTRO ---
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
    toast(`Conta criada com sucesso! Bem-vinda, ${nome.split(' ')[0]}! 🌟`, 'success');
  } else {
    err.textContent = data.erro;
    err.classList.add('show');
  }
}

// --- LOGOUT ---
async function logout() {
  await API.auth.logout();
  State.cliente = null;
  renderUIDeslogado();
  navegar('home');
  toast('Até logo! 👋');
}

// ============================================================
// 7. SERVIÇOS
// ============================================================
let todosServicos = [];

async function carregarServicos(categoria = '') {
  const grid = document.getElementById('servicosGrid');
  grid.innerHTML = '<div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>';

  const { ok, data } = await API.servicos.listar(categoria);
  if (!ok) { grid.innerHTML = '<div class="empty-state"><p>Erro ao carregar serviços.</p></div>'; return; }

  todosServicos = data.servicos;
  renderServicos(data.servicos);
}

function renderServicos(lista) {
  const grid = document.getElementById('servicosGrid');
  if (!lista.length) {
    grid.innerHTML = '<div class="empty-state"><div class="icon">🔍</div><p>Nenhum serviço nesta categoria.</p></div>';
    return;
  }

  const icones = { cabelo: '✂️', manicure: '💅', maquiagem: '💄', massagem: '💆', sobrancelha: '🪄' };

  grid.innerHTML = lista.map(s => `
    <div class="servico-card" data-id="${s.id}" onclick="iniciarAgendamento(${s.id})">
      <div class="servico-cat">${icones[s.categoria] || '✨'} ${s.categoria}</div>
      <div class="servico-nome">${s.nome}</div>
      <div class="servico-desc">${s.descricao || 'Serviço profissional realizado por especialistas.'}</div>
      <div class="servico-footer">
        <div class="servico-preco">R$ ${parseFloat(s.preco).toFixed(2).replace('.',',')}</div>
        <div class="servico-duracao">⏱ ${s.duracao_minutos} min</div>
      </div>
      <button class="btn btn-primary btn-sm" style="margin-top:1rem;width:100%">Agendar</button>
    </div>
  `).join('');
}

function filtrarCategoria(cat, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  carregarServicos(cat);
}

// ============================================================
// 8. AGENDAMENTO — FLUXO EM STEPS
// ============================================================

// State do agendamento
let agStep = 1;

function iniciarAgendamento(servicoId) {
  if (!State.cliente) {
    toast('Faça login para agendar.', 'error');
    abrirModal('modalLogin');
    return;
  }

  State.servicoSelecionado = todosServicos.find(s => s.id == servicoId) || null;

  if (!State.servicoSelecionado && servicoId) {
    // Se veio da home, precisa buscar o serviço
    abrirModal('modalAgendar');
    navegar('servicos');
    toast('Escolha um serviço abaixo para agendar.');
    return;
  }

  agStep = 1;
  abrirModal('modalAgendar');
  irStep(1);

  // Preenche resumo do serviço no modal
  if (State.servicoSelecionado) {
    document.getElementById('agServicoNome').textContent  = State.servicoSelecionado.nome;
    document.getElementById('agServicoPreco').textContent = 'R$ ' + parseFloat(State.servicoSelecionado.preco).toFixed(2).replace('.',',');
  }
}

function irStep(n) {
  agStep = n;
  document.querySelectorAll('.step-panel').forEach((p, i) => {
    p.classList.toggle('active', i + 1 === n);
  });
  document.querySelectorAll('.step-dot').forEach((d, i) => {
    d.classList.toggle('active', i + 1 === n);
    d.classList.toggle('done',   i + 1 < n);
  });

  if (n === 2) renderCalendario();
  if (n === 3) resumoAgendamento();
}

// Calendário
let calMes, calAno;

function renderCalendario() {
  const hoje = new Date();
  if (!calMes) calMes = hoje.getMonth();
  if (!calAno) calAno = hoje.getFullYear();

  const meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
  document.getElementById('calMesLabel').textContent = `${meses[calMes]} ${calAno}`;

  const primeiroDia = new Date(calAno, calMes, 1).getDay();
  const diasNoMes   = new Date(calAno, calMes + 1, 0).getDate();

  let html = '';
  ['D','S','T','Q','Q','S','S'].forEach(d => { html += `<div class="date-header">${d}</div>`; });

  for (let i = 0; i < primeiroDia; i++) html += `<div class="date-cell empty"></div>`;

  for (let d = 1; d <= diasNoMes; d++) {
    const dataStr = `${calAno}-${String(calMes+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const isPast  = new Date(dataStr) < new Date(hoje.toDateString());
    const isHoje  = dataStr === hoje.toISOString().slice(0,10);
    const isSel   = dataStr === State.dataSelecionada;

    let cls = 'date-cell';
    if (isPast) cls += ' past';
    else if (isSel) cls += ' selected';
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

  renderHorarios(true);
}

async function renderHorarios(buscarDoServidor = false) {
  const container = document.getElementById('horariosContainer');
  if (!State.dataSelecionada) {
    container.innerHTML = '<p style="color:var(--text-soft);font-size:.85rem">Selecione uma data acima.</p>';
    return;
  }

  container.innerHTML = '<p style="color:var(--text-soft);font-size:.85rem">⏳ Buscando horários...</p>';

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
  irStep(3);
}

// Step 3 — pagamento + cupom
function selecionarPagamento(forma, el) {
  State.formaPagamento = forma;
  document.querySelectorAll('.pagamento-opt').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
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
    toast('Cupom aplicado com sucesso!', 'success');
  } else {
    State.cupomAplicado = null;
    info.innerHTML = `<span style="color:var(--error);font-size:.82rem">${data.erro || 'Cupom inválido.'}</span>`;
  }
}

function resumoAgendamento() {
  document.getElementById('resumoServico').textContent  = State.servicoSelecionado?.nome || '-';
  document.getElementById('resumoData').textContent     = formatDate(State.dataSelecionada);
  document.getElementById('resumoHora').textContent     = State.horaSelecionada || '-';
  document.getElementById('resumoValor').textContent    = 'R$ ' + parseFloat(State.servicoSelecionado?.preco || 0).toFixed(2).replace('.',',');
}

async function confirmarAgendamento() {
  if (!State.formaPagamento) { toast('Selecione a forma de pagamento.', 'error'); return; }

  const btn = document.getElementById('btnConfirmar');
  setLoading(btn, true);

  const payload = {
    servico_id:      State.servicoSelecionado.id,
    data:            State.dataSelecionada,
    hora:            State.horaSelecionada,
    forma_pagamento: State.formaPagamento,
    cupom:           State.cupomAplicado || '',
  };

  const { ok, data } = await API.agendamentos.criar(payload);
  setLoading(btn, false);

  if (ok) {
    fecharModal('modalAgendar');
    irStep(4); // Confirmação
    document.getElementById('confirmacaoMensagem').innerHTML = `
      <p><strong>${data.servico}</strong></p>
      <p>📅 ${formatDate(State.dataSelecionada)} às ${State.horaSelecionada}</p>
      <p>💰 R$ ${data.valor}${data.desconto !== '0,00' ? ` <em>(desconto: R$ ${data.desconto})</em>` : ''}</p>
      <p>⭐ +${data.pontos_ganhos} pontos ganhos!</p>
      <p style="margin-top:.8rem;font-size:.85rem;color:var(--text-soft)">${data.mensagem}</p>
    `;
    abrirModal('modalConfirmacao');
    // Atualiza pontos na UI
    if (State.cliente) {
      State.cliente.pontos += data.pontos_ganhos;
    }
    toast('Horário confirmado com sucesso! ✨', 'success');
  } else {
    toast(data.erro || 'Erro ao agendar.', 'error');
  }
}

// ============================================================
// 9. AVALIAÇÕES
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

  const ag_id = document.getElementById('avAgendamentoId').value;
  const nota  = document.querySelector('input[name="nota"]:checked')?.value;
  const coment = document.getElementById('avComentario').value.trim();

  if (!ag_id) { toast('Informe o número do agendamento.', 'error'); return; }
  if (!nota)  { toast('Selecione sua satisfação.', 'error'); return; }

  const btn = document.getElementById('btnEnviarAvaliacao');
  setLoading(btn, true);
  const { ok, data } = await API.avaliacoes.enviar({ agendamento_id: ag_id, nota, comentario: coment });
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
// 10. CUPONS
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
    const desc = c.desconto_percent ? `${c.desconto_percent}% OFF` : `R$ ${parseFloat(c.desconto_valor).toFixed(2).replace('.',',')} OFF`;
    const val  = c.validade ? `Válido até ${new Date(c.validade + 'T00:00:00').toLocaleDateString('pt-BR')}` : 'Sem prazo';
    return `
      <div class="cupom-card">
        <div class="cupom-codigo">${c.codigo}</div>
        <div class="cupom-desconto">${desc}</div>
        <div class="cupom-info">${val}</div>
        ${c.pontos_necessarios > 0 ? `<div class="cupom-pontos">⭐ Requer ${c.pontos_necessarios} pontos</div>` : '<div class="cupom-pontos">🎁 Sem custo de pontos</div>'}
      </div>
    `;
  }).join('');
}

// ============================================================
// 11. PLANOS MENSAIS
// ============================================================
async function carregarPlanos() {
  const grid = document.getElementById('planosGrid');
  if (!grid) return;
  const { ok, data } = await API.planos.listar();
  if (!ok) return;

  const nomes = ['Prata', 'Ouro', 'Diamante'];
  const icones = ['🥈', '🥇', '💎'];

  grid.innerHTML = data.planos.map((p, i) => `
    <div class="plano-card ${i === 1 ? 'destaque' : ''}">
      ${i === 1 ? '<div class="plano-badge">Mais popular</div>' : ''}
      <div style="font-size:2.2rem;margin-bottom:.5rem">${icones[i] || '✨'}</div>
      <div class="plano-nome">${p.nome}</div>
      <div class="plano-preco">R$ ${parseFloat(p.preco).toFixed(2).replace('.',',')} <span>/ mês</span></div>
      <div class="plano-desc">${p.descricao || ''}<br><small>${p.servicos_inclusos || ''}</small></div>
      <button class="btn btn-${i === 1 ? 'primary' : 'outline'} btn-lg" style="width:100%"
        onclick="toast('Em breve! Entre em contato para assinar o plano.', 'info')">Assinar</button>
    </div>
  `).join('');
}

// ============================================================
// 12. PERFIL
// ============================================================
async function carregarPerfil() {
  if (!State.cliente) { navegar('home'); return; }

  document.getElementById('perfilAvatar').textContent   = inicialNome(State.cliente.nome);
  document.getElementById('perfilNome').textContent     = State.cliente.nome;
  document.getElementById('perfilEmail').textContent    = State.cliente.email;
  document.getElementById('perfilPontos').textContent   = `⭐ ${State.cliente.pontos} pontos`;

  const lista = document.getElementById('meusAgendamentos');
  lista.innerHTML = '<div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>';

  const { ok, data } = await API.agendamentos.meus();
  if (!ok || !data.agendamentos?.length) {
    lista.innerHTML = '<div class="empty-state"><div class="icon">📅</div><p>Nenhum agendamento ainda.<br><a href="#" onclick="navegar(\'servicos\')" style="color:var(--gold-dark)">Agende agora!</a></p></div>';
    return;
  }

  const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

  lista.innerHTML = data.agendamentos.map(a => {
    const dt  = new Date(a.data_hora);
    const dia = String(dt.getDate()).padStart(2,'0');
    const mes = meses[dt.getMonth()];
    const hora = dt.toLocaleTimeString('pt-BR', { hour:'2-digit', minute:'2-digit' });

    return `
      <div class="agendamento-item">
        <div class="agendamento-data"><div class="dia">${dia}</div><div class="mes">${mes}</div></div>
        <div class="agendamento-info">
          <div class="agendamento-servico">${a.servico}</div>
          <div class="agendamento-hora">🕐 ${hora} &nbsp;·&nbsp; 💳 ${a.forma_pagamento}</div>
        </div>
        <div>
          <span class="agendamento-status status-${a.status}">${a.status}</span>
          ${a.status === 'confirmado' ? `
            <div style="margin-top:.4rem">
              <button class="btn btn-danger btn-sm" onclick="cancelarAgendamento(${a.id})">Cancelar</button>
            </div>` : ''}
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
// 13. INIT
// ============================================================
document.addEventListener('DOMContentLoaded', async () => {
  // Remove loading
  setTimeout(() => {
    const lo = document.getElementById('loadingOverlay');
    if (lo) lo.style.display = 'none';
  }, 600);

  // Verifica sessão
  await verificarSessao();

  // Página inicial
  navegar('home');

  // Navegação
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

  // Fechar modais ao clicar fora
  document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', (e) => {
      if (e.target === o) o.classList.remove('open');
    });
  });

  // Formulários
  document.getElementById('formLogin')?.addEventListener('submit', submitLogin);
  document.getElementById('formRegistro')?.addEventListener('submit', submitRegistro);
  document.getElementById('formAvaliacao')?.addEventListener('submit', submitAvaliacao);
});
