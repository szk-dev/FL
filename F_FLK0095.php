<?php
	//****************************************************************************
	//プログラム名：協力工場品質評価表出力
	//プログラムID：F_FLK0095
	//作成者　　　：㈱鈴木　藤田
	//作成日　　　：2019/09/20
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

	//トークンをセッションに追加する
	$_SESSION['token'][] = $token;

	//ファイル読み込み
	require_once 'vendor/autoload.php';
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Chart\Chart;
	use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
	use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
	use PhpOffice\PhpSpreadsheet\Chart\Layout;
	use PhpOffice\PhpSpreadsheet\Chart\Legend;
	use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
	use PhpOffice\PhpSpreadsheet\Chart\Title;
	
	require_once("module_sel.php");
	require_once("module_upd.php");
	require_once("module_common.php");

	//オブジェクト作成
	$module_sel = new module_sel;
	$module_upd = new module_upd;
	$module_cmn = new module_common;

	//メッセージ用変数
	$strMsg = "";
	
	//画面遷移先を取得
	if(isset($_GET['action'])) {
		$action = $_GET['action'];
	}
	
	//セッション取得
	if(isset($_SESSION['login'][0]) == true  ){
		$snm = $_SESSION['login'][1];
	}else{
		$snm = "";
	}
	if(isset($_SESSION['login'][2]) == true  ){
		$bnm = $_SESSION['login'][3];
	}else{
		$bnm = "";
	}
	
	//品証以外の社員は制限設ける
	if(substr($_SESSION['login'][2],0,3) <> '117'){
		$strLock = "";
	}
	
	//引数の取得
	$setsum = "-1";
	$output = "-1";
	if(isset($_GET['mode'])) {
		$mode = $_GET['mode'];
	}
	if(isset($_GET['setsum'])) {
		$setsum = $_GET['setsum'];
	}
	if(isset($_GET['output'])) {
		$output = $_GET['output'];
	}
	
	//オブジェクトロック用変数
	$strLock = "";
	
	//ボタン表示フラグ(表示:true,非表示:false)
	$bDispflg = true;
	
	//eValueNS集計担当者グループ未所属の場合集計ボタン非活性
	if($module_sel->fChkMstUserNS($_SESSION['login'][0]) === 0){
		$bDispflg = false;
	}
	
	//出力時はデータ取得
	if($output<>"-1"){
		$sTaishoYm = $module_cmn->fEscape($_POST['sTaishoYm']);		//対象年月
		
		//必須チェック
		$strErrMsg = $strErrMsg.$module_cmn->fCmbNCheck($sTaishoYm,"対象年月");
		
		if($strErrMsg == ""){
			//期の計算
			if($sTaishoYm-(floor($sTaishoYm/100)*100) >= 7){
				$hidTaishoKi = (floor($sTaishoYm/100)-1968);
			}else{
				$hidTaishoKi = (floor($sTaishoYm/100)-1969);
			}
			//処理用パラメータセット
			$aPara = Array();
			$aPara[0] = "SZK01";
			$aPara[1] = "F";
			$aPara[2] = $sTaishoYm;
			$aPara[3] = "WEBAPPSV";
			$aPara[4] = $hidTaishoKi;
			$aPara[5] = "コネクタ";
			$aPara[6] = 0;
		}
	}else{
		//引数の初期設定
		$sTaishoYm = date("Ym",mktime(0,0,0,date("m")-1,1,date("Y")));	//対象月（固定値：当月）
		//期の計算
		if($sTaishoYm-(floor($sTaishoYm/100)*100) >= 7){
			$hidTaishoKi = (floor($sTaishoYm/100)-1968);
		}else{
			$hidTaishoKi = (floor($sTaishoYm/100)-1969);
		}
	}
	
	//出力処理
	if($output<>-1 and $strErrMsg == ""){
		if($_GET['output'] == "1"){
			//セルの複製でメモリ消費
			ini_set("memory_limit", "-1");
			
			//協力工場品質評価データ取得
			$aResKyoData = $module_sel->fGetKyoData($aPara);
			if($aResKyoData[0][0] == -1){
				$strMsg = $module_sel->fMsgSearch("E001","協力工場品質評価データ取得エラー");
				goto proc_exit;
			}
			
/* 			//協力工場不良内容取得
			$aResKyoDet = Array();
			$aResKyoDet = $module_sel->fGetKyoDet($aPara);
			if($aResKyoDet[0][0] == -1){
				$strMsg = $module_sel->fMsgSearch("E001","不良内容取得出力エラー");
				goto proc_exit;
			}
*/
			$strImportFile = mb_convert_encoding("協力工場品質評価_雛型.xlsx","SJIS","UTF-8");
			$strExportFile = mb_convert_encoding($aPara[4]."協力工場品質評価".$aPara[5]."_".substr($aPara[2],4,2)."月度.xlsx","SJIS","UTF-8");
			
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			$reader->setIncludeCharts(true);
			$spreadsheet = $reader->load("template/".$strImportFile);
			
			//ブラウザへ出力をリダイレクト
			header("Content-Description: File Transfer");
			header('Content-Disposition: attachment; filename='.$strExportFile);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			ob_end_clean(); //バッファ消去
			ob_start();
			
			$aTrhksk = Array();
			
			if(count($aResKyoData[1]) > 1){
				//集計
				$spreadsheet->setactivesheetindexbyname("集計");
				$sheet = $spreadsheet->getActiveSheet();
				
				//開始列
				$iRows = 3;
				$iColST = 29;
				do{
					$iFlg = 0;
					for($iCols=0;$iCols<=6; $iCols++){
						switch($iCols){
							case 0:$sM = "07";break;
							case 1:$sM = "08";break;
							case 2:$sM = "09";break;
							case 3:$sM = "10";break;
							case 4:$sM = "11";break;
							case 5:$sM = "12";break;
							case 6:$sM = "01";break;
						}
						$sCd = $sheet->getCellByColumnAndRow(1,$iRows)->getValue();
						if($iFlg == 0){
							if(array_search(($sCd.$sM),$aResKyoData[1]) !== false){
								$iArrS = array_search(($sCd.$sM),$aResKyoData[1]);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows,$aResKyoData[5][$iArrS]);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+1,$aResKyoData[8][$iArrS]);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+2,$aResKyoData[9][$iArrS]);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+3,$aResKyoData[6][$iArrS]);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+4,$aResKyoData[7][$iArrS]);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+5,$aResKyoData[10][$iArrS]);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+6,$aResKyoData[11][$iArrS]);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+7,$aResKyoData[12][$iArrS]);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+8,$aResKyoData[13][$iArrS]);
							}else{
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows,0);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+1,0);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+2,0);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+3,0);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+4,0);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+5,0);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+6,0);
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+7,"");
								$sheet->setCellValueByColumnAndRow($iCols+$iColST,$iRows+8,"");
							}
						}
						if($sM == substr($aPara[2],4,2)){
							$iFlg = 1;
						}
					}
					$iRows = $iRows + 9;
				}while($sheet->getCellByColumnAndRow(1,$iRows)->getValue() <> "");
				
				//評価表
				$spreadsheet->setactivesheetindexbyname("評価表");
				$sheet = $spreadsheet->getActiveSheet();
				$intRowSt= 7;
				
				$iArr = 0;
				
				//タイトル
				$sheet->setCellValue("B3",intval(substr($aPara[2],5,2))."月度 品質評価連絡書");
				
				//月設定
				$aMonth = Array();
				$iArr = 0;
				switch(substr($aPara[2],4,2)){
					case "07":$sM = "02";break;
					case "08":$sM = "03";break;
					case "09":$sM = "04";break;
					case "10":$sM = "05";break;
					case "11":$sM = "06";break;
					case "12":$sM = "07";break;
					case "01":$sM = "08";break;
					case "02":$sM = "09";break;
					case "03":$sM = "10";break;
					case "04":$sM = "11";break;
					case "05":$sM = "12";break;
					case "06":$sM = "01";break;
				}
				for($iCols=6;$iCols<=16; $iCols=$iCols+2){
					$aMonth[$iArr] = $sM;
					$sheet->setCellValueByColumnAndRow($iCols,7,intval($sM));
					if(intval($sM)>=12){
						$sM = "01";
					}else{
						$sM = str_pad(intval($sM + 1),2,0, STR_PAD_LEFT);
					}
					$iArr = $iArr + 1;
				}
				$iRows = 9;
				$iTrhksk = 0;
				do{
					$sCd = $sheet->getCellByColumnAndRow(1,$iRows)->getValue();
					//対象取引先CD保管
					$aTrhksk[$iTrhksk][0] = $sheet->getCellByColumnAndRow(1,$iRows)->getValue();
					$aTrhksk[$iTrhksk][1] = $sheet->getCellByColumnAndRow(3,$iRows)->getValue();
					
					$iArr = 0;
					for($iCols=6;$iCols<=16; $iCols=$iCols+2){
						if(array_search(($sCd.$aMonth[$iArr]),$aResKyoData[1]) !== false){
							$iArrS = array_search(($sCd.$aMonth[$iArr]),$aResKyoData[1]);
							$sheet->setCellValueByColumnAndRow($iCols,$iRows,$aResKyoData[13][$iArrS]);
							//カンドリ工業は順位「-」固定
							if($sCd=='S1108'){
								$sheet->setCellValueByColumnAndRow($iCols+1,$iRows,"-");
							}else{
								$sheet->setCellValueByColumnAndRow($iCols+1,$iRows,$aResKyoData[14][$iArrS]);
							}
						}else{
							$sheet->setCellValueByColumnAndRow($iCols,$iRows,"-");
							$sheet->setCellValueByColumnAndRow($iCols+1,$iRows,"-");
						}
						$iArr = $iArr + 1;
					}
					$iRows = $iRows+1;
					$iTrhksk = $iTrhksk + 1;
				}while($sheet->getCellByColumnAndRow(1,$iRows)->getValue() <> "");
				
				//A列非表示
				$sheet->getColumnDimension('A')->setVisible( false );
				
				//アクティブセル設定
				$sheet->getStyle('B1');
			}
			
