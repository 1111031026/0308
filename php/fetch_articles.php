<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 3; // 每頁顯示 3 篇文章
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$response = [
    'articles' => [],
    'totalPages' => 0
];

if (!empty($search)) {
    $sql = "SELECT * FROM article WHERE Category = 'sdg14' AND Title LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $searchTerm = "%" . $conn->real_escape_string($search) . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $searchTerm, $perPage, $offset);
} else {
    $sql = "SELECT * FROM article WHERE Category = 'sdg14' ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $perPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$articles = [];

while ($row = $result->fetch_assoc()) {
    $articles[] = [
        'ArticleID' => $row['ArticleID'],
        'Title' => htmlspecialchars($row['Title']),
        'Description' => htmlspecialchars($row['Description']),
        'ImageURL' => $row['ImageURL'] ? '../' . htmlspecialchars($row['ImageURL']) : '../img/ocean.jpg'
    ];
}

// 計算總頁數
$countSql = "SELECT COUNT(*) as total FROM article WHERE Category = 'sdg14'" . (!empty($search) ? " AND Title LIKE ?" : "");
if (!empty($search)) {
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("s", $searchTerm);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
} else {
    $countResult = $conn->query($countSql);
}

$totalArticles = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalArticles / $perPage);

$response['articles'] = $articles;
$response['totalPages'] = $totalPages;

echo json_encode($response);
$stmt->close();
$conn->close();
?>