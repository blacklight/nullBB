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

require_once (ABSOLUTE_BASEPATH.'/languages/'.BOARD_LANGUAGE.'.lang');
require_once (ABSOLUTE_BASEPATH.'/db.'.PHPEXT);

$db = new nullBB_Database ($_CONF, $_LANG);
$res = $db->query('SHOW TABLES');
$dbOK = true;

if (count($res) == 0)
	$dbOK = false;
else  {
	$dbOK = false;

	foreach ($res as $row)  {
		foreach ($row as $key => $table)  {
			if ($table == $_CONF['dbprefix'].'users')
				$dbOK = true;
		}
	}
}

if (!$dbOK)  {
	require_once (ABSOLUTE_BASEPATH.'/dbcreate.'.PHPEXT);
	die();
}

class nullBB_Session  {
	private $_CONF = null;
	private $_LANG = null;
	
	public $user_id = 0;
	public $session_time = 0;
	public $session_lasttime = 0;
	public $session_id = null;
	public $logged = false;

	function __construct ($data, $conf, $lang)  {
		$this->_CONF = $conf;
		$this->_LANG = $lang;

		if (isset($data['username']))  {
			$db = new nullBB_Database($this->_CONF, $this->_LANG);
			$username = stripslashes(strtolower($data['username']));
			$username = sanitizeQuery($username);

			$user_res = $db->query('select user_id, user_password from '.$this->_CONF['dbprefix']."users where username='".$username."' limit 1");
			$db->freeResult();

			if (empty($user_res))
				die ($this->_LANG['invalid_session']);

			$this->user_id = intval($user_res[0]['user_id']);
			$this->session_time = time();
			$pass = $user_res[0]['user_password'];
			$this->session_id = sha1($username.$pass.$this->session_time);
			unset($user_res);

			$session_res = $db->query('select session_time from '.$this->_CONF['dbprefix']."sessions where user_id='".$this->user_id."' limit 1");
			$db->freeResult();

			if ($session_res[0]['session_time'])  {
				$this->session_lasttime = $session_res[0]['session_time'];
				$db->query('delete from '.$this->_CONF['dbprefix'].'sessions where user_id='.$this->user_id);
				unset($session_res);
			} else if (isset($_COOKIE['lasttime'])) {
				$this->session_lasttime = intval($_COOKIE['lasttime']);
			} else {
				$this->session_lasttime = 0;
			}

			$db->query('insert into '.$this->_CONF['dbprefix'].'sessions(session_id, user_id, session_time, session_lasttime) values('.
				"'".$this->session_id."', '".$this->user_id."', '".$this->session_time."', '".$this->session_lasttime."')");

			$this->logged = true;
			setcookie ('sid', $this->session_id, time()+15*24*60*60, BASEDIR);
			$db->close();
		} else if (isset($data['sid'])) {
			$db = new nullBB_Database($this->_CONF, $this->LANG);
			$sid = stripslashes(strtolower($data['sid']));
			$sid = sanitizeQuery($sid);

			$res = $db->query('select * from '.$this->_CONF['dbprefix']."sessions where session_id='$sid'");
			$db->freeResult();

			if (empty($res))
				return;

			$this->user_id = $res[0]['user_id'];
			$this->session_id = $res[0]['session_id'];
			$this->session_time = $res[0]['session_time'];

			if ($res[0]['session_lasttime'])
				$this->session_lasttime = intval($res[0]['session_lasttime']);
			else if ($_COOKIE['lasttime'])
				$this->session_lasttime = intval($_COOKIE['lasttime']);
			else
				$this->session_lasttime = 0;

			$this->logged = true;
			unset($res);
		}
	}

	function destroy()  {
		if (! $this->session_id )
			return;

		setcookie('sid', '', time(), BASEDIR);
		setcookie('lasttime', time(), time()+60*60*24*365, BASEDIR);
		$this->logged = false;

		$db = new nullBB_Database($this->_CONF, $this->_LANG);
		$db->query('delete from '.$this->_CONF['dbprefix'].
			"sessions where session_id='".addslashes($this->session_id)."'");
		$db->freeResult();
		$db->close();
	}
}

$logged = false;
$userinfo = array();

if (isset($_COOKIE['sid']))  {
	$session = new nullBB_Session(array('sid' => $_COOKIE['sid']), $_CONF, $_LANG);
	$logged = $session->logged;

	if ($logged)  {
		$db = new nullBB_Database($_CONF, $_LANG);
		$res = $db->query('select * from '.$_CONF['dbprefix'].'users where user_id='.intval($session->user_id));
		$db->freeResult();
		$userinfo = $res[0];
		$db->close();
	}
}

?>
