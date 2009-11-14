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

function searchUser()  {
	var popup = null;

	if (!(popup = document.getElementById('loginPopup')))  {
		popup = document.createElement('div');

		var id = document.createAttribute('id');
		var scroll = getScrollXY();

		id.nodeValue = 'loginPopup';
		popup.setAttributeNode(id);
		popup.style.left = parseInt((window.innerWidth/2) + scroll[0] - 150) + 'px';
		popup.style.top  = parseInt((window.innerHeight/2) + scroll[1] - 150) + 'px';
		popup.style.height = '130px';

		popup.innerHTML =
			'<table width="100%">' +
			'<tr><td style="text-align: left; border-bottom: 1px dotted #fff; font-size: 13px">Search a user</td>' +
			'<td style="text-align: right; border-bottom: 1px dotted #fff">' +
			'<a href="javascript:searchUser()" style="text-align: right; font-size: 10px">x close</a></td></tr></table><br>' +
			'<table width="100%">' +
			'<tr><td style="text-align: left">&gt; username (wildcards "*" are allowed)</td>' +
			'<td style="text-align: right"><input type="text" name="search_username" class="login"></td></tr>' +
			'<tr><td>&gt;</td><td style="text-align: right">' +
			"<button onClick='usersByName(document.getElementsByName(\"search_username\")[0].value)'>Search</button></td></tr></table>" +
			'<div id="search_results" style="text-align: center"></div>';

		document.body.appendChild(popup);
		document.getElementsByName('search_username')[0].focus();
	} else {
		document.body.removeChild(popup);
	}
}

function usersByName (user)  {
	var xml = null;
	var search_results = document.getElementById('search_results');
	
	document.getElementById('loginPopup').style.height = '170px';
	user = escape(user);

	if (window.XMLHttpRequest)
		xml = new XMLHttpRequest();
	else if (window.ActiveXObject)
		xml = new ActiveXObject("Microsoft.XMLHTTP");
	else
		return null;

	search_results.innerHTML = 'Loading...';

	xml.open('GET', 'usersbyname.php?user=' + user);
	xml.onreadystatechange = function()  {
		if (xml.readyState == 4 && xml.status == 200)  {
			if (search_results.innerHTML == 'Loading...')
				search_results.innerHTML = '';

			var lines = xml.responseText.split("\n");
			
			if (lines.length < 2)
				search_results.innerHTML = 'No results found';
			else  {
				search_results.innerHTML = '<br><select name="results">';

				for (var i in lines)  {
					var fields = lines[i].split(" \# ");

					if (fields[1] != undefined)  {
						var results = document.getElementsByName('results')[0];
						results.innerHTML += '<option value="' + fields[1] + '">' + fields[1] + '</option>';
					}
				}

				search_results.innerHTML += '</select>' +
					"<button onClick='selectUser(document.getElementsByName(\"results\")[0].value)'>Select</button>";
			}
		}
	}

	xml.send(null);
}

function selectUser (user)  {
	if (document.getElementById('loginPopup'))
		document.body.removeChild(document.getElementById('loginPopup'));

	document.getElementsByName('username')[0].value = user;
}

