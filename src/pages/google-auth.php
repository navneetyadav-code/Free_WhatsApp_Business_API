<?php

declare(strict_types=1);

if (current_user()) {
    redirect_to('index.php?page=dashboard');
}

$clientId = app_config('google.client_id');
$clientSecret = app_config('google.client_secret');
$redirectUri = public_url('index.php?page=google-auth');

if (!$clientId || !$clientSecret) {
    exit('Google OAuth is not configured.');
}

// Handle Google Callback
if (isset($_GET['code'])) {
    $state = $_GET['state'] ?? '';
    
    // 1. Verify State Token (CSRF Protection)
    if (empty($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
        flash('error', 'Invalid security state. Please try again.');
        redirect_to('index.php?page=login');
    }
    unset($_SESSION['oauth_state']);

    $code = $_GET['code'];

    // 2. Exchange Code for Access Token
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code',
        'code' => $code,
    ]));
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $tokenData = json_decode((string) $response, true);
    if ($status !== 200 || empty($tokenData['access_token'])) {
        flash('error', 'Failed to authenticate with Google.');
        redirect_to('index.php?page=login');
    }

    $accessToken = $tokenData['access_token'];

    // 3. Get User Info
    $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $userInfo = json_decode((string) $response, true);
    if ($status !== 200 || empty($userInfo['email'])) {
        flash('error', 'Failed to retrieve user information from Google.');
        redirect_to('index.php?page=login');
    }

    $email = mb_strtolower(trim($userInfo['email']));
    $name = trim((string) ($userInfo['name'] ?? 'Google User'));

    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // User exists, log them in
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        redirect_to('index.php?page=dashboard');
    } else {
        // New user, register them
        $randomPassword = bin2hex(random_bytes(16)); // Secure random password
        $phone = ''; // Google doesn't provide phone by default in this scope
        
        try {
            // Using the existing register_user function from Auth.php
            $credentials = register_user($name, $email, $phone, $randomPassword);
            flash('success', 'Account created successfully via Google.');
            redirect_to('index.php?page=dashboard');
        } catch (Throwable $e) {
            flash('error', 'Failed to create account. Email might already be registered.');
            redirect_to('index.php?page=login');
        }
    }
}

// If error from Google
if (isset($_GET['error'])) {
    flash('error', 'Google authentication failed: ' . htmlspecialchars($_GET['error']));
    redirect_to('index.php?page=login');
}

// Initiate Google OAuth Flow
$_SESSION['oauth_state'] = bin2hex(random_bytes(32));

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['oauth_state'],
    'access_type' => 'online',
    'prompt' => 'select_account'
]);

header('Location: ' . $authUrl);
exit;
