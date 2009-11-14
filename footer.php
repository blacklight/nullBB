<div class="clearfooter"></div>
</div>

<div class="footer"><br>

<?php

if ($userinfo['user_group'] <= USERLEV_ADMIN && $session->logged)  {
	print '<a href="'.BASEDIR.'admin/index.php">'.$_LANG['admin_panel'].'</a><br><br>';
}

?>

powered by <a href="http://0x00.ath.cx">nullBB</a><br>
copyright (C) 2009 by <b>BlackLight</b>
</div>

</body></html>

