<?php
//****************************************************************************
//プログラム名：共通関数用モジュール郡
//プログラムID：module_common
//作成者　　　：㈱鈴木　久米
//作成日　　　：2008/06/10
//履歴　　　　：
//
//
//****************************************************************************
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class module_common{
	
	//コンストラクタ
	function __construct(){
		require_once 'vendor/autoload.php';
	}

	//テキストボックス必須チェック
	function fTxtNCheck($strObj,$strTit){

		//ファイル読み込み
		require_once("vendor/autoload.php");
		require_once("module_sel.php");

		// オブジェクト作成
		$module_sel = new module_sel;

		$strMsg = "";
		//空白を除去してチェック
		if(trim($strObj) == ""){
			$strMsg = $module_sel->fMsgSearch("E004",$strTit);
		}
		return $strMsg;
	}
	//コンボボックス必須チェック
	function fCmbNCheck($strObj,$strTit){
		//ファイル読み込み
		require_once("module_sel.php");
		// オブジェクト作成
		$module_sel = new module_sel;

		$strMsg = "";
		if($strObj == "-1"){
			$strMsg = $module_sel->fMsgSearch("E006",$strTit);
		}
		return $strMsg;
	}

	//日付妥当性チェック
	function fDateCheck($strObj,$strTit){
		//ファイル読み込み
		require_once("module_sel.php");
		// オブジェクト作成
		$module_sel = new module_sel;
		if($strObj <> ""){
			$strMsg = "";
			$intYear = intval(substr($strObj,0,4));
			$intMonth = intval(substr($strObj,4,2));
			$intDay = intval(substr($strObj,6,2));

			if(!checkdate($intMonth,$intDay,$intYear) || strlen($strObj) <> 8 ){
				$strMsg = $module_sel->fMsgSearch("E018",$strTit);
			}
		}
		return $strMsg;
	}


	//文字数チェック
	//指定文字数以上はエラーにする
	function fMojiCountCheck($strObj,$intByte,$strTit){
		//ファイル読み込み
		require_once("module_sel.php");
		// オブジェクト作成
		$module_sel = new module_sel;

		$intByte2 = $intByte * 2;

		$strMsg = "";
		//文字列の桁数が指定桁数を超えていたらエラー
		if(strlen($strObj) > $intByte2){
			$strMsg = $module_sel->fMsgSearch("E028",$strTit."(入力可能文字数(全角)=".$intByte."(半角)=".$intByte2.")");
		}
		return $strMsg;
	}

	//桁数チェック
	//指定桁以外はエラーにする
	function fKetaCheck($strObj,$intKeta,$strTit){
		//ファイル読み込み
		require_once("module_sel.php");
		// オブジェクト作成
		$module_sel = new module_sel;

		$strMsg = "";
		//文字列の桁数と指定桁数が違っていたらエラー
		if(strlen($strObj) <> $intKeta){
			$strMsg = $module_sel->fMsgSearch("E019",$strTit."(指定桁=".$intKeta.")");
		}
		return $strMsg;
	}

	//桁数チェック2
	//指定桁未満はエラーにする
	function fKetaCheck2($strObj,$intKeta,$strTit){
		//ファイル読み込み
		require_once("module_sel.php");
		// オブジェクト作成
		$module_sel = new module_sel;

		$strMsg = "";
		//文字列の桁数と指定桁数が違っていたらエラー
		if(strlen($strObj) < $intKeta){
			$strMsg = $module_sel->fMsgSearch("E019",$strTit."(指定桁=".$intKeta."以上)");
		}
		return $strMsg;
	}

	//数値フォーマットチェック
	//数値以外はエラーにする
	//引数	$strObj		･･･	チェック対象
	//		$intKeta1	･･･	整数部最大桁数
	//		$intKeta2	･･･	小数部最大桁数
	//		$bMinusFlg	･･･	マイナスチェックフラグ(trueはマイナス可能,falseはマイナス不可)
	//		$bZeroFlg	･･･	ZEROチェックフラグ(trueはZERO可能,falseはZERO不可)
	//		$strTit		･･･	タイトル(エラー時の引数とする項目名)
	function fNumericCheck($strObj,$intKeta1,$intKeta2,$bMinusFlg,$bZeroFlg,$strTit){
		//ファイル読み込み
		require_once("module_sel.php");
		// オブジェクト作成
		$module_sel = new module_sel;

		$strMsg = "";
		$aObj = array();
		//数値が入っていれば
		if($strObj <> ""){
			//カンマ除去
			$strObj = str_replace(",","",$strObj);

			//数値がどうかチェック
			if(!is_numeric($strObj)){
				$strMsg = $module_sel->fMsgSearch("E025",$strTit);
				return $strMsg;
			}

			//小数点で整数部と小数部を分割
			$aObj = explode('.',$strObj);

			$aObj[0] = $aObj[0] + 0;
			//$aObj[1] = $aObj[1] + 0;

			//もし小数点が2つ以上あったらor数値でない場合エラー
			if($aObj[2] <> "" || !is_numeric($aObj[0]) || !is_numeric($aObj[1] + 0)){
				$strMsg = $module_sel->fMsgSearch("E022",$strTit);
				return $strMsg;
			}

			//マイナスチェック
			if(!$bMinusFlg){
				if($strObj < 0){
					$strMsg = $module_sel->fMsgSearch("E021",$strTit);
					return $strMsg;
				}
			}
			//ZEROチェック
			if(!$bZeroFlg){
				if($strObj == 0){
					$strMsg = $module_sel->fMsgSearch("E024",$strTit);
					return $strMsg;
				}
			}

			//整数部の桁チェック
			if($intKeta1 < strlen($aObj[0])){
				$strMsg = $module_sel->fMsgSearch("E020",$strTit."(指定桁=".$intKeta1.")");
				return $strMsg;
			}
			//小数部の桁チェック
			if($intKeta2 < strlen($aObj[1])){
				$strMsg = $module_sel->fMsgSearch("E023",$strTit."(指定桁=".$intKeta2.")");
				return $strMsg;
			}

		}
		return $strMsg;
	}

	//すべて半角カタカナ大文字に変換する
	function fHankatakana($strText)
	{
		//半角カナに統一
		$strText = mb_convert_kana($strText,"h");

		//検索文字列
		$aSearch = array("ｧ", "ｨ", "ｩ", "ｪ", "ｫ", "ｬ", "ｭ", "ｮ", "ｯ");
		//置換文字列
		$aReplace = array("ｱ", "ｲ", "ｳ", "ｴ", "ｵ", "ﾔ", "ﾕ", "ﾖ", "ﾂ");

		$strText = str_replace($aSearch, $aReplace, $strText);

	    return $strText;
	}

	//文字コード(SJIS→UTF-8)変換＋アンエスケープ(stripslashes)
	function  fChangUTF8($str) {
		return stripslashes(mb_convert_encoding($str,'UTF8','sjis-win'));
	}

	//文字コード(UTF-8→SJIS)変換＋エスケープ
	function  fChangSJIS($str) {
		return mb_convert_encoding(htmlspecialchars($str,3),'sjis-win','UTF8');
	}

	//文字コード(SJIS→UTF-8)変換
	function  fChangUTF8_SQL($str) {
		return mb_convert_encoding($str,'UTF8','sjis-win');
	}

	//文字コード(UTF-8→SJIS)変換
	function  fChangSJIS_SQL($str) {
		return mb_convert_encoding($str,'sjis-win','UTF8');
	}

	//文字コード(UTF-8→SJIS)変換＋エスケープ+検索条件用
	function  fChangSJIS_SQL_J($str) {

		//一度英数字を半角にする
		$strOut = mb_convert_kana($str,"a","UTF-8");

		//小文字を大文字にする
		$strOut = strtoupper($strOut);
		//全て全角ひらがなに変換する
		$strOut = mb_convert_kana($strOut,"RNASHcV","UTF-8");
		//UTF-8からSJISに変換
		$strOut = mb_convert_encoding($strOut, "sjis-win", "UTF-8");
		return $strOut;

	}
	
	//文字コード(UTF-8→SJIS)変換＋エスケープ+検索条件用
	function  fChangSJIS_SQL_J2($str) {

		//一度英数字を半角にする
		$strOut = mb_convert_kana($str,"a","UTF-8");

		//小文字を大文字にする
		$strOut = strtoupper($strOut);
		//全て全角ひらがなに変換する
		$strOut = mb_convert_kana($strOut,"RNASHcV","UTF-8");
		//UTF-8からSJISに変換
		$strOut = mb_convert_encoding($strOut, "UTF-8", "UTF-8");
		return $strOut;

	}
	
	//文字コード(UTF-8→SJIS)変換(5C問題対応)
	function  fChangSJIS_5C($str) {
		return addslashes(mb_convert_encoding($str,'sjis-win','UTF-8'));

	}

	//エ文字列スケープ
	function  fEscape($str) {
		return htmlspecialchars($str,3);
	}

	//スラッシュ区切変換(年月日用)西暦4桁表示
	function  fChangDateFormat($str) {

		if($str <> "" && strlen($str) == 8){
			$str = substr($str,0,4)."/".substr($str,4,2)."/".substr($str,6,2);
		}elseif($str == 0){
			$str = "";
		}
		return $str;
	}



	//スラッシュ区切変換(年月日用)西暦2桁表示
	function  fChangDateFormat3($str) {

		if($str <> "" && strlen($str) == 8){
			$str = substr($str,2,2)."/".substr($str,4,2)."/".substr($str,6,2);
		}elseif($str == 0){
			$str = "";
		}
		return $str;
	}

	//スラッシュ区切変換(年月日時刻用)
	function  fChangDateTimeFormat($str) {

		if($str <> "" && strlen($str) == 14){
			$str = substr($str,0,4)."/".substr($str,4,2)."/".substr($str,6,2)." ".substr($str,8,2).":".substr($str,10,2).":".substr($str,12,2);
		}elseif($str == 0){
			$str = "";
		}
		return $str;
	}


	//就業コード10桁変換
	function  fChangeTimePro10($str) {

		//８桁の就業コードから１０桁に変換
		$strTimeProCd10 = "";
		$strTimeProCd8 = substr($str,2,8);

		//会社コード部分
		if(substr($strTimeProCd8,0,1) == "1"){
			$strTimeProCd10 = "11";
		}elseif(substr($strTimeProCd8,0,1) == "2"){
			$strTimeProCd10 = "12";
		}elseif(substr($strTimeProCd8,0,1) == "3"){
			$strTimeProCd10 = "21";
		}elseif(substr($strTimeProCd8,0,1) == "4"){
			$strTimeProCd10 = "22";
		}elseif(substr($strTimeProCd8,0,1) == "7"){
			$strTimeProCd10 = "71";
		}
		//部門部分
		$strTimeProCd10 = $strTimeProCd10.substr($strTimeProCd8,1,5);
		//直接間接部分
		if(substr($strTimeProCd8,6,1) >= 5){
			$strTimeProCd10 = $strTimeProCd10.(substr($strTimeProCd8,6,1) - 5).substr($strTimeProCd8,7,1)."2";
		}else{
			$strTimeProCd10 = $strTimeProCd10.substr($strTimeProCd8,6,2)."1";
		}

		return $strTimeProCd10;
	}

	//就業コード8桁変換
	function  fChangeTimePro8($str) {

		//10桁の就業コードから8桁に変換
		$strTimeProCd8 = "";
		$strTimeProCd10 = $str;

		//会社コード部分
		if(substr($strTimeProCd10,0,2) == "11"){
			$strTimeProCd8 = "00";
			$strTimeProCd8 = $strTimeProCd8."1";
		}elseif(substr($strTimeProCd10,0,2) == "12"){
			$strTimeProCd8 = "00";
			$strTimeProCd8 = $strTimeProCd8."2";
		}elseif(substr($strTimeProCd10,0,2) == "21"){
			$strTimeProCd8 = "02";
			$strTimeProCd8 = $strTimeProCd8."3";
		}elseif(substr($strTimeProCd10,0,2) == "22"){
			$strTimeProCd8 = "02";
			$strTimeProCd8 = $strTimeProCd8."4";
			
		//SSE対応 2018/08/09 k.kume START
		}elseif(substr($strTimeProCd10,0,2) == "31"){
			$strTimeProCd8 = "03";
			$strTimeProCd8 = $strTimeProCd8."5";
				
		}elseif(substr($strTimeProCd10,0,2) == "32"){
			$strTimeProCd8 = "03";
			$strTimeProCd8 = $strTimeProCd8."6";
		//SSE対応 2018/08/09 k.kume END
			
		}elseif(substr($strTimeProCd10,0,2) == "71"){
			$strTimeProCd8 = "00";
			$strTimeProCd8 = $strTimeProCd8."7";
		}

		//部門部分
		$strTimeProCd8 = $strTimeProCd8.substr($strTimeProCd10,2,5);

		if(substr($strTimeProCd10,9,1) == "2"){
			$strTimeProCd8 = $strTimeProCd8.(substr($strTimeProCd10,7,1) + 5).substr($strTimeProCd10,8,1);
		}else{
			$strTimeProCd8 = $strTimeProCd8.substr($strTimeProCd10,7,1).substr($strTimeProCd10,8,1);
		}

//		//直接間接部分
//		if(substr($strTimeProCd10,6,1) >= 5){
//			$strTimeProCd10 = $strTimeProCd10.(substr($strTimeProCd8,6,1) - 5).substr($strTimeProCd8,7,1)."2";
//		}else{
//			$strTimeProCd10 = $strTimeProCd10.substr($strTimeProCd8,6,2)."1";
//		}

		return $strTimeProCd8;
	}

	//スラッシュ区切変換(年月用)
	function  fChangDateFormat2($str) {

		if($str <> "" && strlen($str) == 6){
			$str = substr($str,0,4)."/".substr($str,4,2);
		}
		return $str;
	}

	//年月日区切変換(年月日用)西暦4桁表示
	function  fChangDateFormat4($str) {

		if($str <> "" && strlen($str) == 8){
			$str = substr($str,0,4)."年".substr($str,4,2)."月".substr($str,6,2)."日";
		}elseif($str == 0){
			$str = "";
		}
		return $str;
	}

	//金額(抜),金額(込)計算
	function fKingakuCalc($strKbn,$intKng,$strKazei,$strHasu,$strPayHasu,$intTax){

		//bcmul ? 2つの任意精度数値の乗算を行う
		//bcdiv ? 2つの任意精度数値の除算を行う

		//金額抜変数
		$intKngN = 0;
		//金額込変数
		$intKngK = 0;
		//消費税用変数
		$intZei = 0;

		//課税区分(1:内税,2:外税,0:非課税)
		if($strKazei == "1"){
			//消費税
			//$intZei = $intKng * ($intTax / ($intTax + 100));
			//$intZei = bcmul($intKng,bcdiv($intTax,($intTax + 100),10),3);
			$intZei = bcmul(bcdiv($intKng,($intTax + 100),2),$intTax,2);

			//消費税端数処理区分(0:切り上げ,1:切り捨て,2:四捨五入)で判断
			//切上は小数第3位まで考慮
			//四捨五入は小数第1位で判断
			if($strHasu == "0"){
				$intZei = ceil($intZei);
			}elseif($strHasu == "1"){
				$intZei = floor($intZei);
			}elseif($strHasu == "2"){
				$intZei = round($intZei);
			}

			//税抜き
			$intKngN = bcsub($intKng,$intZei,3);

			//税込み
			$intKngK = $intKng;

		}elseif($strKazei == "2"){
			//消費税
			$intZei = bcmul($intKng,bcdiv($intTax,100,10),3);
			//税抜き
			$intKngN = $intKng;

			//消費税端数処理区分(0:切り上げ,1:切り捨て,2:四捨五入)で判断
			//切上は小数第3位まで考慮
			//四捨五入は小数第1位で判断
			if($strHasu == "0"){
				$intZei = ceil($intZei);
			}elseif($strHasu == "1"){
				$intZei = floor($intZei);
			}elseif($strHasu == "2"){
				$intZei = round($intZei);
			}

			//税込み
			$intKngK = bcadd($intKng,$intZei,3);

		}else{
			//消費税
			$intZei = 0;
			//税抜き
			$intKngN = $intKng;
			//税込み
			$intKngK = $intKng;
		}



		//支払端数処理区分(0:切り上げ,1:切り捨て,2:四捨五入)で判断
		//切上は小数第3位まで考慮
		//四捨五入は小数第1位で判断
		if($strPayHasu == "0"){
			$intKngN = ceil($intKngN);
			$intKngK = ceil($intKngK);
		}elseif($strPayHasu == "1"){
			$intKngN = floor($intKngN);
			$intKngK = floor($intKngK);
		}elseif($strPayHasu == "2"){
			$intKngN = round($intKngN);
			$intKngK = round($intKngK);

		}



		//0:金額(抜)か1:金額(込)か判断
		if($strKbn == "0"){
			$intKng = $intKngN;
		}else{
			$intKng = $intKngK;
		}
		return $intKng;

	}

	/**
	 * 2つの日付の差を求める関数
	 * $year1 1つのめ日付の年
	 * $month1 1つめの日付の月
	 * $day1 1つめの日付の日
	 * $year2 2つのめ日付の年
	 * $month2 2つめの日付の月
	 * $day2 2つめの日付の日
	 */
	function fCompareDate($year1, $month1, $day1, $year2, $month2, $day2) {
	    $dt1 = mktime(0, 0, 0, $month1, $day1, $year1);
	    $dt2 = mktime(0, 0, 0, $month2, $day2, $year2);
	    $diff = $dt1 - $dt2;
	    $diffDay = $diff / 86400;//1日は86400秒
	    return $diffDay;
	}

	//nヶ月前を求める関数
	function fGetMonthAgo($year, $month, $day,$n)
    {
        // nヶ月前を正確に計算する関数
        $now_Y = $year;
        $now_m = $month;
        $now_d = $day;
        $last_d = date('d', mktime(0, 0, 0, $now_m-$n+1, 0, $now_Y));
        if ($now_d >= $last_d) {
            $n_month_ago = date("Y/m/d", mktime(0, 0, 0, $now_m-$n+1, 0, $now_Y));
        } else {
            $n_month_ago = date("Y/m/d", mktime(0, 0, 0, $now_m-$n, $now_d, $now_Y));
        }
        return $n_month_ago;
    }

    /**
    * 年月日と加算日からn日後、n日前を求める関数
    * $year 年
    * $month 月
    * $day 日
    * $addDays 加算日。マイナス指定でn日前も設定可能
    */
    function fComputeDate($year, $month, $day, $addDays,$format) {
    	$baseSec = mktime(0, 0, 0, $month, $day, $year);//基準日を秒で取得
    	$addSec = $addDays * 86400;//日数×１日の秒数
    	$targetSec = $baseSec + $addSec;
    	if($format == 0){
    		return date("Y/m/d", $targetSec);
    	}else{
    		return date("Y年m月d日", $targetSec);
    	}

    }



	//半角英数チェック関数
	function fHanEiCheck($strData){

		if(preg_match("/^[a-zA-Z0-9]+$/", $strData)){
			return true;
		} else {
  			return false;
		}
	}

	//半角チェック
	function fCheckHalf($strData){
		$ret = false;
		$len = strlen($this->fChangSJIS($strData));
		$mblen = mb_strlen($strData,mb_internal_encoding());
		//echo "len=".$len."mblen=".$mblen;
		if ($len != ($mblen*2)){
			$ret = true;
		}
		return $ret;
	}

	//全角半角スペース除去
	function fAllTrim($str){
		$str = mb_ereg_replace("^[[:space:]]+", "", $str);
		$str = mb_ereg_replace("[[:space:]]+$", "", $str);
		return $str;
	}

	//メール送信処理（期限通知情報）
	function fMailSend($aPara,$strSendCode,$strStatus,$strKbn){

		//ファイル読み込み
		require_once("module_sel.php");

		// オブジェクト作成
		$module_sel = new module_sel;


		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);
		//iniから取得
		if($aIni){
			$strMailServer = "";
			//出力先パス、ファイル名取得
			$strMailServer = $aIni['FL_INI']['MAIL_SERVER'];

			$strStmpUser = $aIni['FL_INI']['SMTP_USER'];
			$strStmpPass = $aIni['FL_INI']['SMTP_PASS'];

		}

		//宛先(マスタから取得)
		$to = $module_sel->fMailAddressGet($aPara[22],$strSendCode);

		if($to <> ""){

			$to = $to.",";
		}
		//宛先(eValueNSから取得)
		//$to = $to.$module_sel->fMailAddressGetNS($aPara[17]);
		//2019/07/24 品証担当者に変更
		$to = $to.$module_sel->fMailAddressGetNS($aPara[35]);

		//宛先があればメール送信
		if($to <> ""){

			$aTo = array();
			//アドレスを配列に格納
			if(explode(",",$to)){
				//宛先複数ある場合
				$aTo = explode(",",$to);
			}else{
				//宛先１つだけ
				$aTo[0] = $to;
			}

			//メール送信者
			$senderAddress = "announce@suzukinet.co.jp";
			$senderName = "品質管理システム";

			//メール件名
			$messageSubject = "【品質管理自動送信メール】期限通知情報(整理NO:".$aPara[0].")";

			//メール本文作成
			$messageBody = "このメールは自動配信メールです。\n";
			$messageBody = $messageBody."このメールには返信しないで下さい。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."期限切れまたは期限間近の不具合情報があります。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."整理NO：".$aPara[0]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."製品名：".$aPara[4]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."仕様番号：".$aPara[5]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."不具合区分：".$aPara[3]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."不具合内容：".$aPara[26]."\n";
			$messageBody = $messageBody."\n";

			if($strKbn == "0"){
				$messageBody = $messageBody."顧客指定回答日：".$this->fChangDateFormat($aPara[9])."\n";
				$messageBody = $messageBody."期限状態(顧客指定回答日)：".$strStatus."\n";
				$messageBody = $messageBody."\n";
			}
			//$messageBody = $messageBody."発行先区分：".$aPara[$i][13]."\n";
			//$messageBody = $messageBody."\n";

			elseif($strKbn == "1"){
				$messageBody = $messageBody."<<<品質異常改善通知書>>>\n";
				$messageBody = $messageBody."発行先名(社内)：".$aPara[14]."\n";
				$messageBody = $messageBody."品証指定回答日(社内)：".$this->fChangDateFormat($aPara[10])."\n";
				$messageBody = $messageBody."期限状態(品証指定回答日[社内])：".$strStatus."\n";
				$messageBody = $messageBody."\n";
			}

			elseif($strKbn == "2"){
				$messageBody = $messageBody."<<<不良品連絡書>>>\n";
				$messageBody = $messageBody."発行先名(協工)：".$aPara[15]."\n";
				$messageBody = $messageBody."品証指定回答日(協工)：".$this->fChangDateFormat($aPara[11])."\n";
				$messageBody = $messageBody."期限状態(品証指定回答日[協工])：".$strStatus."\n";
				$messageBody = $messageBody."\n";
			}
			//$messageBody = $messageBody."\n";
			//$messageBody = $messageBody."登録日時：".date('Y年m月d日　H時i分s秒')."\n";
			$messageBody = $messageBody.$msg."\n";
			$messageBody = $messageBody."(品質管理システム)----> http://".php_uname('n')."/FL/F_FLK0010.php?mode=2&rrcno=".$aPara[0]." \n";
			$messageBody = $messageBody.$msg."\n";

/* 			$mail = new JPHPMailer();   //文字コード設定

			//SMTP接続
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			//$mail->SMTPDebug = 2;
			$mail->SMTPSecure = 'tls';
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->Username = $strStmpUser; 	//アカウント名
			$mail->Password = $strStmpPass; 	//パスワード
			$mail->in_enc = "UTF-8"; */

			//SMTP接続
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = false;
			$mail->SMTPAutoTLS = false;
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->Username = $strStmpUser; 	//アカウント名
			$mail->Password = $strStmpPass; 	//パスワード
			$mail->CharSet = "UTF-8";
			
			$n = 0;
			while($n < count($aTo)){
				//$mail->addTo($aTo[$n]);
				$mail->addAddress($aTo[$n]);
				$n++;
			}

			$mail->setFrom($senderAddress,$senderName);
			//$mail->setSubject($messageSubject );
			//$mail->setBody($messageBody);   //添付ファイル
			$mail->Subject = $messageSubject;
			$mail->Body = $messageBody;
			//$mail->addAttachment($attachfile);
			if ($mail->send()){
				return true;
				//echo "送信されました。";
			}else{
				//error_log($mail->getErrorMessage(), 3, "log/".date("YmdHis")."_mailerror.log");
				error_log($mail->ErrorInfo, 3, "log/".date("YmdHis")."_mailerror.log");
				return false;
			}

		}
	}

	//メール送信処理(ロット有効性評価)
	function fMailSendValidity($aPara){

		//ファイル読み込み
		require_once("module_sel.php");

		// オブジェクト作成
		$module_sel = new module_sel;


		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);
		//iniから取得
		if($aIni){
			$strMailServer = "";
			//出力先パス、ファイル名取得
			$strMailServer = $aIni['FL_INI']['MAIL_SERVER'];

			$strStmpUser = $aIni['FL_INI']['SMTP_USER'];
			$strStmpPass = $aIni['FL_INI']['SMTP_PASS'];

		}

		//宛先
		$to = $module_sel->fMailAddressGet(substr($aPara[1],2,1),"VALI");

		$strValidityNM = "";
		if($aPara[0] == "1"){
			//$strValidityNM = "5ロットの出荷実績があった製品";
			//要望により通知条件変更のため表記も修正 2015/06/05 k.kume
			$strValidityNM = "5ロットの出荷実績があり最終出荷後、２ヶ月経過した製品";
		}elseif($aPara[0] == "2"){
			$strValidityNM = "1ロット以上の出荷実績があって半年経過した製品";
		}else{
			$strValidityNM = "出荷実績がないが1年経過した製品";
		}

		//宛先があればメール送信
		if($to <> ""){

			$aTo = array();
			//アドレスを配列に格納
			if(explode(",",$to)){
				//宛先複数ある場合
				$aTo = explode(",",$to);
			}else{
				//宛先１つだけ
				$aTo[0] = $to;
			}

			//メール送信者
			$senderAddress = "announce@suzukinet.co.jp";
			$senderName = "品質管理システム";

			//メール件名
			$messageSubject = "【品質管理自動送信メール】ロット有効性評価通知(整理NO:".$aPara[1].")";

			//メール本文作成
			$messageBody = "このメールは自動配信メールです。\n";
			$messageBody = $messageBody."このメールには返信しないで下さい。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."ロット有効性評価対象の不具合情報があります。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."有効性評価対象条件：".$strValidityNM."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."整理NO：".$aPara[1]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."顧客CD：".$aPara[2]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."製品CD：".$aPara[3]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."製品名：".$aPara[4]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."仕様番号：".$aPara[5]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."返却日：".$this->fChangDateFormat($aPara[11])."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."SMART2出荷日：".$this->fChangDateFormat($aPara[9])."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."SMART2出荷リール数量：".number_format($aPara[10])."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."(品質管理システム)----> http://".php_uname('n')."/FL/F_FLK0010.php?mode=2&rrcno=".$aPara[0]." \n";




/* 			$mail = new JPHPMailer();   //文字コード設定

			//SMTP接続
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			//$mail->SMTPDebug = 2;
			$mail->SMTPSecure = 'tls';
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->Username = $strStmpUser; 	//アカウント名
			$mail->Password = $strStmpPass; 	//パスワード
			$mail->in_enc = "UTF-8"; */

			//SMTP接続
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = false;
			$mail->SMTPAutoTLS = false;
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->Username = $strStmpUser; 	//アカウント名
			$mail->Password = $strStmpPass; 	//パスワード
			$mail->CharSet = "UTF-8";
			
			$n = 0;
			while($n < count($aTo)){
				//$mail->addTo($aTo[$n]);
				$mail->addAddress($aTo[$n]);
				$n++;
			}

			$mail->setFrom($senderAddress,$senderName);
			//$mail->setSubject($messageSubject );
			//$mail->setBody($messageBody);   //添付ファイル
			$mail->Subject = $messageSubject;
			$mail->Body = $messageBody;
			//$mail->addAttachment($attachfile);
			if ($mail->send()){
				return true;
				//echo "送信されました。";
			}else{
				//error_log($mail->getErrorMessage(), 3, "log/".date("YmdHis")."_mailerror.log");
				error_log($mail->ErrorInfo, 3, "log/".date("YmdHis")."_mailerror.log");
				return false;
			}

		}
	}


	//効果確認期限通知メール送信処理
	function fMailSendEffect($aPara,$strSendCode){

		//ファイル読み込み
		require_once("module_sel.php");

		// オブジェクト作成
		$module_sel = new module_sel;


		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);
		//iniから取得
		if($aIni){
			$strMailServer = "";
			//出力先パス、ファイル名取得
			$strMailServer = $aIni['FL_INI']['MAIL_SERVER'];

			$strStmpUser = $aIni['FL_INI']['SMTP_USER'];
			$strStmpPass = $aIni['FL_INI']['SMTP_PASS'];

		}

		//宛先(マスタから取得)
		$to = $module_sel->fMailAddressGet($aPara[22],$strSendCode);

		if($to <> ""){

			$to = $to.",";
		}
		//宛先(eValueNSから取得)
		//$to = $to.$module_sel->fMailAddressGetNS($aPara[17]);
		//2019/07/24 品証担当者に変更
		$to = $to.$module_sel->fMailAddressGetNS($aPara[35]);

		//宛先があればメール送信
		if($to <> ""){

			$aTo = array();
			//アドレスを配列に格納
			if(explode(",",$to)){
				//宛先複数ある場合
				$aTo = explode(",",$to);
			}else{
				//宛先１つだけ
				$aTo[0] = $to;
			}

			//メール送信者
			$senderAddress = "announce@suzukinet.co.jp";
			$senderName = "品質管理システム";

			//メール件名
			//$messageSubject = "★【不具合管理自動送信メール】(".$strKbn.")効果の確認期限通知(整理NO:".$aPara[0].")";
			$messageSubject = "★【品質管理自動送信メール】効果の確認期限通知(整理NO:".$aPara[0].")";

			//メール本文作成
			$messageBody = "このメールは自動配信メールです。\n";
			$messageBody = $messageBody."このメールには返信しないで下さい。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."効果の確認期限通知対象の不具合情報があります。\n";
			$messageBody = $messageBody."\n";
			//$messageBody = $messageBody."状態：".$strKbn."\n";
			//$messageBody = $messageBody."\n";
			$messageBody = $messageBody."整理NO：".$aPara[0]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."製品名：".$aPara[4]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."仕様番号：".$aPara[5]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."不具合区分：".$aPara[3]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."不具合内容：".$aPara[26]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."回答日：".$this->fChangDateFormat($aPara[19])."\n";
			$messageBody = $messageBody."\n";


			//$messageBody = $messageBody."\n";
			//$messageBody = $messageBody."登録日時：".date('Y年m月d日　H時i分s秒')."\n";
			$messageBody = $messageBody.$msg."\n";;
			$messageBody = $messageBody."(品質管理システム)----> http://".php_uname('n')."/FL/F_FLK0010.php?mode=2&rrcno=".$aPara[0]." \n";

			$messageBody = $messageBody.$msg."\n";

			$mail = new JPHPMailer();   //文字コード設定

			//SMTP接続
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			//$mail->SMTPDebug = 2;
			$mail->SMTPSecure = 'tls';
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->Username = $strStmpUser; 	//アカウント名
			$mail->Password = $strStmpPass; 	//パスワード
			$mail->in_enc = "UTF-8";

			//SMTP接続
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = false;
			$mail->SMTPAutoTLS = false;
			
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->Username = $strStmpUser; 	//アカウント名
			$mail->Password = $strStmpPass; 	//パスワード
			$mail->CharSet = "UTF-8";
			
			$n = 0;
			while($n < count($aTo)){
				//$mail->addTo($aTo[$n]);
				$mail->addAddress($aTo[$n]);
				$n++;
			}

			$mail->setFrom($senderAddress,$senderName);
			//$mail->setSubject($messageSubject );
			//$mail->setBody($messageBody);   //添付ファイル
			//$mail->addAttachment($attachfile);
			$mail->Subject = $messageSubject;
			$mail->Body = $messageBody;
			if ($mail->send()){
				return true;
				//echo "送信されました。";
			}else{
				//error_log($mail->getErrorMessage(), 3, "log/".date("YmdHis")."_mailerror.log");
				error_log($mail->ErrorInfo, 3, "log/".date("YmdHis")."_mailerror.log");
				return false;
			}

		}
	}
	
	
	//環境紛争鉱物情報期限通知メール(顧客指定回答日)
	function fMailSendEnv($aPara,$strSendCode,$strStatus){

		//ファイル読み込み
		require_once("module_sel.php");

		// オブジェクト作成
		$module_sel = new module_sel;


		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);
		//iniから取得
		if($aIni){
			$strMailServer = "";
			//出力先パス、ファイル名取得
			$strMailServer = $aIni['FL_INI']['MAIL_SERVER'];

			$strStmpUser = $aIni['FL_INI']['SMTP_USER'];
			$strStmpPass = $aIni['FL_INI']['SMTP_PASS'];

		}

		//宛先(マスタから取得)
		$to = $module_sel->fMailAddressGet("","ENV");

		if($to <> ""){

			$to = $to.",";
		}
		//宛先(eValueNSから取得)
		$to = $to.$module_sel->fMailAddressGetNS($aPara[34]);

		//宛先があればメール送信
		if($to <> ""){

			$aTo = array();
			//アドレスを配列に格納
			if(explode(",",$to)){
				//宛先複数ある場合
				$aTo = explode(",",$to);
			}else{
				//宛先１つだけ
				$aTo[0] = $to;
			}

			//メール送信者
			$senderAddress = "announce@suzukinet.co.jp";
			$senderName = "品質管理システム";

			//メール件名
			$messageSubject = "【品質管理自動送信メール】顧客回答期限通知情報(整理NO:".$aPara[0].")";

			//提出要求書類：
			$strSendFile = "";
			if($aPara[8] == 1){
				$strSendFile = $strSendFile."ICPデータ\n";
			}
			if($aPara[9] == 1){
				$strSendFile = $strSendFile."(M)SDS\n";
			}
			if($aPara[10] == 1){
				$strSendFile = $strSendFile."MILシート\n";
			}
			if($aPara[11] == 1){
				$strSendFile = $strSendFile."ChemSHERPAデータ\n";
			}
			if($aPara[12] == 1){
				$strSendFile = $strSendFile."MSDSplueS\n";
			}
			if($aPara[13] == 1){
				$strSendFile = $strSendFile."AIS\n";
			}
			if($aPara[14] == 1){
				$strSendFile = $strSendFile."IMDS\n";
			}
			if($aPara[15] == 1){
				$strSendFile = $strSendFile."EICC\n";
			}
			if($aPara[16] == 1){
				$strSendFile = $strSendFile."受領書\n";
			}
			if($aPara[22] == 1){
				$strSendFile = $strSendFile."その他\n";
			}
			if($aPara[23] <> ""){
				$strSendFile = $strSendFile."その他提出要求書類：".$aPara[23]."\n";
			}
			
			//メール本文作成
			$messageBody = "このメールは自動配信メールです。\n";
			$messageBody = $messageBody."このメールには返信しないで下さい。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."期限切れまたは期限間近の環境・紛争鉱物情報情報があります。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."顧客名：".$aPara[2]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."内容：".$aPara[3]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."対象製品：".$aPara[4]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."提出要求書類：\n".$strSendFile."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."顧客指定回答日：".$this->fChangDateFormat($aPara[5])."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."期限状態：".$strStatus."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."(品質管理システム)----> http://".php_uname('n')."/FL/F_FLK0060.php?mode=2&rrcno=".$aPara[0]." \n";
			$messageBody = $messageBody."\n";

			$mail = new JPHPMailer();   //文字コード設定

			//SMTP接続
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			//$mail->SMTPDebug = 2;
			$mail->SMTPSecure = 'tls';
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->Username = $strStmpUser; 	//アカウント名
			$mail->Password = $strStmpPass; 	//パスワード
			$mail->in_enc = "UTF-8";


			$n = 0;
			while($n < count($aTo)){
				$mail->addTo($aTo[$n]);
				$n++;
			}


			$mail->setFrom($senderAddress,$senderName);
			$mail->setSubject($messageSubject );
			$mail->setBody($messageBody);   //添付ファイル
			//$mail->addAttachment($attachfile);
			if ($mail->send()){
				return true;
				//echo "送信されました。";
			}else{
				error_log($mail->getErrorMessage(), 3, "log/".date("YmdHis")."_mailerror.log");
				return false;
			}

		}
	}
	
	
	//環境紛争鉱物情報期限通知メール(顧客指定回答日)
	function fMailSendEnv2($aPara,$strSendCode,$strStatus,$strMakerNm){

		//ファイル読み込み
		require_once("module_sel.php");

		// オブジェクト作成
		$module_sel = new module_sel;


		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);
		//iniから取得
		if($aIni){
			$strMailServer = "";
			//出力先パス、ファイル名取得
			$strMailServer = $aIni['FL_INI']['MAIL_SERVER'];

			$strStmpUser = $aIni['FL_INI']['SMTP_USER'];
			$strStmpPass = $aIni['FL_INI']['SMTP_PASS'];

		}

		//宛先(マスタから取得)
		$to = $module_sel->fMailAddressGet($aPara[8],"ENV");

		if($to <> ""){

			$to = $to.",";
		}
		//宛先(eValueNSから取得)
		$to = $to.$module_sel->fMailAddressGetNS($aPara[34]);

		//宛先があればメール送信
		if($to <> ""){

			$aTo = array();
			//アドレスを配列に格納
			if(explode(",",$to)){
				//宛先複数ある場合
				$aTo = explode(",",$to);
			}else{
				//宛先１つだけ
				$aTo[0] = $to;
			}

			//メール送信者
			$senderAddress = "announce@suzukinet.co.jp";
			$senderName = "品質管理システム";

			//メール件名
			$messageSubject = "【品質管理自動送信メール】調査依頼先回答期限通知情報(整理NO:".$aPara[0].")";

			//メール本文作成
			$messageBody = "このメールは自動配信メールです。\n";
			$messageBody = $messageBody."このメールには返信しないで下さい。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."品証指定回答日が期限切れまたは期限当日でずが回答未提出の依頼先があります。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."顧客名：".$aPara[1]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."内容：".$aPara[2]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."対象製品：".$aPara[3]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."回答未提出の依頼先名：\n".$strMakerNm."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."品証指定回答日：".$this->fChangDateFormat($aPara[4])."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."期限状態：".$strStatus."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."(品質管理システム)----> http://".php_uname('n')."/FL/F_FLK0060.php?mode=2&rrcno=".$aPara[0]." \n";
			$messageBody = $messageBody."\n";

			$mail = new JPHPMailer();   //文字コード設定

			//SMTP接続
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			//$mail->SMTPDebug = 2;
			$mail->SMTPSecure = 'tls';
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->Username = $strStmpUser; 	//アカウント名
			$mail->Password = $strStmpPass; 	//パスワード
			$mail->in_enc = "UTF-8";


			$n = 0;
			while($n < count($aTo)){
				$mail->addTo($aTo[$n]);
				$n++;
			}


			$mail->setFrom($senderAddress,$senderName);
			$mail->setSubject($messageSubject );
			$mail->setBody($messageBody);   //添付ファイル
			//$mail->addAttachment($attachfile);
			if ($mail->send()){
				return true;
				//echo "送信されました。";
			}else{
				error_log($mail->getErrorMessage(), 3, "log/".date("YmdHis")."_mailerror.log");
				return false;
			}

		}
	}
	
	//メール送信処理（不具合対策期限通知情報）
	function fMailSendAction($aPara){

		//ファイル読み込み
		require_once("module_sel.php");

		// オブジェクト作成
		$module_sel = new module_sel;


		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);
		//iniから取得
		if($aIni){
			$strMailServer = "";
			//出力先パス、ファイル名取得
			$strMailServer = $aIni['FL_INI']['MAIL_SERVER'];

			$strStmpUser = $aIni['FL_INI']['SMTP_USER'];
			$strStmpPass = $aIni['FL_INI']['SMTP_PASS'];

		}

		//宛先(マスタから取得)
		//引数1･･･対象部門(F or M or K)
		//引数2･･･送信先対象CD
		$to = $module_sel->fMailAddressGet($aPara[11],$aPara[10]);

		if($to <> ""){

			$to = $to.",";
		}
		//宛先(eValueNSから取得)不具合登録者
		$to = $to.$module_sel->fMailAddressGetNS($aPara[8]);

		//宛先(eValueNSから取得)トレース担当者
		$to = $to.$module_sel->fMailAddressGetNS($aPara[9]);
		
		//宛先があればメール送信
		if($to <> ""){

			$aTo = array();
			//アドレスを配列に格納
			if(explode(",",$to)){
				//宛先複数ある場合
				$aTo = explode(",",$to);
			}else{
				//宛先１つだけ
				$aTo[0] = $to;
			}

			//メール送信者
			$senderAddress = "announce@suzukinet.co.jp";
			$senderName = "品質管理システム";

			//メール件名
			$messageSubject = "【品質管理自動送信メール】トレース内容実施期限通知(整理NO:".$aPara[0].")";

			//メール本文作成
			$messageBody = "このメールは自動配信メールです。\n";
			$messageBody = $messageBody."このメールには返信しないで下さい。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."期限切れまたは期限間近のトレース内容があります。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."整理NO：".$aPara[0]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."製品名：".$aPara[1]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."仕様番号：".$aPara[2]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."不具合区分：".$aPara[3]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."不具合内容：".$aPara[4]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."トレース内容：".$aPara[5]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."実施期限：".$this->fChangDateFormat($aPara[6])."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."期限状態：".$aPara[7]."\n";
			$messageBody = $messageBody."\n";
			
			//$messageBody = $messageBody."\n";
			//$messageBody = $messageBody."登録日時：".date('Y年m月d日　H時i分s秒')."\n";
			$messageBody = $messageBody.$msg."\n";
			$messageBody = $messageBody."(品質管理システム)----> http://".php_uname('n')."/FL/F_FLK0010.php?mode=2&rrcno=".$aPara[0]." \n";
			$messageBody = $messageBody.$msg."\n";

			$mail = new JPHPMailer();   //文字コード設定

			//SMTP接続
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			//$mail->SMTPDebug = 2;
			$mail->SMTPSecure = 'tls';
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->Username = $strStmpUser; 	//アカウント名
			$mail->Password = $strStmpPass; 	//パスワード
			$mail->in_enc = "UTF-8";


			$n = 0;
			while($n < count($aTo)){
				$mail->addTo($aTo[$n]);
				$n++;
			}


			$mail->setFrom($senderAddress,$senderName);
			$mail->setSubject($messageSubject );
			$mail->setBody($messageBody);   //添付ファイル
			//$mail->addAttachment($attachfile);
			if ($mail->send()){
				return true;
				//echo "送信されました。";
			}else{
				error_log($mail->getErrorMessage(), 3, "log/".date("YmdHis")."_mailerror.log");
				return false;
			}

		}
	}

	//************************************
	//マニュアルパス作成
	//
	//************************************
	function fMakeManualPath($strPHPSELF){

		//マニュアルのアドレス
		$strManulPath = "";
		//ファイル名の取得
		$aFile = array();

		//$aFile[2]にファイル名が格納されている
		//$aFile = split("/",$strPHPSELF);
		//PHP5.3以降バージョンアップ対応 2012/05/28 k.kume
		$aFile = explode('/',$strPHPSELF);

		//$aFile = split('[.]',$aFile[2]);
		//PHP5.3以降バージョンアップ対応 2012/05/28 k.kume
		$aFile = explode('.',$aFile[2]);

		//ファイル名からPDFのパスを作成
		$strManulPath = "MANUAL/".$aFile[0].".pdf";
		//マニュアルの有無をチェック
		if(!file_exists($strManulPath)){
			$strManulPath = "";
		}else{
			$strManulPath = "<A href='".$strManulPath."' target='_blank'><nobr><img src='./gif/help.png' height='20'  width='20' border='0'></nobr></A>";
		}

		return $strManulPath;
	}

	//************************************
	//ファイルアップロード処理
	//引数
	//
	//$strKbn : 0:画像(jpeg or jpg) ,1:Excel(xls or xlsx)
	//************************************
	function fFileUpload($strPass,$strFile,$strObj,$strFileNo,$chkDelFlg,$strKbn){

		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);

		//iniから取得
		if($aIni){
			$intMaxFileSize = 0;
			//出力先パス、ファイル名取得
			$intMaxFileSize = $aIni['FL_INI']['MAX_FILE_SIZE'];

		}


		$msg = "";

		$strFileKbn1 = "";
		$strFileKbn2 = "";
		//拡張子
		$strFileExt1 = "";
		$strFileExt2 = "";

		if($strKbn == "0"){
			$strFileKbn1 = "image/jpg";
			$strFileKbn2 = "image/pjpeg";
			$strFileExt1 = ".jpg";
			$strFileExt2 = ".jpeg";
		}elseif($strKbn == "1"){
			$strFileKbn1 = "application/pdf";
			$strFileKbn2 = "";
			$strFileExt1 = ".pdf";
			$strFileExt2 = "";

		}


		//アップロードファイルオブジェクト
		$img1      = $_FILES[$strObj];
		//tmpファイル名
		$img1tmp   = $_FILES[$strObj]['tmp_name'];
		//ローカルファイル名
		$img1name  = $this->fChangSJIS($_FILES[$strObj]['name']);
		//ファイルサイズ
		$img1size  = $_FILES[$strObj]['size'];
		//ファイルの種類
		$img1type  = $_FILES[$strObj]['type'];
		//システムエラーメッセージ
		$error_msg = $_FILES[$strObj]['error'];




		//アップロードファイルか確認する
		if (is_uploaded_file($img1tmp)) {

			//ファイルサイズのチェック
			if($img1size >= $intMaxFileSize){
				$msg = "E037";
				return $msg;
			}

			//拡張子チェック
			if($strKbn == "0" && $img1type <> $strFileKbn1 && $img1type <> $strFileKbn2){
				//拡張子エラー
				$msg="E038";
				return $msg;
			}elseif($strKbn == "1" && $img1type <> $strFileKbn1 && $img1type <> $strFileKbn2){
				//拡張子エラー
				$msg="E038";
				return $msg;
			}


			//ファイルの拡張子を取得する
			$extension = pathinfo($img1name, PATHINFO_EXTENSION);

			//jpgに統一
			if($extension == "jpeg"){
				$extension = "jpg";
			}

			//ファイルネームを作成
			$strWritename=$strFile.$strFileNo.".".$extension;


			//ディレクトリがなければ作成
			if (!file_exists($strPass)){
				//フォルダ作成
				if (mkdir($strPass)){

				}else{
					$msg="E039";
					return $msg;
				}
			}

			//既存データ削除
			if(file_exists($strPass.$strFile.$extension)){
				unlink($strPass.$strFile.$extension);
			}


			//アップロードする
			$boRtn=move_uploaded_file($img1tmp,$strPass.$strWritename);

			//問題がなかった場合
			if ($boRtn){
				//$msg=$img1name."を".$writename."にリネームし、アップロードしました！";
			}
			//問題が発生した場合
			else{
				//$msg=$img1name."のアップロードに失敗しました";
				$msg="E039";
			}

		}else{
			//削除チェックがついていれば削除
// 			if($_POST[$chkDelFlg] == "1"){
// 				echo $strPass.$strFile.$extension;
				//既存データ削除
// 				if(file_exists($strPass.$strFile.$extension)){
// 					unlink($strPass.$strFile.$extension);

// 				}
// 			}
		}

		return $msg;
	}

	//************************************
	//機種依存文字変換
	//引数
	//
	//
	//************************************
	function fReplaceStrKishuizon($subject) {
		// 現在の文字コードを取得
		$_encode = mb_detect_encoding($subject, "UTF-8,SJIS-WIN,SJIS,EUC");
		// SJIS-winに変換
		if( $_encode != "SJIS-win" )   {
			mb_convert_encoding($subject, "SJIS-win", $_encode);
		}
		$search = Array('Ⅰ','Ⅱ','Ⅲ','Ⅳ','Ⅴ','Ⅵ','Ⅶ','Ⅷ','Ⅸ','Ⅹ','①','②','③','④','⑤','⑥','⑦','⑧','⑨','⑩','№','㈲','㈱');
		$replace = Array('I','II','III','IV','V','VI','VII','VIII','IX','X','(1)','(2)','(3)','(4)','(5)','(6)','(7)','(8)','(9)','(10)','No.','（有）','（株）');
		$ret = str_replace($search, $replace, $subject);
		// UTF-8に変換
		$result = mb_convert_encoding($ret, 'UTF-8', "SJIS-win");
		return $result;
	}
	
	/**
	 * ランダム文字列生成 (英数字)
	 * $length: 生成する文字数
	 */
	function fMakeRandStr($length) {
		$str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
		$r_str = null;
		for ($i = 0; $i < $length; $i++) {
			$r_str .= $str[rand(0, count($str) - 1)];
		}
		return $r_str;
	}
	
	/**
	 * 添付ファイルアップロード・削除処理
	 * $pAction:処理区分
	 * $pName:ファイルオブジェクト名
	 * $pFileName:アップロードファイルオブジェクト名
	 * $pDir:上位フォルダパス
	 * $pDirSub:下位フォルダパス
	 * $pFileKbn：ファイル区分
	 *
	 */
	function fFileUploadDelete($pAction,$pName,$pFileName,$pDir,$pDirSub,$pFileKbn) {
		
		//ファイル読み込み
		require_once("module_sel.php");

		// オブジェクト作成
		$module_sel = new module_sel;

		$strErrMsg　= "";
		
		/*-------------------------------------------------------
		   アップロードする処理
		--------------------------------------------------------*/
		if($pAction == 0){
		
			//受領書類アップロードされたファイルを取得
			$upfile = $_FILES[$pName]["name"];
			
			//ファイルが存在するかチェックする
			if(!empty ($upfile)){
			
				//ディレクトリの作成
				if(!file_exists($pDir)){
					mkdir($pDir,0777);
				}
				//アップロードフォルダの作成
				if(!file_exists($pDirSub)){
					mkdir($pDirSub,0777);
				}
				 
				//ファイル重複チェックするためにディレクトリー内のファイルを取得する
				$filelist=scandir($pDirSub);
				
				foreach($filelist as $file){
				
					//is_dir関数でディレクトリー以外のファイルを調べる
					if(!is_dir($file)){
						if($upfile==$file){
							$er["double"]="重複してるのでアップロードできません。";
							$strErrMsg = $module_sel->fMsgSearch("E042",$pFileKbn);
						}
					}
					
				}
				
				
				//エラーの配列をチェックして空だった場合・・つまりエラーがなければアップロードする
				if(empty ($er)){
					//ファイルアップロード処理
					if(!move_uploaded_file($_FILES[$pName]["tmp_name"],$pDirSub.addslashes(mb_convert_encoding($upfile,"SJIS","AUTO")))){
						$strErrMsg = $module_sel->fMsgSearch("E008","");
					}
				}
			
			}else{
				
				$strErrMsg = $module_sel->fMsgSearch("E041",$pFileKbn);
				
			}
		/*-------------------------------------------------------
		   削除する処理
		--------------------------------------------------------*/
		//削除ボタンが押された場合
		}elseif($pAction == 1){
			
			//チェックされたファイル名を取得
			$deletefiles=$_POST[$pFileName];
			
			//ファイルがアップロードされたかチェックする
			if(!empty ($deletefiles)){
				
				//チェックされた画像の数だけforeachで回す
				//ファイルが実際に存在していた場合にunlink関数で画像ファイルを削除する
				foreach($deletefiles as $dfile){
					//5C問題でfile_existsは使用できない
					//if(file_exists($module_cmn->fChangSJIS_5C($dirGet.$dfile))){
						unlink($this->fChangSJIS_5C($pDirSub.$dfile));
					//}
				}
			}
		}
		return $strErrMsg;
	}
	