/* 
			//不具合内容
			if(count($aResKyoDet) > 1){
				$spreadsheet->setactivesheetindexbyname("不具合内容");
				$sheet = $spreadsheet->getActiveSheet();

				$iRowStTmp = $intRowKyoDet;
				for($iRows=0;$iRows<count($aResKyoDet); ++$iRows){
					$sheet->setCellValueByColumnAndRow(1,$iRowStTmp + $iRows,$aResKyoDet[$iRows][1]);
					$sheet->setCellValueByColumnAndRow(2,$iRowStTmp + $iRows,$aResKyoDet[$iRows][2]);
					$sheet->setCellValueByColumnAndRow(3,$iRowStTmp + $iRows,$aResKyoDet[$iRows][3]);
					$sheet->setCellValueByColumnAndRow(4,$iRowStTmp + $iRows,$aResKyoDet[$iRows][4]);
					$sheet->setCellValueByColumnAndRow(5,$iRowStTmp + $iRows,$aResKyoDet[$iRows][5]);
					$sheet->setCellValueByColumnAndRow(6,$iRowStTmp + $iRows,$aResKyoDet[$iRows][6]);
				}
				//アクティブセル設定
				$sheet->getStyle('A1');
			}
*/

			//連絡書
			$aNewSheet = Array();
			//データソース
			$aXDataSource = Array();
			$iTrhkskRow = 9;
			if(count($aTrhksk) > 0){
				$spreadsheet->setactivesheetindexbyname("連絡書");
				$sheet = $spreadsheet->getActiveSheet();
				$sheetOri = clone $sheet;
				$sSheetNm = $sheetOri->getTitle();
				
				//連絡書シート複製
				for($i=0;$i<count($aTrhksk); $i++){
				//for($i=0;$i<22; $i++){
					$aNewSheet[$i] = clone $sheetOri;
					$aNewSheet[$i]->setTitle($sSheetNm."_".$aTrhksk[$i][1]);
					$spreadsheet->addSheet($aNewSheet[$i]);
					
					$spreadsheet->setactivesheetindexbyname($sSheetNm."_".$aTrhksk[$i][1]);
					$sheet = $spreadsheet->getActiveSheet();
					//$aXDataSource[$i] = '集計!$E$'.(1+($iTrhkskRow*($i+1))).':$AN$'.(1+($iTrhkskRow*($i+1))).'\'';
					
					//対象月
					$sheet->setCellValue("A3",intval(substr($aPara[2],5,2)));
					//タイトル
					$sheet->setCellValue("B5",$aTrhksk[$i][1]."御中");
					//集計データ出力
					$sCd = $aTrhksk[$i][0];
					$iCols=3;
					$iFlg = 0;
					
					for($j=1;$j<=12; $j++){
						switch($j){
							case 1:$sM = "07";break;
							case 2:$sM = "08";break;
							case 3:$sM = "09";break;
							case 4:$sM = "10";break;
							case 5:$sM = "11";break;
							case 6:$sM = "12";break;
							case 7:$sM = "01";break;
							case 8:$sM = "02";break;
							case 9:$sM = "03";break;
							case 10:$sM = "04";break;
							case 11:$sM = "05";break;
							case 12:$sM = "06";break;
						}
						if(array_search(($sCd.$sM),$aResKyoData[1]) !== false and $iFlg == 0){
							$iArrS = array_search(($sCd.$sM),$aResKyoData[1]);
							$sheet->setCellValueByColumnAndRow($iCols,9,$aResKyoData[5][$iArrS]);
							$sheet->setCellValueByColumnAndRow($iCols,10,$aResKyoData[8][$iArrS]);
							$sheet->setCellValueByColumnAndRow($iCols,11,$aResKyoData[9][$iArrS]);
							$sheet->setCellValueByColumnAndRow($iCols,12,$aResKyoData[6][$iArrS]);
							$sheet->setCellValueByColumnAndRow($iCols,13,$aResKyoData[7][$iArrS]);
							$sheet->setCellValueByColumnAndRow($iCols,14,$aResKyoData[12][$iArrS]);
							$sheet->setCellValueByColumnAndRow($iCols,15,$aResKyoData[13][$iArrS]);
						}else{
							$sheet->setCellValueByColumnAndRow($iCols,9,"-");
							$sheet->setCellValueByColumnAndRow($iCols,10,"-");
							$sheet->setCellValueByColumnAndRow($iCols,11,"-");
							$sheet->setCellValueByColumnAndRow($iCols,12,"-");
							$sheet->setCellValueByColumnAndRow($iCols,13,"-");
							$sheet->setCellValueByColumnAndRow($iCols,14,"-");
							$sheet->setCellValueByColumnAndRow($iCols,15,"-");
						}
						
						if($sM == substr($aPara[2],4,2)){
							$iFlg = 1;
						}
						$iCols = $iCols + 1;
					}
					
					//通信欄
					//$sheet->setCellValue("L34","　御協力ありがとうございます。");
					//$sheet->setCellValue("L35","　次月度も宜しくお願い致します。");
					

					
					//アクティブセル設定
					$sheet->getStyle('A1');
				}
			}

			//元シート非表示
			//$spreadsheet->removeSheetByIndex(2);
			$spreadsheet->setActiveSheetIndex(2);
			$spreadsheet->getActiveSheet()->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
			//初期表示シート移動
			$spreadsheet->setActiveSheetIndex(0);
			$sheet = $spreadsheet->getActiveSheet();
			
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet,"Xlsx");
			$writer->setProgramId("F_FLK0095");
			//$writer->setXDataSource($aXDataSource);
			$writer->setIncludeCharts(true);
			$writer->save('php://output');
			exit;
		}
		
		//正常処理
		$strMsg = "N001 出力しました";
	}
	
	proc_exit:
	
	//日本語を省くための正規表現
	$pattern="/^[a-z0-9A-Z\-_]+\.[a-zA-Z]{3}$/";

	//マニュアルパス取得
	$strManulPath = "";
	$strManulPath = $module_cmn->fMakeManualPath($_SERVER["PHP_SELF"]);

