<?php

// grab & sanitize inputs
$user_id       = intval($_SESSION['user_id']);
$method        = mysqli_real_escape_string($conn_back, $_POST['payment_method']);
$amount        = floatval($_POST['deposit_amount']);
$currency      = mysqli_real_escape_string($conn_back, $_POST['currency']);
$reference_id  = mysqli_real_escape_string($conn_back, $_POST['transactionId']);
$wallet_addr   = mysqli_real_escape_string($conn_back, $_POST['wallet_address']);

// handle file upload
$proof_path = null;
if (isset($_FILES['paymentProof']) && $_FILES['paymentProof']['error'] === UPLOAD_ERR_OK) {
    $ext  = pathinfo($_FILES['paymentProof']['name'], PATHINFO_EXTENSION);
    $name = uniqid('proof_') . ".$ext";
    $uploadDir = __DIR__ . '/uploads/payment_proofs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    move_uploaded_file($_FILES['paymentProof']['tmp_name'], $uploadDir . $name);
    $proof_path = 'uploads/payment_proofs/' . $name;
}

mysqli_begin_transaction($conn_back);

try {
    // 1) insert into deposit_requests
    $stmt = mysqli_prepare($conn_back, "
        INSERT INTO deposit_requests
          (user_id, payment_method, amount, currency, reference_id, payment_proof, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");
    mysqli_stmt_bind_param($stmt, "isdsss",
        $user_id,
        $method,
        $amount,
        $currency,
        $reference_id,
        $proof_path
    );
    mysqli_stmt_execute($stmt);
    $deposit_id = mysqli_insert_id($conn_back);
    mysqli_stmt_close($stmt);

    // 2) insert into transactions
    $txn_id      = uniqid('txn_');
    $description = 'User deposit request';
    $stmt2 = mysqli_prepare($conn_back, "
        INSERT INTO transactions
          (transaction_id, transaction_type, reference_id, amount, currency,
           status, date_time, description, to_address, user_id, deposit_request_id)
        VALUES (?, 'deposit', ?, ?, ?, 'pending', NOW(), ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt2, "ssdsssii",
        $txn_id,
        $reference_id,
        $amount,
        $currency,
        $description,
        $wallet_addr,
        $user_id,
        $deposit_id
    );
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    mysqli_commit($conn_back);

    header("Location: success.php?msg=deposit_pending");
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn_back);
    die("Error processing deposit: " . $e->getMessage());
}