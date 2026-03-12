<?php
require_once __DIR__ . '/auth.php';
$user = requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PuzzleTrainer — Play</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0a0a0f;
            --surface: #13131a;
            --surface2: #1a1a25;
            --border: #1e1e2e;
            --accent: #7c3aed;
            --accent2: #10b981;
            --warn: #f59e0b;
            --error: #ef4444;
            --text: #e2e8f0;
            --muted: #64748b;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-family: 'Syne', sans-serif;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }
        .logo span { color: var(--accent); }

        .nav-links { display: flex; gap: 0.5rem; align-items: center; }
        .nav-link {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
            font-weight: 500;
        }
        .nav-link:hover, .nav-link.active { background: var(--surface2); color: var(--text); }

        .avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: <?= htmlspecialchars($user['avatar_color']) ?>;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            color: white;
            margin-left: 0.5rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
        }
        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.25rem;
        }
        .stat-label { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }

        .game-area {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
        }

        .difficulty-bar {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .diff-btn {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .diff-btn.active { border-color: var(--accent); color: var(--accent); background: rgba(124,58,237,0.1); }

        #puzzle-image {
            max-width: 100%;
            max-height: 280px;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
            transition: opacity 0.3s;
        }
        #puzzle-image.loading { opacity: 0.3; }

        #timer-container {
            display: none;
            margin-bottom: 1rem;
        }
        #timer-bar-bg {
            background: var(--border);
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
        }
        #timer-bar {
            height: 100%;
            background: var(--accent2);
            border-radius: 3px;
            transition: width 1s linear, background 0.3s;
            width: 100%;
        }
        #timer-label {
            font-family: 'DM Mono', monospace;
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 0.4rem;
            text-align: right;
        }

        .answer-area {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .numpad {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.5rem;
            max-width: 320px;
            margin: 0 auto 1.5rem;
        }
        .num-btn {
            padding: 0.85rem;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .num-btn:hover { background: var(--accent); border-color: var(--accent); transform: scale(1.05); }
        .num-btn:active { transform: scale(0.95); }

        #feedback {
            height: 2rem;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            margin-bottom: 1rem;
        }
        #feedback.correct { color: var(--accent2); }
        #feedback.wrong { color: var(--error); }

        .controls { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            border: none;
            font-family: 'Syne', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-start { background: var(--accent); color: white; }
        .btn-start:hover { background: #6d28d9; }
        .btn-skip { background: var(--surface2); color: var(--muted); border: 1px solid var(--border); }
        .btn-skip:hover { color: var(--text); }
        .btn-stop { background: rgba(239,68,68,0.1); color: var(--error); border: 1px solid rgba(239,68,68,0.2); }
        .btn-stop:hover { background: rgba(239,68,68,0.2); }

        #pre-game { }
        #in-game { display: none; }
        #post-game { display: none; }

        .post-game-score {
            font-family: 'Syne', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            color: var(--accent);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .post-game-label { color: var(--muted); margin-bottom: 1.5rem; }

        .result-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .result-item { background: var(--surface2); border-radius: 10px; padding: 0.75rem; }
        .result-num { font-family: 'Syne', sans-serif; font-size: 1.5rem; font-weight: 700; }
        .result-txt { font-size: 0.75rem; color: var(--muted); }

        .puzzle-count {
            font-family: 'DM Mono', monospace;
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 0.75rem;
        }

        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            100% { box-shadow: 0 0 0 20px rgba(16,185,129,0); }
        }
        .pulse-correct { animation: pulse-green 0.5s ease-out; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }
        .shake { animation: shake 0.4s ease-out; }
    </style>
</head>
<body>

<nav>
    <div class="logo">Puzzle<span>Trainer</span></div>
    <div class="nav-links">
        <a href="game.php" class="nav-link active">Play</a>
        <a href="leaderboard.php" class="nav-link">Leaderboard</a>
        <a href="logout.php" class="nav-link">Sign Out</a>
        <div class="avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
    </div>
</nav>

<div class="container">

    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-value" id="stat-score">0</div>
            <div class="stat-label">Score</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--accent2)" id="stat-correct">0</div>
            <div class="stat-label">Correct</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--error)" id="stat-wrong">0</div>
            <div class="stat-label">Wrong</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--warn)" id="stat-streak">0</div>
            <div class="stat-label">Streak 🔥</div>
        </div>
    </div>

    <div class="game-area">

        <div id="pre-game">
            <h2 style="font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:700; margin-bottom:0.5rem;">
                Welcome, <?= htmlspecialchars($user['username']) ?>!
            </h2>
            <p style="color:var(--muted); margin-bottom:1.5rem;">Find the missing digit in each puzzle. Choose your difficulty and start playing.</p>

            <div class="difficulty-bar" id="difficulty-selector">
                <button class="diff-btn active" data-diff="normal">Normal (10 puzzles)</button>
                <button class="diff-btn" data-diff="hard">Hard (15 puzzles, less time)</button>
                <button class="diff-btn" data-diff="timed">Timed Rush (60 sec, unlimited)</button>
            </div>

            <button class="btn btn-start" onclick="startGame()">Start Game →</button>
        </div>

        <div id="in-game">
            <div class="puzzle-count" id="puzzle-count">Puzzle 1 / 10</div>

            <div id="timer-container">
                <div id="timer-label">0:30</div>
                <div id="timer-bar-bg"><div id="timer-bar"></div></div>
            </div>

            <img id="puzzle-image" src="" alt="Puzzle" class="loading">

            <div id="feedback"></div>

            <div class="numpad" id="numpad">
                <button class="num-btn" onclick="answerSelected(0)">0</button>
                <button class="num-btn" onclick="answerSelected(1)">1</button>
                <button class="num-btn" onclick="answerSelected(2)">2</button>
                <button class="num-btn" onclick="answerSelected(3)">3</button>
                <button class="num-btn" onclick="answerSelected(4)">4</button>
                <button class="num-btn" onclick="answerSelected(5)">5</button>
                <button class="num-btn" onclick="answerSelected(6)">6</button>
                <button class="num-btn" onclick="answerSelected(7)">7</button>
                <button class="num-btn" onclick="answerSelected(8)">8</button>
                <button class="num-btn" onclick="answerSelected(9)">9</button>
            </div>

            <div class="controls">
                <button class="btn btn-skip" onclick="skipPuzzle()">Skip Puzzle</button>
                <button class="btn btn-stop" onclick="endGame()">End Session</button>
            </div>
        </div>

        <div id="post-game">
            <div class="post-game-score" id="final-score">0</div>
            <div class="post-game-label">points earned this round</div>

            <div class="result-grid">
                <div class="result-item">
                    <div class="result-num" style="color:var(--accent2)" id="res-correct">0</div>
                    <div class="result-txt">Correct</div>
                </div>
                <div class="result-item">
                    <div class="result-num" style="color:var(--error)" id="res-wrong">0</div>
                    <div class="result-txt">Wrong</div>
                </div>
                <div class="result-item">
                    <div class="result-num" style="color:var(--warn)" id="res-streak">0</div>
                    <div class="result-txt">Best Streak</div>
                </div>
            </div>

            <div id="save-status" style="color:var(--muted); font-size:0.875rem; margin-bottom:1rem;"></div>

            <div class="controls">
                <button class="btn btn-start" onclick="resetGame()">Play Again</button>
                <a href="leaderboard.php" class="btn btn-skip" style="text-decoration:none;">View Leaderboard →</a>
            </div>
        </div>

    </div>