?>
<HTML>
<HEAD>
<META name="GENERATOR" content="IBM WebSphere Studio Homepage Builder Version 11.0.0.0 for Windows">
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<?php
	//読み込み中の画像を表示してからリダイレクトして検索に移る（POST値はここでリセットが掛かるため引き継げない）
	//if($search == 1){
		//echo "処理開始";
	//	echo '<meta http-equiv="refresh" content="1;URL=F_FLK0095.php?setsum=2">';
	//}
?>
<TITLE>【協力工場品質評価表出力（コネクタ）】</TITLE>

<style type="text/css">
	
TABLE.type08 {
	border-collapse: collapse;
	text-align: left;
	line-height: 1.5;
	border-left: 1px solid #ccc;
}

TABLE.type08 THEAD th {
	padding: 10px;
	font-weight: bold;
	border-top: 1px solid #ccc;
	border-right: 1px solid #ccc;
	border-bottom: 2px solid #c00;
	background: #dcdcd1;
}
TABLE.type08 TBODY th {
	width: 150px;
	padding: 10px;
	font-weight: bold;
	vertical-align: top;
	border-right: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
	background: #ececec;
}
TABLE.type08 td {
	width: 350px;
	padding: 10px;
	vertical-align: top;
	border-right: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
}
</style>

