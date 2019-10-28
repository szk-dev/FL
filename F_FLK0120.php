<?php
	//****************************************************************************
	//プログラム名： 効果確認期限通知メール通知
	//プログラムID：F_FLK0120
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2013/05/29
	//履歴　　　　：2013/08/07 通知条件変更(回答日の2週間後に1回のみ→回答日の3週間後の3日前から対策効果確認日が0の間は通知を続ける)
	//
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

		$strBeforeAlertMeaYmd = "";
		$strAlertMeaYmd = "";

		//以下の条件で通知
		//・対策日(aPara[$i][34])が入っている
		//・対策効果確認日($aPara[$i][28])が未入力
		//・効果通知あり($aPara[$i][27])
		//の場合
		if($aPara[$i][34] <> "0" && $aPara[$i][28] == "0" && $aPara[$i][27] == 1){

			//対策日を取得
			$strMeaYmd = $module_cmn->fChangDateFormat($aPara[$i][34]);

			//対策日の3週間後の17日後を計算
			$strBeforeAlertMeaYmd = date("Y/m/d", strtotime($strMeaYmd." 17 day" ));
			//対策日の20日後
			$strAlertMeaYmd = date("Y/m/d", strtotime($strMeaYmd." 20 day" ));


			//効果確認通知があり(=1)で対策日から20日経過した場合
			if($strAlertMeaYmd < date("Y/m/d")){

				//効果確認期限通知メール送信(期限切)
				$module_cmn->fMailSendEffect($aPara[$i],$aPara[$i][23],"期限切");
			}elseif($strAlertMeaYmd == date("Y/m/d")){

				//効果確認期限通知メール送信(当日)
				$module_cmn->fMailSendEffect($aPara[$i],$aPara[$i][23],"当日");

			}elseif($strBeforeAlertMeaYmd <= date("Y/m/d")){
				//効果確認期限通知メール送信(期限前)
				$module_cmn->fMailSendEffect($aPara[$i],$aPara[$i][23],"期限前");
			}

		}


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
      【効果確認期限通知処理】
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