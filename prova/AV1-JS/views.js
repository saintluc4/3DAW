/* ============================================================
   views.js — funções que geram o HTML de cada página
   Cada função substitui um arquivo .php:
     dashboard()       → index.php
     listar()          → listar.php
     criar_multipla()  → criar_multipla.php
     criar_texto()     → criar_texto.php
     visualizar()      → visualizar.php
     alterar_multipla()→ alterar_multipla.php
     alterar_texto()   → alterar_texto.php
     excluir()         → excluir.php
     usuarios()        → usuarios.php
============================================================ */
const Views = {

  /* ── index.php ─────────────────────────────────────────── */
  dashboard() {
    const perguntas = DB.lerPerguntas();
    const total     = perguntas.length;
    const multiplas = perguntas.filter(p => p.tipo === 'multipla').length;
    const textos    = perguntas.filter(p => p.tipo === 'texto').length;
    const recentes  = [...perguntas].reverse().slice(0, 5);

    Layout.setHeader('Dashboard', 'Bem-vindo ao Sistema de Treinamento Corporativo WaterFalls');

    const rows = recentes.length === 0
      ? `<p style="color:var(--muted);font-size:.9rem">
           Nenhuma pergunta cadastrada ainda.
           <span class="link-inline" onclick="navigate('criar_multipla')">Crie a primeira!</span>
         </p>`
      : `<div class="table-wrap"><table>
           <thead><tr><th>Enunciado</th><th>Tipo</th><th>Criado em</th><th>Ações</th></tr></thead>
           <tbody>
             ${recentes.map(p => `
               <tr>
                 <td>${Utils.esc(Utils.trunc(p.enunciado, 80))}</td>
                 <td>${Utils.badgeTipo(p.tipo)}</td>
                 <td style="color:var(--muted);font-size:.83rem">${Utils.esc(p.criado_em)}</td>
                 <td><div class="action-row">
                   <button class="btn btn-info btn-sm" onclick="navigate('visualizar',{id:'${p.id}'})">Ver</button>
                   <button class="btn btn-secondary btn-sm" onclick="navigate('${p.tipo === 'multipla' ? 'alterar_multipla' : 'alterar_texto'}',{id:'${p.id}'})">Editar</button>
                 </div></td>
               </tr>`).join('')}
           </tbody>
         </table></div>
         <div style="margin-top:16px">
           <button class="btn btn-secondary" onclick="navigate('listar')">Ver todas as perguntas →</button>
         </div>`;

    return `
      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-number" style="color:var(--accent)">${total}</span>
          <span class="stat-desc">Total de Perguntas</span>
        </div>
        <div class="stat-card">
          <span class="stat-number" style="color:var(--accent)">${multiplas}</span>
          <span class="stat-desc">Múltipla Escolha</span>
        </div>
        <div class="stat-card">
          <span class="stat-number" style="color:var(--accent2)">${textos}</span>
          <span class="stat-desc">Dissertativas</span>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
        <div onclick="navigate('criar_multipla')" style="cursor:pointer">
          <div class="card hover-accent" style="border-color:transparent">
            <div class="card-title">Nova Pergunta — Múltipla Escolha</div>
            <p style="color:var(--muted);font-size:.9rem">Crie perguntas com até 5 alternativas e defina a resposta correta.</p>
          </div>
        </div>
        <div onclick="navigate('criar_texto')" style="cursor:pointer">
          <div class="card hover-accent2" style="border-color:transparent">
            <div class="card-title" style="color:var(--accent2)">Nova Pergunta — Dissertativa</div>
            <p style="color:var(--muted);font-size:.9rem">Crie perguntas abertas com resposta modelo para referência do gestor.</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Perguntas Recentes</div>
        ${rows}
      </div>`;
  },

  /* ── listar.php ─────────────────────────────────────────── */
  listar(params = {}) {
    const filtro    = params.filtro || 'todas';
    const busca     = params.busca  || '';
    const perguntas = DB.lerPerguntas();
    const qtdM      = perguntas.filter(p => p.tipo === 'multipla').length;
    const qtdT      = perguntas.filter(p => p.tipo === 'texto').length;

    let lista = filtro === 'multipla' ? perguntas.filter(p => p.tipo === 'multipla')
              : filtro === 'texto'    ? perguntas.filter(p => p.tipo === 'texto')
              : perguntas;

    if (busca) lista = lista.filter(p =>
      p.enunciado.toLowerCase().includes(busca.toLowerCase())
    );

    Layout.setHeader('Banco de <span>Perguntas</span>', 'Gerencie todas as perguntas e respostas do sistema');

    const alertas = Utils.renderMsgs(params.msgs || []);

    const rows = lista.length === 0
      ? `<div class="empty-state">
           <p>Nenhuma pergunta encontrada.</p>
           <button class="btn btn-primary" onclick="navigate('criar_multipla')">+ Criar pergunta</button>
         </div>`
      : `<div class="table-wrap"><table>
           <thead>
             <tr><th>#</th><th>Enunciado</th><th>Tipo</th><th>Alt.</th><th>Criado em</th><th>Ações</th></tr>
           </thead>
           <tbody>
             ${lista.map((p, i) => `
               <tr>
                 <td style="color:var(--muted);font-size:.8rem">${i + 1}</td>
                 <td style="max-width:360px">
                   <span class="link-table" onclick="navigate('visualizar',{id:'${p.id}'})">${Utils.esc(Utils.trunc(p.enunciado, 90))}</span>
                 </td>
                 <td>${Utils.badgeTipo(p.tipo)}</td>
                 <td style="color:var(--muted)">${p.tipo === 'multipla' ? p.alternativas.length : '—'}</td>
                 <td style="color:var(--muted);font-size:.82rem">${Utils.esc(p.criado_em.slice(0, 10))}</td>
                 <td><div class="action-row">
                   <button class="btn btn-info btn-sm" onclick="navigate('visualizar',{id:'${p.id}'})">Ver</button>
                   <button class="btn btn-secondary btn-sm" onclick="navigate('${p.tipo === 'multipla' ? 'alterar_multipla' : 'alterar_texto'}',{id:'${p.id}'})">Editar</button>
                   <button class="btn btn-danger btn-sm" onclick="pedirExclusao('${p.id}')">Excluir</button>
                 </div></td>
               </tr>`).join('')}
           </tbody>
         </table></div>`;

    return `
      ${alertas}
      <div class="filter-tabs">
        <button class="btn btn-sm ${filtro === 'todas'    ? 'btn-primary' : 'btn-secondary'}" onclick="navigate('listar',{filtro:'todas'})">Todas (${perguntas.length})</button>
        <button class="btn btn-sm ${filtro === 'multipla' ? 'btn-primary' : 'btn-secondary'}" onclick="navigate('listar',{filtro:'multipla'})">Múltipla (${qtdM})</button>
        <button class="btn btn-sm ${filtro === 'texto'    ? 'btn-primary' : 'btn-secondary'}" onclick="navigate('listar',{filtro:'texto'})">Dissertativa (${qtdT})</button>
        <div style="flex:1"></div>
        <button class="btn btn-primary btn-sm" onclick="navigate('criar_multipla')">+ Múltipla Escolha</button>
        <button class="btn btn-info    btn-sm" onclick="navigate('criar_texto')">+ Dissertativa</button>
      </div>

      <div class="search-bar">
        <input type="text" id="busca-input" placeholder="Buscar por enunciado…" value="${Utils.esc(busca)}">
      </div>

      <div class="card">${rows}</div>`;
  },

  /* ── criar_multipla.php ─────────────────────────────────── */
  criar_multipla(params = {}) {
    const dados = params.dados || { enunciado: '', alternativas: ['', '', '', '', ''], correta: 0 };

    Layout.setHeader(
      'Nova Pergunta — <span>Múltipla Escolha</span>',
      'Crie uma pergunta com alternativas para os desafios corporativos'
    );

    return `
      ${Utils.renderMsgs(params.msgs || [])}
      <div class="card">
        <div class="card-title">Dados da Pergunta</div>
        <form id="form-cm">
          <div class="form-group">
            <label>Enunciado da Pergunta</label>
            <textarea id="enunciado" rows="4" placeholder="Digite a situação ou pergunta para o gestor…">${Utils.esc(dados.enunciado)}</textarea>
          </div>
          <div class="form-group">
            <label>
              Alternativas
              <span style="color:var(--muted);text-transform:none;letter-spacing:0;font-size:.78rem">(mín. 2, máx. 5 — marque a correta)</span>
            </label>
            ${LETRAS.map((l, i) => `
              <div class="alt-row">
                <div class="alt-label">${l}</div>
                <input type="text" name="alt${i}" placeholder="Alternativa ${l} (deixe vazio para omitir)" value="${Utils.esc(dados.alternativas[i] || '')}">
                <label>
                  <input type="radio" name="correta" value="${i}" ${dados.correta == i ? 'checked' : ''}>
                  Correta
                </label>
              </div>`).join('')}
          </div>
          <div class="action-row">
            <button type="submit" class="btn btn-primary">Salvar Pergunta</button>
            <button type="button" class="btn btn-secondary" onclick="navigate('dashboard')">Cancelar</button>
          </div>
        </form>
      </div>`;
  },

  /* ── criar_texto.php ────────────────────────────────────── */
  criar_texto(params = {}) {
    const dados = params.dados || { enunciado: '', resposta_modelo: '' };

    Layout.setHeader(
      'Nova Pergunta — <span>Dissertativa</span>',
      'Crie uma pergunta aberta com resposta modelo de referência'
    );

    return `
      ${Utils.renderMsgs(params.msgs || [])}
      <div class="card">
        <div class="card-title">Dados da Pergunta</div>
        <form id="form-ct">
          <div class="form-group">
            <label>Enunciado da Pergunta</label>
            <textarea id="enunciado" rows="4" placeholder="Descreva a situação ou pergunta que o gestor deverá responder…">${Utils.esc(dados.enunciado)}</textarea>
          </div>
          <div class="form-group">
            <label>Resposta Modelo (Gabarito)</label>
            <textarea id="resposta_modelo" rows="5" placeholder="Descreva a resposta ideal esperada do gestor.">${Utils.esc(dados.resposta_modelo)}</textarea>
          </div>
          <div class="action-row">
            <button type="submit" class="btn btn-primary">Salvar Pergunta</button>
            <button type="button" class="btn btn-secondary" onclick="navigate('dashboard')">Cancelar</button>
          </div>
        </form>
      </div>`;
  },

  /* ── visualizar.php ─────────────────────────────────────── */
  visualizar(params = {}) {
    const id = params.id || '';
    const p  = DB.buscarPergunta(id);

    if (!p) {
      Layout.setHeader('Pergunta não encontrada', '');
      return `<div class="alert alert-error">
        Pergunta não encontrada.
        <span class="link-inline" onclick="navigate('listar')">Voltar à lista</span>
      </div>`;
    }

    Layout.setHeader(
      'Detalhe da <span>Pergunta</span>',
      (p.tipo === 'multipla' ? 'Múltipla Escolha' : 'Dissertativa') + ' — criada em ' + p.criado_em
    );

    const editPage = p.tipo === 'multipla' ? 'alterar_multipla' : 'alterar_texto';

    const conteudo = p.tipo === 'multipla'
      ? `<div class="card-title">Alternativas</div>
         ${p.alternativas.map((alt, i) => `
           <div class="alt-item ${i == p.correta ? 'correta' : ''}">
             <div class="alt-label">${LETRAS[i]}</div>
             <span style="flex:1">${Utils.esc(alt)}</span>
             ${i == p.correta ? '<span class="correta-badge">✓ Correta</span>' : ''}
           </div>`).join('')}`
      : `<div class="card-title">Resposta Modelo (Gabarito)</div>
         <div class="gabarito-box">${Utils.nl2br(p.resposta_modelo)}</div>`;

    return `
      ${Utils.renderMsgs(params.msgs || [])}
      <div class="action-row" style="margin-bottom:24px">
        <button class="btn btn-secondary" onclick="navigate('listar')">← Voltar</button>
        <button class="btn btn-primary"   onclick="navigate('${editPage}',{id:'${id}'})">Editar</button>
        <button class="btn btn-danger"    onclick="pedirExclusao('${id}')">Excluir</button>
      </div>

      <div class="card">
        <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:28px">
          <div style="flex:1">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
              ${Utils.badgeTipo(p.tipo)}
              <span style="color:var(--muted);font-size:.8rem">ID: ${Utils.esc(id.slice(0, 20))}…</span>
              ${p.atualizado_em ? `<span style="color:var(--muted);font-size:.8rem">· Editado em ${Utils.esc(p.atualizado_em)}</span>` : ''}
            </div>
            <div class="card-title" style="margin-bottom:0;font-size:1.15rem">Enunciado</div>
          </div>
        </div>

        <div class="enunciado-box">${Utils.nl2br(p.enunciado)}</div>
        ${conteudo}
      </div>`;
  },

  /* ── alterar_multipla.php ───────────────────────────────── */
  alterar_multipla(params = {}) {
    const id = params.id || '';
    const p  = params.pergunta || DB.buscarPergunta(id);

    if (!p) { Layout.setHeader('Não encontrada', ''); return `<div class="alert alert-error">Pergunta não encontrada.</div>`; }
    if (p.tipo !== 'multipla') { navigate('alterar_texto', { id }); return ''; }

    const dados = params.dados || { enunciado: p.enunciado, alternativas: [...p.alternativas], correta: p.correta };
    const alts  = [...dados.alternativas];
    while (alts.length < 5) alts.push('');

    Layout.setHeader('Editar Pergunta — <span>Múltipla Escolha</span>', Utils.trunc(p.enunciado, 80) + '…');

    return `
      ${Utils.renderMsgs(params.msgs || [])}
      <div class="card">
        <div class="card-title">Editar Pergunta</div>
        <form id="form-am">
          <input type="hidden" id="edit-id" value="${Utils.esc(id)}">
          <div class="form-group">
            <label>Enunciado da Pergunta</label>
            <textarea id="enunciado" rows="4">${Utils.esc(dados.enunciado)}</textarea>
          </div>
          <div class="form-group">
            <label>
              Alternativas
              <span style="color:var(--muted);text-transform:none;letter-spacing:0;font-size:.78rem">(marque a correta)</span>
            </label>
            ${LETRAS.map((l, i) => `
              <div class="alt-row">
                <div class="alt-label">${l}</div>
                <input type="text" name="alt${i}" placeholder="Alternativa ${l} (deixe vazio para omitir)" value="${Utils.esc(alts[i] || '')}">
                <label>
                  <input type="radio" name="correta" value="${i}" ${dados.correta == i ? 'checked' : ''}>
                  Correta
                </label>
              </div>`).join('')}
          </div>
          <div class="action-row">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <button type="button" class="btn btn-secondary" onclick="navigate('visualizar',{id:'${Utils.esc(id)}'})">Cancelar</button>
            <button type="button" class="btn btn-danger" style="margin-left:auto" onclick="pedirExclusao('${Utils.esc(id)}')">Excluir</button>
          </div>
        </form>
      </div>`;
  },

  /* ── alterar_texto.php ──────────────────────────────────── */
  alterar_texto(params = {}) {
    const id = params.id || '';
    const p  = params.pergunta || DB.buscarPergunta(id);

    if (!p) { Layout.setHeader('Não encontrada', ''); return `<div class="alert alert-error">Pergunta não encontrada.</div>`; }
    if (p.tipo !== 'texto') { navigate('alterar_multipla', { id }); return ''; }

    const dados = params.dados || { enunciado: p.enunciado, resposta_modelo: p.resposta_modelo };

    Layout.setHeader('Editar Pergunta — <span>Dissertativa</span>', Utils.trunc(p.enunciado, 80) + '…');

    return `
      ${Utils.renderMsgs(params.msgs || [])}
      <div class="card">
        <div class="card-title">Editar Pergunta</div>
        <form id="form-at">
          <input type="hidden" id="edit-id" value="${Utils.esc(id)}">
          <div class="form-group">
            <label>Enunciado da Pergunta</label>
            <textarea id="enunciado" rows="4">${Utils.esc(dados.enunciado)}</textarea>
          </div>
          <div class="form-group">
            <label>Resposta Modelo (Gabarito)</label>
            <textarea id="resposta_modelo" rows="5">${Utils.esc(dados.resposta_modelo)}</textarea>
          </div>
          <div class="action-row">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <button type="button" class="btn btn-secondary" onclick="navigate('visualizar',{id:'${Utils.esc(id)}'})">Cancelar</button>
            <button type="button" class="btn btn-danger" style="margin-left:auto" onclick="pedirExclusao('${Utils.esc(id)}')">Excluir</button>
          </div>
        </form>
      </div>`;
  },

  /* ── excluir.php ────────────────────────────────────────── */
  excluir(params = {}) {
    const id = params.id || '';
    const p  = DB.buscarPergunta(id);
    if (!p) { navigate('listar'); return ''; }

    Layout.setHeader('Excluir <span>Pergunta</span>', 'Esta ação não pode ser desfeita');

    return `
      <div class="alert alert-warning">⚠️ Você está prestes a excluir permanentemente esta pergunta e todas as suas respostas.</div>
      <div class="card">
        <div class="card-title">Confirmar Exclusão</div>
        <div style="margin-bottom:20px">
          <div class="detail-label">Tipo</div>
          <div class="detail-value">${Utils.badgeTipo(p.tipo)}</div>
          <div class="detail-label">Enunciado</div>
          <div style="background:var(--surface2);border:1px solid var(--danger);border-radius:8px;padding:16px;line-height:1.6;margin-bottom:20px">
            ${Utils.nl2br(p.enunciado)}
          </div>
          <div class="detail-label">
            ${p.tipo === 'multipla' ? p.alternativas.length + ' alternativas serão removidas' : 'A resposta modelo também será removida'}
          </div>
        </div>
        <div class="action-row">
          <button class="btn btn-danger"    id="btn-confirm-del">Confirmar Exclusão</button>
          <button class="btn btn-secondary" onclick="navigate('visualizar',{id:'${Utils.esc(id)}'})">Cancelar</button>
        </div>
      </div>`;
  },

  /* ── usuarios.php ───────────────────────────────────────── */
  usuarios(params = {}) {
    const dados  = params.dados || { nome: '', email: '' };
    const lista  = DB.lerUsuarios();

    Layout.setHeader('Gerenciar <span>Usuários</span>', 'Cadastro e listagem de usuários do sistema');

    const rows = lista.length === 0
      ? `<p style="color:var(--muted);font-size:.9rem">Nenhum usuário cadastrado ainda.</p>`
      : `<div class="table-wrap"><table>
           <thead><tr><th>Nome</th><th>E-mail</th><th>Cadastro</th></tr></thead>
           <tbody>
             ${lista.map(u => `
               <tr>
                 <td style="font-weight:500">${Utils.esc(u.nome)}</td>
                 <td style="color:var(--muted);font-size:.88rem">${Utils.esc(u.email)}</td>
                 <td style="color:var(--muted);font-size:.82rem">${Utils.esc(u.criado_em.slice(0, 10))}</td>
               </tr>`).join('')}
           </tbody>
         </table></div>`;

    return `
      ${Utils.renderMsgs(params.msgs || [])}
      <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;align-items:start">
        <div class="card">
          <div class="card-title">Novo Usuário</div>
          <form id="form-user">
            <div class="form-group">
              <label>Nome Completo</label>
              <input type="text" id="u-nome" placeholder="Ex: João da Silva" value="${Utils.esc(dados.nome)}">
            </div>
            <div class="form-group">
              <label>E-mail</label>
              <input type="email" id="u-email" placeholder="joao@empresa.com" value="${Utils.esc(dados.email)}">
            </div>
            <div class="form-group">
              <label>Senha</label>
              <input type="password" id="u-senha" placeholder="Mínimo 6 caracteres">
            </div>
            <div class="form-group">
              <label>Confirmar Senha</label>
              <input type="password" id="u-conf" placeholder="Repita a senha">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Cadastrar Usuário</button>
          </form>
        </div>
        <div class="card">
          <div class="card-title">Usuários Cadastrados (${lista.length})</div>
          ${rows}
        </div>
      </div>`;
  }
};