</div>

<script>
const gameState = {
    active: false,
    difficulty: 'normal',
    maxPuzzles: 10,
    puzzleIndex: 0,
    correctAnswers: 0,
    wrongAnswers: 0,
    streak: 0,
    bestStreak: 0,
    score: 0,
    currentSolution: null,
    isAnswering: false,
    startTime: null,
    timerInterval: null,
    timerSeconds: 0,
    timerMax: 30,
};

const DIFFICULTY_CONFIG = {
    normal: { puzzles: 10, hasTimer: false },
    hard:   { puzzles: 15, hasTimer: true, timerSeconds: 20 },
    timed:  { puzzles: Infinity, hasTimer: true, timerSeconds: 30, isGlobal: true },
};

const ui = {
    preGame:        document.getElementById('pre-game'),
    inGame:         document.getElementById('in-game'),
    postGame:       document.getElementById('post-game'),
    puzzleImg:      document.getElementById('puzzle-image'),
    feedback:       document.getElementById('feedback'),
    puzzleCount:    document.getElementById('puzzle-count'),
    statScore:      document.getElementById('stat-score'),
    statCorrect:    document.getElementById('stat-correct'),
    statWrong:      document.getElementById('stat-wrong'),
    statStreak:     document.getElementById('stat-streak'),
    timerContainer: document.getElementById('timer-container'),
    timerBar:       document.getElementById('timer-bar'),
    timerLabel:     document.getElementById('timer-label'),
    numpad:         document.getElementById('numpad'),
};

