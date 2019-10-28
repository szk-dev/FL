<?php

	//****************************************************************************
	//プログラム名：不具合入力
	//プログラムID：F_FLK0010
	//作成者　　　：㈱鈴木　西村
	//作成日　　　：2012/05/30
	//履歴　　　　：2015/06/05 要望により登録後に画面項目クリア 久米
	//              2015/06/26 要望により異常品暫定処置項目を追加　久米
	//				2016/09/02 要望により連絡受理日を追加　久米
	//				           回答日と回答者CDの片方のみ入力時エラー追加
	//				2017/09/15 メールリンクから直接表示対応 久米
	//				2018/06/05 添付資料に「不具合報告書」を追加 久米
	//				2019/04/01 不具合区分をSMART2から取得 藤田
	//				2019/07/06 品証担当者CDを追加 久米
	//
	//****************************************************************************

	/* 現在のキャッシュリミッタを取得または設定する */
	session_cache_limiter('private, must-revalidate');
	/* セッション開始 */
	session_start();

	//遷移元(0:フレーム内/1:フレーム外)
	$hidFrame = 0;
	
	//メールリンクで開く場合
	if(!empty($_REQUEST['rrcno'])){
		
		$strReference_No = $_REQUEST['rrcno'];
		//フレーム外
		$hidFrame = 1;
		
	}else{
		$hidFrame = $_POST['hidFrame'];
	}
	
	//セッションチェック
	if(empty($_SESSION["login"][0])){
		//セッションが空の場合はエラー画面へ遷移
		//header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/F_CMN0010.php?page=".$_SERVER['REQUEST_URI']);
		exit;
    }

	$token = sha1(uniqid(mt_rand(), true));

	// トークンをセッションに追加する
	$_SESSION['token'][] = $token;

	//ファイル読み込み
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
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

	//引数の取得
	if(isset($_GET['mode'])) {
		$mode = $_GET['mode'];
	}
	if(isset($_GET['save'])) {
		$save = $_GET['save'];
	}
	//引数の取得(整理NO)
	if(isset($_GET['strRrceNo'])) {
		$strReference_No = $_GET['strRrceNo'];

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

	//画面項目の取得
	//引数の取得
	$txtReference_No = $_POST['txtReference_No'];				//整理NO

	//進捗状態(初期は0(受付))
	if(isset($_POST['txtProgres_Stage'])) {
		$txtProgres_Stage = $_POST['txtProgres_Stage'];			//進捗状態
	}else{
		$txtProgres_Stage = "0";								//進捗状態
	}

	$txtContact_Accept_YMD = $_POST['txtContact_Accept_YMD'];	//連絡受理日 2016/09/02 add k.kume

	$txtProd_CD = $_POST['txtProd_CD'];							//製品CD
	$txtProd_NM = $_POST['txtProd_NM'];							//製品名
	$txtDRW_NO = $_POST['txtDRW_NO'];							//仕様番号
//	$txtModel = $_POST['txtModel'];								//型式
	$txtDie_NO = $_POST['txtDie_NO'];							//金型番号
	$txtLot_NO = $_POST['txtLot_NO'];							//ロットNO
	$txtCust_Manage_No = $_POST['txtCust_Manage_No'];			//顧客管理NO
	$txtCust_CD = $_POST['txtCust_CD'];							//顧客CD
	$txtCust_NM = $_POST['txtCust_NM'];							//顧客名
	$txtCust_Officer = $_POST['txtCust_Officer'];				//顧客担当者
	$cmbCust_Contact_KBN = $_POST['cmbCust_Contact_KBN'];		//客先よりの連絡方法
	$cmbRecept_KBN = $_POST['cmbRecept_KBN'];					//受付区分
	$cmbFlaw_KBN = $_POST['cmbFlaw_KBN'];						//不具合区分
	
	$txtPc_Tanto_CD = $_POST['txtPc_Tanto_CD'];					//品証担当者CD 2019/07/06 追加 k.kume
	$txtPc_Tanto_Nm = $_POST['txtPc_Tanto_Nm'];					//品証担当名 2019/07/06 追加 k.kume
	
	$txtTarget_QTY = $_POST['txtTarget_QTY'];					//対象数量
	$cmbTarget_Section_KBN = $_POST['cmbTarget_Section_KBN'];	//対象部門
//	$txtProduct_Ka_CD = $_POST['txtProduct_Ka_CD'];				//生産担当部門CD
//	$txtProduct_Ka_NM = $_POST['txtProduct_Ka_NM'];				//生産担当部門名
	$cmbProduct_Out_Ka_CD = $_POST['cmbProduct_Out_Ka_CD'];		//発生起因部署
	$cmbCheck_Out_Ka_CD1 = $_POST['cmbCheck_Out_Ka_CD1'];		//流出起因部署1
	$cmbCheck_Out_Ka_CD2 = $_POST['cmbCheck_Out_Ka_CD2'];		//流出起因部署2
	$txtFlaw_Contents = $_POST['txtFlaw_Contents'];				//不具合内容
	$txtReturn_QTY = $_POST['txtReturn_QTY'];					//返却数量
	$txtBat_QTY = $_POST['txtBat_QTY'];							//不良数量
	$cmbReturn_Disposal = $_POST['cmbReturn_Disposal'];			//返却品処理
	$cmbResult_KBN = $_POST['cmbResult_KBN'];					//結果区分
	$txtCust_Ap_Ans_YMD = $_POST['txtCust_Ap_Ans_YMD'];			//顧客指定回答日

	$chkCustAns = $_POST['chkCustAns'];							//顧客指定回答不要チェック



	$txtAns_YMD = $_POST['txtAns_YMD'];							//回答日
	$txtMeasures_YMD = $_POST['txtMeasures_YMD'];				//対策日


	$cmbEffectAlert = $_POST['cmbEffectAlert'];					//効果確認通知有無
	$txtEffectConfirm_YMD = $_POST['txtEffectConfirm_YMD'];		//対策効果確認日


	$txtAns_Tanto_CD = $_POST['txtAns_Tanto_CD'];				//回答者CD
	$txtAns_Tanto_Nm = $_POST['txtAns_Tanto_Nm'];				//回答者名
	$txtIssue_YMD1 = $_POST['txtIssue_YMD1'];					//発行日(不具合連絡書)
	$txtIssue_YMD2 = $_POST['txtIssue_YMD2'];					//発行日(品質異常改善通知書)
	$txtIssue_YMD3 = $_POST['txtIssue_YMD3'];					//発行日(不良品連絡書)
	$cmbIncident_KBN = $_POST['cmbIncident_KBN'];				//発行先区分
	$txtIncident_CD1 = $_POST['txtIncident_CD1'];				//発行先CD(社内)
	$txtIncident_NM1 = $_POST['txtIncident_NM1'];				//発行先名称(社内)
	$txtPc_Ap_Ans_YMD1 = $_POST['txtPc_Ap_Ans_YMD1'];			//品証指定回答日(社内)
	$txtReturn_YMD1 = $_POST['txtReturn_YMD1'];					//返却日(社内)
	$txtComplete_YMD1 = $_POST['txtComplete_YMD1'];				//完結日(社内)
	$txtConfirm_Tanto_CD1 = $_POST['txtConfirm_Tanto_CD1'];		//確認者(社内)
	$txtConfirm_Tanto_Nm1 = $_POST['txtConfirm_Tanto_Nm1'];		//確認者名(社内)
	$txtRemarks1 = $_POST['txtRemarks1'];						//備考(社内)
	$txtProduct_Officer_NM = $_POST['txtProduct_Officer_NM'];	//担当者名
	$txtIncident_CD2 = $_POST['txtIncident_CD2'];				//発行先CD(協工)
	$txtIncident_NM2 = $_POST['txtIncident_NM2'];				//発行先名称(協工)
	$txtPc_Ap_Ans_YMD2 = $_POST['txtPc_Ap_Ans_YMD2'];			//品証指定回答日(協工)
	$txtReturn_YMD2 = $_POST['txtReturn_YMD2'];					//返却日(協工)
	$txtComplete_YMD2 = $_POST['txtComplete_YMD2'];				//完結日(協工)
	$txtConfirm_Tanto_CD2 = $_POST['txtConfirm_Tanto_CD2'];		//確認者(協工)
	$txtConfirm_Tanto_Nm2 = $_POST['txtConfirm_Tanto_Nm2'];		//確認者名(協工)
	$txtRemarks2 = $_POST['txtRemarks2'];						//備考(協工)

	//項目追加 2015/06/26 k.kume
	$cmbQuick_Fix_CD = $_POST['cmbQuick_Fix_CD'];				//異常品暫定処置

	//品証担当者追加 2019/07/06 k.kume
	$txtPc_Tanto_CD = $_POST['txtPc_Tanto_CD'];					//品証担当者CD
	$txtPc_Tanto_Nm = $_POST['txtPc_Tanto_Nm'];					//品証担当者名
	
	
	$txtProgres_Stage_Nm = $_POST['txtProgres_Stage_Nm'];
	$hidUCount = $_POST['hidUCount'];

	$hidUp = $_POST['hidUp'];


	//オブジェクトロック用変数
	$strLock = "";

	//メッセージ用変数
	$strMsg = "";
	$strErrMsg = "";

	//ボタン表示フラグ(表示:true,非表示:false)
	$bDispflg = true;


	//モード用変数
	$modeN = "";
	//モードの取得
	if($mode == "1"){
		$modeN ="(登録)";

		//品証指定回答日の初期値(稼動日５日後)を取得
		$txtPc_Ap_Ans_YMD1 = $module_cmn->fChangDateFormat($module_sel->fLimitCalender(5));

	}elseif($mode == "2"){
		$modeN ="(更新)";
		//整理NO取得
		$txtReference_No = $strReference_No;

		//対象部門
		$strTargetSecRO = "readOnly";

	}elseif($mode == "3"){
		$modeN ="(削除)";
		//整理NO取得
		$txtReference_No = $strReference_No;
		//対象部門
		$strTargetSecRO = "readOnly";

	}elseif($mode == "4"){
		$modeN ="(参照)";
		//整理NO取得
		$txtReference_No = $strReference_No;
		//対象部門
		$strTargetSecRO = "readOnly";

	}else{
		//header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/F_CMN0010.php?page=".$_SERVER['PHP_SELF']);
		
		exit;
	}

//	echo $strReference_No;

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
			    //header("Location: err.php");
			}

			//整理NOの取得
			$txtReference_No = $_POST['txtReference_No'];

			//チェック処理
			//セッションチェック(セッションが書き換えられていないか)
			if($_POST['hidTantoCd'] != $_SESSION['login'][0] && $hidFrame == 0){
				$strErrMsg = $module_sel->fMsgSearch("E034","");
			}

			//必須チェック
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtContact_Accept_YMD'],"連絡受理日");		//連絡受理日 2016/09/02 add k.kume
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtProd_CD'],"製品CD");						//製品CD
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtDie_NO'],"金型番号");					//金型番号
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtLot_NO'],"ロットNO");					//ロットNO
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtCust_CD'],"顧客CD");						//顧客CD
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtCust_Officer'],"顧客担当者");			//顧客担当者
			$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbCust_Contact_KBN'],"客先よりの連絡方法");	//客先よりの連絡方法
			$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbRecept_KBN'],"受付区分");				//受付区分
			$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbFlaw_KBN'],"不具合区分");				//不具合区分
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtFlaw_Contents'],"不具合内容");			//不具合内容
			
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtPc_Tanto_CD'],"品証担当者CD");			//品証担当者CD
			
			//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtCust_Ap_Ans_YMD'],"顧客指定回答日");		//顧客指定回答日

			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtTarget_QTY'],"対象数量");				//対象数量
			if($mode == "1"){
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbTarget_Section_KBN'],"対象部門");	//対象部門
			}
			$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbIncident_KBN'],"発行先区分");			//発行先区分
			$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbEffectAlert'],"効果確認期限通知");		//効果確認期限通知

			//文字数チェック
			$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($_POST['txtFlaw_Contents'],250,"不具合内容");			//不具合内容


			//効果確認期限通知がメール通知するで回答日が入っていたら対策日は必須
			if($_POST['cmbEffectAlert'] == "1" && $_POST['txtAns_YMD'] <> "" ){
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtMeasures_YMD'],"対策日");
			}

			//発行先が社内(0)
			if($_POST['cmbIncident_KBN'] == "0"){
				//発行先が社内の場合必須チェックを行う
				//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtProduct_Officer_NM'],"生産担当者");		//生産担当者(発行先が社内の場合必須)
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtIncident_CD1'],"発行先CD(社内)");			//発行先CD(社内)
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtPc_Ap_Ans_YMD1'],"品証指定回答日(社内)");	//品証指定回答日(社内)
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbProduct_Out_Ka_CD'],"発生起因部署");			//生産流出(発行先が社内の場合必須)
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbCheck_Out_Ka_CD1'],"流出起因部署1");			//検査流出1(発行先が社内の場合必須)
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbCheck_Out_Ka_CD2'],"流出起因部署2");			//検査流出2(発行先が社内の場合必須)

				//項目追加 2015/06/26 k.kume
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbQuick_Fix_CD'],"異常品暫定処置");			//異常品暫定処置(発行先が社内の場合必須)

			}
			//発行先が協工(1)の場合の必須チェック
			elseif($_POST['cmbIncident_KBN'] == "1"){
				//発行先が社内の場合必須チェックを行う
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtIncident_CD2'],"発行先CD(協工)");			//発行先CD(協工)
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtPc_Ap_Ans_YMD2'],"品証指定回答日(協工)");	//品証指定回答日(協工)

			}
			//発行先が社内/協工(2)の場合の必須チェック
			elseif($_POST['cmbIncident_KBN'] == "2"){
				//発行先が社内の場合必須チェックを行う
				//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtProduct_Officer_NM'],"生産担当者");				//生産担当者(発行先が社内の場合必須)
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtIncident_CD1'],"発行先CD(社内)");			//発行先CD(社内)
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtPc_Ap_Ans_YMD1'],"品証指定回答日(社内)");	//品証指定回答日(社内)
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtIncident_CD2'],"発行先CD(協工)");			//発行先CD(協工)
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtPc_Ap_Ans_YMD2'],"品証指定回答日(協工)");	//品証指定回答日(協工)
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbProduct_Out_Ka_CD'],"発生起因部署");		//生産流出(発行先が社内の場合必須)
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbCheck_Out_Ka_CD1'],"流出起因部署1");			//検査流出1(発行先が社内の場合必須)
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbCheck_Out_Ka_CD2'],"流出起因部署2");			//検査流出2(発行先が社内の場合必須)

				//項目追加 2015/06/26 k.kume
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbQuick_Fix_CD'],"異常品暫定処置");			//異常品暫定処置(発行先が社内の場合必須)

			}//発行先が発行先なしの場合の入力チェック
			elseif($_POST['cmbIncident_KBN'] == "3"){

			}



