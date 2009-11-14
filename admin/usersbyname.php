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
require_once (ABSOLUTE_BASEPATH.'/db.'.PHPEXT);
require_once (ABSOLUTE_BASEPATH.'/utils.'.PHPEXT);

if (!$_GET['user'])
	die();

$db = new nullBB_Database ($_CONF, $_LANG);

$user = sanitizeQuery($_GET['user']);
$user = str_replace ('*', '%', $user);

$res = $db->query("select user_id, username from ".$_CONF['dbprefix'].'users '.
	"where username like '".$user."'");

if (empty($res))
	die();

foreach ($res as $row)  {
	print getInt($row['user_id']).' # '.sanitizeHTML($row['username'])."\n";
}

unset($res);
$db->freeResult();
$db->close();

?>