document.querySelectorAll('.diff-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.diff-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        gameState.difficulty = btn.dataset.diff;
    });
});

document.addEventListener('keydown', (e) => {
    if (!gameState.active) return;
    const digit = parseInt(e.key);
    if (!isNaN(digit) && digit >= 0 && digit <= 9) {
        answerSelected(digit);
    }
});

function startGame() {
    const config = DIFFICULTY_CONFIG[gameState.difficulty];

    gameState.active = true;
    gameState.puzzleIndex = 0;
    gameState.correctAnswers = 0;
    gameState.wrongAnswers = 0;
    gameState.streak = 0;
    gameState.bestStreak = 0;
    gameState.score = 0;
    gameState.maxPuzzles = config.puzzles;
    gameState.startTime = Date.now();

    updateStatsBar();

    ui.preGame.style.display = 'none';
    ui.inGame.style.display = 'block';
    ui.postGame.style.display = 'none';

    if (config.hasTimer && config.isGlobal) {
        startGlobalTimer(config.timerSeconds);
    }

    loadNextPuzzle();
}

async function loadNextPuzzle() {
    gameState.isAnswering = true;
    ui.puzzleImg.classList.add('loading');
    ui.feedback.textContent = '';
    ui.feedback.className = '';
    setNumpadEnabled(false);

    const config = DIFFICULTY_CONFIG[gameState.difficulty];
    if (config.hasTimer && !config.isGlobal) {
        startPuzzleTimer(config.timerSeconds);
    }

    ui.puzzleCount.textContent = gameState.difficulty === 'timed'
        ? `Puzzle ${gameState.puzzleIndex + 1} — keep going!`
        : `Puzzle ${gameState.puzzleIndex + 1} / ${gameState.maxPuzzles}`;

    try {
        const response = await fetch('api/get_puzzle.php');

        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to load puzzle');
        }

        gameState.currentSolution = data.solution;
        ui.puzzleImg.onload = () => {
            ui.puzzleImg.classList.remove('loading');
            gameState.isAnswering = false;
            setNumpadEnabled(true);
        };
        ui.puzzleImg.src = data.imageUrl;

    } catch (err) {
        ui.feedback.textContent = 'Failed to load puzzle. Retrying...';
        ui.feedback.className = 'wrong';
        setTimeout(loadNextPuzzle, 2000);
    }
}

function answerSelected(digit) {
    if (gameState.isAnswering || !gameState.active) return;
    gameState.isAnswering = true;
    setNumpadEnabled(false);

    const correct = digit === gameState.currentSolution;

    if (correct) {
        gameState.correctAnswers++;
        gameState.streak++;
        if (gameState.streak > gameState.bestStreak) {
            gameState.bestStreak = gameState.streak;
        }

        const streakBonus = Math.min(gameState.streak - 1, 5);
        const points = 10 + streakBonus;
        gameState.score += points;

        ui.feedback.textContent = `✓ Correct! +${points} points${streakBonus > 0 ? ` (${streakBonus} streak bonus!)` : ''}`;
        ui.feedback.className = 'correct';
        ui.puzzleImg.classList.add('pulse-correct');
        setTimeout(() => ui.puzzleImg.classList.remove('pulse-correct'), 500);

    } else {
        gameState.wrongAnswers++;
        gameState.streak = 0;
        gameState.score = Math.max(0, gameState.score - 3);

        ui.feedback.textContent = `✗ Wrong — the answer was ${gameState.currentSolution}`;
        ui.feedback.className = 'wrong';
        ui.puzzleImg.classList.add('shake');
        setTimeout(() => ui.puzzleImg.classList.remove('shake'), 400);
    }

    updateStatsBar();
    clearPuzzleTimer();
    gameState.puzzleIndex++;

    if (gameState.difficulty !== 'timed' && gameState.puzzleIndex >= gameState.maxPuzzles) {
        setTimeout(endGame, 1200);
    } else {
        setTimeout(loadNextPuzzle, 1200);
    }
}

function skipPuzzle() {
    if (!gameState.active || gameState.isAnswering) return;
    gameState.puzzleIndex++;
    clearPuzzleTimer();

    if (gameState.difficulty !== 'timed' && gameState.puzzleIndex >= gameState.maxPuzzles) {
        endGame();
    } else {
        loadNextPuzzle();
    }
}

