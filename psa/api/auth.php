<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../db_connection.php';

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$action = $input['action'] ?? '';

if ($action === 'login') {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit;
    }

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE (username = :user OR email = :user) AND active = 1 LIMIT 1');
        $stmt->execute([':user' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
            exit;
        }

        echo json_encode(['success' => true, 'user' => $user]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'register') {
    $firstName = trim($input['firstName'] ?? $input['first_name'] ?? '');
    $middleName = trim($input['middleName'] ?? $input['middle_name'] ?? '');
    $noMiddleName = !empty($input['noMiddleName']) || !empty($input['no_middle_name']) ? 1 : 0;
    $lastName = trim($input['lastName'] ?? $input['last_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $role = trim($input['role'] ?? 'employee');

    if (!$firstName || !$lastName || !$email || !$username || !$password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required registration fields.']);
        exit;
    }

    try {
        $pdo = getDbConnection();
        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username OR email = :email');
        $check->execute([':username' => $username, ':email' => $email]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
            exit;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO users (first_name, middle_name, last_name, email, username, password, role, active, no_middle_name, created_at)
             VALUES (:first_name, :middle_name, :last_name, :email, :username, :password, :role, 1, :no_middle_name, NOW())'
        );

        $stmt->execute([
            ':first_name' => $firstName,
            ':middle_name' => $noMiddleName ? '' : $middleName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':username' => $username,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role,
            ':no_middle_name' => $noMiddleName,
        ]);

        echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action.']);