function editUser (user)  {
	var xml = null;
	var body = document.getElementById('adminBody');
	user = escape(user);

	if (window.XMLHttpRequest)
		xml = new XMLHttpRequest();
	else if (window.ActiveXObject)
		xml = new ActiveXObject("Microsoft.XMLHTTP");
	else  {
		body.innerHTML = 'AJAX not supported on your browser, sorry';
		return null;
	}

	xml.open('GET', 'usersbyname.php?user=' + user);

	xml.onreadystatechange = function()  {
		if (xml.readyState == 4 && xml.status == 200)  {
			var uid = null;
			var lines = xml.responseText.split("\n");

			if (lines.length < 2)  {
				body.innerHTML += '<br><br>User not found';
				return null;
			} else {
				for (var i=0; i < lines.length && uid == null; i++)  {
					var fields = lines[i].split(" \# ");

					if (fields[0].match(/^\d+$/))  {
						uid = fields[0];
					}
				}
			}

			if (uid.match(/^\d+$/) == false)  {
				body.innerHTML = '<br><br>User not found';
				return null;
			}

			var xml2 = null;
			var body = document.getElementById('adminBody');
			body.innerHTML = 'Loading...';

			if (window.XMLHttpRequest)
				xml2 = new XMLHttpRequest();
			else if (window.ActiveXObject)
				xml2 = new ActiveXObject("Microsoft.XMLHTTP");
			else  {
				body.innerHTML = 'AJAX not supported on your browser, sorry';
				return null;
			}

			xml2.open('GET', 'edituser.php?uid=' + uid);

			xml2.onreadystatechange = function()  {
				if (xml2.readyState == 4 && xml2.status == 200)  {
					body.innerHTML = xml2.responseText;
				}
			}

			xml2.send(null);
		}
	}

	xml.send(null);
}

