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

if (!isset($_POST['topic_id']) || !is_numeric($_POST['topic_id']))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['invalid_topic'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

if (!$_POST['content'] || empty($_POST['content']))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['empty_post'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$topic_id = getInt($_POST['topic_id']);
$user_id  = getInt($session->user_id);

$db = new nullBB_Database($_CONF, $_LANG);
$content = sanitizeQuery($_POST['content']);

$forum = $db->query('select forum_id from '.$_CONF['dbprefix'].'topics where '.
	'topic_id = '.$topic_id);
$db->freeResult();

if (empty($forum))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['invalid_topic'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$forum_id = getInt($forum[0]['forum_id']);
$ip = sanitizeQuery($_SERVER['REMOTE_ADDR']);
$so = sanitizeQuery(getSO($_SERVER['HTTP_USER_AGENT']));
$browser = sanitizeQuery(getBrowser($_SERVER['HTTP_USER_AGENT']));

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

$res = $db->query('select topic_disabled from '.$_CONF['dbprefix'].'topics '.
	"where topic_id='$id'");
$db->freeResult();
$topic_disabled = getInt($res[0]['topic_disabled']);
unset($res);

if ($topic_disabled)  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['topic_disabled'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$db->query('insert into '.$_CONF['dbprefix'].'posts(topic_id, forum_id, poster_id, '.
	'post_time, post_content, poster_ip, poster_so, poster_browser) '.
	"values('$topic_id', '$forum_id', '$user_id', ".time().
	", '$content', '$ip', '$so', '$browser')");

$res = $db->query('select count(*) as numposts from '.$_CONF['dbprefix'].
	"posts where topic_id='".$topic_id."'");
$numPosts = getInt($res[0]['numposts']);

$res = $db->query('select post_id as maxpost from '.$_CONF['dbprefix'].'posts where '.
	"topic_id='$topic_id' order by post_id desc limit 1");
$maxPost = getInt($res[0]['maxpost']);

unset($res);
$db->freeResult();
$db->close();

if ($numPosts >= 10)  {
	$lastPage = ((int) (($numPosts-1)/10) + 1);
	header ('Location: '.BASEDIR."topic/$topic_id/$lastPage#$maxPost");
} else
	header ('Location: '.BASEDIR."topic/$topic_id/1#$maxPost");

?>

