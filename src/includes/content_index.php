<?php
/**
 * @package Abricos
 * @subpackage Bostick
 * @copyright 2011-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License (MIT)
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

// кирпич - шаблон, который вызвал этот скрипт
$brick = Brick::$builder->brick;

// информация о текущем пользователе
$user = Abricos::$user;

$unm = $user->username;
$lnm = $user->lastname;
$fnm = $user->firstname;

$username = empty($lnm) && empty($fnm) ? $unm : $fnm."&nbsp;".$lnm;

// в теле шаблона есть переменные {v#userid} и {v#username}, их 
// нужно заменить на соответствющие значения
$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "userid" => $user->id,
    "username" => $username
));
