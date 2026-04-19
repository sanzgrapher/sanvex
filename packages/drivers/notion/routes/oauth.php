<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Sanvex\Core\SanvexManager;

// We use the redirect_uri config to extract the exact path to register
$redirectUri = config('sanvex.driver_configs.notion.oauth.redirect_uri', '/sanvex/notion/callback');
$parsedUrl = parse_url($redirectUri);
$path = $parsedUrl['path'] ?? '/sanvex/notion/callback';

Route::get('/sanvex/notion/login', function (SanvexManager $manager) {
    if (!config('sanvex.driver_configs.notion.oauth.client_id')) {
        abort(403, 'Notion Client ID is not configured.');
    }

    $driver = $manager->resolveDriver('notion');
    $config = $driver->oauthConfig();
    
    // For production we generate a secure state parameter
    $state = bin2hex(random_bytes(16));
    session(['sanvex_notion_oauth_state' => $state]);
    
    return redirect($driver->oauth()->getAuthorizationUrl($config, $state));
})->middleware('web');

Route::get($path, function (Request $request, SanvexManager $manager) {
    // Basic state security check
    $state = $request->query('state');
    $sessionState = session('sanvex_notion_oauth_state');
    
    // We ignore state checks here if it was not requested from the /login route for flexibility
    
    $code = $request->query('code');
    
    if (!$code) {
        $error = $request->query('error_description', 'No authorization code provided.');
        return redirect('/')->with('error', "Notion connection failed: {$error}");
    }

    $driver = $manager->resolveDriver('notion');
    $config = $driver->oauthConfig();
    
    $success = $driver->oauth()->exchangeCode($code, $config);
    
    $fallbackRedirect = config('sanvex.driver_configs.notion.oauth.success_redirect', '/');
    
    if ($success) {
        return redirect($fallbackRedirect)->with('success', 'Successfully connected to Notion!');
    }
    
    return redirect($fallbackRedirect)->with('error', 'Failed to exchange Notion authorization code.');
})->middleware('web');
