<?php
	//****************************************************************************
	//プログラム名：品質評価集計表出力（モールド）
	//プログラムID：F_FLK0092
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
	
	//集計／出力時はデータ取得
	if($setsum<>"-1" || $output<>"-1"){
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
			$aPara[1] = "M";
			$aPara[2] = $sTaishoYm;
			$aPara[3] = "WEBAPPSV";
			$aPara[4] = $hidTaishoKi;
			$aPara[5] = "モールド";
			$aPara[6] = 3;
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
	
	//集計処理
/* 	if($setsum<>"-1" and $strErrMsg == ""){
		//品質評価集計表データの集計
		if($setsum == "1"){
			//前月の場合、担当者以外不可
			if($sTaishoYm <> date("Ym") and $bDispflg === false){
				$strMsg = "E000 前月データの集計処理は行えません";
				goto proc_exit;
			}
			//品質評価集計表データ登録
			if($module_upd->fUpdTrblHyoka($aPara,$_SESSION["login"]) == -1){
				$strMsg = "E001 集計処理に失敗しました";
				goto proc_exit;
			}
		}
		//正常処理
		$strMsg = "N001 集計しました";
	} */
	
	//出力処理
	if($output<>-1 and $strErrMsg == ""){
		//品質評価集計表出力
		if($_GET['output'] == "1"){
			//部門不良品管理表
			$aResRnk = $module_sel->fGetTrblDisposalRnk($aPara);
			if($aResRnk[0][0] == -1){
				$strMsg = $module_sel->fMsgSearch("E001","部門不良品管理表データ出力エラー");
				goto proc_exit;
			}
			
			//客先不良件数
 			$aResQty = $module_sel->fGetFlawQty($aPara);
			if($aResQty[0][0] == -1){
				$strMsg = $module_sel->fMsgSearch("E001","客先不良件数データ出力エラー");
				goto proc_exit;
			} 
			
			//客先不良内容取得
			$aResDesc = $module_sel->fGetFlawDesc($aPara);
			if($aResDesc[0][0] == -1){
				$strMsg = $module_sel->fMsgSearch("E001","客先不良内容データ出力エラー");
				goto proc_exit;
			}
			
			$strImportFile = mb_convert_encoding("品質評価集計".$aPara[5]."_雛型.xlsx","SJIS","UTF-8");
			$strExportFile = mb_convert_encoding($aPara[4]."期品質評価集計表".$aPara[5]."_".substr($aPara[2],4,2)."月度.xlsx","SJIS","UTF-8");
			
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
			
			//シート数
			$iSheetQty = $aPara[6];
			//各シート出力開始行
			$intRowRnk = 4;
			$intRowBat = 5;
			$intRowBatG = 5;
			$intColCust = 2;
			$intColSum = 2;
			$intRowHoryu = 3;
			
			for($iSheet=0;$iSheet<$iSheetQty; ++$iSheet){
				//シート移動
				$spreadsheet->setActiveSheetIndex($iSheet);
				$sheet = $spreadsheet->getActiveSheet();
				
				switch ($iSheet){
					case 0:		//品質評価集計表
						//タイトル
						$sheet->setCellValue("A2",$aPara[4]."期 部品製造部（".$aPara[5]."） 品質評価集計表");
						//期
						$sheet->setCellValue("T1",$aPara[4]);
						
/* 								if(count($aResHyoka) > 1){
							$iCols = -1;
							
							for($iRows=0;$iRows<count($aResHyoka); ++$iRows){
								switch(substr($aResHyoka[$iRows][1],4,2)){
									case "00":$iCols = 4;break;
									case "07":$iCols = 5;break;
									case "08":$iCols = 6;break;
									case "09":$iCols = 7;break;
									case "10":$iCols = 8;break;
									case "11":$iCols = 9;break;
									case "12":$iCols = 10;break;
									case "01":$iCols = 11;break;
									case "02":$iCols = 12;break;
									case "03":$iCols = 13;break;
									case "04":$iCols = 14;break;
									case "05":$iCols = 15;break;
									case "06":$iCols = 16;break;
								}
								$sheet->setCellValueByColumnAndRow($iCols,11,$aResHyoka[$iRows][2]);
								$sheet->setCellValueByColumnAndRow($iCols,12,$aResHyoka[$iRows][3]);
								$sheet->setCellValueByColumnAndRow($iCols,13,$aResHyoka[$iRows][4]);
								$sheet->setCellValueByColumnAndRow($iCols,15,$aResHyoka[$iRows][5]);
								$sheet->setCellValueByColumnAndRow($iCols,16,$aResHyoka[$iRows][6]);
								$sheet->setCellValueByColumnAndRow($iCols,17,$aResHyoka[$iRows][7]);
								$sheet->setCellValueByColumnAndRow($iCols,18,$aResHyoka[$iRows][8]);
								$sheet->setCellValueByColumnAndRow($iCols,22,$aResHyoka[$iRows][9]);
								$sheet->setCellValueByColumnAndRow($iCols,24,$aResHyoka[$iRows][10]);
								$sheet->setCellValueByColumnAndRow($iCols,26,$aResHyoka[$iRows][11]);
								$sheet->setCellValueByColumnAndRow($iCols,28,$aResHyoka[$iRows][12]);
								$sheet->setCellValueByColumnAndRow($iCols,30,$aResHyoka[$iRows][13]);
								$sheet->setCellValueByColumnAndRow($iCols,32,$aResHyoka[$iRows][14]);
							}
							$sheet->setCellValue("Z1",count($aResHyoka));
							$sheet->setCellValue("R40","※特別作業時間 ".$aResHyoka[count($aResHyoka)-1][15]." Ｈ");
						} */
						break;
					case 1:		//部門不良品管理表
						//タイトル
						$sheet->setCellValue("B1",$aPara[5]."部門不良品管理表（廃棄分）".intval(substr($aPara[2],0,4)).".".intval(substr($aPara[2],4,2))."月度");
						
						if(count($aResRnk) > 0 && $aResRnk[0][1]<>""){
							$iRowStTmp = $intRowRnk;
							$sheet->insertNewRowBefore($intRowRnk+1,count($aResRnk)-1);
							for($iRows=0;$iRows<count($aResRnk); ++$iRows){
								$sheet->setCellValueByColumnAndRow(1,$intRowRnk + $iRows,$aResRnk[$iRows][1]);
								$sheet->setCellValueByColumnAndRow(2,$intRowRnk + $iRows,$aResRnk[$iRows][2]);
								$sheet->setCellValueByColumnAndRow(3,$intRowRnk + $iRows,$aResRnk[$iRows][3]);
								$sheet->setCellValueByColumnAndRow(4,$intRowRnk + $iRows,$aResRnk[$iRows][4]);
								$sheet->setCellValueByColumnAndRow(5,$intRowRnk + $iRows,$aResRnk[$iRows][5]);
								$sheet->setCellValueByColumnAndRow(6,$intRowRnk + $iRows,$aResRnk[$iRows][6]);
								$sheet->setCellValueByColumnAndRow(7,$intRowRnk + $iRows,$aResRnk[$iRows][7]);
								$sheet->setCellValueByColumnAndRow(8,$intRowRnk + $iRows,$aResRnk[$iRows][8]);
								$sheet->setCellValueByColumnAndRow(9,$intRowRnk + $iRows,$aResRnk[$iRows][9]);
								$sheet->setCellValueByColumnAndRow(10,$intRowRnk + $iRows,$aResRnk[$iRows][10]);
								$sheet->setCellValueByColumnAndRow(11,$intRowRnk + $iRows,$aResRnk[$iRows][11]);
								$sheet->setCellValueByColumnAndRow(12,$intRowRnk + $iRows,$aResRnk[$iRows][12]);
								$sheet->setCellValueByColumnAndRow(13,$intRowRnk + $iRows,$aResRnk[$iRows][13]);
								$sheet->setCellValueByColumnAndRow(14,$intRowRnk + $iRows,$aResRnk[$iRows][14]);
								$sheet->setCellValueByColumnAndRow(15,$intRowRnk + $iRows,$aResRnk[$iRows][15]);
								$sheet->setCellValueByColumnAndRow(16,$intRowRnk + $iRows,$aResRnk[$iRows][16]);
								$sheet->setCellValueByColumnAndRow(17,$intRowRnk + $iRows,$aResRnk[$iRows][17]);
								$sheet->setCellValueByColumnAndRow(18,$intRowRnk + $iRows,$aResRnk[$iRows][18]);
								$sheet->setCellValueByColumnAndRow(19,$intRowRnk + $iRows,$aResRnk[$iRows][19]);
								$sheet->setCellValueByColumnAndRow(20,$intRowRnk + $iRows,$aResRnk[$iRows][20]);
								
								if($sRnkTmp <> $aResRnk[$iRows][1] && $iRows > 0){
									//結合
									$sheet->mergeCells('A'.$iRowStTmp.':A'.($intRowRnk + $iRows - 1));
									$sheet->mergeCells('T'.$iRowStTmp.':T'.($intRowRnk + $iRows - 1));
									//設定する
									$iRowStTmp = $intRowRnk + $iRows;
								}else{
									//罫線
									if($iRows<>0){
										$borders = $sheet->getStyle('B'.($intRowRnk + $iRows-1).':S'.($intRowRnk + $iRows-1))->getBorders();
										$borders ->getBottom()->setBorderStyle('dashed');
									}
								}
								$sRnkTmp = $aResRnk[$iRows][1];
							}
							if($iRowStTmp <> count($aResRnk)-1){
								//結合
								$sheet->mergeCells('A'.$iRowStTmp.':A'.($intRowRnk + count($aResRnk)-1));
								$sheet->mergeCells('T'.$iRowStTmp.':T'.($intRowRnk + count($aResRnk)-1));
							}
							$borders = $sheet->getStyle('A'.($intRowRnk + count($aResRnk)).':T'.($intRowRnk + count($aResRnk)))->getBorders();
							$borders ->getTop()->setBorderStyle('double');
							//総数
							$sheet->setCellValueByColumnAndRow(14,$intRowRnk + count($aResRnk),"=SUM(N".$intRowRnk.":N".($intRowRnk + count($aResRnk)-1).")");
							$sheet->setCellValueByColumnAndRow(19,$intRowRnk + count($aResRnk),"=SUM(S".$intRowRnk.":S".($intRowRnk + count($aResRnk)-1).")");
							$sheet->setCellValueByColumnAndRow(20,$intRowRnk + count($aResRnk),"=SUM(T".$intRowRnk.":T".($intRowRnk + count($aResRnk)-1).")");
						}
						break;
					case 2:		//客先不良件数
						//期
						$sheet->setCellValue("R1",$aPara[4]);
						if(count($aResQty) > 0 && $aResQty[0][1]<>""){
							$iColStTmp = $intColCust;
							$sY = $aPara[4]+1968;
							$iFlg = 0;
							
							switch(substr($aPara[2],4,2)){
								case "07":$iColMax = 4;break;
								case "08":$iColMax = 5;break;
								case "09":$iColMax = 6;break;
								case "10":$iColMax = 7;break;
								case "11":$iColMax = 8;break;
								case "12":$iColMax = 9;break;
								case "01":$iColMax = 10;break;
								case "02":$iColMax = 11;break;
								case "03":$iColMax = 12;break;
								case "04":$iColMax = 13;break;
								case "05":$iColMax = 14;break;
								case "06":$iColMax = 15;break;
								default: $iColMax = 2;
							}
							for($iCols=2;$iCols<=$iColMax; $iCols++){
								$sheet->setCellValueByColumnAndRow($iCols,4,0);
								$sheet->setCellValueByColumnAndRow($iCols,5,0);
								$sheet->setCellValueByColumnAndRow($iCols,7,0);
								$sheet->setCellValueByColumnAndRow($iCols,8,0);
								$sheet->setCellValueByColumnAndRow($iCols,9,0);
							}
							
							for($iArr=0;$iArr<count($aResQty); ++$iArr){
								switch($aResQty[$iArr][1]){
									case ($sY-2)."00":$iCols = 0;break;
									case ($sY-1)."00":$iCols = 1;break;
									case substr($aPara[2],0,4)."07":$iCols = 2;break;
									case substr($aPara[2],0,4)."08":$iCols = 3;break;
									case substr($aPara[2],0,4)."09":$iCols = 4;break;
									case substr($aPara[2],0,4)."10":$iCols = 5;break;
									case substr($aPara[2],0,4)."11":$iCols = 6;break;
									case substr($aPara[2],0,4)."12":$iCols = 7;break;
									case substr($aPara[2],0,4)."01":$iCols = 8;break;
									case substr($aPara[2],0,4)."02":$iCols = 9;break;
									case substr($aPara[2],0,4)."03":$iCols = 10;break;
									case substr($aPara[2],0,4)."04":$iCols = 11;break;
									default: $iCols = -1;
								}
								if($iCols<>-1){
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,4,$aResQty[$iArr][2]);
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,5,$aResQty[$iArr][3]);
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,7,$aResQty[$iArr][4]);
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,8,$aResQty[$iArr][5]);
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,9,$aResQty[$iArr][6]);
								}else{
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,4,0);
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,5,0);
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,7,0);
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,8,0);
									$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,9,0);
								}
							}
							
