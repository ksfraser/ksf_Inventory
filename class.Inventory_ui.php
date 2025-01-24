<?php

/*! 
 *  \brief     Inventory UI class.
 *  \details   This class is used to display to a web browser the forms/screens we need
 *  \author    Kevin Fraser
 *  \version   0.9
 *  \date      2017
 *  \pre       First initialize the system.
 *  \bug       Editing an item in the cart doesn't work so has been disabled.
 *  \bug       Changing the location on the dropdown on any screen except scan not guaranteed to make it to the final transfer.
 *  \warning   Change locations through the SCAN tab!
 *  \copyright GNU Public License or whatever FrontAccounting requires of its modules!
 */


//TODO: 
//	
//	Add a report of items at the location.  Already have the code for finding the items... (see rep303)
//	Fix the bug where editing any but the last item nukes everything after the item being edited.
//		See Line 831 for the work-around that removed the EDIT button.  User can always delete
//		and re-add or scan!
//	Fix the handling of Location codes so that switching locations up top actually means something.
//	Learn further how the page security works so that we can tie into access control and what tabs 
//	 	are available and therefore what needs to be displayed on the users usage tab.
//	 Finish documenting the classes and functions

require_once( 'class.Inventory.php' ); 

/** 
 *	This module is for doing a stock taking (aka inventory).
 *
 *	I use as much native FrontAccounting code as possible.
 *	
 *	You can do partial and full inventories.  When you do a count, any difference
 *	between your count and what FA thinks is on hand at that location is transfered
 *	(using FA transfer routines) to your HOLDING TANK (config variable)
 *
 *	You can also transfer ALL inventory in a location to either another location
 *	or to the Holding tank.  You would use this for when you set up a temporary
 *	location (shop at a trade fair) and then decommission the shop.  We do this
 *	for the summer where we travel to Highland Games, and at the end of the summer
 *	the inventory is transfered back to the other store locations.
 *	
 *
 *	Need a reminder to set that config item.
 *	BUG: isHoldTankSet doesn't work - always returns FALSE.
 */

//from inventory/transfers.php
$path_to_root = "../..";
include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

//From sales/Sales_inventory....
include_once($path_to_root . "/sales/includes/cart_class.inc");

include_once($path_to_root . "/includes/db/inventory_db.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/includes/db/manufacturing_db.inc");


$path_to_root = "../..";
$page_security = 'SA_Inventory';
set_page_security( @$_SESSION['InventoryItems']->trans_type,
        array(  ST_Inventory=>'SA_Inventory',
                        ST_Inventory => 'SA_Inventory',
                        ST_CUSTDELIVERY => 'SA_Inventory',
                        ST_Inventory => 'SA_Inventory'),
        array(  'NewOrder' => 'SA_Inventory',
                        'ModifyOrderNumber' => 'SA_Inventory',
                        'AddedID' => 'SA_Inventory',
                        'UpdatedID' => 'SA_Inventory',
                        'NewQuotation' => 'SA_Inventory',
                        'ModifyQuotationNumber' => 'SA_Inventory',
                        'NewQuoteToSalesOrder' => 'SA_Inventory',
                        'AddedQU' => 'SA_Inventory',
                        'UpdatedQU' => 'SA_Inventory',
                        'NewDelivery' => 'SA_Inventory',
                        'AddedDN' => 'SA_Inventory',
                        'NewInvoice' => 'SA_Inventory',
                        'AddedDI' => 'SA_Inventory'
                        )
);


/************************************************************************************/
/*********************Trying to migrate UI code here*********************************/
/************************************************************************************/

/**************************************************************
 *
 *	This module allows an employee to do a partial stock taking
 *
 **************************************************************/

/*************************************************************************//**
 *	Class Inventory is the routines for doing a stock taking.
 *
 *	Class Inventory is the routines for doing a stock taking.
 *	It also allows you to do inventory transfers of ALL inventory
 *	without having to count those items at both locations.  
 *	ASSUMPTION is that you will do an appropriate inventory count later.
 *
 * ***************************************************************************/
