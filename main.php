<?php

	//****************************************************************************
	//プログラム名：メイン画面
	//プログラムID：main
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/05/31
	//履歴　　　 　：
	//
	//
	//****************************************************************************

	/* 現在のキャッシュリミッタを取得または設定する */
	session_cache_limiter('private, must-revalidate');
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

	//異常品（赤伝・緑伝）処理状況の検索
	$aResT = array();
	$aResT = $module_sel->fTrblStatsSearch();
	$sLastM = date('Y年n月度', strtotime(date('Y-m-1') . '-1 month'));
	$sThisM = date('Y年n月度');
	$sTdayM = date("Y年n月j日");

	//不具合管理状況の検索
	//$aRes = array();
	//$aRes = $module_sel->fFlawStatsSearch();
	//$aRes = $module_sel->fFlawDepartStatsSearch();



?>
<html>
<head>
<title>FLメニュー</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" >

<link rel="stylesheet" type="text/css" href="css/common.css">
<!-- <LINK rel="stylesheet" type="text/css" href="table.css" id="_HPB_TABLE_CSS_ID_"> -->
<style>body {margin:12px;}</style>
<script type="text/javascript" >




/* 画面読み込み時処理  */
function fLoadDisplay(){
	//フレーム構成が不正なら初期画面へ遷移
	if(!top.head){
		location.href = "http://<?php echo $_SERVER["SERVER_NAME"]; ?>>/FL/";
	}
}
</script>

<!-- <LINK Type="text/css" Rel="stylesheet" Href="common.css"> -->
</head>

<body bgcolor="#FFFFFF" onload=fLoadDisplay();>




<?php
	if(empty($_SESSION["login"][0])){

		//セッションが空の場合はエラー画面へ遷移
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");
		exit;
    }
?>

<form  >

<div class="shadow23">
	<div class="text23">
		<p class="fontmsg">
			<IMG height='18' src='gif/maininfo.gif' border='0' >
			<font color="#ffffff" style="font-family: 'fantasy'">What's NEW 更新情報</font>
		</p>
	</div>
</div>

<TABLE border="0">
	<TBODY>
		<TR>
			<TD width="800" class="tdnone">
			<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>異常品（赤伝・緑伝）処理状況</B></FONT>
			</DIV>
			</TD>
		</TR>
	</TBODY>
</TABLE>

<TABLE class="tbline" width="860">
	<TBODY>
		<TR>
			<TD class="tdnone6" align="center" width="220" colspan="2" rowspan="2"><p class='fontmsg'><FONT color='white'>項目</p></TD>
			<TD class="tdnone6" align="center" width="200" colspan="2"><p class='fontmsg'><FONT color='white'>コネクタ部門</p></TD>
			<TD class="tdnone6" align="center" width="200" colspan="2"><p class='fontmsg'><FONT color='white'>めっき部門</p></TD>
			<TD class="tdnone6" align="center" width="200" colspan="2"><p class='fontmsg'><FONT color='white'>モールド部門</p></TD>
		</TR>
		<TR>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>数量</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>金額</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>数量</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>金額</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>数量</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>金額</p></TD>
		</TR>
		<TR>
			<TD class="tdnone9" align="center" width="50" rowspan="2">廃棄</TD>
			<TD class="tdnone9" align="left" width="166"><?php echo $sLastM;?>（前月）</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][0]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][1]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][2]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][3]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][4]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][5]);?>円</TD>
		</TR>
		<TR>
			<TD class="tdnone9" align="left"><?php echo $sThisM;?>（当月予定）</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][0]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][1]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][2]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][3]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][4]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][5]);?>円</TD>
		</TR>
		<TR>
			<TD class="tdnone9" align="center">保留</TD>
			<TD class="tdnone9" align="left"><?php echo $sTdayM;?>（現在まで）</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][0]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][1]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][2]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][3]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][4]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][5]);?>円</TD>
		</TR>
	</TBODY>
