<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gold Touch — Estética & Beleza</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

<!-- ===================== LOADING ===================== -->
<div id="loadingOverlay" class="loading-overlay">
  <div class="loading-logo">Gold Touch</div>
  <div class="loading-bar"><div class="loading-bar-inner"></div></div>
</div>

<!-- ===================== PROMO BANNER ===================== -->
<div class="promo-banner">✨ 20% OFF em agendamentos nas tardes! Use o cupom <strong>TARDE20</strong></div>

<!-- ===================== NAVBAR ===================== -->
<nav class="navbar">
  <a href="#" class="navbar-logo" onclick="navegar('home')">
    <div class="logo-icon">G</div>
    <div>
      <div class="logo-name">Gold Touch</div>
      <div class="logo-tag">Estética & Beleza</div>
    </div>
  </a>

  <div class="navbar-links">
    <button class="nav-link active" data-page="home">Home</button>
    <button class="nav-link" data-page="servicos">Serviços</button>
    <button class="nav-link" data-page="planos">Planos</button>
    <button class="nav-link" data-page="cupons">Cupons</button>
    <button class="nav-link" data-page="avaliacoes">Avaliações</button>
    <button class="nav-link" id="navPerfilLink" data-page="perfil" style="display:none">Minha Conta</button>
  </div>

  <div class="navbar-actions">
    <button id="btnLogin"    class="btn btn-ghost btn-sm"   onclick="abrirModal('modalLogin')">Entrar</button>
    <button id="btnRegistro" class="btn btn-primary btn-sm" onclick="abrirModal('modalRegistro')">Criar conta</button>
    <div id="navPerfil" style="display:none;align-items:center;gap:.5rem">
      <span style="font-size:.85rem;color:var(--text-soft)">Olá, <strong id="nomeNavbar"></strong></span>
      <button class="btn btn-ghost btn-sm" onclick="logout()">Sair</button>
    </div>
  </div>
</nav>

