<?php
session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}
include("header.php");
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">MyDashboard</h2>
    <div class="row">
        <div class="col-md-4">
            <a href="services.php" class="text-decoration-none">
                <div class="card text-center shadow-sm hover-box border-modern">
                    <div class="card-body">
                        <i class="fas fa-cogs fa-3x text-primary mb-3"></i>
                        <h5 class="card-title text-dark">Services</h5>
                        <p class="card-text text-muted">List available services.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="customers.php" class="text-decoration-none">
                <div class="card text-center shadow-sm hover-box border-modern">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                        <h5 class="card-title text-dark">Customers</h5>
                        <p class="card-text text-muted">View and create customers.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="domains.php" class="text-decoration-none">
                <div class="card text-center shadow-sm hover-box border-modern">
                    <div class="card-body">
                        <i class="fas fa-globe fa-3x text-info mb-3"></i>
                        <h5 class="card-title text-dark">Domains</h5>
                        <p class="card-text text-muted">View Domains and manage DNS settings.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 mt-4">
            <a href="pricelist.php" class="text-decoration-none">
                <div class="card text-center shadow-sm hover-box border-modern">
                    <div class="card-body">
                        <i class="fas fa-tags fa-3x text-warning mb-3"></i>
                        <h5 class="card-title text-dark">Pricelist</h5>
                        <p class="card-text text-muted">View and manage product and service pricing.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 mt-4">
            <a href="https://admin.arubabusiness.it/Dashboard/" target="_blank" class="text-decoration-none">
                <div class="card text-center shadow-sm hover-box border-modern">
                    <div class="card-body">
                        <i class="fas fa-sign-in-alt fa-3x text-primary mb-3"></i>
                        <h5 class="card-title text-dark">Aruba Business</h5>
                        <p class="card-text text-muted">Login to Aruba Business Dashboard.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 mt-4">
            <a href="logout.php" class="text-decoration-none">
                <div class="card text-center shadow-sm hover-box border-modern">
                    <div class="card-body">
                        <i class="fas fa-sign-out-alt fa-3x text-danger mb-3"></i>
                        <h5 class="card-title text-dark">Logout</h5>
                        <p class="card-text text-muted">Sign out of your account securely.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<footer class="text-center mt-5 py-3">
    <p class="mb-0" style="font-size: 0.9rem;">Powered by the Aruba Business Web API</p>
    <p style="font-size: 0.9rem;">
        <a href="https://github.com/mlgtcode/Aruba-Business-MyDashboard" target="_blank" class="text-decoration-none">
            View Source Code on GitHub
        </a>
    </p>
</footer>

<style>
    .hover-box:hover {
        transform: translateY(-5px);
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 15px rgba(39, 39, 39, 0.2);
    }

    .border-modern {
        border: 2px solid gray;
        border-radius: 10px;
    }

    .border-modern:hover {
        border-color: black;
    }

    footer {
        color: #6c757d;
        background: none;
    }

    footer a {
        color: #007bff;
    }

    footer a:hover {
        text-decoration: underline;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>