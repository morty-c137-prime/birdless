<?php
/**
 * @author Xunnamius <me@xunn.io>
 *
 * Globally useful view helpers and other classes get initialized here
 */

use Application\View\Helper\CacheBuster;
use Application\View\Helper\TitleHelper;

$services->view->titleHelper = new TitleHelper();
$services->view->titleHelper->append(SITE_BASE_TITLE);
$services->view->cacheBuster = new CacheBuster();

Flight::view()->set('cacheBuster', $services->view->cacheBuster);
Flight::view()->set('titleHelper', $services->view->titleHelper);
