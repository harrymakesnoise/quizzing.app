<?
include_once('config.core.php');

/* DATABASE */

/* ENCRYPTION STUFFS */

define('CONFIG_CONSTANTS', true);


/* Question / Round Types */
define('RQTYPE_STANDARD',    1);
define('RQTYPE_PICTURE',     2);
define('RQTYPE_MULTICHOICE', 3);

define('DEFAULT_AVATAR', 'no-avatar.png');


/* Websocket stuff */
define('WS_PORT', (defined('DEBUG') && DEBUG ? 8090 : 8080));