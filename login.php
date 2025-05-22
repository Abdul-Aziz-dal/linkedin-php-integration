<?php
declare(strict_types=1);
session_start();

try {
    $config = require 'config.php';

    if (
        empty($config['client_id']) ||
        empty($config['redirect_uri']) ||
        empty($config['scopes'])
    ) {
        throw new Exception('Missing configuration values. Please check client_id, redirect_uri, and scopes.');
    }

    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth2state'] = $state;

    $params = [
        'response_type' => 'code',
        'client_id' => $config['client_id'],
        'redirect_uri' => $config['redirect_uri'],
        'scope' => $config['scopes'],
        'state' => $state
    ];

    $authUrl = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);
    header("Location: $authUrl");
    exit;

} catch (Throwable $e) {
    error_log('LinkedIn OAuth Init Error: ' . $e->getMessage());
    http_response_code(500);
    echo 'An error occurred while initiating LinkedIn authentication. Please try again later.';
    exit;
}