</TABLE>
<!--
<TABLE border="0">
	<TBODY>
		<TR>
			<TD width="800" class="tdnone">
			<DIV style="width:300;background-color:#000066;filter:alpha(opacity=300,style=1);padding:5px;">
			<FONT color="#ffffff"><B>　　不具合管理状況</B></FONT>
			</DIV>
			</TD>
		</TR>
	</TBODY>
</TABLE>

<TABLE class="tbline" width="400" >

  <TBODY>
    <TR>
      <TD class="tdnone9" align="center" width="100">登録件数</TD>
      <TD class="tdnone9" align="center" width="100">受付中</TD>
      <TD class="tdnone9" align="center" width="100">対策中</TD>
      <TD class="tdnone9" align="center" width="100">解決済</TD>
	</TR>
	<TR>
		<TD class="tdnone3" align="right" ><?php //echo number_format($aRes[0]);?></TD>
		<TD class="tdnone3" align="right" ><?php //echo number_format($aRes[1]);?></TD>
		<TD class="tdnone3" align="right" ><?php //echo number_format($aRes[2]);?></TD>
		<TD class="tdnone3" align="right" ><?php //echo number_format($aRes[4]);?></TD>
	</TR>

  </TBODY>
</TABLE>
-->
<br>



<?php

//お知らせ情報「表示
$contents = @file('info.txt');

echo "<TABLE class='tbline' width='860'>";

$i = 0;
foreach($contents as $line){
	$arrayInfo = explode(',',$line);
	//ヘッダー部出力
	if($i == 0 ){
		echo "<TR>";
		echo "<TD class='tdnone6' height='46' width='100'><p class='fontmsg'><FONT size='+1' color='white'>".$module_cmn->fChangUTF8($arrayInfo[0])."</FONT></p></TD>";
		echo "<TD class='tdnone6' height='46' width='600'><p class='fontmsg'><FONT size='+1' color='white'>".$module_cmn->fChangUTF8($arrayInfo[1])."</FONT></p></TD>";
		echo "<TD class='tdnone6' height='46' width='150'><p class='fontmsg'><FONT size='+1' color='white'>".$module_cmn->fChangUTF8($arrayInfo[2])."</FONT></p></TD>";
		echo "</TR>";
	}else{
		//明細部出力
		echo "<TR>";
		echo "<TD class='tdnone8' height='46' >".$module_cmn->fChangUTF8($arrayInfo[0])."</TD>";
		echo "<TD class='tdnone8' height='46' >".$module_cmn->fChangUTF8($arrayInfo[1]);

		if($i == 1){
			echo "<IMG src='./gif/new.gif'  height='18' border='0'>";
		}
		echo "</TD>";
		echo "<TD class='tdnone8' height='46' >".$module_cmn->fChangUTF8($arrayInfo[2])."</TD>";
		echo "</TR>";
	}
	//15件に達したら終了
	if($i == 15){
		break;
	}
	$i = $i + 1;

}

echo "</TABLE>";



//購入依頼で承認すべきものがあればお知らせする

//$aPara = array();
//表示区分0(未処理)
//$aJoken[7] = "0";
//購入依頼データ検索処理(件数)
//$aPara = $module_sel->fKonyuSearch($_SESSION['login'],$aJoken);

//検索件数が1件以上でN006(件数無し)が入ってこなければ表示
//if(count($aPara) > 0 && $aPara[0][0] <> "N006" ){
//	echo "<br>";
//	echo "<div style='padding:4px 5px;border-color:#990000;border-width:0 0 2px 0;border-style:solid;background:#FECACA;'>";
//	echo "<font color='red'>購入伺兼経費稟議発注依頼の未処理が".count($aPara)."件あります</font>⇒<a href='K_KOB0090.php'>購入伺兼経費稟議発注依頼一覧照会へ</a>";
//	echo "</div>";
//}


?>

</form>



</body>
</html>



