<?php
	//****************************************************************************
	//プログラム名：不具合管理台帳出力(出力部)
	//プログラムID：F_FLK0042
	//作成者　　　：㈱鈴木　西村
	//作成日　　　：2012/08/21
	//履歴　　　　：2012/08/21 新規
	//   　　　　：2019/04/01 不具合区分変更（SMART2より取得）　藤田
	//
	//****************************************************************************

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

	//Oracleへの接続の確立
	//OCILogon(ユーザ名,パスワード,データベース名)
	$conn = oci_connect($module_sel->gUserID, $module_sel->gPass, $module_sel->gDB);

	// データを検索
	$sql = "";
	$sql = $sql."select F.C_REFERENCE_NO"."\n";
	$sql = $sql."      ,F.V2_CUST_MANAGE_NO"."\n";
	//$sql = $sql."      ,F.C_CUST_CD"."\n";
	$sql = $sql."      ,C3.V2_CUST_NM_R as C_CUST_CD"."\n";
	$sql = $sql."      ,F.N_INS_YMD"."\n";
	$sql = $sql."      ,F.V2_DRW_NO"."\n";
	$sql = $sql."      ,K03.V2_KBN_MEI_NM as C_RECEPT_KBN"."\n";
	$sql = $sql."      ,F.V2_PROD_NM"."\n";
	$sql = $sql."      ,F.C_DIE_NO"."\n";
	$sql = $sql."      ,F.V2_LOT_NO"."\n";
//2019/04/01 AD START T.FUJITA
//	$sql = $sql."      ,K15.V2_KBN_MEI_NM as C_FLAW_KBN"."\n";
	$sql = $sql."      ,F.C_FLAW_KBN as C_FLAW_KBN"."\n";
//2019/04/01 AD START T.FUJITA
	$sql = $sql."      ,F.N_TARGET_QTY"."\n";
	$sql = $sql."      ,F.N_RETURN_QTY"."\n";
	$sql = $sql."      ,K08.V2_KBN_MEI_NM as C_RETURN_DISPOSAL"."\n";
	$sql = $sql."      ,F.N_BAD_QTY"."\n";
	$sql = $sql."      ,F.N_CUST_AP_ANS_YMD"."\n";
	$sql = $sql."      ,F.N_ANS_YMD"."\n";
	$sql = $sql."      ,T1.V2_TANTO_NM as C_ANS_TANTO_CD"."\n";
	$sql = $sql."      ,K09.V2_KBN_MEI_NM as C_RESULT_KBN"."\n";
	$sql = $sql."      ,C1.V2_CUST_NM_R as V2_INCIDENT_CD1"."\n";
	$sql = $sql."      ,F.N_ISSUE_YMD2"."\n";
	$sql = $sql."      ,F.N_PC_AP_ANS_YMD1"."\n";
	$sql = $sql."      ,F.N_RETURN_YMD1"."\n";
	$sql = $sql."      ,F.N_COMPLETE_YMD1"."\n";
	$sql = $sql."      ,T3.V2_TANTO_NM as C_CONFIRM_TANTO_CD1"."\n";
	$sql = $sql."      ,C2.V2_CUST_NM_R as V2_INCIDENT_CD2"."\n";
	$sql = $sql."      ,F.N_ISSUE_YMD3"."\n";
	$sql = $sql."      ,F.N_PC_AP_ANS_YMD2"."\n";
	$sql = $sql."      ,F.N_RETURN_YMD2"."\n";
	$sql = $sql."      ,F.N_COMPLETE_YMD2"."\n";
	$sql = $sql."      ,T2.V2_TANTO_NM as C_CONFIRM_TANTO_CD2"."\n";
	$sql = $sql."      ,F.C_INCIDENT_KBN "."\n";
	$sql = $sql."  from T_TR_FLAW F"."\n";
	$sql = $sql."      ,T_TR_ACTION_H A"."\n";
