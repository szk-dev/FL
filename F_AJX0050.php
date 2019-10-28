<?php
	//****************************************************************************
	//プログラム名：科目マスタ検索処理
	//プログラムID：K_AJX0050
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2009/09/11
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
		$cmbChumonKbn = $_GET['key1'];
	}
	  
	if (isset($_GET['key2'])) {
		$cmbRiyoBumon = $_GET['key2'];
	}
	
	try{

		$json = "-1\t▼選択して下さい";
		
		//注文区分と利用区分が選択されたら
		if($cmbChumonKbn <> "" and $cmbRiyoBumon <> "" ){
			$aPara = array();
			//科目マスタ情報取得
			$aPara = $module_sel->fGetKamokuData($cmbChumonKbn,$cmbRiyoBumon);
			
			//結果が取得できたら変数にセットする
			if($aPara[0][0] <> ""){
				
				$length = count($aPara);
			  	for ($i = 0; $i < $length; $i++) {
			    	$json = $json."\n".$aPara[$i][0]."\t".$aPara[$i][1];
			  	}
			}
		}
		header("Content-Type: text/javascript; charset=utf-8"); 
		echo $json;
		
	}catch(Exception $e){
		
		// 出力ファイルを書き込みモードで開く
		$file = fopen("err.txt", "wb");
		// ファイルをロックする
		flock($file, LOCK_EX);
		//書き込み処理
		fputs($file,$json."\r\n");
		// ロックを解除する
		flock($file, LOCK_UN);
	
		// ファイルを閉じる
		fclose($file);
	}



?>
