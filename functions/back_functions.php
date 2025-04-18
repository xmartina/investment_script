<?php

function list_menu($menus_result, $active_url, $page_name)
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
