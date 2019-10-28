<?php
//****************************************************************************
//プログラム名：更新用モジュール郡
//プログラムID：module_upd
//作成者　　　：㈱鈴木　久米
//作成日　　　：2012/05/31
//履歴　　　　：
//
//
//****************************************************************************
class module_upd{


	//データベース接続情報
	public $gUserID;
	public $gPass;
	public $gDB;

	//eMes接続情報(SQLServer)
	public $gEServer;
	public $gEUserid;
	public $gEPasswd;
	public $gEDbName;

	//コンストラクタ
	function __construct(){
		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);

		//iniから取得
		if($aIni){
			$aPara = array();
			//不具合管理DB情報取得
			$aPara[0] = $aIni['FL_INI']['DB'];
			$aPara[1] = $aIni['FL_INI']['USERID'];
			$aPara[2] = $aIni['FL_INI']['PASSWORD'];
			//eMesDB情報取得
			$aPara[3] = $aIni['EMES']['SERVER'];
			$aPara[4] = $aIni['EMES']['USERID'];
			$aPara[5] = $aIni['EMES']['PASSWORD'];
			$aPara[6] = $aIni['EMES']['DBNAME'];

			//PRONES(販売)DB情報取得
			$aPara[11] = $aIni['PRONES']['DB'];
			$aPara[12] = $aIni['PRONES']['USERID'];
			$aPara[13] = $aIni['PRONES']['PASSWORD'];

			//Add Start 2011/10/07 M.Nishimura BOM EDI転送対応
			//FlexBOM　DB情報取得
			$aPara[14] = $aIni['BOM']['DB'];
			$aPara[15] = $aIni['BOM']['USERID'];
			$aPara[16] = $aIni['BOM']['PASSWORD'];
			//Add End 2011/10/07 M.Nishimura BOM EDI転送対応

			//SMART2(生産管理)DB情報取得
			$aPara[17] = $aIni['NF']['DB'];
			$aPara[18] = $aIni['NF']['USERID'];
			$aPara[19] = $aIni['NF']['PASSWORD'];

		}

		//DB接続情報
		$this->gUserID = $aPara[1];
		$this->gPass   = $aPara[2];
		$this->gDB     = $aPara[0];

		//eMesSQLServerの設定値
	    $this->gEServer = $aPara[3];
		$this->gEUserid = $aPara[4];
		$this->gEPasswd = $aPara[5];
		$this->gEDbName = $aPara[6];

		//PRONESのDB設定値
	    $this->gPDB      = $aPara[11];
		$this->gPUserid  = $aPara[12];
		$this->gDPPasswd = $aPara[13];

		//Add Start 2011/10/07 M.Nishimura BOM EDI転送対応
		//BOMのDB設定値
	    $this->gBDB      = $aPara[14];
		$this->gBUserid  = $aPara[15];
		$this->gBPPasswd = $aPara[16];
		//Add End 2011/10/07 M.Nishimura BOM EDI転送対応
		
