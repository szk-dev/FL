<?php
	//****************************************************************************
	//プログラム名：金額計算処理(注文数用)
	//プログラムID：K_AJX0040
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2008/10/23
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

	//=================================================================================================
	//金額取得(注文数と単価が設定されていた場合)
	if($_POST['txtChumonSu'] <> "" && $_POST['txtTanka'] <> ""){

		$strErrMsg = $module_cmn->fNumericCheck($_POST['txtChumonSu'],10,3,false,false,"注文数");
		if($strErrMsg <> ""){
			exit($strErrMsg);
		}
		$strErrMsg = $module_cmn->fNumericCheck($_POST['txtTanka'],10,3,false,false,"単価");
		if($strErrMsg <> ""){
			exit($strErrMsg);
		}

		//税抜金額
		$txtKingaku1 = "";
		//税込金額
		$txtKingaku2 = "";

		//消費税の取得(戻り値は％)
		$intTax = $module_sel->fTaxSearch();

		//取引先コードが入力されて、再計算ボタンが押されたら
		if($_POST['txtShiireCd'] <> ""){
			//取引先が諸口の場合
			if($_POST['txtShiireCd'] == "99999"){
				//金額算出
				//税込金額=単価×注文数
				$txtKingaku1 = number_format($module_cmn->fKingakuCalc("0",bcmul(str_replace(",","",$_POST['txtChumonSu']),str_replace(",","",$_POST['txtTanka']),3),"1","1","2",$intTax));
				$txtKingaku2 = number_format($module_cmn->fKingakuCalc("1",bcmul(str_replace(",","",$_POST['txtChumonSu']),str_replace(",","",$_POST['txtTanka']),3),"0","1","1",$intTax));

				$aPara2 = array();
				$aPara2[0] = $txtKingaku1;
				$aPara2[1] = $txtKingaku2;
				//JavaScript Object Notation(JSON)形式に変更
				$encode = $json->encode($aPara2);
				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;


			}else{
			//取引先がマスタにある場合


				$aPara = array();
				//取引先情報取得
				$aPara = $module_sel->fGetShiireData($_POST['txtShiireCd']);

				//取引先情報が取得できたら変数にセットする
				if($aPara[0] <> ""){

					//金額算出
					$txtKingaku1 = number_format($module_cmn->fKingakuCalc("0",bcmul(str_replace(",","",$_POST['txtChumonSu']),str_replace(",","",$_POST['txtTanka']),3),$aPara[22],$aPara[23],$aPara[24],$intTax));
					$txtKingaku2 = number_format($module_cmn->fKingakuCalc("1",bcmul(str_replace(",","",$_POST['txtChumonSu']),str_replace(",","",$_POST['txtTanka']),3),$aPara[22],$aPara[23],$aPara[24],$intTax));

					$aPara2 = array();
					$aPara2[0] = $txtKingaku1;
					$aPara2[1] = $txtKingaku2;
					//JavaScript Object Notation(JSON)形式に変更
					$encode = $json->encode($aPara2);
					header("Content-Type: text/javascript; charset=utf-8");
					echo $encode;

				}else{
					//エラーメッセージ
					$strErrMsg = $module_sel->fMsgSearch("E015","取引先ｺｰﾄﾞ:".$txtShiireCd);
					echo $strErrMsg;
				}
			}
		}else{
			echo $strErrMsg;
		}
	}


?>
