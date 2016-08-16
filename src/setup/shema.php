<?php
/**
 * @package Abricos
 * @subpackage Bostick
 * @copyright 2011-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License (MIT)
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current;
$db = Abricos::$db;
$pfx = $db->prefix;

// первое обращение к модулю, произвести его инсталляцию
if ($updateManager->isInstall()){
    // инсталлировать роли модуля
    Abricos::GetModule('bostick')->permission->Install();

    // Создать таблицу стикеров в базе
    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."bostk_stick (
		  stickid int(10) unsigned NOT NULL auto_increment COMMENT 'Идентификатор стикера',
		  userid int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор автора',
		  body TEXT NOT NULL  COMMENT 'Текст стикера',
		  color varchar(6) NOT NULL DEFAULT '' COMMENT 'Цвет',
		  region varchar(36) NOT NULL DEFAULT '' COMMENT 'Region: `x,y,w,h`',
		  ord int(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Order',

          dateline int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата/время создания',
		  deldate int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата/время удаления',

		  PRIMARY KEY  (stickid),
		  KEY userid (userid)
		)".$charset
    );
}

if (!$updateManager->isInstall() && $updateManager->isUpdate('0.1.3')){
    $db->query_write("
		ALTER TABLE ".$pfx."bostk_stick
		ADD  ord int(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Order'
	");
}
