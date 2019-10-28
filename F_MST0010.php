<?php
	//****************************************************************************
	//プログラム名：担当者マスタ一覧照会
	//プログラムID：F_MST0010
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/05/31
	//履歴　　　　：
	//
	//
	//****************************************************************************
	/* 現在のキャッシュリミッタを取得または設定する */
	session_cache_limiter('private, must-revalidate');
	/* セッション開始 */
	session_start();

	//セッションチェック
	if(empty($_SESSION["login"][0])){
		//セッションが空の場合はエラー画面へ遷移
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");
		exit;
    	}

	$token = sha1(uniqid(mt_rand(), true));

	// トークンをセッションに追加する
	$_SESSION['token'][] = $token;

	//ファイル読み込み
	require_once("module_sel.php");
	require_once("module_upd.php");
	require_once("module_common.php");

	// オブジェクト作成
	$module_sel = new module_sel;
	$module_upd = new module_upd;
	$module_cmn = new module_common;


	//画面遷移先を取得
	if(isset($_GET['action'])) {
		$action = $_GET['action'];
	}
	//一覧の検索条件の保管変数
	if(isset($_GET['aJoken'])) {
		$aJoken = array();
		$aJoken = $_GET['aJoken'];
	}

	//引数の切替
	//一覧内での遷移
	if($action == "menu"){
		$sSyainCd = $module_cmn->fEscape($_POST['sSyainCd']);
		$sSyainNm = $module_cmn->fEscape($_POST['sSyainNm']);
		$sBumonCd = $module_cmn->fEscape($_POST['sBumonCd']);
		$sBumonNm = $module_cmn->fEscape($_POST['sBumonNm']);
		$sTorokuF = $module_cmn->fEscape($_POST['sTorokuF']);

	//メンテ画面からの遷移
	}elseif($action == "main"){
		$sSyainCd = $module_cmn->fEscape($aJoken[0]);
		$sSyainNm = $module_cmn->fEscape($aJoken[1]);
		$sBumonCd = $module_cmn->fEscape($aJoken[2]);
		$sBumonNm = $module_cmn->fEscape($aJoken[3]);
		$sTorokuF = $module_cmn->fEscape($aJoken[4]);
	}

	//検索条件格納用配列
	$aJoken = array();

	$aJoken[0] = $sSyainCd;
	$aJoken[1] = $sSyainNm;
	$aJoken[2] = $sBumonCd;
	$aJoken[3] = $sBumonNm;
	$aJoken[4] = $sTorokuF;

	$aPara = array();
	
	//検索処理開始

	if(isset($_GET['search'])){
		//検索条件取得
		if($_GET['search'] == "1"){

			//検索処理(件数)
			$aPara = $module_sel->fTantoSearch($aJoken);

			//最大件数オーバーの場合
			if($aPara[0][0] == "E016" ){
				$strErrMsg = $module_sel->fMsgSearch("E016","最大表示件数：1000件");
			}
			//該当件数がなければメッセージ表示
			elseif($aPara[0][0] == "N006" ){
				$strErrMsg = $module_sel->fMsgSearch("N006","");
			}

		}
	}

	//マニュアルパス取得
	$strManulPath = "";
	$strManulPath = $module_cmn->fMakeManualPath($_SERVER["PHP_SELF"]);

?>
<HTML>
<HEAD>
<META name="GENERATOR" content="IBM WebSphere Studio Homepage Builder Version 11.0.0.0 for Windows">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" >
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE></TITLE>
<link rel="stylesheet" type="text/css" href="css/common.css">
<script type="text/javascript" src="js/common.js"><!-- 共通javascript読み込み --></script>
<script type="text/javascript" >
<!-- 個別javascript記述 -->

	//「戻る」ボタン
	function fReturn(){
		location.href="main.php";
	}

	/* 担当者マスタメンテ画面表示 */
	function fTantoDisp(strMode,strTantoCd){
		var aJoken = new Array(4);
		//GETで渡す引数なのでURLエンコードを行う
		aJoken[0] = encodeURI(document.form.sSyainCd.value);
		aJoken[1] = encodeURI(document.form.sSyainNm.value);
		aJoken[2] = encodeURI(document.form.sBumonCd.value);
		aJoken[3] = encodeURI(document.form.sBumonNm.value);
		//aJoken[4] = encodeURI(document.form.sTorokuF.value);

		location.href="F_MST0011.php?mode=" + strMode + "&strTantoCd=" + strTantoCd + "&aJoken[0]=" + aJoken[0] + "&aJoken[1]=" + aJoken[1] + "&aJoken[2]=" + aJoken[2] + "&aJoken[3]=" + aJoken[3];

	}


