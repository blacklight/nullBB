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
require_once(ABSOLUTE_BASEPATH.'/header.'.PHPEXT);

?>

<table class="main">

<?php

$db = new nullBB_Database($_CONF, $_LANG);
$res = $db->query('select * from '.$_CONF['dbprefix'].'forums order by forum_vieworder');
$db->freeResult();

if (empty($res))
	print '<tr class="forums"><td class="forums">'.$_LANG['no_forums'].'</td></tr>';

foreach ($res as $row)  {
	$forum_viewgroup = getInt($row['forum_viewgroup']);
	
	if ($forum_viewgroup < USERLEV_ANY)  {
		if ( ! $session->logged )
			continue;

		if ( $userinfo['user_group'] > $forum_viewgroup)
			continue;
	}

	$lasttime = ($row['forum_lasttime'] == '0') ? $_LANG['no_posts'] : $row['forum_lasttime'];

	print '<tr class="forums">'."\n";
	print "<td class=\"forums\"><b><a href=\"".BASEDIR.$row['forum_id']."\">".sanitizeHTML($row['forum_name'])."</a></b>";

	if ($session->logged)  {
		$new = $db->query(
			'select t.topic_id '.
			'from '.$_CONF['dbprefix'].'viewtopics v join '.$_CONF['dbprefix'].'topics t join '.$_CONF['dbprefix'].'posts p '.
			'on t.topic_id = v.topic_id and t.topic_id = p.topic_id '.
			'where v.user_id = '.getInt($userinfo['user_id']).' '.
			'and t.forum_id = '.getInt($row['forum_id']).' '.
			'and p.post_id = t.topic_lastreply '.
			'and p.post_time > v.viewtime '.
			'union select t.topic_id '.
			'from nullbb_topics t '.
			'where t.forum_id = '.getInt($row['forum_id']).' '.
			'and t.topic_id not in( select topic_id from nullbb_viewtopics )');
		$db->freeResult();

		if (!empty($new) && $row['forum_lasttime'] > $session->session_lasttime)
			print ' <i>[NEW]</i>';
	}
	
	print "<br>".sanitizeHTML($row['forum_desc'])."</td>\n";
	print '<td class="forums">';

	$topics = $db->query('select count(*) as num from '.$_CONF['dbprefix'].'topics '.
		'where forum_id='.getInt($row['forum_id']));
	$db->freeResult();
	$topics = getInt($topics[0]['num']);
	
	if ($topics == 0)
		print $_LANG['no_posts'];
	else  {
		$lastuser = $db->query('select user_id, username from '.$_CONF['dbprefix'].'users u join '.$_CONF['dbprefix'].'posts p '.
			'on u.user_id=p.poster_id where p.post_id='.getInt($row['forum_lastpost']));
		$db->freeResult();

		if (!empty($lastuser))  {
			$user_id = getInt($lastuser[0]['user_id']);
			$username = sanitizeHTML($lastuser[0]['username']);
			unset($lastuser);

			print $_LANG['latest_post'].': <a href="'.BASEDIR.'user/'.$user_id.'">'.$username.'</a>';
			print '<br>@ '.@date('d M Y, h:i:s a', $lasttime).'</td>'."\n";
		} else {
			print $_LANG['latest_post'].': <span style="color: #a00">[anonymous]</span>';
			print '<br>@ '.@date('d M Y, h:i:s a', $lasttime).'</td>'."\n";
		}
	}

	print "<td class=\"forums\">".$row['forum_topics']."</td><td class=\"forumsnoright\">".$row['forum_posts']."</td></tr>";
}

$db->close();

?>
		</table>

<?php require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT); ?>

