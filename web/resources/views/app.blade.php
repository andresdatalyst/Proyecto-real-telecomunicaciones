<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Motor de Fusiones FTTH')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0d0f12;
            --surface:   #141720;
            --border:    #252a35;
            --accent:    #00d4aa;
            --accent-dim:#00d4aa22;
            --text:      #e2e8f0;
            --muted:     #64748b;
            --danger:    #f87171;
            --mono:      'IBM Plex Mono', monospace;
            --sans:      'IBM Plex Sans', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        header {
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            background: var(--surface);
        }

        .logo {
            font-family: var(--mono);
            font-size: 0.8rem;
            color: var(--accent);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-decoration: none;
        }

        .logo span { color: var(--muted); }

        nav a {
            font-size: 0.8rem;
            color: var(--muted);
            text-decoration: none;
            margin-left: 1.5rem;
            letter-spacing: 0.05em;
            transition: color 0.15s;
        }

        nav a:hover { color: var(--accent); }

        /* ── Main ── */
        main {
            flex: 1;
            padding: 3rem 2rem;
            max-width: 860px;
            width: 100%;
            margin: 0 auto;
        }

        /* ── Page header ── */
        .page-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .page-header .label {
            font-family: var(--mono);
            font-size: 0.7rem;
            color: var(--accent);
            letter-spacing: 0.15em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 500;
            letter-spacing: -0.02em;
        }

        .page-header p {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 0.4rem;
            line-height: 1.6;
        }

        /* ── Card ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 2rem;
        }

        /* ── Form ── */
        .form-group {
            margin-bottom: 1.4rem;
        }

        label {
            display: block;
            font-family: var(--mono);
            font-size: 0.72rem;
            color: var(--muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
        }

        input[type="number"],
        input[type="text"] {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 3px;
            padding: 0.65rem 0.9rem;
            color: var(--text);
            font-family: var(--mono);
            font-size: 0.9rem;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-dim);
        }

        .hint {
            font-size: 0.72rem;
            color: var(--muted);
            margin-top: 0.3rem;
            font-family: var(--mono);
        }

        /* ── Button ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--accent);
            color: #0d0f12;
            border: none;
            border-radius: 3px;
            padding: 0.7rem 1.6rem;
            font-family: var(--mono);
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: opacity 0.15s, transform 0.1s;
            margin-top: 0.5rem;
        }

        .btn:hover  { opacity: 0.88; }
        .btn:active { transform: scale(0.98); }

        /* ── Alert ── */
        .alert {
            padding: 0.9rem 1.2rem;
            border-radius: 3px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border-left: 3px solid;
        }

        .alert-success {
            background: #00d4aa11;
            border-color: var(--accent);
            color: var(--accent);
        }

        .alert-error {
            background: #f8717111;
            border-color: var(--danger);
            color: var(--danger);
        }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
            font-family: var(--mono);
        }

        th {
            text-align: left;
            padding: 0.6rem 0.8rem;
            color: var(--muted);
            font-size: 0.68rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
            font-weight: 400;
        }

        td {
            padding: 0.55rem 0.8rem;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--accent-dim); }

        .badge {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 2px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .badge-div1 { background: #3b82f622; color: #60a5fa; }
        .badge-div2 { background: #8b5cf622; color: #a78bfa; }
        .badge-empty { color: var(--muted); }

        /* ── Footer ── */
        footer {
            border-top: 1px solid var(--border);
            padding: 1rem 2rem;
            font-family: var(--mono);
            font-size: 0.68rem;
            color: var(--muted);
            text-align: center;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

<header>
    <a class="logo" href="{{ route('home') }}">
        FTTH <span>/</span> Motor de Fusiones
    </a>
    <nav>
        <a href="{{ route('fusion.distribucion.form') }}">Distribución</a>
        <a href="{{ route('fusion.alimentacion.form') }}">Alimentación</a>
    </nav>
</header>

<main>
    @yield('content')
</main>

<footer>
    Motor de Fusiones FTTH &mdash; PostgreSQL + Laravel
</footer>

</body>
</html>
