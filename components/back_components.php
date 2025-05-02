<?php
//Buttons


//Modals/Dialogs

//Tables

//Forms

//Notifications/Alerts

//Navigation Components

//Cards

function TransactionsCard($conn_back) {
    // Utility functions
    function selectTransactionsByUserIdPaginated($conn_back, $user_id, $offset, $per_page) {
        $stmt = $conn_back->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date_time DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $user_id, $per_page, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function countTransactionsByUserId($conn_back, $user_id) {
        $stmt = $conn_back->prepare("SELECT COUNT(*) AS total FROM transactions WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    // AJAX Request Handler
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

        $transactions = selectTransactionsByUserIdPaginated($conn_back, $user_id, $offset, $per_page);
        $totalTransactions = countTransactionsByUserId($conn_back, $user_id);

        if ($transactions === false) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch transactions.'
            ]);
            exit;
        }

        $rows = [];
        foreach ($transactions as $transaction) {
            $currencies = [
                'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
                'CAD' => 'C$', 'AUD' => 'A$', 'NGN' => '₦', 'CHF' => 'CHF',
                'CNY' => '¥', 'INR' => '₹', 'ZAR' => 'R', 'NZD' => 'NZ$'
            ];
            $trans_currency = $currencies[$transaction['currency']] ?? '$';

            $types = [
                'withdraw' => 'text-danger',
                'deposit' => 'text-primary',
                'investment' => 'text-secondary',
                'stake' => 'text-warning'
            ];
            $trans_type_class = $types[$transaction['transaction_type']] ?? 'text-primary';
            $trans_type_text = ucfirst($transaction['transaction_type']);

            $statuses = [
                'pending' => 'btn-outline-warning',
                'approved' => 'btn-outline-success',
                'running' => 'btn-outline-primary',
                'declined' => 'btn-outline-danger'
            ];
            $status_class = $statuses[$transaction['status']] ?? 'btn-outline-warning';
            $status_text = ucfirst($transaction['status']);

            $rows[] = [
                'reference_id' => $transaction['reference_id'],
                'amount' => $trans_currency . number_format($transaction['amount'], 2),
                'type' => "<span class='$trans_type_class'>$trans_type_text</span>",
                'status' => "<button class='btn btn-sm $status_class'>$status_text</button>",
                'date' => date('M d, Y H:i', strtotime($transaction['date_time']))
            ];
        }

        echo json_encode([
            'success' => true,
            'rows' => $rows,
            'hasMore' => ($page * $per_page) < $totalTransactions
        ]);
        exit;
    }

    // Normal Page Request
    $user_id = 1; // Replace with session value if available
    $initial_page = 1;
    $initial_offset = 0;
    $per_page = 10;
    $initial_transactions = selectTransactionsByUserIdPaginated($conn_back, $user_id, $initial_offset, $per_page);
    $totalTransactions = countTransactionsByUserId($conn_back, $user_id);

    $currencies = [
        'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
        'CAD' => 'C$', 'AUD' => 'A$', 'NGN' => '₦', 'CHF' => 'CHF',
        'CNY' => '¥', 'INR' => '₹', 'ZAR' => 'R', 'NZD' => 'NZ$'
    ];

    $types = [
        'withdraw' => 'text-danger',
        'deposit' => 'text-primary',
        'investment' => 'text-secondary',
        'stake' => 'text-warning'
    ];

    $statuses = [
        'pending' => 'btn-outline-warning',
        'approved' => 'btn-outline-success',
        'running' => 'btn-outline-primary',
        'declined' => 'btn-outline-danger'
    ];
    ?>
    <div class="card-body">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Status</th>
                <th>Date/Time</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($initial_transactions as $transaction): ?>
                <tr>
                    <td><?= $transaction['reference_id'] ?></td>
                    <td><?= $currencies[$transaction['currency']] ?? '$' ?><?= number_format($transaction['amount'], 2) ?></td>
                    <td class="<?= $types[$transaction['transaction_type']] ?? 'text-primary' ?>">
                        <?= ucfirst($transaction['transaction_type']) ?>
                    </td>
                    <td>
                        <button class="btn btn-sm <?= $statuses[$transaction['status']] ?? 'btn-outline-warning' ?>">
                            <?= ucfirst($transaction['status']) ?>
                        </button>
                    </td>
                    <td>
                        <i class="bi bi-calendar-check-fill"></i>
                        <?= date('M d, Y H:i', strtotime($transaction['date_time'])) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($totalTransactions > 10): ?>
            <button id="loadMore" class="btn btn-primary mt-3"
                    data-page="2"
                    data-user-id="<?= $user_id ?>"
                    data-offset="<?= $per_page ?>">
                Load More
            </button>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('loadMore')?.addEventListener('click', function() {
            const button = this;
            const page = parseInt(button.dataset.page);
            const userId = parseInt(button.dataset.userId);
            const offset = parseInt(button.dataset.offset);
            const perPage = 10;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';

            fetch(`?page=${page}&user_id=${userId}&per_page=${perPage}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.querySelector('tbody');
                        data.rows.forEach(row => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${row.reference_id}</td>
                                    <td>${row.amount}</td>
                                    <td>${row.type}</td>
                                    <td>${row.status}</td>
                                    <td><i class="bi bi-calendar-check-fill"></i> ${row.date}</td>
                                </tr>
                            `;
                        });

                        if (data.hasMore) {
                            button.dataset.page = page + 1;
                            button.dataset.offset = offset + perPage;
                            button.disabled = false;
                            button.innerHTML = 'Load More';
                        } else {
                            button.remove();
                        }
                    } else {
                        console.error('Error:', data.message);
                        button.disabled = false;
                        button.innerHTML = 'Error - Try Again';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.disabled = false;
                    button.innerHTML = 'Error - Try Again';
                });
        });
    </script>
    <?php
} // end of TransactionsCard

//Avatar/Profile Picture