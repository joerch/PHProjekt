<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Search Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_SearchController_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test of json get a results
     */
    public function testJsonSeacrchAction()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');
        $config->language = "de";

        $request->setParams(array('action'=>'jsonGetTags','controller'=>'Search','module'=>'Default'));

        $request->setBaseUrl($config->webpath.'index.php/Default/Search/jsonSearch/words/note');
        $request->setPathInfo('/Default/Search/jsonSearch/words/note');
        $request->setRequestUri('/Default/Search/jsonSearch/words/note');

        // getting the view information
        $request->setModuleKey('module');
        $request->setControllerKey('controller');
        $request->setActionKey('action');
        $request->setDispatched(false);

        $view = new Zend_View();
        $view->addScriptPath(PHPR_CORE_PATH . '/Default/Views/scripts/');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        $viewRenderer->setViewBasePathSpec(':moduleDir/Views');
        $viewRenderer->setViewScriptPathSpec(':action.:suffix');

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        // Languages Set
        Zend_Loader::loadClass('Phprojekt_Language', PHPR_CORE_PATH);
        $translate = new Phprojekt_Language($config->language);

        // Front controller stuff
        $front = Zend_Controller_Front::getInstance();
        $front->setDispatcher(new Phprojekt_Dispatcher());

        $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        $front->setDefaultModule('Default');

        foreach (scandir(PHPR_CORE_PATH) as $module) {
            $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module;

            if (is_dir(!$dir)) {
                continue;
            }

            if (is_dir($dir . DIRECTORY_SEPARATOR . 'Controllers')) {
                $front->addModuleDirectory($dir);
            }

            $helperPath = $dir . DIRECTORY_SEPARATOR . 'Helpers';

            if (is_dir($helperPath)) {
                $view->addHelperPath($helperPath, $module . '_' . 'Helpers');
                Zend_Controller_Action_HelperBroker::addPath($helperPath);
            }
        }

        Zend_Registry::set('view', $view);
        $view->webPath  = $config->webpath;
        Zend_Registry::set('translate', $translate);

        $front->setModuleControllerDirectoryName('Controllers');
        $front->addModuleDirectory(PHPR_CORE_PATH);

        $front->setParam('useDefaultControllerAlways', true);

        $front->throwExceptions(true);

        // Getting the output, otherwise the home page will be displayed
        ob_start();

        $front->dispatch($request, $response);
        $response = ob_get_contents();

        ob_end_clean();
        
        // checking some parts of the index template
        $this->assertTrue(strpos($response, '"id":"1","moduleId":"1","moduleName":"Project","firstDisplay":"test"') > 0);
    }

    
}