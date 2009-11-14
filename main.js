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

var x = 0;
var y = 0;

function disableContextMenu()  {
	return false;
}

function captureClick(e, basedir)  {
	var menu = null;
	var xml  = null;
	
	if (!e)
		e = window.event;

	x = e.clientX;
	y = e.clientY;

	if (e.button > 1)  {
		toggleMenu(basedir);
	} else {
		if ((menu = document.getElementById('menu')))  {
			var scroll = getScrollXY();

			var menuX = menu.style.left;
			menuX = parseInt(menuX.replace("px", "") - scroll[0]);

			var menuY = menu.style.top;
			menuY = parseInt(menuY.replace("px", "") - scroll[1]);

			var menuW = menu.style.width;
			menuW = parseInt(menuW.replace("px", ""));
			
			var menuH = menu.style.height;
			menuH = parseInt(menuH.replace("px", ""));

			if (x > menuX + menuW || x < menuX || y > menuY + menuH || y < menuY)  {
				document.body.removeChild(menu);
			}
		}
	}
}

function toggleMenu(basedir)  {
	var menu = null;
	var xml  = null;

	if (!(menu = document.getElementById('menu')))  {
		menu = document.createElement('div');

		var id = document.createAttribute('id');
		id.nodeValue = 'menu';
		menu.setAttributeNode(id);

		menu.style.border = '1px solid #fff';
		menu.innerHTML  = '';
		menu.style.fontSize = '10px';

		var scroll = getScrollXY();
		menu.style.top    = parseInt(y + scroll[1] + 3) + 'px';
		menu.style.left   = parseInt(x + scroll[0] + 3) + 'px';
		menu.style.width  = '200px';

		if (window.XMLHttpRequest)
			xml = new XMLHttpRequest();
		else if (window.ActiveXObject)
			xml = new ActiveXObject("Microsoft.XMLHTTP");
		else
			return null;

		menu.innerHTML = 'Loading...';

		xml.open('GET', basedir + 'menu.php');
		xml.onreadystatechange = function()  {
			if (xml.readyState == 4 && xml.status == 200)  {
				menu.innerHTML = xml.responseText;
				menu.style.height = parseInt(xml.responseText.match(/<li>/gi).length * 10) + 'px';
			}
		}

		xml.send(null);
		document.body.appendChild(menu);
	} else {
		document.body.removeChild(menu);
	}
}

function popLogin(basedir)  {
	var popup = null;
	var menu = null;

	if ((menu = document.getElementById('menu')))
		document.body.removeChild(menu);

	if (!(popup = document.getElementById('loginPopup')))  {
		popup = document.createElement('div');

		var id = document.createAttribute('id');
		var scroll = getScrollXY();

		id.nodeValue = 'loginPopup';
		popup.setAttributeNode(id);
		popup.style.left = parseInt((window.innerWidth/2) + scroll[0] - 150) + 'px';
		popup.style.top  = parseInt((window.innerHeight/2) + scroll[1] - 150) + 'px';

		popup.innerHTML =
			'<form action="' + basedir + 'login.php" method="POST">' +
			'<table width="100%">' +
			'<tr><td style="text-align: left; border-bottom: 1px dotted #fff; font-size: 13px">Login</td>' +
			'<td style="text-align: right; border-bottom: 1px dotted #fff">' +
			'<a href="javascript:popLogin()" style="text-align: right; font-size: 10px">x close</a></td></tr></table><br>' +
			'<table width="100%">' +
			'<tr><td style="text-align: left">&gt; username</td>' +
			'<td style="text-align: right"><input type="text" name="username" class="login"></td></tr>' +
			'<tr><td style="text-align: left">&gt; password</td>' +
			'<td style="text-align: right"><input type="password" name="password" class="login"></td></tr></table><br>' +
			'<center><input type="submit" value="Login" class="login"></center></form>';

		document.body.appendChild(popup);
		document.getElementsByName('username')[0].focus();
	} else {
		document.body.removeChild(popup);
	}
}

