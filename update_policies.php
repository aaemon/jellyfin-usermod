<?php

// Simple CLI tool to update Jellyfin user permissions
// Usage: php update_policies.php

// Load environment variables from .env file
function loadEnv($file) {
    if (!file_exists($file)) {
        echo "Error: .env file not found\n";
        exit(1);
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) == 2) {
            $_ENV[trim($parts[0])] = trim($parts[1]);
        }
    }
}

loadEnv(__DIR__ . '/.env');

// Jellyfin API configuration
$jellyfinUrl = $_ENV['JELLYFIN_URL'] ?? '';
$apiKey = $_ENV['JELLYFIN_API_KEY'] ?? '';

if (empty($jellyfinUrl) || empty($apiKey)) {
    echo json_encode([
        'error' => 'Configuration error',
        'message' => 'JELLYFIN_URL and JELLYFIN_API_KEY must be set in .env file',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

// Function to make HTTP requests to Jellyfin API
function jellyfinRequest($url, $method = 'GET', $data = null) {
    global $apiKey;
    
    $headers = [
        'X-Emby-Token: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $data ? json_encode($data) : null,
            'timeout' => 30,
            'ignore_errors' => true
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        throw new Exception('Failed to connect to Jellyfin API at ' . $url);
    }
    
    return json_decode($result, true);
}

try {
    // Get all users
    $users = jellyfinRequest($jellyfinUrl . '/Users');
    
    if (!is_array($users)) {
        throw new Exception('Invalid response from Jellyfin API');
    }
    
    $results = [];
    
    foreach ($users as $user) {
        // Skip admin users for safety
        if ($user['Policy']['IsAdministrator'] ?? false) {
            $results[] = [
                'userId' => $user['Id'],
                'name' => $user['Name'],
                'status' => 'skipped',
                'reason' => 'Administrator user - skipped for safety'
            ];
            continue;
        }
        
        // Get current policy and update the four specified permissions
        $policy = $user['Policy'];
        
        // Disable the 4 specified permissions
        $policy['EnableLiveTvAccess'] = false;
        $policy['EnableLiveTvManagement'] = false;
        $policy['EnableVideoPlaybackTranscoding'] = false;
        $policy['ForceRemoteSourceTranscoding'] = false;
        
        // Update user policy
        try {
            $updateUrl = $jellyfinUrl . '/Users/' . $user['Id'] . '/Policy';
            jellyfinRequest($updateUrl, 'POST', $policy);
            
            $results[] = [
                'userId' => $user['Id'],
                'name' => $user['Name'],
                'status' => 'updated',
                'reason' => 'Successfully disabled Live TV access, Live TV management, Video transcoding, and Force remote transcoding'
            ];
        } catch (Exception $e) {
            $results[] = [
                'userId' => $user['Id'],
                'name' => $user['Name'],
                'status' => 'error',
                'reason' => $e->getMessage()
            ];
        }
    }
    
    $successCount = count(array_filter($results, function($r) { 
        return $r['status'] === 'updated'; 
    }));
    
    echo json_encode([
        'success' => true,
        'message' => "Updated permissions for {$successCount} users",
        'results' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT) . "\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to update user policies',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT) . "\n";
    exit(1);
}
