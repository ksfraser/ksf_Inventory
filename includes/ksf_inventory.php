<?php

function ksf_inventory_install()
{
    $sql_files = [
        __DIR__ . '/../sql/install.sql',
    ];

    foreach ($sql_files as $file) {
        if (file_exists($file)) {
            $sql = file_get_contents($file);
            
            $sql = str_replace('{{MDB}}', TB_PREF, $sql);
            
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    db_query($statement);
                }
            }
        }
    }
}

function ksf_inventory_render_page()
{
    global $Ajax;
    
    $selected = $_GET['selected'] ?? 'locations';
    
    $tabs = [
        'locations' => 'Warehouse Locations',
        'serials' => 'Serial Numbers',
        'batches' => 'Batch Numbers',
        'movement' => 'Movement Log',
    ];
    
    echo '<div class="ksf-inventory-panel">';
    echo '<ul class="ui-tabs-nav">';
    
    foreach ($tabs as $tab => $label) {
        $active = ($selected === $tab) ? 'ui-tabs-active' : '';
        echo "<li class=\"$active\"><a href=\"?selected=$tab\">$label</a></li>";
    }
    
    echo '</ul>';
    
    switch ($selected) {
        case 'locations':
            ksf_inventory_render_locations();
            break;
        case 'serials':
            ksf_inventory_render_serials();
            break;
        case 'batches':
            ksf_inventory_render_batches();
            break;
        case 'movement':
            ksf_inventory_render_movement();
            break;
    }
    
    echo '</div>';
}

function ksf_inventory_render_locations()
{
    $location = new \Ksfraser\Inventory\WarehouseLocation();
    $locations = $location->getTree();
    
    echo '<h3>Warehouse Locations</h3>';
    echo '<table class="table">';
    echo '<thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Path</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($locations as $loc) {
        echo '<tr>';
        echo '<td>' . $loc->location_code . '</td>';
        echo '<td>' . $loc->location_name . '</td>';
        echo '<td>' . $loc->location_type . '</td>';
        echo '<td>' . $loc->getFullPath() . '</td>';
        echo '<td><a href="?view=location&code=' . $loc->location_code . '">View</a></td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
}

function ksf_inventory_render_serials()
{
    $item_code = $_GET['item'] ?? '';
    
    echo '<h3>Serial Number Tracking</h3>';
    echo '<form method="get">';
    echo '<input type="hidden" name="selected" value="serials">';
    echo '<label>Item: <input type="text" name="item" value="' . $item_code . '"></label>';
    echo '<button type="submit" class="button">Search</button>';
    echo '</form>';
    
    if ($item_code) {
        $serials = \Ksfraser\Inventory\SerialNumber::findByItem($item_code);
        
        echo '<table class="table">';
        echo '<thead><tr><th>Serial #</th><th>Location</th><th>Status</th><th>Purchase Date</th><th>Warranty</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($serials as $sn) {
            echo '<tr>';
            echo '<td>' . $sn->serial_no . '</td>';
            echo '<td>' . ($sn->location_code ?? '-') . '</td>';
            echo '<td>' . $sn->status . '</td>';
            echo '<td>' . $sn->purchase_date . '</td>';
            echo '<td>' . ($sn->isUnderWarranty() ? 'Yes' : 'No') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
}

function ksf_inventory_render_batches()
{
    echo '<h3>Batch Number Tracking</h3>';
}

function ksf_inventory_render_movement()
{
    echo '<h3>Movement Log</h3>';
}

add_menu_entry('inventory', 'Inventory', 'inventory', 'ksf_inventory');
add_shortcode('ksf_inventory', 'ksf_inventory_render_page');