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
<title>品質管理システム</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" >
<LINK REL="SHORTCUT ICON" HREF="gif/favicon.ico">
<!--  <link rel="stylesheet" type="text/css" href="./css/screen.css"> -->
<link rel="stylesheet" type="text/css" href="./css/folders/tree.css">
<style>body {margin:12px;}</style>
<script type="text/javascript" src="./js/yahoo/yahoo-min.js" ></script>
<script type="text/javascript" src="./js/connection/connection-min.js" ></script>
<!-- <script type="text/javascript" src="./js/treeview/treeview-min.js" ></script> -->
<script type="text/javascript" src="./js/event/event-min.js" ></script>
<!-- カスタマイズした.js外部ファイル -->
<!-- <LINK Type="text/css" Rel="stylesheet" Href="common.css">  -->

<script type="text/javascript" >




</script>

</head>






<?php
	//セッションチェック
	if(empty($_SESSION["login"][0])){
		//セッションが空の場合はエラー画面へ遷移
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");

    }
?>

<FRAMESET COLS="17%,*">
<!--<FRAME SRC="menu.php" NAME="left" >-->
<FRAME SRC="menu.php" NAME="left" Scrolling="no" noresize>
<FRAMESET ROWS="7.5%,*" frameborder="No">
<FRAME SRC="head.php" NAME="head" Scrolling="no" noresize>
<FRAME SRC="main.php" NAME="main" noresize>
</FRAMESET>
<NOFRAMES>
<BODY >
<P>フレーム未対応ブラウザへのメッセージ</P>
</BODY>
</NOFRAMES>

</FRAMESET>

</html>



