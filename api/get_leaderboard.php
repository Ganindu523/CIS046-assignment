<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorised.']);
    exit;
}

$db = getDB();

// Top 10 players by best single score
$topStmt = $db->query(
    "SELECT u.username, u.avatar_color, MAX(s.score) as best_score,
            COUNT(s.id) as games_played,
            SUM(s.correct_answers) as total_correct
     FROM scores s
     JOIN users u ON u.id = s.user_id
     GROUP BY u.id, u.username, u.avatar_color
     ORDER BY best_score DESC
     LIMIT 10"
);
$leaderboard = $topStmt->fetchAll();

// Current user's personal stats
$myStmt = $db->prepare(
    "SELECT MAX(score) as best_score, AVG(score) as avg_score,
            COUNT(id) as games_played, SUM(correct_answers) as total_correct
     FROM scores WHERE user_id = ?"
);
$myStmt->execute([$user['id']]);
$myStats = $myStmt->fetch();

// Current user's rank
$rankStmt = $db->prepare(
    "SELECT COUNT(*) + 1 as rank_position
     FROM (SELECT user_id, MAX(score) as best FROM scores GROUP BY user_id) top
     WHERE best > COALESCE((SELECT MAX(score) FROM scores WHERE user_id = ?), 0)"
);
$rankStmt->execute([$user['id']]);
$myRank = $rankStmt->fetch();

// Recent score history for chart (last 10 games)
$historyStmt = $db->prepare(
    "SELECT score, correct_answers, played_at
     FROM scores WHERE user_id = ?
     ORDER BY played_at DESC LIMIT 10"
);
$historyStmt->execute([$user['id']]);
$history = array_reverse($historyStmt->fetchAll());

echo json_encode([
    'success'     => true,
    'leaderboard' => $leaderboard,
    'myStats'     => $myStats,
    'myRank'      => $myRank['rank_position'] ?? 1,
    'history'     => $history,
]);
