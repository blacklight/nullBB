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

$page  = ($_GET['page']) ? getInt($_GET['page']) : 1;
$start = ($page-1)*10;

$db  = new nullBB_Database ($_CONF, $_LANG);
$res = $db->query('select * from '.$_CONF['dbprefix']."users order by user_regtime limit $start,10");
$db->freeResult();

$num = $db->query('select count(*) as num from '.$_CONF['dbprefix'].'users');
$db->freeResult();
$num = getInt($num[0]['num']);

?>

<center>
&gt; <a class="topicHead" href="<?php print BASEDIR; ?>"><?php print $_CONF['title']; ?> home</a>
&gt; <?php print $_LANG['user_list']; ?><br><br>
</center>

<?php

if ($num >= 10)  {
	print '<center>';

	for ($i=1; $i <= ((int) ($num/10) +1); $i++)  {
		if ($i == $page)
			print "$i ";
		else
			print "<a href=\"".BASEDIR."users.".PHPEXT."?page=$i\">$i</a> ";
	}

	print '</center><br>';
}

?>

<table class="users">

<tr class="users">
	<th>username</th>
	<th><?php print $_LANG['registered_since']; ?></th>
	<th><?php print $_LANG['posts']; ?></th>
	<th><?php print $_LANG['reputation']; ?></th>
</tr>

<?php

foreach ($res as $row)  {
	print
		'<tr class="users">'.
		'<td class="users"><a href="'.BASEDIR.'user/'.$row['user_id'].'">'.sanitizeHTML($row['username']).'</a></td>'.
		'<td class="usersCenter">'.@date('d M Y, h:i:s a', $row['user_regtime']).'</td>'.
		'<td class="usersRight">'.getInt($row['user_posts']).'</td>'.
		'<td class="usersRight">'.getInt($row['user_karma']).'</td></tr>';
}

?>

</table>

<?php

unset($res);
$db->close();

?>

<?php require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT); ?>

