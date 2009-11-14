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

if (empty($_REQUEST['post_id']) || !$session->logged)  {
	notification ("Invalid request", $_SERVER['HTTP_REFERER'], 3);
	die();
}

$db = new nullBB_Database ($_CONF, $_LANG);
$post_id = getInt($_REQUEST['post_id']);

$res = $db->query('select * from '.$_CONF['dbprefix'].'posts where post_id='.$post_id);
$db->freeResult();

if (empty($res))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['no_posts'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$topic_id = getInt($res[0]['topic_id']);
$poster_id = getInt($res[0]['poster_id']);
$post_time = getInt($res[0]['post_time']);
unset ($res);

if ($_GET['delete'])  {
	if (! $session->logged )  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['cannot_delete_post'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if ($poster_id != $userinfo['user_id'] && $userinfo['user_group'] > USERLEV_MOD)  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['cannot_delete_post'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if ($userinfo['user_group'] < USERLEV_MOD)  {
		$res = $db->query('select count(*) as num from '.$_CONF['dbprefix'].'posts where '.
			'topic_id='.$topic_id.' and post_time > '.$post_time);
		$db->freeResult();
		$num = getInt($res[0]['num']);

		if ($num > 0)  {
			require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
			notification ($_LANG['cannot_delete_post'], $_SERVER['HTTP_REFERER'], 3);
			die();
		}
	}

	$db->query('delete from '.$_CONF['dbprefix'].'posts where post_id='.$post_id);

	$res = $db->query('select count(*) as num from '.$_CONF['dbprefix'].'posts where topic_id='.$topic_id);
	$db->freeResult();
	$num = getInt($res[0]['num']);

	if ($num == 0)
		$db->query('delete from '.$_CONF['dbprefix'].'topics where topic_id='.$topic_id);

	$db->close();
	header ("Location: ".$_SERVER['HTTP_REFERER']);
	die();
}

$res = $db->query('select poster_id from '.$_CONF['dbprefix'].'posts '.
	"where post_id='".getInt($_POST['post_id'])."'");
$db->freeResult();

if ($res[0]['poster_id'] != $userinfo['user_id'] && $userinfo['user_group'] > USERLEV_MOD)  {
	notification ("Invalid user", $_SERVER['HTTP_REFERER'], 3);
	die();
}

$db->query('update '.$_CONF['dbprefix'].'posts set post_content='."'".
	sanitizeQuery($_POST['post_content'])."' where post_id='".
	getInt($_POST['post_id'])."'");

$res = $db->query('select count(*) as num from '.$_CONF['dbprefix'].'posts where topic_id='.$topic_id.' '.
	'and post_time > '.$post_time);
$db->freeResult();
$num = getInt($res[0]['num']);

if ($num > 0)  {
	$db->query ('update '.$_CONF['dbprefix'].'posts set post_lastedit_date='.time().', '.
		'post_lastedit_user='.$userinfo['user_id'].' where post_id='.$post_id);
}

$db->close();
header ("Location: ".$_SERVER['HTTP_REFERER']);
die();

?>

