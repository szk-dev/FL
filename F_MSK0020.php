<?php

	//=====================================================
	//
	//取引先マスタ検索子画面
	//
	//=====================================================
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

	//共通モジュール読み込み
	require_once("module_sel.php");
	require_once("module_upd.php");
	require_once("module_common.php");

	// オブジェクト作成
	$module_sel = new module_sel;
	$module_upd = new module_upd;
	$module_cmn = new module_common;

	$aPara = array();

	//セッション取得
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


	$kaishacd = $_POST['hidKaishaCd'];


	//引数の取得
	if(isset($_GET['para1'])) {
		$para1 = $_GET['para1'];
	}
	if(isset($_GET['para2'])) {
		$para2 = $_GET['para2'];
	}
	if(isset($_GET['para3'])) {
		$para3 = $_GET['para3'];
	}
	if(isset($_GET['para5'])) {
		$para5 = $_GET['para5'];
	}
	if(isset($_GET['para4'])) {
		$para4 = $_GET['para4'];
	}
	if(isset($_GET['para8'])) {
		$para8 = $_GET['para8'];
	}

	if(isset($_GET['search'])){
		//検索実行判断
		if($_GET['search'] == "1"){
			$sCustCd = $_POST['sCustCd'];
			$sCustNm = $_POST['sCustNm'];
			$sCustNmK = $_POST['sCustNmK'];

			$aPara = array();

			//取引先マスタ検索処理
			$aPara = $module_sel->fGetCustDataList($sCustCd,$sCustNm,$sCustNmK,$para8);
			//最大件数オーバーの場合
			if($aPara[0][0] == "E016" ){
				$strMsg = $module_sel->fMsgSearch("E016","最大表示件数：1000件");
			}
			//該当件数がなければメッセージ表示
			elseif($aPara[0][0] == "N006" ){
				$strMsg = $module_sel->fMsgSearch("N006","");
			}
		}
	}

?>
<HTML>
<HEAD>
<META name="GENERATOR" content="IBM WebSphere Studio Homepage Builder Version 11.0.0.0 for Windows">
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<base target="_self">
<TITLE>取引先マスタ検索</TITLE>
<link rel="stylesheet" type="text/css" href="css/common.css">
<!-- <LINK rel="stylesheet" type="text/css" href="./css/table.css" id="_HPB_TABLE_CSS_ID_"> -->
<script type="text/javascript" >


    /* 閉じる処理処理 */
	function fWindowClose(){
		var w=window.open("","_top")
		w.opener=window
		w.close()
	}


	<?php if($para5 <> ""){  ?>
	/* 取引先情報検索子画面からの値戻し(配列用) */
	function fCustSetArray(aPara1,aPara2,aPara3){



  		<?php if($para1 <> ""){  ?>
  			window.dialogArguments.document.getElementsByName("<?php echo $para1; ?>")[<?php echo $para5; ?>].value = aPara1;
		<?php }  ?>
		<?php if($para2 <> ""){  ?>
			window.dialogArguments.document.getElementsByName("<?php echo $para2; ?>")[<?php echo $para5; ?>].value = aPara2;
		<?php }  ?>
		<?php if($para3 <> ""){  ?>
			window.dialogArguments.document.getElementsByName("<?php echo $para3; ?>")[<?php echo $para5; ?>].value = aPara3;
		<?php }  ?>


  		window.close();
	}
	<?php }else{  ?>

	/* 取引先情報検索子画面からの値戻し(通常用) */
	function fCustSet(aPara1,aPara2,aPara3){
  		<?php if($para1 <> ""){  ?>
  			window.dialogArguments.form.<?php echo $para1; ?>.value = aPara1;
  		<?php }  ?>
  		<?php if($para2 <> ""){  ?>
  			window.dialogArguments.form.<?php echo $para2; ?>.value = aPara2;
  		<?php }  ?>
  		<?php if($para3 <> ""){  ?>
  			window.dialogArguments.form.<?php echo $para3; ?>.value = aPara3;
  		<?php }  ?>

  		window.close();
	}
	<?php }  ?>

	//検索ボタン
	function fSearch(){
		//document.form.target ="_self";
		document.form.action ="F_MSK0020.php?search=1&para1=<?php echo $para1; ?>&para2=<?php echo $para2; ?>&para3=<?php echo $para3; ?>&para4=<?php echo $para4; ?>&para5=<?php echo $para5; ?>&para8=<?php echo $para8; ?>";
		document.form.btnSearch.disabled = true;
		document.form.submit();
	}

</script>

</HEAD>
<BODY style="font-size : medium;border-collapse : separate;" onLoad="">

<form name="form" method="post" action="" >
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD class="tdnone" align="center" width="600">
      	<SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">取引先マスタ検索</SPAN>
      </TD>
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

