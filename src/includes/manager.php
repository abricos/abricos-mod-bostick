<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage Bostick
 * @copyright Copyright (C) 2011 Brickos Ltd. All rights reserved.
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

require_once 'dbquery.php';

/**
 * Управляющий класс модуля
 */
class BostickManager extends Ab_ModuleManager {
	
	/**
	 * @var BostickModule
	 */
	public $module = null;
	
	/**
	 * @var BostickManager
	 */
	public static $instance = null; 
	
	/**
	 * Конструктор
	 * @param BostickModule $module
	 */
	public function __construct(BostickModule $module){
		parent::__construct($module);
		
		BostickManager::$instance = $this;
	}
	
	/**
	 * Есть ли доступ текущего пользователя на запись стикеров
	 */
	public function IsWriteRole(){
		return $this->IsRoleEnable(BostickAction::WRITE);
	}
	
	/**
	 * Обработчик AJAX запросов
	 * @param object $d данные запроса
	 */
	public function AJAX($d){
		switch($d->do){
			case 'init': return $this->InitData();
			case 'sticksave': return $this->StickSave($d->stick);
			case 'stickremove': return $this->StickRemove($d->stickid);
			case 'stickordupd': return $this->StickOrderUpdate($d->order);
		}
		return null;
	}

	/**
	 * Проверить доступ текущего пользователя к стикер
	 * @param integer $stickid идентификатор стикера
	 */
	public function StickAccess($stickid){
		if (!$this->IsWriteRole()){ return false; }
		$row = BostickQuery::Stick($this->db, $this->userid, $stickid, true);
		return !empty($row);
	}
	
	/**
	 * Вернуть данные для инициализации виджета
	 * @return object
	 */
	public function InitData(){
		if (!$this->IsWriteRole()){
			return null;
		}
		$ret = new stdClass();
	
		$ret->sticks = array();
		$rows = BostickQuery::StickList($this->db, $this->userid);
		while (($row = $this->db->fetch_array($rows))){
			$ret->sticks = $row;
		}
		
		$ord = $this->StickOrderConfigRow();
		$ret->orders = is_null($ord) ? '' : $ord['vl'];
		
		return $ret;
	}
	
	/**
	 * Сохранить стикера
	 * @param object $sk данные стикера отправленые виджетом
	 */
	public function StickSave($sk){
		if (!$this->IsWriteRole()){ return null; }
		
		if ($sk->id == 0){
			$sk->id = BostickQuery::StickAppend($this->db, $this->userid, $sk);
		}else{
			if (!$this->StickAccess($sk->id)){
				return null;
			}
			BostickQuery::StickUpdate($this->db, $this->userid, $sk->id, $sk);
		}
		return BostickQuery::Stick($this->db,  $this->userid, $sk->id, true);
	}
	
	/**
	 * Удалить стикера
	 * @param integer $stickid идентификатор стикера
	 */
	public function StickRemove($stickid){
		if (!$this->IsWriteRole()){ return null; }
		BostickQuery::StickRemove($this->db, $this->userid, $stickid);
	}
	
	
	/*  сортировка стикеров будет храниться в специализированной таблице пользовательских настроек */
	
	public function StickOrderConfigRow(){
		$uman = Abricos::$user->GetManager();
		$rows = $uman->UserConfigList($this->userid, 'bostick');
		while (($row = $this->db->fetch_array($rows))){
			if ($row['nm'] == "stickorder"){
				return $row;
			}
		}
		return null;
	}
	
	public function StickOrderUpdate($order){
		if (!$this->IsWriteRole()){ return null; }
		
		$uman = Abricos::$user->GetManager();
		$rowcfg = $this->StickOrderConfigRow();
		
		if (is_null($rowcfg)){
			$uman->UserConfigAppend($this->userid, 'bostick', "stickorder", $order);
		}else{
			$uman->UserConfigUpdate($this->userid, $rowcfg['id'], $order);
		}
	}
}

?>