<!-- ===================== CONTEÚDO SPA ===================== -->
<main>

  <!-- ===== HOME ===== -->
  <div id="pg-home" class="page active">
    <div class="hero">
      <div class="hero-content">
        <div class="hero-eyebrow">Rio de Janeiro · Desde 2018</div>
        <h1 class="hero-title">
          Realce sua<br><em>beleza natural</em><br>com excelência
        </h1>
        <p class="hero-desc">
          Serviços de estética e beleza realizados por profissionais especializados. 
          Agende online, pague como preferir e ganhe pontos a cada visita.
        </p>
        <div class="hero-cta">
          <button class="btn btn-primary btn-lg" onclick="navegar('servicos')">Agendar agora</button>
          <button class="btn btn-outline btn-lg" onclick="navegar('planos')">Ver planos mensais</button>
        </div>
      </div>
      <div class="hero-visual">
        <div class="hero-card-grid">
          <div class="hero-card" onclick="carregarServicos('cabelo');navegar('servicos')">
            <div class="card-icon">✂️</div>
            <div class="card-name">Cabelo</div>
            <div class="card-price">a partir de R$ 80</div>
          </div>
          <div class="hero-card" onclick="carregarServicos('manicure');navegar('servicos')">
            <div class="card-icon">💅</div>
            <div class="card-name">Manicure</div>
            <div class="card-price">a partir de R$ 50</div>
          </div>
          <div class="hero-card" onclick="carregarServicos('maquiagem');navegar('servicos')">
            <div class="card-icon">💄</div>
            <div class="card-name">Maquiagem</div>
            <div class="card-price">a partir de R$ 150</div>
          </div>
          <div class="hero-card" onclick="carregarServicos('massagem');navegar('servicos')">
            <div class="card-icon">💆</div>
            <div class="card-name">Massagem</div>
            <div class="card-price">a partir de R$ 120</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Avaliações na home -->
    <div style="background:var(--creme-dark);padding:4rem 2rem;">
      <div class="section-header">
        <div class="section-eyebrow">Clientes satisfeitas</div>
        <h2 class="section-title">O que dizem sobre nós</h2>
        <div class="gold-divider"></div>
      </div>
      <div class="avaliacoes-grid" id="avaliacoesHomeGrid" style="max-width:1000px;margin:0 auto">
        <div class="empty-state"><div class="icon">⏳</div></div>
      </div>
      <div style="text-align:center;margin-top:2rem">
        <button class="btn btn-outline" onclick="navegar('avaliacoes')">Ver todas as avaliações</button>
      </div>
    </div>

    <!-- Stats -->
    <div class="section">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1.5rem;text-align:center">
        <div>
          <div style="font-family:var(--font-display);font-size:3rem;font-weight:600;color:var(--gold-dark)">500+</div>
          <div style="font-size:.85rem;color:var(--text-soft);text-transform:uppercase;letter-spacing:.1em">Clientes atendidas</div>
        </div>
        <div>
          <div style="font-family:var(--font-display);font-size:3rem;font-weight:600;color:var(--gold-dark)">8</div>
          <div style="font-size:.85rem;color:var(--text-soft);text-transform:uppercase;letter-spacing:.1em">Serviços disponíveis</div>
        </div>
        <div>
          <div style="font-family:var(--font-display);font-size:3rem;font-weight:600;color:var(--gold-dark)">5★</div>
          <div style="font-size:.85rem;color:var(--text-soft);text-transform:uppercase;letter-spacing:.1em">Avaliação média</div>
        </div>
        <div>
          <div style="font-family:var(--font-display);font-size:3rem;font-weight:600;color:var(--gold-dark)">6+</div>
          <div style="font-size:.85rem;color:var(--text-soft);text-transform:uppercase;letter-spacing:.1em">Anos de experiência</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== SERVIÇOS ===== -->
  <div id="pg-servicos" class="page">
    <div class="section">
      <div class="section-header">
        <div class="section-eyebrow">O que oferecemos</div>
        <h2 class="section-title">Nossos Serviços</h2>
        <div class="gold-divider"></div>
        <p class="section-sub">Escolha o serviço e agende online em menos de 2 minutos</p>
      </div>

      <div class="servicos-tabs">
        <button class="tab-btn active" onclick="filtrarCategoria('', this)">Todos</button>
        <button class="tab-btn" onclick="filtrarCategoria('cabelo', this)">✂️ Cabelo</button>
        <button class="tab-btn" onclick="filtrarCategoria('manicure', this)">💅 Manicure</button>
        <button class="tab-btn" onclick="filtrarCategoria('maquiagem', this)">💄 Maquiagem</button>
        <button class="tab-btn" onclick="filtrarCategoria('massagem', this)">💆 Massagem</button>
        <button class="tab-btn" onclick="filtrarCategoria('sobrancelha', this)">🪄 Sobrancelha</button>
      </div>

      <div class="servicos-grid" id="servicosGrid">
        <div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>
      </div>
    </div>
  </div>

  <!-- ===== PLANOS ===== -->
  <div id="pg-planos" class="page">
    <div class="section">
      <div class="section-header">
        <div class="section-eyebrow">Economia e exclusividade</div>
        <h2 class="section-title">Planos Mensais</h2>
        <div class="gold-divider"></div>
        <p class="section-sub">Assine um plano e tenha acesso a serviços com desconto todo mês</p>
      </div>
      <div class="planos-grid" id="planosGrid">
        <div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>
      </div>
    </div>
  </div>

  <!-- ===== CUPONS ===== -->
  <div id="pg-cupons" class="page">
    <div class="section">
      <div class="section-header">
        <div class="section-eyebrow">Economize mais</div>
        <h2 class="section-title">Cupons de Desconto</h2>
        <div class="gold-divider"></div>
        <p class="section-sub">Use no agendamento para garantir seu desconto</p>
      </div>
      <div class="cupons-grid" id="cuponsGrid">
        <div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>
      </div>
    </div>
  </div>

  <!-- ===== AVALIAÇÕES ===== -->
  <div id="pg-avaliacoes" class="page">
    <div class="section">
      <div class="section-header">
        <div class="section-eyebrow">Feedback real</div>
        <h2 class="section-title">Avaliações das Clientes</h2>
        <div class="gold-divider"></div>
      </div>

      <!-- Formulário de avaliação -->
      <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;max-width:560px;margin:0 auto 3rem">
        <h3 style="font-family:var(--font-display);font-size:1.4rem;margin-bottom:1.2rem">Deixe sua avaliação</h3>
        <form id="formAvaliacao">
          <div class="form-group">
            <label class="form-label">Número do agendamento</label>
            <input type="number" id="avAgendamentoId" class="form-input" placeholder="Ex: 42" min="1"/>
          </div>
          <div class="form-group">
            <label class="form-label">Nível de satisfação</label>
            <div style="display:flex;flex-direction:column;gap:.6rem;margin-top:.4rem">
              <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
                <input type="radio" name="nota" value="satisfeito"/> 😊 Satisfeita
              </label>
              <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
                <input type="radio" name="nota" value="pouco_satisfeito"/> 😐 Um pouco satisfeita
              </label>
              <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
                <input type="radio" name="nota" value="insatisfeito"/> 😞 Insatisfeita
              </label>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Comentário (opcional)</label>
            <textarea id="avComentario" class="form-input" rows="3" placeholder="Conte como foi sua experiência..."></textarea>
          </div>
          <button type="submit" id="btnEnviarAvaliacao" class="btn btn-primary" style="width:100%">Enviar avaliação</button>
        </form>
      </div>

      <div class="avaliacoes-grid" id="avaliacoesGrid">
        <div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>
      </div>
    </div>
  </div>

  <!-- ===== PERFIL ===== -->
  <div id="pg-perfil" class="page">
    <div class="section">
      <div class="perfil-header">
        <div class="perfil-avatar" id="perfilAvatar">?</div>
        <div>
          <div class="perfil-nome" id="perfilNome">—</div>
          <div style="font-size:.85rem;color:var(--text-soft)" id="perfilEmail">—</div>
          <div class="perfil-pontos" id="perfilPontos">⭐ 0 pontos</div>
        </div>
      </div>

      <h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:1rem">Meus Agendamentos</h3>
      <div class="agendamentos-lista" id="meusAgendamentos">
        <div class="empty-state"><div class="icon">⏳</div><p>Carregando...</p></div>
      </div>
    </div>
  </div>

