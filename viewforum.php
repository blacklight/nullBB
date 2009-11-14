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

if (!isset($_GET['id']))  {
	print '<tr class="forums"><td class="forums">'.$_LANG['invalid_forum'].'</td></tr>';
	print '</table></body></html>';
	exit(0);
}

$id = getInt($_GET['id']);

$db = new nullBB_Database($_CONF, $_LANG);
$res = $db->query('select forum_viewgroup from '.$_CONF['dbprefix'].'forums where '.
	"forum_id='".$id."'");
$db->freeResult();
$forum_viewgroup = getInt($res[0]['forum_viewgroup']);
unset($res);

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

$res = $db->query("select forum_name from ".$_CONF['dbprefix']."forums where forum_id='$id'");
$db->freeResult();

if (empty($res))  {
	print '<tr class="forums"><td class="forums">'.$_LANG['invalid_forum'].'</td></tr>';
	print '</table></body></html>';
	die();
}

$forum_name = sanitizeHTML($res[0]['forum_name']);
unset($res);

print '<center>';
print '&gt; <a class="topicHead" href="'.BASEDIR.'">'.$_CONF['title'].' home</a> ';
print '&gt; <a class="topicHead" href="'.BASEDIR.$id.'">'.$forum_name.'</a><br><br></center>';

$res = $db->query("select * from ".$_CONF['dbprefix']."topics where forum_id='$id' order by topic_sticked desc, topic_lastreply desc");
$db->freeResult();

if (empty($res))  {
	print '<tr class="forums"><td class="forums">'.$_LANG['no_topics'].'</td></tr>';
	print '</table></body></html>';
	exit(0);
}

foreach ($res as $row)  {

?>

	<tr class="forums">
		<td class="topictitle"><?php if ($row['topic_sticked']) print '['.$_LANG['topic_sticked'].'] ';
			?><b><a href="<?php print BASEDIR; ?>topic/<?php print getInt($row['topic_id']); ?>"><?php
		print sanitizeHTML($row['topic_title']); ?></a></b>

<?php

	$numPosts = getInt($row['topic_replies']);

	if ($numPosts > 10)  {
		print '  [ ';

		for ($i=1; $i <= ((int) ($numPosts / 10) + 1); $i++)  {
			print '<a href="'.BASEDIR.'topic/'.getInt($row['topic_id']).'/'.$i.'">'.$i.'</a> ';
		}

		print ']  ';
	}

	if ($session->logged)  {
		$reply = $db->query('select post_time from '.$_CONF['dbprefix']."posts where post_id='".getInt($row['topic_lastreply'])."'");
		$db->freeResult();

		$new = $db->query(
			'select t.topic_id '.
			'from nullbb_viewtopics v join nullbb_topics t join nullbb_posts p '.
			'on t.topic_id=v.topic_id '.
			'and t.topic_id=v.topic_id '.
			'and t.topic_id='.getInt($row['topic_id']).' '.
			'where v.user_id='.getInt($userinfo['user_id']).' '.
			'and p.post_id=t.topic_lastreply '.
			'and p.post_time > v.viewtime '.
			'union select t.topic_id '.
			'from nullbb_topics t '.
			'where t.topic_id='.getInt($row['topic_id']).' '.
			'and t.topic_id not in( select topic_id from nullbb_viewtopics )');
		$db->freeResult();

		if (!empty($new) && $reply[0]['post_time'] > $session->session_lasttime)
			print ' <i>[NEW]</i>';

		if ($row['topic_disabled'])
			print ' <i>['.$_LANG['closed'].']</i>';
	}
	
?>
		
		<br>

<?php

$user = $db->query('select * from '.$_CONF['dbprefix']."users where ".
	"user_id='".getInt($row['topic_poster'])."'");
$db->freeResult();

if (empty($user))
	$username = null;
else
	$username = sanitizeHTML($user[0]['username']);

$db->freeResult();

?>

		<?php
			print ' -> '.$_LANG['posted_by'].' ';
			print (empty($user)) ? '' : '<a href="'.BASEDIR.'user/'.
				getInt($user[0]['user_id']).'">';

			print (empty($user)) ? '<span style="color: #a00">[anonymous]</span>' : $username;
			print (empty($user)) ? '' : '</a>';
		?>
		</td>
		
		<td class="topicauthor">

		<?php
			$userpost = $db->query('select user_id, username from '.$_CONF['dbprefix'].'users u join '.$_CONF['dbprefix'].'posts p on p.poster_id=u.user_id '.
				'where p.post_id='.getInt($row['topic_lastreply']));
			$db->freeResult();

			print $_LANG['latest_post'].': ';

			print ($userpost[0]['username'])
				? '<a href="'.BASEDIR.'user/'.$userpost[0]['user_id'].'">'.
					sanitizeHTML($userpost[0]['username']).'</a>'
				: '<span style="color: #a00">[anonymous]</span>';

			$lasttime = $db->query('select post_time from '.$_CONF['dbprefix'].'posts where post_id='.getInt($row['topic_lastreply']));
			$db->freeResult();

			print "<br>\n @ ".@date('d M Y, h:i:s a', getInt($lasttime[0]['post_time']));
		?>

		</td>

		<td class="topicreplies" style="text-align: center"><?php
			print $_LANG['replies'].": <b>".getInt($row['topic_replies']-1).'</b>';
		?>
		</td>

		<?php
			if ($session->logged && $userinfo['user_group'] <= USERLEV_MOD)  {
		?>

		<td class="topicreplies" style="text-align: right">
			<select name="modActions" style="font-size: 10px" onChange='modActions ("<?php print BASEDIR; ?>",
					<?php print getInt($row['topic_id']); ?>, this.value)'>
				<option value="none">-- <?php print $_LANG['mod_actions']; ?></option>
				<option value="remove"><?php print $_LANG['remove']; ?></option>
				
				<?php if (!$row['topic_disabled']) { ?> <option value="lock"><?php
					print $_LANG['lock_topic']; ?></option> <?php } ?>
				<?php if ( $row['topic_disabled']) { ?> <option value="unlock"><?php
					print $_LANG['unlock_topic']; ?></option> <?php } ?>
				
				<?php if (!$row['topic_sticked']) { ?> <option value="stick"><?php
					print $_LANG['stick_topic']; ?></option> <?php } ?>
				<?php if ( $row['topic_sticked']) { ?> <option value="unstick"><?php
					print $_LANG['unstick_topic']; ?></option> <?php } ?>
			</select>
		</td>

		<?php
			}
		?>
	</tr>

<?php
}
?>

		</table>

<?php require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT); ?>

