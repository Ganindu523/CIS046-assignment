<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

require_once __DIR__ . '/../auth.php';


$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorised. Please log in.']);
    exit;
}


 @return array|null 

function fetchPuzzleFromAPI(): ?array {

    $apiUrl = 'http://marcconrad.com/uob/banana/api.php?out=json';

 
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,      
        CURLOPT_TIMEOUT        => 10,        
        CURLOPT_FOLLOWLOCATION => true,     
        CURLOPT_SSL_VERIFYPEER => false,    
        CURLOPT_USERAGENT      => 'MathPuzzleTrainer/1.0 (CIS046-3 Assignment)',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

  
    if ($curlError) {
        error_log("External API cURL error: " . $curlError);
        return null;
    }

  
    if ($httpCode !== 200) {
        error_log("External API returned HTTP $httpCode");
        return null;
    }

 
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['question'], $data['solution'])) {
        error_log("External API returned invalid JSON: " . $response);
        return null;
    }

    return $data; 
}


$puzzle = fetchPuzzleFromAPI();

if (!$puzzle) {
    http_response_code(503);
    echo json_encode(['error' => 'Could not load puzzle. Please try again.']);
    exit;
}


echo json_encode([
    'success'  => true,
    'imageUrl' => $puzzle['question'],
    'solution' => (int) $puzzle['solution'],
]);
