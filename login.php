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

require_once ('./config.ini');
require_once (ABSOLUTE_BASEPATH.'/languages/'.BOARD_LANGUAGE.'.lang');
require_once (ABSOLUTE_BASEPATH.'/utils.'.PHPEXT);
require_once (ABSOLUTE_BASEPATH.'/db.'.PHPEXT);

if (!($_POST['username'] && $_POST['password']))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['wrong_user_pass'], $_SERVER['HTTP_REFERER'], 3);
	die();
} else {
	$user = addslashes(strtolower($_POST['username']));
	$pass = sha1(md5($_POST['password']));

	$db = new nullBB_Database($_CONF, $_LANG);
	$res = $db->query('select * from '.$_CONF['dbprefix']."users where username='".$user.
		"' and user_password='".$pass."'");
	$db->freeResult();
	$db->close();

	if (empty($res))  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['wrong_user_pass'], $_SERVER['HTTP_REFERER'], 3);
		die();
	} else {
		unset($res);
		require_once (ABSOLUTE_BASEPATH.'/session.'.PHPEXT);
		$session = new nullBB_Session(array( 'username' => $user ), $_CONF, $_LANG);
	
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['login_ok'].' '.sanitizeHTML($user), $_SERVER['HTTP_REFERER'], 3);
		die();
	}
}

?>

<meta http-equiv="refresh" content="0;<?php print $_SERVER['HTTP_REFERER']; ?>">

