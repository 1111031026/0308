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
    $sql = "SELECT a.*, u.Username FROM article a LEFT JOIN user u ON a.UserID = u.UserID WHERE a.Category = 'sdg15' AND (a.Title LIKE ? OR u.Username LIKE ?) ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
    $searchTerm = "%" . $conn->real_escape_string($search) . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $searchTerm, $searchTerm, $perPage, $offset);
} else {
    $sql = "SELECT a.*, u.Username FROM article a LEFT JOIN user u ON a.UserID = u.UserID WHERE a.Category = 'sdg15' ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
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
        'ImageURL' => $row['ImageURL'] ? '../' . htmlspecialchars($row['ImageURL']) : '../img/mountain.jpg',
        'Username' => isset($row['Username']) ? htmlspecialchars($row['Username']) : '未知作者'
    ];
}

// 計算總頁數
$countSql = "SELECT COUNT(*) as total FROM article a LEFT JOIN user u ON a.UserID = u.UserID WHERE a.Category = 'sdg15'" . (!empty($search) ? " AND (a.Title LIKE ? OR u.Username LIKE ?)" : "");
if (!empty($search)) {
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("ss", $searchTerm, $searchTerm);
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