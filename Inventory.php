<?php
/**********************************************
Name: Inventory 
modified for COAST 1.5.1 and FrontAccounting 2.3.15 by kfraser 
Free software under GNU GPL
***********************************************/

$page_security = 'SA_Inventory';
$path_to_root="../..";

include($path_to_root . "/includes/session.inc");
add_access_extensions();
set_ext_domain('modules/Inventory');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/modules/Inventory/class.Inventory.php");
//include_once($path_to_root . "/modules/Inventory/class.Inventory_ui.php");
require_once( 'Inventory.inc.php' );

error_reporting(E_ALL);
ini_set("display_errors", "on");

global $db; // Allow access to the FA database connection
$debug_sql = 0;  // Change to 1 for debug messages
$prefsDB = "Inventory_prefs";

//$server = "fhs-laptop1.ksfraser.com";	//prod
$server = "fhsws001.ksfraser.com";	//devel
$user = $pass = $dbname = "fhs";

$Inventoryc = new Inventory( "fhs-laptop1.ksfraser.com", "fhs", "fhs", "fhs", $prefsDB );
//$Inventoryc = new Inventory_ui( $server, $user, $pass, $dbname, $prefsDB );
$found = $Inventoryc->is_installed();
$Inventoryc->set_var( 'found', $found );
$Inventoryc->set_var( 'help_context', "Inventory  Interface" );
$Inventoryc->set_var( 'redirect_to', "Inventory.php" );

$Inventoryc->run();


?>