async function endGame() {
    if (!gameState.active) return;
    gameState.active = false;
    clearAllTimers();

    const timeTaken = Math.round((Date.now() - gameState.startTime) / 1000);

    ui.inGame.style.display = 'none';
    ui.postGame.style.display = 'block';

    document.getElementById('final-score').textContent = gameState.score;
    document.getElementById('res-correct').textContent = gameState.correctAnswers;
    document.getElementById('res-wrong').textContent = gameState.wrongAnswers;
    document.getElementById('res-streak').textContent = gameState.bestStreak;

    const saveStatus = document.getElementById('save-status');
    saveStatus.textContent = 'Saving your score...';

    try {
        const response = await fetch('api/save_score.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                correct: gameState.correctAnswers,
                wrong: gameState.wrongAnswers,
                timeTaken: timeTaken,
                difficulty: gameState.difficulty,
            }),
        });

        const data = await response.json();
        if (data.success) {
            saveStatus.textContent = `✓ Score saved! You're ranked #${data.rank} overall.`;
            saveStatus.style.color = 'var(--accent2)';
        } else {
            saveStatus.textContent = 'Could not save score.';
        }
    } catch (err) {
        saveStatus.textContent = 'Could not save score (network error).';
    }
}

function resetGame() {
    clearAllTimers();
    gameState.active = false;
    ui.inGame.style.display = 'none';
    ui.postGame.style.display = 'none';
    ui.preGame.style.display = 'block';
    updateStatsBar();
}

let puzzleTimerInterval = null;
let globalTimerInterval = null;
let globalTimerSeconds = 0;

function startPuzzleTimer(seconds) {
    clearPuzzleTimer();
    gameState.timerSeconds = seconds;
    gameState.timerMax = seconds;
    ui.timerContainer.style.display = 'block';

    puzzleTimerInterval = setInterval(() => {
        gameState.timerSeconds--;
        updateTimerUI(gameState.timerSeconds, gameState.timerMax);

        if (gameState.timerSeconds <= 0) {
            clearPuzzleTimer();
            ui.feedback.textContent = '⏱ Time up! Moving on...';
            ui.feedback.className = 'wrong';
            gameState.wrongAnswers++;
            gameState.streak = 0;
            updateStatsBar();
            gameState.puzzleIndex++;
            setTimeout(loadNextPuzzle, 1000);
        }
    }, 1000);
}

function startGlobalTimer(seconds) {
    globalTimerSeconds = seconds;
    ui.timerContainer.style.display = 'block';

    globalTimerInterval = setInterval(() => {
        globalTimerSeconds--;
        updateTimerUI(globalTimerSeconds, seconds);

        if (globalTimerSeconds <= 0) {
            clearInterval(globalTimerInterval);
            ui.feedback.textContent = '⏱ Time is up!';
            ui.feedback.className = 'wrong';
            setTimeout(endGame, 1000);
        }
    }, 1000);
}

function updateTimerUI(current, max) {
    const pct = (current / max) * 100;
    ui.timerBar.style.width = pct + '%';
    ui.timerLabel.textContent = `${current}s`;

    if (pct <= 25) {
        ui.timerBar.style.background = 'var(--error)';
    } else if (pct <= 50) {
        ui.timerBar.style.background = 'var(--warn)';
    } else {
        ui.timerBar.style.background = 'var(--accent2)';
    }
}

function clearPuzzleTimer() {
    if (puzzleTimerInterval) { clearInterval(puzzleTimerInterval); puzzleTimerInterval = null; }
    ui.timerContainer.style.display = 'none';
}

function clearAllTimers() {
    clearPuzzleTimer();
    if (globalTimerInterval) { clearInterval(globalTimerInterval); globalTimerInterval = null; }
}

function updateStatsBar() {
    ui.statScore.textContent = gameState.score;
    ui.statCorrect.textContent = gameState.correctAnswers;
    ui.statWrong.textContent = gameState.wrongAnswers;
    ui.statStreak.textContent = gameState.streak;
}

function setNumpadEnabled(enabled) {
    document.querySelectorAll('.num-btn').forEach(btn => {
        btn.disabled = !enabled;
        btn.style.opacity = enabled ? '1' : '0.4';
    });
}
</script>

</body>
</html>
