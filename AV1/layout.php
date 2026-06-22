<?php
function render_header($titulo_pagina = 'Dashboard', $subtitulo = '') {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?> — WaterFalls Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:         #0b0f17;
            --surface:    #131927;
            --surface2:   #1a2235;
            --border:     #243047;
            --accent:     #2af5b0;
            --accent2:    #1dd4f0;
            --danger:     #f05c5c;
            --warning:    #f5c842;
            --text:       #e8edf5;
            --muted:      #7a8ba6;
            --radius:     10px;
            --transition: .2s ease;
        }

        html { font-size: 16px; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(42,245,176,.07) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 90% 80%, rgba(29,212,240,.05) 0%, transparent 50%);
        }

        /* ── NAV ── */
        nav {
            background: rgba(13,18,30,.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 100;
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto;
            display: flex; align-items: center; gap: 8px;
            padding: 0 24px; height: 60px;
        }
        .nav-brand {
            font-family: 'Syne', sans-serif;
            font-weight: 800; font-size: 1.1rem;
            color: var(--accent);
            letter-spacing: -.5px;
            margin-right: 20px;
            text-decoration: none;
            display: flex; align-items: center; gap: 8px;
        }
        .nav-brand::before {
            content: '';
            display: inline-block; width: 8px; height: 8px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--accent);
        }
        .nav-link {
            color: var(--muted); text-decoration: none;
            font-size: .85rem; font-weight: 500;
            padding: 6px 14px; border-radius: 6px;
            transition: var(--transition);
            white-space: nowrap;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--text); background: var(--surface2);
        }
        .nav-sep { flex: 1; }

        /* ── PAGE HEADER ── */
        .page-header {
            max-width: 1200px; margin: 0 auto;
            padding: 48px 24px 24px;
        }
        .page-header h1 {
            font-family: 'Syne', sans-serif;
            font-weight: 800; font-size: clamp(1.8rem,4vw,2.6rem);
            line-height: 1.1; letter-spacing: -.5px;
        }
        .page-header h1 span { color: var(--accent); }
        .page-header p {
            color: var(--muted); font-size: .95rem;
            margin-top: 8px; font-weight: 300;
        }

        /* ── MAIN ── */
        main {
            max-width: 1200px; margin: 0 auto;
            padding: 0 24px 64px; flex: 1;
        }

        /* ── CARDS ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
        }
        .card + .card { margin-top: 16px; }
        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 1rem; font-weight: 700;
            color: var(--text); margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }
        .card-title::before {
            content: '';
            display: block; width: 3px; height: 18px;
            background: var(--accent); border-radius: 2px;
        }

        /* ── FORMS ── */
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            font-size: .8rem; font-weight: 500;
            color: var(--muted); letter-spacing: .06em;
            text-transform: uppercase; margin-bottom: 8px;
        }
        input[type=text], input[type=email], input[type=password],
        textarea, select {
            width: 100%; background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text); font-family: 'DM Sans', sans-serif;
            font-size: .95rem; padding: 11px 14px;
            border-radius: 7px;
            outline: none;
            transition: var(--transition);
        }
        input:focus, textarea:focus, select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(42,245,176,.1);
        }
        textarea { resize: vertical; min-height: 100px; line-height: 1.6; }
        select option { background: var(--surface2); }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 22px; border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem; font-weight: 500;
            cursor: pointer; border: none;
            text-decoration: none; transition: var(--transition);
            white-space: nowrap;
        }
        .btn-primary {
            background: var(--accent); color: #0b0f17;
            font-weight: 600;
        }
        .btn-primary:hover { background: #1ed898; box-shadow: 0 4px 18px rgba(42,245,176,.3); }
        .btn-secondary {
            background: transparent; color: var(--muted);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover { border-color: var(--muted); color: var(--text); }
        .btn-danger {
            background: rgba(240,92,92,.12); color: var(--danger);
            border: 1px solid rgba(240,92,92,.25);
        }
        .btn-danger:hover { background: rgba(240,92,92,.22); }
        .btn-info {
            background: rgba(29,212,240,.1); color: var(--accent2);
            border: 1px solid rgba(29,212,240,.25);
        }
        .btn-info:hover { background: rgba(29,212,240,.2); }
        .btn-sm { padding: 6px 14px; font-size: .8rem; }

        /* ── TABLE ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        thead th {
            text-align: left;
            padding: 10px 14px;
            color: var(--muted); font-size: .75rem;
            font-weight: 600; letter-spacing: .07em;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
        }
        tbody tr { border-bottom: 1px solid rgba(36,48,71,.6); transition: var(--transition); }
        tbody tr:hover { background: var(--surface2); }
        tbody td { padding: 14px; vertical-align: middle; }

        /* ── BADGE ── */
        .badge {
            display: inline-block;
            padding: 3px 10px; border-radius: 20px;
            font-size: .73rem; font-weight: 600; letter-spacing: .04em;
        }
        .badge-multipla { background: rgba(42,245,176,.12); color: var(--accent); }
        .badge-texto    { background: rgba(29,212,240,.12); color: var(--accent2); }

        /* ── ALERTS ── */
        .alert {
            padding: 12px 18px; border-radius: 8px;
            font-size: .9rem; margin-bottom: 20px;
            border-left: 3px solid;
        }
        .alert-success { background: rgba(42,245,176,.08); border-color: var(--accent); color: var(--accent); }
        .alert-error   { background: rgba(240,92,92,.08);  border-color: var(--danger); color: var(--danger); }
        .alert-warning { background: rgba(245,200,66,.08); border-color: var(--warning); color: var(--warning); }

        /* ── ALTERNATIVAS ── */
        .alt-row {
            display: flex; gap: 10px; align-items: center;
            margin-bottom: 10px;
        }
        .alt-row input[type=text] { flex: 1; }
        .alt-row label {
            text-transform: none; letter-spacing: 0;
            font-size: .9rem; color: var(--text);
            margin: 0; display: flex; align-items: center; gap: 6px;
            cursor: pointer; white-space: nowrap;
        }
        .alt-row input[type=radio] { accent-color: var(--accent); width: 16px; height: 16px; }
        .alt-label {
            width: 28px; height: 28px;
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif; font-weight: 700;
            font-size: .8rem; color: var(--muted); flex-shrink: 0;
        }

        /* ── DETAIL VIEW ── */
        .detail-label { color: var(--muted); font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
        .detail-value { color: var(--text); font-size: 1rem; margin-bottom: 20px; line-height: 1.6; }
        .alt-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: 8px;
            background: var(--surface2); margin-bottom: 8px;
            border: 1px solid var(--border);
        }
        .alt-item.correta { border-color: var(--accent); background: rgba(42,245,176,.06); }
        .alt-item.correta .alt-label { background: var(--accent); color: #0b0f17; border-color: var(--accent); }
        .correta-badge { margin-left: auto; font-size: .75rem; color: var(--accent); font-weight: 600; }

        /* ── GRID ── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px,1fr)); gap: 16px; margin-bottom: 32px; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 20px;
            display: flex; flex-direction: column; gap: 4px;
        }
        .stat-number { font-family: 'Syne', sans-serif; font-size: 2rem; font-weight: 800; }
        .stat-desc { font-size: .8rem; color: var(--muted); }

        /* ── ACTION ROW ── */
        .action-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

        /* ── FOOTER ── */
        footer {
            text-align: center; padding: 20px;
            font-size: .78rem; color: var(--muted);
            border-top: 1px solid var(--border);
        }
    </style>
</head>
<body>
<nav>
  <div class="nav-inner">
    <a class="nav-brand" href="index.php">WaterFalls Corp</a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>" href="index.php">Dashboard</a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='listar.php'?'active':'' ?>" href="listar.php">Perguntas</a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='criar_multipla.php'?'active':'' ?>" href="criar_multipla.php">+ Múltipla</a>
    <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='criar_texto.php'?'active':'' ?>" href="criar_texto.php">+ Texto</a>
    <div class="nav-sep"></div>
    <a class="nav-link" href="usuarios.php">Usuários</a>
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
<footer>WaterFalls Corp &copy; <?= date('Y') ?> — Sistema de Treinamento Corporativo &nbsp;|&nbsp; FAETERJ-Rio &nbsp;|&nbsp; 3DAW</footer>
</body>
</html>
<?php } ?>