function editMessage(basedir, post_id)  {
	var popup = null;
	var xml = null;

	if (!(popup = document.getElementById('editPopup')))  {
		popup = document.createElement('div');

		var scroll = getScrollXY();
		var id = document.createAttribute('id');

		id.nodeValue = 'editPopup';
		popup.setAttributeNode(id);
		popup.style.left = parseInt((window.innerWidth/2) + scroll[0] - 250) + 'px';
		popup.style.top  = parseInt((window.innerHeight/2) + scroll[1] - 250) + 'px';

		if (window.XMLHttpRequest)
			xml = new XMLHttpRequest();
		else if (window.ActiveXObject)
			xml = new ActiveXObject("Microsoft.XMLHTTP");
		else
			return null;

		popup.innerHTML = 'Loading...';

		xml.open('GET', basedir + 'postbyid.php?post_id=' + post_id);
		xml.onreadystatechange = function()  {
			if (xml.readyState == 4 && xml.status == 200)  {
				popup.innerHTML =
					'<form action="' + basedir + 'updatePost.php" method="POST">' +
					'<table width="100%">' +
					'<tr><td style="text-align: left; border-bottom: 1px dotted #fff; font-size: 13px">Edit post</td>' +
					'<td style="text-align: right; border-bottom: 1px dotted #fff">' +
					'<a href="javascript:editMessage(' + "'" + basedir + "'" +
					',' + post_id + ')" style="text-align: right; font-size: 10px">x close</a></td></tr></table><br>' +
					'<input type="hidden" name="post_id" value="' + post_id + '">' +
					'<textarea class="topicContent" name="post_content">' + xml.responseText + '</textarea><br><br>' +
					'<input type="submit" value="Edit"></form>';
			}
		}

		xml.send(null);
		popup.innerHTML = 'Loading...';

		document.body.appendChild(popup);
	} else
		document.body.removeChild(popup);
}

function newTopic (basedir, forum_id)  {
	var popup = null;
	var menu = null;

	if ((menu = document.getElementById('menu')))
		document.body.removeChild(menu);

	if (!(popup = document.getElementById('topicPopup')))  {
		popup = document.createElement('div');

		var id = document.createAttribute('id');
		id.nodeValue = 'topicPopup';

		var scroll = getScrollXY();
		popup.setAttributeNode(id);
		popup.style.left = parseInt((window.innerWidth/2) + scroll[0] - 270) + 'px';
		popup.style.top  = parseInt((window.innerHeight/2) + scroll[1] - 270) + 'px';

		popup.innerHTML =
			'<form action="' + basedir + 'insertTopic.php" method="POST">' +
			'<input type="hidden" name="forum_id" value="' + forum_id + '">' +
			'<table width="100%">' +
			'<tr><td style="text-align: left; border-bottom: 1px dotted #fff; font-size: 13px">New topic</td>' +
			'<td style="text-align: right; border-bottom: 1px dotted #fff">' +
			'<a href="javascript:newTopic(' + forum_id + ')" style="text-align: right; font-size: 10px">x close</a></td></tr></table><br>' +
			'<table width="100%">' +
			'<tr><td style="width: 30%">&gt; Topic title</td>' +
			'<td style="width: 80%"><input type="text" name="topic_title" class="newtopic"></td></tr>' +
			'<tr><td style="width: 30%">&gt; Content<br>[<a href="javascript:bbcodePopup()">BBcode</a>: enabled]</td>' +
			'<td style="width: 80%"><textarea name="content" class="topicContent"></textarea></td></tr></table>' +
			'<center><input type="submit" value="Post" class="newtopic"></center></form>';

		document.body.appendChild(popup);
		document.getElementsByName('topic_title')[0].focus();
	} else {
		document.body.removeChild(popup);
	}
}

function getScrollXY() {
	  var scrOfX = 0, scrOfY = 0;
	  
	  if( typeof( window.pageYOffset ) == 'number' ) {
		  scrOfY = window.pageYOffset;
		  scrOfX = window.pageXOffset;
	  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		  scrOfY = document.body.scrollTop;
		  scrOfX = document.body.scrollLeft;
	  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		  scrOfY = document.documentElement.scrollTop;
		  scrOfX = document.documentElement.scrollLeft;
	  }
	  
	  return [ scrOfX, scrOfY ];
}

