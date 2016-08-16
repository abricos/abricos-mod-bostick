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
     *
     * @param Ab_Database $db
     * @param integer $userid идентификатор пользователя
     * @param integer $stickid идентификатор стикера
     * @param boolean $retarray вернуть массив если true, иначе указатель на результат запроса
     * @return mixed возвращает значение исходя из параметра $retarray
     */
    public static function Stick(Ab_Database $db, $userid, $stickid){
        $sql = "
			SELECT s.stickid as id, s.*
			FROM ".$db->prefix."bostk_stick s
			WHERE userid=".bkint($userid)." AND stickid=".bkint($stickid)."
		";
        return $db->query_first($sql);
    }

    /**
     * Получить список стикеров из базы
     *
     * @param Ab_Database $db
     * @param integer $userid идентификатор пользователя
     * @return integer возвращает указать на результат запроса
     */
    public static function StickList(Ab_Database $db, $userid){
        $sql = "
			SELECT s.stickid as id, s.*
			FROM ".$db->prefix."bostk_stick s
			WHERE userid=".bkint($userid)." AND deldate=0
			ORDER BY ord, dateline
		";
        return $db->query_read($sql);
    }

    /**
     * Добавить стикер в базу
     *
     * @param Ab_Database $db
     * @param integer $userid идентификатор пользователя
     * @param object $sk данные стикера
     */
    public static function StickAppend(Ab_Database $db, $userid, $sk){
        $sql = "
			INSERT INTO ".$db->prefix."bostk_stick 
			(userid, body, color, region, ord, dateline) VALUES (
				".bkint($userid).",
				'".bkstr($sk->body)."',
				'fefeb4',
				'".bkstr($sk->region)."',
				".intval($sk->ord).",
				".TIMENOW."
			)
		";
        $db->query_write($sql);
        return $db->insert_id();
    }

    /**
     * Обновить стикер а базе
     *
     * @param Ab_Database $db
     * @param integer $userid идентификатор пользователя
     * @param integer $stickid идентификатор стикера
     * @param object $sk данные стикера
     */
    public static function StickUpdate(Ab_Database $db, $userid, $stickid, $sk){
        $sql = "
			UPDATE ".$db->prefix."bostk_stick
			SET 
				body='".bkstr($sk->body)."',
				region='".bkstr($sk->region)."'
			WHERE userid=".bkint($userid)." AND stickid=".bkint($stickid)."
		";
        $db->query_write($sql);
    }

    /**
     * Удалить стикер из базы
     *
     * @param Ab_Database $db
     * @param integer $userid идентификатор пользователя
     * @param integer $stickid идентификатор стикера
     */
    public static function StickRemove(Ab_Database $db, $userid, $stickid){
        $sql = "
			UPDATE ".$db->prefix."bostk_stick
			SET deldate=".TIMENOW."
			WHERE userid=".bkint($userid)." AND stickid=".bkint($stickid)."
		";
        $db->query_write($sql);
    }

    public static function StickersOrderUpdate(Ab_Database $db, $userid, $orders){
        if (!is_object($orders)){
            return;
        }
        foreach ($orders as $id => $ord){
            $sql = "
                UPDATE ".$db->prefix."bostk_stick
                SET ord=".intval($ord)."
                WHERE userid=".bkint($userid)." AND stickid=".bkint($id)."
            ";
            $db->query_write($sql);

        }
    }
}
