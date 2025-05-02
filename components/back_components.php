<?php
//Buttons


//Modals/Dialogs

//Tables

//Forms

//Notifications/Alerts

//Navigation Components

//Cards

function TransactionsCard($conn_back) {
    // grab the logged-in user
    $user_id = (int)($_SESSION['user_id'] ?? 0);

    // ─── utility functions ────────────────────────────────────────────────────
    function selectTransactionsByUserIdPaginated($conn, $user_id, $offset, $per_page) {
        $stmt = $conn->prepare("
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

    function countTransactionsByUserId($conn, $user_id) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total 
              FROM transactions 
             WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['total'];
    }

    // ─── AJAX / JSON response ─────────────────────────────────────────────────
    if (isset($_GET['page'])) {
        header('Content-Type: application/json; charset=utf-8');
        $page     = max(1, (int)$_GET['page']);
        $per_page = 10;
        $offset   = ($page - 1) * $per_page;

        $txs   = selectTransactionsByUserIdPaginated($conn_back, $user_id, $offset, $per_page);
        $total = countTransactionsByUserId($conn_back, $user_id);

        // styling maps
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
        foreach ($txs as $t) {
            $sym       = $currencies[$t['currency']] ?? '$';
            $typeCls   = $types[$t['transaction_type']] ?? 'text-primary';
            $statusCls = $statuses[$t['status']] ?? 'btn-outline-warning';

            $rows[] = [
                'reference_id' => $t['reference_id'],
                'amount'       => $sym . number_format($t['amount'], 2),
                'type'         => "<span class='{$typeCls}'>" . ucfirst($t['transaction_type']) . "</span>",
                'status'       => "<button class='btn btn-sm {$statusCls}'>" . ucfirst($t['status']) . "</button>",
                'date'         => date('M d, Y H:i', strtotime($t['date_time']))
            ];
        }

        echo json_encode([
            'success' => true,
            'rows'    => $rows,
            'hasMore' => ($page * $per_page) < $total
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─── initial page render ──────────────────────────────────────────────────
    $per_page     = 10;
    $init_offset  = 0;
    $initial_txns = selectTransactionsByUserIdPaginated($conn_back, $user_id, $init_offset, $per_page);
    $total        = countTransactionsByUserId($conn_back, $user_id);

    // same styling maps for HTML
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
            <?php foreach ($initial_txns as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['reference_id']) ?></td>
                    <td><?= ($currencies[$t['currency']] ?? '$') . number_format($t['amount'], 2) ?></td>
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

        <?php if ($total > $per_page): ?>
            <button id="loadMore" class="btn btn-primary mt-3" data-page="2">
                Load More
            </button>
        <?php endif; ?>
    </div>

    <script>
    document.getElementById('loadMore')?.addEventListener('click', function() {
      const btn  = this;
      const page = parseInt(btn.dataset.page, 10);

      btn.disabled  = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';

      fetch(window.location.pathname + `?page=${page}`)
        .then(res => res.json())
        .then(data => {
          if (!data.success) throw new Error('API failure');

          const tbody = document.querySelector('tbody');
          data.rows.forEach(r => {
            tbody.insertAdjacentHTML('beforeend', `
              <tr>
                <td>${r.reference_id}</td>
                <td>${r.amount}</td>
                <td>${r.type}</td>
                <td>${r.status}</td>
                <td><i class="bi bi-calendar-check-fill"></i> ${r.date}</td>
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
        })
        .catch(err => {
          console.error(err);
          btn.disabled  = false;
          btn.innerHTML = 'Error - Try Again';
        });
    });
    </script>
    <?php
}

// somewhere in your page template:

// end of TransactionsCard

//Avatar/Profile Picture