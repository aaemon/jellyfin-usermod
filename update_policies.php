<?php

// Set content type
header('Content-Type: application/json');

// Jellyfin API configuration
$jellyfinUrl = 'http://192.168.123.18:8096';
$apiKey = '38b3c72f84ee46b6bff66273d52523f9';

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
            'timeout' => 30
        ]
    ]);
    
    $result = file_get_contents($url, false, $context);
    if ($result === false) {
        throw new Exception('Failed to connect to Jellyfin API');
    }
    
    return json_decode($result, true);
}

try {
    // Get all users first
    $users = jellyfinRequest($jellyfinUrl . '/Users');
    
    $results = [];
    
    foreach ($users as $user) {
        // Skip admin users to be safe
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
        
        // Update user policy - POST /Users/{userId}/Policy
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
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to update user policies',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}

?>