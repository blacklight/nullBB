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
require_once (ABSOLUTE_BASEPATH.'/db.'.PHPEXT);
require_once (ABSOLUTE_BASEPATH.'/utils.'.PHPEXT);
require_once (ABSOLUTE_BASEPATH.'/session.'.PHPEXT);

if (empty($_GET['post_id']))
	die("Invalid request");

if ($_GET['addr'])  {
	if (!$session->logged)  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['insufficient_privileges'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}
	
	$db = new nullBB_Database ($_CONF, $_LANG);
	$post_id = getInt($_GET['post_id']);
	$res = $db->query('select poster_id from '.$_CONF['dbprefix'].'posts where post_id='.$post_id);
	$db->freeResult();
	$poster_id = getInt($res[0]['poster_id']);

	if ($userinfo['user_group'] > USERLEV_MOD && $userinfo['user_id'] != $poster_id)  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['insufficient_privileges'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$res = $db->query('select poster_ip from '.$_CONF['dbprefix'].'posts where post_id='.$post_id);
	$db->freeResult();

	$addr = sanitizeHTML($res[0]['poster_ip']);
	print $addr;
	unset ($res);
	exit(0);
}

$db = new nullBB_Database ($_CONF, $_LANG);
$res = $db->query('select post_content from '.$_CONF['dbprefix'].'posts '.
	"where post_id='".getInt($_GET['post_id'])."'");
$db->freeResult();

print htmlspecialchars($res[0]['post_content']);
unset($res);
$db->close();

?>