//			if(trim($_POST['cmbResult_KBN']) <> "-1" || $_POST['txtAns_YMD'] <> "" || $_POST['txtAns_Tanto_CD'] <> ""){
//				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbResult_KBN'],"結果区分");				//結果区分(結果区分，回答日，回答者CDはいづれか未入力はエラー)
//				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtAns_YMD'],"回答日");					//回答日(結果区分，回答日，回答者CDはいづれか未入力はエラー)
//				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtAns_Tanto_CD'],"回答者CD");				//回答者(結果区分，回答日，回答者CDはいづれか未入力はエラー)
//			}

			//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtComplete_YMD'],"完結日");				//完結日(※完結日，確認者CDはいづれか未入力はエラー)
			//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtConfirm_Tanto_CD'],"確認者");			//確認者(※完結日，確認者CDはいづれか未入力はエラー)

			//必須チェックでエラーがなければフォーマットチェック
			if($strErrMsg == ""){
			//フォーマットチェック
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtTarget_QTY'],10,3,false,true,"対象数量");						//対象数量
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtReturn_QTY'],10,3,false,true,"返却数量");						//返却数量
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtBat_QTY'],10,3,false,true,"不良数量");						//不良数量
//				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtCust_Accept_YMD']),"顧客了承回答日");			//顧客了承回答日

				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtContact_Accept_YMD']),"連絡受理日");			//連絡受理日 2016/09/02 add k.kume

				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtAns_YMD']),"回答日");							//回答日
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtPc_Ap_Ans_YMD']),"顧客指定回答日");			//顧客指定回答日

				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtPc_Ap_Ans_YMD1']),"品証指定回答日(社内)");	//品証指定回答日(社内)
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtReturn_YMD1']),"返却日(社内)");				//返却日(社内)
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtComplete_YMD1']),"完結日(社内)");				//完結日(社内)

				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtPc_Ap_Ans_YMD2']),"品証指定回答日(協工)");	//品証指定回答日(協工)
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtReturn_YMD2']),"返却日(協工)");				//返却日(協工)
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtComplete_YMD2']),"完結日(協工)");				//完結日(協工)


			}


			//フォーマットチェックでエラーがなければ整合性チェック
			if($strErrMsg == ""){
				//両方未設定
				if($_POST['txtCust_Ap_Ans_YMD'] == "" && $_POST['chkCustAns'] == ""){

					$strErrMsg = $strErrMsg.$module_sel->fMsgSearch("E040","");
				}
				//両方設定
				if($_POST['txtCust_Ap_Ans_YMD'] != "" && $_POST['chkCustAns'] == "1"){

					$strErrMsg = $strErrMsg.$module_sel->fMsgSearch("E040","");
				}
				//対策効果確認日が入っている場合は対策日は必須
				if($_POST['txtEffectConfirm_YMD'] <> "" && $_POST['txtMeasures_YMD'] == "" ){
					$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtMeasures_YMD'],"対策日");
				}

				//対策効果確認日と対策日で整合性チェック
				if($_POST['txtEffectConfirm_YMD'] <> "" && $_POST['txtMeasures_YMD'] <> "" ){
					if($_POST['txtEffectConfirm_YMD'] < $_POST['txtMeasures_YMD']){
						$strErrMsg = $strErrMsg.$module_sel->fMsgSearch("E013","「対策日」「対策効果確認日」");
					}
				}

				//回答日・回答者CDのどちらか片方のみの入力はエラーとする 2016/09/02 k.kume
				if($_POST['txtAns_YMD'] <> "" && $_POST['txtAns_Tanto_CD'] == "" ){
					$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtAns_Tanto_CD'],"回答者CD");
				}
				if($_POST['txtAns_Tanto_CD'] <> "" && $_POST['txtAns_YMD'] == "" ){
					$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtAns_YMD'],"回答日");
				}

			}



			//整合性チェックでエラーがなければ存在チェック
			if($strErrMsg == ""){
				//存在チェック
				//製品_CDは諸口以外のデータをチェック
				if($_POST['txtProd_CD'] == 0){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtProd_CD'],"製品CD","V_FL_TANTO_INFO","C_TANTO_CD");
				}

				//顧客CD
				$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtCust_CD'],"顧客CD","V_FL_CUST_INFO","C_CUST_CD");

				//品証担当者
				if(trim($_POST['txtPc_Tanto_CD']) <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtPc_Tanto_CD'],"品証担当者CD","V_FL_TANTO_INFO","C_TANTO_CD");
				}
				
				//発行先CD(社内)
				if(trim($_POST['txtIncident_CD1']) <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtIncident_CD1'],"発行先CD(社内)","V_FL_CUST_INFO","C_CUST_CD");
				}
				//発行先CD(協工)
				if(trim($_POST['txtIncident_CD2']) <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtIncident_CD2'],"発行先CD(協工)","V_FL_CUST_INFO","C_CUST_CD");
				}

				//確認者
				if(trim($_POST['txtAns_Tanto_CD']) <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtAns_Tanto_CD'],"回答者CD","V_FL_TANTO_INFO","C_TANTO_CD");
				}

				//確認者(社内)
				if(trim($_POST['txtConfirm_Tanto_CD1']) <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtConfirm_Tanto_CD1'],"確認者CD(社内)","V_FL_TANTO_INFO","C_TANTO_CD");
				}

				//確認者(協工)
				if(trim($_POST['txtConfirm_Tanto_CD2']) <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtConfirm_Tanto_CD2'],"確認者CD(協工)","V_FL_TANTO_INFO","C_TANTO_CD");
				}



			}


			//データ存在チェックでエラーがなければ更新回数チェック(登録以外のみ)
			if($strErrMsg == ""  && $mode <> "1"){
				//更新回数チェック
				if(!$module_sel->fKoshinCheck($_POST['txtReference_No'],$_POST['hidUCount'],"不具合入力","T_TR_FLAW","C_REFERENCE_NO")){
					$strErrMsg = $module_sel->fMsgSearch("E002","整理NO:".$_POST['txtReference_No']);
				}
			}


			//エラーメッセージがなければ更新処理を実行
			if($strErrMsg == ""){
				//更新処理戻り値用変数(整理ＮＯが入る)
				$aExcutePara = array();

				//Oracleへの接続の確立(トランザクション開始)
				$conn = $module_upd->fTransactionStart();
				$Reference_No = $_POST['txtReference_No'];
				//更新処理
				//$aExcutePara = $module_upd->fKonyuExcute($conn,$mode,$save,$txtReference_No,$_SESSION['login'],$_POST['hidUCount']);
				$aExcutePara = $module_upd->fFlawTorokuExcute($conn,$mode,$Reference_No,$_SESSION['login'],$_POST['hidUCount']);


				//更新処理の結果判断
				if( substr($aExcutePara[0],0,3) <> "err"){

					//不具合登録のみメールを送信する。
					if($mode == '1'){

						//iniファイルの読み込み
						// セクションを意識してパースします。
						$aIni = parse_ini_file("ini/FL.ini", true);

						//iniから取得
						if($aIni){
							$strMailServer = "";
							//出力先パス、ファイル名取得
							$strMailServer = $aIni['FL_INI']['MAIL_SERVER'];

						}


						//送信先メールアドレス取得
						//引数1･･･対象部門(F or M or K)
						//引数2･･･送信先対象CD

						//宛先
						$to = $module_sel->fMailAddressGet($_POST['cmbTarget_Section_KBN'],"");

						//取得アドレスが存在した場合
						if($to != ""){

							$aTo = array();
							//アドレスを配列に格納
							$aTo = explode(",",$to);

							//メール送信者
							$senderAddress = "announce@suzukinet.co.jp";

							$senderName = "不具合管理システム";

							//メール件名
							//$messageSubject = "【不具合管理自動送信メール】不具合情報登録通知";
							$messageSubject = "【品質管理自動送信メール】不具合情報登録通知";
							
							//メール本文作成
							$messageBody = "\nこのメールは自動配信メールです。\n";
							$messageBody = $messageBody."このメールには返信しないで下さい。\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."新しい不具合情報が登録されました。\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."整理NO：".$aExcutePara[0]."\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."得意先名：".$_POST['txtCust_NM']."\n";
							$messageBody = $messageBody."\n";
							switch($_POST['cmbTarget_Section_KBN']){
								case 'F':
									$messageBody = $messageBody."対象部門：コネクタ\n";
									break;
								case 'M':
									$messageBody = $messageBody."対象部門：モールド\n";
									break;
								case 'K':
									$messageBody = $messageBody."対象部門：めっき\n";
									break;
								default:
									$messageBody = $messageBody."対象部門：不明\n";
							}
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."製品名：".$_POST['txtProd_NM']."\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."仕様番号：".$_POST['txtDRW_NO']."\n";
							$messageBody = $messageBody."\n";
		//					$messageBody = $messageBody."型式：".$_POST['txtModel']."\n";
		//					$messageBody = $messageBody."\n";
							$messageBody = $messageBody."不具合内容：\n".$_POST['txtFlaw_Contents']."\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."対象数量：".number_format($_POST['txtTarget_QTY'])."\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."発行先名称(社内)：".$_POST['txtIncident_NM1']."\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."品証指定回答日(社内)：".$_POST['txtPc_Ap_Ans_YMD1']."\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."発行先名称(協工)：".$_POST['txtIncident_NM2']."\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."品証指定回答日(協工)：".$_POST['txtPc_Ap_Ans_YMD2']."\n";
							$messageBody = $messageBody."\n";
							$messageBody = $messageBody."登録日時：".date('Y年m月d日　H時i分s秒')."\n";
							$messageBody = $messageBody.$msg."\n";
							//$messageBody = $messageBody."(不具合管理システム)----> http://".$_SERVER["SERVER_NAME"]."/FL/ \n";
							$messageBody = $messageBody."(不具合管理システム)----> http://".php_uname('n')."/FL/F_FLK0010.php?mode=2&rrcno=".$aExcutePara[0]." \n";
							$messageBody = $messageBody.$msg."\n";


/* 							$mail = new JPHPMailer();   //文字コード設定

							//SMTP接続
							$mail->IsSMTP();
							$mail->SMTPAuth = false;
							//$mail->SMTPDebug = 2;
							$mail->SMTPSecure = 'tls';
							$mail->Host = $strMailServer;
							$mail->Port = 25;
							//$mail->Username = ''; 		//アカウント名
							//$mail->Password = ''; 		//パスワード
							$mail->in_enc = "UTF-8"; 
*/
							//SMTP接続
							$mail = new PHPMailer();
							$mail->isSMTP();
							$mail->SMTPAuth = false;
							$mail->SMTPSecure = false;
							$mail->SMTPAutoTLS = false;
							
							$mail->Host = $strMailServer;
							$mail->Port = 25;
							$mail->CharSet = "UTF-8";

							$n = 0;
							while($n < count($aTo)){
								//$mail->addTo($aTo[$n]);
								$mail->addAddress($aTo[$n]);
								$n++;
							}

							$mail->setFrom($senderAddress,$senderName);
							//$mail->setSubject($messageSubject );
							//$mail->setBody($messageBody);   //添付ファイル
							//$mail->addAttachment($attachfile);
							$mail->Subject = $messageSubject;
							$mail->Body = $messageBody;
							//通知メール送信処理
							if ($mail->send()){
								//echo "送信されました。";
							}else{
								//トランザクション処理とOracle切断
								$module_upd->fTransactionEnd($conn,false);

								$strMsg = "";
								//$strErrMsg = $module_sel->fMsgSearch("E026",$mail->getErrorMessage());	//メール送信に失敗しました
								$strErrMsg = $module_sel->fMsgSearch("E026",$mail->ErrorInfo);	//メール送信に失敗しました
							}
						}
						//更新した整理NOを戻す
						$txtReference_No = $aExcutePara[0];

						//トランザクション処理とOracle切断
						$module_upd->fTransactionEnd($conn,true);
						$strMsg = $module_sel->fMsgSearch("N001","整理NO:".$txtReference_No);	//登録しました
						//整理NOクリア
						$txtReference_No = "";
					}else{
						//更新した整理NOを戻す
						$txtReference_No = $aExcutePara[0];
						//トランザクション処理とOracle切断
						$module_upd->fTransactionEnd($conn,true);
						if($mode == '2'){
							$strMsg = $module_sel->fMsgSearch("N002","整理NO:".$txtReference_No);	//更新しました
						}else{
							$strMsg = $module_sel->fMsgSearch("N003","整理NO:".$txtReference_No);	//削除しました
						}
					}
				}else{
					//トランザクション処理とOracle切断
					$module_upd->fTransactionEnd($conn,false);

					//メッセージIDが入っていたらエラー表示
					if($aExcutePara[1] <> ""){
						$strErrMsg = $module_sel->fMsgSearch($aExcutePara[1],"");
					}
				}


			}
		}
	}

	//登録モード以外はデータ取得を行う
	if($mode <> "1" && $txtReference_No <> "" && $strErrMsg == ""){
		//echo $strReference_No;
		//再検索処理
		
		$aPara = $module_sel->fGetFlawData($txtReference_No);

		$txtReference_No 		= $aPara[0]; 	//整理NO
		$txtProgres_Stage 		= $aPara[1]; 	//進捗状態
		$txtCust_CD 			= $aPara[2]; 	//顧客CD
		$txtCust_NM 			= $aPara[3]; 	//顧客名
		$txtCust_Officer 		= $aPara[4]; 	//顧客担当者
		$txtCust_Manage_No 			= $aPara[43]; 	//顧客管理NO
		$cmbCust_Contact_KBN 	= $aPara[5]; 	//客先よりの連絡方法
		$cmbRecept_KBN 			= $aPara[6]; 	//受付区分
		$cmbFlaw_KBN 			= $aPara[7]; 	//
		
		$txtTarget_QTY 			= number_format($aPara[8]); 	//対象数量
		$cmbTarget_Section_KBN 	= $aPara[9]; 	//対象部門
		$txtProd_CD 			= $aPara[10]; 	//製品CD
		$txtProd_NM 			= $aPara[11]; 	//製品名
		$txtDRW_NO 				= $aPara[12]; 	//図番
		//		$txtModel 				= $aPara[13]; 	//型式
		$txtDie_NO 				= $aPara[14]; 	//金型番号
		$txtLot_NO 				= $aPara[15]; 	//ロットNO
		$cmbIncident_KBN 		= $aPara[16]; 	//発行先
		$txtIncident_CD1 		= $aPara[18]; 	//発行先CD(社内)
		$txtIncident_CD2 		= $aPara[19]; 	//発行先CD(協工)
		$txtProduct_Officer_NM 	= $aPara[20]; 	//生産担当者
		$cmbProduct_Out_Ka_CD 	= $aPara[21]; 	//生産流出
		$cmbCheck_Out_Ka_CD1 	= $aPara[22]; 	//検査流出1
		$cmbCheck_Out_Ka_CD2 	= $aPara[46]; 	//検査流出2

		$txtFlaw_Contents 		= $aPara[23]; 	//不具合内容
		$txtReturn_QTY 			= number_format($aPara[24]); 	//返却数量
		$txtBat_QTY 			= number_format($aPara[25]); 	//不良数量
		$cmbReturn_Disposal 	= $aPara[26]; 	//返却品処理
		$cmbResult_KBN 			= $aPara[27]; 	//結果区分
		$txtCust_Ap_Ans_YMD 	= $module_cmn->fChangDateFormat($aPara[28]); 	//顧客指定回答日

		//顧客指定回答日が0の場合は回答不要にチェックつける
		if($txtCust_Ap_Ans_YMD == 0){
			$chkCustAns = "1";
		}


		//		$txtCust_Accept_YMD 	= $module_cmn->fChangDateFormat($aPara[29]); 	//顧客了承回答日
		$txtAns_YMD 			= $module_cmn->fChangDateFormat($aPara[30]); 	//回答日
		$txtMeasures_YMD 		= $module_cmn->fChangDateFormat($aPara[57]); 	//対策日

		$txtAns_Tanto_CD 		= $aPara[31]; 	//回答者CD

		$cmbEffectAlert 		= $aPara[55]; 	//効果確認通知有無
		$txtEffectConfirm_YMD 	= $module_cmn->fChangDateFormat($aPara[56]); 	//対策効果確認日

		$txtAns_Tanto_Nm 		= $aPara[32]; 	//回答者名
		$txtIssue_YMD1 			= $module_cmn->fChangDateFormat($aPara[33]); 	//発行日
		$txtIssue_YMD2 			= $module_cmn->fChangDateFormat($aPara[44]); 	//発行日
		$txtIssue_YMD3 			= $module_cmn->fChangDateFormat($aPara[45]); 	//発行日

		$txtIncident_NM1 		= $aPara[17]; 										//発行先名称(社内)
		$txtPc_Ap_Ans_YMD1		= $module_cmn->fChangDateFormat($aPara[34]); 		//品証指定回答日(社内)
		$txtReturn_YMD1 		= $module_cmn->fChangDateFormat($aPara[35]); 		//返却日(社内)
		$txtComplete_YMD1 		= $module_cmn->fChangDateFormat($aPara[36]); 		//完結日(社内)
		$txtConfirm_Tanto_CD1 	= $aPara[37]; 										//確認者CD(社内)
		$txtConfirm_Tanto_Nm1 	= $aPara[38]; 										//確認者名(社内)
		$txtRemarks1 			= $aPara[39]; 										//備考(社内)

		$txtIncident_NM2 		= $aPara[48]; 										//発行先名称(協工)
		$txtPc_Ap_Ans_YMD2		= $module_cmn->fChangDateFormat($aPara[49]); 		//品証指定回答日(協工)
		$txtReturn_YMD2 		= $module_cmn->fChangDateFormat($aPara[50]); 		//返却日(協工)
		$txtComplete_YMD2 		= $module_cmn->fChangDateFormat($aPara[51]); 		//完結日(協工)
		$txtConfirm_Tanto_CD2 	= $aPara[52]; 										//確認者CD(協工)
		$txtConfirm_Tanto_Nm2 	= $aPara[53]; 										//確認者名(協工)
		$txtRemarks2 			= $aPara[54]; 										//備考(協工)

		$cmbQuick_Fix_CD 		= $aPara[58]; 										//異常品暫定処置

		$txtProgres_Stage_Nm 	= $aPara[40]; 	//進捗状態
		$hidUCount 				= $aPara[41]; 	//更新回数

		$txtContact_Accept_YMD	= $module_cmn->fChangDateFormat($aPara[42]);		//連絡受理日 2016/09/02 add k.kume
		
		$txtPc_Tanto_CD 		= $aPara[59]; 											//品証担当者CD 2019/07/06 add k.kume
		$txtPc_Tanto_Nm 		= $aPara[60]; 											//品証担当者名 2019/07/06 add k.kume
		
	}


	//顧客指定回答の回答不要のチェックボックス
	if($chkCustAns == "1" ){
		$strCustAnsCheck = "checked";
	}else{
		$strCustAnsCheck = "";
	}

	//呼び出した整理NOがない場合はエラー
	if($txtReference_No == "" && $hidFrame == 1){
		$strErrMsg = $module_sel->fMsgSearch("E001","対象の不具合情報がありません");	
		$bDispflg = false;
	}
	
	//効果の確認通知有無のチェックボックス
	//if($chkEffectAlert == "1" ){
	//	$strEffectAlert = "checked";
	//}else{
	//	$strEffectAlert = "";
	//}

	

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