//	$sql = $sql."      ,T_MS_FL_KBN K15"."\n";		//2019/04/01 AD START T.FUJITA
	$sql = $sql."      ,T_MS_FL_KBN K08"."\n";
	$sql = $sql."      ,T_MS_FL_KBN K09"."\n";
	$sql = $sql."      ,T_MS_FL_KBN K03"."\n";
	$sql = $sql."      ,V_FL_TANTO_INFO T1"."\n";
	$sql = $sql."      ,V_FL_TANTO_INFO T2"."\n";
	$sql = $sql."      ,V_FL_TANTO_INFO T3"."\n";
	$sql = $sql."      ,V_FL_CUST_INFO C1"."\n";
	$sql = $sql."      ,V_FL_CUST_INFO C2"."\n";
	$sql = $sql."      ,V_FL_CUST_INFO C3"."\n";
	$sql = $sql." where 1 = 1"."\n";
	$sql = $sql."   and F.C_REFERENCE_NO      = A.C_REFERENCE_NO(+)"."\n";
//2019/04/01 AD START T.FUJITA
	//$sql = $sql."   and F.C_FLAW_KBN          = K15.V2_KBN_MEI_CD(+)"."\n";
	//$sql = $sql."   and K15.V2_KBN_CD(+)      = 'C15'"."\n";
