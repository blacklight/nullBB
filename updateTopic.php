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

if (empty($_REQUEST['topic_id']) || !$session->logged)  {
	notification ("Invalid request", $_SERVER['HTTP_REFERER'], 3);
	die();
}

$db = new nullBB_Database ($_CONF, $_LANG);
$topic_id = getInt($_REQUEST['topic_id']);

$res = $db->query('select * from '.$_CONF['dbprefix'].'topics where topic_id='.$topic_id);
$db->freeResult();

if (empty($res))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['invalid_topic'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$topic_disabled = getInt($res[0]['topic_disabled']);

if ($_GET['lock'])  {
	if ( !$session->logged || $userinfo['user_group'] > USERLEV_MOD )  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['lock_error'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if ($topic_disabled)  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['already_locked'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$db->query ('update '.$_CONF['dbprefix'].'topics set topic_disabled=1 where topic_id='.$topic_id);
	$db->freeResult();
	$db->close();

	header ("Location: ".$_SERVER['HTTP_REFERER']);
	exit(0);
} else if ($_GET['unlock'])  {
	if ( !$session->logged || $userinfo['user_group'] > USERLEV_MOD )  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['lock_error'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if (!$topic_disabled)  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['already_unlocked'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$db->query ('update '.$_CONF['dbprefix'].'topics set topic_disabled=0 where topic_id='.$topic_id);
	$db->freeResult();
	$db->close();

	header ("Location: ".$_SERVER['HTTP_REFERER']);
	exit(0);
} else if ($_GET['delete'])  {
	if ( !$session->logged || $userinfo['user_group'] > USERLEV_MOD )  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['lock_error'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$replies = $db->query('select count(*) as num from '.$_CONF['dbprefix'].'posts where topic_id='.$topic_id);
	$db->freeResult();
	$replies = getInt($replies[0]['num']);

	if ($replies > 0)
		$db->query ('delete from '.$_CONF['dbprefix'].'posts where topic_id='.$topic_id);

	$db->query ('delete from '.$_CONF['dbprefix'].'topics where topic_id='.$topic_id);
	$db->freeResult();
	
	header ("Location: ".$_SERVER['HTTP_REFERER']);
	$db->close();
	exit(0);
} else if ($_GET['stick'])  {
	if ( !$session->logged || $userinfo['user_group'] > USERLEV_MOD )  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['stick_error'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$db->query ('update '.$_CONF['dbprefix'].'topics set topic_sticked=1 where topic_id='.$topic_id);
	$db->freeResult();
	
	header ("Location: ".$_SERVER['HTTP_REFERER']);
	$db->close();
	exit(0);
} else if ($_GET['unstick'])  {
	if ( !$session->logged || $userinfo['user_group'] > USERLEV_MOD )  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['stick_error'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$db->query ('update '.$_CONF['dbprefix'].'topics set topic_sticked=0 where topic_id='.$topic_id);
	$db->freeResult();
	
	header ("Location: ".$_SERVER['HTTP_REFERER']);
	$db->close();
	exit(0);
}

?>

