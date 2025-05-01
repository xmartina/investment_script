<?php
//Buttons


//Modals/Dialogs

//Tables

//Forms

//Notifications/Alerts

//Navigation Components

//Cards
function TransactionsCard($conn_back, $user_id) {
    // determine current page
    $page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // fetch paginated rows
    $sql = "SELECT * FROM transactions 
            WHERE user_id = ? 
            ORDER BY date_time DESC 
            LIMIT ? OFFSET ?";
    $stmt = $conn_back->prepare($sql);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // fetch total count
    $cntSql = "SELECT COUNT(*) AS c FROM transactions WHERE user_id = ?";
    $cstmt  = $conn_back->prepare($cntSql);
    $cstmt->bind_param("i", $user_id);
    $cstmt->execute();
    $totalCount = (int)$cstmt->get_result()->fetch_assoc()['c'];
    $totalPages = (int)ceil($totalCount / $limit);
    ?>

    <div class="card-body">
        <table class="table mb-0" id="dataTable">
            <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th>Transaction Type</th>
                <th>Status</th>
                <th>Date/Time</th>
            </tr>
            </thead>
            <tbody>
            <!-- AJAX will inject rows here -->
            </tbody>
        </table>

        <nav class="mt-3">
            <ul class="pagination" id="pagination"></ul>
        </nav>
    </div>

    <script>
    (function($){
      let currentPage = <?= $page ?>, totalPages = <?= $totalPages ?>;

      function loadPage(page) {
        $.get('', { page: page })
         .done(function(html){
           // extract new rows and new pagination from the returned HTML
           let newTBody = $(html).find('#dataTable tbody').html();
           let newPager = $(html).find('#pagination').html();
           $('#dataTable tbody').html(newTBody);
           $('#pagination').html(newPager);
           currentPage = page;
         });
      }

      function renderPagination() {
        let pg = '';
        pg += `<li class="page-item ${currentPage===1?'disabled':''}">
                 <a class="page-link" href="#" data-page="${currentPage-1}">Prev</a>
               </li>`;
        for (let p=1; p<= totalPages; p++) {
          pg += `<li class="page-item ${p===currentPage?'active':''}">
                   <a class="page-link" href="#" data-page="${p}">${p}</a>
                 </li>`;
        }
        pg += `<li class="page-item ${currentPage===totalPages?'disabled':''}">
                 <a class="page-link" href="#" data-page="${currentPage+1}">Next</a>
               </li>`;
        $('#pagination').html(pg);
      }

      // render rows into table body (initial and on navigation)
      function renderRows() {
        let html = '';
        <?php foreach ($rows as $tx):
            // currency
            switch ($tx['currency']) {
                case 'USD': $sym='$'; break; case 'EUR': $sym='€'; break;
                case 'GBP': $sym='£'; break; case 'JPY': $sym='¥'; break;
                case 'NGN': $sym='₦'; break; default: $sym='$';
            }
            // type
            switch ($tx['transaction_type']) {
                case 'withdraw':   $typeHtml = "<p class='mb-0 text-danger'>Withdrawal</p>"; break;
                case 'deposit':    $typeHtml = "<p class='mb-0 text-primary'>Deposit</p>"; break;
                default:           $typeHtml = "<p class='mb-0 text-secondary'>Other</p>";
            }
            // status
            switch ($tx['status']) {
                case 'pending':  $st = "btn-outline-warning"; break;
                case 'approved': $st = "btn-outline-success"; break;
                case 'declined': $st = "btn-outline-danger"; break;
                default:         $st = "btn-outline-secondary";
            }
            ?>
          html += `<tr>
                     <td><?= $tx['reference_id'] ?></td>
                     <td><?= $sym . $tx['amount'] ?></td>
                     <td><?= $typeHtml ?></td>
                     <td><button class="btn btn-sm <?= $st ?>"><?= ucfirst($tx['status']) ?></button></td>
                     <td><i class="bi bi-calendar-check-fill"></i> <?= $tx['date_time'] ?></td>
                   </tr>`;
        <?php endforeach; ?>
        $('#dataTable tbody').html(html);
      }

      // bind clicks
      $(document).on('click','#pagination a.page-link', function(e){
        e.preventDefault();
        let p = parseInt($(this).data('page'));
        if (p>=1 && p<= totalPages) {
          // reload the whole function via AJAX
          loadPage(p);
        }
      });

      // initial render
      $(function(){
        renderRows();
        renderPagination();
      });
    })(jQuery);
    </script>

    <?php
}


//Avatar/Profile Picture