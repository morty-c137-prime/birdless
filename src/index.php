<?php

/**
 * @author Xunnamius <me@xunn.io>
 *
 * Borrowed heavily from the ZF3 philosophy
 */

///////////////////////////
// Define core constants //
///////////////////////////

const GLOBAL_CONFIG_PATH = 'src/config/';
const GLOBAL_SERVICE_PATH = 'src/services/';
define('APPLICATION_ROOT', dirname(__DIR__));

/////////////////////////////////
// Define the application core //
/////////////////////////////////

class Bootstrap
{
    /**
     * Load any autoloading struts
     */
    static function loadAutoload()
    {
        return require 'vendor/autoload.php';
    }

    /**
     * Load all configs
     */
    static function loadConfig($vars = [])
    {
        extract($vars);

        $configs = array_diff(scandir(GLOBAL_CONFIG_PATH), ['..', '.', '.gitignore']);
        natsort($configs);

        foreach($configs as $file)
            require GLOBAL_CONFIG_PATH . '/' . $file;

        return $configs;
    }

    /**
     * Load and configure the service structure
     */
    static function loadServices($vars = [])
    {
        extract($vars);

        $configs = array_diff(scandir(GLOBAL_SERVICE_PATH), ['..', '.', '.gitignore']);
        natsort($configs);

        foreach($configs as $file)
            require GLOBAL_SERVICE_PATH . '/' . $file;

        return $configs;
    }

    /**
     * Load flight routes/controllers
     */
    static function loadRoutes($vars = [])
    {
        extract($vars);

        $routes = array_diff(scandir(FLIGHT_ROUTE_PATH), ['..', '.']);
        natsort($routes);

        foreach($routes as $file)
            require FLIGHT_ROUTE_PATH . '/' . $file;

        return $routes;
    }
}

///////////////////////////
// Start the application //
///////////////////////////

chdir(APPLICATION_ROOT);

$spl_autoloader = Bootstrap::loadAutoload();
Bootstrap::loadConfig(["spl_autoloader" => $spl_autoloader]);

if(!defined('BOOTSTRAP_NO_SERVICES'))
    Bootstrap::loadServices(["spl_autoloader" => $spl_autoloader]);

if(!defined('BOOTSTRAP_NO_ROUTES'))
{
    Bootstrap::loadRoutes(["spl_autoloader" => $spl_autoloader]);
    Flight::start();
}