function bbcodePopup()  {
	var bbcode = null;

	if ( !(bbcode = document.getElementById('bbcode')) )  {
		bbcode = document.createElement('div');

		var id = document.createAttribute('id');
		id.nodeValue = 'bbcode';
		bbcode.setAttributeNode(id);

		var scroll = getScrollXY();
		bbcode.style.left = parseInt(x + scroll[0] + 5) + 'px';
		bbcode.style.top  = parseInt(y + scroll[1] + 5) + 'px';
		bbcode.innerHTML =
			'<table width="100%">' +
			'<tr><td style="text-align: left; border-bottom: 1px dotted #fff; font-size: 13px">Supported tags:</td>' +
			'<td style="text-align: right; border-bottom: 1px dotted #fff">' +
			'<a href="javascript:bbcodePopup()" style="text-align: right; font-size: 10px">x close</a></td></tr></table><br>' +
			'[list], [img], [b], [i], [u], [color], [size], [url], ' +
			'[mail], [code], [quote], [tex]';

		document.body.appendChild(bbcode);
	} else
		document.body.removeChild(bbcode);
}

function sanitizeCodeTags()  {
	var code = null;

	if ( (code = document.getElementsByName('code')) )  {
		for (var i=0; i < document.getElementsByName('code').length; i++)  {
			document.getElementsByName('code')[i].innerHTML =
				document.getElementsByName('code')[i].innerHTML.replace(/<br>/ig, '');
			document.getElementsByName('code')[i].innerHTML =
				document.getElementsByName('code')[i].innerHTML.replace(/<([^>]*)>/g, '[$1]');
			
			document.getElementsByName('code')[i].innerHTML =
				'<b>CODE:</b><br><pre>' +
				document.getElementsByName('code')[i].innerHTML + '</pre>';
		}
	}
}

function refreshSignature(maxLen)  {
	var numBytes = null;

	if (!(numBytes = document.getElementById('numBytes')))
		return null;

	if (!(textarea = document.getElementById('signatureTextArea')))
		return null;

	numBytes.innerHTML = '<br>used characters:<br>' + ((textarea.value.length > maxLen) ?
		'<font color="#bb0000">' : '' ) + textarea.value.length + ((textarea.value.length > maxLen) ?
		'</font>' : '' ) + '/' + maxLen;
}

function quotePost (basedir, post_id, author)  {
	var xml = null;
	document.getElementById('postTextArea').focus();

	if (window.XMLHttpRequest)
		xml = new XMLHttpRequest();
	else if (window.ActiveXObject)
		xml = new ActiveXObject("Microsoft.XMLHTTP");
	else
		return null;

	xml.open('GET', basedir + 'postbyid.php?post_id=' + post_id);
	
	xml.onreadystatechange = function()  {
		if (xml.readyState == 4 && xml.status == 200)  {
			var response = xml.responseText.replace(/\s+$/, '');

			document.getElementById('postTextArea').innerHTML += '[quote="' + author + '"]' +
				response + '[/quote]' + "\n\n";
		}
	}

	xml.send(null);
}

function sendMessage (user_id, basedir)  {
	var popup = null;

	if (document.getElementById('viewMsgPopup'))
		document.body.removeChild(document.getElementById('viewMsgPopup'));

	if ( !(popup = document.getElementById('privmsgPopup')) )  {
		popup = document.createElement('div');

		var id = document.createAttribute('id');
		id.nodeValue = 'privmsgPopup';
		popup.setAttributeNode(id);

		var scroll = getScrollXY();
		popup.style.left = parseInt((window.innerWidth/2) + scroll[0] - 300) + 'px';
		popup.style.top  = parseInt((window.innerHeight/2) + scroll[1] - 260) + 'px';
		popup.innerHTML =
			'<form action="' + basedir + 'privmsg.php" method="POST">' +
			'<input type="hidden" name="recv_id" value="' + user_id + '">' +
			'<table width="100%">' +
			'<tr><td style="text-align: left; border-bottom: 1px dotted #fff; font-size: 13px">Private message</td>' +
			'<td style="text-align: right; border-bottom: 1px dotted #fff">' +
			'<a href="javascript:sendMessage(' + user_id + ",'" + basedir + "'" + ')" style="text-align: right; font-size: 10px">x close</a></td></tr></table><br>' +
			'<table width="100%">' +
			'<tr><td style="width: 30%">&gt; Messages subject</td>' +
			'<td style="text-align: right"><input type="text" name="subject" class="newtopic"></td></tr>' +
			'<tr><td style="width: 30%">&gt; Message content</td>' +
			'<td><textarea name="content" class="topicContent"></textarea></td></tr></table>' +
			'<center><input type="submit" value="Post" class="newtopic" name="postMsg"></center></form>';

		document.body.appendChild(popup);
		document.getElementsByName('subject')[0].focus();
	} else {
		document.body.removeChild(popup);
	}
}

