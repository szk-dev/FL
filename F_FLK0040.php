<?php
	//****************************************************************************
	//プログラム名：不具合管理台帳出力トップ(フレーム分割)
	//プログラムID：F_FLK0040
	//作成者　　　：㈱鈴木　西村
	//作成日　　　：2012/08/20
	//履歴　　　　：
	//
	//
	//****************************************************************************
	
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
<title></title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" >
<link rel="stylesheet" type="text/css" href="./css/screen.css">
<link rel="stylesheet" type="text/css" href="./css/folders/tree.css">
<style>body {margin:12px;}</style>
<script type="text/javascript" src="./js/yahoo/yahoo-min.js" ></script>
<script type="text/javascript" src="./js/connection/connection-min.js" ></script>
<script type="text/javascript" src="./js/treeview/treeview-min.js" ></script>
<script type="text/javascript" src="./js/event/event-min.js" ></script>
<!-- カスタマイズした.js外部ファイル -->
<script type="text/javascript" src="./js/tato/tree/mktreebyarray2.js" ></script>

<LINK Type="text/css" Rel="stylesheet" Href="common.css">
</head>






<?php
	//セッションチェック
	if(empty($_SESSION["login"][0])){
		//セッションが空の場合はエラー画面へ遷移
		//header("location: http://papssv:81/PAPS/err.php");
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");
	}
?>

<FRAMESET ROWS="80%,*" frameborder="No" >
<FRAME SRC="F_FLK0041.php" NAME="F_FLK0041" Scrolling="no" noresize>
<FRAME SRC="" NAME="F_FLK0042" Scrolling="yes" noresize>
</FRAMESET>
<NOFRAMES>
<BODY>
<P>フレーム未対応ブラウザへのメッセージ</P>
</BODY>
</NOFRAMES>
</html>