function editForum (forum)  {
	var xml = null;
	var action = null;
	var id = forum.id;

	switch (forum.value)  {
		case 'move_up':
		case 'move_down':
		case 'delete':
		case 'edit_name':
		case 'edit_desc':
		case 'edit_priv':
			action = forum.value;
			break;

		default:
			return null;
	}

	switch (forum.value)  {
		case 'edit_name':
			var inner = document.getElementById('name' + forum.id).innerHTML.replace(/<[^>]*>/g, '');

			document.getElementById('name' + forum.id).innerHTML =
				'<input type="text" name="input' + forum.id + '" ' +
				'value="' + inner + '">' +
				"<button onClick=\"editForumProperty('" + forum.id + "', 'name', " +
				"document.getElementsByName('input" + forum.id + "')[0].value)\"" +
				">Change</button>";

			document.getElementsByName('input' + forum.id)[0].focus();
			document.getElementsByName('input' + forum.id)[0].select();
			
			if (document.getElementById(id))
				document.getElementById(id).value = 'none';

			return true;
			break;

		case 'edit_desc':
			var inner = document.getElementById('desc' + forum.id).innerHTML.replace(/<[^>]*>/g, '');

			document.getElementById('desc' + forum.id).innerHTML =
				'<input type="text" name="input' + forum.id + '" ' +
				'value="' + inner + '">' +
				"<button onClick=\"editForumProperty('" + forum.id + "', 'desc', " +
				"document.getElementsByName('input" + forum.id + "')[0].value)\"" +
				">Change</button>";

			document.getElementsByName('input' + forum.id)[0].focus();
			document.getElementsByName('input' + forum.id)[0].select();
			
			if (document.getElementById(id))
				document.getElementById(id).value = 'none';

			return true;
			break;

		case 'edit_priv':
			var popup = null;

			if (!(popup = document.getElementById('loginPopup')))  {
				popup = document.createElement('div');

				var id = document.createAttribute('id');
				var scroll = getScrollXY();

				id.nodeValue = 'loginPopup';
				popup.setAttributeNode(id);
				popup.style.left = parseInt((window.innerWidth/2) + scroll[0] - 150) + 'px';
				popup.style.top  = parseInt((window.innerHeight/2) + scroll[1] - 150) + 'px';
				popup.style.width  = '350px';
				popup.style.height = '120px';

				popup.innerHTML = 'Loading...';
				document.body.appendChild(popup);

				if (window.XMLHttpRequest)
					xml = new XMLHttpRequest();
				else if (window.ActiveXObject)
					xml = new ActiveXObject("Microsoft.XMLHTTP");
				else  {
					document.body.innerHTML = 'AJAX not supported on your browser, sorry';
					return null;
				}

				xml.open('GET', 'editforum.php?fid=' + forum.id + '&action=view_privs');

				xml.onreadystatechange = function()  {
					if (xml.readyState == 4 && xml.status == 200)  {
						var xmlParser = null;
						var popup = document.getElementById('loginPopup');

						if (!popup)
							return false;

						if (window.DOMParser)  {
							xmlParser = new DOMParser().parseFromString(xml.responseText, 'text/xml');
						} else {
							xmlParser = new ActiveXObject("Microsoft.XMLDOM");
							xmlParser.async = false;
							xmlParser.loadXML(xml.responseText);
						}   

						var xmlRoot = xmlParser.getElementsByTagName('forum')[0];

						if (!xmlRoot)  {
							popup.innerHTML = 'There was a fatal XML error';
							return null;
						}

						var forumID = xmlRoot.getAttribute('id');
						var forumName = xmlRoot.getAttribute('name');

						if (!forumID || !forumName)  {
							popup.innerHTML = 'There was a fatal XML error';
							return null;
						}

						var forumView = null, forumPost = null, viewGroup = null, postGroup = null;
						var groups = new Array();

						for (var i=0; i < xmlRoot.childNodes.length; i++)  {
							var child = xmlRoot.childNodes[i];

							if (child.tagName == 'priv')  {
								if (child.getAttribute('id') == 'view')  {
									forumView = child.getAttribute('value');
									viewGroup = child.getAttribute('name');
								} else if (child.getAttribute('id') == 'post') {
									forumPost = child.getAttribute('value');
									postGroup = child.getAttribute('name');
								}
							} else if (child.tagName == 'group')  {
								var id = parseInt(child.getAttribute('id'));
								groups[id] = child.getAttribute('name');
							}
						}

						if (!forumView || !forumPost)  {
							popup.innerHTML = 'There was a fatal XML error';
							return null;
						}

						var html =
							'<form action="editforum.php?fid=' + forumID + '&action=edit_privs" method="POST">' +
							'<table width="100%">' +
							'<tr><td style="text-align: left; border-bottom: 1px dotted #fff; font-size: 13px">Edit forum privileges</td>' +
							'<td style="text-align: right; border-bottom: 1px dotted #fff">' +
							'<a href="#" onClick="' +
							"document.body.removeChild(document.getElementById('loginPopup'))" +
							'" style="text-align: right; font-size: 10px">x close</a></td></tr></table><br>' +
							'<table width="100%">' +
							'<tr><td style="text-align: left">&gt; Who can view this forum: </td>' +
							'<td style="text-align: right">' +
							'<select name="view_privs">' +
							'<option value="' + forumView + '">&gt;= ' + viewGroup + ' *</option>';

						for (var i in groups)  {
							if (i != forumView)  {
								html +=
									'<option value="' + i + '">&gt;= ' + groups[i] + ' </option>';
							}
						}

						html +=
							'</select></td></tr>';

						html +=
							'<tr><td style="text-align: left">&gt; Who can post on this forum: </td>' +
							'<td style="text-align: right">' +
							'<select name="post_privs">' +
							'<option value="' + forumPost + '">&gt;= ' + postGroup + ' *</option>';

						for (var i in groups)  {
							if (i != forumPost)  {
								html +=
									'<option value="' + i + '">&gt;= ' + groups[i] + ' </option>';
							}
						}
						
						html += '</select></td></tr>' +
							'<tr><td></td><td style="height: 10px"></td></tr>' +
							'<tr><td></td><td style="text-align: right"><input type="submit" value="Change"></td></tr>' +
							'</table></form>';

						popup.innerHTML = html;
					}
				}

				xml.send(null);
			}

			forum.value = 'none';
			return true;
			break;
	}

	if (window.XMLHttpRequest)
		xml = new XMLHttpRequest();
	else if (window.ActiveXObject)
		xml = new ActiveXObject("Microsoft.XMLHTTP");
	else  {
		body.innerHTML = 'AJAX not supported on your browser, sorry';
		return null;
	}

	xml.open('GET', 'editforum.php?fid=' + forum.id + '&action=' + action);

	xml.onreadystatechange = function()  {
		if (xml.readyState == 4 && xml.status == 200)  {
			window.location.reload();
			
			if (document.getElementById(id))
				document.getElementById(id).value = 'none';
		}
	}

	xml.send(null);
}

