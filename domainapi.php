<?php
session_start();

if (empty($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include("api.php");
$config = include("configure.php");

$configuration = new Configuration(
    $config['host'],
    $config['apiKey'],
    $config['userName'],
    $config['password'],
    $config['errorReporting'] ?? false
);

try {
    if (ob_get_length()) {
        ob_clean();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;

    if ($action === 'create') {
        $record = $input['record'] ?? null;
        if (!$record || !isset($record['IdDomain'], $record['Name'], $record['Type'], $record['Content'])) {
            throw new Exception('Invalid record data. IdDomain, Name, Type, and Content are required.');
        }

        $url = $configuration->getUrl("/api/domains/dns/record");
        $token = $configuration->acquireToken();
        $headers = [
            'Authorization-Key: ' . $config['apiKey'],
            'Authorization: bearer ' . $token,
            'Content-Type: application/json'
        ];

        $payload = [
            'IdDomain' => $record['IdDomain'],
            'Name' => $record['Name'],
            'Type' => $record['Type'],
            'Content' => $record['Content']
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            echo json_encode(['success' => true, 'message' => 'Record created successfully', 'recordId' => $record['Id']]);
        } else {
            $apiError = json_decode($response, true);
            $errorMessage = $apiError['message'] ?? 'Unknown error';
            throw new Exception('Failed to create record: ' . $errorMessage);
        }
    } elseif ($action === 'delete') {
        $recordId = $input['recordId'] ?? null;

        if (!$recordId) {
            throw new Exception('Invalid record ID. A valid record ID is required.');
        }

        $url = $configuration->getUrl("/api/domains/dns/record/{$recordId}");
        $token = $configuration->acquireToken();
        $headers = [
            'Authorization-Key: ' . $config['apiKey'],
            'Authorization: bearer ' . $token,
            'Content-Type: application/json'
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
        } else {
            $apiError = json_decode($response, true);
            $errorMessage = $apiError['message'] ?? 'Unknown error';
            throw new Exception('Failed to delete record: ' . $errorMessage);
        }
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}