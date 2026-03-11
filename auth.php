<?php

require_once __DIR__ . '/db.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('COOKIE_NAME', 'puzzle_auth_token');
define('COOKIE_LIFETIME', 60 * 60 * 24 * 7);



 
  @param string $username
  @param string $email
  @param string $plainPassword
  @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
 
function registerUser(string $username, string $email, string $plainPassword): array {
    $db = getDB();

  
    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['success' => false, 'message' => 'Username must be 3-50 characters.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }
    if (strlen($plainPassword) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }

    
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username or email already taken.'];
    }

   
    $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

    
    $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899'];
    $avatarColor = $colors[array_rand($colors)];

    $stmt = $db->prepare(
        "INSERT INTO users (username, email, password_hash, avatar_color) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$username, $email, $passwordHash, $avatarColor]);
    $userId = (int) $db->lastInsertId();

    return ['success' => true, 'message' => 'Account created successfully!', 'user_id' => $userId];
}


 
  @param string $username
  @param string $plainPassword
  @return array ['success' => bool, 'message' => string]

function loginUser(string $username, string $plainPassword): array {
    $db = getDB();

    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

   
    if (!$user || !password_verify($plainPassword, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }


    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + COOKIE_LIFETIME);

    
    $stmt = $db->prepare(
        "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $user['id'],
        $token,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        $expiresAt
    ]);


    setcookie(COOKIE_NAME, $token, [
        'expires'  => time() + COOKIE_LIFETIME,
        'path'     => '/',
        'httponly' => true,   
        'samesite' => 'Lax',  
    ]);

    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['avatar_color'] = $user['avatar_color'];

   
    $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

    return ['success' => true, 'message' => 'Login successful!'];
}


 @return array|null User data array or null if not authenticated
 
function getCurrentUser(): ?array {
   
    if (!empty($_SESSION['user_id'])) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, email, avatar_color FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }

   
    $token = $_COOKIE[COOKIE_NAME] ?? null;
    if (!$token) return null;

    $db = getDB();

    
    $stmt = $db->prepare(
        "SELECT u.id, u.username, u.email, u.avatar_color
         FROM user_sessions s
         JOIN users u ON u.id = s.user_id
         WHERE s.session_token = ? AND s.expires_at > NOW()"
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
      
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['avatar_color'] = $user['avatar_color'];
        return $user;
    }

    return null;
}


function requireLogin(): array {
    $user = getCurrentUser();
    if (!$user) {
        header('Location: index.php?msg=Please log in to continue.');
        exit;
    }
    return $user;
}


function logoutUser(): void {
    $token = $_COOKIE[COOKIE_NAME] ?? null;

    if ($token) {
        
        $db = getDB();
        $db->prepare("DELETE FROM user_sessions WHERE session_token = ?")->execute([$token]);
    }

   
    setcookie(COOKIE_NAME, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

  
    $_SESSION = [];
    session_destroy();
}