</main>

<!-- ===================== FOOTER ===================== -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="logo-name">Gold Touch</div>
      <p>Estética e beleza profissional para realçar o melhor de você. Atendimento personalizado com produtos premium.</p>
    </div>
    <div class="footer-col">
      <h4>Serviços</h4>
      <ul>
        <li onclick="carregarServicos('cabelo');navegar('servicos')">Cabelo</li>
        <li onclick="carregarServicos('manicure');navegar('servicos')">Manicure</li>
        <li onclick="carregarServicos('maquiagem');navegar('servicos')">Maquiagem</li>
        <li onclick="carregarServicos('massagem');navegar('servicos')">Massagem</li>
        <li onclick="carregarServicos('sobrancelha');navegar('servicos')">Sobrancelha</li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Navegação</h4>
      <ul>
        <li onclick="navegar('planos')">Planos Mensais</li>
        <li onclick="navegar('cupons')">Cupons</li>
        <li onclick="navegar('avaliacoes')">Avaliações</li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Contato</h4>
      <ul>
        <li>📍 Rio de Janeiro, RJ</li>
        <li>📞 (21) 99999-0000</li>
        <li>📧 contato@goldtouch.com.br</li>
        <li>🕐 Seg–Sáb, 08h–20h</li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">© 2024 Gold Touch Estética & Beleza. Todos os direitos reservados.</div>
</footer>

<!-- ===================== TOAST CONTAINER ===================== -->
<div id="toastContainer" class="toast-container"></div>

<!-- ===================== MODAL LOGIN ===================== -->
<div id="modalLogin" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Entrar</div>
      <button class="modal-close" onclick="fecharModal('modalLogin')">✕</button>
    </div>
    <form id="formLogin">
      <div class="form-group">
        <label class="form-label">E-mail</label>
        <input id="loginEmail" type="email" class="form-input" placeholder="seu@email.com" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Senha</label>
        <input id="loginSenha" type="password" class="form-input" placeholder="Sua senha" required/>
      </div>
      <div class="form-error" id="loginErro"></div>
      <button type="submit" id="btnSubmitLogin" class="btn btn-primary btn-lg" style="width:100%;margin-top:.5rem">Entrar</button>
    </form>
    <div style="text-align:center;margin-top:1.2rem;font-size:.85rem;color:var(--text-soft)">
      Não tem conta? <a href="#" style="color:var(--gold-dark);font-weight:600"
        onclick="fecharModal('modalLogin');abrirModal('modalRegistro')">Cadastre-se grátis</a>
    </div>
  </div>
</div>

