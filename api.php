<?php

class Configuration
{
    private $host;
    private $apiKey;
    private $userName;
    private $password;
    private $token;

    public function __construct($host, $apiKey, $userName, $password, $errorReporting = false)
    {
        $this->host = $host;
        $this->apiKey = $apiKey;
        $this->userName = $userName;
        $this->password = $password;
        $this->token = '';

        // Configure error reporting
        if ($errorReporting) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    public function acquireToken()
    {
        $resourcePath = "/auth/token";
        $url = $this->host . $resourcePath;

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization-Key: ' . $this->apiKey
        ];

        $postData = http_build_query([
            'grant_type' => 'password',
            'username' => $this->userName,
            'password' => $this->password
        ]);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        curl_close($curl);

        $tokenData = json_decode($response, true);

        if (isset($tokenData['access_token'])) {
            $this->token = $tokenData['access_token'];
        } else {
            throw new Exception('Failed to acquire token: ' . $response);
        }

        return $this->token;
    }

    public function getUrl($resourcePath, $params = [])
    {
        foreach ($params as $key => $value) {
            $resourcePath = str_replace($key, $value, $resourcePath);
        }

        return $this->host . $resourcePath;
    }

    public function getHeader()
    {
        if (empty($this->token)) {
            throw new Exception('Token is not set. Please acquire a token first.');
        }

        return [
            'Content-length: 0',
            'Content-type: application/json',
            'Authorization: Bearer ' . $this->token,
            'Authorization-Key: ' . $this->apiKey
        ];
    }
}

// Example usage:
// $config = new Configuration('https://api.arubabusiness.it', '#APIKEY#', '#USERNAME#', '#PASSWORD#', true);
// $token = $config->acquireToken();
// $url = $config->getUrl('/some/resource', ['{id}' => 123]);
// $headers = $config->getHeader();
?>