function editForumProperty (forumID, prop, value)  {
	var property = null;
	var xml = null;

	if (!value)
		return null;

	switch (prop)  {
		case 'name':
		case 'desc':
			property = 'edit_' + prop;
			break;

		default:
			return false;
			break;
	}

	if (window.XMLHttpRequest)
		xml = new XMLHttpRequest();
	else if (window.ActiveXObject)
		xml = new ActiveXObject("Microsoft.XMLHTTP");
	else  {
		document.body.innerHTML = 'AJAX not supported on your browser, sorry';
		return null;
	}

	xml.open('GET', 'editforum.php?fid=' + forumID + '&action=' + property +
		'&value=' + escape(value));

	xml.onreadystatechange = function()  {
		if (xml.readyState == 4 && xml.status == 200)  {
			window.location.reload();
			
			if (document.getElementById(id))
				document.getElementById(id).value = 'none';
		}
	}

	xml.send(null);
}

function newForum()  {
	var div = document.getElementById('newforum');

	if (!div)
		return null;

	if (!div.innerHTML.match('input'))  {
		div.innerHTML =
			'<br><table border="0">' +
			'<tr><td>Forum name:</td>'+
			'<td><input type="text" name="forum_name"></td></tr>' +
			'<tr><td>Forum description:</td>' +
			'<td><input type="text" name="forum_desc"></td></tr>' +
			'<tr><td></td>' +
			'<td style="text-align: right">' +
			'<input type="submit" value="Create"></td></tr>' +
			'</table>';

		document.getElementsByName('forum_name')[0].focus();
	} else {
		div.innerHTML = '';
	}
}

function newGroup()  {
	var div = document.getElementById('newgroup');

	if (!div)
		return null;

	if (!div.innerHTML.match('input'))  {
		div.innerHTML =
			'<br><table border="0">' +
			'<tr><td>Group name:</td>' +
			'<td><input type="text" name="group_name"></td></tr>' +
			'<tr><td>Group ID:<br>' +
			'<a href="javascript:groupID()">What is this?</a>' +
			'</td>' +
			'<td><input type="text" name="gid"></td></tr>' +
			'<tr><td></td>' +
			'<td style="text-align: right">' +
			'<input type="submit" value="Create"></td></tr>' +
			'</table>';

		document.getElementsByName('group_name')[0].focus();
	} else {
		div.innerHTML = '';
	}
}

