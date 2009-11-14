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

require_once ('../config.ini');
require_once ('admin_head.'.PHPEXT);

$action = $_REQUEST['action'];

switch ($action)  {
	case 'new':
	case 'delete':
	case 'edit_name':
		break;

	default:
		die();
		break;
}

$defaultGroups = array(USERLEV_GOD, USERLEV_ADMIN, USERLEV_GLOBALMOD, USERLEV_MOD,
	USERLEV_USER, USERLEV_ANY, USERLEV_BANNED);

$gid = getInt($_REQUEST['gid']);
$db = new nullBB_Database ($_CONF, $_LANG);

switch ($action)  {
	case 'delete':
		if (in_array($gid, $default_groups))  {
			print '<script>alert("'.$_LANG['no_delete_default_group'].'")</script>'.
				'<meta http-equiv="Refresh" value="0;url='.$_SERVER['HTTP_REFERER'].'">';
			die();
		}

		$db->query('update '.$_CONF['dbprefix'].'users set user_group='.USERLEV_USER.' where user_group='.$gid);
		$db->query('delete from '.$_CONF['dbprefix'].'groups where group_id='.$gid);
		header ('Location: '.$_SERVER['HTTP_REFERER']);
		break;

	case 'new':
		$name = sanitizeQuery($_REQUEST['group_name']);

		if (!$name)
			die();

		$res = $db->query('select group_id, group_name from '.$_CONF['dbprefix'].'groups where group_id = '.$gid);

		if (!empty($res))  {
			print '<script>alert("'.$_LANG['group_id_already_exists'].': '.sanitizeHTML($res[0]['group_name']).'")</script>'.
				'<meta http-equiv="Refresh" value="0;url='.$_SERVER['HTTP_REFERER'].'">';
			die();
		}

		$db->query('insert into '.$_CONF['dbprefix'].'groups(group_id, group_name) values('.$gid.", '".$name."')");
		header ('Location: '.$_SERVER['HTTP_REFERER']);
		break;

	case 'edit_name':
		$value = sanitizeQuery($_REQUEST['value']);

		if (!$value)
			die();

		$db->query('update '.$_CONF['dbprefix']."groups set group_name='".$value."' ".
			'where group_id='.$gid);
		break;
}

$db->close();

?>