		//SMART2のDB設定値
		$this->gNDB      = $aPara[17];
		$this->gNUserID  = $aPara[18];
		$this->gNPass    = $aPara[19];
	}


	//品目情報登録・更新・削除処理
	public function fItemExcute($mode,$item_cd,$session,$count){

		require_once("module_common.php");
		require_once("module_sel.php");

		$module_cmn = new module_common;
		$module_sel = new module_sel;

		$strReturn = "";

		try{

			//Oracleへの接続の確立(トランザクション開始)
			$conn = $this->fTransactionStart();

			//処理モードで取得SQL切り分け(1:登録,2:更新,3:削除)
			if($mode == "1"){
				//登録SQL取得
				$sql = $this->fItemInsertSql($item_cd,$session);

			}else{
				//更新・削除SQL取得
				$sql = $this->fItemUpdateSql($item_cd,$session,$mode,$count);
			}

			//SQLの分析
			$stmt = oci_parse($conn, $sql);

			//処理モードでバインド変数切り分け(1:登録,2:更新,3:削除)
			//登録時
			if($mode == "1"){
				//品目コード採番処理
				$item_cd = $module_sel->fItemCdSearch($conn,$session);

				oci_bind_by_name($stmt, ":txtItemCd", $item_cd, -1);
			}
			//削除以外の場合
			if($mode <> "3"){
				//登録・更新時
				$sTmpJoken = $module_cmn->fChangSJIS_SQL($_POST['txtItemNm']);
	        	oci_bind_by_name($stmt, ":txtItemNm", $sTmpJoken, -1);
				
				$sTmpJoken = $module_cmn->fChangSJIS_SQL($_POST['txtItemNmK']);
	        	oci_bind_by_name($stmt, ":txtItemNmK", $sTmpJoken, -1);
				
				$sTmpJoken = $module_cmn->fChangSJIS_SQL($_POST['txtShiyo']);
				oci_bind_by_name($stmt, ":txtShiyo", $sTmpJoken, -1);
				
				//oci_bind_by_name($stmt, ":txtKikaku", $module_cmn->fChangSJIS_SQL($_POST['txtKikaku']), -1);
				$sTmpJoken = $module_cmn->fChangSJIS_SQL('');
				oci_bind_by_name($stmt, ":txtKikaku", $sTmpJoken, -1);
				oci_bind_by_name($stmt, ":txtMaker", $module_cmn->fChangSJIS_SQL($_POST['txtMaker']), -1);
				oci_bind_by_name($stmt, ":txtDaihyoToriCd", $_POST['txtShiireCd'], -1);
				oci_bind_by_name($stmt, ":cmbStdTani", $_POST['cmbStdTani'], -1);
				oci_bind_by_name($stmt, ":txtStdOrder", $_POST['txtStdOrder'], -1);
				oci_bind_by_name($stmt, ":txtHatMaruTani", $_POST['txtHatMaruTani'], -1);
				oci_bind_by_name($stmt, ":txtTanka", $_POST['txtTanka'], -1);
				oci_bind_by_name($stmt, ":cmbOrderKbn", $_POST['cmbOrderKbn'], -1);
				oci_bind_by_name($stmt, ":cmbSikyuKbn", $_POST['cmbSikyuKbn'], -1);
				oci_bind_by_name($stmt, ":cmbItemBunrui", $_POST['cmbItemBunrui'], -1);
				oci_bind_by_name($stmt, ":cmbDaihyoKeihi", $_POST['cmbDaihyoKeihi'], -1);
				oci_bind_by_name($stmt, ":cmbKanriKaisha", $_POST['cmbKanriKaisha'], -1);
				oci_bind_by_name($stmt, ":cmbKanriBusho", $_POST['cmbKanriBusho'], -1);
				oci_bind_by_name($stmt, ":cmbEcoGoodsKbn", $_POST['cmbEcoGoodsKbn'], -1);
				oci_bind_by_name($stmt, ":cmbRohsKbn", $_POST['cmbRohsKbn'], -1);
				oci_bind_by_name($stmt, ":cmbReachKbn", $_POST['cmbReachKbn'], -1);
				oci_bind_by_name($stmt, ":cmbEccjKbn", $_POST['cmbEccjKbn'], -1);
				oci_bind_by_name($stmt, ":txtBiko", $module_cmn->fChangSJIS_SQL($_POST['txtBiko']), -1);

			}

			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return "err";
			}else{
				//トランザクション処理(commit)とOracle切断
				$this->fTransactionEnd($conn,true);
			}


		}catch(Exception $e){
			//トランザクション処理(rollback)とOracle切断
			$this->fTransactionEnd($conn,false);
			$strReturn = "err";
			return $strReturn;
		}

		$strReturn= $item_cd;

		return $strReturn;

	}




	//取引先情報登録・更新・削除処理
	public function fCustExcute($mode,$cust_cd,$session,$count){

		require_once("module_common.php");

		$module_cmn = new module_common;

		try{


			//Oracleへの接続の確立(トランザクション開始)
			$conn = $this->fTransactionStart();

			//更新回数で取得SQL切り分け
			if($count == ""){
				//登録SQL取得
				$sql = $this->fCustInsertSql($cust_cd,$session);
			}else{
				//更新・削除SQL取得
				$sql = $this->fCustUpdateSql($cust_cd,$session,$mode,$count);
			}

			//SQLの分析
			$stmt = oci_parse($conn, $sql);

			//処理モードでバインド変数切り分け(1:登録,2:更新,3:削除)
			//登録時
			if($count == ""){
				$sTmpJoken = $module_cmn->fChangSJIS_SQL($cust_cd);
				//oci_bind_by_name($stmt, ":txtCustCd", $module_cmn->fChangSJIS_SQL($cust_cd), -1);
				oci_bind_by_name($stmt, ":txtCustCd", $sTmpJoken, -1);
			}
			//更新時
			$sTmpJoken = $module_cmn->fChangSJIS_SQL($_POST['txtMailAddress']);
			//oci_bind_by_name($stmt, ":txtMailAddress", $module_cmn->fChangSJIS_SQL($_POST['txtMailAddress']), -1);
			oci_bind_by_name($stmt, ":txtMailAddress", $sTmpJoken, -1);

			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return false;
			}else{
				//トランザクション処理(commit)とOracle切断
				$this->fTransactionEnd($conn,true);
			}
		}catch(Exception $e){
			//トランザクション処理(rollback)とOracle切断
			$this->fTransactionEnd($conn,false);
			return false;
		}

		return true;

	}


	//取引先マスタ登録SQL作成
	function fCustInsertSql($cust_cd,$session){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;


		$sql = "INSERT INTO T_MS_CUST_MAIL VALUES(";
		$sql = $sql." :txtCustCd ";										//取引先コード
		$sql = $sql.",:txtMailAddress ";									//メールアドレス
		$sql = $sql.", ".date("YmdHis")." ";								//登録日時
		$sql = $sql.", '".$session[0]."' ";									//登録担当者コード
		$sql = $sql.", 'F_MST0021' ";										//登録PG
		$sql = $sql.", ".date("YmdHis")." ";								//更新日時
		$sql = $sql.", '".$session[0]."' ";									//更新担当
		$sql = $sql.", 'F_MST0021' ";										//更新PG
		$sql = $sql.", 0 ";													//削除フラグ
		$sql = $sql.", 0)";													//更新回数


		return $sql;
	}



	//取引先マスタ更新・削除SQL作成
	function fCustUpdateSql($cust_cd,$session,$kbn,$count){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;
		//更新回数はカウントアップ
		$count = $count + 1;

		$sql = "UPDATE T_MS_CUST_MAIL SET ";
		//削除場合は
		if($kbn == "3"){
			$sql = $sql." N_DEL_FLG = 1 ";										//削除フラグ
		}else{
			$sql = $sql." V2_MAIL_ADDRESS = :txtMailAddress ";					//メールアドレス
		}
		$sql = $sql.",N_UPD_YMD = ".date("YmdHis")." ";							//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD = '".$session[0]."' ";						//更新担当
		$sql = $sql.",V2_UPD_PG = 'F_MST0021' ";								//更新PG
		$sql = $sql.",N_UPDATE_COUNT = ".$count;								//更新回数
		$sql = $sql." WHERE C_CUST_CD = '".$cust_cd."'";					//取引先コード

		return $sql;
	}







	//担当者情報登録・更新・削除処理
	public function fTantoExcute($mode,$tanto_cd,$session,$count,$ins_ymd){

		require_once("module_common.php");

		$module_cmn = new module_common;

		try{


			//Oracleへの接続の確立(トランザクション開始)
			$conn = $this->fTransactionStart();

			//登録日の設定有無できりわけ
			if($ins_ymd == ""){
				//登録SQL取得
				$sql = $this->fTantoInsertSql($tanto_cd,$session);

			}else{
				//更新・削除SQL取得
				$sql = $this->fTantoUpdateSql($tanto_cd,$session,$mode,$count);
			}

			//SQLの分析
			$stmt = oci_parse($conn, $sql);

			//登録日有無で切り分け(無:登録,有:更新)

			if($ins_ymd == ""){
				//登録時
				oci_bind_by_name($stmt, ":txtTantoCd", str_replace(" ","",$tanto_cd), -1);
				oci_bind_by_name($stmt, ":txtTantoNm", $module_cmn->fChangSJIS_SQL($_POST['txtTantoNm']), -1);
			}
			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return false;
			}else{
				//トランザクション処理(commit)とOracle切断
				$this->fTransactionEnd($conn,true);
			}

		}catch(Exception $e){
			//トランザクション処理(rollback)とOracle切断
			$this->fTransactionEnd($conn,false);
			return false;
		}

		return true;

	}

	//担当者マスタ登録SQL作成
	function fTantoInsertSql($tanto_cd,$session){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;


		$sql = "INSERT INTO T_MS_SHAIN VALUES(";
		$sql = $sql." :txtTantoCd ";										//担当者コード
		$sql = $sql.",:txtTantoNm ";										//担当者名
		$sql = $sql.", ".date("YmdHis")." ";								//登録日時
		$sql = $sql.", '".$session[0]."' ";									//登録担当者コード
		$sql = $sql.", 'F_MST0011' ";										//登録PG
		$sql = $sql.", ".date("YmdHis")." ";								//更新日時
		$sql = $sql.", '".$session[0]."' ";									//更新担当
		$sql = $sql.", 'F_MST0011' ";										//更新PG
		$sql = $sql.", 0 ";													//削除フラグ
		$sql = $sql.", 0)";													//更新回数


		return $sql;
	}



	//担当者マスタ更新・削除SQL作成
	function fTantoUpdateSql($tanto_cd,$session,$kbn,$count){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;
		//更新回数はカウントアップ
		$count = $count + 1;

		$sql = "UPDATE T_MS_SHAIN SET ";
		//削除場合は
		if($kbn == "3"){
			$sql = $sql." N_DEL_FLG = 1 ";															//削除フラグ
		}else{
			$sql = $sql." N_DEL_FLG = 0 ";													//パスワード

		}
		$sql = $sql.",N_UPD_YMD = ".date("YmdHis")." ";												//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD = '".$session[0]."' ";											//更新担当
		$sql = $sql.",V2_UPD_PG = 'F_MST0011' ";													//更新PG
		$sql = $sql.",N_UPDATE_COUNT = ".$count;													//更新回数
		$sql = $sql." WHERE C_SHAIN_CD = '".$tanto_cd."'";											//担当者コード

		return $sql;
	}




	//メニューマスタ更新処理
	public function fMenuExcute($conn,$pg_id,$session,$n){

		require_once("module_common.php");

		$module_cmn = new module_common;

		try{

			//更新SQL取得
			//更新回数はカウントアップ
			$count = $count + 1;

			$sql = "UPDATE T_MS_MENU SET ";
			$sql = $sql." N_UPD_YMD = ".date("YmdHis");										//更新日時
			$sql = $sql.",C_UPD_SHAIN_CD = '".$session[0]."' ";								//更新担当
			$sql = $sql.",V2_UPD_PG = 'K_MST0060' ";										//更新PG
			if($_POST['chkMenu0'.$n] == "1"){
				$sql = $sql.",N_MENU_NO0 = 1 ";												//ﾒﾆｭｰ0
			}else{
				$sql = $sql.",N_MENU_NO0 = 0 ";												//ﾒﾆｭｰ0
			}
			if($_POST['chkMenu1'.$n] == "1"){
				$sql = $sql.",N_MENU_NO1 = 1 ";												//ﾒﾆｭｰ1
			}else{
				$sql = $sql.",N_MENU_NO1 = 0 ";												//ﾒﾆｭｰ1
			}
			if($_POST['chkMenu2'.$n] == "1"){
				$sql = $sql.",N_MENU_NO2 = 1 ";												//ﾒﾆｭｰ2
			}else{
				$sql = $sql.",N_MENU_NO2 = 0 ";												//ﾒﾆｭｰ2
			}
			if($_POST['chkMenu3'.$n] == "1"){
				$sql = $sql.",N_MENU_NO3 = 1 ";												//ﾒﾆｭｰ3
			}else{
				$sql = $sql.",N_MENU_NO3 = 0 ";												//ﾒﾆｭｰ3
			}
			if($_POST['chkMenu4'.$n] == "1"){
				$sql = $sql.",N_MENU_NO4 = 1 ";												//ﾒﾆｭｰ4
			}else{
				$sql = $sql.",N_MENU_NO4 = 0 ";												//ﾒﾆｭｰ4
			}
			if($_POST['chkMenu5'.$n] == "1"){
				$sql = $sql.",N_MENU_NO5 = 1 ";												//ﾒﾆｭｰ5
			}else{
				$sql = $sql.",N_MENU_NO5 = 0 ";												//ﾒﾆｭｰ5
			}
			if($_POST['chkMenu6'.$n] == "1"){
				$sql = $sql.",N_MENU_NO6 = 1 ";												//ﾒﾆｭｰ6
			}else{
				$sql = $sql.",N_MENU_NO6 = 0 ";												//ﾒﾆｭｰ6
			}
			if($_POST['chkMenu7'.$n] == "1"){
				$sql = $sql.",N_MENU_NO7 = 1 ";												//ﾒﾆｭｰ7
			}else{
				$sql = $sql.",N_MENU_NO7 = 0 ";												//ﾒﾆｭｰ7
			}
			if($_POST['chkMenu8'.$n] == "1"){
				$sql = $sql.",N_MENU_NO8 = 1 ";												//ﾒﾆｭｰ8
			}else{
				$sql = $sql.",N_MENU_NO8 = 0 ";												//ﾒﾆｭｰ8
			}
			if($_POST['chkMenu9'.$n] == "1"){
				$sql = $sql.",N_MENU_NO9 = 1 ";												//ﾒﾆｭｰ9
			}else{
				$sql = $sql.",N_MENU_NO9 = 0 ";												//ﾒﾆｭｰ9
			}

			//$sql = $sql.",N_UPDATE_COUNT = ".$count;										//更新回数
			$sql = $sql." WHERE C_PG_ID = '".$pg_id."'";									//プログラムID

			//SQLの分析
			$stmt = oci_parse($conn, $sql);


			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);

				$aReturn[0] = "err";
				return $aReturn;

			}


		}catch(Exception $e){

			return "err";
		}

		$aReturn[0] = $konyu_no;
		return $aReturn;

	}


	//約束手形データ削除処理
	public function fYakuteDelete($conn,$hidCount,$strTaishoYm){

		try{
			//既存データ削除
			$sql = "DELETE ";
			$sql = $sql." FROM T_TR_YAKUTE ";
			$sql = $sql." WHERE N_YM = '".$strTaishoYm."'";
			$sql = $sql." AND C_KAISHA_CD = '00' ";
			$sql = $sql." AND N_PAY_KU = 0 ";
			$sql = $sql." AND C_SHIIRE_CD IN ( ";
			$i = 0;
			while($i < $hidCount){

				if($i > 0){
					$sql = $sql.",";
				}
				$sql = $sql." '".$_POST['txtShiireCd'.$i]."'";

				$i = $i + 1;
			}
			$sql = $sql." ) ";


			//SQLの分析
			$stmt = oci_parse($conn, $sql);
			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);
			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";

				return false;
			}


		}catch(Exception $e){
			return false;
		}

		return true;

	}





	//トランザクション開始処理
	public function fTransactionStart(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		try{
			//Oracleへの接続の確立
			//OCILogon(ユーザ名,パスワード,データベース名)
			$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
			if (!$conn) {
		  		$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
		  		session_destroy();
		  		die("データベースに接続できません");
			}

		}catch(Exception $e){
			die("データベースに接続できません");
			return $conn;
		}

		return $conn;

	}

	//トランザクション終了処理
	public function fTransactionEnd($conn,$bTranFlg){

		require_once("module_common.php");

		$module_cmn = new module_common;

		try{

	  		if($bTranFlg){
	  			// トランザクションをコミット
	   			$committed = oci_commit($conn);
	  		}else{
	  			// トランザクションをロールバック
	   			$committed = oci_rollback($conn);
	  		}

			//Oracle接続切断
			oci_close($conn);

		}catch(Exception $e){

			return false;
		}

		return true;

	}

	//トランザクション開始処理(汎用)
	public function fTransactionStartH($strUserId,$strPass,$strDB){

		require_once("module_common.php");

		$module_cmn = new module_common;

		try{
			//Oracleへの接続の確立
			//OCILogon(ユーザ名,パスワード,データベース名)
			$conn = oci_connect($strUserId, $strPass, $strDB);
			if (!$conn) {
		  		$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
		  		session_destroy();
		  		die("データベースに接続できません");
			}

		}catch(Exception $e){
			die("データベースに接続できません");
			return $conn;
		}

		return $conn;

	}


	//不具合情報登録
	public function fFlawTorokuExcute($conn,$mode,$Reference_No,$session,$count){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		$aPara = array();
		$aReturn = array();

		try{
			//デフォルト・・・調査中/対策中
			$progress_stage = '0';
		
			//進捗状態のチェック
			//品証からの要望で進捗状況の更新は不具合入力画面で行うように戻す 2018/06/05 k.kume
			//発行先区分が「発行先なし」(=3)の場合は解決済み
			if($_POST['cmbIncident_KBN'] == '3'){
				$progress_stage = '3';
			
			//回答不要にチェックまたは回答日が登録されている
			}elseif($_POST['chkCustAns'] == "1" || $_POST['txtAns_YMD'] > 0){
				
				//発行区分が「社内」で完結日(社内)に日付登録された場合は解決済み
				if($_POST['cmbIncident_KBN'] == '0' && $_POST['txtComplete_YMD1'] > 0){
					$progress_stage = '3';
					
				//発行区分が「協力工場」で完結日(協力)に日付登録された場合は解決済み
				}elseif($_POST['cmbIncident_KBN'] == '1' && $_POST['txtComplete_YMD2'] > 0){
					$progress_stage = '3';
					
				//発行区分が「社内/協力工場」で完結日(社内と協力)に日付登録された場合は解決済み
				}elseif($_POST['cmbIncident_KBN'] == '2' && $_POST['txtComplete_YMD1'] > 0 && $_POST['txtComplete_YMD2'] > 0){
					$progress_stage = '3';
					
				}
				//発行区分が「社内」で返却日(社内)に日付登録された場合は有効性確認中
				elseif($_POST['cmbIncident_KBN'] == '0' && $_POST['txtReturn_YMD1'] > 0){
					$progress_stage = '1';
					
				//発行区分が「協力工場」で返却日(協力)に日付登録された場合は有効性確認中
				}elseif($_POST['cmbIncident_KBN'] == '1' && $_POST['txtReturn_YMD2'] > 0){
					$progress_stage = '1';
					
				//発行区分が「社内/協力工場」で返却日(社内と協力)に日付登録された場合は解決済み
				}elseif($_POST['cmbIncident_KBN'] == '2' && $_POST['txtReturn_YMD1'] > 0 && $_POST['txtReturn_YMD2'] > 0){
					$progress_stage = '1';
					
				}
			
			}
		
			//処理モードで取得SQL切り分け(1:登録,2:更新,3:削除)
			if($mode == 1){

				//整理ＮＯ取得
				$Reference_No = $module_sel->fReference_NoSearch($conn,$session,"F_FLK0010",$_POST['cmbTarget_Section_KBN']);
				//$Reference_No = 1;
				//登録SQL取得
				$sql = $this->fFlawInsertSql($Reference_No,$session,$progress_stage);


			}elseif($mode == 2 && $Reference_No <> ""){
				//echo $session[0]."\n";
				//更新SQL取得
				$sql = $this->fFlawUpdateSql($Reference_No,$session,$count,$progress_stage);


			}elseif($mode == 3 && $Reference_No <> ""){
				//削除SQL取得
				$sql = $this->fFlawDeleteSql($Reference_No,$session,$count);

			}


			//文字列変換
			$sql = $module_cmn->fChangSJIS_SQL($sql);

			//SQLの実行
			//echo $sql;
			$stmt = oci_parse($conn, $sql);


			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);

				$aReturn[0] = "err";
				return $aReturn;

			}


			//添付ファイルがあればアップロード
			$strMsg = "";
			$strMsg = $strMsg.$module_cmn->fFileUpload("upload/".$Reference_No."/",$Reference_No,"tmpFile0","","chktmpFile0","0");			//画像
			if($strMsg == ""){
				$strMsg = $strMsg.$module_cmn->fFileUpload("upload/".$Reference_No."/",$Reference_No,"tmpFile1","-1","chktmpFile1","1");		//不具合連絡書
			}
			if($strMsg == ""){
				$strMsg = $strMsg.$module_cmn->fFileUpload("upload/".$Reference_No."/",$Reference_No,"tmpFile2","-2","chktmpFile2","1");		//品質異常改善通知書
			}
			if($strMsg == ""){
				$strMsg = $strMsg.$module_cmn->fFileUpload("upload/".$Reference_No."/",$Reference_No,"tmpFile3","-3","chktmpFile3","1");		//不良連絡書
			}
			//不具合報告書を追加 2018/06/05 k.kume
			if($strMsg == ""){
				$strMsg = $strMsg.$module_cmn->fFileUpload("upload/".$Reference_No."/",$Reference_No,"tmpFile4","-4","chktmpFile4","1");		//不良報告書
			}

			

			//アップロードチェック
			if($strMsg <> ""){
				$aReturn[0] = "err";
				$aReturn[1] = $strMsg;
				return $aReturn;
			}

			//Audit情報を更新
			$sql = $this->fFlawInsertAuditSql($Reference_No,$session,$mode,$progress_stage);

			//文字列変換
			$sql = $module_cmn->fChangSJIS_SQL($sql);

			//SQLの実行
			//echo $sql;
			$stmt = oci_parse($conn, $sql);


			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);

				$aReturn[0] = "err";
				return $aReturn;

			}



			if($strMsg <> ""){
				$aReturn[0] = "err".$strMsg;
			}else{
				$aReturn[0] = $Reference_No;
			}


			return $aReturn;

		}catch(Exception $e){
			$aReturn[0] = "err";
			return $aReturn;
		}
	}
	//不具合入力データ登録SQL作成
	function fFlawInsertSql($Reference_No,$session,$progress_stage){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//戻り値用の配列
		$aReturn = array();
		//登録SQL作成

		//進捗状態の変更は不具合対策入力にて行うため以下コメント
		//進捗状態のチェック
//		if(str_replace("/","",$_POST['txtComplete_YMD'])<>"" && str_replace("/","",$_POST['txtComplete_YMD'])<>"0"){
//			//完結日が入っている場合は解決とする
//			$progress_stage = '3';
//		}elseif($_POST['cmbResult_KBN']=='5'){
//			//結果区分が保留の場合は保留とする
//			$progress_stage = '2';
//		}else{
//			//それ以外は受付
//			$progress_stage = '0';
//
//			//※「対策中」フラグはインサートではありえない
//		}

		
		
		
		

		$sql = "INSERT INTO T_TR_FLAW("; 		$sql2=" VALUES( ";
		$sql = $sql."C_REFERENCE_NO,";			$sql2=$sql2."'".$Reference_No."',";										//整理NO
		//$sql = $sql."C_PROGRES_STAGE,";			$sql2=$sql2."'".$_POST['hidProgres_Stage']."',";						//進捗状態
		$sql = $sql."C_PROGRES_STAGE,";			$sql2=$sql2."'".$progress_stage."',";									//進捗状態
		$sql = $sql."C_CUST_CD,";				$sql2=$sql2."'".$_POST['txtCust_CD']."',";								//顧客CD
		$sql = $sql."V2_CUST_OFFICER,";			$sql2=$sql2."'".$_POST['txtCust_Officer']."',";							//顧客担当者
		$sql = $sql."V2_CUST_MANAGE_NO,";		$sql2=$sql2."'".$_POST['txtCust_Manage_No']."',";						//顧客管理NO
		$sql = $sql."C_PROD_CD,";				$sql2=$sql2."'".$_POST['txtProd_CD']."',";								//製品CD
		$sql = $sql."V2_PROD_NM,";				$sql2=$sql2."'".$_POST['txtProd_NM']."',";								//製品名
		$sql = $sql."V2_DRW_NO,";				$sql2=$sql2."'".$_POST['txtDRW_NO']."',";								//仕様番号
//		$sql = $sql."V2_MODEL,";				$sql2=$sql2."'".$_POST['txtModel']."',";								//型式
		$sql = $sql."V2_MODEL,";				$sql2=$sql2."'',";														//型式
		$sql = $sql."C_DIE_NO,";				$sql2=$sql2."'".$_POST['txtDie_NO']."',";								//金型番号
		$sql = $sql."V2_LOT_NO,";				$sql2=$sql2."'".$_POST['txtLot_NO']."',";								//ロットNO
		$sql = $sql."C_FLAW_KBN,";				$sql2=$sql2."'".$_POST['cmbFlaw_KBN']."',";								//不具合区分
		$sql = $sql."C_RECEPT_KBN,";			$sql2=$sql2."'".$_POST['cmbRecept_KBN']."',";							//受付区分
		$sql = $sql."C_CUST_CONTACT_KBN,";		$sql2=$sql2."'".$_POST['cmbCust_Contact_KBN']."',";						//客先よりの連絡方法
		$sql = $sql."N_TARGET_QTY,";			$sql2=$sql2."0".str_replace(",","",$_POST['txtTarget_QTY']).",";		//対象数量
		$sql = $sql."C_TARGET_SECTION_KBN,";	$sql2=$sql2."'".$_POST['cmbTarget_Section_KBN']."',";					//対象部門
		$sql = $sql."C_INCIDENT_KBN,";			$sql2=$sql2."'".$_POST['cmbIncident_KBN']."',";							//発行先
		$sql = $sql."V2_PRODUCT_OFFICER_NM,";	$sql2=$sql2."'".$_POST['txtProduct_Officer_NM']."',";					//生産担当者名
		$sql = $sql."C_PRODUCT_OUT_KA_CD,";		$sql2=$sql2."'".$_POST['cmbProduct_Out_Ka_CD']."',";					//生産流出
		$sql = $sql."C_CHECK_OUT_KA_CD1,";		$sql2=$sql2."'".$_POST['cmbCheck_Out_Ka_CD1']."',";						//検査流出1
		$sql = $sql."C_CHECK_OUT_KA_CD2,";		$sql2=$sql2."'".$_POST['cmbCheck_Out_Ka_CD2']."',";						//検査流出2
		$sql = $sql."V2_FLAW_CONTENTS,";		$sql2=$sql2."'".$_POST['txtFlaw_Contents']."',";						//不具合内容
		$sql = $sql."N_RETURN_QTY,";			$sql2=$sql2."0".str_replace(",","",$_POST['txtReturn_QTY']).",";		//返却数量
		$sql = $sql."N_BAD_QTY,";				$sql2=$sql2."0".str_replace(",","",$_POST['txtBat_QTY']).",";			//不良数量
		$sql = $sql."C_RETURN_DISPOSAL,";		$sql2=$sql2."'".$_POST['cmbReturn_Disposal']."',";						//返却品処理
		$sql = $sql."C_RESULT_KBN,";			$sql2=$sql2."'".$_POST['cmbResult_KBN']."',";							//結果区分
		$sql = $sql."N_CUST_AP_ANS_YMD,";		$sql2=$sql2."0".str_replace("/","",$_POST['txtCust_Ap_Ans_YMD']).",";	//顧客指定回答日
		$sql = $sql."N_ANS_YMD,";				$sql2=$sql2."0".str_replace("/","",$_POST['txtAns_YMD']).",";			//回答日
		$sql = $sql."N_MEASURES_YMD,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtMeasures_YMD']).",";		//対策日
		$sql = $sql."N_EFFECT_ALERT,";			$sql2=$sql2."".$_POST['cmbEffectAlert'].",";							//効果確認通知有無
		$sql = $sql."N_EFFECT_CONFIRM_YMD,";	$sql2=$sql2."0".str_replace("/","",$_POST['txtEffectConfirm_YMD']).",";	//対策効果確認日
		$sql = $sql."C_ANS_TANTO_CD,";			$sql2=$sql2."'".trim($_POST['txtAns_Tanto_CD'])."',";					//回答者
		$sql = $sql."N_ISSUE_YMD1,";			$sql2=$sql2."0,";														//発行日1
		$sql = $sql."N_ISSUE_YMD2,";			$sql2=$sql2."0,";														//発行日2
		$sql = $sql."N_ISSUE_YMD3,";			$sql2=$sql2."0,";														//発行日3
		$sql = $sql."V2_INCIDENT_CD1,";			$sql2=$sql2."'".$_POST['txtIncident_CD1']."',";							//発行先名称(社内)
		$sql = $sql."N_PC_AP_ANS_YMD1,";		$sql2=$sql2."0".str_replace("/","",$_POST['txtPc_Ap_Ans_YMD1']).",";	//品証指定回答日(社内)
		$sql = $sql."N_RETURN_YMD1,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtReturn_YMD1']).",";		//返却日(社内)
		$sql = $sql."N_COMPLETE_YMD1,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtComplete_YMD1']).",";		//完結日(社内)
		$sql = $sql."C_CONFIRM_TANTO_CD1,";		$sql2=$sql2."'".trim($_POST['txtConfirm_Tanto_CD1'])."',";				//確認者(社内)
		$sql = $sql."V2_REMARKS1,";				$sql2=$sql2."'".$_POST['txtRemarks1']."', ";							//備考(社内)
		$sql = $sql."V2_INCIDENT_CD2,";			$sql2=$sql2."'".$_POST['txtIncident_CD2']."',";							//発行先名称(社内)
		$sql = $sql."N_PC_AP_ANS_YMD2,";		$sql2=$sql2."0".str_replace("/","",$_POST['txtPc_Ap_Ans_YMD2']).",";	//品証指定回答日(協工)
		$sql = $sql."N_RETURN_YMD2,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtReturn_YMD2']).",";		//返却日(協工)
		$sql = $sql."N_COMPLETE_YMD2,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtComplete_YMD2']).",";		//完結日(協工)
		$sql = $sql."C_CONFIRM_TANTO_CD2,";		$sql2=$sql2."'".trim($_POST['txtConfirm_Tanto_CD2'])."',";				//確認者(協工)
		$sql = $sql."V2_REMARKS2,";				$sql2=$sql2."'".$_POST['txtRemarks2']."' ";								//備考(協工)
		$sql = $sql."N_INS_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";									//登録日時
		$sql = $sql."C_INS_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";										//登録担当者コード
		$sql = $sql."V2_INS_PG,";				$sql2=$sql2.", 'F_FLK0010' ";											//登録PG
		$sql = $sql."N_UPD_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";									//更新日時
		$sql = $sql."C_UPD_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";										//更新担当
		$sql = $sql."V2_UPD_PG,";				$sql2=$sql2.", 'F_FLK0010' ";											//更新PG
		$sql = $sql."N_DEL_FLG,";				$sql2=$sql2.", 0 ";														//削除フラグ
		$sql = $sql."N_UPDATE_COUNT, ";			$sql2=$sql2.", 0 ";														//更新回数
		$sql = $sql."N_VAL_ALERT_YMD, ";		$sql2=$sql2.", 0";														//有効性評価通知日
		$sql = $sql."C_QUICK_FIX_KBN, ";		$sql2=$sql2.",'".$_POST['cmbQuick_Fix_CD']."',";						//異常品暫定処置 2015/06/26追加
		$sql = $sql."N_CONTACT_ACCEPT_YMD, ";	$sql2=$sql2."0".str_replace("/","",$_POST['txtContact_Accept_YMD']).",";//連絡受理日 2016/09/02追加 k.kume
		$sql = $sql."C_PC_TANTO_CD)";			$sql2=$sql2."'".trim($_POST['txtPc_Tanto_CD'])."')";					//品証担当者 2019/07/06追加 k.kume


		//echo $sql.$sql2;

		$sql = $sql.$sql2;

		return $sql;
	}

	//不具合入力Auditデータ登録SQL作成
	function fFlawInsertAuditSql($Reference_No,$session,$cls,$progress_stage){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//戻り値用の配列
		$aReturn = array();
		//登録SQL作成

		//進捗状態の変更は不具合対策入力にて行うため以下コメント
		//進捗状態のチェック
//		if(str_replace("/","",$_POST['txtComplete_YMD'])<>"" && str_replace("/","",$_POST['txtComplete_YMD'])<>"0"){
//			//完結日が入っている場合は解決とする
//			$progress_stage = '3';
//		}elseif($_POST['cmbResult_KBN']=='5'){
//			//結果区分が保留の場合は保留とする
//			$progress_stage = '2';
//		}else{
//			//それ以外は受付
//			$progress_stage = '0';
//
//			//※「対策中」フラグはインサートではありえない
//		}

		$sql = "INSERT INTO T_TR_FLAW_AUDIT("; 	$sql2 = "VALUES(";
		$sql = $sql."C_PRCS_CLS,";				$sql2=$sql2."'".$cls."',";												//処理区分
		$sql = $sql."N_PRCS_YMD,";				$sql2=$sql2."".date("YmdHis").", ";										//処理日時
		$sql = $sql."C_REFERENCE_NO,";			$sql2=$sql2."'".$Reference_No."',";										//整理NO
		//$sql = $sql."C_PROGRES_STAGE,";			$sql2=$sql2."'".$_POST['hidProgres_Stage']."',";						//進捗状態
		$sql = $sql."C_PROGRES_STAGE,";			$sql2=$sql2."'".$progress_stage."',";									//進捗状態
		$sql = $sql."C_CUST_CD,";				$sql2=$sql2."'".$_POST['txtCust_CD']."',";								//顧客CD
		$sql = $sql."V2_CUST_OFFICER,";			$sql2=$sql2."'".$_POST['txtCust_Officer']."',";							//顧客担当者
		$sql = $sql."V2_CUST_MANAGE_NO,";		$sql2=$sql2."'".$_POST['txtCust_Manage_No']."',";						//顧客管理NO
		$sql = $sql."C_PROD_CD,";				$sql2=$sql2."'".$_POST['txtProd_CD']."',";								//製品CD
		$sql = $sql."V2_PROD_NM,";				$sql2=$sql2."'".$_POST['txtProd_NM']."',";								//製品名
		$sql = $sql."V2_DRW_NO,";				$sql2=$sql2."'".$_POST['txtDRW_NO']."',";								//仕様番号
//		$sql = $sql."V2_MODEL,";				$sql2=$sql2."'".$_POST['txtModel']."',";								//型式
		$sql = $sql."V2_MODEL,";				$sql2=$sql2."'',";														//型式
		$sql = $sql."C_DIE_NO,";				$sql2=$sql2."'".$_POST['txtDie_NO']."',";								//金型番号
		$sql = $sql."V2_LOT_NO,";				$sql2=$sql2."'".$_POST['txtLot_NO']."',";								//ロットNO
		$sql = $sql."C_FLAW_KBN,";				$sql2=$sql2."'".$_POST['cmbFlaw_KBN']."',";								//不具合区分
		$sql = $sql."C_RECEPT_KBN,";			$sql2=$sql2."'".$_POST['cmbRecept_KBN']."',";							//受付区分
		$sql = $sql."C_CUST_CONTACT_KBN,";		$sql2=$sql2."'".$_POST['cmbCust_Contact_KBN']."',";						//客先よりの連絡方法
		$sql = $sql."N_TARGET_QTY,";			$sql2=$sql2."0".str_replace(",","",$_POST['txtTarget_QTY']).",";		//対象数量
		$sql = $sql."C_TARGET_SECTION_KBN,";	$sql2=$sql2."'".$_POST['cmbTarget_Section_KBN']."',";					//対象部門
		$sql = $sql."C_INCIDENT_KBN,";			$sql2=$sql2."'".$_POST['cmbIncident_KBN']."',";							//発行先
		$sql = $sql."V2_PRODUCT_OFFICER_NM,";	$sql2=$sql2."'".$_POST['txtProduct_Officer_NM']."',";					//生産担当者名
		$sql = $sql."C_PRODUCT_OUT_KA_CD,";		$sql2=$sql2."'".$_POST['cmbProduct_Out_Ka_CD']."',";					//生産流出
		$sql = $sql."C_CHECK_OUT_KA_CD1,";		$sql2=$sql2."'".$_POST['cmbCheck_Out_Ka_CD1']."',";						//検査流出1
		$sql = $sql."C_CHECK_OUT_KA_CD2,";		$sql2=$sql2."'".$_POST['cmbCheck_Out_Ka_CD2']."',";						//検査流出2
		$sql = $sql."V2_FLAW_CONTENTS,";		$sql2=$sql2."'".$_POST['txtFlaw_Contents']."',";						//不具合内容
		$sql = $sql."N_RETURN_QTY,";			$sql2=$sql2."0".str_replace(",","",$_POST['txtReturn_QTY']).",";		//返却数量
		$sql = $sql."N_BAD_QTY,";				$sql2=$sql2."0".str_replace(",","",$_POST['txtBat_QTY']).",";			//不良数量
		$sql = $sql."C_RETURN_DISPOSAL,";		$sql2=$sql2."'".$_POST['cmbReturn_Disposal']."',";						//返却品処理
		$sql = $sql."C_RESULT_KBN,";			$sql2=$sql2."'".$_POST['cmbResult_KBN']."',";							//結果区分
		$sql = $sql."N_CUST_AP_ANS_YMD,";		$sql2=$sql2."0".str_replace("/","",$_POST['txtCust_Ap_Ans_YMD']).",";	//顧客指定回答日
		$sql = $sql."N_ANS_YMD,";				$sql2=$sql2."0".str_replace("/","",$_POST['txtAns_YMD']).",";			//回答日
		$sql = $sql."N_MEASURES_YMD,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtMeasures_YMD']).",";		//対策日
		$sql = $sql."N_EFFECT_ALERT,";			$sql2=$sql2."".$_POST['cmbEffectAlert'].",";							//効果確認通知有無
		$sql = $sql."N_EFFECT_CONFIRM_YMD,";	$sql2=$sql2."0".str_replace("/","",$_POST['txtEffectConfirm_YMD']).",";	//対策効果確認日
		$sql = $sql."C_ANS_TANTO_CD,";			$sql2=$sql2."'".trim($_POST['txtAns_Tanto_CD'])."',";					//回答者
		$sql = $sql."N_ISSUE_YMD1,";			$sql2=$sql2."0,";														//発行日1
		$sql = $sql."N_ISSUE_YMD2,";			$sql2=$sql2."0,";														//発行日2
		$sql = $sql."N_ISSUE_YMD3,";			$sql2=$sql2."0,";														//発行日3
		$sql = $sql."V2_INCIDENT_CD1,";			$sql2=$sql2."'".$_POST['txtIncident_CD1']."',";							//発行先名称(社内)
		$sql = $sql."N_PC_AP_ANS_YMD1,";		$sql2=$sql2."0".str_replace("/","",$_POST['txtPc_Ap_Ans_YMD1']).",";	//品証指定回答日(社内)
		$sql = $sql."N_RETURN_YMD1,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtReturn_YMD1']).",";		//返却日(社内)
		$sql = $sql."N_COMPLETE_YMD1,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtComplete_YMD1']).",";		//完結日(社内)
		$sql = $sql."C_CONFIRM_TANTO_CD1,";		$sql2=$sql2."'".trim($_POST['txtConfirm_Tanto_CD1'])."',";				//確認者(社内)
		$sql = $sql."V2_REMARKS1,";				$sql2=$sql2."'".$_POST['txtRemarks1']."', ";							//備考(社内)
		$sql = $sql."V2_INCIDENT_CD2,";			$sql2=$sql2."'".$_POST['txtIncident_CD2']."',";							//発行先名称(社内)
		$sql = $sql."N_PC_AP_ANS_YMD2,";		$sql2=$sql2."0".str_replace("/","",$_POST['txtPc_Ap_Ans_YMD2']).",";	//品証指定回答日(協工)
		$sql = $sql."N_RETURN_YMD2,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtReturn_YMD2']).",";		//返却日(協工)
		$sql = $sql."N_COMPLETE_YMD2,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtComplete_YMD2']).",";		//完結日(協工)
		$sql = $sql."C_CONFIRM_TANTO_CD2,";		$sql2=$sql2."'".trim($_POST['txtConfirm_Tanto_CD2'])."',";				//確認者(協工)
		$sql = $sql."V2_REMARKS2,";				$sql2=$sql2."'".$_POST['txtRemarks2']."' ";								//備考(協工)
		$sql = $sql."N_INS_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";									//登録日時
		$sql = $sql."C_INS_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";										//登録担当者コード
		$sql = $sql."V2_INS_PG,";				$sql2=$sql2.", 'F_FLK0010' ";											//登録PG
		$sql = $sql."N_UPD_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";									//更新日時
		$sql = $sql."C_UPD_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";										//更新担当
		$sql = $sql."V2_UPD_PG,";				$sql2=$sql2.", 'F_FLK0010' ";											//更新PG
		$sql = $sql."N_DEL_FLG,";				$sql2=$sql2.", 0 ";														//削除フラグ
		$sql = $sql."N_UPDATE_COUNT, ";			$sql2=$sql2.", 0 ";														//更新回数
		$sql = $sql."N_VAL_ALERT_YMD, ";		$sql2=$sql2.", 0";														//有効性評価通知日
		$sql = $sql."C_QUICK_FIX_KBN, ";		$sql2=$sql2.",'".$_POST['cmbQuick_Fix_CD']."',";						//異常品暫定処置 2015/06/26追加
		$sql = $sql."N_CONTACT_ACCEPT_YMD, ";	$sql2=$sql2."0".str_replace("/","",$_POST['txtContact_Accept_YMD']).",";//連絡受理日 2016/09/02追加 k.kume
		$sql = $sql."C_PC_TANTO_CD)";			$sql2=$sql2."'".trim($_POST['txtPc_Tanto_CD'])."')";					//品証担当者 2019/07/06追加 k.kume

		$sql = $sql.$sql2;

		return $sql;
	}


	//不具合入力データ更新SQL作成
	function fFlawUpdateSql($Reference_No,$session,$count,$progress_stage){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//更新回数はカウントアップ
		$count = $count + 1;

		//進捗状態の変更は不具合対策入力にて行うため以下コメント
		//進捗状態のチェック
//		if($_POST['txtComplete_YMD']<>"" && $_POST['txtComplete_YMD'] <> "0"){
//			//完結日が登録されている場合は状態を解決にする
//			$progress_stage = '3';
//		}elseif($_POST['cmbResult_KBN']=='5'){
//			//上記以外で結果区分が保留の場合、保留とする
//			$progress_stage = '2';
//		}else{
//			//それ以外は受付
//			$progress_stage = '0';
//
//			//※対策中のフラグがあるが、不具合対策情報のデータが無いので「対策中」は発生し得ない
//		}

		$sql = "UPDATE T_TR_FLAW SET ";
		$sql = $sql."C_PROGRES_STAGE       = '".$progress_stage."',";									//進捗状態
		$sql = $sql."C_CUST_CD             = '".$_POST['txtCust_CD']."',";								//顧客CD
		$sql = $sql."V2_CUST_OFFICER       = '".$_POST['txtCust_Officer']."',";							//顧客担当者
		$sql = $sql."V2_CUST_MANAGE_NO     = '".$_POST['txtCust_Manage_No']."',";						//顧客管理NO
		$sql = $sql."C_PROD_CD             = '".$_POST['txtProd_CD']."',";								//製品CD
		$sql = $sql."V2_PROD_NM            = '".$_POST['txtProd_NM']."',";								//製品名
		$sql = $sql."V2_DRW_NO             = '".$_POST['txtDRW_NO']."',";								//仕様番号
//		$sql = $sql."V2_MODEL              = '".$_POST['txtModel']."',";								//型式
		$sql = $sql."C_DIE_NO              = '".$_POST['txtDie_NO']."',";								//金型番号
		$sql = $sql."V2_LOT_NO             = '".$_POST['txtLot_NO']."',";								//ロットNO
		$sql = $sql."C_FLAW_KBN            = '".$_POST['cmbFlaw_KBN']."',";								//不具合区分
		$sql = $sql."C_RECEPT_KBN          = '".$_POST['cmbRecept_KBN']."',";							//受付区分
		$sql = $sql."C_CUST_CONTACT_KBN    = '".$_POST['cmbCust_Contact_KBN']."',";						//客先よりの連絡方法
		$sql = $sql."N_TARGET_QTY          = ".str_replace(",","",$_POST['txtTarget_QTY']).",";			//対象数量
		//$sql = $sql."C_TARGET_SECTION_KBN  = '".$_POST['cmbTarget_Section_KBN']."',";					//対象部門
		$sql = $sql."C_INCIDENT_KBN        = '".$_POST['cmbIncident_KBN']."',";							//発行先
		$sql = $sql."V2_PRODUCT_OFFICER_NM = '".$_POST['txtProduct_Officer_NM']."',";					//生産担当者名
		$sql = $sql."C_PRODUCT_OUT_KA_CD   = '".$_POST['cmbProduct_Out_Ka_CD']."',";					//生産流出
		$sql = $sql."C_CHECK_OUT_KA_CD1    = '".$_POST['cmbCheck_Out_Ka_CD1']."',";						//検査流出1
		$sql = $sql."C_CHECK_OUT_KA_CD2    = '".$_POST['cmbCheck_Out_Ka_CD2']."',";						//検査流出2
		$sql = $sql."V2_FLAW_CONTENTS      = '".$_POST['txtFlaw_Contents']."',";						//不具合内容
		$sql = $sql."N_RETURN_QTY          = 0".str_replace(",","",$_POST['txtReturn_QTY']).",";		//返却数量
		$sql = $sql."N_BAD_QTY             = 0".str_replace(",","",$_POST['txtBat_QTY']).",";			//不良数量
		$sql = $sql."C_RETURN_DISPOSAL     = '".$_POST['cmbReturn_Disposal']."',";						//返却品処理
		$sql = $sql."C_RESULT_KBN          = '".$_POST['cmbResult_KBN']."',";							//結果区分
		$sql = $sql."N_CUST_AP_ANS_YMD     = 0".str_replace("/","",$_POST['txtCust_Ap_Ans_YMD']).",";	//顧客指定回答日
		$sql = $sql."N_ANS_YMD             = 0".str_replace("/","",$_POST['txtAns_YMD']).",";			//回答日
		$sql = $sql."N_MEASURES_YMD        = 0".str_replace("/","",$_POST['txtMeasures_YMD']).",";		//対策日
		$sql = $sql."N_EFFECT_ALERT        = ".$_POST['cmbEffectAlert'].",";							//効果確認期限通知
		$sql = $sql."N_EFFECT_CONFIRM_YMD  = 0".str_replace("/","",$_POST['txtEffectConfirm_YMD']).",";	//対策効果確認日
		$sql = $sql."C_ANS_TANTO_CD        = '".trim($_POST['txtAns_Tanto_CD'])."',";					//回答者
		$sql = $sql."V2_INCIDENT_CD1        = '".$_POST['txtIncident_CD1']."',";						//発行先名称(社内)
		$sql = $sql."N_PC_AP_ANS_YMD1       = 0".str_replace("/","",$_POST['txtPc_Ap_Ans_YMD1']).",";	//品証指定回答日(社内)
		$sql = $sql."N_RETURN_YMD1          = 0".str_replace("/","",$_POST['txtReturn_YMD1']).",";		//返却日(社内)
		$sql = $sql."N_COMPLETE_YMD1        = 0".str_replace("/","",$_POST['txtComplete_YMD1']).",";	//完結日(社内)
		$sql = $sql."C_CONFIRM_TANTO_CD1    = '".trim($_POST['txtConfirm_Tanto_CD1'])."',";				//確認者(社内)
		$sql = $sql."V2_REMARKS1            = '".$_POST['txtRemarks1']."',";							//備考(社内)
		$sql = $sql."V2_INCIDENT_CD2        = '".$_POST['txtIncident_CD2']."',";						//発行先名称(協工)
		$sql = $sql."N_PC_AP_ANS_YMD2       = 0".str_replace("/","",$_POST['txtPc_Ap_Ans_YMD2']).",";	//品証指定回答日(協工)
		$sql = $sql."N_RETURN_YMD2          = 0".str_replace("/","",$_POST['txtReturn_YMD2']).",";		//返却日(協工)
		$sql = $sql."N_COMPLETE_YMD2        = 0".str_replace("/","",$_POST['txtComplete_YMD2']).",";	//完結日(協工)
		$sql = $sql."C_CONFIRM_TANTO_CD2    = '".trim($_POST['txtConfirm_Tanto_CD2'])."',";				//確認者(協工)
		$sql = $sql."V2_REMARKS2            = '".$_POST['txtRemarks2']."',";							//備考(協工)
		$sql = $sql."N_UPD_YMD             = ".date("YmdHis")." ,";										//更新日時
		$sql = $sql."C_UPD_SHAIN_CD        = '".$session[0]."' ,";										//更新担当
		$sql = $sql."V2_UPD_PG             = 'F_FLK0010' ,";											//更新PG
		$sql = $sql."N_DEL_FLG             = 0 ,";														//削除フラグ
		$sql = $sql."N_UPDATE_COUNT        = (N_UPDATE_COUNT + 1),";						//更新回数
		$sql = $sql."C_QUICK_FIX_KBN       = '".$_POST['cmbQuick_Fix_CD']."', ";				//異常品暫定処置 2015/06/26
		$sql = $sql."N_CONTACT_ACCEPT_YMD  = 0".str_replace("/","",$_POST['txtContact_Accept_YMD']).",";	//連絡受理日 2016/09/02 add k.kume
		$sql = $sql."C_PC_TANTO_CD        = '".trim($_POST['txtPc_Tanto_CD'])."'";				//品証担当者 2019/07/06 add k.kume
		$sql = $sql." WHERE C_REFERENCE_NO = '".$_POST['txtReference_No']."'";					//整理NO

		return $sql;
	}

	//不具合入力データ削除SQL作成
	function fFlawDeleteSql($Reference_No,$session,$post){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//更新回数はカウントアップ
		$count = $count + 1;

		$sql = "UPDATE T_TR_FLAW SET ";
		$sql = $sql." N_DEL_FLG = 1 ";														//削除フラグ
		$sql = $sql.",N_UPD_YMD = ".date("YmdHis")." ";										//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD = '".$session[0]."' ";									//更新担当
		$sql = $sql.",V2_UPD_PG = 'F_FLK0010' ";											//更新PG
		$sql = $sql.",N_UPDATE_COUNT = N_UPDATE_COUNT + 1";											//更新回数
		$sql = $sql." WHERE C_REFERENCE_NO = '".$Reference_No."'";								//注文番号

		return $sql;
	}


	//品証各種書類発行日処理
	public function fUpdateIssuDate($rrce_no,$kbn,$session){

		require_once("module_common.php");

		$module_cmn = new module_common;

		try{

			//Oracleへの接続の確立(トランザクション開始)
			$conn = $this->fTransactionStart();
			$sql = "update T_TR_FLAW";
			$sql = $sql." set ";
			if($kbn == "1"){
				$sql = $sql." N_ISSUE_YMD1 = ";
			}elseif($kbn == "2"){
				$sql = $sql." N_ISSUE_YMD2 = ";
			}elseif($kbn == "3"){
				$sql = $sql." N_ISSUE_YMD3 = ";
			}
			$sql = $sql.date("Ymd");
			$sql = $sql.",N_UPD_YMD = ".date("YmdHis")." ";										//更新日時
			$sql = $sql.",C_UPD_SHAIN_CD = '".$session[0]."' ";									//更新担当
			$sql = $sql.",V2_UPD_PG = 'F_FLK0020' ";											//更新PG
			$sql = $sql.",N_UPDATE_COUNT = N_UPDATE_COUNT + 1";									//更新回数
			$sql = $sql." where C_REFERENCE_NO='".$rrce_no."'";

			if($kbn == "1"){
				$sql = $sql." and N_ISSUE_YMD1 = 0 ";
			}elseif($kbn == "2"){
				$sql = $sql." and N_ISSUE_YMD2 = 0 ";
			}elseif($kbn == "3"){
				$sql = $sql." and N_ISSUE_YMD3 = 0 ";
			}


			//SQLの分析
			$stmt = oci_parse($conn, $sql);


			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return false;
			}else{
				//トランザクション処理(commit)とOracle切断
				$this->fTransactionEnd($conn,true);
			}

		}catch(Exception $e){
			//トランザクション処理(rollback)とOracle切断
			$this->fTransactionEnd($conn,false);
			return false;
		}

		return true;

	}


	//不具合対策情報更新
	public function fActionHExcute($conn,$session,$rrceno,$count){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		$aPara = array();
		$aReturn = array();

		try{

			//更新回数で登録・更新を切り分け
			if($count == ""){
				//登録SQL取得
				$sql = $this->fActionHInsertSql($rrceno,$session);
			}else{
				//更新SQL取得
				$sql = $this->fActionHUpdateSql($rrceno,$session,$count);
			}


			//文字列変換
			$sql = $module_cmn->fChangSJIS_SQL($sql);

			//SQLの実行
			//echo $sql;
			$stmt = oci_parse($conn, $sql);


			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);

				$aReturn[0] = "err";
				return $aReturn;

			}


			if($strMsg <> ""){
				$aReturn[0] = "err".$strMsg;
			}else{
				$aReturn[0] = $rrceno;
			}


			return $aReturn;

		}catch(Exception $e){
			$aReturn[0] = "err";
			return $aReturn;
		}
	}
	//不具合対策データ登録SQL作成
	function fActionHInsertSql($rrceno,$session){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//戻り値用の配列
		$aReturn = array();
		//登録SQL作成



		$sql = "INSERT INTO T_TR_ACTION_H("; 	$sql2=" VALUES( ";
		$sql = $sql."C_REFERENCE_NO,";			$sql2=$sql2."'".$rrceno."'";							//整理NO
		$sql = $sql."C_HAPPEN_CAUSE_KBN,";		$sql2=$sql2.",'".$_POST['cmbHappenKbn']."'";			//発生原因
		$sql = $sql."V2_HAPPEN_NOTES,";			$sql2=$sql2.",'".$_POST['txtHappenNotes']."'";			//発生原因備考
		$sql = $sql."C_OUTFLOW_CAUSE_KBN,";		$sql2=$sql2.",'".$_POST['cmbOutFlowKbn']."'";			//流出原因
		$sql = $sql."V2_OUTFLOW_NOTES,";		$sql2=$sql2.",'".$_POST['txtOutFlowNotes']."'";			//流出原因備考
		$sql = $sql."V2_HAPPEN_ACTION,";		$sql2=$sql2.",'".$_POST['txtHappenAction']."'";			//発生対策
		$sql = $sql."V2_OUTFLOW_ACTION,";		$sql2=$sql2.",'".$_POST['txtOutFlowAction']."'";		//流出対策
		//$sql = $sql."V2_ARTICLE_DISPOSE,";		$sql2=$sql2.",'".$_POST['cmbDisposeKbn']."'";			//現品処置
		$sql = $sql."V2_ARTICLE_DISPOSE,";		$sql2=$sql2.",' '";										//現品処置   2016/09/02 現品処置は不要のため削除 k.kume
		$sql = $sql."C_ALL_ACTION_VALIDITY,";	$sql2=$sql2.",'".$_POST['cmbAllValKbn']."'";			//全ての対策の有効性
		$sql = $sql."N_INS_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";					//登録日時
		$sql = $sql."C_INS_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";						//登録担当者コード
		$sql = $sql."V2_INS_PG,";				$sql2=$sql2.", 'F_FLK0030' ";							//登録PG
		$sql = $sql."N_UPD_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";					//更新日時
		$sql = $sql."C_UPD_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";						//更新担当
		$sql = $sql."V2_UPD_PG,";				$sql2=$sql2.", 'F_FLK0030' ";							//更新PG
		$sql = $sql."N_DEL_FLG,";				$sql2=$sql2.", 0 ";										//削除フラグ
		$sql = $sql."N_UPDATE_COUNT) ";			$sql2=$sql2.", 0)";										//更新回数
		//echo $sql2;

		$sql = $sql.$sql2;

		return $sql;
	}

	//不具合対策データ更新SQL作成
	function fActionHUpdateSql($rrceno,$session,$count){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//更新回数はカウントアップ
		$count = $count + 1;


		$sql = "UPDATE T_TR_ACTION_H SET ";
		$sql = $sql."C_HAPPEN_CAUSE_KBN    = '".$_POST['cmbHappenKbn']."',";			//発生原因
		$sql = $sql."V2_HAPPEN_NOTES       = '".$_POST['txtHappenNotes']."',";			//発生原因備考
		$sql = $sql."C_OUTFLOW_CAUSE_KBN   = '".$_POST['cmbOutFlowKbn']."',";			//流出原因
		$sql = $sql."V2_OUTFLOW_NOTES      = '".$_POST['txtOutFlowNotes']."',";			//流出原因備考
		$sql = $sql."V2_HAPPEN_ACTION      = '".$_POST['txtHappenAction']."',";			//発生対策
		$sql = $sql."V2_OUTFLOW_ACTION     = '".$_POST['txtOutFlowAction']."',";		//流出対策
		//$sql = $sql."V2_ARTICLE_DISPOSE    = '".$_POST['cmbDisposeKbn']."',";			//現品処置 2016/09/02 現品処置は不要のため削除 k.kume
		$sql = $sql."C_ALL_ACTION_VALIDITY = '".$_POST['cmbAllValKbn']."',";			//全ての対策の有効性
		$sql = $sql."N_UPD_YMD             = ".date("YmdHis")." ,";						//更新日時
		$sql = $sql."C_UPD_SHAIN_CD        = '".$session[0]."' ,";						//更新担当
		$sql = $sql."V2_UPD_PG             = 'F_FLK0030' ,";							//更新PG
		$sql = $sql."N_DEL_FLG             = 0 ,";										//削除フラグ
		$sql = $sql."N_UPDATE_COUNT        = N_UPDATE_COUNT + 1";						//更新回数
		$sql = $sql." WHERE C_REFERENCE_NO = '".$rrceno."'";							//整理NO

		return $sql;
	}

	//不具合対策明細削除
	public function fActionDDelete($conn,$session,$rrceno){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		$aPara = array();
		$aReturn = array();

		try{


			//更新SQL取得
			$sql = $this->fActionDDeleteSql($rrceno,$session);

			//文字列変換
			$sql = $module_cmn->fChangSJIS_SQL($sql);

			//SQLの実行
			//echo $sql;
			$stmt = oci_parse($conn, $sql);


			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);

				$aReturn[0] = "err";
				return $aReturn;

			}


			if($strMsg <> ""){
				$aReturn[0] = "err".$strMsg;
			}else{
				$aReturn[0] = $rrceno;
			}


			return $aReturn;

		}catch(Exception $e){
			$aReturn[0] = "err";
			return $aReturn;
		}
	}
	//不具合対策明細データ削除SQL作成
	function fActionDDeleteSql($rrceno,$session){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//更新回数はカウントアップ
		$count = $count + 1;


		$sql = "DELETE T_TR_ACTION_D  ";
		$sql = $sql." WHERE C_REFERENCE_NO = '".$rrceno."'";							//整理NO

		return $sql;
	}


	//不具合対策データ登録処理
	public function fActionDInsert($conn,$session,$rrceno){

		require_once("module_sel.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;


		try{


			//画面項目取得(配列)
			$aRequest = $_POST["txtRequest"];
			$aTantoCd = $_POST["txtTantoCd"];
			$aLimitYmd = $_POST["txtLimitYmd"];
			$aOpeMat = $_POST["txtOpeMat"];
			$aOpeYmd = $_POST["txtOpeYmd"];
			$aResult = $_POST["txtResult"];
			$cmbActVal = $_POST["cmbActVal"];

			//未入力時は0日間
			if($aLimitYmd == ""){
				$aLimitYmd = 0;
			}
			if($aOpeYmd == ""){
				$aOpeYmd = 0;
			}


			//登録件数
			$intCnt = 0;
			$intCnt = count($aRequest);


			$i = 0;

			while($i < $intCnt){
				//要望が設定されていたら登録する
				if(trim($aRequest[$i]) <> ""){

					//登録SQL作成
					$sql = "INSERT INTO T_TR_ACTION_D VALUES( ";
					$sql = $sql."'".$rrceno."'" ;											//整理NO
					$sql = $sql.",'".($i+1)."'" ;											//明細NO
					$sql = $sql.",'".$aRequest[$i]."'" ;									//要望
					$sql = $sql.",'".trim($aTantoCd[$i])."'" ;								//対策担当者CD

					//期限
					if(str_replace("/","",$aLimitYmd[$i]) == ""){
						$sql = $sql.",0" ;													//期限
					}else{
						$sql = $sql.",'".str_replace("/","",$aLimitYmd[$i])."'" ;			//期限
					}

					$sql = $sql.",'".$aOpeMat[$i]."'" ;										//実施内容

					//実施日
					if(str_replace("/","",$aOpeYmd[$i]) == ""){
						$sql = $sql.",0" ;													//実施日
					}else{
						$sql = $sql.",'".str_replace("/","",$aOpeYmd[$i])."'" ;				//実施日
					}

					$sql = $sql.",'".$aResult[$i]."'" ;										//結果
					$sql = $sql.",'".$cmbActVal[$i]."'" ;									//有効性
					$sql = $sql.", ".date("YmdHis")." ";									//登録日時
					$sql = $sql.", '".$session[0]."' ";										//登録担当者コード
					$sql = $sql.", 'F_FLK0030' ";											//登録PG
					$sql = $sql.", ".date("YmdHis")." ";									//更新日時
					$sql = $sql.", '".$session[0]."' ";										//更新担当
					$sql = $sql.", 'F_FLK0030' ";											//更新PG
					$sql = $sql.", '0' ";													//削除フラグ
					$sql = $sql.", 0)";														//更新回数




					//文字列変換
					$sql = $module_cmn->fChangSJIS_SQL($sql);

					//SQLの分析
					$stmt = oci_parse($conn, $sql);
					//SQLの実行
					$r = oci_execute($stmt,OCI_DEFAULT);

					if (!$r) {
						$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
						echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
						echo "<pre>";
						echo htmlentities("ERROR_SQL:".$e['sqltext']);
						printf("\n%".($e['offset']+1)."s", "^");
						echo "</pre>";
						return false;
					}

					//SQLの実行
					//					if(!oci_execute($stmt,OCI_DEFAULT)){
					//						return false;
					//					}
				}

				$i = $i + 1;

			}

		}catch(Exception $e){
			return false;
		}

		return true;

	}



	//不具合情報状態更新
	public function fFlawStatusUpdate($conn,$session,$rrceno,$count){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		$aReturn = array();

		try{


			$sql = "";
			$sql = $sql." UPDATE T_TR_FLAW SET ";
			//全ての有効性が「未完」の場合は1(対策中)、「完了」の場合は3(解決済)
			if($_POST['cmbAllValKbn'] == "0"){
				$sql = $sql." C_PROGRES_STAGE = '1', ";
			}else{
				$sql = $sql." C_PROGRES_STAGE = '3', ";
			}

			$sql = $sql." N_UPD_YMD             = ".date("YmdHis")." ,";					//更新日時
			$sql = $sql." C_UPD_SHAIN_CD        = '".$session[0]."' ,";						//更新担当
			$sql = $sql." V2_UPD_PG             = 'F_FLK0030' ,";							//更新PG
			$sql = $sql." N_UPDATE_COUNT        = N_UPDATE_COUNT + 1";						//更新回数
			$sql = $sql." WHERE C_REFERENCE_NO ='".$rrceno."'";
			//文字列変換
			$sql = $module_cmn->fChangSJIS_SQL($sql);

			//SQLの実行
			//echo $sql;
			$stmt = oci_parse($conn, $sql);


			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);

				$aReturn[0] = "err";
				return $aReturn;

			}


			if($strMsg <> ""){
				$aReturn[0] = "err".$strMsg;
			}else{
				$aReturn[0] = $rrceno;
			}


			return $aReturn;

		}catch(Exception $e){
			$aReturn[0] = "err";
			return $aReturn;
		}
	}



	//主要顧客データ削除処理
	public function fPrimeCustDelete($conn,$strTaishoBumon){

		try{
			$sql = "DELETE ";
			$sql = $sql." FROM T_MS_PRIME_CUST ";
			$sql = $sql." WHERE C_TAISHO_SECTION = '".$strTaishoBumon."'";

			//SQLの分析
			$stmt = oci_parse($conn, $sql);
			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);
			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";

				return false;
			}

		}catch(Exception $e){
			return false;
		}

		return true;

	}

	//主要顧客データ登録処理
	public function fPrimeCustInsert($conn,$hidCount,$session,$hidUCount){

		require_once("module_sel.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;



		try{

			//画面項目取得
			$strBumon = $_POST["hidTaishoBumon"];

			//画面項目取得(配列)
			$aToriCd = $_POST["txtToriCd"];
			$aTekiyo = $_POST["txtTekiyo"];

			//更新回数に数値が入っていたらアップカウント
			if($hidUCount <> ""){
				$hidUCount = $hidUCount + 1;
			}else{
				$hidUCount = 0;
			}

			//登録件数
			$intCnt = 0;
			$intCnt = count($aToriCd);


			$i = 0;

			while($i < $intCnt){
				//取引先コードが設定されていたら登録する
				if(trim($aToriCd[$i]) <> ""){

					//登録SQL作成
					$sql = "INSERT INTO T_MS_PRIME_CUST VALUES( ";
					$sql = $sql."'".str_replace("/","",$strBumon)."'" ;							//対象部門
					$sql = $sql.",'".$aToriCd[$i]."'" ;											//取引先コード
					$sql = $sql.",'".$module_cmn->fChangSJIS_SQL($aTekiyo[$i])."'" ;			//摘要
					$sql = $sql.", ".date("YmdHis")." ";										//登録日時
					$sql = $sql.", '".$session[0]."' ";											//登録担当者コード
					$sql = $sql.", 'F_MST0030' ";												//登録PG
					$sql = $sql.", ".date("YmdHis")." ";										//更新日時
					$sql = $sql.", '".$session[0]."' ";											//更新担当
					$sql = $sql.", 'F_MST0030' ";												//更新PG
					$sql = $sql.", ".$hidUCount.")";											//更新回数


					//SQLの分析
					$stmt = oci_parse($conn, $sql);
					//SQLの実行
					$r = oci_execute($stmt,OCI_DEFAULT);

					if (!$r) {
						$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
						echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
						echo "<pre>";
						echo htmlentities("ERROR_SQL:".$e['sqltext']);
						printf("\n%".($e['offset']+1)."s", "^");
						echo "</pre>";
						return false;
					}

					//SQLの実行
					//					if(!oci_execute($stmt,OCI_DEFAULT)){
					//						return false;
					//					}
				}

				$i = $i + 1;

			}

		}catch(Exception $e){
			return false;
		}

		return true;

	}


	//環境紛争鉱物情報登録
	public function fEnvTorokuExcute($conn,$mode,$Reference_No,$session,$count){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		$aPara = array();
		$aReturn = array();

		try{

			//進捗状況の判断
			//明細画面項目取得(配列)
			$aToriCd = $_POST["txtToriCd"];
			$aAnsReceiptYmd = $_POST["txtAnsReceiptYmd"];
			
			//登録件数
			$intCnt = 0;
			$intCnt = count($aToriCd);
			//進捗状況
			$strStatus = "0"; 	//デフォルト:メーカー回答待ち
			//メーカー回答有無
			$bMakerAnsFlg = true;
			
			$i = 0;
			while($i < $intCnt){
				if($aAnsReceiptYmd[$i] == ""){
					//未回答があればfalse
					$bMakerAnsFlg = false;
				}
				$i = $i + 1;
			}
			
			if($_POST["txtAns_YMD"] <> "" || $_POST["chkCustAns"] == "1"){
				$strStatus = "2"; 	//調査完了済
			}elseif($bMakerAnsFlg == true && $_POST["txtAns_YMD"] == ""){
				$strStatus = "1"; 	//調査結果まとめ中
			}elseif($_POST["cmbMakerSurvey_KBN"] == "1"){
				$strStatus = "1"; 	//調査結果まとめ中
			}
			
			//処理モードで取得SQL切り分け(1:登録,2:更新,3:削除)
			if($mode == 1 || $mode == 5){

				//整理ＮＯ取得
				$Reference_No = $module_sel->fReference_NoSearch_Env($conn,$session,"F_FLK0060");
				//$Reference_No = 1;
				//登録SQL取得
				$sql = $this->fEnvInsertSql($Reference_No,$session,$strStatus);

			}elseif($mode == 2 && $Reference_No <> ""){
				//echo $session[0]."\n";
				//更新SQL取得
				$sql = $this->fEnvUpdateSql($Reference_No,$session,$strStatus);

			}elseif($mode == 3 && $Reference_No <> ""){
				//削除SQL取得
				$sql = $this->fEnvDeleteSql($Reference_No,$session,$count);

			}

			//文字列変換
			$sql = $module_cmn->fChangSJIS_SQL($sql);
			
			//SQLの実行
			//echo $sql;
			$stmt = oci_parse($conn, $sql);
			
			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);

				$aReturn[0] = "err";
				return $aReturn;

			}


			

			//Audit情報を更新
//			$sql = $this->fEnvInsertAuditSql($Reference_No,$session,$strPost,$mode);

			//文字列変換
//			$sql = $module_cmn->fChangSJIS_SQL($sql);

			//SQLの実行
			//echo $sql;
//			$stmt = oci_parse($conn, $sql);


			//SQLの実行
//			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);

				$aReturn[0] = "err";
				return $aReturn;

			}

			if($strMsg <> ""){
				$aReturn[0] = "err".$strMsg;
			}else{
				$aReturn[0] = $Reference_No;
			}

			return $aReturn;

		}catch(Exception $e){
			$aReturn[0] = "err";
			return $aReturn;
		}
	}
	
	//不具合入力データ登録SQL作成
	function fEnvInsertSql($Reference_No,$session,$pStatus){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//戻り値用の配列
		$aReturn = array();
		//登録SQL作成
		$sql = "INSERT INTO T_TR_ENV("; 		$sql2=" VALUES( ";
		$sql = $sql."C_REFERENCE_NO,";			$sql2=$sql2."'".$Reference_No."'";											//整理NO
		$sql = $sql."C_PROGRES_STAGE,";			$sql2=$sql2.",'".$pStatus."'";												//進捗状態
		$sql = $sql."N_CONTACT_ACCEPT_YMD,";	$sql2=$sql2.",0".str_replace("/","",$_POST['txtContact_Accept_YMD'])."";	//連絡受理日
		$sql = $sql."C_GET_INFO_KBN,";			$sql2=$sql2.",'".$_POST['cmbInfo_Get_KBN']."'";							//情報入手先
		$sql = $sql."C_CUST_CD,";				$sql2=$sql2.",'".$_POST['txtCust_CD']."'";								//顧客CD
		//$sql = $sql."V2_INFO_OFFICER,";			$sql2=$sql2.",'".$_POST['txtInfo_Officer']."'";						//顧客担当者
		$sql = $sql."V2_INFO_OFFICER,";			$sql2=$sql2.",'".$module_cmn->fEscape($_POST['txtInfo_Officer'])."'";	//顧客担当者
		$sql = $sql."C_SURVEY_KBN,";			$sql2=$sql2.",'".$_POST['cmbSurvey_KBN']."'";							//調査区分
		$sql = $sql."C_TARGET_SECTION_KBN,";	$sql2=$sql2.",'".$_POST['cmbTarget_Section_KBN']."'";					//対象部門
		//$sql = $sql."V2_ENV_CONTENTS,";			$sql2=$sql2.",'".$_POST['txtEnv_Contents']."'";						//内容
		$sql = $sql."V2_ENV_CONTENTS,";			$sql2=$sql2.",'".$module_cmn->fEscape($_POST['txtEnv_Contents'])."'";	//内容
		//$sql = $sql."V2_TARGET_ITEM,";			$sql2=$sql2.",'".$_POST['txtTarget_Item']."'";						//対象製品
		$sql = $sql."V2_TARGET_ITEM,";			$sql2=$sql2.",'".$module_cmn->fEscape($_POST['txtTarget_Item'])."'";	//対象製品
		$sql = $sql."N_CUST_AP_ANS_YMD,";		$sql2=$sql2.",0".str_replace("/","",$_POST['txtCust_Ap_Ans_YMD'])."";	//顧客指定回答日
		$sql = $sql."N_ANS_YMD,";				$sql2=$sql2.",0".str_replace("/","",$_POST['txtAns_YMD'])."";			//回答日	
		$sql = $sql."N_ANS_DOC1,";				$sql2=$sql2.",0".$_POST['chkAnsDoc1']."";								//提出要求書類1
		$sql = $sql."N_ANS_DOC2,";				$sql2=$sql2.",0".$_POST['chkAnsDoc2']."";								//提出要求書類2
		$sql = $sql."N_ANS_DOC3,";				$sql2=$sql2.",0".$_POST['chkAnsDoc3']."";								//提出要求書類3
		$sql = $sql."N_ANS_DOC4,";				$sql2=$sql2.",0".$_POST['chkAnsDoc4']."";								//提出要求書類4
		$sql = $sql."N_ANS_DOC5,";				$sql2=$sql2.",0".$_POST['chkAnsDoc5']."";								//提出要求書類5
		$sql = $sql."N_ANS_DOC6,";				$sql2=$sql2.",0".$_POST['chkAnsDoc6']."";								//提出要求書類6
		$sql = $sql."N_ANS_DOC7,";				$sql2=$sql2.",0".$_POST['chkAnsDoc7']."";								//提出要求書類7
		$sql = $sql."N_ANS_DOC8,";				$sql2=$sql2.",0".$_POST['chkAnsDoc8']."";								//提出要求書類8
		$sql = $sql."N_ANS_DOC9,";				$sql2=$sql2.",0".$_POST['chkAnsDoc9']."";								//提出要求書類9
		$sql = $sql."N_ANS_DOC10,";				$sql2=$sql2.",0";														//提出要求書類10
		$sql = $sql."N_ANS_DOC11,";				$sql2=$sql2.",0";														//提出要求書類11
		$sql = $sql."N_ANS_DOC12,";				$sql2=$sql2.",0";														//提出要求書類12
		$sql = $sql."N_ANS_DOC13,";				$sql2=$sql2.",0";														//提出要求書類13
		$sql = $sql."N_ANS_DOC14,";				$sql2=$sql2.",0";														//提出要求書類14
		$sql = $sql."N_ANS_DOC15,";				$sql2=$sql2.",0".$_POST['chkAnsDoc15']."";								//提出要求書類15
		$sql = $sql."V2_ANS_DOC15,";			$sql2=$sql2.",'".$_POST['txtAnsDocEtc']."'";							//提出要求書類15コメント
		$sql = $sql."C_ANS_TANTO_CD,";			$sql2=$sql2.",'".trim($_POST['txtAns_Tanto_CD'])."'";					//回答者
		$sql = $sql."C_MAKER_SURVEY_KBN,";		$sql2=$sql2.",'".$_POST['cmbMakerSurvey_KBN']."'";						//メーカー調査区分
		$sql = $sql."N_PC_AP_ANS_YMD,";			$sql2=$sql2.",0".str_replace("/","",$_POST['txtPc_Ap_Ans_YMD'])."";		//品証指定回答日
		$sql = $sql."N_INS_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";									//登録日時
		$sql = $sql."C_INS_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";										//登録担当者コード
		$sql = $sql."V2_INS_PG,";				$sql2=$sql2.", 'F_FLK0060' ";											//登録PG
		$sql = $sql."N_UPD_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";									//更新日時
		$sql = $sql."C_UPD_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";										//更新担当
		$sql = $sql."V2_UPD_PG,";				$sql2=$sql2.", 'F_FLK0060' ";											//更新PG
		$sql = $sql."N_DEL_FLG,";				$sql2=$sql2.", 0 ";														//削除フラグ
		$sql = $sql."N_UPDATE_COUNT) ";			$sql2=$sql2.", 0 )";													//更新回数

		//echo $sql.$sql2;

		$sql = $sql.$sql2;

		return $sql;
	}

	//不具合入力Auditデータ登録SQL作成
	function fEnvInsertAuditSql($Reference_No,$session,$pStatus){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//戻り値用の配列
		$aReturn = array();
		//登録SQL作成

		$sql = "INSERT INTO T_TR_ENV_AUDIT("; 	$sql2 = "VALUES(";
		$sql = $sql."C_PRCS_CLS,";				$sql2=$sql2."'".$cls."',";												//処理区分
		$sql = $sql."N_PRCS_YMD,";				$sql2=$sql2."".date("YmdHis").", ";										//処理日時
		$sql = $sql."C_REFERENCE_NO,";			$sql2=$sql2."'".$Reference_No."',";										//整理NO
		$sql = $sql."C_PROGRES_STAGE,";			$sql2=$sql2."'".$pStatus."',";											//進捗状態
		$sql = $sql."C_CUST_CD,";				$sql2=$sql2."'".$_POST['txtCust_CD']."',";								//顧客CD
		$sql = $sql."V2_CUST_OFFICER,";			$sql2=$sql2."'".$_POST['txtInfo_Officer']."',";							//顧客担当者
		$sql = $sql."C_SURVEY_KBN,";			$sql2=$sql2."'".$_POST['cmbSurvey_KBN']."',";							//調査区分
		$sql = $sql."C_TARGET_SECTION_KBN,";	$sql2=$sql2."'".$_POST['cmbTarget_Section_KBN']."',";					//対象部門		
		$sql = $sql."V2_ENV_CONTENTS,";			$sql2=$sql2."'".$_POST['txtEnv_Contents']."',";							//内容
		$sql = $sql."V2_TARGET_ITEM,";			$sql2=$sql2."'".$_POST['txtTarget_Item']."',";							//対象製品
		$sql = $sql."N_CUST_AP_ANS_YMD,";		$sql2=$sql2."0".str_replace("/","",$_POST['txtCust_Ap_Ans_YMD']).",";	//顧客指定回答日
		$sql = $sql."N_ANS_YMD,";				$sql2=$sql2."0".str_replace("/","",$_POST['txtAns_YMD']).",";			//回答日	
		$sql = $sql."N_ANS_DOC1,";				$sql2=$sql2.$_POST['N_ANS_DOC1'].",";								//提出要求書類1
		$sql = $sql."N_ANS_DOC2,";				$sql2=$sql2.$_POST['N_ANS_DOC2'].",";								//提出要求書類2
		$sql = $sql."N_ANS_DOC3,";				$sql2=$sql2.$_POST['N_ANS_DOC3'].",";								//提出要求書類3
		$sql = $sql."N_ANS_DOC4,";				$sql2=$sql2.$_POST['N_ANS_DOC4'].",";								//提出要求書類4
		$sql = $sql."N_ANS_DOC5,";				$sql2=$sql2.$_POST['N_ANS_DOC5'].",";								//提出要求書類5
		$sql = $sql."N_ANS_DOC6,";				$sql2=$sql2.$_POST['N_ANS_DOC6'].",";								//提出要求書類6
		$sql = $sql."N_ANS_DOC7,";				$sql2=$sql2.$_POST['N_ANS_DOC7'].",";								//提出要求書類7
		$sql = $sql."N_ANS_DOC8,";				$sql2=$sql2.$_POST['N_ANS_DOC8'].",";								//提出要求書類8
		$sql = $sql."N_ANS_DOC9,";				$sql2=$sql2.$_POST['N_ANS_DOC9'].",";								//提出要求書類9
		$sql = $sql."N_ANS_DOC10,";				$sql2=$sql2."0,";													//提出要求書類10
		$sql = $sql."N_ANS_DOC11,";				$sql2=$sql2."0,";													//提出要求書類11
		$sql = $sql."N_ANS_DOC12,";				$sql2=$sql2."0,";													//提出要求書類12
		$sql = $sql."N_ANS_DOC13,";				$sql2=$sql2."0,";													//提出要求書類13
		$sql = $sql."N_ANS_DOC14,";				$sql2=$sql2."0,";													//提出要求書類14
		$sql = $sql."N_ANS_DOC15,";				$sql2=$sql2.$_POST['N_ANS_DOC15'].",";								//提出要求書類15
		$sql = $sql."V2_ANS_DOC15,";			$sql2=$sql2."'".$_POST['V2_ANS_DOC15']."',";						//提出要求書類15コメント
		$sql = $sql."C_ANS_TANTO_CD,";			$sql2=$sql2."'".trim($_POST['txtAns_Tanto_CD'])."',";				//回答者
		$sql = $sql."C_MAKER_SURVEY_KBN,";		$sql2=$sql2."'".$_POST['cmbSurvey_KBN']."',";						//メーカー調査区分
		$sql = $sql."N_PC_AP_ANS_YMD,";			$sql2=$sql2."0".str_replace("/","",$_POST['txtPc_Ap_Ans_YMD']).",";	//品証指定回答日
		$sql = $sql."N_INS_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";									//登録日時
		$sql = $sql."C_INS_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";										//登録担当者コード
		$sql = $sql."V2_INS_PG,";				$sql2=$sql2.", 'F_FLK0060' ";											//登録PG
		$sql = $sql."N_UPD_YMD,";				$sql2=$sql2.", ".date("YmdHis")." ";									//更新日時
		$sql = $sql."C_UPD_SHAIN_CD,";			$sql2=$sql2.", '".$session[0]."' ";										//更新担当
		$sql = $sql."V2_UPD_PG,";				$sql2=$sql2.", 'F_FLK0060' ";											//更新PG
		$sql = $sql."N_DEL_FLG,";				$sql2=$sql2.", 0 ";														//削除フラグ
		$sql = $sql."N_UPDATE_COUNT) ";			$sql2=$sql2.", 0 ";														//更新回数

		$sql = $sql.$sql2;

		return $sql;
	}


	//環境紛争鉱物情報入力データ更新SQL作成
	function fEnvUpdateSql($Reference_No,$session,$pStatus){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//更新回数はカウントアップ
		$count = $count + 1;
		$sql = "UPDATE T_TR_ENV SET ";
		$sql = $sql."C_PROGRES_STAGE        = '".$pStatus."'";												//進捗状態
		$sql = $sql.",N_CONTACT_ACCEPT_YMD  = 0".str_replace("/","",$_POST['txtContact_Accept_YMD'])."";	//連絡受理日
		$sql = $sql.",C_GET_INFO_KBN        = '".$_POST['cmbInfo_Get_KBN']."'";								//情報入手先
		$sql = $sql.",C_CUST_CD             = '".$_POST['txtCust_CD']."'";									//顧客CD
		//$sql = $sql.",V2_INFO_OFFICER       = '".$_POST['txtInfo_Officer']."'";							//顧客担当者
		$sql = $sql.",V2_INFO_OFFICER       = '".$module_cmn->fEscape($_POST['txtInfo_Officer'])."'";		//顧客担当者
		$sql = $sql.",C_SURVEY_KBN          = '".$_POST['cmbSurvey_KBN']."'";								//調査区分
		$sql = $sql.",C_TARGET_SECTION_KBN  = '".$_POST['cmbTarget_Section_KBN']."'";						//対象部門
		//$sql = $sql.",V2_ENV_CONTENTS       = '".$_POST['txtEnv_Contents']."'";							//内容
		$sql = $sql.",V2_ENV_CONTENTS       = '".$module_cmn->fEscape($_POST['txtEnv_Contents'])."'";		//内容
		//$sql = $sql.",V2_TARGET_ITEM        = '".$_POST['txtTarget_Item']."'";							//対象製品
		$sql = $sql.",V2_TARGET_ITEM       = '".$module_cmn->fEscape($_POST['txtTarget_Item'])."'";			//対象製品
		$sql = $sql.",N_CUST_AP_ANS_YMD     = 0".str_replace("/","",$_POST['txtCust_Ap_Ans_YMD'])."";		//顧客指定回答日
		$sql = $sql.",N_ANS_YMD             = 0".str_replace("/","",$_POST['txtAns_YMD'])."";				//回答日	
		$sql = $sql.",N_ANS_DOC1            = 0".$_POST['chkAnsDoc1']."";									//提出要求書類1
		$sql = $sql.",N_ANS_DOC2            = 0".$_POST['chkAnsDoc2']."";									//提出要求書類2
		$sql = $sql.",N_ANS_DOC3            = 0".$_POST['chkAnsDoc3']."";									//提出要求書類3
		$sql = $sql.",N_ANS_DOC4            = 0".$_POST['chkAnsDoc4']."";									//提出要求書類4
		$sql = $sql.",N_ANS_DOC5            = 0".$_POST['chkAnsDoc5']."";									//提出要求書類5
		$sql = $sql.",N_ANS_DOC6            = 0".$_POST['chkAnsDoc6']."";									//提出要求書類6
		$sql = $sql.",N_ANS_DOC7            = 0".$_POST['chkAnsDoc7']."";									//提出要求書類7
		$sql = $sql.",N_ANS_DOC8            = 0".$_POST['chkAnsDoc8']."";									//提出要求書類8
		$sql = $sql.",N_ANS_DOC9            = 0".$_POST['chkAnsDoc9']."";									//提出要求書類9
		$sql = $sql.",N_ANS_DOC15           = 0".$_POST['chkAnsDoc15']."";									//提出要求書類15
		//$sql = $sql.",V2_ANS_DOC15          = '".$_POST['txtAnsDocEtc']."'";								//提出要求書類15コメント
		$sql = $sql.",V2_ANS_DOC15          = '".$module_cmn->fEscape($_POST['txtAnsDocEtc'])."'";			//提出要求書類15コメント
		$sql = $sql.",C_ANS_TANTO_CD        = '".trim($_POST['txtAns_Tanto_CD'])."'";						//回答者
		$sql = $sql.",C_MAKER_SURVEY_KBN    = '".$_POST['cmbMakerSurvey_KBN']."'";							//メーカー調査区分
		$sql = $sql.",N_PC_AP_ANS_YMD       = 0".str_replace("/","",$_POST['txtPc_Ap_Ans_YMD'])."";			//品証指定回答日
		$sql = $sql.",N_UPD_YMD             = ".date("YmdHis")." ";											//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD        = '".$session[0]."' ";											//更新担当
		$sql = $sql.",V2_UPD_PG             = 'F_FLK0060' ";												//更新PG
		$sql = $sql.",N_UPDATE_COUNT        = N_UPDATE_COUNT + 1 ";											//更新回数
		$sql = $sql." WHERE C_REFERENCE_NO = '".$Reference_No."'";											//整理NO
		return $sql;
	}

	//環境紛争鉱物情報データ削除SQL作成
	function fEnvDeleteSql($Reference_No,$session,$post){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//更新回数はカウントアップ
		$count = $count + 1;

		$sql = "UPDATE T_TR_ENV SET ";
		$sql = $sql." N_DEL_FLG = 1 ";														//削除フラグ
		$sql = $sql.",N_UPD_YMD = ".date("YmdHis")." ";										//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD = '".$session[0]."' ";									//更新担当
		$sql = $sql.",V2_UPD_PG = 'F_FLK0010' ";											//更新PG
		$sql = $sql.",N_UPDATE_COUNT = N_UPDATE_COUNT + 1";											//更新回数
		$sql = $sql." WHERE C_REFERENCE_NO = '".$Reference_No."'";								//注文番号

		return $sql;
	}
	
	
	//メーカー調査回答情報登録
	public function fMakerAnsExcute($conn,$mode,$Reference_No,$session,$count){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		$aPara = array();
		$aReturn = array();

		try{
			//登録・更新・流用登録
			if($mode == 1 || $mode == 2 || $mode == 5){
				//削除SQL作成
				$sql = $this->fMakerAnsDDeleteSql($Reference_No,$session);

			}else{
				//更新SQL作成
				$sql = $this->fMakerAnsDUpdateSql($Reference_No,$session);
			}

			//文字列変換
			$sql = $module_cmn->fChangSJIS_SQL($sql);

			//SQLの実行
			$stmt = oci_parse($conn, $sql);

			//登録SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);
	
			//登録・更新・流用登録
			if($mode == 1 || $mode == 2 || $mode == 5){
			
				if (!$this->fMakerAnsDInsertSql($conn,$session,$Reference_No)) {
					$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
					echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
					echo "<pre>";
					echo htmlentities("ERROR_SQL:".$e['sqltext']);
					printf("\n%".($e['offset']+1)."s", "^");
					echo "</pre>";
					//トランザクション処理(rollback)とOracle切断
					$this->fTransactionEnd($conn,false);

					$aReturn[0] = "err";
					return $aReturn;

				}

				if($strMsg <> ""){
					$aReturn[0] = "err".$strMsg;
				}else{
					$aReturn[0] = $Reference_No;
				}
			}
			return $aReturn;

		}catch(Exception $e){
			$aReturn[0] = "err";
			return $aReturn;
		}
	}
	
	
	//環境紛争鉱物情報メーカ回答情報データ削除SQL作成
	function fMakerAnsDDeleteSql($rrceno,$session){

		$sql = "DELETE T_TR_ENV_D  ";
		$sql = $sql." WHERE C_REFERENCE_NO = '".$rrceno."'";							//整理NO

		return $sql;
	}

	//環境紛争鉱物情報メーカ回答情報データ更新SQL作成
	function fMakerAnsDUpdateSql($rrceno,$session){

		$sql = "UPDATE T_TR_ENV_D  ";
		$sql = $sql." SET N_DEL_FLG = 1  ";
		$sql = $sql." ,V2_UPD_PG = 'F_FLK0060'  ";
		$sql = $sql." ,C_UPD_SHAIN_CD = '".$session[0]."' ";							//更新担当
		$sql = $sql." WHERE C_REFERENCE_NO = '".$rrceno."'";							//整理NO

		return $sql;
	}

	//環境紛争鉱物情報メーカ回答情報データ登録処理
	function fMakerAnsDInsertSql($conn,$session,$rrceno){

		require_once("module_sel.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;


		try{


			//画面項目取得(配列)
			$aToriCd = $_POST["txtToriCd"];
			$aAnsReceiptYmd = $_POST["txtAnsReceiptYmd"];
			$aComment = $_POST["txtComment"];
			
			//未入力時は0日
			if($aAnsReceiptYmd == ""){
				$aAnsReceiptYmd = 0;
			}
			

			//登録件数
			$intCnt = 0;
			$intCnt = count($aToriCd);


			$i = 0;

			while($i < $intCnt){
				//取引先CDが設定されていたら登録する
				if(trim($aToriCd[$i]) <> ""){

					//登録SQL作成
					$sql = "INSERT INTO T_TR_ENV_D VALUES( ";
					$sql = $sql."'".$rrceno."'" ;											//整理NO
					$sql = $sql.",'".($i+1)."'" ;											//明細NO
					$sql = $sql.",'".$aToriCd[$i]."'" ;										//取引先CD
					
					if(str_replace("/","",$aAnsReceiptYmd[$i]) == ""){
						$sql = $sql.",0" ;													//回答受領日
					}else{
						$sql = $sql.",'".str_replace("/","",$aAnsReceiptYmd[$i])."'" ;		//回答受領日
					}
					//$sql = $sql.",'".$aComment[$i]."'" ;									//備考
					$sql = $sql.",'".$module_cmn->fEscape($aComment[$i])."'" ;				//備考
					$sql = $sql.", ".date("YmdHis")." ";									//登録日時
					$sql = $sql.", '".$session[0]."' ";										//登録担当者コード
					$sql = $sql.", 'F_FLK0060' ";											//登録PG
					$sql = $sql.", ".date("YmdHis")." ";									//更新日時
					$sql = $sql.", '".$session[0]."' ";										//更新担当
					$sql = $sql.", 'F_FLK0060' ";											//更新PG
					$sql = $sql.", '0' ";													//削除フラグ
					$sql = $sql.", 0)";														//更新回数

					//文字列変換
					$sql = $module_cmn->fChangSJIS_SQL($sql);

					//SQLの分析
					$stmt = oci_parse($conn, $sql);
					//SQLの実行
					$r = oci_execute($stmt,OCI_DEFAULT);

					if (!$r) {
						$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
						echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
						echo "<pre>";
						echo htmlentities("ERROR_SQL:".$e['sqltext']);
						printf("\n%".($e['offset']+1)."s", "^");
						echo "</pre>";
						return false;
					}
				}

				$i = $i + 1;

			}

		}catch(Exception $e){
			return false;
		}

		return true;

	}

//2019/04/01 AD START T.FUJITA
	//赤伝緑伝情報登録
	//引数
	//$conn				
	//$mode				更新モード
	//$Reference_NO		伝票NO
	//$Reference_SEQ	伝票SEQ
	//$session			接続情報
	//$count			件数
	public function fTrblTorokuExcute($conn,$mode,$Reference_NO,$Reference_SEQ,$session,$count){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aPara = array();
		$aReturn = array();

		try{
			//進捗状態
			$strStatus = "0"; 	//デフォルト:保留
			//メーカー回答有無
			$bMakerAnsFlg = true;
			
			if($_POST["txtDecision_YMD"] <> "" && $_POST["txtApproval_YMD"] == ""){
				//処理判定済み	「処理判定日」が登録されていて、「製造部長承認日」が未登録
				$strStatus = "1";
			}elseif($_POST["txtDecision_YMD"] <> "" && $_POST["txtApproval_YMD"] <> ""){
				//処理承認済み	「製造部長承認日」が登録
				$strStatus = "2";
			}

			//処理モードで取得SQL切り分け(1:登録,2:更新,3:削除)
			if($mode == 1 || $mode == 5){
				//登録SQL取得
				$sql = $this->fTrblInsertSql($Reference_NO,$Reference_SEQ,$session,$strStatus);
			}elseif($mode == 2 && $Reference_NO <> ""){
				//更新SQL取得
				$sql = $this->fTrblUpdateSql($Reference_NO,$Reference_SEQ,$session,$strStatus);
			}elseif($mode == 3 && $Reference_NO <> ""){
				//削除SQL取得
				$sql = $this->fTrblDeleteSql($Reference_NO,$Reference_SEQ,$session,$count);
			}

			//文字列変換
			$sql = $module_cmn->fChangSJIS_SQL($sql);

			//SQLの実行
			$stmt = oci_parse($conn, $sql);

			//SQLの実行
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);

				$aReturn[0] = "err";
				return $aReturn;
			}

			if($strMsg <> ""){
				$aReturn[0] = "err".$strMsg;
			}else{
				$aReturn[0] = $Reference_NO;
				$aReturn[1] = $Reference_SEQ;
			}

			return $aReturn;

		}catch(Exception $e){
			$aReturn[0] = "err";
			return $aReturn;
		}
	}

	//赤伝緑伝入力データ登録SQL作成
	//引数
	//$Reference_No		伝票NO
	//$Reference_SEQ	伝票SEQ
	//$session			登録担当者コード
	//$pStatus			進捗状態
	function fTrblInsertSql($Reference_No,$Reference_SEQ,$session,$pStatus){

		//戻り値用の配列
		$aReturn = array();
		//登録SQL作成
		$sql = "INSERT INTO T_TR_TRBL("; 				$sql2=" VALUES( ";
		$sql = $sql." C_REFERENCE_NO";					$sql2=$sql2."'".$Reference_No."'";											//伝票NO
		$sql = $sql.",N_REFERENCE_SEQ";					$sql2=$sql2.",".$Reference_SEQ."";											//伝票SEQ
		$sql = $sql.",C_REFERENCE_KBN";					$sql2=$sql2.",'".$_POST['txtReference_KBN']."'";							//伝票種別
		$sql = $sql.",C_TARGET_SECTION_KBN";			$sql2=$sql2.",'".$_POST['cmbTargetSection_KBN']."'";						//対象部門
		$sql = $sql.",C_POINTREF_NO";					$sql2=$sql2.",'".$_POST['txtPointRef_NO']."'";								//代表伝票NO
		// 2019/09/20 ADD START
		//起因部署・協力会社CD入力済の場合、起因部署CDも更新
		//$sql = $sql.",C_BUSYO_CD";						$sql2=$sql2.",'".$_POST['txtBusyo_CD']."'";								//起因部署CD
		if(trim($_POST['txtPartner_CD']) <> ""){
			$sql = $sql.",C_BUSYO_CD";					$sql2=$sql2.",'".trim($_POST['txtPartner_CD'])."'";							//起因部署・協力会社CD
		}else{
			$sql = $sql.",C_BUSYO_CD";					$sql2=$sql2.",'".trim($_POST['txtBusyo_CD'])."'";							//起因部署CD
		}
		// 2019/09/20 ADD END
		$sql = $sql.",V2_SUMBIKOU";						$sql2=$sql2.",'".$_POST['txtSumBikou']."'";									//集計用備考欄
		$sql = $sql.",N_INCIDENT_YMD";					$sql2=$sql2.",0".str_replace("/","",$_POST['txtIncident_YMD'])."";			//伝票発行日
		$sql = $sql.",C_PROGRES_STAGE";					$sql2=$sql2.",'".$pStatus."'";												//進捗状態
		$sql = $sql.",V2_PROD_GRP_NM";					$sql2=$sql2.",'".$_POST['txtProdGrp_NM']."'";								//生産グループ名
		$sql = $sql.",V2_PROD_TANTO_NM1";				$sql2=$sql2.",'".$_POST['txtProdT_NM1']."'";								//生産担当者1
		$sql = $sql.",V2_PROD_TANTO_NM2";				$sql2=$sql2.",'".$_POST['txtProdT_NM2']."'";								//生産担当者2
		$sql = $sql.",V2_PROD_TANTO_NM3";				$sql2=$sql2.",'".$_POST['txtProdT_NM3']."'";								//生産担当者3
		$sql = $sql.",V2_EXAM_GRP_NM";					$sql2=$sql2.",'".$_POST['txtExamGrp_NM']."'";								//検査グループ名
		$sql = $sql.",V2_HINGI_TANTO_NM";				$sql2=$sql2.",'".$_POST['txtHingiT_NM']."'";								//品技担当者
		$sql = $sql.",V2_CUST_NM";						$sql2=$sql2.",'".$_POST['txtCust_NM']."'";									//得意先名
		$sql = $sql.",C_PROD_CD";						$sql2=$sql2.",'".$_POST['txtProd_CD']."'";									//製品CD
		$sql = $sql.",C_DIE_NO";						$sql2=$sql2.",'".$_POST['txtDie_NO']."'";									//金型番号
		$sql = $sql.",V2_PROD_NM";						$sql2=$sql2.",'".$_POST['txtProd_NM']."'";									//製品名
		$sql = $sql.",V2_DRW_NO";						$sql2=$sql2.",'".$_POST['txtDRW_NO']."'";									//仕様番号
		$sql = $sql.",C_FLAW_LOT_NO";					$sql2=$sql2.",'".$_POST['txtFlawLot_NO']."'";								//不具合ロットNO
		$sql = $sql.",N_FLAW_LOT_QTY";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtFlawLot_QTY'])."";			//不具合数量（個）
		$sql = $sql.",N_UNIT_PRICE";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtUnitPrice'])."";				//単価（円）
		$sql = $sql.",N_FLAW_PRICE";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtFlawPrice'])."";				//不具合金額（円）
		$sql = $sql.",C_PLATING_CD";					$sql2=$sql2.",'".$_POST['txtPlating_CD']."'";								//めっき先CD
		$sql = $sql.",V2_PLATING_NM";					$sql2=$sql2.",'".$_POST['txtPlating_NM']."'";								//めっき先名
		$sql = $sql.",C_KBN";							$sql2=$sql2.",'".$_POST['cmbKBN']."'";										//区分
		$sql = $sql.",V2_MATERIAL_SPEC";				$sql2=$sql2.",'".$_POST['txtMaterialSpec']."'";								//材料仕様
		$sql = $sql.",C_FLAW_KBN1";						$sql2=$sql2.",'".$_POST['cmbFlaw_KBN1']."'";								//不具合区分1
		$sql = $sql.",C_FLAW_KBN2";						$sql2=$sql2.",'".$_POST['cmbFlaw_KBN2']."'";								//不具合区分2
		$sql = $sql.",C_FLAW_KBN3";						$sql2=$sql2.",'".$_POST['cmbFlaw_KBN3']."'";								//不具合区分3
		$sql = $sql.",V2_FLAW_CONTENTS";				$sql2=$sql2.",'".$_POST['txtFlawContents']."'";								//不具合内容
		$sql = $sql.",N_SPECIAL_YMD";					$sql2=$sql2.",0".str_replace("/","",$_POST['txtSpecial_YMD'])."";			//特別作業記録発行日
		$sql = $sql.",N_SPECIAL";						$sql2=$sql2.",0".$_POST['chkSpecial']."";									//特別作業記録チェック
		$sql = $sql.",N_PROCESS_PERIOD_YMD";			$sql2=$sql2.",0".str_replace("/","",$_POST['txtProcessPeriod_YMD'])."";		//処理期限
		$sql = $sql.",V2_STRETCH_REASON";				$sql2=$sql2.",'".$_POST['txtStretchReason']."'";							//処理期限延伸理由
		// 2019/05/13 ADD START
		$sql = $sql.",N_SUBMIT_YMD1";					$sql2=$sql2.",0".str_replace("/","",$_POST['txtSubmit_YMD1'])."";			//特別作業払い出し日1
		$sql = $sql.",N_SUBMIT_YMD2";					$sql2=$sql2.",0".str_replace("/","",$_POST['txtSubmit_YMD2'])."";			//特別作業払い出し日2
		$sql = $sql.",N_SUBMIT_YMD3";					$sql2=$sql2.",0".str_replace("/","",$_POST['txtSubmit_YMD3'])."";			//特別作業払い出し日3
		$sql = $sql.",N_BACK_YMD1";						$sql2=$sql2.",0".str_replace("/","",$_POST['txtBack_YMD1'])."";				//特別作業戻り日1
		$sql = $sql.",N_BACK_YMD2";						$sql2=$sql2.",0".str_replace("/","",$_POST['txtBack_YMD2'])."";				//特別作業戻り日2
		$sql = $sql.",N_BACK_YMD3";						$sql2=$sql2.",0".str_replace("/","",$_POST['txtBack_YMD3'])."";				//特別作業戻り日3
		// 2019/05/13 ADD END
		$sql = $sql.",N_INITIAL_PROCESS_PERIOD_YMD";	$sql2=$sql2.",0".str_replace("/","",$_POST['txtProcessPeriod_YMD'])."";		//初期処理期限（処理期限）
		$sql = $sql.",C_TANTO_CD";						$sql2=$sql2.",'".trim($_POST['txtTanto_CD'])."'";							//品証担当者CD
		$sql = $sql.",N_NON_ISSUE";						$sql2=$sql2.",0".$_POST['chkNonIssue']."";									//発行不要
		$sql = $sql.",C_INCIDENT_CD";					$sql2=$sql2.",'".trim($_POST['txtIncident_CD'])."'";						//報告書発行先部署・協力会社CD
		$sql = $sql.",N_PROCESS_LIMIT_YMD";				$sql2=$sql2.",0".str_replace("/","",$_POST['txtProcessLimit_YMD'])."";		//報告書処理期限
		$sql = $sql.",N_RETURN_YMD";					$sql2=$sql2.",0".str_replace("/","",$_POST['txtReturn_YMD'])."";			//返却日
		$sql = $sql.",N_COMP_YMD";						$sql2=$sql2.",0".str_replace("/","",$_POST['txtComplete_YMD'])."";			//完結日
		$sql = $sql.",N_DECISION_YMD";					$sql2=$sql2.",0".str_replace("/","",$_POST['txtDecision_YMD'])."";			//処理判定日
		$sql = $sql.",N_APPROVAL_YMD";					$sql2=$sql2.",0".str_replace("/","",$_POST['txtApproval_YMD'])."";			//製造部長承認日
		$sql = $sql.",N_EXCLUDED";						$sql2=$sql2.",0".$_POST['chkExcluded']."";									//不良集計対象外
		$sql = $sql.",N_SELECTION";						$sql2=$sql2.",0".str_replace(",","",$_POST['txtSelection'])."";				//選別工数（h）
		$sql = $sql.",C_DUE_PROCESS";					$sql2=$sql2.",'".$_POST['cmbDueProcess_KBN']."'";							//起因工程
		$sql = $sql.",V2_COMENTS";						$sql2=$sql2.",'".$_POST['txtComments']."'";									//その他コメント
		$sql = $sql.",C_PARTNER_CD";					$sql2=$sql2.",'".trim($_POST['txtPartner_CD'])."'";							//起因部署・協力会社CD
		$sql = $sql.",C_PROCESS";						$sql2=$sql2.",'".$_POST['cmbProcess_KBN']."'";								//処理
		$sql = $sql.",N_FAILURE_QTY";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtFailure_QTY'])."";			//納入数量（個）
		$sql = $sql.",N_DISPOSAL_QTY";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtDisposal_QTY'])."";			//廃棄数量（個）
		$sql = $sql.",N_RETURN_QTY";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtReturn_QTY'])."";			//返却数量（個）
		$sql = $sql.",N_LOSS_QTY";						$sql2=$sql2.",0".str_replace(",","",$_POST['txtLoss_QTY'])."";				//調整ﾛｽ数量（個）
		$sql = $sql.",N_EXCLUD_QTY";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtExclud_QTY'])."";			//対象外数量（個）
		$sql = $sql.",N_FAILURE_PRICE";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtFailurePrice'])."";			//納入金額（円）
		$sql = $sql.",N_DISPOSAL_PRICE";				$sql2=$sql2.",0".str_replace(",","",$_POST['txtDisposalPrice'])."";			//廃棄金額（円）
		$sql = $sql.",N_RETURN_PRICE";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtReturnPrice'])."";			//返却金額（円）
		$sql = $sql.",N_LOSS_PRICE";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtLossPrice'])."";				//調整ﾛｽ金額（円）
		$sql = $sql.",N_EXCLUD_PRICE";					$sql2=$sql2.",0".str_replace(",","",$_POST['txtExcludPrice'])."";			//対象外金額（円）
		$sql = $sql.",C_PLAN_NO";						$sql2=$sql2.",'".$_POST['hidPlan_NO']."'";									//計画NO
		$sql = $sql.",C_PLAN_SEQ";						$sql2=$sql2.",'".$_POST['hidPlanSeq']."'";									//計画SEQ
		$sql = $sql.",N_INS_YMD";						$sql2=$sql2.", ".date("YmdHis")."";											//登録日時
		$sql = $sql.",C_INS_SHAIN_CD";					$sql2=$sql2.",'".$session[0]."' ";											//登録担当者コード
		$sql = $sql.",V2_INS_PG";						$sql2=$sql2.",'F_FLK0080'";													//登録PG
		$sql = $sql.",N_UPD_YMD";						$sql2=$sql2.", ".date("YmdHis")."";											//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD";					$sql2=$sql2.",'".$session[0]."'";											//更新担当
		$sql = $sql.",V2_UPD_PG";						$sql2=$sql2.",'F_FLK0080'";													//更新PG
		$sql = $sql.",N_UPDATE_COUNT) ";				$sql2=$sql2.", 0 )";														//更新回数
		$sql = $sql.$sql2;
		return $sql;
	}

	//赤伝緑伝入力データ更新SQL作成
	//引数
	//$Reference_No		伝票NO
	//$Reference_SEQ	伝票SEQ
	//$session			登録担当者コード
	//$pStatus			進捗状態
	function fTrblUpdateSql($Reference_No,$Reference_SEQ,$session,$pStatus){

		require_once("module_sel.php");
		require_once("module_common.php");

		$module_sel = new module_sel;
		$module_cmn = new module_common;

		//更新回数はカウントアップ
		$count = $count + 1;
		$sql = "UPDATE T_TR_TRBL SET ";
		$sql = $sql." C_PROGRES_STAGE		= '".$pStatus."'";												//進捗状態
		$sql = $sql.",C_REFERENCE_KBN		= '".$_POST['txtReference_KBN']."'";							//伝票種別
		$sql = $sql.",C_TARGET_SECTION_KBN	= '".$_POST['cmbTargetSection_KBN']."'";						//対象部門
		$sql = $sql.",C_POINTREF_NO			= '".$_POST['txtPointRef_NO']."'";								//代表伝票NO
		// 2019/09/20 ADD START
		//$sql = $sql.",C_BUSYO_CD			= '".$_POST['txtBusyo_CD']."'";									//起因部署CD
		//起因部署CD
		//起因部署・協力会社CD入力済の場合、起因部署CDも更新
		if(trim($_POST['txtPartner_CD']) <> ""){
			$sql = $sql.",C_BUSYO_CD			= '".trim($_POST['txtPartner_CD'])."'";						//起因部署・協力会社CD
		}else{
			$sql = $sql.",C_BUSYO_CD			= '".trim($_POST['txtBusyo_CD'])."'";						//起因部署CD
		}
		// 2019/09/20 ADD END
		$sql = $sql.",V2_SUMBIKOU			= '".$_POST['txtSumBikou']."'";									//集計用備考欄
		$sql = $sql.",C_FLAW_LOT_NO			= '".str_replace("'","''",$_POST['txtFlawLot_NO'])."'";			//不具合ロットNO
		$sql = $sql.",N_FLAW_LOT_QTY		= 0".str_replace(",","",$_POST['txtFlawLot_QTY'])."";			//不具合数量（個）
		$sql = $sql.",N_UNIT_PRICE			= 0".str_replace(",","",$_POST['txtUnitPrice'])."";				//単価（円）
		$sql = $sql.",N_FLAW_PRICE			= 0".str_replace(",","",$_POST['txtFlawPrice'])."";				//不具合金額（円）
		$sql = $sql.",C_PLATING_CD			= '".$_POST['txtPlating_CD']."'";								//めっき先CD
		$sql = $sql.",V2_PLATING_NM			= '".$_POST['txtPlating_NM']."'";								//めっき先名
		$sql = $sql.",C_KBN					= '".$_POST['cmbKBN']."'";										//区分
		$sql = $sql.",V2_MATERIAL_SPEC		= '".$_POST['txtMaterialSpec']."'";								//材料仕様
		$sql = $sql.",C_FLAW_KBN1			= '".$_POST['cmbFlaw_KBN1']."'";								//不具合区分1
		$sql = $sql.",C_FLAW_KBN2			= '".$_POST['cmbFlaw_KBN2']."'";								//不具合区分2
		$sql = $sql.",C_FLAW_KBN3			= '".$_POST['cmbFlaw_KBN3']."'";								//不具合区分3
		$sql = $sql.",V2_FLAW_CONTENTS		= '".$_POST['txtFlawContents']."'";								//不具合内容
		$sql = $sql.",N_SPECIAL_YMD			= 0".str_replace("/","",$_POST['txtSpecial_YMD'])."";			//特別作業記録発行日
		$sql = $sql.",N_SPECIAL				= 0".$_POST['chkSpecial']."";									//特別作業記録チェック
		$sql = $sql.",N_PROCESS_PERIOD_YMD	= 0".str_replace("/","",$_POST['txtProcessPeriod_YMD'])."";		//処理期限
		$sql = $sql.",V2_STRETCH_REASON		= '".$_POST['txtStretchReason']."'";							//初期期限延伸理由
		// 2019/05/13 ADD START
		$sql = $sql.",N_SUBMIT_YMD1			= 0".str_replace("/","",$_POST['txtSubmit_YMD1'])."";			//特別作業払い出し日1
		$sql = $sql.",N_SUBMIT_YMD2			= 0".str_replace("/","",$_POST['txtSubmit_YMD2'])."";			//特別作業払い出し日2
		$sql = $sql.",N_SUBMIT_YMD3			= 0".str_replace("/","",$_POST['txtSubmit_YMD3'])."";			//特別作業払い出し日3
		$sql = $sql.",N_BACK_YMD1			= 0".str_replace("/","",$_POST['txtBack_YMD1'])."";				//特別作業戻り日1
		$sql = $sql.",N_BACK_YMD2			= 0".str_replace("/","",$_POST['txtBack_YMD2'])."";				//特別作業戻り日2
		$sql = $sql.",N_BACK_YMD3			= 0".str_replace("/","",$_POST['txtBack_YMD3'])."";				//特別作業戻り日3
		// 2019/05/13 ADD END
		$sql = $sql.",C_TANTO_CD			= '".$_POST['txtTanto_CD']."'";									//品証担当者CD
		$sql = $sql.",N_NON_ISSUE			= 0".$_POST['chkNonIssue']."";									//発行不要
		$sql = $sql.",C_INCIDENT_CD			= '".trim($_POST['txtIncident_CD'])."'";						//報告書発行先部署・協力会社CD
		$sql = $sql.",N_PROCESS_LIMIT_YMD	= 0".str_replace("/","",$_POST['txtProcessLimit_YMD'])."";		//報告書処理期限
		$sql = $sql.",N_RETURN_YMD			= 0".str_replace("/","",$_POST['txtReturn_YMD'])."";			//返却日
		$sql = $sql.",N_COMP_YMD			= 0".str_replace("/","",$_POST['txtComplete_YMD'])."";			//完結日
		$sql = $sql.",N_DECISION_YMD		= 0".str_replace("/","",$_POST['txtDecision_YMD'])."";			//処理判定日
		$sql = $sql.",N_APPROVAL_YMD		= 0".str_replace("/","",$_POST['txtApproval_YMD'])."";			//製造部長承認日
		$sql = $sql.",N_EXCLUDED			= 0".$_POST['chkExcluded']."";									//不良集計対象外
		$sql = $sql.",N_SELECTION			= '".$_POST['txtSelection']."'";								//選別工数（h）
		$sql = $sql.",C_DUE_PROCESS			= '".$_POST['cmbDueProcess_KBN']."'";							//起因工程
		$sql = $sql.",V2_COMENTS			= '".$_POST['txtComments']."'";									//その他コメント
		$sql = $sql.",C_PARTNER_CD			= '".trim($_POST['txtPartner_CD'])."'";							//起因部署・協力会社CD
		$sql = $sql.",C_PROCESS				= '".$_POST['cmbProcess_KBN']."'";								//処理
		$sql = $sql.",N_FAILURE_QTY			= 0".str_replace(",","",$_POST['txtFailure_QTY'])."";			//納入数量（個）
		$sql = $sql.",N_DISPOSAL_QTY		= 0".str_replace(",","",$_POST['txtDisposal_QTY'])."";			//廃棄数量（個）
		$sql = $sql.",N_RETURN_QTY			= 0".str_replace(",","",$_POST['txtReturn_QTY'])."";			//返却数量（個）
		$sql = $sql.",N_LOSS_QTY			= 0".str_replace(",","",$_POST['txtLoss_QTY'])."";				//調整ﾛｽ数量（個）
		$sql = $sql.",N_EXCLUD_QTY			= 0".str_replace(",","",$_POST['txtExclud_QTY'])."";			//対象外数量（個）
		$sql = $sql.",N_FAILURE_PRICE		= 0".str_replace(",","",$_POST['txtFailurePrice'])."";			//納入金額（円）
		$sql = $sql.",N_DISPOSAL_PRICE		= 0".str_replace(",","",$_POST['txtDisposalPrice'])."";			//廃棄金額（円）
		$sql = $sql.",N_RETURN_PRICE		= 0".str_replace(",","",$_POST['txtReturnPrice'])."";			//返却金額（円）
		$sql = $sql.",N_LOSS_PRICE			= 0".str_replace(",","",$_POST['txtLossPrice'])."";				//調整ﾛｽ金額（円）
		$sql = $sql.",N_EXCLUD_PRICE		= 0".str_replace(",","",$_POST['txtExcludPrice'])."";			//対象外金額（円）
		$sql = $sql.",N_UPD_YMD				= ".date("YmdHis")." ";											//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD		= '".$session[0]."' ";											//更新担当
		$sql = $sql.",V2_UPD_PG				= 'F_FLK0080' ";												//更新PG
		$sql = $sql.",N_UPDATE_COUNT		= N_UPDATE_COUNT + 1 ";											//更新回数
		$sql = $sql." WHERE C_REFERENCE_NO	= '".$Reference_No."'";											//伝票NO
		$sql = $sql."   AND N_REFERENCE_SEQ	= '".$Reference_SEQ."'";										//伝票SEQ

		return $sql;

	}

	//赤伝緑伝データ削除SQL作成
	//引数
	//$Reference_No		伝票NO
	//$Reference_SEQ	伝票SEQ
	//$session			登録担当者コード
	function fTrblDeleteSql($Reference_No,$Reference_SEQ,$session){
		$sql = "";
		$sql = $sql."DELETE FROM T_TR_TRBL ";
		$sql = $sql." WHERE C_REFERENCE_NO = '".$Reference_No."'";
		$sql = $sql."   AND N_REFERENCE_SEQ	= ".$Reference_SEQ."";

		return $sql;

	}
