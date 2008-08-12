<?php
/**
 * Represents a module in PHProjekt and coordinates it's mapping
 * to a database
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Represents a module in PHProjekt and coordinates it's mapping
 * to a database
 *
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Module
{
    /**
     * Saves the cache for our module entries, to minimize
     * database lookups
     *
     * @var array
     */
    protected static $_cache = null;

    /**
     * Receives all module <-> id combinations from the database.
     *
     * This is somewhat a pretty stupid caching mechanism, but
     * as the module id itself is used often, we try not to do it
     * using active record.
     *
     * The method returns an array of the following format
     *  array( MODULENAME => MODULEID,
     *         MODULENAME => MODULEID );
     *
     * @todo: Provide a ActiveRecord like interface, but actually don't do
     *        ActiveRecord
     *
     * @return array
     */
    protected static function _getCachedIds()
    {
        if (isset(self::$_cache) && null !== self::$_cache) {
            return self::$_cache;
        }

        $db     = Zend_Registry::get('db');
        $select = $db->select()
                     ->from('Module')
                     ->where('active = 1');
        $stmt = $db->query($select);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
           self::$_cache[$row['name']] = $row['id'];
        }

        if (isset(self::$_cache)) {
            return self::$_cache;
        } else {
            return array();
        }
    }

    /**
     * Returns the id for a given module
     *
     * @param string $name The Module name
     *
     * @return integer
     */
    public static function getId($name)
    {
        $modules = self::_getCachedIds();

        if (array_key_exists($name, $modules)) {
            return $modules[$name];
        }

        return 0;
    }

    /**
     * Returns the name for a given module id
     *
     * @param integer $id The module id
     *
     * @return string
     */
    public static function getModuleName($id)
    {
        $modules = self::_getCachedIds();

        if ((in_array($id, $modules))) {
            return array_search($id, $modules);
        }

        return null;
    }
}