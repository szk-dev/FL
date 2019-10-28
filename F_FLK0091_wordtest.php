<?php
	//****************************************************************************
	//プログラム名：品質評価集計表
	//プログラムID：F_FLK0091
	//作成者　　　：㈱鈴木　藤田
	//作成日　　　：2019/08/01
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
		$cmbDocu_KBN = $_POST['cmbDocu_KBN'];						//対象資料
		$cmbTargetSection_KBN = $_POST['cmbTargetSection_KBN'];		//対象部門
		$sTaishoYm = $module_cmn->fEscape($_POST['sTaishoYm']);		//対象年月
		
		//期の計算
		if($sTaishoYm-(floor($sTaishoYm/100)*100) >= 7){
			$hidTaishoKi = (floor($sTaishoYm/100)-1968);
		}else{
			$hidTaishoKi = (floor($sTaishoYm/100)-1969);
		}
		//処理用パラメータセット
		$aPara = Array();
		$aPara[0] = "SZK01";
		$aPara[1] = trim($cmbTargetSection_KBN);
		$aPara[2] = $sTaishoYm;
		$aPara[3] = "WEBAPPSV";
		$aPara[4] = $hidTaishoKi;
		$aPara[5] = $cmbDocu_KBN;
		$aPara[6] = "";
		switch ($aPara[1]){
			case "F":
				$aPara[6] = "コネクタ";
				break;
			case "K":
				$aPara[6] = "めっき";
				break;
			case "M":
				$aPara[6] = "モールド";
				break;
		}
		$aPara[7] = "";
		switch ($aPara[1]){
			case "F":
				$aPara[7] = 7;
				break;
			case "K":
				$aPara[7] = 0;
				break;
			case "M":
				$aPara[7] = 4;
				break;
		}
	}else{
		//引数の初期設定
		$cmbDocu_KBN = "0";												//対象資料（初期値：品質評価集計表）
		$cmbTargetSection_KBN = "F";									//対象部門（初期値：コネクタ）
		$sTaishoYm = date("Ym",mktime(0,0,0,date("m")-1,1,date("Y")));	//対象月（固定値：前月）
		//期の計算
		if($sTaishoYm-(floor($sTaishoYm/100)*100) >= 7){
			$hidTaishoKi = (floor($sTaishoYm/100)-1968);
		}else{
			$hidTaishoKi = (floor($sTaishoYm/100)-1969);
		}
	}
	
	//集計処理
	if($setsum<>"-1"){
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
		}else if($_GET['setsum'] == "2"){
		//協力工場集計表
			//プログラム追加予定
		}
		//正常処理
		$strMsg = "N001 集計しました";
	}
	
	//出力処理
	if($output<>-1){
		//品質評価集計表出力
		if($_GET['output'] == "1"){
			if($aPara[1] == "F"){
				//品質評価集計表集計データチェック
				$iRtnChk = $module_sel->fChkTrblDataWk($aPara);
				if($iRtnChk  == -1){
					$strMsg = $module_sel->fMsgSearch("E001","品質評価集計表集計データチェック処理エラー");
					goto proc_exit;
				}elseif($iRtnChk  == -2){
					$strMsg = "E002 対象年月の集計データがありません";
					goto proc_exit;
				}
				//品質評価集計表出力データ取得
				$aResHyoka = $module_sel->fGetTrblHyoka($aPara);
				if($aResHyoka[0][0] == -1){
					$strMsg = $module_sel->fMsgSearch("E001","品質評価集計表データ出力エラー");
					goto proc_exit;
				}
				//不良内訳（社内起因）
				$aResBat = $module_sel->fGetTrblBatMonth($aPara);
				if($aResBat[0][0] == -1){
					$strMsg = $module_sel->fMsgSearch("E001","不良内訳（社内起因）データ出力エラー");
					goto proc_exit;
				}
				
				//不良内訳（協力会社起因）
				$aResBatG = $module_sel->fGetTrblBatMonthG($aPara);
				if($aResBatG[0][0] == -1){
					$strMsg = $module_sel->fMsgSearch("E001","不良内訳（協力会社起因）データ出力エラー");
					goto proc_exit;
				}
				//計算シート
				$aResSum = $module_sel->fGetTrblSumSheet($aPara);
				if($aResSum[0][0] == -1){
					$strMsg = $module_sel->fMsgSearch("E001","計算シートデータ出力スエラー");
					goto proc_exit;
				}
				//材料歩留表
				$aResHoryu = $module_sel->fGetTrblHoryu($aPara);
				if($aResHoryu[0][0] == -1){
					$strMsg = $module_sel->fMsgSearch("E001","材料歩留まり票データ出力エラー");
					goto proc_exit;
				}
			}
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
			
			$strImportFile = mb_convert_encoding("品質評価集計".$aPara[6]."_雛型.xlsx","SJIS","UTF-8");
			if($aPara[1] == "F"){
				$strExportFile = mb_convert_encoding($aPara[4]."期品質評価集計表".$aPara[6]."_".substr($aPara[2],4,2)."月度.xlsx","SJIS","UTF-8");
			}else{
				$strExportFile = mb_convert_encoding($aPara[4]."期品質評価集計表.xlsx","SJIS","UTF-8");
			}
			
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
			$iSheetQty = $aPara[7];
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
				
				switch ($aPara[1]){
					case "F":	//コネクタ
						switch ($iSheet){
							case 0:		//品質評価集計表
								//タイトル
								$sheet->setCellValue("A2",$aPara[4]."期 部品製造部（".$aPara[6]."） 品質評価集計表");
								//期
								$sheet->setCellValue("T1",$aPara[4]);
								
								if(count($aResHyoka) > 1){
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
								}
								break;
							case 1:		//部門不良品管理表
								//タイトル
								$sheet->setCellValue("B1",$aPara[6]."部門不良品管理表（廃棄分）".intval(substr($aPara[2],0,4)).".".intval(substr($aPara[2],4,2))."月度");
								
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
									$borders = $sheet->getStyle('A'.($intRowRnk + count($aResRnk)).':T'.($intRowRnk + count($aResBatG)))->getBorders();
									$borders ->getTop()->setBorderStyle('double');
									//総数
									$sheet->setCellValueByColumnAndRow(14,$intRowRnk + count($aResRnk),"=SUM(N".$intRowRnk.":N".($intRowRnk + count($aResRnk)-1).")");
									$sheet->setCellValueByColumnAndRow(19,$intRowRnk + count($aResRnk),"=SUM(S".$intRowRnk.":S".($intRowRnk + count($aResRnk)-1).")");
									$sheet->setCellValueByColumnAndRow(20,$intRowRnk + count($aResRnk),"=SUM(T".$intRowRnk.":T".($intRowRnk + count($aResRnk)-1).")");
								}
								break;
							case 2:		//不良内訳（社内起因）
								//月度
								$sheet->setCellValue("F1",intval(substr($aPara[2],4,2))."月度");
								
								if(count($aResBat) > 0 && $aResBat[0][1]<>""){
									$sheet->insertNewRowBefore($intRowBat+2,count($aResBat)-1);
									$sheet->removeRow($intRowBat,1); 
									$sFlawTmp ="";
									$sProdTmp ="";
									for($iRows=0;$iRows<count($aResBat); ++$iRows){
										$sheet->setCellValueByColumnAndRow(1,$intRowBat + $iRows,$aResBat[$iRows][1]);
										$sheet->setCellValueByColumnAndRow(2,$intRowBat + $iRows,$aResBat[$iRows][2]);
										$sheet->setCellValueByColumnAndRow(3,$intRowBat + $iRows,$aResBat[$iRows][3]);
										$sheet->setCellValueByColumnAndRow(4,$intRowBat + $iRows,$aResBat[$iRows][4]);
										$sheet->setCellValueByColumnAndRow(5,$intRowBat + $iRows,$aResBat[$iRows][5]);
										$sheet->setCellValueByColumnAndRow(6,$intRowBat + $iRows,$aResBat[$iRows][6]);
										$sheet->setCellValueByColumnAndRow(7,$intRowBat + $iRows,$aResBat[$iRows][7]);
										$sheet->setCellValueByColumnAndRow(8,$intRowBat + $iRows,$aResBat[$iRows][8]);
										$sheet->setCellValueByColumnAndRow(9,$intRowBat + $iRows,$aResBat[$iRows][9]);
										$sheet->setCellValueByColumnAndRow(10,$intRowBat + $iRows,$aResBat[$iRows][10]);
										$sheet->setCellValueByColumnAndRow(11,$intRowBat + $iRows,$aResBat[$iRows][11]);
										$sheet->setCellValueByColumnAndRow(12,$intRowBat + $iRows,$aResBat[$iRows][12]);
										$sheet->setCellValueByColumnAndRow(13,$intRowBat + $iRows,$aResBat[$iRows][13]);
										$sheet->setCellValueByColumnAndRow(14,$intRowBat + $iRows,$aResBat[$iRows][14]);
										$sheet->setCellValueByColumnAndRow(15,$intRowBat + $iRows,$aResBat[$iRows][15]);
										$sheet->setCellValueByColumnAndRow(16,$intRowBat + $iRows,$aResBat[$iRows][16]);
										
										//同代表NOの場合点線
										if($sNoTmp == $aResBat[$iRows][19] && $iRows > 0){
											$borders = $sheet->getStyle('A'.($intRowBat + $iRows-1).':P'.($intRowBat + $iRows-1))->getBorders();
											$borders ->getBottom()->setBorderStyle('dashed');
										}
										$sNoTmp = $aResBat[$iRows][19];
										//集計対象外は色塗り表示
										if($aResBat[$iRows][17]==1){
											$sheet->getStyle('A'.($intRowBat + $iRows).':P'.($intRowBat + $iRows))->getFill()
												  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
												  ->getStartColor()->setARGB('00CCCCFF');
										}
										
									}
									$borders = $sheet->getStyle('A'.($intRowBat + count($aResBat)-1).':P'.($intRowBat + count($aResBat)-1))->getBorders();
									$borders ->getBottom()->setBorderStyle('double');
									//合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat),"=SUM(I".$intRowBat.":I".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(10,$intRowBat + count($aResBat),"=SUM(J".$intRowBat.":J".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat),"=SUM(K".$intRowBat.":K".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat),"=SUM(M".$intRowBat.":M".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat),"=SUM(N".$intRowBat.":N".($intRowBat + count($aResBat)-1).")");
									//社内検査合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat)+1,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=検査\",\$I\$".$intRowBat.":\$I\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat)+1,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=検査\",\$K\$".$intRowBat.":\$K\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat)+1,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=検査\",\$M\$".$intRowBat.":\$M\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat)+1,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=検査\",\$N\$".$intRowBat.":\$N\$".($intRowBat + count($aResBat)-1).")");
									//製造工程合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat)+2,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=工程内\",\$I\$".$intRowBat.":\$I\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat)+2,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=工程内\",\$K\$".$intRowBat.":\$K\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat)+2,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=工程内\",\$M\$".$intRowBat.":\$M\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat)+2,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=工程内\",\$N\$".$intRowBat.":\$N\$".($intRowBat + count($aResBat)-1).")");
									//客先返却合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat)+3,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=客先\",\$I\$".$intRowBat.":\$I\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat)+3,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=客先\",\$K\$".$intRowBat.":\$K\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat)+3,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=客先\",\$M\$".$intRowBat.":\$M\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat)+3,"=SUMIF(\$G\$".$intRowBat.":\$G\$".($intRowBat + count($aResBat)-1).",\"=客先\",\$N\$".$intRowBat.":\$N\$".($intRowBat + count($aResBat)-1).")");
									//生産１課合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat)+4,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=1\",\$I\$".$intRowBat.":\$I\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat)+4,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=1\",\$K\$".$intRowBat.":\$K\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat)+4,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=1\",\$M\$".$intRowBat.":\$M\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat)+4,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=1\",\$N\$".$intRowBat.":\$N\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(16,$intRowBat + count($aResBat)+4,"1");
									//生産２課合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat)+5,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=2\",\$I\$".$intRowBat.":\$I\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat)+5,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=2\",\$K\$".$intRowBat.":\$K\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat)+5,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=2\",\$M\$".$intRowBat.":\$M\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat)+5,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=2\",\$N\$".$intRowBat.":\$N\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(16,$intRowBat + count($aResBat)+5,"2");
									//生産３課合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat)+6,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=3\",\$I\$".$intRowBat.":\$I\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat)+6,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=3\",\$K\$".$intRowBat.":\$K\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat)+6,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=3\",\$M\$".$intRowBat.":\$M\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat)+6,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=3\",\$N\$".$intRowBat.":\$N\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(16,$intRowBat + count($aResBat)+6,"3");
									//生産４課合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat)+7,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=4\",\$I\$".$intRowBat.":\$I\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat)+7,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=4\",\$K\$".$intRowBat.":\$K\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat)+7,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=4\",\$M\$".$intRowBat.":\$M\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat)+7,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=4\",\$N\$".$intRowBat.":\$N\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(16,$intRowBat + count($aResBat)+7,"4");
									//生産５課合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat)+8,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=5\",\$I\$".$intRowBat.":\$I\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat)+8,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=5\",\$K\$".$intRowBat.":\$K\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat)+8,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=5\",\$M\$".$intRowBat.":\$M\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat)+8,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=5\",\$N\$".$intRowBat.":\$N\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(16,$intRowBat + count($aResBat)+8,"5");
									//その他合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBat + count($aResBat)+9,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=-\",\$I\$".$intRowBat.":\$I\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBat + count($aResBat)+9,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=-\",\$K\$".$intRowBat.":\$K\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBat + count($aResBat)+9,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=-\",\$M\$".$intRowBat.":\$M\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBat + count($aResBat)+9,"=SUMIF(\$P\$".$intRowBat.":\$P\$".($intRowBat + count($aResBat)-1).",\"=-\",\$N\$".$intRowBat.":\$N\$".($intRowBat + count($aResBat)-1).")");
									$sheet->setCellValueByColumnAndRow(16,$intRowBat + count($aResBat)+9,"-");
								}
								break;
							case 3:		//不良内訳（協力会社起因）
								//月度
								$sheet->setCellValue("E1",intval(substr($aPara[2],4,2))."月度");
								
								if(count($aResBatG) > 0 && $aResBatG[0][1]<>""){
									$sheet->insertNewRowBefore($intRowBatG+2,count($aResBatG)-1);
									$sheet->removeRow($intRowBatG,1); 
									$sProdTmp ="";
									$sFlawTmp ="";
									for($iRows=0;$iRows<count($aResBatG); ++$iRows){
										$sheet->setCellValueByColumnAndRow(1,$intRowBatG + $iRows,$aResBatG[$iRows][1]);
										$sheet->setCellValueByColumnAndRow(2,$intRowBatG + $iRows,$aResBatG[$iRows][2]);
										$sheet->setCellValueByColumnAndRow(3,$intRowBatG + $iRows,$aResBatG[$iRows][3]);
										$sheet->setCellValueByColumnAndRow(4,$intRowBatG + $iRows,$aResBatG[$iRows][4]);
										$sheet->setCellValueByColumnAndRow(5,$intRowBatG + $iRows,$aResBatG[$iRows][5]);
										$sheet->setCellValueByColumnAndRow(6,$intRowBatG + $iRows,$aResBatG[$iRows][6]);
										$sheet->setCellValueByColumnAndRow(7,$intRowBatG + $iRows,$aResBatG[$iRows][7]);
										$sheet->setCellValueByColumnAndRow(8,$intRowBatG + $iRows,$aResBatG[$iRows][8]);
										$sheet->setCellValueByColumnAndRow(9,$intRowBatG + $iRows,$aResBatG[$iRows][9]);
										$sheet->setCellValueByColumnAndRow(10,$intRowBatG + $iRows,$aResBatG[$iRows][10]);
										$sheet->setCellValueByColumnAndRow(11,$intRowBatG + $iRows,$aResBatG[$iRows][11]);
										$sheet->setCellValueByColumnAndRow(12,$intRowBatG + $iRows,$aResBatG[$iRows][12]);
										$sheet->setCellValueByColumnAndRow(13,$intRowBatG + $iRows,$aResBatG[$iRows][13]);
										$sheet->setCellValueByColumnAndRow(14,$intRowBatG + $iRows,$aResBatG[$iRows][14]);
										$sheet->setCellValueByColumnAndRow(15,$intRowBatG + $iRows,$aResBatG[$iRows][15]);
										
										//同代表NOの場合点線
										if($sNoTmp == $aResBatG[$iRows][18] && $iRows > 0){
											$borders = $sheet->getStyle('A'.($intRowBatG + $iRows-1).':O'.($intRowBatG + $iRows-1))->getBorders();
											$borders ->getBottom()->setBorderStyle('dashed');
										}
										$sNoTmp = $aResBatG[$iRows][18];
										//集計対象外は色塗り表示
										if($aResBatG[$iRows][16]==1){
											$sheet->getStyle('A'.($intRowBatG + $iRows).':O'.($intRowBatG + $iRows))->getFill()
												  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
												  ->getStartColor()->setARGB('00CCCCFF');
										}
									}
									$borders = $sheet->getStyle('A'.($intRowBatG + count($aResBatG)-1).':O'.($intRowBatG + count($aResBatG)-1))->getBorders();
									$borders ->getBottom()->setBorderStyle('double');
									//合計
									$sheet->setCellValueByColumnAndRow(9,$intRowBatG + count($aResBatG),"=SUM(I".$intRowBatG.":I".($intRowBatG + count($aResBatG)-1).")");
									$sheet->setCellValueByColumnAndRow(10,$intRowBatG + count($aResBatG),"=SUM(J".$intRowBatG.":J".($intRowBatG + count($aResBatG)-1).")");
									$sheet->setCellValueByColumnAndRow(11,$intRowBatG + count($aResBatG),"=SUM(K".$intRowBatG.":K".($intRowBatG + count($aResBatG)-1).")");
									$sheet->setCellValueByColumnAndRow(13,$intRowBatG + count($aResBatG),"=SUM(M".$intRowBatG.":M".($intRowBatG + count($aResBatG)-1).")");
									$sheet->setCellValueByColumnAndRow(14,$intRowBatG + count($aResBatG),"=SUM(N".$intRowBatG.":N".($intRowBatG + count($aResBatG)-1).")");
								}
								
								break;
							case 4:		//計算シート
								if(count($aResSum) > 0 && $aResSum[0][1]<>""){
									for($iCols=0;$iCols<count($aResSum); ++$iCols){
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,4,$aResSum[$iCols][2]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,5,$aResSum[$iCols][3]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,7,$aResSum[$iCols][4]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,8,$aResSum[$iCols][5]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,13,$aResSum[$iCols][6]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,14,$aResSum[$iCols][7]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,16,$aResSum[$iCols][8]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,17,$aResSum[$iCols][9]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,22,$aResSum[$iCols][10]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,23,$aResSum[$iCols][11]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,25,$aResSum[$iCols][12]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,26,$aResSum[$iCols][13]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,31,$aResSum[$iCols][14]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,32,$aResSum[$iCols][15]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,34,$aResSum[$iCols][16]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,35,$aResSum[$iCols][17]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,40,$aResSum[$iCols][18]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,41,$aResSum[$iCols][19]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,43,$aResSum[$iCols][20]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,44,$aResSum[$iCols][21]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,49,$aResSum[$iCols][22]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,50,$aResSum[$iCols][23]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,55,$aResSum[$iCols][24]);
										$sheet->setCellValueByColumnAndRow($intColSum + $iCols,56,$aResSum[$iCols][25]);
									}
								}
								break;
							case 5:		//客先不良件数
								//期
								$sheet->setCellValue("R1",$aPara[4]);
								
								if(count($aResQty) > 0 && $aResQty[0][1]<>""){
									$iColStTmp = $intColCust;
									for($iCols=0;$iCols<count($aResQty); ++$iCols){
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,4,$aResQty[$iCols][2]);
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,5,$aResQty[$iCols][3]);
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,7,$aResQty[$iCols][4]);
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,8,$aResQty[$iCols][5]);
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,9,$aResQty[$iCols][6]);
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
										if($aResDesc[$iRows][4] == 1){
											$iLine = 5;
										}else{
											$iLine = 4;
										}
										$commentRichText = $sheet->getCommentByColumnAndRow($iCols,$iLine)->getText()->createTextRun($aResDesc[$iRows][2]."\r\n");
										$commentRichText->getFont()->setSize(9);
										if($aResDesc[$iRows][3] == 2){
											$commentRichText->getFont()->getColor()->setARGB('00FF66FF');
										}
										$commentRichText = $sheet->getCommentByColumnAndRow($iCols,$iLine)->setWidth('400px');
										$commentRichText = $sheet->getCommentByColumnAndRow($iCols,$iLine)->setHeight('110px');
									}
								}
								
								break;
							case 6:		//材料保留表
								//タイトル
								$sheet->setCellValue("A1",intval(substr($aPara[2],4,2))."月度 コネクタ材料保留表");
								
								if(count($aResHoryu) > 0 && $aResHoryu[0][1]<>""){
									$iRowStTmp = $intRowHoryu;
									$sheet->insertNewRowBefore($iRowStTmp+1,count($aResHoryu)-1);
									for($iRows=0;$iRows<count($aResHoryu); ++$iRows){
										$sheet->setCellValueByColumnAndRow(1,$iRowStTmp + $iRows,$aResHoryu[$iRows][1]);
										$sheet->setCellValueByColumnAndRow(2,$iRowStTmp + $iRows,$aResHoryu[$iRows][2]);
										$sheet->setCellValueByColumnAndRow(3,$iRowStTmp + $iRows,$aResHoryu[$iRows][3]);
										$sheet->setCellValueByColumnAndRow(4,$iRowStTmp + $iRows,$aResHoryu[$iRows][4]);
										$sheet->setCellValueByColumnAndRow(5,$iRowStTmp + $iRows,$aResHoryu[$iRows][5]);
										$sheet->setCellValueByColumnAndRow(6,$iRowStTmp + $iRows,$aResHoryu[$iRows][6]);
										$sheet->setCellValueByColumnAndRow(7,$iRowStTmp + $iRows,$aResHoryu[$iRows][7]);
										$sheet->setCellValueByColumnAndRow(8,$iRowStTmp + $iRows,$aResHoryu[$iRows][8]);
										$sheet->setCellValueByColumnAndRow(9,$iRowStTmp + $iRows,$aResHoryu[$iRows][9]);
										$sheet->setCellValueByColumnAndRow(10,$iRowStTmp + $iRows,$aResHoryu[$iRows][10]);
										$sheet->setCellValueByColumnAndRow(11,$iRowStTmp + $iRows,$aResHoryu[$iRows][11]);
										$sheet->setCellValueByColumnAndRow(12,$iRowStTmp + $iRows,$aResHoryu[$iRows][12]);
										$sheet->setCellValueByColumnAndRow(13,$iRowStTmp + $iRows,$aResHoryu[$iRows][13]);
										$sheet->setCellValueByColumnAndRow(14,$iRowStTmp + $iRows,$aResHoryu[$iRows][14]);
										$sheet->setCellValueByColumnAndRow(15,$iRowStTmp + $iRows,$aResHoryu[$iRows][15]);
										$sheet->setCellValueByColumnAndRow(16,$iRowStTmp + $iRows,$aResHoryu[$iRows][16]);
										$sheet->setCellValueByColumnAndRow(17,$iRowStTmp + $iRows,$aResHoryu[$iRows][17]);
										$sheet->setCellValueByColumnAndRow(18,$iRowStTmp + $iRows,$aResHoryu[$iRows][18]);
									}
									//総数
									$sheet->setCellValueByColumnAndRow(9,$iRowStTmp + count($aResHoryu),"=SUM(I".$iRowStTmp.":I".($iRowStTmp + count($aResHoryu)-1).")");
									$sheet->setCellValueByColumnAndRow(10,$iRowStTmp + count($aResHoryu),"=SUM(J".$iRowStTmp.":J".($iRowStTmp + count($aResHoryu)-1).")");
									$sheet->setCellValueByColumnAndRow(15,$iRowStTmp + count($aResHoryu),"=SUM(O".$iRowStTmp.":O".($iRowStTmp + count($aResHoryu)-1).")");
									$sheet->setCellValueByColumnAndRow(16,$iRowStTmp + count($aResHoryu),"=SUM(P".$iRowStTmp.":P".($iRowStTmp + count($aResHoryu)-1).")");
									$sheet->setCellValueByColumnAndRow(17,$iRowStTmp + count($aResHoryu),"=SUM(Q".$iRowStTmp.":Q".($iRowStTmp + count($aResHoryu)-1).")");
								}
								break;
						}
						break;
					case "K":	//めっき
						break;
					case "M":	//モールド
						switch ($iSheet){
							case 0:		//品質評価集計表
								//タイトル
								$sheet->setCellValue("A2",$aPara[4]."期 部品製造部（".$aPara[6]."） 品質評価集計表");
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
								$sheet->setCellValue("B1",$aPara[6]."部門不良品管理表（廃棄分）".intval(substr($aPara[2],0,4)).".".intval(substr($aPara[2],4,2))."月度");
								
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
									$borders = $sheet->getStyle('A'.($intRowRnk + count($aResRnk)).':T'.($intRowRnk + count($aResBatG)))->getBorders();
									$borders ->getTop()->setBorderStyle('double');
									//総数
									$sheet->setCellValueByColumnAndRow(14,$intRowRnk + count($aResRnk),"=SUM(N".$intRowRnk.":N".($intRowRnk + count($aResRnk)-1).")");
									$sheet->setCellValueByColumnAndRow(19,$intRowRnk + count($aResRnk),"=SUM(S".$intRowRnk.":S".($intRowRnk + count($aResRnk)-1).")");
									$sheet->setCellValueByColumnAndRow(20,$intRowRnk + count($aResRnk),"=SUM(T".$intRowRnk.":T".($intRowRnk + count($aResRnk)-1).")");
								}
								break;
							case 2:		//総加工個数
								break;
							case 3:		//客先不良件数
								//期
								$sheet->setCellValue("R1",$aPara[4]);
								
								if(count($aResQty) > 0 && $aResQty[0][1]<>""){
									$iColStTmp = $intColCust;
									for($iCols=0;$iCols<count($aResQty); ++$iCols){
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,4,$aResQty[$iCols][2]);
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,5,$aResQty[$iCols][3]);
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,7,$aResQty[$iCols][4]);
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,8,$aResQty[$iCols][5]);
										$sheet->setCellValueByColumnAndRow($iColStTmp + $iCols,9,$aResQty[$iCols][6]);
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
										if($aResDesc[$iRows][4] == 1){
											$iLine = 5;
										}else{
											$iLine = 4;
										}
										$commentRichText = $sheet->getCommentByColumnAndRow($iCols,$iLine)->getText()->createTextRun($aResDesc[$iRows][2]."\r\n");
										$commentRichText->getFont()->setSize(9);