/* 							$xAxisTickValues = [
								new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '$B$3:$O$3', null, 5),
							];
							$dataSeriesValues = [
								new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, '$B$5:$O$5', null, 5),
							];
							$series = new DataSeries(
								DataSeries::TYPE_BARCHART, // plotType
								DataSeries::GROUPING_STANDARD, // plotGrouping
								range(0, count($dataSeriesValues) - 1), // plotOrder
								[], // plotLabel
								$xAxisTickValues, // plotCategory
								$dataSeriesValues // plotValues
							);

							$series->setPlotDirection(DataSeries::DIRECTION_COL);
							
							$plotArea = new PlotArea(null,[$series]);
							
							$title = new Title('客先不良件数');
							
							$yAxisLabel = new Title('（件）');
							
							$chart = new Chart(
								'bar chart', // name
								$title, // title
								null, // legend
								$plotArea, // plotArea
								true, // plotVisibleOnly
								0, // displayBlanksAs
								null, // xAxisLabel
								$yAxisLabel  // yAxisLabel
							);
							
							$chart->setTopLeftPosition('B12');
							$chart->setBottomRightPosition('P38');
							
							$sheet->addChart($chart); */
						}
						
						//内容コメント表示
						if(count($aResDesc) > 0 && $aResDesc[0][1]<>""){
							for($iRows=0;$iRows<count($aResDesc); ++$iRows){
								switch($aResDesc[$iRows][1]){
									case "07":$iCols = 4;break;
									case "08":$iCols = 5;break;
									case "09":$iCols = 6;break;
									case "10":$iCols = 7;break;
									case "11":$iCols = 8;break;
									case "12":$iCols = 9;break;
									case "01":$iCols = 10;break;
									case "02":$iCols = 11;break;
									case "03":$iCols = 12;break;
									case "04":$iCols = 13;break;
									case "05":$iCols = 14;break;
									case "06":$iCols = 15;break;
								}
								//不良
								if($aResDesc[$iRows][4] == 1){
									$iLine = 5;
								//クレーム
								}elseif($aResDesc[$iRows][4] == 3){
									$iLine = 4;
								//調査中
								}else{
									$iLine = 9;
								}
								$commentRichText = $sheet->getCommentByColumnAndRow($iCols,$iLine)->getText()->createTextRun($aResDesc[$iRows][2]."\r\n");
								$commentRichText->getFont()->setSize(9);
/*
								if($aResDesc[$iRows][3] == 2){
									$commentRichText->getFont()->getColor()->setARGB('00FF66FF');
								}
*/
								$commentRichText = $sheet->getCommentByColumnAndRow($iCols,$iLine)->setWidth('400px');
								$commentRichText = $sheet->getCommentByColumnAndRow($iCols,$iLine)->setHeight('110px');
							}
						}
						
						break;
				}

				//アクティブセル設定
				$sheet->getStyle('A1');
			}
			//初期表示シート移動
			$spreadsheet->setActiveSheetIndex(0);
			$sheet = $spreadsheet->getActiveSheet();
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet,"Xlsx");
			$writer->setProgramId("F_FLK0092");
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

	$aRireki = array();
	//集計履歴の取得
	$aRireki = $module_sel->fGetRenkeiRireki("F_FLK0092",$_SESSION['login']);
	
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
	//	echo '<meta http-equiv="refresh" content="1;URL=F_FLK0092.php?setsum=2">';
	//}