<link rel="stylesheet" type="text/css" href="css/common.css">
<link rel="stylesheet" href="js/protocalendar/stylesheets/paper.css" type="text/css" media="all">
<script type="text/javascript" src="js/prototype.js"></script>
<script src="js/protocalendar/lib/effects.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script src="js/protocalendar/javascripts/protocalendar.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script src="js/protocalendar/javascripts/lang_ja.js" type="text/javascript" ><!-- Ajax用スクリプト --></script>
<script type="text/javascript" src="js/common.js"><!-- 共通javascript読み込み --></script>
<script type="text/javascript" >
<!-- 個別javascript記述 -->

	//戻るボタン
	function fReturn(){
		document.form.target ="main";
		document.form.action ="main.php";
		document.form.submit();
	}

	//エクセル出力ボタン
	function fOutPut(){
			//確認メッセージ
			if(window.confirm('出力してもよろしいですか？')){
				//document.form.btnOutPut.disabled = true;
				//document.form.btnSum.disabled = true;
				document.form.target ="main";
				document.form.action ="F_FLK0095.php?output=1";
				document.form.submit();
			}else{
				return false;
			}
	}
	
</script>
</HEAD>
<BODY style="font-size : medium;border-collapse : separate;">
<TABLE border="0" bgcolor="#000066">
	<TBODY>
		<TR>
			<TD align="center" width="1000"><SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【協力工場品質評価表出力（コネクタ）】
			</SPAN></TD>
		</TR>
	</TBODY>
