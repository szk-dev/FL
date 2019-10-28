<?php
	//****************************************************************************
	//プログラム名：ロット有効性評価通知
	//プログラムID：F_FLK0110
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/12/01
	//履歴　　　　：
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
	$aPara = $module_sel->fFlawValidityNotice();
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

		//ロット有効性評価通知メール送信
		if($module_cmn->fMailSendValidity($aPara[$i])){
			//通知後不具合情報更新

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
      【ロット有効性評価通知】
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