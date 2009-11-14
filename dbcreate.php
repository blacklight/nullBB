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

if ($_REQUEST['admin_username'] && $_REQUEST['admin_password']
		&& $_REQUEST['admin_password_again'] && $_REQUEST['admin_email'])  {
	$user = sanitizeQuery($_REQUEST['admin_username']);
	$pass = $_REQUEST['admin_password'];
	$passagain = $_REQUEST['admin_password_again'];
	$email = sanitizeQuery($_REQUEST['admin_email']);

	if ($pass != $passagain)
		die ($_LANG['not_matching_passwords']);

$sql =
"drop table if exists ".$_CONF["dbprefix"]."forums;\n".
"drop table if exists ".$_CONF["dbprefix"]."topics;\n".
"drop table if exists ".$_CONF["dbprefix"]."posts;\n".
"drop table if exists ".$_CONF["dbprefix"]."privmsgs;\n".
"drop table if exists ".$_CONF["dbprefix"]."users;\n".
"drop table if exists ".$_CONF["dbprefix"]."sessions;\n".
"drop table if exists ".$_CONF["dbprefix"]."karma;\n".
"drop table if exists ".$_CONF["dbprefix"]."groups;\n".
"drop table if exists ".$_CONF["dbprefix"]."viewtopics;\n".
"\n".
"drop view if exists ".$_CONF["dbprefix"]."newtopics;\n".
"\n".
"drop trigger if exists insTopic;\n".
"drop trigger if exists insPost;\n".
"drop trigger if exists delPost;\n".
"drop trigger if exists delUser;\n".
"drop trigger if exists delTopic;\n".
"\n".
"create table ".$_CONF["dbprefix"]."forums(\n".
"forum_id 			integer unsigned not null auto_increment,\n".
"forum_name 		varchar(150),\n".
"forum_desc 		text,\n".
"forum_posts 		integer unsigned default 0,\n".
"forum_topics 		integer unsigned default 0,\n".
"forum_lasttopic 	integer unsigned not null default 0,\n".
"forum_lastpost 	integer unsigned not null default 0,\n".
"forum_lasttime 	integer unsigned not null default 0,\n".
"forum_viewgroup 	smallint default 20 not null,\n".
"forum_postgroup 	smallint default 10 not null,\n".
"forum_vieworder 	integer unsigned not null default 0,\n".
"\n".
"primary key(forum_id),\n".
"foreign key(forum_lasttopic) 	references ".$_CONF["dbprefix"]."topics(topic_id),\n".
"foreign key(forum_lastpost) 	references ".$_CONF["dbprefix"]."posts(post_id),\n".
"foreign key(forum_lasttime) 	references ".$_CONF["dbprefix"]."posts(post_time),\n".
"foreign key(forum_viewgroup) 	references ".$_CONF["dbprefix"]."groups(group_id),\n".
"foreign key(forum_postgroup) 	references ".$_CONF["dbprefix"]."groups(group_id)\n".
");\n".
"\n".
"create table ".$_CONF["dbprefix"]."topics(\n".
"topic_id 			integer unsigned not null auto_increment,\n".
"forum_id 			integer unsigned not null,\n".
"topic_title 		varchar(128),\n".
"topic_poster 		integer unsigned not null,\n".
"topic_time 		integer unsigned default 0,\n".
"topic_replies 		integer unsigned default 0,\n".
"topic_lastreply 	integer unsigned not null default 0,\n".
"topic_disabled 	boolean default 0 not null,\n".
"topic_sticked 		boolean default 0 not null,\n".
"\n".
"primary key(topic_id),\n".
"foreign key(forum_id) 		references ".$_CONF["dbprefix"]."forums(forum_id),\n".
"foreign key(topic_poster) 	references ".$_CONF["dbprefix"]."users(user_id),\n".
"foreign key(topic_lastreply) 	references ".$_CONF["dbprefix"]."posts(post_id)\n".
");\n".
"\n".
"create table ".$_CONF["dbprefix"]."posts(\n".
"post_id 			integer unsigned not null auto_increment,\n".
"topic_id 			integer unsigned not null,\n".
"forum_id 			integer unsigned not null,\n".
"poster_id 		integer unsigned not null,\n".
"poster_ip 		varchar(40) not null,\n".
"poster_so 		varchar(64),\n".
"poster_browser 	varchar(64),\n".
"post_time 		integer unsigned default 0,\n".
"post_content 		text,\n".
"post_lastedit_date 	integer unsigned default null,\n".
"post_lastedit_user 	integer unsigned default null,\n".
"\n".
"primary key(post_id),\n".
"foreign key(topic_id) 		references ".$_CONF["dbprefix"]."topics(topic_id),\n".
"foreign key(forum_id) 		references ".$_CONF["dbprefix"]."forums(forum_id),\n".
"foreign key(poster_id) 		references ".$_CONF["dbprefix"]."useres(user_id)\n".
");\n".
"\n".
"create table ".$_CONF["dbprefix"]."privmsgs(\n".
"privmsg_id 		integer unsigned not null auto_increment,\n".
"privmsg_subject 	varchar(255),\n".
"privmsg_from 		integer unsigned not null,\n".
"privmsg_to 		integer unsigned not null,\n".
"privmsg_date 		integer unsigned not null,\n".
"privmsg_ip 		varchar(40) not null,\n".
"privmsg_seen 		boolean default 0,\n".
"privmsg_content 	text,\n".
"\n".
"primary key(privmsg_id),\n".
"foreign key(privmsg_from) 	references ".$_CONF["dbprefix"]."users(user_id),\n".
"foreign key(privmsg_to)  	references ".$_CONF["dbprefix"]."users(user_id)\n".
");\n".
"\n".
"create table ".$_CONF["dbprefix"]."users(\n".
"user_id 			integer unsigned not null auto_increment,\n".
"username 			varchar(25) unique not null,\n".
"user_password 		varchar(60) not null,\n".
"user_posts 		integer unsigned default 0 not null,\n".
"user_email 		varchar(60) unique not null,\n".
"user_website 		varchar(60),\n".
"user_msn 			varchar(60),\n".
"user_karma 		integer default 0 not null,\n".
"user_regtime 		integer unsigned default 0 not null,\n".
"user_disabled 		boolean default false not null,\n".
"user_group 		smallint default 10 not null,\n".
"user_signature 	text,\n".
"user_avatar 		text,\n".
"user_viewavatars 	boolean default 0,\n".
"user_theme 		varchar(60) default '".$_CONF['theme']."',\n".
"user_language 	varchar(50) default '".BOARD_LANGUAGE."',\n".
"\n".
"primary key(user_id),\n".
"foreign key(user_group) references ".$_CONF["dbprefix"]."groups(group_id)\n".
");\n".
"\n".
"create table ".$_CONF["dbprefix"]."sessions(\n".
"session_id 		varchar(60) not null,\n".
"user_id 			integer unsigned not null,\n".
"session_time 		integer unsigned default 0 not null,\n".
"session_lasttime 	integer unsigned default 0 not null,\n".
"\n".
"primary key(session_id),\n".
"foreign key(user_id) 		references ".$_CONF["dbprefix"]."users(user_id)\n".
");\n".
"\n".
"create table ".$_CONF["dbprefix"]."karma(\n".
"user_id 			integer unsigned default 0 not null,\n".
"voter 			integer unsigned default 0 not null,\n".
"vote 			integer default 0,\n".
"\n".
"primary key(user_id, voter),\n".
"foreign key(user_id) 		references ".$_CONF["dbprefix"]."karma(user_id),\n".
"foreign key(voter) 			references ".$_CONF["dbprefix"]."karma(voter)\n".
");\n".
"\n".
"create table ".$_CONF["dbprefix"]."groups(\n".
"group_id 			smallint not null,\n".
"group_name 		varchar(20),\n".
"primary key(group_id)\n".
");\n".
"\n".
"create table ".$_CONF["dbprefix"]."viewtopics(\n".
"user_id 			integer unsigned not null,\n".
"topic_id 			integer unsigned not null,\n".
"viewtime 			integer unsigned not null,\n".
"\n".
"primary key(user_id, topic_id),\n".
"foreign key(user_id) references ".$_CONF["dbprefix"]."users(user_id),\n".
"foreign key(topic_id) references ".$_CONF["dbprefix"]."topics(topic_id)\n".
");\n".
"\n".
"create view ".$_CONF["dbprefix"]."newtopics\n".
"as\n".
"select f.forum_id, f.forum_name, t.topic_id, t.topic_title, t.topic_lastreply,\n".
"p.poster_id, u.username as last_poster, p.post_time\n".
"from ".$_CONF["dbprefix"]."forums f join ".$_CONF["dbprefix"]."topics t join ".$_CONF["dbprefix"]."posts p join ".$_CONF["dbprefix"]."users u\n".
"on f.forum_id=t.forum_id\n".
"and f.forum_id=p.forum_id\n".
"and p.topic_id=t.topic_id\n".
"and t.topic_lastreply=p.post_id\n".
"and p.poster_id=u.user_id\n".
"where p.post_id=t.topic_lastreply\n".
"order by p.post_time desc;\n".

"insert into ".$_CONF["dbprefix"]."groups(group_id, group_name) values(".USERLEV_GOD.", 'God');\n".
"insert into ".$_CONF["dbprefix"]."groups(group_id, group_name) values(".USERLEV_ADMIN.", 'Admin');\n".
"insert into ".$_CONF["dbprefix"]."groups(group_id, group_name) values(".USERLEV_GLOBALMOD.", 'Global mod');\n".
"insert into ".$_CONF["dbprefix"]."groups(group_id, group_name) values(".USERLEV_MOD.", 'Mod');\n".
"insert into ".$_CONF["dbprefix"]."groups(group_id, group_name) values(".USERLEV_USER.", 'User');\n".
"insert into ".$_CONF["dbprefix"]."groups(group_id, group_name) values(".USERLEV_ANY.", 'Any');\n".
"insert into ".$_CONF["dbprefix"]."groups(group_id, group_name) values(".USERLEV_BANNED.", 'Banned');\n".
"insert into ".$_CONF["dbprefix"]."users(user_id, username, user_password, user_group, user_regtime, user_email) values(1,'".$user."', '".
sha1(md5($pass))."', '".USERLEV_GOD."', '".time()."', '$email')\n";

$db = new nullBB_Database($_CONF, $_LANG);

foreach (explode(';', $sql) as $query)  {
	$db->query(trim($query));
}

$sql =
"create trigger insTopic\n".
"after insert\n".
"on ".$_CONF["dbprefix"]."topics\n".
"for each row\n".
"begin\n".
"select forum_topics into @num from ".$_CONF["dbprefix"]."forums where forum_id = new.forum_id;\n".
"update ".$_CONF["dbprefix"]."forums set forum_topics = @num + 1 where forum_id = new.forum_id;\n".
"update ".$_CONF["dbprefix"]."forums set forum_lasttopic = new.topic_id where forum_id = new.forum_id;\n".
"end;";
	
$db->query($sql);

$sql =
"create trigger insPost\n".
"after insert\n".
"on ".$_CONF["dbprefix"]."posts\n".
"for each row\n".
"begin\n".
"select forum_posts into @num from ".$_CONF["dbprefix"]."forums where forum_id = new.forum_id;\n".
"update ".$_CONF["dbprefix"]."forums set forum_posts = @num + 1 where forum_id = new.forum_id;\n".
"select topic_replies into @num from ".$_CONF["dbprefix"]."topics where topic_id = new.topic_id;\n".
"update ".$_CONF["dbprefix"]."topics set topic_replies = @num + 1 where topic_id = new.topic_id;\n".
"select user_posts into @num from ".$_CONF["dbprefix"]."users where user_id = new.poster_id;\n".
"update ".$_CONF["dbprefix"]."users set user_posts = @num + 1 where user_id = new.poster_id;\n".
"update ".$_CONF["dbprefix"]."topics set topic_lastreply = new.post_id where topic_id = new.topic_id;\n".
"update ".$_CONF["dbprefix"]."forums set forum_lastpost  = new.post_id where forum_id = new.forum_id;\n".
"update ".$_CONF["dbprefix"]."forums set forum_lasttime  = new.post_time where forum_id = new.forum_id;\n".
"end;";

$db->query($sql);

$sql =
"create trigger delUser\n".
"after delete\n".
"on ".$_CONF["dbprefix"]."users\n".
"for each row\n".
"begin\n".
"update ".$_CONF["dbprefix"]."topics set topic_poster=0 where topic_poster=old.user_id;\n".
"update ".$_CONF["dbprefix"]."posts set poster_id=0 where poster_id=old.user_id;\n".
"end;\n";

$db->query($sql);

/* DUMP, STUPID, ASSHOLE MySQL
 * The delTopic trigger is not accepted if executed via MySQL query, while it is if you
 * dump it to an SQL file and just pass it to your database. This is the MySQL error
 * message I get on my system if I try to uncomment these lines:
 * "This version of MySQL doesn't yet support 'multiple triggers with the same action time and event for one table'"
 * That's just meaningless, and a big big bug in MySQL. If your MySQL version is not prone
 * to this stupid bug, just copy the commented lines below and execute them as a query
 * on your MySQL database
 */

/*$sql =
"create trigger delTopic\n".
"after delete\n".
"on ".$_CONF["dbprefix"]."topics\n".
"for each row\n".
"begin\n".
"select forum_topics into @num from ".$_CONF["dbprefix"]."forums where forum_id = old.forum_id;\n".
"update ".$_CONF["dbprefix"]."forums set forum_topics = @num - 1 where forum_id = old.forum_id;\n".
"select count(*) into @topicPosts from ".$_CONF["dbprefix"]."posts where topic_id = old.topic_id;\n".
"select forum_posts into @forumPosts from ".$_CONF["dbprefix"]."forums where forum_id = old.forum_id;\n".
"update ".$_CONF["dbprefix"]."forums set forum_posts = @forumPosts - @topicPosts where forum_id = old.forum_id;\n".
"delete from ".$_CONF["dbprefix"]."posts where topic_id = old.topic_id;\n".
"select topic_id into @lasttopic from ".$_CONF["dbprefix"]."topics where forum_id = old.forum_id order by topic_time desc limit 1,1;\n".
"update ".$_CONF["dbprefix"]."forums set forum_lasttopic = @lasttopic where forum_id = old.forum_id;\n".
"end;".

$db->query($sql);*/

$sql =
"create trigger delPost\n".
"after delete\n".
"on ".$_CONF["dbprefix"]."posts\n".
"for each row\n".
"begin\n".
"select forum_posts into @num from ".$_CONF["dbprefix"]."forums where forum_id = old.forum_id;\n".
"update ".$_CONF["dbprefix"]."forums set forum_posts = @num - 1 where forum_id = old.forum_id;\n".
"select topic_replies into @num from ".$_CONF["dbprefix"]."topics where topic_id = old.topic_id;\n".
"update ".$_CONF["dbprefix"]."topics set topic_replies = @num - 1 where topic_id = old.topic_id;\n".
"select user_posts into @num from ".$_CONF["dbprefix"]."users where user_id = old.poster_id;\n".
"update ".$_CONF["dbprefix"]."users set user_posts = @num - 1 where user_id = old.poster_id;\n".
"select post_id into @lastreply from ".$_CONF["dbprefix"]."posts where topic_id = old.topic_id order by post_time desc limit 1,1;\n".
"update ".$_CONF["dbprefix"]."topics set topic_lastreply = @lastreply where topic_id = old.topic_id;\n".
"select post_id into @lastpost from ".$_CONF["dbprefix"]."posts where forum_id = old.forum_id order by post_time desc limit 1,1;\n".
"update ".$_CONF["dbprefix"]."forums set forum_lastpost  = @lastpost where forum_id = old.forum_id;\n".
"select post_time into @lasttime from ".$_CONF["dbprefix"]."posts where forum_id = old.forum_id order by post_time desc limit 1,1;\n".
"update ".$_CONF["dbprefix"]."forums set forum_lasttime  = @lasttime where forum_id = old.forum_id;\n".
"end;";

$db->query($sql);

$session_id = sha1($user.$pass.time());
setcookie ('sid', $session_id, time()+15*24*60*60, BASEDIR);

$db->query('insert into '.$_CONF['dbprefix'].'sessions(session_id, user_id, session_time, '.
	'session_lasttime) values('.
	"'$session_id', '1', '".time()."', '0')");

$db->close();

if (!($fp = fopen('.htaccess', 'w')))  {
	notification ($_LANG['htaccess_write_error'], '', 60);
	die();
}

$htaccess =
'RewriteEngine on'."\n".
'RewriteRule ^([0-9]+)$ '.BASEDIR.'viewforum.php?id=$1'."\n".
'RewriteRule ^topic/([0-9]+)$ '.BASEDIR.'viewtopic.php?id=$1'."\n".
'RewriteRule ^topic/([0-9]+)/([0-9]+)$ '.BASEDIR.'viewtopic.php?id=$1&page=$2'."\n".
'RewriteRule ^topic/([0-9]+)/([0-9]+)(#[0-9]+)$ '.BASEDIR.'viewtopic.php?id=$1&page=$2$3'."\n".
'RewriteRule ^user/([0-9]+)$ '.BASEDIR.'viewuser.php?id=$1'."\n".
'RewriteRule ^admin/_([a-z]+)$ '.BASEDIR.'admin/index.php?action=$1'."\n".
'RewriteRule ^admin/(.*)\.sql$ '.BASEDIR.'admin/$1.php'."\n";

fputs ($fp, $htaccess);
fclose($fp);

print '<meta http-equiv="Refresh" content="0;url='.BASEDIR.'index.'.PHPEXT.'">';

?>


<?php

} else {

?>

<html>
	<head>
		<link href="<?php print BASEDIR.'themes/'.$_CONF['theme']; ?>/style.css" rel="stylesheet" type="text/css">
	</head>

	<body style="padding: 20px">

<?php

	print $_LANG['first_access_db_create']."<br><br>\n";

?>

		<form action="#" method="POST">
			<?php print $_LANG['admin_username']; ?><br>
			<input type="text" name="admin_username"><br>
			
			<?php print $_LANG['admin_password']; ?><br>
			<input type="password" name="admin_password"><br>
			
			<?php print $_LANG['admin_password_again']; ?><br>
			<input type="password" name="admin_password_again"><br>
			
			<?php print $_LANG['admin_email']; ?><br>
			<input type="text" name="admin_email"><br>

			<input type="submit" value="<?php print $_LANG['create_database']; ?>">
		</form>

<?php

}

?>

	</body>
</html>
