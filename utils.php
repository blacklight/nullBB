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

function getInt ($val)  {
	if (!is_numeric($val))
		return 0;
	else
		return intval($val);
}

function notification ($message, $redirect = null, $interval = 0)  {
	print '<div class="message">'.$message.'</div>';

	if ($redirect)
		print '<meta http-equiv="refresh" content="'.$interval.';'.$redirect.'">';
}

function sanitizeHTML ($string)  {
	return (htmlentities($string, ENT_NOQUOTES));
}

function sanitizeQuery ($string)  {
	return addslashes($string);
}

function syntax_hilight ($enscript_path, $code, $lang)  {
	if ($lang == 'php') {
		$buffer = highlight_string($code, true);
		$buffer = preg_replace ('/^.*<code>/', '', $buffer);
		$buffer = preg_replace ('/<\/code>.*$/', '', $buffer);

		$buffer = str_replace("\n", "", $buffer);
		$buffer = str_replace("&nbsp;", " ", $buffer);
		$buffer = '<br /><br />'.implode("\n", explode("<br />", $buffer));
	} else {
		$lang = escapeshellarg($lang);
		$argv = "-C -q -p - -E --highlight=$lang --language=html --color";
		$desc = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'a')
		);

		if (!is_resource($pipe = proc_open($enscript_path.' '.$argv, $desc, $pipes)))
			die ("Error");

		fwrite ($pipes[0], $code);
		fclose ($pipes[0]);

		$buffer = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		proc_close($pipe);

		$buffer = eregi_replace('^.*<PRE>',  '<pre>',  $buffer);
		$buffer = eregi_replace('</PRE>.*$', '</pre>', $buffer);
	}

	$buffer = eregi_replace('<FONT COLOR="', '<span style="color:', $buffer);
	$buffer = eregi_replace('</FONT>', '</style>', $buffer);

	return $buffer;
}

