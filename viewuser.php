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

<table class="userinfo">

<?php

if (!isset($_GET['id']))
	die ($_LANG['invalid_user']);

$id   = getInt($_GET['id']);
$db   = new nullBB_Database($_CONF, $_LANG);
$user = $db->query('select u.*, g.group_name from '.$_CONF['dbprefix'].'users u join '.$_CONF['dbprefix'].'groups g on u.user_group=g.group_id '.
		'where user_id='.$id);
$db->freeResult();

if (empty($user))
	die ($_LANG['user_not_found']);

?>

<center>
&gt; <a class="topicHead" href="<?php print BASEDIR; ?>"><?php print $_CONF['title']; ?> home</a>
&gt; <?php print sanitizeHTML($user[0]['username']); ?><br><br>

<tr>
	<td>
		<span class="username"><b><?php print sanitizeHTML($user[0]['username']); ?></b></span><br>
		[<?php print sanitizeHTML($user[0]['group_name']); ?>]<br><br>
	</td>

	<td style="text-align: right">
		<span class="avatar"><?php
		if ($userinfo['user_viewavatars'])
			print sanitizeHTML($user[0]['user_avatar']); ?>
		</span>
	</td>
</tr>

<tr>
	<td class="userinfoentry"><?php print $_LANG['posts']; ?></td>
	<td class="userinfoentry" style="text-align: right"><?php
		print $user[0]['user_posts']; ?></td>
</tr>

<tr>
	<td class="userinfoentry"><?php print $_LANG['registered']; ?></td>
	<td class="userinfoentry" style="text-align: right"><?php
		print @date('d M Y, h:i:s a', $user[0]['user_regtime']); ?>
	</td>
</tr>

<tr>
	<td class="userinfoentry"><?php print $_LANG['reputation']; ?></td>
	<td class="userinfoentry" style="text-align: right">
		<a href="<?php print BASEDIR; ?>karma.<?php print PHPEXT; ?>?user=<?php
		print $user[0]['user_id']; ?>"><?php print $user[0]['user_karma']; ?></a>

<?php

if ($session->logged && !$userinfo['user_disabled'])  {
	print '  [ <a href="'.BASEDIR.'karma.'.PHPEXT.'?user='.
		$user[0]['user_id'].'&vote=plus">+</a> | ';

	print '<a href="'.BASEDIR.'karma.'.PHPEXT.'?user='.
		$user[0]['user_id'].'&vote=minus">-</a> ]';
}

?>

</td></tr>

<?php

if ($user[0]['user_website'])  {
	$website = sanitizeHTML($user[0]['user_website']);
	
	print '<tr><td class="userinfoentry">web:</td>'.
		'<td class="userinfoentry" style="text-align: right">'.
		$website."</td></tr>\n";
}

if ($user[0]['user_msn'])  {
	$msn = sanitizeHTML($user[0]['user_msn']);
	$msn = preg_replace ('/@/', '&lt;AT&gt;', $msn);
	$msn = preg_replace ('/\./', '&lt;DOT&gt;', $msn);
	
	print '<tr><td class="userinfoentry">msn:</td>'.
		'<td class="userinfoentry" style="text-align: right">'.
		$msn."</td></tr>\n";
}

?>

<tr>
	<td class="userinfoentry">.</td>
	<td class="userinfoentry" style="text-align: right"><a href="javascript:sendMessage(<?php
	print getInt($user[0]['user_id']).",'".BASEDIR."'"; ?>)"><?php print $_LANG['send_pm']; ?></a></td>
</tr>

<tr>
	<td class="userinfoentry">.</td>
	<td class="userinfoentry" style="text-align: right">
		<a href="<?php print BASEDIR; ?>messagesByUser.<?php
		print PHPEXT; ?>?user_id=<?php print $id; ?>"><?php
		print $_LANG['messages_by_user'].sanitizeHTML($user[0]['username']); ?></a></td>
</tr>

</table>

<?php require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT); ?>

