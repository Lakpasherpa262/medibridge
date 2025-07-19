<?php
require_once 'scripts/connect.php';

header('Content-Type: application/json');

try {
    $request = $_POST;

    $columns = [
        0 => 'id',
        1 => 'first_name',
        2 => 'email',
        3 => 'phone',
        4 => 'role',
        5 => 'verification'
    ];

    // Base query
    $sql = "SELECT SQL_CALC_FOUND_ROWS id, first_name, last_name, email, phone, role, verification FROM users";
    $where = " WHERE 1=1";
    $params = [];

    // Search filter
    if (!empty($request['search'])) {
        $search = $request['search'];
        $where .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Role filter
    if (!empty($request['role'])) {
        $where .= " AND role = :role";
        $params[':role'] = $request['role'];
    }

    // Ordering
    $order = "";
    if (isset($request['order']) && isset($columns[$request['order'][0]['column']])) {
        $orderBy = $columns[$request['order'][0]['column']];
        $dir = $request['order'][0]['dir'];
        $order = " ORDER BY $orderBy $dir";
    }

    // Pagination
    $limit = "";
    if (isset($request['start']) && $request['length'] != -1) {
        $limit = " LIMIT " . intval($request['start']) . ", " . intval($request['length']);
    }

    // Execute query
    $query = $sql . $where . $order . $limit;
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total records
    $totalRecords = $db->query("SELECT FOUND_ROWS()")->fetchColumn();

    echo json_encode([
        "draw" => intval($request['draw']),
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "data" => $data
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>