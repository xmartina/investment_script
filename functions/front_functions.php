<?php
function list_menu($menus_result, $active_url, $page_name)
{
    $first = true; // Flag to identify the first menu item

    while ($row = $menus_result->fetch_assoc()) {
        $menu_name = $row['menu_name'];
        $menu_link = $row['menu_link'];

        if ($first && (strpos($active_url, $page_name) !== false )) {
            $current = 'current';
            $first = false; // Apply only once
        } elseif ( $page_name == 'Home') {
            $current = 'current';
            $first = false; // Apply only once
        }

        else {
            $current = '';
        }
        ?>
        <li class="<?= $current ?>">
            <a href="<?= $menu_link ?>"><?= $menu_name ?></a>
        </li>
        <?php
    }
}

function single_menu($menu_name, $conn_front) {
    $menu_name = mysqli_real_escape_string($conn_front, $menu_name); // basic SQL safety
    $sql = "SELECT menu_name, menu_link FROM front_menus WHERE menu_name = '$menu_name' LIMIT 1";
    $result = $conn_front->query($sql);

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

function social_media($social_result)
{
    while ($social_row = $social_result->fetch_assoc()) {
        $social_name = $social_row['social_name'];
        $social_link = $social_row['social_link'];
        $social_icon = $social_row['social_icon']; ?>
        <li><a href="<?= $social_link ?>"><i class="fa-brands <?= $social_icon ?>"></i></a></li>
    <?php }
}


// Call the function for each menu item
$register = single_menu('register', $conn_front);
$about = single_menu('about', $conn_front);
$login = single_menu('login', $conn_front);
$contact = single_menu('contact', $conn_front);
$plans = single_menu('plans', $conn_front);
$home = single_menu('home', $conn_front);
$faq = single_menu('faq', $conn_front);
?>
