<?php
function list_menu($result, $active_url, $page_name) {
    while ($row = $result->fetch_assoc()) {
        $menu_name = $row['menu_name'];
        $menu_link = $row['menu_link'];

        if (strpos($active_url, $page_name) !== false || $page_name == 'Home') {
            $current = 'current';
        } else {
            $current = '';
        }
        ?>
        <li class="<?= $current ?> dropdown">
            <a href="<?= $menu_link ?>"><?= $menu_name ?></a>
        </li>
        <?php
    }
}
?>
