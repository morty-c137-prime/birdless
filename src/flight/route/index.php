<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

Flight::route('/', function()
{
    Flight::render('application/index', [], 'body_content');
    Flight::render('layout/default');
});
