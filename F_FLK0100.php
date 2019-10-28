<?php
	//****************************************************************************
	//プログラム名：期限切れメール通知
	//プログラムID：F_FLK0100
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/08/12
	//履歴　　　　：2017/09/11 環境紛争鉱物情報追加 k.kume
	//　　　　　　　：2018/06/14 不具合対策入力情報追加 k.kume
	//　　　　　　　：2019/07/25 期限切れ通知先変更(登録者→品証担当者) k.kume
	//
	//****************************************************************************
	//ファイル読み込み
	require_once("module_sel.php");
	require_once("module_upd.php");
	require_once("module_common.php");

	// オブジェクト作成
	$module_sel = new module_sel;
	$module_upd = new module_upd;
	$module_cmn = new module_common;

	$aPara = array();


	//不具合情報データ検索処理
	$aPara = $module_sel->fFlawSearch($_SESSION['login'],$aJoken,$module_sel->fWorkCalender());
	$i = 0;


	//ライブラリ読み込み
	require("/jphpmailer.php");
	//言語設定、内部エンコーディングを指定する
	mb_language("japanese");
	mb_internal_encoding("UTF-8");

	//文字コード設定
//	ini_set('mbstring.internal_encoding','UTF-8');
//	ini_set('mbstring.http_output','UTF-8');
//	ini_set('mbstring.script_encoding','UTF-8');


	//1件でもあればメール送信
	while($i < count($aPara) ){

		//顧客指定回答日
		$strLimitCustS = "";
		if($aPara[$i][31] == "limit"){
			$strLimitCustS = "×期限切)";
		}elseif($aPara[$i][31] == "near"){
			$strLimitCustS = "△期限間近";
		}

		//品証指定回答日(社内)
		$strLimitS1 = "";
		if($aPara[$i][32] == "limit"){
			$strLimitS1 = "×期限切";
		}elseif($aPara[$i][32] == "near"){
			$strLimitS1 = "△期限間近";
		}

		//品証指定回答日(協工)
		$strLimitS2 = "";
		if($aPara[$i][33] == "limit"){
			$strLimitS2 = "×期限切";
		}elseif($aPara[$i][33] == "near"){
			$strLimitS2 = "△期限間近";
		}

		//顧客指定回答日対象メール送信
		if($strLimitCustS <> ""){
			$module_cmn->fMailSend($aPara[$i],$aPara[$i][23],$strLimitCustS,"0");
		}

		//品証指定回答日(社内)対象メール送信
		//if($strLimitS1 <> ""){
		//	$module_cmn->fMailSend($aPara[$i],$aPara[$i][24],$strLimitS1,"1");
		//}
		//品証指定回答日(協工)対象メール送信
		//if($strLimitS2 <> ""){
		//	$module_cmn->fMailSend($aPara[$i],$aPara[$i][25],$strLimitS2,"2");
		//}


		$i++;

	}

	/////////////////////////////////////////////////////////////////////////////
	//環境紛争鉱物情報通知追加(客先指定回答日期限切れ通知)
	$aPara = array();

	//環境紛争鉱物情報データ検索処理
	$aPara = $module_sel->fEnvSearch($_SESSION['login'],$aJoken,$module_sel->fWorkCalender());
	$i = 0;

	//当日
	$today = date("Ymd");
	
	//1件でもあればメール送信
	while($i < count($aPara) ){

	
		//顧客指定回答日
		$strLimitCustS = "";
		if($aPara[$i][51] == "limit"){
			$strLimitCustS = "×期限切";
		}elseif($aPara[$i][51] == "near"){
			$strLimitCustS = "△期限間近";
		}

		//顧客指定回答日対象メール送信
		if($strLimitCustS <> ""){
	
			$module_cmn->fMailSendEnv($aPara[$i],"",$strLimitCustS);
		}

		$i++;

	}
	/////////////////////////////////////////////////////////////////////////////

	/////////////////////////////////////////////////////////////////////////////
	//環境紛争鉱物情報通知追加(品証指定回答日期限切れ通知)
	$aPara = array();

	//環境紛争鉱物情報データ検索処理
	$aPara = $module_sel->fGetEnvPcAleartData();
	$i = 0;

	
	//整理NO退避用変数
	$strBeforeRrcno = "";
	//メーカー名
	$strMakerNm = "";
	
	
	//1件でもあればメール送信
	while($i < count($aPara)){
		//初回
		if($i == 0){
			$strBeforeRrcno = $aPara[$i][0];
		}
		
		
		//整理NOが切り替わる場合
		if(($strBeforeRrcno <> $aPara[$i][0])){
			//品証指定回答日
			$strLimitPcS = "";
			if($aPara[$i - 1][10] == "limit"){
				$strLimitPcS = "×期限切";
			}elseif($aPara[$i - 1][10] == "near"){
				$strLimitPcS = "△期限当日";
			}

			//期限切れ対象の場合
			if($strLimitPcS <> ""){
				//メール送信
				$module_cmn->fMailSendEnv2($aPara[$i - 1],"",$strLimitPcS,$strMakerNm);
			}
			//メーカー名
			$strMakerNm = "";
			
		}
		
		//メーカー名
		$strMakerNm = $strMakerNm.$aPara[$i][5]."\n";
		
		//最終データ名の場合
		if($i == (count($aPara) - 1)){
			
			//品証指定回答日
			$strLimitPcS = "";
			if($aPara[$i][10] == "limit"){
				$strLimitPcS = "×期限切";
			}elseif($aPara[$i][10] == "near"){
				$strLimitPcS = "△期限当日";
			}
			//期限切れ対象の場合
			if($strLimitPcS <> ""){
				//メール送信
				$module_cmn->fMailSendEnv2($aPara[$i],"",$strLimitPcS,$strMakerNm);
			
			}
			
		}
		
		//前回値退避
		$strBeforeRrcno = $aPara[$i][0];
		
		
		$i++;
		
	}
	/////////////////////////////////////////////////////////////////////////////


	$aPara = array();


	//不具合対策情報期限切れデータ検索処理
	$aPara = $module_sel->fGetActionAleartData();
	$i = 0;

	//1件でもあればメール送信
	while($i < count($aPara) ){

		//メール送信
		$module_cmn->fMailSendAction($aPara[$i],"");
		
		$i++;

	}





	?>