function popupPrivmsg (msg_id, basedir, from_id)  {
	var popup = null;
	var xml = null;

	if (!(popup = document.getElementById('viewMsgPopup')))  {
		popup = document.createElement('div');

		var scroll = getScrollXY();
		var id = document.createAttribute('id');

		id.nodeValue = 'viewMsgPopup';
		popup.setAttributeNode(id);
		popup.style.left = parseInt((window.innerWidth/2) + scroll[0] - 250) + 'px';
		popup.style.top  = parseInt((window.innerHeight/2) + scroll[1] - 250) + 'px';

		if (window.XMLHttpRequest)
			xml = new XMLHttpRequest();
		else if (window.ActiveXObject)
			xml = new ActiveXObject("Microsoft.XMLHTTP");
		else
			return null;

		popup.innerHTML = 'Loading...';

		xml.open('GET', 'privmsgbyid.php?msg_id=' + msg_id);
		xml.onreadystatechange = function()  {
			if (xml.readyState == 4 && xml.status == 200)  {
				popup.innerHTML =
					'<table width="100%">' +
					'<tr><td style="text-align: left; border-bottom: 1px dotted #fff; font-size: 13px">Private message</td>' +
					'<td style="text-align: right; border-bottom: 1px dotted #fff">' +
					'<a href="javascript:popupPrivmsg()" style="text-align: right; font-size: 10px">x close</a>' +
					'</td></tr></table><br>' +
					xml.responseText +
					'</table>' +
					'<button onClick="sendMessage(' + from_id + ", '" + basedir + "'" + ')">Reply</button>';
			}
		}

		xml.send(null);
		document.body.appendChild(popup);
	} else {
		document.body.removeChild(popup);
	}
}

function ipAddrPopup(basedir, post_id)  {
	var popup = null;
	var xml = null;

	if ( !(popup = document.getElementById('ipPopup')) )  {
		popup = document.createElement('div');

		var id = document.createAttribute('id');
		id.nodeValue = 'ipPopup';
		popup.setAttributeNode(id);

		var scroll = getScrollXY();
		popup.style.left = parseInt(x + scroll[0] + 5) + 'px';
		popup.style.top  = parseInt(y + scroll[1] + 5) + 'px';

		if (window.XMLHttpRequest)
			xml = new XMLHttpRequest();
		else if (window.ActiveXObject)
			xml = new ActiveXObject("Microsoft.XMLHTTP");
		else
			return null;

		popup.innerHTML = 'Loading...';

		xml.open('GET', basedir + 'postbyid.php?addr=1&post_id=' + post_id);
		xml.onreadystatechange = function()  {
			if (xml.readyState == 4 && xml.status == 200)  {
				popup.innerHTML = xml.responseText;
			}
		}

		xml.send(null);
		document.body.appendChild(popup);
	} else
		document.body.removeChild(popup);
}

