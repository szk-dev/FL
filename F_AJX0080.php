<?php
	//****************************************************************************
	//プログラム名：赤伝緑伝情報入力画面数量制御
	//プログラムID：F_AJX0080
	//作成者		：株鈴木　藤田
	//作成日		：2019/04/01
	//履歴		:2019/05/13 単価入れると自動で不具合金額が出るように修正 藤田
	//
	//
	//****************************************************************************

	//ファイル読み込み
	require_once("module_sel.php");
	require_once("JSON/JSON.php");

	// オブジェクト作成
	$module_sel = new module_sel;
	$json = new Services_JSON;

	$aPara = array();

	//引数の取得
	//処理区分：0:区分選択時処理 1:数量入力時処理
	if(isset($_GET['ajx'])) {
		$type = $_GET['ajx'];
	}else{
		$type = "";
	}
	
	$txtFailure_QTY = 0;
	$txtDisposal_QTY = 0;
	$txtReturn_QTY = 0;
	$txtLoss_QTY = 0;
	$txtExclud_QTY = 0;
	$txtFlawLot_QTY = 0;
	$txtFailurePrice = 0;
	$txtDisposalPrice = 0;
	$txtReturnPrice = 0;
	$txtLossPrice = 0;
	$txtExcludPrice = 0;
	$txtFlawPrice = 0;
	$txtUnitPrice = 0;

	if($_POST['txtFailure_QTY'] <> "") {
		$txtFailure_QTY = str_replace(",","",$_POST['txtFailure_QTY']);
	}else{
		$txtFailure_QTY = 0;
	}
	if($_POST['txtDisposal_QTY'] <> "") {
		$txtDisposal_QTY = str_replace(",","",$_POST['txtDisposal_QTY']);
	}else{
		$txtDisposal_QTY = 0;
	}
	if($_POST['txtReturn_QTY'] <> "") {
		$txtReturn_QTY = str_replace(",","",$_POST['txtReturn_QTY']);
	}else{
		$txtReturn_QTY = 0;
	}
	if($_POST['txtLoss_QTY'] <> "") {
		$txtLoss_QTY = str_replace(",","",$_POST['txtLoss_QTY']);
	}else{
		$txtLoss_QTY = 0;
	}
	if($_POST['txtExclud_QTY'] <> "") {
		$txtExclud_QTY = str_replace(",","",$_POST['txtExclud_QTY']);
	}else{
		$txtExclud_QTY = 0;
	}
	if($_POST['txtFlawLot_QTY'] <> "") {
		$txtFlawLot_QTY = str_replace(",","",$_POST['txtFlawLot_QTY']);
	}else{
		$txtFlawLot_QTY = 0;
	}
	if($_POST['txtFailurePrice'] <> "") {
		$txtFailurePrice = str_replace(",","",$_POST['txtFailurePrice']);
	}else{
		$txtFailurePrice = 0;
	}
	if($_POST['txtDisposalPrice'] <> "") {
		$txtDisposalPrice = str_replace(",","",$_POST['txtDisposalPrice']);
	}else{
		$txtDisposalPrice = 0;
	}
	if($_POST['txtReturnPrice'] <> "") {
		$txtReturnPrice = str_replace(",","",$_POST['txtReturnPrice']);
	}else{
		$txtReturnPrice = 0;
	}
 	if($_POST['txtLossPrice'] <> "") {
		$txtLossPrice = str_replace(",","",$_POST['txtLossPrice']);
	}else{
		$txtLossPrice = 0;
	}
	if($_POST['txtExcludPrice'] <> "") {
		$txtExcludPrice = str_replace(",","",$_POST['txtExcludPrice']);
	}else{
		$txtExcludPrice = 0;
	}
	if($_POST['txtFlawPrice'] <> "") {
		$txtFlawPrice = str_replace(",","",$_POST['txtFlawPrice']);
	}else{
		$txtFlawPrice = 0;
	}
	if($_POST['txtUnitPrice'] <> "") {
		$txtUnitPrice = str_replace(",","",$_POST['txtUnitPrice']);
	}else{
		$txtUnitPrice = 0;
	} 

	$aPara[0][0] = false;
	$aPara[0][1] = false;
	$aPara[0][2] = false;
	$aPara[0][3] = false;
	$aPara[0][4] = false;
	$aPara[1][0] = "";
	$aPara[1][1] = "";
	$aPara[1][2] = "";
	$aPara[1][3] = "";
	$aPara[1][4] = "";
	$aPara[2][0] = "";
	$aPara[2][1] = "";
	$aPara[2][2] = "";
	$aPara[2][3] = "";
	$aPara[2][4] = "";
	
	//取得項目別
	switch ($type){
	case '801':
		switch ($_POST['cmbProcess_KBN']){
			case '0':		//納入
				$aPara[0][0] = false;
				$aPara[0][1] = true;
				$aPara[0][2] = true;
				$aPara[0][3] = true;
				$aPara[0][4] = false;
				if($txtFailure_QTY == 0){
					$aPara[1][0] = number_format($txtFlawLot_QTY - $txtExclud_QTY);
				}else{
					$aPara[1][0] = number_format($txtFlawLot_QTY);
				}
				$aPara[1][1] = "";
				$aPara[1][2] = "";
				$aPara[1][3] = "";
				//$aPara[1][4] = "";
				if($txtExclud_QTY == 0){
					$aPara[1][4] = "";
				}else{
					$aPara[1][4] = number_format($txtExclud_QTY);
				}
				if($txtFailurePrice == 0){
					$aPara[2][0] = number_format($txtFlawPrice - $txtExcludPrice);
				}else{
					$aPara[2][0] = number_format($txtFailurePrice);
				}
				$aPara[2][1] = "";
				$aPara[2][2] = "";
				$aPara[2][3] = "";
				//$aPara[2][4] = "";
				if($txtExcludPrice == 0){
					$aPara[2][4] = "";
				}else{
					$aPara[2][4] = number_format($txtExcludPrice);
				}
				break;
			case '1':		//廃棄
				$aPara[0][0] = true;
				$aPara[0][1] = false;
				$aPara[0][2] = true;
				$aPara[0][3] = true;
				$aPara[0][4] = false;
				$aPara[1][0] = "";
				if($txtDisposal_QTY == 0){
					$aPara[1][1] = number_format($txtFlawLot_QTY - $txtExclud_QTY);
				}else{
					$aPara[1][1] = number_format($txtDisposal_QTY);
				}
				$aPara[1][2] = "";
				$aPara[1][3] = "";
				//$aPara[1][4] = "";
				if($txtExclud_QTY == 0){
					$aPara[1][4] = "";
				}else{
					$aPara[1][4] = number_format($txtExclud_QTY);
				}
				$aPara[2][0] = "";
				if($txtDisposalPrice == 0){
					$aPara[2][1] = number_format($txtFlawPrice - $txtExcludPrice);
				}else{
					$aPara[2][1] = number_format($txtDisposalPrice);
				}
				$aPara[2][2] = "";
				$aPara[2][3] = "";
				//$aPara[2][4] = "";
				if($txtExcludPrice == 0){
					$aPara[2][4] = "";
				}else{
					$aPara[2][4] = number_format($txtExcludPrice);
				}
				break;
			case '2':		//返品
				$aPara[0][0] = true;
				$aPara[0][1] = true;
				$aPara[0][2] = false;
				$aPara[0][3] = true;
				$aPara[0][4] = false;
				$aPara[1][0] = "";
				$aPara[1][1] = "";
				if($txtReturn_QTY == 0){
					$aPara[1][2] = number_format($txtFlawLot_QTY - $txtExclud_QTY);
				}else{
					$aPara[1][2] = number_format($txtReturn_QTY);
				}
				$aPara[1][3] = "";
				//$aPara[1][4] = "";
				if($txtExclud_QTY == 0){
					$aPara[1][4] = "";
				}else{
					$aPara[1][4] = number_format($txtExclud_QTY);
				}
				$aPara[2][0] = "";
				$aPara[2][1] = "";
				if($txtReturnPrice == 0){
					$aPara[2][2] = number_format($txtFlawPrice - $txtExcludPrice);
				}else{
					$aPara[2][2] = number_format($txtReturnPrice);
				}
				$aPara[2][3] = "";
				//$aPara[2][4] = "";
				if($txtExcludPrice == 0){
					$aPara[2][4] = "";
				}else{
					$aPara[2][4] = number_format($txtExcludPrice);
				}
				break;
			case '3':		//一部納品廃棄
				$aPara[0][0] = false;
				$aPara[0][1] = false;
				$aPara[0][2] = true;
				$aPara[0][3] = true;
				$aPara[0][4] = false;
				if($txtFailure_QTY == 0){
					$aPara[1][0] = "";
				}else{
					$aPara[1][0] = number_format($txtFailure_QTY);
				}
				if($txtDisposal_QTY == 0){
					$aPara[1][1] = "";
				}else{
					$aPara[1][1] = number_format($txtDisposal_QTY);
				}
				$aPara[1][2] = "";
				$aPara[1][3] = "";
				if($txtExclud_QTY == 0){
					$aPara[1][4] = "";
				}else{
					$aPara[1][4] = number_format($txtExclud_QTY);
				}
				if($txtFailurePrice == 0){
					$aPara[2][0] = "";
				}else{
					$aPara[2][0] = number_format($txtFailurePrice);
				}
				if($txtDisposalPrice == 0){
					$aPara[2][1] = "";
				}else{
					$aPara[2][1] = number_format($txtDisposalPrice);
				}
				$aPara[2][2] = "";
				$aPara[2][3] = "";
				if($txtExcludPrice == 0){
					$aPara[2][4] = "";
				}else{
					$aPara[2][4] = number_format($txtExcludPrice);
				}
				break;
			case '4':		//一部納品返品
				$aPara[0][0] = false;
				$aPara[0][1] = true;
				$aPara[0][2] = false;
				$aPara[0][3] = true;
				$aPara[0][4] = false;
				if($txtFailure_QTY == 0){
					$aPara[1][0] = "";
				}else{
					$aPara[1][0] = number_format($txtFailure_QTY);
				}
				$aPara[1][1] = "";
				if($txtReturn_QTY == 0){
					$aPara[1][2] = "";
				}else{
					$aPara[1][2] = number_format($txtReturn_QTY);
				}
				$aPara[1][3] = "";
				if($txtExclud_QTY == 0){
					$aPara[1][4] = "";
				}else{
					$aPara[1][4] = number_format($txtExclud_QTY);
				}
				if($txtFailurePrice == 0){
					$aPara[2][0] = "";
				}else{
					$aPara[2][0] = number_format($txtFailurePrice);
				}
				
				$aPara[2][1] = "";
				if($txtReturnPrice == 0){
					$aPara[2][2] = "";
				}else{
					$aPara[2][2] = number_format($txtReturnPrice);
				}
				$aPara[2][3] = "";
				if($txtExcludPrice == 0){
					$aPara[2][4] = "";
				}else{
					$aPara[2][4] = number_format($txtExcludPrice);
				}
				break;
			case '5':		//調整ﾛｽ
				$aPara[0][0] = false;
				$aPara[0][1] = false;
				$aPara[0][2] = false;
				$aPara[0][3] = false;
				$aPara[0][4] = false;
				if($txtFailure_QTY == 0){
					$aPara[1][0] = "";
				}else{
					$aPara[1][0] = number_format($txtFailure_QTY);
				}
				if($txtDisposal_QTY == 0){
					$aPara[1][1] = "";
				}else{
					$aPara[1][1] = number_format($txtDisposal_QTY);
				}
				if($txtReturn_QTY == 0){
					$aPara[1][2] = "";
				}else{
					$aPara[1][2] = number_format($txtReturn_QTY);
				}
				if($txtLoss_QTY == 0){
					$aPara[1][3] = "";
				}else{
					$aPara[1][3] = number_format($txtLoss_QTY);
				}
				if($txtExclud_QTY == 0){
					$aPara[1][4] = "";
				}else{
					$aPara[1][4] = number_format($txtExclud_QTY);
				}
				if($txtFailurePrice == 0){
					$aPara[2][0] = "";
				}else{
					$aPara[2][0] = number_format($txtFailurePrice);
				}
				if($txtDisposalPrice == 0){
					$aPara[2][1] = "";
				}else{
					$aPara[2][1] = number_format($txtDisposalPrice);
				}
				if($txtReturnPrice == 0){
					$aPara[2][2] = "";
				}else{
					$aPara[2][2] = number_format($txtReturnPrice);
				}
				if($txtLossPrice == 0){
					$aPara[2][3] = "";
				}else{
					$aPara[2][3] = number_format($txtLossPrice);
				}
				if($txtExcludPrice == 0){
					$aPara[2][4] = "";
				}else{
					$aPara[2][4] = number_format($txtExcludPrice);
				}
				break;
			default:
				$aPara[0][0] = false;
				$aPara[0][1] = false;
				$aPara[0][2] = false;
				$aPara[0][3] = false;
				$aPara[0][4] = false;
				if($txtFailure_QTY == 0){
					$aPara[1][0] = "";
				}else{
					$aPara[1][0] = number_format($txtFailure_QTY);
				}
				if($txtDisposal_QTY == 0){
					$aPara[1][1] = "";
				}else{
					$aPara[1][1] = number_format($txtDisposal_QTY);
				}
				if($txtReturn_QTY == 0){
					$aPara[1][2] = "";
				}else{
					$aPara[1][2] = number_format($txtReturn_QTY);
				}
				if($txtLoss_QTY == ""){
					$aPara[1][3] = "";
				}else{
					$aPara[1][3] = number_format($txtLoss_QTY);
				}
				if($txtExclud_QTY == 0){
					$aPara[1][4] = "";
				}else{
					$aPara[1][4] = number_format($txtExclud_QTY);
				}
				if($txtFailurePrice == 0){
					$aPara[2][0] = "";
				}else{
					$aPara[2][0] = number_format($txtFailurePrice);
				}
				if($txtDisposalPrice == 0){
					$aPara[2][1] = "";
				}else{
					$aPara[2][1] = number_format($txtDisposalPrice);
				}
				if($txtReturnPrice == ""){
					$aPara[2][2] = "";
				}else{
					$aPara[2][2] = number_format($txtReturnPrice);
				}
				if($txtLossPrice == 0){
					$aPara[2][3] = "";
				}else{
					$aPara[2][3] = number_format($txtLossPrice);
				}
				if($txtExcludPrice == 0){
					$aPara[2][4] = "";
				}else{
					$aPara[2][4] = number_format($txtExcludPrice);
				}
				break;
		}
		break;
	case '802':
		$aPara[0][0] = "";
		$aPara[0][1] = "";
		$aPara[0][2] = "";
		$aPara[0][3] = "";
		$aPara[0][4] = "";
		$aPara[1][0] = "";
		$aPara[1][1] = "";
		$aPara[1][2] = "";
		$aPara[1][3] = "";
		$aPara[1][4] = "";
		$aPara[2][0] = "";
		$aPara[2][1] = "";
		$aPara[2][2] = "";
		$aPara[2][3] = "";
		$aPara[2][4] = "";
		if($txtUnitPrice <> 0){
			if($txtFailure_QTY <> 0){
				$aPara[0][0] = number_format($txtFailure_QTY * $txtUnitPrice);
			}else{
				$aPara[0][0] = "";
			}
			if($txtDisposal_QTY <> ""){
				$aPara[0][1] = number_format($txtDisposal_QTY * $txtUnitPrice);
			}else{
				$aPara[0][1] = "";
			}
			if($txtReturn_QTY <> ""){
				$aPara[0][2] = number_format($txtReturn_QTY * $txtUnitPrice);
			}else{
				$aPara[0][2] = "";
			}
			if($txtLoss_QTY <> ""){
				$aPara[0][3] = number_format($txtLoss_QTY * $txtUnitPrice);
			}else{
				$aPara[0][3] = "";
			}
			if($txtExclud_QTY <> ""){
				$aPara[0][4] = number_format($txtExclud_QTY * $txtUnitPrice);
			}else{
				$aPara[0][4] = "";
			}
		}
		if($txtFailure_QTY <> ""){
			$aPara[1][0] = number_format($txtFailure_QTY);
		}
		if($txtDisposal_QTY <> ""){
			$aPara[1][1] = number_format($txtDisposal_QTY);
		}
		if($txtReturn_QTY <> ""){
			$aPara[1][2] = number_format($txtReturn_QTY);
		}
		if($txtLoss_QTY <> ""){
			$aPara[1][3] = number_format($txtLoss_QTY);
		}
		if($txtExclud_QTY <> ""){
			$aPara[1][4] = number_format($txtExclud_QTY);
		}
		break;
	//2019/05/13 ADD START
	case '803':
		if($txtFlawLot_QTY <> ""){
			if($txtUnitPrice <> ""){
				$aPara[0][0] = number_format($txtUnitPrice * $txtFlawLot_QTY);
			}else{
				$aPara[0][0] = number_format($txtFlawLot_QTY);
			}
		}else{
			$aPara[0][0] = number_format($txtUnitPrice);
		}
		break;
	//2019/05/13 ADD END
	default:
		break;
	}

	$encode = $json->encode($aPara);
	header("Content-Type: text/javascript; charset=utf-8");
	echo $encode;
?>
