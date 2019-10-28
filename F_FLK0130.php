<?php
	//****************************************************************************
	//プログラム名： 品質改善報告書・協力工場不良品連絡書の期限切れメール通知
	//プログラムID：F_FLK0130
	//作成者　　　：㈱鈴木　藤田
	//作成日　　　：2019/08/01
	//履歴
	//
	//
	//****************************************************************************
	//ファイル読み込み
	require_once("module_sel.php");
	require_once("module_common.php");

	// オブジェクト作成
	$module_sel = new module_sel;
	$module_cmn = new module_common;

	$aPara = array();
	$aJoken = Array();
	
	$aJoken[100] = date("Ymd");

	//赤伝緑伝情報データ一覧取得処理
	$aPara = $module_sel->fTrblSearch($aJoken,2);

	//言語設定、内部エンコーディングを指定する
	mb_language("japanese");
	mb_internal_encoding("UTF-8");

	//1件でもあればメール送信
	if($aPara[0][0] <> "N006"){
		$i = 0;
		while($i < count($aPara)){
			$module_cmn->fMailSendProcessLimit($aPara[$i],"TRBL2");
			$i++;
		}
	}

?>