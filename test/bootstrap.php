<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

const BOOTSTRAP_NO_ROUTES = TRUE;
const BOOTSTRAP_NO_SERVICES = TRUE;

// XXX: We take the public version rather than src because of potential preproccessing
require __DIR__ . '/../public/index.php';
