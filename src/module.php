<?php
/**
 * @package Abricos
 * @subpackage Bostick
 * @copyright 2011-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License (MIT)
 * @author Alexander Kuzmin <roosit@abricos.org>
 */


/**
 * Класс модуля "Стикер"
 */
class BostickModule extends Ab_Module {

    private $_manager = null;

    /**
     * Конструктор
     */
    public function __construct(){
        // версия модуля
        $this->version = "0.1.3";
        // наименование модуля (идентификатор его)
        $this->name = "bostick";
        // имя раздела в адресной строке (http://youdomain.tld/bostick/*)
        $this->takelink = "bostick";
        // роли доступа
        $this->permission = new BostickPermission($this);
    }

    /**
     * Управляющий менеджер модуля. Все AJAX запросы этого модуля
     * будут переданы в этот класс.
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
     * Стартовый шаблон для сборки страницы.
     * Вызывается когда браузер заппросит страницу по адресу http://youdomain.tld/bostick/*
     *
     * @return string
     */
    public function GetContentName(){
        // стартовые шаблоны находятся в папке модуля content
        // для этого модуля это будет папка /modules/bostick/content
        if (Abricos::$user->IsRegistred() == 0){
            // для гостей собираем страницу из шаблона index_guest.html
            return "index_guest"; // /modules/bostick/content/index_guest.html
        }
        // для зарегистрированных пользователекй собираем страницу из index.html
        return "index"; // /modules/bostick/content/index.html
    }

    public function Bos_IsMenu() {
        return true;
    }

    public function Bos_IsExtension() {
        return true;
    }
}

/**
 * Класс констант ролей модуля Стикер
 */
class BostickAction {
    /**
     * Роль на запись стикера (создание/удаление/редактирование)
     *
     * @var integer
     */
    const WRITE = 30;
}

/**
 * Менеджер ролей модуля Стикер
 */
class BostickPermission extends Ab_UserPermission {

    /**
     * Конструктор
     *
     * @param BostickModule $module
     */
    public function BostickPermission(BostickModule $module){
        // объявление ролей по умолчанию
        // используется при инсталяции модуля в платформе
        $defRoles = array(
            new Ab_UserRole(BostickAction::WRITE, Ab_UserGroup::REGISTERED),
            new Ab_UserRole(BostickAction::WRITE, Ab_UserGroup::ADMIN)
        );
        parent::__construct($module, $defRoles);
    }

    /**
     * Получить роли
     */
    public function GetRoles(){
        return array(
            BostickAction::WRITE => $this->CheckAction(BostickAction::WRITE)
        );
    }
}

// создать экземляр класса модуля и зарегистрировать его в ядре 
Abricos::ModuleRegister(new BostickModule());

?>