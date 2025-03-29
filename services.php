<?php
session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

include("api.php");
include("header.php");
$config = include("configure.php");

$configuration = new Configuration(
    $config['host'],
    $config['apiKey'],
    $config['userName'],
    $config['password'],
    $config['errorReporting'] ?? false
);

$serviceId = $_POST['serviceId'] ?? null;
$resourcePath = "/api/services/{idService}/detail/{deep}";

$response = '';

try {
    $token = $configuration->acquireToken();

    $params = [
        '{deep}' => "false",
        '{idService}' => $serviceId ?? '#IDSERVICE#'
    ];

    $url = $configuration->getUrl($resourcePath, $params);
    $headers = $configuration->getHeader();

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);

    $responseData = json_decode($response, true);
} catch (Exception $e) {
    $responseData = [];
    $response = 'Error: ' . $e->getMessage();
}
?>
<div class="container mt-5">
    <h2 class="text-center">Services</h2>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="serviceId" class="form-label">Service ID</label>
            <input type="text" class="form-control" id="serviceId" name="serviceId" placeholder="Enter Service ID (optional)">
        </div>
        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>
    <div class="mt-4">
        <h4>Response:</h4>
        <?php if (!empty($responseData) && is_array($responseData)): ?>
            <?php if ($serviceId): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Service Details</h5>
                        <?php foreach ($responseData as $key => $value): ?>
                            <p><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <table class="table table-bordered table-hover mt-4">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Domain</th>
                            <th scope="col">Article</th>
                            <th scope="col">State</th>
                            <th scope="col">Activation Date</th>
                            <th scope="col">Expiration Date</th>
                            <th scope="col">Client Name</th>
                            <th scope="col">Renewal Price</th>
                            <th scope="col">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responseData as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['Id']); ?></td>
                                <td><?php echo htmlspecialchars($item['Domain'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['Article']); ?></td>
                                <td><?php echo htmlspecialchars($item['State']); ?></td>
                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($item['ActivationDate']))); ?></td>
                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($item['ExpirationDate']))); ?></td>
                                <td><?php echo htmlspecialchars($item['ClientName']); ?></td>
                                <td><?php echo htmlspecialchars($item['RenewalPrice']); ?></td>
                                <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php else: ?>
            <pre><?php echo htmlspecialchars($response); ?></pre>
        <?php endif; ?>
    </div>
</div>
</body>
</html>