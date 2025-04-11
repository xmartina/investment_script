<?php
function list_menu($menus_result, $active_url, $page_name) {
    $first = true; // Flag to identify the first menu item

    while ($row = $menus_result->fetch_assoc()) {
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

function social_media($social_result)
{
    while ($social_row = $social_result->fetch_assoc()){
        $social_name = $social_row['social_name'];
        $social_link = $social_row['social_link'];
        $social_icon = $social_row['social_icon']; ?>
        <li><a href="<?=$social_link?>"><i class="fa-brands <?=$social_icon?>"></i></a></li>
    <?php }
}
?>
