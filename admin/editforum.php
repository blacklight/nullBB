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
	case 'edit_desc':
	case 'edit_privs':
	case 'move_up':
	case 'move_down':
	case 'view_privs':
		break;

	default:
		die();
		break;
}

$fid = getInt($_REQUEST['fid']);
$db = new nullBB_Database ($_CONF, $_LANG);

switch ($action)  {
	case 'delete':
		if (!$fid)
			die();

		$db->query('delete from '.$_CONF['dbprefix'].'posts where forum_id='.$fid);
		$db->query('delete from '.$_CONF['dbprefix'].'topics where forum_id='.$fid);
		$db->query('delete from '.$_CONF['dbprefix'].'forums where forum_id='.$fid);
		break;

	case 'new':
		$name = sanitizeQuery($_REQUEST['forum_name']);
		$desc = sanitizeQuery($_REQUEST['forum_desc']);

		if (!$name || !$desc)
			die();

		$res = $db->query('select forum_id from '.$_CONF['dbprefix'].'forums where forum_id >= all('.
			'select forum_id from '.$_CONF['dbprefix'].'forums)');
		$vieworder = (empty($res)) ? 1 : getInt($res[0]['forum_id']) + 1;

		$db->query('insert into '.$_CONF['dbprefix'].'forums(forum_name, forum_desc, forum_vieworder) values('.
			"'$name','$desc',$vieworder)");
		$db->close();

		header ('Location: '.$_SERVER['HTTP_REFERER']);
		break;

	case 'move_up':
		if (!$fid)
			die();

		$res = $db->query('select * from '.$_CONF['dbprefix'].'forums where forum_id='.$fid);

		if (empty($res))
			die();

		$res2 = $db->query('select forum_id, forum_vieworder from '.$_CONF['dbprefix'].'forums where forum_vieworder < '.
			getInt($res[0]['forum_vieworder']).' and forum_vieworder >= all('.
			'select forum_vieworder from '.$_CONF['dbprefix'].'forums where forum_vieworder < '.
			getInt($res[0]['forum_vieworder']).')');

		$db->query('update '.$_CONF['dbprefix'].'forums set forum_vieworder='.getInt($res[0]['forum_vieworder']).
			' where forum_id='.getInt($res2[0]['forum_id']));
		
		$db->query('update '.$_CONF['dbprefix'].'forums set forum_vieworder='.getInt($res2[0]['forum_vieworder']).
			' where forum_id='.getInt($res[0]['forum_id']));
		break;

	case 'move_down':
		if (!$fid)
			die();

		$res = $db->query('select * from '.$_CONF['dbprefix'].'forums where forum_id='.$fid);

		if (empty($res))
			die();

		$res2 = $db->query('select forum_id, forum_vieworder from '.$_CONF['dbprefix'].'forums where forum_vieworder > '.
			getInt($res[0]['forum_vieworder']).' and forum_vieworder <= all('.
			'select forum_vieworder from '.$_CONF['dbprefix'].'forums where forum_vieworder > '.
			getInt($res[0]['forum_vieworder']).')');

		$db->query('update '.$_CONF['dbprefix'].'forums set forum_vieworder='.getInt($res[0]['forum_vieworder']).
			' where forum_id='.getInt($res2[0]['forum_id']));
		
		$db->query('update '.$_CONF['dbprefix'].'forums set forum_vieworder='.getInt($res2[0]['forum_vieworder']).
			' where forum_id='.getInt($res[0]['forum_id']));
		break;

	case 'edit_name':
		$value = sanitizeQuery($_REQUEST['value']);

		if (!$fid || !$value)
			die();

		$db->query('update '.$_CONF['dbprefix']."forums set forum_name='".$value."' ".
			'where forum_id='.$fid);
		break;

	case 'edit_desc':
		$value = sanitizeQuery($_REQUEST['value']);

		if (!$fid || !$value)
			die();

		$db->query('update '.$_CONF['dbprefix']."forums set forum_desc='".$value."' ".
			'where forum_id='.$fid);
		break;

	case 'view_privs':
		if (!$fid)
			die();

		$groups = array();
		$res = $db->query('select * from '.$_CONF['dbprefix'].'groups');
	
		foreach ($res as $row)
			$groups[$row['group_id']] = $row['group_name'];

		$xml = '<?xml version="1.0" encoding="UTF-8" ?>';
		$res = $db->query('select forum_name, forum_viewgroup, forum_postgroup from '.$_CONF['dbprefix'].
			'forums where forum_id='.$fid);
		$res = $res[0];

		$xml .= '<forum id="'.$fid.'" name="'.$res['forum_name'].'">'.
			'<priv id="view" value="'.getInt($res['forum_viewgroup']).'" name="'.
			sanitizeHTML($groups[$res['forum_viewgroup']]).'"></priv>'.
			'<priv id="post" value="'.getInt($res['forum_postgroup']).'" name="'.
			sanitizeHTML($groups[$res['forum_postgroup']]).'"></priv>';

		foreach ($groups as $id => $name)
			$xml .= '<group id="'.getInt($id).'" name="'.sanitizeHTML($name).'"></group>';

		$xml .= '</forum>';
		print $xml;
		break;

	case 'edit_privs':
		if (!$fid)
			die();

		$view_privs = getInt($_REQUEST['view_privs']);
		$post_privs = getInt($_REQUEST['post_privs']);

		$db->query('update '.$_CONF['dbprefix'].'forums set forum_viewgroup='.
			$view_privs.' where forum_id='.$fid);
		
		$db->query('update '.$_CONF['dbprefix'].'forums set forum_postgroup='.
			$post_privs.' where forum_id='.$fid);

		header ('Location: '.$_SERVER['HTTP_REFERER']);
		break;
}

$db->close();

?>

