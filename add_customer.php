<?php
session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

include("api.php");
include("header.php");
$config = include("configure.php");

$addCustomerResponse = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $newCustomerData = [
        'Username' => $_POST['Username'] ?? '',
        'Name' => $_POST['Name'] ?? '',
        'Surname' => $_POST['Surname'] ?? '',
        'TaxCode' => $_POST['TaxCode'] ?? '',
        'Address' => $_POST['Address'] ?? '',
        'Zip' => $_POST['Zip'] ?? '',
        'City' => $_POST['City'] ?? '',
        'Province' => $_POST['Province'] ?? '',
        'VATCode' => $_POST['VATCode'] ?? '',
        'Email' => $_POST['Email'] ?? '',
        'CompanyName' => $_POST['CompanyName'] ?? '',
        'Phone' => $_POST['Phone'] ?? '',
        'MobilePhone' => $_POST['MobilePhone'] ?? '',
        'Fax' => $_POST['Fax'] ?? '',
        'Nation' => $_POST['Nation'] ?? '',
        'SubjectType' => (int) ($_POST['SubjectType'] ?? 0),
        'PECEmail' => $_POST['PECEmail'] ?? ''
    ];

    try {
        $tokenUrl = "https://api.arubabusiness.it/auth/token";
        $tokenHeaders = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization-Key: ' . $config['apiKey']
        ];
        $tokenPayload = http_build_query([
            'grant_type' => 'password',
            'username' => $config['userName'],
            'password' => $config['password']
        ]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $tokenHeaders);
        curl_setopt($curl, CURLOPT_URL, $tokenUrl);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $tokenPayload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $tokenResponse = curl_exec($curl);
        $tokenHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($tokenHttpCode !== 200) {
            throw new Exception('Failed to retrieve access token. Response: ' . $tokenResponse);
        }

        $tokenData = json_decode($tokenResponse, true);
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            throw new Exception('Access token is missing in the response.');
        }

        $url = "https://api.arubabusiness.it/api/endusers";
        $headers = [
            'Authorization-Key: ' . $config['apiKey'],
            'Authorization: bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($newCustomerData));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $addCustomerResponse = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 201) {
            $addCustomerResponse = 'Customer added successfully!';
        } elseif ($httpCode === 400) {
            $addCustomerResponse = 'Error: Customer already exists.';
        } elseif ($httpCode === 403) {
            $addCustomerResponse = 'Error: Invalid access token.';
        } elseif ($httpCode === 422) {
            $responseDetails = json_decode($addCustomerResponse, true);
            $validationErrors = $responseDetails['details'] ?? 'Validation failed.';
            $addCustomerResponse = 'Error: ' . json_encode($validationErrors);
        } else {
            $addCustomerResponse = 'Error: Unexpected response from the server.';
        }
    } catch (Exception $e) {
        $addCustomerResponse = 'Error: ' . $e->getMessage();
    }
}
?>
<div class="container mt-5">
    <h2 class="text-center">Add New Customer</h2>
    <div class="mt-4">
        <?php if (!empty($addCustomerResponse)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($addCustomerResponse); ?></div>
        <?php endif; ?>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="Username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="Username" name="Username" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="Name" name="Name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Surname" class="form-label">Surname</label>
                    <input type="text" class="form-control" id="Surname" name="Surname" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="TaxCode" class="form-label">Tax Code</label>
                    <input type="text" class="form-control" id="TaxCode" name="TaxCode">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="Address" name="Address">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Zip" class="form-label">Zip</label>
                    <input type="text" class="form-control" id="Zip" name="Zip">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="City" class="form-label">City</label>
                    <input type="text" class="form-control" id="City" name="City">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Province" class="form-label">Province</label>
                    <input type="text" class="form-control" id="Province" name="Province">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Nation" class="form-label">Nation</label>
                    <input type="text" class="form-control" id="Nation" name="Nation">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="SubjectType" class="form-label">Subject Type</label>
                    <input type="number" class="form-control" id="SubjectType" name="SubjectType" min="0" max="7">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="PECEmail" class="form-label">PEC Email</label>
                    <input type="email" class="form-control" id="PECEmail" name="PECEmail">
                </div>
            </div>
            <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
        </form>
    </div>
</div>
</body>
</html>