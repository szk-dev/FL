<?php
	//****************************************************************************
	//プログラム名：担当者マスタメンテナンス
	//プログラムID：F_MST0011
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/05/131
	//履歴　　　 　：
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

	//引数の取得(モード)
	if(isset($_GET['mode'])) {
		$mode = $_GET['mode'];
	}
	//引数の取得(担当者コード)
	if(isset($_GET['strTantoCd'])) {
		$strTantoCd = $_GET['strTantoCd'];
	}

	//一覧の検索条件の保管変数
	if(isset($_GET['aJoken'])) {
		$aJoken = $_GET['aJoken'];
		//条件について特殊文字を HTML エンティティに変換とURLエンコードを行う
		$aJoken[0] = urlencode($module_cmn->fEscape($aJoken[0]));
		$aJoken[1] = urlencode($module_cmn->fEscape($aJoken[1]));
		$aJoken[2] = urlencode($module_cmn->fEscape($aJoken[2]));
		$aJoken[3] = urlencode($module_cmn->fEscape($aJoken[3]));
		//$aJoken[4] = urlencode($module_cmn->fEscape($aJoken[4]));
	}

	//画面項目の取得
	//引数の取得
	$txtTantoCd = $_POST['txtTantoCd'];
	$txtTantoNm = $module_cmn->fEscape($_POST['txtTantoNm']);
	$txtTantoNmK = $module_cmn->fEscape($_POST['txtTantoNmK']);
	$txtKaishaNm = $module_cmn->fEscape($_POST['txtKaishaNm']);
	$txtBumonCd = $_POST['txtBumonCd'];
	$txtBumonNm = $module_cmn->fEscape($_POST['txtBumonNm']);

	$txtPassword = $_POST['txtPassword'];
	$hidUCount = $_POST['hidUCount'];
	//$hidPassword = $_POST['hidPassword'];
	//更新情報
	$txtInsYmd = $_POST['txtInsYmd'];
	$txtInsShainNm = $_POST['txtInsShainNm'];
	$txtUpdYmd = $_POST['txtUpdYmd'];
	$txtUpdShainNm = $_POST['txtUpdShainNm'];


	//メッセージ用変数
	$strMsg = "";
	$strErrMsg = "";

	//オブジェクトロック用変数
	$strLock = "";
	$strLock2 = "";

	//モード名用変数
	$modeN = "";

	//ボタン表示フラグ(表示:true,非表示:false)
	$bDispflg = true;

	//モードの取得
	if($mode == "1"){
		$modeN ="(登録)";
	}elseif($mode == "2"){
		$modeN ="(更新)";
	}elseif($mode == "3"){
		$modeN ="(削除)";
		$strLock = "readonly";
		$strLock2 = "disabled=true";
	}elseif($mode == "4"){
		$modeN ="(参照)";
		$strLock = "readonly";
		$strLock2 = "disabled=true";
	}else{
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");
		exit;
	}


	//登録モード以外はデータ取得を行う
	if($mode <> "1" && $strTantoCd <> ""){
		//再検索処理
		$aPara = $module_sel->fGetTantoData($strTantoCd);

		//取得データを画面項目にセット

		$txtTantoCd = $aPara[0];
		$txtTantoNm = $aPara[1];
		$txtTantoNmK = $aPara[2];
		$txtKaishaNm = $aPara[5];
		$txtBumonCd = $module_cmn->fChangeTimePro10($aPara[6]);
		$txtBumonNm = $aPara[7];
		$cmbMenu = $aPara[16];
		$cmbLevel = $aPara[3];
		//$txtPassword = $aPara[4];

		//更新情報
		$txtInsYmd = $aPara[8];
		$txtInsShainNm = $aPara[9];
		$txtUpdYmd = $aPara[11];
		$txtUpdShainNm = $aPara[12];
		$hidUCount = $aPara[14];
		//$hidPassword = $aPara[4];
	}




	//部門情報取得
	if(isset($_POST['hidBumonGet'])){
		$hidBumonGet = $_POST['hidBumonGet'];
		//部門所属コードが存在し、部門情報取得ボタンが押されたら
		if($txtBumonCd <> "" && $hidBumonGet == 1){
			$aPara = array();
			//部門情報取得
			$aPara = $module_sel->fGetBumonData($txtBumonCd,false);

			//部門が取得できたら変数にセットする
			if($aPara[2] <> ""){

				$txtBumonNm = $aPara[2];

			}else{
				//エラーメッセージ
				$strErrMsg = $module_sel->fMsgSearch("E015","部門所属ｺｰﾄﾞ:".$txtBumonCd);
			}

		}
	}

	//更新有無区分
	if(isset($_POST['hidUp'])){
		$hidUp = $_POST['hidUp'];

		//更新有無区分が１なら更新処理を行う
		if($hidUp == 1){

			//=============================================
			//リロード対策
			//=============================================
			// 送信されたトークンがセッションのトークン配列の中にあるか調べる
			$key = array_search($_POST['token'], $_SESSION['token']);
			if ($key !== false) {
			    // 正常な POST
			    unset($_SESSION['token'][$key]); // 使用済みトークンを破棄
			} else {
			    //リロードされた場合はエラー画面へ遷移
			    header("Location: err.php");
			}

			//存在チェックでエラーがなければ更新回数チェック(登録以外のみ)
			if($strErrMsg == ""  && $mode <> "1"){
				//更新回数チェック
				if(!$module_sel->fKoshinCheck($_POST['txtTantoCd'],$_POST['hidUCount'],"担当者マスタ","T_MS_SHAIN","C_SHAIN_CD")){
					$strErrMsg = $module_sel->fMsgSearch("E002","担当者コード:".$_POST['txtTantoCd']);
				}
			}

			//エラーメッセージがなければ更新処理を実行
			if($strErrMsg == ""){
				//パスワード変更フラグ(初期値はfalse)
				//$bChangePass = false;
				//更新処理の時にはパスワードが変更されたかチェックする
				//if($mode == "2"){
					//txtPassword(新パスワード)とhidPassword(旧パスワード)を比較
				//	if($_POST['txtPassword'] != $_POST['hidPassword']){
				//		$bChangePass = true;
				//	}
				//}

				//更新処理
				if($module_upd->fTantoExcute($mode,$_POST['txtTantoCd'],$_SESSION['login'],$_POST['hidUCount'],$txtInsYmd)){

					if($mode == "1"){
						$strMsg = $module_sel->fMsgSearch("N001","担当者コード:".$_POST['txtTantoCd']);	//登録しました
					}elseif($mode == "2"){
						$strMsg = $module_sel->fMsgSearch("N002","担当者コード:".$_POST['txtTantoCd']);	//更新しました
					}elseif($mode == "3"){
						$strMsg = $module_sel->fMsgSearch("N003","担当者コード:".$_POST['txtTantoCd']);	//削除しました
						//画面表示無し
						$bDispflg = false;
					}
					//再検索処理
					$aPara = $module_sel->fGetTantoData($_POST['txtTantoCd']);

					//取得データを画面項目にセット

					$txtTantoCd = $aPara[0];
					$txtTantoNm = $aPara[1];
					$txtTantoNmK = $aPara[2];
					$txtKaishaNm = $aPara[5];
					$txtBumonCd = $module_cmn->fChangeTimePro10($aPara[6]);
					$txtBumonNm = $aPara[7];
					$cmbMenu = $aPara[16];
					$cmbLevel = $aPara[3];
					//$txtPassword = $aPara[4];
					//更新情報
					$txtInsYmd = $aPara[8];
					$txtInsShainNm = $aPara[9];
					$txtUpdYmd = $aPara[11];
					$txtUpdShainNm = $aPara[12];
					$hidUCount = $aPara[14];
					//$hidPassword = $aPara[4];

				}else{
					$strMsg = $module_sel->fMsgSearch("E001","エラー内容:データベースエラー");
				}

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
<script type="text/javascript" src="js/common.js"><!-- 共通javascript読み込み --></script>
<script type="text/javascript" >
<!-- 個別javascript記述 -->

	//「メニューへ」ボタン
	function fReturn(){
		location.href="main.php";
	}

	/* 部門所属コードテキストボックスでエンターキーが押された時 */
	function fBumonGet(strMode){
		//エンターキーが押されたら
		if(window.event.keyCode==13) {
			document.form.hidBumonGet.value = 1;
			document.form.action ="F_MST0011.php?mode=" + strMode + "&aJoken[0]=<?php echo $aJoken[0];?>&aJoken[1]=<?php echo $aJoken[1];?>&aJoken[2]=<?php echo $aJoken[2];?>&aJoken[3]=<?php echo $aJoken[3];?>";

		 	document.form.submit();
		}
	}

	//検索、登録ボタン押下時
	function fExcute(strMode,strDialogMsg){

		//確認メッセージ
		if(window.confirm(strDialogMsg + 'してもよろしいですか？')){
			document.form.hidUp.value = 1;
			document.form.action ="F_MST0011.php?mode=" + strMode + "&aJoken[0]=<?php echo $aJoken[0];?>&aJoken[1]=<?php echo $aJoken[1];?>&aJoken[2]=<?php echo $aJoken[2];?>&aJoken[3]=<?php echo $aJoken[3];?>";
			document.form.submit();
		}else{
			return false;
		}


	}

	//戻るボタン
	function fReturn(strMode){
		//登録以外の場合は一覧に戻る
		if(strMode != 1){
			document.form.action ="F_MST0010.php?action=main&search=1&aJoken[0]=<?php echo $aJoken[0];?>&aJoken[1]=<?php echo $aJoken[1];?>&aJoken[2]=<?php echo $aJoken[2];?>&aJoken[3]=<?php echo $aJoken[3];?>&aJoken[4]=<?php echo $aJoken[4];?>";
			document.form.submit();
		}else{
			document.form.action ="main.php";
			document.form.submit();
		}

	}


</script>

</HEAD>
<BODY style="font-size : medium;border-collapse : separate;" onload=fLoadDisplay();>


<FORM name="form" method="post" onSubmit="return false;">
<input type="hidden" name="token" value="<?php echo $token; ?>">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000">
      <SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">
      【担当者マスタメンテナンス】<?php echo $modeN ?>
      </SPAN>
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

<TABLE border="0" >
  <TBODY>
    <TR>
      <TD class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>登録情報</B></FONT>
		</DIV>
      </TD>
      <TD class="tdnone" width="200" align="right">
      	<INPUT type="button" name="btnSearch" value="　戻　る　" onClick="fReturn(<?php echo $mode;?>)" >
      	<?php echo $strManulPath;  ?>
      </TD>
    </TR>
  </TBODY>
</TABLE>

<TABLE class="tbline" width="524">
  <TBODY>
    <TR>
      <TD class="tdnone2" width="126"><B>担当者コード</B></TD>
      <TD class="tdnone3" width="388">
      	<INPUT name="txtTantoCd" size="6" maxlength="5" type="text" class="textboxdisp" value="<?php echo $txtTantoCd; ?>" style="ime-mode: disabled;" readonly >
      </TD>
    </TR>
<TR>
      <TD class="tdnone2" ><B>担当者名</B></TD>
      <TD class="tdnone3"><INPUT name="txtTantoNm" maxlength="15" size="25" type="text" class="textboxdisp" value="<?php echo $txtTantoNm; ?>" style="ime-mode: disabled;" readonly></TD>
    </TR>
<TR>
      <TD class="tdnone2" ><B>担当者名カナ</B></TD>
      <TD class="tdnone3" ><INPUT name="txtTantoNmK" maxlength="15" size="25" type="text" class="textboxdisp" value="<?php echo $txtTantoNmK; ?>" style="ime-mode: disabled;" readonly></TD>
    </TR>
    <TR>
      <TD class="tdnone2"><B>会社名</B></TD>
      <TD class="tdnone3"><INPUT name="txtKaishaNm" class="textboxdisp" size="50" type="text" value="<?php echo $txtKaishaNm; ?>" style="ime-mode: disabled;" readonly ></TD>
    </TR>
    <TR>
      <TD class="tdnone2">
      	<B>部門所属コード</B>
      	<input type="hidden" name="hidBumonGet" value="0">
      </TD>
      <TD class="tdnone3"><INPUT name="txtBumonCd" maxlength="10" size="15" type="text" class="textboxdisp" value="<?php echo $txtBumonCd; ?>" style="ime-mode: disabled;" readonly>

      </TD>

    </TR>
    <TR>
      <TD class="tdnone2"><B>部門所属名</B></TD>
      <TD class="tdnone3"><INPUT name="txtBumonNm" class="textboxdisp" size="50" type="text" value="<?php echo $txtBumonNm; ?>" style="ime-mode: disabled;" readonly ></TD>
    </TR>

  </TBODY>
</TABLE>
<br>



<?php

//参照以外かつボタン表示フラグがTrueならは確定ボタン表示
if($mode <> "4" && $bDispflg){

	//品証のユーザのみ表示
	if(substr($_SESSION['login'][2],0,3) == "117"){

?>
	<INPUT type="button" name="btnExcute" value="　確　定　" onClick="fExcute('<?php echo($mode); ?>','<?php echo($modeN); ?>')">　<INPUT type="reset" name="btnExcute" value="　リセット　">
	<input type="hidden" name="hidUCount" value="<?php echo $hidUCount;?>">
	<input type="hidden" name="hidPassword" value="<?php echo $hidPassword;?>">
<?php
	}
}
?>

<input type="hidden" name="hidUp" value="0">
<?php
//登録モード以外に更新履歴を表示
if($mode <> "1"){
?>
<br>
<HR style="height:2px; border:none; background:linear-gradient(to right,#999999,transparent)">
<br>
<TABLE border="0">
  <TBODY>
    <TR>
      <TD class="tdnone">
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>更新履歴</B></FONT>
		</DIV>
      </TD>

    </TR>
  </TBODY>
</TABLE>
<TABLE class="tbline" width="420" >
  <TBODY>
    <TR>
      <TD class="tdnone2" width="100"><B>登録日時</B></TD>
      <TD class="tdnone3" width="120"><INPUT name="txtInsYmd" size="16" type="text" class="textboxdisp" value="<?php echo $txtInsYmd; ?>" readonly></TD>
      <TD class="tdnone2" width="100"><B>登録者</B></TD>
      <TD class="tdnone3" width="100"><INPUT name="txtInsShainNm" size="16" type="text" class="textboxdisp" value="<?php echo $txtInsShainNm; ?>" readonly></TD>
    </TR>
    <TR>
      <TD class="tdnone2"><B>更新日時</B></TD>
      <TD class="tdnone3"><INPUT name="txtUpdYmd" size="16" type="text" class="textboxdisp" value="<?php echo $txtUpdYmd; ?>" readonly></TD>
      <TD class="tdnone2"><B>更新者</B></TD>
      <TD class="tdnone3"><INPUT name="txtUpdShainNm" size="16" type="text" class="textboxdisp" value="<?php echo $txtUpdShainNm; ?>" readonly></TD>
    </TR>
  </TBODY>
</TABLE>
<?php
}
?>
</FORM>
</BODY>
</HTML>