</script>

</HEAD>
<BODY style="font-size : medium;border-collapse : separate;" onload=fLoadDisplay();>

<form name="form" method="post" action="F_MST0010.php?action=menu&search=1" onSubmit="">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000"><SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【担当者マスタ一覧照会】</SPAN></TD>
    </TR>
  </TBODY>
</TABLE>
<br>

<?php
//メッセージの有無を判断して表示
if ($strMsg <> ""){
?>
<TABLE border="0" bgcolor="#FFFFFF" >
  <TBODY>
    <TR >
      <!-- メッセージ区分で色分 -->

      <?php if(substr($strMsg,0,1) == "N"){ ?>
      <TD class="tdnone" align="center" width="1000" ><B><FONT color="#0000FF" size="2px"><?php echo $strMsg; ?></FONT></B></TD>
      <?php }elseif(substr($strMsg,0,1) == "E"){ ?>
      <TD class="tdnone" align="center" width="1000" ><B><FONT color="#FF0000" size="2px"><?php echo $strMsg; ?></FONT></B></TD>
	  <?php }elseif(substr($strMsg,0,1) == "W"){ ?>
	  <TD class="tdnone" align="center" width="1000" ><B><FONT color="#FFFF00" size="2px"><?php echo $strMsg; ?></FONT></B></TD>
	  <?php } ?>
    </TR>
  </TBODY>
</TABLE>
<?php
}
?>

<?php
//エラーメッセージの有無を判断して表示
if ($strErrMsg <> ""){
?>
<TABLE border="0" bgcolor="#FFFFFF" >
  <TBODY>
    <TR  >
       <!-- メッセージ区分で色分 -->
      <?php if(substr($strErrMsg,0,1) == "N"){ ?>
      <TD class="tdnone" align="center" width="1000" ><B><FONT color="#0000FF" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
      <?php }elseif(substr($strErrMsg,0,1) == "E"){ ?>
      <TD class="tdnone" align="center" width="1000" ><B><FONT color="#FF0000" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
	  <?php }elseif(substr($strErrMsg,0,1) == "W"){ ?>
	  <TD class="tdnone" align="center" width="1000" ><B><FONT color="#FFFF00" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
	  <?php } ?>
	</TR>
  </TBODY>
</TABLE>
<?php
}
?>

<TABLE border="0">
  <TBODY>
    <TR>
	<TD class="tdnone" width="800">
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>検索条件</B></FONT>
		</DIV>
    </TD>
    <TD class="tdnone" width="200" align="right">
    <INPUT type="button" name="btnSearch" value="　戻　る　" onClick="fReturn();">
     <?php echo $strManulPath;  ?>
     </TD>

    </TR>
  </TBODY>
</TABLE>
<TABLE class="tbline" width="1000" >

  <TBODY>
    <TR>
      <!--<TD class="tdnone2" width="98" ><B><A href="#" onclick="fOpenSearch('K_MSK0030','sSyainCd','sSyainNm','sBumonNm')">担当者ｺｰﾄﾞ</A></B></TD>-->
      <TD class="tdnone2" width="88" ><B>担当者ｺｰﾄﾞ</B></TD>
      <TD class="tdnone3" width="88" ><B><INPUT size="7" type="text" name="sSyainCd" maxlength=5 style="ime-mode: disabled;" value="<?php echo $sSyainCd; ?>" ></B></TD>
      <TD class="tdnone2" width="73" ><B>担当者名</B></TD>
      <TD class="tdnone3" width="80" ><INPUT size="10" type="text" name="sSyainNm" maxlength=15 value="<?php echo $sSyainNm; ?>"></TD>
      <TD class="tdnone2" width="105" >
      	<B>
      		<A href="#" onclick="fOpenSearch('F_MSK0050','sBumonCd','sBumonNm','')">部門所属ｺｰﾄﾞ</A>
      	</B>
      </TD>
      <TD class="tdnone3" width="98" ><INPUT size="14" type="text" name="sBumonCd" maxlength=10 style="ime-mode: disabled;" value="<?php echo $sBumonCd; ?>"></TD>
      <TD class="tdnone2" width="99" ><B>部門所属名</B></TD>
      <TD class="tdnone3" width="150" ><INPUT size="20" type="text" name="sBumonNm" maxlength=30 value="<?php echo $sBumonNm; ?>"></TD>

    </TR>
  </TBODY>
