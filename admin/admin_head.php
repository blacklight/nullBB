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

require_once ('../config.ini');
require_once (ABSOLUTE_BASEPATH.'/utils.'.PHPEXT);
require_once (ABSOLUTE_BASEPATH.'/session.'.PHPEXT);

if ($_POST['username'] && $_POST['password'])  {
	$db = new nullBB_Database($_CONF, $_LANG);
	$user = sanitizeQuery($_POST['username']);
	$res = $db->query('select * from '.$_CONF['dbprefix']."users where username='".$user."' ".
		"and user_password='".sha1(md5($_POST['password']))."'");

	if (empty($res))  {
		require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		notification ($_LANG['wrong_user_pass'], $_SERVER['HTTP_REFERER'], 3);
		die();
	} else {
		if ($res[0]['user_group'] > USERLEV_ADMIN)  {
			require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
			notification ($_LANG['insufficient_privileges'].' -> '.$res[0]['user_group'], $_SERVER['HTTP_REFERER'], 3);
			die();
		}

		setcookie ( 'admin_sid', sha1(md5($res[0]['username'].$res[0]['user_password'])) );
		$user = sanitizeHTML($user);
		notification ($_LANG['login_ok'].' '.$user, $_SERVER['HTTP_REFERER'], 3);
		exit(0);
	}
}

if ( ! $session->logged )  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['insufficient_privileges'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

if ($userinfo['user_group'] > USERLEV_ADMIN || $userinfo['user_disabled'])  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	notification ($_LANG['insufficient_privileges'], $_SERVER['HTTP_REFERER'], 3);
	die();
}

if (!$_COOKIE['admin_sid'] || $_COOKIE['admin_sid'] !=
		sha1(md5($userinfo['username'].$userinfo['user_password'])))  {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
?>

<div class="loginPanel">
<?php print $_LANG['admin_login_again']; ?><br><br>

<form action="#" method="POST">
<table border="0" width="90%" align="center">
	<tr>
		<td>&gt; username</td>
		<td><input class="login" type="text" name="username"></td>
	</tr>

	<tr>
		<td>&gt; password</td>
		<td><input class="login" type="password" name="password"></td>
	</tr>
</table>

<center><input type="submit" value="Login" class="login"></center>
</form>

</div>

<?php

require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT);
exit(0);

}

?>
