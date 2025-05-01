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
//function selectTransactionsByUserId($conn_back,$user_id) {
//    $sql = "SELECT * FROM transactions WHERE user_id = '$user_id'";
//    $result = $conn_back->query($sql);
//
//    $transactions = array();
//
//    if ($result->num_rows > 0) {
//        while ($row = $result->fetch_assoc()) {
//            $transactions[] = $row;
//        }
//    }
//
//    return $transactions;
//}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$per_page = 10;

$offset = ($page - 1) * $per_page;
$transactions = selectTransactionsByUserIdPaginated($conn_back, $user_id, $offset, $per_page);
$totalTransactions = countTransactionsByUserId($conn_back, $user_id);

// Generate HTML for new rows
ob_start();
foreach ($transactions as $transaction) {
    // Determine currency, type, status as before
    ?>
    <tr>
        <td><?= $transaction['reference_id'] ?></td>
        <td><?= $trans_currency . $transaction['amount'] ?></td>
        <td><?= $trans_type ?></td>
        <td><?= $trans_status ?></td>
        <td><?= $transaction['date_time'] ?></td>
    </tr>
    <?php
}
$rows = ob_get_clean();

$response = [
    'rows' => $rows,
    'hasMore' => ($page * $per_page) < $totalTransactions,
    'nextPage' => $page + 1
];

header('Content-Type: application/json');
echo json_encode($response);
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