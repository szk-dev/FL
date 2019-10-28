<?php

	//****************************************************************************
	//プログラム名：主要得意先マスタメンテナンス
	//プログラムID：F_MST0030
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2013/03/25
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

	$aPara = array();
	$aPara2 = array();

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

	//メッセージ用変数
	$strMsg = "";
	$strErrMsg = "";


	$aToriCd = Array();

	//画面項目取得(配列)
	//$aToriCd = $_POST["txtToriCd"];
	$aToriCd[0] = $_POST["txtToriCd"];
	$aToriNm = $_POST["txtToriNm"];
	//$aBumon = $_POST["cmbBumon"];
	//$aKamoku = $_POST["cmbKamoku"];
	//$aShihaKng = $_POST["txtShihaKng"];
	//$aShihaYmd = $_POST["txtShihaYmd"];
	$aBiko = $_POST["txtBiko"];

	$hidUCount = $_POST["hidUCount"];

	//画面件数
	$intCnt = 0;
	$hidCount = count($aToriCd);

	$i = 0;

	$aPara = array();

	while($i < $hidCount){

		$aPara[0][0] = $_POST["txtTaishoBumon"];
		$aPara[$i][1] = $aToriCd[$i];
		$aPara[$i][2] = $aToriNm[$i];
		$aPara[$i][3] = $aBiko[$i];
		$aPara[$i][4] = $hidUCount;

		$i = $i + 1;
	}




	//年月の初期値セット
//	if($_POST['sTaishoBumon'] == "" ){
//		$sTaishoYm = date("Y/m",strtotime("0 month"));
//		//$sTaishoYm = date("Y/m",strtotime("-1 month"));
//	}else{
		$sTaishoBumon = $_POST['sTaishoBumon'];
