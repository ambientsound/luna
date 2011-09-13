<?php
/*
 * LUNA content management system
 * Copyright (c) 2011, Kim Tore Jensen
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the author nor the names of its contributors may be
 * used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

function debug($data)
{
	echo '<pre>';
	var_dump($data);
	echo '</pre>';
	ob_flush();
}

function diebug($data)
{
	echo '<pre>';
	var_dump($data);
	debug_print_backtrace();
	echo '</pre>';
	ob_flush();
	die;
}

/* Define some paths */
define('LUNA_PATH', realpath(dirname(__FILE__) . '/..'));
define('ADMIN_PATH', realpath(LUNA_PATH . '/admin'));
define('FRONT_PATH', realpath(LUNA_PATH . '/front'));
define('LOCAL_BASE_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/..'));
define('LOCAL_ADMIN_PATH', realpath(LOCAL_BASE_PATH . '/admin'));
define('LOCAL_FRONT_PATH', realpath(LOCAL_BASE_PATH . '/front'));
define('PUBLIC_PATH', realpath(LOCAL_BASE_PATH . '/public'));
define('APPLICATION_PATH', FRONT_PATH);
define('LOCAL_PATH', LOCAL_FRONT_PATH);
define('APPLICATION_TYPE', 'front');

/* This is a production site unless specified otherwise */
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

/* Set our default locale */
setlocale(LC_ALL, 'nb_NO.utf8');
date_default_timezone_set('Europe/Oslo');

/* Ensure our libraries are being included */
set_include_path(join(PATH_SEPARATOR, array(
    realpath(LUNA_PATH. '/library'),
    realpath(LOCAL_BASE_PATH. '/library'),
    get_include_path()
)));

/* Bring up class autoloading */
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Luna_');
$autoloader->registerNamespace('Smarty_');
$autoloader->unshiftAutoloader('smartyAutoload', 'Smarty_');

/* Bring up resource autoloading */
$resourceLoader = new Luna_Loader_Autoloader_Resource(array(
	'basePath'  => array(LOCAL_PATH, APPLICATION_PATH),
	'namespace' => '',
));
$resourceLoader->addResourceType('form', 'forms', 'Form');
$resourceLoader->addResourceType('model', 'models', 'Model');

/* Register our multi-plugin. */
$controller = Zend_Controller_Front::getInstance();
$controller->registerPlugin(new Luna_Controller_Plugin_Localload);

/* Set up our routing */
$routeconfig = Luna_Config::get('routes');
$router = Zend_Controller_Front::getInstance()->getRouter();
$router->addConfig($routeconfig, 'routes');

/* Here we go! */
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap()->run();
