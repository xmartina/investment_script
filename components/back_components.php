<?php
//Buttons


//Modals/Dialogs

//Tables

//Forms

//Notifications/Alerts

//Navigation Components

//Cards
function TransactionsCard($conn_back, $user_id, $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;
    $transactions = selectTransactionsByUserIdPaginated($conn_back, $user_id, $offset, $per_page);
    $totalTransactions = countTransactionsByUserId($conn_back, $user_id);

    ?>
    <div class="card-body">
        <table class="table mb-0" data-show-toggle="true" id="dataTable">
            <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th data-breakpoints="xs">Transaction Type</th>
                <th>Status</th>
                <th>Date/Time</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $SingleTransactions):
                // Determine currency, type, and status (corrected to use 'status' field)
                switch ($SingleTransactions['currency']) {
                    // ... existing cases ...
                }
                switch ($SingleTransactions['transaction_type']) {
                    // ... existing cases ...
                }
                switch ($SingleTransactions['status']) { // Corrected to 'status'
                    case 'pending':
                        $trans_status = '<button class="btn btn-sm btn-outline-warning">Pending</button>';
                        break;
                    // ... other cases ...
                }
                ?>
                <tr>
                    <td><?= $SingleTransactions['reference_id'] ?></td>
                    <td><?= $trans_currency . $SingleTransactions['amount'] ?></td>
                    <td><?= $trans_type ?></td>
                    <td><?= $trans_status ?></td>
                    <td><?= $SingleTransactions['date_time'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (($page * $per_page) < $totalTransactions): ?>
            <button id="loadMore" class="btn btn-primary mt-3" data-page="<?= $page + 1 ?>" data-user-id="<?= $user_id ?>">
                Load More
            </button>
        <?php endif; ?>
    </div>
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/user/layout/transactions_js.php';?>
    <?php
}

// New functions for paginated data and count
function selectTransactionsByUserIdPaginated($conn, $user_id, $offset, $per_page) {
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date_time DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $user_id, $per_page, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function countTransactionsByUserId($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM transactions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

//Avatar/Profile Picture