<?php

$path_to_root="../..";

require_once( $path_to_root . '/includes/db/connect_db.inc' ); //db_query, ...
require_once( $path_to_root . '/includes/errors.inc' ); //check_db_error, ...

class db_base
{
	var $action;
	var $dbHost;
	var $dbUser;
	var $dbPassword;
	var $dbName;
	var $db_connection;
	var $prefs_tablename;
	var $company_prefix;
        var $config_values = array();   //What fields to be put on config screen

	//function __construct( $host, $user, $pass, $database, $prefs_tablename )
	function __construct( $prefs_tablename )
	{
//		echo "Base constructor prefs_tablename: $prefs_tablename";
/*
 *		$this->set_var( "dbHost", $host );
 *		$this->set_var( "dbUser", $user );
 *		$this->set_var( "dbPassword", $pass );
 *		$this->set_var( "dbName", $database );
 */
		$this->set_var( "prefs_tablename", $prefs_tablename );
		$this->set_prefix();
//		$this->connect_db();
	}
	function set_var( $var, $value )
	{
			$this->$var = $value ;
	}
	function get_var( $var )
	{
		return $this->$var;
	}
/*	function connect_db()
 *	{
 *        	$this->db_connection = mysql_connect($this->dbHost, $this->dbUser, $this->dbPassword);
 *        	if (!$this->db_connection) 
 *		{
 *			display_notification("Failed to connect to source of import Database");
 //*			return FALSE;
 *		}
 *		else
 *		{
 *            		mysql_select_db($this->dbName, $this->db_connection);
 *			return TRUE;
 *		}
 *	}
 */
	/*bool*/ function is_installed()
	{
        	global $db_connections;
        
		$cur_prefix = $db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'];

        	$sql = "SHOW TABLES LIKE '%" . $cur_prefix . $this->prefs_tablename . "%'";
        	$result = db_query($sql, __FILE__ . " could not show tables in is_installed: " . $sql);

        	return db_num_rows($result) != 0;
	}
	function set_prefix()
	{
		if( !isset( $this->company_prefix ) )
		{
			if( strlen( TB_PREF ) == 2 )
			{
				$this->set_var( 'company_prefix', TB_PREF );
			}
			else
			{
        			global $db_connections;
				$this->set_var( 'company_prefix',  $db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'] );
			}
			
		}
	}
	function create_prefs_tablename()
	{
	        $sql = "DROP TABLE IF EXISTS " . $this->company_prefix . $this->prefs_tablename;
		        db_query($sql, "Error dropping table");
		
	    	$sql = "CREATE TABLE `" . $this->company_prefix . $this->prefs_tablename ."` (
		         `name` varchar(32) NOT NULL default \"\",
		         `value` varchar(100) NOT NULL default \"\",
		          PRIMARY KEY  (`name`))
		          ENGINE=MyISAM";
	    	db_query($sql, "Error creating table");
		
	}
	function set_pref( $pref, $value )
	{
	        $sql = "REPLACE " . $this->company_prefix . $this->prefs_tablename . " (name, value) VALUES (".db_escape($pref).", ".db_escape($value).")";
    		db_query($sql, "can't update ". $pref);
	}
	/*string*/ function get_pref( $pref )
	{
        	$pref = db_escape($pref);

    		$sql = "SELECT * FROM " . $this->company_prefix . $this->prefs_tablename . " WHERE name = $pref";
    		$result = db_query($sql, "could not get pref ".$pref);

    		if (!db_num_rows($result))
        		return null;
        	$row = db_fetch_row($result);
    		return $row[1];
	}
        function loadprefs()
        {
                // Get last oID exported
                foreach( $this->config_values as $row )
                {
                        $this->set_var( $row['pref_name'], $this->get_pref( $row['pref_name'] ) );
                }
        }
        function updateprefs()
        {
                foreach( $this->config_values as $row )
                {
                        if( isset( $_POST[$row['pref_name']] ) )
                        {
                                $this->set_var( $row['pref_name'], $_POST[ $row['pref_name'] ] );
                                $this->set_pref( $row['pref_name'], $_POST[ $row['pref_name'] ] );
                        }
                }
        }
}
?>
