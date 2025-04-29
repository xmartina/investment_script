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
 ?>

        <div class="card-body">
            <table class="table mb-0" data-show-toggle="true" id="dataTable">
                <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Amount</th>
                    <th data-breakpoints="xs">Transaction Type</th>
                    <th data-breakpoints="xs sm">Profit/Loss</th>
                    <th data-breakpoints="xs">Today's Trend</th>
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
                        <p class="mb-0">$ 100.45</p>
                        <p class="small">
                            <span class="text-secondary" data-bs-toggle="tooltip" title="Last top price">LTP:</span> 152
                        </p>
                    </td>
                    <td>
                        <p class="mb-0">102 units</p>
                        <p class="small"><span class="text-secondary">Invested:</span> $ 1400.45</p>
                    </td>
                    <td>
                        <p class="mb-0 text-success"><i class="bi bi-caret-up-fill"></i> 25.30%</p>
                        <p class="small"><span class="text-secondary">Profit:</span> $ 305.5</p>
                    </td>
                    <td>
                        <p class="mb-0 text-success"><i class="bi bi-graph-up-arrow"></i> Bullish</p>
                    </td>
                    <td>
                        <p class="mb-0 text-success"><i class="bi bi-caret-up-fill"></i> 1.24%</p>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-success">Invest</button>
                        <button class="btn btn-sm btn-outline-danger">Sell</button>
                        <div class="dropdown d-inline-block">
                            <a class="btn btn-link btn-square no-caret" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0)">Favorite</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">View Chart</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)">Company Events</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <!-- Additional Rows can be added below in similar format -->
                </tbody>
            </table>
        </div>

    <?php } }

//Avatar/Profile Picture