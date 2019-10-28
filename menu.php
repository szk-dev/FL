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
	//共通モジュール読み込み
	require_once("module_common.php");
	require_once("module_sel.php");

	$module_cmn = new module_common;
	$module_sel = new module_sel;

?>
<html>
<head>
<title>品質管理メニュー</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" >

<link rel="stylesheet" type="text/css" href="./css/common.css">

<link href="./js/TreeView/jquery.treeview.css" rel="stylesheet" type="text/css" />
<link href="./js/TreeView/screen.css" rel="stylesheet" type="text/css" />
<script src="./js/jquery-1.2.6.js" type="text/javascript"></script>
<!-- <script src="./js/jquery.cookie.js" type="text/javascript"></script> -->
<script src="./js/TreeView/jquery.treeview.js" type="text/javascript"></script>



<style type="text/css">
<!--
body {
	margin:12px;
	color: #191970;
	background-color: #AFEEEE;
}
-->
</style>


<!-- <LINK Type="text/css" Rel="stylesheet" Href="common.css"> -->
<script type="text/javascript" >


	/* ログアウト処理 */
	function fLogOut(){
		if (confirm("ログアウトしますか？")==true){
			top.location.href="index.php?out=1";
		}
	}

	/* ﾊﾟｽﾜｰﾄﾞ変更画面 */
	function fPassChange(){
		parent.main.location.href="F_MST0050.php";
	}


	/* ステータス表示 */
	function fStatus(strTitle){
		window.status　=　strTitle;
	}


	//時間表示
	function time() {

	        var now = new Date();
	        mon = now.getMonth()+1; day = now.getDate();
	        hou = now.getHours(); min = now.getMinutes(); sec = now.getSeconds();
	        year = now.getYear();
	        if (year < 2000) { year += 1900; }
	        if (mon <= "9"){mon = "0" + mon;};
	        if (day <= "9"){day = "0" + day;};
	        if (hou <= "9"){hou = "0" + hou;};
	        if (min <= "9"){min = "0" + min;};
	        if (sec <= "9"){sec = "0" + sec;};
	        window.status= year +"/"+ mon +"/"+ day +" "+ hou +":"+ min +":"+ sec;
	        setTimeout('time()',1000);
	}


	$(function()
	{
		$("#tree").treeview({collapsed: true, animated: "medium", control: "#sidetreecontrol", persist: "location" }); }
	)

</script>


</head>

<bodystyle="background-color: black; color: white;" onLoad="time();" link="#330000"; alink="#FF0000"; vlink="#330000"; >
 

<?php
	if(empty($_SESSION["login"][0])){

		//セッションが空の場合はエラー画面へ遷移
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");
		exit;
    }
?>

<form method="post" name="frmmenu" >
<center>
<img src="./gif/topk.png" id="PAPS1"  alt="PAPS1" border="0" width="190" /><br><br>
<input type="button" name="logout" value="ﾛｸﾞｱｳﾄ" onClick="fLogOut()">
<!-- <input type="button" name="passchg" value="ﾊﾟｽﾜｰﾄﾞ変更" onClick="fPassChange()"> -->
</center>
</form>

 <?php


//メニューマスタデータ保管用配列
$aPara = array();

//メニューデータ検索処理
$aPara = $module_sel->fMenuSearch($_SESSION['login']);
$i = 0;
$strBeforeKate = "";


?>

<div id="sidetreecontrol">
	<center>
		<a href="?#">全て閉じる</a> | <a href="?#">全て開く</a>
	</center>
</div>
<ul id="tree" class="filetree">
<?php
	while( $i < count($aPara)){

		//カテゴリが変わったらメニューヘッダ表示
		if($strBeforeKate <> $aPara[$i][4]){
			if($i > 0){
				echo "</ul>";
				echo "</li>";
			}
			echo "<li>";
			echo "<span class='folder'><strong>".$aPara[$i][1]."</strong></span>";
			echo "<ul>";
			$strBeforeKate = $aPara[$i][4];
		}



		echo "<li>";
		echo "<span class='file'><a href=".$aPara[$i][3]." target='main'>".$aPara[$i][2]."</a></span>";
		echo "</li>";
		//echo "</ul>";

		//カテゴリが変わったらメニューヘッダ表示
		if($strBeforeKate <> $aPara[$i][4]){
			//echo "</li>";
			//echo "</ul>";
		}
		$i = $i + 1;
	}
?>
</ul>
</body>
</html>