/* 										if($aResDesc[$iRows][3] == 2){
											$commentRichText->getFont()->getColor()->setARGB('00FF66FF');
										} */
										$commentRichText = $sheet->getCommentByColumnAndRow($iCols,$iLine)->setWidth('400px');
										$commentRichText = $sheet->getCommentByColumnAndRow($iCols,$iLine)->setHeight('110px');
									}
								}
								
								break;
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
			//$writer->setOffice2003Compatibility(true);
			$writer->setIncludeCharts(true);
			$writer->save('php://output');
			exit;
		}elseif($_GET['output'] == "2"){
			//協力工場品質評価集計表 出力
			
		}elseif($_GET['output'] == "3"){
			//テスト
			$strImportFile = mb_convert_encoding("品.docx","SJIS","UTF-8");
			$strExportFile = mb_convert_encoding("品質状況報告.docx","SJIS","UTF-8");
			
			//ブラウザへ出力をリダイレクト
			header("Content-Description: File Transfer");
			header('Content-Disposition: attachment; filename='.$strExportFile);
			header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			ob_end_clean(); //バッファ消去
			ob_start();
			
			$phpWord = new \PhpOffice\PhpWord\PhpWord();
			$phpWord = $phpWord->loadTemplate(strImportFile);
			$section = $phpWord->addSection();
			//文字
			$section->addText('テキスト1');
			$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
			ob_clean();
			$objWriter->save('php://output');
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
	$aRireki = $module_sel->fGetRenkeiRireki("F_FLK0091",$_SESSION['login']);
	
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
	//	echo '<meta http-equiv="refresh" content="1;URL=F_FLK0091.php?setsum=2">';
	//}
?>
<TITLE>【月末月初資料】</TITLE>

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
				document.form.action ="F_FLK0091.php?output=1";
				//document.form.action ="F_FLK0091.php?output=3";
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
				document.form.action ="F_FLK0091.php?setsum=1";
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
			<TD align="center" width="1000"><SPAN STYLE="FONT-FAMILY: Times New Roman;FONT-SIZE: 18PT;COLOR: FFFFFF;HEIGHT: 18PT;WIDTH: 400PT;FILTER:Alpha(,Style=uniform) DropShadow(Color=#660099,OffX=1,OffY=1) Shadow(Color=9933ff) ;">【月末月初資料出力】
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

<TABLE class="tbline" width="300">
	<TBODY>
		<TR>
			<TD class="tdnone1" width="20">対象資料</TD>
			<TD class="tdnone3" width="100" colspan="3">
				<SELECT name="cmbDocu_KBN" id="cmbDocu_KBN" tabindex="10" <?php echo $strCmbLock; ?>>
					<OPTION selected value="-1">▼選択して下さい</OPTION>
					<?php $module_sel->fMakeCombo('C40',$cmbDocu_KBN); ?>
				</SELECT>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone1">対象部門</TD>
			<TD class="tdnone3" colspan="3">
				<SELECT name="cmbTargetSection_KBN" id="cmbTargetSection_KBN" tabindex="20" <?php echo $strCmbLock; ?>>
					<OPTION selected value="-1">▼選択して下さい</OPTION>
					<?php $module_sel->fMakeCombo('C04',$cmbTargetSection_KBN); ?>
				</SELECT>
			</TD>
		</TR>
		<TR>
			<TD class="tdnone1">対象年月</TD>
			<TD class="tdnone3" colspan="3">
			<SELECT name="sTaishoYm" tabindex="30">
				<option <?php if($_POST['sTaishoYm']==date("Ym") || $_POST['sTaishoYm'] == ""){echo "selected";}?> value=<?php echo date("Ym") ?>><?php echo date("Y年m月") ?></option>
				<option <?php if($_POST['sTaishoYm']<>date("Ym")){echo "selected";}?> value=<?php echo date("Ym",mktime(0,0,0,date("m")-1,1,date("Y"))) ?>><?php echo date("Y年m月",mktime(0,0,0,date("m")-1,1,date("Y"))) ?></option>
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
		<INPUT type="button" name="btnSum" value="　集　計　" onClick="fSum()" tabindex="110">
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
	<TABLE class="tbline" width="780">
		<TBODY>
			<TR class="tdnone3">
			  <TD class="tdnone2" width="130" align="center" nowrap>集計日</TD>
			  <TD class="tdnone2" width="130" align="center" nowrap>対象資料</TD>
			  <TD class="tdnone2" width="100" align="center" nowrap>対象部門</TD>
			  <TD class="tdnone2" width="85" align="center" nowrap>対象月</TD>
			  <TD class="tdnone2" width="110" align="center" nowrap>処理者</TD>
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
				echo "<TD class='".$strClass."'>".$module_cmn->fChangDateTimeFormat($aRireki[$i][0])."</TD>";
				echo "<TD class='".$strClass."'>".$aRireki[$i][1]."</TD>";
				echo "<TD class='".$strClass."'>".$aRireki[$i][2]."</TD>";
				echo "<TD class='".$strClass."'>".substr($aRireki[$i][3],0,4)."年".substr($aRireki[$i][3],4,2)."月"."</TD>";
				echo "<TD class='".$strClass."'>".$aRireki[$i][4]."</TD>";
				echo "</TR>";
				$i = $i + 1;
			}
?>
		</TBODY>
	</TABLE>

<?php
}
?>

<?php
//if($_GET['setsum'] == 1 || $_GET['output'] == 1){
?>
<!--
<div id="wait_msg">
	<P>　Loading...　<img src="gif/load.gif" width="50px"></P>
</div>
-->
<?php
//}
?>
<INPUT type="hidden" name="hidTantoCd" value="<?php echo $_SESSION['login'][0];?>">
<INPUT type="hidden" name="hidFrame" value="<?php echo $hidFrame; ?>">
<INPUT type="hidden" name="hidTaishoKi" value="<?php echo $hidTaishoKi; ?>">
</FORM>
<script type="text/javascript" >
	/* 初期フォーカス */
	document.getElementById('cmbDocu_KBN').focus();
</script>

</BODY>
</HTML>