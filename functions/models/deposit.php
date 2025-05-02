<?php

//// grab & sanitize inputs
//$user_id      = intval($_SESSION['user_id']);
//$method       = mysqli_real_escape_string($conn_back, $_POST['payment_method'] ?? '');
//$amount       = floatval($_POST['deposit_amount'] ?? 0);
//$currency     = mysqli_real_escape_string($conn_back, $_POST['currency'] ?? '');
//$reference_id = mysqli_real_escape_string($conn_back, $_POST['transactionId'] ?? '');
//$wallet_addr  = mysqli_real_escape_string($conn_back, $_POST['wallet_address'] ?? '');
//
//// handle file upload
//$proof_path = '';
//if (!empty($_FILES['paymentProof']['name']) && $_FILES['paymentProof']['error'] === UPLOAD_ERR_OK) {
//    $ext       = pathinfo($_FILES['paymentProof']['name'], PATHINFO_EXTENSION);
//    $name      = uniqid('proof_') . ".$ext";
//    $uploadDir = __DIR__ . '/uploads/payment_proofs/';
//    if (!is_dir($uploadDir)) {
//        mkdir($uploadDir, 0755, true);
//    }
//    move_uploaded_file($_FILES['paymentProof']['tmp_name'], $uploadDir . $name);
//    $proof_path = 'uploads/payment_proofs/' . $name;
//}
//
//mysqli_begin_transaction($conn_back);
//
//// 1) deposit_requests
//$sql1 = "
//  INSERT INTO deposit_requests
//    (user_id, payment_method, amount, currency, reference_id, payment_proof, status)
//  VALUES
//    ($user_id, '$method', $amount, '$currency', '$reference_id', '$proof_path', 'pending')
//";
//if (!mysqli_query($conn_back, $sql1)) {
//    mysqli_rollback($conn_back);
//    die("Error inserting deposit request: " . mysqli_error($conn_back));
//}
//
//$deposit_id = mysqli_insert_id($conn_back);
//
//// 2) transactions
//$txn_id      = 'txn_' . uniqid();
//$description = 'User deposit request';
//$sql2 = "
//  INSERT INTO transactions
//    (transaction_id, transaction_type, reference_id, amount, currency,
//     status, date_time, description, to_address, user_id, deposit_request_id)
//  VALUES
//    ('$txn_id', 'deposit', '$reference_id', $amount, '$currency',
//     'pending', NOW(), '$description', '$wallet_addr', $user_id, $deposit_id)
//";
//if (!mysqli_query($conn_back, $sql2)) {
//    mysqli_rollback($conn_back);
//    die("Error inserting transaction: " . mysqli_error($conn_back));
//}
//
//mysqli_commit($conn_back);
//
//// success
//header("Location: success.php?msg=deposit_pending");
//exit;