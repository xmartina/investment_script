<?php
//Buttons


//Modals/Dialogs

//Tables

//Forms

//Notifications/Alerts

//Navigation Components

//Cards

<?php
function TransactionsCard($conn_back) {
    // Helper functions inside the main function
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

    // Handle AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

        $transactions = selectTransactionsByUserIdPaginated($conn_back, $user_id, $offset, $per_page);
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

        $rows = [];
        foreach ($transactions as $transaction) {
            $trans_currency = $currencies[$transaction['currency']] ?? '$';
            $trans_type_class = $types[$transaction['transaction_type']] ?? 'text-primary';
            $status_class = $statuses[$transaction['status']] ?? 'btn-outline-warning';

            $rows[] = [
                'reference_id' => $transaction['reference_id'],
                'amount' => $trans_currency . number_format($transaction['amount'], 2),
                'type' => "<span class='$trans_type_class'>" . ucfirst($transaction['transaction_type']) . "</span>",
                'status' => "<button class='btn btn-sm $status_class'>" . ucfirst($transaction['status']) . "</button>",
                'date' => date('M d, Y H:i', strtotime($transaction['date_time']))
            ];
        }

        echo json_encode([
            'rows' => $rows,
            'hasMore' => ($page * $per_page) < $totalTransactions
        ]);
        exit;
    }

    // Main display
    $user_id = 1; // Replace with dynamic user ID
    $per_page = 10;
    $transactions = selectTransactionsByUserIdPaginated($conn_back, $user_id, 0, $per_page);
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
            <tbody id="transactionRows">
            <?php foreach ($transactions as $transaction): ?>
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
                    <td><i class="bi bi-calendar-check-fill"></i> <?= date('M d, Y H:i', strtotime($transaction['date_time'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalTransactions > $per_page): ?>
            <button id="loadMore" class="btn btn-primary mt-3"
                    data-page="2"
                    data-user-id="<?= $user_id ?>">
                Load More
            </button>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const button = document.getElementById('loadMore');
            if (!button) return;

            button.addEventListener('click', function () {
                const page = this.dataset.page;
                const userId = this.dataset.userId;
                const button = this;

                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';

                fetch(`?page=${page}&user_id=${userId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.getElementById('transactionRows');
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
                            button.dataset.page = parseInt(page) + 1;
                            button.disabled = false;
                            button.innerHTML = 'Load More';
                        } else {
                            button.remove();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        button.disabled = false;
                        button.innerHTML = 'Error - Try Again';
                    });
            });
        });
    </script>

<?php } ?>


//Avatar/Profile Picture