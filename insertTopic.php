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

require_once('./config.ini');
require_once (ABSOLUTE_BASEPATH.'/languages/'.BOARD_LANGUAGE.'.lang');
require_once (ABSOLUTE_BASEPATH.'/db.'.PHPEXT);
require_once (ABSOLUTE_BASEPATH.'/utils.'.PHPEXT);
require_once (ABSOLUTE_BASEPATH.'/session.'.PHPEXT);

if ( ! $session->logged )  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['not_logged_in'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

if ($userinfo['user_disabled'])  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['disabled_user'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

if (!isset($_POST['forum_id']) || !is_numeric($_POST['forum_id']))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['invalid_forum'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

if (!$_POST['topic_title'] || empty($_POST['topic_title']))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['empty_topic_title'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$db  = new nullBB_Database($_CONF, $_LANG);

$forum_id = getInt($_POST['forum_id']);
$forum = $db->query('select forum_id from '.$_CONF['dbprefix'].'forums where '.
	'forum_id = '.$forum_id);
$db->freeResult();

if (empty($forum))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['invalid_forum'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$res = $db->query('select forum_postgroup from '.$_CONF['dbprefix'].'forums where '.
	"forum_id='".$forum_id."'");
$db->freeResult();
$forum_postgroup = getInt($res[0]['forum_postgroup']);
unset($res);

if ($userinfo['user_group'] > $forum_postgroup)  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['insufficient_privileges'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$user_id  = getInt($session->user_id);
$topic_title = sanitizeQuery($_POST['topic_title']);

unset($forum);

$db->query('insert into '.$_CONF['dbprefix'].'topics(forum_id, topic_title, topic_poster, topic_time) values('.
	"'$forum_id', '$topic_title', '$user_id', '".time()."')");

$res = $db->query('select topic_id from '.$_CONF['dbprefix'].'topics order by topic_id desc limit 1');
$db->freeResult();

$_POST['topic_id'] = $res[0]['topic_id'];
$topic_id = getInt($_POST['topic_id']);
unset($res);

$db->close();
require_once (ABSOLUTE_BASEPATH.'/insertPost.'.PHPEXT);

?>
