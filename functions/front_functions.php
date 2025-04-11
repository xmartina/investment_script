<?php
function list_menu($result, $active_url, $page_name) {
    $first = true; // Flag to identify the first menu item

    while ($row = $result->fetch_assoc()) {
        $menu_name = $row['menu_name'];
        $menu_link = $row['menu_link'];

        if ($first && (strpos($active_url, $page_name) !== false || $page_name == 'Home')) {
            $current = 'current';
            $first = false; // Apply only once
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


?>