<TABLE border="0">
  <TBODY>
    <TR>
	<TD class="tdnone" width="400" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>検索条件</B></FONT>
		</DIV>
    </TD>
    <TD class="tdnone" width="200" align="right"><INPUT type="button" name="btnClose" value="　閉じる　" onClick="fWindowClose()"></TD>
    </TR>
  </TBODY>
</TABLE>
<TABLE class="tbline" width="500" >

  <TBODY>
    <TR>
      <TD class="tdnone2" bgcolor="#99ccff" width="100" height="16" nowrap><B>取引先ｺｰﾄﾞ</B></TD>
      <TD class="tdnone2" bgcolor="#99ccff" width="200" nowrap><B>取引先名</B></TD>
      <TD class="tdnone2" bgcolor="#99ccff" width="100" nowrap><B>取引先名カナ</B></TD>
    </TR>
    <TR>
      <TD class="tdnone3" height="16"><INPUT size="7" type="text" name="sCustCd" maxlength=8 style="ime-mode: disabled;" value="<?php echo $_POST['sCustCd']; ?>" ></TD>
      <TD class="tdnone3" height="16"><INPUT size="30" type="text" name="sCustNm" maxlength=30 value="<?php echo $module_cmn->fEscape($_POST['sCustNm']); ?>"></TD>
	  <TD class="tdnone3" height="16"><INPUT size="30" type="text" name="sCustNmK" maxlength=30 value="<?php echo $module_cmn->fEscape($_POST['sCustNmK']); ?>"></TD>
    </TR>
  </TBODY>
</TABLE>
<br>
<P>
<INPUT type="button" name="btnSearch" value="　検　索　" onClick="fSearch()">
<input type="hidden" name="hidKaishaCd" id="hidKaishaCd" value="<?php echo $kaishacd; ?> ">
</P>
</FORM>

<?php
//検索時にエラーがない場合は表示。
if ($strMsg == "" ){

	//検索結果があれば
	if(count($aPara) > 0 ){
?>
	<HR style="height:2px; border:none; background:linear-gradient(to right,#999999,transparent)">
	<br>
	<TABLE border="0">
	  <TBODY>
	    <TR>
	      <TD class="tdnone" >
			<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
				<FONT color="#ffffff"><B>検索結果</B></FONT>
			</DIV>
	      </TD>
	    </TR>
	  </TBODY>
	</TABLE>
	<P>
		<FONT size='-1'>
		<IMG src='./gif/yaji.gif' width='9' height='11' border='0'>
		<IMG src='./gif/yaji.gif' width='9' height='11' border='0'>
		　検索結果は<?php echo count($aPara); ?>件です</FONT>
		<BR>
	</P>

	<TABLE class="tbline" width="560" >
	  <TBODY>
		<?php
		$i = 0;
		//件数分ループ
		while($i < count($aPara)){
			//奇数行、偶数行によって色変更
			if(($i % 2) == 0){
				$strClass = "tdnone3";
				$strClass2 = "textboxdisp";
			}else{
				$strClass = "tdnone4";
				$strClass2 = "textboxdisp2";
			}

			//ヘッダーの挿入(20行毎)
			if($i%20 == 0){
		?>

			<TR class="tdnone3">
		    	<TD  class='tdnone2'  width='70' nowrap><B>取引先ｺｰﾄﾞ</B></TD>
	 			<TD  class='tdnone2'  width='230' nowrap><B>取引先名</B></TD>
		    	<TD  class='tdnone2'  width='120' nowrap><B>取引先名カナ</B></TD>

		    </TR>
		<?php
			}
		?>

			<TR class="<?php echo $strClass; ?>">
		    <TD class="<?php echo $strClass; ?>">
		    <?php if($para5 == ""){ //通常用 ?>
		    	<a href='#' onclick="fCustSet('<?php echo $aPara[$i][0]; ?>','<?php echo $aPara[$i][1]; ?>','<?php echo $aPara[$i][2]; ?>')"><?php echo $aPara[$i][0]; ?></a>
		    <?php }else{ //配列用 ?>
		  		<a href='#' onclick="fCustSetArray('<?php echo $aPara[$i][0]; ?>','<?php echo $aPara[$i][1]; ?>','<?php echo $aPara[$i][2]; ?>')"><?php echo $aPara[$i][0]; ?></a>
		    <?php } ?>
		    </TD>
		    <TD class="<?php echo $strClass; ?>"><?php echo $aPara[$i][1]; ?></TD>
		    <TD class="<?php echo $strClass; ?>"><?php echo $aPara[$i][2]; ?></TD>

		    </TR>
		<?php
			$i = $i + 1;
		}
		?>

	</TBODY>
	</TABLE>
	<P>
		<FONT size='-1'>
		<IMG src='./gif/yaji.gif' width='9' height='11' border='0'>
		<IMG src='./gif/yaji.gif' width='9' height='11' border='0'>
		　検索結果は<?php echo count($aPara); ?>件です</FONT>
		<BR>
	</P>
<?php
	}
}
?>

</HTML>