function bb2html($text)  {
	@require_once ('./config.ini');
	require_once (ABSOLUTE_BASEPATH.'/languages/'.BOARD_LANGUAGE.'.lang');

	$bbcode = array(
		"[list]", "[*]", "[/list]", 
		"[b]", "[/b]", 
		"[u]", "[/u]", 
		"[i]", "[/i]",
		"[code]", "[/code]",
		"[quote]", "[/quote]",
	);
	
	$htmlcode = array(
		"<ul>", "<li>", "</ul>", 
		"<b>", "</b>", 
		"<u>", "</u>", 
		"<i>", "</i>",
		"<br><div class=\"code\" name=\"code\">", "</div><br>",
		"<br><div class=\"quote\"><b>QUOTE:</b><br><br>", "</div><br>",
	);

	$newtext = sanitizeHTML($text);

	if (get_magic_quotes_gpc())
		$newtext = stripslashes($newtext);

	if (preg_match_all('/\[img\](.*?)\[\/img\]/ims', $newtext, $match))  {
		foreach ($match[1] as $img)  {
			$newimg  = str_replace ('"', '', $img);
			$newtext = str_ireplace ("[img]".$img."[/img]", '<img src="'.$newimg.'">', $newtext);
		}
	}

	if (preg_match_all('/\[color=([^\]]+)\](.*?)\[\/color\]/ims', $newtext, $match))  {
		for ($i=0; $i < count($match[1]); $i++)  {
			$color   = $match[1][$i];
			$content = $match[2][$i];

			$newcolor = str_replace ('"', '', $color);
			$newtext  = str_ireplace ("[color=".$color."]".$content."[/color]",
				'<span style="color: '.$newcolor.'">'.$content.'</span>', $newtext);
		}
	}

	if (preg_match_all('/\[size=([^\]]+)\](.*?)\[\/size\]/ims', $newtext, $match))  {
		for ($i=0; $i < count($match[1]); $i++)  {
			$size    = $match[1][$i];
			$content = $match[2][$i];

			$newsize = str_replace ('"', '', $size);
			$newtext = str_ireplace ("[size=".$size."]".$content."[/size]",
				'<span style="font-size: '.$newsize.'">'.$content.'</span>', $newtext);
		}
	}

	if (preg_match_all('/\[mail=([^\]]+)\](.*?)\[\/mail\]/ims', $newtext, $match))  {
		for ($i=0; $i < count($match[1]); $i++)  {
			$mail    = $match[1][$i];
			$content = $match[2][$i];

			$newmail  = str_replace ('"', '', $mail);
			$newtext  = str_ireplace ("[mail=".$mail."]".$content."[/mail]",
				'<a target="_new" href="mailto:'.$newmail.'">'.$content.'</a>', $newtext);
		}
	}

	if (preg_match_all('/\[quote=([^\]]+)\]/ims', $newtext, $match))  {
		for ($i=0; $i < count($match[1]); $i++)  {
			$author  = $match[1][$i];

			$newauthor = str_replace ('\"', '', $author);
			$newauthor = str_replace ('"', '',  $newauthor);
			$newtext   = str_ireplace ("[quote=".$author."]",
				'<br><div class="quote"><b>'.$newauthor.' wrote:</b><br><br>', $newtext);
		}
	}

	if (preg_match_all('/\[url\](.*?)\[\/url\]/ims', $newtext, $match))  {
		$url = $match[1][0];
		$newurl  = str_replace ('"', '', $url);
		$newtext = str_ireplace ("[url]".$url."[/url]",
				'<a target="_new" href="'.$newurl.'">'.$newurl.'</a>', $newtext);
	}

	if (preg_match_all('/\[url=([^\]]+)\](.*?)\[\/url\]/ims', $newtext, $match))  {
		for ($i=0; $i < count($match[1]); $i++)  {
			$url  = $match[1][$i];
			$link = $match[2][$i];

			$newurl  = str_replace ('"', '', $url);
			$newtext = str_ireplace ("[url=".$url."]".$link."[/url]",
				'<a target="_new" href="'.$newurl.'">'.$link.'</a>', $newtext);
		}
	}

	if (TEX_BBCODE)  {
		if (preg_match_all('/\[tex\](.*?)\[\/tex\]/ims', $newtext, $match))  {
			for ($i=0; $i < count($match[1]); $i++)  {
				$math = $match[1][$i];

				if (($pipe = popen(TEXVC_PATH.' '.TEXPNG_PATH.' '.
						TEXPNG_PATH.' '.escapeshellarg($math).' iso-8859-1',
						'r')))  {
					
					$out = fgets($pipe);

					if (strlen($out) > 32)  {
						$hash = substr($out, 1, 32);

						if (preg_match('/^\.\//', TEXPNG_PATH))
							$pngpath = substr(TEXPNG_PATH, 2);
						else
							$pngpath = TEXPNG_PATH;
						$pngpath = BASEDIR.$pngpath;

						$newtext = str_ireplace("[tex]".$math."[/tex]",
							'<img src="'.$pngpath.'/'.$hash.'.png">',
							$newtext);
					} else {
						$newtext = str_ireplace("[tex]".$math."[/tex]",
							$_LANG['invalid_tex_formula'],
							$newtext);
					}
					
					pclose($pipe);
				}
			}
		}
	}

	$newtext = nl2br($newtext);

	if (CODE_HIGHLIGHTING == true)  {
		if (preg_match_all('/\[code lang=([^\]]+)\](.*?)\[\/code\]/ims', $newtext, $match))  {
			for ($i=0; $i < count($match[1]); $i++)  {
				$lang    = $match[1][$i];
				$content = $match[2][$i];
			
				$newcontent = preg_replace ('/<br[^>]*>/', '', $content);
				$newcontent = preg_replace ('/<([^>]*)>/', '[$1]', $newcontent);

				$newlang = str_replace ('\"', '', $lang);
				$newlang = str_replace ('"', '',  $newlang);
				$newlang = str_replace ("'", '',  $newlang);
				$newcontent = syntax_hilight(ENSCRIPT_PATH, html_entity_decode($newcontent), $newlang);
				$newtext = str_ireplace ("[code lang=".$match[1][$i]."]".$match[2][$i]."[/code]",
					'<br><div class="code">'.strtoupper(htmlspecialchars($newlang)).' CODE:'.$newcontent.'</div>', $newtext);
			}
		}
	} else {
		$newtext = preg_replace ('/\[code lang=[^\]]+\]/', '[code]', $newtext);
	}

	$newtext = str_replace($bbcode, $htmlcode, $newtext);

	if (CODE_HIGHLIGHTING == true)  {
		if (preg_match_all('/<div class="code">(.*?)<\/div>/ims', $newtext, $match))  {
			for ($i=0; $i < count($match[1]); $i++)  {
				$code = $match[1][$i];
				$newcode = str_replace ($htmlcode, $bbcode, $code);
				$newtext = str_replace('<div class="code">'.$code.'</div>',
					'<div class="code">'.$newcode.'</div>', $newtext);
			}
		}
	}

	if (preg_match_all('/<div class="code" name="code">(.*?)<\/div>/ims', $newtext, $match))  {
		foreach ($match[1] as $code)  {
			$newcode = preg_replace ('/<br[^>]*>/', '', $code);
			$newcode = preg_replace ('/<([^>]*)>/', '[$1]', $newcode);

			$newcode = '<b>CODE:</b><br><br>'.$newcode;
			$newtext =
				str_replace ('<div class="code" name="code">'.$code.'</div>',
						'<div class="code" name="code">'.$newcode.'</div>',
						$newtext);
		}
	}

	return $newtext;
}

