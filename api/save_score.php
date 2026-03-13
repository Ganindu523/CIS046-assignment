<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

// Require authentication
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorised.']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

// Parse JSON body sent from JavaScript fetch()
$body = json_decode(file_get_contents('php://input'), true);

$correct   = (int) ($body['correct'] ?? 0);
$wrong     = (int) ($body['wrong'] ?? 0);
$timeTaken = (int) ($body['timeTaken'] ?? 0);
$difficulty = in_array($body['difficulty'] ?? '', ['normal', 'hard', 'timed'])
    ? $body['difficulty']
    : 'normal';

// Calculate score:

$score = max(0, ($correct * 10) - ($wrong * 3));
if ($difficulty === 'timed' && $timeTaken < 60) {
    $score += 20; // speed bonus
}

// Save to database using prepared statement (prevents SQL injection)
$db = getDB();
$stmt = $db->prepare(
    "INSERT INTO scores (user_id, score, correct_answers, wrong_answers, time_taken_seconds, difficulty)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->execute([$user['id'], $score, $correct, $wrong, $timeTaken, $difficulty]);

// Return new score and user's updated rank
$rankStmt = $db->prepare(
    "SELECT COUNT(*) + 1 as rank_position
     FROM (
         SELECT user_id, MAX(score) as best
         FROM scores GROUP BY user_id
     ) top
     WHERE best > (SELECT MAX(score) FROM scores WHERE user_id = ?)"
);
$rankStmt->execute([$user['id']]);
$rank = $rankStmt->fetch();

echo json_encode([
    'success'  => true,
    'score'    => $score,
    'rank'     => $rank['rank_position'] ?? 1,
    'message'  => "Score saved! You earned $score points.",
]);
