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
require_once ('admin_head.'.PHPEXT);

if (!$_REQUEST['uid'])
	die();

$uid = getInt($_REQUEST['uid']);
$db = new nullBB_Database ($_CONF, $_LANG);
$db->freeResult();

$results = $db->query('select * from '.$_CONF['dbprefix'].'users where user_id='.$uid);
$results = $results[0];

if (!$_POST['change'])  {

?>

<form enctype="multipart/form-data" action="<?php print $_SERVER['PHP_SELF'].'?uid='.$uid; ?>" method="POST">
<table style="padding: 10px">

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['new_password']; ?>*</td>
	<td class="registerinput"><input type="password" name="new_password"></td>
</tr>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['repeat_new_password']; ?>*</td>
	<td class="registerinput"><input type="password" name="repeat_password"></td>
</tr>

<tr>
	<td class="registerfield">&gt; website</td>
	<td class="registerinput"><input type="text" name="website" value="<?php print sanitizeHTML($results['user_website']); ?>"></td>
</tr>

<tr>
	<td class="registerfield">&gt; MSN address</td>
	<td class="registerinput"><input type="text" name="msn" value="<?php print sanitizeHTML($results['user_msn']); ?>"></td>
</tr>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['signature']; ?>
		<div id="numBytes"></div></td>
	<td class="registerinput"><textarea id="signatureTextArea" class="signature" name="signature"
		onKeyDown='refreshSignature(<?php print $_CONF['signature_max_len']; ?>)'
		onKeyUp='refreshSignature(<?php print $_CONF['signature_max_len']; ?>)'
		onLoad='refreshSignature(<?php print $_CONF['signature_max_len']; ?>)'
		><?php
		print sanitizeHTML($results['user_signature']); ?></textarea></td>
</tr>

<?php

if (($dir = @opendir('../themes')))  {

?>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['theme']; ?></td>
	<td class="registerinput">
		<select name="theme">
			<option name="<?php print sanitizeHTML($results['user_theme']); ?>"><?php
			print sanitizeHTML($results['user_theme']).' *'; ?></option>
		
		<?php
		
		while (($theme = readdir($dir)))  {
			$theme_ok = false;

			if (is_dir('../themes/'.$theme) && $theme != '.' && $theme != '..' && $theme != $results['user_theme'])  {
				if (($dir2 = @opendir('../themes/'.$theme)))  {
					while (($file = readdir($dir2)) && !$theme_ok)  {
						if ($file == 'style.css')
							$theme_ok = true;
					}

					closedir($dir2);
				}
			}

			if ($theme_ok)  {

		?>

			<option value="<?php print sanitizeHTML($theme); ?>"><?php
				print sanitizeHTML($theme);
			?></option>

		<?php
			}
		}

		?>
		
		</select>
	</td>
</tr>

<?php

	closedir($dir);
}

?>
<tr>
	<td class="registerfield">&gt; <?php print $_LANG['view_avatars']; ?></td>
	<td class="registerinput">
		<input type="radio" name="viewavatars" value="view" <?php if ($results['user_viewavatars'] != 0) print 'checked'; ?>> view<br>
		<input type="radio" name="viewavatars" value="hide" <?php if ($results['user_viewavatars'] == 0) print 'checked'; ?>> hide <br>
	</td>
</tr>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['avatar_upload']; ?></td>
	<td class="registerinput">
		<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
		<input type="file" name="avatarfile">
	</td>
</tr>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['remove_avatar']; ?></td>
	<td class="registerinput">
		<input type="checkbox" name="removeavatar">
	</td>
</tr>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['remove_user']; ?></td>
	<td class="registerfield" style="text-align: right"><input type="checkbox" name="removeuser"></td>
</tr>

<tr>
	<td class="registerfield">&gt; <?php print $_LANG['ban_status']; ?></td>
	<td class="registerfield" style="text-align: right">
		<select name="ban">
			<option value="">--</option>
		<?php
			if ($results['user_disabled'])
				print '<option value="unban">'.$_LANG['unban'].'</option>';
			else
				print '<option value="ban">'.$_LANG['ban'].'</option>';
		?>
		</select>
	</td>
</tr>

<tr>
	<td class="registerfield"><?php print $_LANG['edit_user_level']; ?></td>
	<td class="registerfield" style="text-align: right">
		<select name="userlev">

<?php

$levels = $db->query('select group_name from '.$_CONF['dbprefix'].'groups where group_id='.getInt($results['user_group']));
$db->freeResult();

print '<option value="'.$results['user_group'].'">'.$levels[0]['group_name'].'*</option>';

$levels = $db->query('select * from '.$_CONF['dbprefix'].'groups');
$db->freeResult();

foreach ($levels as $row)  {
	if ($row['group_id'] != $results['user_group'])  {
		print '<option value="'.$row['group_id'].'">'.$row['group_name'].'</option>';
	}
}

unset($levels);

?>

		</select>
	</td>
</tr>

<tr>
	<td class="registerfield">&gt;</td>
	<td class="registerfield">* <?php print $_LANG['fill_change_password']; ?></td>
</tr>

<tr>
	<td class="registerfield">&gt;</td>
	<td class="registerinput"><input type="submit" value="<?php print $_LANG['submit']; ?>" name="change"></td>
</tr>

</table>
</form>

<?php

} else {
	$edit_pass = false;

	if ($_POST['new_password'] && $_POST['repeat_password'])
		$edit_pass = true;

	if (!$edit_pass)  {
		if ($_POST['new_password'] || $_POST['repeat_password'])  {
			notification ($_LANG['cannot_modify_password'], $_SERVER['HTTP_REFERER'], 3);
			die();
		}
	} else {
		$pass = $results['user_password'];

		if (strcmp($_POST['new_password'], $_POST['repeat_password']))  {
			notification ($_LANG['not_matching_passwords'], $_SERVER['HTTP_REFERER'], 3);
			die();
		}
	}
	
	if ($edit_pass)
		$db->query('update '.$_CONF['dbprefix']."users set user_password='".sha1(md5($_POST['new_password']))."' where user_id=".$uid);

	if (strcmp($_POST['website'], $results['user_website']))
		$db->query('update '.$_CONF['dbprefix']."users set user_website='".sanitizeQuery($_POST['website'])."' where user_id=".$uid);

	if (strcmp($_POST['msn'], $results['user_msn']))
		$db->query('update '.$_CONF['dbprefix']."users set user_msn='".sanitizeQuery($_POST['msn'])."' where user_id=".$uid);
	
	if (strcmp($_POST['signature'], $results['user_signature']))  {
		if (strlen($_POST['signature']) > $_CONF['signature_max_len'])  {
			notification ($_LANG['signature_too_long'].'(max: '.$_CONF['signature_max_len'].' bytes)', $_SERVER['HTTP_REFERER'], 3);
			die();
		}

		$db->query('update '.$_CONF['dbprefix']."users set user_signature='".sanitizeQuery($_POST['signature'])."' where user_id=".$uid);
	}

	if (strcmp($_POST['theme'], $results['user_theme']))  {
		$theme = preg_replace ('/\s*\*\s*$/', '', $_POST['theme']);
		$theme = sanitizeQuery($theme);

		if (!file_exists('../themes/'.$theme.'/style.css'))  {
			notification ($_LANG['theme_not_found'], $_SERVER['HTTP_REFERER'], 3);
			die();
		}

		$db->query('update '.$_CONF['dbprefix']."users set user_theme='".$theme."' where user_id=".$uid);
	}

	if (isset($_POST['viewavatars']))  {
		if ($_POST['viewavatars'] == 'view')
			$db->query('update '.$_CONF['dbprefix']."users set user_viewavatars=1 where user_id=".getInt($uid));
		else if ($_POST['viewavatars'] == 'hide')
			$db->query('update '.$_CONF['dbprefix']."users set user_viewavatars=0 where user_id=".getInt($uid));
	}

	if ($_FILES['avatarfile']['name'])  {
		if ($_FILES['avatarfile']['error'] > 0)  {
			notification ('Upload error: '.$_FILES['avatarfile']['error'], $_SERVER['HTTP_REFERER'], 3);
			die();
		}

		if (preg_match('/\.([a-z0-9]{2,4})$/i', $_FILES['avatarfile']['name'], $match))  {
			$ext = $match[1];

			switch ($ext)  {
				case 'jpg':
				case 'jpeg':
				case 'gif':
				case 'png':
				case 'xpm':
				case 'xbm':
					break;

				default:
					notification ($_LANG['invalid_file_type'], $_SERVER['HTTP_REFERER'], 3);
					die();
					break;
			}
		} else {
			notification ($_LANG['invalid_file_type'], $_SERVER['HTTP_REFERER'], 3);
			die();
		}

		if (preg_match('/^image\/([0-9a-z]{2,4})$/i', $_FILES['avatarfile']['type'], $match))  {
			$ext = $match[1];

			switch ($ext)  {
				case 'jpg':
				case 'jpeg':
				case 'gif':
				case 'png':
				case 'xpm':
				case 'xbm':
					break;

				default:
					notification ($_LANG['invalid_file_type'], $_SERVER['HTTP_REFERER'], 3);
					die();
					break;
			}
		} else {
			notification ($_LANG['invalid_file_type'], $_SERVER['HTTP_REFERER'], 3);
			die();
		}

		if ($ext == 'jpg') $ext = 'jpeg';
		$ascii = img2ascii($_FILES['avatarfile']['tmp_name'], $ext);

		if (!empty($ascii))  {
			$db->query('update '.$_CONF['dbprefix']."users set user_avatar='".sanitizeQuery($ascii)."' ".
				"where user_id='".getInt($uid)."'");
		}
	}

	if (isset($_POST['removeavatar']))  {
		$db->query('update '.$_CONF['dbprefix']."users set user_avatar=null where user_id=".getInt($uid));
	}

	if (isset($_POST['removeuser']))  {
		$db->query('delete from '.$_CONF['dbprefix'].'users where user_id='.$uid);
	}

	if ($_POST['userlev'] != $results['user_group'])  {
		$db->query('update '.$_CONF['dbprefix']."users set user_group=".getInt($_POST['userlev']).
			' where user_id='.$uid);
	}

	if (isset($_POST['ban']))  {
		switch ($_POST['ban'])  {
			case 'ban':
				if (!$results['user_disabled'])
					$db->query('update '.$_CONF['dbprefix'].'users set user_disabled=1 where user_id='.$uid);
				break;

			case 'unban':
				if ($results['user_disabled'])
					$db->query('update '.$_CONF['dbprefix'].'users set user_disabled=0 where user_id='.$uid);
				break;
		}
	}

	$db->freeResult();
	$db->close();

	header ('Location: '.$_SERVER['HTTP_REFERER']);
	exit(0);
}
?>

