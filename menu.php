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

if (! $session->logged)  {

?>

<link href="<?php print BASEDIR.'themes/'.$_CONF['theme']; ?>/style.css" rel="stylesheet" type="text/css">

<?php

} else {

?>

<link href="<?php print BASEDIR.'themes/'.$userinfo['user_theme']; ?>/style.css" rel="stylesheet" type="text/css">

<?php

}

$db  = new nullBB_Database($_CONF, $_LANG);

print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.'">~ Forum home</li>';
print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.'users.'.PHPEXT.'">~ '.$_LANG['user_list'].'</li>';
print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.'search.'.PHPEXT.'">~ '.$_LANG['search'].'</li>';
print '<li class="contestmenu" style="border-bottom: 3px solid #fff"></li>';

if (!$logged)  {
	print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.'register.'.PHPEXT.'">~ Register</li>';
	print '<li class="contestmenu"><a class="contestmenu" href="javascript:popLogin('."'".BASEDIR."'".')">~ Log in</li>';
} else {
	print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.'logout.'.PHPEXT.'">~ Logout ['.sanitizeHTML($userinfo['username']).']</li>';
	$basedir = preg_replace ('/\//', '', BASEDIR);
	
	print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.'newposts.'.PHPEXT.'">~ '.$_LANG['new_messages'].'</li>';
	print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.'privmsg.'.PHPEXT.'">~ '.$_LANG['privmsg'];

	$res = $db->query('select count(*) as num from '.$_CONF['dbprefix'].'privmsgs where privmsg_to='.getInt($userinfo['user_id']).' and privmsg_seen=0');
	$db->freeResult();
	$num = getInt($res[0]['num']);

	if ($num > 0)  {
		print ' <b>('.$num.'</b> new)';
	}
	
	print '</li>';
	print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.'profile.'.PHPEXT.'">~ '.$_LANG['user_panel'].'</li>';

	if (preg_match('/\/'.$basedir.'\/([0-9]+)/', $_SERVER['HTTP_REFERER'], $match))  {
		$forum_id = getInt($match[1]);
		$res = $db->query('select forum_postgroup from '.$_CONF['dbprefix'].'forums '.
			"where forum_id='".$forum_id."'");
		$db->freeResult();
		$forum_postgroup = getInt($res[0]['forum_postgroup']);
		unset($res);

		if ($userinfo['user_group'] <= $forum_postgroup)  {
			print '<li class="contestmenu"><a class="contestmenu" href="javascript:newTopic('.
				BASEDIR.','.$forum_id.')">~ New topic</a></li>';
		}
	}
}
	
print '<li class="contestmenu" style="border-bottom: 3px solid #fff"></li>';

$res = $db->query('select forum_id, forum_name, forum_viewgroup from '.$_CONF['dbprefix'].'forums');

foreach ($res as $row)  {
	$forum_viewgroup = getInt($row['forum_viewgroup']);
	
	if ($forum_viewgroup < USERLEV_ANY)  {
		if ( $session->logged )  {
			if ($userinfo['user_group'] <= $forum_viewgroup)  {
				print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.$row['forum_id'].'">'.$row['forum_name'].'</a></li>'."\n";
			}
		}
	} else {
		print '<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.$row['forum_id'].'">'.$row['forum_name'].'</a></li>'."\n";
	}
}

if ($session->logged)  {
	if (!$userinfo['user_disabled'] && $userinfo['user_group'] <= USERLEV_ADMIN)  {
		print '<li class="contestmenu" style="border-bottom: 3px solid #fff"></li>'.
			'<li class="contestmenu"><a class="contestmenu" href="'.BASEDIR.
			'admin/index.'.PHPEXT.'">'.$_LANG['admin_panel'].'</a></li>';
	}
}

?>