//2019/04/01 AD END T.FUJITA

//2019/08/01 AD START T.FUJITA
	//品質評価集計表データ登録
	//引数
	//$paPara		パラメータ
	public function fUpdTrblHyoka($paPara,$session){
		
		//登録日時
		$date = date("YmdHis");
		
		//材料保留表用ワークテーブル更新（SMART2）
		if($this->fUpdTrblHoryuWk($paPara,$session) == -1){
			return -1;
		}
		//生産状況一覧ワークテーブル更新（SMART2）
		if($this->fUpdTrblJokyoWkS2($paPara) == -1){
			return -1;
		}
		//品質評価集計表ワークテーブル更新(FL)
		if($this->fUpdDocuHyokaWk($paPara,$session,$date) == -1){
			return -1;
		}
		//月末月初資料集計履歴テーブル更新(FL)
		if($this->fUpdDocuRenkeiRireki($paPara,$session,$date) == -1){
			return -1;
		}
		
		return 0;

	}

	//品質評価集計表SQL作成
	//引数
	//$paPara		パラメータ
	function fTrblInsSqlHyoka($paPara,$session,$pDate,$pi){

		//対象日
		$iDateF = $paPara[2]."01";
		$iDateT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));

		//登録SQL作成
		$sql = "";
		if($pi==0){
			$sql = "INSERT INTO T_TR_DOCU_HYOUKA( ";
		}else{
			$sql = "INSERT INTO T_TR_DOCU_HYOUKA_RIREKI( ";
		}
		$sql = $sql." N_YM ";						//対象年月
		$sql = $sql.",C_TARGET_SECTION_KBN ";		//対象部門
		$sql = $sql.",N_ALL_SALE_PRICE ";			//総売上金額
		$sql = $sql.",N_PROCESS_QTY1 ";				//加工個数(生産1課)
		$sql = $sql.",N_PROCESS_QTY2 ";				//加工個数(生産2課)
		$sql = $sql.",N_PROCESS_QTY3 ";				//加工個数(生産3課)
		$sql = $sql.",N_PROCESS_QTY4 ";				//加工個数(生産4課)
		$sql = $sql.",N_PROCESS_QTY5 ";				//加工個数(生産5課)
		$sql = $sql.",N_BAD_COST ";					//総加工不良工数（H）
		$sql = $sql.",N_IND_PRICE1 ";				//生産金額(生産1課)
		$sql = $sql.",N_IND_PRICE2 ";				//生産金額(生産2課)
		$sql = $sql.",N_IND_PRICE3 ";				//生産金額(生産3課)
		$sql = $sql.",N_IND_PRICE4 ";				//生産金額(生産4課)
		$sql = $sql.",N_IND_PRICE5 ";				//生産金額(生産5課)
		$sql = $sql.",N_IND_PRICE6 ";				//生産金額(プレス協力会社)
		$sql = $sql.",N_IND_PRICE7 ";				//生産金額(めっき協力会社)
		$sql = $sql.",N_ALL_STAND_PROCESS_QTY ";	//総材料標準加工個数
		$sql = $sql.",N_ALL_STAND_PROCESS_PRICE ";	//総材料標準加工金額
		$sql = $sql.",N_INS_YMD ";					//登録日時
		$sql = $sql.",C_INS_SHAIN_CD ";				//登録担当者CD
		$sql = $sql.",V2_INS_PG ";					//登録PG
		$sql = $sql.",N_UPD_YMD ";					//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD ";				//更新担当者CD
		$sql = $sql.",V2_UPD_PG ";					//更新PG
		$sql = $sql.") ";
		//参照
		$sql = $sql." SELECT ".$paPara[2]."				AS N_YM ";
		$sql = $sql."       ,'".$paPara[1]."'			AS C_TARGET_SECTION_KBN ";
		$sql = $sql."       ,AMT01.PRICE + AMT02.PRICE  AS N_ALL_SALE_PRICE ";
		$sql = $sql."       ,K01Q.QTY					AS N_PROCESS_QTY1 ";
		$sql = $sql."       ,K02Q.QTY					AS N_PROCESS_QTY2 ";
		$sql = $sql."       ,K03Q.QTY					AS N_PROCESS_QTY3 ";
		$sql = $sql."       ,K04Q.QTY					AS N_PROCESS_QTY4 ";
		$sql = $sql."       ,K05Q.QTY					AS N_PROCESS_QTY5 ";
		$sql = $sql."       ,H05.TIME					AS N_BAD_COST ";
		$sql = $sql."       ,K01P.PRICE					AS N_IND_PRICE1 ";
		$sql = $sql."       ,K02P.PRICE					AS N_IND_PRICE2 ";
		$sql = $sql."       ,K03P.PRICE					AS N_IND_PRICE3 ";
		$sql = $sql."       ,K04P.PRICE					AS N_IND_PRICE4 ";
		$sql = $sql."       ,K05P.PRICE					AS N_IND_PRICE5 ";
		$sql = $sql."       ,K06P.PRICE					AS N_IND_PRICE6 ";
		$sql = $sql."       ,K07P.PRICE					AS N_IND_PRICE7 ";
		$sql = $sql."       ,W90.QTY					AS N_ALL_STAND_PROCESS_QTY ";
		$sql = $sql."       ,W90.PRICE					AS N_ALL_STAND_PROCESS_PRICE ";
		$sql = $sql."       ,".$pDate."					AS N_INS_YMD ";
		$sql = $sql."       ,'".$session[0]."'			AS C_INS_SHAIN_CD ";
		$sql = $sql."       ,'F_FLK0091'				AS V2_INS_PG ";
		$sql = $sql."       ,".$pDate."					AS N_UPD_YMD ";
		$sql = $sql."       ,'".$session[0]."'			AS C_UPD_SHAIN_CD ";
		$sql = $sql."       ,'F_FLK0091'				AS V2_UPD_PG ";
		$sql = $sql."   FROM ";
		//総売上金額（PRONES 納入金額）
		$sql = $sql." (SELECT SUM(NVL(SUM(T_SM_SALES_TR.I_AMT),0)) AS PRICE ";
		$sql = $sql."    FROM T_SM_SALES_TR@PRONES.US.ORACLE.COM@PRONES ";
		$sql = $sql."        ,T_PROD_MS@PRONES.US.ORACLE.COM@PRONES ";
		$sql = $sql."   WHERE	T_SM_SALES_TR.I_FAC_CD = '".$paPara[0]."' ";
		$sql = $sql." 	AND	T_SM_SALES_TR.I_SALES_SECTION = '10' ";
		$sql = $sql." 	AND T_SM_SALES_TR.I_SALES_DATE BETWEEN '".$iDateF."' AND '".$iDateT."' ";
		$sql = $sql." 	AND T_SM_SALES_TR.I_SALES_CLS = '00' ";
		$sql = $sql." 	AND T_SM_SALES_TR.I_FAC_CD = T_PROD_MS.I_FAC_CD(+) ";
		$sql = $sql." 	AND T_SM_SALES_TR.I_PROD_CD = T_PROD_MS.I_PROD_CD(+) ";
		$sql = $sql." GROUP BY T_SM_SALES_TR.I_SALES_SECTION ";
		$sql = $sql." ,T_SM_SALES_TR.I_PROD_CLS) AMT01 ";
		//総売上金額（S2 MD端子出荷金額）
		$sql = $sql." ,(SELECT SUM(MD.PRICE) AS PRICE ";
		$sql = $sql." FROM ";
		$sql = $sql."  (SELECT ROUND(JT.単価 * JT.数量) AS PRICE ";
		$sql = $sql."     FROM J_端子受入ファイル@NF.US.ORACLE.COM@NF JT ";
		$sql = $sql."         ,M_製品@NF.US.ORACLE.COM@NF MS ";
		$sql = $sql."         ,M_単価@NF.US.ORACLE.COM@NF MT ";
		$sql = $sql."    WHERE JT.製品_CD = MS.製品_CD(+) ";
		$sql = $sql."      AND JT.製品_CD = MT.品目_CD(+) ";
		$sql = $sql."      AND JT.計上日_YMD BETWEEN '".$iDateF."' AND '".$iDateT."' ";
		$sql = $sql."      AND JT.計上日_YMD <> 0 ";
		$sql = $sql."      AND MT.単価区分_KU = '00' ";
		$sql = $sql."      AND MT.品目_KU = '1' ";
		$sql = $sql."      AND MT.削除日_YMD = 0) MD) AMT02 ";
		//総加工不良工数（H） （プレス稼働時間表（マシン工数） + 個人別プレス生産実績の無人稼働時間）
		$sql = $sql." ,(SELECT NVL(K01.TIME + K02.TIME,0) AS TIME "; 
		$sql = $sql."     FROM (SELECT SUM(JP.マシン工数) AS TIME ";
		$sql = $sql."             FROM J_プレス工数@NF.US.ORACLE.COM@NF  JP ";
		$sql = $sql."                 ,M_製品@NF.US.ORACLE.COM@NF  MS ";
		$sql = $sql."            WHERE JP.製品_CD = MS.製品_CD ";
		$sql = $sql."              AND MS.管理部署_KU = '1' ";
		$sql = $sql."              AND JP.生産日_YMD BETWEEN '".$iDateF."' AND '".$iDateT."') K01 ";
		$sql = $sql."         ,(SELECT SUM(J_プレス工数.その他作業) AS TIME ";
		$sql = $sql."             FROM M_取引先@NF.US.ORACLE.COM@NF ";
		$sql = $sql."                 ,J_プレス工数@NF.US.ORACLE.COM@NF ";
		$sql = $sql."                 ,V_区分_107_管理部署@NF.US.ORACLE.COM@NF ";
		$sql = $sql."                 ,(SELECT 製品_CD ";
		$sql = $sql."                         ,プレス標準取数 AS プレス標準取数 ";
		$sql = $sql."                     FROM M_製品@NF.US.ORACLE.COM@NF ";
		$sql = $sql."                    WHERE 削除日_YMD  = 0 ";
		$sql = $sql."                  ) W_製品 ";
		$sql = $sql."                ,(SELECT 品目_CD ";
		$sql = $sql."                        ,単価 AS 単価 ";
		$sql = $sql."                    FROM M_単価@NF.US.ORACLE.COM@NF ";
		$sql = $sql."                   WHERE 単価区分_KU = '00' ";
		$sql = $sql."                     AND 品目_KU     = '1' ";
		$sql = $sql."                     AND 削除日_YMD  = 0 ";
		$sql = $sql."                 ) W_単価 ";
		$sql = $sql."                ,M_担当者@NF.US.ORACLE.COM@NF ";
		$sql = $sql."                ,J_プレス工数_追加@NF.US.ORACLE.COM@NF ";
		$sql = $sql."            WHERE M_取引先.部門所属_CD = M_担当者.部門所属_CD ";
		$sql = $sql."              AND M_取引先.取引先_CD IN('K01001','K01002','K01003','K01004','K01005','K01006','K01007','K01008') ";
		$sql = $sql."              AND J_プレス工数.生産日_YMD BETWEEN '".$iDateF."' AND '".$iDateT."' ";
		$sql = $sql."              AND J_プレス工数.在庫場所 LIKE 'P%' ";
		$sql = $sql."              AND V_区分_107_管理部署.区分明細_CD = '1' ";
		$sql = $sql."              AND J_プレス工数.製品_CD = W_製品.製品_CD (+) ";
		$sql = $sql."              AND J_プレス工数.製品_CD = W_単価.品目_CD (+) ";
		$sql = $sql."              AND J_プレス工数.作業者_CD = M_担当者.担当者_CD (+) ";
		$sql = $sql."              AND J_プレス工数.計画_NO = J_プレス工数_追加.計画_NO(+) ";
		$sql = $sql."              AND J_プレス工数.計画_SEQ = J_プレス工数_追加.計画_SEQ(+) ";
		$sql = $sql."              AND J_プレス工数.ロット管理_NO = J_プレス工数_追加.ロット管理_NO(+) ";
		$sql = $sql."              AND J_プレス工数.元ロット管理_NO = J_プレス工数_追加.元ロット管理_NO(+) ";
		$sql = $sql."              AND J_プレス工数.作業者_CD = J_プレス工数_追加.作業者_CD(+)) K02) H05 ";
		//製造数量（生産一課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造数量),0) AS QTY ";
		$sql = $sql."     FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."     AND 生産グループ IN ('K01001','K01002')) K01Q ";
		//製造数量（生産二課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造数量),0) AS QTY ";
		$sql = $sql."     FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."      AND 生産グループ IN ('K01003','K01004')) K02Q ";
		//製造数量（生産三課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造数量),0) AS QTY ";
		$sql = $sql."     FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."      AND 生産グループ IN ('K01005')) K03Q ";
		//製造数量（生産四課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造数量),0) AS QTY ";
		$sql = $sql."     FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."      AND 生産グループ IN ('K01006','K01007')) K04Q ";
		//製造数量（生産五課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造数量),0) AS QTY ";
		$sql = $sql."    FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."      AND 生産グループ IN ('K01008')) K05Q ";
		//製造金額（生産一課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造金額),0) AS PRICE ";
		$sql = $sql."     FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."      AND 生産グループ IN ('K01001','K01002')) K01P ";
		//製造金額（生産二課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造金額),0) AS PRICE ";
		$sql = $sql."     FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."      AND 生産グループ IN ('K01003','K01004')) K02P ";
		//製造金額（生産三課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造金額),0) AS PRICE ";
		$sql = $sql."     FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."      AND 生産グループ IN ('K01005')) K03P ";
		//製造金額（生産四課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造金額),0) AS PRICE ";
		$sql = $sql."     FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."      AND 生産グループ IN ('K01006','K01007')) K04P ";
		//製造金額（生産五課）
		$sql = $sql." ,(SELECT NVL(SUM(当月実績製造金額),0) AS PRICE ";
		$sql = $sql."     FROM W_PTS0150@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE CMP_NAME = '".$paPara[3]."' ";
		$sql = $sql."      AND 生産グループ IN ('K01008')) K05P ";
		//製造金額（プレス協力会社）
		$sql = $sql." ,(SELECT NVL(SUM(受入金額),0) AS PRICE ";
		$sql = $sql."     FROM J_外注受入検収@NF.US.ORACLE.COM@NF ";
		$sql = $sql."    WHERE 管理部署 = '1' ";
		$sql = $sql."      AND 製品工程_KU = '010' ";
		$sql = $sql."      AND 受入日_YMD BETWEEN '".$iDateF."' AND '".$iDateT."' ";
		$sql = $sql."      AND 削除日_YMD = 0) K06P ";
		//製造金額（めっき協力会社）
		$sql = $sql.",(SELECT SUM(PRICE) AS PRICE ";
		$sql = $sql."    FROM ";
		$sql = $sql." (SELECT ROUND(JG.受入数量*MT.単価) AS PRICE ";
		$sql = $sql."    FROM NF.J_外注受入検収@NF.US.ORACLE.COM@NF JG ";
		$sql = $sql."        ,NF.J_ロット情報@NF.US.ORACLE.COM@NF JL ";
		$sql = $sql."        ,NF.M_単価@NF.US.ORACLE.COM@NF MT ";
		$sql = $sql."   WHERE JG.管理部署 = '1' ";
		$sql = $sql."     AND JG.製品工程_KU = '050' ";
		$sql = $sql."     AND JG.受入日_YMD BETWEEN '".$iDateF."' AND '".$iDateT."' ";
		$sql = $sql."     AND JG.削除日_YMD = 0 ";
		$sql = $sql."     AND JG.計画_NO = JL.計画_NO ";
		$sql = $sql."     AND JG.計画_SEQ = JL.計画_SEQ ";
		$sql = $sql."     AND JG.ロット管理_NO = JL.ロット管理_NO";
		$sql = $sql."     AND JG.製品_CD = MT.品目_CD";
		$sql = $sql."     AND MT.単価区分_KU=00";
		$sql = $sql."     AND MT.品目_KU=1)) K07P";
		//総材料標準加工個数&総材料標準加工金額
		$sql = $sql." ,(SELECT NVL(SUM(DECODE(W.素材重量,0,0,ROUND(W.Ｊ実際重量 / W.素材重量 / DECODE(TRIM(W.製品_CD),'66901316',1000,1) * 1000,0))),0) AS QTY ";
		$sql = $sql."         ,NVL(SUM(DECODE(W.素材重量,0,0,ROUND((W.Ｊ実際重量 / W.素材重量 / DECODE(TRIM(W.製品_CD),'66901316',1000,1) * 1000) * J.製品単価,0))),0) AS PRICE ";
		$sql = $sql."     FROM W_PTS0090@NF.US.ORACLE.COM@NF W ";
		$sql = $sql."         ,ST_PROD_MS_ADD_PTS@PRONES.US.ORACLE.COM@PRONES S ";
		$sql = $sql."         ,J_製品単価情報@NF.US.ORACLE.COM@NF J ";
		$sql = $sql."    WHERE W.CMP_NAME = 'WEBAPPSV' ";
		$sql = $sql."      AND W.製品_CD = S.I_PROD_CD ";
		$sql = $sql."      AND S.I_FAC_CD = '".$paPara[0]."' ";
		$sql = $sql."      AND W.製品_CD = J.製品_CD) W90 ";
		
		return $sql;
		
	}

	//コネクタ材料保留表用ワークテーブル更新
	//引数	$paPara	パラメータ
	public function fUpdTrblHoryuWk($paPara,$session){

		require_once("module_common.php");
		require_once("module_sel.php");
		$module_cmn = new module_common;
		$module_sel = new module_sel;
		
		if($paPara[1] == "F"){
			$iBKbn = 1;
		}
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gNUserID, $this->gNPass, $this->gNDB);
		if (!$conn) {
			$e = oci_error();	//oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			return -1;
		}

		//データ検索
		$sql = "";
		$sql = 'BEGIN P_PTS0090_1(:P_T_ID,:P_BU_KU,:P_YM); END;';
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		//パラメータを割り当て
		oci_bind_by_name($stmt,":P_T_ID",$paPara[3]);
		oci_bind_by_name($stmt,":P_BU_KU",$iBKbn);
		oci_bind_by_name($stmt,":P_YM",$paPara[2]);

		oci_execute($stmt,OCI_DEFAULT);
		oci_free_statement($stmt);
		

		try{
			//Oracleへの接続の確立(トランザクション開始)
			$conn = $this->fTransactionStart();
			//WT削除
			$sql = "";
			$sql = $sql."DELETE FROM T_TR_TRBL_HORYU ";
			$sql = $sql." WHERE C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
			$sql = $sql."   AND N_YM = '".$paPara[2]."' ";

			//SQLの実行
			$stmt = oci_parse($conn,$sql);
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return -1;
			}
			oci_free_statement($stmt);
			
			//WT取得登録
			$sql = "INSERT INTO T_TR_TRBL_HORYU( ";
			$sql = $sql." N_YM ";						//対象年月
			$sql = $sql.",C_TARGET_SECTION_KBN ";		//対象部門
			$sql = $sql.",C_PROD_CD ";					//製品CD
			$sql = $sql.",V2_PROD_NM ";					//製品名
			$sql = $sql.",V2_DRW_NO ";					//仕様番号
			$sql = $sql.",C_MATERIAL_CD ";				//材料CD
			$sql = $sql.",N_MAT_WGT ";					//素材重量
			$sql = $sql.",N_MANUFA_QTY ";				//製造数量
			$sql = $sql.",N_STAND_WGT ";				//S標準重量
			$sql = $sql.",N_STAND_PRICE ";				//標準金額
			$sql = $sql.",N_ACTUAL_WGT ";				//J実際重量
			$sql = $sql.",N_SJ ";						//SJ
			$sql = $sql.",N_MAT_UNIT ";					//材料単価
			$sql = $sql.",N_DIF_PRICE ";				//差異金額
			$sql = $sql.",N_YIELD ";					//保留
			$sql = $sql.",N_MANU_UNIT ";				//J製品単価
			$sql = $sql.",N_MANU_PRICE ";				//製造金額
			$sql = $sql.",N_STAND_PROCESS_QTY ";		//総材料標準加工個数
			$sql = $sql.",N_STAND_PROCESS_PRICE ";		//総材料標準加工金額
			$sql = $sql.",N_ACTUAL_HOLD ";				//実際歩留
			$sql = $sql.",N_INS_YMD ";					//登録日時
			$sql = $sql.",C_INS_SHAIN_CD ";				//登録担当
			$sql = $sql.",V2_INS_PG ";					//登録PG
			$sql = $sql.") ";
			$sql = $sql."SELECT ".$paPara[2]." AS N_YM ";
			$sql = $sql."      ,'".$paPara[1]."' AS C_TARGET_SECTION_KBN ";
			$sql = $sql."      ,W.製品_CD AS C_PROD_CD ";
			$sql = $sql."      ,W.製品名 AS V2_PROD_NM ";
			$sql = $sql."      ,W.仕様番号 AS V2_DRW_NO ";
			$sql = $sql."      ,W.材料_CD AS C_MATERIAL_CD ";
			$sql = $sql."      ,W.素材重量 AS N_MAT_WGT";
			$sql = $sql."      ,W.製造数量 AS N_MANUFA_QTY ";
			$sql = $sql."      ,W.Ｓ標準重量 AS N_STAND_WGT ";
			$sql = $sql."      ,W.標準金額 AS N_STAND_PRICE ";
			$sql = $sql."      ,W.Ｊ実際重量 / decode(trim(W.製品_CD),'66901316',1000,1) AS N_ACTUAL_WGT ";
			$sql = $sql."      ,W.Ｓ標準重量 - W.Ｊ実際重量 / decode(trim(W.製品_CD),'66901316',1000,1) AS N_SJ ";
			$sql = $sql."      ,W.材料単価 AS N_MAT_UNIT ";
			$sql = $sql."      ,decode(trim(W.製品_CD),'66901316',(W.Ｓ標準重量 -W.Ｊ実際重量/1000)*W.材料単価 ,W.差異金額) AS N_DIF_PRICE ";
			$sql = $sql."      ,S.I_ZAI_YIELD AS N_YIELD ";
			$sql = $sql."      ,J.製品単価 AS N_MANU_UNIT ";
			$sql = $sql."      ,round(W.製造数量 * J.製品単価,0) AS N_MANU_PRICE ";
			$sql = $sql."      ,DECODE(W.素材重量,0,0,round(W.Ｊ実際重量 / W.素材重量 / decode(trim(W.製品_CD),'66901316',1000,1) * 1000,0)) AS N_STAND_PROCESS_QTY ";
			$sql = $sql."      ,DECODE(W.素材重量,0,0,Round((W.Ｊ実際重量 / W.素材重量 / decode(trim(W.製品_CD),'66901316',1000,1) * 1000) * J.製品単価,0)) AS N_STAND_PROCESS_PRICE ";
			$sql = $sql."      ,DECODE(W.製造数量,0,0,round(round(W.Ｊ実際重量 / decode(trim(W.製品_CD),'66901316',1000,1) * S.I_ZAI_YIELD / DECODE(S.I_ZAI_MTRL_WEIGHT,0,1,S.I_ZAI_MTRL_WEIGHT) * 1000,0) / W.製造数量,5)) AS N_ACTUAL_HOLD ";
			$sql = $sql."      ,".date("YmdHis")." AS N_INS_YMD ";
			$sql = $sql."      ,'".$session[0]."' AS C_INS_SHAIN_CD ";
			$sql = $sql."      ,'F_FLK0091' AS V2_INS_PG ";
			$sql = $sql."  FROM W_PTS0090@NF.US.ORACLE.COM@NF W ";
			$sql = $sql."      ,ST_PROD_MS_ADD_PTS@PRONES.US.ORACLE.COM@PRONES S ";
			$sql = $sql."      ,J_製品単価情報@NF.US.ORACLE.COM@NF J ";
			$sql = $sql." WHERE W.CMP_NAME = '".$paPara[3]."' ";
			$sql = $sql."   AND W.製品_CD = S.I_PROD_CD";
			$sql = $sql."   AND S.I_FAC_CD = '".$paPara[0]."' ";
			$sql = $sql."   AND W.製品_CD = J.製品_CD ";
			//SQLをSJISに変換(DB)
			$sql = $module_cmn->fChangSJIS_SQL($sql);
			//SQLの実行
			$stmt = oci_parse($conn,$sql);
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return -1;
			}else{
				//トランザクション処理(commit)とOracle切断
				$this->fTransactionEnd($conn,true);
			}
			
			oci_free_statement($stmt);
			oci_close($conn);
			
			return 0;
		
		}catch(Exception $e){
			//トランザクション処理(rollback)とOracle切断
			$this->fTransactionEnd($conn,false);
			return -1;
		}

		return 0;

	}
	
	//生産状況一覧ワークテーブル更新
	//引数	$paPara	パラメータ
	public function fUpdTrblJokyoWkS2($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		if($paPara[1] == "F"){
			$iBKbn = 1;
		}
		$sYmd = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gNUserID, $this->gNPass, $this->gNDB);
		if (!$conn) {
			$e = oci_error();	//oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			return -1;
		}

		//データ検索
		$sql = "";
		$sql = 'BEGIN P_PTS0150(:P_T_ID,:P_BU_KU,:P_YM); END;';
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		//パラメータを割り当て
		oci_bind_by_name($stmt,":P_T_ID",$paPara[3]);
		oci_bind_by_name($stmt,":P_BU_KU",$iBKbn);
		oci_bind_by_name($stmt,":P_YM",$sYmd);

		oci_execute($stmt,OCI_DEFAULT);
		
		oci_free_statement($stmt);
		oci_close($conn);

		return 0;
	}

	
	//月末月初資料ワークテーブル更新
	//引数	$paPara	パラメータ
	//		$mode	更新種類
	public function fUpdDocuHyokaWk($paPara,$session,$pDate){

		require_once("module_common.php");
		$module_cmn = new module_common;

		try{
			//Oracleへの接続の確立(トランザクション開始)
			$conn = $this->fTransactionStart();
			
			//WT削除
			$sql = "";
			$sql = $sql."DELETE FROM T_TR_DOCU_HYOUKA ";
			$sql = $sql." WHERE C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
			$sql = $sql."   AND N_YM = ".$paPara[2]." ";
			
			//SQLの実行
			$stmt = oci_parse($conn,$sql);
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return -1;
			}
			oci_free_statement($stmt);
			
			for($i=0;$i<2; ++$i){
				//登録SQL取得
				$sql = $this->fTrblInsSqlHyoka($paPara,$session,$pDate,$i);
				
				//文字列変換
				$sql = $module_cmn->fChangSJIS_SQL($sql);
				
				//SQLの実行
				$stmt = oci_parse($conn, $sql);
				$r = oci_execute($stmt,OCI_DEFAULT);

				if (!$r) {
					$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
					echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
					echo "<pre>";
					echo htmlentities("ERROR_SQL:".$e['sqltext']);
					printf("\n%".($e['offset']+1)."s", "^");
					echo "</pre>";
					//トランザクション処理(rollback)とOracle切断
					$this->fTransactionEnd($conn,false);
					return -1;
				}else{
					//トランザクション処理(commit)とOracle切断
					$this->fTransactionEnd($conn,true);
				}
			}


			return 0;

		}catch(Exception $e){
			//トランザクション処理(rollback)とOracle切断
			$this->fTransactionEnd($conn,false);
			return -1;
		}
	}
	
	//月末月初資料集計履歴ワークテーブル更新
	//引数	$paPara	パラメータ
	public function fUpdDocuRenkeiRireki($paPara,$session,$pDate){

		require_once("module_common.php");
		$module_cmn = new module_common;

		try{
			//Oracleへの接続の確立(トランザクション開始)
			$conn = $this->fTransactionStart();
			
			//履歴登録
			$sql = "";
			$sql = $sql."INSERT INTO T_TR_DOCU_RIREKI( ";
			$sql = $sql." N_YM ";						//対象年月
			$sql = $sql.",C_TARGET_SECTION_KBN ";		//対象部門
			$sql = $sql.",V2_DOCU_CD ";					//資料CD
			$sql = $sql.",N_INS_YMD ";					//登録日時
			$sql = $sql.",C_INS_SHAIN_CD ";				//登録担当
			$sql = $sql.",V2_INS_PG ";					//登録PG
			$sql = $sql.",N_UPD_YMD ";					//更新日時
			$sql = $sql.",C_UPD_SHAIN_CD ";				//更新担当
			$sql = $sql.",V2_UPD_PG ";					//更新PG
			$sql = $sql.",N_DEL_FLG ";					//削除FLG
			$sql = $sql.") ";
			$sql = $sql."VALUES( ";
			$sql = $sql." ".$paPara[2];
			$sql = $sql.",'".$paPara[1]."' ";
			$sql = $sql.",'0' ";
			$sql = $sql.",".$pDate;
			$sql = $sql.",'".$session[0]."' ";
			$sql = $sql.",'F_FLK0091' ";
			$sql = $sql.",".$pDate;
			$sql = $sql.",'".$session[0]."' ";
			$sql = $sql.",'F_FLK0091' ";
			$sql = $sql.",0 ";
			$sql = $sql.") ";
			
			//SQLをSJISに変換(DB)
			$sql = $module_cmn->fChangSJIS_SQL($sql);
			//SQLの実行
			$stmt = oci_parse($conn,$sql);
			$r = oci_execute($stmt,OCI_DEFAULT);

			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return -1;
			}else{
				//トランザクション処理(commit)とOracle切断
				$this->fTransactionEnd($conn,true);
			}
				
			
			oci_free_statement($stmt);
			oci_close($conn);
			
			return 0;
			
		}catch(Exception $e){
			//トランザクション処理(rollback)とOracle切断
			$this->fTransactionEnd($conn,false);
			return -1;
		}
		
		return 0;
	}
	
	//品質評価集計表指定履歴データ適用処理
	//引数	$paPara	パラメータ
	public function fUpdHyokaFromRireki($paPara,$piYm,$piDate){

		require_once("module_common.php");
		$module_cmn = new module_common;

		try{
			//Oracleへの接続の確立(トランザクション開始)
			$conn = $this->fTransactionStart();
			
			//対象の集計元データ削除
			$sql = "";
			$sql = $sql."DELETE FROM T_TR_DOCU_HYOUKA ";
			$sql = $sql." WHERE C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
			$sql = $sql."   AND N_YM = ".$piYm." ";
			
			//SQLの実行
			$stmt = oci_parse($conn,$sql);
			$r = oci_execute($stmt,OCI_DEFAULT);
			
			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return -1;
			}
			oci_free_statement($stmt);
			
			//履歴テーブル反映
			$sql = "";
			$sql = $sql."INSERT INTO T_TR_DOCU_HYOUKA ";
			$sql = $sql."SELECT * ";
			$sql = $sql."  FROM T_TR_DOCU_HYOUKA_RIREKI ";
			$sql = $sql." WHERE N_YM = ".$piYm." ";
			$sql = $sql."   AND C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
			$sql = $sql."   AND N_INS_YMD = ".$piDate." ";
			
			//SQLの実行
			$stmt = oci_parse($conn, $sql);
			$r = oci_execute($stmt,OCI_DEFAULT);
			if (!$r) {
				$e = oci_error($stmt); // oci_execute のエラーの場合、ステートメントハンドルを渡す
				echo htmlentities($module_cmn->fChangSJIS("ERROR_TABLE:".$e['message']));
				echo "<pre>";
				echo htmlentities("ERROR_SQL:".$e['sqltext']);
				printf("\n%".($e['offset']+1)."s", "^");
				echo "</pre>";
				//トランザクション処理(rollback)とOracle切断
				$this->fTransactionEnd($conn,false);
				return -1;
			}else{
				//トランザクション処理(commit)とOracle切断
				$this->fTransactionEnd($conn,true);
			}
			
			return 0;
			
		}catch(Exception $e){
			//トランザクション処理(rollback)とOracle切断
			$this->fTransactionEnd($conn,false);
			return -1;
		}
	}
	
//2019/08/01 AD END T.FUJITA

}

?>