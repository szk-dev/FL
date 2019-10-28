<?php
	//****************************************************************************
	//プログラム名：金額計算処理(検収数用)
	//プログラムID：K_AJX0041
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2009/05/20
	//履歴　　　　：2012/01/27取引先諸口対応 k.kume
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
	$aPara2 = array();

	//=================================================================================================
	//金額取得(注文数と単価が設定されていた場合)
	if($_POST['txtKenshuSu'] <> "" && $_POST['txtKenshuTanka'] <> ""){



		$strErrMsg = $module_cmn->fNumericCheck($_POST['txtKenshuSu'],10,3,false,true,"検収数");
		if($strErrMsg <> ""){
			exit($strErrMsg);
		}
		$strErrMsg = $module_cmn->fNumericCheck($_POST['txtKenshuTanka'],10,3,false,false,"検収単価");
		if($strErrMsg <> ""){
			exit($strErrMsg);
		}


		$txtKingaku1 = "";
		$txtKingaku2 = "";


		//取引先コードが入力されて、再計算ボタンが押されたら
		if($_POST['txtShiireCd'] <> ""){


			//消費税の取得(戻り値は％)
			$intTax = $module_sel->fTaxSearch();

			$aPara = array();

			$strKazeiKbn = "";		//課税区分
			$strHasuKbn = "";		//消費税端数区分
			$strPHasuKbn = "";		//支払端数区分

			$strChumonNo = "";
			$strChumonEdaNo = "";

			$strChumonNo = substr($_POST['txtChumonNo'], 0,10);
			if(isset($_POST['txtChumonEdaNo'])){
				$strChumonEdaNo = $_POST['txtChumonEdaNo'];
			}else{
				$strChumonEdaNo = substr($_POST['txtChumonNo'], 10,2);
			}

			//諸口の場合は注文付加データから情報を取得 2012/01/27 k.kume
			if($_POST['txtShiireCd'] == "99999"){

				$aPara = $module_sel->fGetChumonData($strChumonNo,$strChumonEdaNo);
				$strKazeiKbn = $aPara[66];
				$strHasuKbn = $aPara[67];
				$strPHasuKbn = $aPara[68];

			}else{
				//取引先情報取得
				$aPara = $module_sel->fGetShiireData($_POST['txtShiireCd']);
				$strKazeiKbn = $aPara[22];
				$strHasuKbn = $aPara[23];
				$strPHasuKbn = $aPara[24];
			}



			//0以外の場合に下記の処理で計算
			if(str_replace(",","",$_POST['txtKenshuSu']) > 0 ) {


				//取引先情報が取得できたら変数にセットする
				if($strKazeiKbn <> ""){

					//金額算出
					$txtKingaku1 = number_format($module_cmn->fKingakuCalc("0",bcmul(str_replace(",","",$_POST['txtKenshuSu']),str_replace(",","",$_POST['txtKenshuTanka']),3),$strKazeiKbn,$strHasuKbn,$strPHasuKbn,$intTax));
					$txtKingaku2 = number_format($module_cmn->fKingakuCalc("1",bcmul(str_replace(",","",$_POST['txtKenshuSu']),str_replace(",","",$_POST['txtKenshuTanka']),3),$strKazeiKbn,$strHasuKbn,$strPHasuKbn,$intTax));


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
			}else{
				//単価・数量が0の場合は金額は0で戻す
				$txtKingaku1 = 0;
				$txtKingaku2 = 0;
				$aPara2 = array();
				$aPara2[0] = $txtKingaku1;
				$aPara2[1] = $txtKingaku2;
				//JavaScript Object Notation(JSON)形式に変更
				$encode = $json->encode($aPara2);
				header("Content-Type: text/javascript; charset=utf-8");
				echo $encode;

			}
		}else{
			echo $strErrMsg;


		}
	}


?>
