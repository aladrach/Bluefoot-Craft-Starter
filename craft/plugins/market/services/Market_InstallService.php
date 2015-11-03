<?php
namespace Craft;

/**
 * Class Market_InstallService
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2015, Pixel & Tonic, Inc.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @see       http://buildwithcraft.com/commerce
 * @package   craft.plugins.commerce.services
 * @since     1.0
 */
class Market_InstallService extends BaseApplicationComponent
{

    public function install()
    {

        $this->_createTables();
        $this->_addForeignKeys();
    }

    private function _createTables()
    {
    }

    private function _addForeignKeys()
    {
    }
}