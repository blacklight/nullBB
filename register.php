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
require_once (ABSOLUTE_BASEPATH.'/utils.'.PHPEXT);
require_once (ABSOLUTE_BASEPATH.'/session.'.PHPEXT);

if ($_POST['username'])  {
	if (!($_POST['password'] && $_POST['repeat_password'] && $_POST['email'] && $_POST['input_captcha'] && $_POST['captcha']))  {
		notification ($_LANG['no_mandatory_fields'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$db = new nullBB_Database($_CONF, $_LANG);
	$user = sanitizeQuery(strtolower($_POST['username']));
	$pass = $_POST['password'];
	$repeat_pass = $_POST['repeat_password'];

	if (strlen($user) > 25)  {
		notification ($_LANG['username_too_long'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if (strstr($user, "'"))  {
		notification ($_LANG['username_invalid_character'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if (strcmp($pass, $repeat_pass))  {
		notification ($_LANG['not_matching_passwords'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	if (!preg_match('/^[a-zA-Z0-9_\.-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-\.]+$/', $_POST['email']))  {
		notification ($_LANG['invalid_email_address'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$res = $db->query('select user_email from '.$_CONF['dbprefix'].
			"users where user_email='".$_POST['email']."'");
	$db->freeResult();

	if (!empty($res))  {
		notification ($_LANG['email_already_assigned'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$pass = sha1(md5($pass));
	$email = $_POST['email'];
	$captcha = $_POST['captcha'];
	$input_captcha = md5(md5($_POST['input_captcha']));

	if ($captcha != $input_captcha)  {
		notification ($_LANG['wrong_captcha'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$res = $db->query('select user_id from '.$_CONF['dbprefix']."users where username='".$user."'");

	if (!empty($res))  {
		notification ($_LANG['taken_username'], $_SERVER['HTTP_REFERER'], 3);
		die();
	}

	$web = (isset($_POST['website'])) ? sanitizeQuery($_POST['website']) : '';
	$msn = (isset($_POST['msn'])) ? sanitizeQuery($_POST['msn']) : '';

	$db->query('insert into '.$_CONF['dbprefix'].'users(username,user_password,user_email,user_website,user_msn,user_regtime) values('.
		"'$user', '$pass', '$email', '$web', '$msn', '".time()."')");

	$session = new nullBB_Session(array( 'username' => $user ), $_CONF, $_LANG);
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
		
	notification ($_LANG['login_ok'].' '.sanitizeHTML($user), BASEDIR, 3);
	die();
} else {
	require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
	require_once (ABSOLUTE_BASEPATH.'/gen_captcha.'.PHPEXT);
?>

<center>
&gt; <a class="topicHead" href="<?php print BASEDIR; ?>"><?php print $_CONF['title']; ?> home</a>
&gt; <?php print $_LANG['register']; ?></center><br><br>

<div class="main"><br><br>
<table class="register">

<form action="register.<?php print PHPEXT; ?>" method="POST">

<input type="hidden" name="captcha" value="<?php print md5(md5($captcha)); ?>">

<tr>
	<td class="registerfield">&gt; username*</td>
	<td class="registerinput"><input type="text" name="username"></td>
</tr>

<tr>
	<td class="registerfield">&gt; password*</td>
	<td class="registerinput"><input type="password" name="password"></td>
</tr>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['repeat_password']; ?>*</td>
	<td class="registerinput"><input type="password" name="repeat_password"></td>
</tr>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['email_address']; ?>*</td>
	<td class="registerinput"><input type="text" name="email"></td>
</tr>

<tr>
	<td class="registerfield">&gt; website</td>
	<td class="registerinput"><input type="text" name="website"></td>
</tr>

<tr>
	<td class="registerfield">&gt; MSN address</td>
	<td class="registerinput"><input type="text" name="msn"></td>
</tr>

<tr>
	<td class="registerfield">&gt;</td>
	<td class="registerinput"><img src="captcha.<?php print PHPEXT; ?>?str=<?php print urlencode(base64_encode($cyph)); ?>"></td>
</tr>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['insert_captcha']; ?>*</td>
	<td class="registerinput"><input type="text" name="input_captcha"></td>
</tr>

<tr>
	<td class="registerfield">&gt;</td>
	<td class="registerinput"><input type="submit" value="<?php print $_LANG['register']; ?>"></td>
</tr>

<tr>
	<td class="registerfield"><br><br>&gt;</td>
	<td class="registerinput"><br><br>&lt;</td>
</tr>

<tr>
	<td class="registerfield">&gt;</td>
	<td class="registerinput" style="font-size: 10px"><?php print $_LANG['mandatory_fields']; ?></td>
</tr>

</form>

</table><br><br>
</div>

<script language="javascript" type="text/javascript">
	document.getElementsByName('username')[0].focus();
</script>

<?php
}
?>

<?php require_once (ABSOLUTE_BASEPATH.'/footer.'.PHPEXT); ?>

