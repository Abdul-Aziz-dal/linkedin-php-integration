<?php
require_once '../app/LinkedInService.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['access_token'], $_SESSION['linkedin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$postText = trim($_POST['post_text'] ?? '');

if ($postText === '') {
    echo json_encode(['success' => false, 'message' => 'Post text is required']);
    exit;
}

// Handle uploaded files
$imageFilePath = null;
$videoFilePath = null;

try {
    if (!empty($_FILES['image_file']['tmp_name'])) {
        $imageFilePath = $_FILES['image_file']['tmp_name'];
    }

    if (!empty($_FILES['video_file']['tmp_name'])) {
        $videoFilePath = $_FILES['video_file']['tmp_name'];
    }

    $linkedin = new LinkedInClient();
    $authorUrn = "urn:li:person:{$_SESSION['linkedin_id']}";

    list($status, $response) = $linkedin->shareOnLinkedIn(
        $_SESSION['access_token'],
        $authorUrn,
        $postText,
        $imageFilePath,
        $videoFilePath
    );

    echo json_encode([
        'success' => $status === 201,
        'status' => $status,
        'response' => json_decode($response, true)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage()
    ]);
}
