<?php
/**
 * @package Abricos
 * @subpackage Bostick
 * @copyright 2011-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License (MIT)
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

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
     *
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

    private $_bostick = null;

    /**
     * @return Bostick
     */
    public function GetBostick(){
        if (!is_null($this->_bostick)){
            return $this->_bostick;
        }
        require_once 'dbquery.php';
        require_once 'classes/bostick.php';
        $this->_bostick = new Bostick($this);
        return $this->_bostick;
    }

    public function AJAX($d){
        return $this->GetBostick()->AJAX($d);
    }

    /**
     * Проверить доступ текущего пользователя к стикер
     *
     * @param integer $stickid идентификатор стикера
     */
    public function StickAccess($stickid){
        if (!$this->IsWriteRole()){
            return false;
        }
        $row = BostickQuery::Stick($this->db, $this->userid, $stickid, true);
        return !empty($row);
    }

    /**
     * Вернуть данные для инициализации виджета
     *
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
     *
     * @param object $sk данные стикера отправленые виджетом
     */
    public function StickSave($sk){
        if (!$this->IsWriteRole()){
            return null;
        }

        if ($sk->id == 0){
            $sk->id = BostickQuery::StickAppend($this->db, $this->userid, $sk);
        } else {
            if (!$this->StickAccess($sk->id)){
                return null;
            }
            BostickQuery::StickUpdate($this->db, $this->userid, $sk->id, $sk);
        }
        return BostickQuery::Stick($this->db, $this->userid, $sk->id, true);
    }

    /**
     * Удалить стикера
     *
     * @param integer $stickid идентификатор стикера
     */
    public function StickRemove($stickid){
        if (!$this->IsWriteRole()){
            return null;
        }
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
        if (!$this->IsWriteRole()){
            return null;
        }

        $uman = Abricos::$user->GetManager();
        $rowcfg = $this->StickOrderConfigRow();

        if (is_null($rowcfg)){
            $uman->UserConfigAppend($this->userid, 'bostick', "stickorder", $order);
        } else {
            $uman->UserConfigUpdate($this->userid, $rowcfg['id'], $order);
        }
    }

    public function Bos_MenuData(){
        if (!$this->IsWriteRole()){
            return null;
        }
        $i18n = $this->module->I18n();
        return array(
            array(
                "name" => "bostick",
                "title" => $i18n->Translate('bosmenu.title'),
                "descript" => $i18n->Translate('bosmenu.descript'),
                "icon" => "/modules/bostick/images/app_icon.gif",
                "method" => "createSticker",
                "component" => "board"
            )
        );
    }

    public function Bos_ExtensionData(){
        if (!$this->IsWriteRole()){
            return null;
        }
        return array(
            "component" => "board",
            "method" => "initializeBoard"
        );
    }

}

?>