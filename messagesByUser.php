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

if (!$_GET['user_id'])  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['no_user'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$user_id = getInt($_GET['user_id']);
$db = new nullBB_Database ($_CONF, $_LANG);
$res = $db->query('select username from '.$_CONF['dbprefix'].'users '.
	'where user_id='.$user_id);
$db->freeResult();

if (empty($res))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['user_not_found'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$_POST = array();
$_POST['search'] = true;
$_POST['search_author'] = sanitizeQuery($res[0]['username']);

require_once (ABSOLUTE_BASEPATH.'/search.'.PHPEXT);

?>

