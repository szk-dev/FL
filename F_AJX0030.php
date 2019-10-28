<?php
	//****************************************************************************
	//プログラム名：担当者マスタ検索処理
	//プログラムID：F_AJX0030
	//作成者		：㈱鈴木　久米
	//作成日		：2012/08/22
	//履歴		：2019/04/01 追加 赤伝緑伝情報入力画面処理（品証担当者取得用） 藤田
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
	//回答者情報取得
	if(i$_POST['txtAns_Tanto_CD'] <> ""){

		//担当者情報取得
		$aPara = $module_sel->fGetTanDataAjax($_POST['txtAns_Tanto_CD']);

		//担当者が取得できたら変数にセットする
		if($aPara[0] <> ""){

			//JavaScript Object Notation(JSON)形式に変更
			//error_log("CD=".$aPara[0]."NM=".$aPara[1], 3, "out.log");
			$encode = $json->encode($aPara);

			header("Content-Type: text/javascript; charset=utf-8");
			echo $encode;




		}
	}

	//確認者(社内)情報取得
	if($_POST['txtConfirm_Tanto_CD1'] <> ""){

		//担当者情報取得
		$aPara = $module_sel->fGetTanDataAjax($_POST['txtConfirm_Tanto_CD1']);

		//担当者が取得できたら変数にセットする
		if($aPara[0] <> ""){

			//JavaScript Object Notation(JSON)形式に変更
			$encode = $json->encode($aPara);

			//error_log($encode, 3, "out.log");
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
	case '804':
	//品証担当者情報取得
		if($_POST['txtTanto_CD'] <> ""){
			$aJoken[0] = trim($_POST['txtTanto_CD']);
			
			//担当者情報取得
			$aPara = $module_sel->fS2TantoSearch($aJoken);

			//担当者が取得できたら変数にセットする
			if($aPara[0][1] <> ""){
				//JavaScript Object Notation(JSON)形式に変更
				$encode = $json->encode($aPara);
				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;
			}
		}
		break;

	default:
		//回答者情報取得
		if($_POST['txtAns_Tanto_CD'] <> ""){

			//担当者情報取得
			$aPara = $module_sel->fGetTanDataAjax($_POST['txtAns_Tanto_CD']);

			//担当者が取得できたら変数にセットする
			if($aPara[0] <> ""){

				//JavaScript Object Notation(JSON)形式に変更
				//error_log("CD=".$aPara[0]."NM=".$aPara[1], 3, "out.log");
				$encode = $json->encode($aPara);

				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;




			}
		}

		//確認者(社内)情報取得
		if($_POST['txtConfirm_Tanto_CD1'] <> ""){

			//担当者情報取得
			$aPara = $module_sel->fGetTanDataAjax($_POST['txtConfirm_Tanto_CD1']);

			//担当者が取得できたら変数にセットする
			if($aPara[0] <> ""){

				//JavaScript Object Notation(JSON)形式に変更
				$encode = $json->encode($aPara);

				//error_log($encode, 3, "out.log");
				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;
			}
		}
		break;
	}
	
//2019/04/01 AD END

?>
