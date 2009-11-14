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
require_once (ABSOLUTE_BASEPATH.'/session.'.PHPEXT);

if (!$_GET['msg_id'] || !$session->logged)  {
	print "Invalid request";
	die();
}

if ($_GET['del'])  {
	$db = new nullBB_Database ($_CONF, $_LANG);
	$res = $db->query('select * from '.$_CONF['dbprefix'].'privmsgs '.
			"where privmsg_id=".getInt($_GET['msg_id'])." and privmsg_to=".getInt($userinfo['user_id']));
	$db->freeResult();

	if (empty($res))  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['privmsg_not_found'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$db->query('delete from '.$_CONF['dbprefix'].'privmsgs where privmsg_id='.getInt($_GET['msg_id']).' '.
		'and privmsg_to='.getInt($userinfo['user_id']));
	
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['delete_privmsg_ok'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$db = new nullBB_Database ($_CONF, $_LANG);
$res = $db->query('select * from '.$_CONF['dbprefix'].'privmsgs '.
	"where privmsg_id=".getInt($_GET['msg_id'])." and privmsg_to=".getInt($userinfo['user_id']));
$db->freeResult();

if (empty($res))  {
	print $_LANG['privmsg_not_found'];
	die();
}

$res = $res[0];

if ($res['privmsg_seen'] == false)
	$db->query('update '.$_CONF['dbprefix'].'privmsgs set privmsg_seen=1 '.
		'where privmsg_id='.getInt($_GET['msg_id']).' and privmsg_to='.getInt($userinfo['user_id']));

$from = $db->query('select username from '.$_CONF['dbprefix'].'users where user_id='.getInt($res['privmsg_from']));
$db->freeResult();

$from = sanitizeHTML($from[0]['username']);
$to = sanitizeHTML($userinfo['username']);
$content = bb2html($res['privmsg_content']);

print '<li class="privmsgHead">'.$_LANG['from'].': '.
	'<a href="'.BASEDIR.'user/'.getInt($res['privmsg_from']).'">'.
	$from."</a></li>\n";
print '<li class="privmsgHead">'.$_LANG['to'].': '.
	'<a href="'.BASEDIR.'user/'.getInt($res['privmsg_to']).'">'.
	$to."</a></li><br>\n";

print '<div class="privmsgContent">'.$content."</div><br>\n";

unset($res);
$db->close();

?>

