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
header ('Content-type: text/sql');

$db = new nullBB_Database ($_CONF, $_LANG);
$tables = $db->query('SHOW TABLES');

$views = array();

foreach ($tables as $td)  {
	$table = $td[key($td)];
	$r = $db->query("SHOW CREATE TABLE `$table`");

	if (!empty($r))  {
		$insert_sql = "";

		if (!strcasecmp(key($r[0]), 'View'))
			array_push ($views, $r[0][key($r[0])]);
		else  {
			$SQL .= "DROP TABLE IF EXISTS `$table`;\n";
			next($r[0]);
			$d = $r[0][key($r[0])].";";
			$SQL .= str_replace("\n", "", $d)."\n";

			$table_query = $db->query("SELECT * FROM `$table`");

			foreach ($table_query as $row)  {
				$num_fields = 0;
					
				foreach ($table_query[0] as $field => $value)  {
					$num_fields++;
				}
			}

			foreach ($table_query as $row)  {
				$insert_sql .= "INSERT INTO $table VALUES(";
				$i = 0;

				foreach ($row as $field => $value)  {
					if ($value != null)
						$insert_sql .= "'".sanitizeQuery($value)."'";
					else
						$insert_sql .= "NULL";

					if ($i < $num_fields-1)
						$insert_sql .= ', ';
					$i++;
				}

				$insert_sql .= ");";
			}

			if ($insert_sql != "")
				$SQL .= $insert_sql."\n";
		}
	}
}

foreach ($views as $view)  {
	$r = $db->query("SHOW CREATE VIEW `$view`");

	if (!empty($r))  {
		$insert_sql = "";
		$SQL .= "DROP VIEW IF EXISTS `$view`;\n";
		next($r[0]);

		$d = $r[0][key($r[0])].";";
		$SQL .= str_replace("\n", "", $d)."\n";
	}
}

$triggers = $db->query('SHOW TRIGGERS');

foreach ($triggers as $tt)  {
	$trigger = $tt['Trigger'];
	$timing = $tt['Timing'];
	$event = $tt['Event'];
	$table = $tt['Table'];
	$statement = $tt['Statement'];

	$SQL .= "DROP TRIGGER IF EXISTS `$trigger`;\n".
		"DELIMITER $$\n".
		"CREATE TRIGGER `$trigger` AFTER $event ON `$table`\n".
		"FOR EACH ROW\n".
		"$statement$$\n".
		"DELIMITER ;\n";
}

print $SQL;
$db->close();

?>

