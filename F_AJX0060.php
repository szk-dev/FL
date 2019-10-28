<?php
	//****************************************************************************
	//プログラム名：手数料算出処理
	//プログラムID：K_AJX0060
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2010/11/02
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
	
	
	/*
	 * リクエストパラメータを取得する。
	*/
	if (isset($_GET['key1'])) {
		$strShiireCd = $_GET['key1'];
	}
	  
	if (isset($_GET['key2'])) {
		$intKingk = $_GET['key2'];
	}
				

	
	//取引先と金額が設定されていたら
	if($strShiireCd <> "" and $intKingk <> "" ){
		
		//手数料情報取得
		$aPara = $module_sel->fTesuryoGet($strShiireCd,$intKingk);
		
		
		//手数料情報が取得できたら変数にセットする
		if($aPara[0] <> ""){
			
			echo $aPara[0];
			
			//$json = $aPara[0];
			
			//JavaScript Object Notation(JSON)形式に変更
//			$encode = $json->encode($aPara);
			
//			header("Content-Type: text/javascript; charset=utf-8"); 
//			echo $encode;				
			
		}		
	}


?>