function getSO ($agent)  {
	if (stristr($agent, 'debian'))
		return 'Debian';
	else if (stristr($agent, 'gentoo'))
		return 'Gentoo';
	else if (stristr($agent, 'red hat'))
		return 'Red Hat';
	else if (stristr($agent, 'fedora'))
		return 'Fedora';
	else if (stristr($agent, 'ubuntu'))
		return 'Ubuntu';
	else if (stristr($agent, 'linux'))
		return 'Linux';
	else if (stristr($agent, 'freebsd'))
		return 'FreeBSD';
	else if (stristr($agent, 'openbsd'))
		return 'OpenBSD';
	else if (stristr($agent, 'netbsd'))
		return 'NetBSD';
	else if (stristr($agent, 'bsd'))
		return '*BSD';
	else if (stristr($agent, 'solaris') || stristr($agent, 'sunos'))
		return 'SunOS';
	else if (stristr($agent, 'minix'))
		return 'Minix';
	else if (stristr($agent, 'curl'))
		return 'cURL';
	else if (stristr($agent, 'macos') || stristr($agent, 'darwin') || stristr($agent, 'macintosh'))
		return 'MacOS';
	else if (stristr($agent, 'windows 3.'))
		return 'Windows 3.x';
	else if (stristr($agent, 'windows 95') || stristr($agent, 'win95'))
		return 'Windows 95';
	else if (stristr($agent, 'windows 98') || stristr($agent, 'win98'))
		return 'Windows 98';
	else if (stristr($agent, 'windows CE') || stristr($agent, 'wince'))
		return 'Windows CE';
	else if (stristr($agent, 'windows ME') || stristr($agent, 'winme') ||
			stristr($agent, 'win 9x'))
		return 'Windows ME';
	else if (stristr($agent, 'windows NT 4.') || stristr($agent, 'winnt 4.'))
		return 'Windows NT';
	else if (stristr($agent, 'windows NT 5.0') || stristr($agent, 'winnt 5.0'))
		return 'Windows 2000';
	else if (stristr($agent, 'windows NT 5.1') || stristr($agent, 'winnt 5.1'))
		return 'Windows XP';
	else if (stristr($agent, 'windows NT 6.') || stristr($agent, 'winnt 6.'))
		return 'Windows Vista';
	else if (stristr($agent, 'windows NT 7.') || stristr($agent, 'winnt 7.'))
		return 'Windows 7';
	else
		return 'null';
}

function getBrowser ($agent)  {
	if (stristr($agent, 'firefox') || stristr($agent, 'iceweasel'))
		return 'Firefox';
	else if (stristr($agent, 'netscape'))
		return 'Netscape';
	else if (stristr($agent, 'opera'))
		return 'Opera';
	else if (stristr($agent, 'chrome'))
		return 'Chrome';
	else if (stristr($agent, 'galeon'))
		return 'Galeon';
	else if (stristr($agent, 'epiphany'))
		return 'Epiphany';
	else if (stristr($agent, 'elinks'))
		return 'eLinks';
	else if (stristr($agent, 'links'))
		return 'Links';
	else if (stristr($agent, 'lynx'))
		return 'Lynx';
	else if (stristr($agent, 'w3m'))
		return 'w3m';
	else if (stristr($agent, 'safari'))
		return 'Safari';
	else if (stristr($agent, 'camino'))
		return 'Camino';
	else if (stristr($agent, 'konqueror'))
		return 'Konqueror';
	else if (stristr($agent, 'msie'))
		return 'Internet Explorer';
	else if (stristr($agent, 'mozilla'))
		return 'Mozilla';
	else
		return 'null';
}

?>
