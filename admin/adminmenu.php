<div class="adminMenu">

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

function writeLink ($url, $text)  {
	print '<a class="adminmenu" href="'.BASEDIR.$url.'">'.$text.'</a><br>'."\n";
}

writeLink ('', $_LANG['forum_index']);
writeLink ('admin/', $_LANG['admin_index']);
writeLink ('admin/_user', $_LANG['user_management']);
writeLink ('admin/_forum', $_LANG['forum_management']);
writeLink ('admin/_group', $_LANG['group_management']);
writeLink ('admin/_dump', $_LANG['db_backup']);

?>

</div>

