<?php
	//****************************************************************************
	//プログラム名：保留品情報取得処理
	//プログラムID：F_AJX0070
	//作成者		：㈱鈴木　藤田
	//作成日		：2019/04/01
	//****************************************************************************

	//ファイル読み込み
	require_once("module_sel.php");
	require_once("JSON/JSON.php");
	// オブジェクト作成
	$module_sel = new module_sel;
	$json = new Services_JSON;

	//保留品情報取得
	header("Content-Type: text/javascript; charset=utf-8");
	$aPara = array();
	$iSeq = 1;
	$encode = $json->encode("SMART2保留品情報データを取得できませんでした");

	if($_POST['txtReference_NO'] <> ""){
		//SMART2保留品情報データ存在確認
		if($module_sel->fchkTrblSonzaiS2($_POST['txtReference_NO']) === false) {
			//エラー　N006「一致する情報は見つかりませんでした」
			$encode = $json->encode(str_replace("<br>","",$module_sel->fMsgSearch("N006","J_保留品情報:".trim($_POST['txtReference_NO']))));
			echo $encode;
			exit;
		}

		$checkNo = "";
		//赤伝緑伝情報データ重複確認
		if($module_sel->fchkTrblDataDupli($_POST['txtReference_NO'],$iSeq) === false) {
			//確認メッセージ「[伝票NO]は既にN件登録されています」
			$checkNo = "[".trim($_POST['txtReference_NO'])."]は既に".($iSeq-1)."件登録済みです";
			//$encode = $json->encode(str_replace("<br>","",$module_sel->fMsgSearch("E003","T_TR_TRBL:".trim($_POST['txtReference_NO']))));
		}

		//赤伝緑伝情報データ取得
		$aPara = $module_sel->fGetTrblDataS2($_POST['txtReference_NO']);
		//データが取得できたら変数にセットする
		if($aPara[0] <> ""){
			//伝票SEQ取得
			$aPara[29] = $iSeq;
			//確認メッセージ
			$aPara[30] = $checkNo;
			$encode = $json->encode($aPara);
			echo $encode;
		}else{
			echo $encode;
		}
	}else{
		echo $encode;
	}

?>
