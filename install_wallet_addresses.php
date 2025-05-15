<?php
// Installation helper for wallet addresses feature
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Addresses Installation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fc;
            padding: 40px 0;
        }
        .install-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            font-weight: bold;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        .steps {
            counter-reset: step-counter;
            list-style-type: none;
            padding-left: 0;
        }
        .steps li {
            position: relative;
            padding-left: 40px;
            margin-bottom: 20px;
        }
        .steps li:before {
            content: counter(step-counter);
            counter-increment: step-counter;
            position: absolute;
            left: 0;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #4e73df;
            color: white;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container install-container">
        <div class="card mb-4">
            <div class="card-header py-3">
                <h4 class="m-0">Wallet Addresses Management Installation</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    This page will help you install and configure the wallet addresses management feature.
                </div>
                
                <h5 class="mt-4 mb-3">Installation Steps:</h5>
                <ol class="steps">
                    <li>
                        <h6>Create Database Table</h6>
                        <p>First, we need to create the wallet_addresses table in your database.</p>
                        <a href="/admin/create_wallet_addresses_table" class="btn btn-primary">Create Database Table</a>
                    </li>
                    <li>
                        <h6>Manage Wallet Addresses</h6>
                        <p>Once the table is created, you can manage your wallet addresses from the admin panel.</p>
                        <a href="/admin/wallet_addresses" class="btn btn-primary">Manage Wallet Addresses</a>
                    </li>
                    <li>
                        <h6>Test Deposit Form</h6>
                        <p>After setting up your wallet addresses, you can test the deposit form to ensure everything is working correctly.</p>
                        <a href="/user/deposit" class="btn btn-primary">Test Deposit Form</a>
                    </li>
                </ol>
                
                <div class="alert alert-warning mt-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Make sure you're logged in as an admin before proceeding with the installation steps.
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="/admin" class="btn btn-secondary">Return to Admin Dashboard</a>
                    <a href="/admin/wallet_addresses" class="btn btn-primary">Skip to Wallet Management</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html> 