//2019/08/01 AD START T.FUJITA
	//協力工場不良品連絡書の期限切れ通知メール送信処理
	function fMailSendProcessLimit($aPara,$strSendCode){

		//ファイル読み込み
		require_once("module_sel.php");
		$module_sel = new module_sel;

		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);
		//iniから取得
		if($aIni){
			$strMailServer = "";
			//出力先パス、ファイル名取得
 			$strMailServer = $aIni['FL_INI']['MAIL_SERVER'];
			$strStmpUser = $aIni['FL_INI']['SMTP_USER'];
			$strStmpPass = $aIni['FL_INI']['SMTP_PASS'];
		}

		//宛先(マスタから取得)
		$to = $module_sel->fMailAddressGet($aPara[2],$strSendCode);

		if($to <> ""){
			$to = $to.",";
		}
		
		//宛先があればメール送信
		if($to <> ""){
			$aTo = array();
			//アドレスを配列に格納
			if(explode(",",$to)){
				//宛先複数ある場合
				$aTo = explode(",",$to);
			}else{
				//宛先１つだけ
				$aTo[0] = $to;
			}
			//社内（F,K）は品質改善報告書、社外（その他）は協力工場不良品連絡書
			if(substr($aPara[15],0,1) == "F" or substr($aPara[15],0,1) == "K"){
				$sDocuNm = "品質改善報告書";
			}else{
				$sDocuNm = "協力工場不良品連絡書";
			}
			
			//メール送信者
			$senderAddress = "announce@suzukinet.co.jp";
			$senderName = "品質管理システム";

			//メール件名
			$messageSubject = "【品質管理自動送信メール】".$sDocuNm."催促通知_".$aPara[0];

			//メール本文作成
			$messageBody = "このメールは自動配信メールです。\n";
			$messageBody = $messageBody."このメールには返信しないで下さい。\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."下記の".$sDocuNm."が未返却です。\n";
			$messageBody = $messageBody."至急ご提出頂くか、期限の延長依頼をして下さい。\n";
			$messageBody = $messageBody."\n";
			switch($aPara[2]){
				case 'F':
					$messageBody = $messageBody."対象部門：コネクタ\n";
					break;
				case 'M':
					$messageBody = $messageBody."対象部門：モールド\n";
					break;
				case 'K':
					$messageBody = $messageBody."対象部門：めっき\n";
					break;
				default:
					$messageBody = $messageBody."対象部門：不明\n";
					break;
			}
			$messageBody = $messageBody."伝票NO：".$aPara[0]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."製品名：".$aPara[6]."\n";
			$messageBody = $messageBody."仕様番号：".$aPara[7]."\n";
			$messageBody = $messageBody."得意先名：".$aPara[8]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."報告書発行先：".$aPara[9]."\n";
			$messageBody = $messageBody."報告書処理期限：".$this->fChangDateFormat4($aPara[10])."\n";
			$messageBody = $messageBody."不具合ロットNO：".$aPara[11]."\n";
			$messageBody = $messageBody."不具合数量：".$aPara[12]."\n";
			$messageBody = $messageBody."不具合金額：".$aPara[13]."\n";
			$messageBody = $messageBody."不具合内容：".$aPara[14]."\n";
			$messageBody = $messageBody."\n";
			$messageBody = $messageBody."(品質管理システム)----> http://".php_uname('n')."/FL/F_FLK0080.php?mode=2&rrcno=".$aPara[0]."&rrcseq=".$aPara[1]." \n";
			$messageBody = $messageBody.$msg."\n";
			
			//SMTP接続
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->SMTPAuth = false;
			$mail->SMTPSecure = false;
			$mail->SMTPAutoTLS = false;
			
			$mail->Host = $strMailServer;
			$mail->Port = 25;
			$mail->CharSet = "UTF-8";
			
			$n = 0;
			while($n < count($aTo)){
				$mail->addAddress($aTo[$n]);
				$n++;
			}
			
			$mail->setFrom($senderAddress,$senderName);
			$mail->Subject = $messageSubject;
			$mail->Body = $messageBody;
			//通知メール送信処理
			if ($mail->send()){
				//echo "送信されました。";
			}else{
				error_log($mail->ErrorInfo, 3, "log/".date("YmdHis")."_mailerror.log");
				return false;
			}
		}
	}
	//2019/08/01 AD END T.FUJITA
	
}


?>