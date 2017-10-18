<?php
/**
 * @author Xunnamius <me@xunn.io>
 *
 * Make DB configuration a bit less annoying by leveraging our knowns
 */

switch(APP_ENV)
{
    case 'development': /* The local version of "staging" */
    case 'staging': /* The remote version of "development" */
        if(defined('DB_DEVELOP_DSN')) define('DB_DSN', DB_DEVELOP_DSN);
        if(defined('DB_DEVELOP_USER')) define('DB_USER', DB_DEVELOP_USER);
        if(defined('DB_DEVELOP_PASSWD')) define('DB_PASSWD', DB_DEVELOP_PASSWD);
        if(defined('DB_DEVELOP_DBNAME')) define('DB_DBNAME', DB_DEVELOP_DBNAME);
        break;
    
    case 'production':
        if(defined('DB_PRODUCTION_DSN')) define('DB_DSN', DB_PRODUCTION_DSN);
        if(defined('DB_PRODUCTION_USER')) define('DB_USER', DB_PRODUCTION_USER);
        if(defined('DB_PRODUCTION_PASSWD')) define('DB_PASSWD', DB_PRODUCTION_PASSWD);
        if(defined('DB_PRODUCTION_DBNAME')) define('DB_DBNAME', DB_PRODUCTION_DBNAME);
        break;
}
