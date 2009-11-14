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

class nullBB_Database  {
	private $db     = null;
	private $res    = null;
	private $dbhost = null;
	private $dbuser = null;
	private $dbpass = null;
	private $dbname = null;
	private $_CONF  = null;
	private $_LANG  = null;

	function __construct($conf, $lang)  {
		$this->_CONF = $conf;
		$this->_LANG = $lang;

		if (!(isset($this->_CONF['dbhost']) && isset($this->_CONF['dbuser']) &&
				isset($this->_CONF['dbpass']) && isset($this->_CONF['dbname'])) )  {
			require_once (ABSOLUTE_BASEPATH.'/languages/'.BOARD_LANGUAGE.'.lang');
			require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
			notification ("Invalid instance of nullBB_Database", '', 60);
			die();
		}

		$this->dbhost = $this->_CONF['dbhost'];
		$this->dbuser = $this->_CONF['dbuser'];
		$this->dbpass = $this->_CONF['dbpass'];
		$this->dbname = $this->_CONF['dbname'];
		$this->dbtype = $this->_CONF['dbtype'];

		$dbconnect = $this->dbtype.'_connect';
		$dbselect  = $this->dbtype.'_select_db';

		if (!($this->db = $dbconnect($this->dbhost, $this->dbuser, $this->dbpass)))  {
			require_once (ABSOLUTE_BASEPATH.'/languages/'.BOARD_LANGUAGE.'.lang');
			require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
			notification ($this->_LANG['db_connect_error'], '', 60);
			die();
		}

		if (! @$dbselect($this->dbname, $this->db))  {
			require_once (ABSOLUTE_BASEPATH.'/languages/'.BOARD_LANGUAGE.'.lang');
			require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
			notification ($this->_LANG['db_select_error'], '', 60);
			die();
		}
	}

	function query ($query)  {
		require_once (ABSOLUTE_BASEPATH.'/languages/'.BOARD_LANGUAGE.'.lang');
		
		$dbquery = $this->dbtype.'_query';
		$dbfetch = $this->dbtype.'_fetch_array';

		if (!($this->res = @$dbquery($query)))  {
			require_once (ABSOLUTE_BASEPATH.'/header.'.PHPEXT);
			notification ($this->_LANG['query_error'].
					'<br><br>'.$query.'<br><br>'.mysql_error(),
					'', 60);
			die();
		}

		$dbres = array();

		while ($row = @$dbfetch($this->res, MYSQL_ASSOC))  {
			array_push ($dbres, $row);
		}

		return $dbres;
	}

	function freeResult()  {
		$dbfree = $this->dbtype.'_free_result';
		@$dbfree($this->res);
	}

	function close()  {
		$dbclose = $this->dbtype.'_close';
		@$dbclose($this->db);
	}
}

?>
