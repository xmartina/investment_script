<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/models/transactions.fn.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/models/transactions.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

function back_menu($menus_result, $active_url, $page_name)
{
    $first = true; // Flag to identify the first active menu item

    while ($row = $menus_result->fetch_assoc()) {
        $menu_name = $row['menu_name'];
        $menu_link = $row['menu_link'];

        if (strpos($menu_link, 'privacy') !== false) {
            continue;
        }

        // Determine if this is the active menu
        if ($first && (
                strpos($active_url, $menu_link) !== false ||
                $menu_name == $page_name ||
                ($page_name == 'Dashboard' && $menu_name == 'Dashboard')
            )) {
            $current = 'current';
            $first = false;
        } else {
            $current = '';
        }

        ?>
        <li class="<?= $current ?>">
            <a href="<?= $menu_link ?>"><?= $menu_name ?></a>
        </li>
        <?php
    }
}

function back_single_menu($menu_name, $conn_back)
{
    $menu_name = mysqli_real_escape_string($conn_back, $menu_name); // basic SQL safety
    $sql = "SELECT menu_name, menu_link FROM back_menus WHERE menu_name = '$menu_name' LIMIT 1";
    $result = $conn_back->query($sql);

    if ($row = $result->fetch_assoc()) {
        return [
            'name' => $row['menu_name'],
            'link' => $row['menu_link']
        ];
    } else {
        return [
            'name' => ucfirst($menu_name),
            'link' => '#'
        ];
    }
}


function login_user($conn_back, $email, $password)
{
    // Query (NOT SECURE - for simple use only)
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn_back, $sql);

    if (!$result) {
        return ['success' => false, 'message' => 'Query failed.'];
    }

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user'] = $user;
        return ['success' => true, 'message' => 'Login successful.'];
    } else {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
}

function get_user($user_id, $conn_back)
{
    if ($user_id === null || $user_id === '') {
        return [
            'user_fname' => 'not set',
            'user_lname' => 'not set',
            'user_email' => 'not set'
        ];
    }

    $user_id = mysqli_real_escape_string($conn_back, $user_id);
    $getusersql = "SELECT * FROM users WHERE id = '$user_id'";
    $get_user_result = mysqli_query($conn_back, $getusersql);

    if ($row = mysqli_fetch_assoc($get_user_result)) {
        return [
            'fname' => $row['first_name'],  # Change key to match header.php
            'lname' => $row['last_name'],
            'email' => $row['email'],
            'main_balance' => $row['main_balance'],
            'investment_balance' => $row['investment_balance'],
            'staking_balance' => $row['staking_balance'],
            'currency' => $row['currency'],
            'profile_photo' => $row['profile_photo']
        ];
    } else {
        return [
            'user_fname' => 'not set',
            'user_lname' => 'not set',
            'user_email' => 'not set'
        ];
    }
}


$default_photo = $site_link . '/back_assets/img/users/profile_photo/default_photo.jpg';

$dashboard = back_single_menu('dashboard', $conn_back);
$profile = back_single_menu('profile', $conn_back);
$transactions = back_single_menu('transactions', $conn_back);
$settings = back_single_menu('settings', $conn_back);

$get_user = get_user($user_id, $conn_back);
if (isset($user_id)) {
    $investment_balance = $get_user['investment_balance'];
    $staking_balance = $get_user['staking_balance'];
    $main_balance = $get_user['main_balance'];
    switch ($get_user['currency']) {
        case 'USD':
            $user_currency = '$';
            break;
        case 'EUR':
            $user_currency = '€';
            break;
        case 'GBP':
            $user_currency = '£';
            break;
        case 'JPY':
            $user_currency = '¥';
            break;
        case 'CAD':
            $user_currency = 'C$';
            break;
        case 'AUD':
            $user_currency = 'A$';
            break;
        case 'NGN':
            $user_currency = '₦';
            break;
        case 'CHF':
            $user_currency = 'CHF'; // Swiss Franc
            break;
        case 'CNY':
            $user_currency = '¥'; // Chinese Yuan
            break;
        case 'INR':
            $user_currency = '₹'; // Indian Rupee
            break;
        case 'ZAR':
            $user_currency = 'R'; // South African Rand
            break;
        case 'NZD':
            $user_currency = 'NZ$'; // New Zealand Dollar
            break;
        default:
            $user_currency = '$'; // default fallback
            break;
    }

    $profile_photo = !empty($get_user['profile_photo'])
        ? $site_link . '/back_assets/img/users/profile_photo/' . rawurlencode($get_user['profile_photo'])
        : $default_photo;


    $sql_back = "SELECT * FROM general_settings";
    $back_general_settings = $conn_back->query($sql_back);
    $get_info = $back_general_settings->fetch_assoc();
    $footer_content = $get_info['footer_content'];
    $footer_sub_content = $get_info['footer_sub_content'];
    $footer_copyright_link = $get_info['footer_copyright_link'];

    $total_returns = 0;
    $total_invested = 0;
    $amount_invested = 0;
    $returns_amount = 0;

    $returns_stakes_amount = 0;
    $amount_stakes_invested = 0;

// Abbreviate function (only once)
    function abbreviate_number($num) {
        if ($num >= 1000000) {
            return number_format($num / 1000000, 2) . 'M';
        } elseif ($num >= 1000) {
            return number_format($num / 1000, 2) . 'K';
        } else {
            return number_format($num, 2);
        }
    }

// Fetch investments
    $sql_investments = "SELECT * FROM investments WHERE user_id = '$user_id' AND status = 'active'";
    $get_investments = $conn_back->query($sql_investments);

    if ($get_investments && $get_investments->num_rows > 0) {
        while ($row = $get_investments->fetch_assoc()) {
            $returns_amount += isset($row['roi_expected']) ? $row['roi_expected'] : 0;
            $amount_invested += isset($row['amount']) ? $row['amount'] : 0;
        }
    }

    $total_returns = abbreviate_number($returns_amount);
    $total_invested = abbreviate_number($amount_invested);

// Fetch stakes
    $sql_stakes = "SELECT * FROM staking WHERE user_id = '$user_id' AND status = 'active'";
    $get_stakes = $conn_back->query($sql_stakes);

    if ($get_stakes && $get_stakes->num_rows > 0) {
        while ($row = $get_stakes->fetch_assoc()) {
            $reward_amount = ($row['amount'] * $row['reward_percent'] / 100);
            $returns_stakes_amount += $reward_amount;
            $amount_stakes_invested += $row['amount'];
        }
    }

    $total_returns_stakes = abbreviate_number($returns_stakes_amount);
    $total_stakes_invested = abbreviate_number($amount_stakes_invested);

// Final total profit (combined returns)
    $total_profit = abbreviate_number($returns_amount + $returns_stakes_amount);
    $total_investments = abbreviate_number($amount_invested + $amount_stakes_invested);

    function generateUniqueString($length) {
        // Generate a unique ID and clean it to be alphanumeric
        $uniqueString = uniqid("", true);
        $uniqueString = preg_replace("/[^A-Za-z0-9]/", "", $uniqueString);

        // Shuffle the string to randomize the order of characters
        $shuffledString = str_shuffle($uniqueString);

        // Return the string with the desired length
        return substr($shuffledString, 0, $length);
    }

// Generate a random alphanumeric string with 9 characters
$randomString = generateUniqueString(9);

}

include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/helpers.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/components/back_components.php';

