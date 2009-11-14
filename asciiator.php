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

function sobel ($img, $x, $y, $sizeX, $sizeY)  {
	$gx_matrix = array(
		array(-1, 0, 1),
		array(-2, 0, 2),
		array(-1, 0, 1)
	);

	$gy_matrix = array(
		array( 1, 2, 1),
		array( 0, 0, 0),
		array(-1,-2,-1)
	);

	$xg = 0; $yg = 0; $sx = 0;

	for ($mx=$x; $mx < $x + 3; $mx++)  {
		$sy = 0;

		for ($my=$y; $my < $y + 3; $my++)  {
			if ($mx < $sizeX && $my < $sizeY)  {
				$rgb = imagecolorat($img, $mx, $my);

				$xg += ($rgb * $gx_matrix[$sx][$sy]);
				$yg += ($rgb * $gy_matrix[$sx][$sy]);
			}

			$sy++;
		}

		$sx++;
	}

	return (double) abs($xg) + abs($yg);
}

function img2ascii ($imgfile, $ext)  {
	if ($ext == 'gif')
		$threshold = 270;
	else
		$threshold = 13000000;

	$func = "imagecreatefrom$ext";
	$ascii = '';

	if (!($fp = fopen($imgfile, 'rb')))
		die();
	fclose($fp);

	$img = $func($imgfile);
	list ($width, $height) = getimagesize($imgfile);

	if ($width > 100 || $height > 100)  {
		$max = ($width > $height) ? $width : $height;
		$ratio = $max/100;

		imagecopyresized ($img, $img, 0, 0, 0, 0, (int) $width/$ratio, (int) $height/$ratio, $width, $height);
	
		$width  = (int) $width/$ratio;
		$height = (int) $height/$ratio;
	}

	for ($y=0; $y < $height; $y++)  {
		for ($x=0; $x < $width; $x++)  {
			$sobel = sobel($img, $x, $y, $width, $height);

			if ($sobel > $threshold && $x > 0 && $y > 0 &&
					$x < $width - 3 && $y < $height - 3)  {
				if (strpos($_SERVER['HTTP_USER_AGENT'], 'chrome'))
					$ascii .= '.';
				else
					$ascii .= '. ';
			} else {
				if (strpos($_SERVER['HTTP_USER_AGENT'], 'chrome'))
					$ascii .= ' ';
				else
					$ascii .= '  ';
			}
		}

		$ascii .= "\n";
	}

	return $ascii;
}

?>

