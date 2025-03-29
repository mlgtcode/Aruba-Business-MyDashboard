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

$resourcePath = "/api/pricelist";

$response = '';
$pricelistItems = [];
$pricelistName = 'Unnamed Pricelist';

try {
    $token = $configuration->acquireToken();

    $url = $configuration->getUrl($resourcePath);
    $headers = $configuration->getHeader();

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);

    $responseData = json_decode($response, true);

    $pricelistItems = $responseData['Object']['PricelistItems'] ?? [];
    $pricelistName = $responseData['Object']['Name'] ?? 'Unnamed Pricelist';
} catch (Exception $e) {
    $responseData = [];
    $response = 'Error: ' . $e->getMessage();
}
?>
  <div class="container mt-4">
    <h1 class="mb-4">Pricelist: <?php echo htmlspecialchars($pricelistName); ?></h1>
    <p>Note: All prices are in <strong>EURO</strong>.</p>
    <div class="mb-3">
      <button id="applySurchargeBtn" class="btn btn-secondary me-2">Apply Surcharge</button>
      <button id="exportBtn" class="btn btn-primary">Export to CSV</button>
    </div>
    <?php if (!empty($pricelistItems) && is_array($pricelistItems)): ?>
      <table id="pricelistTable" class="table table-striped table-bordered">
        <thead class="table-dark">
          <tr>
            <th>Id</th>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Service Name</th>
            <th>Service Type</th>
            <th>Price</th>
            <th>Renewal Price</th>
            <th>Full Price</th>
            <th>Full Renewal Price</th>
            <th>Month Duration</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pricelistItems as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['Id'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($item['ProductId'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($item['ProductName'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($item['ServiceName'] == "*" ? "N/A" : ($item['ServiceName'] ?? 'N/A')); ?></td>
              <td><?php echo htmlspecialchars($item['ServiceType'] ?? 'N/A'); ?></td>
              <td class="price" data-baseprice="<?php echo htmlspecialchars($item['Price'] ?? 0); ?>">
                <?php echo '€ ' . number_format($item['Price'] ?? 0, 2); ?>
              </td>
              <td><?php echo '€ ' . number_format($item['RenewalPrice'] ?? 0, 2); ?></td>
              <td class="full-price" data-baseprice="<?php echo htmlspecialchars($item['FullPrice'] ?? 0); ?>">
                <?php echo '€ ' . number_format($item['FullPrice'] ?? 0, 2); ?>
              </td>
              <td class="full-renewal-price" data-baseprice="<?php echo htmlspecialchars($item['FullRenewalPrice'] ?? 0); ?>">
                <?php echo '€ ' . number_format($item['FullRenewalPrice'] ?? 0, 2); ?>
              </td>
              <td><?php echo htmlspecialchars($item['MonthDuration'] ?? 'N/A'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert alert-warning" role="alert">
        No pricelist items found.
      </div>
    <?php endif; ?>
  </div>

  <script>
    document.getElementById('applySurchargeBtn').addEventListener('click', function () {
      const surcharge = parseFloat(prompt('Enter surcharge percentage (e.g., 10 for 10%):'));
      if (isNaN(surcharge)) {
        alert('Invalid percentage. Please enter a valid number.');
        return;
      }

      document.querySelectorAll('.price, .full-price, .full-renewal-price').forEach(function (cell) {
        const basePrice = parseFloat(cell.getAttribute('data-baseprice'));
        if (!isNaN(basePrice)) {
          const newPrice = basePrice * (1 + surcharge / 100);
          cell.textContent = '€ ' + newPrice.toFixed(2);
        }
      });
    });

    document.getElementById('exportBtn').addEventListener('click', function () {
      const rows = document.querySelectorAll('#pricelistTable tr');
      const csv = Array.from(rows)
        .map(row => Array.from(row.querySelectorAll('td, th'))
          .map(cell => `"${cell.innerText.replace(/"/g, '""')}"`)
          .join(','))
        .join('\n');

      const blob = new Blob([csv], { type: 'text/csv' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'pricelist.csv';
      link.click();
    });
  </script>
</body>
</html>