<script src="js/protocalendar/lib/effects.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script src="js/protocalendar/javascripts/protocalendar.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script src="js/protocalendar/javascripts/lang_ja.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>

<script type="text/javascript" src="js/common.js"><!-- 共通javascript読み込み --></script>
<script type="text/javascript" >
<!-- 個別javascript記述 -->

	//フォーカスセット処理
	function fSetFocus(strObj){
		if(window.event.keyCode==13){

			document.form.elements[strObj].setFocus();
			return false;
		}
	}

	//チェック処理
	function fCheck(strMode){
		//登録・更新の場合チェック
		if(strMode == 1 || strMode == 2){
			//日付フォーマットチェック
			if(!fCalCheckFormat('txtPc_Ap_Ans_YMD1','品証指定回答日(社内)')){
				return false;
			}
			if(!fCalCheckFormat('txtPc_Ap_Ans_YMD2','品証指定回答日(協工)')){
				return false;
			}

//			if(!fCalCheckFormat('txtCust_Accept_YMD','顧客了承回答日')){
//				return false;
//			}
			if(!fCalCheckFormat('txtAns_YMD','回答日')){
				return false;
			}


			if(!fCalCheckFormat('txtReturn_YMD1','返却日(社内)')){
				return false;
			}
			if(!fCalCheckFormat('txtComplete_YMD1','完結日(社内)')){
				return false;
			}
			if(!fCalCheckFormat('txtReturn_YMD2','返却日(協工)')){
				return false;
			}
			if(!fCalCheckFormat('txtComplete_YMD2','完結日(協工)')){
				return false;
			}
			
			//添付ファイルの必須チェックを追加 2018/06/05 k.kume
			//不具合報告書必須チェック(回答日入力時のみ)
			if(document.getElementById('txtAns_YMD').value != ""){
				//アップロード分チェック
				//if(document.getElementById('tmpFile4').value == "" && document.getElementById('hidFile4') == null){
				if(document.form.tmpFile4.value == ""){
					if (document.getElementById("hidFile4") != null){
						if(document.form.hidFile4.value == null){
							alert('回答日の場合は不具合報告書を添付して下さい');
							return false;
						}
					}else{
						alert('回答日の場合は不具合報告書を添付して下さい');
						return false;
					}
				}
				
			}
			
			//品質異常改善通知書必須チェック(完結日(社内)入力時のみ)
			if(document.getElementById('txtComplete_YMD1').value != ""){
				//アップロード分チェック
				//if(document.getElementById('tmpFile2').value == ""  && document.getElementById('hidFile2') == null){
				if(document.form.tmpFile2.value == ""){
					if (document.getElementById("hidFile2") != null){
						if(document.form.hidFile2.value == null){
							alert('完結日(社内)の場合は品質異常改善通知書を添付して下さい');
							return false;
						}
					}else{
						alert('完結日(社内)の場合は品質異常改善通知書を添付して下さい');
						return false;
					}
				}
			}
			
			//不良品連絡書必須チェック(回答日(協工)入力時のみ)
			//if(document.getElementById('txtComplete_YMD2').value != ""){
			if(document.form.txtComplete_YMD2.value != ""){
				//アップロード分チェック
				<?php if (!file_exists("upload\\".$txtReference_No."\\".$txtReference_No."-3*")){ ?>
				
					//if(document.getElementById('tmpFile3').value == "" && document.getElementById('hidFile3') == null){
					if(document.form.tmpFile3.value == ""){
						if (document.getElementById("hidFile3") != null){
							if(document.form.hidFile3.value == null){
								alert('完結日(協工)の場合は不良品連絡書を添付して下さい');
								return false;
							}
						}else{
							alert('完結日(協工)の場合は不良品連絡書を添付して下さい');
							return false;
						}
					}
				<?php } ?>
			}
			
			//機種依存文字チェック 2017/10/18追加 k.kume
			var search_txt = "[①②③④⑤⑥⑦⑧⑨⑩⑪⑫⑬⑭⑮⑯⑰⑱⑲⑳ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ㍉㌔㌢㍍㌘㌧㌃㌶㍑㍗㌍㌦㌣㌫㍊㌻㎜㎝㎞㎎㎏㏄㎡㍻〝〟№㏍℡㊤㊥㊦㊧㊨㈱㈲㈹㍾㍽㍼]";
			
			//var txt = document.getElementById('tmpFile0').value;
			var txt = document.form.tmpFile0.value;
			if(txt.match(search_txt)){
				alert("アップロードするファイル名に機種依存文字が設定されています。(画像)\nファイル名を変更して下さい。");
				return false;
			}
			
			//var txt = document.getElementById('tmpFile1').value;
			var txt = document.form.tmpFile1.value;
			if(txt.match(search_txt)){
				alert("アップロードするファイル名に機種依存文字が設定されています。(不具合連絡書)\nファイル名を変更して下さい。");
				return false;
			}
			
			//不具合報告書を追加 2018/06/05 k.kume
			//var txt = document.getElementById('tmpFile4').value;
			var txt = document.form.tmpFile4.value;
			if(txt.match(search_txt)){
				alert("アップロードするファイル名に機種依存文字が設定されています。(不具合報告書)\nファイル名を変更して下さい。");
				return false;
			}
			
			//var txt = document.getElementById('tmpFile2').value;
			var txt = document.form.tmpFile2.value;
			if(txt.match(search_txt)){
				alert("アップロードするファイル名に機種依存文字が設定されています。(品質異常改善通知書)\nファイル名を変更して下さい。");
				return false;
			}
			
			//var txt = document.getElementById('tmpFile3').value;
			var txt = document.form.tmpFile3.value;
			if(txt.match(search_txt)){
				alert("アップロードするファイル名に機種依存文字が設定されています。(不良品連絡書)\nファイル名を変更して下さい。");
				return false;
			}
			
		}
		return true;
	}



	//戻るボタン
	function fReturn(strMode){
		//登録以外の場合は一覧に戻る
		if(strMode != 1){
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
		}else{
			document.form.action ="main.php";
			document.form.submit();
		}

	}

	//確定ボタン押下時
	function fncExcute(strMode,strDialogMsg,intSave){

		//チェック処理
		if(fCheck(strMode)){

			//確認メッセージ
			if(window.confirm(strDialogMsg + 'してもよろしいですか？')){
				document.form.hidUp.value = 1;
				//ヘッダの担当者コードをセット
				//document.form.hidTantoCd.value = parent.head.hidTantoCd.value;
				document.form.encoding = "multipart/form-data";

				document.form.action ="F_FLK0010.php?mode="
				+ strMode + "&save=" + intSave +
				"&aJoken[0]=<?php echo $aJoken[0];?>
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
			}else{
				return false;
			}

		}

	}

	/* 製品検索処理 */
	function fItemGet(strMode){

		//製品CDが入っていたら
		if( document.form.txtProd_CD.value!="") {

			var a = new Ajax.Request("F_AJX0010.php",
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
					    //alert('読み込み成功しました');
					    //取得データセット
				        document.form.txtProd_NM.value = json[1];
				        document.form.txtDRW_NO.value = json[2];
				        document.form.txtDie_NO.value = json[3];
				        document.form.txtCust_CD.value = json[4];
				        document.form.txtCust_NM.value = json[5];


					}
					,onFailure: function(request) {
						alert('SMART2の製品マスタに存在しません');
					}
					,onException: function (request) {
						alert('SMART2の製品マスタに存在しません');
					}
				}
			);
		}
	}


	/* 取引先コードテキストボックスでエンターキーが押された時 */
	function fCustGet(strMode){

		//取引先コードが入っていたら
		if(document.form.txtCust_CD.value!="") {

			var a = new Ajax.Request("F_AJX0020.php",
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
				        document.form.txtCust_NM.value = json[1];
					}
					,onFailure: function(request) {
						alert('取引先マスタに存在しません');
						document.form.txtCust_NM.value = "";
					}
					,onException: function (request) {
						alert('取引先マスタに存在しません');
						document.form.txtCust_NM.value = "";
					}
				}
			);
		}

	}

	/* 回答者コードテキストボックスでエンターキーが押された時 */
	function fAnsTanGet(strMode){
		//回答者コードが入っていたら
		if(document.form.txtAns_Tanto_CD.value!="") {
			var a = new Ajax.Request("F_AJX0030.php",
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
				        document.form.txtAns_Tanto_NM.value = json[1];
					}
					,onFailure: function(request) {
						alert('担当者マスタに存在しません');
						document.form.txtAns_Tanto_NM.value = "";
					}
					,onException: function (request) {
						alert('担当者マスタに存在しません');
						document.form.txtAns_Tanto_NM.value = "";
					}
				}
			);
		}
	}

	/* 確認者コードテキストボックスでエンターキーが押された時 */
	function fConfTanGet1(strMode){

		//確認者(社内)が入っていたら
		if(document.form.txtConfirm_Tanto_CD1.value!="") {

			var a = new Ajax.Request("F_AJX0030.php",
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
				        document.form.txtConfirm_Tanto_NM1.value =  json[0];

					}
					,onFailure: function(request) {
						alert('担当者マスタに存在しません');
						document.form.txtConfirm_Tanto_NM1.value =  "";

					}
					,onException: function (request) {
						alert('担当者マスタに存在しません');
						document.form.txtConfirm_Tanto_NM1.value =  "";

					}
				}
			);
		}
	}


	/* 数値以外入力付加 */
	function isNaN_chk1(strObj,strTit){
		chk_fld=document.form.elements[strObj].value
		if(isNaN(chk_fld)){
			alert("入力された数値が不正です[" + strTit + "]");
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

	//発行先
	function fIncKbnChange(){

		//発行先区分によって項目削除
		//0:社内、1:協力工場、2:社内/協力工場、3:なし
		if(document.form.cmbIncident_KBN.value == "0"){
			document.form.txtIncident_CD2.value = "";
			document.form.txtIncident_NM2.value = "";
			document.form.txtPc_Ap_Ans_YMD2.value = "";
			document.form.txtReturn_YMD2.value = "";
			document.form.txtComplete_YMD2.value = "";
			document.form.txtConfirm_Tanto_CD2.value = "";
			document.form.txtConfirm_Tanto_Nm2.value = "";
			document.form.txtRemarks2.value = "";

		}else if(document.form.cmbIncident_KBN.value == "1"){
			document.form.txtIncident_CD1.value = "";
			document.form.txtIncident_NM1.value = "";
			document.form.txtPc_Ap_Ans_YMD1.value = "";
			document.form.txtProduct_Officer_NM.value = "";
			document.form.txtReturn_YMD1.value = "";
			document.form.txtComplete_YMD1.value = "";
			document.form.txtConfirm_Tanto_CD1.value = "";
			document.form.txtConfirm_Tanto_Nm1.value = "";
			document.form.txtRemarks1.value = "";


		}else if(document.form.cmbIncident_KBN.value == "3"){
			document.form.txtIncident_CD1.value = "";
			document.form.txtIncident_NM1.value = "";
			document.form.txtProduct_Officer_NM.value = "";
			document.form.txtPc_Ap_Ans_YMD1.value = "";
			document.form.txtReturn_YMD1.value = "";
			document.form.txtComplete_YMD1.value = "";
			document.form.txtConfirm_Tanto_CD1.value = "";
			document.form.txtConfirm_Tanto_Nm1.value = "";
			document.form.txtRemarks1.value = "";
			document.form.txtIncident_CD2.value = "";
			document.form.txtIncident_NM2.value = "";
			document.form.txtPc_Ap_Ans_YMD2.value = "";
			document.form.txtReturn_YMD2.value = "";
			document.form.txtComplete_YMD2.value = "";
			document.form.txtConfirm_Tanto_CD2.value = "";
			document.form.txtConfirm_Tanto_Nm2.value = "";
			document.form.txtRemarks2.value = "";

		}

		return true;

	}

	//画面項目オールクリア
	function fClearFormAll() {
	    for (var i=0; i<document.forms.length; ++i) {
	        fClearForm(document.forms[i]);
	    }
	}
	function fClearForm(form) {
	    for(var i=0; i<form.elements.length; ++i) {
	        fClearElement(form.elements[i]);
	    }
	}
	function fClearElement(element) {
	    switch(element.type) {
	        case "hidden":
	        case "submit":
	        case "reset":
	        case "button":
	        case "image":
	            return;
	        case "file":
	            return;
	        case "text":
	        case "password":
	        case "textarea":
	            element.value = "";
	            return;
	        case "checkbox":
	        case "radio":
	            element.checked = false;
	            return;
	        case "select-one":
	        case "select-multiple":
	            element.selectedIndex = 0;
	            return;
	        default:
	    }
	}


</script>
</HEAD>
<BODY style="font-size : medium;border-collapse : separate;" 
<?php if($strMsg <> "" && $mode == '1') { ?> fClearFormAll(); <?php } ?>">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000"><SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【不具合入力】<?php echo($modeN); ?>
      </SPAN></TD>
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
    <TR>
      <TD class="tdnone" align="center" width="1000"  ><B><FONT color="#ff0000" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
    </TR>
  </TBODY>
</TABLE>
<?php
}
?>

<FORM id="form" name="form" method="post" enctype="multipart/form-data\" onSubmit="return false;">

<TABLE border="0">
  <TBODY>
    <TR>
      <TD class="tdnone" width="400" align="left">
		<?php
		//参照以外かつボタン表示フラグがTrueならは確定ボタン表示
		if($mode <> "4" && $bDispflg){
			//品証のユーザのみ表示
			if(substr($_SESSION['login'][2],0,3) == "117"){
		?>
			  <INPUT type="button" name="btnExcute" value="　確　定　" onClick="fncExcute('<?php echo($mode); ?>','<?php echo($modeN); ?>',0)" tabindex="2401">
		<?php
			}
		?>
		<?php
		}
		?>
		<?php if($hidFrame == 0){ ?>
      	<INPUT type="button" name="btnSearch" value="　戻　る　" tabindex="2402" onClick="fReturn(<?php echo $mode;?>)">
      	<?php } ?>
		<?php echo $strManulPath;  ?>
      </TD>
      <TD class="tdnone" width="600" >

<?php
//参照以外かつボタン表示フラグがTrueならは確定ボタン表示
if($mode <> "4" && $bDispflg){
	//品証のユーザのみ表示
	if(substr($_SESSION['login'][2],0,3) == "117"){
?>
<!--
      <INPUT type="button" name="btnExcute1" value="　確　定　" tabindex="1" onClick="fncExcute('<?php echo($mode); ?>','<?php echo($modeN); ?>',0)" tabindex="2400">
-->
<?php
	}
?>

<br>
<br>
<input type="hidden" name="hidUCount" value="<?php echo $hidUCount;?>">

<?php
}
?>
      </TD>
    </TR>
  </TBODY>
</TABLE>


<TABLE border="0">
  <TBODY>
    <TR>
      <TD class="tdnone" width="600" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>受付情報</B></FONT>
		</DIV>
      </TD>
    </TR>
  </TBODY>
</TABLE>

<TABLE class="tbline"  width="1000"  >

  <TBODY>
    <TR>
      <TD class="tdnone9" height="46" width="250">整理ＮＯ</TD>
      <TD colspan="" class="tdnone3" height="46" width="150">
      	<INPUT size="20" type="text" class="textboxdisp" name="txtReference_No" style="ime-mode: disabled;" readonly value="<?php echo $txtReference_No; ?>">
      </TD>
      <TD class="tdnone9" height="46" width="180">進捗状態</TD>
      <TD class="tdnone3" height="46" width="150">
      	<INPUT size="20" type="text" class="textboxdisp" name="txtProgres_Stage_Nm" style="ime-mode: disabled;" readonly value="<?php echo $txtProgres_Stage_Nm; ?>">
      	<INPUT type="hidden" name="hidProgres_Stage" value="<?php echo $txtProgres_Stage; ?>">
      </TD>
      <TD class="tdnone1" height="46" width="150">連絡受理日</TD>
      <TD colspan="3" class="tdnone3" height="46" width="150">
      	<INPUT size="7" type="text" id="txtContact_Accept_YMD" name="txtContact_Accept_YMD" maxlength="10" style="ime-mode: disabled;" tabindex="5" value="<?php echo $txtContact_Accept_YMD; ?>">
    		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtContact_Accept_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
      </TD>
    </TR>
    <TR style="border:1px solid">
      <TD class="tdnone1" >
      	<A href="JavaScript:fOpenSearch('F_MSK0010','txtProd_CD','txtProd_NM','txtDRW_NO','','txtDie_NO','txtCust_CD','txtCust_NM','0')" >製品CD</A>
      </TD>
      <TD class="tdnone3" colspan="1" >
      	<!--<INPUT size="8" type="text" name="txtProd_CD" maxlength="8" style="ime-mode: disabled;" value="<?php echo $txtProd_CD; ?>" tabindex="10" onBlur="fShireGet('<?php echo($mode); ?>');" > -->
      	<INPUT size="7" type="text" id="txtProd_CD" name="txtProd_CD" maxlength="8" style="ime-mode: disabled;" value="<?php echo $txtProd_CD; ?>" tabindex="10" onBlur="window.event.keyCode=13;fItemGet('<?php echo($mode); ?>');" >
      	<input type="hidden" name="hidShireGet" readonly value="0">
      </TD>
      <TD class="tdnone9">製品名</TD>
      <TD class="tdnone3" colspan="">
      	<INPUT size="36" type="text" class="textboxdisp" id="txtProd_NM" name="txtProd_NM" style="ime-mode: disabled;" readonly value="<?php echo $txtProd_NM; ?>">
      </TD>
      <TD class="tdnone9" height="46" width="150">仕様番号</TD>
      <TD colspan="3" class="tdnone3" height="46" width="150">
      	<INPUT size="20" type="text" class="textboxdisp" id="txtDRW_NO" name="txtDRW_NO" style="ime-mode: disabled;" readonly value="<?php echo $txtDRW_NO; ?>">
      </TD>
    </TR>


    <TR>
      <TD class="tdnone1" height="46" width="150">金型番号</TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="5" type="text" id="txtDie_NO" name="txtDie_NO" maxlength="5" tabindex="20" style="ime-mode: disabled;" value="<?php echo $txtDie_NO; ?>">
      </TD>
      <TD class="tdnone1" >
      	<A href="JavaScript:fOpenSearch('F_MSK0040','txtLot_NO','','','','','','','0')" >ロットNO</A>
      </TD>
      <TD class="tdnone3" >
      	<INPUT size="30" type="text" id="txtLot_NO" name="txtLot_NO" maxlength="100" value="<?php echo $txtLot_NO; ?>" tabindex="20" onBlur="" >
      </TD>
      <TD class="tdnone2">顧客管理NO</TD>
      <TD class="tdnone3" colspan="3">
      	<INPUT size="30" type="text" name="txtCust_Manage_No" maxlength="15" tabindex="40" value="<?php echo $txtCust_Manage_No; ?>">
      </TD>

    </TR>


    <TR style="border:1px solid">
      <TD class="tdnone1" >
      	<A href="JavaScript:fOpenSearch('F_MSK0020','txtCust_CD','txtCust_NM','','','','','','0')" onclick="">顧客CD</A>
      </TD>
      <TD class="tdnone3">
      	<INPUT size="5" type="text" id="txtCust_CD" name="txtCust_CD" maxlength="5" tabindex="50" style="ime-mode: disabled;" value="<?php echo $txtCust_CD; ?>" tabindex="40" onBlur="fCustGet('<?php echo($mode); ?>');" >
      </TD>
      <TD class="tdnone9">顧客名</TD>
      <TD class="tdnone3" colspan="1">
      	<INPUT size="36" type="text" class="textboxdisp"  id="txtCust_NM" name="txtCust_NM" readonly value="<?php echo $txtCust_NM; ?>">
      </TD>
      <TD class="tdnone1">顧客担当者</TD>
      <TD class="tdnone3" colspan="3">
      	<INPUT size="30" type="text" id="txtCust_Officer" name="txtCust_Officer" maxlength="25" tabindex="55" value="<?php echo $txtCust_Officer; ?>">
      </TD>
    </TR>

    <TR style="border:1px solid">
      <TD class="tdnone1" >客先よりの連絡方法</TD>
      <TD class="tdnone3">
      	<SELECT name="cmbCust_Contact_KBN" id="cmbCust_Contact_KBN" tabindex="60" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C02',$cmbCust_Contact_KBN); ?>
      	</SELECT>
      </TD>
      <TD class="tdnone1">受付区分</TD>
      <TD class="tdnone3">
      	<SELECT name="cmbRecept_KBN" id="cmbRecept_KBN" tabindex="70" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C03',$cmbRecept_KBN); ?>
      	</SELECT>
      </TD>
      <TD class="tdnone1">不具合区分</TD>
      <TD class="tdnone3" colspan="3">
      	<SELECT name="cmbFlaw_KBN" id="cmbFlaw_KBN" tabindex="80" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
<!-- 2019/04/01 ED START -->
			<?php $module_sel->//fMakeCombo('C15',$cmbFlaw_KBN); 
				fMakeComboS2('085',$cmbFlaw_KBN); 
			?>
<!-- 2019/04/01 ED END -->
      	</SELECT>
      </TD>
    </TR>

     <TR>
      <TD class="tdnone1" height="46" width="150">対象数量</TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="10" type="text" name="txtTarget_QTY" maxlength="8" tabindex="90" style="text-align: right; ime-mode: disabled;" value="<?php echo $txtTarget_QTY; ?>">個
      </TD>
      <TD class="tdnone1" height="46" width="150">対象部門</TD>
      <TD colspan="5" class="tdnone3" height="46" width="">
      	<?php if($strTargetSecRO == ""){ ?>

      	<SELECT name="cmbTarget_Section_KBN" id="cmbTarget_Section_KBN" tabindex="100" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C04',$cmbTarget_Section_KBN); ?>

      	</SELECT>（※）整理NO採番時に対象部門を考慮するので登録後は変更できません。
      	<?php }else{
	      echo $module_sel->fDispKbn('C04',$cmbTarget_Section_KBN);
	      echo "<input type='hidden' id='cmbTarget_Section_KBN' name='cmbTarget_Section_KBN' value='".$cmbTarget_Section_KBN."'>";
      	} ?>
      </TD>
    </TR>
    <TR style="border:1px solid">
      <TD class="tdnone1">不具合内容</TD>
      <TD colspan="7" class="tdnone3" height="46" width="150">
      	<textarea  cols="100" rows="5" name="txtFlaw_Contents" tabindex="110" ><?php echo $txtFlaw_Contents; ?></textarea>

      </TD>
    </TR>
	<TR>
	<TD class="tdnone1" height="46" width="150">
      	<A href="JavaScript:fOpenSearch('F_MSK0030','txtPc_Tanto_CD','txtPc_Tanto_Nm','','','','','','0')" >品証担当者CD</A>
      </TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="5" type="text" name="txtPc_Tanto_CD" maxlength="5" tabindex="280" style="ime-mode: disabled;" value="<?php echo $txtPc_Tanto_CD; ?>" onBlur="">
      </TD>
      <TD class="tdnone9" height="46" width="150">品証担当者名</TD>
      <TD colspan="5" class="tdnone3" height="46" width="150" >
      	<INPUT size="20" type="text" class="textboxdisp" name="txtPc_Tanto_Nm"  readonly style="ime-mode: disabled;" value="<?php echo $txtPc_Tanto_Nm; ?>">
      </TD>
	</TR>
    <TR>
      <TD colspan="6" class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>発行情報(初回発行日)</B></FONT>
		</DIV>
      </TD>
    </TR>
    <TR>
      <TD class="tdnone9" height="46" width="150" nowrap>不具合連絡書</TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT class="textboxdisp" readonly size="10" type="text" id="txtIssue_YMD1" name="txtIssue_YMD1" style="ime-mode: disabled;" value="<?php echo $txtIssue_YMD1; ?>">
      </TD>
      <TD class="tdnone9" height="46" width="150" nowrap>品質異常改善通知書</TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT class="textboxdisp" readonly size="10" type="text" id="txtIssue_YMD2" name="txtIssue_YMD2" style="ime-mode: disabled;" value="<?php echo $txtIssue_YMD2; ?>">
      </TD>
      <TD class="tdnone9" height="46" width="150" nowrap>不良品連絡書</TD>
      <TD colspan="3" class="tdnone3" height="46" width="150">
      	<INPUT class="textboxdisp" readonly size="10" type="text" id="txtIssue_YMD3" name="txtIssue_YMD3" style="ime-mode: disabled;" value="<?php echo $txtIssue_YMD3; ?>">
      </TD>
    </TR>

    <TR>
      <TD colspan="6" class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>返却情報</B></FONT>
		</DIV>
      </TD>
    </TR>

    <TR style="border:1px solid">
      <TD class="tdnone2" >返却数量</TD>
      <TD class="tdnone3" height="46" width="150">
      	<INPUT size="12" type="text" name="txtReturn_QTY" maxlength="8" style="text-align: right;ime-mode: disabled;" tabindex="120" value="<?php echo $txtReturn_QTY; ?>">
      </TD>
      <TD class="tdnone2" >不良数量</TD>
      <TD class="tdnone3" height="46" width="150">
      	<INPUT size="12" type="text" name="txtBat_QTY"  maxlength="8" style="text-align: right;ime-mode: disabled;" tabindex="130" value="<?php echo $txtBat_QTY; ?>">
      </TD>
      <TD class="tdnone2" >返却品処理</TD>
      <TD colspan="3" class="tdnone3">
      	<SELECT name="cmbReturn_Disposal" id="cmbReturn_Disposal" tabindex="140" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C08',$cmbReturn_Disposal); ?>
      	</SELECT>
      </TD>
    </TR>

    <TR>
      <TD colspan="6" class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>回答情報</B></FONT>
		</DIV>
      </TD>
    </TR>

    <TR style="border:1px solid">
      <TD class="tdnone2" >結果区分</TD>
      <TD  class="tdnone3">
      	<SELECT name="cmbResult_KBN" id="cmbResult_KBN" tabindex="150" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C09',$cmbResult_KBN); ?>
      	</SELECT>
      </TD>
      <TD class="tdnone1" height="46" width="150">顧客指定回答日</TD>
      <TD class="tdnone3" height="46" width="150" colspan="5">
      	<INPUT size="7" type="text" id="txtCust_Ap_Ans_YMD" name="txtCust_Ap_Ans_YMD" maxlength="10" style="ime-mode: disabled;" tabindex="160" value="<?php echo $txtCust_Ap_Ans_YMD; ?>">
      		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtCust_Ap_Ans_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
		<input type="checkbox" name="chkCustAns" value="1" <?php echo $strCustAnsCheck; ?> >回答不要
      </TD>

    </TR>

    <TR>
      <TD class="tdnone0" height="46" width="150">回答日</TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="7" type="text" id="txtAns_YMD" name="txtAns_YMD" maxlength="10" style="ime-mode: disabled;" tabindex="170" value="<?php echo $txtAns_YMD; ?>">
    		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtAns_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
      </TD>
      <TD class="tdnone0" height="46" width="150">
      	<A href="JavaScript:fOpenSearch('F_MSK0030','txtAns_Tanto_CD','txtAns_Tanto_Nm','','','','','','0')" >回答者CD</A>
      </TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="5" type="text" id="txtAns_Tanto_CD" name="txtAns_Tanto_CD" tabindex="180" maxlength="5" style="ime-mode: disabled;" value="<?php echo $txtAns_Tanto_CD; ?>" onBlur="">
      </TD>
      <TD class="tdnone9" height="46" width="150">回答者名</TD>
      <TD colspan="3" class="tdnone3" height="46" width="150">
      	<INPUT size="12" type="text" class="textboxdisp" readonly id="txtAns_Tanto_Nm" name="txtAns_Tanto_Nm" style="ime-mode: disabled;" value="<?php echo $txtAns_Tanto_Nm; ?>">
      </TD>

    </TR>
    <TR>
      <TD class="tdnone1" height="46" width="150">効果確認期限通知</TD>
      <TD class="tdnone3" height="46" >
      	<SELECT name="cmbEffectAlert" id="cmbEffectAlert" tabindex="185" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C18',$cmbEffectAlert); ?>
      	</SELECT>
      </TD>
      <TD class="tdnone0" height="46" width="150">対策日</TD>
      <TD class="tdnone3" height="46" width="150" >
      	<INPUT size="7" type="text" id="txtMeasures_YMD" name="txtMeasures_YMD" maxlength="10" style="ime-mode: disabled;" tabindex="190" value="<?php echo $txtMeasures_YMD; ?>">
      		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtMeasures_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
	  </TD>
	  <TD class="tdnone2" height="46" width="150">対策効果確認日</TD>
      <TD class="tdnone3" height="46" width="150" colspan="3">
      	<INPUT size="7" type="text" id="txtEffectConfirm_YMD" name="txtEffectConfirm_YMD" maxlength="10" style="ime-mode: disabled;" tabindex="190" value="<?php echo $txtEffectConfirm_YMD; ?>">
      		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtEffectConfirm_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
	  </TD>
    </TR>
    <TR>
      <TD colspan="6" class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>発行先情報</B></FONT>
		</DIV>
      </TD>
    </TR>
	<TR style="border:1px solid">
      <TD class="tdnone1" >発行先区分</TD>
      <TD colspan="7" class="tdnone3">
      	<SELECT name="cmbIncident_KBN" id="cmbIncident_KBN" tabindex="190" onChange="fIncKbnChange()">
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C05',$cmbIncident_KBN); ?>
      	</SELECT>（※）発行先区分が社内の場合…「品質異常改善情報（Ａ）」を入力、協力工場の場合…「協力工場不良品連絡情報（Ｂ）」を入力
      </TD>
    </TR>

    <TR>
      <TD colspan="6" class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>品質異常改善情報（Ａ）</B></FONT>
		</DIV>
      </TD>
    </TR>


    <TR>
    	<TD class="tdnone0" width="150">
      		<A href="JavaScript:fOpenSearch('F_MSK0020','txtIncident_CD1','txtIncident_NM1','','','','','','1')" >発行先CD(社内)</A>
      	</TD>
      	<TD colspan="1" class="tdnone3">
      		<INPUT size="5" type="text" name="txtIncident_CD1" maxlength="6"  tabindex="200" style="ime-mode: disabled;" value="<?php echo $txtIncident_CD1; ?>" tabindex="1000" ur="fShireGet('<?php echo($mode); ?>');" >
      	</TD>
      	<TD class="tdnone9">発行先名称(社内)</TD>
      	<TD colspan="" class="tdnone3" height="46" >
      		<INPUT size="20" type="text" name="txtIncident_NM1" class="textboxdisp" readonly value="<?php echo $txtIncident_NM1; ?>">
      	</TD>
      	<TD class="tdnone2" >
      		<A href="JavaScript:fOpenSearch('F_MSK0030','','txtProduct_Officer_NM','','','','','','2')" >担当者</A>
      	</TD>
      	<TD colspan="3" class="tdnone3">
      		<INPUT size="30" type="text" id="txtProduct_Officer_NM" name="txtProduct_Officer_NM" maxlength="20" tabindex="205" value="<?php echo $txtProduct_Officer_NM; ?>" tabindex="1000"  >
     	 </TD>
    </TR>
	<TR style="border:1px solid">
      <TD class="tdnone0" >発生起因部署</TD>
      <TD colspan="1" class="tdnone3">
      	<SELECT name="cmbProduct_Out_Ka_CD" id="cmbProduct_Out_Ka_CD" tabindex="210" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C06',$cmbProduct_Out_Ka_CD); ?>
      	</SELECT>
      </TD>

      <TD class="tdnone0" >流出起因部署1</TD>
      <TD  class="tdnone3">
      	<SELECT name="cmbCheck_Out_Ka_CD1" id="cmbCheck_Out_Ka_CD1" tabindex="215" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C06',$cmbCheck_Out_Ka_CD1); ?>
      	</SELECT>
      </TD>
      <TD class="tdnone0" >流出起因部署2</TD>
      <TD  class="tdnone3" colspan="3">
      	<SELECT name="cmbCheck_Out_Ka_CD2" id="cmbCheck_Out_Ka_CD2" tabindex="220" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C07',$cmbCheck_Out_Ka_CD2); ?>
      	</SELECT>
      </TD>
    </TR>
	<TR>
		<TD class="tdnone0" >異常品暫定処置</TD>
      	<TD colspan="1" class="tdnone3" colsplan="5">
      	<SELECT name="cmbQuick_Fix_CD" id="cmbQuick_Fix_CD" tabindex="230" >
      		<OPTION selected value="-1" >▼選択して下さい</OPTION>
        	<?php $module_sel->fMakeCombo('C28',$cmbQuick_Fix_CD); ?>
      	</SELECT>
      	</TD>
	</TR>
    <TR>
        <TD class="tdnone0" height="46" nowrap>品証指定回答日(社内)</TD>
        <TD class="tdnone3" height="46" width="150">
      		<INPUT size="7" type="text" id="txtPc_Ap_Ans_YMD1" name="txtPc_Ap_Ans_YMD1" maxlength="10" tabindex="250" style="ime-mode: disabled;" value="<?php echo $txtPc_Ap_Ans_YMD1; ?>">
    		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtPc_Ap_Ans_YMD1", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
        </TD>
        <TD class="tdnone2" height="46" >返却日(社内)</TD>
        <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="7" type="text" id="txtReturn_YMD1" name="txtReturn_YMD1" maxlength="10" tabindex="260" style="ime-mode: disabled;" value="<?php echo $txtReturn_YMD1; ?>">
    		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtReturn_YMD1", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
      	</TD>
      	<TD class="tdnone2" height="46" >完結日(社内)</TD>
      	<TD  class="tdnone3" height="46" width="150" colspan="3">
      	<INPUT size="7" type="text" id="txtComplete_YMD1" name="txtComplete_YMD1" maxlength="10" tabindex="270" style="ime-mode: disabled;" value="<?php echo $txtComplete_YMD1; ?>">
    		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtComplete_YMD1", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
      	</TD>
    </TR>
    <TR>
      <TD class="tdnone2" height="46" width="150">
      	<A href="JavaScript:fOpenSearch('F_MSK0030','txtConfirm_Tanto_CD1','txtConfirm_Tanto_Nm1','','','','','','0')" >確認者CD(社内)</A>
      </TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="5" type="text" name="txtConfirm_Tanto_CD1" maxlength="5" tabindex="280" style="ime-mode: disabled;" value="<?php echo $txtConfirm_Tanto_CD1; ?>" onBlur="">
      </TD>
      <TD class="tdnone9" height="46" width="150">確認者名(社内)</TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="20" type="text" class="textboxdisp" name="txtConfirm_Tanto_Nm1"  readonly style="ime-mode: disabled;" value="<?php echo $txtConfirm_Tanto_Nm1; ?>">
      </TD>
      <TD class="tdnone2" height="46" width="150">備考(社内)</TD>
      <TD colspan="3" class="tdnone3" height="46" width="150" >
      	<INPUT size="20" type="text" name="txtRemarks"  maxlength="50" tabindex="300" value="<?php echo $txtRemarks; ?>">
      </TD>
    </TR>

        <TR>
      <TD colspan="6" class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>協力工場不良品連絡情報（Ｂ）</B></FONT>
		</DIV>
      </TD>
    </TR>

    <TR>
	    <TD class="tdnone0" >
      		<A href="JavaScript:fOpenSearch('F_MSK0020','txtIncident_CD2','txtIncident_NM2','','','','','','2')" >発行先CD(協工)</A>
      	</TD>
      	<TD colspan="1" class="tdnone3">
      		<INPUT size="5" type="text" name="txtIncident_CD2"  maxlength="6" tabindex="310" style="ime-mode: disabled;" value="<?php echo $txtIncident_CD2; ?>" tabindex="1000" onBlur="fShireGet('<?php echo($mode); ?>');" >
      	</TD>
      	<TD class="tdnone9">発行先名称(協工)</TD>
      	<TD colspan="5" class="tdnone3" height="46" width="150" >
      		<INPUT size="20" type="text" name="txtIncident_NM2" class="textboxdisp" readonly value="<?php echo $txtIncident_NM2; ?>">
      	</TD>
    </TR>
    <TR>

      <TD class="tdnone0" height="46" width="150">品証指定回答日(協工)</TD>
      <TD  class="tdnone3" height="46" width="150">
      	<INPUT size="7" type="text" id="txtPc_Ap_Ans_YMD2" name="txtPc_Ap_Ans_YMD2" maxlength="10" tabindex="320" style="ime-mode: disabled;" value="<?php echo $txtPc_Ap_Ans_YMD2; ?>">
    		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtPc_Ap_Ans_YMD2", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
      </TD>
      <TD class="tdnone2" height="46" width="150">返却日(協工)</TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="7" type="text" id="txtReturn_YMD2" name="txtReturn_YMD2" maxlength="10" tabindex="330" style="ime-mode: disabled;" value="<?php echo $txtReturn_YMD2; ?>">
    		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtReturn_YMD2", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
      </TD>
      <TD class="tdnone2" height="46" width="150">完結日(協工)</TD>
      <TD colspan="3" class="tdnone3" height="46" width="150">
      	<INPUT size="7" type="text" id="txtComplete_YMD2" name="txtComplete_YMD2" maxlength="10" tabindex="340" style="ime-mode: disabled;" value="<?php echo $txtComplete_YMD2; ?>">
    		<script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("txtComplete_YMD2", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
      </TD>


    </TR>
    <TR>
      <TD class="tdnone2" height="46" width="150">
      	<A href="JavaScript:fOpenSearch('F_MSK0030','txtConfirm_Tanto_CD2','txtConfirm_Tanto_Nm2','','','','','','0')" >確認者CD(協工)</A>
      </TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="5" type="text" name="txtConfirm_Tanto_CD2" maxlength="5" tabindex="350" style="ime-mode: disabled;" value="<?php echo $txtConfirm_Tanto_CD2; ?>">
      </TD>
      <TD class="tdnone9" height="46" width="150">確認者名(協工)</TD>
      <TD colspan="1" class="tdnone3" height="46" width="150">
      	<INPUT size="20" type="text" class="textboxdisp" name="txtConfirm_Tanto_Nm2" readonly style="ime-mode: disabled;" value="<?php echo $txtConfirm_Tanto_Nm2; ?>">
      </TD>
      <TD class="tdnone2" height="46" width="150">備考(協工)</TD>
      <TD colspan="3" class="tdnone3" height="46" width="150">
      	<INPUT size="20" type="text" name="txtRemarks2"  maxlength="50" tabindex="360" value="<?php echo $txtRemarks2; ?>">
      </TD>
    </TR>


  </TBODY>
</TABLE>
<br>

<TABLE class="tbline"  width="1000"  >
  <TBODY>
    <TR>
      <TD colspan="3" class="tdnone" width="100" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>画像・資料（各ファイル最大３ＭＢ）</B></FONT>
		</DIV>
      </TD>
    </TR>

    <TR>
	    <TD class="tdnone0" height="46">共通</TD>
	    <TD class="tdnone0" height="46">画像（JPEGのみ添付可能）</TD>
	     <TD class="tdnone3" colspan="1">
	      <?php if($mode == "1" or $mode == "2"){ ?>
			<input type="hidden" name="MAX_FILE_SIZE" value="2048000">
			<input type="file" name="tmpFile0" SIZE="40" tabindex="370"><!-- 削除<input type="checkbox" name="chktmpFile0" value="1" > -->
			<?php
		      }
				//ファイルが存在したらリンク表示
				if(file_exists("upload\\".$txtReference_No."\\".$txtReference_No.".jpg")){
					echo "<A href='upload\\".$txtReference_No."\\".$txtReference_No.".jpg' target='_blank'>参照<IMG src='./gif/photo.png' width='16' height='16' border='0'></A>";
					echo "<input type='hidden' name='hidFile' value='".$txtReference_No.".jpg'>";
				}else{
					echo "<input type='hidden' name='hidFile' value=''>";
				}
			?>
		</TD>
    </TR>
    <TR>
	    <TD class="tdnone0" height="46">共通</TD>
	    <TD class="tdnone0" height="46">不具合連絡書（PDFのみ添付可能）</TD>
	    <TD class="tdnone3" colspan="1">
	      <?php if($mode == "1" or $mode == "2"){ ?>
			<input type="hidden" name="MAX_FILE_SIZE" value="2048000">
			<input type="file" name="tmpFile1" SIZE="40" tabindex="380"><!-- 削除<input type="checkbox" name="chktmpFile1" value="1" >-->
			<?php
		      }

		        //ファイルが存在したらリンク表示
		      	$file = "upload\\".$txtReference_No."\\".$txtReference_No."-1*";
		      	foreach(glob($file) as $filename){
		      		echo "<A href='".$filename."' target='_blank'>参照<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A><input type='hidden' name='hidFile1' id='hidFile1' value='1'>";
		      		//echo "<input type='hidden' name='hidFile' value='".$txtReference_No."-1.xls'>";
		      	}

				//ファイルが存在したらリンク表示
//				if(file_exists("upload\\".$txtReference_No."\\".$txtReference_No."-1.xls")){
//					echo "<A href='upload\\".$txtReference_No."\\".$txtReference_No."-1.xls' target='_blank'>参照<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A>";
//					echo "<input type='hidden' name='hidFile' value='".$txtReference_No."-1.xls'>";
//				}elseif(file_exists("upload\\".$txtReference_No."\\".$txtReference_No."-1.xlsx")){
//					echo "<A href='upload\\".$txtReference_No."\\".$txtReference_No."-1.xlsx' target='_blank'>添付ファイル<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A>";
//					echo "<input type='hidden' name='hidFile' value='".$txtReference_No."-1.xlsx'>";
//				}else{
//					echo "<input type='hidden' name='hidFile' value=''>";
//				}
			?>
		</TD>
    </TR>
	    <TR>
	    <TD class="tdnone0" height="46">共通</TD>
	    <TD class="tdnone0" height="46">不具合報告書（PDFのみ添付可能）</TD>
	    <TD class="tdnone3" colspan="1">
	      <?php if($mode == "1" or $mode == "2"){ ?>
			<input type="hidden" name="MAX_FILE_SIZE" value="2048000">
			<input type="file" name="tmpFile4" SIZE="40" tabindex="380">
			<?php
		      }
		        //ファイルが存在したらリンク表示
		      	$file = "upload\\".$txtReference_No."\\".$txtReference_No."-4*";
		      	foreach(glob($file) as $filename){
		      		echo "<A href='".$filename."' target='_blank'>参照<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A><input type='hidden' name='hidFile4' id='hidFile4' value='1'>";
		      	}

			?>
		</TD>
    </TR>
    <TR>
	    <TD class="tdnone0" height="46">社内</TD>
	    <TD class="tdnone0" height="46">品質異常改善通知書（PDFのみ添付可能）</TD>
	    <TD class="tdnone3" colspan="1">
	      <?php if($mode == "1" or $mode == "2"){ ?>
			<input type="hidden" name="MAX_FILE_SIZE" value="2048000">
			<input type="file" name="tmpFile2" SIZE="40" tabindex="390"><!-- 削除<input type="checkbox" name="chktmpFile2" value="1" >-->
			<?php
		      }

		      //ファイルが存在したらリンク表示
		      $file = "upload\\".$txtReference_No."\\".$txtReference_No."-2*";
		      foreach(glob($file) as $filename){
		      	echo "<A href='".$filename."' target='_blank'>参照<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A><input type='hidden' name='hidFile2' id='hidFile2' value='1'>";
		      	//echo "<input type='hidden' name='hidFile' value='".$txtReference_No."-1.xls'>";
		      }

				//ファイルが存在したらリンク表示
//				if(file_exists("upload\\".$txtReference_No."\\".$txtReference_No."-2.xls")){
//					echo "<A href='upload\\".$txtReference_No."\\".$txtReference_No."-2.xls' target='_blank'>参照<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A>";
//					echo "<input type='hidden' name='hidFile' value='".$txtReference_No."-2.xls'>";
//				}elseif(file_exists("upload\\".$txtReference_No."\\".$txtReference_No."-2.xlsx")){
//					echo "<A href='upload\\".$txtReference_No."\\".$txtReference_No."-2.xlsx' target='_blank'>添付ファイル<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A>";
//					echo "<input type='hidden' name='hidFile' value='".$txtReference_No."-2.xlsx'>";
//				}else{
//					echo "<input type='hidden' name='hidFile' value=''>";
//				}
			?>
		</TD>
    </TR>
    <TR>
    <TD class="tdnone0" height="46">協工</TD>
    <TD class="tdnone0" height="46">不良品連絡書（PDFのみ添付可能）</TD>
    <TD class="tdnone3" colspan="1">
      <?php if($mode == "1" or $mode == "2"){ ?>
		<input type="hidden" name="MAX_FILE_SIZE" value="2048000">
		<input type="file" name="tmpFile3" SIZE="40" tabindex="400"><!-- 削除<input type="checkbox" name="chkz3" value="1" >-->
		<?php
	      }

		        //ファイルが存在したらリンク表示
		      	$file = "upload\\".$txtReference_No."\\".$txtReference_No."-3*";
		      	foreach(glob($file) as $filename){
		      		echo "<A href='".$filename."' target='_blank'>参照<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A><input type='hidden' name='hidFile3' id='hidFile3' value='1'>";
		      		//echo "<input type='hidden' name='hidFile' value='".$txtReference_No."-1.xls'>";
		      	}


				//ファイルが存在したらリンク表示
//				if(file_exists("upload\\".$txtReference_No."\\".$txtReference_No."-3.xls")){
//					echo "<A href='upload\\".$txtReference_No."\\".$txtReference_No."-3.xls' target='_blank'>参照<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A>";
//					echo "<input type='hidden' name='hidFile' value='".$txtReference_No."-1.xls'>";
//				}elseif(file_exists("upload\\".$txtReference_No."\\".$txtReference_No."-3.xlsx")){
//					echo "<A href='upload\\".$txtReference_No."\\".$txtReference_No."-3.xlsx' target='_blank'>添付ファイル<IMG src='./gif/tmp.png' width='16' height='16' border='0'></A>";
//					echo "<input type='hidden' name='hidFile' value='".$txtReference_No."-3.xlsx'>";
//				}else{
//					echo "<input type='hidden' name='hidFile' value=''>";
//				}
		?>
	</TD>
     </TR>
  </TBODY>
</TABLE>
<P>

<TABLE border="0">
  <TBODY>
    <TR>
      <TD class="tdnone" width="800" >

<?php
//参照以外かつボタン表示フラグがTrueならは確定ボタン表示
if($mode <> "4" && $bDispflg){
	//品証のユーザのみ表示
	if(substr($_SESSION['login'][2],0,3) == "117"){
?>
      <INPUT type="button" name="btnExcute" value="　確　定　" tabindex="410" onClick="fncExcute('<?php echo($mode); ?>','<?php echo($modeN); ?>',0)" tabindex="2400">
<?php
	}
}
?>
<?php if($hidFrame == 0){ ?>
<INPUT type="button" name="btnSearch" value="　戻　る　" onClick="fReturn(<?php echo $mode;?>)">
<?php } ?>
<?php echo $strManulPath;  ?>
<?php
//参照以外かつボタン表示フラグがTrueならは確定ボタン表示
if($mode <> "4" && $bDispflg){
?>
<br>
<br>
<input type="hidden" name="hidUCount" value="<?php echo $hidUCount;?>">

<?php
}
?>
      </TD>
      <TD class="tdnone" width="200" align="right">
		<?php if($hidFrame == 0){ ?>



      	<?php } ?>
		<?php echo $strManulPath;  ?>
      </TD>
    </TR>
  </TBODY>
</TABLE>
<br>
<input type="hidden" name="hidUCount" value="<?php echo $hidUCount;?>">



</P>
<input type="hidden" name="hidUp" value="0">
<input type="hidden" name="hidTantoCd" value="<?php echo $_SESSION['login'][0];?>">
<input type="hidden" name="hidFrame" value="<?php echo $hidFrame; ?>">
</FORM>
</BODY>
</HTML>
