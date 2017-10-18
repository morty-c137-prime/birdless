<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

/////////////////////////////
// Configuration constants //
/////////////////////////////

const APP_ENV_OVERRIDE       = NULL;
const SITE_BASE_TITLE        = '';

const FLIGHT_ROUTE_PATH      = 'src/flight/route/';
const FLIGHT_VIEW_PATH       = 'src/flight/view/';
const FLIGHT_VIEW_EXT        = '.phtml';
const FLIGHT_LOG_ERRORS      = true;
const FLIGHT_HANDLE_ALL_ERR  = true;
const FLIGHT_CASE_SENSITIVE  = true;

define('APP_ENV', empty(APP_ENV_OVERRIDE) ? (empty($_SERVER['APP_ENV'])
                                              ? (empty($_SERVER['APPLICATION_ENV'])
                                                  ? 'unknown'
                                                  : $_SERVER['APPLICATION_ENV'])
                                              : $_SERVER['APP_ENV'])
                                          : APP_ENV_OVERRIDE);