function modActions (basedir, topic_id, action)  {
	if (action != 'none')  {
		var baseurl = document.location.href.replace (/([^\/]+\/\/)?([^\/]+)\/.*/, '$1$2');

		if (action == 'lock')
			document.location.href = baseurl + basedir + 'updateTopic.php?lock=1&topic_id=' + topic_id;
		else if (action == 'unlock')
			document.location.href = baseurl + basedir + 'updateTopic.php?unlock=1&topic_id=' + topic_id;
		else if (action == 'remove')
			document.location.href = baseurl + basedir + 'updateTopic.php?delete=1&topic_id=' + topic_id;
		else if (action == 'stick')
			document.location.href = baseurl + basedir + 'updateTopic.php?stick=1&topic_id=' + topic_id;
		else if (action == 'unstick')
			document.location.href = baseurl + basedir + 'updateTopic.php?unstick=1&topic_id=' + topic_id;
	}
}

var aStr = new Array();
var divChild = new Array();
var divMain = undefined;
var selected = -1;

function purgeChildren()  {
	if (divChild.length > 0)  {
		for (var i=0; i < divChild.length; i++)
			divMain.removeChild(divChild[i]);
		divChild = new Array();
	}
}

function removeAutoComplete()  {
	purgeChildren(divChild);
	divMain.style.innerHTML = '';
	divMain.style.visibility = 'hidden';
	divMain.style.border = '0px';
}

function fillText (val, txt)  {
	val = val.replace(/<[^>]+>/g, '');
	txt.value = val;
	selected = -1;
	removeAutoComplete();
}

function toggleSelected (k)  {
	var ended = false;
					
	if (typeof(k) == 'object')  {
		for (var i=0; i < divChild.length; i++)
			divChild[i].className = 'unselected';
		k.className = 'selected';
		return true;
	}
					
	switch (k)  {
		case 40:
			if (selected == -1)
				selected = 0;
			else  {
				divChild[selected].className = 'unselected';
				selected = (selected+1) % divChild.length;
			}

			break;

		case 38:
			if (selected == -1)
				selected = 0;
			else  {
				divChild[selected].className = 'unselected';
				selected = (selected > 0) ? (selected-1) % divChild.length : divChild.length-1;
			}

			break;
	}

	divChild[selected].className = 'selected';
}

function AutoComplete (txt, divName, uri, event)  {
	var xml = null;
	var matches = 0;
	var k;

	if (divChild == undefined)
		divChild = new Array();

	if (!(divMain = document.getElementById(divName)))
		return false;

	txt.onblur = function()  {
		removeAutoComplete();

	};
	
	txt.ontextblur = function()  {
		removeAutoComplete();
	};

	if (window.XMLHttpRequest)
		xml = new XMLHttpRequest();
	else if (window.ActiveXObject)
		xml = new ActiveXObject("Microsoft.XMLHTTP");
	else
		return false;

	xml.open("GET", uri);

	xml.onreadystatechange = function()  {
		if (xml.readyState == 4 && xml.status == 200)  {
			aStr = xml.responseText.split("\n");
		}
	}

	xml.send(null);
	if (event.which == undefined)
		k = event.keyCode;
	else
		k = event.which;

	if (aStr.length > 0)  {
		divMain.style.visibility = 'visible';
		divMain.style.border = '1px solid #000';
	}

	if (k == 13 && selected != -1)  {
		var val = divChild[selected].innerHTML;
		fillText(val, txt);
	}

	if (k >= 33 && k <= 40)  {
		toggleSelected(k);
	} else {
		purgeChildren();

		for (var i=0; i < aStr.length; i++)  {
			var reg = new RegExp('(' + txt.value + ')', 'i');

			if (reg.exec(aStr[i]) && txt.value.length > 0)  {
				divChild[matches] = document.createElement('div');
				id = document.createAttribute('id');

				id.nodeValue = 'complete' + (matches);
				divChild[matches].setAttributeNode(id);
				divChild[matches].className = 'unselected';
				divChild[matches].innerHTML = aStr[i] + '<br>';
				divChild[matches].innerHTML = divChild[matches].innerHTML.replace(
						txt.value, '<b>' + txt.value + '</b>');

				divMain.appendChild(divChild[matches]);

				divChild[matches].onmouseover = function(e)  {
					toggleSelected(e.target);
				};

				divChild[matches].onmousedown = function(e)  {
					fillText(e.target.innerHTML, txt);
				};

				matches++;
			}
		}

		if (matches == 0 || divChild.length == 0)  {
			removeAutoComplete();
		}

		selected = -1;
	}
}

