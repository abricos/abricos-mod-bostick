<?php
/**
 * @package Abricos
 * @subpackage Bostick
 * @copyright 2011-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License (MIT)
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

require_once 'models.php';

class Bostick {

    /**
     * @var BostickManager
     */
    public $manager;

    /**
     * @var Ab_Database
     */
    public $db;

    /**
     * @var AbricosModelManager
     */
    public $models;

    protected $_cache = array();

    public function __construct(BostickManager $manager){
        $this->manager = $manager;
        $this->db = $manager->db;

        $models = $this->models = AbricosModelManager::GetManager($manager->module->name);

        $models->RegisterClass('Sticker', 'BostickSticker');
        $models->RegisterClass('StickerList', 'BostickStickerList');
    }

    public function AJAX($d){
        switch ($d->do){
            case "appStructure":
                return $this->AppStructureToJSON();
            case "stickerList":
                return $this->StickerListToJSON();
            case "stickerSave":
                return $this->StickerSaveToJSON($d->sticker);
            case "stickerRemove":
                return $this->StickerRemoveToJSON($d->stickerid);
        }
        return null;
    }

    public function ClearCache(){
        $this->_cache = array();
    }

    private function ResultToJSON($name, $res){
        $ret = new stdClass();

        if (is_integer($res)){
            $ret->err = $res;
            return $ret;
        }
        if (is_object($res) && method_exists($res, 'ToJSON')){
            $ret->$name = $res->ToJSON();
        } else {
            $ret->$name = $res;
        }

        return $ret;
    }

    private function MergeObject($o1, $o2){
        foreach ($o2 as $key => $v2){
            $v1 = $o1->$key;
            if (is_array($v1) && is_array($v2)){
                for ($i = 0; $i < count($v2); $i++){
                    $v1[] = $v2[$i];
                }
                $o1->$key = $v1;
            } else if (is_object($o1->$key) && is_object($o2->$key)){
                $this->MergeObject($o1->$key, $o2->$key);
            } else {
                $o1->$key = $v2;
            }
        }
    }

    private function ImplodeJSON($jsons, $ret = null){
        if (empty($ret)){
            $ret = new stdClass();
        }
        if (!is_array($jsons)){
            $jsons = array($jsons);
        }
        foreach ($jsons as $json){
            $this->MergeObject($ret, $json);
        }
        return $ret;
    }

    public function AppStructureToJSON(){
        if (!$this->manager->IsWriteRole()){
            return 403;
        }

        $res = $this->models->ToJSON('Sticker');
        if (empty($res)){
            return null;
        }

        $ret = new stdClass();
        $ret->appStructure = $res;

        return $ret;
    }

    public function StickerListToJSON(){
        $ret = $this->StickerList();
        return $this->ResultToJSON('stickerList', $ret);
    }

    public function StickerList(){
        if (!$this->manager->IsWriteRole()){
            return 403;
        }

        /** @var MoneyAccountList $list */
        $list = $this->models->InstanceClass('StickerList');

        $rows = BostickQuery::StickList($this->db, Abricos::$user->id);
        while (($d = $this->db->fetch_array($rows))){
            $sticker = $this->models->InstanceClass('Sticker', $d);
            $list->Add($sticker);
        }

        return $list;
    }

    public function StickerSaveToJSON($sk){
        $ret = $this->StickerSave($sk);
        return $this->ResultToJSON('stickerSave', $ret);
    }

    public function StickerSave($sk){
        if (!$this->manager->IsWriteRole()){
            return 403;
        }

        $parser = Abricos::TextParser();
        $sk->id = intval($sk->id);
        $sk->region = strval($sk->region);
        $sk->body = $parser->Parser($sk->body);

        if ($sk->id == 0){
            $sk->id = BostickQuery::StickAppend($this->db, Abricos::$user->id, $sk);
        } else {
            BostickQuery::StickUpdate($this->db, Abricos::$user->id, $sk->id, $sk);
        }

        $ret = new stdClass();
        $ret->stickerid = $sk->id;

        return $ret;
    }

    public function StickerRemoveToJSON($stickerid){
        $ret = $this->StickerRemove($stickerid);
        return $this->ResultToJSON('stickerRemove', $ret);
    }

    public function StickerRemove($stickerid){
        if (!$this->manager->IsWriteRole()){
            return 403;
        }

        BostickQuery::StickRemove($this->db, Abricos::$user->id, $stickerid);

        $ret = new stdClass();
        $ret->stickerid = $stickerid;

        return $ret;
    }
}

?>