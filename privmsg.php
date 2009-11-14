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

if (!$session->logged)  {
	notification ($_LANG['not_logged_in'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

$db = new nullBB_Database($_CONF, $_LANG);

if ($_POST['postMsg'])  {
	if (!$_POST['subject'] || !$_POST['content'] || !$_POST['recv_id'])  {
		notification ($_LANG['no_info_specified'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if (preg_match('/^\s+$/', $_POST['subject']) ||
			preg_match('/^\s+/', $_POST['content']))  {
		notification ($_LANG['no_info_specified'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$subject = sanitizeQuery($_POST['subject']);
	$content = sanitizeQuery($_POST['content']);
	$recv_id = getInt($_POST['recv_id']);
	$send_id = getInt($userinfo['user_id']);

	$db->query('insert into '.$_CONF['dbprefix'].'privmsgs(privmsg_subject, '.
		'privmsg_from, privmsg_to, privmsg_date, privmsg_ip, privmsg_seen, privmsg_content) values('.
		"'".$subject."', ".$send_id.", ".$recv_id.", ".time().", '".
		$_SERVER['REMOTE_ADDR']."', 0, '".$content."')");
	$db->freeResult();
	$db->close();

	notification ($_LANG['message_ok'], $_SERVER['HTTP_REFERER'], 3);
	die();
} else {
	$user_id = getInt($userinfo['user_id']);

?>

<center>
&gt; <a class="topicHead" href="<?php print BASEDIR; ?>"><?php
	print $_CONF['title']; ?> home</a>
&gt; <?php print $_LANG['privmsg']; ?><br><br></center>

<div class="main"><br><br>
<table class="searchresults">

<tr>
	<th><?php print $_LANG['from']; ?></th>
	<th><?php print $_LANG['subject']; ?></th>
	<th><?php print $_LANG['date']; ?></th>
	<th><?php print $_LANG['delete']; ?></th>
</tr>

<?php

	$res = $db->query('select * from '.$_CONF['dbprefix'].'privmsgs where '.
		'privmsg_to='.$user_id.' order by privmsg_date desc');
	$db->freeResult();

	foreach ($res as $row)  {
		$msg_id  = getInt($row['privmsg_id']);
		$from_id = getInt($row['privmsg_from']);
		$from = $db->query('select username from '.$_CONF['dbprefix'].'users '.
			'where user_id='.$from_id);
		$db->freeResult();
		$from_username = sanitizeHTML($from[0]['username']);
		unset ($from);

		$subject = sanitizeHTML($row['privmsg_subject']);

?>

<tr class="searchresults">
	<td class="searchresults"><a href="<?php print BASEDIR.'user/'.$from_id; ?>"><?php
	print $from_username; ?></a></td>

	<td class="searchresults" style="width: 55%"><a href="javascript:popupPrivmsg(<?php
	print $msg_id.",'".BASEDIR."', ".$from_id; ?>)"><?php
	if (!$row['privmsg_seen']) print '<b>';
	print $subject;
	if (!$row['privmsg_seen']) print '</b>';
	?></a></td>

	<td class="searchresults" style="width: 25%"><?php print @date('d M Y, h:i:s a', getInt($row['privmsg_date'])); ?>

	<td class="searchresults"><a href="<?php print BASEDIR.
	'privmsgbyid.'.PHPEXT.'?del=1&msg_id='.$msg_id; ?>">x</a></td>
</tr>

<?php
	}
?>

</table><br><br></div>

<?php

	unset($res);
	$db->close();
}

?>