<!-- ===================== MODAL REGISTRO ===================== -->
<div id="modalRegistro" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Criar conta</div>
      <button class="modal-close" onclick="fecharModal('modalRegistro')">✕</button>
    </div>
    <form id="formRegistro">
      <div class="form-group">
        <label class="form-label">Nome completo</label>
        <input id="regNome" type="text" class="form-input" placeholder="Seu nome" required/>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">E-mail</label>
          <input id="regEmail" type="email" class="form-input" placeholder="seu@email.com" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Telefone</label>
          <input id="regTelefone" type="tel" class="form-input" placeholder="(21) 99999-0000"/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Senha</label>
        <input id="regSenha" type="password" class="form-input" placeholder="Mínimo 6 caracteres" required/>
      </div>
      <div class="form-error" id="registroErro"></div>
      <button type="submit" id="btnSubmitRegistro" class="btn btn-primary btn-lg" style="width:100%;margin-top:.5rem">Criar minha conta</button>
    </form>
    <div style="text-align:center;margin-top:1.2rem;font-size:.85rem;color:var(--text-soft)">
      Já tem conta? <a href="#" style="color:var(--gold-dark);font-weight:600"
        onclick="fecharModal('modalRegistro');abrirModal('modalLogin')">Entrar</a>
    </div>
  </div>
</div>

