<?php

require_once __DIR__ . '/auth.php';
$user = requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PuzzleTrainer — Leaderboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #0a0a0f; --surface: #13131a; --surface2: #1a1a25;
            --border: #1e1e2e; --accent: #7c3aed; --accent2: #10b981;
            --warn: #f59e0b; --text: #e2e8f0; --muted: #64748b;
        }
        body { background: var(--bg); color: var(--text); font-family: 'DM Sans', sans-serif; min-height: 100vh; }

        nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 2rem; border-bottom: 1px solid var(--border);
            background: var(--surface); position: sticky; top: 0; z-index: 100;
        }
        .logo { font-family: 'Syne', sans-serif; font-size: 1.3rem; font-weight: 800; letter-spacing: -0.03em; }
        .logo span { color: var(--accent); }
        .nav-links { display: flex; gap: 0.5rem; align-items: center; }
        .nav-link { padding: 0.5rem 1rem; border-radius: 8px; color: var(--muted); text-decoration: none; font-size: 0.875rem; transition: all 0.2s; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background: var(--surface2); color: var(--text); }
        .avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: <?= htmlspecialchars($user['avatar_color']) ?>;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.85rem; color: white; margin-left: 0.5rem;
        }

        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }

        h1 { font-family: 'Syne', sans-serif; font-size: 1.75rem; font-weight: 800; margin-bottom: 2rem; letter-spacing: -0.03em; }
        h1 span { color: var(--accent); }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; }
        .card-title { font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); }

        /* Leaderboard rows */
        .lb-row {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--border);
            transition: all 0.2s;
        }
        .lb-row:last-child { border-bottom: none; }
        .lb-rank { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1rem; width: 24px; text-align: center; }
        .lb-rank.gold { color: #fbbf24; }
        .lb-rank.silver { color: #94a3b8; }
        .lb-rank.bronze { color: #b45309; }
        .lb-avatar { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.8rem; color: white; flex-shrink: 0; }
        .lb-name { flex: 1; font-weight: 500; }
        .lb-name.me { color: var(--accent); }
        .lb-score { font-family: 'Syne', sans-serif; font-weight: 700; color: var(--accent2); }

        /* Personal stats */
        .my-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem; }
        .my-stat { background: var(--surface2); border-radius: 10px; padding: 0.75rem; text-align: center; }
        .my-stat-val { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 700; }
        .my-stat-lbl { font-size: 0.7rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em; }

        /* Score chart */
        .chart-area { position: relative; height: 120px; }
        canvas { width: 100% !important; height: 100% !important; }

        .loading-msg { color: var(--muted); font-size: 0.875rem; text-align: center; padding: 2rem; }

        /* Rank badge */
        .rank-badge {
            background: linear-gradient(135deg, var(--accent), #a855f7);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        .rank-badge-num { font-family: 'Syne', sans-serif; font-size: 2rem; font-weight: 800; }
        .rank-badge-lbl { font-size: 0.75rem; color: rgba(255,255,255,0.7); }
    </style>
</head>
<body>

<nav>
    <div class="logo">Puzzle<span>Trainer</span></div>
    <div class="nav-links">
        <a href="game.php" class="nav-link">Play</a>
        <a href="leaderboard.php" class="nav-link active">Leaderboard</a>
        <a href="logout.php" class="nav-link">Sign Out</a>
        <div class="avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
    </div>
</nav>

<div class="container">
    <h1>Leader<span>board</span></h1>

    <div class="grid">

        <!-- TOP PLAYERS -->
        <div class="card">
            <div class="card-title">🏆 Top Players</div>
            <div id="leaderboard-list"><p class="loading-msg">Loading...</p></div>
        </div>

        <!-- MY STATS -->
        <div class="card">
            <div class="card-title">📊 Your Stats</div>
            <div id="my-stats-area"><p class="loading-msg">Loading...</p></div>
        </div>

    </div>
</div>

<script>

// EVENT: DOMContentLoaded → fetch leaderboard data
document.addEventListener('DOMContentLoaded', loadLeaderboard);

/**
 * Fetches leaderboard data from our PHP API.
 * EVENT: async fetch resolves → triggers UI render
 * INTEROPERABILITY: communicates with our PHP API via HTTP/JSON
 */
async function loadLeaderboard() {
    try {
        const response = await fetch('api/get_leaderboard.php');
        const data = await response.json();

        if (!data.success) throw new Error(data.error);

        renderLeaderboard(data.leaderboard);
        renderMyStats(data.myStats, data.myRank, data.history);

    } catch (err) {
        document.getElementById('leaderboard-list').innerHTML =
            '<p class="loading-msg">Could not load data. Please refresh.</p>';
        console.error('Leaderboard error:', err);
    }
}

function renderLeaderboard(players) {
    const container = document.getElementById('leaderboard-list');

    if (!players.length) {
        container.innerHTML = '<p class="loading-msg">No scores yet. Be the first!</p>';
        return;
    }

    const rankClass = ['gold', 'silver', 'bronze'];
    const rankSymbol = ['🥇', '🥈', '🥉'];
    const currentUser = '<?= htmlspecialchars($user['username']) ?>';

    container.innerHTML = players.map((p, i) => `
        <div class="lb-row">
            <div class="lb-rank ${rankClass[i] || ''}">
                ${i < 3 ? rankSymbol[i] : (i + 1)}
            </div>
            <div class="lb-avatar" style="background:${p.avatar_color || '#6366f1'}">
                ${p.username.charAt(0).toUpperCase()}
            </div>
            <div class="lb-name ${p.username === currentUser ? 'me' : ''}">
                ${escHtml(p.username)}${p.username === currentUser ? ' (you)' : ''}
            </div>
            <div class="lb-score">${p.best_score}</div>
        </div>
    `).join('');
}

function renderMyStats(stats, rank, history) {
    const container = document.getElementById('my-stats-area');

    const bestScore = stats?.best_score || 0;
    const avgScore  = stats?.avg_score  ? Math.round(stats.avg_score) : 0;
    const games     = stats?.games_played || 0;
    const correct   = stats?.total_correct || 0;

    container.innerHTML = `
        <div class="rank-badge">
            <div class="rank-badge-num">#${rank}</div>
            <div class="rank-badge-lbl">Global Rank</div>
        </div>
        <div class="my-stats">
            <div class="my-stat">
                <div class="my-stat-val" style="color:var(--accent2)">${bestScore}</div>
                <div class="my-stat-lbl">Best Score</div>
            </div>
            <div class="my-stat">
                <div class="my-stat-val" style="color:var(--warn)">${avgScore}</div>
                <div class="my-stat-lbl">Avg Score</div>
            </div>
            <div class="my-stat">
                <div class="my-stat-val">${games}</div>
                <div class="my-stat-lbl">Games</div>
            </div>
            <div class="my-stat">
                <div class="my-stat-val" style="color:var(--accent)">${correct}</div>
                <div class="my-stat-lbl">Total Correct</div>
            </div>
        </div>
        <div class="card-title" style="margin-bottom:0.75rem;">Recent Scores</div>
        <div class="chart-area">
            <canvas id="score-chart"></canvas>
        </div>
    `;

   
    // EVENT: canvas drawing happens after DOM is updated
    if (history && history.length) {
        drawChart(history);
    }
}




function drawChart(history) {
    const canvas = document.getElementById('score-chart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const W = canvas.offsetWidth;
    const H = canvas.offsetHeight;
    canvas.width = W;
    canvas.height = H;

    const scores = history.map(h => h.score);
    const maxScore = Math.max(...scores, 10);
    const barW = (W / scores.length) * 0.6;
    const gap = (W / scores.length) * 0.4;

    scores.forEach((score, i) => {
        const barH = (score / maxScore) * (H - 20);
        const x = i * (barW + gap) + gap / 2;
        const y = H - barH;

        // Gradient bar
        const grad = ctx.createLinearGradient(0, y, 0, H);
        grad.addColorStop(0, '#7c3aed');
        grad.addColorStop(1, '#10b981');
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.roundRect(x, y, barW, barH, 3);
        ctx.fill();

        // Score label
        ctx.fillStyle = '#64748b';
        ctx.font = '10px DM Sans';
        ctx.textAlign = 'center';
        ctx.fillText(score, x + barW / 2, y - 4);
    });
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>

</body>
</html>
