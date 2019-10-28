<?php
	//****************************************************************************
	//プログラム名：赤伝緑伝情報一覧照会
	//プログラムID：F_FLK0090
	//作成者　　　：㈱鈴木　藤田
	//作成日　　　：2019/04/01
	//履歴
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
	require_once 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
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
		$sTargetSectionKbn = $module_cmn->fEscape($_POST['sTargetSectionKbn']);		//対象部門
		$sPgrsStage = $_POST['sPgrsStage'];											//進捗状態
		$sRrceNo = $_POST['sRrceNo'];												//伝票NO
		$sBusyoNm = $module_cmn->fEscape($_POST['sBusyoNm']);						//起因部署
		$sProdCd = $module_cmn->fEscape($_POST['sProdCd']);							//製品CD
		$sDrwNo = $module_cmn->fEscape($_POST['sDrwNo']);							//仕様番号
		$sProdNm = $module_cmn->fEscape($_POST['sProdNm']);							//製品名
		$sCustNm = $module_cmn->fEscape($_POST['sCustNm']);							//得意先名
		$sFlawKbn = $module_cmn->fEscape($_POST['sFlawKbn']);						//不具合区分
		$sIncidentF = $module_cmn->fEscape($_POST['sIncidentF']);					//伝票発行日（開始）
		$sIncidentT = $module_cmn->fEscape($_POST['sIncidentT']);					//伝票発行日（終了）
		$sDisposalFlg = $module_cmn->fEscape($_POST['sDisposalFlg']);				//廃棄数量／金額有のみチェック
		//チェック記述に変換
		if($sDisposalFlg == "1"){
			$sDisposalCheck = "checked";
		}else{
			$sDisposalCheck = "";
		}
		$sProcessPeriodF = $module_cmn->fEscape($_POST['sProcessPeriodF']);			//処理期限（開始）
		$sProcessPeriodT = $module_cmn->fEscape($_POST['sProcessPeriodT']);			//処理期限（終了）
		$sDecisionM = $module_cmn->fEscape($_POST['sDecisionM']);					//処理判定月（3ヶ月分）
		$sReports = $module_cmn->fEscape($_POST['sReports']);						//報告書
		$sIncidentKbn = $module_cmn->fEscape($_POST['sIncidentKbn']);				//発行先区分
		$sFlawLotNo = $module_cmn->fEscape($_POST['sFlawLotNo']);					//不具合ロットNO		// 2019/09/20 ADD END
	//赤伝緑伝情報入力画面からの遷移
	}elseif($action == "main"){
		$sTargetSectionKbn = $module_cmn->fEscape($aJoken[0]);
		$sPgrsStage = $module_cmn->fEscape($aJoken[1]);
		$sRrceNo = $module_cmn->fEscape($aJoken[2]);
		$sBusyoNm = $module_cmn->fEscape($aJoken[3]);
		$sProdCd = $module_cmn->fEscape($aJoken[4]);
		$sDrwNo = $module_cmn->fEscape($aJoken[5]);
		$sProdNm = $module_cmn->fEscape($aJoken[6]);
		$sCustNm = $module_cmn->fEscape($aJoken[7]);
		$sFlawKbn = $module_cmn->fEscape($aJoken[8]);
		$sIncidentF = $module_cmn->fEscape($aJoken[9]);
		$sIncidentT = $module_cmn->fEscape($aJoken[10]);
		$sProcessPeriodF = $module_cmn->fEscape($aJoken[11]);
		$sProcessPeriodT = $module_cmn->fEscape($aJoken[12]);
		$sDisposalFlg = $module_cmn->fEscape($aJoken[13]);
		//チェック記述に変換
		if($sDisposalFlg == "1"){
			$sDisposalCheck = "checked";
		}else{
			$sDisposalCheck = "";
		}
		$sDecisionM = $module_cmn->fEscape($aJoken[14]);
		$sReports = $module_cmn->fEscape($aJoken[15]);
		$sIncidentKbn = $module_cmn->fEscape($aJoken[16]);
		$sFlawLotNo = $module_cmn->fEscape($aJoken[17]);		// 2019/09/20 ADD END
	}
	//検索条件格納用配列
	$aJoken = array();
	$aJoken[0] = $sTargetSectionKbn;
	$aJoken[1] = $sPgrsStage;
	$aJoken[2] = $sRrceNo;
	$aJoken[3] = $sBusyoNm;
	$aJoken[4] = $sProdCd;
	$aJoken[5] = $sDrwNo;
	$aJoken[6] = $sProdNm;
	$aJoken[7] = $sCustNm;
	$aJoken[8] = $sFlawKbn;
	$aJoken[9] = $sIncidentF;
	$aJoken[10] = $sIncidentT;
	$aJoken[11] = $sProcessPeriodF;
	$aJoken[12] = $sProcessPeriodT;
	$aJoken[13] = $sDisposalFlg;
	$aJoken[14] = $sDecisionM;
	$aJoken[15] = $sReports;
	$aJoken[16] = $sIncidentKbn;
	$aJoken[17] = $sFlawLotNo;					//不具合ロットNO		// 2019/09/20 ADD END

	//赤伝緑伝管理状況の検索
	$aRes = array();
	$aResT = $module_sel->fTrblStatsSearch();
	$sLastM = date('Y年n月度', strtotime(date('Y-m-1') . '-1 month'));
	$sThisM = date('Y年n月度');
	$sTdayM = date("Y年n月j日");
	
	$aPara = array();
	
	//検索処理(件数取得)
	if(isset($_GET['search'])){
		//検索条件取得
		if($_GET['search'] == "1"){
			//$aPara = array();
			$strErrMsg = "";

			//整合性チェック
			if($strErrMsg == ""){
				//伝票発行日の開始と終了の両方指定されている場合、終了日が開始日より前日の場合エラー
				if($_POST['sIncidentF'] <> "" && $_POST['sIncidentT'] <> ""){
					if(str_replace("/","",$_POST['sIncidentT']) < str_replace("/","",$_POST['sIncidentF'])){
						$strErrMsg = $strErrMsg."伝票発行日の終了日が開始日より後の日付が指定されています<BR>";
					}
				}
				//処理期限の開始と終了の両方指定されている場合、終了日が開始日より前日の場合エラー
				if($_POST['sProcessPeriodF'] <> "" && $_POST['sProcessPeriodT'] <> ""){
					if(str_replace("/","",$_POST['sProcessPeriodT']) < str_replace("/","",$_POST['sProcessPeriodF'])){
						$strErrMsg = $strErrMsg."処理期限の終了日が開始日より後の日付が指定されています<BR>";
					}
				}
			}
			if($strErrMsg == ""){
				//赤伝緑伝情報データ検索処理
				$aPara = $module_sel->fTrblSearch($aJoken);

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
	}

	//各種関連書類のExcel出力
	if(isset($_GET['excel'])){
		if($_GET['excel'] == "1"){
			$aRec = array();
			//出力するExcelファイルへ書き出すデータを取得
			$aRec = $module_sel->fGetTrblData($_GET['no'],$_GET['seq']);

			$txtReference_NO = $aRec[0]; 							//発行NO（伝票NO）
			$txtProd_CD = $aRec[16]; 								//ﾕｰｻﾞｰNO
			$txtIncident_NM = $aRec[63]; 							//報告書発行先部署・協力会社名
			$txtTmp = "";
			if($aRec[11] <> ""){
				$txtTmp = trim($aRec[11]);
			}
			if($aRec[12] <> ""){
				if($txtTmp <> ""){
					$txtTmp = $txtTmp.",".trim($aRec[12]);
				}else{
					$txtTmp = trim($aRec[12]);
				}
			}
			if($aRec[13] <> ""){
				if($txtTmp <> ""){
					$txtTmp = $txtTmp.",".trim($aRec[13]);
				}else{
					$txtTmp = trim($aRec[13]);
				}
			}
			$txtTanto_NM = $txtTmp;									//担当者
			$txtGrp_NM = $aRec[9]; 									//担当課
			$txtProd_NM = $aRec[18]; 								//品名
			$txtFlawLot_NO = $aRec[20];								//ﾛｯﾄNO
			$txtTmp = "";
			if($aRec[28] <> "-1"){
				$txtTmp = $module_sel->fDispKbnS2('085', trim($aRec[28]));
			}
			if($aRec[29] <> "-1"){
				if($txtTmp <> ""){
					$txtTmp = $txtTmp.",".$module_sel->fDispKbnS2('085', trim($aRec[29]));
				}else{
					$txtTmp = $module_sel->fDispKbnS2('085', trim($aRec[29]));
				}
			}
			if($aRec[30] <> "-1"){
				if($txtTmp <> ""){
					$txtTmp = $txtTmp.",".$module_sel->fDispKbnS2('085', trim($aRec[30]));
				}else{
					$txtTmp = $module_sel->fDispKbnS2('085', trim($aRec[30]));
				}
			}
			$txtFlaw_KBN = $txtTmp;										//不良項目
			if($aRec[21] <> ""){
				$txtFlawLot_QTY = number_format($aRec[21]); 			//数量
			}else{
				$txtFlawLot_QTY = "";
			}
			if($aRec[23] <> ""){
				$txtFlawPrice = number_format($aRec[23]); 				//金額
			}else{
				$txtFlawPrice = "";
			}
			$txtFlawContents = $aRec[31]; 								//不良内容
			$txtDrw_NO = $aRec[19]; 									//図番（仕様番号）
			$txtIncident = $module_cmn->fChangDateFormat4($aRec[7]);	//伝票発行日
			$txtDie_NO = $aRec[17]; 									//金型番号
			$cmbKBN = $aRec[26]; 										//区分
			
			$txtProcessLimit_YMD = $aRec[39];

			//入出力ファイルの切替
			if(isset($_GET['class'])){
				if($_GET['class'] == "1"){
					if($cmbKBN <> 1){
						$strImportFile = mb_convert_encoding("品質改善報告書_雛型_01.xlsx","SJIS","UTF-8");
					}else{
						//区分が「工程内」の場合　流出原因斜線のテンプレート使用
						$strImportFile = mb_convert_encoding("品質改善報告書_雛型_02.xlsx","SJIS","UTF-8");
					}
					$strExportFile = mb_convert_encoding("品質改善報告書.xlsx","SJIS","UTF-8");
				}elseif($_GET['class'] == "2"){
					$strImportFile = mb_convert_encoding("不良品連絡書_雛型.xlsx","SJIS","UTF-8");
					$strExportFile = mb_convert_encoding("不良品連絡書.xlsx","SJIS","UTF-8");
				}elseif($_GET['class'] == "3"){
					$strImportFile = mb_convert_encoding("特別作業記録_雛型.xlsx","SJIS","UTF-8");
					$strExportFile = mb_convert_encoding("特別作業記録.xlsx","SJIS","UTF-8");
				}
			}
			
			//ブラウザへ出力をリダイレクト
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			$reader->setIncludeCharts(true);
			$spreadsheet = $reader->load("template/".$strImportFile);
			
			//ブラウザへ出力をリダイレクト
			header("Content-Description: File Transfer");
			header('Content-Disposition: attachment; filename='.$strExportFile);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			$reader->setIncludeCharts(true);
			$spreadsheet = $reader->load("template/".$strImportFile);
			
			//シートの設定
			$spreadsheet->setActiveSheetIndex(0);
			$sheet = $spreadsheet->getActiveSheet();
			
			//出力用バッファをクリア(消去)し、出力のバッファリングをオフにする
			ob_end_clean();
			//出力のバッファリングを有効にする
			ob_start();

			//品質改善報告書
			if($_GET['class'] == "1"){
				//セルの値を設定
				$sheet->setCellValue('K2', date("Y年m月d日"));													//発行日
				$sheet->setCellValue('D2', $txtReference_NO);													//整理NO
				$sheet->setCellValue('D3', $txtProd_CD);														//ﾕｰｻﾞｰNO
				//2019/08/01 ED START
				//めっきの場合は担当課→「めっき課」、担当者→「-」
				if($aPara[$i][51] == 'K05001'){
					$sheet->setCellValue('K3', "-");
					$sheet->setCellValue('K4', "めっき課");
				}else{
					$sheet->setCellValue('K3', $txtTanto_NM);														//担当者
					$sheet->setCellValue('K4', $txtGrp_NM);															//担当課
				}
				//2019/08/01 ED END
				if($cmbKBN <> 1){
					// 2019/05/13 ED START
					//$sheet->setCellValue('H4', $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(7)));	//発生原因
					//$sheet->setCellValue('H5', $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(14)));//流出原因
					if($txtProcessLimit_YMD <> 0){
						$sheet->setCellValue('H4', $module_cmn->fChangDateFormat4($module_sel->fGetCalendar($txtProcessLimit_YMD,-5)));		//発生原因
						$sheet->setCellValue('H5', $module_cmn->fChangDateFormat4($txtProcessLimit_YMD));									//流出原因
					}
					// 2019/05/13 ED END
				}else{
					//区分が「工程内」の場合　流出原因斜線のテンプレート使用
					// 2019/05/13 ED START
					//$sheet->setCellValue('H4', $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(14)));//発生原因
					if($txtProcessLimit_YMD <> 0){
						$sheet->setCellValue('H4', $module_cmn->fChangDateFormat4($txtProcessLimit_YMD));			//発生原因
					}
					// 2019/05/13 ED END
				}
				$sheet->setCellValue('D6', $txtDrw_NO);															//図番（仕様番号）
				$sheet->setCellValue('D7', $txtProd_NM);														//品名
				$sheet->setCellValue('D8', $txtFlawLot_NO);														//ﾛｯﾄNO
				$sheet->setCellValue('D9', $txtFlaw_KBN);														//不良項目
				$sheet->setCellValue('H8', $txtFlawLot_QTY);													//数量
				$sheet->setCellValue('H9', $txtFlawPrice);														//金額
				$sheet->setCellValue('B11', $txtFlawContents);													//不良内容
				$sheet->setCellValue('D24', $txtDie_NO);														//金型番号
			}
			//協力工場不良品連絡書
			elseif($_GET['class'] == "2"){
				$txtFlawLot_QTY = $txtFlawLot_QTY."個";
				//セルの値を設定
				$sheet->setCellValue('L2', date("Y年m月d日")); 													//発行日
				$sheet->setCellValue('L3', $txtReference_NO); 													//整理NO
				$sheet->setCellValue('L4', $txtProd_CD); 														//製品CD
				$sheet->setCellValue('C3', $txtIncident_NM);													//報告書発行先部署・協力会社名
				// 2019/05/13 ED START
				//$sheet->setCellValue('F10', $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(14)));	//指定回答日
				if($txtProcessLimit_YMD <> 0){
					$sheet->setCellValue('F10', $module_cmn->fChangDateFormat4($txtProcessLimit_YMD));				//指定回答日
				}
				// 2019/05/13 ED END
				$sheet->setCellValue('D12', $txtDrw_NO);														//仕様番号
				$sheet->setCellValue('D13', $txtProd_NM);														//品名
				$sheet->setCellValue('D14', $txtDie_NO);														//金型番号
				$sheet->setCellValue('D15', $txtFlawLot_NO); 													//ロットNo
				$sheet->setCellValue('D17', $txtIncident);														//連絡受理日（伝票発行日）
				$sheet->setCellValue('D18', $txtFlawLot_QTY);						 							//不具合数量
				$sheet->setCellValue('H13', $txtFlawContents);						 							//不具合内容
			}
			//特別作業記録
			elseif($_GET['class'] == "3"){
				$sheet->setCellValue('J2', date("Y年m月d日")); 													//発行日
				$sheet->setCellValue('J3', $txtReference_NO); 													//整理NO
				$sheet->setCellValue('C8', $txtDrw_NO);															//仕様番号
				$sheet->setCellValue('C9', $txtProd_NM);														//製品名
				$sheet->setCellValue('C10', $txtFlawLot_QTY."個");												//数量
				$sheet->setCellValue('C11', $txtFlawLot_NO); 													//ロットNo
				$sheet->setCellValue('B15', $txtFlawContents);						 							//不具合内容
				$sheet->setCellValue('I13', $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(7)));	//実施期限
			}
			
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet,"Xlsx");
			//$writer->setOffice2003Compatibility(true);
			$writer->setIncludeCharts(true);
			$writer->save('php://output');
			exit;
		}
	}

	//台帳出力
	if(isset($_GET['output'])){
		$output = $_GET['output'];
		if($_GET['output'] == "1"){
			//列数
			$iCol = 56;
			
			$hitflag = false;
			$iCnt = 0;
			$iStartPosCol = 0;
			$iStartPosRow = 6;
			
			//出力するExcelファイルへ書き出すデータを取得
			$aPara = $module_sel->fTrblSearch($aJoken,"1");
			$iRows = count($aPara);
			if($iRows <> 0){
				
				//入出力ファイルの切替
				$strImportFile = mb_convert_encoding("赤伝緑伝管理台帳_雛型.xlsx","SJIS","UTF-8");
				$strExportFile = mb_convert_encoding("赤伝緑伝管理台帳.xlsx","SJIS","UTF-8");
				
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
				$reader->setIncludeCharts(true);
				$spreadsheet = $reader->load("template/".$strImportFile);
				
				//ブラウザへ出力をリダイレクト
				header("Content-Description: File Transfer");
				header('Content-Disposition: attachment; filename='.$strExportFile);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Transfer-Encoding: binary');
				header('Cache-Control: max-age=0');
				header('Expires: 0');
				
				//シートの設定
				$spreadsheet->setActiveSheetIndex(0);
				$sheet = $spreadsheet->getActiveSheet();
				
				//出力用バッファをクリア(消去)し、出力のバッファリングをオフにする
				ob_end_clean();
				//出力のバッファリングを有効にする
				ob_start();
				
				//行挿入
				$sheet->insertNewRowBefore($iStartPosRow+1,$iRows);
				//1行削除
				$sheet->removeRow($iStartPosRow,2);
				
				//$sPointRefNo = "";
				for($i = 0; $i < count($aPara); $i++) {
					// データの取得
					$rec_C_REFERENCE_NO = $aPara[$i][0];
					$rec_N_REFERENCE_SEQ = $aPara[$i][1];
					if($aPara[$i][48]<>""){
						$rec_C_POINTREF_NO = $aPara[$i][48];
					}else{
						$rec_C_POINTREF_NO = "-";
					}
					$rec_C_REFERENCE_KBN = $aPara[$i][2];
					if($aPara[$i][3] == "0"){
						$rec_C_PROGRES_STAGE = "3";
					}else{
						$rec_C_PROGRES_STAGE = $aPara[$i][3];
					}
					if($aPara[$i][4]<>"0"){
						$rec_N_INCIDENT_YMD = substr($aPara[$i][4],0,4)."/".substr($aPara[$i][4],4,2)."/".substr($aPara[$i][4],6,2);
					}else{
						$rec_N_INCIDENT_YMD = "-";
					}
					if($aPara[$i][5]<>""){
						$rec_V2_CUST_NM = $aPara[$i][5];
					}else{
						$rec_V2_CUST_NM = "-";
					}
					if($aPara[$i][6]<>""){
						$rec_C_PROD_CD = $aPara[$i][6];
					}else{
						$rec_C_PROD_CD = "-";
					}
					if($aPara[$i][7]<>""){
						$rec_V2_DRW_NO = $aPara[$i][7];
					}else{
						$rec_V2_DRW_NO = "-";
					}
					if($aPara[$i][8]<>""){
						$rec_V2_PROD_NM = $aPara[$i][8];
					}else{
						$rec_V2_PROD_NM = "-";
					}
					if($aPara[$i][9]<>""){
						$rec_C_DIE_NO = $aPara[$i][9];
					}else{
						$rec_C_DIE_NO = "-";
					}
					if($aPara[$i][10]<>""){
						$rec_C_FLAW_LOT_NO = $aPara[$i][10];
					}else{
						$rec_C_FLAW_LOT_NO = "-";
					}
					if(trim($aPara[$i][11])<>""){
						$rec_C_FLAW_KBN1 = $aPara[$i][11];
					}else{
						$rec_C_FLAW_KBN1 = "-";
					}
					if(trim($aPara[$i][12])<>""){
						$rec_C_FLAW_KBN2 = $aPara[$i][12];
					}else{
						$rec_C_FLAW_KBN2 = "-";
					}
					if(trim($aPara[$i][13])<>""){
						$rec_C_FLAW_KBN3 = $aPara[$i][13];
					}else{
						$rec_C_FLAW_KBN3 = "-";
					}
					//不具合内容
					if(trim($aPara[$i][14])<>""){
						$rec_V2_FLAW_CONTENTS = $aPara[$i][14];
					}else{
						$rec_V2_FLAW_CONTENTS = "-";
					}
					if($aPara[$i][15]<>"-1"){
						$rec_C_KBN = $module_sel->fDispKbn('C34',$aPara[$i][15]);
					}else{
						$rec_C_KBN = "-";
					}
					$sTmpTanto = "";
					if($aPara[$i][16]<>""){
						$sTmpTanto = $aPara[$i][16];
					}
					if($aPara[$i][17]<>""){
						if($sTmpTanto<>""){
							$sTmpTanto = $sTmpTanto.",".$aPara[$i][17];
						}else{
							$sTmpTanto = $aPara[$i][17];
						}
					}
					if($aPara[$i][18]<>""){
						if($sTmpTanto<>""){
							$sTmpTanto = $sTmpTanto.",".$aPara[$i][18];
						}else{
							$sTmpTanto = $aPara[$i][18];
						}
					}
					if($sTmpTanto<>""){
						$rec_V2_PROD_TANTO_NM = $sTmpTanto;
					}else{
						$rec_V2_PROD_TANTO_NM = "-";
					}
					if($aPara[$i][19]<>""){
						$rec_V2_PROD_GRP_NM = $aPara[$i][19];
					}else{
						$rec_V2_PROD_GRP_NM = "-";
					}
					$rec_N_FLAW_LOT_QTY = $aPara[$i][20];
					$rec_N_UNIT_PRICE = $aPara[$i][21];
					$rec_N_FLAW_PRICE = $aPara[$i][22];
					if($aPara[$i][25]<>"0"){
						$rec_N_PROCESS_PERIOD_YMD = substr($aPara[$i][25],4,2)."/".substr($aPara[$i][25],6,2);
					}else{
						$rec_N_PROCESS_PERIOD_YMD = "-";
					}
					if($aPara[$i][26]<>""){
						$rec_C_HISYO_TANTO_NM = $aPara[$i][26];
					}else{
						$rec_C_HISYO_TANTO_NM = "-";
					}
					if($aPara[$i][27]<>"-1"){
						$rec_C_PROCESS = $module_sel->fDispKbn('C37',$aPara[$i][27]);
					}else{
						$rec_C_PROCESS = "保留";
					}
					// 2019/09/20 ADD START
					if($aPara[$i][28]<>"0"){
						$rec_N_DECISION_YM =  substr($aPara[$i][28],0,4)."/".substr($aPara[$i][28],4,2);
					}else{
						$rec_N_DECISION_YM = "-";
					}
					// 2019/09/20 ADD END
					if($aPara[$i][28]<>"0"){
						$rec_N_DECISION_YMD =  substr($aPara[$i][28],0,4)."/".substr($aPara[$i][28],4,2)."/".substr($aPara[$i][28],6,2);
					}else{
						$rec_N_DECISION_YMD = "-";
					}
					if($aPara[$i][3] == "0"){
						$rec_N_HOLD_QTY = $aPara[$i][20];
					}else{
						$rec_N_HOLD_QTY = 0;
					}
/* 					if($aPara[$i][29]<>0){
						$rec_N_FAILURE_QTY = $aPara[$i][29];
					}else{
						$rec_N_FAILURE_QTY = "-";
					} */
					$rec_N_FAILURE_QTY = $aPara[$i][29];
					$rec_N_DISPOSAL_QTY = $aPara[$i][30];
					$rec_N_RETURN_QTY = $aPara[$i][31];
					$rec_N_LOSS_QTY = $aPara[$i][32];
					$rec_N_EXCLUD_QTY = $aPara[$i][33];
					if($aPara[$i][34]<>0){
						$rec_N_SELECTION = $aPara[$i][34];
					}else{
						$rec_N_SELECTION = "-";
					}
					$rec_N_DISPOSAL_PRICE = $aPara[$i][35];
					$rec_N_LOSS_PRICE = $aPara[$i][36];
					if($aPara[$i][37]<>""){
						$rec_V2_BUSYO_NM = $aPara[$i][37];
					}else{
						$rec_V2_BUSYO_NM = "-";
					}
					$rec_V2_INCIDENT_NM1 = "-";
					$rec_N_INS_YMD1 = "-";
					$rec_N_PROCESS_LIMIT_YMD1 = "-";
					$rec_N_RETURN_YMD1 = "-";
					$rec_N_COMP_YMD1 = "-";
					$rec_V2_INCIDENT_NM2 = "-";
					$rec_N_INS_YMD2 = "-";
					$rec_N_PROCESS_LIMIT_YMD2 = "-";
					$rec_N_RETURN_YMD2 = "-";
					$rec_N_COMP_YMD2 = "-";
					if($aPara[$i][2] == "1"){
						//赤伝
						if($aPara[$i][38]<>""){
							//社内（F,K）は品質改善報告書、社外（その他）は協力工場不良品連絡書
							if(substr($aPara[$i][51],0,1) == "F" or substr($aPara[$i][51],0,1) == "K"){
								$rec_V2_INCIDENT_NM1 = $aPara[$i][38];
								if($aPara[$i][49]<>"0"){
									$rec_N_INS_YMD1 = substr($aPara[$i][49],4,2)."/".substr($aPara[$i][49],6,2);
								}else{
									$rec_N_INS_YMD1 = "-";
								}
								if($aPara[$i][39]<>"0"){
									$rec_N_PROCESS_LIMIT_YMD1 = substr($aPara[$i][39],4,2)."/".substr($aPara[$i][39],6,2);
								}else{
									$rec_N_PROCESS_LIMIT_YMD1 = "-";
								}
								if($aPara[$i][40]<>"0"){
									$rec_N_RETURN_YMD1 = substr($aPara[$i][40],4,2)."/".substr($aPara[$i][40],6,2);
								}else{
									$rec_N_RETURN_YMD1 = "-";
								}
								if($aPara[$i][41]<>"0"){
									$rec_N_COMP_YMD1 = substr($aPara[$i][41],4,2)."/".substr($aPara[$i][41],6,2);
								}else{
									$rec_N_COMP_YMD1 = "-";
								}
							}else{
								$rec_V2_INCIDENT_NM2 = $aPara[$i][38];
								if($aPara[$i][49]<>"0"){
									$rec_N_INS_YMD2 = substr($aPara[$i][49],4,2)."/".substr($aPara[$i][49],6,2);
								}else{
									$rec_N_INS_YMD2 = "-";
								}
								if($aPara[$i][39]<>"0"){
									$rec_N_PROCESS_LIMIT_YMD2 = substr($aPara[$i][39],4,2)."/".substr($aPara[$i][39],6,2);
								}else{
									$rec_N_PROCESS_LIMIT_YMD2 = "-";
								}
								if($aPara[$i][40]<>"0"){
									$rec_N_RETURN_YMD2 = substr($aPara[$i][40],4,2)."/".substr($aPara[$i][40],6,2);
								}else{
									$rec_N_RETURN_YMD2 = "-";
								}
								if($aPara[$i][41]<>"0"){
									$rec_N_COMP_YMD2 = substr($aPara[$i][41],4,2)."/".substr($aPara[$i][41],6,2);
								}else{
									$rec_N_COMP_YMD2 = "-";
								}
							}
						}
					}
					//特別作業発行日（特別作業）
					if($aPara[$i][24]<>"0"){
						$rec_N_SPECIAL_SPECIAL_YMD = substr($aPara[$i][24],4,2)."/".substr($aPara[$i][24],6,2);
						//処理期限（特別作業）
						if($aPara[$i][25]<>"0"){
							$rec_N_PROCESS_PERIOD_SPECIAL_YMD = substr($aPara[$i][25],4,2)."/".substr($aPara[$i][25],6,2);
						}else{
							$rec_N_PROCESS_PERIOD_SPECIAL_YMD = "-";
						}
					}else{
						$rec_N_SPECIAL_SPECIAL_YMD = "-";
						$rec_N_PROCESS_PERIOD_SPECIAL_YMD = "-";
					}
					if($aPara[$i][42]<>"0"){
						$rec_N_SUBMIT_YMD1 = substr($aPara[$i][42],4,2)."/".substr($aPara[$i][42],6,2);
					}else{
						$rec_N_SUBMIT_YMD1 = "-";
					}
					if($aPara[$i][43]<>"0"){
						$rec_N_SUBMIT_YMD2 = substr($aPara[$i][43],4,2)."/".substr($aPara[$i][43],6,2);
					}else{
						$rec_N_SUBMIT_YMD2 = "-";
					}
					if($aPara[$i][44]<>"0"){
						$rec_N_SUBMIT_YMD3 = substr($aPara[$i][44],4,2)."/".substr($aPara[$i][44],6,2);
					}else{
						$rec_N_SUBMIT_YMD3 = "-";
					}
					if($aPara[$i][45]<>"0"){
						$rec_N_BACK_YMD1 = substr($aPara[$i][45],4,2)."/".substr($aPara[$i][45],6,2);
					}else{
						$rec_N_BACK_YMD1 = "-";
					}
					if($aPara[$i][46]<>"0"){
						$rec_N_BACK_YMD2 = substr($aPara[$i][46],4,2)."/".substr($aPara[$i][46],6,2);
					}else{
						$rec_N_BACK_YMD2 = "-";
					}
					if($aPara[$i][47]<>"0"){
						$rec_N_BACK_YMD3 = substr($aPara[$i][47],4,2)."/".substr($aPara[$i][47],6,2);
					}else{
						$rec_N_BACK_YMD3 = "-";
					}
					$rec_N_EXCLUDED = $aPara[$i][50];
					$rec_N_SPECIAL = $aPara[$i][52];

					//セルの記入
					$sheet->setCellValueByColumnAndRow(1,$iStartPosRow + $i,$i + 1);						//NO
					$sheet->setCellValueByColumnAndRow(2,$iStartPosRow + $i,$rec_C_REFERENCE_NO);			//伝票NO
					$sheet->setCellValueByColumnAndRow(3,$iStartPosRow + $i,$rec_N_REFERENCE_SEQ);			//伝票SEQ
					$sheet->setCellValueByColumnAndRow(4,$iStartPosRow + $i,$rec_C_POINTREF_NO);			//代表伝票NO
					$sheet->setCellValueByColumnAndRow(5,$iStartPosRow + $i,$rec_C_REFERENCE_KBN);			//伝票種別
					$sheet->setCellValueByColumnAndRow(6,$iStartPosRow + $i,$rec_C_PROGRES_STAGE);			//進捗状態
					$sheet->setCellValueByColumnAndRow(7,$iStartPosRow + $i,$rec_N_INCIDENT_YMD);			//伝票発行日
					$sheet->setCellValueByColumnAndRow(8,$iStartPosRow + $i,$rec_V2_CUST_NM);				//得意先名
					$sheet->setCellValueByColumnAndRow(9,$iStartPosRow + $i,$rec_C_PROD_CD);				//製品CD
					$sheet->setCellValueByColumnAndRow(10,$iStartPosRow + $i,$rec_V2_DRW_NO);				//仕様番号
					$sheet->setCellValueByColumnAndRow(11,$iStartPosRow + $i,$rec_V2_PROD_NM);				//製品名
					$sheet->setCellValueByColumnAndRow(12,$iStartPosRow + $i,$rec_C_DIE_NO);				//金型番号
					$sheet->setCellValueByColumnAndRow(13,$iStartPosRow + $i,$rec_C_FLAW_LOT_NO);			//不具合ロット番号
					$sheet->setCellValueByColumnAndRow(14,$iStartPosRow + $i,$rec_C_FLAW_KBN1);				//不具合区分1
					$sheet->setCellValueByColumnAndRow(15,$iStartPosRow + $i,$rec_C_FLAW_KBN2);				//不具合区分2
					$sheet->setCellValueByColumnAndRow(16,$iStartPosRow + $i,$rec_C_FLAW_KBN3);				//不具合区分3
					$sheet->setCellValueByColumnAndRow(17,$iStartPosRow + $i,$rec_V2_FLAW_CONTENTS);		//不具合内容
					$sheet->setCellValueByColumnAndRow(18,$iStartPosRow + $i,$rec_C_KBN);					//区分
					$sheet->setCellValueByColumnAndRow(19,$iStartPosRow + $i,$rec_V2_PROD_TANTO_NM);		//生産担当者
					$sheet->setCellValueByColumnAndRow(20,$iStartPosRow + $i,$rec_V2_PROD_GRP_NM);			//生産ｸﾞﾙｰﾌﾟ名
					$sheet->setCellValueByColumnAndRow(21,$iStartPosRow + $i,$rec_N_FLAW_LOT_QTY);			//不具合数量
					$sheet->setCellValueByColumnAndRow(22,$iStartPosRow + $i,$rec_N_UNIT_PRICE);			//単価
					$sheet->setCellValueByColumnAndRow(23,$iStartPosRow + $i,$rec_N_FLAW_PRICE);			//不具合金額
					$sheet->setCellValueByColumnAndRow(24,$iStartPosRow + $i,$rec_N_PROCESS_PERIOD_YMD);	//処理期限
					$sheet->setCellValueByColumnAndRow(25,$iStartPosRow + $i,$rec_C_HISYO_TANTO_NM);		//品証担当者
					$sheet->setCellValueByColumnAndRow(26,$iStartPosRow + $i,$rec_C_PROCESS);				//処理
					$sheet->setCellValueByColumnAndRow(27,$iStartPosRow + $i,$rec_N_DECISION_YM);			//処理判定月		// 2019/09/20 ADD
					$sheet->setCellValueByColumnAndRow(28,$iStartPosRow + $i,$rec_N_DECISION_YMD);			//処理判定日
					$sheet->setCellValueByColumnAndRow(29,$iStartPosRow + $i,$rec_N_HOLD_QTY);				//保留数量
					$sheet->setCellValueByColumnAndRow(30,$iStartPosRow + $i,$rec_N_FAILURE_QTY);			//納入数量
					$sheet->setCellValueByColumnAndRow(31,$iStartPosRow + $i,$rec_N_DISPOSAL_QTY);			//廃棄数量
					$sheet->setCellValueByColumnAndRow(32,$iStartPosRow + $i,$rec_N_RETURN_QTY);			//返却数量
					$sheet->setCellValueByColumnAndRow(33,$iStartPosRow + $i,$rec_N_LOSS_QTY);				//調整ロス数量
					$sheet->setCellValueByColumnAndRow(34,$iStartPosRow + $i,$rec_N_EXCLUD_QTY);			//対象外数量
					$sheet->setCellValueByColumnAndRow(35,$iStartPosRow + $i,$rec_N_SELECTION);				//選別工程
					$sheet->setCellValueByColumnAndRow(36,$iStartPosRow + $i,$rec_N_DISPOSAL_PRICE);		//廃棄金額
					$sheet->setCellValueByColumnAndRow(37,$iStartPosRow + $i,$rec_N_LOSS_PRICE);			//調整ロス金額
					$sheet->setCellValueByColumnAndRow(38,$iStartPosRow + $i,$rec_V2_BUSYO_NM);				//起因部署
					$sheet->setCellValueByColumnAndRow(39,$iStartPosRow + $i,$rec_V2_INCIDENT_NM1);			//発行先（品質改善報告書管理台帳）
					$sheet->setCellValueByColumnAndRow(40,$iStartPosRow + $i,$rec_N_INS_YMD1);				//登録／発行日（品質改善報告書管理台帳）
					$sheet->setCellValueByColumnAndRow(41,$iStartPosRow + $i,$rec_N_PROCESS_LIMIT_YMD1);	//指定回答日（品質改善報告書管理台帳）
					$sheet->setCellValueByColumnAndRow(42,$iStartPosRow + $i,$rec_N_RETURN_YMD1);			//返却日（品質改善報告書管理台帳）
					$sheet->setCellValueByColumnAndRow(43,$iStartPosRow + $i,$rec_N_COMP_YMD1);				//完結日（品質改善報告書管理台帳）
					$sheet->setCellValueByColumnAndRow(44,$iStartPosRow + $i,$rec_V2_INCIDENT_NM2);			//発行先（協力工場不良品連絡書管理台帳）
					$sheet->setCellValueByColumnAndRow(45,$iStartPosRow + $i,$rec_N_INS_YMD2);				//登録／発行日（協力工場不良品連絡書管理台帳）
					$sheet->setCellValueByColumnAndRow(46,$iStartPosRow + $i,$rec_N_PROCESS_LIMIT_YMD2);	//指定回答日（協力工場不良品連絡書管理台帳）
					$sheet->setCellValueByColumnAndRow(47,$iStartPosRow + $i,$rec_N_RETURN_YMD2);			//返却日（協力工場不良品連絡書管理台帳）
					$sheet->setCellValueByColumnAndRow(48,$iStartPosRow + $i,$rec_N_COMP_YMD2);				//完結日（協力工場不良品連絡書管理台帳）
					$sheet->setCellValueByColumnAndRow(49,$iStartPosRow + $i,$rec_N_SPECIAL_SPECIAL_YMD);	//発行日（特別作業記録管理台帳）
					$sheet->setCellValueByColumnAndRow(50,$iStartPosRow + $i,$rec_N_PROCESS_PERIOD_SPECIAL_YMD);	//処理期限（特別作業記録管理台帳）
					$sheet->setCellValueByColumnAndRow(51,$iStartPosRow + $i,$rec_N_SUBMIT_YMD1);			//払い出し日1
					$sheet->setCellValueByColumnAndRow(52,$iStartPosRow + $i,$rec_N_SUBMIT_YMD2);			//払い出し日2
					$sheet->setCellValueByColumnAndRow(53,$iStartPosRow + $i,$rec_N_SUBMIT_YMD3);			//払い出し日3
					$sheet->setCellValueByColumnAndRow(54,$iStartPosRow + $i,$rec_N_BACK_YMD1);				//戻り日1
					$sheet->setCellValueByColumnAndRow(55,$iStartPosRow + $i,$rec_N_BACK_YMD2);				//戻り日2
					$sheet->setCellValueByColumnAndRow(56,$iStartPosRow + $i,$rec_N_BACK_YMD3);				//戻り日3
					
					// 2019/09/20 ADD START
					//不具合区分縦を点線
					$sheet->getStyle('O'.($iStartPosRow + $i))->getBorders()->getLeft()->setBorderStyle('dashed');
					$sheet->getStyle('O'.($iStartPosRow + $i))->getBorders()->getRight()->setBorderStyle('dashed');
					$sheet->getStyle('P'.($iStartPosRow + $i))->getBorders()->getLeft()->setBorderStyle('dashed');
					// 2019/09/20 ADD END
					
					// 2019/10/ ADD START  対応中
					//同代表伝票NO間は点線
/* 					if($rec_C_POINTREF_NO = $sPointRefNoBf){
						$borders = $sheet->getStyle('A'.($iStartPosRow + $i-1).':BD'.($iStartPosRow + $i-1))->getBorders();
						$borders ->getBottom()->setBorderStyle('dashed');
					} */
					
					//廃棄金額を赤字表示
					$sheet->getStyle('AJ'.($iStartPosRow + $i))
						  ->getFont()->getColor()->setARGB('00FF0000');
					
					//代表番号が入力されている行は色塗り表示（ピンク）
					if($rec_C_POINTREF_NO <> "-"){
						$sheet->getStyle('A'.($iStartPosRow + $i).':BD'.($iStartPosRow + $i))->getFill()
							  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
							  ->getStartColor()->setARGB('00FFCCFF');
					}
					
					//集計対象外は色塗り表示（紫）
					if($rec_N_EXCLUDED==1){
						$sheet->getStyle('A'.($iStartPosRow + $i).':BD'.($iStartPosRow + $i))->getFill()
							  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
							  ->getStartColor()->setARGB('00CCCCFF');
					}
					
					// 2019/09/20 ADD START
					//保留で処理期限が昨日以前は色塗り表示（オレンジ）
					if($rec_C_PROCESS == "保留" and $aPara[$i][25]<>"0" and $aPara[$i][25] <= date("Ymd",strtotime("-1 day"))){
						$sheet->getStyle('X'.($iStartPosRow + $i).':X'.($iStartPosRow + $i))->getFill()
							  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
							  ->getStartColor()->setARGB('00FFC000');
					}
					// 2019/09/20 ADD END
					
					//特別作業記録マーキングチェック入っている場合、発行日・処理期限を赤塗
					if($rec_N_SPECIAL==1){
						$sheet->getStyle('AW'.($iStartPosRow + $i))
							  ->getFill()
							  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
							  ->getStartColor()->setARGB('00FF0000');
						$sheet->getStyle('AW'.($iStartPosRow + $i))->getFont()->getColor()->setARGB('FFFFFFFF');
						$sheet->getStyle('AW'.($iStartPosRow + $i))->getFont()->setBold(true);
							
						$sheet->getStyle('AX'.($iStartPosRow + $i))
							  ->getFill()
							  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
							  ->getStartColor()->setARGB('00FF0000');
							  $sheet->getStyle('AX'.($iStartPosRow + $i))->getFont()->getColor()->setARGB('FFFFFFFF');
						$sheet->getStyle('AX'.($iStartPosRow + $i))->getFont()->setBold(true);
						
					}
					//代表伝票NO保管
					//$sPointRefNoBf = $rec_C_POINTREF_NO;
				}
				
				//最終行に罫線を引く
				$borders = $sheet->getStyle('A'.($iStartPosRow + $i-1).':BD'.($iStartPosRow + $i-1))->getBorders();
				$borders ->getBottom()->setBorderStyle('medium');
				
				//アクティブセル設定
				$sheet->getStyle('A1');
				
				$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet,"Xlsx");
				//$writer->setOffice2003Compatibility(true);
				$writer->setIncludeCharts(true);
				$writer->save('php://output');
				exit;
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
<!-- ↓ADD 20180319 Y.Kuraishi -->
<?php
	//読み込み中の画像を表示してからリダイレクトして検索に移る（POST値はここでリセットが掛かるため引き継げない）
	if($output == 1){
		//echo "処理開始";
		//echo '<meta http-equiv="refresh" content="1;URL=F_FLK0090.php?action=menu&output=2">';
	}
?>
<!-- ↑ADD 20180319 Y.Kuraishi -->
<TITLE></TITLE>
<link rel="stylesheet" type="text/css" href="css/common.css">
<link rel="stylesheet" href="js/protocalendar/stylesheets/paper.css" type="text/css" media="all">
<script type="text/javascript" src="js/common.js"><!-- 共通javascript読み込み --></script>
<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/fabtabulous.js"></script>
<script type="text/javascript" src="js/tablekit.js"></script>
<script src="js/protocalendar/lib/effects.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script src="js/protocalendar/javascripts/protocalendar.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script src="js/protocalendar/javascripts/lang_ja.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script type="text/javascript" >
<!-- 個別javascript記述 -->
	
	//奇数行のクラス
	TableKit.options.rowEvenClass = "roweven";
	//偶数行のクラス
	TableKit.options.rowOddClass = "rowodd";
	
	/* 赤伝緑伝情報入力画面表示 */
	function fTrblDisp(strMode,strRrceNo,strRrceSeq){
		var strUrl;
		var aJoken = new Array(17);
		//GETで渡す引数なのでURLエンコードを行う
		aJoken[0] = encodeURI(document.form.sTargetSectionKbn.value);
		aJoken[1] = encodeURI(document.form.sPgrsStage.value);
		aJoken[2] = encodeURI(document.form.sRrceNo.value);
		aJoken[3] = encodeURI(document.form.sBusyoNm.value);
		aJoken[4] = encodeURI(document.form.sProdCd.value);
		aJoken[5] = encodeURI(document.form.sDrwNo.value);
		aJoken[6] = encodeURI(document.form.sProdNm.value);
		aJoken[7] = encodeURI(document.form.sCustNm.value);
		aJoken[8] = encodeURI(document.form.sFlawKbn.value);
		aJoken[9] = encodeURI(document.form.sIncidentF.value);
		aJoken[10] = encodeURI(document.form.sIncidentT.value);
		aJoken[11] = encodeURI(document.form.sProcessPeriodF.value);
		aJoken[12] = encodeURI(document.form.sProcessPeriodT.value);
		if (document.form.sDisposalFlg.checked == true){
			aJoken[13] = encodeURI("1");
		}else{
			aJoken[13] = encodeURI("");
		}
		
		aJoken[14] = encodeURI(document.form.sDecisionM.value);
		aJoken[15] = encodeURI(document.form.sReports.value);
		aJoken[16] = encodeURI(document.form.sIncidentKbn.value);
		aJoken[17] = encodeURI(document.form.sFlawLotNo.value);
		strUrl = "F_FLK0080";

		//URLを作成してジャンプ
		location.href = strUrl + ".php?mode=" + strMode + "&strRrceNo=" + strRrceNo + "&strRrceSeq=" + strRrceSeq
		+ "&aJoken[0]=" + aJoken[0] + "&aJoken[1]=" + aJoken[1] + "&aJoken[2]=" + aJoken[2]
		+ "&aJoken[3]=" + aJoken[3] + "&aJoken[4]=" + aJoken[4] + "&aJoken[5]=" + aJoken[5]
		+ "&aJoken[6]=" + aJoken[6] + "&aJoken[7]=" + aJoken[7] + "&aJoken[8]=" + aJoken[8]
		+ "&aJoken[9]=" + aJoken[9] + "&aJoken[10]=" + aJoken[10] + "&aJoken[11]=" + aJoken[11]
		+ "&aJoken[12]=" + aJoken[12] + "&aJoken[13]=" + aJoken[13] + "&aJoken[14]=" + aJoken[14]
		+ "&aJoken[15]=" + aJoken[15] + "&aJoken[16]=" + aJoken[16] + "&aJoken[17]=" + aJoken[17];
	}

	//戻るボタン
	function fReturn(){
		document.form.target ="main";
		document.form.action ="main.php";
		document.form.submit();
	}

	//検索ボタン
	function fSearch(){
		document.form.target ="main";
		document.form.action ="F_FLK0090.php?action=menu&search=1";
		document.form.submit();
	}

	//台帳出力ボタン
	function fOutPut(){
		document.form.target ="main";
		document.form.action ="F_FLK0090.php?action=menu&output=1";
		document.form.submit();
	}

	/* Excel出力 */
	function fExcelOut(strClass,strRrceNo,strRrceSeq){

		var strPrintName;

		if(strClass == "1"){
			strPrintName = "品質改善報告書";
		}else if(strClass == "2"){
			strPrintName = "協力工場不良品連絡書";
		}else if(strClass == "3"){
			strPrintName = "特別作業記録";
		}

		//確認メッセージ
		if(window.confirm(strPrintName + 'を出力してもよろしいですか？')){
			document.form.action ="F_FLK0090.php?action=menu&search=1&excel=1&no=" + strRrceNo + "&seq=" + strRrceSeq + "&class=" + strClass;
			document.form.method ="POST";
			document.form.target ="main";
			document.form.submit();
		}else{
			return false;
		}
	}

	//アップロードファイルダウンロード
	function fStartDownload(strURL){

		var strPrintName;
		strPrintName = "伝票ファイル";
		
		//確認メッセージ
		if(window.confirm(strPrintName + 'を出力してもよろしいですか？')){
			window.open(strURL, '_blank'); // 新しいタブを開き、ページを表示
		}else{
			return false;
		}
		
	}

</script>
</HEAD>
<BODY style="border-collapse : separate;" onload=fLoadDisplay();>
<form name="form" method="post" action="" onSubmit="">
<TABLE border="0" bgcolor="#000066">
	<TBODY>
		<TR>
			<TD align="center" width="1000">
				<SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【赤伝緑伝情報一覧照会】</SPAN>
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
		<TR>
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
<TABLE border="0">
	<TBODY>
		<TR>
			<TD width="800" class="tdnone">
				<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
					<FONT color="#ffffff"><B>異常品（赤伝・緑伝）処理状況</B></FONT></DIV>
				</TD>
			</TD>
		</TR>
	</TBODY>
</TABLE>

<TABLE class="tbline" width="860">
	<TBODY>
		<TR>
			<TD class="tdnone6" align="center" width="220" colspan="2" rowspan="2"><p class='fontmsg'><FONT color='white'>項目</p></TD>
			<TD class="tdnone6" align="center" width="200" colspan="2"><p class='fontmsg'><FONT color='white'>コネクタ部門</p></TD>
			<TD class="tdnone6" align="center" width="200" colspan="2"><p class='fontmsg'><FONT color='white'>めっき部門</p></TD>
			<TD class="tdnone6" align="center" width="200" colspan="2"><p class='fontmsg'><FONT color='white'>モールド部門</p></TD>
		</TR>
		<TR>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>数量</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>金額</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>数量</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>金額</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>数量</p></TD>
			<TD class="tdnone6" align="center"><p class='fontmsg'><FONT color='white'>金額</p></TD>
		</TR>
		<TR>
			<TD class="tdnone9" align="center" width="50" rowspan="2">廃棄</TD>
			<TD class="tdnone9" align="left" width="170"><?php echo $sLastM;?>（前月）</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][0]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][1]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][2]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][3]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][4]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[0][5]);?>円</TD>
		</TR>
		<TR>
			<TD class="tdnone9" align="left"><?php echo $sThisM;?>（当月予定）</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][0]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][1]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][2]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][3]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][4]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[1][5]);?>円</TD>
		</TR>
		<TR>
			<TD class="tdnone9" align="center">保留</TD>
			<TD class="tdnone9" align="left"><?php echo $sTdayM;?>（現在まで）</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][0]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][1]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][2]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][3]);?>円</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][4]);?>個</TD>
			<TD class="tdnone3" align="right"><?php echo number_format($aResT[2][5]);?>円</TD>
		</TR>
	</TBODY>
