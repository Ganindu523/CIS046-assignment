<?php

require_once __DIR__ . '/auth.php';

if (getCurrentUser()) {
    header('Location: game.php');
    exit;
}

$error = '';
$success = '';
$activeTab = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $result = loginUser($username, $password);

        if ($result['success']) {
            header('Location: game.php');
            exit;
        } else {
            $error = $result['message'];
            $activeTab = 'login';
        }
    } elseif ($action === 'register') {
        $username = trim($_POST['reg_username'] ?? '');
        $email    = trim($_POST['reg_email'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        $result   = registerUser($username, $email, $password);

        if ($result['success']) {
            loginUser($username, $password);
            header('Location: game.php');
            exit;
        } else {
            $error = $result['message'];
            $activeTab = 'register';
        }
    }
}

$msgParam = htmlspecialchars($_GET['msg'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PuzzleTrainer — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0a0a0f;
            --surface: #13131a;
            --border: #1e1e2e;
            --accent: #7c3aed;
            --accent2: #10b981;
            --text: #e2e8f0;
            --muted: #64748b;
            --error: #ef4444;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(124,58,237,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(124,58,237,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            animation: float 8s ease-in-out infinite;
        }
        .orb-1 { width: 400px; height: 400px; background: rgba(124,58,237,0.15); top: -100px; left: -100px; }
        .orb-2 { width: 300px; height: 300px; background: rgba(16,185,129,0.1); bottom: -50px; right: -50px; animation-delay: -4s; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(20px, -20px); }
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }

        .logo {
            font-family: 'Syne', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 0.25rem;
        }
        .logo span { color: var(--accent); }

        .tagline {
            color: var(--muted);
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.75rem;
            background: var(--bg);
            padding: 4px;
            border-radius: 10px;
        }
        .tab-btn {
            flex: 1;
            padding: 0.6rem;
            border: none;
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            color: var(--muted);
        }
        .tab-btn.active {
            background: var(--surface);
            color: var(--text);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .form-panel { display: none; }
        .form-panel.active { display: block; }

        .form-group { margin-bottom: 1rem; }
        label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.2s;
            outline: none;
        }
        input:focus { border-color: var(--accent); }

        .btn-primary {
            width: 100%;
            padding: 0.85rem;
            background: var(--accent);
            border: none;
            border-radius: 10px;
            color: white;
            font-family: 'Syne', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: all 0.2s;
            letter-spacing: 0.02em;
        }
        .btn-primary:hover { background: #6d28d9; transform: translateY(-1px); }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .alert-info { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #6ee7b7; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="card">
        <div class="logo">Puzzle<span>Trainer</span></div>
        <p class="tagline">Sharpen your mind. Climb the leaderboard.</p>

        <?php if ($msgParam): ?>
            <div class="alert alert-info"><?= $msgParam ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn <?= $activeTab === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Sign In</button>
            <button class="tab-btn <?= $activeTab === 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Create Account</button>
        </div>

        <div class="form-panel <?= $activeTab === 'login' ? 'active' : '' ?>" id="panel-login">
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="your_username" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-primary">Sign In →</button>
            </form>
        </div>

        <div class="form-panel <?= $activeTab === 'register' ? 'active' : '' ?>" id="panel-register">
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="reg_username" placeholder="choose_a_username" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="reg_email" placeholder="you@example.com" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="reg_password" placeholder="min 8 characters" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-primary">Create Account →</button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
            document.querySelector(`#panel-${tab}`).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
