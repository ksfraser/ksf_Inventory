<?php
define ('SS_Inventory', 114<<8);

/*************************************************//**
 * class hooks_Inventory extends hooks as required by the
 * FrontAccounting API.
 *
 * ***************************************************/
class hooks_Inventory extends hooks {
	var $module_name = 'Inventory'; //!< Module Name so that I only have to replace in one place for easier copy and paste to new modules

	/**//**
		Install additonal menu options provided by module
		@param app object
		@return nothing
	*/
	function install_options($app) {
		global $path_to_root;

		switch($app->id) {
			case 'stock':
				$app->add_rapp_function(2, _('Inventory Taking'), 
					$path_to_root.'/modules/Inventory/Inventory.php', 'SA_Inventory');
				break;
		}
	}

	/**//**
	 * Install_access sets up the Security settings for FA
	 * @param none
	 * @return array
	 * */
	function install_access()
	{
		$security_sections[SS_Inventory] = _("Inventory");

		$security_areas['SA_Inventory'] = array(SS_Inventory|101, _("Inventory"));

		return array($security_areas, $security_sections);
	}
}
?>
