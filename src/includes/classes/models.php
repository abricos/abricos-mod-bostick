<?php
/**
 * @package Abricos
 * @subpackage Bostick
 * @copyright 2011-2015 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License (MIT)
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class BostickSticker
 *
 * @property int $id User ID
 * @property int $role User Role Value
 */
class BostickSticker extends AbricosModel {
    protected $_structModule = 'bostick';
    protected $_structName = 'Sticker';
}

/**
 * Class BostickStickerList
 * @method BostickSticker Get(int $stickerid)
 * @method BostickSticker GetByIndex(int $index)
 */
class BostickStickerList extends AbricosModelList {

}
