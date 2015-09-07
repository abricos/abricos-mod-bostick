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
                "icon" => "/modules/bostick/images/bostick-24.png",
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