</TABLE>
<br>
<INPUT type="hidden" name="token" value="<?php echo $token; ?>">

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
    <TR>
      <TD class="tdnone" align="center" width="1000"  ><B><FONT color="#ff0000" size="2px"><?php echo $strErrMsg; ?></FONT></B></TD>
    </TR>
  </TBODY>
</TABLE>
<?php
}
?>

<FORM id="form" name="form" method="post" enctype="multipart/form-data\" onSubmit="return false;">
<TABLE border="0">
		<TBODY>
		<TR>
			<TD width="800" class="tdnone">
				<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
					<FONT color="#ffffff"><B>出力情報</B></FONT>
				</DIV>
			</TD>
			<TD class="tdnone" width="200" align="right">
				<?php if($hidFrame == 0){ ?>
				<INPUT type="button" name="btnSearch" tabindex="600" value="　戻　る　" onClick="fReturn(<?php echo $mode;?>)">
				<?php } ?>
				<?php echo $strManulPath;  ?>
			</TD>
		</TR>
	</TBODY>
</TABLE>

<TABLE class="tbline" width="210">
	<TBODY>
		<TR>
			<TD class="tdnone1" width="90">対象年月</TD>
			<TD class="tdnone3" width="120">
			<SELECT name="sTaishoYm" id="sTaishoYm" tabindex="30">
				<OPTION selected value="-1">▼選択して下さい</OPTION>
				<OPTION value=<?php echo date("Ym") ?>><?php echo date("Y年m月") ?></OPTION>
				<OPTION value=<?php echo date("Ym",mktime(0,0,0,date("m")-1,1,date("Y"))) ?>><?php echo date("Y年m月",mktime(0,0,0,date("m")-1,1,date("Y"))) ?></OPTION>
			</SELECT>
			</TD>
		</TR>
	</TBODY>
</TABLE>
<P>※稼働日4日目以降に出力すること</P>
<BR>
<P>
<?php
	//品証のユーザのみ表示
	if(substr($_SESSION['login'][2],0,3) == "117"){
?>
		<INPUT type="button" name="btnOutPut" value="　出　力　" onClick="fOutPut()" tabindex="100">
<?php
	}

?>
</P>

<INPUT type="hidden" name="hidTantoCd" value="<?php echo $_SESSION['login'][0];?>">
<INPUT type="hidden" name="hidFrame" value="<?php echo $hidFrame; ?>">
<INPUT type="hidden" name="hidTaishoKi" value="<?php echo $hidTaishoKi; ?>">
</FORM>
<script type="text/javascript" >
	/* 初期フォーカス */
	document.getElementById('sTaishoYm').focus();
</script>

</BODY>
</HTML>