</TABLE>
<br>
<P><INPUT type="submit" name="btnSearch" value="　検　索　">　<INPUT type="reset" name="btnReset" value="　リセット　"></P>
</FORM>
<br>

<?php
//検索時にエラーがない場合は表示。
if ($strErrMsg == "" ){

	//検索結果があれば
	if(count($aPara) > 0 ){
?>

		<!-- ヘッダー部出力 -->
		<HR style="height:2px; border:none; background:linear-gradient(to right,#999999,transparent)">
		<br>
		<TABLE border='0'>
		<TBODY>
		<TR>
		<TD class='tdnone' width='800'>
			<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
				<FONT color="#ffffff"><B>結果一覧</B></FONT>
			</DIV>
		</TD>
		</TR>
		</TBODY>
		</TABLE>
		<P><FONT size='-1'><IMG src='./gif/yaji.gif' width='9' height='11' border='0'><IMG src='./gif/yaji.gif' width='9' height='11' border='0'>　検索結果は<?php echo count($aPara); ?>件です</FONT><BR>
		</P>
		<TABLE class='tbline' width='1000' >
		<TBODY>

<?php
			$i = 0;
	  		while ($i < count($aPara)){
				//奇数行、偶数行によって色変更
				if(($i % 2) == 0){
					$strClass = "tdnone3";
				}else{
					$strClass = "tdnone4";
				}
				//ヘッダーの挿入(20行毎)
				if($i%20 == 0){
					echo "<TR height='15'>";
			  		echo "<TD class='tdnone2' width='75'><B>担当者ｺｰﾄﾞ</B></TD>";
			 		echo "<TD class='tdnone2' width='133'><B>担当者名</B></TD>";
				    echo "<TD class='tdnone2' width='163'><B>会社名</B></TD>";
				    echo "<TD class='tdnone2' width='115'><B>部門所属コード</B></TD>";
				    echo "<TD class='tdnone2' width='354'><B>部門所属名</B></TD>";
				    echo "<TD class='tdnone2' width='60'><B>登録区分</B></TD>";
				    echo "<TD class='tdnone5' align='center' width='100' ><B>ｱｸｼｮﾝ</B></TD>";
					echo "</TR>";
				}

	    		echo "<TR height='15'>";

	     	 	echo "<TD class='".$strClass."'>".$aPara[$i][0]."</TD>";
	      		echo "<TD class='".$strClass."'>".$aPara[$i][1]."</TD>";
			    echo "<TD class='".$strClass."'>".$aPara[$i][2]."</TD>";
			    echo "<TD class='".$strClass."'>".$aPara[$i][3]."</TD>";
			    echo "<TD class='".$strClass."'>".$aPara[$i][4]."</TD>";
                            echo "<TD class='".$strClass."'>".$aPara[$i][5]."</TD>";
			    echo "<TD class='hpb-cnt-tb-cell4' align='center' >";
			    echo "<INPUT type='button' value='更新' style='background-color : #fdc257;' onClick='fTantoDisp(\"2\",\"".$aPara[$i][0]."\");'>";
			    echo "<INPUT type='button' value='削除' style='background-color : #fdc257;' onClick='fTantoDisp(\"3\",\"".$aPara[$i][0]."\");'>";
			    echo "<INPUT type='button' value='参照' style='background-color : #fdc257;' onClick='fTantoDisp(\"4\",\"".$aPara[$i][0]."\");'>";
			    echo "</TD>";
	    		echo "</TR>";

				$i = $i + 1;
			}

?>
			</TBODY>
			</TABLE>
			<P><BR>
			</P>
			<P><FONT size='-1'><IMG src='./gif/yaji.gif' width='9' height='11' border='0'><IMG src='./gif/yaji.gif' width='9' height='11' border='0'>　検索結果は<?php echo count($aPara); ?>件です</FONT></P>
<?php
		}
	}
?>


</BODY>
</HTML>
