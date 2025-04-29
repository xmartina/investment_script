<?php
//Buttons


//Modals/Dialogs

//Tables

//Forms

//Notifications/Alerts

//Navigation Components

//Cards
function TransactionsCard($conn_back,$user_id){
    $ListUserTransactions = selectTransactionsByUserId($conn_back,$user_id);
    foreach ($ListUserTransactions as $SingleTransactions) {
        switch ($SingleTransactions['currency']) {
            case 'USD':
                $trans_currency = '$';
                break;
            case 'EUR':
                $trans_currency = '€';
                break;
            case 'GBP':
                $trans_currency = '£';
                break;
            case 'JPY':
                $trans_currency = '¥';
                break;
            case 'CAD':
                $trans_currency = 'C$';
                break;
            case 'AUD':
                $trans_currency = 'A$';
                break;
            case 'NGN':
                $trans_currency = '₦';
                break;
            case 'CHF':
                $trans_currency = 'CHF'; // Swiss Franc
                break;
            case 'CNY':
                $trans_currency = '¥'; // Chinese Yuan
                break;
            case 'INR':
                $trans_currency = '₹'; // Indian Rupee
                break;
            case 'ZAR':
                $trans_currency = 'R'; // South African Rand
                break;
            case 'NZD':
                $trans_currency = 'NZ$'; // New Zealand Dollar
                break;
            default:
                $trans_currency = '$'; // default fallback
                break;
        }
        switch ($SingleTransactions['transaction_type']) {
            case 'withdraw':
                $trans_type = '<p class="mb-0 text-danger">Withdrawal</p>';
                break;
            case 'deposit':
                $trans_type = '<p class="mb-0 text-primary">Deposit</p>';
                break;
            case 'investment':
                $trans_type = '<p class="mb-0 text-secondary">Investment</p>';
                break;
            case 'stake':
                $trans_type = '<p class="mb-0 text-warning">Stacked</p>';
                break;
            default:
                $trans_type = '<p class="mb-0 text-primary">Transaction</p>'; // default fallback
                break;
        }
        switch ($SingleTransactions['transaction_type']) {
            case 'pending':
                $trans_status = '<button class="btn btn-sm btn-outline-warning">Pending</button>';
                break;
            case 'approved':
                $trans_status = '<button class="btn btn-sm btn-outline-success">Approved</button>';
                break;
            case 'running':
                $trans_status = '<button class="btn btn-sm btn-outline-primary">Running</button>';
                break;
            case 'declined':
                $trans_status = '<button class="btn btn-sm btn-outline-danger">Declined</button>';
                break;
            default:
                $trans_status = '<button class="btn btn-sm btn-outline-warning">Pending</button>'; // default fallback
                break;
        }
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
                <!-- Investment Row 1 -->
                <tr>
                    <td>
                        <p class="mb-0"><?=$SingleTransactions['reference_id']?></p>
                    </td>
                    <td>
                        <p class="mb-0"><?=$trans_currency.$SingleTransactions['amount']?></p>
                    </td>
                    <td>
                        <?=$trans_type?>
                    </td>
                    <td>
                        <?=$trans_status?>
                    </td>
                    <td>
                        <p class="mb-0 text-success"><i class="bi bi-calendar-check-fill"></i> <?=$SingleTransactions['date_time']?></p>
                    </td>
                </tr>
                <!-- Additional Rows can be added below in similar format -->
                </tbody>
            </table>
        </div>

    <?php } }

//Avatar/Profile Picture