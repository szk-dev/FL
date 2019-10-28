<?php
	//****************************************************************************
	//プログラム名：不具合管理台帳出力(検索部)
	//プログラムID：F_FLK0041
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/08/20
	//履歴　　　　：
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

 	//メッセージ用変数
	$strMsg = "";
	$strErrMsg = "";

    //画面遷移先を取得
	if(isset($_GET['action'])) {
		$action = $_GET['action'];
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

	//引数の取得
	if(isset($_GET['mode'])) {
		$mode = $_GET['mode'];
	}

	//引数の取得
	$sInsYmdF = $_POST['sInsYmdF'];
	$sInsYmdT = $_POST['sInsYmdT'];
	$sCustCd = $_POST['sCustCd'];
	$sTargetSec = $_POST['sTargetSec'];
	$sPgrsStage = $_POST['sPgrsStage'];
	$sFlawStep = $_POST['sFlawStep'];
	$sPcApAnsDateF = $_POST['sPcApAnsDateF'];
	$sPcApAnsDateT = $_POST['sPcApAnsDateT'];
	$sResultKbn = $_POST['sResultKbn'];
	$sValidKbn = $_POST['sValidKbn'];
	$sApAnsDateF = $_POST['sApAnsDateF'];
	$sApAnsDateT = $_POST['sApAnsDateT'];
	$sCustApAnsDateF = $_POST['sCustApAnsDateF'];
	$sCustApAnsDateT = $_POST['sCustApAnsDateT'];
	$chkKaito = $_POST['chkKaito'];
	//初期値の設定
	if($sInsYmdF == ""){
		$sInsYmdF = date("Y/m")."/01";
	}
	if($sInsYmdT == ""){
		$sInsYmdT = date("Y/m/t");
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



	//戻るボタン
	function fReturn(){
		document.form.target ="main";
		document.form.action ="main.php";
		document.form.submit();
	}



	//チェック処理
	function fCheck(){

		if(!fCalCheckFormat('sInsYmdF','受領日(開始)')){
			return false;
		}
		if(!fCalCheckFormat('sInsYmdT','受領日(終了)')){
			return false;
		}

		if(!fCalCheckFormat('sPcApAnsDateF','品証指定回答日(開始)')){
			return false;
		}
		if(!fCalCheckFormat('sPcApAnsDateT','品証指定回答日(終了)')){
			return false;
		}

		if(!fCalCheckFormat('sCustApAnsDateF','顧客指定回答日(開始)')){
			return false;
		}
		if(!fCalCheckFormat('sCustApAnsDateF','顧客指定回答日(終了)')){
			return false;
		}


		if(!fCalCheckFormat('sApAnsDateF','品証指定回答日(協工)')){
			return false;
		}
		if(!fCalCheckFormat('sApAnsDateT','品証指定回答日(協工)')){
			return false;
		}


		//日付整合性チェック
		if(!fCheckDateMatch('sInsYmdF','sInsYmdT','受領日(開始)','受領日(終了)')){
			return false;
		}
		if(!fCheckDateMatch('sPcApAnsDateF','sPcApAnsDateT','品証指定回答日(社内)(開始)','品証指定回答日(社内)(終了)')){
			return false;
		}
		if(!fCheckDateMatch('sCustApAnsDateF','sCustApAnsDateT','顧客指定回答日(開始)','顧客指定回答日(終了)')){
			return false;
		}
		if(!fCheckDateMatch('sApAnsDateF','sApAnsDateT','品証指定回答日(協工)(開始)','品証指定回答日(協工)(終了)')){
			return false;
		}

		//顧客指定回答日と指定回答日未入力のみが同時選択されていたらメッセージを表示する
		if(((document.form.sCustApAnsDateF.value != "") || (document.form.sCustApAnsDateT.value != "")) && (document.form.chkKaito.checked == true)){
			alert("顧客指定回答日と指定回答日未入力のみチェックは同時選択は出来ません");
			return false;
		}
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
	/* 結果出力 */
	function fncOut(){	
		
		var strUrl;
		
		//チェック処理
		if(fCheck()){
			//URL作成
			document.form.method ="POST";
			document.form.target ="F_FLK0042";
			document.form.submit();	
		}
	}	
	/* 指定回答日未入力のみをチェックした場合*/
	function fKaitoFlgCheck(obj){
		//顧客指定回答日が入っていたら日付を削除する
		if (obj.checked) {
			//削除しますかの確認
			
			document.form.sCustApAnsDateF.value = "";
			document.form.sCustApAnsDateF.readOnly = true;
			document.form.sCustApAnsDateT.value = "";
			document.form.sCustApAnsDateT.readOnly = true;
		} else {
			document.form.sCustApAnsDateF.readOnly = false;
			document.form.sCustApAnsDateT.readOnly = false;
		}
	}
	/* 顧客指定回答日が入力した場合*/
	function fKaitoYMDCheck(obj){
		//顧客指定回答日が入っていたら日付を削除する
		if ((document.form.sCustApAnsDateF.value != "") && (document.form.sCustApAnsDateT.value != "")) {
			//削除しますかの確認
			document.form.chkKaito.checked = false;
		} else {
		}
	}


</script>
</HEAD>
<BODY style="border-collapse : separate;" onload=fLoadDisplay();>
<form name="form" method="post" action="F_FLK0042.php" onSubmit="">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000">
      	<SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【不具合管理台帳出力】</SPAN>
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
	<TD class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>出力条件</B></FONT>
		</DIV>
	</TD>
	<TD class="tdnone" width="200" align="right"><INPUT type="button" name="btnSearch" value="　戻　る　" onClick="fReturn();">
	<?php echo $strManulPath;  ?></TD>

    </TR>
  </TBODY>
</TABLE>



<TABLE class="tbline" width="1000" >

  <TBODY>
    <TR>
		<TD class="tdnone1">受領日</TD>
		<TD class="tdnone3" colspan="3" >
			<INPUT id="sInsYmdF" name="sInsYmdF" size="6" type="text" style="ime-mode: disabled;" value="<?php echo $sInsYmdF; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sInsYmdF", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>～
		    <INPUT size="6" id="sInsYmdT" name="sInsYmdT" type="text" style="ime-mode: disabled;" value="<?php echo $sInsYmdT; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sInsYmdT", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
		</TD>
		<TD class="tdnone2" height="17">
			<A href="#" onclick="fOpenSearch('F_MSK0020','sCustCd','','','','','','','0')">顧客CD</A>
		</TD>
			<TD class="tdnone3" height="17" width="93">
			<INPUT name="sCustCd" size="5" type="text" maxlength="5" style="ime-mode: disabled;" value="<?php echo $sCustCd; ?>">
		</TD>

		<TD class="tdnone2" height="17">対象部門</TD>
		<TD class="tdnone3" height="17">
			<SELECT name="sTargetSec">
				<OPTION selected value="-1" >全て</OPTION>
				<?php $module_sel->fMakeCombo('C04',$sTargetSec); ?>
			</SELECT>
		</TD>
    </TR>
    <TR>
		<TD class="tdnone2" >進捗状態</TD>
		<TD class="tdnone3" >
			<SELECT name="sPgrsStage">
				<OPTION selected value="-1" >全て</OPTION>
				<?php $module_sel->fMakeCombo('C01',$sPgrsStage); ?>
			</SELECT>
		</TD>
		<TD class="tdnone2">不具合対策</TD>
		<TD class="tdnone3">
			<SELECT name="sFlawStep">
				<OPTION selected value="-1" >全て</OPTION>
				<?php $module_sel->fMakeCombo('C11',$sFlawStep); ?>
			</SELECT>
		</TD>
		<TD class="tdnone2" >品証指定回答日(社内)</TD>
		<TD class="tdnone3" >
			<INPUT id="sPcApAnsDateF" name="sPcApAnsDateF" size="6" type="text" style="ime-mode: disabled;" value="<?php echo $sPcApAnsDateF; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sPcApAnsDateF", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>～
		    <INPUT size="6" id="sPcApAnsDateT" name="sPcApAnsDateT" type="text" style="ime-mode: disabled;" value="<?php echo $sPcApAnsDateT; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sPcApAnsDateT", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
		</TD>
		<TD class="tdnone2" >品証指定回答日(協工)</TD>
		<TD class="tdnone3" >
			<INPUT id="sApAnsDateF" name="sApAnsDateF" size="6" type="text" style="ime-mode: disabled;" value="<?php echo $sApAnsDateF; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sApAnsDateF", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>～
		    <INPUT size="6" id="sApAnsDateT" name="sApAnsDateT" type="text" style="ime-mode: disabled;" value="<?php echo $sApAnsDateT; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sApAnsDateT", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
		</TD>

    </TR>
    <TR>
      	<TD class="tdnone2" >全ての対策有効性</TD>
    	<TD class="tdnone3">
      		<SELECT name="sValidKbn">
       		<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C11',$sValidKbn); ?>
      		</SELECT>
      	</TD>
		<TD class="tdnone2" >顧客指定回答日</TD>
		<TD class="tdnone3" colspan="3">
			<INPUT id="sCustApAnsDateF" name="sCustApAnsDateF" size="6" type="text" style="ime-mode: disabled;" value="<?php echo $sCustApAnsDateF; ?>" onChange="fKaitoYMDCheck(this);">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sCustApAnsDateF", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>～
		    <INPUT size="6" id="sCustApAnsDateT" name="sCustApAnsDateT" type="text" style="ime-mode: disabled;" value="<?php echo $sCustApAnsDateT; ?>" onChange="fKaitoYMDCheck(this);">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sCustApAnsDateT", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
			<input type="checkbox" name="chkKaito" value="1"
	      	<?php if ($chkKaito == 1) { ?> checked <?php }  ?>
			onClick="fKaitoFlgCheck(this);">指定回答日未入力のみ 
			
		</TD>
      	<TD class="tdnone2" >結果区分</TD>
    	<TD class="tdnone3">
      		<SELECT name="sResultKbn">
       		<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C09',$sResultKbn); ?>
      		</SELECT>
      	</TD>


    </TR>
  </TBODY>
</TABLE>
<br>
<P><INPUT type="button" name="btnSearch" value="　出　力　" onClick="fncOut()">　<INPUT type="reset" name="btnReset" value="　リセット　"></P>
</FORM>
</BODY>
</HTML>