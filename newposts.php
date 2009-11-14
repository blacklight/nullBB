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
require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);

?>

<center>&gt; <a class="topicHead" href="<?php print BASEDIR; ?>"><?php print $_CONF['title']; ?> home</a>
&gt; <?php print $_LANG['new_messages']; ?><br><br></center>
<table class="main">

<?php

if ( ! $session->logged )  {
	notification ($_LANG['not_logged_in'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$db = new nullBB_Database($_CONF, $_LANG);
$res = $db->query('select * from '.$_CONF['dbprefix']."newtopics where post_time > ".getInt($session->session_lasttime)." order by post_time desc");

$res = $db->query(
	'select f.forum_id, f.forum_name, t.topic_id, t.topic_title, t.topic_lastreply, t.topic_disabled, p.poster_id, u.username as last_poster, p.post_time '.
	'from '.$_CONF['dbprefix'].'viewtopics v join '.$_CONF['dbprefix'].'topics t join '.$_CONF['dbprefix'].'posts p join '.$_CONF['dbprefix'].
	'forums f join '.$_CONF['dbprefix'].'users u '.
	'on v.topic_id=t.topic_id and t.forum_id=f.forum_id '.
	'and p.forum_id=f.forum_id '.
	'and p.topic_id=t.topic_id '.
	'and p.poster_id=u.user_id '.
	'where p.post_time > '.getInt($session->session_lasttime).' '.
	'and p.post_id=t.topic_lastreply '.
	'and ( (v.user_id='.getInt($userinfo['user_id']).' '.
	'and p.post_time > v.viewtime) '.
	'or (t.topic_id not in '.
	'(select topic_id from '.$_CONF['dbprefix'].'viewtopics where user_id='.getInt($userinfo['user_id']).')) ) '.
	'group by t.topic_id '.
	'order by post_time desc');

$db->freeResult();

if (empty($res))  {
	notification ($_LANG['no_new_posts'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

?>

<table class="newposts">

<tr>
	<th>Forum</th>
	<th>Topic</th>
	<th><?php print $_LANG['latest_post']; ?></th>
	<th><?php print $_LANG['replies']; ?></th>
</tr>

<?php

foreach ($res as $row)  {

?>

<tr class="newposts">
	<td class="newposts"><a href="<?php print BASEDIR.getInt($row['forum_id']); ?>"><?php
		print sanitizeHTML($row['forum_name']); ?></a></td>
	<td class="newposts"><a href="<?php print BASEDIR; ?>topic/<?php print getInt($row['topic_id']); ?>"><?php
		print sanitizeHTML($row['topic_title']);

		if ($row['topic_disabled'])
			print ' <i>'.$_LANG['closed'].'</i>';

		?></a>
	<?php

	$replies = $db->query('select topic_replies from '.$_CONF['dbprefix'].'topics where '.
		"topic_id = '".getInt($row['topic_id'])."'");
	$db->freeResult();
	$replies = getInt($replies[0]['topic_replies']) - 1;

	if ($replies >= 10)  {
		print ' [ ';
		$pages = (int) ($replies/10) + 1;

		for ($i=1; $i <= $pages; $i++)
			print '<a href="'.BASEDIR.'topic/'.getInt($row['topic_id']).'/'.$i.'">'.$i.'</a> ';
		print ']';
	}

	$author = $db->query('select user_id, username from '.$_CONF['dbprefix'].'users u join '.
		$_CONF['dbprefix'].'topics t on u.user_id=t.topic_poster '.
		"where t.topic_id='".getInt($row['topic_id'])."'");
	$db->freeResult();

	$user_id = getInt($author[0]['user_id']);
	$user_author = sanitizeHTML($author[0]['username']);

	print "<br>\n -> ".$_LANG['posted_by'].' <a href="'.BASEDIR.'user/'.
		$user_id.'">'.$user_author.'</a>';

	?>
		
	</td>
	<td class="newposts"><?php print $_LANG['latest_post'].': '; ?><a href="<?php print BASEDIR; ?>user/<?php print intval($row['poster_id']); ?>"><?php
		print sanitizeHTML($row['last_poster'], ENT_NOQUOTES); ?></a><br>
		@ <?php print @date('d M Y, h:i:s a', intval($row['post_time'])); ?>
	</td>

	<td class="newposts"><?php print $replies; ?>
	</td>
</tr>

<?php

}

print '</table></table>';
$db->close();

?>

<?php require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT); ?>

