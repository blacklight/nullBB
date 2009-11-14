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
require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);

?>

<h2><?php print $_LANG['admin_panel']; ?></h2><br>

<script src="admin.js" type="text/javascript" language="javascript"></script>
<div class="adminMain">

<?php

require_once ('adminmenu.php');

print '<div class="adminBody" id="adminBody">';

switch ($_GET['action'])  {
	case 'user':
		print $_LANG['enter_user_name'].'<br><input type="text" name="username"> or '.
			'<a href="javascript:searchUser()"> '.$_LANG['search_one'].'</a><br>'."\n".
			"<button onClick='editUser(".
			"document.getElementsByName(\"username\")[0].value)'>". $_LANG['search'].'</button>';
		break;

	case 'forum':
		require_once (ABSOLUTE_BASEPATH.'/db.'.PHPEXT);
		$db = new nullBB_Database($_CONF, $_LANG);
		$res = $db->query('select forum_id, forum_name, forum_desc from '.$_CONF['dbprefix'].'forums '.
			'order by forum_vieworder');
		$db->freeResult();

		print '<a href="javascript:newForum()" style="padding-left: 10px">'.$_LANG['new_forum'].'</a><br>'.
			'<form action="editforum.'.PHPEXT.'?action=new" method="POST">'.
			'<div id="newforum" style="padding-left: 10px"></div><br>'.
			'</form>'.
			'<table class="forumlist">'."\n";

		foreach ($res as $row)  {
			print '<tr class="forumlist">'.
				'<td class="forumlist">'.
				'<span id="name'.getInt($row['forum_id']).'">'.
				'<a href="/forum/'.getInt($row['forum_id']).'">'.
				sanitizeHTML($row['forum_name']).'</a></span><br>'.
				'<span id="desc'.getInt($row['forum_id']).'">'.
				sanitizeHTML($row['forum_desc']).
				'</span></td>'.
				'<td class="forumlist" style="text-align: right">'.
				'<select id="'.$row['forum_id'].'" name="forumaction" value="none" onChange="editForum(this)">'.
				'<option value="none">-- '.$_LANG['choose_action'].'</option>'.
				'<option value="delete">'.$_LANG['remove_forum'].'</option>'.
				'<option value="move_up">'.$_LANG['move_up'].'</option>'.
				'<option value="move_down">'.$_LANG['move_down'].'</option>'.
				'<option value="edit_name">'.$_LANG['edit_name'].'</option>'.
				'<option value="edit_desc">'.$_LANG['edit_desc'].'</option>'.
				'<option value="edit_priv">'.$_LANG['edit_priv'].'</option>'.
				'</select></td>'.
				'</tr>';
		}

		print '</table>';
		$db->close();
		break;

	case 'group':
		require_once (ABSOLUTE_BASEPATH.'/db.'.PHPEXT);
		$db = new nullBB_Database($_CONF, $_LANG);
		$res = $db->query('select * from '.$_CONF['dbprefix'].'groups '.
			'order by group_id');
		$db->freeResult();
		$defaultGroups = array(USERLEV_GOD, USERLEV_ADMIN, USERLEV_GLOBALMOD, USERLEV_MOD,
			USERLEV_USER, USERLEV_ANY, USERLEV_BANNED);

		print '<a href="javascript:newGroup()" style="padding-left: 10px">'.$_LANG['new_group'].'</a><br>'.
			'<form action="editgroup.'.PHPEXT.'?action=new" method="POST">'.
			'<div id="newgroup" style="padding-left: 10px"></div><br>'.
			'</form>'.
			'<table class="forumlist">'."\n";

		foreach ($res as $row)  {
			print '<tr class="forumlist">'.
				'<td class="forumlist">'.
				'<span id="name'.getInt($row['group_id']).'"';

			if (!in_array($row['group_id'], $defaultGroups))
				print ' style="color: #990000"';

			print '>'.sanitizeHTML($row['group_name']).'</span><br>'.
				'</td>'.
				'<td class="forumlist" style="text-align: right">'.
				'<select id="'.$row['group_id'].'" name="groupaction" value="none" onChange="editGroup(this)">'.
				'<option value="none">-- '.$_LANG['choose_action'].'</option>';

			if (!in_array($row['group_id'], $defaultGroups))
				print '<option value="delete">'.$_LANG['remove_group'].'</option>';

			print '<option value="edit_name">'.$_LANG['edit_name'].'</option>'.
				'</select></td>'.
				'</tr>';
		}

		print '</table>';
		$db->close();
		break;

	case 'dump':
		print $_LANG['generate_dump'];
		print '<meta http-equiv="Refresh" content="0;url='.BASEDIR.'admin/dump.sql">';
		break;

	default :
		print $_LANG['admin_greeting']; break;
}

print '</div>';

?>

</div>

