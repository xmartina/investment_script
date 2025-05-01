<?php
/**
 * Fetch user’s transactions, optionally paged.
 *
 * @param mysqli   $conn_back
 * @param int      $user_id
 * @param int|null $limit   number of rows per page (pass null to fetch all)
 * @param int|null $offset  zero-based offset (ignored if $limit=null)
 * @return array
 */
function selectTransactionsByUserId($conn_back, $user_id, $limit = null, $offset = null) {
    $sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY date_time DESC";

    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $conn_back->prepare($sql);
        $stmt->bind_param("iii", $user_id, $limit, $offset);
    } else {
        $stmt = $conn_back->prepare($sql);
        $stmt->bind_param("i", $user_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    return $transactions;
}

/**
 * Count total transactions for a user.
 *
 * @param mysqli $conn_back
 * @param int    $user_id
 * @return int
 */
function countTransactionsByUserId($conn_back, $user_id) {
    $sql = "SELECT COUNT(*) AS c FROM transactions WHERE user_id = ?";
    $stmt = $conn_back->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)$row['c'];
}
?>