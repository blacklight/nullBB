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

$alphabeth = array();

for ($i=ord('a'); $i<=ord('z'); $i++)
	array_push($alphabeth,chr($i));

for ($i=0; $i<=9; $i++)
	array_push($alphabeth,$i);

$key = 'lolasd';
$randstr = '';
$cyph = urldecode(base64_decode($_GET['str']));

for ($i=0; $i<6; $i++)
	$randstr .= ($cyph[$i] ^ $key[$i%6]);

$img=imageCreate(60,30);

$black=imageColorAllocate($img,0,0,0);
$white=imageColorAllocate($img,255,255,255);

imageRectangle($img,0,0,99,49,$white);
imageString($img, 5, 2, 10, $randstr, $white);

header("Content-type: image/png");
imagePng($img);
imageDestroy($img);
?>

