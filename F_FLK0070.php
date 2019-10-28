<?php

	//****************************************************************************
	//プログラム名：環境・紛争鉱物情報一覧照会
	//プログラムID：F_FLK0070
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2017/09/04
	//履歴　　　　：2019/04/01 「顧客名」、「内容」を部分一致で検索条件へ追加 藤田
	//
	//****************************************************************************

	/* 現在のキャッシュリミッタを取得または設定する */
	session_cache_limiter('private, must-revalidate');
	/* セッション開始 */
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

 	//メッセージ用変数
	$strMsg = "";
	$strErrMsg = "";

    //画面遷移先を取得
	if(isset($_GET['action'])) {
		$action = $_GET['action'];
	}

	//一覧の検索条件の保管変数
	if(isset($_GET['aJoken'])) {
		$aJoken = array();
		$aJoken = $_GET['aJoken'];
	}

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

	//品証以外の社員は制限設ける
	if(substr($_SESSION['login'][2],0,3) <> '117'){
		$strLock = "";
	}

	//引数の取得
	if(isset($_GET['mode'])) {
		$mode = $_GET['mode'];
	}

	//引数の切替
	//一覧内での遷移
	if($action == "menu"){
	//画面項目の取得
	//引数の取得
		$sPgrsStage = $_POST['sPgrsStage'];
		$sRrceNo = $_POST['sRrceNo'];
		$sCustCd = $module_cmn->fEscape($_POST['sCustCd']);
		$sSurveyKbn = $module_cmn->fEscape($_POST['sSurveyKbn']);
//2019/04/01 AD START
		$sCustNm = $module_cmn->fEscape($_POST['sCustNm']);
		$sContents = $module_cmn->fEscape($_POST['sContents']);
//2019/04/01 AD END

	//環境紛争鉱物情報入力画面からの遷移
	}elseif($action == "main"){

		$sPgrsStage = $module_cmn->fEscape($aJoken[0]);
		$sRrceNo = $module_cmn->fEscape($aJoken[1]);
		$sCustCd = $module_cmn->fEscape($aJoken[2]);
		$sEnvKbn = $module_cmn->fEscape($aJoken[3]);
//2019/04/01 AD START
		$sCustNm = $module_cmn->fEscape($aJoken[4]);
		$sContents = $module_cmn->fEscape($aJoken[5]);
//2019/04/01 AD END
	}


	//検索条件格納用配列
	$aJoken = array();

	$aJoken[0] = $sPgrsStage;
	$aJoken[1] = $sRrceNo;
	$aJoken[2] = $sCustCd;
	$aJoken[3] = $sSurveyKbn;
//2019/04/01 AD START
	$aJoken[4] = $sCustNm;
	$aJoken[5] = $sContents;
