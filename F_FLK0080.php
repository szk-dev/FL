<?php
	//****************************************************************************
	//プログラム名：赤伝緑伝情報入力
	//プログラムID：F_FLK0080
	//作成者	：㈱鈴木　藤田
	//作成日		：2019/04/01
	//履歴		：2019/05/13
	//			：2019/08/01
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
		
		$strReference_NO = $_REQUEST['rrcno'];
		$strReference_SEQ = $_REQUEST['rrcseq'];
		//フレーム外
		$hidFrame = 1;
		
	}else{
		$hidFrame = $_POST['hidFrame'];
	}

	//セッションチェック
	if(empty($_SESSION["login"][0])){
		//セッションが空の場合はエラー画面へ遷移
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/F_CMN0020.php?page=".$_SERVER['REQUEST_URI']);
		exit;
	}

	$token = sha1(uniqid(mt_rand(), true));

	//トークンをセッションに追加する
	$_SESSION['token'][] = $token;

	
	//ファイル読み込み
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	require_once 'vendor/autoload.php';
	require_once("module_sel.php");
	require_once("module_upd.php");
	require_once("module_common.php");

	//オブジェクト作成
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

	if(isset($_GET['save'])){
		$save = $_GET['save'];
	}

	//引数の取得(伝票NO)
	if(isset($_GET['strRrceNo'])) {
		$strReference_NO = $_GET['strRrceNo'];
		$strReference_SEQ = $_GET['strRrceSeq'];
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
		$aJoken[17] = urlencode($module_cmn->fEscape($aJoken[17]));		// 2019/09/20 ADD END
	}

	//メール送信
	if(isset($_GET['aMail'])) {
		$aMail = $_GET['aMail'];
		$aMail[0] = urlencode($module_cmn->fEscape($aMail[0]));
	}else{
		$aMail[0] = 0;
	}
	
	//画面項目の取得
	//引数の取得
	$cmbTargetSection_KBN = $_POST['cmbTargetSection_KBN'];		//対象部門
	$txtReference_NO = $_POST['txtReference_NO'];				//伝票NO
	$hidReference_SEQ = $_POST['hidReference_SEQ'];				//伝票SEQ
	$txtReference_KBN = $_POST['txtReference_KBN'];				//伝票種別
	$txtPointRef_NO = $_POST['txtPointRef_NO'];					//代表伝票NO
	$txtBusyo_CD = $_POST['txtBusyo_CD'];						//起因部署CD
	$txtSumBikou = $_POST['txtSumBikou'];						//集計用備考欄
	//伝票種別名
	if($txtReference_KBN == ""){
		$txtReference_KBNNM = $module_sel->fDispKbn('C39',0);
	}else{
		$txtReference_KBNNM = $module_sel->fDispKbn('C39',$txtReference_KBN-1);
	}
	$txtIncident_YMD = $_POST['txtIncident_YMD'];				//伝票発行日
	$txtProgresStage_KBN = $_POST['txtProgresStage_KBN'];		//進捗状態
	$txtProgresStage_KBNNM = $module_sel->fDispKbn('C38',$txtProgresStage_KBN);		//進捗状態名
	$txtProdGrp_NM = $_POST['txtProdGrp_NM'];					//生産グループ名
	$txtCust_NM = $_POST['txtCust_NM'];							//得意先名
	$txtProdT_NM1 = $_POST['txtProdT_NM1'];						//生産担当者1
	$txtProdT_NM2 = $_POST['txtProdT_NM2'];						//生産担当者2
	$txtProdT_NM3 = $_POST['txtProdT_NM3'];						//生産担当者3
	$txtDie_NO = $_POST['txtDie_NO'];							//金型番号
	$txtExamGrp_NM = $_POST['txtExamGrp_NM'];					//検査グループ名
	$txtHingiT_NM = $_POST['txtHingiT_NM'];						//品技担当者
	$txtProd_CD = $_POST['txtProd_CD'];							//製品CD
	$txtProd_NM = $_POST['txtProd_NM'];							//製品名
	$txtDRW_NO = $_POST['txtDRW_NO'];							//仕様番号
	$txtFlawLot_NO = $_POST['txtFlawLot_NO'];					//不具合ロットNO
	$txtFlawLot_QTY = $_POST['txtFlawLot_QTY'];					//不具合数量（個）
	$txtPlating_CD = $_POST['txtPlating_CD'];					//めっき先CD
	$txtPlating_NM = $_POST['txtPlating_NM'];					//めっき先名
	$txtUnitPrice = $_POST['txtUnitPrice'];						//単価（円）
	$txtFlawPrice = $_POST['txtFlawPrice'];						//不具合金額（円）
	$cmbKBN = $_POST['cmbKBN'];									//区分
	$txtMaterialSpec = $_POST['txtMaterialSpec'];				//材料仕様
	$cmbFlaw_KBN1 = $_POST['cmbFlaw_KBN1'];						//不具合区分1
	$cmbFlaw_KBN2 = $_POST['cmbFlaw_KBN2'];						//不具合区分2
	$cmbFlaw_KBN3 = $_POST['cmbFlaw_KBN3'];						//不具合区分3
	$txtFlawContents = $_POST['txtFlawContents'];				//不具合内容
	$txtSpecial_YMD = $_POST['txtSpecial_YMD'];					//特別作業記録発行日
	$chkSpecial = $_POST['chkSpecial'];							//特別作業記録チェック
	//チェック記述に変換
	if($chkSpecial == "1"){
		$strSpecialCheck = "checked";
	}
	$txtProcessPeriod_YMD = $_POST['txtProcessPeriod_YMD'];		//処理期限
	$txtStretchReason = $_POST['txtStretchReason'];				//初期期限延伸理由
	// 2019/05/13 ADD START
	$txtSubmit_YMD1 = $_POST['txtSubmit_YMD1'];					//特別作業払い出し日1
	$txtSubmit_YMD2 = $_POST['txtSubmit_YMD2'];					//特別作業払い出し日2
	$txtSubmit_YMD3 = $_POST['txtSubmit_YMD3'];					//特別作業払い出し日3
	$txtBack_YMD1 = $_POST['txtBack_YMD1'];						//特別作業戻り日1
	$txtBack_YMD2 = $_POST['txtBack_YMD2'];						//特別作業戻り日2
	$txtBack_YMD3 = $_POST['txtBack_YMD3'];						//特別作業戻り日3
	// 2019/05/13 ADD END
	$txtIniProcPeriod_YMD = $_POST['txtIniProcPeriod_YMD'];		//初期処理期限
	$txtTanto_CD = $_POST['txtTanto_CD'];						//品証担当者CD
	$txtIncident_CD = $_POST['txtIncident_CD'];					//報告書発行先部署・協力会社CD
	$chkNonIssue = $_POST['chkNonIssue'];						//発行不要
	//チェック記述に変換
	if($chkNonIssue == "1"){
		$strNonIssueCheck = "checked";
	}
	$txtProcessLimit_YMD = $_POST['txtProcessLimit_YMD'];		//報告書処理期限
	$txtReturn_YMD = $_POST['txtReturn_YMD'];					//返却日
	$txtComplete_YMD = $_POST['txtComplete_YMD'];				//完結日
	$txtDecision_YMD = $_POST['txtDecision_YMD'];				//処理判定日
	$txtApproval_YMD = $_POST['txtApproval_YMD'];				//製造部長承認日
	$chkExcluded = $_POST['chkExcluded'];						//不良集計対象外
	//チェック記述に変換
	if($chkExcluded == "1"){
		$strExcludedCheck = "checked";
	}
	$txtSelection = $_POST['txtSelection'];						//選別工程（h）
	$cmbDueProcess_KBN = $_POST['cmbDueProcess_KBN'];			//起因工程
	$txtComments = $_POST['txtComments'];						//その他コメント
	$txtPartner_CD = $_POST['txtPartner_CD'];					//起因部署・協力会社CD
	$cmbProcess_KBN = $_POST['cmbProcess_KBN'];					//処理
	$txtFailure_QTY = $_POST['txtFailure_QTY'];					//納入数量（個）
	$txtDisposal_QTY = $_POST['txtDisposal_QTY'];				//廃棄数量（個）
	$txtReturn_QTY = $_POST['txtReturn_QTY'];					//返却数量（個）
	$txtLoss_QTY = $_POST['txtLoss_QTY'];						//調整ﾛｽ数量（個）
	$txtExclud_QTY = $_POST['txtExclud_QTY'];					//対象外数量（個）
	$txtFailurePrice = $_POST['txtFailurePrice'];				//納入金額（円）
	$txtDisposalPrice = $_POST['txtDisposalPrice'];				//廃棄金額（円）
	$txtReturnPrice = $_POST['txtReturnPrice'];					//返却金額（円）
	$txtLossPrice = $_POST['txtLossPrice'];						//調整ﾛｽ金額（円）
	$txtExcludPrice = $_POST['txtExcludPrice'];					//対象外金額（円）
	$hidUCount = $_POST['hidUCount'];							//更新回数
	$hidUp = $_POST['hidUp'];									//更新有無区分
	$hidPlan_NO = $_POST['hidPlan_NO'];							//計画NO
	$hidPlanSeq = $_POST['hidPlanSeq'];							//計画SEQ
	// 2019/08/01 ADD START
	$hidProdKbn = $_POST['hidProdKbn'];							//製品区分
	$hidDecision_OLD_YMD = $_POST['txtDecision_YMD'];			//処理判定日BK
	// 2019/08/01 ADD END

	//添付ファイルアップロード用ランダムファルダ名
	$hidTempFolder = $_POST['hidTempFolder'];

	//オブジェクトロック用変数
	$strLock = "";

	//メッセージ用変数
	$strMsg = "";
	$strErrMsg = "";

	//ボタン表示フラグ(表示:true,非表示:false)
	$bDispflg = true;

	//eValueNS集計担当者グループ未所属の場合更新ボタン非活性
	if($module_sel->fChkMstUserNS($_SESSION['login'][0]) === 0){
		$bDispflg = false;
	}

	//更新制御
	//更新時、伝票NOのみ変更不可
	if($mode <> "1"){
		$strBoxDspRefNo = "textboxdisp";
		$strUpdCtrlRefNo = "readonly";
		if($save == "1"){
			$aSave = $_GET['aSave'];
			$aSave[0] = urlencode($module_cmn->fEscape($aSave[0]));
		}
	}
	//削除、参照モード
	if($mode == "3" || $mode == "4"){
		$strBoxDsp = "textboxdisp";
		$strUpdCtrl = "readonly";
		$strCmbLock = "onmousedown=\"reset_value=this.selectedIndex\" onchange=\"this.selectedIndex=reset_value\"";
		$strBtnLock = "disabled";
	}

	//モード用変数
	$modeN = "";
	//モードの取得
	if($mode == "1"){
		$modeN ="(登録)";

		//添付ファイル用にランダムなフォルダ名を作成するためフォルダ名作成
		if($hidTempFolder == ""){
			$hidTempName = "temp_".date("YmdHis")."_".$module_cmn->fMakeRandStr(20);
			//フォルダパス作成
			$hidTempFolder  = $hidTempName;		//書類用フォルダパス
		}
		
		//初期処理期限の初期値(稼動日10日後)を取得
		if($txtIniProcPeriod_YMD == ""){
			$txtIniProcPeriod_YMD = $module_cmn->fChangDateFormat($module_sel->fLimitCalender(10));
		}
		
		//処理期限の初期値(稼動日10日後)を取得
		if($txtProcessPeriod_YMD == ""){
			$txtProcessPeriod_YMD = $module_cmn->fChangDateFormat($module_sel->fLimitCalender(10));
		}
		
		//報告書処理期限の初期値(稼動日10日後)を取得
		if($txtProcessLimit_YMD == "" && $chkNonIssue != "1"){
			$txtProcessLimit_YMD = $module_cmn->fChangDateFormat($module_sel->fLimitCalender(10));
		}
		
	}elseif($mode == "2"){
		$modeN ="(更新)";
		//伝票NO取得
		$txtReference_NO = $strReference_NO;
		$hidReference_SEQ = $strReference_SEQ;
	}elseif($mode == "3"){
		$modeN ="(削除)";
		//伝票NO取得
		$txtReference_NO = $strReference_NO;
		$hidReference_SEQ = $strReference_SEQ;
	}elseif($mode == "4"){
		$modeN ="(参照)";
		//伝票NO取得
		$txtReference_NO = $strReference_NO;
		$hidReference_SEQ = $strReference_SEQ;
	}else{
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/F_CMN0020.php?page=".$_SERVER['PHP_SELF']);
		exit;
	}

	//整理NOがある場合はフォルダ名変更
	if($txtReference_NO <> ""){
		$hidTempFolder = trim($txtReference_NO)."_".$hidReference_SEQ;
	}

	//添付資料を保存するディレクトリ
	$dir  ="upload/trouble/".$hidTempFolder."/";
	//不具合写真・報告書添付・伝票添付ファルダパス
	$dirFlaw  = $dir."flaw/";
	$dirRep  = $dir."report/";
	$dirVou  = $dir."voucher/";
	$dirSpe  = $dir."special/";

	//更新有無区分
	if(isset($_POST['hidUp'])){
		$hidUp = $_POST['hidUp'];

		//更新有無区分が１なら更新処理を行う、削除時は除く
		if($hidUp == 1){
			//=============================================
			//リロード対策
			//=============================================
			// 送信されたトークンがセッションのトークン配列の中にあるか調べる
			$key = array_search($_POST['token'], $_SESSION['token']);
			if ($key !== false) {
				// 正常な POST
				unset($_SESSION['token'][$key]);	//使用済みトークンを破棄
			}

			//伝票NO,伝票SEQの取得
			$txtReference_NO = $_POST['txtReference_NO'];
			$hidReference_SEQ = $_POST['hidReference_SEQ'];

			//チェック処理
			//セッションチェック(セッションが書き換えられていないか)
			if($_POST['hidTantoCd'] != $_SESSION['login'][0] && $hidFrame == 0){
				$strErrMsg = $module_sel->fMsgSearch("E034","");
			}

			//必須チェック
			if($_POST['cmbTargetSection_KBN'] == -1){
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbTargetSection_KBN'],"対象部門");
			}
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtBusyo_CD'],"起因部署CD");
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtReference_NO'],"伝票NO");
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtFlawLot_NO'],"不具合ロットNO");
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtFlawLot_QTY'],"不具合数量（個）");
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtUnitPrice'],"単価（円）");
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtFlawPrice'],"不具合金額（円）");
			if($_POST['cmbKBN'] == -1){
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbKBN'],"区分");
			}
			if($_POST['cmbFlaw_KBN1'] == -1 && $_POST['cmbFlaw_KBN2'] == -1 && $_POST['cmbFlaw_KBN3'] == -1){
				$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbFlaw_KBN1'],"不具合区分1～3");
			}
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtProcessPeriod_YMD'],"処理期限");
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtTanto_CD'],"品証担当者CD");

			//処理期限が初期処理期限と異なる場合必須
			if(str_replace("/","",$_POST['txtIniProcPeriod_YMD']) <> str_replace("/","",$_POST['txtProcessPeriod_YMD'])){
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtStretchReason'],"処理期限延伸理由");
			}

			//発行不要にチェックが入っていない場合は必須
			if($chkNonIssue != "1"){
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtIncident_CD'],"報告書発行先部署・協力会社CD");
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtProcessLimit_YMD'],"報告書処理期限");
			}

			//処理判定日が入力されていたら必須
			if($_POST['txtDecision_YMD'] <> ""){
				$flgQty = false;
				$flgPrice = false;
				if($_POST['cmbDueProcess_KBN'] == -1){
					$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbDueProcess_KBN'],"起因工程");
				}
				//起因工程が「その他」の場合は、その他コメントへの入力必須
				if($_POST['cmbDueProcess_KBN'] == "12" ){
					$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtComments'],"その他コメント");
				}
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtPartner_CD'],"起因部署・協力会社CD");
				if($_POST['cmbProcess_KBN'] == -1){
					$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbProcess_KBN'],"処理");
				}
				if($_POST['txtFailure_QTY'] == "" && $_POST['txtDisposal_QTY'] == "" && 
				   $_POST['txtReturn_QTY'] == "" && $_POST['txtLoss_QTY'] == ""){
					$strErrMsg = $strErrMsg."E004 必須項目を入力してください[数量のどれか]<BR>";
				}
				if($_POST['txtFailurePrice'] == "" && $_POST['txtDisposalPrice'] == "" && 
				   $_POST['txtReturnPrice'] == "" && $_POST['txtLossPrice'] == ""){
					$strErrMsg = $strErrMsg."E004 必須項目を入力してください[金額のどれか]<BR>";
				}
			}

			//文字数チェック
			//2019/08/01 ADD START
			$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck(trim($_POST['txtFlawLot_NO']),50,"不具合ロットNO");		//不具合ロットNO
			//2019/08/01 ADD END
			$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($_POST['txtSumBikou'],500,"集計用備考欄");				//集計用備考欄
			$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($_POST['txtFlawContents'],500,"不具合内容");			//不具合内容
			$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($_POST['txtStretchReason'],100,"処理期限延伸理由");		//処理期限延伸理由
			if($_POST['txtDecision_YMD'] <> "" && $_POST['cmbDueProcess_KBN'] == "12"){
				$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($_POST['txtComments'],100,"その他コメント");			//その他コメント
			}

			//必須チェックでエラーがなければフォーマットチェック
			if($strErrMsg == ""){
			//フォーマットチェック
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtFlawLot_QTY'],10,3,false,true,"不具合数量（個）");					//不具合数量（個）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtUnitPrice'],10,7,false,true,"単価（円）");							//単価（円）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtFlawPrice'],10,3,false,true,"不具合金額（円）");						//不具合金額（円）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtSelection'],10,3,false,true,"選別工程（h）");						//選別工程（h）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtFailure_QTY'],10,3,false,true,"納入数量（個）");						//納入数量（個）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtDisposal_QTY'],10,3,false,true,"廃棄数量（個）");					//廃棄数量（個）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtReturn_QTY'],10,3,false,true,"返却数量（個）");						//返却数量（個）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtLoss_QTY'],10,3,false,true,"調整ﾛｽ数量（個）");						//調整ﾛｽ数量（個）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtExclud_QTY'],10,3,false,true,"対象外数量（個）");					//対象外数量（個）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtFailurePrice'],10,3,false,true,"納入金額（円）");					//納入金額（円）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtDisposalPrice'],10,3,false,true,"廃棄金額（円）");					//廃棄金額（円）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtReturnPrice'],10,3,false,true,"返却金額（円）");						//返却金額（円）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtLossPrice'],10,3,false,true,"調整ﾛｽ金額（円）");						//調整ﾛｽ金額（円）
				$strErrMsg = $strErrMsg.$module_cmn->fNumericCheck($_POST['txtExcludPrice'],10,3,false,true,"対象外金額（円）");					//対象外金額（円）
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtSpecial_YMD']),"特別作業記録発行日");				//特別作業記録発行日
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtProcessPeriod_YMD']),"処理期限");					//処理期限
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtIniProcPeriod_YMD']),"初期処理期限");				//初期処理期限
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtProcessLimit_YMD']),"報告書処理期限");				//報告書処理期限
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtReturn_YMD']),"返却日");							//返却日
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtComplete_YMD']),"完結日");							//完結日
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtDecision_YMD']),"処理判定日");						//処理判定日
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtApproval_YMD']),"製造部長承認日");					//製造部長承認日
			}

			//フォーマットチェックでエラーがなければ整合性チェック
			if($strErrMsg == ""){
				//報告書処理期限に日付入力かつ発行不要にチェックが入っていた場合エラー表示
				if($_POST['txtProcessLimit_YMD'] != "" && $chkNonIssue == "1"){
					$strErrMsg = $strErrMsg."報告書処理期限入力時は発行不要のチェックを外してください<BR>";
				}
				//処理判定日が入力されていたら必須
				if($_POST['txtDecision_YMD'] <> "" ){
					//「納入数量（個）」、「廃棄数量（個）」、「返却数量（個）」、「調整ﾛｽ数量（個）」、「対象外数量（個）」の合計数量が不具合数量と合わない場合はエラー
					if((int)str_replace(",","",$_POST['txtFlawLot_QTY']) <> ((int)str_replace(",","",$_POST['txtFailure_QTY']) + (int)str_replace(",","",$_POST['txtDisposal_QTY']) + (int)str_replace(",","",$_POST['txtReturn_QTY']) + (int)str_replace(",","",$_POST['txtLoss_QTY']) + (int)str_replace(",","",$_POST['txtExclud_QTY']))){
						$strErrMsg = $strErrMsg."数量の合計が不具合数量と合っていません<BR>";
					}
					//「納入金額（円）」、「廃棄金額（円）」、「返却金額（円）」、「調整ﾛｽ金額（円）」、「対象外金額（円）」の合計金額が不具合金額と合わない場合はエラー
					if((int)str_replace(",","",$_POST['txtFlawPrice']) <> ((int)str_replace(",","",$_POST['txtFailurePrice']) + (int)str_replace(",","",$_POST['txtDisposalPrice']) + (int)str_replace(",","",$_POST['txtReturnPrice']) + (int)str_replace(",","",$_POST['txtLossPrice']) + (int)str_replace(",","",$_POST['txtExcludPrice']))){
						$strErrMsg = $strErrMsg."金額の合計が不具合金額と合っていません<BR>";
					}
					//処理によって数量・金額の入力状態チェック
					switch ($_POST['cmbProcess_KBN']){
						case '0':		//納入：納入以外入力されている場合はエラー
							if($_POST['txtDisposal_QTY'] <> ""){
								$strErrMsg = $strErrMsg."納入処理で廃棄数量が入力されています<BR>";
							}
							if($_POST['txtReturn_QTY'] <> ""){
								$strErrMsg = $strErrMsg."納入処理で返却数量が入力されています<BR>";
							}
							if($_POST['txtLoss_QTY'] <> ""){
								$strErrMsg = $strErrMsg."納入処理で調整ﾛｽ数量が入力されています<BR>";
							}
							if($_POST['txtDisposalPrice'] <> ""){
								$strErrMsg = $strErrMsg."納入処理で廃棄金額が入力されています<BR>";
							}
							if($_POST['txtReturnPrice'] <> ""){
								$strErrMsg = $strErrMsg."納入処理で返却金額が入力されています<BR>";
							}
							if($_POST['txtLossPrice'] <> ""){
								$strErrMsg = $strErrMsg."納入処理で調整ﾛｽ金額が入力されています<BR>";
							}
							break;
						case '1':		//廃棄：廃棄以外入力されている場合はエラー
							if($_POST['txtFailure_QTY'] <> ""){
								$strErrMsg = $strErrMsg."廃棄処理で納入数量が入力されています<BR>";
							}
							if($_POST['txtReturn_QTY'] <> ""){
								$strErrMsg = $strErrMsg."廃棄処理で返却数量が入力されています<BR>";
							}
							if($_POST['txtLoss_QTY'] <> ""){
								$strErrMsg = $strErrMsg."廃棄処理で調整ﾛｽ数量が入力されています<BR>";
							}
							if($_POST['txtFailurePrice'] <> ""){
								$strErrMsg = $strErrMsg."廃棄処理で納入金額が入力されています<BR>";
							}
							if($_POST['txtReturnPrice'] <> ""){
								$strErrMsg = $strErrMsg."廃棄処理で返却金額が入力されています<BR>";
							}
							if($_POST['txtLossPrice'] <> ""){
								$strErrMsg = $strErrMsg."廃棄処理で調整ﾛｽ金額が入力されています<BR>";
							}
							break;
						case '2':		//返品：返却以外入力されている場合はエラー
							if($_POST['txtFailure_QTY'] <> ""){
								$strErrMsg = $strErrMsg."返却処理で納入数量が入力されています<BR>";
							}
							if($_POST['txtDisposal_QTY'] <> ""){
								$strErrMsg = $strErrMsg."返却処理で廃棄数量が入力されています<BR>";
							}
							if($_POST['txtLoss_QTY'] <> ""){
								$strErrMsg = $strErrMsg."返却処理で調整ﾛｽ数量が入力されています<BR>";
							}
							if($_POST['txtFailurePrice'] <> ""){
								$strErrMsg = $strErrMsg."返却処理で納入金額が入力されています<BR>";
							}
							if($_POST['txtDisposalPrice'] <> ""){
								$strErrMsg = $strErrMsg."返却処理で廃棄金額が入力されています<BR>";
							}
							if($_POST['txtLossPrice'] <> ""){
								$strErrMsg = $strErrMsg."返却処理で調整ﾛｽ金額が入力されています<BR>";
							}
							break;
						case '3':		//一部納品廃棄：納入・廃棄以外入力されている場合はエラー
							if($_POST['txtReturn_QTY'] <> ""){
								$strErrMsg = $strErrMsg."一部納品廃棄処理で返却数量が入力されています<BR>";
							}
							if($_POST['txtLoss_QTY'] <> ""){
								$strErrMsg = $strErrMsg."一部納品廃棄処理で調整ﾛｽ数量が入力されています<BR>";
							}
							if($_POST['txtReturnPrice'] <> ""){
								$strErrMsg = $strErrMsg."一部納品廃棄処理で返却金額が入力されています<BR>";
							}
							if($_POST['txtLossPrice'] <> ""){
								$strErrMsg = $strErrMsg."一部納品廃棄処理で調整ﾛｽ金額が入力されています<BR>";
							}
							break;
						case '4':		//一部納品返品：納入・返品以外入力されている場合はエラー
							if($_POST['txtDisposal_QTY'] <> ""){
								$strErrMsg = $strErrMsg."一部納品返品処理で廃棄数量が入力されています<BR>";
							}
							if($_POST['txtLoss_QTY'] <> ""){
								$strErrMsg = $strErrMsg."一部納品返品処理で調整ﾛｽ数量が入力されています<BR>";
							}
							if($_POST['txtDisposalPrice'] <> ""){
								$strErrMsg = $strErrMsg."一部納品返品処理で廃棄金額が入力されています<BR>";
							}
							if($_POST['txtLossPrice'] <> ""){
								$strErrMsg = $strErrMsg."一部納品返品処理で調整ﾛｽ金額が入力されています<BR>";
							}
							break;
						default:		//調整ﾛｽ又は指定なしはチェックなし
							break;
					}
				}
			}

			//整合性チェックでエラーがなければ存在チェック
			if($strErrMsg == ""){
				//存在チェック
				
				//起因部署CD
				if($_POST['txtBusyo_CD'] == 0){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtBusyo_CD'],"起因部署CD","V_FL_CUST_INFO","C_CUST_CD");
				}
				//品証担当者CD
				if($_POST['txtTanto_CD'] == 0){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtTanto_CD'],"品証担当者CD","V_FL_TANTO_INFO","C_TANTO_CD");
				}
				if($_POST['txtDecision_YMD'] <> "" ){
				//起因部署・協力会社CD
					if($_POST['txtPartner_CD'] == 0){
						$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtPartner_CD'],"起因部署・協力会社CD","V_FL_CUST_INFO","C_CUST_CD");
					}
				}
				if($chkNonIssue != "1"){
					//報告書発行先部署・協力会社CD
					if($_POST['txtIncident_CD'] == 0){
						$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtIncident_CD'],"報告書発行先部署・協力会社CD","V_FL_CUST_INFO","C_CUST_CD");
					}
				}
			}

			//起因部署・協力会社CD入力されていたら起因部署CDも同CDで合わせる
			if($_POST['txtPartner_CD'] <> 0){
				if($_POST['txtBusyo_CD'] <> $_POST['txtPartner_CD']){
					$_POST['txtBusyo_CD'] = $_POST['txtPartner_CD'];
				}
			}

			//データ存在チェックでエラーがなければ更新回数チェック(登録以外のみ)
			if($strErrMsg == ""  && $mode <> "1"){
				//更新回数チェック
				if(!$module_sel->fKoshinCheck2($_POST['txtReference_NO'],$_POST['hidReference_SEQ'],$_POST['hidUCount'],"赤伝緑伝情報入力","T_TR_TRBL","C_REFERENCE_NO","N_REFERENCE_SEQ")){
					$strErrMsg = $module_sel->fMsgSearch("E002","伝票NO:".$_POST['txtReference_NO']);
				}
			}

			//エラーメッセージがなければ更新処理を実行
			if($strErrMsg == ""){
				//更新処理戻り値用変数(伝票NOが入る)
				$aExcutePara = array();
				//Oracleへの接続の確立(トランザクション開始)
				$conn = $module_upd->fTransactionStart();
				$Reference_NO = $_POST['txtReference_NO'];
				$Reference_SEQ = $_POST['hidReference_SEQ'];

				//2019/08/01 ADD START
				//処理判定確定メール（MDのみ）
				$iDecMailFlg = 0;
				if($_POST['txtReference_KBN'] == 'M'){
					if($mode == 1){
						if($_POST['txtDecision_YMD'] <> ''){
							$iDecMailFlg = 1;
						}
					}elseif($mode == 2){
						$iYmd = fChkTrblDecision($Reference_NO,$Reference_SEQ);
						//元々処理判定日が未入力の状態から入力された場合
						if($_POST['txtDecision_YMD'] <> '' and $iYmd == 0){
							$iDecMailFlg = 1;
						}
						//元々処理判定日が変更された場合
						if($_POST['txtDecision_YMD'] <> '' and $_POST['txtDecision_YMD'] <> $iYmd){
							$iDecMailFlg = 1;
						}
					}
				}
				//2019/08/01 ADD END
				
				//更新処理
				$aExcutePara = $module_upd->fTrblTorokuExcute($conn,$mode,$Reference_NO,$Reference_SEQ,$_SESSION['login'],$_POST['hidUCount']);

				//登録時添付ファイルがあれば伝票NOでフォルダ名を変更する
				if(($mode == '1') && file_exists($dir)){
					//フォルダ名変更
					$hidTempFolder = trim($txtReference_NO)."_".$hidReference_SEQ;
					if(!rename($dir,"upload/trouble/".trim($txtReference_NO)."_".$hidReference_SEQ."/")){
						$aExcutePara[0] = "err";
					}else{
						//添付ファイルフォルダ名を伝票NO_伝票SEQで置き換え
						$hidTempFolder = trim($txtReference_NO)."_".$hidReference_SEQ;
					}
				}

				//更新処理の結果判断
				if( substr($aExcutePara[0],0,3) <> "err"){
					//赤伝緑伝情報登録時
					if($mode == '1'){
						//更新した伝票NO,伝票SEQを戻す
						$txtReference_NO = $aExcutePara[0];
						$hidReference_SEQ = $aExcutePara[1];

						//添付ファルダ名クリア
						$hidTempFolder = "";

						//トランザクション処理とOracle切断
						$module_upd->fTransactionEnd($conn,true);
						$strMsg = $module_sel->fMsgSearch("N001","伝票NO:".$txtReference_NO." 伝票SEQ:".$hidReference_SEQ);		//登録しました
						//伝票NO,伝票SEQクリア
						$txtReference_NO = "";
						$hidReference_SEQ = "";

						//2019/05/13 ADD START
						//緑伝登録時、メール送信（MD端子の場合はメール送信しない）
						if(trim($txtReference_KBN) == "2" and $hidProdKbn <> "2"){
							
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
							$to = $module_sel->fMailAddressGet($_POST['cmbTargetSection_KBN'],"TRBL");

							//取得アドレスが存在した場合
							if($to != ""){
								$aTo = array();
								//アドレスを配列に格納
								$aTo = explode(",",$to);
								
								//メール送信者
								$senderAddress = "announce@suzukinet.co.jp";
								$senderName = "品質管理システム";

								//メール本文作成
								$message = "\nこのメールは自動配信メールです。\n";
								$message = $message."このメールには返信しないで下さい。\n";
								$message = $message."\n";
								$message = $message."得意先返却処理伝票が登録されました。\n";
								$message = $message."\n";
								switch($_POST['cmbTargetSection_KBN']){
									case 'F':
										//メール件名
										$messageSubject = "【品質管理自動送信メール】コネクタ得意先返却処理伝票登録通知_".$aExcutePara[0];
										$message = $message."対象部門：コネクタ\n";
										break;
									case 'M':
										//メール件名
										$messageSubject = "【品質管理自動送信メール】モールド得意先返却処理伝票登録通知_".$aExcutePara[0];
										$message = $message."対象部門：モールド\n";
										break;
									case 'K':
										//メール件名
										$messageSubject = "【品質管理自動送信メール】めっき得意先返却処理伝票登録通知_".$aExcutePara[0];
										$message = $message."対象部門：めっき\n";
										break;
									default:
										//メール件名
										$messageSubject = "【品質管理自動送信メール】得意先返却処理伝票登録通知_".$aExcutePara[0];
										$message = $message."対象部門：不明\n";
										break;
								}
								$message = $message."伝票NO：".$aExcutePara[0]."\n";
								$message = $message."伝票発行日：".$module_cmn->fChangDateFormat4($_POST['txtIncident_YMD'])."\n";
								$message = $message."\n";
								$message = $message."製品名：".$_POST['txtProd_NM']."\n";
								$message = $message."仕様番号：".$_POST['txtDRW_NO']."\n";
								$message = $message."得意先名：".$_POST['txtCust_NM']."\n";
								$message = $message."\n";
								$message = $message."不具合ロットNO：".$_POST['txtFlawLot_NO']."\n";
								$message = $message."不具合数量：".$_POST['txtFlawLot_QTY']."\n";
								$message = $message."不具合金額：".$_POST['txtFlawPrice']."\n";
								$message = $message."不具合内容：".$_POST['txtFlawContents']."\n";
								$message = $message."\n";
								$message = $message."(品質管理システム)----> http://".php_uname('n')."/FL/F_FLK0080.php?mode=2&rrcno=".$aExcutePara[0]."&rrcseq=".$aExcutePara[1]." \n";
								$message = $message.$msg."\n";
								
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
									$mail->addAddress($aTo[$n]);
									$n++;
								}
								
								$mail->setFrom($senderAddress,$senderName);
								$mail->Subject = $messageSubject;
								$mail->Body = $message;
								//通知メール送信処理
								if ($mail->send()){
									//echo "送信されました。";
								}else{
									$strMsg = "";
									$strErrMsg = $module_sel->fMsgSearch("E026",$mail->ErrorInfo);	//メール送信に失敗しました
								}
							}
						}
						//伝票NO、SEQクリア
						$txtReference_NO = "";
						$hidReference_SEQ = "";
						//2019/05/13 ADD END
					}else{
						//更新した伝票NO、SEQを戻す
						$txtReference_NO = $aExcutePara[0];
						$hidReference_SEQ = $aExcutePara[1];

						//更新回数カウントアップ
						$hidUCount = $hidUCount + 1;

						//トランザクション処理とOracle切断
						$module_upd->fTransactionEnd($conn,true);
						if($mode == '2'){
							$strMsg = $module_sel->fMsgSearch("N002","伝票NO:".$txtReference_NO." 伝票SEQ:".$hidReference_SEQ);	//更新しました
						}else{
							$strMsg = $module_sel->fMsgSearch("N003","伝票NO:".$txtReference_NO." 伝票SEQ:".$hidReference_SEQ);	//削除しました
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
				//処理判定確定メール通知確認メッセージ（対応予定）
				if($aMail[0] = 1){
/* 					//iniファイルの読み込み
					// セクションを意識してパースします。
					$aIni = Array();
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
					$to = $module_sel->fMailAddressGet($_POST['cmbTargetSection_KBN'],"TRBL");

					//取得アドレスが存在した場合
					if($to != ""){
						$aTo = array();
						//アドレスを配列に格納
						$aTo = explode(",",$to);
						
						//メール送信者
						$senderAddress = "announce@suzukinet.co.jp";
						$senderName = "品質管理システム";

						//メール本文作成
						$message = "\nこのメールは自動配信メールです。\n";
						$message = $message."このメールには返信しないで下さい。\n";
						$message = $message."\n";
						$message = $message."得意先返却処理伝票が登録されました。\n";
						$message = $message."\n";
						switch($_POST['cmbTargetSection_KBN']){
							case 'F':
								//メール件名
								$messageSubject = "【品質管理自動送信メール】コネクタ得意先返却処理伝票登録通知_".$aExcutePara[0];
								$message = $message."対象部門：コネクタ\n";
								break;
							case 'M':
								//メール件名
								$messageSubject = "【品質管理自動送信メール】モールド得意先返却処理伝票登録通知_".$aExcutePara[0];
								$message = $message."対象部門：モールド\n";
								break;
							case 'K':
								//メール件名
								$messageSubject = "【品質管理自動送信メール】めっき得意先返却処理伝票登録通知_".$aExcutePara[0];
								$message = $message."対象部門：めっき\n";
								break;
							default:
								//メール件名
								$messageSubject = "【品質管理自動送信メール】得意先返却処理伝票登録通知_".$aExcutePara[0];
								$message = $message."対象部門：不明\n";
								break;
						}
						$message = $message."伝票NO：".$aExcutePara[0]."\n";
						$message = $message."伝票発行日：".$module_cmn->fChangDateFormat4($_POST['txtIncident_YMD'])."\n";
						$message = $message."\n";
						$message = $message."製品名：".$_POST['txtProd_NM']."\n";
						$message = $message."仕様番号：".$_POST['txtDRW_NO']."\n";
						$message = $message."得意先名：".$_POST['txtCust_NM']."\n";
						$message = $message."\n";
						$message = $message."不具合ロットNO：".$_POST['txtFlawLot_NO']."\n";
						$message = $message."不具合数量：".$_POST['txtFlawLot_QTY']."\n";
						$message = $message."不具合金額：".$_POST['txtFlawPrice']."\n";
						$message = $message."不具合内容：".$_POST['txtFlawContents']."\n";
						$message = $message."\n";
						$message = $message."(品質管理システム)----> http://".php_uname('n')."/FL/F_FLK0080.php?mode=2&rrcno=".$aExcutePara[0]."&rrcseq=".$aExcutePara[1]." \n";
						$message = $message.$msg."\n";
						$mail = new JPHPMailer();   //文字コード設定
						
						//SMTP接続
						$mail->IsSMTP();
						$mail->SMTPAuth = false;
						$mail->SMTPSecure = 'tls';
						$mail->Host = $strMailServer;
						$mail->Port = 25;
						$mail->in_enc = "UTF-8";
						
						$n = 0;
						while($n < count($aTo)){
							$mail->addTo($aTo[$n]);
							$n++;
						}
						
						$mail->setFrom($senderAddress,$senderName);
						$mail->setSubject($messageSubject );
						$mail->setBody($message);
						//通知メール送信処理
						if ($mail->send()){
							//echo "送信されました。";
						}else{
							$strMsg = "";
							$strErrMsg = $module_sel->fMsgSearch("E026",$mail->getErrorMessage());	//メール送信に失敗しました
						}
					} */
				}
			}
		}
	}

	//日本語を省くための正規表現
	$pattern="/^[a-z0-9A-Z\-_]+\.[a-zA-Z]{3}$/";

	//サイズチェック
	if($_SERVER['CONTENT_LENGTH'] > 10485760){
		$strErrMsg = $module_sel->fMsgSearch("E008","");
	}else{
		//リクエストがPOSTかどうかチェック
		if($_SERVER["REQUEST_METHOD"]=="POST" && !empty ($_POST)){
			
			
			
			//フォームボタンのvalueを取得する
			$hidTemp = $_POST["hidTemp"];		//0:不具合写真 1:報告書 2:伝票
			$hidAction = $_POST["hidAction"];	//0:アップロード 1:削除

			
			//伝票NO,伝票SEQ退避
			$txtReference_NO = $_POST['txtReference_NO'];
			$txtReference_SEQ = $_POST['hidReference_SEQ'];

			if($hidTemp == ""){
				
			}
			/*-------------------------------------------------------
			不具合写真アップロード・削除する処理
			--------------------------------------------------------*/
			elseif($hidTemp == 0){ 
				$strErrMsg = $module_cmn->fFileUploadDelete($hidAction,"getFlawPhoto","deletefile",$dir,$dirFlaw,"不具合写真");
			}
			/*-------------------------------------------------------
			報告書アップロード・削除する処理
			--------------------------------------------------------*/
			elseif($hidTemp == 1){ 
				$strErrMsg = $module_cmn->fFileUploadDelete($hidAction,"getReport","deletefile2",$dir,$dirRep,"報告書");
			}
			/*-------------------------------------------------------
			伝票アップロード・削除する処理
			--------------------------------------------------------*/
			elseif($hidTemp == 2){ 
				$strErrMsg = $module_cmn->fFileUploadDelete($hidAction,"getVoucher","deletefile3",$dir,$dirVou,"伝票");
			}
			/*-------------------------------------------------------
			特別作業記録アップロード・削除する処理
			--------------------------------------------------------*/
			elseif($hidTemp == 3){ 
				$strErrMsg = $module_cmn->fFileUploadDelete($hidAction,"getSpecial","deletefile4",$dir,$dirSpe,"特別作業記録");
			}
		}
	}

	//発行不要のチェックボックス
	if($chkNonIssue == "1" ){
		$strNonIssueCheck = "checked";
	}else{
		$strNonIssueCheck = "";
	}

	//不良集計対象外のチェックボックス
	if($chkExcluded == "1" ){
		$strExcludedCheck = "checked";
	}else{
		$strExcludedCheck = "";
	}

	//特別作業記録のチェックボックス
	if($chkSpecial == "1" ){
		$strSpecialCheck = "checked";
	}else{
		$strSpecialCheck = "";
	}
	
	//登録モード以外はデータ取得を行う
	if($mode <> "1" && $txtReference_NO <> "" && $strErrMsg == ""){
		if(isset($_GET['aSave'])) {
			$aSave = $_GET['aSave'];
			$txtReference_NO		= $aSave[0];										//伝票NO
			$hidReference_SEQ		= $aSave[1];										//伝票SEQ
			$txtReference_KBN		= $aSave[2];										//伝票種別
			$txtReference_KBNNM 	= $module_sel->fDispKbn('C39',$txtReference_KBN-1);	//伝票種別名
			$cmbTargetSection_KBN	= $aSave[3];										//対象部門
			$txtPointRef_NO			= $aSave[4];										//代表伝票NO
			$txtBusyo_CD			= $aSave[5];										//起因部署CD
			$txtSumBikou			= $aSave[6];										//集計用備考欄
			$txtIncident_YMD		= $module_cmn->fChangDateFormat($aSave[7]);			//伝票発行日
			$txtProgresStage_KBN	= $aSave[8];										//進捗状態
			$txtProgresStage_KBNNM	= $module_sel->fDispKbn('C38',$txtProgresStage_KBN);//進捗状態名
			$txtProdGrp_NM			= $aSave[9];										//生産グループ名
			$txtCust_NM				= $aSave[10];										//得意先名
			$txtProdT_NM1			= $aSave[11];										//生産担当者1
			$txtProdT_NM2			= $aSave[12];										//生産担当者2
			$txtProdT_NM3			= $aSave[13];										//生産担当者3
			$txtExamGrp_NM			= $aSave[14];										//検査グループ名
			$txtHingiT_NM			= $aSave[15];										//品技担当者
			$txtProd_CD				= $aSave[16];										//製品CD
			$txtDie_NO				= $aSave[17];										//金型番号
			$txtProd_NM				= $aSave[18];										//製品名
			$txtDRW_NO				= $aSave[19];										//仕様番号
			$txtFlawLot_NO			= $aSave[20];										//不具合ロットNO
			$txtFlawLot_QTY			= $aSave[21];										//不具合数量（個）
			$txtUnitPrice			= $aSave[22];										//単価（円）
			$txtFlawPrice			= $aSave[23];										//不具合金額（円）
			$txtPlating_CD			= $aSave[24];										//めっき先CD
			$txtPlating_NM			= $aSave[25];										//めっき先名
			$cmbKBN					= $aSave[26];										//区分
			$txtMaterialSpec		= $aSave[27];										//材料仕様
			$cmbFlaw_KBN1			= $aSave[28];										//不具合区分1
			$cmbFlaw_KBN2			= $aSave[29];										//不具合区分2
			$cmbFlaw_KBN3			= $aSave[30];										//不具合区分3
			$txtFlawContents		= $aSave[31];										//不具合内容
			$txtSpecial_YMD			= $aSave[32];										//特別作業記録発行日
			$txtProcessPeriod_YMD	= $aSave[33];										//処理期限
			$txtStretchReason		= $aSave[34];										//初期期限延伸理由
			$txtIniProcPeriod_YMD	= $aSave[35];										//初期処理期限
			$txtTanto_CD			= $aSave[36];										//品証担当者CD
			$chkNonIssue			= $aSave[37];										//発行不要
			$strNonIssueCheck		= str_replace(1,"checked",$aSave[37]);				//発行不要チェック
			$txtIncident_CD			= $aSave[38];										//報告書発行先部署・協力会社CD
			$txtProcessLimit_YMD	= $aSave[39];										//報告書処理期限
			$txtReturn_YMD			= $aSave[40];										//返却日
			$txtComplete_YMD		= $aSave[41];										//完結日
			$txtDecision_YMD		= $aSave[42];										//処理判定日
			$txtApproval_YMD		= $aSave[43];										//製造部長承認日
			$chkExcluded			= $aSave[44];										//不良集計対象外
			$strExcludedCheck		= str_replace(1,"checked",$aSave[44]);				//不良集計対象外
			$txtSelection			= $aSave[45];										//選別工数（h）
			$cmbDueProcess_KBN		= $aSave[46];										//起因工程
			$txtComments			= $aSave[47];										//その他コメント
			$txtPartner_CD			= $aSave[48];										//起因部署・協力会社CD
			$cmbProcess_KBN			= $aSave[49];										//処理
			$txtFailure_QTY			= $aSave[50];										//納入数量（個）
			$txtDisposal_QTY		= $aSave[51];										//廃棄数量（個）
			$txtReturn_QTY			= $aSave[52];										//返却数量（個）
			$txtLoss_QTY			= $aSave[53];										//調整ﾛｽ数量（個）
			$txtExclud_QTY			= $aSave[54];										//対象外数量（個）
			$txtFailurePrice		= $aSave[55];										//納入金額（円）
			$txtDisposalPrice		= $aSave[56];										//廃棄金額（円）
			$txtReturnPrice			= $aSave[57];										//返却金額（円）
			$txtLossPrice			= $aSave[58];										//調整ﾛｽ金額（円）
			$txtExcludPrice			= $aSave[59];										//対象外金額（円）
			$hidUCount 				= $aSave[60]; 										//更新回数
			$txtBusyo_NM 			= $aSave[61]; 										//起因部署名
			$txtTanto_NM 			= $aSave[62]; 										//品証担当者名
			$txtIncident_NM 		= $aSave[63]; 										//報告書発行先部署・協力会社名
			$txtPartner_NM 			= $aSave[64]; 										//起因部署・協力会社名
			$hidPlan_NO 			= $aSave[65]; 										//計画NO
			$hidPlanSeq 			= $aSave[66]; 										//計画SEQ
			// 2019/05/13 ADD START
			$txtSubmit_YMD1 		= $aSave[67]; 										//特別作業払い出し日1
			$txtSubmit_YMD2 		= $aSave[68]; 										//特別作業払い出し日2
			$txtSubmit_YMD3 		= $aSave[69]; 										//特別作業払い出し日3
			$txtBack_YMD1 			= $aSave[70]; 										//特別作業戻り日1
			$txtBack_YMD2 			= $aSave[71]; 										//特別作業戻り日2
			$txtBack_YMD3 			= $aSave[72]; 										//特別作業戻り日3
			// 2019/05/13 ADD END
			$chkSpecial				= $aSave[73];										//特別作業記録チェック
			$strSpecialCheck		= str_replace(1,"checked",$aSave[73]);				//特別作業記録チェック
			$hidProdKbn 			= $aSave[74]; 										//製品区分
			$hidDecision_OLD_YMD	= $aSave[42];										//処理判定日BK
		}
		elseif($mode == "3" && $hidUp == "1"){
			//削除後は表示そのまま
		}
		else{
			//再検索処理
			$aPara = $module_sel->fGetTrblData($txtReference_NO,$hidReference_SEQ);
			$txtReference_NO		= $aPara[0];										//伝票NO
			$hidReference_SEQ		= $aPara[1];										//伝票SEQ
			$txtReference_KBN		= $aPara[2];										//伝票種別
			$txtReference_KBNNM 	= $module_sel->fDispKbn('C39',$txtReference_KBN-1);	//伝票種別名
			$cmbTargetSection_KBN	= $aPara[3];										//対象部門
			$txtPointRef_NO			= $aPara[4];										//代表伝票NO
			$txtBusyo_CD			= $aPara[5];										//起因部署CD
			$txtSumBikou			= $aPara[6];										//集計用備考欄
			$txtIncident_YMD		= $module_cmn->fChangDateFormat($aPara[7]);			//伝票発行日
			$txtProgresStage_KBN	= $aPara[8];										//進捗状態
			$txtProgresStage_KBNNM	= $module_sel->fDispKbn('C38',$txtProgresStage_KBN);//進捗状態名
			$txtProdGrp_NM			= $aPara[9];										//生産グループ名
			$txtCust_NM				= $aPara[10];										//得意先名
			$txtProdT_NM1			= $aPara[11];										//生産担当者1
			$txtProdT_NM2			= $aPara[12];										//生産担当者2
			$txtProdT_NM3			= $aPara[13];										//生産担当者3
			$txtExamGrp_NM			= $aPara[14];										//検査グループ名
			$txtHingiT_NM			= $aPara[15];										//品技担当者
			$txtProd_CD				= $aPara[16];										//製品CD
			$txtDie_NO				= $aPara[17];										//金型番号
			$txtProd_NM				= $aPara[18];										//製品名
			$txtDRW_NO				= $aPara[19];										//仕様番号
			$txtFlawLot_NO			= $aPara[20];										//不具合ロットNO
			if(number_format($aPara[21]) <> 0) {
				$txtFlawLot_QTY		= number_format($aPara[21]);						//不具合数量（個）
			}else{
				$txtFlawLot_QTY		= "";
			}
			// 2019/05/13 ED START
			//$txtUnitPrice		= preg_replace("/\.?0+$/","",number_format($aPara[22],5));		//単価（円）
			//if(number_format($aPara[22],5) <> 0.00000) {
			//	$txtUnitPrice		= preg_replace("/\.?0+$/","",number_format($aPara[22],5));	//単価（円）
			$txtUnitPrice		= preg_replace("/\.?0+$/","",number_format($aPara[22],7));		//単価（円）
			if(number_format($aPara[22],7) <> 0.0000000) {
				$txtUnitPrice		= preg_replace("/\.?0+$/","",number_format($aPara[22],7));	//単価（円）
			// 2019/05/13 ED END
				
			}else{
				$txtUnitPrice		= "";
			}
			if(number_format($aPara[23]) <> 0) {
				$txtFlawPrice		= number_format($aPara[23]);						//不具合金額（円）
			}else{
				$txtFlawPrice		= "";
			}
			$txtPlating_CD			= $aPara[24];										//めっき先CD
			$txtPlating_NM			= $aPara[25];										//めっき先名
			$cmbKBN					= $aPara[26];										//区分
			$txtMaterialSpec		= $aPara[27];										//材料仕様
			$cmbFlaw_KBN1			= $aPara[28];										//不具合区分1
			$cmbFlaw_KBN2			= $aPara[29];										//不具合区分2
			$cmbFlaw_KBN3			= $aPara[30];										//不具合区分3
			$txtFlawContents		= $aPara[31];										//不具合内容
			$txtSpecial_YMD			= $module_cmn->fChangDateFormat($aPara[32]);		//特別作業記録発行日
			$chkSpecial				= $aPara[73];										//特別作業記録発行チェック
			$strSpecialCheck		= str_replace(1,"checked",$aPara[73]);				//特別作業記録発行チェック
			$txtProcessPeriod_YMD	= $module_cmn->fChangDateFormat($aPara[33]);		//処理期限
			$txtStretchReason		= $aPara[34];										//初期期限延伸理由
			// 2019/05/13 ADD START
			$txtSubmit_YMD1			= $module_cmn->fChangDateFormat($aPara[67]);		//特別作業払い出し日1
			$txtSubmit_YMD2			= $module_cmn->fChangDateFormat($aPara[68]);		//特別作業払い出し日2
			$txtSubmit_YMD3			= $module_cmn->fChangDateFormat($aPara[69]);		//特別作業払い出し日3
			$txtBack_YMD1			= $module_cmn->fChangDateFormat($aPara[70]);		//特別作業戻り日1
			$txtBack_YMD2			= $module_cmn->fChangDateFormat($aPara[71]);		//特別作業戻り日2
			$txtBack_YMD3			= $module_cmn->fChangDateFormat($aPara[72]);		//特別作業戻り日3
			// 2019/05/13 ADD END
			$txtIniProcPeriod_YMD	= $module_cmn->fChangDateFormat($aPara[35]);		//初期処理期限
			$txtTanto_CD			= $aPara[36];										//品証担当者CD
			$chkNonIssue			= $aPara[37];										//発行不要
			$strNonIssueCheck		= str_replace(1,"checked",$aPara[37]);				//発行不要チェック
			$txtIncident_CD			= $aPara[38];										//報告書発行先部署・協力会社CD
			$txtProcessLimit_YMD	= $module_cmn->fChangDateFormat($aPara[39]);		//報告書処理期限
			$txtReturn_YMD			= $module_cmn->fChangDateFormat($aPara[40]);		//返却日
			$txtComplete_YMD		= $module_cmn->fChangDateFormat($aPara[41]);		//完結日
			$txtDecision_YMD		= $module_cmn->fChangDateFormat($aPara[42]);		//処理判定日
			$txtApproval_YMD		= $module_cmn->fChangDateFormat($aPara[43]);		//製造部長承認日
			$chkExcluded			= $aPara[44];										//不良集計対象外
			$strExcludedCheck		= str_replace(1,"checked",$aPara[44]);				//不良集計対象外
			if($aPara[45] <> 0) {
				$txtSelection		= $aPara[45];										//選別工数（h）
			}else{
				$txtSelection		= "";
			}
			$cmbDueProcess_KBN		= $aPara[46];										//起因工程
			$txtComments			= $aPara[47];										//その他コメント
			$txtPartner_CD			= $aPara[48];										//起因部署・協力会社CD
			$cmbProcess_KBN			= $aPara[49];										//処理
			if(number_format($aPara[50]) <> 0) {
				$txtFailure_QTY		= number_format($aPara[50]);						//納入数量（個）
			}else{
				$txtFailure_QTY		= "";
			}
			if(number_format($aPara[51]) <> 0) {
				$txtDisposal_QTY	= number_format($aPara[51]);						//廃棄数量（個）
			}else{
				$txtDisposal_QTY	= "";
			}
			if(number_format($aPara[52]) <> 0) {
				$txtReturn_QTY		= number_format($aPara[52]);						//返却数量（個）
			}else{
				$txtReturn_QTY		= "";
			}
			if(number_format($aPara[53]) <> 0) {
				$txtLoss_QTY		= number_format($aPara[53]);						//調整ﾛｽ数量（個）
			}else{
				$txtLoss_QTY		= "";
			}
			if(number_format($aPara[54]) <> 0) {
				$txtExclud_QTY		= number_format($aPara[54]);						//対象外数量（個）
			}else{
				$txtExclud_QTY		= "";
			}
			if(number_format($aPara[55]) <> 0) {
				$txtFailurePrice		= number_format($aPara[55]);					//納入金額（円）
			}else{
				$txtFailurePrice		= "";
			}
			if(number_format($aPara[56]) <> 0) {
				$txtDisposalPrice	= number_format($aPara[56]);						//廃棄金額（円）
			}else{
				$txtDisposalPrice	= "";
			}
			if(number_format($aPara[57]) <> 0) {
				$txtReturnPrice		= number_format($aPara[57]);						//返却金額（円）
			}else{
				$txtReturnPrice		= "";
			}
			if(number_format($aPara[58]) <> 0) {
				$txtLossPrice		= number_format($aPara[58]);						//調整ﾛｽ金額（円）
			}else{
				$txtLossPrice		= "";
			}
			if(number_format($aPara[59]) <> 0) {
				$txtExcludPrice		= number_format($aPara[59]);						//対象外金額（円）
			}else{
				$txtExcludPrice		= "";
			}
			$hidUCount 				= $aPara[60]; 										//更新回数
			$txtBusyo_NM 			= $aPara[61]; 										//起因部署名
			$txtTanto_NM 			= $aPara[62]; 										//品証担当者名
			$txtIncident_NM 		= $aPara[63]; 										//報告書発行先部署・協力会社名
			$txtPartner_NM 			= $aPara[64]; 										//起因部署・協力会社名
			$hidPlan_NO 			= $aPara[65]; 										//計画NO
			$hidPlanSeq 			= $aPara[66]; 										//計画SEQ
			$hidProdKbn 			= $aSave[74]; 										//製品区分
			$hidDecision_OLD_YMD	= $module_cmn->fChangDateFormat($aPara[42]);		//処理判定日BK
		}
	}
	elseif($mode == "1" && $txtReference_NO <> "" && $strErrMsg == ""){
		//アップロード時 登録前の区分名称再取得
		if($txtBusyo_CD <> ""){
			$aTmp　= Array();
			$aTmp = $module_sel->fGetCustDataDetail($txtBusyo_CD,"","","6");
			$txtBusyo_NM 			= $aTmp[1]; 										//起因部署名
		}
		if($txtTanto_CD <> ""){
			$aTmp　= Array();
			$aTanto[0] = trim($txtTanto_CD);
			$aTmp = $module_sel->fS2TantoSearch($aTanto);
			$txtTanto_NM 			= $aTmp[1]; 										//品証担当者名
		}
		if($txtIncident_CD <> ""){
			$aTmp　= Array();
			$aTmp = $module_sel->fGetCustDataDetail($txtIncident_CD,"","","6");
			$txtIncident_NM 			= $aTmp[1]; 									//報告書発行先部署・協力会社名
		}
		if($txtPartner_CD <> ""){
			$aTmp　= Array();
			$aTmp = $module_sel->fGetCustDataDetail($txtPartner_CD,"","","6");
			$txtPartner_NM 			= $aTmp[1]; 										//起因部署・協力会社名
		}
	}
	
	//呼び出した伝票NO、伝票SEQがない場合はエラー
	if(($txtReference_NO == "" || $hidReference_SEQ == "") && $hidFrame == 1){
		$strErrMsg = $module_sel->fMsgSearch("E001","対象の赤伝緑伝情報がありません");
		$bDispflg = false;
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
<TITLE>【赤伝緑伝情報入力】</TITLE>

<style type="text/css">
	
TABLE.type08 {
	border-collapse: collapse;
	text-align: left;
	line-height: 1.5;
	border-left: 1px solid #ccc;
}

TABLE.type08 THEAD th {
	padding: 10px;
	font-weight: bold;
	border-top: 1px solid #ccc;
	border-right: 1px solid #ccc;
	border-bottom: 2px solid #c00;
	background: #dcdcd1;
}
TABLE.type08 TBODY th {
	width: 150px;
	padding: 10px;
	font-weight: bold;
	vertical-align: top;
	border-right: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
	background: #ececec;
}
TABLE.type08 td {
	width: 350px;
	padding: 10px;
	vertical-align: top;
	border-right: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
}
</style>

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
			if(!fCalCheckFormat('txtIncident_YMD','伝票発行日')){
				return false;
			}
			if(!fCalCheckFormat('txtSpecial_YMD','特別作業記録発行日')){
				return false;
			}
			if(!fCalCheckFormat('txtProcessPeriod_YMD','処理期限')){
				return false;
			}
			if(!fCalCheckFormat('txtIniProcPeriod_YMD','初期処理期限')){
				return false;
			}
			if(!fCalCheckFormat('txtProcessLimit_YMD','報告書処理期限')){
				return false;
			}
			if(!fCalCheckFormat('txtReturn_YMD','返却日')){
				return false;
			}
			if(!fCalCheckFormat('txtComplete_YMD','完結日')){
				return false;
			}
			if(!fCalCheckFormat('txtDecision_YMD','処理判定日')){
				return false;
			}
			if(!fCalCheckFormat('txtApproval_YMD','製造部長承認日')){
				return false;
			}
		}
		
		return true;
	}

	//チェック処理
	function fCheckMail(strMode){
		//登録・更新の場合チェック
		if(strMode == 1){
			if(document.form.txtIncident_YMD.value != ''){
				return true;
			}else{
				return false;
			}
		}else if(strMode == 2){
			if(document.form.txtDecision_YMD.value != '' && document.form.hidDecision_OLD_YMD.value == ''){
				return true;
			}else{
				if(document.form.txtDecision_YMD.value != '' && document.form.hidDecision_OLD_YMD.value != ''  && document.form.txtDecision_YMD.value != document.form.hidDecision_OLD_YMD.value){
					return true;
				}else{
					return false;
				}
			}
		}
		
		return true;
	}
	
	//戻るボタン
	function fReturn(strMode){
		//登録以外の場合は一覧に戻る
		if(strMode != 1){
			document.form.action ="F_FLK0090.php?action=main&search=1"
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
			&aJoken[17]=<?php echo $aJoken[17];?>";		// 2019/09/20 ADD END
			document.form.submit();
		}else{
			document.form.action ="main.php";
			document.form.submit();
		}
	}

	//確定ボタン押下時
	function fncExcute(strMode,strDialogMsg,intSave){

		var intMailFlg = 0;
		
		//チェック処理
		if(fCheck(strMode)){
			//メール通知ﾌﾗｸﾞ
			if(fCheckMail(strMode)){
				intMailFlg = 1;
			}
			//確認メッセージ
			if(window.confirm(strDialogMsg + 'してもよろしいですか？')){
				//更新時メール通知の確認メッセージ
/* 				if(strMode == 2 && intMailFlg == 1){
					if(window.confirm('処理判定確定メールを送信してもよろしいですか？\n\n送信したくない場合は[キャンセル]ボタンを押して下さい。')){
					}else{
						intMailFlg == 0;
					}
				} */
				document.form.hidUp.value = 1;
				//ヘッダの担当者コードをセット
				document.form.encoding = "multipart/form-data";
				document.form.action ="F_FLK0080.php?mode="
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
				&$aMail[0]=intMailFlg";
				document.form.submit();
			}else{
				return false;
			}
		}
	}

	//ファイルアップロード削除ボタン押下時
	function fncFileUpload(strMode,strDialogMsg,intSave,intFile,intKbn,strFileName){
		//機種依存文字チェック
		var txt = document.getElementById(strFileName).value;
		var search_txt = "[①②③④⑤⑥⑦⑧⑨⑩⑪⑫⑬⑭⑮⑯⑰⑱⑲⑳ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ㍉㌔㌢㍍㌘㌧㌃㌶㍑㍗㌍㌦㌣㌫㍊㌻㎜㎝㎞㎎㎏㏄㎡㍻〝〟№㏍℡㊤㊥㊦㊧㊨㈱㈲㈹㍾㍽㍼]";
		if(txt.match(search_txt)){
			alert("アップロードするファイル名に機種依存文字が設定されています。\nファイル名を変更して下さい。");
			return false;
		}
		
		//削除の場合はメッセージ変更
		if(strDialogMsg == "削除"){
			
		}

		//確認メッセージ
		if(window.confirm(strDialogMsg + 'してもよろしいですか？')){
			var aSave = new Array( );
			aSave = fncSetSaveArray();
			//ヘッダの担当者コードをセット
			document.form.encoding = "multipart/form-data";
			document.form.method = "POST";
			document.form.target = "_self";
			document.form.action ="F_FLK0080.php?mode="
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
					&aJoken[17]=<?php echo $aJoken[17];?>" +		// 2019/09/20 ADD END
					"&aSave[0]=" + aSave[0] +
					"&aSave[1]=" + aSave[1] +
					"&aSave[2]=" + aSave[2] +
					"&aSave[3]=" + aSave[3] +
					"&aSave[4]=" + aSave[4] +
					"&aSave[5]=" + aSave[5] +
					"&aSave[6]=" + aSave[6] +
					"&aSave[7]=" + aSave[7] +
					"&aSave[8]=" + aSave[8] +
					"&aSave[9]=" + aSave[9] +
					"&aSave[10]=" + aSave[10] +
					"&aSave[11]=" + aSave[11] +
					"&aSave[12]=" + aSave[12] +
					"&aSave[13]=" + aSave[13] +
					"&aSave[14]=" + aSave[14] +
					"&aSave[15]=" + aSave[15] +
					"&aSave[16]=" + aSave[16] +
					"&aSave[17]=" + aSave[17] +
					"&aSave[18]=" + aSave[18] +
					"&aSave[19]=" + aSave[19] +
					"&aSave[20]=" + aSave[20] +
					"&aSave[21]=" + aSave[21] +
					"&aSave[22]=" + aSave[22] +
					"&aSave[23]=" + aSave[23] +
					"&aSave[24]=" + aSave[24] +
					"&aSave[25]=" + aSave[25] +
					"&aSave[26]=" + aSave[26] +
					"&aSave[27]=" + aSave[27] +
					"&aSave[28]=" + aSave[28] +
					"&aSave[29]=" + aSave[29] +
					"&aSave[30]=" + aSave[30] +
					"&aSave[31]=" + aSave[31] +
					"&aSave[32]=" + aSave[32] +
					"&aSave[33]=" + aSave[33] +
					"&aSave[34]=" + aSave[34] +
					"&aSave[35]=" + aSave[35] +
					"&aSave[36]=" + aSave[36] +
					"&aSave[37]=" + aSave[37] +
					"&aSave[38]=" + aSave[38] +
					"&aSave[39]=" + aSave[39] +
					"&aSave[40]=" + aSave[40] +
					"&aSave[41]=" + aSave[41] +
					"&aSave[42]=" + aSave[42] +
					"&aSave[43]=" + aSave[43] +
					"&aSave[44]=" + aSave[44] +
					"&aSave[45]=" + aSave[45] +
					"&aSave[46]=" + aSave[46] +
					"&aSave[47]=" + aSave[47] +
					"&aSave[48]=" + aSave[48] +
					"&aSave[49]=" + aSave[49] +
					"&aSave[50]=" + aSave[50] +
					"&aSave[51]=" + aSave[51] +
					"&aSave[52]=" + aSave[52] +
					"&aSave[53]=" + aSave[53] +
					"&aSave[54]=" + aSave[54] +
					"&aSave[55]=" + aSave[55] +
					"&aSave[56]=" + aSave[56] +
					"&aSave[57]=" + aSave[57] +
					"&aSave[58]=" + aSave[58] +
					"&aSave[59]=" + aSave[59] +
					"&aSave[60]=" + aSave[60] +
					"&aSave[61]=" + aSave[61] +
					"&aSave[62]=" + aSave[62] +
					"&aSave[63]=" + aSave[63] +
					"&aSave[64]=" + aSave[64] +
					"&aSave[65]=" + aSave[65] +
					"&aSave[66]=" + aSave[66] +
					"&aSave[67]=" + aSave[67] +
					"&aSave[68]=" + aSave[68] +
					"&aSave[69]=" + aSave[69] +
					"&aSave[70]=" + aSave[70] +
					"&aSave[71]=" + aSave[71] +
					"&aSave[72]=" + aSave[72];

			//0：不具合写真,1:報告書添付,2:伝票添付,3:特別作業記録添付
			document.form.hidTemp.value = intFile;
			//0:アップロード,1:削除
			document.form.hidAction.value = intKbn;
			document.form.submit();
			return true;
		}
	}

	/* 登録モード 伝票NOテキストボックスでエンターキーが押されたとき */
	function fGetTrbl(strMode){
		//エンターキーが押されたら
		var strTmp = document.form.txtReference_NO.value.replace(/\s+$/g, "");
		if(window.event.keyCode==13 && strTmp!="" && <?php echo $mode;?> =="1") {
			var a = new Ajax.Request("F_AJX0070.php",
				{
					method: 'POST'
					,postBody: Form.serialize('form')
					,onSuccess: function(request) {
					}
					,onComplete: function(request) {
						var json = eval(request.responseText);
						if(json instanceof Array){
							//取得データセット
							if(json[30] != ""){
								//確認メッセージ表示
								window.alert(json[30]);
							}
							document.form.txtReference_NO.value = strTmp;
							document.form.txtReference_KBN.value = json[1];		//伝票種別
							document.form.txtReference_KBNNM.value = json[2];	//伝票種別名
							document.form.txtIncident_YMD.value = json[3];		//伝票発行日
							document.form.txtProgresStage_KBN.value = json[4];	//進捗状態
							document.form.txtProgresStage_KBNNM.value = json[5];//進捗状態名
							document.form.txtProdGrp_NM.value = json[6];		//生産グループ名
							document.form.txtProdT_NM1.value = json[7];			//生産担当者1
							document.form.txtProdT_NM2.value = json[8];			//生産担当者2
							document.form.txtProdT_NM3.value = json[9];			//生産担当者3
							document.form.txtExamGrp_NM.value = json[10];		//検査グループ名
							document.form.txtHingiT_NM.value = json[11];		//品技担当者名
							document.form.txtCust_NM.value = json[12];			//得意先名
							document.form.txtProd_CD.value = json[13];			//製品CD
							document.form.txtDie_NO.value = json[14];			//金型番号
							document.form.txtProd_NM.value = json[15];			//製品名
							document.form.txtDRW_NO.value = json[16];			//仕様番号
							document.form.txtFlawLot_NO.value = json[17];		//不具合ロットNO
							document.form.txtFlawLot_QTY.value = json[18];		//不具合数量
							document.form.txtUnitPrice.value = json[19];		//単価
							document.form.txtFlawPrice.value = json[20];		//不具合金額
							document.form.txtPlating_CD.value = json[21];		//めっき先CD
							document.form.txtPlating_NM.value = json[22];		//めっき先名
							document.form.txtMaterialSpec.value = json[23];		//材料仕様
							document.form.cmbFlaw_KBN1.value = json[24];		//不具合区分1
							document.form.txtFlawContents.value = json[25];		//不具合内容
							document.form.hidPlan_NO.value = json[26];			//計画NO
							document.form.hidPlanSeq.value = json[27];			//計画SEQ
							document.form.hidProdKbn.value = json[28];			//製品区分
							document.form.hidReference_SEQ.value = json[29];	//伝票SEQ
						} else {
							alert(json);
							document.form.txtReference_NO.value = "";
							document.form.txtReference_KBN.value = "";
							document.form.txtReference_KBNNM.value = "";
							document.form.txtIncident_YMD.value = "";
							document.form.txtProgresStage_KBN.value = "";
							document.form.txtProgresStage_KBNNM.value = "";
							document.form.txtProdGrp_NM.value = "";
							document.form.txtProdT_NM1.value = "";
							document.form.txtProdT_NM2.value = "";
							document.form.txtProdT_NM3.value = "";
							document.form.txtExamGrp_NM.value = "";
							document.form.txtHingiT_NM.value = "";
							document.form.txtCust_NM.value = "";
							document.form.txtProd_CD.value = "";
							document.form.txtDie_NO.value = "";
							document.form.txtProd_NM.value = "";
							document.form.txtDRW_NO.value = "";
							document.form.txtFlawLot_NO.value = "";
							document.form.txtFlawLot_QTY.value = "";
							document.form.txtUnitPrice.value = "";
							document.form.txtFlawPrice.value = "";
							document.form.txtPlating_CD.value = "";
							document.form.txtPlating_NM.value = "";
							document.form.txtMaterialSpec.value = "";
							document.form.cmbFlaw_KBN1.value = -1;
							document.form.txtFlawContents.value = "";
							document.form.hidPlan_NO.value = "";
							document.form.hidPlanSeq.value = "";
							document.form.hidReference_SEQ.value = "";
							document.form.hidProdKbn.value = "";
							document.form.txtReference_NO.focus();
						}
					}
					,onFailure: function(request) {
						alert('一致する情報は見つかりませんでした');
						document.form.txtReference_NO.value = "";
						document.form.txtReference_KBN.value = "";
						document.form.txtReference_KBNNM.value = "";
						document.form.txtIncident_YMD.value = "";
						document.form.txtProgresStage_KBN.value = "";
						document.form.txtProgresStage_KBNNM.value = "";
						document.form.txtProdGrp_NM.value = "";
						document.form.txtProdT_NM1.value = "";
						document.form.txtProdT_NM2.value = "";
						document.form.txtProdT_NM3.value = "";
						document.form.txtExamGrp_NM.value = "";
						document.form.txtHingiT_NM.value = "";
						document.form.txtCust_NM.value = "";
						document.form.txtProd_CD.value = "";
						document.form.txtDie_NO.value = "";
						document.form.txtProd_NM.value = "";
						document.form.txtDRW_NO.value = "";
						document.form.txtFlawLot_NO.value = "";
						document.form.txtFlawLot_QTY.value = "";
						document.form.txtUnitPrice.value = "";
						document.form.txtFlawPrice.value = "";
						document.form.txtPlating_CD.value = "";
						document.form.txtPlating_NM.value = "";
						document.form.txtMaterialSpec.value = "";
						document.form.cmbFlaw_KBN1.value = -1;
						document.form.txtFlawContents.value = "";
						document.form.hidPlan_NO.value = "";
						document.form.hidPlanSeq.value = "";
						document.form.hidReference_SEQ.value = "";
						document.form.hidProdKbn.value = "";
						document.form.txtReference_NO.focus();
					}
					,onException: function (request) {
						alert('一致する情報は見つかりませんでした');
						document.form.txtReference_NO.value = "";
						document.form.txtReference_KBN.value = "";
						document.form.txtReference_KBNNM.value = "";
						document.form.txtIncident_YMD.value = "";
						document.form.txtProgresStage_KBN.value = "";
						document.form.txtProgresStage_KBNNM.value = "";
						document.form.txtProdGrp_NM.value = "";
						document.form.txtProdT_NM1.value = "";
						document.form.txtProdT_NM2.value = "";
						document.form.txtProdT_NM3.value = "";
						document.form.txtExamGrp_NM.value = "";
						document.form.txtHingiT_NM.value = "";
						document.form.txtCust_NM.value = "";
						document.form.txtProd_CD.value = "";
						document.form.txtDie_NO.value = "";
						document.form.txtProd_NM.value = "";
						document.form.txtDRW_NO.value = "";
						document.form.txtFlawLot_NO.value = "";
						document.form.txtFlawLot_QTY.value = "";
						document.form.txtUnitPrice.value = "";
						document.form.txtFlawPrice.value = "";
						document.form.txtPlating_CD.value = "";
						document.form.txtPlating_NM.value = "";
						document.form.txtMaterialSpec.value = "";
						document.form.cmbFlaw_KBN1.value = -1;
						document.form.txtFlawContents.value = "";
						document.form.hidPlan_NO.value = "";
						document.form.hidPlanSeq.value = "";
						document.form.hidReference_SEQ.value = "";
						document.form.hidProdKbn.value = "";
						document.form.txtReference_NO.focus();
					}
				}
			);
		}else if(<?php echo $mode;?> =="1"){
			document.form.txtReference_NO.value = "";
			document.form.txtReference_KBN.value = "";
			document.form.txtReference_KBNNM.value = "";
			document.form.txtIncident_YMD.value = "";
			document.form.txtProgresStage_KBN.value = "";
			document.form.txtProgresStage_KBNNM.value = "";
			document.form.txtProdGrp_NM.value = "";
			document.form.txtProdT_NM1.value = "";
			document.form.txtProdT_NM2.value = "";
			document.form.txtProdT_NM3.value = "";
			document.form.txtExamGrp_NM.value = "";
			document.form.txtHingiT_NM.value = "";
			document.form.txtCust_NM.value = "";
			document.form.txtProd_CD.value = "";
			document.form.txtDie_NO.value = "";
			document.form.txtProd_NM.value = "";
			document.form.txtDRW_NO.value = "";
			document.form.txtFlawLot_NO.value = "";
			document.form.txtFlawLot_QTY.value = "";
			document.form.txtUnitPrice.value = "";
			document.form.txtFlawPrice.value = "";
			document.form.txtPlating_CD.value = "";
			document.form.txtPlating_NM.value = "";
			document.form.txtMaterialSpec.value = "";
			document.form.cmbFlaw_KBN1.value = -1;
			document.form.txtFlawContents.value = "";
			document.form.hidPlan_NO.value = "";
			document.form.hidPlanSeq.value = "";
			document.form.hidReference_SEQ.value = "";
			document.form.hidProdKbn.value = "";
		}
	}

	/* 取引マスタ検索子画面表示時の絞込み条件制御 */
	function fGetRecKbn(strRecKbn,strPlatKbn){
		var strRtn = "";

		if(strPlatKbn == "1"){
			//めっき先用
			switch (strRecKbn){
				case "1":	//赤伝：協力工場、工程
					strRtn = "4";
					break;
				case "2":	//緑伝：協力工場のみ
					strRtn = "5";
					break;
				default:	//条件未定の場合は協力工場、工程
					strRtn = "4";
					break;
			}
		}else {
			switch(strRecKbn){
				case "1":	//赤伝：協力工場、生産課
					strRtn = "1";
					break;
				case "2":	//緑伝：得意先のみ
					strRtn = "0";
					break;
				default:	//条件未定の場合は条件なし（協力会社、仕入先、運送）
					strRtn = "";
					break;
			}
		}

		return strRtn;
	}

	/* テキストボックスでエンターキーが押された時 */
	function fGetItem(strMode,strKbn){
		var strTmp = "";

		if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
			//strKbn： 1:めっき先、2:報告書発行先部署・協力会社 3:起因部署・協力会社 4:品証担当者 5:起因部署
			switch (strKbn){
				case "1":		//めっき先取得
					strTmp = document.form.txtPlating_CD.value.replace(/\s+$/g, "");
					if(strTmp!=""){
						var a = new Ajax.Request("F_AJX0020.php?ajx=80" + strKbn,
							{
								method: 'POST'
								,postBody: Form.serialize('form')
								,onSuccess: function(request) {
									//alert('読み込み成功しました');
								}
								,onComplete: function(request){
									//JavaScript Object Notation(JSON)形式に変更
									var json = eval(request.responseText);
									//取得データセット
									document.form.txtPlating_CD.value = strTmp
									document.form.txtPlating_NM.value = json[1];
								}
								,onFailure: function(request){
									alert('取引先マスタに存在しません');
									document.form.txtPlating_CD.value = "";
									document.form.txtPlating_NM.value = "";
									document.form.txtPlating_CD.focus();
								}
								,onException: function (request) {
									alert('取引先マスタに存在しません');
									document.form.txtPlating_CD.value = "";
									document.form.txtPlating_NM.value = "";
									document.form.txtPlating_CD.focus();
								}
							}
						);
					}else{
						document.form.txtPlating_CD.value = "";
						document.form.txtPlating_NM.value = "";
					}
					break;
				case "2":		//報告書発行先部署・協力会社取得
					strTmp = document.form.txtIncident_CD.value.replace(/\s+$/g, "");
					if(strTmp!="") {
						var a = new Ajax.Request("F_AJX0020.php?ajx=80" + strKbn,
							{
								method: 'POST'
								,postBody: Form.serialize('form')
								,onSuccess: function(request) {
									//alert('読み込み成功しました');
								}
								,onComplete: function(request) {
									//JavaScript Object Notation(JSON)形式に変更
									var json = eval(request.responseText);
									//取得データセット
									document.form.txtIncident_CD.value = strTmp;
									document.form.txtIncident_NM.value = json[1];
								}
								,onFailure: function(request) {
									alert('取引先マスタに存在しません');
									document.form.txtIncident_NM.value = "";
									document.form.txtIncident_CD.value = "";
									document.form.txtIncident_CD.focus();
								}
								,onException: function (request) {
									alert('取引先マスタに存在しません');
									document.form.txtIncident_NM.value = "";
									document.form.txtIncident_CD.value = "";
									document.form.txtIncident_CD.focus();
								}
							}
						);
					}else{
						document.form.txtIncident_NM.value = "";
					}
					break;
				case "3":		//起因部署・協力会社取得
					strTmp = document.form.txtPartner_CD.value.replace(/\s+$/g, "");
					if(strTmp!="") {
						var a = new Ajax.Request("F_AJX0020.php?ajx=80" + strKbn,
							{
								method: 'POST'
								,postBody: Form.serialize('form')
								,onSuccess: function(request) {
									//alert('読み込み成功しました');
								}
								,onComplete: function(request) {
									//JavaScript Object Notation(JSON)形式に変更
									var json = eval(request.responseText);
									//取得データセット
									document.form.txtPartner_CD.value = strTmp;
									document.form.txtPartner_NM.value = json[1];
								}
								,onFailure: function(request) {
									alert('取引先マスタに存在しません');
									document.form.txtPartner_CD.value = "";
									document.form.txtPartner_NM.value = "";
									document.form.txtPartner_CD.focus();
								}
								,onException: function (request) {
									alert('取引先マスタに存在しません');
									document.form.txtPartner_CD.value = "";
									document.form.txtPartner_NM.value = "";
									document.form.txtPartner_CD.focus();
								}
							}
						);
					}else{
						document.form.txtPartner_NM.value = "";
					}
					break;
				case "4":		//品証担当者取得
					strTmp = document.form.txtTanto_CD.value.replace(/\s+$/g, "");
					if(strTmp!="") {
						var a = new Ajax.Request("F_AJX0030.php?ajx=80" + strKbn,
							{
								method: 'POST'
								,postBody: Form.serialize('form')
								,onSuccess: function(request) {
									//alert('読み込み成功しました');
								}
								,onComplete: function(request) {
									//JavaScript Object Notation(JSON)形式に変更
									var json = eval(request.responseText);
									//取得データセット
									document.form.txtTanto_CD.value = strTmp;
									document.form.txtTanto_NM.value = json[0][1];
								}
								,onFailure: function(request) {
									alert('担当者マスタに存在しません');
									document.form.txtTanto_CD.value = "";
									document.form.txtTanto_NM.value = "";
									document.form.txtTanto_CD.focus();
								}
								,onException: function (request) {
									alert('担当者マスタに存在しません');
									document.form.txtTanto_CD.value = "";
									document.form.txtTanto_NM.value = "";
									document.form.txtTanto_CD.focus();
								}
							}
						);
					}else{
						document.form.txtTanto_NM.value = "";
					}
					break;
				case "5":		//起因部署取得
					strTmp = document.form.txtBusyo_CD.value.replace(/\s+$/g, "");
					if(strTmp!="") {
						var a = new Ajax.Request("F_AJX0020.php?ajx=80" + strKbn,
							{
								method: 'POST'
								,postBody: Form.serialize('form')
								,onSuccess: function(request) {
									//alert('読み込み成功しました');
								}
								,onComplete: function(request) {
									//JavaScript Object Notation(JSON)形式に変更
									var json = eval(request.responseText);
									//取得データセット
									document.form.txtBusyo_CD.value = strTmp;
									document.form.txtBusyo_NM.value = json[1];
								}
								,onFailure: function(request) {
									alert('取引先マスタに存在しません');
									document.form.txtBusyo_NM.value = "";
									document.form.txtBusyo_CD.value = "";
									document.form.txtBusyo_CD.focus();
								}
								,onException: function (request) {
									alert('取引先マスタに存在しません');
									document.form.txtBusyo_NM.value = "";
									document.form.txtBusyo_CD.value = "";
									document.form.txtBusyo_CD.focus();
								}
							}
						);
					}else{
						document.form.txtBusyo_NM.value = "";
					}
					break;
				default:
					break;
			}
		}
	}

	/* 納入・廃棄・返却・調整ﾛｽ数量のテキストボックスでエンターキーが押されたとき */
	function fCalcPrice(strMode,strKbn,strQtyKbn){
		//エンターキーが押されたら
		if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2") {
			var a = new Ajax.Request("F_AJX0080.php?ajx=80" + strKbn,
				{
					method: 'POST'
					,postBody: Form.serialize('form')
					,onSuccess: function(request) {
					}
					,onComplete: function(request) {
						var json = eval(request.responseText);
						if(json instanceof Array){
							if (strKbn == 1){
								//編集可否
								document.form.txtFailure_QTY.disabled = json[0][0];
								document.form.txtDisposal_QTY.disabled = json[0][1];
								document.form.txtReturn_QTY.disabled = json[0][2];
								document.form.txtLoss_QTY.disabled = json[0][3];
								document.form.txtExclud_QTY.disabled = json[0][4];
								document.form.txtFailurePrice.disabled = json[0][0];
								document.form.txtDisposalPrice.disabled = json[0][1];
								document.form.txtReturnPrice.disabled = json[0][2];
								document.form.txtLossPrice.disabled = json[0][3];
								document.form.txtExcludPrice.disabled = json[0][4];
								//数量
								document.form.txtFailure_QTY.value = json[1][0];
								document.form.txtDisposal_QTY.value = json[1][1];
								document.form.txtReturn_QTY.value = json[1][2];
								document.form.txtLoss_QTY.value = json[1][3];
								document.form.txtExclud_QTY.value = json[1][4];
								//金額
								document.form.txtFailurePrice.value = json[2][0];
								document.form.txtDisposalPrice.value = json[2][1];
								document.form.txtReturnPrice.value = json[2][2];
								document.form.txtLossPrice.value = json[2][3];
								document.form.txtExcludPrice.value = json[2][4];
							}else if(strKbn == 2){
								switch (strQtyKbn) {
									case 0:
										document.form.txtFailurePrice.value = json[0][0];
										document.form.txtFailure_QTY.value = json[1][0];
										break;
									case 1:
										document.form.txtDisposalPrice.value = json[0][1];
										document.form.txtDisposal_QTY.value = json[1][1];
										break;
									case 2:
										document.form.txtReturnPrice.value = json[0][2];
										document.form.txtReturn_QTY.value = json[1][2];
										break;
									case 3:
										document.form.txtLossPrice.value = json[0][3];
										document.form.txtLoss_QTY.value = json[1][3];
										break;
									case 4:
										document.form.txtExcludPrice.value = json[0][4];
										document.form.txtExclud_QTY.value = json[1][4];
										break;
									default:
										break;
								}
							//2019/05/13 ADD START
							}else{
								document.form.txtFlawPrice.value = json[0][0];
							}
							//2019/05/13 ADD END
						} else {
							alert(json);
							document.form.txtFailure_QTY.disabled = false;
							document.form.txtDisposal_QTY.disabled = false;
							document.form.txtReturn_QTY.disabled = false;
							document.form.txtLoss_QTY.disabled = false;
							document.form.txtExclud_QTY.disabled = false;
							document.form.txtFailurePrice.disabled = false;
							document.form.txtDisposalPrice.disabled = false;
							document.form.txtReturnPrice.disabled = false;
							document.form.txtLossPrice.disabled = false;
							document.form.txtExcludPrice.disabled = false;
							document.form.cmbProcess_KBN.focus();
						}
					}
					,onFailure: function(request) {
						alert('一致する情報は見つかりませんでした');
						//編集可否
						document.form.txtFailure_QTY.disabled = false;
						document.form.txtDisposal_QTY.disabled = false;
						document.form.txtReturn_QTY.disabled = false;
						document.form.txtLoss_QTY.disabled = false;
						document.form.txtExclud_QTY.disabled = false;
						document.form.txtFailurePrice.disabled = false;
						document.form.txtDisposalPrice.disabled = false;
						document.form.txtReturnPrice.disabled = false;
						document.form.txtLossPrice.disabled = false;
						document.form.txtExcludPrice.disabled = false;
					}
					,onException: function (request) {
						alert('一致する情報は見つかりませんでした');
						//編集可否
						document.form.txtFailure_QTY.disabled = false;
						document.form.txtDisposal_QTY.disabled = false;
						document.form.txtReturn_QTY.disabled = false;
						document.form.txtLoss_QTY.disabled = false;
						document.form.txtExclud_QTY.disabled = false;
						document.form.txtFailurePrice.disabled = false;
						document.form.txtDisposalPrice.disabled = false;
						document.form.txtReturnPrice.disabled = false;
						document.form.txtLossPrice.disabled = false;
						document.form.txtExcludPrice.disabled = false;
						document.form.cmbProcess_KBN.focus();
					}
				}
			);
		}
	}
	
	
	//入力値保管
	function fncSetSaveArray(){
		var aSave = new Array( );
		aSave[0] = document.form.txtReference_NO.value;
		aSave[1] = document.form.hidReference_SEQ.value;
		aSave[2] = document.form.txtReference_KBN.value;
		aSave[3] = document.form.cmbTargetSection_KBN.value;
		aSave[4] = document.form.txtPointRef_NO.value;
		aSave[5] = document.form.txtBusyo_CD.value;
		aSave[6] = document.form.txtSumBikou.value;
		aSave[7] = document.form.txtIncident_YMD.value;
		aSave[8] = document.form.txtProgresStage_KBN.value;
		aSave[9] = document.form.txtProdGrp_NM.value;
		aSave[10] = document.form.txtCust_NM.value;
		aSave[11] = document.form.txtProdT_NM1.value;
		aSave[12] = document.form.txtProdT_NM2.value;
		aSave[13] = document.form.txtProdT_NM3.value;
		aSave[14] = document.form.txtExamGrp_NM.value;
		aSave[15] = document.form.txtHingiT_NM.value;
		aSave[16] = document.form.txtProd_CD.value;
		aSave[17] = document.form.txtDie_NO.value;
		aSave[18] = document.form.txtProd_NM.value;
		aSave[19] = document.form.txtDRW_NO.value;
		aSave[20] = document.form.txtFlawLot_NO.value;
		aSave[21] = document.form.txtFlawLot_QTY.value;
		aSave[22] = document.form.txtUnitPrice.value;
		aSave[23] = document.form.txtFlawPrice.value;
		aSave[24] = document.form.txtPlating_CD.value;
		aSave[25] = document.form.txtPlating_NM.value;
		aSave[26] = document.form.cmbKBN.value;
		aSave[27] = document.form.txtMaterialSpec.value;
		aSave[28] = document.form.cmbFlaw_KBN1.value;
		aSave[29] = document.form.cmbFlaw_KBN2.value;
		aSave[30] = document.form.cmbFlaw_KBN3.value;
		aSave[31] = document.form.txtFlawContents.value;
		aSave[32] = document.form.txtSpecial_YMD.value;
		aSave[33] = document.form.txtProcessPeriod_YMD.value;
		aSave[34] = document.form.txtStretchReason.value;
		aSave[35] = document.form.txtIniProcPeriod_YMD.value;
		aSave[36] = document.form.txtTanto_CD.value;
		if (document.form.chkNonIssue.checked == true){
			aSave[37] = "1";
		}else{
			aSave[37] = "0";
		}
		aSave[38] = document.form.txtIncident_CD.value;
		aSave[39] = document.form.txtProcessLimit_YMD.value;
		aSave[40] = document.form.txtReturn_YMD.value;
		aSave[41] = document.form.txtComplete_YMD.value;
		aSave[42] = document.form.txtDecision_YMD.value;
		aSave[43] = document.form.txtApproval_YMD.value;
		if (document.form.chkExcluded.checked == true){
			aSave[44] = "1";
		}else{
			aSave[44] = "0";
		}
		aSave[45] = document.form.txtSelection.value;
		aSave[46] = document.form.cmbDueProcess_KBN.value;
		aSave[47] = document.form.txtComments.value;
		aSave[48] = document.form.txtPartner_CD.value;
		aSave[49] = document.form.cmbProcess_KBN.value;
		aSave[50] = document.form.txtFailure_QTY.value;
		aSave[51] = document.form.txtDisposal_QTY.value;
		aSave[52] = document.form.txtReturn_QTY.value;
		aSave[53] = document.form.txtLoss_QTY.value;
		aSave[54] = document.form.txtExclud_QTY.value;
		aSave[55] = document.form.txtFailurePrice.value;
		aSave[56] = document.form.txtDisposalPrice.value;
		aSave[57] = document.form.txtReturnPrice.value;
		aSave[58] = document.form.txtLossPrice.value;
		aSave[59] = document.form.txtExcludPrice.value;
		aSave[60] = document.form.hidUCount.value;
		aSave[61] = document.form.txtBusyo_NM.value;
		aSave[62] = document.form.txtTanto_NM.value;
		aSave[63] = document.form.txtIncident_NM.value;
		aSave[64] = document.form.txtPartner_NM.value;
		aSave[65] = document.form.hidPlan_NO.value;
		aSave[66] = document.form.hidPlanSeq.value;
		// 2019/05/13 ADD START
		aSave[67] = document.form.txtSubmit_YMD1.value;
		aSave[68] = document.form.txtSubmit_YMD2.value;
		aSave[69] = document.form.txtSubmit_YMD3.value;
		aSave[70] = document.form.txtBack_YMD1.value;
		aSave[71] = document.form.txtBack_YMD2.value;
		aSave[72] = document.form.txtBack_YMD3.value;
		// 2019/05/13 ADD END
		if (document.form.chkSpecial.checked == true){
			aSave[73] = "1";
		}else{
			aSave[73] = "0";
		}
		aSave[74] = document.form.hidProdKbn.value;
		aSave[75] = document.form.hidDecision_OLD_YMD.value;
		
		return aSave;
		
	}

	//日付桁数チェック
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
			wMChk = 12;	 // 閏年テーブル

			//if (!(!(wYear % 100) && (wYear % 400))) {
			if (!(wYear % 100)) {
				if (wYear % 400) {
					wMChk = 1;	  // non閏年テーブル
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

	//アップロードファイルダウンロード
	function fStartDownload(strURL,strFileName) {
		window.open(strURL, '_blank'); // 新しいタブを開き、ページを表示
		
		//IE11(互換表示なし)用
		//var xhr = new XMLHttpRequest();
		//xhr.open('GET', strURL);
		//xhr.responseType = 'blob';
		//xhr.onloadend = function() {
		//	if(xhr.status !== 200) return;
		//	window.navigator.msSaveBlob(xhr.response, strFileName);
		//}
		//xhr.send();
		
		//var link = document.createElement('a');
		//link.download = strFileName;
		//link.href = strURL;
		//link.target= "_blank";
		//link.click();
	}

</script>
</HEAD>
<BODY style="font-size : medium;border-collapse : separate;" 
<?php if($strMsg <> "" && $mode == '1') { ?> fClearFormAll(); <?php } ?>">
<TABLE border="0" bgcolor="#000066">
	<TBODY>
		<TR>
			<TD align="center" width="1000"><SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【赤伝緑伝情報入力】<?php echo($modeN); ?>
			</SPAN></TD>
		</TR>
	</TBODY>
</TABLE>
<br>
<INPUT type="hidden" name="token" value="<?php echo $token; ?>">

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
			<TD class="tdnone" width="1000" align="left">
				<?php
				//参照以外かつボタン表示フラグがTrueならは確定ボタン表示
				if($mode <> "4" && $bDispflg){
					//品証のユーザのみ表示
					if(substr($_SESSION['login'][2],0,3) == "117"){
				?>
						<INPUT type="button" name="btnExcute" tabindex="598" value="　確　定　" onClick="fncExcute('<?php echo($mode); ?>','<?php echo($modeN); ?>',0)" tabindex="2400">
				<?php
					}
				}
				?>
				<?php if($hidFrame == 0){ ?>
				<INPUT type="button" name="btnSearch" tabindex="600" value="　戻　る　" onClick="fReturn(<?php echo $mode;?>)">
				<?php } ?>
				<?php echo $strManulPath;  ?>
			</TD>
		</TR>
	</TBODY>
</TABLE>
<TABLE border="0">
		<TBODY>
		<TR>
			<TD class="tdnone" width="1000">
				<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
					<FONT color="#ffffff"><B>受付情報</B></FONT>
				</DIV>
			</TD>
		</TR>
	</TBODY>
</TABLE>

<TABLE class="tbline" width="1007">
	<TBODY>
		<TR>
			<TD class="tdnone1" width="166">対象部門</TD>
			<TD class="tdnone3" width="167">
				<SELECT name="cmbTargetSection_KBN" id="cmbTargetSection_KBN" tabindex="10" <?php echo $strCmbLock; ?>>
					<OPTION selected value="-1" >▼選択して下さい</OPTION>
					<?php $module_sel->fMakeCombo('C04',$cmbTargetSection_KBN); ?>
				</SELECT>
			</TD>
			<TD class="tdnone1" width="167">伝票NO</TD>
			<TD class="tdnone3" width="167">
				<INPUT type="text" class="<?php echo $strBoxDspRefNo; ?>" name="txtReference_NO" id="txtReference_NO" tabindex="20" size="12" maxlength="15" style="ime-mode:disabled;" <?php echo $strUpdCtrlRefNo; ?> value="<?php echo $txtReference_NO; ?>" onBlur="window.event.keyCode=13;fGetTrbl('<?php echo($mode); ?>');">
			</TD>
			<TD class="tdnone9" width="167">伝票種別</TD>
			<TD class="tdnone3" width="166">
				<INPUT type="text" class="textboxdisp" id="txtReference_KBNNM" name="txtReference_KBNNM" size="10" style="ime-mode:disabled;" readonly value="<?php echo $txtReference_KBNNM; ?>">
				<INPUT type="hidden" id="txtReference_KBN" name="txtReference_KBN" value="<?php echo $txtReference_KBN; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone2" width="167">代表伝票NO</TD>
			<TD class="tdnone3" width="167">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" name="txtPointRef_NO" id="txtPointRef_NO" tabindex="21" size="12" maxlength="15" style="ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtPointRef_NO; ?>">
			</TD>
			<TD class="tdnone1">
				<?php
					if($mode == "1" || $mode == "2"){
						echo "<A href=\"JavaScript:fOpenSearch('F_MSK0020','txtBusyo_CD','txtBusyo_NM','','','','','',3)\" onclick=\"\" tabindex=\"22\">起因部署CD</A>";

					}else{
						echo "起因部署CD";
					}
				?>
			</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" name="txtBusyo_CD" id="txtBusyo_CD" tabindex="23" size="5" maxlength="8" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtBusyo_CD; ?>" onBlur="window.event.keyCode=13;fGetItem('<?php echo($mode); ?>','5');">
			</TD>
			<TD class="tdnone9">起因部署</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" name="txtBusyo_NM" id="txtBusyo_NM" size="22" maxlength="960" style="ime-mode: disabled;" readonly value="<?php echo $txtBusyo_NM; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone2" width="166">集計用備考欄</TD>
			<TD class="tdnone3" colspan="5">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtSumBikou" name="txtSumBikou" style="width:100%" tabindex="24" size="70" maxlength="40" <?php echo $strUpdCtrl; ?> value="<?php echo $txtSumBikou; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone9">進捗状態</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtProgresStage_KBNNM" name="txtProgresStage_KBNNM" size="24" style="ime-mode:disabled;" readonly value="<?php echo $txtProgresStage_KBNNM; ?>">
				<INPUT type="hidden" id="txtProgresStage_KBN" id="txtProgresStage_KBN" name="txtProgresStage_KBN" value="<?php echo $txtReference_KBN; ?>">
			</TD>
			<TD class="tdnone9">生産グループ名</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtProdGrp_NM" name="txtProdGrp_NM" size="24" style="ime-mode:disabled;" readonly value="<?php echo $txtProdGrp_NM; ?>">
			</TD>
			<TD class="tdnone9">伝票発行日</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtIncident_YMD" name="txtIncident_YMD" size="7" maxlength="10" style="ime-mode:disabled;" readonly value="<?php echo $txtIncident_YMD; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone9">生産担当者1</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtProdT_NM1" name="txtProdT_NM1" size="24" style="ime-mode:disabled;" readonly value="<?php echo $txtProdT_NM1; ?>">
			</TD>
			<TD class="tdnone9">生産担当者2</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtProdT_NM2" name="txtProdT_NM2" size="24" style="ime-mode:disabled;" readonly value="<?php echo $txtProdT_NM2; ?>">
			</TD>
			<TD class="tdnone9">生産担当者3</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtProdT_NM3" name="txtProdT_NM3" size="24" style="ime-mode:disabled;" readonly value="<?php echo $txtProdT_NM3; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone9">金型番号</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtDie_NO" name="txtDie_NO" size="24" style="ime-mode: disabled;" readonly value="<?php echo $txtDie_NO; ?>">
			</TD>
			<TD class="tdnone9">検査グループ名</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtExamGrp_NM" name="txtExamGrp_NM" size="24" style="ime-mode: disabled;" readonly value="<?php echo $txtExamGrp_NM; ?>">
			</TD>
			<TD class="tdnone9">品技担当者</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtHingiT_NM" name="txtHingiT_NM" size="24" style="ime-mode: disabled;" readonly value="<?php echo $txtHingiT_NM; ?>">
		  </TD>
		</TR>
		<TR>
			<TD class="tdnone9">製品CD</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtProd_CD" name="txtProd_CD" size="24" style="ime-mode: disabled;" readonly value="<?php echo $txtProd_CD; ?>">
			</TD>
			<TD class="tdnone9">製品名</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtProd_NM" name="txtProd_NM" size="24" disabled;" readonly value="<?php echo $txtProd_NM; ?>">
			</TD>
			<TD class="tdnone9">仕様番号</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtDRW_NO" name="txtDRW_NO" size="24" style="ime-mode: disabled;" readonly value="<?php echo $txtDRW_NO; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone1">不具合ロットNO</TD>
			<TD class="tdnone3" colspan="3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtFlawLot_NO" name="txtFlawLot_NO" tabindex="30" size="69" maxlength="25" style="ime-mode;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtFlawLot_NO; ?>">
			</TD>
			<TD class="tdnone9">得意先名</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtCust_NM" name="txtCust_NM" size="24" style="ime-mode:disabled;" readonly value="<?php echo $txtCust_NM; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone1">不具合数量</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtFlawLot_QTY" name="txtFlawLot_QTY" tabindex="40" size="8" maxlength="10" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtFlawLot_QTY; ?>">個
			</TD>
			<TD class="tdnone1">単価</TD>
			<TD class="tdnone3">
				<!-- 2019/05/13 ED START -->
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtUnitPrice" name="txtUnitPrice" tabindex="50" size="12" maxlength="14" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtUnitPrice; ?>" onBlur="window.event.keyCode=13;fCalcPrice('<?php echo($mode); ?>',3);">円
				<!-- 2019/05/13 ED END -->
			</TD>
			<TD class="tdnone1">不具合金額</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtFlawPrice" name="txtFlawPrice" tabindex="60" size="8" maxlength="10" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtFlawPrice; ?>">円
			</TD>
		</TR>
		<TR>
			<TD class="tdnone2">
				<?php
					if($mode == "1" || $mode == "2"){
						echo "<A href=\"JavaScript:fOpenSearch('F_MSK0020','txtPlating_CD','txtPlating_NM','','','','','',fGetRecKbn(document.form.txtReference_KBN.value,1))\" onclick=\"\" tabindex=\"65\">めっき先CD</A>";
					}else{
						echo "めっき先CD";
					}
				?>
			</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" name="txtPlating_CD" id="txtPlating_CD" tabindex="70" size="5" maxlength="8" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtPlating_CD; ?>" onBlur="window.event.keyCode=13;fGetItem('<?php echo($mode); ?>','1');">
			</TD>
			<TD class="tdnone2">めっき先名</TD>
			<TD class="tdnone3" colspan="3">
				<INPUT type="text" class="textboxdisp" id="txtPlating_NM" name="txtPlating_NM" size="70" maxlength="30" style="ime-mode: disabled;" readonly value="<?php echo $txtPlating_NM; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone1">区分</TD>
			<TD class="tdnone3">
				<SELECT name="cmbKBN" id="cmbKBN" tabindex="80" <?php echo $strCmbLock; ?>>
					<OPTION selected value="-1" >▼選択して下さい</OPTION>
					<?php $module_sel->fMakeCombo('C34',$cmbKBN); ?>
				</SELECT>
			</TD>
			<TD class="tdnone2">材料仕様</TD>
			<TD class="tdnone3" colspan="3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtMaterialSpec" name="txtMaterialSpec" tabindex="90" size="70" maxlength="40" style="ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtMaterialSpec; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone1">不具合区分1</TD>
			<TD class="tdnone3">
				<SELECT name="cmbFlaw_KBN1" id="cmbFlaw_KBN1" tabindex="100" <?php echo $strCmbLock ?>>
					<OPTION selected value="-1" >▼選択して下さい</OPTION>
					<?php $module_sel->fMakeComboS2('085',$cmbFlaw_KBN1); ?>
				</SELECT>
			</TD>
			<TD class="tdnone1">不具合区分2</TD>
			<TD class="tdnone3">
				<SELECT name="cmbFlaw_KBN2" id="cmbFlaw_KBN2" tabindex="110" <?php echo $strCmbLock; ?>>
					<OPTION selected value="-1" >▼選択して下さい</OPTION>
					<?php $module_sel->fMakeComboS2('085',$cmbFlaw_KBN2); ?>
				</SELECT>
			</TD>
			<TD class="tdnone1">不具合区分3</TD>
			<TD class="tdnone3">
				<SELECT name="cmbFlaw_KBN3" id="cmbFlaw_KBN3" tabindex="120" <?php echo $strCmbLock; ?>>
					<OPTION selected value="-1" >▼選択して下さい</OPTION>
					<?php $module_sel->fMakeComboS2('085',$cmbFlaw_KBN3); ?>
				</SELECT>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone1">不具合内容</TD>
			<TD class="tdnone3" colspan="5">
				<textarea class="<?php echo $strBoxDsp; ?>" style="width:100%" rows="5" name="txtFlawContents" id="txtFlawContents" tabindex="130" maxlength="500" <?php echo $strUpdCtrl; ?>><?php echo $txtFlawContents; ?></textarea>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone2" width="166">不具合写真<br>(1ファイル10MBまでアップロード可能)</TD>
			<TD class="tdnone3" width="834" colspan="5">
				<INPUT type="file" name="getFlawPhoto" id="getFlawPhoto" size="100" tabindex="140" <?php echo $strBtnLock; ?> />
				<INPUT type="button" value="アップロード" tabindex="150" style="margin-top:4" <?php echo $strBtnLock; ?> onClick="fncFileUpload('<?php echo($mode); ?>','アップロード',1,0,0,'getFlawPhoto')"/>
				<TABLE class="type08" width="100%" style="margin-top:6">
					<THEAD>
						<TR>
							<TH scope="cols" width="10%">選択</TH>
							<TH scope="cols" width="10%">リンク</TH>
							<TH scope="cols" width="80%">ファイル名</TH>
						</TR>
					 </THEAD>
					<TBODY>
					<?php
					//ディレクトリ存在チェック
					if(file_exists($dirFlaw)){
						//アップロードファイル取得
						$filelist=scandir($dirFlaw);
						foreach($filelist as $file):
							if(!is_dir($file)):
					?>
						<TR>
							<TD>
								<INPUT type="checkbox" name="deletefile[]" tabindex="155" value="<?php echo $file; ?>" />
							</TD>
							<TD>
								<?php 
									echo "<INPUT type='button' tabindex='160'  value='ダウンロード' style='background-color : #fdc257;' onClick=\"fStartDownload('".($dirFlaw.$file)."','".$file."');\">";
									
								?>
							</TD>
							<TD>
								<?php echo $file; ?>
							</TD>
						
						</TR>
						<?php endif; endforeach; ?>
					<?php } ?>
					</TBODY>
				</TABLE>
				<p>
					<INPUT type="button" tabindex="170" value="削　除" <?php echo $strBtnLock; ?> onClick="fncFileUpload('<?php echo($mode); ?>','削除',0,0,1,'getFlawPhoto')"/>
				</p>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone1">
				<?php
					if($mode == "1" || $mode == "2"){
						echo "<A href=\"JavaScript:fOpenSearch('F_MSK0030','txtTanto_CD','txtTanto_NM','','','','','','1')\" onclick=\"\" tabindex=\"185\">品証担当者CD</A>";
					}else{
						echo "品証担当者CD";
					}
				?>
				
			</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtTanto_CD" name="txtTanto_CD" tabindex="190" size="7" maxlength="8" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtTanto_CD; ?>" onBlur="window.event.keyCode=13;fGetItem('<?php echo($mode); ?>','4');">
			</TD>
			<TD class="tdnone1">品証担当者</TD>
			<TD class="tdnone3" colspan="3">
				<INPUT type="text" class="textboxdisp" name="txtTanto_NM" id="txtTanto_NM" size="24" style="ime-mode: disabled;" readonly value="<?php echo $txtTanto_NM; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone2">特別作業記録発行日</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtSpecial_YMD" name="txtSpecial_YMD" tabindex="210" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtSpecial_YMD; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtSpecial_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
				<INPUT type="checkbox" name="chkSpecial" tabindex="211" value="1" <?php echo $strSpecialCheck; ?> <?php echo $strChkLock; ?>>マーキング
			</TD>
			<TD class="tdnone1">処理期限</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtProcessPeriod_YMD" name="txtProcessPeriod_YMD" tabindex="230" size="7" maxlength="10" style="ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtProcessPeriod_YMD; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtProcessPeriod_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
			<TD class="tdnone9">初期処理期限</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" id="txtIniProcPeriod_YMD" name="txtIniProcPeriod_YMD" size="7" maxlength="10" style="ime-mode: disabled;" readonly value="<?php echo $txtIniProcPeriod_YMD; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0" width="166">特別作業記録添付<br>(10MBまでアップロード可能)</TD>
			<TD class="tdnone3" width="834" colspan="9">
				<INPUT type="file" name="getSpecial" id="getSpecial" tabindex="240" size="100" <?php echo $strBtnLock; ?> />
				<INPUT type="button" value="アップロード" tabindex="250" style="margin-top:4" <?php echo $strBtnLock; ?>  onClick="fncFileUpload('<?php echo($mode); ?>','アップロード',0,3,0,'getSpecial')"/>
				<TABLE class="type08" width="100%" style="margin-top:6">
					<THEAD>
						<tr>
							<th scope="cols" width="10%">選択</th>
							<th scope="cols" width="10%">リンク</th>
							<th scope="cols" width="80%">ファイル名</th>
						</tr>
					 </THEAD>
					<TBODY>
					<?php
					//ディレクトリ存在チェック
					if(file_exists($dirSpe)){
						//アップロードファイル取得
						$filelist=scandir($dirSpe);
						foreach($filelist as $file):
							if(!is_dir($file)):
					?>
						<tr>
							<td>
								<INPUT type="checkbox" name="deletefile4[]" tabindex="260" <?php echo $strBtnLock; ?> value="<?php echo $file; ?>" />
							</td>
							<td>
								<?php 
									echo "<INPUT type='button' tabindex='270' value='ダウンロード' style='background-color : #fdc257;' onClick=\"fStartDownload('".($dirSpe.$file)."','".$file."');\">";
									
								?>
							</td>
							<td>
								<?php echo $file; ?>
							</td>
						
						</tr>
						<?php endif; endforeach; ?>
					<?php } ?>
					</TBODY>
				</TABLE>
				<p>
					<INPUT type="button" tabindex="280" value="削　除" <?php echo $strBtnLock; ?> onClick="fncFileUpload('<?php echo($mode); ?>','削除',0,3,1,'getSpecial')"/>
				</p>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0">初期処理期限延伸理由</TD>
			<TD class="tdnone3" colspan="5">
				<textarea class="<?php echo $strBoxDsp; ?>" style="width:100%" rows="3" name="txtStretchReason" id="txtStretchReason" tabindex="290" maxlength="100" <?php echo $strUpdCtrl; ?>><?php echo $txtStretchReason; ?></textarea>
			</TD>
		</TR>
<!-- 2019/05/13 ADD START -->
		<TR>
			<TD class="tdnone2">特別作業払い出し日1</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" name="txtSubmit_YMD1" id="txtSubmit_YMD1" tabindex="300" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtSubmit_YMD1; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtSubmit_YMD1", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
			<TD class="tdnone2">特別作業払い出し日2</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtSubmit_YMD2" name="txtSubmit_YMD2" tabindex="310" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtSubmit_YMD2; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtSubmit_YMD2", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
			<TD class="tdnone2">特別作業払い出し日3</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtSubmit_YMD3" name="txtSubmit_YMD3" tabindex="320" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtSubmit_YMD3; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtSubmit_YMD3", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone2">特別作業戻り日1</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" name="txtBack_YMD1" id="txtBack_YMD1" tabindex="305" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtBack_YMD1; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtBack_YMD1", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
			<TD class="tdnone2">特別作業戻り日2</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtBack_YMD2" name="txtBack_YMD2" tabindex="315" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtBack_YMD2; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtBack_YMD2", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
			<TD class="tdnone2">特別作業戻り日3</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtBack_YMD3" name="txtBack_YMD3" tabindex="325" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtBack_YMD3; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtBack_YMD3", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
		</TR>
<!-- 2019/05/13 ADD END -->
		<TR>
			<TD class="tdnone0" colspan="2">
				<?php
					if($mode == "1" || $mode == "2"){
						echo "<A href=\"JavaScript:fOpenSearch('F_MSK0020','txtIncident_CD','txtIncident_NM','','','','','',3)\" onclick=\"\" tabindex=\"330\">報告書発行先部署・協力会社CD</A>";

					}else{
						echo "報告書発行先部署・協力会社CD";
					}
				?>
			</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" name="txtIncident_CD" id="txtIncident_CD" tabindex="340" size="5" maxlength="8" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtIncident_CD; ?>" onBlur="window.event.keyCode=13;fGetItem('<?php echo($mode); ?>','2');">
			</TD>
			<TD class="tdnone9" colspan="2">報告書発行先（部署・協力会社名）</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="textboxdisp" name="txtIncident_NM" id="txtIncident_NM" size="22" maxlength="960" style="ime-mode: disabled;" readonly value="<?php echo $txtIncident_NM; ?>">
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0">報告書処理期限</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" name="txtProcessLimit_YMD" id="txtProcessLimit_YMD" tabindex="350" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtProcessLimit_YMD; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtProcessLimit_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
				<INPUT type="checkbox" name="chkNonIssue" tabindex="360" value="1" <?php echo $strNonIssueCheck; ?> <?php echo $strChkLock; ?>>発行不要
			</TD>
			<TD class="tdnone2">返却日</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtReturn_YMD" name="txtReturn_YMD" tabindex="370" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtReturn_YMD; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtReturn_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
			<TD class="tdnone2">完結日</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtComplete_YMD" name="txtComplete_YMD" tabindex="380" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtComplete_YMD; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtComplete_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0" width="166">報告書添付<br>(10MBまでアップロード可能)</TD>
			<TD class="tdnone3" width="834" colspan="5">
				<INPUT type="file" name="getReport" id="getReport" tabindex="390" abindex="280" size="100" <?php echo $strBtnLock; ?> />
				<INPUT type="button" value="アップロード" tabindex="400" style="margin-top:4" <?php echo $strBtnLock; ?> onClick="fncFileUpload('<?php echo($mode); ?>','アップロード',0,1,0,'getReport')"/>
				<TABLE class="type08" width="100%" style="margin-top:6">
					<THEAD>
						<tr>
							<th scope="cols" width="10%">選択</th>
							<th scope="cols" width="10%">リンク</th>
							<th scope="cols" width="80%">ファイル名</th>
						</tr>
					 </THEAD>
					<TBODY>
					<?php
					//ディレクトリ存在チェック
					if(file_exists($dirRep)){
						//アップロードファイル取得
						$filelist=scandir($dirRep);
						foreach($filelist as $file):
							if(!is_dir($file)):
					?>
						<tr>
							<td>
								<INPUT type="checkbox" name="deletefile2[]" tabindex="410" value="<?php echo $file; ?>" />
							</td>
							<td>
								<?php 
									echo "<INPUT type='button' value='ダウンロード' style='background-color : #fdc257;' onClick=\"fStartDownload('".($dirRep.$file)."','".$file."');\">";
								?>
							</td>
							<td>
								<?php echo $file; ?>
							</td>
						
						</tr>
						<?php endif; endforeach; ?>
					<?php } ?>
					</TBODY>
				</TABLE>
				<p>
					<INPUT type="button" tabindex="420" value="削　除" <?php echo $strBtnLock; ?> onClick="fncFileUpload('<?php echo($mode); ?>','削除',0,1,1,'getReport')"/>
				</p>
			</TD>
		</TR>
	</TBODY>
</TABLE>
<BR>

<TABLE border="0">
	<TBODY>
		<TR>
			<TD class="tdnone" width="800">
			<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
				<FONT color="#ffffff"><B>処理情報</B></FONT>
			</DIV>
			</TD>
		</TR>
	  </TBODY>
	</TABLE>

<TABLE class="tbline" width="1007">
	<TBODY>
		<TR>
			<TD class="tdnone2" width="100">処理判定日</TD>
			<TD class="tdnone3" width="100">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtDecision_YMD" name="txtDecision_YMD" tabindex="430" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtDecision_YMD; ?>">
				<script type="text/javascript">
					if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtDecision_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					}
				</script>
			</TD>
			<TD class="tdnone2" width="100">製造部長承認日</TD>
			<TD class="tdnone3" width="100">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtApproval_YMD" name="txtApproval_YMD" tabindex="440" size="7" maxlength="10" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtApproval_YMD; ?>">
					<script type="text/javascript">
						if(<?php echo $mode;?> =="1" || <?php echo $mode;?> =="2"){
							//Ajaxカレンダ読込
							InputCalendar.createOnLoaded("txtApproval_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
						}
					</script>
			</TD>
			<TD class="tdnone2" width="100">選別工数</TD>
			<TD class="tdnone3" width="100">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtSelection" name="txtSelection"tabindex="450" size="8" maxlength="8" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtSelection; ?>">h
			</TD>
			<TD class="tdnone0" width="100">起因工程</TD>
			<TD class="tdnone3" width="300" colspan="3">
				<SELECT id="cmbDueProcess_KBN" name="cmbDueProcess_KBN" tabindex="460" <?php echo $strCmbLock; ?>>
					<OPTION selected value="-1" >▼選択して下さい</OPTION>
					<?php $module_sel->fMakeCombo('C36',$cmbDueProcess_KBN); ?>
				</SELECT>
				<INPUT type="checkbox" id="chkExcluded" name="chkExcluded" tabindex="470" value="1" <?php echo $strExcludedCheck; ?> <?php echo $strChkLock; ?>>不良集計対象外
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0">その他コメント</TD>
			<TD class="tdnone3" colspan="9">
				<textarea class="<?php echo $strBoxDsp; ?>" style="width:100%" rows="3" id="txtComments" name="txtComments" tabindex="480" <?php echo $strUpdCtrl; ?>><?php echo $txtComments; ?></textarea>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0" width="200" colspan="2">
				<?php
					if($mode == "1" || $mode == "2"){
						echo "<A href=\"JavaScript:fOpenSearch('F_MSK0020','txtPartner_CD','txtPartner_NM','','','','','',3)\" onclick=\"\" tabindex=\"490\">起因部署・協力会社CD</A>";
					}else{
						echo "起因部署・協力会社CD";
					}
				?>
			</TD>
			<TD class="tdnone3" width="100">
				<INPUT type="text" class="txtboxdisp" id="txtPartner_CD" name="txtPartner_CD" tabindex="500" size="5" maxlength="8" style="ime-mode: disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtPartner_CD; ?>" onBlur="window.event.keyCode=13;fGetItem('<?php echo($mode); ?>','3');">
			</TD>
			<TD class="tdnone0" width="200" colspan="2">起因部署・協力会社名</TD>
			<TD class="tdnone3" width="100" colspan="2">
				<INPUT type="text" class="textboxdisp" id="txtPartner_NM" name="txtPartner_NM" size="22" maxlength="96" style="ime-mode: disabled;" value="<?php echo $txtPartner_NM; ?>">
			</TD>
			<TD class="tdnone0" width="100">処理</TD>
			<TD class="tdnone3" colspan="2" width="100">
				<SELECT name="cmbProcess_KBN" id="cmbProcess_KBN" tabindex="510" <?php echo $strCmbLock; ?> onBlur="window.event.keyCode=13;fCalcPrice('<?php echo($mode); ?>',1);">
					<OPTION selected value="-1" >▼選択して下さい</OPTION>
					<?php $module_sel->fMakeCombo('C37',$cmbProcess_KBN); ?>
				</SELECT>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0" width="100">納入数量</TD>
			<TD class="tdnone3" width="100">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtFailure_QTY" name="txtFailure_QTY" tabindex="520" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtFailure_QTY; ?>" onBlur="window.event.keyCode=13;fCalcPrice('<?php echo($mode); ?>',2,0);">個
			</TD>
			<TD class="tdnone0" width="100">廃棄数量</TD>
			<TD class="tdnone3" width="100">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtDisposal_QTY" name="txtDisposal_QTY" tabindex="525" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtDisposal_QTY; ?>" onBlur="window.event.keyCode=13;fCalcPrice('<?php echo($mode); ?>',2,1);">個
			</TD>
			<TD class="tdnone0" width="100">返却数量</TD>
			<TD class="tdnone3" width="100">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtReturn_QTY" name="txtReturn_QTY" tabindex="530" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtReturn_QTY; ?>" onBlur="window.event.keyCode=13;fCalcPrice('<?php echo($mode); ?>',2,2);">個
			</TD>
			<TD class="tdnone0" width="100">調整ﾛｽ数量</TD>
			<TD class="tdnone3" width="100">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtLoss_QTY" name="txtLoss_QTY" tabindex="535" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtLoss_QTY; ?>" onBlur="window.event.keyCode=13;fCalcPrice('<?php echo($mode); ?>',2,3);">個
			</TD>
			<TD class="tdnone0" width="100">対象外数量</TD>
			<TD class="tdnone3" width="100">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtExclud_QTY" name="txtExclud_QTY" tabindex="540" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtExclud_QTY; ?>" onBlur="window.event.keyCode=13;fCalcPrice('<?php echo($mode); ?>',2,4);">個
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0">納入金額</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtFailurePrice" name="txtFailurePrice" tabindex="545" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtFailurePrice; ?>">円
			</TD>
			<TD class="tdnone0">廃棄金額</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtDisposalPrice" name="txtDisposalPrice" tabindex="550" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtDisposalPrice; ?>">円
			</TD>
			<TD class="tdnone0">返却金額</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtReturnPrice" name="txtReturnPrice" tabindex="555" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtReturnPrice; ?>">円
			</TD>
			<TD class="tdnone0">調整ﾛｽ金額</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtLossPrice" name="txtLossPrice" tabindex="560" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtLossPrice; ?>">円
			</TD>
			<TD class="tdnone0">対象外金額</TD>
			<TD class="tdnone3">
				<INPUT type="text" class="<?php echo $strBoxDsp; ?>" id="txtExcludPrice" name="txtExcludPrice" tabindex="565" size="6" maxlength="9" style="text-align:right; ime-mode:disabled;" <?php echo $strUpdCtrl; ?> value="<?php echo $txtExcludPrice; ?>">円
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0" width="166">伝票添付<br>(10MBまでアップロード可能)</TD>
			<TD class="tdnone3" width="834" colspan="9">
				<INPUT type="file" name="getVoucher" id="getVoucher" tabindex="570" size="100" <?php echo $strBtnLock; ?> />
				<INPUT type="button" value="アップロード" tabindex="575" style="margin-top:4" <?php echo $strBtnLock; ?>  onClick="fncFileUpload('<?php echo($mode); ?>','アップロード',0,2,0,'getVoucher')"/>
				<TABLE class="type08" width="100%" style="margin-top:6">
					<THEAD>
						<tr>
							<th scope="cols" width="10%">選択</th>
							<th scope="cols" width="10%">リンク</th>
							<th scope="cols" width="80%">ファイル名</th>
						</tr>
					 </THEAD>
					<TBODY>
					<?php
					//ディレクトリ存在チェック
					if(file_exists($dirVou)){
						//アップロードファイル取得
						$filelist=scandir($dirVou);
						foreach($filelist as $file):
							if(!is_dir($file)):
					?>
						<tr>
							<td>
								<INPUT type="checkbox" name="deletefile3[]" tabindex="580" <?php echo $strBtnLock; ?> value="<?php echo $file; ?>" />
							</td>
							<td>
								<?php 
									echo "<INPUT type='button' tabindex='585' value='ダウンロード' style='background-color : #fdc257;' onClick=\"fStartDownload('".($dirVou.$file)."','".$file."');\">";
									
								?>
							</td>
							<td>
								<?php echo $file; ?>
							</td>
						
						</tr>
						<?php endif; endforeach; ?>
					<?php } ?>
					</TBODY>
				</TABLE>
				<p>
					<INPUT type="button" tabindex="590" value="削　除" <?php echo $strBtnLock; ?> onClick="fncFileUpload('<?php echo($mode); ?>','削除',0,2,1,'getVoucher')"/>
				</p>
			</TD>
		</TR>
	</TBODY>
</TABLE>

<P>
<?php
//参照以外かつボタン表示フラグがTrueならは確定ボタン表示
if($mode <> "4" && $bDispflg){
	//品証のユーザのみ表示
	if(substr($_SESSION['login'][2],0,3) == "117"){
?>

		<INPUT type="button" name="btnExcute" value="　確　定　" tabindex="595" onClick="fncExcute('<?php echo($mode); ?>','<?php echo($modeN); ?>',0)" tabindex="2400">
		<INPUT type="hidden" name="hidTempFolder" value="<?php echo $hidTempFolder; ?>">
		<INPUT type="hidden" name="hidTemp" value="">
		<INPUT type="hidden" name="hidAction" value="">

<?php
	}
?>

<?php if($hidFrame == 0){ ?>
<INPUT type="button" name="btnSearch" value="　戻　る　" tabindex="597" onClick="fReturn(<?php echo $mode;?>)">
<?php } ?>
<?php echo $strManulPath;  ?>
<br>
<INPUT type="hidden" name="hidUCount" value="<?php echo $hidUCount;?>">

<?php
}
?>
</P>

<INPUT type="hidden" name="hidUp" value="0">
<INPUT type="hidden" name="hidTantoCd" value="<?php echo $_SESSION['login'][0];?>">
<INPUT type="hidden" name="hidFrame" value="<?php echo $hidFrame; ?>">
<INPUT type="hidden" name="hidReference_SEQ" value="<?php echo $hidReference_SEQ; ?>">
<INPUT type="hidden" name="hidPlan_NO" value="<?php echo $hidPlan_NO; ?>">
<INPUT type="hidden" name="hidPlanSeq" value="<?php echo $hidPlanSeq; ?>">
<INPUT type="hidden" name="hidProdKbn" value="<?php echo $hidProdKbn; ?>">
<INPUT type="hidden" name="hidDecision_OLD_YMD" value="<?php echo $hidDecision_OLD_YMD; ?>">
</FORM>
<script type="text/javascript" >
	/* 初期フォーカス */
	document.getElementById('cmbTargetSection_KBN').focus();
	fCalcPrice(1,1);
</script>
</BODY>
</HTML>