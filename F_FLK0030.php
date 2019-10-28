<?php

	//****************************************************************************
	//プログラム名：不具合対策入力
	//プログラムID：F_FLK0030
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/06/27
	//履歴　　　　:2018/06/05 品証からの要望でヘッダー項目の削除(久米)
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
	$aParaD = array();
	$aRequest = array();

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

	//引数の取得(整理NO)
	if(isset($_GET['strRrceNo'])) {
		$txtRrceNo = $_GET['strRrceNo'];

	}
	elseif(isset($_POST['txtRrceNo'])) {
		if($_POST['txtRrceNo'] <> ""){
			$txtRrceNo = $_POST['txtRrceNo'];
		}
	}

	//一覧の検索条件の保管変数
	if(isset($_GET['aJoken'])) {
		$aJoken = $_GET['aJoken'];
		//条件について特殊文字を HTML エンティティに変換とURLエンコードを行う
		$aJoken[0] = urlencode($module_cmn->fEscape($aJoken[0]));
		$aJoken[1] = urlencode($module_cmn->fEscape($aJoken[1]));
		$aJoken[2] = urlencode($module_cmn->fEscape($aJoken[2]));
		$aJoken[3] = urlencode($module_cmn->fEscape($aJoken[3]));
		$aJoken[4] = urlencode($module_cmn->fEscape($aJoken[4]));
		$aJoken[5] = urlencode($module_cmn->fEscape($aJoken[5]));
		$aJoken[6] = urlencode($module_cmn->fEscape($aJoken[6]));
		$aJoken[7] = urlencode($module_cmn->fEscape($aJoken[7]));
		$aJoken[8] = urlencode($module_cmn->fEscape($aJoken[8]));
		$aJoken[9] = urlencode($module_cmn->fEscape($aJoken[9]));
		$aJoken[10] = urlencode($module_cmn->fEscape($aJoken[10]));
		$aJoken[11] = urlencode($module_cmn->fEscape($aJoken[11]));
		$aJoken[12] = urlencode($module_cmn->fEscape($aJoken[12]));
		$aJoken[13] = urlencode($module_cmn->fEscape($aJoken[13]));
		$aJoken[14] = urlencode($module_cmn->fEscape($aJoken[14]));
		$aJoken[15] = urlencode($module_cmn->fEscape($aJoken[15]));
		$aJoken[16] = urlencode($module_cmn->fEscape($aJoken[16]));
		$aJoken[17] = urlencode($module_cmn->fEscape($aJoken[17]));
		$aJoken[18] = urlencode($module_cmn->fEscape($aJoken[18]));
		$aJoken[19] = urlencode($module_cmn->fEscape($aJoken[19]));
		$aJoken[20] = urlencode($module_cmn->fEscape($aJoken[20]));
		$aJoken[21] = urlencode($module_cmn->fEscape($aJoken[21]));
		$aJoken[22] = urlencode($module_cmn->fEscape($aJoken[22]));
		$aJoken[23] = urlencode($module_cmn->fEscape($aJoken[23]));
	}


	//メッセージ用変数
	$strMsg = "";
	$strErrMsg = "";




	//画面項目取得(配列)
	//$txtRrceNo = $_POST["txtRrceNo"];
	$txtDrwNo = $_POST["txtDrwNo"];
	$txtProdNm = $_POST["txtProdNm"];
	$txtLotNo = $_POST["txtLotNo"];
	$txtFlawContents = $_POST["txtFlawContents"];

	$txtPc_Ap_Ans_YMD1 = $_POST["txtPc_Ap_Ans_YMD1"];
	$txtPc_Ap_Ans_YMD2 = $_POST["txtPc_Ap_Ans_YMD2"];
	$txtReturn_YMD1 = $_POST["txtReturn_YMD1"];
	$txtReturn_YMD2 = $_POST["txtReturn_YMD2"];

	$cmbHappenKbn = $_POST["cmbHappenKbn"];
	$txtHappenNotes = $_POST["txtHappenNotes"];
	$cmbOutFlowKbn = $_POST["cmbOutFlowKbn"];
	$txtOutFlowNotes = $_POST["txtOutFlowNotes"];
	$txtHappenAction = $_POST["txtHappenAction"];
	$txtOutFlowAction = $_POST["txtOutFlowAction"];
	//$cmbDisposeKbn = $_POST["cmbDisposeKbn"];
	$cmbAllValKbn = $_POST["cmbAllValKbn"];

	$aRequest = $_POST["txtRequest"];
	$aTantoCd = $_POST["txtTantoCd"];
	$aTantoNm = $_POST["txtTantoNm"];
	$aLimitYmd = $_POST["txtLimitYmd"];
	$aOpeMat = $_POST["txtOpeMat"];
	$aOpeYmd = $_POST["txtOpeYmd"];
	$aResult = $_POST["txtResult"];
	$cmbActVal = $_POST["cmbActVal"];

	$hidUCount = $_POST["hidUCount"];

	//画面件数
	$intCnt = 0;
	$hidCount = 0;
	//$hidCount = count($aRequest);
	if(is_array($aRequest)){
		$hidCount = count($aRequest);
	}
	$i = 0;

	$aPara = array();

	while($i < $hidCount){


		$aParaD[$i][1] = $aRequest[$i];
		$aParaD[$i][2] = $aTantoCd[$i];
		$aParaD[$i][3] = $aTantoNm[$i];
		$aParaD[$i][4] = $aLimitYmd[$i];
		$aParaD[$i][5] = $aOpeMat[$i];
		$aParaD[$i][6] = $aOpeYmd[$i];
		$aParaD[$i][7] = $aResult[$i];



		$i = $i + 1;
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


			//セッションチェック(セッションが書き換えられていないか)
			if($_POST['hidTantoCd'] != $_SESSION['login'][0]){
				$strErrMsg = $module_sel->fMsgSearch("E034","");
			}


			//チェック処理
			//$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbHappenKbn'],"発生原因");					//発生原因	2018/06/05 不要なため削除 k.kume
			//$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbOutFlowKbn'],"流出原因");				//流出原因	2018/06/05 不要なため削除 k.kume
			//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtHappenAction'],"発生対策");				//発生対策	2018/06/05 不要なため削除 k.kume
			//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtOutFlowAction'],"流出対策");				//流出対策	2018/06/05 不要なため削除 k.kume
			//$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbDisposeKbn'],"現品処置");				//現品処置	2016/09/05 不要なため削除 k.kume
			//$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbAllValKbn'],"全ての対策有効性");			//全ての対策有効性	2018/06/05 不要なため削除 k.kume




			if($strErrMsg == ""){
			//担当者コード存在チェック
				$i = 0;
				$aTantoCd = $_POST["txtTantoCd"];
				//件数分チェック
				while($i < count($aTantoCd)){
					//取引先コードが設定されていたらチェックする
					if(trim($aTantoCd[$i]) <> ""){
						$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($aTantoCd[$i],"担当者コード","V_FL_TANTO_INFO","C_TANTO_CD");
					}
					$i = $i + 1;
				}
			}



			if($strErrMsg == ""){
				//期限日付存在チェック
				$i = 0;
				$aLimitYmd = $_POST["txtLimitYmd"];
				//件数分チェック
				while($i < count($aLimitYmd)){
					//期限が設定されていたらチェックする
					if(str_replace("/","",$aLimitYmd[$i]) <> ""){
						$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$aLimitYmd[$i]),"期限");
					}
					$i = $i + 1;
				}
			}

			if($strErrMsg == ""){
				//実施日日付存在チェック
				$i = 0;
				$aOpeYmd = $_POST["txtOpeYmd"];
				//件数分チェック
				while($i < count($aOpeYmd)){
					//実施日が設定されていたらチェックする
					if(str_replace("/","",$aOpeYmd[$i]) <> ""){
						$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$aOpeYmd[$i]),"実施日");
					}
					$i = $i + 1;
				}
			}

			//文字数チェック
			if($strErrMsg == ""){
				//要望事項文字数チェック
				$i = 0;
				$aRequest = $_POST["txtRequest"];
				//件数分チェック
				while($i < count($aRequest)){
					//文字数チェック
					$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($aRequest[$i],250,"要望事項");
					$i = $i + 1;
				}

				//実施内容文字数チェック
				$i = 0;
				$aOpeMat = $_POST["txtOpeMat"];
				//件数分チェック
				while($i < count($aOpeMat)){
					//文字数チェック
					$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($aOpeMat[$i],250,"実施内容");
					$i = $i + 1;
				}

				//結果文字数チェック
				$i = 0;
				$aResult = $_POST["txtResult"];
				//件数分チェック
				while($i < count($aResult)){
					//文字数チェック
					$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($aResult[$i],250,"結果");
					$i = $i + 1;
				}

			}




			if($strErrMsg == ""){
				//更新回数チェック
				if(!$module_sel->fKoshinCheck(str_replace("/","",$_POST['txtRrceNo']),$hidUCount,"不具合対策情報","T_TR_ACTION_H","C_REFERENCE_NO")){
					$strErrMsg = $strErrMsg.$module_sel->fMsgSearch("E002","整理NO:".$_POST['txtRrceNo']);
				}
			}

			//エラーメッセージがなければ更新処理を実行
			if($strErrMsg == ""){

				//=========================================
				//トランザクション開始処理
				//=========================================
				$conn = $module_upd->fTransactionStart();


				//不具合対策ヘッダ更新処理
				//品証からの要望でヘッダ情報は更新なし 2018/06/05 k.kume
				/*
				if(!$module_upd->fActionHExcute($conn,$_SESSION['login'],$_POST['txtRrceNo'],$_POST['hidUCount'])){
					$module_upd->fTransactionEnd($conn,false);
					$strErrMsg = $module_sel->fMsgSearch("E001","エラー内容:データベースエラー");
					exit;
				}
				*/

				//不具合情報更新処理(状態区分を更新)
				//品証からの要望でヘッダ情報は更新なし 2018/06/05 k.kume
				/*
				if(!$module_upd->fFlawStatusUpdate($conn,$_SESSION['login'],$_POST['txtRrceNo'],$_POST['hidUCount'])){
					$module_upd->fTransactionEnd($conn,false);
					$strErrMsg = $module_sel->fMsgSearch("E001","エラー内容:データベースエラー");
					exit;
				}
				*/

				//不具合対策明細データ削除処理
				if(!$module_upd->fActionDDelete($conn,$_SESSION['login'],$_POST['txtRrceNo'])){
					$module_upd->fTransactionEnd($conn,false);
					$strErrMsg = $module_sel->fMsgSearch("E001","エラー内容:データベースエラー");
					exit;
				}


				//不具合対策明細データ登録処理
				if(!$module_upd->fActionDInsert($conn,$_SESSION['login'],$_POST['txtRrceNo'])){
					$strErrMsg = $module_sel->fMsgSearch("E001","エラー内容:データベースエラー");
					//exit;
					//================================================
					//トランザクション終了処理(true:コミット,false:ロールバック)
					//================================================
					if($module_upd->fTransactionEnd($conn,false)){
						//$strMsg = $module_sel->fMsgSearch("N002","");	//ロールバックしました
					}
				}

				//エラーがなければトランザクションコミットする
				if($strErrMsg == ""){
					//================================================
					//トランザクション終了処理(true:コミット,false:ロールバック)
					//================================================
					if($module_upd->fTransactionEnd($conn,true)){
						$strMsg = $module_sel->fMsgSearch("N002","");	//更新しました
					}
				}

			}

		}
	}

	if($strErrMsg == ""){
		//不具合情報と対策ヘッダデータ検索
		$aPara = $module_sel->fGetFlawStepHData($txtRrceNo);


		$txtDrwNo = $aPara[12];			//図番
		$txtProdNm = $aPara[11];		//製品名
		$txtLotNo = $aPara[15];			//ロットNO
		$txtProdNm = $aPara[11];		//製品名
		$txtFlawContents = $aPara[23];	//不具合内容



		$txtPc_Ap_Ans_YMD1 = $module_cmn->fChangDateFormat($aPara[34]);	//品証指定回答日(社内)
		$txtPc_Ap_Ans_YMD2 = $module_cmn->fChangDateFormat($aPara[35]);	//品証指定回答日(協工)
		$txtReturn_YMD1 = $module_cmn->fChangDateFormat($aPara[36]);	//返却日(社内)
		$txtReturn_YMD2 = $module_cmn->fChangDateFormat($aPara[37]);	//返却日(協工)


		$cmbHappenKbn = $aPara[40];
		$txtHappenNotes = $aPara[41];
		$cmbOutFlowKbn = $aPara[42];
		$txtOutFlowNotes = $aPara[43];
		$txtHappenAction = $aPara[44];
		$txtOutFlowAction = $aPara[45];
		//$cmbDisposeKbn = $aPara[46];
		$cmbAllValKbn = $aPara[47];

		$hidUCount = $aPara[48];

		//不具合対策明細データ検索
		$aParaD = $module_sel->fGetFlawStepDData($txtRrceNo);

		$hidCount = count($aParaD);
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
<TITLE>不具合トレース入力</TITLE>
<link rel="stylesheet" type="text/css" href="css/common.css">
<script type="text/javascript" src="js/common.js"><!-- 共通javascript読み込み --></script>
<script type="text/javascript" >
<!-- 個別javascript記述 -->


	//戻るボタン
	function fReturn(){
		document.form.action ="F_FLK0020.php?action=main&search=1"
		+ "&aJoken[0]=<?php echo $aJoken[0];?>
		&aJoken[1]=<?php echo $aJoken[1];?>
		&aJoken[2]=<?php echo $aJoken[2];?>
		&aJoken[3]=<?php echo $aJoken[3];?>
		&aJoken[4]=<?php echo $aJoken[4];?>
		&aJoken[5]=<?php echo $aJoken[5];?>
		&aJoken[6]=<?php echo $aJoken[6];?>
		&aJoken[7]=<?php echo $aJoken[7];?>
		&aJoken[8]=<?php echo $aJoken[8];?>
		&aJoken[9]=<?php echo $aJoken[9];?>
		&aJoken[10]=<?php echo $aJoken[10];?>
		&aJoken[11]=<?php echo $aJoken[11];?>
		&aJoken[12]=<?php echo $aJoken[12];?>
		&aJoken[13]=<?php echo $aJoken[13];?>
		&aJoken[14]=<?php echo $aJoken[14];?>
		&aJoken[15]=<?php echo $aJoken[15];?>
		&aJoken[16]=<?php echo $aJoken[16];?>
		&aJoken[17]=<?php echo $aJoken[17];?>
		&aJoken[18]=<?php echo $aJoken[18];?>
		&aJoken[19]=<?php echo $aJoken[19];?>
		&aJoken[20]=<?php echo $aJoken[20];?>
		&aJoken[21]=<?php echo $aJoken[21];?>
		&aJoken[22]=<?php echo $aJoken[22];?>
		&aJoken[23]=<?php echo $aJoken[23];?>";
		document.form.submit();
	}




	//確定ボタン押下時
	function fncExcute(){


		//確認メッセージ
		if(window.confirm('確定してもよろしいですか？')){
			document.form.hidExcute.value = "1";
			//ヘッダの担当者コードをセット
			document.form.hidTantoCd.value = parent.head.hidTantoCd.value;
			document.form.action ="F_FLK0030.php"
			+ "?aJoken[0]=<?php echo $aJoken[0];?>
			&aJoken[1]=<?php echo $aJoken[1];?>
			&aJoken[2]=<?php echo $aJoken[2];?>
			&aJoken[3]=<?php echo $aJoken[3];?>
			&aJoken[4]=<?php echo $aJoken[4];?>
			&aJoken[5]=<?php echo $aJoken[5];?>
			&aJoken[6]=<?php echo $aJoken[6];?>
			&aJoken[7]=<?php echo $aJoken[7];?>
			&aJoken[8]=<?php echo $aJoken[8];?>
			&aJoken[9]=<?php echo $aJoken[9];?>
			&aJoken[10]=<?php echo $aJoken[10];?>
			&aJoken[11]=<?php echo $aJoken[11];?>
			&aJoken[12]=<?php echo $aJoken[12];?>
			&aJoken[13]=<?php echo $aJoken[13];?>
			&aJoken[14]=<?php echo $aJoken[14];?>
			&aJoken[15]=<?php echo $aJoken[15];?>
			&aJoken[16]=<?php echo $aJoken[16];?>
			&aJoken[17]=<?php echo $aJoken[17];?>
			&aJoken[18]=<?php echo $aJoken[18];?>
			&aJoken[19]=<?php echo $aJoken[19];?>
			&aJoken[20]=<?php echo $aJoken[20];?>
			&aJoken[21]=<?php echo $aJoken[21];?>
			&aJoken[22]=<?php echo $aJoken[22];?>
			&aJoken[23]=<?php echo $aJoken[23];?>";

			document.form.method ="POST";
			document.form.submit();
		}else{
			return false;
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
		//alert(cloneNode.getElementsByTagName("INPUT")[0].value);

		//alert(cloneNode.getElementsByTagName("INPUT").length);
		//要望事項をクリア
		cloneNode.getElementsByTagName("TEXTAREA")[0].value = "";
		cloneNode.getElementsByTagName("TEXTAREA")[1].value = "";
		cloneNode.getElementsByTagName("TEXTAREA")[2].value = "";
		cloneNode.getElementsByTagName("INPUT")[0].value = "";
		cloneNode.getElementsByTagName("INPUT")[1].value = "";
		cloneNode.getElementsByTagName("INPUT")[2].value = "";
		cloneNode.getElementsByTagName("INPUT")[3].value = "";
		cloneNode.getElementsByTagName("INPUT")[4].value = "";

		//cloneNode.getElementsByTagName("INPUT")[5].value = "";
		//cloneNode.getElementsByTagName("INPUT")[6].value = "";

		//取引先名をクリア
//		cloneNode.getElementsByTagName("INPUT")[2].value = "";
		//支払金額のデフォルト値を設定
		//cloneNode.getElementsByTagName("INPUT")[3].value = "0";
		//経理からの要望のため未設定に変更 2011/06/15 k.kume
//		cloneNode.getElementsByTagName("INPUT")[3].value = "";


		//システム日付取得
//		var strSysDate = fGetSysDate();
//
//		var strNext15 = fncGetNext15(eval(strSysDate.substr(0,4)),eval(strSysDate.substr(4,2)),1,1);
//		var strYear = strNext15.getFullYear();
//		var strMonth = strNext15.getMonth();
//		var strDay = "15";
//		//一桁の場合は０付加
//		if(strMonth < 10){
//			strMonth = "0" + strMonth;
//		}

		//alert(strNext15.getFullYear());

		//cloneNode.getElementsByTagName("INPUT")[4].value = strYear + "/" + strMonth + "/" + strDay;
		//支払日の対象年月の15日
		//cloneNode.getElementsByTagName("INPUT")[4].value = document.form.txtTaishoYm.value + "/" + strDay;
		//経理からの要望のため前の行コピーに変更 2011/06/15 k.kume
//		cloneNode.getElementsByTagName("INPUT")[4].value = document.getElementsByName("txtShihaYmd[]")[eval(nLength) - 2].value;


		//適要をクリア
//		cloneNode.getElementsByTagName("INPUT")[5].value = "";


		//部門 ノード
//		var targetNodeBumon = document.getElementsByName("cmbBumon[]")[0];
		//部門 ノードリスト
//		var targetNodeListBumon = targetNodeBumon.getElementsByTagName("OPTION");

		//科目 ノード
//		var targetNodeKamoku = document.getElementsByName("cmbKamoku[]")[0];
		//科目 ノードリスト
//		var targetNodeListKamoku = targetNodeKamoku.getElementsByTagName("OPTION");


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

	//	alert(document.form.txtRequest[0].value);


		//var obj = document.getElementsById('txtRequest')[0];
		//alert(obj.value);

		// 最初の tbody に行追加（HTML4.01 の場合 tbody 必須）
		//table.tBodies[0].appendChild(row);

		//txtRequestの値取得([0]･･･1行目、[1]･･･2行目)
		//alert(document.getElementsByName("txtRequest[]")[1].value);




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

	/* Excel出力 */
	function fncExcelOut(){

		//確認メッセージ
		if(window.confirm('登録済みのデータをExcel出力します。\nExcel出力してもよろしいですか？')){

			document.form.action ="F_FLK0030.php";
			document.form.method ="POST";
			document.form.target ="main";
			//document.form.target ="_blank";
			document.form.submit();
		}else{
			return false;
		}
	}


	/* 日付桁数チェック  */
	function fCalCheckFormatDOM(strObj,i,strTit,nullFlg){
		var strYYYYMMDD;

		fChangeColor(document.getElementsByName(strObj)[i],"white");

		if(nullFlg){
			if(document.getElementsByName(strObj)[i].value == ""){
				alert("日付は必須入力です");
				fChangeColor(document.getElementsByName(strObj)[i],"red");
				document.getElementsByName(strObj)[i].focus();
				return false;
			}else{
				fChangeColor(document.getElementsByName(strObj)[i],"white");
			}
		}

		//入力されていたらチェック
		if(document.getElementsByName(strObj)[i].value != ""){
			//入力日付が8桁もしくは10桁ならば日付チェック
			if(document.getElementsByName(strObj)[i].value.length == 8 || document.getElementsByName(strObj)[i].value.length == 10){
				//スラッシュを一旦取り除く
				strYYYYMMDD = document.getElementsByName(strObj)[i].value.replace(/\//g,"");

				//日付の妥当性チェック
				if(!fChkURUDOM(strYYYYMMDD,strObj,i,strTit)){
					return false;
				}
			}else{
				alert("日付はYYYYMMDD形式かYYYY/MM/DD形式で入力して下さい");
				fChangeColor(document.getElementsByName(strObj)[i],"red");
				document.getElementsByName(strObj)[i].focus();
				return false;
			}
		}

		return true;
	}

	//日付の妥当性チェック
	function fChkURUDOM(number,strObj,i,strTit) {
	    yy = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31, 29);

	    wYear  = number.substr(0, 4);
	    wMonth = wMChk = number.substr(4, 2);
	    wDay   = number.substr(6, 2);

		fChangeColor(document.getElementsByName(strObj)[i],"white");

	    // 年の範囲検証
	    if (!(wYear >= 2000 && wYear <= 2100)) {
	        alert("年の指定が正しくありません[" + strTit + "]");
	        fChangeColor(document.getElementsByName(strObj)[i],"red");
	        return false;
	    }

	    // 月の範囲検証
	    if (!(wMonth >= 1 && wMonth <= 12)) {
	        alert("月の指定が正しくありません[" + strTit + "]");
	        fChangeColor(document.getElementsByName(strObj)[i],"red");
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
	        fChangeColor(document.getElementsByName(strObj)[i],"red");
	        return false;
	    }

	    return true;
	}


	/* 取引先コードテキストボックスでエンターキーが押された時 */
	function fShireGet(intRow){

		//取引先コードが入っていたら
		if(document.form.txtRequest[intRow].value!="") {

			var a = new Ajax.Request("K_AJX0021.php",
				{
					method: 'POST'
					,postBody: Form.serialize('form')
					,onSuccess: function(request) {
						//alert('読み込み成功しました');
					}
					,onComplete: function(request) {


						//JavaScript Object Notation(JSON)形式に変更
						var json = eval(request.responseText);  // ← この行
					    var html = "";
					    //取得データセット
				        document.form.txtToriNm[intRow].value = json[0];

				        //document.form.txtChumonDate.focus();
					}
					,onFailure: function(request) {
						alert('取引先マスタに存在しません');
						document.form.txtToriNm[intRow].value = "";
					}
					,onException: function (request) {
						alert('取引先マスタに存在しません');
						document.form.txtToriNm[intRow].value = "";
					}
				}
			);
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
      	【不具合トレース入力】
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



<br>
<?php


//不具合情報があれば表示
//if ( $aPara[0] <> ""){

//エラーがなくもしくは確定時のエラーの場合かつ正常処理メッセージがない場合は表示。
//if (  $strErrMsg == ""  ||  $hidExcute == "1"){

?>



<TABLE border="0">
  <TBODY>
    <TR>
      <TD class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>不具合情報</B></FONT>
		</DIV>
      </TD>
      <TD class="tdnone" width="200" align="right">
      	<INPUT type="button" name="btnSearch" value="　戻　る　" onClick="fReturn()">
      	<?php echo $strManulPath;  ?>
      </TD>
    </TR>
  </TBODY>
</TABLE>
<TABLE class="tbline" width="1000" id="Table1">

  <TBODY>
    <TR>
      <TD class="tdnone9" width="250">整理NO</TD>
      <TD class="tdnone3" width="750" colspan="3" width="75">
	      <INPUT size="20" type="text" class="textboxdisp" name="txtRrceNo" style="ime-mode: disabled;" readonly value="<?php echo $txtRrceNo; ?>">
      </TD>
    </TR>
    <TR>
    	<TD class="tdnone9"  >仕様番号</TD>
    	<TD class="tdnone3" width="300" colspan="">
    		<INPUT size="40" type="text" class="textboxdisp" name="txtDrwNo" style="ime-mode: disabled;" readonly value="<?php echo $txtDrwNo;  ?>">
    	</TD>

    	<TD class="tdnone9"  width="160">製品名</TD>
    	<TD class="tdnone3" width="300" colspan="">
    		<INPUT size="40" type="text" class="textboxdisp" name="txtProdNm" style="ime-mode: disabled;" readonly value="<?php echo $txtProdNm;  ?>">
    	</TD>
    </TR>
    <TR>
		<TD class="tdnone9">ロットNO</TD>
		<TD class="tdnone3" colspan="3">
			<INPUT size="100" type="text" class="textboxdisp" name="txtLotNo" style="ime-mode: disabled;" readonly value="<?php echo $txtLotNo;  ?>">
		</TD>
	</TR>
    <TR>
		<TD class="tdnone9">不具合内容</TD>
		<TD class="tdnone3" colspan="3">
			<textarea  cols="100" rows="5" name="txtFlawContents" style="ime-mode: disabled;" readonly><?php echo $txtFlawContents; ?></textarea>

		</TD>
	</TR>
<!--

	<TR>
    	<TD class="tdnone9"  >品証指定回答日(社内)</TD>
    	<TD class="tdnone3"  colspan="">
    		<INPUT size="40" type="text" class="textboxdisp" name="txtPc_Ap_Ans_YMD1" style="ime-mode: disabled;" readonly value="<?php //echo $txtPc_Ap_Ans_YMD1;  ?>">
    	</TD>
    	<TD class="tdnone9"  >返却日(社内)</TD>
    	<TD class="tdnone3"  colspan="">
    		<INPUT size="40" type="text" class="textboxdisp" name="txtReturn_YMD1" style="ime-mode: disabled;" readonly value="<?php //echo $txtReturn_YMD1;  ?>">
    	</TD>

    </TR>
	<TR>
		<TD class="tdnone9"  >品証指定回答日(協工)</TD>
    	<TD class="tdnone3"  colspan="">
    		<INPUT size="40" type="text" class="textboxdisp" name="txtPc_Ap_Ans_YMD2" style="ime-mode: disabled;" readonly value="<?php //echo $txtPc_Ap_Ans_YMD2;  ?>">
    	</TD>
    	<TD class="tdnone9"  >返却日(協工)</TD>
    	<TD class="tdnone3"  colspan="">
    		<INPUT size="40" type="text" class="textboxdisp" name="txtReturn_YMD2" style="ime-mode: disabled;" readonly value="<?php //echo $txtReturn_YMD2;  ?>">
    	</TD>
    </TR>
	<TR>
		<TD class="tdnone1">発生原因</TD>
		<TD class="tdnone3" colspan="3">
			<SELECT name="cmbHappenKbn" id="cmbHappenKbn" tabindex="150" >
	      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
	        	<?php //$module_sel->fMakeCombo('C12',$cmbHappenKbn); ?>
	      	</SELECT>
	      	<INPUT size="80" type="text" name="txtHappenNotes" maxlength="50" value="<?php //echo $txtHappenNotes; ?>">
		</TD>
	</TR>
	<TR>
		<TD class="tdnone1">流出原因</TD>
		<TD class="tdnone3" colspan="3">
			<SELECT name="cmbOutFlowKbn" id="cmbOutFlowKbn" tabindex="150" >
	      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
	        	<?php //$module_sel->fMakeCombo('C13',$cmbOutFlowKbn); ?>
	      	</SELECT>
	      	<INPUT size="80" type="text" name="txtOutFlowNotes" maxlength="50" value="<?php //echo $txtOutFlowNotes; ?>">
		</TD>
	</TR>
	<TR>
		<TD class="tdnone1">発生対策</TD>
		<TD class="tdnone3" colspan="3">
			<INPUT size="100" type="text" name="txtHappenAction" maxlength="100" value="<?php //echo $txtHappenAction; ?>">
		</TD>
	</TR>
		<TR>
		<TD class="tdnone1">流出対策</TD>
		<TD class="tdnone3" colspan="3">
			<INPUT size="100" type="text" name="txtOutFlowAction" maxlength="100" value="<?php //echo $txtOutFlowAction; ?>">
		</TD>
	</TR>
	<TR>
	
		<TD class="tdnone1">現品処置</TD>
		<TD class="tdnone3" colspan="">
			<SELECT name="cmbDisposeKbn" id="cmbDisposeKbn" tabindex="150" >
	      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
	        	<?php //$module_sel->fMakeCombo('C16',$cmbDisposeKbn); ?>
	      	</SELECT>
		</TD>
	
		<TD class="tdnone1">全ての対策有効性</TD>
		<TD class="tdnone3" colspan="3">
			<SELECT name="cmbAllValKbn" id="cmbAllValKbn" tabindex="150" >
	      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
	        	<?php //$module_sel->fMakeCombo('C11',$cmbAllValKbn); ?>
	      	</SELECT>
		</TD>
	</TR>
-->

  </TBODY>
</TABLE>
<br>
<div >

<TABLE border="0">
  <TBODY>
    <TR>
      <TD class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>不具合トレース情報</B></FONT>
		</DIV>
      </TD>
    </TR>
  </TBODY>
</TABLE>

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
		  <TD class="tdnone2" width="45" align="center" nowrap>NO</TD>
	      <TD class="tdnone0" width="150" align="center" nowrap>要望事項(登録時必須)</TD>
	      <TD class="tdnone2" width="90" align="center" nowrap>担当者CD</TD>
	      <TD class="tdnone2" width="80" align="center" nowrap>担当者名</TD>
	      <TD class="tdnone2" width="75" align="center" nowrap>期限</TD>
	      <TD class="tdnone2" width="100" align="center" nowrap>実施内容</TD>
	      <TD class="tdnone2" width="75" align="center" nowrap>実施日</TD>
	      <TD class="tdnone2" width="100" align="center" nowrap>結果</TD>
	      <TD class="tdnone2" width="60" align="center" nowrap>有効性</TD>
	      <TD class="tdnone5" width="40" align="center" nowrap>ｱｸｼｮﾝ</TD>
	    </TR>
	<?php
		}
	?>
	    <TR class="<?php echo $strClass; ?>" align="center" >
	    	<TD class="<?php echo $strClass; ?>">
	    		<?php echo $i+1; ?>
	    	</TD>
	    	 <TD class="<?php echo $strClass; ?>">
	      		<textarea class="<?php echo $strClass2; ?>" name="txtRequest[]" id="txtRequest[]" style="width:150px;height:70px;" ><?php echo $aParaD[$i][1]; ?></textarea>
	      	</TD>
	    	<TD class="<?php echo $strClass; ?>">
	      		<INPUT size="4" type="text" class="<?php echo $strClass2; ?>" name="txtTantoCd[]" id="txtTantoCd[]" maxlength=5 value="<?php echo $aParaD[$i][2]; ?>" style="ime-mode:disabled;width:50px;height:20px;">
	      		<INPUT type="button" name="btnTantoC" id="btnTantoC" value="" onClick="fOpenSearchArray('F_MSK0030','txtTantoCd[]','txtTantoNm[]','','0','1',this)">
	      	</TD>
	      	<TD class="<?php echo $strClass; ?>"  >
	      		<INPUT size="8" type="text"  class="textboxdisp" name="txtTantoNm[]" id="txtTantoNm[]" readOnly value="<?php echo $aParaD[$i][3]; ?>" style="width:70px;height:20px;">
	      	</TD>

			<TD class="<?php echo $strClass; ?>"  >
		      	<INPUT size="6" type="text" class="<?php echo $strClass; ?>" name="txtLimitYmd[]" id="txtLimitYmd[]"  maxlength="10" value="<?php echo $module_cmn->fChangDateFormat($aParaD[$i][4]); ?>" style="ime-mode:disabled;width:70px;height:20px;">
		    </TD>
		    <TD class="<?php echo $strClass; ?>" align="center" >
		      	<textarea cols="18" rows="4" class="<?php echo $strClass2; ?>" name="txtOpeMat[]" id="txtOpeMat[]" style="width:150px;height:70px;"><?php echo $aParaD[$i][5]; ?></textarea>
		    </TD>
			<TD class="<?php echo $strClass; ?>"  >
		      	<INPUT size="6" type="text" class="<?php echo $strClass; ?>" name="txtOpeYmd[]" id="txtOpeYmd[]"  maxlength="10" value="<?php echo $module_cmn->fChangDateFormat($aParaD[$i][6]); ?>" style="ime-mode:disabled;width:70px;height:20px;">
		    </TD>
			<TD class="<?php echo $strClass; ?>" align="center" >
		    	<textarea cols="18" rows="4" class="<?php echo $strClass2; ?>" name="txtResult[]" id="txtResult[]" style="width:150px;height:70px;"><?php echo $aParaD[$i][7]; ?></textarea>
		    </TD>

		    <TD class="<?php echo $strClass; ?>" align="center" >
		    	<SELECT name="cmbActVal[]" id="cmbActVal[]" >
      				<OPTION selected value="-1" >▼選択</OPTION>
        			<?php $module_sel->fMakeCombo('C14',$aParaD[$i][8]); ?>
      			</SELECT>
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
		$strClass = "tdnone3_60";
		$strClass2 = "tdnone3_60";
		//支払日のデフォルト値を設定する(対象年月の15日)
		//$txtShiharaiYmd = $module_cmn->fGetMonthAgo(date("Y"),date("m"),"15",0);
		//$txtShiharaiYmd = $sTaishoYm."/15";

	?>
		<TR class="tdnone3" >
		  <TD class="tdnone2" width="45" align="center" nowrap>NO</TD>
	      <TD class="tdnone2" width="100" align="center" nowrap>要望事項</TD>
	      <TD class="tdnone2" width="60" align="center" nowrap>担当者CD</TD>
	      <TD class="tdnone2" width="80" align="center" nowrap>担当者名</TD>
	      <TD class="tdnone2" width="75" align="center" nowrap>期限</TD>
	      <TD class="tdnone2" width="100" align="center" nowrap>実施内容</TD>
	      <TD class="tdnone2" width="75" align="center" nowrap>実施日</TD>
	      <TD class="tdnone2" width="100" align="center" nowrap>結果</TD>
	      <TD class="tdnone2" width="60" align="center" nowrap>有効性</TD>
	      <TD class="tdnone5" width="40" align="center" nowrap>ｱｸｼｮﾝ</TD>
	    </TR>
		<TR class="<?php echo $strClass; ?>" >
	    	<TD class="<?php echo $strClass; ?>" height="60">
	    		<?php echo 1; ?>
	    	</TD>
	    	<TD class="<?php echo $strClass; ?>">
	      		<textarea  class="<?php echo $strClass2; ?>" name="txtRequest[]" id="txtRequest[]" style="width:150px;height:70px;"></textarea>
	   	   	</TD>
	      	<TD class="<?php echo $strClass; ?>">
	      		<INPUT size="4" type="text" class="<?php echo $strClass2; ?>" name="txtTantoCd[]" id="txtTantoCd[]" maxlength=5 value="" style="ime-mode:disabled;width:50px;height:20px;">
	      		<INPUT type="button" name="btnTantoC" id="btnTantoC" value="" onClick="fOpenSearchArray('F_MSK0030','txtTantoCd[]','txtTantoNm[]','','0','1',this)">
	      	</TD>
	      	<TD class="<?php echo $strClass; ?>">
	      		<INPUT size="8" type="text"  class="textboxdisp" name="txtTantoNm[]" id="txtTantoNm[]" readOnly value="" style="width:70px;height:20px;">
	      	</TD>
	      	<TD class="<?php echo $strClass; ?>">
	      		<INPUT size="6" type="text" class="<?php echo $strClass2; ?>" name="txtLimitYmd[]" id="txtLimitYmd[]" value="" style="ime-mode: disabled;width:70px;height:20px;">
	      	</TD>
	    	<TD class="<?php echo $strClass; ?>">
	      		<textarea  class="<?php echo $strClass2; ?>" name="txtOpeMat[]" id="txtOpeMat[]" style="width:150px;height:70px;"></textarea>
	   	   	</TD>
	   	   	<TD class="<?php echo $strClass; ?>">
	      		<INPUT size="6" type="text" class="<?php echo $strClass2; ?>" name="txtOpeYmd[]" id="txtOpeYmd[]" value="" style="ime-mode: disabled;width:70px;height:20px;">
	      	</TD>
	      	<TD class="<?php echo $strClass; ?>">
	      		<textarea  class="<?php echo $strClass2; ?>" name="txtResult[]" id="txtResult[]" style="width:150px;height:70px;"></textarea>
	   	   	</TD>
		    <TD class="<?php echo $strClass; ?>" align="center" >
		    	<SELECT name="cmbActVal[]" id="cmbActVal[]" >
      				<OPTION selected value="-1" >▼選択</OPTION>
        			<?php $module_sel->fMakeCombo('C14',""); ?>
      			</SELECT>
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


<br>

<?php

//品証のユーザのみ表示
if(substr($_SESSION['login'][2],0,3) == "117"){

?>

	<INPUT type='button' name='btnAddRow' value='行追加' onClick='fncInsertRow(this)'>
<!--	<INPUT type='button' name='btnExcelOut' value='Excel出力' onClick='fncExcelOut()'>-->
	<INPUT type="button" name="btnExcute" value="　確　定　" onclick="fncExcute()" >
	<INPUT type="reset" name="btnReset" value="　リセット　">
	<input type="hidden" name="hidExcute" value="0">
	<input type="hidden" name="hidTantoCd" value="">
	<input type="hidden" name="hidUCount" value="<?php echo $hidUCount; ?>">
	<input type="hidden" name="hidCount" value="<?php echo $hidCount; ?>">
	<input type="hidden" name="hidDenpyoKbn" value="0">

<?php
}
?>

</div>

<br>
</FORM>
</BODY>
</HTML>
