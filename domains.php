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

$zone = $_POST['domain'] ?? null;
$resourcePath = "/api/domains/dns/{zone}/details";

$response = '';

try {
    $token = $configuration->acquireToken();

    $params = [
        '{zone}' => $zone ?? '#ZONE#'
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
    <h2 class="text-center">Domain Details</h2>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="domain" class="form-label">Domain Name</label>
            <input type="text" class="form-control" id="domain" name="domain" placeholder="Enter Domain Name" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>
    <div class="mt-4">
        <h4>Response:</h4>
        <?php if (!empty($responseData) && is_array($responseData)): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Domain Details</h5>
                    <?php foreach ($responseData as $key => $value): ?>
                        <?php if ($key !== 'Records'): ?>
                            <p><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if (!empty($responseData['Records']) && is_array($responseData['Records'])): ?>
                <h5 class="mt-4">Records</h5>
                <button id="addRecord" class="btn btn-success mb-3">Add Record</button>
                <table class="table table-bordered table-hover mt-3" id="recordsTable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">DomainId</th>
                            <th scope="col">Name</th>
                            <th scope="col">Type</th>
                            <th scope="col">Content</th>
                            <th scope="col">Ttl</th>
                            <th scope="col">Prio</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responseData['Records'] as $record): ?>
                            <tr data-id="<?php echo htmlspecialchars($record['Id']); ?>">
                                <td><?php echo htmlspecialchars($record['Id']); ?></td>
                                <td><?php echo htmlspecialchars($record['DomainId']); ?></td>
                                <td contenteditable="false" class="editable" data-field="Name"><?php echo htmlspecialchars($record['Name']); ?></td>
                                <td contenteditable="false" class="editable" data-field="Type"><?php echo htmlspecialchars($record['Type']); ?></td>
                                <td contenteditable="false" class="editable" data-field="Content"><?php echo htmlspecialchars($record['Content']); ?></td>
                                <td contenteditable="false" class="editable" data-field="Ttl"><?php echo htmlspecialchars($record['Ttl']); ?></td>
                                <td contenteditable="false" class="editable" data-field="Prio"><?php echo htmlspecialchars($record['Prio'] ?? 'N/A'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-btn">Edit</button>
                                    <button class="btn btn-sm btn-success save-btn" disabled>Save</button>
                                    <button class="btn btn-sm btn-secondary cancel-btn" disabled>Cancel</button>
                                    <button class="btn btn-sm btn-danger delete-btn">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php else: ?>
            <p><?php echo htmlspecialchars($response); ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const recordsTable = document.getElementById('recordsTable');
    const addRecordButton = document.getElementById('addRecord');
    const currentDomainId = <?php echo json_encode($responseData['Id'] ?? 0); ?>;

    recordsTable.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-btn')) {
            const row = e.target.closest('tr');
            row.querySelectorAll('.editable').forEach(cell => {
                cell.contentEditable = true;
            });
            row.querySelector('.save-btn').disabled = false;
            row.querySelector('.cancel-btn').disabled = false;
            e.target.disabled = true;
        }
    });

    recordsTable.addEventListener('click', function (e) {
        if (e.target.classList.contains('save-btn')) {
            const row = e.target.closest('tr');
            const recordId = row.dataset.id;
            const updatedRecord = {};

            row.querySelectorAll('.editable').forEach(cell => {
                const field = cell.dataset.field;
                updatedRecord[field] = cell.textContent.trim();
            });

            updatedRecord.Id = recordId;

            if (!updatedRecord.Name || !updatedRecord.Type || !updatedRecord.Content) {
                alert('Name, Type, and Content are required fields.');
                return;
            }

            fetch('domainapi.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'update', record: updatedRecord })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Record updated successfully!');
                    row.querySelectorAll('.editable').forEach(cell => {
                        cell.contentEditable = false;
                    });
                    row.querySelector('.edit-btn').disabled = false;
                    row.querySelector('.cancel-btn').disabled = true;
                    e.target.disabled = true;
                } else {
                    alert('Failed to update record: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred: ' + error.message);
            });
        }
    });

    recordsTable.addEventListener('click', function (e) {
        if (e.target.classList.contains('cancel-btn')) {
            const row = e.target.closest('tr');
            row.querySelectorAll('.editable').forEach(cell => {
                cell.contentEditable = false;
            });
            row.querySelector('.edit-btn').disabled = false;
            row.querySelector('.save-btn').disabled = true;
            e.target.disabled = true;
        }
    });

    addRecordButton.addEventListener('click', function () {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>New</td>
            <td>${currentDomainId}</td>
            <td contenteditable="true" class="editable" data-field="Name"></td>
            <td contenteditable="true" class="editable" data-field="Type"></td>
            <td contenteditable="true" class="editable" data-field="Content"></td>
            <td contenteditable="true" class="editable" data-field="Ttl">3600</td>
            <td contenteditable="true" class="editable" data-field="Prio">0</td>
            <td>
                <button class="btn btn-sm btn-success save-btn">Save</button>
                <button class="btn btn-sm btn-secondary cancel-btn">Cancel</button>
            </td>
        `;
        recordsTable.querySelector('tbody').appendChild(newRow);
    });

    recordsTable.addEventListener('click', function (e) {
        if (e.target.classList.contains('save-btn')) {
            const row = e.target.closest('tr');
            const isNew = row.cells[0].textContent === 'New';
            const newRecord = {
                IdDomain: currentDomainId
            };

            row.querySelectorAll('.editable').forEach(cell => {
                const field = cell.dataset.field;
                newRecord[field] = cell.textContent.trim();
            });

            if (!newRecord.Name || !newRecord.Type || !newRecord.Content) {
                alert('Name, Type, and Content are required fields.');
                return;
            }

            if (isNew) {
                fetch('domainapi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'create', record: newRecord })
                })
                .then(response => response.text())
                .then(rawResponse => {
                    const data = JSON.parse(rawResponse);
                    if (data.success) {
                        alert('Record created successfully!');
                        row.cells[0].textContent = data.recordId || 'New ID';
                        row.querySelectorAll('.editable').forEach(cell => {
                            cell.contentEditable = false;
                        });
                        e.target.disabled = true;
                    } else {
                        alert('Failed to create record: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred: ' + error.message);
                });
            }
        }
    });

    recordsTable.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-btn')) {
            const row = e.target.closest('tr');
            const recordId = row.dataset.id;

            if (!recordId) {
                alert('Invalid record ID.');
                return;
            }

            if (confirm('Are you sure you want to delete this record?')) {
                fetch('domainapi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'delete', recordId: recordId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Record deleted successfully!');
                        row.remove();
                    } else {
                        alert('Failed to delete record: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred: ' + error.message);
                });
            }
        }
    });
});
</script>
</body>
</html>