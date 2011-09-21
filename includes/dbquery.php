<?php
/**
 * @version $Id$
 * @package Abricos
 * @subpackage Bostick
 * @copyright Copyright (C) 2011 Brickos Ltd. All rights reserved.
 * @author Alexander Kuzmin (roosit@abricos.org)
 */

class BostickQuery {
	
	public static function Stick(CMSDatabase $db, $userid, $stickid, $retarray = false){
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
	
	public static function StickList(CMSDatabase $db, $userid){
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
	
	public static function StickAppend(CMSDatabase $db, $userid, $sk){
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

	public static function StickUpdate(CMSDatabase $db, $userid, $stickid, $sk){
		$sql = "
			UPDATE ".$db->prefix."bostk_stick
			SET 
				body='".bkstr($sk->bd)."',
				region='".bkstr($sk->rg)."' 
			WHERE userid=".bkint($userid)." AND stickid=".bkint($stickid)."
		";
		$db->query_write($sql);
	}
	
	public static function StickRemove(CMSDatabase $db, $userid, $stickid){
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