<HTML>
<HEAD>
<META name="GENERATOR" content="IBM WebSphere Studio Homepage Builder Version 11.0.0.0 for Windows">
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE></TITLE>
<link rel="stylesheet" type="text/css" href="css/common.css">
</HEAD>
<BODY style="font-size : medium;border-collapse : separate;" >
<FORM name="form" method="post" onSubmit="return false;">
<input type="hidden" name="token" value="<?php echo $token; ?>">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000">
      <SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">
      【期限切れメール通知】
      </SPAN>
      </TD>
    </TR>
  </TBODY>
</TABLE>
<br>

<?php
//メッセージの有無を判断して表示
if ($strMsg <> ""){
?>
<TABLE border="0" bgcolor="#FFFFFF" >
  <TBODY>
    <TR >
      <!-- メッセージ区分で色分 -->
      <?php if(substr($strMsg,0,1) == "N"){ ?>
      <TD class="tdnone" align="center" width="1000" ><B><FONT color="#0000FF" size="2px"><?php echo $strMsg; ?></FONT></B></TD>
      <?php }elseif(substr($strMsg,0,1) == "E"){ ?>
      <TD class="tdnone" align="center" width="1000" ><B><FONT color="#FF0000" size="2px"><?php echo $strMsg; ?></FONT></B></TD>
	  <?php }elseif(substr($strMsg,0,1) == "W"){ ?>
	  <TD class="tdnone" align="center" width="1000" ><B><FONT color="#FFFF00" size="2px"><?php echo $strMsg; ?></FONT></B></TD>
	  <?php } ?>
    </TR>
  </TBODY>
</TABLE>
<?php
}
//エラーメッセージの有無を判断して表示
if ($strErrMsg <> ""){
?>
<TABLE border="0" bgcolor="#FFFFFF" >
  <TBODY>
    <TR  >
      <TD class="tdnone" align="center" width="1000"  ><B><FONT color="#ff0000" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
    </TR>
  </TBODY>
</TABLE>
<?php
}

?>




<br>
</FORM>
</BODY>
</HTML>