//2019/04/01 AD END

	$aPara = Array();
	
	//検索処理(件数取得)
	if(isset($_GET['search'])){
		//検索条件取得
		if($_GET['search'] == "1"){


			$aPara = array();
			//環境紛争鉱物情報データ検索処理
			$aPara = $module_sel->fEnvSearch($_SESSION['login'],$aJoken,$module_sel->fWorkCalender());

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
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE></TITLE>
<link rel="stylesheet" type="text/css" href="css/common.css">

<script type="text/javascript" src="js/prototype.js"></script>
<link rel="stylesheet" href="js/protocalendar/stylesheets/paper.css" type="text/css" media="all">
<!-- <link rel="stylesheet" href="js/protocalendar/stylesheets/main.css" type="text/css" media="all"> -->

<script src="js/protocalendar/lib/effects.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script src="js/protocalendar/javascripts/protocalendar.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script src="js/protocalendar/javascripts/lang_ja.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>

<script type="text/javascript" src="js/common.js"><!-- 共通javascript読み込み --></script>
<script type="text/javascript" >
<!-- 個別javascript記述 -->


	/* 環境紛争鉱物情報入力画面表示 */
	function fEnvDisp(strMode,strRrceNo){

		var strUrl;
		var aJoken = new Array(3);
		//GETで渡す引数なのでURLエンコードを行う
		aJoken[0] = encodeURI(document.form.sPgrsStage.value);
		aJoken[1] = encodeURI(document.form.sRrceNo.value);
		aJoken[2] = encodeURI(document.form.sCustCd.value);
		aJoken[3] = encodeURI(document.form.sSurveyKbn.value);
//2019/04/01 AD START
		aJoken[4] = encodeURI(document.form.sCustNm.value);
		aJoken[5] = encodeURI(document.form.sContents.value);
//2019/04/01 AD END
		strUrl = "F_FLK0060";

		//URLを作成してジャンプ
		location.href = strUrl + ".php?mode=" + strMode + "&strRrceNo=" + strRrceNo
		+ "&aJoken[0]=" + aJoken[0] + "&aJoken[1]=" + aJoken[1] + "&aJoken[2]=" + aJoken[2]
//2019/04/01 AD START
//		+ "&aJoken[3]=" + aJoken[3];
		+ "&aJoken[3]=" + aJoken[3] + "&aJoken[4]=" + aJoken[4] + "&aJoken[5]=" + aJoken[5];
//2019/04/01 AD END
	}


	//戻るボタン
	function fReturn(){
		document.form.target ="main";
		document.form.action ="main.php";
		document.form.submit();
	}

	//検索ボタン
	function fSearch(){
		if(fCheck()){
			document.form.target ="main";
			document.form.action ="F_FLK0070.php?action=menu&search=1";
			document.form.submit();
		}
	}


	//チェック処理
	function fCheck(){

		return true;
	}

	/* 日付桁数チェック  */
	function fCalCheckFormat(strObj,strTit){
		var strYYYYMMDD;

		//入力されていたらチェック
		if(document.form.elements[strObj].value != ""){
			//入力日付が8桁もしくは10桁ならば日付チェック
			if(document.form.elements[strObj].value.length == 8 || document.form.elements[strObj].value.length == 10){
				//スラッシュを一旦取り除く
				strYYYYMMDD = document.form.elements[strObj].value.replace(/\//g,"");

				//日付の妥当性チェック
				if(!fChkURU(strYYYYMMDD,strObj,strTit)){
					return false;
				}
			}else{
				alert("日付はYYYYMMDD形式かYYYY/MM/DD形式で入力して下さい");
				document.form.elements[strObj].focus();
				return false;
			}
		}
		return true;
	}

	//日付の妥当性チェック
	function fChkURU(number,strObj,strTit) {
	    yy = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31, 29);

	    wYear  = number.substr(0, 4);
	    wMonth = wMChk = number.substr(4, 2);
	    wDay   = number.substr(6, 2);

	    // 年の範囲検証
	    if (!(wYear >= 2000 && wYear <= 2100)) {
	        alert("年の指定が正しくありません[" + strTit + "]");
	        document.form.elements[strObj].focus();
	        return false;
	    }

	    // 月の範囲検証
	    if (!(wMonth >= 1 && wMonth <= 12)) {
	        alert("月の指定が正しくありません[" + strTit + "]");
	        document.form.elements[strObj].focus();
	        return false;
	    }

	    // 閏年の判定
	    if (!(wYear % 4) && wMonth == 2) {
	        wMChk = 12;     // 閏年テーブル

	        //if (!(!(wYear % 100) && (wYear % 400))) {
	        if (!(wYear % 100)) {
	            if (wYear % 400) {
	                wMChk = 1;      // non閏年テーブル
	            }
	        }
	    } else {
	        wMChk--;
	    }

	    // 日の範囲検証
	    if (!(1 <= wDay && yy[wMChk] >= wDay)) {
	        alert("日付の指定が間違ってます[" + strTit + "]");
	        document.form.elements[strObj].focus();
	        return false;
	    }

	    return true;
	}



</script>
</HEAD>
<BODY style="border-collapse : separate;" onload=fLoadDisplay();>
<form name="form" method="post" action="" onSubmit="">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000">
      	<SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【環境・紛争鉱物情報一覧照会】</SPAN>
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

<?php
//エラーメッセージの有無を判断して表示
if ($strErrMsg <> ""){
?>
<TABLE border="0" bgcolor="#FFFFFF" >
  <TBODY>
    <TR  >
      <TD class="tdnone" align="center" width="1000"  ><B><FONT color="#ff0000" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
    </TR>
  </TBODY>
</TABLE>
<?php
}
?>

<TABLE border="0">
  <TBODY>
    <TR>
		<TD width="800" class="tdnone">
			<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
				<FONT color="#ffffff"><B>検索条件</B></FONT>
			</DIV>
      	</TD>
    </TR>
  </TBODY>
</TABLE>

<TABLE class="tbline" width="1000" >

  <TBODY>
    <TR>
      <TD class="tdnone2"  width="90">進捗状態</TD>
      <TD class="tdnone3"  width="83">
      	<SELECT name="sPgrsStage">
       	<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C29',$sPgrsStage); ?>
      	</SELECT>
      </TD>
      <TD class="tdnone2"  width="71">整理NO</TD>
      <TD class="tdnone3"  width="130">
      	<INPUT name="sRrceNo" size="11" maxlength="10" type="text" style="ime-mode: disabled;" value="<?php echo $sRrceNo; ?>">
      </TD>
	  <TD class="tdnone2"  width="71">
		<A href="JavaScript:fOpenSearch('F_MSK0020','sCustCd','','','','','','','0')" onclick="">顧客CD</A>
	  </TD>
      <TD class="tdnone3"  width="100">
      	<INPUT name="sCustCd" size="7" maxlength="5" type="text" style="ime-mode: disabled;" value="<?php echo $sCustCd; ?>">
      </TD>
      <TD class="tdnone2"  width="70">調査区分</TD>
      <TD class="tdnone3"  width="163">
      	<SELECT name="sSurveyKbn">
       	<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C31',$sSurveyKbn); ?>
      	</SELECT>
      </TD>
    </TR>
<!-- 2019/03/05 AD START T.FUJITA -->
    <TR>
      <TD class="tdnone2">顧客名</TD>
      <TD class="tdnone3" colspan="3">
      	<INPUT type="text" name="sCustNm" id="sCustNm" size="49" maxlength="40" value="<?php echo $sCustNm; ?>">
      </TD>
      <TD class="tdnone2">内容</TD>
      <TD class="tdnone3" colspan="3">
      	<INPUT type="text" name="sContents" id="sContents" size="67" maxlength="40" value="<?php echo $sContents; ?>">
      </TD>
    </TR>
<!-- 2019/03/05 AD END T.FUJITA -->
  </TBODY>
</TABLE>
<br>
<P><INPUT type="button" name="btnSearch" value="　検　索　" onClick="fSearch()">　<INPUT type="reset" name="btnReset" value="　リセット　"></P>
<br>
<?php
//検索時にエラーがない場合は表示。
if ($strErrMsg == "" ){

	//検索結果があれば
	if(count($aPara) > 0 ){
?>

	<HR style="height:2px; border:none; background:linear-gradient(to right,#999999,transparent)">
	<br>
	<TABLE border='0'>
	<TBODY>
	<TR>
	<TD class='tdnone' width='800' >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>結果一覧</B></FONT>
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

	<TABLE class="tbline" width="1250" >
	  <TBODY>
<?php
		$i = 0;
		//ヘッダー行追加判断用変数
		$iPageCnt = 0;
		//件数分ループ
		while($i < count($aPara)){
			//奇数行、偶数行によって色変更
			if(($i % 2) == 0){
				$strClass = "tdnone3";
				$strClassProgress = "tdnone3";
				$strClassCustApAns = "tdnone3";
				$strClassApAns1 =  "tdnone3";
				$strClassApAns2 =  "tdnone3";

			}else{

				$strClass = "tdnone4";
				$strClassProgress = "tdnone4";
				$strClassCustApAns = "tdnone4";
				$strClassApAns1 =  "tdnone4";
				$strClassApAns2 =  "tdnone4";

			}

			//進捗状態が調査済の場合はグレー
			if($aPara[$i][50] == "gray"){
				$strClassProgress = "tdnone12";
			}

			//顧客指定回答日期限切れもしくは間近の場合
			if($aPara[$i][51] == "limit"){
				$strClassCustApAns = "tdnone10";
			}elseif($aPara[$i][51] == "near"){
				$strClassCustApAns = "tdnone11";
			}

			//ヘッダーの挿入(20行毎)
			if($iPageCnt%20 == 0){

				echo "<TR height='15'>";
				echo "<TD class='tdnone5' align='center' width='100' nowrap><B>アクション</B></TD>";
				echo "<TD class='tdnone2' align='center' width='70' nowrap>整理NO</TD>";
				echo "<TD class='tdnone2' align='center' width='150' nowrap>進捗状態</TD>";
				echo "<TD class='tdnone2' align='center' width='150' nowrap>顧客名</TD>";
				echo "<TD class='tdnone2' align='center' width='290' nowrap>内容</TD>";
				echo "<TD class='tdnone2' align='center' width='290' nowrap>対象製品</TD>";
				echo "<TD class='tdnone2' align='center' width='80' >顧客指定<br>回答日</TD>";
			    echo "<TD class='tdnone2' align='center' width='100' nowrap>登録者</TD>";
				echo "</TR>	";
			}

			echo "<TR height='15'>";
			echo "<TD class='".$strClass."' align='center' >";
			echo "<INPUT type='button' value='更新' style='background-color : #fdc257;' onClick='fEnvDisp(\"2\",\"".$aPara[$i][0]."\");'>";
			echo "<INPUT type='button' value='流用' style='background-color : #fdc257;' onClick='fEnvDisp(\"5\",\"".$aPara[$i][0]."\");'>";
			echo "<INPUT type='button' value='削除' style='background-color : #fdc257;' onClick='fEnvDisp(\"3\",\"".$aPara[$i][0]."\");'>";
	    	echo "</TD>";
		    echo "<TD class='".$strClassProgress."'>".$aPara[$i][0]."</TD>";
     	 	echo "<TD class='".$strClass."'>".$aPara[$i][1]."</TD>";
     	 	echo "<TD class='".$strClass."'>".$aPara[$i][2]."</TD>";
			echo "<TD class='".$strClass."'>".$aPara[$i][3]."</TD>";
			echo "<TD class='".$strClass."'>".$aPara[$i][4]."</TD>";
		    echo "<TD class='".$strClassCustApAns."'>".$module_cmn->fChangDateFormat($aPara[$i][5])."</TD>";
			echo "<TD class='".$strClass."'>".$aPara[$i][6]."</TD>";

		    echo "</TR>";
			$iPageCnt = $iPageCnt + 1;

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
</FORM>
</BODY>
</HTML>