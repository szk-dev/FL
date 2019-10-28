<?php
	//****************************************************************************
	//プログラム名：品目マスタ検索処理
	//プログラムID：F_AJX0010
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/08/22
	//履歴　　　　：
	//
	//
	//****************************************************************************

	//ファイル読み込み
	require_once("module_sel.php");
	require_once("module_common.php");
	require_once("JSON/JSON.php");
	// オブジェクト作成
	$module_sel = new module_sel;
 	$module_cmn = new module_common;
	$json = new Services_JSON;

	$aPara = array();


	//品目コードが入力されていたら
	if($_POST['txtProd_CD'] <> "" ){

		//品目マスタ情報取得
		$aPara = $module_sel->fGetProdDataDetail($_POST['txtProd_CD'],"","","","","");
		//品目が取得できたら変数にセットする

		if($aPara[0] <> ""){

			//JavaScript Object Notation(JSON)形式に変更
			$encode = $json->encode($aPara);
			header("Content-Type: text/javascript; charset=utf-8");
			echo $encode;

		}else{
			//エラーメッセージ
			$strErrMsg = $module_sel->fMsgSearch("E015","製品CD:".$txtItemCd);
		}

	}
?>
