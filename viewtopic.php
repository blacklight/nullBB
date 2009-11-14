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
require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);

?>

<table class="main">

<?php

if (!isset($_GET['id']))
	die ($_LANG['invalid_topic']);

$id = getInt($_GET['id']);

if (!isset($_GET['page']))
	$page = 1;
else
	$page = getInt($_GET['page']);

$start = intval(($page-1)*10);
$db  = new nullBB_Database($_CONF, $_LANG);
$res = $db->query("select * from ".$_CONF['dbprefix']."topics where topic_id='$id'");
$db->freeResult();
$numPosts = getInt($res[0]['topic_replies']) - 1;

if (empty($res))  {
	print '<tr class="forums"><td class="forums">'.$_LANG['invalid_topic'].'</td></tr>';
	print '</table></body></html>';
	exit(0);
}

$forum_id = getInt($res[0]['forum_id']);
$topic_disabled = getInt($res[0]['topic_disabled']);
$res = $db->query('select forum_viewgroup from '.$_CONF['dbprefix'].'forums '.
	"where forum_id='".$forum_id."'");
$db->freeResult();
$forum_viewgroup = getInt($res[0]['forum_viewgroup']);

if ($forum_viewgroup < USERLEV_ANY)  {
	if ( ! $session->logged )  {
		notification ($_LANG['insufficient_privileges'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if ($userinfo['user_group'] > $forum_viewgroup)  {
		notification ($_LANG['insufficient_privileges'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}
}

$res = $db->query("select * from ".$_CONF['dbprefix']."posts where topic_id='$id' order by post_time limit $start,10");
$db->freeResult();

if (empty($res))  {
	print '<tr class="forums"><td class="forums">'.$_LANG['no_topics'].'</td></tr>';
	print '</table></body></html>';
	exit(0);
}

$info = $db->query("select f.forum_id, forum_name, topic_title from ".$_CONF['dbprefix']."forums f join ".$_CONF['dbprefix']."topics t ".
	"on f.forum_id=t.forum_id where t.topic_id='$id' limit 1");
$db->freeResult();

if (!empty($info))  {
	print '<center>';
	print '&gt; <a class="topicHead" href="'.BASEDIR.'">'.$_CONF['title'].' home</a> ';

	if ($info[0]['forum_id'])  {
		$forum_id = intval($info[0]['forum_id']);
		$forum_name = sanitizeHTML($info[0]['forum_name']);
		print "&gt; <a class=\"topicHead\" href=\"".BASEDIR."$forum_id\">$forum_name</a> ";
	}

	$topic_title = sanitizeHTML($info[0]['topic_title']);
	print "&gt; <a class=\"topicHead\" href=\"".BASEDIR."/topic/$id\">$topic_title</a>";
	print '</center><br>';
	unset ($info);
}

if ($numPosts >= 10)  {
	print '<center>';

	for ($i=1; $i <= ((int) ($numPosts/10) + 1); $i++)  {
		if ($i == $page)
			print "$i ";
		else
			print "<a href=\"".BASEDIR."topic/$id/$i\">$i</a> ";
	}
	
	print '</center><br>';
}

if ($session->logged)  {
	$view = $db->query('select * from '.$_CONF['dbprefix'].'viewtopics where user_id='.getInt($userinfo['user_id']).
		' and topic_id='.getInt($id));
	$db->freeResult();

	if (empty($view))
		$db->query('insert into '.$_CONF['dbprefix'].'viewtopics(user_id, topic_id, viewtime) values('.
			getInt($userinfo['user_id']).', '.$id.', '.time().')');
	else
		$db->query('update '.$_CONF['dbprefix'].'viewtopics set viewtime='.time().' where user_id='.
			getInt($userinfo['user_id']).' and topic_id='.$id);
}

foreach ($res as $row)  {
	$user = $db->query('select u.*, g.group_name from '.$_CONF['dbprefix'].'users u join '.$_CONF['dbprefix'].'groups g '.
		' on u.user_group=g.group_id where user_id='.getInt($row['poster_id']));
	$db->freeResult();

	if ($user[0]['user_id'] > 0)  {
?>
	<tr>
		<td class="topicuserinfo"><b><a href="<?php print BASEDIR; ?>user/<?php
		print getInt($user[0]['user_id']); ?>"><?php print sanitizeHTML($user[0]['username']); ?></a></b><br>
		[<?php print sanitizeHTML($user[0]['group_name']); ?>]<br><br>

<?php

	} else {

?>

	<tr>
		<td class="topicuserinfo"><b><span style="color: #a00">[anonymous]</span></b><br>
		[No group]<br><br>

<?php

	}

	if ($userinfo['user_viewavatars'])
		print '<span class="avatar">'.sanitizeHTML($user[0]['user_avatar']).'</span><br>';

?>

		<table style="width: 280px">

		<?php
		
		print '<tr><td class="userinfoentry">'.$_LANG['posts'].': </td><td class="userinfoentry" style="text-align: right">'.$user[0]['user_posts']."</td></tr>\n";
		print '<tr><td class="userinfoentry">'.$_LANG['registered'].': </td><td class="userinfoentry" style="text-align: right">'.
			@date('d M Y, h:i:s a', $user[0]['user_regtime']);
		
		print "</td></tr>\n";

		print '<tr><td class="userinfoentry">'.$_LANG['reputation'].':</td><td class="userinfoentry" style="text-align: right">'.
			'<a href="'.BASEDIR.'karma.'.PHPEXT.'?user='.$user[0]['user_id'].'">'.$user[0]['user_karma'].'</a>';
		
		if ($session->logged && !$userinfo['user_disabled'])  {
			print '  [ <a href="'.BASEDIR.'karma.'.PHPEXT.'?user='.$user[0]['user_id'].'&vote=plus">+</a> | ';
			print '<a href="'.BASEDIR.'karma.'.PHPEXT.'?user='.$user[0]['user_id'].'&vote=minus">-</a> ]';
		}
		
		print "</td></tr>\n";

		if ($user[0]['user_website'])  {
			$website = sanitizeHTML($user[0]['user_website']);
			print '<tr><td class="userinfoentry">web:</td><td class="userinfoentry" style="text-align: right">'.$website."</td></tr>\n";
		}

		if ($user[0]['user_msn'])  {
			$msn = sanitizeHTML($user[0]['user_msn']);
			$msn = preg_replace ('/@/', '&lt;AT&gt;', $msn);
			$msn = preg_replace ('/\./', '&lt;DOT&gt;', $msn);
			print '<tr><td class="userinfoentry">msn:</td><td class="userinfoentry" style="text-align: right">'.$msn."</td></tr>\n";
		}

		$so = sanitizeHTML($row['poster_so']);
		$browser = sanitizeHTML($row['poster_browser']);

		if ($so && $browser)
			print '<tr><td class="userinfoentry">posted using:</td><td class="userinfoentry" style="text-align: right">'."$so<br>\n$browser</td></tr>\n";

		print '<tr><td class="userinfoentry">&gt;</td>'.
			'<td class="userinfoentry" style="text-align: right">'.
			'<a href="javascript:sendMessage('.
			getInt($user[0]['user_id']).",'".BASEDIR."'".
			')">'.$_LANG['send_pm'].'</a></td>';

		print '</table>';

		?></td>

		<td class="topiccontent">
	<a name="<?php print getInt($row['post_id']); ?>">
		<li class="posttime">

<?php
		print @date('d M Y, h:i:s a', $row['post_time']).'<br>';

		if ($session->logged)  {
			if ($row['poster_id'] == $userinfo['user_id'] || $userinfo['user_group'] <= USERLEV_MOD)  {
				print '<a href="javascript:editMessage('.
					"'".BASEDIR."',".$row['post_id'].')">Edit</a> ';
			}

			$author = $db->query('select username from '.$_CONF['dbprefix'].'users u join '.$_CONF['dbprefix'].'posts p '.
				'on u.user_id=p.poster_id where p.post_id='.getInt($row['post_id']));
			$db->freeResult();
			$author = sanitizeHTML($author[0]['username']);

			print '<a href="javascript:quotePost('."'".BASEDIR."', ".getInt($row['post_id']).", '".$author."'".')">Quote</a>';

			if ($row['poster_id'] == $userinfo['user_id'] || $userinfo['user_group'] <= USERLEV_MOD)  {
				print ' <a href="javascript:ipAddrPopup('."'".BASEDIR."', ".getInt($row['post_id']).')">IP</a> ';

				if ($userinfo['user_group'] > USERLEV_MOD)  {
					$cond = false;
					$replies = $db->query('select count(*) as replies from '.$_CONF['dbprefix'].'posts where '.
						'topic_id='.$id.' and post_time > '.getInt($row['post_time']));
					$db->freeResult();
					$replies = getInt($replies[0]['replies']);

					if ($replies == 0)
						$cond = true;
				}

				if ($userinfo['user_group'] <= USERLEV_MOD || $cond)
					print ' <a href="'.BASEDIR.'updatePost.'.PHPEXT.'?delete=1&post_id='.getInt($row['post_id']).'">x</a>';
			}
		}

?>

		</li><br>
		<?php print bb2html(($row['post_content'])); ?>

<?php

	if ($row['post_lastedit_user'] && $row['post_lastedit_date'])  {
		$username = $db->query('select username from '.$_CONF['dbprefix'].'users where user_id='.getInt($row['post_lastedit_user']));
		$db->freeResult();
		$username = sanitizeHTML($username[0]['username']);
		$lastdate = @date('d M Y, h:i:s a', getInt($row['post_lastedit_date']));

		print '<br><br><span style="font-size: 10px">'.$_LANG['last_modify_by'].
			' <a href="'.BASEDIR.'user/'.getInt($row['post_lastedit_user']).'">'.$username.'</a> '.
			'@ '.$lastdate.'</span>';
	}

	if ($user[0]['user_signature'])
		print '<br><br><div class="signatureHead"></div><div class="signature">'.
			bb2html(($user[0]['user_signature'])).'</div>';

?>

		</td>
	</tr>
<?php

}

?>
		</table><br><br>

<?php

if ($numPosts >= 10)  {
	print '<center>';

	for ($i=1; $i <= ((int) ($numPosts/10) + 1); $i++)  {
		if ($i == $page)
			print "$i ";
		else
			print "<a href=\"".BASEDIR."topic/$id/$i\">$i</a> ";
	}
	
	print '</center><br>';
}


if ($session->logged)  {
	$res = $db->query('select forum_postgroup from '.$_CONF['dbprefix'].'forums '.
		"where forum_id='".$forum_id."'");
	$db->freeResult();
	$forum_postgroup = getInt($res[0]['forum_postgroup']);

	$res = $db->query('select topic_disabled from '.$_CONF['dbprefix'].'topics '.
		"where topic_id='$id'");
	$db->freeResult();
	$topic_disabled = getInt($res[0]['topic_disabled']);
	$topic_sticked  = getInt($res[0]['topic_sticked']);
	unset($res);

	if ($userinfo['user_group'] <= USERLEV_MOD)  {

?>
		<center><select name="modActions" style="font-size: 10px" onChange='modActions ("<?php print BASEDIR; ?>",
				<?php print getInt($row['topic_id']); ?>, this.value)'>
			<option value="none">-- <?php print $_LANG['mod_actions']; ?></option>
			<option value="remove"><?php print $_LANG['remove']; ?></option>
			
			<?php if (!$topic_disabled) { ?> <option value="lock"><?php
				print $_LANG['lock_topic']; ?></option> <?php } ?>
			<?php if ( $topic_disabled) { ?> <option value="unlock"><?php
				print $_LANG['unlock_topic']; ?></option> <?php } ?>
			
			<?php if (!$topic_sticked) { ?> <option value="stick"><?php
				print $_LANG['stick_topic']; ?></option> <?php } ?>
			<?php if ( $topic_sticked) { ?> <option value="unstick"><?php
				print $_LANG['unstick_topic']; ?></option> <?php } ?>
		</select></center><br>

<?php

	}

	if ($userinfo['user_group'] <= $forum_postgroup)  {
		if (!$topic_disabled)  {

?>

		<table class="reply">
			<tr>
				<td width="50%" style="vertical-align: top; padding: 20px"><?php print $_LANG['reply_desc']; ?></td>
				<td width="50%" style="vertical-align: top">
					<form action="<?php print BASEDIR; ?>insertPost.<?php print PHPEXT; ?>" method="POST">
						<input type="hidden" name="topic_id" value="<?php print $id; ?>">
						<textarea id="postTextArea" class="newpost" name="content"></textarea><br>
						<input type="submit" value="<?php print $_LANG['reply']; ?>" class="doreply">
					</form>
				</td>
			</tr>
		</table>

<?php
		}
	}
}

$db->close();

?>

<?php require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT); ?>

