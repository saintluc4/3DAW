<?php
function render_header($titulo_pagina = 'Dashboard', $subtitulo = '') {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= strip_tags($titulo_pagina) ?> — WaterFalls Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:         #f5f3ef;
            --surface:    #ffffff;
            --surface2:   #f9f8f6;
            --border:     #e5e1da;
            --border2:    #d4cfc6;
            --accent:     #2a6b4f;
            --accent-lt:  #e8f4ee;
            --accent2:    #c47c2b;
            --danger:     #c0392b;
            --danger-lt:  #fdf0ee;
            --warning:    #b8850a;
            --warning-lt: #fef9ec;
            --info:       #1a5f82;
            --info-lt:    #eaf4fb;
            --text:       #1c1a17;
            --text2:      #4a4540;
            --muted:      #8c8680;
            --radius:     8px;
            --shadow:     0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
            --shadow-md:  0 2px 8px rgba(0,0,0,.08), 0 8px 24px rgba(0,0,0,.06);
        }

        html { font-size: 16px; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Epilogue', sans-serif;
            font-weight: 400;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        nav {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 100;
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto;
            display: flex; align-items: center; gap: 4px;
            padding: 0 32px; height: 58px;
        }
        .nav-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700; font-size: 1.05rem;
            color: var(--accent);
            margin-right: 24px;
            text-decoration: none;
            display: flex; align-items: center; gap: 10px;
        }
        .nav-brand-dot {
            width: 7px; height: 7px;
            background: var(--accent2);
            border-radius: 50%; flex-shrink: 0;
        }
        .nav-link {
            color: var(--muted); text-decoration: none;
            font-size: .85rem; font-weight: 500;
            padding: 6px 12px; border-radius: 6px;
            transition: .15s ease; white-space: nowrap;
        }
        .nav-link:hover { color: var(--text); background: var(--surface2); }
        .nav-link.active { color: var(--accent); background: var(--accent-lt); }
        .nav-sep { flex: 1; }
        .nav-divider { width: 1px; height: 20px; background: var(--border); margin: 0 6px; }

        .page-header {
            max-width: 1200px; margin: 0 auto;
            padding: 40px 32px 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 32px;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 700; font-size: clamp(1.6rem, 3.5vw, 2.2rem);
            line-height: 1.15; color: var(--text); letter-spacing: -.3px;
        }
        .page-header h1 span { color: var(--accent); }
        .page-header p { color: var(--muted); font-size: .9rem; margin-top: 6px; font-weight: 300; }

        main {
            max-width: 1200px; margin: 0 auto;
            padding: 0 32px 64px; flex: 1; width: 100%;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            box-shadow: var(--shadow);
        }
        .card + .card { margin-top: 16px; }
        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: .95rem; font-weight: 600;
            color: var(--text); margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
            padding-bottom: 14px; border-bottom: 1px solid var(--border);
        }
        .card-title::before {
            content: '';
            display: block; width: 3px; height: 16px;
            background: var(--accent); border-radius: 2px; flex-shrink: 0;
        }

        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: .75rem; font-weight: 600;
            color: var(--text2); letter-spacing: .07em;
            text-transform: uppercase; margin-bottom: 7px;
        }
        input[type=text], input[type=email], input[type=password], textarea, select {
            width: 100%; background: var(--surface);
            border: 1px solid var(--border2);
            color: var(--text); font-family: 'Epilogue', sans-serif;
            font-size: .93rem; padding: 10px 13px;
            border-radius: 6px; outline: none; transition: .15s ease;
        }
        input:focus, textarea:focus, select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(42,107,79,.1);
        }
        input:hover:not(:focus), textarea:hover:not(:focus) { border-color: var(--text2); }
        textarea { resize: vertical; min-height: 100px; line-height: 1.65; }

        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 20px; border-radius: 6px;
            font-family: 'Epilogue', sans-serif;
            font-size: .875rem; font-weight: 500;
            cursor: pointer; border: none;
            text-decoration: none; transition: .15s ease;
            white-space: nowrap; line-height: 1;
        }
        .btn-primary { background: var(--accent); color: #fff; font-weight: 600; }
        .btn-primary:hover { background: #235c43; box-shadow: 0 2px 12px rgba(42,107,79,.25); }
        .btn-secondary { background: transparent; color: var(--text2); border: 1px solid var(--border2); }
        .btn-secondary:hover { border-color: var(--text2); color: var(--text); background: var(--surface2); }
        .btn-danger { background: var(--danger-lt); color: var(--danger); border: 1px solid rgba(192,57,43,.2); }
        .btn-danger:hover { background: #f9e0dd; }
        .btn-info { background: var(--info-lt); color: var(--info); border: 1px solid rgba(26,95,130,.2); }
        .btn-info:hover { background: #d7ebf5; }
        .btn-sm { padding: 6px 13px; font-size: .8rem; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead th {
            text-align: left; padding: 9px 14px;
            color: var(--muted); font-size: .72rem;
            font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
            border-bottom: 2px solid var(--border);
            background: var(--surface2);
        }
        tbody tr { border-bottom: 1px solid var(--border); transition: .12s ease; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--surface2); }
        tbody td { padding: 13px 14px; vertical-align: middle; }

        .badge { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
        .badge-multipla { background: var(--accent-lt); color: var(--accent); }
        .badge-texto    { background: var(--info-lt); color: var(--info); }

        .alert {
            padding: 12px 16px; border-radius: 7px;
            font-size: .875rem; margin-bottom: 20px; border: 1px solid;
        }
        .alert-success { background: var(--accent-lt); border-color: rgba(42,107,79,.25); color: var(--accent); }
        .alert-error   { background: var(--danger-lt); border-color: rgba(192,57,43,.25); color: var(--danger); }
        .alert-warning { background: var(--warning-lt); border-color: rgba(184,133,10,.25); color: var(--warning); }

        .alt-row { display: flex; gap: 10px; align-items: center; margin-bottom: 9px; }
        .alt-row input[type=text] { flex: 1; }
        .alt-row label {
            text-transform: none; letter-spacing: 0;
            font-size: .875rem; color: var(--text2);
            margin: 0; display: flex; align-items: center; gap: 6px;
            cursor: pointer; white-space: nowrap; font-weight: 400;
        }
        .alt-row input[type=radio] { accent-color: var(--accent); width: 15px; height: 15px; }
        .alt-label {
            width: 28px; height: 28px;
            background: var(--surface2); border: 1px solid var(--border2);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Epilogue', sans-serif; font-weight: 600;
            font-size: .78rem; color: var(--muted); flex-shrink: 0;
        }

        .detail-label { color: var(--muted); font-size: .75rem; text-transform: uppercase; letter-spacing: .07em; margin-bottom: 5px; font-weight: 600; }
        .detail-value { color: var(--text); font-size: .95rem; margin-bottom: 20px; line-height: 1.65; }
        .alt-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 16px; border-radius: 7px;
            background: var(--surface2); margin-bottom: 8px;
            border: 1px solid var(--border); transition: .12s;
        }
        .alt-item.correta { border-color: rgba(42,107,79,.4); background: var(--accent-lt); }
        .alt-item.correta .alt-label { background: var(--accent); color: #fff; border-color: var(--accent); }
        .correta-badge { margin-left: auto; font-size: .75rem; color: var(--accent); font-weight: 600; }

        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(160px,1fr));
            gap: 14px; margin-bottom: 28px;
        }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 20px 22px;
            box-shadow: var(--shadow);
            display: flex; flex-direction: column; gap: 4px;
            position: relative; overflow: hidden;
        }
        .stat-card::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0;
            height: 3px; background: var(--accent);
            border-radius: 0 0 var(--radius) var(--radius);
        }
        .stat-card:nth-child(3)::after { background: var(--info); }
        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1;
        }
        .stat-desc { font-size: .8rem; color: var(--muted); margin-top: 2px; }

        .action-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

        footer {
            text-align: center; padding: 18px;
            font-size: .78rem; color: var(--muted);
            border-top: 1px solid var(--border);
            background: var(--surface);
        }
    </style>
</head>
<body>
<nav>
  <div class="nav-inner">
    <a class="nav-brand" href="index.php">
      <span class="nav-brand-dot"></span>
      WaterFalls Corp
    </a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>" href="index.php">Dashboard</a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='listar.php'?'active':'' ?>" href="listar.php">Perguntas</a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='criar_multipla.php'?'active':'' ?>" href="criar_multipla.php">+ Múltipla</a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='criar_texto.php'?'active':'' ?>" href="criar_texto.php">+ Texto</a>
    <div class="nav-sep"></div>
    <div class="nav-divider"></div>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='usuarios.php'?'active':'' ?>" href="usuarios.php">Usuários</a>
  </div>
</nav>
<div class="page-header">
  <h1><?= $titulo_pagina ?></h1>
  <?php if($subtitulo): ?><p><?= htmlspecialchars($subtitulo) ?></p><?php endif; ?>
</div>
<main>
<?php } ?>

<?php
function render_footer() {
?>
</main>
<footer>WaterFalls Corp &copy; <?= date('Y') ?> — Sistema de Treinamento Corporativo &nbsp;·&nbsp; FAETERJ-Rio &nbsp;·&nbsp; 3DAW</footer>
</body>
</html>
<?php } ?>
