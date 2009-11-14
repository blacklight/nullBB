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

<?php

if (!isset($_GET['user']))  {
	notification ($_LANG['no_user'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$user = getInt($_GET['user']);
$db = new nullBB_Database ($_CONF, $_LANG);

if (isset($_GET['vote']))  {
	if ($_GET['vote'] == 'plus')
		$vote = 1;
	else if ($_GET['vote'] == 'minus')
		$vote = -1;
	else  {
		notification ($_LANG['invalid_vote'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if ( ! $session->logged )  {
		notification ($_LANG['not_logged_in'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if ($session->user_id == $user)  {
		notification ($_LANG['self_vote'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$res = $db->query ('select * from '.$_CONF['dbprefix'].'karma where user_id='.$user.' and voter='.$session->user_id);
	$db->freeResult();

	if (!empty($res))  {
		notification ($_LANG['already_voted'], $_SERVER['HTTP_REFERER'], 3);
		unset($res);
		die();
	}

	$db->query('insert into '.$_CONF['dbprefix'].'karma(user_id, voter, vote) '.
		"values('$user', '".$session->user_id."', '$vote')");

	$db->query('update '.$_CONF['dbprefix'].'users set user_karma = user_karma + '.$vote.' where user_id = '.$user);
	notification ($_LANG['karma_ok'], $_SERVER['HTTP_REFERER'], 3);

} else {

	$username = $db->query('select username from '.$_CONF['dbprefix'].'users where user_id='.$user);
	$db->freeResult();
	$username = sanitizeHTML($username[0]['username']);

	$res = $db->query('select u1.user_id as voted_id, u1.username as username_voted, u2.user_id as voter_id, u2.username as username_voter, k.vote '.
		'from '.$_CONF['dbprefix'].'karma k, '.$_CONF['dbprefix'].'users u1, '.$_CONF['dbprefix'].'users u2 '.
		'where k.user_id=u1.user_id and k.voter=u2.user_id and u1.user_id='.$user);
	$db->freeResult();

?>

<center>
&gt; <a class="topicHead" href="<?php print BASEDIR; ?>"><?php print $_CONF['title']; ?> home</a>
&gt; <?php print $_LANG['karma_info'].' '.$username; ?><br><br>

<table class="reputations">

<tr class="reputations">
	<th>Voter</th>
	<th>Vote</th>
</tr>

<?php

	foreach ($res as $row)  {

?>

<tr class="reputations">
	<td class="reputations"><a href="<?php print BASEDIR.'user/'.$row['voter_id'] ?>"><?php
		print sanitizeHTML($row['username_voter']); ?></a></td>
	<td class="reputations" style="text-align: right; width: 10%"><?php if (getInt($row['vote']) > 0) print '+1'; else print '-1'; ?></td>
</tr>

<?php
	}

	unset($res);
}

?>

</table></center>

<?php

$db->close();

?>

<?php require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT); ?>

