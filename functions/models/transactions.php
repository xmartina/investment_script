<?php
// Function to select all transactions
function selectAllTransactions($conn_back) {
    $sql = "SELECT * FROM transactions";
    $result = $conn_back->query($sql);

    $transactions = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
    }

    return $transactions;
}

// Function to select transactions by user_id
function selectTransactionsByUserId($conn_back,$user_id) {
    $sql = "SELECT * FROM transactions WHERE user_id = '$user_id'";
    $result = $conn_back->query($sql);

    $transactions = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
    }

    return $transactions;
}

// Function to update a transaction
function updateTransaction($conn_back,$transaction_id, $new_status) {
    $sql = "UPDATE transactions SET status = '$new_status' WHERE transaction_id = '$transaction_id'";
    $result = $conn_back->query($sql);

    if ($result === TRUE) {
        $response = true;
    } else {
        $response = false;
    }

    return $response;
}

// Example usage:

// Select all transactions
$all_transactions = selectAllTransactions($conn_back);
foreach ($all_transactions as $transaction) {
    echo "Transaction ID: " . $transaction['transaction_id'] . "<br>";
    echo "Type: " . $transaction['transaction_type'] . "<br>";
    echo "Amount: " . $transaction['amount'] . "<br>";
    echo "Status: " . $transaction['status'] . "<br>";
    echo "Date & Time: " . $transaction['date_time'] . "<br><br>";
}

// Update a transaction
//$transaction_id_to_update = 'TXN123456';
//$new_status = 'Completed';
//$update_result = updateTransaction($transaction_id_to_update, $new_status);
//
//if ($update_result) {
//    echo "Transaction updated successfully.";
//} else {
//    echo "Failed to update transaction.";
//}
?>