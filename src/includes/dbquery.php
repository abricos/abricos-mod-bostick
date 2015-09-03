<?php
/**
 * @package Abricos
 * @subpackage Bostick
 * @copyright 2011-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License (MIT)
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Все запросы к базе данных модуля Стикер
 */
class BostickQuery {
	
	/**
	 * Получить данные стикера из базы
	 * @param Ab_Database $db
	 * @param integer $userid идентификатор пользователя
	 * @param integer $stickid идентификатор стикера
	 * @param boolean $retarray вернуть массив если true, иначе указатель на результат запроса
	 * @return mixed возвращает значение исходя из параметра $retarray
	 */
	public static function Stick(Ab_Database $db, $userid, $stickid, $retarray = false){
		$sql = "
			SELECT
				stickid as id,
				body as bd,
				region as rg
			FROM ".$db->prefix."bostk_stick 
			WHERE userid=".bkint($userid)." AND stickid=".bkint($stickid)."
		";
		return ($retarray ? $db->query_first($sql) : $db->query_read($sql));
	}

	/**
	 * Получить список стикеров из базы
	 * @param Ab_Database $db
	 * @param integer $userid идентификатор пользователя
	 * @return integer возвращает указать на результат запроса
	 */
	public static function StickList(Ab_Database $db, $userid){
		$sql = "
			SELECT
				stickid as id,
				body as bd,
				region as rg
			FROM ".$db->prefix."bostk_stick 
			WHERE userid=".bkint($userid)." AND deldate=0
			ORDER BY dateline
		";
		return $db->query_read($sql);
	}
	
	/**
	 * Добавить стикер в базу
	 * @param Ab_Database $db
	 * @param integer $userid идентификатор пользователя
	 * @param object $sk данные стикера
	 */
	public static function StickAppend(Ab_Database $db, $userid, $sk){
		$sql = "
			INSERT INTO ".$db->prefix."bostk_stick 
			(userid, body, color, region, dateline) VALUES (
				".bkint($userid).",
				'".bkstr($sk->bd)."',
				'fefeb4',
				'".bkstr($sk->rg)."',
				".TIMENOW."
			)
		";
		$db->query_write($sql);
		return $db->insert_id();
	}

	/**
	 * Обновить стикер а базе
	 * @param Ab_Database $db
	 * @param integer $userid идентификатор пользователя
	 * @param integer $stickid идентификатор стикера
	 * @param object $sk данные стикера
	 */
	public static function StickUpdate(Ab_Database $db, $userid, $stickid, $sk){
		$sql = "
			UPDATE ".$db->prefix."bostk_stick
			SET 
				body='".bkstr($sk->bd)."',
				region='".bkstr($sk->rg)."' 
			WHERE userid=".bkint($userid)." AND stickid=".bkint($stickid)."
		";
		$db->query_write($sql);
	}
	
	/**
	 * Удалить стикер из базы
	 * @param Ab_Database $db
	 * @param integer $userid идентификатор пользователя
	 * @param integer $stickid идентификатор стикера
	 */
	public static function StickRemove(Ab_Database $db, $userid, $stickid){
		$sql = "
			UPDATE ".$db->prefix."bostk_stick
			SET 
				deldate=".TIMENOW." 
			WHERE userid=".bkint($userid)." AND stickid=".bkint($stickid)."
		";
		$db->query_write($sql);
	}
}
?>