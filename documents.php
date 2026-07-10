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
        $stmt = $pdo->query('SELECT * FROM documents ORDER BY created_at DESC');
        $docs = $stmt->fetchAll();
        foreach ($docs as &$doc) {
            if (!empty($doc['history'])) {
                $decoded = json_decode($doc['history'], true);
                $doc['history'] = $decoded === null ? [] : $decoded;
            } else {
                $doc['history'] = [];
            }
        }
        echo json_encode($docs);
        exit;
    }

    if ($method === 'POST') {
        $stmt = $pdo->prepare(
            'INSERT INTO documents (qr_key, control_number, document_type, title, employee, date_submitted, origin, current_office, destination_office, priority, status, assigned_to, created_by, description, history, created_at)
             VALUES (:qr_key, :control_number, :document_type, :title, :employee, :date_submitted, :origin, :current_office, :destination_office, :priority, :status, :assigned_to, :created_by, :description, :history, NOW())'
        );

        $stmt->execute([
            ':qr_key' => $input['qrKey'] ?? $input['qr_key'] ?? '',
            ':control_number' => $input['controlNumber'] ?? $input['control_number'] ?? '',
            ':document_type' => $input['type'] ?? $input['docType'] ?? '',
            ':title' => $input['title'] ?? '',
            ':employee' => $input['employee'] ?? '',
            ':date_submitted' => $input['dateSubmitted'] ?? $input['date_submitted'] ?? null,
            ':origin' => $input['origin'] ?? '',
            ':current_office' => $input['currentOffice'] ?? $input['current_office'] ?? '',
            ':destination_office' => $input['destinationOffice'] ?? $input['destination_office'] ?? null,
            ':priority' => $input['priority'] ?? 'Normal',
            ':status' => $input['status'] ?? 'Received',
            ':assigned_to' => $input['assigned'] ?? $input['assigned_to'] ?? null,
            ':created_by' => $input['createdBy'] ?? $input['created_by'] ?? null,
            ':description' => $input['description'] ?? '',
            ':history' => json_encode($input['history'] ?? []),
        ]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        exit;
    }

    if ($method === 'PUT') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Document id is required.']);
            exit;
        }

        $fields = [];
        $params = [':id' => $input['id']];

        $map = [
            'qrKey' => 'qr_key',
            'qr_key' => 'qr_key',
            'controlNumber' => 'control_number',
            'control_number' => 'control_number',
            'type' => 'document_type',
            'document_type' => 'document_type',
            'title' => 'title',
            'employee' => 'employee',
            'dateSubmitted' => 'date_submitted',
            'date_submitted' => 'date_submitted',
            'origin' => 'origin',
            'currentOffice' => 'current_office',
            'current_office' => 'current_office',
            'destinationOffice' => 'destination_office',
            'destination_office' => 'destination_office',
            'priority' => 'priority',
            'status' => 'status',
            'assigned' => 'assigned_to',
            'assigned_to' => 'assigned_to',
            'createdBy' => 'created_by',
            'created_by' => 'created_by',
            'description' => 'description',
            'history' => 'history',
        ];

        foreach ($map as $key => $column) {
            if (array_key_exists($key, $input)) {
                if ($key === 'history') {
                    $fields[] = 'history = :history';
                    $params[':history'] = json_encode($input['history'] ?: []);
                } else {
                    $paramKey = ':' . $column;
                    $fields[] = "$column = $paramKey";
                    $params[$paramKey] = $input[$key];
                }
            }
        }

        if (empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'Nothing to update.']);
            exit;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE documents SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($method === 'DELETE') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Document id is required.']);
            exit;
        }

        $stmt = $pdo->prepare('DELETE FROM documents WHERE id = :id');
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