//2019/04/01 AD END T.FUJITA
	$sql = $sql."   and trim(F.C_RETURN_DISPOSAL)   = K08.V2_KBN_MEI_CD(+)"."\n";
	$sql = $sql."   and K08.V2_KBN_CD(+)      = 'C08'"."\n";
	$sql = $sql."   and trim(F.C_RESULT_KBN)        = K09.V2_KBN_MEI_CD(+)"."\n";
	$sql = $sql."   and K09.V2_KBN_CD(+)      = 'C09'"."\n";
	$sql = $sql."   and F.C_RECEPT_KBN        = K03.V2_KBN_MEI_CD(+)"."\n";
	$sql = $sql."   and K03.V2_KBN_CD(+)      = 'C03'"."\n";
	$sql = $sql."   and F.C_ANS_TANTO_CD      = T1.C_TANTO_CD(+)"."\n";
	$sql = $sql."   and F.C_CONFIRM_TANTO_CD2 = T2.C_TANTO_CD(+)"."\n";
	$sql = $sql."   and F.C_CONFIRM_TANTO_CD1 = T3.C_TANTO_CD(+)"."\n";
	$sql = $sql."   and F.V2_INCIDENT_CD1     = C1.C_CUST_CD(+)"."\n";
	$sql = $sql."   and F.V2_INCIDENT_CD2     = C2.C_CUST_CD(+)"."\n";
	$sql = $sql."   and F.C_CUST_CD           = C3.C_CUST_CD(+)"."\n";
	$sql = $sql."   and F.N_DEL_FLG           = 0"."\n";
	$sql = $sql."   and F.N_INS_YMD between ".str_replace('/','',$sInsYmdF)."000000 and ".str_replace('/','',$sInsYmdT)."235959\n";

	if($sCustCd <> ""){
		//顧客コード
		$sql = $sql."   and F.C_CUST_CD = '".$sCustCd."'"."\n" ;
	}
	if($sTargetSec <> "-1"){
		//対象部門
		$sql = $sql."   and F.C_TARGET_SECTION_KBN = '".$sTargetSec."'"."\n" ;
	}
	if($sPgrsStage <> "-1"){
		//進捗状況
		$sql = $sql."   and F.C_PROGRES_STAGE = '".$sPgrsStage."'"."\n" ;
	}
	if($sFlawStep <> "-1"){
		//不具合区分
		$sql = $sql."   and F.C_FLAW_KBN = '".$sFlawStep."'"."\n" ;
	}
	if($sPcApAnsDateF <> ""){
		//品証指定回答日(社内)
		$sql = $sql."   and F.N_PC_AP_ANS_YMD1 between ".str_replace('/','',$sPcApAnsDateF)." and ".str_replace('/','',$sPcApAnsDateT)."\n";
	}
	if($sApAnsDateF <> ""){
		//品証指定回答日(協工)
		$sql = $sql."   and F.N_PC_AP_ANS_YMD2 between ".str_replace('/','',$sApAnsDateF)." and ".str_replace('/','',$sApAnsDateT)."\n";
	}
	if($chkKaito=='1'){
		//顧客指定回答日
		//未回答のみをチェックした場合、未入力のみの条件とする
		$sql = $sql."   and F.N_CUST_AP_ANS_YMD = 0 \n";
	}elseif($sCustApAnsDateF <> ""){
		//上記以外で日付が入っている場合
		$sql = $sql."   and F.N_CUST_AP_ANS_YMD between ".str_replace('/','',$sCustApAnsDateF)." and ".str_replace('/','',$sCustApAnsDateT)."\n";
	}

	if($sResultKbn <> "-1"){
		//結果区分
		$sql = $sql."   and F.C_RESULT_KBN = '".$sResultKbn."'"."\n" ;
	}
	if($sResultKbn <> "-1"){
		//全ての対策の有効性
		$sql = $sql."   and A.C_ALL_ACTION_VALIDITY = '".$sValidKbn."'"."\n" ;
	}
	$sql = $sql." order by F.N_INS_YMD,F.C_REFERENCE_NO";


	//echo $sql;
	//SQLをSJISに変換(DB)
    $sql = $module_cmn->fChangSJIS_SQL($sql);

    //SQLの実行
	$stmt = oci_parse($conn, $sql);
	oci_execute($stmt,OCI_DEFAULT);

	$iRows = oci_fetch_all($stmt,$results);

	oci_execute($stmt,OCI_DEFAULT);

	$hitflag = false;
	$iCnt = 0;
	$iStartPosCol = 0;
	$iStartPosRow = 6;

	if($iRows <> 0){
		$iRows = $iRows -1;
		// 必要なクラスをインクルードする
		/** パスの設定（PHPExcel.phpまで届くようにパスを設定します） **/
		//set_include_path(get_include_path() .'/Classes');
		/** PHPExcel ここでPHPExcel.phpを相対パスで直接指定すれば上のパスの設定はなくても大丈夫です。*/
		// 'PHPExcel.php';
		//include 'PHPExcel/Writer/Excel2007.php';

		//require_once '/Classes/PHPExcel.php';
		//require_once '/Classes/PHPExcel/IOFactory.php';

		//入出力ファイルの切替
		//$strImportFile = mb_convert_encoding("不具合管理台帳_雛型.xls","SJIS","UTF-8");
		//$strExportFile = mb_convert_encoding("不具合管理台帳.xls","SJIS","UTF-8");
		$strImportFile = mb_convert_encoding("不具合管理台帳_雛型.xlsx","SJIS","UTF-8");
		$strExportFile = mb_convert_encoding("不具合管理台帳.xlsx","SJIS","UTF-8");
		
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

		//テンプレートの読み込み
		//$objReader = PHPExcel_IOFactory::createReader("Excel5");
		//$xl = $objReader->load("template/".$strImportFile);

		//シートの設定
		//$xl->setActiveSheetIndex(0);
		//$sheet = $xl->getActiveSheet();
		//$sheet->insertNewRowBefore($iStartPosRow+1, $iRows);
		$spreadsheet->setActiveSheetIndex(0);
		$sheet = $spreadsheet->getActiveSheet();
		
		//出力用バッファをクリア(消去)し、出力のバッファリングをオフにする
		ob_end_clean();
		//出力のバッファリングを有効にする
		ob_start();
		
		//行挿入
		$sheet->insertNewRowBefore($iStartPosRow+1,$iRows);
		//1行削除
		$sheet->removeRow($iStartPosRow,1);
		
		//1行増やす
/* 		for($col=0;$col<29;$col++) {
			for($row=$iStartPosRow;$row<$iStartPosRow + $iRows+1;$row++) {
				// セルを取得
				$cell = $sheet->getCellByColumnAndRow($col, $row);
				// セルスタイルを取得
				$style = $sheet->getStyleByColumnAndRow($col, $row);
				// 数値から列文字列に変換する (0,1) → A1
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex($col + $colOffset) . (string)($row + $rowOffset);
				// セル値をコピー
				$sheet->setCellValue($offsetCell, $cell->getValue());
				// スタイルをコピー
				//$sheet->duplicateStyle($style, $offsetCell);
			}
		} */

		while (oci_fetch($stmt)) {
			$hitflag = true;
			// データの取得
			$rec_C_REFERENCE_NO = oci_result($stmt, 'C_REFERENCE_NO');
			$rec_V2_CUST_MANAGE_NO = oci_result($stmt, 'V2_CUST_MANAGE_NO');
			//顧客管理Noが未入力の場合はハイフン 2017/10/02 k.kume 品証からの要望
			if($rec_V2_CUST_MANAGE_NO == ""){
				$rec_V2_CUST_MANAGE_NO = "―";
			}
			$rec_C_CUST_CD = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD'));
			$rec_N_INS_YMD = oci_result($stmt, 'N_INS_YMD');
			$rec_C_RECEPT_KBN = $module_cmn->fChangUTF8(oci_result($stmt, 'C_RECEPT_KBN'));
			$rec_V2_DRW_NO = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));
			$rec_V2_PROD_NM = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));
			$rec_C_DIE_NO = $module_cmn->fChangUTF8(oci_result($stmt, 'C_DIE_NO'));
			$rec_V2_LOT_NO = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_LOT_NO'));