//	}
	//検索件数セット
	//$hidCount = $_POST['hidCount'];

	//検索有無判断
	if(isset($_POST['hidSearch'])){
		$hidSearch = $_POST['hidSearch'];

		//検索ボタンが押されたら
		if($hidSearch == "1"){
			//必須チェック
			$strErrMsg = $module_cmn->fCmbNCheck($sTaishoBumon,"対象部門");


			//エラーメッセージがなければ検索処理を実行
			if($strErrMsg == ""){
				$aPara = array();

				//主要顧客データ検索
				$aPara = $module_sel->fGetPrimeCustData($sTaishoBumon);

				//該当件数がなければメッセージ表示
				//if(count($aPara) == 0){
				//	$strMsg = $module_sel->fMsgSearch("N006","");
				//}
				//検索件数セット
				$hidCount = count($aPara);

				//検索結果がない場合は処理年月を設定
				//if($aPara[0][0] == ""){
				//	$aPara[0][0] = $sTaishoYm;
				//}

			}

		}
	}


	//確定処理
	if(isset($_POST['hidExcute'])){
		$hidExcute = $_POST['hidExcute'];

		//確定区分が１なら更新処理を行う
		if($hidExcute == "1"){

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

			//チェック処理

			//セッションチェック(セッションが書き換えられていないか)
			if($_POST['hidTantoCd'] != $_SESSION['login'][0]){
				$strErrMsg = $module_sel->fMsgSearch("E034","");
			}

//			if($strErrMsg == ""){
//				//取引先コード存在チェック
//				$i = 0;
//				$aToriCd = $_POST["txtToriCd"];
//				//件数分チェック
//				while($i < count($aToriCd)){
//					//取引先コードが設定されていたらチェックする
//					if(trim($aToriCd[$i]) <> ""){
//						$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck2($aToriCd[$i],"取引先コード","T_MS_SHIIRE","C_SHIIRE_CD","V2_DENPYO_KBN","99","('1','2')");
//					}
//					$i = $i + 1;
//				}
//			}

//			if($strErrMsg == ""){
//				//更新回数チェック
//				if(!$module_sel->fKoshinCheck($_POST['hidTaishoBumon'],$hidUCount,"主要顧客情報","T_MS_PRIME_CUST","C_TAISHO_SECTION")){
//					$strErrMsg = $strErrMsg.$module_sel->fMsgSearch("E002","対象部門:".$_POST['hidTaishoBumon']);
//				}
//			}

			//エラーメッセージがなければ更新処理を実行
			if($strErrMsg == ""){

				//=========================================
				//トランザクション開始処理
				//=========================================
				$conn = $module_upd->fTransactionStart();


				//主要顧客データ削除処理
				if(!$module_upd->fPrimeCustDelete($conn,str_replace("/","",$_POST['hidTaishoBumon']))){
					$module_upd->fTransactionEnd($conn,false);
					$strErrMsg = $module_sel->fMsgSearch("E001","エラー内容:データベースエラー");
					exit;
				}


				//主要顧客データ登録処理
				if(!$module_upd->fPrimeCustInsert($conn,$hidCount,$_SESSION['login'],$_POST['hidUCount'])){
					$strErrMsg = $module_sel->fMsgSearch("E001","エラー内容:データベースエラー");
					//exit;
					//================================================
					//トランザクション終了処理(true:コミット,false:ロールバック)
					//================================================
					if($module_upd->fTransactionEnd($conn,false)){
						//$strMsg = $module_sel->fMsgSearch("N002","");	//ロールバックしました
					}
				}else{
					//================================================
					//トランザクション終了処理(true:コミット,false:ロールバック)
					//================================================
					if($module_upd->fTransactionEnd($conn,true)){
						$strMsg = $module_sel->fMsgSearch("N002","");	//更新しました
					}
					//データ再表示
					$aPara = array();

					//その他未払いデータ検索
					$aPara = $module_sel->fGetPrimeCustData($sTaishoBumon);

					//該当件数がなければメッセージ表示
					//if(count($aPara) == 0){
					//	$strMsg = $module_sel->fMsgSearch("N006","");
					//}
					//検索件数セット
					$hidCount = count($aPara);

					//検索結果がない場合は処理年月を設定
					//if($aPara[0][0] == ""){
					//	$aPara[0][0] = $sTaishoYm;
					//}
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
<TITLE>主要取引先ﾏｽﾀﾒﾝﾃﾅﾝｽ</TITLE>
<link rel="stylesheet" type="text/css" href="css/common.css">
<script type="text/javascript" src="js/common.js"><!-- 共通javascript読み込み --></script>
<script type="text/javascript" >
<!-- 個別javascript記述 -->


	//戻るボタン
	function fReturn(){
		document.form.target ="main";
		document.form.action ="main.php";
		document.form.submit();
	}


	//検索ボタン押下時
	function fncSearch(){


			document.form.hidSearch.value = "1";
			document.form.target ="main";
			document.form.action ="F_MST0030.php";
			document.form.submit();

	}

	//確定ボタン押下時
	function fncExcute(){

		if(fncCheck()){
			//確認メッセージ
			if(window.confirm('確定してもよろしいですか？')){
				document.form.hidExcute.value = "1";
				//ヘッダの担当者コードをセット
				document.form.hidTantoCd.value = parent.head.hidTantoCd.value;
				document.form.action ="F_MST0030.php";
				document.form.method ="POST";
				document.form.submit();
			}else{
				return false;
			}
		}
	}


	/* テキストボックス背景色変更 */
	function fChangeColor(strObj,strClr){
		strObj.style.backgroundColor=strClr;
	}





	//テーブル行追加
	//引数･･･追加対象行数
	function fncInsertRow(trigger){



//		var table = trigger.ownerDocument.getElementById('Table2');
//		// 最初の行コピー
//		var row = table.rows[1].cloneNode(true);
//		// 名前は好きなように。
//		row.id = 'ROW.' + table.rows.length;
//



		var i,j,objNode,objNodeList,nLength,objResult;

		// 結果表示用 DIV
		objResult = document.getElementById( "Table2" );
		// 対象テーブル
		objNode = document.getElementById( "Table2" );

		// 対象テーブル内の TR ノード
		objNodeList = objNode.getElementsByTagName( "TR" );

		// 行数
		nLength = objNodeList.length;

		// 一行目のクローンを作成 ( TH があるので、最初の行は 1 )
		var cloneNode = objNodeList.item(1).cloneNode( true );



		//最終行の項番を取得する
		var lastNode = objNodeList.item(nLength - 1).cloneNode( true );
		var lastTDList = lastNode.getElementsByTagName( "TD" );
		var lastPageValue = lastTDList.item( 0 ).firstChild.nodeValue;





		//alert(objNode.getElementsByTagName("INPUT")[0].value);
		//alert(cloneNode.getElementsByTagName("INPUT")[2].value);
		//alert(cloneNode.getElementsByTagName("INPUT").length);
		//取引先コードをクリア
		cloneNode.getElementsByTagName("INPUT")[0].value = "";
		//取引先名をクリア
		cloneNode.getElementsByTagName("INPUT")[2].value = "";


		//システム日付取得
//		var strSysDate = fGetSysDate();
//
//		var strNext15 = fncGetNext15(eval(strSysDate.substr(0,4)),eval(strSysDate.substr(4,2)),1,1);
//		var strYear = strNext15.getFullYear();
//		var strMonth = strNext15.getMonth();
//		//一桁の場合は０付加
//		if(strMonth < 10){
//			strMonth = "0" + strMonth;
//		}



		//備考をクリア
		cloneNode.getElementsByTagName("INPUT")[3].value = "";


		//部門 ノード
//		var targetNodeBumon = document.getElementsByName("cmbBumon[]")[0];
		//部門 ノードリスト
//		var targetNodeListBumon = targetNodeBumon.getElementsByTagName("OPTION");


		// クローンノードの設定
		var nodeTDList = cloneNode.getElementsByTagName( "TD" );


		//項番
		nodeTDList.item( 0 ).firstChild.nodeValue = eval(lastPageValue) + 1;

		//var testTDList = document.getElementsByTagName( "TD" )[nLength];
		//alert(testTDList.item(0).value);

		// 個別設定
	//	nodeTDList.item( 1 ).style.fontWeight = "bold";
	//	nodeTDList.item( 2 ).setAttribute("align", "right");

		// ２行目の前に追加
		// appendChild を実行する為に、TR ノードの親ノードを取得
		var parentNode = objNodeList.item(1).parentNode;
		parentNode.appendChild( cloneNode, objNodeList[2] );

	//	alert(document.form.txtToriCd[0].value);


		//var obj = document.getElementsById('txtToriCd')[0];
		//alert(obj.value);

		// 最初の tbody に行追加（HTML4.01 の場合 tbody 必須）
		//table.tBodies[0].appendChild(row);

		//txtToriCdの値取得([0]･･･1行目、[1]･･･2行目)
		//alert(document.getElementsByName("txtToriCd[]")[1].value);




	}

	//行削除
	function fDelRow(obj){

		var i,j,objNode,objNodeList;

		// 対象テーブル
		objNode = document.getElementById( "Table2" );

		// 対象テーブル内の TR ノード
		objNodeList = objNode.getElementsByTagName( "TR" );

		// 残りの行数が2の場合は削除させない
		if(objNodeList.length == 2 ){
			alert("削除できません");
		}else{
			var TR = obj.parentNode.parentNode;
			TR.parentNode.deleteRow(TR.sectionRowIndex);
		}

	}





	/* 計算＋入力値チェック処理 */
	function fncCheck(){
		var iKng;
		var iTegataKngS;
		var i;
		var iHit = 0;
		var bErr;
		var strSysYmd = "";
		var strShihaYmd = "";

		i = 0;
		bErr = false;


		var objNode,objNodeList,nLength,objResult;

		// 結果表示用 DIV
		objResult = document.getElementById( "Table2" );
		// 対象テーブル
		objNode = document.getElementById( "Table2" );

		// 対象テーブル内の TR ノード
		objNodeList = objNode.getElementsByTagName( "TR" );

		// 行数
		nLength = objNodeList.length - 1;

		//１０件以上行があったらエラーにする
		if(nLength > 10){
			alert("登録できる行数は１０行以下です\n「削除」ボタンで１０行以内にして下さい");
			bErr = true;
		}else{

			//件数分ループ
			while(i < nLength){

				//得意先コードが設定されていたらチェックを行う

				if(fSpaceTrim(document.getElementsByName("txtToriCd[]")[i].value) != ""){

					iHit = iHit + 1;

					//色変更
					fChangeColor(document.getElementsByName("txtToriCd[]")[i],"white");

				}else{
					alert("追加した行に取引先コードを設定して下さい");
					//色変更
					fChangeColor(document.getElementsByName("txtToriCd[]")[i],"red");
					bErr = true;
				}

				i = i + 1;
			}


		}


		//1件でもエラーがあったらfalse
		if(bErr){
			return false;
		}else{
			return true;
		}
	}


</script>
</HEAD>
<BODY style="font-size : medium;border-collapse : separate;" onload=fLoadDisplay();>



<FORM name="form" method="post" onSubmit="return false;">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000">
      	<SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">
      	【主要取引先マスタメンテナンス】
      	</SPAN>
      </TD>
    </TR>
  </TBODY>
</TABLE>
<br>
<input type="hidden" name="token" value="<?php echo $token; ?>">


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
      <TD class="tdnone" width="800">
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>検索条件</B></FONT>
		</DIV>
      </TD>
      <TD class="tdnone" width="200" align="right">
      	<INPUT type="button" name="btnSearch" value="　戻　る　" onClick="fReturn()">
      	<?php echo $strManulPath;  ?>
      </TD>
    </TR>
  </TBODY>
</TABLE>
<TABLE class="tbline" width="250">

  <TBODY>
    <TR>
      <TD class="tdnone1" width="100">対象部門</TD>
      <TD class="tdnone3" width="150">
      	<SELECT name="sTaishoBumon" id="sTaishoBumon" tabindex="0" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C04',$sTaishoBumon); ?>
      	</SELECT>
      </TD>
    </TR>
  </TBODY>
</TABLE>
<br>
<P>
	<INPUT type="button" name="btnSearch" value="　検　索　" onClick="fncSearch()">　
	<INPUT type="reset" name="btnReset" value="　リセット　">　
</P>
<input type="hidden" name="hidSearch" value="0">
<br>
<?php
//検索時にエラーがなくもしくは確定時のエラーの場合かつ正常処理メッセージがない場合は表示。
if ( ($hidSearch == "1" && $strErrMsg == "")  || ( $hidExcute == "1")){

	//検索結果があれば
	//if($hidCount > 0 ){
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

<TABLE class="tbline" width="250" id="Table1" >

  <TBODY>
    <TR>
      <TD class="tdnone2" width="100" ><B>対象部門</B></TD>
      <TD class="tdnone3" width="150">
      	<B>
      		<INPUT type="text" name="txtTaishoBumon" id="txtTaishoBumon" class="textboxdisp1" size="5" readonly value="<?php echo $module_sel->fDispKbn('C04', $sTaishoBumon); ?>">
      		<INPUT type="hidden" name="hidTaishoBumon" id="hidTaishoBumon"  readonly value="<?php echo $sTaishoBumon; ?>">

      	</B>
      </TD>
    </TR>
  </TBODY>
</TABLE>
<?php  //品証のユーザのみ表示
	if(substr($_SESSION['login'][2],0,3) == "117"){
?>
<br>
	<INPUT type='button' name='btnAddRow' value='行追加' onClick='fncInsertRow(this)'>
	<INPUT type="button" name="btnExcute" value="　確　定　" onclick="fncExcute()" >
	<INPUT type="reset" name="btnReset" value="　リセット　">
	<input type="hidden" name="hidExcute" value="0">
	<input type="hidden" name="hidTantoCd" value="">
	<input type="hidden" name="hidUCount" value="<?php echo $aPara[0][8]; ?>">
	<input type="hidden" name="hidCount" value="<?php echo $hidCount; ?>">
	<input type="hidden" name="hidDenpyoKbn" value="0">
<br>
<?php
	}
?>

<br>
<div >

<TABLE class="tbline" width="1000" id="Table2">
  <TBODY>

	<?php

	$i = 0;
	//件数分ループ
	while($i < $hidCount){
		//奇数行、偶数行によって色変更
		if(($i % 2) == 0){
			$strClass = "tdnone3";
			$strClass2 = "tdnone3";
		}else{
			$strClass = "tdnone3";
			$strClass2 = "tdnone3";
			//$strClass = "tdnone4";
			//$strClass2 = "textboxdisp2";
		}

		//ヘッダーの挿入
		if($i == 0){
	?>

		<TR class="tdnone3">
		  <TD class="tdnone2" width="45" align="center" nowrap>項番</TD>
	      <TD class="tdnone1" width="70" align="center" nowrap>取引先ｺｰﾄﾞ</TD>
	      <TD class="tdnone2" width="300" align="center" nowrap>取引先名</TD>
	      <TD class="tdnone2" width="420" align="center" nowrap>備考</TD>
	      <TD class="tdnone5" width="40" align="center" nowrap>ｱｸｼｮﾝ</TD>
	    </TR>
	<?php
		}
	?>

	    <TR class="<?php echo $strClass; ?>">
	    	<TD class="<?php echo $strClass; ?>">
	    		<?php echo $i+1; ?>
	    	</TD>
	    	<TD class="<?php echo $strClass; ?>">
	      		<INPUT size="2" type="text" class="<?php echo $strClass2; ?>" name="txtToriCd[]" id="txtToriCd[]" maxlength=5 value="<?php echo $aPara[$i][1]; ?>" readonly>
	      		<INPUT type="button" name="btnIraiC" id="btnIraiC" value="" onClick="fOpenSearchArray('F_MSK0020','txtToriCd[]','txtToriNm[]','','0','0',this)">

	      	</TD>
	      	<TD class="<?php echo $strClass; ?>"  >
	      		<INPUT size="60" type="text" class="<?php echo $strClass2; ?>" name="txtToriNm[]" id="txtToriNm[]" readOnly value="<?php echo $aPara[$i][2]; ?>">
	      	</TD>
		    <TD class="<?php echo $strClass; ?>">
	      		<INPUT size="60" type="text" class="<?php echo $strClass; ?>" name="txtTekiyo[]" id="txtTekiyo[]"  value="<?php echo $aPara[$i][4]; ?>">
	      		<INPUT  type="hidden" class="<?php echo $strClass2; ?>" name="hidCount"   value="<?php echo $aPara[$i][5]; ?>">
	      	</TD>
	    	<TD class="<?php echo $strClass; ?>" align="center">
	    		<INPUT type='button' value='削除' style='background-color : #fdc257;' onClick='fDelRow(this);'>
	    	</TD>
	    </TR>

	<?php
		$i = $i + 1;
	}
	//検索件数が0件の場合はヘッダ行と空白行を追加する
	if($i == 0 ){
		//TDクラス
		$strClass = "tdnone3";
		$strClass2 = "tdnone3";
		//支払日のデフォルト値を設定する(対象年月の15日)
		//$txtShiharaiYmd = $module_cmn->fGetMonthAgo(date("Y"),date("m"),"15",0);
		$txtShiharaiYmd = $sTaishoYm."/15";

	?>
		<TR class="tdnone3">
		  <TD class="tdnone2" width="45" align="center" nowrap>項番</TD>
	      <TD class="tdnone1" width="70" align="center" nowrap>取引先ｺｰﾄﾞ</TD>
	      <TD class="tdnone2" width="300" align="center" nowrap>取引先名</TD>
	      <TD class="tdnone2" width="420" align="center" nowrap>備考</TD>
	      <TD class="tdnone5" width="40" align="center" nowrap>ｱｸｼｮﾝ</TD>
	    </TR>
		<TR class="<?php echo $strClass; ?>">
	    	<TD class="<?php echo $strClass; ?>">
	    		<?php echo 1; ?>
	    	</TD>
	    	<TD class="<?php echo $strClass; ?>">
	      		<INPUT size="2" type="text" class="<?php echo $strClass2; ?>" name="txtToriCd[]" id="txtToriCd[]" maxlength=5 value="" readonly>
	      		<INPUT type="button" name="btnToriC" id="btnToriC" value="" onClick="fOpenSearchArray('F_MSK0020','txtToriCd[]','txtToriNm[]','','0','1',this)">
	      	</TD>
	      	<TD class="<?php echo $strClass; ?>">
	      		<INPUT size="60" type="text" class="<?php echo $strClass2; ?>" name="txtToriNm[]" id="txtToriNm[]" readOnly value="">
	      	</TD>
		    <TD class="<?php echo $strClass; ?>">
	      		<INPUT size="60" type="text" class="<?php echo $strClass; ?>" name="txtTekiyo[]" id="txtTekiyo[]"  value="">
	      		<INPUT  type="hidden" class="<?php echo $strClass2; ?>" name="hidCount[]" id="hidCount[]"  value="">
	      	</TD>
	    	<TD class="<?php echo $strClass; ?>" align="center">
	    		<INPUT type='button' value='削除' style='background-color : #fdc257;' onClick='fDelRow(this);'>
	    	</TD>
	    </TR>
	<?php
	}
	?>
	</TBODY>
</TABLE>




</div>

<?php
}elseif($hidSearch == "1"){

}
//}
?>
<br>
</FORM>
</BODY>
</HTML>