function groupID()  {
	if (!(document.getElementById('loginPopup')))  {
		var popup = document.createElement('div');
		var id = document.createAttribute('id');
		var scroll = getScrollXY();

		id.nodeValue = 'loginPopup';
		popup.setAttributeNode(id);
		popup.style.left = parseInt((window.innerWidth/2) + scroll[0] - 200) + 'px';
		popup.style.top  = parseInt((window.innerHeight/2) + scroll[1] - 220) + 'px';
		popup.style.width  = '430px';
		popup.style.height = '410px';
		popup.style.textAlign = 'justify';
		popup.style.opacity = '.90';

		popup.innerHTML =
			'<a href="javascript:groupID()" style="text-align: right"' +
			'>x close</a><br><br>' +
			'nullBB has a quite original way of managing groups, in a strongly hierachical way. ' +
			'On the top of the pyramid we have the "God" or "Founder" group, with ID == -1, then ' +
			'the "Admin" group (ID == 0), the "Global mod" group (ID == 1), the "Mod" group ' +
			'(ID == 2), the "User" group (ID == 10) for the common registered users, the "Any" ' +
			'group (ID == 20) to identify unregistered users, and the "Banned" group (ID == 30) ' +
			'for users banned and should not be able to access the forum anymore. These are the ' +
			'default groups and should not be removed. Anyway, I intentionally left some free slots ' +
			'between some groups. For example, you can define a new group whose privileges are ' +
			'intermediate between the ones of a moderator and a common user (for example, "V.I.P.", ' +
			'"Co-moderator" or "Developer") specifying an ID between 3 and 9 for this new group, ' +
			'and specifying afterwards a section of the forum onto which that group has special ' +
			'privileges. As the ID must be in [3,9], you can specify up to 7 groups with these ' +
			'properties. The same way, you can specify up to 9 groups with an ID in [11,19], i.e. ' +
			'with privileges intermediate between a common registered user and an unregistered ' +
			'user, and also 9 groups with an ID in [21,29] for users between "Any" and "Banned". ' +
			'This groups management system is very efficient for specifying groups privileges ' +
			'onto sections of your forum. You have several options in the "forums management" ' +
			'section for the privileges on a forum. Specifying ">= Mod" for read privileges, ' +
			'for example, a section can be read only by users in "Mod" group or in more privileged ' +
			'groups (Global mod, Admin, etc.).';
		document.body.appendChild(popup);
	} else {
		document.body.removeChild(document.getElementById('loginPopup'));
	}
}

function editGroup (group)  {
	var xml = null;
	var action = null;
	var id = group.id;

	switch (group.value)  {
		case 'delete':
		case 'edit_name':
			action = group.value;
			break;

		default:
			return null;
	}

	switch (group.value)  {
		case 'edit_name':
			var inner = document.getElementById('name' + group.id).innerHTML.replace(/<[^>]*>/g, '');

			document.getElementById('name' + group.id).innerHTML =
				'<input type="text" name="input' + group.id + '" ' +
				'value="' + inner + '">' +
				"<button onClick=\"editGroupProperty('" + group.id + "', 'name', " +
				"document.getElementsByName('input" + group.id + "')[0].value)\"" +
				">Change</button>";

			document.getElementsByName('input' + group.id)[0].focus();
			document.getElementsByName('input' + group.id)[0].select();
			
			if (document.getElementById(id))
				document.getElementById(id).value = 'none';

			return true;
			break;

		case 'delete':
			if (window.XMLHttpRequest)
				xml = new XMLHttpRequest();
			else if (window.ActiveXObject)
				xml = new ActiveXObject("Microsoft.XMLHTTP");
			else  {
				document.body.innerHTML = 'AJAX not supported on your browser, sorry';
				return null;
			}

			xml.open('GET', 'editgroup.php?gid=' + group.id + '&action=delete');

			xml.onreadystatechange = function()  {
				if (xml.readyState == 4 && xml.status == 200)  {
					window.location.reload();
			
					if (document.getElementById(id))
						document.getElementById(id).value = 'none';
				}
			}

			xml.send(null);
			break;
	}
}

function editGroupProperty (groupID, prop, value)  {
	var property = null;
	var xml = null;

	if (!value)
		return null;

	switch (prop)  {
		case 'name':
			property = 'edit_' + prop;
			break;

		default:
			return false;
			break;
	}

	if (window.XMLHttpRequest)
		xml = new XMLHttpRequest();
	else if (window.ActiveXObject)
		xml = new ActiveXObject("Microsoft.XMLHTTP");
	else  {
		document.body.innerHTML = 'AJAX not supported on your browser, sorry';
		return null;
	}

	xml.open('GET', 'editgroup.php?gid=' + groupID + '&action=' + property +
		'&value=' + escape(value));

	xml.onreadystatechange = function()  {
		if (xml.readyState == 4 && xml.status == 200)  {
			window.location.reload();
			
			if (document.getElementById(id))
				document.getElementById(id).value = 'none';
		}
	}

	xml.send(null);
}