class Inventory_ui extends Inventory
{
	//var $location;		//Inventory location
	//var $holdtank;		//!< Location that acts as holding tank for corrections.  Config Value
	//var $to_location;	//!< Used for the xfer_all functions
	//var $from_location;	//!< Used for the xfer_all functions
	//var $url;
	//var $javascript; 	//in generic_interface 	//in generic_interface
	//var $trans_type;
	//var $trans_no;
	//var $barcode;
	//var $locationPrefix;
	//var $locationPrefix2;
	//var $line_items;	//Set as [$count][...].  	//quantity = amount being transferred (diff).  
								//counted is how many we counted.  
								//qoh is how many the system thinks we have at this location
								//stock_id is the stock_id in stock_master
	//var $reference;		//set in create_cart
	//var $Comments;
	//var $document_date;
	//var $ex_rate;
	//var $deliver_to;
	//var $delivery_address;
	//var $cart_id;
	//var $cart;	//includes/ui/items_cart.inc
	//var $copyfromcount;
	//var $copytocount;
	//var $title;
	//var $path_to_root;
	//var $update_quantity;
	//var $add_quantity;
	//var $item_text;
	//var $stock_id;
	//var $change_line_number;
	//var $table_interface;
	var $showPricebookPrice; //!< Should we show the price on the inventory count screen to aid with price checking while counting
	/********************************************************************************//**
	 *
	 * Constructor
	 *
	 * The params for this constructor are not needed for this particular function;
	 * They are needed for compatibility to generic_interface which this class extends
	 * so that we don't break the parent constructor.
	 *
	 * Generic_interface gives us the routines that allow us to set actions and 
	 * the forms/routines that we are supposed to then call without having to 
	 * write a bunch of if/then/else statements.  (see $this->tabs).  It also
	 * gives us the generic CONFIG VARIABLES (see $this->config_values) which
	 * are then presented on the configuration tab, and stored in the modules prefs table.
	 *
	 * @param[in] string $host server name for connection to the database
	 * @param[in] string $user username for connecting to the database
	 * @param[in] string $pass password for connecting to the database
	 * @param[in] string $database database name
	 * @param[in] string $pref_tablename The name of the table storing the preferences (config) of this class
	 *
	 * *********************************************************************************/
	function __construct( $host, $user, $pass, $database, $pref_tablename )
	{
		
		//The forms/actions for this module
		//Hidden tabs are just action handlers, without accompying GUI elements.
		//$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'config_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Usage', 'action' => 'usage', 'form' => 'module_usage_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables', 'action' => 'init_tables_form', 'form' => 'init_tables_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Init Tables Completed', 'action' => 'init_tables_completed_form', 'form' => 'init_tables_completed_form', 'hidden' => TRUE );
		
		$this->tabs[] = array( 'title' => 'Install Module', 'action' => 'install', 'form' => 'install', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Config Updated', 'action' => 'update', 'form' => 'updateprefs', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Inventory Scan', 'action' => 'scan_form', 'form' => 'scan_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Inventory Count', 'action' => 'Inventory_form', 'form' => 'Inventory_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Inventory Over and Under', 'action' => 'call_overunder', 'form' => 'overunder_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Inventory Over and Under', 'action' => 'overunder', 'form' => 'overunder_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Process Transfer', 'action' => 'process', 'form' => 'process_form', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Process Transfer', 'action' => 'processall', 'form' => 'process_form', 'hidden' => TRUE );

		$this->tabs[] = array( 'title' => 'Transfer ALL inventory between locations', 'action' => 'xfer_all_to_location_form', 'form' => 'xfer_all_to_location_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Transfer step 2 ALL inventory between locations', 'action' => 'xfer_all_to_location', 'form' => 'xfer_all_to_location', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Transfer ALL inventory to HOLDING (Zero this location)', 'action' => 'xfer_all_to_holding_form', 'form' => 'xfer_all_to_holding_form', 'hidden' => FALSE );
		$this->tabs[] = array( 'title' => 'Transfered ALL inventory to HOLDING', 'action' => 'xfer_all_to_holding', 'form' => 'xfer_all_to_holding', 'hidden' => TRUE );
		$this->tabs[] = array( 'title' => 'Clear Cart', 'action' => 'clearcart', 'form' => 'clearcart_form', 'hidden' => TRUE );


		$this->javascript = get_js_open_window(900, 500);
		$this->javascript .= get_js_date_picker();

		if( ! $this->isHoldingTankSet() )
			$_POST['action'] = 'config';

		parent::__construct( $host, $user, $pass, $database, $pref_tablename );
		$this->config_values[] = array( 'pref_name' => 'showPricebookPrice', 'label' => 'Show the pricebook price on counting screen?(0/1)', 'type' => 'boolean' );
		global $path_to_root;
		$this->handlePOST();
	}
	/**//**
	 * Clear the cart of all items
	 * */
	function clearcart_form()
	{
		$this->clearcart();
		$this->Inventory_form();
		$this->action = "Inventory_form";
		//$this->page_modified();
	  	//$this->line_start_focus();
	}
	/************************************************************//**
	 * Check that the holding tank variable has been set.  Needed for transfers to work correctly.
	 *
	 * @returns Bool set or not
	 * **************************************************************/
	/*@bool@*/function isHoldingTankSet()
	{
		//Until we have coded this without bugs, we need to return TRUE.
		return TRUE;

		//For whatever reason we are not finding the holdtank value at the time this is called.
		//Constructor hasn't happened, so maybe we don't have a DB connection.
		//Do we need an action handler like Wordpress where you can insert functions
		//into the normal work flow so that this can be checked before going to a form in tabs?
		if( $this->is_installed() )
		{
			$this->loadprefs();
			if( isset( $this->holdtank ) AND strlen( $this->holdtank ) > 1 )
				return TRUE;
			else
				return FALSE;
		}
	}
	/**//**
	 * Handle the variables we are expecting to be set somewhere in this class on a form
	 *
	 * \warning calls Ajax calls in the browser
	 * */
       	function handlePOST()
	{
		//display_notification( __method__ . ":" . __LINE__ . " handlePost" );
		global $Ajax;
		if (isset($_POST['AddItem']))
		        $this->handle_new_item();
		if (isset($_POST['document_date']))
			$this->document_date = $_POST['document_date'];
		if (isset($_POST['location']))
		{
			$this->location = $_POST['location'];
		}
		if (isset($_POST['from_location']))
		{
			$this->from_location = $_POST['from_location'];
		}
		if (isset($_POST['to_location']))
		{
			$this->to_location = $_POST['to_location'];
		}
		else
		{
			$this->to_location = $this->holdtank;
		}
		if (isset($_POST['UpdateItem']))
		{
		        $this->handle_update_item();
		}
		$id = find_submit('Delete');
		if ($id!=-1)
		{
		        $this->handle_delete_item($id);
		}
		$this->copy_to_session();
	$Ajax->activate('items_table');
	//$this->page_modified();		//handle UI
	//$this->line_start_focus();
	return;
	}
	function submenu_choices()
	{
		if (isset($_GET['AddedID'])) {
		        $inventory_no = $_GET['AddedID'];
		        display_notification_centered(sprintf( _("Order # %d has been entered."),$inventory_no));
		
		        submenu_view(_("&View This Order"), ST_Inventory, $inventory_no);
		
		        submenu_print(_("&Print This Order"), ST_Inventory, $inventory_no, 'prtopt');
		        submenu_print(_("&Email This Order"), ST_Inventory, $inventory_no, null, 1);
		        set_focus('prtopt');
		
		        submenu_option(_("Make &Delivery Against This Order"),
		                "/sales/customer_delivery.php?OrderNumber=$inventory_no");
		
		        submenu_option(_("Work &Order Entry"),  "/manufacturing/work_inventory_entry.php?");
		
		        submenu_option(_("Enter a &New Order"), "/sales/sales_inventory_entry.php?NewOrder=0");
		
		        display_footer_exit();
		} elseif (isset($_GET['UpdatedID'])) {
		        $inventory_no = $_GET['UpdatedID'];
		
		        display_notification_centered(sprintf( _("Order # %d has been updated."),$inventory_no));
		
		        submenu_view(_("&View This Order"), ST_Inventory, $inventory_no);
		
		        submenu_print(_("&Print This Order"), ST_Inventory, $inventory_no, 'prtopt');
		        submenu_print(_("&Email This Order"), ST_Inventory, $inventory_no, null, 1);
		        set_focus('prtopt');
		
		        submenu_option(_("Confirm Order Quantities and Make &Delivery"),
		                "/sales/customer_delivery.php?OrderNumber=$inventory_no");
		
		        submenu_option(_("Select A Different &Order"),
		                "/sales/inquiry/sales_inventorys_view.php?OutstandingOnly=1");
		
		        display_footer_exit();
		
		} 
		else
		{
		}
	}
	/**************************************************//**
	 * Set the focus on the cart's displayed table and put the cursor into the UPC edit box
	 *
	 * @returns NONE
	 * ***************************************************/
	function line_start_focus() 
	{
	  global        $Ajax;
	  $Ajax->activate('items_table');
	  set_focus('_stock_id_edit');
	}

	/**//**
	 * Can we process the data of the cart?
	 *
	 * \sa (Inventory)#can_process()
	 * @return bool Yes we can or Not
	 * */
	/*@bool@*/function can_process() 
	{
	
	        global $Refs, $SysPrefs;
		$input_error = 0;
		try 
		{
			parent::can_process();	//Do the checks (data)
		}
		catch (Exception $e)
		{	//Display error messages and take actions on UI as needed
			display_error(_( $e->getMessage() ) );
			switch( $e->getCode() )
			{
				case INVALIDDATE:
					//$_POST['action'] = "Inventory_form";
					set_focus('AdjDate');
					break;
				case INVALIDYEAR:
					//$_POST['action'] = "Inventory_form";
					set_focus('AdjDate');
					break;
				case INVALIDFROMLOC:	
					//$_POST['action'] = "Inventory_form";
					set_focus('FromStockLocation');
					break;
				case INVALIDTOLOC:
					//$_POST['action'] = "Inventory_form";
					set_focus('FromStockLocation');
					break;
			}
			return FALSE;
	        }
		return TRUE;
	}

	/**//**
	 * Handle the updating of the count of a stock_id
	 * */
	function handle_update_item()
	{
		parent::handle_update_item();	//handle data part
		display_notification( __LINE__ . ' updated counted for ' . $this->line_items[$_POST['LineNo']]['stock_id'] . ' to ' . $this->line_items[$_POST['LineNo']]['counted'] );
	        $this->page_modified();		//handle UI part
	  	$this->line_start_focus();
	}
	/**//**
	 * Handle the deleting from the cart of an item
	 * */
	function handle_delete_item($line_no)
	{
		display_notification( __LINE__ . ' handle_delete removing ' . $line_no . " item " . $this->line_items[$line_no]['stock_id'] );
		parent::handle_delete_item($line_no);	//handle data part
	        $this->page_modified();			//handle UI part
	  	$this->line_start_focus();
	}
	/**//**
	 * \fn function isStockid( $stock_id )
	 * \brief inherited
	 * \param string $stock_id
	 * \return bool
	 * */
	/**//**
	 * \fn function isItemCode( $item_code )
	 * \brief inherited
	 * \param string $item_code
	 * \return bool
	 * */

	/**//**
	 * Get the description from stock_master for the item
	 * \warning expects stock_id to be set
	 * */
	/*@string@*/function get_item_description()
	{
		$ret = "";
		try
		{
			$ret = parent::get_item_description();
		}
		catch (Exception $e)
		{
			display_error(_( $e->getMessage() ) );
			switch( $e->getCode() )
			{
				case INVALIDSTOCKID:
					$ret = "Item not found in stock_master";
					break;
				case INVALIDDESCRIPTION:
					$ret = "";
					break;
			}
			
	        }
		return $ret;
	}
	function add_item()
	{
		parent::add_item();	//handle data part
		global $Ajax;
		$Ajax->activate('items_table');	//handle UI part
	}
	//inherited function find_cart_item()
	function handle_new_item()
	{
		parent::handle_new_item();	//handle data part
	        $this->page_modified();		//handle UI
	        $this->line_start_focus();
	}
	function display_inventory_header( $hidden_action = "")
	{
		var_dump( $_POST );
		var_dump( $this );
        	start_outer_table(TABLESTYLE, "width=70%");
        	table_section(1);
		//label, name, selected, showAll, submit_on_change
		//locations_list_cells(_("Inventory Location:"), 'from_location', $this->from_location, FALSE, FALSE);	//includes/ui/ui_lists.inc
		if( "xfer_all_to_location_form" == $hidden_action OR "xferall_form" == $hidden_action )
		{
        		locations_list_cells(_("Inventory From Location:"), 'from_location', $this->from_location, FALSE, TRUE);	//includes/ui/ui_lists.inc
        		locations_list_cells(_("Inventory TO Location:"), 'to_location', $this->to_location, FALSE, TRUE);	//includes/ui/ui_lists.inc
		}
		else
	        	locations_list_cells(_("Inventory Location:"), 'from_location', $this->from_location, FALSE, TRUE);	//includes/ui/ui_lists.inc
        	table_section(2, "33%");
		//$label, $name, $title=null, $check=null, $inc_days=0, $inc_months=0, $inc_years=0, $params=null, $submit_on_change=false)
    		date_row(_("Date:"), 'document_date', $this->document_date, true, 0, 0, 0, null, TRUE);
        	table_section(3, "33%");
		hidden( 'action', $hidden_action );
        	end_outer_table(1); // outer table
	}
	function upc_table()
	{
                start_table(TABLESTYLE, "width=70%");
                //start_outer_table(TABLESTYLE, "width=70%");
                table_section(1);
                table_section_title(_("Scan Barcode"));
		hidden( 'action', 'scan_form' );
                label_row("&nbsp;", NULL);
               	label_row(_("Please scan the product's barcode"), NULL);
		text_row( "UPC", "UPC", "UPC", 20, 40);
		//table_section(2);
		//submit_center( "SubmitUPC", "Submit UPC" );
	               //button_cell('Submit', _("Submit"), _('Submit'), ICON_UPDATE);
		       //var_dump( $_SESSION );
		end_table(1); // outer table
		submit_center( "scan_form", "Submit UPC" );
		//end_outer_table(1); // outer table
		set_focus('UPC');
	}
	/**************************************************************//**
	 * Form to show how to use the module.  
	 *
	 * TODO:
	 * 	Extend with conditional includes to only include tabs
	 * 	that are active
	 *
	 * 	Learn further how the page security works so that we can
	 * 	tie into access control and what tabs are available and 
	 * 	therefore what needs to be displayed on the users usage
	 * 	tab.
	 *
	 * @param NONE
	 * @returns NONE
	 * ***************************************************************/
	function module_usage_form()
	{
		$this->title = "Inventory Scan Form";
		start_form(true);
                start_table(TABLESTYLE2, "width=40%");
                table_section_title( "How to use this module" );
		table_section(1);
		label_row( "Config", "Configuration screen for things like the HOLDING tank location." );
		label_row( "Init Tables", "Setup the database tables this module needs" );
		label_row( "Inventory Scan", "Count stock by scanning each and every item.  Increments the count by ONE each scan.  Like running items across the scanner at a POS till.  You will then need to go to over/under or Inventory Count to progress once the scanning is done." );
		label_row( "Inventory Count", "This screen has a 'cart' similar to order/sales screens where you can edit the item code and its count.  If you enter a bad total (too low) add more through Scan.  If too high, delete and re-enter" );
		label_row( "Inventory Over and Under", "This tab displays the items in the cart and how many FA thinks is inventory for the selected location.  2nd stage to 'updating' the count at the location and transfer any over/under to the HOLDING tank." );
		label_row( "Transfer All between locations", "Move every last item from one location to another.  We use this for going to trade shows where we gather everything from multiple locations and merge into the trade show location." );
		label_row( "Transfer All to Holding", "If you decommission a location and don't want to scan everything out the door, you can 'zero' the count for everything, and the HOLDING tank ends up with the differences.  The next time you count the inventory for those items at their new location they will be decrimented out of HOLDING, so long term it can save you a bit of work." );
		//end_table(1);
                //start_table(TABLESTYLE2, "width=40%");
                table_section_title( "Known Bugs" );
		label_row( "Inventory Count", "Editing a line item's total didn't work so is disabled  Workaround mentioned above" );
		label_row( "Inventory Count", "Changing the Location doesn't always move over to Over/Under.  Set the location by using the Scan tab" );
		//end_table(1);
                //start_table(TABLESTYLE2, "width=40%");
                table_section_title( "Roadmap" );
		label_row( "V2", "No Planned enhancements other than bug fixes unless there becomes an extension with sub-locations (i.e. rack and shelf and bin)" );
                table_section_title( "Developer Documentation" );
		label_row( "Documentation", '<a href="html/index.html">Class and member Documentation</a>' );
		end_table(1);
                end_form();
	}
	/**************************************************************//**
	 * Scan a UPC and if a product add to the count of items in the cart.
	 *
	 * @param NONE
	 * @returns NONE
	 * ***************************************************************/
	function scan_form()
	{
        	global $Refs;
		$this->title = "Inventory Scan Form";

		//This will display the form on which the inventory is scanned.
		//This will only insert a count of the product, for the location

		        //UPC is set.  Can be one of:
		        //      1 - loc set and a new location code.  Don't care about the media code.
		        //      2 - loc set and a media code.  Can update the database and clear media.
		        //      3 - loc not set and a loc code.  Step 1
		        //      4 - loc not set and a media code.  Still need a location.
		        //      5 - loc set, media set, new media scanned but previous not cleared for some reason

		//$this->copy_from_session();
		if( isset(  $_POST['AddedID'] ) )
		{
			echo "View transfer <href=" . $this->path_to_root . "/inventory/view/view_transfer.php?trans_no=" . $_POST['AddedID'] . ">" . $_POST['AddedID'] . "</a><br />"; 
		}

                start_form();
		$this->display_inventory_header( "scan_form" );
		echo "<br /><h2>NOTE</h2><br />The scanned barcode/upc/stock_id must match casewise what is in the inventory.  If there is different character cases<br />";
		echo "the program will find the QOH but will not add together the various products that are really the same.<br />";
                //start_table();


	 	if (!isset($_POST['UPC']))
	 	{
		        //echo "UPC Not set<br />"; //First time to tab...
			//hidden("location");
		        if( $this->hasBarcode() )
		        {
		                //clear the barcode
		                $this->set_var( "barcode", "" );
		        }
			$this->upc_table();
	 	}
		else
		{
			//UPC set so we need to do something with it.
		        $this->set_var( "barcode", $_POST['UPC'] );
		        $this->set_var( "add_quantity", 1 );
	
			$this->add_item();
			$this->upc_table();
		        //echo "DEBUG: Barcode is " . $_POST['UPC'] . "<br />";
		        //Is not a location code so is a UPC/EAN barcode
		                //need to replace the current this-barcode
				//Case 5 variation
			/*
			 * Triggering every time.
		                if( $this->isSameBarcode( $_POST['UPC'] ) )
		                {
		                        //Why did we rescan this code?
					echo "We just scanned this code.  Did you intend to do this?";
					//Why do we care about case 5?  Do we need to clear/reset something
					//or is it just a curiosity?  It is plausible that we will scan
					//the same item multiple times if there are more than 1 of them on the shelf!
		                }
		                else
		                {
		                        //case 2 partial
		                        //case 4 partial
		                        //case 5 partial
		                        //echo "DEBUG: Setting Barcode 1 <br />";
				}
			 */
		                //In this case we have a UPC/EAN for the first time.
		                //Set the this-barcode
		                //case 2 partial
		                //case 4 partial
		                //case 5 partial
		}
		//Does the set_var above set session variables too?  If not why bother... what happens after the form?
                //end_table();
                end_form();
	}
	//This is probably inherited...
	function call_table( $action, $msg )
	{
		//display_notification( __method__ . ":" . __LINE__  );
                start_form(true);
                 start_table(TABLESTYLE2, "width=40%");
                 table_section_title( $msg );
                 hidden('action', $action );
                 end_table(1);
                 submit_center( $action, $msg );
                 end_form();
	}

	/*****************************************************************************************************************
	 *
	 * 	function xfer_all_to_location
	 *
	 * 	This function will transfer all inventory from a given location
	 * 	into another.  You would use this if you have a temporary
	 * 	inventory location (i.e. trade show/farmers market) where you bring
	 * 	inventory from multiple locations into 1 so you sell out of one place
	 * 	at that show/market rather than having to hunt for the inventory and 
	 * 	worse having one order from multiple locations.
	 *
	 *
	 *
	 *****************************************************************************************************************/
	function xfer_all_to_location()
	{
		//display_notification( __method__ . ":" . __LINE__  );
		//We are given the from, to and date
		$this->to_location = $_POST['to_location'];
		$this->xfer_all_settings( "xfer" );
		$this->xferall_form();	
	}
	function xfer_all_to_holding()
	{
		//display_notification( __method__ . ":" . __LINE__  );
		$this->to_location = $this->holdtank;
		$this->xfer_all_settings( "zero" );
		$this->overunder_form();
	}
	//Find the list of items in inventory in the FROM location, and add to transfer list
	/****************************************************************//**
	 *
	 *	xfer_all_to_holding_form displays a button to initiate the transfer
	 *
	 *	This function displays the form with a button to initiate a transfer
	 *	of ALL inventory at a location to the HOLDING tank.  It zero's the
	 *	inventory count for any items that have more than 0 items showing
	 *	as on hand.
	 *
	 * *******************************************************************/
	function xfer_all_to_holding_form()
	{
		//display_notification( __method__ . ":" . __LINE__  );
		//Used when we stand down a location like Highland Games
		start_form();
		$this->display_inventory_header( __FUNCTION__ );
        	$this->call_table( 'xfer_all_to_holding', "Transfer ALL inventory" );
		end_form();

	}
	/****************************************************************//**
	 *
	 *	xfer_all_to_location_form displays a button to initiate the transfer
	 *
	 *	This function displays the form with a button to initiate a transfer
	 *	of ALL inventory from one location to another.  It zero's the
	 *	inventory count for any items that have more than 0 items showing
	 *	as on hand at the FROM location and using FA routines transfers
	 *	that count to the TO location.
	 *
	 * *******************************************************************/
	function xfer_all_to_location_form()
	{
		//display_notification( __method__ . ":" . __LINE__  );
		start_form();
		$this->display_inventory_header( __FUNCTION__ );
        	$this->call_table( 'xfer_all_to_location', "Transfer ALL inventory" );
		end_form();
	}
	 
	function xferall_form()
	{
		//In this form we will display the list of items the system thinks is on hand

		$this->title = "Transfer ALL Inventory from one location to another";
  		$_SESSION['page_title'] = _($help_context = $this->title);
		$this->page_title = $_SESSION['page_title'];

			$this->get_items_qoh_location();
			$this->copy_to_session();
	                start_form();
			$this->display_inventory_header( "xferall_form" );
			$doEdit = FALSE;
			$showStock = FALSE;
			
			$this->display_adjustment_items(_("Items for Transfer"), $doEdit, $showStock, "xfer");
			$this->copy_to_session();
			hidden( 'AdjDate', $this->document_date );
			hidden( 'from_location', $this->from_location );
			hidden( 'to_location', $this->to_location );
			hidden( 'ref', $this->reference );
			hidden( 'action', 'processall' );
			submit_center('process', _("Transfer Inventory from " . $this->from_location . " to " . $this->to_location) );
			end_form();
	}
	function overunder_form()
	{
		//display_notification( __method__ . ":" . __LINE__  );
		//In this form we will display the list of items we've taken in inventory and show
		//how many are in stock for the location selected.

		$this->title = "Inventory Over and Under";
  		$_SESSION['page_title'] = _($help_context = $this->title);
		$this->page_title = $_SESSION['page_title'];
		if( count( $this->line_items ) > 0 )
		{
			$this->get_items_qoh_location();
			$this->copy_to_session();
	                start_form();
			$this->display_inventory_header( "overunder_form" );
			$doEdit = FALSE;
			$showStock = TRUE;
			
			$this->display_adjustment_items(_("Items for Transfer"), $doEdit, $showStock, "inventory");
			$this->copy_to_session();
			hidden( 'AdjDate', $this->document_date );
			hidden( 'from_location', $this->from_location );
			hidden( 'to_location', $this->holdtank );
			hidden( 'ref', $this->reference );
			hidden( 'action', 'process' );
			submit_center('process', _("Transfer Difference to Holding") );
			end_form();
		}
	}
	function Inventory_form()
	{
		//display_notification( __method__ . ":" . __LINE__  );
  //      	global $Refs;
		$this->title = "Inventory Stock Taking";
		$_SESSION['page_title'] = _($help_context = $this->title);
		$this->to_location = $this->holdtank;

                start_form();
		if( isset(  $_POST['AddedID'] ) )
		{
			echo "View transfer <href=" . $this->path_to_root . "/inventory/view/view_transfer.php?trans_no=" . $_POST['AddedID'] . ">" . $_POST['AddedID'] . "</a><br />"; 
		}
		$this->display_inventory_header( "Inventory_form" );
		$this->display_adjustment_items(_("Items for adjustment"), TRUE, FALSE, "inventory");
		$this->adjustment_options_controls();
		end_form();
	
                start_form();
	    	div_start('items_table_buttons');
		//hidden( 'action', 'call_overunder' );
		submit_center_first('call_overunder', _("Show Over and Under Stock Count ") );
		submit_center_last('clearcart', _("Clear the cart") );
		div_end();
		end_form();
	
	}
	/*Not currently called?*/
	function config_form()
	{
                start_form(true);
		if( isset(  $_POST['AddedID'] ) )
		{
			echo "View transfer <href=" . $this->path_to_root . "/inventory/view/view_transfer.php?trans_no=" . $_POST['AddedID'] . ">" . $_POST['AddedID'] . "</a><br />"; 
		}
                start_table(TABLESTYLE2, "width=40%");
                $th = array("Config Variable", "Value");
                table_header($th);
                $k = 0;
                alt_table_row_color($k);
                        /* To show a labeled cell...*/
                        //label_cell("Table Status");
                        //if ($this->found) $table_st = "Found";
                        //else $table_st = "<font color=red>Not Found</font>";
                        //label_cell($table_st);
                        //end_row();
		//This currently only puts text boxes on the config screen!
                foreach( $this->config_values as $row )
                {
                                text_row($row['label'], $row['pref_name'], $this->$row['pref_name'], 20, 40);
                }
                end_table(1);
                if (!$this->found) {
                    hidden('action', 'create');
                    submit_center('create', 'Create Table');
                } else {
                    hidden('action', 'update');
                    submit_center('update', 'Update Configuration');
                }
                end_form();
	}
	function init_tables_form()
	{
            	display_notification("init tables form");
		$this->call_table( 'init_tables_completed_form', "Init Tables" );
	}

	function init_tables_completed_form()
	{
     		display_notification("init tables complete form created " . parent::init_tables_completed() . " tables");
	}
	function process_form()
	{
		//display_notification( __method__ . ":" . __LINE__  );
		$this->title = "Process Inventory Transfer";
  		$_SESSION['page_title'] = _($help_context = $this->title);
                start_form();
		//display_notification( __LINE__ . " Process Form" );
		$this->reference = 'auto';
		if( $this->can_process() )
			$this->process();
		else
			display_error( "Can't process :( " );
		end_form();
	}
	function display_adjustment_items($title, $doEdit = TRUE, $showStock = FALSE, $action = "inventory" )
	{
	        display_heading($title);
	    	div_start('items_table');
	        start_table(TABLESTYLE, "width=80%");
		$th = array(_("Item Code"), _("Item Description"), _("Quantity"), _("Unit"));
		if( isset( $this->showPricebookPrice ) AND $this->showPricebookPrice == TRUE AND $doEdit )
		{
			$th[] = "Price";
		}
		//We should only have showStock OR doEdit, but you never know
		if( $showStock )
		{
	        									$th[] = "QOH"; $th[] = "Diff";
		}
		if( $doEdit )
		{
	        									$th[] = ""; $th[] = "";
		}
		$linecount = count($this->line_items);
	        if ( $linecount )
		{
			echo "Line Count: " . $linecount;
		}
		table_header($th);
		$total = 0;
		$k = 0;  //row colour counter
	        $id = find_submit('Edit');	//Looks for Edit## and returns the ## portion.
		$line_no = 0;
	       	if ( $linecount )
		{
	        	foreach ($this->line_items as $stock_item)
		        {
		                if ($id != $line_no)
		                {
					if( $stock_item['stock_id'] != null )
					{
		                                alt_table_row_color($k);
		
			                        view_stock_status_cell($stock_item['stock_id']);
						if( isset( $stock_item['item_description'] ) )
			                        	label_cell($stock_item['item_description']);
						else
			                        	label_cell( "" );
			                	qty_cell($stock_item['counted'], false, get_qty_dec($stock_item['stock_id']));
						if( isset( $stock_item['units'] ) )
			                        	label_cell($stock_item['units']);
						else
							label_cell( "" );
						if( isset( $this->showPricebookPrice ) AND $this->showPricebookPrice == TRUE AND $doEdit )
						{
							if( isset( $stock_item['pricebookPrice'] ) )
			                        		label_cell($stock_item['pricebookPrice']);
							else
								label_cell( "-1" );
						}
						if( "inventory" == $action )
						{
							if( isset( $stock_item['qoh'] ) )
			                			$stock_item['quantity'] = $stock_item['counted'] - $stock_item['qoh'];
							else
			                			$stock_item['quantity'] = $stock_item['counted'];
							$this->line_items[$line_no]['quantity'] = -$stock_item['quantity'];
						}
						else if( "xfer" == $action )
						{
							$this->line_items[$line_no]['quantity'] = $stock_item['qoh'];
						}
						else
						{
							$this->line_items[$line_no]['quantity'] = 0;
						}

						if( $showStock )
						{
							if( isset( $stock_item['qoh'] ) )
			                        		label_cell($stock_item['qoh']);
							else
			                        		label_cell( "" );
			                		qty_cell($stock_item['quantity'], false, get_qty_dec($stock_item['stock_id']));
						}

						if( $doEdit )
						{	
									//name, value, title
							//edit_button_cell("Edit$line_no", _("Edit"), _('Edit document line' . $line_no));
							label_cell( "" ); //In place of the edit button since there is a bug!
			                        	delete_button_cell("Delete$line_no", _("Delete"), _('Remove line ' . $line_no . 'from document'));
						}
			                        end_row();
						$line_no++;
					}
		                }
		                else
		                {
		                        $this->adjustment_edit_item_controls($line_no);
		                }
		        }
		}
	
	        if ($id == -1 AND $doEdit)
	                $this->adjustment_edit_item_controls(0);
	    	end_table();
/*
 *Something like this to indicate over/unders
	        if ($low_stock)
	                display_note(_("Marked items have insufficient quantities in stock as on day of adjustment."), 0, 1, "class='stockmankofg'");
 */
	        div_end();
	}
	/*Not currently called?*/
	function display_inventory_summary($title, &$inventory, $editable_items=false)
	{
		//display_notification( __method__ . ":" . __LINE__  );

	        display_heading($title);
	
	    div_start('items_table');
	        start_table(TABLESTYLE, "colspan=7 width=90%");
	        $th = array(_("Item Code"), _("Item Description"), _("Quantity"), _("Unit"), "");
	
	        if ($inventory->trans_no == 0) {
	        unset( $th[3] );
	        }
	
	        if ( count($this->line_items) )
	             $th[]= '';
	
	        table_header($th);
	
	        $total = 0;
	        $k = 0;  //row colour counter
	
	        $id = find_submit('Edit');
	
	        $low_stock = $inventory->check_qoh($_POST['InventoryDate'], $_POST['location']);
	        foreach ($inventory->get_items() as $line_no=>$stock_item)
	        {
	
	                $line_total = round($stock_item->quantity_dispatched * $stock_item->price * (1 - $stock_item->discount_percent),
	                   user_price_dec());
	
	                $qoh_msg = '';
	                if (!$editable_items || $id != $line_no)
	                {
	                        if (in_array($stock_item->stock_id, $low_stock))
	                                start_row("class='stockmankobg'");      // notice low stock status
	                        else
	                                alt_table_row_color($k);
	
	                        view_stock_status_cell($stock_item->stock_id);
	                        //label_cell($stock_item->item_description, "nowrap" );
	                        label_cell($stock_item->item_description );
	                        $dec = get_qty_dec($stock_item->stock_id);
	                        if ($inventory->trans_no!=0)
	                                qty_cell($stock_item->quantity_done, false, $dec);
	
	                        label_cell($stock_item->units);
	
	                        if ($editable_items)
	                        {
	                                edit_button_cell("Edit$line_no", _("Edit"),
	                                _('Edit document line'));
	                                delete_button_cell("Delete$line_no", _("Delete"),
	                                _('Remove line from document'));
	                        }
	                        end_row();
	                }
	                else
	                {
	                        //sales_inventory_item_controls($inventory, $k,  $line_no);
	                }
	
	                $total += $line_total;
	        }
	
	        if ($id==-1 && $editable_items)
	                //sales_inventory_item_controls($inventory, $k);
			$this->adjustment_edit_item_controls($line_no=-1);

	
	        $colspan = 6;
	        if ($inventory->trans_no!=0)
	                ++$colspan;
	        start_row();
	        submit_cells('update', _("Update"), "colspan=2 align='center'", _("Refresh"), true);
	        end_row();
	
	        end_table();
/*
	        if ($low_stock)
	                display_note(_("Marked items have insufficient quantities in stock as on day of delivery."), 0, 1, "class='stockmankofg'");
*/
	
	    div_end();
	}

	
	function adjustment_edit_item_controls($line_no=-1)
	{
	        global $Ajax;
	        start_row();
	
	        $dec2 = 0;
		$amount = 1;
		$units = "ea";
	        $id = find_submit('Edit');
		if( $id >= 0 )
		{
			//We are editing a line item
			$stock_id = $this->line_items[$id]['stock_id'];
		}
	        if ($line_no != -1 && $line_no == $id)
	        {
			//Editing THIS item.
	                hidden('stock_id', $stock_id);
		        view_stock_status_cell($stock_id);
	                label_cell($this->line_items[$id]['item_description'], 'nowrap');
	                hidden('LineNo', $line_no);
			//quantity cell not in original...
			if( isset( $this->line_items[$id]['counted'] ) )
				$amount = $this->line_items[$id]['counted'];
			if( isset( $this->line_items[$id]['units'] ) )
	        		$units = $this->line_items[$id]['units'];
	        }
	        else
	        {
			//Not editing an existing line item (lineno was passed in -1 from calling routine
			//and/or the Edit# isn't the same.
			//display_notification( __LINE__ . " DEBUG Blank (new) item " );
			//
			stock_costable_items_list_cells(null, 'stock_id', null, false, false);
			//		label, name, selected_id, all_option, submit_on_change, show_inactive, editkey 
			//sales_items_list_cells(null,'stock_id', null, false, false, false, false); 
	                if (list_updated('stock_id')) 
			{
	                            $Ajax->activate('counted');
	                }
			small_qty_cells(null, 'counted', $amount, null, null, 1);
			label_cell($units);
			if( isset( $this->showPricebookPrice ) AND $this->showPricebookPrice == TRUE  )
				label_cell("");;
		        $Ajax->activate('items_table');
		        $Ajax->activate('items_table_buttons');
		        if ($id != -1)
		        {
		                button_cell('UpdateItem', _("Update"),
		                                _('Confirm changes'), ICON_UPDATE);
		                button_cell('CancelItemChanges', _("Cancel"),
		                                _('Cancel changes'), ICON_CANCEL);
		                hidden('LineNo', $line_no);
		                set_focus('counted');
		        }
		        else
		        {
				label_cell("");;
		                submit_cells('AddItem', _("Add Item"), "colspan=2",
		                    _('Add new item to document'), true);
		        }
		
		        end_row();
		}
	}
	function adjustment_options_controls()
	{
	}
	function table_memo_field()
	{
	          echo "<br>";
	          start_table();
	          textarea_row(_("Memo"), 'memo_', null, 50, 3);
	          end_table(1);
	}
	function processing_start()
	{
	    $this->page_processing(false);
	    $this->processing_end();
	    $_SESSION['Processing'] = $_SERVER['PHP_SELF'];
	}
	
	function processing_end()
	{
	        $this->page_processing(true);
	    unset($_SESSION['Processing']);
	}
	/*not currently called*/	
	function confirm_dialog($submit, $msg) {
	        if (find_post($submit)) {
	                display_warning($msg);
	                br();
	                submit_center_first('DialogConfirm', _("Proceed"), '', true);
	                submit_center_last('DialogCancel', _("Cancel"), '', 'cancel');
	                return 0;
	        } else
	                return get_post('DialogConfirm', 0);
	}
	
	function page_processing($msg = false)
	{
		/*
	        	Block menu/shortcut links during transaction procesing.
		*/
	        global $Ajax;
	
	        if ($msg === true)
	                $msg = _('Entered data has not been saved yet.\nDo you want to abandon changes?');
	
	        $js = "_validate._processing=" . (
	                $msg ? '\''.strtr($msg, array("\n"=>'\\n')) . '\';' : 'null;');
	        if (in_ajax()) {
	                $Ajax->addScript(true, $js);
	        } else
	                add_js_source($js);
	}
	
	function page_modified($status = true)
	{
	        global $Ajax;
	
	        $js = "_validate._modified=" . ($status ? 1:0).';';
	        if (in_ajax()) {
	                $Ajax->addScript(true, $js);
	        } else
	                add_js_source($js);
		//header( "Location: " . $_SERVER['REQUEST_URI'] );
		echo '<script>parent.window.location.reload(true);</script>';
	}


}
?>