?>
<TITLE>【品質評価集計表出力（モールド）】</TITLE>

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
				document.form.action ="F_FLK0092.php?output=1";
				document.form.submit();
			}else{
				return false;
			}
	}

	//自動出力ボタン
	function fSum(){
			//確認メッセージ
			if(window.confirm('集計してもよろしいですか？')){
				//document.form.btnOutPut.disabled = true;
				//document.form.btnSum.disabled = true;
				document.form.target ="main";
				document.form.action ="F_FLK0092.php?setsum=1";
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
			<TD align="center" width="1000"><SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【品質評価集計表出力（モールド）】
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
<BR>

<P>
<?php
	//品証のユーザのみ表示
	if(substr($_SESSION['login'][2],0,3) == "117"){
?>
		<INPUT type="button" name="btnOutPut" value="　出　力　" onClick="fOutPut()" tabindex="100">
		<!--<INPUT type="button" name="btnSum" value="　集　計　" onClick="fSum()" tabindex="110" style="margin-left:20px">-->
<?php
	}

?>
</P>

<br><br>

<?php
//履歴件数があったら
if(count($aRireki) > 0){
?>
	<TABLE border="0">
		<TBODY>
			<TR>
				<TD class="tdnone">
				<DIV style="width:300;background:linear-gradient(to right,#000066,transparent); padding:5px 5px 5px 20px;">
					<FONT color="#ffffff"><B>集計履歴情報</B></FONT></DIV>
				</TD>
			</TR>
		</TBODY>
	</TABLE>
	<TABLE class="tbline" width="395">
		<TBODY>
			<TR class="tdnone3">
			  <TD class="tdnone2" width="20" align="center" nowrap></TD>
			  <TD class="tdnone2" width="140" align="center" nowrap>集計日</TD>
			  <TD class="tdnone2" width="85" align="center" nowrap>対象月</TD>
			  <TD class="tdnone2" width="105" align="center" nowrap>処理者</TD>
			  <TD class='tdnone5' width='45' align='center' nowrap>対象</TD>
			</TR>
<?php
			$i = 0;
	  		while ($i < count($aRireki)){
				//奇数行、偶数行によって色変更
				if(($i % 2) == 0){
					$strClass = "tdnone3";
				}else{
					$strClass = "tdnone4";
				}
				echo "<TR height='15'>";
				echo "<TD class='".$strClass."' width='20'> </TD>";
				echo "<TD class='".$strClass."'>".$module_cmn->fChangDateTimeFormat($aRireki[$i][0])."</TD>";
				echo "<TD class='".$strClass."'>".substr($aRireki[$i][3],0,4)."年".substr($aRireki[$i][3],4,2)."月"."</TD>";
				echo "<TD class='".$strClass."'>".$aRireki[$i][4]."</TD>";
				echo "<TD class='".$strClass."' align='center' nowrap>";
				echo "<INPUT type='button' value='適用' style='background-color : #fdc257;' onClick='fStartDownload(\"".$dir."\");' ".$sVouBtnDis.">";
				echo "</TD>";
				echo "</TR>";
				$i = $i + 1;
			}
?>
		</TBODY>
	</TABLE>

<?php
}
?>

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