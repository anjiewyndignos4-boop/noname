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

try {
    $pdo = getDbConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id, first_name, middle_name, last_name, email, username, role, active, no_middle_name, created_at, updated_at FROM users ORDER BY created_at DESC');
        $users = $stmt->fetchAll();
        echo json_encode($users);
        exit;
    }

    if ($method === 'POST') {
        $stmt = $pdo->prepare(
            'INSERT INTO users (first_name, middle_name, last_name, email, username, password, role, active, no_middle_name, created_at)
             VALUES (:first_name, :middle_name, :last_name, :email, :username, :password, :role, :active, :no_middle_name, NOW())'
        );

        $stmt->execute([
            ':first_name' => $input['firstName'] ?? $input['first_name'] ?? '',
            ':middle_name' => ($input['noMiddleName'] ?? $input['no_middle_name'] ?? false) ? '' : ($input['middleName'] ?? $input['middle_name'] ?? ''),
            ':last_name' => $input['lastName'] ?? $input['last_name'] ?? '',
            ':email' => $input['email'] ?? '',
            ':username' => $input['username'] ?? '',
            ':password' => password_hash($input['password'] ?? '', PASSWORD_DEFAULT),
            ':role' => $input['role'] ?? 'employee',
            ':active' => isset($input['active']) ? (int)$input['active'] : 1,
            ':no_middle_name' => (!empty($input['noMiddleName']) || !empty($input['no_middle_name'])) ? 1 : 0,
        ]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        exit;
    }

    if ($method === 'PUT') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User id is required.']);
            exit;
        }

        $fields = [];
        $params = [':id' => $input['id']];

        if (isset($input['firstName'])) { $fields[] = 'first_name = :first_name'; $params[':first_name'] = $input['firstName']; }
        if (isset($input['middleName'])) { $fields[] = 'middle_name = :middle_name'; $params[':middle_name'] = $input['middleName']; }
        if (isset($input['noMiddleName'])) { $fields[] = 'no_middle_name = :no_middle_name'; $params[':no_middle_name'] = $input['noMiddleName'] ? 1 : 0; }
        if (isset($input['lastName'])) { $fields[] = 'last_name = :last_name'; $params[':last_name'] = $input['lastName']; }
        if (isset($input['email'])) { $fields[] = 'email = :email'; $params[':email'] = $input['email']; }
        if (isset($input['username'])) { $fields[] = 'username = :username'; $params[':username'] = $input['username']; }
        if (isset($input['role'])) { $fields[] = 'role = :role'; $params[':role'] = $input['role']; }
        if (isset($input['active'])) { $fields[] = 'active = :active'; $params[':active'] = $input['active'] ? 1 : 0; }
        if (!empty($input['password'])) { $fields[] = 'password = :password'; $params[':password'] = password_hash($input['password'], PASSWORD_DEFAULT); }

        if (empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'Nothing to update.']);
            exit;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($method === 'DELETE') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User id is required.']);
            exit;
        }
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $input['id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
