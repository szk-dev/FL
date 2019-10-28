<?php

	//****************************************************************************
	//プログラム名：不具合情報一覧照会
	//プログラムID：F_FLK0020
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/06/15
	//履歴　　　　：2019/04/01 不具合区分をSMART2から取得 藤田
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
	require_once 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
	
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
	
	$aPara = Array();

	//引数の切替
	//一覧内での遷移
	if($action == "menu"){
	//画面項目の取得
	//引数の取得
		$sPgrsStage = $_POST['sPgrsStage'];
		$sRrceNo = $_POST['sRrceNo'];
		$sProdCd = $module_cmn->fEscape($_POST['sProdCd']);
		$sProdNm = $module_cmn->fEscape($_POST['sProdNm']);
		$sDrwNo = $module_cmn->fEscape($_POST['sDrwNo']);
		//$sModel = $module_cmn->fEscape($_POST['sModel']);
		$sCustCd = $module_cmn->fEscape($_POST['sCustCd']);
		$sFlawKbn = $module_cmn->fEscape($_POST['sFlawKbn']);
		$sReceptKbn = $module_cmn->fEscape($_POST['sReceptKbn']);
		$sResultKbn = $module_cmn->fEscape($_POST['sResultKbn']);
		$sCustApAnsDateF = $module_cmn->fEscape($_POST['sCustApAnsDateF']);
		$sCustApAnsDateT = $module_cmn->fEscape($_POST['sCustApAnsDateT']);
		$sApAnsDateF1 = $module_cmn->fEscape($_POST['sApAnsDateF1']);
		$sApAnsDateT1 = $module_cmn->fEscape($_POST['sApAnsDateT1']);
		$sApAnsDateF2 = $module_cmn->fEscape($_POST['sApAnsDateF2']);
		$sApAnsDateT2 = $module_cmn->fEscape($_POST['sApAnsDateT2']);
		$sCmpDateF1 = $module_cmn->fEscape($_POST['sCmpDateF1']);
		$sCmpDateT1 = $module_cmn->fEscape($_POST['sCmpDateT1']);
		$sCmpDateF2 = $module_cmn->fEscape($_POST['sCmpDateF2']);
		$sCmpDateT2 = $module_cmn->fEscape($_POST['sCmpDateT2']);
		$sFlawStep = $module_cmn->fEscape($_POST['sFlawStep']);
		$sTargetSec = $module_cmn->fEscape($_POST['sTargetSec']);
		$sIncidentKbn = $module_cmn->fEscape($_POST['sIncidentKbn']);
		$sIncidentCd1 = $module_cmn->fEscape($_POST['sIncidentCd1']);
		$sIncidentCd2 = $module_cmn->fEscape($_POST['sIncidentCd2']);


	//不具合入力画面からの遷移
	}elseif($action == "main"){

		$sPgrsStage = $module_cmn->fEscape($aJoken[0]);
		$sRrceNo = $module_cmn->fEscape($aJoken[1]);
		$sProdCd = $module_cmn->fEscape($aJoken[2]);
		$sProdNm = $module_cmn->fEscape($aJoken[3]);
		$sDrwNo = $module_cmn->fEscape($aJoken[4]);
//		$sModel = $module_cmn->fEscape($aJoken[5]);
		$sFlawKbn = $module_cmn->fEscape($aJoken[5]);
		$sReceptKbn = $module_cmn->fEscape($aJoken[6]);
		$sResultKbn = $module_cmn->fEscape($aJoken[7]);
		$sApAnsDateF1 = $module_cmn->fEscape($aJoken[8]);
		$sApAnsDateT1 = $module_cmn->fEscape($aJoken[9]);
		$sApAnsDateF2 = $module_cmn->fEscape($aJoken[10]);
		$sApAnsDateT2 = $module_cmn->fEscape($aJoken[11]);
		$sCmpDateF1 = $module_cmn->fEscape($aJoken[12]);
		$sCmpDateT1 = $module_cmn->fEscape($aJoken[13]);
		$sCmpDateF2 = $module_cmn->fEscape($aJoken[14]);
		$sCmpDateT2 = $module_cmn->fEscape($aJoken[15]);
		$sFlawStep = $module_cmn->fEscape($aJoken[16]);
		$sTargetSec = $module_cmn->fEscape($aJoken[17]);
		$sIncidentKbn = $module_cmn->fEscape($aJoken[18]);
		$sIncidentCd1 = $module_cmn->fEscape($aJoken[19]);
		$sIncidentCd2 = $module_cmn->fEscape($aJoken[20]);
		$sCustCd = $module_cmn->fEscape($aJoken[21]);
		$sCustApAnsDateF = $module_cmn->fEscape($aJoken[22]);
		$sCustApAnsDateT = $module_cmn->fEscape($aJoken[23]);

	}


	//検索条件格納用配列
	$aJoken = array();

	$aJoken[0] = $sPgrsStage;
	$aJoken[1] = $sRrceNo;
	$aJoken[2] = $sProdCd;
	$aJoken[3] = $sProdNm;
	$aJoken[4] = $sDrwNo;
	$aJoken[5] = $sFlawKbn;
	$aJoken[6] = $sReceptKbn;
	$aJoken[7] = $sResultKbn;
	$aJoken[8] = str_replace('/','',$sApAnsDateF1);			//日付はスラッシュ削除
	$aJoken[9] = str_replace('/','',$sApAnsDateT1);			//日付はスラッシュ削除
	$aJoken[10] = str_replace('/','',$sApAnsDateF2);		//日付はスラッシュ削除
	$aJoken[11] = str_replace('/','',$sApAnsDateT2);		//日付はスラッシュ削除
	$aJoken[12] = str_replace('/','',$sCmpDateF1);		//日付はスラッシュ削除
	$aJoken[13] = str_replace('/','',$sCmpDateT1);		//日付はスラッシュ削除
	$aJoken[14] = str_replace('/','',$sCmpDateF2);		//日付はスラッシュ削除
	$aJoken[15] = str_replace('/','',$sCmpDateT2);		//日付はスラッシュ削除
	$aJoken[16] = $sFlawStep;
	$aJoken[17] = $sTargetSec;
	$aJoken[18] = $sIncidentKbn;
	$aJoken[19] = $sIncidentCd1;
	$aJoken[20] = $sIncidentCd2;
	$aJoken[21] = $sCustCd;
	$aJoken[22] = str_replace('/','',$sCustApAnsDateF);			//日付はスラッシュ削除
	$aJoken[23] = str_replace('/','',$sCuatApAnsDateT);			//日付はスラッシュ削除





	//不具合管理状況の検索
	$aRes = array();
	$aRes = $module_sel->fFlawStatsSearch();



	//検索処理(件数取得)
	if(isset($_GET['search'])){
		//検索条件取得
		if($_GET['search'] == "1"){


			$aPara = array();
			//不具合情報データ検索処理
			$aPara = $module_sel->fFlawSearch($_SESSION['login'],$aJoken,$module_sel->fWorkCalender());

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

	//各種関連書類のExcel出力
	if(isset($_GET['excel'])){
		if($_GET['excel'] == "1"){

			$aRec = array();
			//出力するExcelファイルへ書き出すデータを取得
			$aRec = $module_sel->fGetFlawData($_GET['no']);

			$txtReference_No 		= $aRec[0]; 	//整理NO
			$txtProgres_Stage 		= $aRec[1]; 	//進捗状態
			$txtCust_CD 			= $aRec[2]; 	//顧客CD
			$txtCust_NM 			= $aRec[3]; 	//顧客名
			$txtCust_Officer 		= $aRec[4]; 	//顧客担当者
			$cmbCust_Contact_KBN 	= $aRec[5]; 	//客先よりの連絡方法
			$cmbRecept_KBN 			= $aRec[6]; 	//受付区分
			$cmbFlaw_KBN 			= $aRec[7]; 	//不具合区分
			$txtTarget_QTY 			= number_format($aRec[8]); 	//対象数量
			$cmbTarget_Section_KBN 	= $aRec[9]; 	//対象部門
			$txtProd_CD 			= $aRec[10]; 	//製品CD
			$txtProd_NM 			= $aRec[11]; 	//製品名
			$txtDRW_NO 				= $aRec[12]; 	//仕様番号
//			$txtModel 				= $aRec[13]; 	//型式
			$txtDie_NO 				= $aRec[14]; 	//金型番号
			$txtLot_NO 				= $aRec[15]; 	//ロットNO
			$cmbIncident_KBN		= $aRec[16]; 	//発行先区分
			$txtIncident_NM1		= $aRec[17]; 	//発行先名称(社内)
			$txtIncident_NM2		= $aRec[48]; 	//発行先名称(協工)

			$txtProduct_Ka_CD 		= $aRec[18]; 	//生産担当部門CD
			$txtProduct_Ka_NM 		= $aRec[19]; 	//生産担当部門名
			$txtProduct_Officer_NM 	= $aRec[20]; 	//生産担当者
			$cmbProduct_Out_Ka_CD 	= $aRec[21]; 	//発生起因部署
			$cmbCheck_Out_Ka_CD1 	= $aRec[22]; 	//流出起因部署1
			$cmbCheck_Out_Ka_CD2 	= $aRec[46]; 	//流出起因部署2
			$txtFlaw_Contents 		= $aRec[23]; 	//不具合内容
			$txtReturn_QTY 			= number_format($aRec[24]); 					//返却数量
			$txtBat_QTY 			= number_format($aRec[25]); 					//不良数量
			$cmbReturn_Disposal 	= $aRec[26]; 									//返却品処理
			$cmbResult_KBN 			= $aRec[27]; 									//結果区分
			$txtCust_Ap_Ans_YMD 	= $module_cmn->fChangDateFormat($aRec[28]); 	//顧客指定回答日
			$txtCust_Accept_YMD 	= $module_cmn->fChangDateFormat($aRec[29]); 	//顧客了承回答日
			$txtAns_YMD 			= $module_cmn->fChangDateFormat($aRec[30]); 	//回答日
			$txtAns_Tanto_CD 		= $aRec[31]; 									//回答者CD
			$txtAnd_Tanto_Nm 		= $aRec[32]; 									//回答者名
			$txtPc_Ap_Ans_YMD1 		= $module_cmn->fChangDateFormat($aRec[34]); 	//品証指定回答日(社内)(スラッシュ)
			$txtPc_Ap_Ans_YMD1_W 	= $module_cmn->fChangDateFormat4($aRec[34]); 	//品証指定回答日(社内)(年月日)
			$txtPc_Ap_Ans_YMD2 		= $module_cmn->fChangDateFormat($aRec[49]); 	//品証指定回答日(協工)(スラッシュ)
			$txtPc_Ap_Ans_YMD2_W 	= $module_cmn->fChangDateFormat4($aRec[49]); 	//品証指定回答日(協工)(年月日)

			$txtReturn_YMD 			= $module_cmn->fChangDateFormat($aRec[35]); 	//返却日
			$txtContact_Accept_YMD 	= $module_cmn->fChangDateFormat($aRec[42]); 	//連絡受理日
			$aTanto = array();
			$aTanto					= $module_sel->fGetTantoData($aRec[47]); 		//登録者情報
			$txtIns_Tanto_Info		= $aTanto[7]."　".$aTanto[1];

			$cmbQuick_Fix_CD		= $aRec[58]; 									//異常品暫定処置CD

			// 必要なクラスをインクルードする
			/** パスの設定（PHPExcel.phpまで届くようにパスを設定します） **/
			//set_include_path(get_include_path() .'/Classes');
			/** PHPExcel ここでPHPExcel.phpを相対パスで直接指定すれば上のパスの設定はなくても大丈夫です。*/
			// 'PHPExcel.php';
			//include 'PHPExcel/Writer/Excel2007.php';

			//require_once '/Classes/PHPExcel.php';
			//require_once '/Classes/PHPExcel/IOFactory.php';

			//入出力ファイルの切替
			if(isset($_GET['class'])){
				if($_GET['class'] == "1"){
					//$strImportFile = mb_convert_encoding("不具合連絡書_雛型.xls","SJIS","UTF-8");
					//$strExportFile = mb_convert_encoding("不具合連絡書.xls","SJIS","UTF-8");
					$strImportFile = mb_convert_encoding("不具合連絡書_雛型.xlsx","SJIS","UTF-8");
					$strExportFile = mb_convert_encoding("不具合連絡書.xlsx","SJIS","UTF-8");
				}elseif($_GET['class'] == "2"){
					//$strImportFile = mb_convert_encoding("品質異常改善通知書_雛型.xls","SJIS","UTF-8");
					//$strExportFile = mb_convert_encoding("品質異常改善通知書.xls","SJIS","UTF-8");
					$strImportFile = mb_convert_encoding("品質異常改善通知書_雛型.xlsx","SJIS","UTF-8");
					$strExportFile = mb_convert_encoding("品質異常改善通知書.xlsx","SJIS","UTF-8");
				}elseif($_GET['class'] == "3"){
					//$strImportFile = mb_convert_encoding("不良品連絡書_雛型.xls","SJIS","UTF-8");
					//$strExportFile = mb_convert_encoding("不良品連絡書.xls","SJIS","UTF-8");
					$strImportFile = mb_convert_encoding("不良品連絡書_雛型.xlsx","SJIS","UTF-8");
					$strExportFile = mb_convert_encoding("不良品連絡書.xlsx","SJIS","UTF-8");
				}
			}

			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			$spreadsheet = $reader->load("template/".$strImportFile);
			
			//ブラウザへ出力をリダイレクト
			//header('Content-Type: application/vnd.ms-excel');
			//header("Content-Disposition: attachment;filename=".$strExportFile);
			//header('Cache-Control: max-age=0');
			header("Content-Description: File Transfer");
			header('Content-Disposition: attachment; filename='.$strExportFile);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			//出力用バッファをクリア(消去)し、出力のバッファリングをオフにする
			ob_end_clean();
			//出力のバッファリングを有効にする
			ob_start();
			
			//テンプレートの読み込み
			//$objReader = PHPExcel_IOFactory::createReader("Excel5");
			//$xl = $objReader->load("template/".$strImportFile);

			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			//$reader->setIncludeCharts(true);
			$spreadsheet = $reader->load("template/".$strImportFile);
			
			//シートの設定
			//$xl->setActiveSheetIndex(0);
			//$sheet = $xl->getActiveSheet();
			$spreadsheet->setActiveSheetIndex(0);
			$sheet = $spreadsheet->getActiveSheet();

			//不具合連絡書
			if($_GET['class'] == "1"){
				//セルの値を設定
				$sheet->setCellValue('Q1', date("Y年m月d日")); 												//発行日
				$sheet->setCellValue('A2', $sheet->getCell('A2')->getValue()
				."(".$module_sel->fDispKbn('C04', trim($cmbTarget_Section_KBN)).")"); 						//部門名
				$sheet->setCellValue('R2', $txtReference_No); 												//整理NO
				$sheet->setCellValue('B3', $txtIncident_NM1); 												//発行先名称(社内)
				$sheet->setCellValue('B7', $txtIncident_NM2); 												//発行先名称(協工)

				//$sheet->setCellValue('B4', $txtProduct_Officer_NM); 										//生産担当者名
				$sheet->setCellValue('F12', $module_cmn->fChangDateTimeFormat($txtContact_Accept_YMD));		//連絡受理日
				$sheet->setCellValue('F13', $txtIns_Tanto_Info);											//登録者
				$sheet->setCellValue('F14', $txtCust_NM );	 												//顧客名
				$sheet->setCellValue('P14', $txtCust_Officer );	 											//顧客担当者名
				$sheet->setCellValue('F15', $module_sel->fDispKbn('C02', trim($cmbCust_Contact_KBN))); 		//客先連絡方法
				$sheet->setCellValue('F16', $module_sel->fDispKbn('C03', trim($cmbRecept_KBN))); 			//受付区分
				$sheet->setCellValue('F17', $txtDRW_NO );	 												//図番
				$sheet->setCellValue('M17', $txtProd_NM );	 												//品名
				$sheet->setCellValue('F18', $txtLot_NO );	 												//ロットNO
				$sheet->setCellValue('F20', $txtTarget_QTY."個" );	 										//対象数量
				$sheet->setCellValue('A22', $txtFlaw_Contents); 											//不具合内容

				//不具合連絡書の書式変更に伴い、掲載期限は不要 2017/05/11 k.kume
				//$sheet->setCellValue('Q36', $module_cmn->fChangDateFormat($module_sel->fLimitCalender(5))); //掲載期限

				//発行先が社内の場合
				if($cmbIncident_KBN == "0"){
					$sheet->setCellValue('G32', "必要");
					$sheet->setCellValue('R32', "不要");
					$txtPc_Ap_Ans_YMD2 = "-";
				}elseif($cmbIncident_KBN == "1"){
					//発行先が協力工場の場合
					$sheet->setCellValue('G32', "不要");
					$sheet->setCellValue('R32', "必要");
					$txtPc_Ap_Ans_YMD1 = "-";
				}elseif($cmbIncident_KBN == "2"){
					//発行先が社内・協力工場の場合
					$sheet->setCellValue('G32', "必要");
					$sheet->setCellValue('R32', "必要");
				}else{
					//発行先がない場合
					$sheet->setCellValue('G32', "不要");
					$sheet->setCellValue('R32', "不要");
					$txtPc_Ap_Ans_YMD1 = "-";
					$txtPc_Ap_Ans_YMD2 = "-";
				}

				$sheet->setCellValue('E33', $txtPc_Ap_Ans_YMD1); 											//回答期限(社内)
				$sheet->setCellValue('O33', $txtPc_Ap_Ans_YMD2); 											//回答期限(協工)

				//ファイルが存在したらリンク表示
				if(file_exists("upload\\".$txtReference_No."\\".$txtReference_No.".jpg")){
					//添付画像貼り付け
					///画像用のオプジェクト作成
					$objDrawing = new drawing();

					//画像パスファイル
					$strPhotoPath = "upload\\".$txtReference_No."\\".$txtReference_No.".jpg";

					$objDrawing->setPath($strPhotoPath);///貼り付ける画像のパスを指定
					//$objDrawing->setHeight(150);////画像の高さを指定
					$objDrawing->setWidth(210);	////画像の幅を指定

					///画像のプロパティを見たときに表示される情報を設定
					$objDrawing->setName('');////ファイル名
					$objDrawing->setDescription('');////画像の概要
					$objDrawing->setCoordinates('O22');///位置
					//$objDrawing->setOffsetX(300);////横方向へ何ピクセルずらすかを指定
					//$objDrawing->setRotation(25);//回転の角度
					//$objDrawing->getShadow()->setVisible(true);////ドロップシャドウをつけるかどうか。
					
					///オブジェクトに張り込み
					$objDrawing->setWorksheet($sheet);
				}
			}
			//品質異常改善通知書
			elseif($_GET['class'] == "2"){
				//セルの値を設定
// 2019/04/01 ED START
/*
				//セルの値を設定
				$sheet->setCellValue('V6', date("Y年m月d日")); 												//発行日
				$sheet->setCellValue('W2', $txtReference_No); 												//整理NO
				$sheet->setCellValue('V3', $txtIncident_NM1);						 						//発行先名称(社内)
				$sheet->setCellValue('V4', $txtProduct_Officer_NM);						 					//生産担当名

				$sheet->setCellValue('F9', $txtPc_Ap_Ans_YMD1_W); 											//品証指定回答日(社内)


				//$sheet->setCellValue('N8',  $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(3)));	//生産担当回答日
				//$sheet->setCellValue('N9',  $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(6))); 	//発生起因回答日
				//$sheet->setCellValue('N10', $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(9)));	//流出起因回答日
				//品証からの要望で設定日変更 2016/09/02 k.kume
				$sheet->setCellValue('N8',  $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(2)));	//生産担当回答日
				$sheet->setCellValue('N9',  $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(3))); 	//発生起因回答日
				$sheet->setCellValue('N10', $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(5)));	//流出起因回答日

				$sheet->setCellValue('E11', $txtDRW_NO);						 								//仕様番号
				$sheet->setCellValue('E12', $txtProd_NM);						 								//品名
				$sheet->setCellValue('E13', $txtLot_NO);						 								//ロットNO
				$sheet->setCellValue('E14', $txtTarget_QTY."個");												//対象数量
				$sheet->setCellValue('C16', $txtFlaw_Contents);						 							//不具合内容

				$sheet->setCellValue('E41', $txtDie_NO);						 								//金型番号

				$sheet->setCellValue('L8', "発生原因 ".$module_sel->fDispKbn("C06", $cmbProduct_Out_Ka_CD)."：");			//生産担当部署名
				$sheet->setCellValue('L9', "流出原因 ".$module_sel->fDispKbn("C06", $cmbCheck_Out_Ka_CD1)."：");			//流出起因部署名1
				$sheet->setCellValue('L10', "流出原因 ".$module_sel->fDispKbn("C07", $cmbCheck_Out_Ka_CD2)."：");			//流出起因部署名2

				$sheet->setCellValue('B22', $module_sel->fDispKbn("C06", $cmbProduct_Out_Ka_CD));				//生産担当部署名
				$sheet->setCellValue('B43', $module_sel->fDispKbn("C06", $cmbCheck_Out_Ka_CD1));				//流出起因部署名1
				$sheet->setCellValue('B54', $module_sel->fDispKbn("C07", $cmbCheck_Out_Ka_CD2));				//流出起因部署名2

				$sheet->setCellValue('F20', $module_sel->fDispKbn("C28", $cmbQuick_Fix_CD));					//異常品暫定処置名称
				$sheet->setCellValue('F20', $module_sel->fDispKbn("C28", $cmbQuick_Fix_CD));					//異常品暫定処置名称
*/
				$sheet->setCellValue('E2', $txtReference_No); 													//整理NO
				$sheet->setCellValue('E3', trim($txtProd_CD));					 								//製品CD
				$sheet->setCellValue('V2', $txtIncident_NM1);						 							//発行先名称(社内)
				$sheet->setCellValue('V3', $txtProduct_Officer_NM);						 						//生産担当名
				$sheet->setCellValue('V4', date("Y年m月d日")); 													//発行日
				$sheet->setCellValue('F7', $txtPc_Ap_Ans_YMD1_W); 												//品証指定回答日(社内)
				$sheet->setCellValue('N6',  $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(2)));	//生産担当回答日
				$sheet->setCellValue('N7',  $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(3))); 	//発生起因回答日
				$sheet->setCellValue('N8', $module_cmn->fChangDateFormat4($module_sel->fLimitCalender(5)));		//流出起因回答日
				$sheet->setCellValue('E9', $txtDRW_NO);						 									//仕様番号
				$sheet->setCellValue('E10', $txtProd_NM);						 								//品名
				$sheet->setCellValue('E11', $txtLot_NO);						 								//ロットNO
				$sheet->setCellValue('E12', $txtTarget_QTY."個");												//対象数量
				$sheet->setCellValue('C14', $txtFlaw_Contents);						 							//不具合内容
				$sheet->setCellValue('E33', $txtDie_NO);						 								//金型番号
				$sheet->setCellValue('L6', "発生原因a ".$module_sel->fDispKbn("C06", $cmbProduct_Out_Ka_CD)."：");//生産担当部署名
				$sheet->setCellValue('L7', "流出原因 ".$module_sel->fDispKbn("C06", $cmbCheck_Out_Ka_CD1)."：");	//流出起因部署名1
				$sheet->setCellValue('L8', "流出原因 ".$module_sel->fDispKbn("C07", $cmbCheck_Out_Ka_CD2)."：");	//流出起因部署名2
				$sheet->setCellValue('B16', $module_sel->fDispKbn("C06", $cmbProduct_Out_Ka_CD));				//生産担当部署名
				$sheet->setCellValue('B36', $module_sel->fDispKbn("C06", $cmbCheck_Out_Ka_CD1));				//流出起因部署名1
				$sheet->setCellValue('B52', $module_sel->fDispKbn("C07", $cmbCheck_Out_Ka_CD2));				//流出起因部署名2
// 2019/04/01 ED END

				//ファイルが存在したらリンク表示
				if(file_exists("upload\\".$txtReference_No."\\".$txtReference_No.".jpg")){
					//添付画像貼り付け
					///画像用のオプジェクト作成
					$objDrawing = new drawing();

					//画像パスファイル
					$strPhotoPath = "upload\\".$txtReference_No."\\".$txtReference_No.".jpg";

					$objDrawing->setPath($strPhotoPath);///貼り付ける画像のパスを指定

					$objDrawing->setHeight(160);////画像の高さを指定
					//$objDrawing->setWidth(210);	////画像の幅を指定

					///画像のプロパティを見たときに表示される情報を設定
					$objDrawing->setName('');////ファイル名
					$objDrawing->setDescription('');////画像の概要
// 2019/04/01 ED START
					//$objDrawing->setCoordinates('O12');///位置
					$objDrawing->setCoordinates('O10');///位置
// 2019/04/01 ED END
					//$objDrawing->setOffsetX(300);////横方向へ何ピクセルずらすかを指定
					//$objDrawing->setRotation(25);//回転の角度
					//$objDrawing->getShadow()->setVisible(true);////ドロップシャドウをつけるかどうか。

					///PHPExcelオブジェクトに張り込み
					$objDrawing->setWorksheet($sheet);
				}
			}
			//不良品連絡書
			elseif($_GET['class'] == "3"){
				//セルの値を設定
				$sheet->setCellValue('L2', date("Y年m月d日")); 												//発行日
				$sheet->setCellValue('L3', $txtReference_No); 												//整理NO
				$sheet->setCellValue('L4', trim($txtProd_CD)); 												//製品CD
				$sheet->setCellValue('C3', $txtIncident_NM2); 												//発行先名称(協工)
				$sheet->setCellValue('F10', $txtPc_Ap_Ans_YMD2_W); 											//品証指定回答日
				$sheet->setCellValue('D12', $txtDRW_NO);						 							//仕様番号
				$sheet->setCellValue('D13', $txtProd_NM);						 							//品名

				//品証からの要望で金型番号追加(2014/03/31)k.kume
				//$sheet->setCellValue('D14', $txtLot_NO);						 							//ロットNO
				$sheet->setCellValue('D14', $txtDie_NO);						 							//金型番号
				$sheet->setCellValue('D15', $txtLot_NO);						 							//ロットNO
				$sheet->setCellValue('D17', $module_cmn->fChangDateTimeFormat($txtContact_Accept_YMD));		//連絡受理日
				$sheet->setCellValue('D18', $txtTarget_QTY."個");					 						//対象数量
				$sheet->setCellValue('H13', $txtFlaw_Contents);						 						//不具合内容

				//ファイルが存在したらリンク表示
				if(file_exists("upload\\".$txtReference_No."\\".$txtReference_No.".jpg")){
					//添付画像貼り付け
					///画像用のオプジェクト作成
					$objDrawing = new drawing();

					//画像パスファイル
					$strPhotoPath = "upload\\".$txtReference_No."\\".$txtReference_No.".jpg";

					$objDrawing->setPath($strPhotoPath);///貼り付ける画像のパスを指定
					//$objDrawing->setHeight(160);////画像の高さを指定
					$objDrawing->setWidth(150);	////画像の幅を指定

					///画像のプロパティを見たときに表示される情報を設定
					$objDrawing->setName('');////ファイル名
					$objDrawing->setDescription('');////画像の概要
					$objDrawing->setCoordinates('K13');///位置
					//$objDrawing->setOffsetX(300);////横方向へ何ピクセルずらすかを指定
					//$objDrawing->setRotation(25);//回転の角度
					//$objDrawing->getShadow()->setVisible(true);////ドロップシャドウをつけるかどうか。

					///PHPExcelオブジェクトに張り込み
					$objDrawing->setWorksheet($sheet);
				}

			}

			//発行日の更新
			if(!$module_upd->fUpdateIssuDate($txtReference_No,$_GET['class'],$_SESSION['login'])){
				$strErrMsg = $module_sel->fMsgSearch("E012","整理NO:".$_POST['txtReference_No']);
			}


			//Excel5形式で保存
			//$writer = PHPExcel_IOFactory::createWriter($xl, 'Excel5');
			//$writer->save('php://output');
			
			//保存
			//アクティブセル設定
			$sheet->getStyle('A1');
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet,"Xlsx");
			//$writer->setOffice2003Compatibility(true);
			//$writer->setIncludeCharts(true);
			$writer->save('php://output');
			exit;

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


	/* 不具合入力画面表示 */
	function fFlawDisp(strMode,strRrceNo){

		var strUrl;
		var aJoken = new Array(3);
		//GETで渡す引数なのでURLエンコードを行う
		aJoken[0] = encodeURI(document.form.sPgrsStage.value);
		aJoken[1] = encodeURI(document.form.sRrceNo.value);
		aJoken[2] = encodeURI(document.form.sProdCd.value);
		aJoken[3] = encodeURI(document.form.sProdNm.value);
		aJoken[4] = encodeURI(document.form.sDrwNo.value);
//		aJoken[5] = encodeURI(document.form.sModel.value);
		aJoken[5] = encodeURI(document.form.sFlawKbn.value);
		aJoken[6] = encodeURI(document.form.sReceptKbn.value);
		aJoken[7] = encodeURI(document.form.sResultKbn.value);
		aJoken[8] = encodeURI(document.form.sApAnsDateF1.value);
		aJoken[9] = encodeURI(document.form.sApAnsDateT1.value);
		aJoken[10] = encodeURI(document.form.sApAnsDateF2.value);
		aJoken[11] = encodeURI(document.form.sApAnsDateT2.value);
		aJoken[12] = encodeURI(document.form.sCmpDateF1.value);
		aJoken[13] = encodeURI(document.form.sCmpDateT1.value);
		aJoken[14] = encodeURI(document.form.sCmpDateF2.value);
		aJoken[15] = encodeURI(document.form.sCmpDateT2.value);
		aJoken[16] = encodeURI(document.form.sFlawStep.value);
		aJoken[17] = encodeURI(document.form.sTargetSec.value);
		aJoken[18] = encodeURI(document.form.sIncidentKbn.value);
		aJoken[19] = encodeURI(document.form.sIncidentCd1.value);
		aJoken[20] = encodeURI(document.form.sIncidentCd2.value);
		aJoken[21] = encodeURI(document.form.sCustCd.value);
		aJoken[22] = encodeURI(document.form.sCustApAnsDateF.value);
		aJoken[23] = encodeURI(document.form.sCustApAnsDateT.value);

		strUrl = "F_FLK0010";

		//URLを作成してジャンプ
		location.href = strUrl + ".php?mode=" + strMode + "&strRrceNo=" + strRrceNo
		+ "&aJoken[0]=" + aJoken[0] + "&aJoken[1]=" + aJoken[1] + "&aJoken[2]=" + aJoken[2]
		+ "&aJoken[3]=" + aJoken[3] + "&aJoken[4]=" + aJoken[4] + "&aJoken[5]=" + aJoken[5]
		+ "&aJoken[6]=" + aJoken[6] + "&aJoken[7]=" + aJoken[7] + "&aJoken[8]=" + aJoken[8]
		+ "&aJoken[9]=" + aJoken[9] + "&aJoken[10]=" + aJoken[10] + "&aJoken[11]=" + aJoken[11]
		+ "&aJoken[12]=" + aJoken[12] + "&aJoken[13]=" + aJoken[13] + "&aJoken[14]=" + aJoken[14]
		+ "&aJoken[15]=" + aJoken[15] + "&aJoken[16]=" + aJoken[16] + "&aJoken[17]=" + aJoken[17]
		+ "&aJoken[18]=" + aJoken[18] + "&aJoken[19]=" + aJoken[19] + "&aJoken[20]=" + aJoken[20]
		+ "&aJoken[21]=" + aJoken[21] + "&aJoken[22]=" + aJoken[22] + "&aJoken[23]=" + aJoken[23];
	}

	/* 不具合対策入力画面表示 */
	function fFlawStepDisp(strRrceNo){

		var strUrl;
		var aJoken = new Array(3);
		//GETで渡す引数なのでURLエンコードを行う

		aJoken[0] = encodeURI(document.form.sPgrsStage.value);
		aJoken[1] = encodeURI(document.form.sRrceNo.value);
		aJoken[2] = encodeURI(document.form.sProdCd.value);
		aJoken[3] = encodeURI(document.form.sProdNm.value);
		aJoken[4] = encodeURI(document.form.sDrwNo.value);
//		aJoken[5] = encodeURI(document.form.sModel.value);
		aJoken[5] = encodeURI(document.form.sFlawKbn.value);
		aJoken[6] = encodeURI(document.form.sReceptKbn.value);
		aJoken[7] = encodeURI(document.form.sResultKbn.value);
		aJoken[8] = encodeURI(document.form.sApAnsDateF1.value);
		aJoken[9] = encodeURI(document.form.sApAnsDateT1.value);
		aJoken[10] = encodeURI(document.form.sApAnsDateF2.value);
		aJoken[11] = encodeURI(document.form.sApAnsDateT2.value);
		aJoken[12] = encodeURI(document.form.sCmpDateF1.value);
		aJoken[13] = encodeURI(document.form.sCmpDateT1.value);
		aJoken[14] = encodeURI(document.form.sCmpDateF2.value);
		aJoken[15] = encodeURI(document.form.sCmpDateT2.value);
		aJoken[16] = encodeURI(document.form.sFlawStep.value);
		aJoken[17] = encodeURI(document.form.sTargetSec.value);
		aJoken[18] = encodeURI(document.form.sIncidentKbn.value);
		aJoken[19] = encodeURI(document.form.sIncidentCd1.value);
		aJoken[20] = encodeURI(document.form.sIncidentCd2.value);
		aJoken[21] = encodeURI(document.form.sCustCd.value);
		aJoken[22] = encodeURI(document.form.sCustApAnsDateF.value);
		aJoken[23] = encodeURI(document.form.sCustApAnsDateT.value);



		strUrl = "F_FLK0030";

		//URLを作成してジャンプ
		location.href = strUrl + ".php?strRrceNo=" + strRrceNo
		+ "&aJoken[0]=" + aJoken[0] + "&aJoken[1]=" + aJoken[1] + "&aJoken[2]=" + aJoken[2]
		+ "&aJoken[3]=" + aJoken[3] + "&aJoken[4]=" + aJoken[4] + "&aJoken[5]=" + aJoken[5]
		+ "&aJoken[6]=" + aJoken[6] + "&aJoken[7]=" + aJoken[7] + "&aJoken[8]=" + aJoken[8]
		+ "&aJoken[9]=" + aJoken[9] + "&aJoken[10]=" + aJoken[10] + "&aJoken[11]=" + aJoken[11]
		+ "&aJoken[12]=" + aJoken[12] + "&aJoken[13]=" + aJoken[13] + "&aJoken[14]=" + aJoken[14]
		+ "&aJoken[15]=" + aJoken[15] + "&aJoken[16]=" + aJoken[16] + "&aJoken[17]=" + aJoken[17]
		+ "&aJoken[18]=" + aJoken[18] + "&aJoken[19]=" + aJoken[19] + "&aJoken[20]=" + aJoken[20]
		+ "&aJoken[21]=" + aJoken[21] + "&aJoken[22]=" + aJoken[22] + "&aJoken[23]=" + aJoken[23];
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
			document.form.action ="F_FLK0020.php?action=menu&search=1";
			document.form.submit();
		}
	}

	/* Excel出力 */
	function fExcelOut(strClass,strRrceNo){

		var strPrintName;

		if(strClass == "1"){
			strPrintName = "不具合連絡書";
		}else if(strClass == "2"){
			strPrintName = "品質異常改善通知書";
		}else if(strClass == "3"){
			strPrintName = "不良品連絡書";
		}


		//確認メッセージ
		if(window.confirm(strPrintName + 'を出力してもよろしいですか？')){

			document.form.action ="F_FLK0020.php?action=menu&search=1&excel=1&no=" + strRrceNo + "&class=" + strClass;
			document.form.method ="POST";
			document.form.target ="main";
			//document.form.target ="_blank";
			document.form.submit();
		}else{
			return false;
		}
	}


	//チェック処理
	function fCheck(){

		if(!fCalCheckFormat('sCustApAnsDateF','顧客指定回答日(開始)')){
			return false;
		}
		if(!fCalCheckFormat('sCustApAnsDateT','顧客指定回答日(終了)')){
			return false;
		}

		if(!fCalCheckFormat('sApAnsDateF1','指定回答日(社内)開始')){
			return false;
		}
		if(!fCalCheckFormat('sApAnsDateT1','指定回答日(社内)終了')){
			return false;
		}
		if(!fCalCheckFormat('sApAnsDateF2','指定回答日(協工)開始')){
			return false;
		}
		if(!fCalCheckFormat('sApAnsDateT2','指定回答日(協工)終了')){
			return false;
		}

		if(!fCalCheckFormat('sCmpDateF1','完結日(社内)開始')){
			return false;
		}
		if(!fCalCheckFormat('sCmpDateT1','完結日(社内)終了')){
			return false;
		}

		if(!fCalCheckFormat('sCmpDateF2','完結日(協工)開始')){
			return false;
		}
		if(!fCalCheckFormat('sCmpDateT2','完結日(協工)終了')){
			return false;
		}


		//日付整合性チェック
		if(!fCheckDateMatch('sCustApAnsDateF','sCustApAnsDateT','顧客指定回答日(社内)開始','顧客指定回答日(社内)終了')){
			return false;
		}
		if(!fCheckDateMatch('sApAnsDateF1','sApAnsDateT1','指定回答日(社内)開始','指定回答日(社内)終了')){
			return false;
		}
		if(!fCheckDateMatch('sApAnsDateF2','sApAnsDateT2','指定回答日(協工)開始','指定回答日(協工)終了')){
			return false;
		}

		if(!fCheckDateMatch('sCmpDateF1','sCmpDateT1','完結日(社内)開始','完結日(社内)終了')){
			return false;
		}
		if(!fCheckDateMatch('sCmpDateF2','sCmpDateT2','完結日(協工)開始','完結日(協工)開始')){
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



</script>
</HEAD>
<BODY style="border-collapse : separate;" onload=fLoadDisplay();>
<form name="form" method="post" action="" onSubmit="">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000">
      	<SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【不具合情報一覧照会】</SPAN>
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
				<FONT color="#ffffff"><B>不具合管理状況</B></FONT>
			</DIV>
      	</TD>
      	<TD class="tdnone" width="200" align="right" >
      		<INPUT type="button" name="btnReturn" value="　戻　る　" onClick="fReturn()">
      		<?php echo $strManulPath;  ?>
      	</TD>
    </TR>
  </TBODY>
</TABLE>

<TABLE class="tbline" width="400" >

  <TBODY>
    <TR>
      <TD class="tdnone9" align="center" width="100">登録件数</TD>
      <TD class="tdnone9" align="center" width="100">調査中/対策中</TD>
      <TD class="tdnone9" align="center" width="100">有効性確認中</TD>
      <TD class="tdnone9" align="center" width="100">解決済</TD>
	</TR>
	<TR>
		<TD class="tdnone3" align="right" ><?php echo number_format($aRes[0]);?></TD>
		<TD class="tdnone3" align="right" ><?php echo number_format($aRes[1]);?></TD>
		<TD class="tdnone3" align="right" ><?php echo number_format($aRes[2]);?></TD>
		<TD class="tdnone3" align="right" ><?php echo number_format($aRes[4]);?></TD>
	</TR>

  </TBODY>
</TABLE>
<br>

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
<!-- 2019/04/01 ED START -->
<!--<TABLE class="tbline" width="1050" >-->
<TABLE class="tbline" width="1070" >
<!-- 2019/04/01 ED END -->
  <TBODY>
    <TR>
      <TD class="tdnone2"  width="90">進捗状態</TD>
      <TD class="tdnone3"  width="83">
      	<SELECT name="sPgrsStage">
       	<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C01',$sPgrsStage); ?>
      	</SELECT>
      </TD>
      <TD class="tdnone2"  width="71">整理NO</TD>
      <TD class="tdnone3"  width="130">
      	<INPUT name="sRrceNo" size="11" maxlength="10" type="text" style="ime-mode: disabled;" value="<?php echo $sRrceNo; ?>">
      </TD>
      <TD class="tdnone2"  width="110">不具合区分</TD>
      <TD class="tdnone3"  width="163">
      	<SELECT name="sFlawKbn">
       	<OPTION selected value="-1" >全て</OPTION>
<!-- 2019/04/01 ED START -->
        	<?php //$module_sel->fMakeCombo('C15',$sFlawKbn); 
				  $module_sel->fMakeComboS2('085',$sFlawKbn); ?>
<!-- 2019/04/01 ED END -->
      	</SELECT>
      </TD>
      <TD class="tdnone2"  width="81">不具合対策</TD>
      <TD class="tdnone3"  width="150">
      	<SELECT name="sFlawStep">
       	<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C11',$sFlawStep); ?>
      	</SELECT>
      </TD>
    </TR>
    <TR>
      <TD class="tdnone2" ><B>製品CD</B></TD>
      <TD class="tdnone3" ><INPUT size="7" type="text" name="sProdCd" maxlength="8" style="ime-mode: disabled;" value="<?php echo $sProdCd; ?>"></TD>
      <TD class="tdnone2" ><B>製品名</B></TD>
      <TD class="tdnone3" ><INPUT size="15" type="text" name="sProdNm" value="<?php echo $sProdNm; ?>"></TD>
      <TD class="tdnone2" >仕様番号</TD>
      <TD class="tdnone3" ><INPUT size="20" type="text" name="sDrwNo" value="<?php echo $sDrwNo; ?>"></TD>
      <TD class="tdnone2" height="17" width="111">
      	<A href="#" onclick="fOpenSearch('F_MSK0020','sCustCd','','','','','','','0')">顧客CD</A>
      </TD>
      <TD class="tdnone3" height="17" width="93">
      	<INPUT name="sCustCd" size="5" type="text" maxlength="5" style="ime-mode: disabled;" value="<?php echo $sCustCd; ?>">
      </TD>
    </TR>
    <TR>


      <TD class="tdnone2" height="17" width="71">対象部門</TD>
      <TD class="tdnone3" height="17" width="137">
		<SELECT name="sTargetSec">
       	<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C04',$sTargetSec); ?>
      	</SELECT>
      </TD>
      <TD class="tdnone2"  width="111">受付区分</TD>
      	<TD class="tdnone3"  width="93" >
      		<SELECT name="sReceptKbn">
       		<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C03',$sReceptKbn); ?>
      		</SELECT>
      	</TD>

      	<TD class="tdnone2" >結果区分</TD>
    	<TD class="tdnone3"  width="93" >
      		<SELECT name="sResultKbn">
       		<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C09',$sResultKbn); ?>
      		</SELECT>
      	</TD>
		<TD class="tdnone2" >顧客指定回答日</TD>
		<TD class="tdnone3" >
			<INPUT id="sCustApAnsDateF" name="sCustApAnsDateF" size="7" type="text" style="ime-mode: disabled;" value="<?php echo $sCustApAnsDateF; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sCustApAnsDateF", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>～
		    <INPUT size="7" id="sCustApAnsDateT" name="sCustApAnsDateT" type="text" style="ime-mode: disabled;" value="<?php echo $sCustApAnsDateT; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sCustApAnsDateT", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
		</TD>
    </TR>
    <TR>
      <TD class="tdnone2" rowspan="2">発行先区分</TD>
      <TD class="tdnone3" rowspan="2">
      	<SELECT name="sIncidentKbn">
       	<OPTION selected value="-1" >全て</OPTION>
        	<?php $module_sel->fMakeCombo('C05',$sIncidentKbn); ?>
      	</SELECT>
      </TD>
	      	<TD class="tdnone2" >発行先CD(社内)</TD>
		<TD class="tdnone3" ><INPUT size="5" type="text" name="sIncidentCd1" value="<?php echo $sIncidentCd1; ?>"></TD>
		<TD class="tdnone2" >指定回答日(社内)</TD>
		<TD class="tdnone3" >
			<INPUT id="sApAnsDateF1" name="sApAnsDateF1" size="7" type="text" style="ime-mode: disabled;" value="<?php echo $sApAnsDateF1; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sApAnsDateF1", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>～
		    <INPUT size="7" id="sApAnsDateT1" name="sApAnsDateT1" type="text" style="ime-mode: disabled;" value="<?php echo $sApAnsDateT1; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sApAnsDateT1", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
		</TD>
		<TD class="tdnone2" >完結日(社内)</TD>
		<TD class="tdnone3" >
			<INPUT id="sCmpDateF1" name="sCmpDateF1" size="7" type="text" style="ime-mode: disabled;" value="<?php echo $sCmpDateF1; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sCmpDateF1", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>～
		    <INPUT size="7" id="sCmpDateT1" name="sCmpDateT1" type="text" style="ime-mode: disabled;" value="<?php echo $sCmpDateT1; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sCmpDateT1", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
		</TD>


    </TR>
    <TR>
    	<TD class="tdnone2" >発行先CD(協工)</TD>
		<TD class="tdnone3" ><INPUT size="5" type="text" name="sIncidentCd2" value="<?php echo $sIncidentCd2; ?>"></TD>
      		<TD class="tdnone2" >指定回答日(協工)</TD>
		<TD class="tdnone3" >
			<INPUT id="sApAnsDateF2" name="sApAnsDateF2" size="7" type="text" style="ime-mode: disabled;" value="<?php echo $sApAnsDateF2; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sApAnsDateF2", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>～
		    <INPUT size="7" id="sApAnsDateT2" name="sApAnsDateT" type="text" style="ime-mode: disabled;" value="<?php echo $sApAnsDateT2; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sApAnsDateT2", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
		</TD>
		<TD class="tdnone2" >完結日(協工)</TD>
		<TD class="tdnone3" >
			<INPUT id="sCmpDateF2" name="sCmpDateF2" size="7" type="text" style="ime-mode: disabled;" value="<?php echo $sCmpDateF2; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sCmpDateF2", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>～
		    <INPUT size="7" id="sCmpDateT2" name="sCmpDateT2" type="text" style="ime-mode: disabled;" value="<?php echo $sCmpDateT2; ?>">
		    <script type="text/javascript">
				//Ajaxカレンダ読込
				InputCalendar.createOnLoaded("sCmpDateT2", {lang:"ja",ifInvisible: "None",weekFirstDay:ProtoCalendar.SUNDAY});
			</script>
		</TD>
    </TR>

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
	<TD class='tdnone' width='800'>
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

	<TABLE class="tbline" width="1570" >
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

			//進捗状態が解決済の場合はグレー
			if($aPara[$i][30] == "gray"){
				$strClassProgress = "tdnone12";
			}

			//顧客指定回答日期限切れもしくは間近の場合
			if($aPara[$i][31] == "limit"){
				$strClassCustApAns = "tdnone10";
			}elseif($aPara[$i][31] == "near"){
				$strClassCustApAns = "tdnone11";
			}

			//品証指定回答日(社内)期限切れもしくは間近の場合
			if($aPara[$i][32] == "limit"){
				$strClassApAns1 = "tdnone10";
			}elseif($aPara[$i][32] == "near"){
				$strClassApAns1 = "tdnone11";
			}

			//品証指定回答日(協工)期限切れもしくは間近の場合
			if($aPara[$i][33] == "limit"){
				$strClassApAns2 = "tdnone10";
			}elseif($aPara[$i][33] == "near"){
				$strClassApAns2 = "tdnone11";
			}

			//出力ボタン制御
			//1…品質異常改善通知書
			//2…不具合連絡書
			$strButton1 = "";
			$strButton2 = "";

			if($aPara[$i][18] == "0"){
				$strButton2 = "disabled";
			}elseif($aPara[$i][18] == "1"){
				$strButton1 = "disabled";
			}elseif($aPara[$i][18] == "2"){

			}else{
				$strButton1 = "disabled";
				$strButton2 = "disabled";
			}
			//ヘッダーの挿入(20行毎)
			if($iPageCnt%20 == 0){

				echo "<TR height='15'>";
				echo "<TD class='tdnone5' align='center' width='110'><B>アクション</B></TD>";
				echo "<TD class='tdnone2' align='center' width='70'>整理NO</TD>";
			    echo "<TD class='tdnone2' align='center' width='90'>進捗状態</TD>";
			    echo "<TD class='tdnone2' align='center' width='80'>不具合<br>区分</TD>";
			    echo "<TD class='tdnone2' align='center' width='150'>顧客名</TD>";
			    echo "<TD class='tdnone2' align='center' width='100'>製品名</TD>";
			    echo "<TD class='tdnone2' align='center' width='100'>仕様番号</TD>";
			    echo "<TD class='tdnone2' align='center' width='85'>発行先<br>区分</TD>";
			    echo "<TD class='tdnone2' align='center' width='67'>顧客指定<br>回答日</TD>";
			    echo "<TD class='tdnone2' align='center' width='100'>発行先<br>名称(社内)</TD>";
			    echo "<TD class='tdnone2' align='center' width='67'>指定回答<br>(社内)</TD>";
			    echo "<TD class='tdnone2' align='center' width='100'>発行先<br>名称(協工)</TD>";
			    echo "<TD class='tdnone2' align='center' width='67'>指定回答<br>(協工)</TD>";
			    echo "<TD class='tdnone5' align='center' width='80'><B>不具合<br>連絡書</B></TD>";
			    echo "<TD class='tdnone5' align='center' width='80'><B>品質異常<br>改善通知書</B></TD>";
			    echo "<TD class='tdnone5' align='center' width='80'><B>協工不良品<br>連絡書</B></TD>";
				echo "</TR>	";
			}

			echo "<TR height='15'>";
		    echo "<TD class='".$strClass."' align='center' >";
	    	echo "<INPUT type='button' value='更新' style='background-color : #fdc257;' onClick='fFlawDisp(\"2\",\"".$aPara[$i][0]."\");'>";
	    	echo "<INPUT type='button' value='削除' style='background-color : #fdc257;' onClick='fFlawDisp(\"3\",\"".$aPara[$i][0]."\");'>";
	    	echo "<INPUT type='button' value='ﾄﾚｰｽ' style='background-color : #fdc257;' onClick='fFlawStepDisp(\"".$aPara[$i][0]."\");'>";
	    	echo "</TD>";
		    echo "<TD class='".$strClassProgress."'>".$aPara[$i][0]."</TD>";
     	 	echo "<TD class='".$strClassProgress."'>".$aPara[$i][1]."</TD>";
     	 	echo "<TD class='".$strClass."'>".$aPara[$i][3]."</TD>";
     	 	echo "<TD class='".$strClass."'>".$aPara[$i][2]."</TD>";
		    echo "<TD class='".$strClass."'>".$aPara[$i][4]."</TD>";
		    echo "<TD class='".$strClass."'>".$aPara[$i][5]."</TD>";
		    //echo "<TD class='".$strClass."'>".$aPara[$i][6]."</TD>";
		    echo "<TD class='".$strClass."'>".$aPara[$i][13]."</TD>";
		    echo "<TD class='".$strClassCustApAns."'>".$module_cmn->fChangDateFormat($aPara[$i][9])."</TD>";
		    echo "<TD class='".$strClass."'>".$aPara[$i][14]."</TD>";
		    echo "<TD class='".$strClassApAns1."'>".$module_cmn->fChangDateFormat($aPara[$i][10])."</TD>";
		    echo "<TD class='".$strClass."'>".$aPara[$i][15]."</TD>";
		    echo "<TD class='".$strClassApAns2."'>".$module_cmn->fChangDateFormat($aPara[$i][11])."</TD>";
		    echo "<TD class='".$strClass."' align='center' >";
		    echo "<INPUT type='button' value='出　力' style='background-color : #fdc257;' onClick='fExcelOut(\"1\",\"".$aPara[$i][0]."\");'>";
		    echo "</TD>";
		    echo "<TD class='".$strClass."' align='center' >";
		    echo "<INPUT type='button' ".$strButton1." value='出　力' style='background-color : #fdc257;' onClick='fExcelOut(\"2\",\"".$aPara[$i][0]."\");'>";
		    echo "</TD>";
		    echo "<TD class='".$strClass."' align='center' >";
		    echo "<INPUT type='button' ".$strButton2." value='出　力' style='background-color : #fdc257;' onClick='fExcelOut(\"3\",\"".$aPara[$i][0]."\");'>";
		    echo "</TD>";
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