<!-- ===================== MODAL AGENDAMENTO ===================== -->
<div id="modalAgendar" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title">Agendar serviço</div>
        <div style="font-size:.82rem;color:var(--text-soft);margin-top:.2rem">
          <strong id="agServicoNome"></strong> · <span id="agServicoPreco"></span>
        </div>
      </div>
      <button class="modal-close" onclick="fecharModal('modalAgendar')">✕</button>
    </div>

    <!-- Indicador de steps -->
    <div class="steps-indicator">
      <div class="step-dot active">1</div>
      <div class="step-line"></div>
      <div class="step-dot">2</div>
      <div class="step-line"></div>
      <div class="step-dot">3</div>
    </div>

    <!-- Step 1: Data e Hora -->
    <div class="step-panel active">
      <p style="font-size:.82rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text-soft);margin-bottom:1rem">Escolha data e horário</p>

      <div class="cal-nav">
        <button class="cal-nav-btn" onclick="mudarMes(-1)">◀</button>
        <span class="cal-month" id="calMesLabel"></span>
        <button class="cal-nav-btn" onclick="mudarMes(1)">▶</button>
      </div>
      <div class="date-picker" id="calGrid"></div>

      <div style="margin-top:1rem">
        <p style="font-size:.82rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text-soft);margin-bottom:.6rem">Horários disponíveis</p>
        <div id="horariosContainer">
          <p style="color:var(--text-soft);font-size:.85rem">Selecione uma data acima.</p>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;margin-top:1.5rem">
        <button class="btn btn-primary" onclick="avancarStep2()">Próximo →</button>
      </div>
    </div>

    <!-- Step 2: Pagamento + Cupom -->
    <div class="step-panel">
      <p class="step-label">Forma de pagamento</p>

      <div class="pagamento-options">
        <label class="pagamento-opt" onclick="selecionarPagamento('credito', this)">
          <input type="radio" name="pgto" value="credito"/> <span>💳 Crédito</span>
        </label>
        <label class="pagamento-opt" onclick="selecionarPagamento('debito', this)">
          <input type="radio" name="pgto" value="debito"/> <span>💳 Débito</span>
        </label>
        <label class="pagamento-opt" onclick="selecionarPagamento('pix', this)">
          <input type="radio" name="pgto" value="pix"/> <span>⚡ Pix</span>
        </label>
        <label class="pagamento-opt" onclick="selecionarPagamento('dinheiro', this)">
          <input type="radio" name="pgto" value="dinheiro"/> <span>💵 Dinheiro</span>
        </label>
      </div>

      <!-- Campos: Cartão de Crédito -->
      <div id="campos-credito" class="campos-pagamento">
        <p class="campos-pagamento-titulo">Dados do cartão de crédito</p>
        <div class="form-group">
          <label class="form-label">Nome no cartão</label>
          <input type="text" id="cc-nome" class="form-input" placeholder="NOME SOBRENOME" maxlength="40"/>
        </div>
        <div class="form-group">
          <label class="form-label">Número do cartão</label>
          <input type="text" id="cc-numero" class="form-input" placeholder="0000 0000 0000 0000" maxlength="19" oninput="mascaraCartao(this)"/>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Validade</label>
            <input type="text" id="cc-validade" class="form-input" placeholder="MM/AA" maxlength="5" oninput="mascaraValidade(this)"/>
          </div>
          <div class="form-group">
            <label class="form-label">CVV</label>
            <input type="text" id="cc-cvv" class="form-input" placeholder="123" maxlength="4" oninput="this.value=this.value.replace(/\D/g,'')"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Parcelas</label>
          <select id="cc-parcelas" class="form-input">
            <option value="1">1x sem juros</option>
            <option value="2">2x sem juros</option>
            <option value="3">3x sem juros</option>
            <option value="6">6x com juros (1,99%)</option>
            <option value="12">12x com juros (1,99%)</option>
          </select>
        </div>
      </div>

      <!-- Campos: Cartão de Débito -->
      <div id="campos-debito" class="campos-pagamento">
        <p class="campos-pagamento-titulo">Dados do cartão de débito</p>
        <div class="form-group">
          <label class="form-label">Nome no cartão</label>
          <input type="text" id="db-nome" class="form-input" placeholder="NOME SOBRENOME" maxlength="40"/>
        </div>
        <div class="form-group">
          <label class="form-label">Número do cartão</label>
          <input type="text" id="db-numero" class="form-input" placeholder="0000 0000 0000 0000" maxlength="19" oninput="mascaraCartao(this)"/>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Validade</label>
            <input type="text" id="db-validade" class="form-input" placeholder="MM/AA" maxlength="5" oninput="mascaraValidade(this)"/>
          </div>
          <div class="form-group">
            <label class="form-label">CVV</label>
            <input type="text" id="db-cvv" class="form-input" placeholder="123" maxlength="4" oninput="this.value=this.value.replace(/\D/g,'')"/>
          </div>
        </div>
      </div>

      <!-- Campos: Pix -->
      <div id="campos-pix" class="campos-pagamento">
        <div class="pix-box">
          <div class="pix-qr">
            <div class="pix-qr-inner">
              <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
                <rect width="80" height="80" fill="#F5F0E8"/>
                <!-- QR simulado -->
                <rect x="8"  y="8"  width="24" height="24" rx="2" fill="#1A1A2E"/>
                <rect x="11" y="11" width="18" height="18" rx="1" fill="#F5F0E8"/>
                <rect x="14" y="14" width="12" height="12" rx="1" fill="#1A1A2E"/>
                <rect x="48" y="8"  width="24" height="24" rx="2" fill="#1A1A2E"/>
                <rect x="51" y="11" width="18" height="18" rx="1" fill="#F5F0E8"/>
                <rect x="54" y="14" width="12" height="12" rx="1" fill="#1A1A2E"/>
                <rect x="8"  y="48" width="24" height="24" rx="2" fill="#1A1A2E"/>
                <rect x="11" y="51" width="18" height="18" rx="1" fill="#F5F0E8"/>
                <rect x="14" y="54" width="12" height="12" rx="1" fill="#1A1A2E"/>
                <rect x="36" y="8"  width="6"  height="6"  fill="#C9A84C"/>
                <rect x="36" y="18" width="6"  height="6"  fill="#C9A84C"/>
                <rect x="36" y="28" width="6"  height="6"  fill="#1A1A2E"/>
                <rect x="8"  y="36" width="6"  height="6"  fill="#1A1A2E"/>
                <rect x="18" y="36" width="6"  height="6"  fill="#C9A84C"/>
                <rect x="28" y="36" width="6"  height="6"  fill="#1A1A2E"/>
                <rect x="48" y="36" width="6"  height="6"  fill="#C9A84C"/>
                <rect x="58" y="36" width="6"  height="6"  fill="#1A1A2E"/>
                <rect x="68" y="36" width="6"  height="6"  fill="#C9A84C"/>
                <rect x="36" y="48" width="6"  height="6"  fill="#1A1A2E"/>
                <rect x="48" y="48" width="6"  height="6"  fill="#C9A84C"/>
                <rect x="58" y="58" width="6"  height="6"  fill="#1A1A2E"/>
                <rect x="68" y="48" width="6"  height="6"  fill="#C9A84C"/>
                <rect x="36" y="68" width="6"  height="6"  fill="#C9A84C"/>
                <rect x="48" y="68" width="6"  height="6"  fill="#1A1A2E"/>
                <rect x="68" y="68" width="6"  height="6"  fill="#1A1A2E"/>
              </svg>
            </div>
            <p class="pix-qr-label">QR Code simulado</p>
          </div>
          <div class="pix-info">
            <p class="pix-titulo">Pague via Pix</p>
            <p class="pix-desc">Escaneie o QR Code ao lado ou copie a chave abaixo. O pagamento é confirmado na hora.</p>
            <div class="pix-chave-box">
              <span class="pix-chave-label">Chave Pix:</span>
              <span class="pix-chave-valor">contato@goldtouch.com.br</span>
              <button class="btn btn-ghost btn-sm" onclick="copiarChavePix()">Copiar</button>
            </div>
            <label class="pix-confirmar-label">
              <input type="checkbox" id="pix-confirmado"/>
              Já realizei o pagamento via Pix
            </label>
          </div>
        </div>
      </div>

      <!-- Campos: Dinheiro -->
      <div id="campos-dinheiro" class="campos-pagamento">
        <div class="dinheiro-box">
          <div class="dinheiro-icon">💵</div>
          <p class="dinheiro-titulo">Pagamento em dinheiro</p>
          <p class="dinheiro-desc">O pagamento será realizado presencialmente no salão, no dia do atendimento. Traga o valor exato se possível.</p>
          <label class="pix-confirmar-label">
            <input type="checkbox" id="dinheiro-confirmado"/>
            Estou ciente que pagarei no local
          </label>
        </div>
      </div>

      <!-- Cupom -->
      <div class="cupom-section">
        <label class="form-label">Cupom de desconto</label>
        <div class="cupom-inline">
          <input type="text" id="inputCupom" class="form-input form-input--upper" placeholder="Ex: TARDE20"/>
          <button type="button" id="btnValidarCupom" class="btn btn-outline" onclick="validarCupom()">Aplicar</button>
        </div>
        <div id="cupomInfo"></div>
      </div>

      <div class="step-actions step-actions--between">
        <button class="btn btn-ghost" onclick="irStep(1)">← Voltar</button>
        <button class="btn btn-primary" onclick="avancarStep3()">Revisar →</button>
      </div>
    </div>

    <!-- Step 3: Resumo + Confirmar -->
    <div class="step-panel">
      <p style="font-size:.82rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text-soft);margin-bottom:1rem">Confirmar agendamento</p>

      <div style="background:var(--creme-dark);border-radius:var(--radius);padding:1.2rem;margin-bottom:1.2rem">
        <div style="display:grid;gap:.5rem">
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--text-soft);font-size:.88rem">Serviço</span>
            <strong id="resumoServico" style="font-size:.88rem"></strong>
          </div>
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--text-soft);font-size:.88rem">Data</span>
            <strong id="resumoData" style="font-size:.88rem"></strong>
          </div>
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--text-soft);font-size:.88rem">Horário</span>
            <strong id="resumoHora" style="font-size:.88rem"></strong>
          </div>
          <hr style="border:none;border-top:1px solid var(--border);margin:.4rem 0"/>
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--text-soft);font-size:.9rem">Total</span>
            <strong id="resumoValor" style="font-size:1.05rem;color:var(--gold-dark)"></strong>
          </div>
        </div>
      </div>

      <p style="font-size:.8rem;color:var(--text-soft);margin-bottom:1rem">
        ℹ️ Limite de espera: <strong>15 minutos</strong>. Cancelamentos com até 2h de antecedência.
      </p>

      <div style="display:flex;justify-content:space-between">
        <button class="btn btn-ghost" onclick="irStep(2)">← Voltar</button>
        <button class="btn btn-primary btn-lg" id="btnConfirmar" onclick="confirmarAgendamento()">✓ Confirmar</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== MODAL CONFIRMAÇÃO ===================== -->
<div id="modalConfirmacao" class="modal-overlay">
  <div class="modal" style="text-align:center">
    <div style="font-size:3.5rem;margin-bottom:.8rem">🎉</div>
    <h2 style="font-family:var(--font-display);font-size:1.8rem;margin-bottom:.8rem;color:var(--success)">Horário Confirmado!</h2>
    <div id="confirmacaoMensagem" style="color:var(--text-soft);font-size:.9rem;line-height:1.8;margin-bottom:1.5rem"></div>
    <div style="display:flex;gap:.8rem;justify-content:center">
      <button class="btn btn-primary" onclick="fecharModal('modalConfirmacao');navegar('perfil')">Ver meus agendamentos</button>
      <button class="btn btn-ghost" onclick="fecharModal('modalConfirmacao')">Fechar</button>
    </div>
  </div>
</div>

<script src="js/app.js"></script>
</body>
</html>
