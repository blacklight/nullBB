<?php

/**************************************************************************************************
 * nullBB - Light CMS forum                                                                       *
 * Copyright (C) 2009, BlackLight                                                                 *
 *                                                                                                *
 * This program is free software: you can redistribute it and/or modify it under the terms of the *
 * GNU General Public License as published by the Free Software Foundation, either version 3 of   *
 * the License, or (at your option) any later version. This program is distributed in the hope    *
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for    *
 * more details. You should have received a copy of the GNU General Public License along with     *
 * this program. If not, see <http://www.gnu.org/licenses/>.                                      *
 **************************************************************************************************/

if ($UNCONFIGURED)  {
	die ("Configure your installation by modifying your config.ini file, then access again");
}

require_once (ABSOLUTE_BASEPATH.'/languages/'.BOARD_LANGUAGE.'.lang');
require_once(ABSOLUTE_BASEPATH.'/utils.'.PHPEXT);
require_once(ABSOLUTE_BASEPATH.'/session.'.PHPEXT);

if ($session->logged)  {
	if ($userinfo['user_language'] && file_exists(ABSOLUTE_BASEPATH.'/languages/'.$userinfo['user_language'].'.lang')
				&& preg_match('/^[a-zA-Z0-9_]+$/', $userinfo['user_language']))  {
		require_once (ABSOLUTE_BASEPATH.'/languages/'.$userinfo['user_language'].'.lang');
	}
}

$_CONF['headname'] = '/'.$_CONF['title'].'/<blink>_</blink>';

?>

<html>
	<head>
		<title><?php print $_CONF['title']; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<?php

if (!$session->logged)  {

?>
		<link href="<?php print BASEDIR.'themes/'.$_CONF['theme']; ?>/style.css" rel="stylesheet" type="text/css">

<?php

} else {

?>

		<link href="<?php print BASEDIR.'themes/'.$userinfo['user_theme']; ?>/style.css" rel="stylesheet" type="text/css">

<?php

}

?>

		<script src="<?php print BASEDIR; ?>main.js" language="javascript" type="text/javascript"></script>
	
		<script type="text/javascript" language="javascript">
			if (document.layers)  {
				document.captureEvents(event.MOUSEDOWN);
				document.captureEvents(event.MOUSEUP);
			}
		</script>
	</head>

	<body
		onContextMenu = 'return false'
		onLoad='
			document.onContextMenu = new function()  { return false; };
			var menu = null;

			if ((menu = document.getElementById("menu")))
				document.body.removeChild(menu);'

		onMouseDown="captureClick(event, '<?php print BASEDIR; ?>')"
		onMouseUp="disableContextMenu()">

		<div class="container">

<?php

?>

		<h1 class="maintitle" align="center"><a class="maintitle" href="/"><?php print $_CONF['headname']; ?></a></h1><br>
		<center><span style="font-size: 9px"><?php print $_LANG['right_click_menu']; ?></span></center>
		<br><br>

<?php

if ($session->logged)  {
	$db = new nullBB_Database($_CONF, $_LANG);
	$res = $db->query('select count(*) as num from '.$_CONF['dbprefix'].'privmsgs where privmsg_to='.getInt($userinfo['user_id']).' and '.
	'privmsg_seen=0');
	$db->freeResult();
	$num = getInt($res[0]['num']);

	if ($num > 0)  {
		print '<script language="javascript" type="text/javascript">alert('.
			"'You have ".$num." unread messages');</script>";
	}

	$db->close();
}

?>

