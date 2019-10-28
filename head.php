<?php

	/* セッション開始 */
	session_start();
	//社員・部門情報取得
	if(isset($_SESSION['login'][0]) == true  ){
		$snm = $_SESSION['login'][1];
	}else{
		$snm = "";
	}
	if(isset($_SESSION['login'][2]) == true  ){
		$bnm = $_SESSION['login'][3];
	}else{
		$bnm = "";
	}

?>
<html>
<head>
<title>FLメニュー</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" >
<link rel="stylesheet" type="text/css" href="css/common.css">
<!-- <link rel="stylesheet" type="text/css" href="./css/screen.css"> -->
<link rel="stylesheet" type="text/css" href="./css/folders/tree.css">
<style>body {margin:12px;}</style>
<script type="text/javascript" src="./js/yahoo/yahoo-min.js" ></script>
<script type="text/javascript" src="./js/connection/connection-min.js" ></script>
<!-- <script type="text/javascript" src="./js/treeview/treeview-min.js" ></script> -->
<script type="text/javascript" src="./js/event/event-min.js" ></script>


<!-- カスタマイズした.js外部ファイル -->
<script type="text/javascript" src="./js/tato/tree/mktreebyarray2.js" ></script>
<!-- <LINK Type="text/css" Rel="stylesheet" Href="common.css"> -->
</head>
<body bgcolor="#FFFFFF">
<?php
	if(empty($_SESSION["login"][0])){

		//セッションが空の場合はエラー画面へ遷移
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");
		exit;
    }
?>
<TABLE border="0"  >
  <TBODY>
    <TR height="15">
      <TD class="tdnone" align="left" width="900" nowrap >
      	<FONT color="#000000" size="3px">
      			ログイン者　：　<INPUT size="15" type="text" class="textboxdisp" name="snm" readonly STYLE="FONT-SIZE:12pt" value="<?php echo $snm; ?>">
      			ログイン部門　：　<INPUT size="60" type="text" class="textboxdisp" name="bnm" readonly STYLE="FONT-SIZE:12pt" value="<?php echo $bnm; ?>">
      			<INPUT type="hidden" name="hidTantoCd" value="<?php echo $_SESSION['login'][0]; ?>">
      	</FONT>
      </TD>
      <TD class="tdnone" align="right" width="100" nowrap>
      	<IMG src="./gif/fl.png" height="30" border="0" alt="FL ">
      </TD>
    </TR>
  </TBODY>
</TABLE>
</body>
</html>
