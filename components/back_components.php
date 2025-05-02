<?php
//Buttons


//Modals/Dialogs

//Tables

//Forms

//Notifications/Alerts

//Navigation Components

//Cards

function TransactionsCard($conn_back, $user_id) {
    // Utility functions
    function selectTransactionsByUserIdPaginated($conn_back, $user_id, $offset, $per_page) {
        $stmt = $conn_back->prepare("
            SELECT * 
            FROM transactions 
            WHERE user_id = ? 
            ORDER BY date_time DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $user_id, $per_page, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function countTransactionsByUserId($conn_back, $user_id) {
        $stmt = $conn_back->prepare("
            SELECT COUNT(*) AS total 
            FROM transactions 
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['total'];
    }

    // ─── AJAX / API Request Handler ───────────────────────────────────────────
    if (isset($_GET['page'])) {
        header('Content-Type: application/json');

        $page       = max(1, (int)$_GET['page']);
        $user_id    = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        $per_page   = 10;
        $offset     = ($page - 1) * $per_page;

        $transactions     = selectTransactionsByUserIdPaginated($conn_back, $user_id, $offset, $per_page);
        $totalTransactions = countTransactionsByUserId($conn_back, $user_id);

        if ($transactions === false) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch transactions.'
            ]);
            exit;
        }

        // Prepare response rows
        $currencies = [
            'USD'=>'$', 'EUR'=>'€', 'GBP'=>'£','JPY'=>'¥',
            'CAD'=>'C$','AUD'=>'A$','NGN'=>'₦','CHF'=>'CHF',
            'CNY'=>'¥','INR'=>'₹','ZAR'=>'R','NZD'=>'NZ$'
        ];
        $types = [
            'withdraw'=>'text-danger',
            'deposit'=>'text-primary',
            'investment'=>'text-secondary',
            'stake'=>'text-warning'
        ];
        $statuses = [
            'pending'=>'btn-outline-warning',
            'approved'=>'btn-outline-success',
            'running'=>'btn-outline-primary',
            'declined'=>'btn-outline-danger'
        ];

        $rows = [];
        foreach ($transactions as $t) {
            $symbol = $currencies[$t['currency']] ?? '$';
            $typeClass = $types[$t['transaction_type']] ?? 'text-primary';
            $statusClass = $statuses[$t['status']] ?? 'btn-outline-warning';

            $rows[] = [
                'reference_id' => $t['reference_id'],
                'amount'       => $symbol . number_format($t['amount'], 2),
                'type'         => "<span class='{$typeClass}'>" . ucfirst($t['transaction_type']) . "</span>",
                'status'       => "<button class='btn btn-sm {$statusClass}'>" . ucfirst($t['status']) . "</button>",
                'date'         => date('M d, Y H:i', strtotime($t['date_time']))
            ];
        }

        echo json_encode([
            'success' => true,
            'rows'    => $rows,
            'hasMore' => ($page * $per_page) < $totalTransactions
        ]);
        exit;
    }

    // ─── Normal Page Request ───────────────────────────────────────────────────
    // (Replace with actual session ID retrieval)

    $per_page            = 10;
    $initial_offset      = 0;
    $initial_transactions = selectTransactionsByUserIdPaginated($conn_back, $user_id, $initial_offset, $per_page);
    $totalTransactions   = countTransactionsByUserId($conn_back, $user_id);

    // Reuse the same styling arrays for the initial render
    $currencies = [
        'USD'=>'$', 'EUR'=>'€', 'GBP'=>'£','JPY'=>'¥',
        'CAD'=>'C$','AUD'=>'A$','NGN'=>'₦','CHF'=>'CHF',
        'CNY'=>'¥','INR'=>'₹','ZAR'=>'R','NZD'=>'NZ$'
    ];
    $types = [
        'withdraw'=>'text-danger',
        'deposit'=>'text-primary',
        'investment'=>'text-secondary',
        'stake'=>'text-warning'
    ];
    $statuses = [
        'pending'=>'btn-outline-warning',
        'approved'=>'btn-outline-success',
        'running'=>'btn-outline-primary',
        'declined'=>'btn-outline-danger'
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
            <?php foreach ($initial_transactions as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['reference_id']) ?></td>
                    <td>
                        <?= ($currencies[$t['currency']] ?? '$') . number_format($t['amount'], 2) ?>
                    </td>
                    <td class="<?= $types[$t['transaction_type']] ?? 'text-primary' ?>">
                        <?= ucfirst($t['transaction_type']) ?>
                    </td>
                    <td>
                        <button class="btn btn-sm <?= $statuses[$t['status']] ?? 'btn-outline-warning' ?>">
                            <?= ucfirst($t['status']) ?>
                        </button>
                    </td>
                    <td>
                        <i class="bi bi-calendar-check-fill"></i>
                        <?= date('M d, Y H:i', strtotime($t['date_time'])) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalTransactions > $per_page): ?>
            <button id="loadMore"
                    class="btn btn-primary mt-3"
                    data-page="2"
                    data-user-id="<?= $user_id ?>">
                Load More
            </button>
        <?php endif; ?>
    </div>

    <script>
    document.getElementById('loadMore')?.addEventListener('click', function() {
        const btn    = this;
        const page   = parseInt(btn.dataset.page, 10);
        const userId = parseInt(btn.dataset.userId, 10);

        btn.disabled    = true;
        btn.innerHTML   = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';

        fetch(`?page=${page}&user_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.querySelector('tbody');
                    data.rows.forEach(row => {
                        tbody.insertAdjacentHTML('beforeend', `
                            <tr>
                                <td>${row.reference_id}</td>
                                <td>${row.amount}</td>
                                <td>${row.type}</td>
                                <td>${row.status}</td>
                                <td><i class="bi bi-calendar-check-fill"></i> ${row.date}</td>
                            </tr>
                        `);
                    });
                    if (data.hasMore) {
                        btn.dataset.page = page + 1;
                        btn.disabled     = false;
                        btn.innerHTML    = 'Load More';
                    } else {
                        btn.remove();
                    }
                } else {
                    console.error('Error:', data.message);
                    btn.disabled  = false;
                    btn.innerHTML = 'Error - Try Again';
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                btn.disabled  = false;
                btn.innerHTML = 'Error - Try Again';
            });
    });
    </script>
    <?php
}
// end of TransactionsCard

//Avatar/Profile Picture