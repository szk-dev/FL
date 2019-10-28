<?php
	//****************************************************************************
	//プログラム名：取引先マスタ検索処理
	//プログラムID：K_AJX0020
	//作成者		：㈱鈴木　久米
	//作成日		：2008/10/22
	//履歴		：2019/04/01 追加 赤伝緑伝情報入力画面処理（めっき先、報告書発行先部署・協力会社、起因部署・協力会社取得用） 藤田
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

//2019/04/01 AD START
/*
	//取引先情報取得
	if($_POST['txtCust_CD'] <> ""){

		//取引先情報取得
		$aPara = $module_sel->fGetCustDataDetail($_POST['txtCust_CD'],"","","0");

		//取引先情報が取得できたら変数にセットする
		if($aPara[0] <> ""){

			//JavaScript Object Notation(JSON)形式に変更
			$encode = $json->encode($aPara);
			header("Content-Type: text/javascript; charset=utf-8");
			echo $encode;

		}
	}
*/

	//引数の取得
	if(isset($_GET['ajx'])) {
		$type = $_GET['ajx'];
	}else{
		$type = "";
	}

	//取得項目別
	switch ($type){
	case '801':
		//めっき先情報取得
		if($_POST['txtPlating_CD'] <> ""){
			//伝票種別取得（めっき先用 赤伝：3 緑伝:4 全て:3
			if($_POST['txtReference_KBN'] == "1"){
				$rKbn = "3";
			}elseif($_POST['txtReference_KBN'] == "2"){
				$rKbn = "4";
			}else{
				$rKbn = "3";
			}
			//取引先情報取得
			$aPara = $module_sel->fGetCustDataDetail(trim($_POST['txtPlating_CD']),"","",$rKbn);

			//取引先情報が取得できたら変数にセットする
			if($aPara[0] <> ""){
				//JavaScript Object Notation(JSON)形式に変更
				$encode = $json->encode($aPara);
				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;
			}
		}
		break;

	case '802':
		//報告書発行先部署・協力会社情報取得
		if($_POST['txtIncident_CD'] <> ""){
			//取引先情報取得
			$aPara = $module_sel->fGetCustDataDetail(trim($_POST['txtIncident_CD']),"","","6");

			//取引先情報が取得できたら変数にセットする
			if($aPara[0] <> ""){
				//JavaScript Object Notation(JSON)形式に変更
				$encode = $json->encode($aPara);
				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;
			}
		}
		break;

	case '803':
		//起因部署・協力会社情報取得
		if($_POST['txtPartner_CD'] <> ""){
			//取引先情報取得
			$aPara = $module_sel->fGetCustDataDetail(trim($_POST['txtPartner_CD']),"","","6");

			//取引先情報が取得できたら変数にセットする
			if($aPara[0] <> ""){
				//JavaScript Object Notation(JSON)形式に変更
				$encode = $json->encode($aPara);
				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;
			}
		}
		break;

	case '805':
		//起因部署情報取得
		if($_POST['txtBusyo_CD'] <> ""){
			//取引先情報取得
			$aPara = $module_sel->fGetCustDataDetail(trim($_POST['txtBusyo_CD']),"","","6");

			//取引先情報が取得できたら変数にセットする
			if($aPara[0] <> ""){
				//JavaScript Object Notation(JSON)形式に変更
				$encode = $json->encode($aPara);
				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;
			}
		}
		break;

	default:
		//取引先情報取得
		if($_POST['txtCust_CD'] <> ""){

			//取引先情報取得
			$aPara = $module_sel->fGetCustDataDetail($_POST['txtCust_CD'],"","","0");

			//取引先情報が取得できたら変数にセットする
			if($aPara[0] <> ""){

				//JavaScript Object Notation(JSON)形式に変更
				$encode = $json->encode($aPara);
				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;

			}
		}
		break;

	}
//2019/04/01 AD END

?>
