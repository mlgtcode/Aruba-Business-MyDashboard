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

$response = '';
$customers = [];
$debugInfo = '';

try {
    $token = $configuration->acquireToken();

    $url = $configuration->getUrl("/api/endusers", []);
    $headers = $configuration->getHeader();

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($config['errorReporting'] ?? false) {
        $debugInfo = '<strong>Headers:</strong> ' . htmlspecialchars(json_encode($headers)) . '<br>';
        $debugInfo .= '<strong>Response:</strong> ' . htmlspecialchars($response) . '<br>';
        $debugInfo .= '<strong>HTTP Code:</strong> ' . htmlspecialchars($httpCode) . '<br>';
    }

    curl_close($curl);

    $responseData = json_decode($response, true);

    $customers = $responseData['Object'] ?? [];
} catch (Exception $e) {
    $response = 'Error: ' . $e->getMessage();
}
?>
<div class="container mt-5">
    <h2 class="text-center">Customers</h2>
    <div class="mt-4">
        <a href="add_customer.php" class="btn btn-primary mb-3">Add New Customer</a>
        <button id="exportCsv" class="btn btn-success mb-3">Export as CSV</button>

        <?php if (!empty($debugInfo)): ?>
            <div class="alert alert-warning">
                <h5>Debug Information</h5>
                <?php echo $debugInfo; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($customers) && is_array($customers)): ?>
            <table id="customersTable" class="table table-bordered table-hover mt-4">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Username</th>
                        <th scope="col">Name</th>
                        <th scope="col">Surname</th>
                        <th scope="col">Tax Code</th>
                        <th scope="col">Address</th>
                        <th scope="col">Zip</th>
                        <th scope="col">City</th>
                        <th scope="col">Province</th>
                        <th scope="col">Nation</th>
                        <th scope="col">Subject Type</th>
                        <th scope="col">PEC Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['Username'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['Name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['Surname'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['TaxCode'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['Address'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['Zip'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['City'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['Province'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['Nation'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['SubjectType'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['PECEmail'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <pre><?php echo htmlspecialchars($response); ?></pre>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>
<script>
    document.getElementById('exportCsv').addEventListener('click', function () {
        const table = document.getElementById('customersTable');
        const rows = Array.from(table.querySelectorAll('tr'));
        const csvData = rows.map(row => Array.from(row.querySelectorAll('th, td')).map(cell => cell.textContent));
        const csv = Papa.unparse(csvData);
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'customers.csv';
        link.click();
    });
</script>
</body>
</html>