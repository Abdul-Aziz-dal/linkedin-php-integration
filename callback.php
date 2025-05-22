<?php
session_start();
require_once __DIR__ . '/app/LinkedInService.php';

try {
    // CSRF protection: validate state
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
        unset($_SESSION['oauth2state']);
        throw new Exception('Invalid state parameter (possible CSRF attempt)');
    }

    if (!isset($_GET['code'])) {
        throw new Exception('Authorization code not found in the response');
    }

    $code = $_GET['code'];
    $client = new LinkedInClient();

    $tokenData = $client->fetchAccessToken($code);
    if (!$tokenData || !isset($tokenData['access_token'])) {
        throw new Exception('Failed to obtain access token from LinkedIn 1');
    }

    $_SESSION['token_data']   = $tokenData;
    $_SESSION['access_token'] = $tokenData['access_token'];

    $userInfo = $client->getData('https://api.linkedin.com/v2/userinfo');
    if (!$userInfo || !isset($userInfo['sub'])) {
        throw new Exception('Failed to retrieve LinkedIn user information');
    }

    $_SESSION['linkedin_id'] = $userInfo['sub'];
    $_SESSION['userData']    = $userInfo;

    header('Location: dashboard.php');
    exit;

} catch (Exception $e) {
    error_log('LinkedIn callback error: ' . $e->getMessage());
    echo "<h3>Oops! Something went wrong during LinkedIn login.</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
