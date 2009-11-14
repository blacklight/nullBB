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

if (!$_POST['search'])  {

?>

<center>
&gt; <a class="topicHead" href="<?php print BASEDIR; ?>"><?php
	print $_CONF['title']; ?> home</a>
&gt; <?php print $_LANG['search']; ?><br><br></center>

<div class="main"><br><br>
<table class="register">

	<form action="search.<?php print PHPEXT; ?>" method="POST">

	<tr>
		<td class="searchfield">&gt; <?php print $_LANG['search_string']; ?></td>
		<td class="searchfield"><input type="text" name="search_string"></td>
	</tr>

	<tr>
		<td class="searchfield">&gt; <?php print $_LANG['search_by_author']; ?></td>
		<td class="searchfield">
			<input type="text" name="search_author" value="*"
				onFocus="this.value=''" autocomplete="off"
				onKeyUp="AutoComplete(this, 'compl', 'getusers.php', event)"
				style="width: 200px"><br>
	
			<div id="compl"></div>
		</td>
	</tr>
</table><br>

<center>
	<input type="submit" name="search" value="<?php print $_LANG['search']; ?>">
</center><br></div>

<?php

} else {
	if (!$_POST['search_string'] && !$_POST['search_author'])  {
		notification ($_LANG['insufficient_search_parameters'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$db = new nullBB_Database ($_CONF, $_LANG);

	if ($_POST['search_string'])
		$string = sanitizeQuery($_POST['search_string']);

	if ($_POST['search_author'])  {
		if ($_POST['search_author'] == '*')
			$_POST['search_author'] = null;
		else  {
			$author = sanitizeQuery($_POST['search_author']);
			$author = str_replace ('*', '%', $author);
		}
	}

	if ($string && $author)
		$res = $db->query('select f.forum_id, t.topic_id, p.post_id, u_topic.user_id as topic_user_id, u_post.user_id as post_user_id, p.post_time, forum_name, topic_title, u_topic.username as topic_author, u_post.username as post_author, topic_replies from '.
			$_CONF['dbprefix'].'users u_topic join '.$_CONF['dbprefix'].'forums f join '.$_CONF['dbprefix'].'posts p join '.
			$_CONF['dbprefix'].'topics t join '.$_CONF['dbprefix'].'users u_post on f.forum_id=t.forum_id and p.topic_id=t.topic_id and p.forum_id=f.forum_id '.
			"and p.poster_id=u_post.user_id and t.topic_poster=u_topic.user_id where post_content like '%".$string."%' ".
			"and u_post.username like '".$author."' order by p.post_time desc");
	else if (!$author)
		$res = $db->query('select f.forum_id, t.topic_id, p.post_id, u_topic.user_id as topic_user_id, u_post.user_id as post_user_id, p.post_time, forum_name, topic_title, u_topic.username as topic_author, u_post.username as post_author, topic_replies from '.
			$_CONF['dbprefix'].'users u_topic join '.$_CONF['dbprefix'].'forums f join '.$_CONF['dbprefix'].'posts p join '.
			$_CONF['dbprefix'].'topics t join '.$_CONF['dbprefix'].'users u_post on f.forum_id=t.forum_id and p.topic_id=t.topic_id and p.forum_id=f.forum_id '.
			"and p.poster_id=u_post.user_id and t.topic_poster=u_topic.user_id where post_content like '%".$string."%' ".
			"order by p.post_time desc");
	else if (!$string)
		$res = $db->query('select f.forum_id, t.topic_id, p.post_id, u_topic.user_id as topic_user_id, u_post.user_id as post_user_id, p.post_time, forum_name, topic_title, u_topic.username as topic_author, u_post.username as post_author, topic_replies from '.
			$_CONF['dbprefix'].'users u_topic join '.$_CONF['dbprefix'].'forums f join '.$_CONF['dbprefix'].'posts p join '.
			$_CONF['dbprefix'].'topics t join '.$_CONF['dbprefix'].'users u_post on f.forum_id=t.forum_id and p.topic_id=t.topic_id and p.forum_id=f.forum_id '.
			"and p.poster_id=u_post.user_id and t.topic_poster=u_topic.user_id ".
			"where u_post.username like '".$author."' order by p.post_time desc");

	$db->freeResult();

?>

<center>&gt; <a class="topicHead" href="<?php print BASEDIR; ?>"><?php print $_CONF['title']; ?> home</a>
&gt; <?php print $_LANG['search_results']; ?><br><br></center>

<div class="main"><br><br>

<?php

	if (empty($res))
		print '<center>'.$_LANG['no_search_results'].'</center>';
	else  {

?>

<table class="searchresults">

<tr>
	<th>Forum</th>
	<th>Topic</th>
	<th><?php print $_LANG['topic_author']; ?></th>
	<th><?php print $_LANG['post_author']; ?></th>
	<th><?php print $_LANG['replies']; ?></th>
</tr>

<?php

		foreach ($res as $row)  {
			$forum_name = sanitizeHTML($row['forum_name']);
			$topic_name = sanitizeHTML($row['topic_title']);
			$topic_author = sanitizeHTML($row['topic_author']);
			$post_author = sanitizeHTML($row['post_author']);
			$topic_replies = getInt($row['topic_replies']);
			$forum_id = getInt($row['forum_id']);
			$topic_id = getInt($row['topic_id']);
			$post_id = getInt($row['post_id']);
			$topic_user_id = getInt($row['topic_user_id']);
			$post_user_id = getInt($row['post_user_id']);

			$page = $db->query('select count(*) as numPosts from '.$_CONF['dbprefix'].'posts '.
					'where topic_id='.$topic_id.' and post_time < '.$row['post_time']);
			$page = (int) ( (getInt($page[0]['numPosts']) / 10) + 1 );
			$db->freeResult();

?>

<tr class="searchresults">
	<td class="searchresults"><a href="<?php print BASEDIR.$forum_id; ?>"><?php
		print $forum_name; ?></a></td>
	<td class="searchresults"><a href="<?php
		print BASEDIR."topic/$topic_id/$page#$post_id"; ?>"><?php
		print $topic_name; ?></a></td>
	<td class="searchresults"><a href="<?php
		print BASEDIR.'user/'.$topic_user_id; ?>"><?php
		print $topic_author; ?></a></td>
	<td class="searchresults"><a href="<?php
		print BASEDIR.'user/'.$post_user_id; ?>"><?php
		print $post_author; ?></a></td>
	<td class="searchresults"><?php print $topic_replies; ?></td>
</tr>

<?php

		}

		unset ($res);
		$db->close();

	}
}

?>

</table><br><br></div>
<?php require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT); ?>