//2019/04/01 AD START T.FUJITA
//			$rec_C_FLAW_KBN = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN'));
			$rec_C_FLAW_KBN = $module_sel->fDispKbnS2("085",$module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN')));
//2019/04/01 AD END T.FUJITA
			$rec_N_TARGET_QTY = oci_result($stmt, 'N_TARGET_QTY');
			$rec_N_RETURN_QTY = oci_result($stmt, 'N_RETURN_QTY');
			$rec_C_RETURN_DISPOSAL = $module_cmn->fChangUTF8(oci_result($stmt, 'C_RETURN_DISPOSAL'));
			$rec_N_BAD_QTY = oci_result($stmt, 'N_BAD_QTY');
			if(oci_result($stmt, 'N_CUST_AP_ANS_YMD') <> "0"){
				$rec_N_CUST_AP_ANS_YMD = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_CUST_AP_ANS_YMD')),5,5);
				if(oci_result($stmt, 'N_ANS_YMD') <> "0"){
					$rec_N_ANS_YMD = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_ANS_YMD')),5,5);
				}else{
					$rec_N_ANS_YMD = "";
				}
				$rec_C_ANS_TANTO_CD = $module_cmn->fChangUTF8(oci_result($stmt, 'C_ANS_TANTO_CD'));
			}else{
				$rec_N_CUST_AP_ANS_YMD = "回答不要";
				$rec_N_ANS_YMD = "―";
				$rec_C_ANS_TANTO_CD = "―";
			}
			
			$rec_C_RESULT_KBN = $module_cmn->fChangUTF8(oci_result($stmt, 'C_RESULT_KBN'));
			$rec_V2_INCIDENT_CD1 = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INCIDENT_CD1'));
			if(oci_result($stmt, 'N_ISSUE_YMD2')<>"0"){
				$rec_N_ISSUE_YMD2 = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_ISSUE_YMD2')),5,5);
			}else{
				$rec_N_ISSUE_YMD2 = "";
			}
			if(oci_result($stmt, 'N_PC_AP_ANS_YMD1')<>"0"){
				$rec_N_PC_AP_ANS_YMD1 = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_PC_AP_ANS_YMD1')),5,5);
			}else{
				$rec_N_PC_AP_ANS_YMD1 = "";
			}
			if(oci_result($stmt, 'N_RETURN_YMD1')<>"0"){
				$rec_N_RETURN_YMD1 = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_RETURN_YMD1')),5,5);
			}else{
				$rec_N_RETURN_YMD1 = "";
			}
			if(oci_result($stmt, 'N_COMPLETE_YMD1')<>"0"){
				$rec_N_COMPLETE_YMD1 = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_COMPLETE_YMD1')),5,5);
			}else{
				$rec_N_COMPLETE_YMD1 = "";
			}
			$rec_C_CONFIRM_TANTO_CD1 = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CONFIRM_TANTO_CD1'));
			$rec_V2_INCIDENT_CD2 = str_replace("　","",trim($module_cmn->fChangUTF8(oci_result($stmt, 'V2_INCIDENT_CD2'))));
			if(oci_result($stmt, 'N_ISSUE_YMD3')<>"0"){
				$rec_N_ISSUE_YMD3 = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_ISSUE_YMD3')),5,5);
			}else{
				$rec_N_ISSUE_YMD3 = "";
			}
			if(oci_result($stmt, 'N_PC_AP_ANS_YMD2')<>"0"){
				$rec_N_PC_AP_ANS_YMD2 = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_PC_AP_ANS_YMD2')),5,5);
			}else{
				$rec_N_PC_AP_ANS_YMD2 = "";
			}
			if(oci_result($stmt, 'N_RETURN_YMD2')<>"0"){
				$rec_N_RETURN_YMD2 = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_RETURN_YMD2')),5,5);
			}else{
				$rec_N_RETURN_YMD2 = "";
			}
			if(oci_result($stmt, 'N_COMPLETE_YMD2')<>"0"){
				$rec_N_COMPLETE_YMD2 = substr($module_cmn->fChangDateFormat(oci_result($stmt, 'N_COMPLETE_YMD2')),5,5);
			}else{
				$rec_N_COMPLETE_YMD2 = "";
			}
			$rec_C_CONFIRM_TANTO_CD2 = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CONFIRM_TANTO_CD2'));
			$rec_C_INCIDENT_KBN = $module_cmn->fChangUTF8(oci_result($stmt, 'C_INCIDENT_KBN'));

/* 			for($col=0;$col<30;$col++) {
				$sheet->getStyleByColumnAndRow($col, $iStartPosRow + $iCnt)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
			} */

/* 			//セルの記入
			//整理ＮＯ
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(0) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_C_REFERENCE_NO);
			//顧客管理ＮＯ
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(1) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_V2_CUST_MANAGE_NO);
			//ユーザコード
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(2) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_C_CUST_CD);
			//受領日
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(3) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, substr($rec_N_INS_YMD,0,4)."/".substr($rec_N_INS_YMD,4,2)."/".substr($rec_N_INS_YMD,6,2));
			//受付区分
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(4) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_C_RECEPT_KBN);
			//仕様番号
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(5) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_V2_DRW_NO);
			//品名
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(6) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_V2_PROD_NM);
			//金型番号
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(7) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_C_DIE_NO);
			//ロット番号
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(8) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_V2_LOT_NO);
			//不具合内容
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(9) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_C_FLAW_KBN);
			//対象数量
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(10) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, number_format($rec_N_TARGET_QTY));
			//返却数量(返却品)
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(11) . (string)($iStartPosRow + $iCnt);
			if($rec_N_RETURN_QTY == "0"){
				$sheet->setCellValue($offsetCell, '-');
			}else{
				$sheet->setCellValue($offsetCell, number_format($rec_N_RETURN_QTY));
			}
			//処理(返却品)
			if($rec_C_RETURN_DISPOSAL == ""){
				$rec_C_RETURN_DISPOSAL = '-';
			}
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(12) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_C_RETURN_DISPOSAL);
			//不良数(返却品)
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(13) . (string)($iStartPosRow + $iCnt);
			if($rec_N_BAD_QTY == "0"){
				$sheet->setCellValue($offsetCell, '-');
			}else{
				$sheet->setCellValue($offsetCell, number_format($rec_N_BAD_QTY));
			}

			//顧客指定回答日(回答)
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(14) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_N_CUST_AP_ANS_YMD);
			//回答日(回答)
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(15) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_N_ANS_YMD);
			//回答者(回答)
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(16) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_C_ANS_TANTO_CD);
			//区分(調査結果)
			$offsetCell = PHPExcel_Cell::stringFromColumnIndex(17) . (string)($iStartPosRow + $iCnt);
			$sheet->setCellValue($offsetCell, $rec_C_RESULT_KBN);
			if($rec_C_INCIDENT_KBN == "0" || $rec_C_INCIDENT_KBN == "2"){
				//社内用

				//課名(社内)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(18) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_V2_INCIDENT_CD1);
				//発行日(社内)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(19) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_N_ISSUE_YMD2);
				//品証指定回答日(社内)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(20) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_N_PC_AP_ANS_YMD1);
				//返却日(社内)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(21) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_N_RETURN_YMD1);
				//完結日(社内)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(22) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_N_COMPLETE_YMD1);
				//確認者(社内)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(23) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_C_CONFIRM_TANTO_CD1);
			}else{
				//斜線を引く
				for($col=18;$col<24;$col++) {
					//$sheet->getStyleByColumnAndRow($col, $iStartPosRow + $iCnt)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_SLANTDASHDOT);
					$offsetCell = PHPExcel_Cell::stringFromColumnIndex($col) . (string)($iStartPosRow + $iCnt);
					$sheet->setCellValue($offsetCell, "-");
				}
			}
			if($rec_C_INCIDENT_KBN == "1" || $rec_C_INCIDENT_KBN == "2"){
				//協力会社用
				//協力工場(社外)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(24) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_V2_INCIDENT_CD2);
				//発行日(社外)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(25) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_N_ISSUE_YMD3);
				//品証指定回答日(社外)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(26) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_N_PC_AP_ANS_YMD2);
				//返却日(社外)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(27) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_N_RETURN_YMD2);
				//完結日(社外)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(28) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_N_COMPLETE_YMD2);
				//確認者(社外)
				$offsetCell = PHPExcel_Cell::stringFromColumnIndex(29) . (string)($iStartPosRow + $iCnt);
				$sheet->setCellValue($offsetCell, $rec_C_CONFIRM_TANTO_CD2);
			}else{
				//斜線を引く
				for($col=24;$col<30;$col++) {
					//$sheet->getStyleByColumnAndRow($col, $iStartPosRow + $iCnt)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_SLANTDASHDOT);
					$offsetCell = PHPExcel_Cell::stringFromColumnIndex($col) . (string)($iStartPosRow + $iCnt);
					$sheet->setCellValue($offsetCell, "-");
				}
			}
			$iCnt++;
		} */
			//セルの記入
			//整理ＮＯ
			$sheet->setCellValueByColumnAndRow(1,$iStartPosRow + $iCnt,$rec_C_REFERENCE_NO);
			//顧客管理ＮＯ
			$sheet->setCellValueByColumnAndRow(2,$iStartPosRow + $iCnt,$rec_V2_CUST_MANAGE_NO);
			//ユーザコード
			$sheet->setCellValueByColumnAndRow(3,$iStartPosRow + $iCnt,$rec_C_CUST_CD);
			//受領日
			$sheet->setCellValueByColumnAndRow(4,$iStartPosRow + $iCnt,substr($rec_N_INS_YMD,0,4)."/".substr($rec_N_INS_YMD,4,2)."/".substr($rec_N_INS_YMD,6,2));
			//受付区分
			$sheet->setCellValueByColumnAndRow(5,$iStartPosRow + $iCnt,$rec_C_RECEPT_KBN);
			//仕様番号
			$sheet->setCellValueByColumnAndRow(6,$iStartPosRow + $iCnt,$rec_V2_DRW_NO);
			//品名
			$sheet->setCellValueByColumnAndRow(7,$iStartPosRow + $iCnt,$rec_V2_PROD_NM);
			//金型番号
			$sheet->setCellValueByColumnAndRow(8,$iStartPosRow + $iCnt,$rec_C_DIE_NO);
			//ロット番号
			$sheet->setCellValueByColumnAndRow(9,$iStartPosRow + $iCnt,$rec_V2_LOT_NO);
			//不具合内容
			$sheet->setCellValueByColumnAndRow(10,$iStartPosRow + $iCnt,$rec_C_FLAW_KBN);
			//対象数量
			$sheet->setCellValueByColumnAndRow(11,$iStartPosRow + $iCnt,number_format($rec_N_TARGET_QTY));
			//処理(返却数量)
			if($rec_N_RETURN_QTY == "0"){
				$sheet->setCellValueByColumnAndRow(12,$iStartPosRow + $iCnt,"-");
			}else{
				$sheet->setCellValueByColumnAndRow(12,$iStartPosRow + $iCnt,number_format($rec_N_RETURN_QTY));
			}
			//処理(返却品)
			if($rec_C_RETURN_DISPOSAL == ""){
				$rec_C_RETURN_DISPOSAL = '-';
			}
			$sheet->setCellValueByColumnAndRow(13,$iStartPosRow + $iCnt,$rec_C_RETURN_DISPOSAL);
			//不良数(返却品)
			if($rec_N_BAD_QTY == "0"){
				$sheet->setCellValueByColumnAndRow(14,$iStartPosRow + $iCnt,"-");
			}else{
				$sheet->setCellValueByColumnAndRow(14,$iStartPosRow + $iCnt,number_format($rec_N_BAD_QTY));
			}
			//顧客指定回答日(回答)
			$sheet->setCellValueByColumnAndRow(15,$iStartPosRow + $iCnt,$rec_N_CUST_AP_ANS_YMD);
			//回答日(回答)
			$sheet->setCellValueByColumnAndRow(16,$iStartPosRow + $iCnt,$rec_N_ANS_YMD);
			//回答者(回答)
			$sheet->setCellValueByColumnAndRow(17,$iStartPosRow + $iCnt,$rec_C_ANS_TANTO_CD);
			//区分(調査結果)
			$sheet->setCellValueByColumnAndRow(18,$iStartPosRow + $iCnt,$rec_C_RESULT_KBN);
			
			if($rec_C_INCIDENT_KBN == "0" || $rec_C_INCIDENT_KBN == "2"){
				//社内用
				//課名(社内)
				$sheet->setCellValueByColumnAndRow(19,$iStartPosRow + $iCnt,$rec_V2_INCIDENT_CD1);
				//発行日(社内)
				$sheet->setCellValueByColumnAndRow(20,$iStartPosRow + $iCnt,$rec_N_ISSUE_YMD2);
				//品証指定回答日(社内)
				$sheet->setCellValueByColumnAndRow(21,$iStartPosRow + $iCnt,$rec_N_PC_AP_ANS_YMD1);
				//返却日(社内)
				$sheet->setCellValueByColumnAndRow(22,$iStartPosRow + $iCnt,$rec_N_RETURN_YMD1);
				//完結日(社内)
				$sheet->setCellValueByColumnAndRow(23,$iStartPosRow + $iCnt,$rec_N_COMPLETE_YMD1);
				//確認者(社内)
				$sheet->setCellValueByColumnAndRow(24,$iStartPosRow + $iCnt,$rec_C_CONFIRM_TANTO_CD1);
			}else{
				$sheet->setCellValueByColumnAndRow(19,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(20,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(21,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(22,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(23,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(24,$iStartPosRow + $iCnt,"-");
			}
			if($rec_C_INCIDENT_KBN == "1" || $rec_C_INCIDENT_KBN == "2"){
				//協力会社用
				//協力工場(社外)
				$sheet->setCellValueByColumnAndRow(25,$iStartPosRow + $iCnt,$rec_V2_INCIDENT_CD2);
				//発行日(社外)
				$sheet->setCellValueByColumnAndRow(26,$iStartPosRow + $iCnt,$rec_N_ISSUE_YMD3);
				//品証指定回答日(社外)
				$sheet->setCellValueByColumnAndRow(27,$iStartPosRow + $iCnt,$rec_N_PC_AP_ANS_YMD2);
				//返却日(社外)
				$sheet->setCellValueByColumnAndRow(28,$iStartPosRow + $iCnt,$rec_N_RETURN_YMD2);
				//完結日(社外)
				$sheet->setCellValueByColumnAndRow(29,$iStartPosRow + $iCnt,$rec_N_COMPLETE_YMD2);
				//確認者(社外)
				$sheet->setCellValueByColumnAndRow(30,$iStartPosRow + $iCnt,$rec_C_CONFIRM_TANTO_CD2);
			}else{
				$sheet->setCellValueByColumnAndRow(25,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(26,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(27,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(28,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(29,$iStartPosRow + $iCnt,"-");
				$sheet->setCellValueByColumnAndRow(30,$iStartPosRow + $iCnt,"-");
			}
			$iCnt++;
		}
/* 		for($col=0;$col<30;$col++) {
			for($row=1;$row<$iStartPosRow;$row++) {
				$sheet->getStyleByColumnAndRow($col, $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
			}
		} */
/* 		for($col=0;$col<30;$col++) {
			//最終行に罫線を引く
			$sheet->getStyleByColumnAndRow($col, $iStartPosRow+$iRows)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		} */
		//最終行に罫線を引く
		$borders = $sheet->getStyle('A'.($iStartPosRow + $iCnt-1).':AD'.($iStartPosRow + $iCnt-1))->getBorders();
		$borders ->getBottom()->setBorderStyle('medium');
		
		//アクティブセル設定
		$sheet->getStyle('A1');
		
		//Excel5形式で保存
		//$writer = PHPExcel_IOFactory::createWriter($xl, 'Excel5');
		//$writer->save('php://output');

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet,"Xlsx");
		//$writer->setOffice2003Compatibility(true);
		$writer->save('php://output');
		exit;
	}
	//リソース開放
	oci_free_statement($stmt);
	//Oracle接続切断
	oci_close($conn);

	//検索結果が存在した場合
	if($hitflag){
		//PDF出力
		$pdf->Output();
	}else{
		//検索結果がない場合の画面用意
		$strMsg = $module_sel->fMsgSearch("N006","");
	}
?>
<HTML>
<HEAD>
<META name="GENERATOR" content="IBM WebSphere Studio Homepage Builder Version 11.0.0.0 for Windows">
<META http-equiv="Content-Style-Type" content="text/css">
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<TITLE></TITLE>
<link rel="stylesheet" type="text/css" href="css/common.css">
<LINK rel="stylesheet" href="../css/common.css" type="text/css">
<LINK rel="stylesheet" type="text/css" href="table.css" id="_HPB_TABLE_CSS_ID_">
</HEAD>
<BODY style="font-size : medium;border-collapse : separate;">
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
    <TR  >
      <TD class="tdnone" align="center" width="1000"  ><B><FONT color="#ff0000" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
    </TR>
  </TBODY>
</TABLE>
<?php
}
?>
</BODY>
</HTML>
