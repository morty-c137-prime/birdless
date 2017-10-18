<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

Flight::set('flight.case_sensitive', FLIGHT_CASE_SENSITIVE);
Flight::set('flight.handle_errors', FLIGHT_HANDLE_ALL_ERR);
Flight::set('flight.log_errors', FLIGHT_LOG_ERRORS);
Flight::set('flight.views.path', FLIGHT_VIEW_PATH);
Flight::set('flight.views.extension', FLIGHT_VIEW_EXT);
Flight::set('flight.controllers.path', FLIGHT_ROUTE_PATH);

Flight::view()->set('APP_ENV', APP_ENV);
