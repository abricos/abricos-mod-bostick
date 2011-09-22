<?php 
/**
 * @version $Id$
 * @package Abricos
 * @subpackage Bostick
 * @copyright Copyright (C) 2011 Brickos Ltd. All rights reserved.
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

class BostickModule extends CMSModule {
	
	public function __construct(){
		$this->version = "0.1";
		$this->name = "bostick";
		$this->takelink = "bostick";
		$this->permission = new BostickPermission($this);
	}
	
	/**
	 * Получить менеджер
	 *
	 * @return BostickManager
	 */
	public function GetManager(){
		if (is_null($this->_manager)){
			require_once 'includes/manager.php';
			$this->_manager = new BostickManager($this);
		}
		return $this->_manager;
	}
	
	/**
	* Получить имя стартового кирпича (/modules/bostick/content/index.html)
	*
	* @return string
	*/
	public function GetContentName(){
		if (CMSRegistry::$instance->user->info['userid'] == 0){
			return "index_guest";
		}
		return "index";
	}
}

class BostickAction {
	const WRITE	= 30;
}

class BostickPermission extends AbricosPermission {

	public function BostickPermission(BostickModule $module){

		$defRoles = array(
			new AbricosRole(BostickAction::WRITE, UserGroup::REGISTERED),
			new AbricosRole(BostickAction::WRITE, UserGroup::ADMIN)
		);
		parent::__construct($module, $defRoles);
	}

	public function GetRoles(){
		return array(
			BostickAction::WRITE => $this->CheckAction(BostickAction::WRITE)
		);
	}
}


$mod = new BostickModule();
CMSRegistry::$instance->modules->Register($mod);

?>