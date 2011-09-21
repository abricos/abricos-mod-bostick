<?php
/**
 * Схема таблиц данного модуля.
 * 
 * @version $Id$
 * @package Abricos
 * @subpackage Bostick
 * @copyright Copyright (C) 2011 Abricos. All rights reserved.
 * @author  Alexander Kuzmin (roosit@abricos.org)
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = CMSRegistry::$instance->modules->updateManager; 
$db = CMSRegistry::$instance->db;
$pfx = $db->prefix;

if ($updateManager->isInstall()){
	CMSRegistry::$instance->modules->GetModule('bostick')->permission->Install();
	
	// Список стикеров
	$db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."bostk_stick (
		  `stickid` int(10) unsigned NOT NULL auto_increment COMMENT 'Идентификатор стикера',
		  `userid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор автора',
		  `body` TEXT NOT NULL  COMMENT 'Текст стикера',
		  `color` varchar(6) NOT NULL DEFAULT '' COMMENT 'Цвет',
		  `region` varchar(36) NOT NULL DEFAULT '' COMMENT 'Цвет',
          `dateline` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата/время создания',
		  `deldate` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата/время удаления',
		  PRIMARY KEY  (`stickid`),
		  KEY `userid` (`userid`)
		)".$charset
	);
	
}

?>