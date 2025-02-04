<?php
// Disable any potential output buffering
ob_clean();

// Fetch the prompt from GET parameter
$prompt = $_GET['prompt'] ?? null;

// Validate prompt
if (empty($prompt)) {
    http_response_code(400);
    die(json_encode([
        'status' => 'error',
        'message' => 'Prompt is required'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// Construct the API URL
$baseUrl = 'https://imgen.duck.mom/prompt/';
$encodedPrompt = urlencode($prompt);
$apiUrl = $baseUrl . $encodedPrompt;

try {
    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // Execute the request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    }

    // Get HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Get content type
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    // Close cURL session
    curl_close($ch);

    // Check if request was successful
    if ($httpCode == 200) {
        // Set headers to output image directly
        header("Content-Type: $contentType");
        header("Cache-Control: public, max-age=86400"); // 24-hour caching
        header("Pragma: cache");

        // Output the image
        echo $response;
        exit;
    } else {
        throw new Exception("API request failed with status code: $httpCode");
    }

} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}
?>
