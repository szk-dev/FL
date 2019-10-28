<?php
	//****************************************************************************
	//プログラム名：メール配信マスタ一覧照会
	//プログラムID：F_MST0020
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2012/05/31
	//履歴　　　　：
	//
	//****************************************************************************
	/* 現在のキャッシュリミッタを取得または設定する */
	session_cache_limiter('private, must-revalidate');
	/* セッション開始 */
	session_start();

	//セッションチェック
	if(empty($_SESSION["login"][0])){
		//セッションが空の場合はエラー画面へ遷移
		header("location: http://".$_SERVER["SERVER_NAME"]."/FL/err.php");
		exit;
	}

	$token = sha1(uniqid(mt_rand(), true));

	// トークンをセッションに追加する
	$_SESSION['token'][] = $token;

	//ファイル読み込み
	require_once("module_sel.php");
	require_once("module_upd.php");
	require_once("module_common.php");

	// オブジェクト作成
	$module_sel = new module_sel;
	$module_upd = new module_upd;
	$module_cmn = new module_common;


	//画面遷移先を取得
	if(isset($_GET['action'])) {
		$action = $_GET['action'];
	}
	//一覧の検索条件の保管変数
	if(isset($_GET['aJoken'])) {
		$aJoken = array();
		$aJoken = $_GET['aJoken'];
	}

	//引数の切替
	//一覧内での遷移
	if($action == "menu"){
		$sCustCd = $module_cmn->fEscape($_POST['sCustCd']);
		$sCustNm = $module_cmn->fEscape($_POST['sCustNm']);
		$sCustNmK = $module_cmn->fEscape($_POST['sCustNmK']);

	//メンテ画面からの遷移
	}elseif($action == "main"){
		$sCustCd = $module_cmn->fEscape($aJoken[0]);
		$sCustNm = $module_cmn->fEscape($aJoken[1]);
		$sCustNmK = $module_cmn->fEscape($aJoken[2]);
	}

	//検索条件格納用配列
	$aJoken = array();

	$aJoken[0] = $sCustCd;
	$aJoken[1] = $sCustNm;
	$aJoken[2] = $sCustNmK;

	$aPara = Array();
	
	//検索処理開始
	if(isset($_GET['search'])){
		//検索条件取得
		if($_GET['search'] == "1"){
			
			//検索処理(件数)
			$aPara = $module_sel->fCustMailSendList($aJoken);

			//最大件数オーバーの場合
			if($aPara[0][0] == "E016" ){
				$strErrMsg = $module_sel->fMsgSearch("E016","最大表示件数：200件");
			}
			//該当件数がなければメッセージ表示
			elseif($aPara[0][0] == "N006" ){
				$strErrMsg = $module_sel->fMsgSearch("N006","");
			}

		}
	}

	//Excel出力
	if(isset($_GET['excel'])){
		if($_GET['excel'] == "1"){

			/** パスの設定（PHPExcel.phpまで届くようにパスを設定します） **/
			//set_include_path(get_include_path() .'/Classes');
			/** PHPExcel ここでPHPExcel.phpを相対パスで直接指定すれば上のパスの設定はなくても大丈夫です。*/
			// 'PHPExcel.php';
			//include 'PHPExcel/Writer/Excel2007.php';

			require_once '/Classes/PHPExcel.php';
			require_once '/Classes/PHPExcel/IOFactory.php';

			$fileName = mb_convert_encoding("test.xls", 'SJIS','UTF-8');
			$sheetName = mb_convert_encoding("sheet", 'SJIS','UTF-8');

			//ブラウザへ出力をリダイレクト
			header('Content-Type: application/vnd.ms-excel');
			header("Content-Disposition: attachment;filename=".$fileName);
			header('Cache-Control: max-age=0');


			//オブジェクトの生成
			$xl = new PHPExcel();

			//シートの設定
			$xl->setActiveSheetIndex(0);
			$sheet = $xl->getActiveSheet();
			$sheet->setTitle($sheetName);

			//セルの値を設定
			$sheet->setCellValue('A1', mb_convert_encoding('PHPExcelテスト', 'SJIS')); //文字列
			//$sheet->setCellValue('B2', 123);            //数値
			//$sheet->setCellValue('C3', '=B2-100');      //計算式
			//$sheet->setCellValue('D4', true);           //真偽値
			//$sheet->setCellValue('E5', false);          //真偽値

			//スタイルの設定(標準フォント、罫線、中央揃え)
			//$sheet->getDefaultStyle()->getFont()->setName('ＭＳ Ｐゴシック');
			//$sheet->getDefaultStyle()->getFont()->setSize(11);
			$sheet->getStyle('C3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$sheet->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			//Excel5形式で保存
			$writer = PHPExcel_IOFactory::createWriter($xl, 'Excel5');
			$writer->save('php://output');

			//exit;


		}
	}

	//マニュアルパス取得
	$strManulPath = "";
	$strManulPath = $module_cmn->fMakeManualPath($_SERVER["PHP_SELF"]);

?>
<HTML>
<HEAD>
<META name="GENERATOR" content="IBM WebSphere Studio Homepage Builder Version 11.0.0.0 for Windows">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" >
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE></TITLE>
<link rel="stylesheet" type="text/css" href="css/common.css">
<script type="text/javascript" src="js/common.js"><!-- 共通javascript読み込み --></script>
<script type="text/javascript" >
<!-- 個別javascript記述 -->

	//「戻る」ボタン
	function fReturn(){
		location.href="main.php";
	}

	/* メール配信マスタメンテ画面表示 */
	function fMailDisp(strMode,strCustCd){
		var aJoken = new Array(4);
		//GETで渡す引数なのでURLエンコードを行う
		aJoken[0] = encodeURI(document.form.sCustCd.value);
		aJoken[1] = encodeURI(document.form.sCustNm.value);
		aJoken[2] = encodeURI(document.form.sCustNmK.value);

		location.href="F_MST0021.php?mode=" + strMode + "&strCustCd=" + strCustCd + "&aJoken[0]=" + aJoken[0] + "&aJoken[1]=" + aJoken[1] + "&aJoken[2]=" + aJoken[2];

	}

	/* 検索 */
	function fncSearch(){

		document.form.target ="main";
		document.form.action ="F_MST0020.php?action=menu&search=1";
		document.form.submit();
	}


	/* Excel出力 */
	function fncExcelOut(){

		//確認メッセージ
		if(window.confirm('Excel出力してもよろしいですか？')){

			document.form.target ="main";
			document.form.action ="F_MST0020.php?action=menu&search=1&excel=1";
			document.form.submit();

		}else{
			return false;
		}
	}


</script>

</HEAD>
<BODY style="font-size : medium;border-collapse : separate;" onload=fLoadDisplay();>

<form name="form" method="post" action="" onSubmit="">
<TABLE border="0" bgcolor="#000066">
  <TBODY>
    <TR>
      <TD align="center" width="1000"><SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【取引先メールマスタ一覧照会】</SPAN></TD>
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
?>

<?php
//エラーメッセージの有無を判断して表示
if ($strErrMsg <> ""){
?>
<TABLE border="0" bgcolor="#FFFFFF" >
  <TBODY>
    <TR  >
       <!-- メッセージ区分で色分 -->
      <?php if(substr($strErrMsg,0,1) == "N"){ ?>
      <TD class="tdnone" align="center" width="1000" ><B><FONT color="#0000FF" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
      <?php }elseif(substr($strErrMsg,0,1) == "E"){ ?>
      <TD class="tdnone" align="center" width="1000" ><B><FONT color="#FF0000" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
	  <?php }elseif(substr($strErrMsg,0,1) == "W"){ ?>
	  <TD class="tdnone" align="center" width="1000" ><B><FONT color="#FFFF00" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
	  <?php } ?>
	</TR>
  </TBODY>
</TABLE>
<?php
}
?>

<TABLE border="0">
  <TBODY>
    <TR>
	<TD class="tdnone" width="800" >
		<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
			<FONT color="#ffffff"><B>検索条件</B></FONT>
		</DIV>
      </TD>
      <TD class="tdnone" width="200" align="right">
      	<INPUT type="button" name="btnSearch" value="　戻　る　" onClick="fReturn();">
      	<?php echo $strManulPath;  ?>
      </TD>

    </TR>
  </TBODY>
</TABLE>
<TABLE class="tbline" width="800" >

  <TBODY>
    <TR>
      <TD class="tdnone2" width="88" ><B>取引先ｺｰﾄﾞ</B></TD>
      <TD class="tdnone3" width="68" ><B><INPUT size="5" type="text" name="sCustCd" maxlength=5 style="ime-mode: disabled;" value="<?php echo $sCustCd; ?>" ></B></TD>
      <TD class="tdnone2" width="73" ><B>取引先名</B></TD>
      <TD class="tdnone3" width="150" ><INPUT size="20" type="text" name="sCustNm" maxlength=80 value="<?php echo $sCustNm; ?>"></TD>
      <TD class="tdnone2" width="99" ><B>取引先名ｶﾅ</B></TD>
      <TD class="tdnone3" width="150" ><INPUT size="20" type="text" name="sCustNmK" maxlength=80 value="<?php echo $sCustNmK; ?>"></TD>
    </TR>
  </TBODY>
</TABLE>
<br>
<P><INPUT type='button' name='btnSearch' value='　検　索　' onClick='fncSearch()'>　<INPUT type="reset" name="btnReset" value="　リセット　">

</P>
</FORM>
<br>

<?php
//検索時にエラーがない場合は表示。
if ($strErrMsg == "" ){

	//検索結果があれば
	if(count($aPara) > 0 ){
?>

		<!-- ヘッダー部出力 -->
		<HR style="height:2px; border:none; background:linear-gradient(to right,#999999,transparent)">
		<br>
		<TABLE border='0'>
		<TBODY>
		<TR>
		<TD class='tdnone' width='800'>
			<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
				<FONT color="#ffffff"><B>結果一覧</B></FONT>
			</DIV>
		</TD>
		</TR>
		</TBODY>
		</TABLE>
		<P><FONT size='-1'><IMG src='./gif/yaji.gif' width='9' height='11' border='0'><IMG src='./gif/yaji.gif' width='9' height='11' border='0'>　検索結果は<?php echo count($aPara); ?>件です</FONT><BR>
		</P>
		<TABLE class='tbline' width='1000' >
		<TBODY>

<?php
			$i = 0;
			while ($i < count($aPara)){
				//奇数行、偶数行によって色変更
				if(($i % 2) == 0){
					$strClass = "tdnone3";
				}else{
					$strClass = "tdnone4";
				}
				//ヘッダーの挿入(20行毎)
				if($i%20 == 0){
					echo "<TR height='15'>";
			  		echo "<TD class='tdnone2' width='75'><B>取引先ｺｰﾄﾞ</B></TD>";
			 		echo "<TD class='tdnone2' width='133'><B>取引先名</B></TD>";
				    echo "<TD class='tdnone2' width='163'><B>取引先名ｶﾅ</B></TD>";
				    echo "<TD class='tdnone2' width='500'><B>メールアドレス(先頭80文字のみ表示,詳細は右の「更新」をクリック)</B></TD>";
				    echo "<TD class='tdnone5' align='center' width='30' ><B>ｱｸｼｮﾝ</B></TD>";
					echo "</TR>";
				}

	    		echo "<TR height='15'>";

	     	 	echo "<TD class='".$strClass."'>".$aPara[$i][0]."</TD>";
	      		echo "<TD class='".$strClass."'>".$aPara[$i][1]."</TD>";
			    echo "<TD class='".$strClass."'>".$aPara[$i][2]."</TD>";
			    echo "<TD class='".$strClass."'>".substr($aPara[$i][3],0,80)."</TD>";
			    echo "<TD class='hpb-cnt-tb-cell4' align='center' >";
			    echo "<INPUT type='button' value='更新' style='background-color : #fdc257;' onClick='fMailDisp(\"2\",\"".$aPara[$i][0]."\");'>";
			    //echo "<INPUT type='button' value='削除' style='background-color : #fdc257;' onClick='fMailDisp(\"3\",\"".$aPara[$i][0]."\");'>";
			    //echo "<INPUT type='button' value='参照' style='background-color : #fdc257;' onClick='fMailDisp(\"4\",\"".$aPara[$i][0]."\");'>";
			    echo "</TD>";
	    		echo "</TR>";

				$i = $i + 1;
			}

?>
			</TBODY>
			</TABLE>
			<P><BR>

			</P>
			<P><FONT size='-1'><IMG src='./gif/yaji.gif' width='9' height='11' border='0'><IMG src='./gif/yaji.gif' width='9' height='11' border='0'>　検索結果は<?php echo count($aPara); ?>件です</FONT></P>
			<!--<INPUT type='button' name='btnExcelOut' value='Excel出力' onClick='fncExcelOut()'>-->
<?php
		}
	}
?>


</BODY>
</HTML>
