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

function single_menu($menu_name, $conn_front){
    // Dynamically select the column based on $menu_name
    $sql_front_single = "SELECT menu_name, menu_link FROM front_menus WHERE menu_name = '$menu_name'";

    // Execute the query
    $single_menu_result = $conn_front->query($sql_front_single);

    // Check if a row is returned
    if ($row = $single_menu_result->fetch_assoc()) {
        $menu_name = $row['menu_name'];
        $menu_link = $row['menu_link'];

        return ['menu_name' => $menu_name, 'menu_link' => $menu_link];
    } else {
        return null;  // Return null if no menu is found
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
