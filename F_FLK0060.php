<?php

	//****************************************************************************
	//プログラム名：環境紛争鉱物入力
	//プログラムID：F_FLK0060
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2017/08/02
	//履歴　　　　：2017/08/02 新規作成 久米
	//　　　　　　　：2019/04/01 「顧客名」、「内容」を部分一致で検索条件へ追加、
	//					   更新モードでアップロード時に入力値が削除される不具合改善 藤田
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
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/F_CMN0010.php?page=".$_SERVER['REQUEST_URI']);
		exit;
	}
	$token = sha1(uniqid(mt_rand(), true));

	// トークンをセッションに追加する
	$_SESSION['token'][] = $token;

	//ファイル読み込み
	require_once("module_sel.php");
	require_once("module_upd.php");
	require_once("module_common.php");
	//require("/jphpmailer.php");

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
//2019/04/01 AD START
		$aJoken[4] = urlencode($module_cmn->fEscape($aJoken[4]));
		$aJoken[5] = urlencode($module_cmn->fEscape($aJoken[5]));
//2019/04/01 AD END
		
	}

	//画面項目の取得
	//引数の取得
	$txtReference_No = $_POST['txtReference_No'];				//整理NO
	
	if($strReference_No == ""){
		$strReference_No = $txtReference_No;
	}
	
	//進捗状態(初期は0(受付))
	//if(isset($_POST['txtProgres_Stage'])) {
	//	$txtProgres_Stage = $_POST['txtProgres_Stage'];			//進捗状態
	//}else{
	//	$txtProgres_Stage = "0";								//進捗状態
	//}

	$txtContact_Accept_YMD = $_POST['txtContact_Accept_YMD'];		//連絡受理日 
	$cmbInfo_Get_KBN = $_POST['cmbInfo_Get_KBN'];					//情報入手元
	$txtCust_CD = $_POST['txtCust_CD'];								//顧客CD
	$txtCust_NM = $_POST['txtCust_NM'];								//顧客名
	$txtInfo_Officer = $_POST['txtInfo_Officer'];					//提供者
	$cmbSurvey_KBN = $_POST['cmbSurvey_KBN'];						//調査区分
	$cmbTarget_Section_KBN = $_POST['cmbTarget_Section_KBN'];		//対象部門

	$txtEnv_Contents = $_POST['txtEnv_Contents'];					//内容
	$txtTarget_Item = $_POST['txtTarget_Item'];						//対象製品
	$txtCust_Ap_Ans_YMD = $_POST['txtCust_Ap_Ans_YMD'];				//顧客指定回答日
	$txtAns_YMD = $_POST['txtAns_YMD'];								//回答日
	
	
	
	$chkCustAns = $_POST['chkCustAns'];								//顧客指定回答不要チェック

	
	$chkAnsDoc1 = $_POST['chkAnsDoc1'];								//ICPデータ　
	$chkAnsDoc2 = $_POST['chkAnsDoc2'];								//(M)SDS　
	$chkAnsDoc3 = $_POST['chkAnsDoc3'];								//MILシート　
	$chkAnsDoc4 = $_POST['chkAnsDoc4'];								//ChemSHERPAデータ　
	$chkAnsDoc5 = $_POST['chkAnsDoc5'];								//MSDSplueS
	$chkAnsDoc6 = $_POST['chkAnsDoc6'];								//AIS
	$chkAnsDoc7 = $_POST['chkAnsDoc7'];								//IMDS　
	$chkAnsDoc8 = $_POST['chkAnsDoc8'];								//EISS
	$chkAnsDoc9 = $_POST['chkAnsDoc9'];								//受領書
	$chkAnsDoc15 = $_POST['chkAnsDoc15'];							//その他

	//チェック記述に変換
	if($chkAnsDoc1 == "1"){
		$chkAnsDoc1 = "checked";
	}
	if($chkAnsDoc2 == "1"){
		$chkAnsDoc2 = "checked";
	}
	if($chkAnsDoc3 == "1"){
		$chkAnsDoc3 = "checked";
	}
	if($chkAnsDoc4 == "1"){
		$chkAnsDoc4 = "checked";
	}
	if($chkAnsDoc5 == "1"){
		$chkAnsDoc5 = "checked";
	}
	if($chkAnsDoc6 == "1"){
		$chkAnsDoc6 = "checked";
	}
	if($chkAnsDoc7 == "1"){
		$chkAnsDoc7 = "checked";
	}
	if($chkAnsDoc8 == "1"){
		$chkAnsDoc8 = "checked";
	}
	if($chkAnsDoc9 == "1"){
		$chkAnsDoc9 = "checked";
	}
	if($chkAnsDoc15 == "1"){
		$chkAnsDoc15 = "checked";
	}

	$txtAnsDocEtc = $_POST['txtAnsDocEtc'];							//その他入力用
	$txtAns_Tanto_CD = $_POST['txtAns_Tanto_CD'];					//回答者CD
	$txtAns_Tanto_NM = $_POST['txtAns_Tanto_NM'];					//回答者名
	$cmbMakerSurvey_KBN= $_POST['cmbMakerSurvey_KBN'];				//メーカー調査
	$txtPc_Ap_Ans_YMD = $_POST['txtPc_Ap_Ans_YMD'];					//品証指定回答日
	
	$hidUCount = $_POST['hidUCount'];
	$hidDCount = $_POST['hidDCount'];
	
	//メーカー調査依頼情報明細部
	$aToriCd = $_POST["txtToriCd"];
	$aToriNm = $_POST["txtToriNm"];
	$aAnsReceiptYmd = $_POST["txtAnsReceiptYmd"];
	$aComment = $_POST["txtComment"];
	
	$hidUp = $_POST['hidUp'];
	//添付ファイルアップロード用ランダムファルダ名
	$hidTempFolder = $_POST['hidTempFolder'];

	
	
	//画面件数
	$intCnt = 0;
	if (is_array($aToriCd)) {
		$hidDCount = count($aToriCd);
	}else{
		//$hidDCount = 0;
	}

	$i = 0;

	$aPara = array();

	while($i < $hidDCount){

		$aParaD[$i][0] = $aToriCd[$i];			//取引先CD
		$aParaD[$i][1] = $aToriNm[$i];			//取引先名
		$aParaD[$i][2] = $aAnsReceiptYmd[$i];	//回答受領日
		$aParaD[$i][3] = $aComment[$i];			//備考
		
		$i = $i + 1;
		
	}
	


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

		//添付ファイル用にランダムなフォルダ名を作成するためフォルダ名作成
		if($hidTempFolder == ""){
			$hidTempName = "temp_".date("YmdHis")."_".$module_cmn->fMakeRandStr(20);
			//フォルダパス作成
			$hidTempFolder  = $hidTempName;		//書類用フォルダパス
		}
		
		if($txtPc_Ap_Ans_YMD == ""){
			//品証指定回答日の初期値(稼動日５日後)を取得
			$txtPc_Ap_Ans_YMD = $module_cmn->fChangDateFormat($module_sel->fLimitCalender(5));		
		}
		
	}elseif($mode == "2"){
		$modeN ="(更新)";
		//整理NO取得
		$txtReference_No = $strReference_No;

		//対象部門
		//$strTargetSecRO = "readOnly";

	}elseif($mode == "3"){
		$modeN ="(削除)";
		//整理NO取得
		$txtReference_No = $strReference_No;
		//対象部門
		//$strTargetSecRO = "readOnly";

	}elseif($mode == "4"){
		$modeN ="(参照)";
		//整理NO取得
		$txtReference_No = $strReference_No;
		//対象部門
		//$strTargetSecRO = "readOnly";
	}elseif($mode == "5"){
		$modeN ="(流用)";
		//整理NO取得
		$txtReference_No = $strReference_No;

		//添付ファイル用にランダムなフォルダ名を作成するためフォルダ名作成
		if($hidTempFolder == ""){
			$hidTempName = "temp_".date("YmdHis")."_".$module_cmn->fMakeRandStr(20);
			//フォルダパス作成
			$hidTempFolder  = $hidTempName;		//書類用フォルダパス
		}

		
		//品証指定回答日の初期値(稼動日５日後)を取得
		$txtPc_Ap_Ans_YMD = $module_cmn->fChangDateFormat($module_sel->fLimitCalender(5));
		
	}else{
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/F_CMN0010.php?page=".$_SERVER['PHP_SELF']);
		
		exit;
	}

	//整理NOがあるかつ流用以外場合はフォルダ名変更
	if($txtReference_No <> "" && $mode <> 5){
		$hidTempFolder = trim($txtReference_No);
	}
	
	//添付資料を保存するディレクトリ
	$dir  ="upload/environment/".$hidTempFolder."/";
	//受領・回答書類・製品添付・メーカー調査依頼ファルダパス
	$dirGet  = $dir."get/";
	$dirAns  = $dir."ans/";
	$dirProd  = $dir."prod/";
	$dirMaker  = $dir."maker/";
	
	
	
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
			
			//顧客指定回答の回答不要のチェックボックス
			if($chkCustAns == "1" ){
				$strCustAnsCheck = "checked";
			}else{
				$strCustAnsCheck = "";
			}
			
			//チェック処理
			//セッションチェック(セッションが書き換えられていないか)
			if($_POST['hidTantoCd'] != $_SESSION['login'][0] && $hidFrame == 0){
			
				$strErrMsg = $module_sel->fMsgSearch("E034","");
			}

			//必須チェック
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtContact_Accept_YMD'],"連絡受理日");			//連絡受理日
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtInfo_Officer'],"情報提供者");				//情報提供者
			$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbSurvey_KBN'],"調査区分");					//調査区分
			$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbTarget_Section_KBN'],"対象部門");		//対象部門
			$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtEnv_Contents'],"内容");					//内容
			//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtTarget_Item'],"対象製品");				//対象製品
			//情報入手元が外部の場合
			if($_POST['cmbInfo_Get_KBN'] == "0"){
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtCust_CD'],"顧客CD");						//顧客CD
				//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtCust_Ap_Ans_YMD'],"顧客指定回答日");		//顧客指定回答日
			}else{
				if($_POST['txtCust_CD'] <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fMsgSearch("E043","顧客CD");						//顧客CD				
				}
				if($_POST['txtCust_Ap_Ans_YMD'] <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fMsgSearch("E043","顧客指定回答日");					//顧客指定回答日			
				}
			}
			//その他がチェックされている場合はその他入力欄必須
			if($chkAnsDoc15 == "checked"){
				$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtAnsDocEtc'],"その他入力欄");					//内容
			}
			
			$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($_POST['cmbMakerSurvey_KBN'],"メーカー調査");			//メーカー調査
			
			//文字数チェック
			$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($_POST['txtEnv_Contents'],250,"内容");			//内容
			$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($_POST['txtTarget_Item'],250,"対象製品");		//対象製品

			

			//$strErrMsg = $strErrMsg.$module_cmn->fTxtNCheck($_POST['txtConfirm_Tanto_CD'],"確認者");			//確認者(※完結日，確認者CDはいづれか未入力はエラー)

			//必須チェックでエラーがなければフォーマットチェック
			if($strErrMsg == ""){
			//フォーマットチェック

				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtContact_Accept_YMD']),"連絡受理日");			//連絡受理日 

				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtAns_YMD']),"回答日");							//回答日
				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtPc_Ap_Ans_YMD']),"顧客指定回答日");			//顧客指定回答日

				$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$_POST['txtPc_Ap_Ans_YMD']),"品証指定回答日");	//品証指定回答日(社内)

				
				
			}

			//明細行のフォーマットチェック
			if($strErrMsg == ""){
				//回答受領日のフォーマットチェック
				$i = 0;
				$aAnsReceiptYmd = $_POST["txtAnsReceiptYmd"];
				//件数分チェック
				while($i < count($aAnsReceiptYmd)){
					//日付が設定されていたらチェックする
					if(str_replace("/","",$aAnsReceiptYmd[$i]) <> ""){
						$strErrMsg = $strErrMsg.$module_cmn->fDateCheck(str_replace("/","",$aAnsReceiptYmd[$i]),"回答受領日(".($i+1)."行目)");
					}
					//備考が入力されていたらチェックする
					if(str_replace("/","",$aComment[$i]) <> ""){
						$strErrMsg = $strErrMsg.$module_cmn->fMojiCountCheck($aComment[$i],250,"備考[".($i+1)."行目]");			//備考
					}
					
					$i = $i + 1;
				}
			}

			//フォーマットチェックでエラーがなければ整合性チェック
			if($strErrMsg == ""){
				
				//顧客指定回答日と回答不要チェックが両方未設定
				if($_POST['txtCust_Ap_Ans_YMD'] == "" && $_POST['chkCustAns'] == ""){
					$strErrMsg = $strErrMsg.$module_sel->fMsgSearch("E040","");
				}
				//顧客指定回答日と回答不要チェックが両方設定
				if($_POST['txtCust_Ap_Ans_YMD'] != "" && $_POST['chkCustAns'] == "1"){
					$strErrMsg = $strErrMsg.$module_sel->fMsgSearch("E040","");
				}

				//回答日・回答者CDのどちらか片方のみの入力はエラーとする 
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

				//顧客CD
				if(trim($_POST['txtCust_CD']) <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtCust_CD'],"顧客CD","V_FL_CUST_INFO","C_CUST_CD");
				}

				//確認者
				if(trim($_POST['txtAns_Tanto_CD']) <> ""){
					$strErrMsg = $strErrMsg.$module_sel->fSonzaiCheck($_POST['txtAns_Tanto_CD'],"回答者CD","V_FL_TANTO_INFO","C_TANTO_CD");
				}

			}


			//データ存在チェックでエラーがなければ更新回数チェック(登録以外のみ)
			if($strErrMsg == ""  && $mode <> "1"){

				//更新回数チェック
				if(!$module_sel->fKoshinCheck($_POST['txtReference_No'],$_POST['hidUCount'],"不具合入力","T_TR_ENV","C_REFERENCE_NO")){
					$strErrMsg = $module_sel->fMsgSearch("E002","整理NO:".$_POST['txtReference_No']);
				}
			}


			//エラーメッセージがなければ更新処理を実行
			if($strErrMsg == ""){
				//更新処理戻り値用変数(整理ＮＯが入る)
				$aExcutePara = array();
				$aExcuteParaD = array();

				
				//Oracleへの接続の確立(トランザクション開始)
				$conn = $module_upd->fTransactionStart();
				$Reference_No = $_POST['txtReference_No'];
				//更新処理
				//$aExcutePara = $module_upd->fKonyuExcute($conn,$mode,$save,$txtReference_No,$_SESSION['login'],$_POST['hidUCount']);
				$aExcutePara = $module_upd->fEnvTorokuExcute($conn,$mode,$Reference_No,$_SESSION['login'],$_POST['hidUCount']);
				//メーカー調査データ更新処理
				$aExcuteParaD = $module_upd->fMakerAnsExcute($conn,$mode,$aExcutePara[0],$_SESSION['login'],$_POST['hidUCount']);

				//登録時添付ファイルがあれば整理NOでフォルダ名を変更する
				if(($mode == '1' || $mode == '5') && file_exists($dir)){

					//フォルダ名変更
					if(!rename($dir,"upload/environment/".trim($aExcutePara[0])."/")){
						$aExcutePara[0] = "err";
					}else{
						//添付ファイルフォルダ名を整理NOで置き換え
						$hidTempFolder = $aExcutePara[0];
					}						
					
					//echo $dir;
				}

				//更新処理の結果判断
				if( substr($aExcutePara[0],0,3) <> "err"){

					//不具合登録または流用登録時
					if($mode == '1' || $mode == '5'){
						
						//更新した整理NOを戻す
						$txtReference_No = $aExcutePara[0];

						//添付ファルダ名クリア
						$hidTempFolder = "";
						
						//トランザクション処理とOracle切断
						$module_upd->fTransactionEnd($conn,true);
						$strMsg = $module_sel->fMsgSearch("N001","整理NO:".$txtReference_No);	//登録しました
						//整理NOクリア
						$txtReference_No = "";

					}else{
						//更新した整理NOを戻す
						$txtReference_No = $aExcutePara[0];
						
						//更新回数カウントアップ
						$hidUCount = $hidUCount + 1;
						
						
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

	//日本語を省くための正規表現
	$pattern="/^[a-z0-9A-Z\-_]+\.[a-zA-Z]{3}$/";

	//サイズチェック
	if($_SERVER['CONTENT_LENGTH'] > 10485760){
		$strErrMsg = $module_sel->fMsgSearch("E008","");

	}else{
		//リクエストがPOSTかどうかチェック
		if($_SERVER["REQUEST_METHOD"]=="POST" && !empty ($_POST)){
			
			
			
			//フォームボタンのvalueを取得する
			$hidTemp = $_POST["hidTemp"];	//0:受領書類 1:回答書類 2:製品添付 3:メーカー調査
			$hidAction = $_POST["hidAction"];	//0:アップロード 1:削除
			
			//$hidAction2 = $_POST["hidAction2"];	//回答書類
			//$hidAction3 = $_POST["hidAction"];	//受領書類
			//$hidAction2 = $_POST["hidAction2"];	//回答書類
			
			//整理NO退避
			$txtReference_No = $_POST['txtReference_No'];
			
			
			/**
			 * 添付ファイルアップロード・削除処理
			 * $pAction:処理区分
			 * $pName:ファイルオブジェクト名
			 * $pFileName:アップロードファイルオブジェクト名
			 * $pDir:上位フォルダパス
			 * $pDirSub:下位フォルダパス
			 * $pFileKbn：ファイル区分
			 *
			 */
			
			if($hidTemp == ""){
				
			}
			/*-------------------------------------------------------
			受領書類アップロード・削除する処理
			--------------------------------------------------------*/
			elseif($hidTemp == 0){ 
				$strErrMsg = $module_cmn->fFileUploadDelete($hidAction,"getDoc","deletefile",$dir,$dirGet,"受領書類");
			}
			/*-------------------------------------------------------
			回答書類アップロード・削除する処理
			--------------------------------------------------------*/
			elseif($hidTemp == 1){ 
				$strErrMsg = $module_cmn->fFileUploadDelete($hidAction,"ansDoc","deletefile2",$dir,$dirAns,"回答書類");
			}
			/*-------------------------------------------------------
			製品添付アップロード・削除する処理
			--------------------------------------------------------*/
			elseif($hidTemp == 2){ 
				$strErrMsg = $module_cmn->fFileUploadDelete($hidAction,"prodDoc","deletefile3",$dir,$dirProd,"対象製品資料");
			}
			/*-------------------------------------------------------
			メーカー調査添付アップロード・削除する処理
			--------------------------------------------------------*/
			elseif($hidTemp == 3){ 
				$strErrMsg = $module_cmn->fFileUploadDelete($hidAction,"makerDoc","deletefile4",$dir,$dirMaker,"メーカー調査資料");
			}
		}
	
	}
	
	//呼び出した整理NOがない場合はエラー
	if($txtReference_No == "" && $hidFrame == 1){
		$strErrMsg = $module_sel->fMsgSearch("E001","対象の環境・紛争鉱物情報がありません");
		$bDispflg = false;		
	}


	//効果の確認通知有無のチェックボックス
	//if($chkEffectAlert == "1" ){
	//	$strEffectAlert = "checked";
	//}else{
	//	$strEffectAlert = "";
	//}

	
		//登録モード以外はデータ取得を行う
	if($mode <> "1" && $txtReference_No <> "" && $strErrMsg == ""){
//2019/04/01 ED START T.FUJITA
/*
		//echo $strReference_No;
		//環境情報再検索処理
		$aPara = $module_sel->fGetEnvData($txtReference_No);

		$txtReference_No 		= $aPara[0]; 	//整理NO
		$cmbProgres_Stage 		= $aPara[1]; 	//進捗状態
		

		$txtContact_Accept_YMD	= $module_cmn->fChangDateFormat($aPara[2]);		//連絡受理日
		$cmbInfo_Get_KBN		= $aPara[3]; 	//情報入手元
		$txtCust_CD 			= $aPara[4]; 	//顧客CD
		$txtCust_NM 			= $aPara[5]; 	//顧客名
		$txtInfo_Officer 		= $aPara[6]; 	//情報提供者
		$cmbSurvey_KBN			= $aPara[7]; 	//調査区分
		$cmbTarget_Section_KBN 	= $aPara[8]; 	//対象部門
		$txtEnv_Contents		= $aPara[9]; 	//内容
		$txtTarget_Item			= $aPara[10]; 	//対象製品
		$txtCust_Ap_Ans_YMD 	= $module_cmn->fChangDateFormat($aPara[11]); 	//顧客指定回答日

		//顧客指定回答日が0の場合は回答不要にチェックつける
		if($txtCust_Ap_Ans_YMD == 0){
			$chkCustAns = "1";
		}
		$txtAns_YMD 			= $module_cmn->fChangDateFormat($aPara[12]); 	//回答日
		$chkAnsDoc1				= str_replace(1,"checked",$aPara[13]); 	//提出要求書類1
		$chkAnsDoc2				= str_replace(1,"checked",$aPara[14]); 	//提出要求書類2
		$chkAnsDoc3				= str_replace(1,"checked",$aPara[15]); 	//提出要求書類3
		$chkAnsDoc4				= str_replace(1,"checked",$aPara[16]); 	//提出要求書類4
		$chkAnsDoc5				= str_replace(1,"checked",$aPara[17]); 	//提出要求書類5
		$chkAnsDoc6				= str_replace(1,"checked",$aPara[18]); 	//提出要求書類6
		$chkAnsDoc7				= str_replace(1,"checked",$aPara[19]); 	//提出要求書類7
		$chkAnsDoc8				= str_replace(1,"checked",$aPara[20]); 	//提出要求書類8
		$chkAnsDoc9				= str_replace(1,"checked",$aPara[21]); 	//提出要求書類9
		$chkAnsDoc10			= str_replace(1,"checked",$aPara[22]); 	//提出要求書類10
		$chkAnsDoc11			= str_replace(1,"checked",$aPara[23]); 	//提出要求書類11
		$chkAnsDoc12			= str_replace(1,"checked",$aPara[24]); 	//提出要求書類12
		$chkAnsDoc13			= str_replace(1,"checked",$aPara[25]); 	//提出要求書類13
		$chkAnsDoc14			= str_replace(1,"checked",$aPara[26]); 	//提出要求書類14
		$chkAnsDoc15			= str_replace(1,"checked",$aPara[27]); 	//提出要求書類15		
		$txtAnsDocEtc			= str_replace(1,"checked",$aPara[28]); 	//提出要求書類15入力用
		
		$txtAns_Tanto_CD		= $aPara[29]; 	//回答者CD
		$txtAns_Tanto_NM		= $aPara[30]; 	//回答者名
		$cmbMakerSurvey_KBN		= $aPara[31]; 	//メーカ調査
		$txtPc_Ap_Ans_YMD		= $module_cmn->fChangDateFormat($aPara[32]); 	//品証指定回答日
		
		$hidUCount 				= $aPara[33]; 	//更新回数
*/
		if(isset($_GET['aSave'])) {
			$aSave = $_GET['aSave'];
			$txtReference_No		= $aSave[0];	//整理NO
			$cmbProgres_Stage		= $aSave[1];	//進捗状態
			$txtContact_Accept_YMD	= $aSave[2];	//連絡受理日
			$cmbInfo_Get_KBN		= $aSave[3];	//情報入手元
			$txtCust_CD				= $aSave[4];	//顧客CD
			$txtCust_NM				= $aSave[5];	//顧客名
			$txtInfo_Officer		= $aSave[6];	//情報提供者
			$cmbSurvey_KBN			= $aSave[7];	//調査区分
			$cmbTarget_Section_KBN	= $aSave[8];	//対象部門
			$txtEnv_Contents		= $aSave[9];	//内容
			$txtTarget_Item			= $aSave[10];	//対象製品
			$txtCust_Ap_Ans_YMD		= $aSave[11];	//顧客指定回答日
			//顧客指定回答日が0の場合は回答不要にチェックつける
			if($txtCust_Ap_Ans_YMD == ""){
				$chkCustAns = "1";
			}
			$txtCust_Ap_Ans_YMD = "1111";
			$txtAns_YMD				= $aSave[12];	//回答日
			$chkAnsDoc1				= $aSave[13];	//提出要求書類1
			$chkAnsDoc2				= $aSave[14];	//提出要求書類2
			$chkAnsDoc3				= $aSave[15];	//提出要求書類3
			$chkAnsDoc4				= $aSave[16];	//提出要求書類4
			$chkAnsDoc5				= $aSave[17];	//提出要求書類5
			$chkAnsDoc6				= $aSave[18];	//提出要求書類6
			$chkAnsDoc7				= $aSave[19];	//提出要求書類7
			$chkAnsDoc8				= $aSave[20];	//提出要求書類8
			$chkAnsDoc9				= $aSave[21];	//提出要求書類9
			$chkAnsDoc15			= $aSave[27];	//提出要求書類15
			$txtAnsDocEtc			= $aSave[28];	//提出要求書類15入力用
			$txtAns_Tanto_CD		= $aSave[29];	//回答者CD
			$txtAns_Tanto_NM		= $aSave[30];	//回答者名
			$cmbMakerSurvey_KBN		= $aSave[31];	//メーカ調査
			$txtPc_Ap_Ans_YMD		= $aSave[32];	//品証指定回答日
			$hidUCount				= $aSave[33];	//更新回数
		}else{
			//環境情報再検索処理
			$aPara = $module_sel->fGetEnvData($txtReference_No);

			$txtReference_No 		= $aPara[0]; 	//整理NO
			$cmbProgres_Stage 		= $aPara[1]; 	//進捗状態
			$txtContact_Accept_YMD	= $module_cmn->fChangDateFormat($aPara[2]);		//連絡受理日
			$cmbInfo_Get_KBN		= $aPara[3]; 	//情報入手元
			$txtCust_CD 			= $aPara[4]; 	//顧客CD
			$txtCust_NM 			= $aPara[5]; 	//顧客名
			$txtInfo_Officer 		= $aPara[6]; 	//情報提供者
			$cmbSurvey_KBN			= $aPara[7]; 	//調査区分
			$cmbTarget_Section_KBN 	= $aPara[8]; 	//対象部門
			$txtEnv_Contents		= $aPara[9]; 	//内容
			$txtTarget_Item			= $aPara[10]; 	//対象製品
			$txtCust_Ap_Ans_YMD 	= $module_cmn->fChangDateFormat($aPara[11]); 	//顧客指定回答日
			//顧客指定回答日が0の場合は回答不要にチェックつける
			if($txtCust_Ap_Ans_YMD == 0){
				$chkCustAns = "1";
				
			}
			$txtAns_YMD 			= $module_cmn->fChangDateFormat($aPara[12]); 	//回答日
			$chkAnsDoc1				= str_replace(1,"checked",$aPara[13]); 	//提出要求書類1
			$chkAnsDoc2				= str_replace(1,"checked",$aPara[14]); 	//提出要求書類2
			$chkAnsDoc3				= str_replace(1,"checked",$aPara[15]); 	//提出要求書類3
			$chkAnsDoc4				= str_replace(1,"checked",$aPara[16]); 	//提出要求書類4
			$chkAnsDoc5				= str_replace(1,"checked",$aPara[17]); 	//提出要求書類5
			$chkAnsDoc6				= str_replace(1,"checked",$aPara[18]); 	//提出要求書類6
			$chkAnsDoc7				= str_replace(1,"checked",$aPara[19]); 	//提出要求書類7
			$chkAnsDoc8				= str_replace(1,"checked",$aPara[20]); 	//提出要求書類8
			$chkAnsDoc9				= str_replace(1,"checked",$aPara[21]); 	//提出要求書類9
			$chkAnsDoc10			= str_replace(1,"checked",$aPara[22]); 	//提出要求書類10
			$chkAnsDoc11			= str_replace(1,"checked",$aPara[23]); 	//提出要求書類11
			$chkAnsDoc12			= str_replace(1,"checked",$aPara[24]); 	//提出要求書類12
			$chkAnsDoc13			= str_replace(1,"checked",$aPara[25]); 	//提出要求書類13
			$chkAnsDoc14			= str_replace(1,"checked",$aPara[26]); 	//提出要求書類14
			$chkAnsDoc15			= str_replace(1,"checked",$aPara[27]); 	//提出要求書類15		
			$txtAnsDocEtc			= str_replace(1,"checked",$aPara[28]); 	//提出要求書類15入力用
			
			$txtAns_Tanto_CD		= $aPara[29]; 	//回答者CD
			$txtAns_Tanto_NM		= $aPara[30]; 	//回答者名
			$cmbMakerSurvey_KBN		= $aPara[31]; 	//メーカ調査
			$txtPc_Ap_Ans_YMD		= $module_cmn->fChangDateFormat($aPara[32]); 	//品証指定回答日
			
			$hidUCount 				= $aPara[33]; 	//更新回数
		}

//2019/04/01 ED END T.FUJITA
		//顧客指定回答の回答不要のチェックボックス
		if($chkCustAns == "1" ){
			$strCustAnsCheck = "checked";
		}else{
			$strCustAnsCheck = "";
		}
		//メーカー調査依頼データ検索
		$aParaD = $module_sel->fGetMakerSurveyDData($txtReference_No);

		$hidDCount = count($aParaD);
		
	}

	//流用の場合は以下の項目をクリア
	if($mode == "5"){

		$txtReference_No = $_POST["txtReference_No"];
		$cmbProgres_Stage = $_POST["hidProgres_Stage"];
		$txtContact_Accept_YMD = $_POST["txtContact_Accept_YMD"];
		$txtCust_Ap_Ans_YMD = $_POST["txtCust_Ap_Ans_YMD"];
		$txtAns_YMD = $_POST["txtAns_YMD"];
		$txtAns_Tanto_CD = $_POST["txtAns_Tanto_CD"];
		$txtAns_Tanto_NM = $_POST["txtAns_Tanto_NM"];
		$txtPc_Ap_Ans_YMD = $_POST["txtPc_Ap_Ans_YMD"];
				
	}


	//マニュアルパス取得
	$strManulPath = "";
	$strManulPath = $module_cmn->fMakeManualPath($_SERVER["PHP_SELF"]);

?>
<HTML>
<HEAD>
<META name="GENERATOR" content="IBM WebSphere Studio Homepage Builder Version 11.0.0.0 for Windows">
<!--<META http-equiv="Content-Type" content="text/html; charset=UTF-8">-->
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>【環境紛争鉱物情報入力】</TITLE>

<style type="text/css">
	
table.type08 {
	border-collapse: collapse;
	text-align: left;
	line-height: 1.5;
	border-left: 1px solid #ccc;
}

table.type08 thead th {
	padding: 10px;
	font-weight: bold;
	border-top: 1px solid #ccc;
	border-right: 1px solid #ccc;
	border-bottom: 2px solid #c00;
	background: #dcdcd1;
}
table.type08 tbody th {
	width: 150px;
	padding: 10px;
	font-weight: bold;
	vertical-align: top;
	border-right: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
	background: #ececec;
}
table.type08 td {
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
		}
		return true;
	}



	//戻るボタン
	function fReturn(strMode){
		//登録以外の場合は一覧に戻る
		if(strMode != 1){
			document.form.action ="F_FLK0070.php?action=main&search=1"
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
		//if(fCheck(strMode)){

			//確認メッセージ
			if(window.confirm(strDialogMsg + 'してもよろしいですか？')){
				document.form.hidUp.value = 1;
				//ヘッダの担当者コードをセット
				//document.form.hidTantoCd.value = parent.head.hidTantoCd.value;
				
				document.form.encoding = "multipart/form-data";
//2019/04/01 ED START
/*
				document.form.action ="F_FLK0060.php?mode="
				+ strMode + "&save=" + intSave +
				"&aJoken[0]=<?php echo $aJoken[0];?>
				&aJoken[1]=<?php echo $aJoken[1];?>
				&aJoken[2]=<?php echo $aJoken[2];?>
				&aJoken[3]=<?php echo $aJoken[3];?>";
*/
				document.form.action ="F_FLK0060.php?mode="
				+ strMode + "&save=" + intSave +
				"&aJoken[0]=<?php echo $aJoken[0];?>
				&aJoken[1]=<?php echo $aJoken[1];?>
				&aJoken[2]=<?php echo $aJoken[2];?>
				&aJoken[3]=<?php echo $aJoken[3];?>
				&aJoken[4]=<?php echo $aJoken[4];?>
				&aJoken[5]=<?php echo $aJoken[5];?>";
//2019/04/01 ED END
				document.form.submit();
			}else{
				return false;
			}

		//}

	}

	//ファイルアップロード削除ボタン押下時
	function fncFileUpload(strMode,strDialogMsg,intSave,intFile,intKbn,strFileName){
		
		//機種依存文字チェック 2017/10/18追加 k.kume
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
			//メーカー調査依頼情報件数取得
			fMakerSurverDetailGet();
			
			//ヘッダの担当者コードをセット
			//document.form.hidTantoCd.value = parent.head.hidTantoCd.value;
			document.form.encoding = "multipart/form-data";
			document.form.method = "POST";
			document.form.target = "_self";
//2019/04/01 ED START
/*
			document.form.action ="F_FLK0060.php?mode="
					+ strMode + "&save=" + intSave +
					"&aJoken[0]=<?php echo $aJoken[0];?>
					&aJoken[1]=<?php echo $aJoken[1];?>
					&aJoken[2]=<?php echo $aJoken[2];?>
					&aJoken[3]=<?php echo $aJoken[3];?>";
*/
			document.form.action ="F_FLK0060.php?mode="
					+ strMode + "&save=" + intSave +
					"&aJoken[0]=<?php echo $aJoken[0];?>
					&aJoken[1]=<?php echo $aJoken[1];?>
					&aJoken[2]=<?php echo $aJoken[2];?>
					&aJoken[3]=<?php echo $aJoken[3];?>
					&aJoken[4]=<?php echo $aJoken[4];?>
					&aJoken[5]=<?php echo $aJoken[5];?>" +
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
					"&aSave[33]=" + aSave[33];
//2019/04/01 ED END
			
			//0：受領書類,1:回答書類,2:製品添付,3:メーカー調査
			document.form.hidTemp.value = intFile;
			//0:アップロード,1:削除
			document.form.hidAction.value = intKbn;

			//document.form.hidTempFolder.value = "<?php echo $_POST['hidTempFolder']; ?>";
			
			document.form.submit();
			return true;
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

//2019/04/01 ADD START
	//入力値保管
	function fncSetSaveArray(){
		var aSave = new Array( );
		aSave[0] = document.form.txtReference_No.value;
		aSave[1] = document.form.txtProgres_Stage_Nm.value;
		aSave[2] = document.form.txtContact_Accept_YMD.value;
		aSave[3] = document.form.cmbInfo_Get_KBN.value;
		aSave[4] = document.form.txtCust_CD.value;
		aSave[5] = document.form.txtCust_NM.value;
		aSave[6] = document.form.txtInfo_Officer.value;
		aSave[7] = document.form.cmbSurvey_KBN.value;
		aSave[8] = document.form.cmbTarget_Section_KBN.value;
		aSave[9] = document.form.txtEnv_Contents.value;
		aSave[10] = document.form.txtTarget_Item.value;
		aSave[11] = document.form.txtCust_Ap_Ans_YMD.value;
		aSave[12] = document.form.txtAns_YMD.value;
		aSave[13] = document.form.chkAnsDoc1.value;
		aSave[14] = document.form.chkAnsDoc2.value;
		aSave[15] = document.form.chkAnsDoc3.value;
		aSave[16] = document.form.chkAnsDoc4.value;
		aSave[17] = document.form.chkAnsDoc5.value;
		aSave[18] = document.form.chkAnsDoc6.value;
		aSave[19] = document.form.chkAnsDoc7.value;
		aSave[20] = document.form.chkAnsDoc8.value;
		aSave[21] = document.form.chkAnsDoc9.value;
		aSave[27] = document.form.chkAnsDoc15.value;
		aSave[28] = document.form.txtAnsDocEtc.value;
		aSave[29] = document.form.txtAns_Tanto_CD.value;
		aSave[30] = document.form.txtAns_Tanto_NM.value;
		aSave[31] = document.form.cmbMakerSurvey_KBN.value;
		aSave[32] = document.form.txtPc_Ap_Ans_YMD.value;
		aSave[33] = document.form.hidUCount.value;

		return aSave;

	}
//2019/04/01 ADD END

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

	//テーブル行追加
	//引数･･･追加対象行数
	function fncInsertRow(trigger){

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
		cloneNode.getElementsByTagName("INPUT")[0].value = "";
		//cloneNode.getElementsByTagName("INPUT")[1].value = "";
		cloneNode.getElementsByTagName("INPUT")[2].value = "";
		cloneNode.getElementsByTagName("INPUT")[3].value = "";

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
		//alert(document.getElementsByName("txtToriCd[]")[1].value);

	}

	//行削除
	function fDelRow(obj){

		var i,j,objNode,objNodeList,objNodeCol;

		// 対象テーブル
		objNode = document.getElementById( "Table2" );

		
		// 対象テーブル内の TR ノード
		objNodeList = objNode.getElementsByTagName( "TR" );

		// 残りの行数が2の場合は削除させない
		if(objNodeList.length == 2 ){
			//値のみクリア
			document.form.elements['txtToriCd[]'].value = "";
			document.form.elements['txtToriNm[]'].value = "";
			document.form.elements['txtAnsReceiptYmd[]'].value = "";
			document.form.elements['txtComment[]'].value = "";
			
			//alert("削除できません");
		}else{
			//行ごと削除
			var TR = obj.parentNode.parentNode;
			TR.parentNode.deleteRow(TR.sectionRowIndex);
		}

	}
	
	//メーカー調査依頼情報取得
	function fMakerSurverDetailGet(){
		//明細件数を退避
		document.form.hidDCount.value = document.form.elements["txtToriCd[]"].length;
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
<?php if($strMsg <> "" && $mode == '1') { ?> onLoad="fClearFormAll()" <?php } ?>">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000"><SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【環境紛争鉱物情報入力】<?php echo($modeN); ?>
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

<FORM name="form" method="post" enctype="multipart/form-data" >
	<TABLE border="0">
	  <TBODY>
		<TR>
		  <TD class="tdnone" width="800" >
			<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
				<FONT color="#ffffff"><B>受付情報</B></FONT>
			</DIV>
		  </TD>
		  <TD class="tdnone" width="200" align="right">
			<?php if($hidFrame == 0){ ?>
			<INPUT type="button" name="btnSearch" value="　戻　る　" onClick="fReturn(<?php echo $mode;?>)">
			<?php } ?>
			<?php echo $strManulPath;  ?>
		  </TD>
		</TR>
	  </TBODY>
	</TABLE>

	<TABLE class="tbline"  width="1000"  >

	  <TBODY>
		<TR>
		  <TD class="tdnone9" height="46" width="200">整理ＮＯ</TD>
		  <TD colspan="" class="tdnone3" height="46" width="150">
			<INPUT size="20" type="text" class="textboxdisp" name="txtReference_No" style="ime-mode: disabled;" readonly value="<?php echo $txtReference_No; ?>">
		  </TD>
		  <TD class="tdnone9" height="46" width="100">進捗状態</TD>
		  <TD class="tdnone3" height="46" width="150">
			<INPUT size="20" type="text" class="textboxdisp" name="txtProgres_Stage_Nm" style="ime-mode: disabled;" readonly value="<?php echo $module_sel->fDispKbn('C29',$cmbProgres_Stage); ?>">
			<INPUT type="hidden" name="hidProgres_Stage" value="<?php echo $cmbProgres_Stage; ?>">
		  </TD>
		  <TD class="tdnone1" height="46" width="150">連絡受理日</TD>
		  <TD  class="tdnone3" height="46" width="150">
			<INPUT size="7" type="text" id="txtContact_Accept_YMD" name="txtContact_Accept_YMD" maxlength="10" style="ime-mode: disabled;" tabindex="5" value="<?php echo $txtContact_Accept_YMD; ?>">
				<script type="text/javascript">
					//Ajaxカレンダ読込
					InputCalendar.createOnLoaded("txtContact_Accept_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
				</script>
		  </TD>
		</TR>
		<TR style="border:1px solid">
		  <TD class="tdnone1" >情報入手先</TD>
		  <TD class="tdnone3">
			<SELECT name="cmbInfo_Get_KBN" id="cmbInfo_Get_KBN" tabindex="60" >
				<OPTION selected value="-1" >▼選択して下さい</OPTION>
				<?php $module_sel->fMakeCombo('C30',$cmbInfo_Get_KBN); ?>
			</SELECT>
		  </TD>
		  <TD class="tdnone0" >
			<A href="JavaScript:fOpenSearch('F_MSK0020','txtCust_CD','txtCust_NM','','','','','','0')" onclick="">顧客CD</A>
		  </TD>
		  <TD class="tdnone3">
			<INPUT size="5" type="text" id="txtCust_CD" name="txtCust_CD" maxlength="5" tabindex="50" style="ime-mode: disabled;" value="<?php echo $txtCust_CD; ?>" tabindex="40" onBlur="fCustGet('<?php echo($mode); ?>');" >
		  </TD>
		  <TD class="tdnone9">顧客名</TD>
		  <TD class="tdnone3" >
			<INPUT size="36" type="text" class="textboxdisp"  id="txtCust_NM" name="txtCust_NM" readonly value="<?php echo $txtCust_NM; ?>">
		  </TD>
		</TR>
		<TR style="border:1px solid">
		  
		  <TD class="tdnone1">情報提供者</TD>
		  <TD class="tdnone3" colspan="6">
			<INPUT size="50" type="text" id="txtInfo_Officer" name="txtInfo_Officer" maxlength="25" tabindex="55" value="<?php echo $txtInfo_Officer; ?>">
		  </TD>
		</TR>

		<TR style="border:1px solid">
		  <TD class="tdnone1" >調査区分</TD>
		  <TD class="tdnone3" colspan="3">
			<SELECT name="cmbSurvey_KBN" id="cmbSurvey_KBN" tabindex="60" >
				<OPTION selected value="-1" >▼選択して下さい</OPTION>
				<?php $module_sel->fMakeCombo('C31',$cmbSurvey_KBN); ?>
			</SELECT>
		  </TD>
		  <TD class="tdnone1" height="46" >対象部門</TD>
		  <TD  class="tdnone3" height="46" width=""　colspan="4">
			<SELECT name="cmbTarget_Section_KBN" id="cmbTarget_Section_KBN" tabindex="100" >
				<OPTION selected value="-1" >▼選択して下さい</OPTION>
				<?php $module_sel->fMakeCombo('C33',$cmbTarget_Section_KBN); ?>
			</SELECT>
		  </TD>
		</TR>
		<TR style="border:1px solid">
			<TD class="tdnone2" >受領書類<br>(1ファイル10MBまでアップロード可能)</TD>
			<TD class="tdnone3" colspan="6" width="200">
				<input type="file" name="getDoc" id="getDoc" size="100" />
				<input type="button" value="アップロード" onClick="fncFileUpload('<?php echo($mode); ?>','アップロード',0,0,0,'getDoc')"/>
				<table class="type08" width="100%">
					<thead>
						<tr>
							<th scope="cols" width="10%">選択</th>
							<th scope="cols" width="10%">リンク</th>
							<th scope="cols" width="80%">ファイル名</th>
						</tr>　　
					  </thead>
					<tbody>
					<?php
					//ディレクトリ存在チェック
					if(file_exists($dirGet)){
						//アップロードファイル取得
						$filelist=scandir($dirGet);
						foreach($filelist as $file):
							if(!is_dir($file)):
					?>
						<tr>
							<td>
								<input type="checkbox" name="deletefile[]" value="<?php echo $file; ?>" />
							</td>
							<td>
								<?php 
									echo "<INPUT type='button' value='ダウンロード' style='background-color : #fdc257;' onClick=\"fStartDownload('".($dirGet.$file)."','".$file."');\">";
									
								?>
							</td>
							<td>
								<?php echo $file; ?>
							</td>
						
						</tr>
						<?php endif; endforeach; ?>
					<?php } ?>
					
					</tbody>
				</table>
				<p>
				<input type="button" value="削　除" onClick="fncFileUpload('<?php echo($mode); ?>','削除',0,0,1,'getDoc')"/>
				</p>
			</TD>
		</TR>
		
		<TR style="border:1px solid">
		  <TD class="tdnone1">内容</TD>
		  <TD colspan="7" class="tdnone3" height="46" >
			<textarea  cols="100" rows="5" name="txtEnv_Contents" tabindex="110" maxlength="250"><?php echo $txtEnv_Contents; ?></textarea>
		  </TD>
		</TR>
		<TR style="border:1px solid">
		  <TD class="tdnone2">対象製品</TD>
		  <TD colspan="7" class="tdnone3" height="46" >
			<textarea  cols="100" rows="5" name="txtTarget_Item" tabindex="110" maxlength="250"><?php echo $txtTarget_Item; ?></textarea>
		  </TD>
		</TR>
		<TR style="border:1px solid">
			<TD class="tdnone2" >対象製品<br>添付ファイル<br>(1ファイル10MBまでアップロード可能)</TD>
			<TD class="tdnone3" colspan="7" width="200">
				<input type="file" name="prodDoc" id="prodDoc" size="100" />
				<input type="button" value="アップロード" onClick="fncFileUpload('<?php echo($mode); ?>','アップロード',0,2,0,'prodDoc')"/>
				<table class="type08" width="100%">
					<thead>
						<tr>
							<th scope="cols" width="10%">選択</th>
							<th scope="cols" width="10%">リンク</th>
							<th scope="cols" width="80%">ファイル名</th>
						  
						</tr>　　
					  </thead>
					<tbody>
					<?php
					//ディレクトリ存在チェック
					if(file_exists($dirProd)){
						//アップロードファイル取得
						$filelist=scandir($dirProd);
						foreach($filelist as $file):
							if(!is_dir($file)):
					?>
						<tr>
							<td>
							
								<input type="checkbox" name="deletefile3[]" value="<?php echo $file; ?>" />
							</td>
							<td>
								<?php 
									echo "<INPUT type='button' value='ダウンロード' style='background-color : #fdc257;' onClick=\"fStartDownload('".($dirProd.$file)."');\">";
								?>
							</td>
						  <td>
						  <?php echo $file; ?>
						  </td>
						  
						</tr>
						<?php endif; endforeach; ?>
					<?php } ?>
					
					</tbody>
				</table>
				<p>
				<input type="button" value="削　除" onClick="fncFileUpload('<?php echo($mode); ?>','削除',0,2,1,'prodDoc')"/>
				</p>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone0" height="46" >顧客指定回答日</TD>
			<TD class="tdnone3" height="46"  >
				<INPUT size="7" type="text" id="txtCust_Ap_Ans_YMD" name="txtCust_Ap_Ans_YMD" maxlength="10" style="ime-mode: disabled;" tabindex="160" value="<?php echo $txtCust_Ap_Ans_YMD; ?>">
					<script type="text/javascript">
						//Ajaxカレンダ読込
						InputCalendar.createOnLoaded("txtCust_Ap_Ans_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
					</script>
				<input type="checkbox" name="chkCustAns" value="1" <?php echo $strCustAnsCheck; ?> >回答不要
			</TD>
			<TD class="tdnone0" height="46" >回答日</TD>
			<TD colspan="5" class="tdnone3" height="46" colspan="4">
				<INPUT size="7" type="text" id="txtAns_YMD" name="txtAns_YMD" maxlength="10" style="ime-mode: disabled;" tabindex="170" value="<?php echo $txtAns_YMD; ?>">
				<script type="text/javascript">
					//Ajaxカレンダ読込
					InputCalendar.createOnLoaded("txtAns_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
				</script>
		  </TD>
		</TR>
		<TR style="border:1px solid">
			<TD class="tdnone0">提出要求書類<br>(複数選択可)</TD>
			<TD colspan="7" class="tdnone3"  >
				<input type="checkbox" name="chkAnsDoc1" value="1" <?php echo $chkAnsDoc1;?> >ICPデータ　
				<input type="checkbox" name="chkAnsDoc2" value="1" <?php echo $chkAnsDoc2;?> >(M)SDS
				<input type="checkbox" name="chkAnsDoc3" value="1" <?php echo $chkAnsDoc3;?> >MILシート
				<input type="checkbox" name="chkAnsDoc4" value="1" <?php echo $chkAnsDoc4;?> >ChemSHERPAデータ
				<input type="checkbox" name="chkAnsDoc5" value="1" <?php echo $chkAnsDoc5;?> >MSDSplueS
				<input type="checkbox" name="chkAnsDoc6" value="1" <?php echo $chkAnsDoc6;?> >AIS
				<input type="checkbox" name="chkAnsDoc7" value="1" <?php echo $chkAnsDoc7;?> >IMDS
				<input type="checkbox" name="chkAnsDoc8" value="1" <?php echo $chkAnsDoc8;?> >EICC
				<input type="checkbox" name="chkAnsDoc9" value="1" <?php echo $chkAnsDoc9;?> >受領書
				<br>
				<input type="checkbox" name="chkAnsDoc15" value="1"  <?php echo $chkAnsDoc15;?> >その他
				<INPUT size="120" type="text" id="txtAnsDocEtc" name="txtAnsDocEtc" maxlength="50" tabindex="55" value="<?php echo $txtAnsDocEtc; ?>">
				　
		  </TD>
		</TR>
		
		<TR style="border:1px solid">
			<TD class="tdnone0" >回答書類<br>(1ファイル10MBまでアップロード可能)</TD>
			<TD class="tdnone3" colspan="7" width="200">
				<input type="file" name="ansDoc" id="ansDoc" size="100" />
				<input type="button" value="アップロード" onClick="fncFileUpload('<?php echo($mode); ?>','アップロード',0,1,0,'ansDoc')"/>
				<table class="type08" width="100%">
					<thead>
						<tr>
							<th scope="cols" width="10%">選択</th>
							<th scope="cols" width="10%">リンク</th>
							<th scope="cols" width="80%">ファイル名</th>
						  
						</tr>　　
					  </thead>
					<tbody>
					<?php
					//ディレクトリ存在チェック
					if(file_exists($dirAns)){
						//アップロードファイル取得
						$filelist=scandir($dirAns);
						foreach($filelist as $file):
							if(!is_dir($file)):
					?>
						<tr>
							<td>
							
								<input type="checkbox" name="deletefile2[]" value="<?php echo $file; ?>" />
							</td>
							<td>
								<?php 
									echo "<INPUT type='button' value='ダウンロード' style='background-color : #fdc257;' onClick=\"fStartDownload('".($dirAns.$file)."');\">";
								?>
							</td>
						  <td>
						  <?php echo $file; ?>
						  </td>
						  
						</tr>
						<?php endif; endforeach; ?>
					<?php } ?>
					
					</tbody>
				</table>
				<p>
				<input type="button" value="削　除" onClick="fncFileUpload('<?php echo($mode); ?>','削除',0,1,1,'ansDoc')"/>
				</p>
			</TD>
		</TR>
		
		
		<TR>
			<TD class="tdnone0" height="46" >
				<A href="JavaScript:fOpenSearch('F_MSK0030','txtAns_Tanto_CD','txtAns_Tanto_NM','','','','','','0')" >回答者CD</A>
			</TD>
			<TD colspan="1" class="tdnone3" height="46" >
				<INPUT size="5" type="text" id="txtAns_Tanto_CD" name="txtAns_Tanto_CD" tabindex="180" maxlength="5" style="ime-mode: disabled;" value="<?php echo $txtAns_Tanto_CD; ?>" onBlur="">
			</TD>
			<TD class="tdnone9" height="46" >回答者名</TD>
			<TD colspan="5" class="tdnone3" height="46" >
				<INPUT size="12" type="text" class="textboxdisp" readonly id="txtAns_Tanto_NM" name="txtAns_Tanto_NM" style="ime-mode: disabled;" value="<?php echo $txtAns_Tanto_NM; ?>">
			</TD>
		</TR>
	  </TBODY>
	</TABLE>
	<br>

	<div >

	<TABLE border="0">
	  <TBODY>
		<TR>
		  <TD class="tdnone" width="800" >
			<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
				<FONT color="#ffffff"><B>メーカー調査依頼情報</B></FONT>
			</DIV>
		  </TD>
		</TR>
	  </TBODY>
	</TABLE>
	<TABLE class="tbline"  width="1000"  >
		<TR style="border:1px solid">
			<TD class="tdnone1" height="46" width="100">メーカー調査</TD>
			<TD  class="tdnone3"　height="46" width="120">
				<SELECT name="cmbMakerSurvey_KBN" id="cmbMakerSurvey_KBN" tabindex="150" >
					<OPTION selected value="-1" >▼選択して下さい</OPTION>
					<?php $module_sel->fMakeCombo('C32',$cmbMakerSurvey_KBN); ?>
				</SELECT>
			</TD>
			<TD class="tdnone0" height="46" width="130"　nowrap>品証指定回答日</TD>
			<TD class="tdnone3" height="46" width="650">
				<INPUT size="7" type="text" id="txtPc_Ap_Ans_YMD" name="txtPc_Ap_Ans_YMD" maxlength="10" tabindex="250" style="ime-mode: disabled;" value="<?php echo $txtPc_Ap_Ans_YMD; ?>">
				<script type="text/javascript">
					//Ajaxカレンダ読込
					InputCalendar.createOnLoaded("txtPc_Ap_Ans_YMD", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
				</script>
			</TD>
		</TR>
		<TR style="border:1px solid">
			<TD class="tdnone2" >メーカー調査<br>添付ファイル</TD>
			<TD class="tdnone3" colspan="3" width="200">
				<input type="file" name="makerDoc" id="makerDoc" size="100" />
				<input type="button" value="アップロード" onClick="fncFileUpload('<?php echo($mode); ?>','アップロード',0,3,0,'makerDoc')"/>
				<table class="type08" width="100%">
					<thead>
						<tr>
							<th scope="cols" width="10%">選択</th>
							<th scope="cols" width="10%">リンク</th>
							<th scope="cols" width="80%">ファイル名</th>
						  
						</tr>　　
					  </thead>
					<tbody>
					<?php
					//ディレクトリ存在チェック
					if(file_exists($dirMaker)){
						//アップロードファイル取得
						$filelist=scandir($dirMaker);
						foreach($filelist as $file):
							if(!is_dir($file)):
					?>
						<tr>
							<td>
							
								<input type="checkbox" name="deletefile4[]" value="<?php echo $file; ?>" />
							</td>
							<td>
								<?php 
									echo "<INPUT type='button' value='ダウンロード' style='background-color : #fdc257;' onClick=\"fStartDownload('".($dirMaker.$file)."');\">";
								?>
							</td>
						  <td>
						  <?php echo $file; ?>
						  </td>
						  
						</tr>
						<?php endif; endforeach; ?>
					<?php } ?>
					
					</tbody>
				</table>
				<p>
				<input type="button" value="削　除" onClick="fncFileUpload('<?php echo($mode); ?>','削除',0,3,1,'makerDoc')"/>
				</p>
			</TD>
		</TR>
	</TABLE><br>
	<TABLE class="tbline" width="1000" id="Table2">
		<TBODY>
		<?php
		$i = 0;
		//件数分ループ
		while($i < $hidDCount){
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
				<TD class="tdnone2" width="50" align="center" nowrap>NO</TD>
				<TD class="tdnone1" width="100" align="center" nowrap>依頼先CD</TD>
				<TD class="tdnone2" width="250" align="center" nowrap>依頼先名</TD>
				<TD class="tdnone2" width="75" align="center" nowrap>回答受領日</TD>
				<TD class="tdnone2" width="400" align="center" nowrap>備考</TD>
				<TD class="tdnone5" width="50" align="center" nowrap>ｱｸｼｮﾝ</TD>
			</TR>
		<?php
			}
		?>
			<TR class="<?php echo $strClass; ?>" align="center" >
				<TD class="<?php echo $strClass; ?>">
					<?php echo $i+1; ?>
				</TD>
				<TD class="<?php echo $strClass; ?>"　>
					<INPUT size="4" type="text" class="<?php echo $strClass2; ?>" name="txtToriCd[]" id="txtToriCd[]" maxlength=5 value="<?php echo $aParaD[$i][0]; ?>" style="ime-mode:disabled;width:50px;height:20px;">
					<INPUT type="button" name="btnIraiC" id="btnIraiC" value="参照" onClick="fOpenSearchArray('F_MSK0020','txtToriCd[]','txtToriNm[]','','0','3',this)">
				</TD>
				<TD class="<?php echo $strClass; ?>"  >
					<INPUT size="30" type="text"  class="textboxdisp" name="txtToriNm[]" id="txtToriNm[]" readOnly value="<?php echo $aParaD[$i][1]; ?>" style="width:270px;height:20px;">
				</TD>

				<TD class="<?php echo $strClass; ?>"  >
					<INPUT size="7" type="text" class="<?php echo $strClass; ?>" name="txtAnsReceiptYmd[]" id="txtAnsReceiptYmd[]"  maxlength="10" value="<?php echo $module_cmn->fChangDateFormat($aParaD[$i][2]); ?>" style="ime-mode:disabled;width:70px;height:20px;" >
					
				</TD>
				<TD class="<?php echo $strClass; ?>" align="center" >
					<textarea cols="50" rows="4" class="<?php echo $strClass2; ?>" name="txtComment[]" id="txtComment[]" style="width:400px;height:70px;"><?php echo $aParaD[$i][3]; ?></textarea>
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
			

		?>
			<TR class="tdnone3" >
			  <TD class="tdnone2" width="50" align="center" nowrap>NO</TD>
				<TD class="tdnone1" width="100" align="center" nowrap>依頼先CD</TD>
				<TD class="tdnone2" width="250" align="center" nowrap>依頼先名</TD>
				<TD class="tdnone2" width="75" align="center" nowrap>回答受領日</TD>
				<TD class="tdnone2" width="400" align="center" nowrap>備考</TD>
				<TD class="tdnone5" width="50" align="center" nowrap>ｱｸｼｮﾝ</TD>
			</TR>
			<TR class="<?php echo $strClass; ?>" >
				<TD class="<?php echo $strClass; ?>" height="60">
					<?php echo 1; ?>
				</TD>
				<TD class="<?php echo $strClass; ?>" >
					<INPUT size="4" type="text" class="<?php echo $strClass2; ?>" name="txtToriCd[]" id="txtToriCd[]" maxlength=5 value="<?php echo $aParaD[$i][0]; ?>" style="ime-mode:disabled;width:50px;height:25px;" readOnly>
					<INPUT type="button" name="btnToriC" id="btnToriC" value="参照" onClick="fOpenSearchArray('F_MSK0020','txtToriCd[]','txtToriNm[]','','0','3',this)">
				</TD>
				<TD class="<?php echo $strClass; ?>"  >
					<INPUT size="30" type="text"  class="textboxdisp" name="txtToriNm[]" id="txtToriNm[]" readOnly value="<?php echo $aParaD[$i][1]; ?>" style="width:270px;height:20px;">
				</TD>

				<TD class="<?php echo $strClass; ?>"  >
					<INPUT size="7" type="text" class="<?php echo $strClass; ?>" name="txtAnsReceiptYmd[]" id="txtAnsReceiptYmd[]"  maxlength="10" value="<?php echo $module_cmn->fChangDateFormat($aParaD[$i][2]); ?>" style="ime-mode:disabled;width:70px;height:20px;">
					
				</TD>
				<TD class="<?php echo $strClass; ?>" align="center" >
					<textarea cols="50" rows="4" class="<?php echo $strClass2; ?>" name="txtComment[]" id="txtComment[]" style="width:400px;height:70px;"><?php echo $aParaD[$i][3]; ?></textarea>
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
		<INPUT type="button" name="btnExcute" value="　確　定　" tabindex="410" onClick="fncExcute('<?php echo($mode); ?>','<?php echo($modeN); ?>',0)" tabindex="2400">
		<INPUT type="reset" name="btnReset" value="　リセット　">
		<input type="hidden" name="hidExcute" value="0">
		<input type="hidden" name="hidUp" value="0">
		<input type="hidden" name="hidTantoCd" value="<?php echo $_SESSION['login'][0];?>">
		<input type="hidden" name="hidUCount" value="<?php echo $hidUCount; ?>">
		<input type="hidden" name="hidDCount" value="<?php echo $hidDCount; ?>">
		<input type="hidden" name="hidTempFolder" value="<?php echo $hidTempFolder; ?>">
		<input type="hidden" name="hidDenpyoKbn" value="0">
		<input type="hidden" name="hidTemp" value="">
		<input type="hidden" name="hidAction" value="">
		<input type="hidden" name="hidFrame" value="<?php echo $hidFrame; ?>">
		<br/><br/>

	<?php
	}
	?>

	</div>

<?php
//参照以外かつボタン表示フラグがTrueならは確定ボタン表示
if($mode <> "4" && $bDispflg){
	//品証のユーザのみ表示
	if(substr($_SESSION['login'][2],0,3) == "117"){
?>


<?php
	}
	//削除、否認以外の場合表示
	if($mode <> "3" && $mode <> "5"){
?>



<?php
	}
}	
?>

	</P>
	</FORM>
	</BODY>
</HTML>