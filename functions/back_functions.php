<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

function back_single_menu($menu_name, $conn_back) {
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

function get_user($user_id, $conn_back) {
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
            'user_fname' => $row['first_name'],
            'user_lname' => $row['last_name'],
            'user_email' => $row['email']
        ];
    } else {
        return [
            'user_fname' => 'not set',
            'user_lname' => 'not set',
            'user_email' => 'not set'
        ];
    }
}


$dashboard = back_single_menu('dashboard', $conn_back);
$profile = back_single_menu('profile', $conn_back);
$transactions = back_single_menu('transactions', $conn_back);

$get_user = get_user($user_id, $conn_back);