</TABLE>
<br>
<TABLE border="0">
	<TBODY>
		<TR>
			<TD width="800" class="tdnone">
				<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
					<FONT color="#ffffff"><B>検索条件</B></FONT></DIV>
				</TD>
			</TD>
		</TR>
	</TBODY>
</TABLE>
<TABLE class="tbline" width="1007">
	<TBODY>
		<TR>
			<TD class="tdnone2" width="125">対象部門</TD>
			<TD class="tdnone3" width="125">
				<SELECT name="sTargetSectionKbn" id="sTargetSectionKbn" tabindex="10">
					<OPTION selected value="-1" >全て</OPTION>
					<?php $module_sel->fMakeCombo('C04',$sTargetSectionKbn); ?>
				</SELECT>
			</TD>
			<TD class="tdnone2" width="125">進捗状態</TD>
			<TD class="tdnone3" width="125"　>
				<SELECT name="sPgrsStage" id="sPgrsStage" tabindex="20">
					<OPTION selected value="-1" >全て</OPTION>
					<?php $module_sel->fMakeCombo('C38',$sPgrsStage); ?>
				</SELECT>
			</TD>
			<TD class="tdnone2" width="125">伝票NO</TD>
			<TD class="tdnone3" width="125">
				<INPUT type="text" name="sRrceNo" id="sRrceNo" tabindex="30" size="15" maxlength="15" style="ime-mode: disabled;" value="<?php echo $sRrceNo; ?>">
			</TD>
			<TD class="tdnone2" width="125">起因部署</TD>
			<TD class="tdnone3" width="125">
				<INPUT type="text" name="sBusyoNm" id="sBusyoNm" tabindex="40" size="15" maxlength="40" value="<?php echo $sBusyoNm; ?>"></TD>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone2">製品CD</TD>
			<TD class="tdnone3"><INPUT type="text" name="sProdCd" id="sProdCd" tabindex="50" size="7" maxlength="25" style="ime-mode: disabled;" value="<?php echo $sProdCd; ?>"></TD>
			<TD class="tdnone2">仕様番号</TD>
			<TD class="tdnone3"><INPUT type="text" name="sDrwNo" id="sDrwNo" tabindex="60" size="7" maxlength="40" style="ime-mode: disabled;" value="<?php echo $sDrwNo; ?>"></TD>
			<TD class="tdnone2">製品名</TD>
			<TD class="tdnone3"><INPUT type="text" name="sProdNm" id="sProdNm" tabindex="70" size="15" maxlength="40" value="<?php echo $sProdNm; ?>"></TD>
			<TD class="tdnone2">得意先名</TD>
			<TD class="tdnone3"><INPUT type="text" name="sCustNm" id="sCustNm" tabindex="80" size="15" maxlength="40" value="<?php echo $sCustNm; ?>"></TD>
		</TR>
		<TR>
			<TD class="tdnone2">不具合区分</TD>
			<TD class="tdnone3">
				<SELECT name="sFlawKbn" id="sFlawKbn" tabindex="100">
					<OPTION selected value="-1">全て</OPTION>
					<?php $module_sel->fMakeComboS2('085',$sFlawKbn); ?>
				</SELECT>
			</TD>
			<TD class="tdnone2">伝票発行日</TD>
			<TD class="tdnone3" colspan = "3">
				<INPUT id="sIncidentF" tabindex="110" name="sIncidentF" size="7" type="text" style="ime-mode: disabled;" value="<?php echo $sIncidentF; ?>">
				<script type="text/javascript">
					//Ajaxカレンダ読込
					InputCalendar.createOnLoaded("sIncidentF", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
				</script>～
				<INPUT size="7" id="sIncidentT" tabindex="120" name="sIncidentT" type="text" style="ime-mode: disabled;" value="<?php echo $sIncidentT; ?>">
				<script type="text/javascript">
					//Ajaxカレンダ読込
					InputCalendar.createOnLoaded("sIncidentT", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
				</script>
			</TD>
			<TD class="tdnone3" colspan="2"><INPUT type="checkbox" name="sDisposalFlg" tabindex="150" value="1" <?php echo $sDisposalCheck; ?>>廃棄数量／金額有のみ</TD>
		</TR>
		<TR>
			<TD class="tdnone2">処理期限</TD>
			<TD class="tdnone3">
				<INPUT id="sProcessPeriodF" name="sProcessPeriodF" tabindex="130" size="7" type="text" style="ime-mode: disabled;" value="<?php echo $sProcessPeriodF; ?>">
				<script type="text/javascript">
					//Ajaxカレンダ読込
					InputCalendar.createOnLoaded("sProcessPeriodF", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
				</script>～
				<INPUT size="7" id="sProcessPeriodT" name="sProcessPeriodT" tabindex="140" type="text" style="ime-mode: disabled;" value="<?php echo $sProcessPeriodT; ?>">
				<script type="text/javascript">
					//Ajaxカレンダ読込
					InputCalendar.createOnLoaded("sProcessPeriodT", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
				</script>
			</TD>
			<TD class="tdnone2">処理判定月</TD>
			<TD class="tdnone3">
				<SELECT name="sDecisionM" id="sDecisionM" tabindex="160">
					<OPTION <?php if($sDecisionM==-1){echo "selected";}?> value="-1">全て</OPTION>
					<OPTION <?php if($sDecisionM==date("Ym",mktime(0,0,0,date("m")-2,1,date("Y")))){echo "selected";}?> value=<?php echo date("Ym",mktime(0,0,0,date("m")-2,1,date("Y"))) ?>><?php echo date("Y年m月",mktime(0,0,0,date("m")-2,1,date("Y"))) ?></option>
					<OPTION <?php if($sDecisionM==date("Ym",mktime(0,0,0,date("m")-1,1,date("Y")))){echo "selected";}?> value=<?php echo date("Ym",mktime(0,0,0,date("m")-1,1,date("Y"))) ?>><?php echo date("Y年m月",mktime(0,0,0,date("m")-1,1,date("Y"))) ?></option>
					<OPTION <?php if($sDecisionM==date("Ym")){echo "selected";}?> value=<?php echo date("Ym") ?>><?php echo date("Y年m月") ?></option>
				</SELECT>
			</TD>
			<TD class="tdnone2">報告書</TD>
			<TD class="tdnone3">
				<SELECT name="sReports" id="sReports" tabindex="170">
					<OPTION selected value="-1">全て</OPTION>
					<?php $module_sel->fMakeCombo('C35',$sReports); ?>
				</SELECT>
			</TD>
			<TD class="tdnone2">発行先区分</TD>
			<TD class="tdnone3">
				<SELECT name="sIncidentKbn" id="sIncidentKbn" tabindex="180">
				<OPTION selected value="-1" >全て</OPTION>
					<?php $module_sel->fMakeCombo('C05',$sIncidentKbn); ?>
				</SELECT>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone2">不具合ロットNO</TD>
			<TD class="tdnone3"><INPUT type="text" name="sFlawLotNo" id="sFlawLotNo" tabindex="190" size="25" maxlength="90" value="<?php echo $sFlawLotNo; ?>"></TD>
		</TR>
	</TBODY>
</TABLE>
<br>
<P><INPUT type="button" name="btnSearch" id="btnSearch" tabindex="200" value="　検　索　" onClick="fSearch()">　<!--<INPUT type="reset" name="btnReset" id="btnReset" tabindex="210" value="　リセット　">-->　<INPUT type="button" name="btnOut" id="btnOut" tabindex="210" value="  台帳出力  " onClick="fOutPut()"></P>
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

	<TABLE class="tbline sortable resizable" width="1600">
<!--		<TBODY> -->
<?php
		//更新／削除ボタン制御
		$strButton = "";

		//eValueNS集計担当者グループ未所属の場合更新ボタン非活性
		if($module_sel->fChkMstUserNS($_SESSION['login'][0]) === 0){
			$strButton = "disabled";
		}

		$i = 0;
		//ヘッダー行追加判断用変数
		$iPageCnt = 0;
		//件数分ループ
		while($i < count($aPara)){
			//奇数行、偶数行によって色変更
			if(($i % 2) == 0){
				$strClass = "tdnone3";
				$strClassProgress = "tdnone3";
				$strClassProcess = "tdnone3";
				$strClassPrcLimit =  "tdnone3";
			}else{
				$strClass = "tdnone4";
				$strClassProgress = "tdnone4";
				$strClassProcess = "tdnone4";
				$strClassPrcLimit =  "tdnone4";
			}

			//進捗状態が処理承認済の場合は処理をグレー
			if($aPara[$i][50] == "gray"){
				$strClassProgress = "tdnone12";
			}

			//処理期限が本日を過ぎている場合はピンク
			if($aPara[$i][51] == "limit"){
				$strClassProcess = "tdnone10";
			}

			//報告書処理期限が本日を過ぎている場合はピンク
			if($aPara[$i][52] == "limit"){
				$strClassPrcLimit = "tdnone10";
			}
			
			//伝票ファイルディレクトリ
			$dir  ="upload/trouble/".(trim($aPara[$i][0])."_".$aPara[$i][7])."/voucher/";
			$sVouBtnDis = "disabled";
			if(file_exists($dir)){
				//アップロードファイル取得
				$filelist=scandir($dir);
				foreach($filelist as $file):
					if(!is_dir($file)):
						$dir = $dir.$file;
						$sVouBtnDis = "";
						break;
					endif;
				endforeach;
			}
			//ヘッダーの挿入(20行毎)
//			if($iPageCnt%20 == 0){
			if($iPageCnt == 0){
				echo "<THEAD>";
				echo "<TR height='15'>";
				echo "<TD class='tdnone5 nosort' align='center' width='100' nowrap><B>アクション</B></TD>";
				echo "<TD class='tdnone2' align='center' width='80' nowrap>伝票NO</TD>";
				echo "<TD class='tdnone2' align='center' width='8' nowrap>SEQ</TD>";
				echo "<TD class='tdnone2' align='center' width='50' nowrap>処理</TD>";
				echo "<TD class='tdnone2' align='center' width='67' nowrap>伝票<br>発行日</TD>";
				echo "<TD class='tdnone2' align='center' width='78' nowrap>得意先名</TD>";
				echo "<TD class='tdnone2' align='center' width='70' nowrap>製品名</TD>";
				echo "<TD class='tdnone2' align='center' width='60'>仕様番号</TD>";
				echo "<TD class='tdnone2' align='center' width='67' nowrap>不具合<br>区分</TD>";
				echo "<TD class='tdnone2' align='center' width='70' nowrap>不具合<br>ロットNo</TD>";				// 2019/09/20 ADD END
				echo "<TD class='tdnone2' align='center' width='67' nowrap>処理<br>期限</TD>";
				echo "<TD class='tdnone2' align='center' width='67' nowrap>処理<br>判定日</TD>";					// 2019/09/20 ADD END
				echo "<TD class='tdnone2 number' align='center' width='70' nowrap>不具合<br>数量(個)</TD>";
				echo "<TD class='tdnone2' align='center' width='70' nowrap>不具合<br>金額(円)</TD>";
				echo "<TD class='tdnone2' align='center' width='70' nowrap>廃棄<br>数量(個)</TD>";
				echo "<TD class='tdnone2' align='center' width='70' nowrap>廃棄<br>金額(円)</TD>";
				echo "<TD class='tdnone2' align='center' width='70' nowrap>返品<br>数量(個)</TD>";
				echo "<TD class='tdnone2' align='center' width='70' nowrap>返品<br>金額(円)</TD>";
				echo "<TD class='tdnone2' align='center' width='70' nowrap>報告書<br>発行先</TD>";
				echo "<TD class='tdnone2' align='center' width='74' nowrap>報告書<br>処理期限</TD>";
				echo "<TD class='tdnone2' align='center' width='67' nowrap>報告書<br>完結日</TD>";
				echo "<TD class='tdnone5 nosort' align='center' width='50' nowrap><B>品質<br>改善<br>報告書</B></TD>";
				echo "<TD class='tdnone5 nosort' align='center' width='50' nowrap><B>協力<br>工場<br>不良品<br>連絡書</B></TD>";
				echo "<TD class='tdnone5 nosort' align='center' width='50' nowrap><B>特別<br>作業<br>記録</B></TD>";
				echo "<TD class='tdnone5 nosort' align='center' width='50' nowrap><B>伝票</B></TD>";
				echo "</TR>";
				echo "</THEAD>";
				echo "<TBODY>";
			}
			echo "<TR height='15'>";
			echo "<TD class='".$strClass."' align='center' width='120' nowrap>";
			echo "<INPUT type='button' ".$strButton." value='更新' style='background-color : #fdc257; width:40px; padding:0' onClick='fTrblDisp(\"2\",\"".$aPara[$i][0]."\",\"".$aPara[$i][7]."\");'>";
			echo "<INPUT type='button' ".$strButton." value='削除' style='background-color : #fdc257; width:40px; padding:0' onClick='fTrblDisp(\"3\",\"".$aPara[$i][0]."\",\"".$aPara[$i][7]."\");'>";
			echo "<INPUT type='button' value='参照' style='background-color : #fdc257; width:40px; padding:0' onClick='fTrblDisp(\"4\",\"".$aPara[$i][0]."\",\"".$aPara[$i][7]."\");'>";
			echo "</TD>";
			echo "<TD class='".$strClass."' width='86' nowrap>".$aPara[$i][0]."</TD>";
			echo "<TD class='".$strClass."' align='right' width='10' nowrap>".$aPara[$i][7]."</TD>";
			echo "<TD class='".$strClassProgress."' nowrap>".$aPara[$i][20]."</TD>";
			echo "<TD class='".$strClass."' nowrap>".$module_cmn->fChangDateFormat($aPara[$i][3])."</TD>";
			echo "<TD class='".$strClass."' width='60'><FONT size='-1.5' nowrap>".$aPara[$i][4]."</FONT></TD>";
			echo "<TD class='".$strClass."' width='60'><FONT size='-1.5' nowrap>".$aPara[$i][5]."</FONT></TD>";
			echo "<TD class='".$strClass."' nowrap>".$aPara[$i][6]."</TD>";
			echo "<TD class='".$strClass."' width='50' nowrap>".$aPara[$i][9]."</TD>";
			echo "<TD class='".$strClass."' width='60' style='word-break:break-all'><FONT size='-1.5' nowrap>".$aPara[$i][23]."</FONT></TD>";		// 2019/09/20 ADD END
			echo "<TD class='".$strClassProcess."' nowrap>".$module_cmn->fChangDateFormat($aPara[$i][10])."</TD>";
			echo "<TD class='".$strClass."' nowrap>".$module_cmn->fChangDateFormat($aPara[$i][8])."</TD>";				// 2019/09/20 ADD END
			echo "<TD class='".$strClass."' align='right' nowrap>".number_format($aPara[$i][11])."</TD>";
			echo "<TD class='".$strClass."' align='right' nowrap>".number_format($aPara[$i][12])."</TD>";
			echo "<TD class='".$strClass."' align='right' nowrap>".number_format($aPara[$i][13])."</TD>";
			echo "<TD class='".$strClass."' align='right' nowrap>".number_format($aPara[$i][14])."</TD>";
			echo "<TD class='".$strClass."' align='right' nowrap>".number_format($aPara[$i][15])."</TD>";
			echo "<TD class='".$strClass."' align='right' nowrap>".number_format($aPara[$i][16])."</TD>";
			echo "<TD class='".$strClass."' width='60' nowrap>".$aPara[$i][17]."</TD>";
			echo "<TD class='".$strClassPrcLimit."' align='center' width='67' nowrap>".$module_cmn->fChangDateFormat($aPara[$i][18])."</TD>";
			echo "<TD class='".$strClass."' align='center' width='74' nowrap>".$module_cmn->fChangDateFormat($aPara[$i][22])."</TD>";
			echo "<TD class='".$strClass."' align='center' nowrap>";
			echo "<INPUT type='button' value='出力' style='background-color : #fdc257;' onClick='fExcelOut(\"1\",\"".$aPara[$i][0]."\",\"".$aPara[$i][7]."\");'>";
			echo "</TD>";
			echo "<TD class='".$strClass."' align='center' nowrap>";
			echo "<INPUT type='button' value='出力' style='background-color : #fdc257;' onClick='fExcelOut(\"2\",\"".$aPara[$i][0]."\",\"".$aPara[$i][7]."\");'>";
			echo "</TD>";
			echo "<TD class='".$strClass."' align='center' nowrap>";
			echo "<INPUT type='button' value='出力' style='background-color : #fdc257;' onClick='fExcelOut(\"3\",\"".$aPara[$i][0]."\",\"".$aPara[$i][7]."\");'>";
			echo "</TD>";
			echo "<TD class='".$strClass."' align='center' nowrap>";
			echo "<INPUT type='button' value='出力' style='background-color : #fdc257;' onClick='fStartDownload(\"".$dir."\");' ".$sVouBtnDis.">";
			echo "</TD>";
			echo "</TR>";
			$iPageCnt = $iPageCnt + 1;
			$i = $i + 1;
			
			//最後の行を表示したら
			if( $i == count($aPara)){
				echo "</TBODY>";
			}
		}
?>
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

<?php
if($output == 1){
?>
<!--<div id="wait_msg">
	<P>　Loading...　<img src="gif/load.gif" width="50px"></P>
</div>-->
<?php
}
?>

<script type="text/javascript" >
	/* 初期フォーカス */
	document.getElementById('sTargetSectionKbn').focus();
</script>
</BODY>
</HTML>