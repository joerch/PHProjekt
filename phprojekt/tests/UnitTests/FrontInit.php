<?php
/**
 * Unit test
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Index Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class FrontInit extends PHPUnit_Framework_TestCase
{
    public $request  = null;
    public $response = null;
    public $front    = null;
    public $config   = null;
    public $content  = null;
    public $error    = null;
    
    /**
     * Init the front for test it
     */
    public function __construct() 
    {
        $this->request  = new Zend_Controller_Request_Http();
        $this->response = new Zend_Controller_Response_Http();
        $this->config   = Zend_Registry::get('config');
        
        $this->config->language = "en";

        $this->request->setModuleName('Default');
        $this->request->setActionName('index');        

        // Languages Set
        Zend_Loader::loadClass('Phprojekt_Language', PHPR_LIBRARY_PATH);
        $translate = new Phprojekt_Language('en');
        Zend_Registry::set('translate', $translate);

        $view = new Zend_View();
        $view->addScriptPath(PHPR_CORE_PATH . '/Default/Views/dojo/');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        $viewRenderer->setViewBasePathSpec(':moduleDir/Views');
        $viewRenderer->setViewScriptPathSpec(':action.:suffix');

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        /* Front controller stuff */
        $this->front = Zend_Controller_Front::getInstance();
        $this->front->setDispatcher(new Phprojekt_Dispatcher());

        $this->front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        $this->front->setDefaultModule('Default');

        foreach (scandir(PHPR_CORE_PATH) as $module) {
            $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module;

            if (is_dir(!$dir)) {
                continue;
            }

            if (is_dir($dir . DIRECTORY_SEPARATOR . 'Controllers')) {
                $this->front->addModuleDirectory($dir);
            }

            $helperPath = $dir . DIRECTORY_SEPARATOR . 'Helpers';

            if (is_dir($helperPath)) {
                $view->addHelperPath($helperPath, $module . '_' . 'Helpers');
                Zend_Controller_Action_HelperBroker::addPath($helperPath);
            }
        }

        Zend_Registry::set('view', $view);
        $view->webPath  = $this->config->webpath;
        Zend_Registry::set('translate', $translate);

        $this->front->setModuleControllerDirectoryName('Controllers');
        $this->front->addModuleDirectory(PHPR_CORE_PATH);
        $this->front->setParam('useDefaultControllerAlways', true);

        $this->front->throwExceptions(true);        
    }
    
    public function getResponse()
    {
        ob_start();
        $this->error = false;
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $e) {
            $this->error = true;
        }
        $this->content = ob_get_contents();
        ob_end_clean();
        
        return $this->content;
    }
}