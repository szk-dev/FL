<?php
//****************************************************************************
//プログラム名：検索用モジュール郡
//プログラムID：module_sel
//作成者　　　：㈱鈴木　久米
//作成日　　　：2012/05/30
//履歴　　　　：
//
//
//****************************************************************************
class module_sel{


	public $Meisho;
	public $Setubi;
	public $Chosei;
	//データベース接続情報
	public $gUserID;
	public $gPass;
	public $gDB;

	//SMART2接続情報
	public $gNUserID;
	public $gNPass;
	public $gNDB;


	//PRONES接続情報
	public $gPUserID;
	public $gPPass;
	public $gPDB;

	//Dugong接続情報(SQLServer)
	public $gDServer;
	public $gDUserid;
	public $gDPasswd;
	public $gDDbName;
	//TimePro接続情報(SQLServer)
	public $gTServer;
	public $gTUserid;
	public $gTPasswd;
	public $gTDbName;


	//eValueNS接続情報(SQLServer)
	public $gNSServer;
	public $gNSUserid;
	public $gNSPasswd;
	public $gNSDbName;


	//コンストラクタ
	function __construct(){

		//iniファイルの読み込み
		// セクションを意識してパースします。
		$aIni = parse_ini_file("ini/FL.ini", true);

		//iniから取得
		if($aIni){
			$aPara = array();
			//品質管理システムDB情報取得
			$aPara[0] = $aIni['FL_INI']['DB'];
			$aPara[1] = $aIni['FL_INI']['USERID'];
			$aPara[2] = $aIni['FL_INI']['PASSWORD'];
			//ワークフローDB情報取得
			$aPara[3] = $aIni['WORKFLOW']['SERVER'];
			$aPara[4] = $aIni['WORKFLOW']['USERID'];
			$aPara[5] = $aIni['WORKFLOW']['PASSWORD'];
			$aPara[6] = $aIni['WORKFLOW']['DBNAME'];
			//TimePro(就業)DB情報取得
			$aPara[7] = $aIni['TIMEPRO']['SERVER'];
			$aPara[8] = $aIni['TIMEPRO']['USERID'];
			$aPara[9] = $aIni['TIMEPRO']['PASSWORD'];
			$aPara[10] = $aIni['TIMEPRO']['DBNAME'];
			//PRONES(販売)DB情報取得
			$aPara[11] = $aIni['PRONES']['DB'];
			$aPara[12] = $aIni['PRONES']['USERID'];
			$aPara[13] = $aIni['PRONES']['PASSWORD'];

			//SMART2(生産管理)DB情報取得
			$aPara[14] = $aIni['NF']['DB'];
			$aPara[15] = $aIni['NF']['USERID'];
			$aPara[16] = $aIni['NF']['PASSWORD'];

			//eValueNS(グループウエア)DB情報取得
			$aPara[17] = $aIni['NS']['SERVER'];
			$aPara[18] = $aIni['NS']['USERID'];
			$aPara[19] = $aIni['NS']['PASSWORD'];
			$aPara[20] = $aIni['NS']['DBNAME'];


		}

		//DB接続情報
		$this->gUserID = $aPara[1];
		$this->gPass   = $aPara[2];
		$this->gDB     = $aPara[0];


		//ワークフローSQLServerの設定値
		$this->gDServer = $aPara[3];
		$this->gDUserid = $aPara[4];
		$this->gDPasswd = $aPara[5];
		$this->gDDbName = $aPara[6];

		//TimeProSQLServerの設定値
		$this->gTServer = $aPara[7];
		$this->gTUserid = $aPara[8];
		$this->gTPasswd = $aPara[9];
		$this->gTDbName = $aPara[10];

		//PRONESのDB設定値
		$this->gPDB      = $aPara[11];
		$this->gPUserid  = $aPara[12];
		$this->gDPPasswd = $aPara[13];

		//SMART2のDB設定値
		$this->gNDB      = $aPara[14];
		$this->gNUserID  = $aPara[15];
		$this->gNPass = $aPara[16];

		//eValueNSSQLServerの設定値
		$this->gNSServer = $aPara[17];
		$this->gNSUserid = $aPara[18];
		$this->gNSPasswd = $aPara[19];
		$this->gNSDbName = $aPara[20];



	}


	//概要：ログイン処理
	//処理内容：入力されたID、パスでログイン処理を行う
	//引数
	//		$id(ログインID)
	//		$pass(ログインパスワード)
	//		$page(遷移先ページ)
	//		$rrcno(整理NO)
	//		$rrcseq(伝票SEQ)		// 2019/05/13 ADD T.FUJITA
	public function flogin($id,$pass,$page,$rrcno,$rrcseq = 0){

		require_once("module_common.php");
		$module_cmn = new module_common;

		/* セッション変数に登録 */
		session_start();
		//		session_register("syain_cd");
		//		session_register("syain_nm");
		//		session_register("bumon_cd");
		//		session_register("bumon_nm");
		//		session_register("kaisha_cd");

		//IDとパスワードが未入力の場合はエラー
		//if($id == "" or $pass == ""){
		//	//ADに存在しなかったらエラー画面へ遷移
		//	session_destroy();
		//	header("Location: index.php?err=1&adng=1");
		//	break;
		//}

		//ログインフラグ(true:OK,false:NG)
		$bLoginFlag = true;
		
		//共通ログインパスワード以外はチェック
		if($pass <> "fladmin"){
			//LDAP認証(Windowsのログインパスワードでチェックを行う)
			//LDAP接続
			$objConnection = ldap_connect('ldap://172.16.15.50', 389);
			//LDAPオプション設定
			ldap_set_option($objConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
			//接続が有効ならば入力されたユーザとパスワードで認証を行う
			if ($objConnection) {

				if (@ldap_bind($objConnection, $id."@suzulan.local", $pass)) {

				} else {
					//ADに存在しなかったらエラー画面へ遷移
					session_destroy();
					//header("Location: index.php?err=1&adng=1");
					
					$bLoginFlag = false;

				}
				//LDAP ディレクトリへのバインドを解除する
				ldap_unbind($objConnection);
			}else{
				//header("Location: index.php?err=1&adng=1");
				$bLoginFlag = false;
				
			}
		}

		//ODBCへの接続の確立
		$conn_ms=odbc_connect("Driver={SQL Server};Server=".$this->gTServer.";Database=".$this->gTDbName
						   ,$this->gTUserid
						   ,$this->gTPasswd);
		
		if (!$conn_ms) {
			$e = odbc_errormsg();
			session_destroy();
			die("データベースに接続できません");
		}
		
		//最初に就業データを検索して部門情報を取得する
		//検索SQL作成
		$sql = "";
		$sql = $sql."SELECT ";
		$sql = $sql."   T1.EmpCode AS SHAIN_CD ";
		$sql = $sql."  ,T1.WordName AS SHAIN_NM ";
		$sql = $sql."  ,T2.DepCodeAll AS SOSHIKI_CD ";
		$sql = $sql."  ,T2.WordNameAll AS SOSHIKI_NM ";
		$sql = $sql."  ,T1.CompanyCode AS KAISHA_CD ";
		$sql = $sql."  FROM dbo.DGINDIVI T1,dbo.DGDEPMNT T2  ";
		$sql = $sql." WHERE T1.DepCodeAll = T2.DepCodeAll ";
		$sql = $sql."AND T1.EmpCode = '".$id."'  ";

		//SQL実行
		$stmt = NULL;
		$stmt = odbc_prepare($conn_ms, $sql);
		odbc_execute($stmt);
		
		if (!$stmt){
			$e = odbc_error();
			return -1;
		}

		$n = 0;

		while(odbc_fetch_row($stmt)){
			$syainnm = $module_cmn->fChangUTF8(odbc_result($stmt,"SHAIN_NM"));
			$bumoncd = $module_cmn->fChangUTF8(odbc_result($stmt,"SOSHIKI_CD"));
			$bumonnm = $module_cmn->fChangUTF8(odbc_result($stmt,"SOSHIKI_NM"));
			//就業の組織コードの変換(８桁から１０桁)
			$bumoncd = $module_cmn->fChangeTimePro10($bumoncd);
			$kaishacd = $module_cmn->fChangUTF8(odbc_result($stmt,"KAISHA_CD"));
		}

		//次に不具合管理側の担当者マスタを検索
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);

		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}else{
			//SQLの実行(文字エンコーディングの設定)
			oci_parse($conn, "");
		}

		//ID,パスワードで組織マスタ検索
		$sql = "SELECT TS.C_SHAIN_CD AS SHAIN_CD ";
		$sql = $sql." ,TS.V2_SHAIN_NM AS SHAIN_NM ";
		$sql = $sql." FROM T_MS_SHAIN TS ";
		$sql = $sql." WHERE TS.C_SHAIN_CD ='".$id."'";	//IDは大文字に変換
		$sql = $sql." AND TS.N_DEL_FLG = 0 ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);


		//検索結果が0件の場合
		if(!oci_fetch($stmt)) {
			
			$bLoginFlag = false;			
		}
		
		if($bLoginFlag){
			
			//担当者情報の取得
			//セッション(変数名:login)に書き込み
			//0:ログインID(担当者コード)
			//1:担当者名
			//2:部門所属コード
			//3:部門所属名
			$_SESSION['login'] = array($id,$syainnm,$bumoncd,$bumonnm);
			
			//遷移先がなければメインページへ
			if(empty($page)){
				//遷移先アドレス指定
				header("Location: top.php");

			}else{
				//遷移先があれば指定ページへ
				//2019/05/13 ADD START T.FUJITA
				//header("Location: http://".$_SERVER['HTTP_HOST'].$page."&rrcno=".$rrcno);
				if($rrcseq==0){
					header("Location: http://".$_SERVER['HTTP_HOST'].$page."&rrcno=".$rrcno);
				}else{
					//赤伝緑伝用
					header("Location: http://".$_SERVER['HTTP_HOST'].$page."&rrcno=".$rrcno."&rrcseq=".$rrcseq);
				}
				//2019/05/13 ADD END T.FUJITA
			
			}
			
		}else{
			//遷移先がなければメインページへ
			if(empty($page)){
				header("Location: index.php?err=1");
			}else{
			//die("Location: http://".$_SERVER['HTTP_HOST']."/FL/F_CMN0010.php?".$page."&rrcno=".$rrcno);
				//遷移先があれば指定ページへ
				//2019/05/13 ADD START T.FUJITA
				//header("Location: http://".$_SERVER['HTTP_HOST']."/FL/F_CMN0010.php?page=".$page."&rrcno=".$rrcno."&err=1");
				if($rrcseq==0){
					header("Location: http://".$_SERVER['HTTP_HOST']."/FL/F_CMN0010.php?page=".$page."&rrcno=".$rrcno."&err=1");
				}else{
					header("Location: http://".$_SERVER['HTTP_HOST']."/FL/F_CMN0010.php?page=".$page."&rrcno=".$rrcno."&rrcseq=".$rrcseq."&err=1");
				}
				//2019/05/13 ADD END T.FUJITA
				//header("Location: http://webappsvtest/F_CMN0010.php?".$page."&rrcno=".$rrcno);
			}
			
		}

		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);


	}


	//概要：区分マスタコンボボックス作成処理
	//処理内容：区分マスタの値でリストボックスを作成する
	//引数
	//		$strKbnCd(区分コード)
	//		$strKbnMeiCd(区分明細コード)
	public function fMakeCombo($strKbnCd,$strKbnMeiCd){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);


		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}

		//区分マスタ検索SQL
		$sql = "SELECT V2_KBN_MEI_CD,V2_KBN_MEI_NM FROM T_MS_FL_KBN ";
		$sql = $sql." WHERE V2_KBN_CD = '".$strKbnCd."'";
		$sql = $sql." AND N_DEL_FLG = 0";
		$sql = $sql." AND C_LIST_DISP_KBN = '1'";
		$sql = $sql." ORDER BY N_LIST_DISP_SORT";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			if(trim($strKbnMeiCd) == oci_result($stmt, 'V2_KBN_MEI_CD')){
				echo "<option selected value=".oci_result($stmt, 'V2_KBN_MEI_CD').">".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_KBN_MEI_NM'))."</option>";
			}else{
				echo "<option value=".oci_result($stmt, 'V2_KBN_MEI_CD').">".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_KBN_MEI_NM'))."</option>";
			}
		}
		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);
	}


	//概要：不具合区分マスタコンボボックス作成処理
	//処理内容：区分マスタの値でリストボックスを作成する
	//引数
	//
	//		$strKbnMeiCd(区分明細コード)
	public function fMakeComboFlaw($strKbnMeiCd){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);


		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}


		//不具合区分マスタ検索SQL
		$sql = "SELECT C_KBN_CD, C_KBN_DETAIL_CD, V2_KBN_NM, V2_KBN_DETAIL_NM  ";
		$sql = $sql." FROM V_FL_FLAW_INFO ";
		$sql = $sql." ORDER BY C_KBN_DETAIL_CD";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			if(trim($strKbnMeiCd) == oci_result($stmt, 'C_KBN_DETAIL_CD')){
				echo "<option selected value=".oci_result($stmt, 'C_KBN_DETAIL_CD').">".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_KBN_DETAIL_NM'))."</option>";
			}else{
				echo "<option value=".oci_result($stmt, 'C_KBN_DETAIL_CD').">".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_KBN_DETAIL_NM'))."</option>";
			}
		}
		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);
	}

	//概要：稼動カレンダマスタ値取得
	public function fWorkCalender(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);


		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}


		//不具合区分マスタ検索SQL
		$sql = "SELECT N_YMD ";
		$sql = $sql." FROM V_FL_CALENDER_INFO ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		$strJudgeDate = "";

		while (oci_fetch($stmt)) {
			$strJudgeDate = oci_result($stmt, 'N_YMD');
			break;
		}
		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);

		return $strJudgeDate;

	}
	
	//概要：稼動カレンダマスタ値取得(環境用)
	public function fWorkCalender_ENV(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);


		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}


		//不具合区分マスタ検索SQL
		$sql = "SELECT N_YMD ";
		$sql = $sql." FROM V_FL_CALENDER_INFO_ENV ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		$strJudgeDate = "";

		while (oci_fetch($stmt)) {
			$strJudgeDate = oci_result($stmt, 'N_YMD');
			break;
		}
		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);

		return $strJudgeDate;

	}

	//概要：品質異常改善通知書用　期限取得
	public function fLimitCalender($intDay){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);


		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}


		//不具合区分マスタ検索SQL
		$sql = "SELECT N_YMD ";
		$sql = $sql." FROM V_FL_CALENDER_LIMIT ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;

		while (oci_fetch($stmt)) {
			if($i == $intDay){
				$intLimitDate = oci_result($stmt, 'N_YMD');
				break;
			}
			$i++;
		}
		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);

		return $intLimitDate;

	}


	//メニューマスタ情報取得処理
	//引数
	//$session ･･･ セッション変数
	public function fMenuSearch($session){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();



		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		//$conn->setOption( 'optimize', 'portability' );

		//品目情報SQL取得
		$sql = "SELECT ";
		$sql = $sql." T1.C_PG_ID AS PG_ID ";
		$sql = $sql." ,T2.V2_KBN_MEI_NM AS MENU_NM ";
		$sql = $sql." ,T1.V2_PG_NAME AS PG_NAME ";
		$sql = $sql." ,T1.V2_URL AS URL ";
		$sql = $sql." ,T1.V2_KATEGORY AS KATEGORY ";
		$sql = $sql." ,T1.N_SORT AS SORT  ";
		$sql = $sql." FROM T_MS_MENU T1,T_MS_FL_KBN T2 ";
		$sql = $sql." WHERE T1.V2_KATEGORY = T2.V2_KBN_MEI_CD  ";
		$sql = $sql." AND T2.V2_KBN_CD = 'C26' ";
		$sql = $sql." AND T2.N_DEL_FLG = 0 ";
		//$sql = $sql." AND N_MENU_NO".$session[7]." = 1 ";
		$sql = $sql." ORDER BY T1.V2_KATEGORY ,T1.N_SORT  ";

		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[$i][0]  = oci_result($stmt, 'PG_ID');
			$aPara[$i][1]  = $module_cmn->fChangUTF8(oci_result($stmt, 'MENU_NM'));
			$aPara[$i][2]  = $module_cmn->fChangUTF8(oci_result($stmt, 'PG_NAME'));
			$aPara[$i][3]  = oci_result($stmt, 'URL');
			$aPara[$i][4]  = oci_result($stmt, 'KATEGORY');
			$aPara[$i][5]  = oci_result($stmt, 'SORT');

			$i = $i + 1;
		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}





	//概要：会社マスタコンボボックス作成処理
	//処理内容：会社マスタの値でリストボックスを作成する
	//引数
	//		$strPara(会社コード)
	public function fMakeComboKaisha($strPara){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);


		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}

		//会社マスタ検索SQL
		$sql = "SELECT * FROM T_MS_KAISHA ";
		$sql = $sql." WHERE N_DEL_FLG = 0";
		$sql = $sql." ORDER BY C_KAISHA_CD";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			if($strPara == oci_result($stmt, 'C_KAISHA_CD')){
				echo "<option selected value=".oci_result($stmt, 'C_KAISHA_CD').">".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_KAISHA_NM'))."</option>";
			}else{
				echo "<option value=".oci_result($stmt, 'C_KAISHA_CD').">".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_KAISHA_NM'))."</option>";
			}
		}
		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);
	}


	//概要：区分マスタ名称取得処理
	//処理内容：区分マスタの値を返す
	//引数
	//		$strKbnCd(区分コード)
	//		$strKbnMeiCd(区分明細コード)
	public function fDispKbn($strKbnCd,$strKbnMeiCd){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$strMeisho = "";
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);


		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}

		//組織マスタ検索SQL
		$sql = "SELECT V2_KBN_MEI_CD,V2_KBN_MEI_NM FROM T_MS_FL_KBN ";
		$sql = $sql." WHERE V2_KBN_CD = '".$strKbnCd."'";
		$sql = $sql." AND V2_KBN_MEI_CD = '".trim($strKbnMeiCd)."'";
		$sql = $sql." AND N_DEL_FLG = 0";
		$sql = $sql." ORDER BY V2_KBN_MEI_CD";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		if(oci_fetch($stmt)) {
			$strMeisho = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_KBN_MEI_NM'));
		}
		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);

		return $strMeisho;
	}




	//取引先メール配信マスタメンテナンス一覧取得処理
	//引数
	public function fCustMailSendList($aJoken){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		$aPara[0][0] = "N006";

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		//$conn->setOption( 'optimize', 'portability' );


		//メニュー情報SQL取得
		$sql = "SELECT ";
		$sql = $sql." T1.C_CUST_CD AS C_CUST_CD ";
		$sql = $sql." ,T1.V2_CUST_NM AS V2_CUST_NM ";
		$sql = $sql." ,T1.V2_CUST_NM_K AS V2_CUST_NM_K ";
		$sql = $sql." ,T1.V2_MAIL_ADDRESS AS V2_MAIL_ADDRESS ";
		$sql = $sql." FROM ";
		$sql = $sql." V_FL_MAIL_INFO T1 ";
		$sql = $sql." WHERE 1 = 1 ";
		//$sql = $sql." ,T_MS_CUST_MAIL T2 ";
		//$sql = $sql." WHERE T1.C_CUST_CD = T2.C_CUST_CD(+) ";
		//$sql = $sql." AND T1.C_CUST_CLS IN ('C','G','K') ";

		//取引先CD
		if($aJoken[0] <> ""){
			$sql = $sql." AND TRIM(T1.C_CUST_CD) LIKE '%' || :strCustCd || '%'" ;
		}
		//取引先名
		if($aJoken[1] <> ""){
			$sql = $sql." AND T1.V2_CUST_NM LIKE '%' || :strCustNm || '%'" ;
		}
		//取引先名カナ
		if($aJoken[2] <> ""){
			$sql = $sql." AND T1.V2_CUST_NM_K LIKE '%' || :strCustNmK || '%'" ;
		}
		//$sql = $sql." ORDER BY T1.C_CUST_CD ";


		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//取引先CD
		if($aJoken[0] <> ""){
			oci_bind_by_name($stmt, ":strCustCd", $aJoken[0], -1);
		}
		//取引先名
		if($aJoken[1] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS_SQL($aJoken[1]);
			oci_bind_by_name($stmt, ":strCustNm",$sTmpJoken, -1);
		}
		//取引先名カナ
		if($aJoken[2] <> ""){
			$strCustNmK = $module_cmn->fChangSJIS_SQL($aJoken[2]);
			oci_bind_by_name($stmt, ":strCustNmK",$strCustNmK, -1);
		}

		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[$i][0]  = oci_result($stmt, 'C_CUST_CD');
			$aPara[$i][1]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM'));
			$aPara[$i][2]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM_K'));
			$aPara[$i][3]  = oci_result($stmt, 'V2_MAIL_ADDRESS');

			$i = $i + 1;
		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}

	//取引先メール配信マスタメンテナンス明細取得処理
	//引数
	public function fCustMailSendDetail($strCustCd){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();


		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		//$conn->setOption( 'optimize', 'portability' );


		//取引先メールマスタ明細情報SQL取得
		$sql = "SELECT ";
		$sql = $sql." T1.C_CUST_CD AS C_CUST_CD ";
		$sql = $sql." ,T1.V2_CUST_NM AS V2_CUST_NM ";
		$sql = $sql." ,T1.V2_CUST_NM_K AS V2_CUST_NM_K ";
		$sql = $sql." ,T2.V2_MAIL_ADDRESS AS V2_MAIL_ADDRESS ";
		$sql = $sql." ,T2.N_INS_YMD AS N_INS_YMD ";
		$sql = $sql." ,T3.V2_TANTO_NM AS V2_INS_TANTO_NM ";
		$sql = $sql." ,T2.N_UPD_YMD AS N_UPD_YMD ";
		$sql = $sql." ,T4.V2_TANTO_NM AS V2_UPD_TANTO_NM ";
		$sql = $sql." ,T2.N_UPDATE_COUNT AS N_UPDATE_COUNT ";
		$sql = $sql." FROM ";
		$sql = $sql." V_FL_MAIL_INFO T1 ";
		$sql = $sql." ,T_MS_CUST_MAIL T2 ";
		$sql = $sql." ,V_FL_TANTO_INFO T3 ";
		$sql = $sql." ,V_FL_TANTO_INFO T4 ";
		$sql = $sql." WHERE TRIM(T1.C_CUST_CD) = TRIM(T2.C_CUST_CD(+)) ";
		$sql = $sql." AND TRIM(T1.C_CUST_CD) = '".$strCustCd."' ";
		$sql = $sql." AND TRIM(T2.C_INS_SHAIN_CD) = T3.C_TANTO_CD(+)	 ";
		$sql = $sql." AND TRIM(T2.C_UPD_SHAIN_CD) = T4.C_TANTO_CD(+)	 ";

		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		//$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[0]  = oci_result($stmt, 'C_CUST_CD');
			$aPara[1]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM'));
			$aPara[2]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM_K'));
			$aPara[3]  = oci_result($stmt, 'V2_MAIL_ADDRESS');
			$aPara[4]  = $module_cmn->fChangDateTimeFormat(oci_result($stmt, 'N_INS_YMD'));
			$aPara[5]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INS_TANTO_NM'));
			$aPara[6]  = $module_cmn->fChangDateTimeFormat(oci_result($stmt, 'N_UPD_YMD'));
			$aPara[7]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_UPD_TANTO_NM'));
			$aPara[8]  = oci_result($stmt, 'N_UPDATE_COUNT');


			$i = $i + 1;
		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}


	//概要：整理NO検索
	//処理内容：既存整理NOを検索して新規の整理NO番号を作成する
	//引数
	//
	public function fReference_NoSearch($conn,$session,$strUpPg,$strTargetSec){

		$strRrceNo = "";
		$intUpCount = 0;

		$intKi = 0;
		//期の算出
		if(date("n") >= 7){
			//7～12月
			$intKi = date("Y") - 1968;
		}else{
			//1～6月
			$intKi = date("Y") - 1969;
		}

		//注文番号SQL取得
		$sql = "SELECT TRIM(TO_CHAR((TO_NUMBER(SUBSTR(V2_SAIBAN_CD,8)) + 1),'000')) AS RRCE_NO ";
		$sql = $sql." ,(N_UPDATE_COUNT + 1) AS UPDATE_COUNT ";
		$sql = $sql." FROM T_MS_SAIBAN ";
		$sql = $sql." WHERE TRIM(C_ID_CD) = '".$strTargetSec."'";
		$sql = $sql." AND V2_SAIBAN_CD LIKE '".$intKi.$strTargetSec."-".date("m")."%'";
		$sql = $sql." FOR UPDATE ";

		//echo $sql;

		//SQLの解析
		$stmt = oci_parse($conn, $sql);
		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$strRrceNo = $intKi.$strTargetSec."-".date("m")."-".oci_result($stmt, 'RRCE_NO');
			$intUpCount = oci_result($stmt, 'UPDATE_COUNT');
		}

		//当日の注番がなければ新規で作成
		if($strRrceNo == ""){
			$strRrceNo = $intKi.$strTargetSec."-".date("m")."-"."001";
		}

		//採番したら採番テーブルを更新
		$sql = "UPDATE T_MS_SAIBAN    ";
		$sql = $sql." SET V2_SAIBAN_CD = '".$strRrceNo."'";
		$sql = $sql.",N_UPD_YMD = ".date("YmdHis");									//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD = '".$session[0]."' ";							//更新担当
		$sql = $sql." WHERE TRIM(C_ID_CD) = '".$strTargetSec."'";					//採番部門

		//echo $sql;

		//SQLの解析
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

			return "err";

		}


		oci_free_statement($stmt);

		return $strRrceNo;
	}
	
	//概要：整理NO検索(環境紛争鉱物用)
	//処理内容：既存整理NOを検索して新規の整理NO番号を作成する
	//引数
	//
	public function fReference_NoSearch_Env($conn,$session,$strUpPg){

		$strRrceNo = "";
		$intUpCount = 0;

		$intKi = 0;
		//期の算出
		if(date("n") >= 7){
			//7～12月
			$intKi = date("Y") - 1968;
		}else{
			//1～6月
			$intKi = date("Y") - 1969;
		}

		//注文番号SQL取得
		$sql = "SELECT TRIM(TO_CHAR((TO_NUMBER(SUBSTR(V2_SAIBAN_CD,4)) + 1),'000')) AS RRCE_NO ";
		$sql = $sql." ,(N_UPDATE_COUNT + 1) AS UPDATE_COUNT ";
		$sql = $sql." FROM T_MS_SAIBAN ";
		$sql = $sql." WHERE TRIM(C_ID_CD) = 'E'";
		$sql = $sql." AND V2_SAIBAN_CD LIKE '".$intKi."-%'";
		$sql = $sql." FOR UPDATE ";

		//echo $sql;

		//SQLの解析
		$stmt = oci_parse($conn, $sql);
		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$strRrceNo = $intKi."-".oci_result($stmt, 'RRCE_NO');
			$intUpCount = oci_result($stmt, 'UPDATE_COUNT');
		}

		//当日の注番がなければ新規で作成
		if($strRrceNo == ""){
			$strRrceNo = $intKi."-001";
		}

		//採番したら採番テーブルを更新
		$sql = "UPDATE T_MS_SAIBAN    ";
		$sql = $sql." SET V2_SAIBAN_CD = '".$strRrceNo."'";
		$sql = $sql.",N_UPD_YMD = ".date("YmdHis");									//更新日時
		$sql = $sql.",C_UPD_SHAIN_CD = '".$session[0]."' ";							//更新担当
		$sql = $sql." WHERE TRIM(C_ID_CD) = 'E'";									//採番部門

		//echo $sql;

		//SQLの解析
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

			return "err";

		}


		oci_free_statement($stmt);

		return $strRrceNo;
	}


	//不具合管理状況情報取得処理
	public function fFlawStatsSearch(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		//$conn->setOption( 'optimize', 'portability' );



		//SQL取得
		$sql = "SELECT ";
		$sql = $sql." N_ALL_CNT, N_RECEPT_CNT, N_STEP_CNT, N_RESERVE_CNT,N_COMPLETE_CNT ";
		$sql = $sql." FROM V_FL_FLAW_STATUS ";

		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {

			$aPara[0]  = oci_result($stmt, 'N_ALL_CNT');
			$aPara[1]  = oci_result($stmt, 'N_RECEPT_CNT');
			$aPara[2]  = oci_result($stmt, 'N_STEP_CNT');
			//$aPara[3]  = oci_result($stmt, 'N_RESERVE_CNT');
			$aPara[4]  = oci_result($stmt, 'N_COMPLETE_CNT');

		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}
/*
	//不具合管理状況情報取得処理（部門別）
	public function fFlawDepartStatsSearch(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//SQL取得
		$sql = "";
		$sql = $sql."SELECT ";
		$sql = $sql." N_ALL_F_CNT ";
		$sql = $sql.",N_RECEPT_F_CNT ";
		$sql = $sql.",N_STEP_F_CNT ";
		$sql = $sql.",N_RESERVE_F_CNT ";
		$sql = $sql.",N_COMPLETE_F_CNT ";
		$sql = $sql.",N_ALL_K_CNT ";
		$sql = $sql.",N_RECEPT_K_CNT ";
		$sql = $sql.",N_STEP_K_CNT ";
		$sql = $sql.",N_RESERVE_K_CNT ";
		$sql = $sql.",N_COMPLETE_K_CNT ";
		$sql = $sql.",N_ALL_M_CNT ";
		$sql = $sql.",N_RECEPT_M_CNT ";
		$sql = $sql.",N_STEP_M_CNT ";
		$sql = $sql.",N_RESERVE_M_CNT ";
		$sql = $sql.",N_COMPLETE_M_CNT ";
		$sql = $sql.",N_ALL_T_CNT ";
		$sql = $sql.",N_RECEPT_T_CNT ";
		$sql = $sql.",N_STEP_T_CNT ";
		$sql = $sql.",N_RESERVE_T_CNT ";
		$sql = $sql.",N_COMPLETE_T_CNT ";
		$sql = $sql." FROM V_FL_FLAW_DEPART_STATUS ";

		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$aPara[0][0]  = oci_result($stmt, 'N_ALL_F_CNT');
			$aPara[0][1]  = oci_result($stmt, 'N_RECEPT_F_CNT');
			$aPara[0][2]  = oci_result($stmt, 'N_STEP_F_CNT');
			$aPara[0][3]  = oci_result($stmt, 'N_COMPLETE_F_CNT');
			$aPara[1][0]  = oci_result($stmt, 'N_ALL_K_CNT');
			$aPara[1][1]  = oci_result($stmt, 'N_RECEPT_K_CNT');
			$aPara[1][2]  = oci_result($stmt, 'N_STEP_K_CNT');
			$aPara[1][3]  = oci_result($stmt, 'N_COMPLETE_K_CNT');
			$aPara[2][0]  = oci_result($stmt, 'N_ALL_M_CNT');
			$aPara[2][1]  = oci_result($stmt, 'N_RECEPT_M_CNT');
			$aPara[2][2]  = oci_result($stmt, 'N_STEP_M_CNT');
			$aPara[2][3]  = oci_result($stmt, 'N_COMPLETE_M_CNT');
			$aPara[3][0]  = oci_result($stmt, 'N_ALL_T_CNT');
			$aPara[3][1]  = oci_result($stmt, 'N_RECEPT_T_CNT');
			$aPara[3][2]  = oci_result($stmt, 'N_STEP_T_CNT');
			$aPara[3][3]  = oci_result($stmt, 'N_COMPLETE_T_CNT');
		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}
*/
	//概要：マスタ存在チェック
	//処理内容：引数から各マスタの存在チェックを行う
	//引数
	//		$strObj(プライマリキー値)
	//		$strTit(マスタ名)
	//		$strTable(マスタテーブル)
	//		$strJyoken(プライマリキー項目名)
	public function fSonzaiCheck($strObj,$strTit,$strTable,$strJyoken){
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strChumon_no = "";

		//注文番号SQL取得
		$sql = "SELECT * ";
		$sql = $sql." FROM ".$strTable ;
		$sql = $sql." WHERE ".$strJyoken."= '".trim($strObj)."'" ;
		//$sql = $sql." AND N_DEL_FLG = 0 ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		//検索結果が0件の場合
		if(!oci_fetch($stmt)) {
			$strMsg = $this->fMsgSearch("E015",$strTit.":".$strObj);
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $strMsg;
	}

	//概要：マスタ存在チェック2(取引先マスタチェック用に増設)
	//処理内容：引数から各マスタの存在チェックを行う(条件切り分け用)
	//引数
	//		$strObj(プライマリキー値)
	//		$strTit(マスタ名)
	//		$strTable(マスタテーブル)
	//		$strJyoken(プライマリキー項目名)
	//		$strJyokenEtc(その他条件項目名)
	//		$strKaisha(管理会社)
	//		$strObjEtc(その他条件値)
	public function fSonzaiCheck2($strObj,$strTit,$strTable,$strJyoken,$strJyokenEtc,$strKaisha,$strObjEtc){
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strChumon_no = "";

		//注文番号SQL取得
		$sql = "SELECT * ";
		$sql = $sql." FROM ".$strTable ;
		$sql = $sql." WHERE ".$strJyoken."= '".trim($strObj)."'" ;
		$sql = $sql." AND ".$strJyokenEtc." IN ".trim($strObjEtc) ;
		//管理会社で絞り込み
		if($strKaisha == "00"){
			$sql = $sql." AND C_KANRI_KAISHA_CD IN ('00','99') ";
		}elseif($strKaisha == "02"){
			$sql = $sql." AND C_KANRI_KAISHA_CD IN ('02','99') ";
		}

		$sql = $sql." AND N_DEL_FLG = 0 ";
		//echo $sql;
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		//検索結果が0件の場合
		if(!oci_fetch($stmt)) {
			$strMsg = $this->fMsgSearch("E015",$strTit.":".$strObj);
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $strMsg;
	}



	//概要：マスタ既存データチェック
	//処理内容：引数から各マスタの既存データチェックを行う
	//引数
	//		$strObj(プライマリキー値)
	//		$strTit(マスタ名)
	//		$strTable(マスタテーブル)
	//		$strJyoken(プライマリキー項目名)
	public function fKizonCheck($strObj,$strTit,$strTable,$strJyoken){
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}


		//SQL取得
		$sql = "SELECT * ";
		$sql = $sql." FROM ".$strTable ;
		$sql = $sql." WHERE ".$strJyoken."= '".trim($strObj)."'" ;
		//$sql = $sql." AND N_DEL_FLG = 0 ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		//検索結果が0件の場合
		if(oci_fetch($stmt)) {
			$strMsg = $this->fMsgSearch("E003",$strTit.":".$strObj);
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $strMsg;
	}



	//概要：マスタ更新回数チェック（キー１つ）
	//処理内容：引数から各マスタの更新回数をチェックし更新前の回数と違っていたらfalseを戻す
	//引数
	//		$strObj(プライマリキー値)
	//		$intUCount(更新前更新回数)
	//		$strTit(マスタ名)
	//		$strTable(マスタテーブル)
	//		$strJyoken(プライマリキー項目名)
	public function fKoshinCheck($strObj,$intUCount,$strTit,$strTable,$strJyoken){
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}


		//SQL取得
		$sql = "SELECT N_UPDATE_COUNT ";
		$sql = $sql." FROM ".$strTable ;
		$sql = $sql." WHERE ".$strJyoken."= '".$strObj."'" ;
		//$sql = $sql." AND N_DEL_FLG = 0 ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {

			if($intUCount <> oci_result($stmt, 'N_UPDATE_COUNT')){
				return false;
			}
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return true;
	}

	//概要：更新回数チェック(キー２つ)
	//処理内容：引数から各マスタの更新回数をチェックし更新前の回数と違っていたらfalseを戻す
	//引数
	//		$strObj1(プライマリキー1値)
	//		$strObj2(プライマリキー2値)
	//		$intUCount(更新前更新回数)
	//		$strTit(テーブル名)
	//		$strTable(物理テーブル名)
	//		$strJyoken1(プライマリキー1項目名)
	//		$strJyoken2(プライマリキー2項目名)
	public function fKoshinCheck2($strObj1,$strObj2,$intUCount,$strTit,$strTable,$strJyoken1,$strJyoken2){
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}


		//SQL取得
		$sql = "SELECT N_UPDATE_COUNT ";
		$sql = $sql." FROM ".$strTable ;
		$sql = $sql." WHERE ".$strJyoken1."= '".trim($strObj1)."'" ;
		$sql = $sql." AND ".$strJyoken2."= '".trim($strObj2)."'" ;
		//$sql = $sql." AND N_DEL_FLG = 0 ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			if($intUCount <> oci_result($stmt, 'N_UPDATE_COUNT')){
				return false;
			}
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return true;
	}

	//概要：更新回数チェック(キー３つ)
	//処理内容：引数から各マスタの更新回数をチェックし更新前の回数と違っていたらfalseを戻す
	//引数
	//		$strObj1(プライマリキー1値)
	//		$strObj2(プライマリキー2値)
	//		$strObj3(プライマリキー3値)
	//		$intUCount(更新前更新回数)
	//		$strTit(テーブル名)
	//		$strTable(物理テーブル名)
	//		$strJyoken1(プライマリキー1項目名)
	//		$strJyoken2(プライマリキー2項目名)
	//		$strJyoken2(プライマリキー3項目名)
	public function fKoshinCheck3($strObj1,$strObj2,$strObj3,$intUCount,$strTit,$strTable,$strJyoken1,$strJyoken2,$strJyoken3){
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}


		//SQL取得
		$sql = "SELECT N_UPDATE_COUNT ";
		$sql = $sql." FROM ".$strTable ;
		$sql = $sql." WHERE ".$strJyoken1."= '".trim($strObj1)."'" ;
		$sql = $sql." AND ".$strJyoken2."= '".trim($strObj2)."'" ;
		$sql = $sql." AND ".$strJyoken3."= '".trim($strObj3)."'" ;
		//$sql = $sql." AND N_DEL_FLG = 0 ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			if($intUCount <> oci_result($stmt, 'N_UPDATE_COUNT')){
				return false;
			}
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return true;
	}

	//概要：メッセージ取得処理
	//処理内容：引数を元にメッセージマスタからメッセージを取得する
	//引数
	//		$strMsgCd(メッセージコード)
	//		$strPara(メッセージ区分)
	public function fMsgSearch($strMsgCd,$strPara){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//注文番号SQL取得
		$sql = "SELECT C_MSG_CD, C_MSG_KBN, V2_MSG_NAIYO1, V2_MSG_NAIYO2, V2_MSG_NAIYO3 ";
		$sql = $sql." FROM T_MS_MSG ";
		$sql = $sql." WHERE C_MSG_CD = '".$strMsgCd."'";
		$sql = $sql." AND N_DEL_FLG = 0 ";

		//SQLの実行

		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$strMsg = oci_result($stmt, 'C_MSG_CD')." ".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_MSG_NAIYO1'));
		}

		//引数に何か設定されていたら付加する
		if($strPara <> ""){
			$strMsg = $strMsg."[".$strPara."]";
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $strMsg."<br>";
	}

	//製品マスタリスト情報取得処理(検索子画面用)
	//引数
	//$strProdCd…品目コード
	//$strProdNm…品目名
	//$strProdNmK…品目名カナ
	public function fGetProdDataList($strProdCd,$strProdNm,$strDrwNo,$strDieNo,$strCustCd,$strCustNm){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();



		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//Oracleのバージョン取得
		$strOraVer = oci_server_version($conn);
		//9iかそれ以外かで判断
		//if(ereg("9i",$strOraVer)){
		//PHP5.3以降対応 2012/05/28 k.kume
		if(preg_match("/9i/",$strOraVer)){
			$bSearchOk = false;
		}else{
			$bSearchOk = true;
		}

		//$conn->setOption( 'optimize', 'portability' );

		//製品情報SQL取得
		$sql = "SELECT C_PROD_CD, V2_PROD_NM, V2_MODEL, V2_DRW_NO, C_DIE_NO, C_CUST_CD,V2_CUST_NM ";
		$sql = $sql." FROM V_FL_PROD_INFO ";
		//$sql = $sql." WHERE C_PROD_CD LIKE '%' || :strProdCd || '%'" ;
		$sql = $sql." WHERE C_PROD_CD LIKE '%".$strProdCd."%'" ;
		//$sql = $sql." WHERE V2_PROD_NM LIKE '%' || :strProdNm || '%'" ;
		if($strProdNm <> ""){
			$sql = $sql." AND V2_PROD_NM LIKE '%".$strProdNm."%'" ;
		}
		//$sql = $sql." AND V2_MODEL LIKE '%' || :strModel || '%'" ;
		//$sql = $sql." AND V2_DRW_NO LIKE '%' || :strDrwNo || '%'" ;
		if($strDrwNo <> ""){
			$sql = $sql." AND V2_DRW_NO LIKE '%".$strDrwNo."%'" ;
		}
		if($strDieNo <> ""){
			//$sql = $sql." AND C_DIE_NO LIKE '%' || :strDieNo || '%'" ;
			$sql = $sql." AND C_DIE_NO LIKE '%' || :strDieNo || '%'" ;
		}
		//$sql = $sql." AND C_CUST_CD LIKE '%' || :strCustCd || '%'" ;
		if($strCustCd <> ""){
			$sql = $sql." AND C_CUST_CD LIKE '%".$strCustCd."%'" ;
		}
		//$sql = $sql." AND V2_CUST_NM LIKE '%' || :strCustNm || '%'" ;
		if($strCustNm <> ""){
			$sql = $sql." AND V2_CUST_NM LIKE '%".$strCustNm."%'" ;
		}
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの分析
		$stmt = oci_parse($conn, $sql);
		//条件セット
		//oci_bind_by_name($stmt, ":strProdCd", $strProdCd, -1);
/* 		oci_bind_by_name($stmt, ":strProdNm", $strProdNm, -1);
		//oci_bind_by_name($stmt, ":strModel", $strModel, -1);
		oci_bind_by_name($stmt, ":strDrwNo", $module_cmn->fChangSJIS_SQL($strDrwNo), -1);
		if($strDieNo <> ""){
			oci_bind_by_name($stmt, ":strDieNo", $strDieNo, -1);
		}
		oci_bind_by_name($stmt, ":strCustCd", $strCustCd, -1);
		oci_bind_by_name($stmt, ":strCustNm", $module_cmn->fChangSJIS_SQL($strCustNm), -1); */
		
		//echo $sql;
		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);
		
		//件数取得
		$iRows = oci_fetch_all($stmt,$results);


		//初期に検索結果無しのパラメータを設定しておく(検索結果があれば上書き)
		$aPara[0][0] = "N006";

		//1000件以上あった
		if($iRows > 1000){
			$aPara[0][0] = "E016";
			return $aPara;
		}
		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);
		$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[$i][0]  = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));
			$aPara[$i][1]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));
			$aPara[$i][2]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));
			//$aPara[$i][3]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_MODEL'));
			$aPara[$i][4]  = $module_cmn->fChangUTF8(oci_result($stmt, 'C_DIE_NO'));
			$aPara[$i][5]  = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD'));
			$aPara[$i][6]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM'));


			$i = $i + 1;
		}



		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}


	//製品マスタリスト情報取得処理(不具合登録用)
	//引数
	//$strProdCd…品目コード
	//$strProdNm…品目名
	//$strProdNmK…品目名カナ
	public function fGetProdDataDetail($strProdCd,$strProdNm,$strDrwNo,$strDieNo,$strCustCd,$strCustNm){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();



		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//Oracleのバージョン取得
		$strOraVer = oci_server_version($conn);
		//9iかそれ以外かで判断
		//if(ereg("9i",$strOraVer)){
		//PHP5.3以降対応 2012/05/28 k.kume
		if(preg_match("/9i/",$strOraVer)){
			$bSearchOk = false;
		}else{
			$bSearchOk = true;
		}

		//$conn->setOption( 'optimize', 'portability' );

		//製品情報SQL取得
		$sql = "SELECT C_PROD_CD, V2_PROD_NM, V2_MODEL, V2_DRW_NO, C_DIE_NO, C_CUST_CD,V2_CUST_NM ";
		$sql = $sql." FROM V_FL_PROD_INFO ";
		$sql = $sql." WHERE C_PROD_CD = :strProdCd" ;
		$sql = $sql." AND V2_PROD_NM LIKE '%' || :strProdNm || '%'" ;
		//$sql = $sql." AND V2_MODEL LIKE '%' || :strModel || '%'" ;
		$sql = $sql." AND V2_DRW_NO LIKE '%' || :strDrwNo || '%'" ;
		$sql = $sql." AND C_DIE_NO LIKE '%' || :strDieNo || '%'" ;
		$sql = $sql." AND C_CUST_CD LIKE '%' || :strCustCd || '%'" ;
		$sql = $sql." AND V2_CUST_NM LIKE '%' || :strCustNm || '%'" ;

		//echo $sql;
		//SQLの分析
		$stmt = oci_parse($conn, $sql);
		//条件セット
		oci_bind_by_name($stmt, ":strProdCd", $strProdCd, -1);
		$sTmpJoken = $module_cmn->fChangSJIS_SQL($strProdNm);
		oci_bind_by_name($stmt, ":strProdNm", $sTmpJoken, -1);
		//oci_bind_by_name($stmt, ":strModel", $strModel, -1);
		
		$sTmpJoken = $module_cmn->fChangSJIS_SQL($strDrwNo);
		oci_bind_by_name($stmt, ":strDrwNo", $sTmpJoken, -1);
		oci_bind_by_name($stmt, ":strDieNo", $strDieNo, -1);
		oci_bind_by_name($stmt, ":strCustCd", $strCustCd, -1);
		
		$sTmpJoken = $module_cmn->fChangSJIS_SQL($strCustNm);
		oci_bind_by_name($stmt, ":strCustNm", $sTmpJoken, -1);

		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		//件数取得
		$iRows = oci_fetch_all($stmt,$results);




		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);
		$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[0]  = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));
			$aPara[1]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));
			$aPara[2]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));
			$aPara[3]  = $module_cmn->fChangUTF8(oci_result($stmt, 'C_DIE_NO'));
			$aPara[4]  = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD'));
			$aPara[5]  = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM'));


			$i = $i + 1;
		}



		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}




	//顧客マスタリスト情報取得処理
	//引数
	//$strCustCd…取引先コード
	//$strCustNm…取引先名
	//$strCustNmK…取引先名カナ
	//$strJokenKbn…絞込み条件(0:得意先のみ,1:協力工場と生産課,2:外注・仕入・個別登録,3:得意先以外全て,
	//						 4:めっき先（赤伝／全て）,5:めっき先（緑伝）)
	public function fGetCustDataList($strCustCd,$strCustNm,$strCustNmK,$strJokenKbn){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();
		$sTmpJoken = "";
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)


		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//Oracleのバージョン取得
		$strOraVer = oci_server_version($conn);
		//9iかそれ以外かで判断
		//if(ereg("9i",$strOraVer)){
		//PHP5.3以降対応 2012/05/28 k.kume
		if(preg_match("/9i/",$strOraVer)){
			$bSearchOk = false;
		}else{
			$bSearchOk = true;
		}

		//得意先情報SQL取得
		$sql = " select C_CUST_CLS, C_CUST_CD, V2_CUST_NM, V2_CUST_NM_R, V2_CUST_NM_K ";
		$sql = $sql." from V_FL_CUST_INFO ";

		if($strJokenKbn == "0" ){
			$sql = $sql." where C_CUST_CLS = 'C' ";
		}elseif($strJokenKbn == "1" ){
			$sql = $sql." where C_CUST_CLS IN ('K','F') ";
		}elseif($strJokenKbn == "2" ){
				$sql = $sql." where C_CUST_CLS IN ('G','S','U') ";
		}elseif($strJokenKbn == "3" ){
				$sql = $sql." where C_CUST_CLS IN ('G','S','U','K','F') ";
//2019/04/01 AD START T.FUJITA
		}elseif($strJokenKbn == "4" ){
			$sql = $sql." where C_CUST_CLS IN ('G','K','U') ";
		}elseif($strJokenKbn == "5" ){
			$sql = $sql." where C_CUST_CLS IN ('G') ";
//2019/04/01 AD END T.FUJITA
		}else{
			$sql = $sql." where 1 = 1 ";
		}

		if($strCustCd <> ""){
			$sql = $sql." AND C_CUST_CD LIKE '%' || :strCustCd || '%'" ;
		}
		if($strCustNm <> ""){
			//$sql = $sql." AND V2_CUST_NM LIKE '%' || :strCustNm || '%'" ;
			//品証の要望で略称に変更 2015/03/24 k.kume
			$sql = $sql." AND V2_CUST_NM_R LIKE '%' || :strCustNm || '%'" ;
		}
		if($strCustNmK <> ""){
			$sql = $sql." AND V2_CUST_NM_K LIKE '%' || :strCustNmK || '%'" ;
		}
		$sql = $sql." order by C_CUST_CD ";
		//$sql = $module_cmn->fChangSJIS_SQL_J($sql);

		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//条件セット
		if($strCustCd <> ""){
			oci_bind_by_name($stmt, ":strCustCd", $strCustCd, -1);
		}
		if($strCustNm <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS_SQL($strCustNm);
			oci_bind_by_name($stmt, ":strCustNm",$sTmpJoken, -1);
		}
		if($strCustNmK <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS_SQL($strCustNmK);
			oci_bind_by_name($stmt, ":strCustNmK", $sTmpJoken, -1);
		}


		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		//件数取得
		$iRows = oci_fetch_all($stmt,$results);

		//1001件以上
		if($iRows > 1000){
			$aPara[0][0] = "E016";
			return $aPara;
		}elseif($iRows == 0){
			$aPara[0][0] = "N006";
			return $aPara;
		}

		//SQL実行
		oci_execute($stmt,OCI_DEFAULT);


		$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[$i][0] = trim($module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD')));
			//$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM'));
			//品証の要望で略称に変更 2015/03/24 k.kume
			$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM_R'));
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM_K'));

			$i = $i + 1;

		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}

	//顧客マスタ情報取得処理
	//引数
	//$strCustCd…取引先コード
	//$strCustNm…取引先名
	//$strCustNmK…取引先名カナ
	//$strJokenKbn…絞込み条件(0:得意先のみ,1:協力工場と生産課,2:全て
	//						,4:めっき先（赤伝／全て）,5:めっき先（緑伝）,6:協力工場と生産課含む全て)
	public function fGetCustDataDetail($strCustCd,$strCustNm,$strCustNmK,$strJokenKbn){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)


		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//Oracleのバージョン取得
		$strOraVer = oci_server_version($conn);
		//9iかそれ以外かで判断
		//if(ereg("9i",$strOraVer)){
		//PHP5.3以降対応 2012/05/28 k.kume
		if(preg_match("/9i/",$strOraVer)){
			$bSearchOk = false;
		}else{
			$bSearchOk = true;
		}

		//得意先情報SQL取得
		$sql = " select C_CUST_CLS, C_CUST_CD, V2_CUST_NM, V2_CUST_NM_R, V2_CUST_NM_K ";
		$sql = $sql." from V_FL_CUST_INFO ";

		if($strJokenKbn == "0" ){
			$sql = $sql." where C_CUST_CLS = 'C' ";
		}elseif($strJokenKbn == "1" ){
			$sql = $sql." where C_CUST_CLS IN ('K','F') ";
		}elseif($strJokenKbn == "2" ){
			$sql = $sql." where C_CUST_CLS IN ('G','S','U') ";
//2019/04/01 AD START T.FUJITA
		}elseif($strJokenKbn == "4" ){
			$sql = $sql." where C_CUST_CLS IN ('G','K') ";
		}elseif($strJokenKbn == "5" ){
			$sql = $sql." where C_CUST_CLS IN ('G') ";
		}elseif($strJokenKbn == "6" ){
			$sql = $sql." where C_CUST_CLS IN ('G','S','U','K','F') ";
//2019/04/01 AD END T.FUJITA
		}else{
			$sql = $sql." where 1 = 1 ";
		}

		if($strCustCd <> ""){
			$sql = $sql." AND C_CUST_CD = :strCustCd " ;
		}
		if($strCustNm <> ""){
			$sql = $sql." AND V2_CUST_NM LIKE '%' || :strCustNm || '%'" ;
		}
		if($strCustNmK <> ""){
			$sql = $sql." AND V2_CUST_NM_K LIKE '%' || :strCustNmK || '%'" ;
		}
		$sql = $sql." order by C_CUST_CD ";
		//$sql = $module_cmn->fChangSJIS_SQL_J($sql);

		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//条件セット
		if($strCustCd <> ""){
			oci_bind_by_name($stmt, ":strCustCd", $strCustCd, -1);
		}
		if($strCustNm <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS_SQL($strCustNm);
			oci_bind_by_name($stmt, ":strCustNm", $sTmpJoken, -1);
		}
		if($strCustNmK <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS_SQL($strCustNmK);
			oci_bind_by_name($stmt, ":strCustNmK", $sTmpJoken, -1);
		}


		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);



		$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[0] = trim($module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD')));
			$aPara[1] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM'));
			$aPara[2] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM_K'));

			$i = $i + 1;

		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}


	//担当者マスタリスト情報取得処理
	//引数
	//$strTantoCd…担当者コード
	//$strTantoNm…担当者名
	//$strBumonNm…部門所属名
	public function fGetTantoDataList($strTantoCd,$strTantoNm,$strBumonNm){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		$conn_ms = odbc_connect("Driver={SQL Server};Server=".$this->gTServer.";Database=".$this->gTDbName
								,$this->gTUserid
								,$this->gTPasswd);
		
		if (!$conn_ms) {
			$e = odbc_errormsg();
			session_destroy();
			die("データベースに接続できません");
		}
		
		//検索SQL作成
		$sql = "";
		$sql = $sql."SELECT ";
		$sql = $sql."   T3.CNT AS CNT ";
		$sql = $sql."   ,T1.EmpCode AS SHAIN_CD ";
		$sql = $sql."   ,T1.WordName AS SHAIN_NM ";
		$sql = $sql."   ,T1.ByteName AS SHAIN_NM_K ";
		$sql = $sql."   ,T2.DepCodeAll AS SOSHIKI_CD ";
		$sql = $sql."   ,T2.WordNameAll AS SOSHIKI_NM ";
		$sql = $sql."   ,T1.CompanyCode AS KAISHA_CD ";
		$sql = $sql."FROM ";
		$sql = $sql."  dbo.DGINDIVI T1 , dbo.DGDEPMNT T2  ";
		$sql = $sql."  ,(SELECT COUNT(T3.EmpCode) AS CNT FROM  dbo.DGINDIVI T3, dbo.DGDEPMNT T4  ";
		$sql = $sql." WHERE T4.WordNameAll Like '%".$strBumonNm."%' ";
		$sql = $sql." AND T3.EmpCode Like '%".$strTantoCd."%' ";
		$sql = $sql." AND T3.WordName Like '%".$strTantoNm."%' ";
		$sql = $sql." AND T4.DepClass = 3 ";
		$sql = $sql." AND T4.DepCodeLow <> '00' ";
		$sql = $sql." AND T3.DepCodeAll = T4.DepCodeAll ";
		$sql = $sql." ) T3 ";
		$sql = $sql."  WHERE T2.WordNameAll Like '%".$strBumonNm."%'  ";
		$sql = $sql."  AND T1.EmpCode Like '%".$strTantoCd."%'  ";
		$sql = $sql."  AND T1.WordName Like '%".$strTantoNm."%'  ";
		$sql = $sql."  AND T2.DepClass = 3 ";
		$sql = $sql."  AND T2.DepCodeLow <> '00' ";
		$sql = $sql."  AND T1.DepCodeAll = T2.DepCodeAll ";

//		//SQLをSJISに変換(DB)
//		$sql = $module_cmn->fChangSJIS_SQL($sql);
//		//MS SQL Serverへ接続
//		$db = mssql_connect($this->gTServer, $this->gTUserid, $this->gTPasswd);
//		//MS SQL データベースを選択する
//		mssql_select_db($this->gTDbName, $db);
//
//		//実行時間の最大値を150秒にする
//		//set_time_limit(150);
//
//		//SQLの実行
//		$res = mssql_query($sql, $db);
//
//		$i = 0;
//
//		while($row = mssql_fetch_array($res)){
//
//			//201件以上あった
//			if(mssql_result($res,$n,"CNT") > 1000){
//				$aPara[0][0] = "E016";
//				return $aPara;
//			}
//
//			$aPara[$i][0] = trim($module_cmn->fChangUTF8(mssql_result($res,$i, 'SHAIN_CD')));
//			$aPara[$i][1] = $module_cmn->fChangUTF8(mssql_result($res,$i, 'SHAIN_NM'));
//			$aPara[$i][2] = $module_cmn->fChangUTF8(mssql_result($res,$i, 'SHAIN_NM_K'));
//			$aPara[$i][3] = $module_cmn->fChangUTF8(mssql_result($res,$i, 'SOSHIKI_CD'));
//			$aPara[$i][4] = $module_cmn->fChangUTF8(mssql_result($res,$i, 'SOSHIKI_NM'));
//			$aPara[$i][5] = $module_cmn->fChangUTF8(mssql_result($res,$i, 'KAISHA_CD'));
//
//			$i = $i + 1;
//		}
//
//		//結果保持用メモリを解放する
//		mssql_free_result($res);
//		//MS SQL Server への接続を閉じる
//		mssql_close();

		//クエリーを実行
		$res = NULL;
		$res = odbc_prepare($conn_ms, $sql);
		odbc_execute($res);
		
		$i = 0;
		
		while(odbc_fetch_row($res)){
			//1001件以上あった
			if($row['CNT'] > 1000){
				$aPara[0][0] = "E016";
				return $aPara;
			}
			$aPara[$i][0] = $module_cmn->fChangUTF8(trim(odbc_result($res,"SHAIN_CD")));
			$aPara[$i][1] = $module_cmn->fChangUTF8(odbc_result($res,"SHAIN_NM"));
			$aPara[$i][2] = $module_cmn->fChangUTF8(odbc_result($res,"SHAIN_NM_K"));
			$aPara[$i][3] = $module_cmn->fChangUTF8(odbc_result($res,"SOSHIKI_CD"));
			$aPara[$i][4] = $module_cmn->fChangUTF8(odbc_result($res,"SOSHIKI_NM"));
			$aPara[$i][5] = $module_cmn->fChangUTF8(odbc_result($res,"KAISHA_CD"));
			$i = $i + 1;
		}

		return $aPara;

		//クエリー結果の開放
		odbc_free_result($res);
		//コネクションのクローズ
		odbc_close($conn_ms);


		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);
		//初期に検索結果無しのパラメータを設定しておく(検索結果があれば上書き)
		$aPara[0][0] = "N006";

		$i = 0;
		while (oci_fetch($stmt)) {
			//1000件以上あった
			if(oci_result($stmt, 'CNT') > 1000){
				$aPara[0][0] = "E016";
				return $aPara;
			}
			$aPara[$i][0] = trim($module_cmn->fChangUTF8(oci_result($stmt, 'SYAIN_CD')));
			$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'SHAIN_NM'));
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'SHAIN_NM_K'));
			$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'BUMON_CD'));
			$aPara[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'BUMON_NM'));
			$aPara[$i][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'KAISHA_CD'));

			$i = $i + 1;

		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}


	//部門マスタリスト情報取得処理
	//引数
	//$strBumonCd…部門所属コード
	//$strBumonNm…部門所属名
	public function fGetBumonDataList($strKaishaCd,$strBumonCd,$strBumonNm){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();
		
		$conn_ms = odbc_connect("Driver={SQL Server};Server=".$this->gTServer.";Database=".$this->gTDbName
								,$this->gTUserid
								,$this->gTPasswd);
		
		if (!$conn_ms) {
			$e = odbc_errormsg();
			session_destroy();
			die("データベースに接続できません");
		}
		
		//検索SQL作成
		$sql = "";
		$sql = $sql."SELECT ";
		$sql = $sql."   T3.CNT AS CNT ";
		$sql = $sql."   ,T1.DepCodeAll AS SOSHIKI_CD ";
		$sql = $sql."   ,T1.WordNameAll AS SOSHIKI_NM ";
		$sql = $sql."   ,T1.CompanyCode AS KAISHA_CD ";
		$sql = $sql."   ,T2.WordName AS KAISHA_NM ";
		$sql = $sql."FROM ";
		$sql = $sql."  dbo.DGDEPMNT T1 , dbo.DGCMPANY T2 ";
		$sql = $sql."  ,(SELECT COUNT(DepCodeAll) AS CNT FROM  dbo.DGDEPMNT  ";
		$sql = $sql." WHERE WordNameAll Like '%".$strBumonNm."%' ";
		$sql = $sql." AND DepClass = 3 ";
		$sql = $sql." AND DepCodeLow <> '00' ";
		//組織コードが入力されていたら条件追加(組織コードは10桁から8桁に変換する)
		if($strBumonCd <> ""){
			$sql = $sql." AND DepCodeAll = '".$module_cmn->fChangeTimePro8($strBumonCd)."' ";
		}
		//選択されていたら条件追加(会社)
		if($strKaishaCd <> "-1" && $strKaishaCd <> ""){
			$sql = $sql." AND CompanyCode =  '".$strKaishaCd."'";
		}
		$sql = $sql." ) T3 ";
		$sql = $sql."  WHERE T1.WordNameAll Like '%".$strBumonNm."%'  ";
		$sql = $sql."  AND T1.DepClass = 3 ";
		$sql = $sql."  AND T1.DepCodeLow <> '00' ";
		$sql = $sql."  AND T1.CompanyCode = T2.CompanyCode ";
		//組織コードが入力されていたら条件追加(組織コードは10桁から8桁に変換する)
		if($strBumonCd <> ""){
			$sql = $sql." AND T1.DepCodeAll = '".$module_cmn->fChangeTimePro8($strBumonCd)."' ";
		}
		//選択されていたら条件追加(会社)
		if($strKaishaCd <> "-1" && $strKaishaCd <> ""){
			$sql = $sql." AND T1.CompanyCode =  '".$strKaishaCd."'";
		}

		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		
		//クエリーを実行
		$res = NULL;
		$res = odbc_prepare($conn_ms, $sql);
		odbc_execute($res);

		$i = 0;

		while(odbc_fetch_row($res)){
			//1001件以上あった
			if($row['CNT'] > 1000){
				$aPara[0][0] = "E016";
				return $aPara;
			}
			//会社名の設定
			$aPara[$i][0] = $module_cmn->fChangUTF8(odbc_result($res,"KAISHA_NM"));
			$aPara[$i][1] = $module_cmn->fChangeTimePro10(odbc_result($res,"SOSHIKI_CD"));
			$aPara[$i][2] = $module_cmn->fChangUTF8(trim(odbc_result($res,"SOSHIKI_NM")));

			$i = $i + 1;
		}
		
		//クエリー結果の開放
		odbc_free_result($res);
		//コネクションのクローズ
		odbc_close($conn_ms);

		return $aPara;

	}

	//SMART2ロット情報取得処理
	//引数
	public function fGetS2LotDataList($strPlanNo,$strProdCd,$strLotNo,$strDieCd,$strProdYmd,$strDelInspYmd,$strDelYmd){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//LOT情報SQL取得
		$sql = "SELECT C_PLAN_NO, C_PROD_CD, V2_LOT_NO, C_DIE_CD  ";
		$sql = $sql." FROM V_FL_LOT_INFO ";
		$sql = $sql." WHERE 1 = 1 ";

		if($strPlanNo <> ""){
			$sql = $sql." AND C_PLAN_NO = :strPlanNo ";
		}
		if($strProdCd <> ""){
			$sql = $sql." AND C_PROD_CD = :strProdCd ";
		}
		if($strLotNo <> ""){
			$sql = $sql." AND V2_LOT_NO LIKE '%' || :strLotNo || '%' ";
		}
		if($strDieCd <> ""){
			$sql = $sql." AND C_DIE_CD = :strDieCd ";
		}
		if($strProdYmd <> ""){
			$sql = $sql." AND N_PROD_YMD = :strProdYmd ";
		}
		if($strDelInspYmd <> ""){
			$sql = $sql." AND N_DEL_INSP_YMD = :strDelInspYmd ";
		}
		if($strDelYmd <> ""){
			$sql = $sql." AND N_DEL_YMD = :strDelYmd ";
		}


		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//条件セット
		if($strPlanNo <> ""){
			oci_bind_by_name($stmt, ":strPlanNo", $strPlanNo, -1);
		}
		if($strProdCd <> ""){
			oci_bind_by_name($stmt, ":strProdCd", $strProdCd, -1);
		}
		if($strLotNo <> ""){
			oci_bind_by_name($stmt, ":strLotNo", $strLotNo, -1);
		}
		if($strDieCd <> ""){
			oci_bind_by_name($stmt, ":strDieCd", $strDieCd, -1);
		}
		if($strProdYmd <> ""){
			oci_bind_by_name($stmt, ":strProdYmd", $strProdYmd, -1);
		}
		if($strDelInspYmd <> ""){
			oci_bind_by_name($stmt, ":strDelInspYmd", $strDelInspYmd, -1);
		}
		if($strDelYmd <> ""){
			oci_bind_by_name($stmt, ":strDelYmd", $strDelYmd, -1);
		}


		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);
		//初期に検索結果無しのパラメータを設定しておく(検索結果があれば上書き)
		$aPara[0][0] = "N006";

		//件数取得
		$iRows = oci_fetch_all($stmt,$results);


		$i = 0;

		//1000件以上あった
		if($iRows > 1000){
			$aPara[0][0] = "E016";
			return $aPara;
		}
		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[$i][0] = oci_result($stmt, 'C_PLAN_NO');
			$aPara[$i][1] = oci_result($stmt, 'C_PROD_CD');
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_LOT_NO'));
			$aPara[$i][3] = oci_result($stmt, 'C_DIE_CD');
			//$aPara[$i][4] = oci_result($stmt, 'N_PROD_YMD');
			//$aPara[$i][5] = oci_result($stmt, 'N_DEL_INSP_YMD');
			//$aPara[$i][6] = oci_result($stmt, 'N_DEL_YMD');

			$i = $i + 1;

		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}

	//担当者データ取得処理
	public function fGetTantoData($tanto_cd){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		$conn_ms = odbc_connect("Driver={SQL Server};Server=".$this->gTServer.";Database=".$this->gTDbName
								,$this->gTUserid
								,$this->gTPasswd);
		
		if (!$conn_ms) {
			$e = odbc_errormsg();
			session_destroy();
			die("データベースに接続できません");
		}
		
		//検索SQL作成
		$sql = "";
		$sql = $sql."SELECT ";
		$sql = $sql."   T1.EmpCode AS SHAIN_CD ";
		$sql = $sql."   ,T1.WordName AS SHAIN_NM ";
		$sql = $sql."   ,T1.ByteName AS SHAIN_NM_K ";
		$sql = $sql."   ,T1.CompanyCode AS KAISHA_CD ";
		$sql = $sql."   ,T3.WordName AS KAISHA_NM ";
		$sql = $sql."   ,T1.DepCodeAll AS SOSHIKI_CD ";
		$sql = $sql."   ,T2.WordNameAll AS SOSHIKI_NM ";
		$sql = $sql."FROM DGINDIVI T1,DGDEPMNT T2, DGCMPANY T3 ";
		$sql = $sql."  WHERE T1.DepCodeAll = T2.DepCodeAll ";
		$sql = $sql."  AND T1.CompanyCode = T3.CompanyCode ";
		$sql = $sql."  AND T2.DepCodeLow <> '00' ";
		$sql = $sql."  AND T1.RetireYMD = '0' ";
		$sql = $sql."  AND T1.EmpCode = '".$tanto_cd."'  ";

		//クエリーを実行
		$res = NULL;
		$res = odbc_prepare($conn_ms, $sql);
		odbc_execute($res);
		
		while(odbc_fetch_row($res)){
			$aPara[0] = $module_cmn->fChangUTF8(trim(odbc_result($res,"SHAIN_CD")));
			$aPara[1] = $module_cmn->fChangUTF8(trim(odbc_result($res,"SHAIN_NM")));
			$aPara[2] = $module_cmn->fChangUTF8(odbc_result($res,"SHAIN_NM_K"));
			$aPara[5] = $module_cmn->fChangUTF8(odbc_result($res,"KAISHA_NM"));
			$aPara[6] = $module_cmn->fChangUTF8(odbc_result($res,"SOSHIKI_CD"));
			$aPara[7] = $module_cmn->fChangUTF8(odbc_result($res,"SOSHIKI_NM"));
		}
		
		//クエリー結果の開放
		odbc_free_result($res);
		//コネクションのクローズ
		odbc_close($conn_ms);

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//担当者データ検索
		$sql = "SELECT  ";
		$sql = $sql."TS.N_INS_YMD AS INS_YMD ";
		$sql = $sql.",ITS.V2_SHAIN_NM  AS INS_SHAIN_NM ";
		$sql = $sql.",TS.V2_INS_PG AS INS_PG ";
		$sql = $sql.",TS.N_UPD_YMD AS UPD_YMD ";
		$sql = $sql.",UTS.V2_SHAIN_NM  AS UPD_SHAIN_NM ";
		$sql = $sql.",TS.V2_UPD_PG AS UPD_PG ";
		$sql = $sql.",TS.N_UPDATE_COUNT AS UPDATE_COUNT ";
		$sql = $sql."FROM T_MS_SHAIN TS,T_MS_SHAIN ITS,T_MS_SHAIN UTS ";
		$sql = $sql."WHERE ";
		$sql = $sql."TS.C_SHAIN_CD = '".$tanto_cd."' ";
		$sql = $sql."AND ITS.C_SHAIN_CD = TS.C_INS_SHAIN_CD ";
		$sql = $sql."AND UTS.C_SHAIN_CD = TS.C_UPD_SHAIN_CD ";
		$sql = $sql."ORDER BY TS.C_SHAIN_CD ";
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			//履歴
			$aPara[8] = $module_cmn->fChangDateTimeFormat(oci_result($stmt, 'INS_YMD'));
			$aPara[9] = $module_cmn->fChangUTF8(oci_result($stmt, 'INS_SHAIN_NM'));
			$aPara[10] = $module_cmn->fChangUTF8(oci_result($stmt, 'INS_PG'));
			$aPara[11] = $module_cmn->fChangDateTimeFormat(oci_result($stmt, 'UPD_YMD'));
			$aPara[12] = $module_cmn->fChangUTF8(oci_result($stmt, 'UPD_SHAIN_NM'));
			$aPara[13] = $module_cmn->fChangUTF8(oci_result($stmt, 'UPD_PG'));
			$aPara[14] = $module_cmn->fChangUTF8(oci_result($stmt, 'UPDATE_COUNT'));
		}


		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}



	//担当者情報取得処理(Ajax用)
	public function fGetTanDataAjax($strTanCd){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		$conn_ms = odbc_connect("Driver={SQL Server};Server=".$this->gTServer.";Database=".$this->gTDbName
								,$this->gTUserid
								,$this->gTPasswd);
		
		if (!$conn_ms) {
			$e = odbc_errormsg();
			session_destroy();
			die("データベースに接続できません");
		}
		
		//検索SQL作成
		$sql = "";
		$sql = $sql."SELECT ";
		$sql = $sql."   T1.EmpCode AS SHAIN_CD ";
		$sql = $sql."   ,T1.WordName AS SHAIN_NM ";
		$sql = $sql."   ,T1.ByteName AS SHAIN_NM_K ";
		$sql = $sql."   ,T1.CompanyCode AS KAISHA_CD ";
		$sql = $sql."   ,T3.WordName AS KAISHA_NM ";
		$sql = $sql."   ,T1.DepCodeAll AS SOSHIKI_CD ";
		$sql = $sql."   ,T2.WordNameAll AS SOSHIKI_NM ";
		$sql = $sql."FROM DGINDIVI T1,DGDEPMNT T2, DGCMPANY T3 ";
		$sql = $sql."  WHERE T1.DepCodeAll = T2.DepCodeAll ";
		$sql = $sql."  AND T1.CompanyCode = T3.CompanyCode ";
		$sql = $sql."  AND T2.DepCodeLow <> '00' ";
		$sql = $sql."  AND T1.RetireYMD = '0' ";
		$sql = $sql."  AND T1.EmpCode = '".$strTanCd."'  ";

		//クエリーを実行
		$res = NULL;
		$res = odbc_prepare($conn_ms, $sql);
		odbc_execute($res);

		while(odbc_fetch_row($res)){
			$aPara[0] = odbc_result($res,"CNT");
			$aPara[1] = $module_cmn->fChangSJIS(odbc_result($res,"SHAIN_NM"));
		}

		//クエリー結果の開放
		odbc_free_result($res);
		//コネクションのクローズ
		odbc_close($conn_ms);

		return $aPara;
	}

	//不具合情報データ一覧表示処理
	public function fFlawSearch($session,$aJoken,$intJudgeDate){

		require_once("module_common.php");

		$module_cmn = new module_common;


		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);

		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}

		$iRowNo = 0;


		//Oracleのバージョン取得
		$strOraVer = oci_server_version($conn);
		//9iかそれ以外かで判断
		//if(ereg("9i",$strOraVer)){
		//PHP5.3以降対応 2012/05/28 k.kume
		if(preg_match("/9i/",$strOraVer)){

			$bSearchOk = false;
		}else{
			$bSearchOk = true;
		}


		//SQL取得
		$sql = "SELECT ";
		$sql = $sql."FL.C_REFERENCE_NO AS RRCE_NO ";
		$sql = $sql.",MSK1.V2_KBN_MEI_NM AS PGRS_STATUS ";
		$sql = $sql.",VCI.V2_CUST_NM AS CUST_NM ";
// 2019/04/01 ED START
		//$sql = $sql.",MSK4.V2_KBN_MEI_NM AS FLAW_KBN ";
		//$sql = $sql.",FL.C_FLAW_KBN AS FLAW_KBN ";
// 2019/04/01 ED END
		$sql = $sql.",MK.区分明細名称_KJ AS FLAW_KBN ";
		$sql = $sql.",FL.V2_FLAW_CONTENTS AS FLAW_CONTENTS ";
		$sql = $sql.",FL.V2_PROD_NM AS PROD_NM ";
		$sql = $sql.",FL.V2_DRW_NO AS DRW_NO ";
//		$sql = $sql.",FL.V2_MODEL AS MODEL ";
		$sql = $sql.",FL.C_DIE_NO AS DIE_NO ";
		$sql = $sql.",FL.V2_LOT_NO AS LOT_NO ";
		$sql = $sql.",FL.C_INCIDENT_KBN AS INCIDENT_KBN_CD ";
		$sql = $sql.",FL.C_CUST_CD AS CUST_CD ";
		$sql = $sql.",FL.C_TARGET_SECTION_KBN AS TARGET_SECTION_KBN ";
		$sql = $sql.",FL.V2_INCIDENT_CD1 AS INCIDENT_CD1 ";
		$sql = $sql.",FL.V2_INCIDENT_CD2 AS INCIDENT_CD2 ";
		$sql = $sql.",FL.N_PC_AP_ANS_YMD1 AS AP_ANS_YMD1 ";
		$sql = $sql.",FL.N_PC_AP_ANS_YMD2 AS AP_ANS_YMD2 ";
		$sql = $sql.",FL.N_RETURN_YMD1 AS RETURN_YMD1 ";
		$sql = $sql.",FL.N_RETURN_YMD2 AS RETURN_YMD2 ";
		$sql = $sql.",FL.N_ANS_YMD AS ANS_YMD ";
		$sql = $sql.",FL.N_MEASURES_YMD AS MEASURES_YMD ";
		$sql = $sql.",FL.N_EFFECT_ALERT AS EFFECT_ALERT ";
		$sql = $sql.",FL.N_EFFECT_CONFIRM_YMD AS EFFECT_CONFIRM_YMD ";
		$sql = $sql.",FL.N_CUST_AP_ANS_YMD AS CUST_AP_ANS_YMD ";
		$sql = $sql.",MSK2.V2_KBN_MEI_NM AS RESULT_KBN ";
		$sql = $sql.",MSK3.V2_KBN_MEI_NM AS INCIDENT_KBN ";
		//$sql = $sql.",VCI1.V2_CUST_NM  AS INCIDENT_NM1 ";
		//$sql = $sql.",VCI2.V2_CUST_NM  AS INCIDENT_NM2 ";

		//品証の要望で略称に変更 2015/03/24 k.kume
		$sql = $sql.",VCI1.V2_CUST_NM_R  AS INCIDENT_NM1 ";
		$sql = $sql.",VCI2.V2_CUST_NM_R  AS INCIDENT_NM2 ";

		$sql = $sql.",ACH.C_ALL_ACTION_VALIDITY AS ACT_VAL ";
		$sql = $sql.",FL.C_INS_SHAIN_CD  AS INS_SHAIN_CD ";
		//品証担当者追加  2019/07/25 k.kume
		$sql = $sql.",FL.C_PC_TANTO_CD  AS C_PC_TANTO_CD ";
		
		$sql = $sql." FROM  ";
		$sql = $sql." T_TR_FLAW FL ";
		$sql = $sql." ,T_MS_FL_KBN MSK1 ";
		$sql = $sql." ,T_MS_FL_KBN MSK2 ";
		$sql = $sql." ,T_MS_FL_KBN MSK3 ";
		//$sql = $sql." ,T_MS_FL_KBN MSK4 ";		// 2019/04/01 ED
		//$sql = $sql." ,V_FL_FLAW_INFO VFI ";
		$sql = $sql." ,V_FL_CUST_INFO VCI ";
		$sql = $sql." ,V_FL_CUST_INFO VCI1 ";
		$sql = $sql." ,V_FL_CUST_INFO VCI2 ";

		$sql = $sql." ,T_TR_ACTION_H ACH ";
		$sql = $sql." ,(SELECT * FROM M_区分@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM WHERE 区分_CD = '085') MK ";
		$sql = $sql." WHERE FL.C_PROGRES_STAGE = MSK1.V2_KBN_MEI_CD ";
		$sql = $sql." AND MSK1.V2_KBN_CD = 'C01' ";
		$sql = $sql." AND TRIM(FL.C_RESULT_KBN) = MSK2.V2_KBN_MEI_CD(+) ";
		$sql = $sql." AND MSK2.V2_KBN_CD(+) = 'C09' ";
		$sql = $sql." AND FL.C_INCIDENT_KBN = MSK3.V2_KBN_MEI_CD ";
		$sql = $sql." AND MSK3.V2_KBN_CD = 'C05' ";
		//$sql = $sql." AND FL.C_FLAW_KBN = MSK4.V2_KBN_MEI_CD ";	// 2019/04/01 ED
		//$sql = $sql." AND MSK4.V2_KBN_CD = 'C15' ";				// 2019/04/01 ED
		$sql = $sql." AND FL.C_CUST_CD = VCI.C_CUST_CD ";
		$sql = $sql." AND VCI.C_CUST_CLS = 'C' ";

		$sql = $sql." AND FL.V2_INCIDENT_CD1 = VCI1.C_CUST_CD(+) ";
		$sql = $sql." AND FL.V2_INCIDENT_CD2 = VCI2.C_CUST_CD(+) ";



		//$sql = $sql." AND FL.C_FLAW_KBN = VFI.C_KBN_DETAIL_CD ";
		$sql = $sql." AND FL.C_REFERENCE_NO = ACH.C_REFERENCE_NO(+) ";
		$sql = $sql." AND FL.N_DEL_FLG = 0 ";

		$sql = $sql." AND FL.C_FLAW_KBN = MK.区分明細_CD(+) ";

		if($aJoken[0] <> "-1" && $aJoken[0] <> ""){
			$sql = $sql." AND FL.C_PROGRES_STAGE =  :sPgrsStage  ";
		}
		$sql = $sql." AND FL.C_REFERENCE_NO LIKE '%' || :sRrceNo || '%' ";
		$sql = $sql." AND FL.C_PROD_CD LIKE '%' || :sProdCd || '%' ";
		//$sql = $sql." AND FL.V2_PROD_NM LIKE '%' || :sProdNm || '%' ";
		$sql = $sql." AND FL.V2_PROD_NM LIKE '%".trim($aJoken[3])."%' ";
		$sql = $sql." AND FL.V2_DRW_NO LIKE '%' || :sDrwNo || '%' ";
//		$sql = $sql." AND FL.V2_MODEL LIKE '%' || :sModel || '%' ";
		$sql = $sql." AND FL.C_CUST_CD LIKE '%' || :sCustCd || '%' ";

		if($aJoken[19] <> ""){
			$sql = $sql." AND FL.V2_INCIDENT_CD1 LIKE '%' || :sIncCd1 || '%' ";
		}
		if($aJoken[20] <> ""){
			$sql = $sql." AND FL.V2_INCIDENT_CD2 LIKE '%' || :sIncCd2 || '%' ";
		}

		if($aJoken[5] <> "-1" && $aJoken[5] <> ""){
			$sql = $sql." AND TRIM(FL.C_FLAW_KBN) = :sFlawKbn ";
		}
		if($aJoken[6] <> "-1" && $aJoken[6] <> ""){
			$sql = $sql." AND FL.C_RECEPT_KBN = :sReceptKbn ";
		}
		if($aJoken[7] <> "-1" && $aJoken[7] <> ""){
			$sql = $sql." AND TRIM(FL.C_RESULT_KBN) = :sResultKbn ";
		}
		if($aJoken[8] <> "" ){
			$sql = $sql." AND FL.N_PC_AP_ANS_YMD1 >= :sApAnsYmdF1 ";
		}
		if($aJoken[9] <> "" ){
			$sql = $sql." AND FL.N_PC_AP_ANS_YMD1 <= :sApAnsYmdT1 ";
		}
		if($aJoken[10] <> "" ){
			$sql = $sql." AND FL.N_PC_AP_ANS_YMD2 >= :sApAnsYmdF2 ";
		}
		if($aJoken[11] <> "" ){
			$sql = $sql." AND FL.N_PC_AP_ANS_YMD2 <= :sApAnsYmdT2 ";
		}
		if($aJoken[12] <> "" ){
			$sql = $sql." AND FL.N_COMPLETE_YMD1 >= :sCmpYmdF1 ";
		}
		if($aJoken[13] <> "" ){
			$sql = $sql." AND FL.N_COMPLETE_YMD1 <= :sCmpYmdT1 ";
		}
		if($aJoken[14] <> "" ){
			$sql = $sql." AND FL.N_COMPLETE_YMD2 >= :sCmpYmdF2 ";
		}
		if($aJoken[15] <> "" ){
			$sql = $sql." AND FL.N_COMPLETE_YMD2 <= :sCmpYmdT2 ";
		}
		if($aJoken[16] <> "-1" && $aJoken[16] <> ""){
			$sql = $sql." AND TRIM(ACH.C_ALL_ACTION_VALIDITY) = :sFlawStep ";
		}
		if($aJoken[17] <> "-1" && $aJoken[17] <> ""){
			$sql = $sql." AND TRIM(FL.C_TARGET_SECTION_KBN) = :sTargetSec ";
		}
		if($aJoken[18] <> "-1" && $aJoken[18] <> ""){
			$sql = $sql." AND TRIM(FL.C_INCIDENT_KBN) = :sIncidentKbn ";
		}

		if($aJoken[22] <> "" ){
			$sql = $sql." AND FL.N_CUST_AP_ANS_YMD >= :sCustApAnsYmdF ";
		}
		if($aJoken[23] <> "" ){
			$sql = $sql." AND FL.N_CUST_AP_ANS_YMD <= :sCustApAnsYmdT ";
		}
		//$sql = $sql." ORDER BY FL.C_REFERENCE_NO ";			//2019/04/01 ED START T.FUJITA
		$sql = $sql." ORDER BY FL.C_REFERENCE_NO DESC ";		//2019/04/01 ED START T.FUJITA

//	error_log($aJoken[3], 3, "out.log");
//		echo $aJoken[6];
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの解析
		$stmt = oci_parse($conn, $sql);


		//進捗状態
		if($aJoken[0] <> "-1" && $aJoken[0] <> ""){
			oci_bind_by_name($stmt, ":sPgrsStage", $aJoken[0], -1);
		}
		oci_bind_by_name($stmt, ":sRrceNo", $aJoken[1], -1);
		oci_bind_by_name($stmt, ":sProdCd", $aJoken[2], -1);
		//$sTmpJoken = $module_cmn->fChangSJIS($aJoken[3]);
		//oci_bind_by_name($stmt, ":sProdNm", $sTmpJoken, -1);
		$sTmpJoken = $module_cmn->fChangSJIS($aJoken[4]);
		oci_bind_by_name($stmt, ":sDrwNo", $sTmpJoken, -1);
//		oci_bind_by_name($stmt, ":sModel", $aJoken[5], -1);
		if($aJoken[19] <> ""){
			oci_bind_by_name($stmt, ":sIncCd1", $aJoken[19], -1);
		}
		if($aJoken[20] <> ""){
			oci_bind_by_name($stmt, ":sIncCd2", $aJoken[20], -1);
		}
		oci_bind_by_name($stmt, ":sCustCd", $aJoken[21], -1);

		if($aJoken[5] <> "-1" && $aJoken[5] <> ""){
			oci_bind_by_name($stmt, ":sFlawKbn", $aJoken[5], -1);
		}
		if($aJoken[6] <> "-1" && $aJoken[6] <> ""){
			oci_bind_by_name($stmt, ":sReceptKbn", $aJoken[6], -1);
		}
		if($aJoken[7] <> "-1" && $aJoken[7] <> ""){
			oci_bind_by_name($stmt, ":sResultKbn", $aJoken[7], -1);
		}
		if($aJoken[8] <> "" ){
			oci_bind_by_name($stmt, ":sApAnsYmdF1", $aJoken[8], -1);
		}
		if($aJoken[9] <> "" ){
			oci_bind_by_name($stmt, ":sApAnsYmdT1", $aJoken[9], -1);
		}
		if($aJoken[10] <> "" ){
			oci_bind_by_name($stmt, ":sApAnsYmdF2", $aJoken[10], -1);
		}
		if($aJoken[11] <> "" ){
			oci_bind_by_name($stmt, ":sApAnsYmdT2", $aJoken[11], -1);
		}
		if($aJoken[12] <> "" ){
			oci_bind_by_name($stmt, ":sCmpYmdF1", $aJoken[12], -1);
		}
		if($aJoken[13] <> "" ){
			oci_bind_by_name($stmt, ":sCmpYmdT1", $aJoken[13], -1);
		}
		if($aJoken[14] <> "" ){
			oci_bind_by_name($stmt, ":sCmpYmdF2", $aJoken[14], -1);
		}
		if($aJoken[15] <> "" ){
			oci_bind_by_name($stmt, ":sCmpYmdT2", $aJoken[15], -1);
		}
		if($aJoken[16] <> "-1" && $aJoken[16] <> ""){
			oci_bind_by_name($stmt, ":sFlawStep", $aJoken[16], -1);
		}
		if($aJoken[17] <> "-1" && $aJoken[17] <> ""){
			oci_bind_by_name($stmt, ":sTargetSec", $aJoken[17], -1);
		}
		if($aJoken[18] <> "-1" && $aJoken[18] <> ""){
			oci_bind_by_name($stmt, ":sIncidentKbn", $aJoken[18], -1);
		}
		if($aJoken[22] <> "" ){
			oci_bind_by_name($stmt, ":sCustApAnsYmdF", $aJoken[22], -1);
		}
		if($aJoken[23] <> "" ){
			oci_bind_by_name($stmt, ":sCustApAnsYmdT", $aJoken[23], -1);
		}


		//初期に検索結果無しのパラメータを設定しておく(検索結果があれば上書き)
		$aPara[0][0] = "N006";


		//SQL実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		$today = date("Ymd");

		while (oci_fetch($stmt)){



			$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'RRCE_NO'));
			$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'PGRS_STATUS'));
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'CUST_NM'));
// 2019/04/01 ED START
			$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_KBN'));
			//$aPara[$i][3] = $this->fDispKbnS2("085",$module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_KBN')));
// 2019/04/01 ED END
			$aPara[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD_NM'));
			$aPara[$i][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'DRW_NO'));
//			$aPara[$i][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'MODEL'));
			$aPara[$i][7] = $module_cmn->fChangUTF8(oci_result($stmt, 'DIE_NO'));
			$aPara[$i][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'LOT_NO'));
			$aPara[$i][9] = $module_cmn->fChangUTF8(oci_result($stmt, 'CUST_AP_ANS_YMD'));
			$aPara[$i][10] = $module_cmn->fChangUTF8(oci_result($stmt, 'AP_ANS_YMD1'));
			$aPara[$i][11] = $module_cmn->fChangUTF8(oci_result($stmt, 'AP_ANS_YMD2'));
			$aPara[$i][12] = $module_cmn->fChangUTF8(oci_result($stmt, 'RESULT_KBN'));
			$aPara[$i][13] = $module_cmn->fChangUTF8(oci_result($stmt, 'INCIDENT_KBN'));
			$aPara[$i][14] = $module_cmn->fChangUTF8(oci_result($stmt, 'INCIDENT_NM1'));
			$aPara[$i][15] = $module_cmn->fChangUTF8(oci_result($stmt, 'INCIDENT_NM2'));


			if($module_cmn->fChangUTF8(oci_result($stmt, 'ACT_VAL')) == "1"){
				$aPara[$i][16] = "完了";
			}else{
				$aPara[$i][16] = "未完";
			}

			$aPara[$i][17] = $module_cmn->fChangUTF8(oci_result($stmt, 'INS_SHAIN_CD'));
			$aPara[$i][18] = $module_cmn->fChangUTF8(oci_result($stmt, 'INCIDENT_KBN_CD'));
			$aPara[$i][19] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_YMD'));

			$aPara[$i][20] = $module_cmn->fChangUTF8(oci_result($stmt, 'RETURN_YMD1'));
			$aPara[$i][21] = $module_cmn->fChangUTF8(oci_result($stmt, 'RETURN_YMD2'));

			$aPara[$i][22] = $module_cmn->fChangUTF8(oci_result($stmt, 'TARGET_SECTION_KBN'));
			$aPara[$i][23] = $module_cmn->fChangUTF8(oci_result($stmt, 'CUST_CD'));
			$aPara[$i][24] = $module_cmn->fChangUTF8(oci_result($stmt, 'INCIDENT_CD1'));
			$aPara[$i][25] = $module_cmn->fChangUTF8(oci_result($stmt, 'INCIDENT_CD2'));
			$aPara[$i][26] = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_CONTENTS'));

			$aPara[$i][27] = $module_cmn->fChangUTF8(oci_result($stmt, 'EFFECT_ALERT'));
			$aPara[$i][28] = $module_cmn->fChangUTF8(oci_result($stmt, 'EFFECT_CONFIRM_YMD'));

			//セル色
			//進捗状態が解決済の場合はグレー
			if($aPara[$i][1] == "解決済" ){
				$aPara[$i][30] = "gray";
			}else{
				$aPara[$i][30] = "";
			}

			//客先指定回答日が入っていて回答日が未入力
			if($aPara[$i][9] <> 0 && $aPara[$i][19] == 0 ){
				//客先指定回答日が本日を過ぎている場合はピンク
				if($today > $aPara[$i][9]){
					$aPara[$i][31] = "limit";
				}
				//回答期限日数よりも少ない場合はイエロー
				elseif($aPara[$i][9] <= $intJudgeDate){
					$aPara[$i][31] = "near";
				}
			}

			//品証指定回答日(社内)が入っていて返却日が未入力
			if($aPara[$i][10] <> 0 && $aPara[$i][20] == 0){
				//本日を過ぎている場合はピンク
				if($today > $aPara[$i][10]){
					$aPara[$i][32] = "limit";
				}
				//回答期限日数よりも少ない場合はイエロー
				elseif($aPara[$i][10] <= $intJudgeDate){
					$aPara[$i][32] = "near";
				}
			}
			//品証指定回答日(協力工場)が入っていて返却日が未入力
			if($aPara[$i][11] <> 0 && $aPara[$i][21] == 0){
				//本日を過ぎている場合はピンク
				if($today > $aPara[$i][11]){
					$aPara[$i][33] = "limit";
				}
				//回答期限日数よりも少ない場合はイエロー
				elseif($aPara[$i][11] <= $intJudgeDate){
					$aPara[$i][33] = "near";
				}
			}

			$aPara[$i][34] = $module_cmn->fChangUTF8(oci_result($stmt, 'MEASURES_YMD'));

			//2019/07/24 品証担当者追加 k.kume
			$aPara[$i][35] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PC_TANTO_CD'));
			

			$i = $i + 1;

		}

		//1000件以上あった
		if(count($aPara) > 1000){
			$aPara[0][0] = "E016";
			return $aPara;
		}

		//リソース開放
		oci_free_statement($stmt);

		//Oracle接続切断
		oci_close($conn);

		return $aPara;

	}




	//FlawLess担当者マスタ一覧表示処理
	public function fTantoSearch($aJoken){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//初期に検索結果無しのパラメータを設定しておく(検索結果があれば上書き)
		$aPara[0][0] = "N006";

		//就業検索結果保管用配列
		$aParaSs = array();

		$conn_ms = odbc_connect("Driver={SQL Server};Server=".$this->gTServer.";Database=".$this->gTDbName
								,$this->gTUserid
								,$this->gTPasswd);

		if (!$conn_ms) {
			$e = odbc_errormsg();
			session_destroy();
			die("データベースに接続できません");
		}
		
		//検索SQL作成
		$sql = "";
		$sql = $sql."SELECT ";
		$sql = $sql."   T1.EmpCode AS SHAIN_CD ";
		$sql = $sql."   ,T1.WordName AS SHAIN_NM ";
		$sql = $sql."   ,T1.CompanyCode AS KAISHA_CD ";
		$sql = $sql."   ,T2.DepCodeAll AS SOSHIKI_CD ";
		$sql = $sql."   ,T2.WordNameAll AS SOSHIKI_NM ";
		$sql = $sql."   ,T3.WordName AS KAISHA_NM ";
		$sql = $sql."FROM ";
		$sql = $sql."  dbo.DGINDIVI T1,dbo.DGDEPMNT T2,DGCMPANY T3  ";
		$sql = $sql."WHERE T1.DepCodeAll = T2.DepCodeAll  ";
		$sql = $sql."  AND T1.CompanyCode = T3.CompanyCode  ";
		$sql = $sql."  AND T1.RetireYMD = 0 ";
		$sql = $sql."  AND T1.EmpCode Like '%".$aJoken[0]."%'  ";
		$sql = $sql."  AND T1.WordName Like '%".$aJoken[1]."%'  ";
		if($aJoken[2] <> ""){
			$sql = $sql."  AND T2.DepCodeAll = '".$module_cmn->fChangeTimePro8($aJoken[2])."'  ";
		}
		$sql = $sql."  AND T2.WordNameAll Like '%".$aJoken[3]."%' ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//クエリーを実行
		$res = NULL;
		$res = odbc_prepare($conn_ms, $sql);
		odbc_execute($res);
		
		$n = 0;

		while(odbc_fetch_row($res)){
			$aParaSs[$n][0] = $module_cmn->fChangUTF8(trim(odbc_result($res,"SHAIN_CD")));
			$aParaSs[$n][1] = $module_cmn->fChangUTF8(odbc_result($res,"SHAIN_NM"));
			$aParaSs[$n][2] = $module_cmn->fChangUTF8(odbc_result($res,"KAISHA_NM"));
			$aParaSs[$n][3] = $module_cmn->fChangeTimePro10(odbc_result($res,"SOSHIKI_CD"));
			$aParaSs[$n][4] = $module_cmn->fChangUTF8(odbc_result($res,"SOSHIKI_NM"));
			$aParaSs[$n][5] = $module_cmn->fChangUTF8(odbc_result($res,"KAISHA_CD"));
			$n = $n + 1;
		}

		//クエリー結果の開放
		odbc_free_result($res);
		//コネクションのクローズ
		odbc_close($conn_ms);
		
		//就業に1件以上存在したら全社購買も検索
		if(count($aParaSs) > 0){

			//Oracleへの接続の確立
			//OCILogon(ユーザ名,パスワード,データベース名)
			$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
			if (!$conn) {
				$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
				session_destroy();
				die("データベースに接続できません");
			}
			$strMsg = "";

			//部門データ検索
			$sql = "SELECT ";
			$sql = $sql." TS.C_SHAIN_CD ";
			$sql = $sql." ,TS.V2_SHAIN_NM ";
			$sql = $sql." ,TS.N_DEL_FLG ";
			$sql = $sql." FROM T_MS_SHAIN TS  ";
			//$sql = $sql." WHERE TS.N_DEL_FLG = 0";
			//$sql = $sql." AND TB.N_DEL_FLG = 0";
			//$sql = $sql." AND TS.C_BUMON_CD = TB.C_BUMON_CD";

			//SQLの実行
			$stmt = oci_parse($conn, $sql);
			oci_execute($stmt,OCI_DEFAULT);
			//全社購買側の部門データ結果保存用配列
			$aParaOra = array();

			while (oci_fetch($stmt)) {

				$aParaOra += array(trim(oci_result($stmt, 'C_SHAIN_CD')) => oci_result($stmt, 'N_DEL_FLG'));

			}

			oci_free_statement($stmt);
			oci_close($conn);

		}


		//就業検索結果と全社購買結果をマッチング
		$m = 0;
		$n = 0;

		while($m < count($aParaSs)){


			$aValue = array();
			//全社購買の配列から検索してヒットしたら集計部門を格納
			foreach( $aParaOra as $value ){
				//指定したキーの値を配列に格納
				$aValue[] = $aParaOra[$aParaSs[$m][0]];
			}


			//登録未登録の条件が選択されていたら
			if($aJoken[4] <> "-1" && $aJoken[4] <> ""){
				//未登録が選択された場合
				if($aJoken[4] == "0"){
					//全社購買側に未登録のデータだけを格納
					if($aValue[0] == ""){
						$aPara[$n][0] = $aParaSs[$m][0];
						$aPara[$n][1] = $aParaSs[$m][1];
						$aPara[$n][2] = $aParaSs[$m][2];
						$aPara[$n][3] = $aParaSs[$m][3];
						$aPara[$n][4] = $aParaSs[$m][4];
						$aPara[$n][5] = "未登録";
						$n = $n + 1;
					}
				}elseif($aJoken[4] == "1"){	//登録済み分の検索

					//全社購買側に登録済のデータだけを格納
					if($aValue[0] <> ""){
						$aPara[$n][0] = $aParaSs[$m][0];
						$aPara[$n][1] = $aParaSs[$m][1];
						$aPara[$n][2] = $aParaSs[$m][2];
						$aPara[$n][3] = $aParaSs[$m][3];
						$aPara[$n][4] = $aParaSs[$m][4];
						$aPara[$n][5] = "登録";
						$n = $n + 1;
					}


				}
			}else{
				$aPara[$n][0] = $aParaSs[$m][0];
				$aPara[$n][1] = $aParaSs[$m][1];
				$aPara[$n][2] = $aParaSs[$m][2];
				$aPara[$n][3] = $aParaSs[$m][3];
				$aPara[$n][4] = $aParaSs[$m][4];

				//全社購買でヒットしない場合は「未登録」とセット
				if($aValue[0] == ""){
					$aPara[$n][5] = "未登録";
				}elseif($aValue[0] == "1"){
					$aPara[$n][5] = "無効";
				}else{
					$aPara[$n][5] = "有効";
				}


				$n = $n + 1;
			}


			$m = $m + 1;

		}

		//1001件以上あったらエラー
		if(count($aPara) > 1000){
			$aPara[0][0] = "E016";
		}

		return $aPara;

	}

	//SMART2担当者マスタ一覧表示処理
	public function fS2TantoSearch($aJoken){

		require_once("module_common.php");

		$module_cmn = new module_common;


		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";


		//部門データ検索
		$sql = "SELECT ";
		$sql = $sql." C_TANTO_CD, V2_TANTO_NM, V2_TANTO_NM_K, C_BUMON_CD, V2_BUMON_NM,V2_BUMON_NM_R ";
		$sql = $sql." FROM V_FL_TANTO_INFO  ";
		$sql = $sql." where 1 = 1 ";

		if($aJoken[0] <> ""){
			$sql = $sql." AND C_TANTO_CD LIKE '%' || :strTantoCd || '%'" ;
		}
		if($aJoken[1] <> ""){
			$sql = $sql." AND V2_TANTO_NM LIKE '%' || :strTantoNm || '%'" ;
		}
		if($aJoken[3] <> ""){
			$sql = $sql." AND V2_BUMON_NM LIKE '%' || :strBumonNm || '%'" ;
		}

		//SQLの実行
		$stmt = oci_parse($conn, $sql);

		if($aJoken[0] <> ""){
			oci_bind_by_name($stmt, ":strTantoCd", $aJoken[0], -1);
		}
		if($aJoken[1] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS_SQL($aJoken[1]);
			oci_bind_by_name($stmt, ":strTantoNm", $sTmpJoken, -1);
		}
		if($aJoken[3] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS_SQL($aJoken[3]);
			oci_bind_by_name($stmt, ":strBumonNm", $sTmpJoken, -1);
		}

		oci_execute($stmt,OCI_DEFAULT);
		//全社購買側の部門データ結果保存用配列
		$aPara = array();
		//初期に検索結果無しのパラメータを設定しておく(検索結果があれば上書き)
		$aPara[0][0] = "N006";

		$i = 0;

		while (oci_fetch($stmt)) {

			$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_TANTO_CD'));
			$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_TANTO_NM'));
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_TANTO_NM_K'));
			$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_BUMON_CD'));
			$aPara[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_BUMON_NM'));

			$i = $i + 1;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		//201件以上あったらエラー
		if(count($aPara) > 200){
			$aPara[0][0] = "E016";
		}

		return $aPara;

	}


	//取引先メール配信マスタからメールアドレス取得
	//引数1･･･対象部門(F or M or K)
	//引数2･･･送信先対象CD


	public function fMailAddressGet($strBumonCd,$strTaishoCd){

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

			$strPara = "";

			//登録SQL作成
			$sql = "";
			$sql = $sql." SELECT T1.V2_MAIL_ADDRESS AS MAIL_ADDRESS ";
			$sql = $sql." FROM T_MS_CUST_MAIL T1,V_FL_CUST_INFO T2 ";
			$sql = $sql." WHERE T1.C_CUST_CD IN ";
			$sql = $sql." ('FL00A' ";								//全てのメール通知先

			//ロット有効性評価通知
			if($strTaishoCd == "VALI"){
				$sql = $sql." ,'FL0V".$strBumonCd."'";					//部門別のメール通知先
			}
			//環境・紛争鉱物関係通知
			elseif($strTaishoCd == "ENV"){
				$sql = $sql." ,'FL0EA'";								//環境用
			}
			//2019/05/13 ADD START
			//赤伝緑伝情報データ登録通知
			elseif($strTaishoCd == "TRBL"){
				switch($_POST['cmbTargetSection_KBN']){
					case 'F':
						$sql = $sql." ,'FL0TF'";
						break;
					case 'M':
						$sql = $sql." ,'FL0TM'";
						break;
					case 'K':
						$sql = $sql." ,'FL0TK'";
						break;
					default:
						$sql = $sql." ,,'FL0TF','FL0TM','FL0TK'";
						break;
				}
			////2019/08/01 ADD START
			//品質改善報告書・協力工場不良品連絡書の期限切れのメール配信用
			}elseif($strTaishoCd == "TRBL2"){
				switch($_POST['cmbTargetSection_KBN']){
					case 'F':
						$sql = $sql." ,'FL2TF'";
						break;
					case 'M':
						$sql = $sql." ,'FL2TM'";
						break;
					case 'K':
						$sql = $sql." ,'FL2TK'";
						break;
					default:
						$sql = $sql." ,'FL2TF','FL2TM','FL2TK'";
						break;
				}
			////2019/08/01 ADD END
			//2019/05/13 ADD END
			}
			else{
				//期限切れ通知
				$sql = $sql." ,'FL00".$strBumonCd."'";					//部門別のメール通知先
				$sql = $sql." ,'".$strTaishoCd."'";						//得意先担当へのメール通知先
			}

//			$sql = $sql." ,'".$strIncdentCd1."'";					//発行先CD(社内)へのメール通知先
//			$sql = $sql." ,'".$strIncdentCd2."'";					//発行先CD(協工)へのメール通知先
			$sql = $sql."  )";
			$sql = $sql." AND T1.C_CUST_CD = T2.C_CUST_CD(+) ";
			$sql = $sql." AND T1.V2_MAIL_ADDRESS IS NOT NULL ";

			//SQLの解析
			$stmt = oci_parse($conn, $sql);

			//SQLの実行
			oci_execute($stmt,OCI_DEFAULT);

			$i = 0;
			while (oci_fetch($stmt)) {

				if($i == 0){
					$strPara = $strPara."".$module_cmn->fChangUTF8(oci_result($stmt, 'MAIL_ADDRESS'));
				}else{
					$strPara = $strPara.",".$module_cmn->fChangUTF8(oci_result($stmt, 'MAIL_ADDRESS'));
				}

				$i++;

			}
			oci_free_statement($stmt);
			oci_close($conn);

			return str_replace("\r\n","",$strPara);

		}catch(Exception $e){

			return -1;
		}
	}

	//eValueNSからメールアドレス取得
	//引数1･･･登録者Cd
	public function fMailAddressGetNS($strTantoCd){

		require_once("module_common.php");

		$module_cmn = new module_common;

		try{

			$strPara = "";

			$conn_ms = odbc_connect("Driver={SQL Server};Server=".$this->gNSServer.";Database=".$this->gNSDbName
									,$this->gNSUserid
									,$this->gNSPasswd);
			
			if (!$conn_ms) {
				$e = odbc_errormsg();
				session_destroy();
				die("データベースに接続できません");
			}
			
			//検索SQL作成
			$sql = "";
			$sql = $sql."SELECT ";
			$sql = $sql." USER_MailAddr AS MAIL_ADS ";
			$sql = $sql." FROM eValue.USER_MST  ";
			$sql = $sql." WHERE USER_UserNum = '".$strTantoCd."'  ";

			//クエリーを実行
			$res = NULL;
			$res = odbc_prepare($conn_ms, $sql);
			odbc_execute($res);

			$i = 0;

			if($res){
				while(odbc_fetch_row($res)){
					if($i == 0){
						$strPara = $strPara."".$module_cmn->fChangUTF8(odbc_result($res,"MAIL_ADS"));
					}else{
						$strPara = $strPara.",".$module_cmn->fChangUTF8(odbc_result($res,"MAIL_ADS"));
					}
					$i++;
				}
				
				//クエリー結果の開放
				odbc_free_result($res);
			}

			//コネクションのクローズ
			odbc_close($conn_ms);

			return str_replace("\r\n","",$strPara);

		}catch(Exception $e){

			return -1;
		}
	}


	//不具合データ取得処理
	public function fGetFlawData($Reference_No){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//不具合データ検索
		$sql = "";
		$sql = $sql."select FLAW.C_REFERENCE_NO,";			//整理NO
		$sql = $sql."       FLAW.C_PROGRES_STAGE,";			//進捗状態
		$sql = $sql."       FLAW.C_CUST_CD,";     			//顧客CD
		$sql = $sql."       TRIM(CUST1.V2_CUST_NM) AS V2_CUST_NM,";    			//顧客名
		$sql = $sql."       FLAW.V2_CUST_OFFICER,";			//顧客担当者
		$sql = $sql."       FLAW.V2_CUST_MANAGE_NO,";		//顧客管理NO
		$sql = $sql."       FLAW.C_CUST_CONTACT_KBN,";		//客先よりの連絡方法
		$sql = $sql."       FLAW.C_RECEPT_KBN,";  			//受付区分
		$sql = $sql."       FLAW.C_FLAW_KBN,";    			//不具合区分
		$sql = $sql."       FLAW.N_TARGET_QTY,";  			//対象数量
		$sql = $sql."       FLAW.C_TARGET_SECTION_KBN,";	//対象部門
		$sql = $sql."       FLAW.C_PROD_CD,";     			//製品CD
		$sql = $sql."       FLAW.V2_PROD_NM,";    			//製品名
		$sql = $sql."       FLAW.V2_DRW_NO,";     			//仕様番号
//		$sql = $sql."       FLAW.V2_MODEL,";      			//型式
		$sql = $sql."       FLAW.C_DIE_NO,";      			//金型番号
		$sql = $sql."       FLAW.V2_LOT_NO,";     			//ロットNO
//		$sql = $sql."       FLAW.C_PRODUCT_KA_CD,";			//生産担当部門CD
		$sql = $sql."       FLAW.C_PRODUCT_OUT_KA_CD,";		//生産流出
		$sql = $sql."       FLAW.C_CHECK_OUT_KA_CD1,";		//検査流出1
		$sql = $sql."       FLAW.C_CHECK_OUT_KA_CD2,";		//検査流出2
		$sql = $sql."       FLAW.V2_FLAW_CONTENTS,";		//不具合内容
		$sql = $sql."       FLAW.N_RETURN_QTY,";  			//返却数量
		$sql = $sql."       FLAW.N_BAD_QTY,";     			//不良数量
		$sql = $sql."       FLAW.C_RETURN_DISPOSAL,";		//返却品処理
		$sql = $sql."       FLAW.C_RESULT_KBN,";  			//結果区分
		$sql = $sql."       FLAW.N_CUST_AP_ANS_YMD,";		//顧客指定回答日
		$sql = $sql."       FLAW.N_ANS_YMD,";     			//回答日
		$sql = $sql."       FLAW.N_MEASURES_YMD,";     		//対策日
		$sql = $sql."       FLAW.N_EFFECT_ALERT,";     		//効果確認通知有無
		$sql = $sql."       FLAW.N_EFFECT_CONFIRM_YMD,";    //対策効果確認日
		$sql = $sql."       FLAW.C_ANS_TANTO_CD,";			//回答者CD
		$sql = $sql."       TANTO2.V2_TANTO_NM as KAITAN,"; //回答者名
		$sql = $sql."       FLAW.N_ISSUE_YMD1,";   			//発行日(不具合)
		$sql = $sql."       FLAW.N_ISSUE_YMD2,";   			//発行日(品質異常)
		$sql = $sql."       FLAW.N_ISSUE_YMD3,";   			//発行日(不良品)

		$sql = $sql."       FLAW.C_INCIDENT_KBN,";			//発行先区分
		$sql = $sql."       FLAW.V2_INCIDENT_CD1,";			//発行先名称(社内)
		//$sql = $sql."       TRIM(CUST2.V2_CUST_NM) as V2_INCIDENT_NM1,"; 	//発行先名称(社内)
		//品証の要望により略称に変更 2015/03/24 k.kume
		$sql = $sql."       TRIM(CUST2.V2_CUST_NM_R) as V2_INCIDENT_NM1,"; 	//発行先名称(社内)
		$sql = $sql."       FLAW.N_PC_AP_ANS_YMD1,";		//品証指定回答日(社内)
		$sql = $sql."       FLAW.N_RETURN_YMD1,";  			//返却日(社内)
		$sql = $sql."       FLAW.N_COMPLETE_YMD1,";			//完結日(社内)
		$sql = $sql."       FLAW.C_CONFIRM_TANTO_CD1,";		//確認者CD(社内)
		$sql = $sql."       TANTO3.V2_TANTO_NM as KAKUTAN1,"; //確認者名(社内)
		$sql = $sql."       FLAW.V2_REMARKS1,";     			//備考(社内)
		$sql = $sql."       FLAW.V2_PRODUCT_OFFICER_NM,";	//生産担当者(社内)
		$sql = $sql."       FLAW.V2_INCIDENT_CD2,";			//発行先名称(社外)
		//$sql = $sql."       TRIM(CUST3.V2_CUST_NM) as V2_INCIDENT_NM2,"; 	//発行先名称(社外)
		//品証の要望により略称に変更 2015/03/24 k.kume
		$sql = $sql."       TRIM(CUST3.V2_CUST_NM_R) as V2_INCIDENT_NM2,"; 	//発行先名称(社外)
		$sql = $sql."       FLAW.N_PC_AP_ANS_YMD2,";		//品証指定回答日(社外)
		$sql = $sql."       FLAW.N_RETURN_YMD2,";  			//返却日(社外)
		$sql = $sql."       FLAW.N_COMPLETE_YMD2,";			//完結日(社外)
		$sql = $sql."       FLAW.C_CONFIRM_TANTO_CD2,";		//確認者CD(社外)
		$sql = $sql."       TANTO4.V2_TANTO_NM as KAKUTAN2,"; //確認者名(社外)
		$sql = $sql."       FLAW.V2_REMARKS2,";     			//備考(社外)
		$sql = $sql."       KBN1.V2_KBN_MEI_NM as C_PROGRES_STAGE_NM, ";   //進捗状態
		$sql = $sql."       FLAW.N_UPDATE_COUNT, ";			//更新回数
		//$sql = $sql."       FLAW.N_INS_YMD, ";				//登録日時
		//品証からの依頼で登録日→連絡受理日に変更 2016/09/02 k.kume
		$sql = $sql."       FLAW.N_CONTACT_ACCEPT_YMD, ";			//連絡受理日
		$sql = $sql."       FLAW.C_INS_SHAIN_CD, ";			//登録者CD

		//品証の要望により異常品暫定処置追加 2015/06/26 k.kume
		$sql = $sql."       FLAW.C_QUICK_FIX_KBN,";		//異常品暫定処置CD

		//品証の要望により品証担当者追加 2019/07/06 k.kume
		$sql = $sql."       FLAW.C_PC_TANTO_CD,";		//品証担当者CD
		$sql = $sql."       TANTO5.V2_TANTO_NM as PCTAN "; //品証担当者名

		$sql = $sql."  from T_TR_FLAW FLAW,";
		$sql = $sql."       V_FL_CUST_INFO CUST1,";
		$sql = $sql."       V_FL_CUST_INFO CUST2,";
		$sql = $sql."       V_FL_CUST_INFO CUST3,";

		//$sql = $sql."       V_FL_TANTO_INFO TANTO1,";
		$sql = $sql."       V_FL_TANTO_INFO TANTO2,";
		$sql = $sql."       V_FL_TANTO_INFO TANTO3,";
		$sql = $sql."       V_FL_TANTO_INFO TANTO4,";
		$sql = $sql."       V_FL_TANTO_INFO TANTO5,";
		$sql = $sql."       T_MS_FL_KBN KBN1 ";

		$sql = $sql." where FLAW.C_CUST_CD = CUST1.C_CUST_CD(+)";
		$sql = $sql."   and TRIM(FLAW.V2_INCIDENT_CD1) = CUST2.C_CUST_CD(+)";
		$sql = $sql."   and TRIM(FLAW.V2_INCIDENT_CD2) = CUST3.C_CUST_CD(+)";
		$sql = $sql."   and FLAW.C_ANS_TANTO_CD = TANTO2.C_TANTO_CD(+)";
		$sql = $sql."   and FLAW.C_CONFIRM_TANTO_CD1 = TANTO3.C_TANTO_CD(+)";
		$sql = $sql."   and FLAW.C_CONFIRM_TANTO_CD2 = TANTO4.C_TANTO_CD(+)";
		$sql = $sql."   and FLAW.C_PC_TANTO_CD = TANTO5.C_TANTO_CD(+)";
		$sql = $sql."   and FLAW.C_PROGRES_STAGE = KBN1.V2_KBN_MEI_CD(+)";
		$sql = $sql."   and KBN1.V2_KBN_CD(+) = 'C01'";

		$sql = $sql." AND FLAW.C_REFERENCE_NO = '".$Reference_No."' ";


		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$aPara[0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));			//整理NO
			$aPara[1] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROGRES_STAGE'));			//進捗状態
			$aPara[2] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD'));     			//顧客CD
			$aPara[3] = trim(mb_convert_kana($module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM')),"s"));	//顧客名
			$aPara[4] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_OFFICER'));			//顧客担当者
			$aPara[5] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CONTACT_KBN'));		//客先よりの連絡方法
			$aPara[6] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_RECEPT_KBN'));  			//受付区分
			$aPara[7] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN'));    			//不具合区分
			$aPara[8] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_TARGET_QTY'));  			//対象数量
			$aPara[9] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_TARGET_SECTION_KBN'));		//対象部門
			$aPara[10] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));     			//製品CD
			$aPara[11] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));    			//製品名
			$aPara[12] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));     			//仕様番号
//			$aPara[13] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_MODEL'));      			//型式
			$aPara[14] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_DIE_NO'));      			//金型番号
			$aPara[15] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_LOT_NO'));     			//ロットNO
			$aPara[18] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INCIDENT_CD1'));			//発行先CD(社内)
			$aPara[19] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INCIDENT_CD2'));			//発行先CD(協工)
			$aPara[20] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PRODUCT_OFFICER_NM'));	//生産担当者
			$aPara[21] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PRODUCT_OUT_KA_CD'));		//生産流出
			$aPara[22] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CHECK_OUT_KA_CD1'));		//検査流出1
			$aPara[23] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_FLAW_CONTENTS'));		//不具合内容
			$aPara[24] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_QTY'));  			//返却数量
			$aPara[25] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_BAD_QTY'));     			//不良数量
			$aPara[26] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_RETURN_DISPOSAL'));		//返却品処理
			$aPara[27] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_RESULT_KBN'));  			//結果区分
			$aPara[28] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_CUST_AP_ANS_YMD'));		//顧客指定回答日
//			$aPara[29] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_CUST_ACCEPT_YMD'));		//顧客了承回答日
			$aPara[30] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_YMD'));     			//回答日
			$aPara[31] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_ANS_TANTO_CD'));			//回答者CD
			$aPara[32] = $module_cmn->fChangUTF8(oci_result($stmt, 'KAITAN')); 					//回答者名
			$aPara[33] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ISSUE_YMD1'));   			//発行日(不具合)

			$aPara[16] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_INCIDENT_KBN'));			//発行先区分
			$aPara[17] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INCIDENT_NM1'));			//発行先名称(社内)
			$aPara[34] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PC_AP_ANS_YMD1'));		//品証指定回答日(社内)
			$aPara[35] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_YMD1'));  			//返却日(社内)
			$aPara[36] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_COMPLETE_YMD1'));			//完結日(社内)
			$aPara[37] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CONFIRM_TANTO_CD1'));		//確認者CD(社内)
			$aPara[38] = $module_cmn->fChangUTF8(oci_result($stmt, 'KAKUTAN1')); 				//確認者名(社内)
			$aPara[39] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_REMARKS1'));     		//備考(社内)
			$aPara[40] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROGRES_STAGE_NM'));     	//進捗状態
			$aPara[41] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_UPDATE_COUNT'));     		//更新回数
			//$aPara[42] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_INS_YMD'));     		//登録日時
			//品証からの依頼で登録日→連絡受理日(追加)に変更 2016/09/02 k.kume
			$aPara[42] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_CONTACT_ACCEPT_YMD'));   	//連絡受理日

			$aPara[43] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_MANAGE_NO'));		//顧客管理NO
			$aPara[44] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ISSUE_YMD2'));   			//発行日(品質異常)
			$aPara[45] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ISSUE_YMD3'));   			//発行日(不良)
			$aPara[46] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CHECK_OUT_KA_CD2'));		//検査流出2
			$aPara[47] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_INS_SHAIN_CD'));			//登録者CD
			$aPara[48] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INCIDENT_NM2'));			//発行先名称(社外)
			$aPara[49] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PC_AP_ANS_YMD2'));		//品証指定回答日(社外)
			$aPara[50] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_YMD2'));  			//返却日(社外)
			$aPara[51] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_COMPLETE_YMD2'));			//完結日(社外)
			$aPara[52] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CONFIRM_TANTO_CD2'));		//確認者CD(社外)
			$aPara[53] = $module_cmn->fChangUTF8(oci_result($stmt, 'KAKUTAN2')); 				//確認者名(社外)
			$aPara[54] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_REMARKS2'));     		//備考(社外)
			$aPara[55] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_EFFECT_ALERT'));     		//効果確認通知有無
			$aPara[56] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_EFFECT_CONFIRM_YMD'));    //対策効果確認日
			$aPara[57] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_MEASURES_YMD'));    		//対策日

			$aPara[58] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_QUICK_FIX_KBN'));    		//異常品暫定処置CD

			$aPara[59] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PC_TANTO_CD'));		//品証担当者CD
			$aPara[60] = $module_cmn->fChangUTF8(oci_result($stmt, 'PCTAN')); 			//品証担当者名

		}


		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}

	//不具合対策ヘッダデータ取得処理
	public function fGetFlawStepHData($strRrceNo){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//不具合データ検索
		$sql = "";
		$sql = $sql."select FLAW.C_REFERENCE_NO,";			//整理NO
		$sql = $sql."       FLAW.C_PROGRES_STAGE,";			//進捗状態
		$sql = $sql."       FLAW.C_CUST_CD,";     			//顧客CD
//		$sql = $sql."       CUST1.V2_CUST_NM,";    			//顧客名
//		$sql = $sql."       FLAW.V2_CUST_OFFICER,";			//顧客担当者
//		$sql = $sql."       FLAW.V2_CUST_MANAGE_NO,";		//顧客管理NO
//		$sql = $sql."       FLAW.C_CUST_CONTACT_KBN,";		//客先よりの連絡方法
//		$sql = $sql."       FLAW.C_RECEPT_KBN,";  			//受付区分
//		$sql = $sql."       FLAW.C_FLAW_KBN,";    			//不具合区分
//		$sql = $sql."       FLAW.N_TARGET_QTY,";  			//対象数量
//		$sql = $sql."       FLAW.C_TARGET_SECTION_KBN,";	//対象部門
		$sql = $sql."       FLAW.C_PROD_CD,";     			//製品CD
		$sql = $sql."       FLAW.V2_PROD_NM,";    			//製品名
		$sql = $sql."       FLAW.V2_DRW_NO,";     			//図番
		$sql = $sql."       FLAW.V2_MODEL,";      			//型式
//		$sql = $sql."       FLAW.C_DIE_NO,";      			//金型番号
		$sql = $sql."       FLAW.V2_LOT_NO,";     			//ロットNO
//		$sql = $sql."       FLAW.C_INCIDENT_KBN,";			//発行先
//		$sql = $sql."       FLAW.V2_INCIDENT_NM,";			//発行先名称
//		$sql = $sql."       FLAW.C_PRODUCT_KA_CD,";			//生産担当部門CD
//		$sql = $sql."       CUST2.V2_CUST_NM as SEITAN,"; 	//生産担当部門名
//		$sql = $sql."       FLAW.V2_PRODUCT_OFFICER_NM,";	//生産担当者
		$sql = $sql."       FLAW.V2_FLAW_CONTENTS,";		//不具合内容
//		$sql = $sql."       FLAW.N_RETURN_QTY,";  			//返却数量
//		$sql = $sql."       FLAW.N_BAD_QTY,";     			//不良数量
//		$sql = $sql."       FLAW.C_RETURN_DISPOSAL,";		//返却品処理
//		$sql = $sql."       FLAW.C_RESULT_KBN,";  			//結果区分
		$sql = $sql."       FLAW.N_PC_AP_ANS_YMD1,";		//品証指定回答日(社内)
		$sql = $sql."       FLAW.N_PC_AP_ANS_YMD2,";		//品証指定回答日(協工)
//		$sql = $sql."       FLAW.N_CUST_AP_ANS_YMD,";		//顧客指定回答日
//		$sql = $sql."       FLAW.N_CUST_ACCEPT_YMD,";		//顧客了承回答日
//		$sql = $sql."       FLAW.N_ANS_YMD,";     			//回答日
//		$sql = $sql."       FLAW.C_ANS_TANTO_CD,";			//回答者CD
//		$sql = $sql."       TANTO2.V2_TANTO_NM as KAITAN,"; //回答者名
//		$sql = $sql."       FLAW.N_ISSUE_YMD1,";   			//発行日(不具合)
//		$sql = $sql."       FLAW.N_ISSUE_YMD2,";   			//発行日(品質異常)
//		$sql = $sql."       FLAW.N_ISSUE_YMD3,";   			//発行日(不良品)
		$sql = $sql."       FLAW.N_RETURN_YMD1,";  			//返却日(社内)
		$sql = $sql."       FLAW.N_RETURN_YMD2,";  			//返却日(協工)
//		$sql = $sql."       FLAW.N_COMPLETE_YMD,";			//完結日
//		$sql = $sql."       FLAW.C_CONFIRM_TANTO_CD,";		//確認者CD
//		$sql = $sql."       TANTO3.V2_TANTO_NM as KAKUTAN,"; //確認者名
//		$sql = $sql."       FLAW.V2_REMARKS,";     			//備考
//		$sql = $sql."       KBN1.V2_KBN_MEI_NM as C_PROGRES_STAGE_NM, ";   //進捗状態
		$sql = $sql."       FLAW.N_UPDATE_COUNT AS FLAW_UPCNT, ";			//更新回数(不具合情報)
//		$sql = $sql."       FLAW.N_INS_YMD, ";				//登録日時
//		$sql = $sql."       FLAW.C_INS_SHAIN_CD ";			//登録者CD

		$sql = $sql."       ACH.N_UPDATE_COUNT AS  ACH_UPCNT, ";			//更新回数(不具合対策ヘッダ)

		$sql = $sql."       ACH.C_HAPPEN_CAUSE_KBN AS HAPPEN_CAUSE_KBN, ";			//発生原因CD(不具合対策ヘッダ)
		$sql = $sql."       ACH.V2_HAPPEN_NOTES AS HAPPEN_NOTES, ";					//発生原因備考(不具合対策ヘッダ)
		$sql = $sql."       ACH.C_OUTFLOW_CAUSE_KBN AS OUTFLOW_CAUSE_KBN, ";		//流出原因CD(不具合対策ヘッダ)
		$sql = $sql."       ACH.V2_OUTFLOW_NOTES AS OUTFLOW_NOTES, ";				//流出原因備考(不具合対策ヘッダ)
		$sql = $sql."       ACH.V2_HAPPEN_ACTION AS HAPPEN_ACTION, ";				//発生対策(不具合対策ヘッダ)
		$sql = $sql."       ACH.V2_OUTFLOW_ACTION AS OUTFLOW_ACTION, ";				//流出対策(不具合対策ヘッダ)
		$sql = $sql."       ACH.V2_ARTICLE_DISPOSE AS ARTICLE_DISPOSE, ";			//現品処置(不具合対策ヘッダ)
		$sql = $sql."       ACH.C_ALL_ACTION_VALIDITY AS ALL_ACTION_VALIDITY ";	//全ての対策有効性(不具合対策ヘッダ)

		$sql = $sql."  FROM T_TR_FLAW FLAW,";
		$sql = $sql."       V_FL_CUST_INFO CUST1,";
//		$sql = $sql."       V_FL_CUST_INFO CUST2,";
		$sql = $sql."       T_TR_ACTION_H ACH,";
		//$sql = $sql."       V_FL_TANTO_INFO TANTO1,";
//		$sql = $sql."       V_FL_TANTO_INFO TANTO2,";
//		$sql = $sql."       V_FL_TANTO_INFO TANTO3,";
		$sql = $sql."       T_MS_FL_KBN KBN1 ";
		$sql = $sql." where FLAW.C_CUST_CD = CUST1.C_CUST_CD(+)";
//		$sql = $sql."   and TRIM(FLAW.C_PRODUCT_KA_CD) = CUST2.C_CUST_CD(+)";
//		$sql = $sql."   and FLAW.C_ANS_TANTO_CD = TANTO2.C_TANTO_CD(+)";
//		$sql = $sql."   and FLAW.C_CONFIRM_TANTO_CD = TANTO3.C_TANTO_CD(+)";
		$sql = $sql."   and FLAW.C_PROGRES_STAGE = KBN1.V2_KBN_MEI_CD(+)";
		$sql = $sql."   and KBN1.V2_KBN_CD(+) = 'C01'";
		$sql = $sql."   and FLAW.C_REFERENCE_NO = ACH.C_REFERENCE_NO(+)";
		$sql = $sql." AND FLAW.C_REFERENCE_NO = '".$strRrceNo."' ";

//		echo $sql;
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$aPara[0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));			//整理NO
			$aPara[1] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROGRES_STAGE'));			//進捗状態
//			$aPara[2] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD'));     			//顧客CD
//			$aPara[3] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM'));    			//顧客名
//			$aPara[4] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_OFFICER'));			//顧客担当者
//			$aPara[5] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CONTACT_KBN'));		//客先よりの連絡方法
//			$aPara[6] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_RECEPT_KBN'));  			//受付区分
//			$aPara[7] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN'));    			//不具合区分
//			$aPara[8] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_TARGET_QTY'));  			//対象数量
//			$aPara[9] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_TARGET_SECTION_KBN'));		//対象部門
			$aPara[10] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));     			//製品CD
			$aPara[11] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));    			//製品名
			$aPara[12] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));     			//図番
			$aPara[13] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_MODEL'));      			//型式
//			$aPara[14] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_DIE_NO'));      			//金型番号
			$aPara[15] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_LOT_NO'));     			//ロットNO
//			$aPara[16] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_INCIDENT_KBN'));			//発行先
//			$aPara[17] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INCIDENT_NM'));			//発行先名称
//			$aPara[18] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PRODUCT_KA_CD'));			//生産担当部門CD
//			$aPara[19] = $module_cmn->fChangUTF8(oci_result($stmt, 'SEITAN')); 					//生産担当部門名
//			$aPara[20] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PRODUCT_OFFICER_NM'));	//生産担当者
//			$aPara[21] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PRODUCT_OUT_KA_CD'));		//生産流出
//			$aPara[22] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CHECK_OUT_KA_CD1'));		//検査流出1
			$aPara[23] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_FLAW_CONTENTS'));		//不具合内容
//			$aPara[24] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_QTY'));  			//返却数量
//			$aPara[25] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_BAD_QTY'));     			//不良数量
//			$aPara[26] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_RETURN_DISPOSAL'));		//返却品処理
//			$aPara[27] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_RESULT_KBN'));  			//結果区分
//			$aPara[28] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_CUST_AP_ANS_YMD'));		//顧客指定回答日
//			$aPara[29] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_CUST_ACCEPT_YMD'));		//顧客了承回答日
//			$aPara[30] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_YMD'));     			//回答日
//			$aPara[31] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_ANS_TANTO_CD'));			//回答者CD
//			$aPara[32] = $module_cmn->fChangUTF8(oci_result($stmt, 'KAITAN')); 					//回答者名
//			$aPara[33] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ISSUE_YMD1'));   			//発行日(不具合)
			$aPara[34] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PC_AP_ANS_YMD1'));		//品証指定回答日(社内)
			$aPara[35] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PC_AP_ANS_YMD2'));		//品証指定回答日(協工)

			$aPara[36] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_YMD1'));  			//返却日(社内)
			$aPara[37] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_YMD2'));  			//返却日(協工)

//			$aPara[36] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_COMPLETE_YMD'));			//完結日
//			$aPara[37] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CONFIRM_TANTO_CD'));		//確認者CD
//			$aPara[38] = $module_cmn->fChangUTF8(oci_result($stmt, 'KAKUTAN')); 				//確認者名
//			$aPara[39] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_REMARKS'));     			//備考
//			$aPara[40] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROGRES_STAGE_NM'));     	//進捗状態
			$aPara[41] = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_UPCNT'));     			//更新回数(不具合情報)

			$aPara[40] = $module_cmn->fChangUTF8(oci_result($stmt, 'HAPPEN_CAUSE_KBN'));     	//発生原因
			$aPara[41] = $module_cmn->fChangUTF8(oci_result($stmt, 'HAPPEN_NOTES'));     		//発生原因備考
			$aPara[42] = $module_cmn->fChangUTF8(oci_result($stmt, 'OUTFLOW_CAUSE_KBN'));     	//流出原因
			$aPara[43] = $module_cmn->fChangUTF8(oci_result($stmt, 'OUTFLOW_NOTES'));     		//流出原因備考
			$aPara[44] = $module_cmn->fChangUTF8(oci_result($stmt, 'HAPPEN_ACTION'));     		//発生対策
			$aPara[45] = $module_cmn->fChangUTF8(oci_result($stmt, 'OUTFLOW_ACTION'));     		//流出対策
//			$aPara[46] = $module_cmn->fChangUTF8(oci_result($stmt, 'ARTICLE_DISPOSE'));     	//現品処置
			$aPara[47] = $module_cmn->fChangUTF8(oci_result($stmt, 'ALL_ACTION_VALIDITY'));     //全ての有効性
			$aPara[48] = $module_cmn->fChangUTF8(oci_result($stmt, 'ACH_UPCNT'));     			//更新回数(不具合対策)
		}


		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}


	//不具合対策明細データ取得処理
	public function fGetFlawStepDData($strRrceNo){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aParaD = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//不具合対策明細データ検索
		$sql = "";
		$sql = $sql." select T_TR_ACTION_D.*,V2_TANTO_NM ";
		$sql = $sql." from T_TR_ACTION_D, V_FL_TANTO_INFO ";
		$sql = $sql." where C_REFERENCE_NO = '".$strRrceNo."' ";
		$sql = $sql." and T_TR_ACTION_D.N_DEL_FLG = 0";
		$sql = $sql." and T_TR_ACTION_D.C_ACTION_OFFIER_CD = V_FL_TANTO_INFO.C_TANTO_CD(+)";
		$sql = $sql." order by N_ACTION_NO ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);
		$i = 0;
		while (oci_fetch($stmt)) {
			$aParaD[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ACTION_NO'));					//NO
			$aParaD[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_REQUEST_MATTERS'));			//要望事項
			$aParaD[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_ACTION_OFFIER_CD'));     		//担当者CD
			$aParaD[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_TANTO_NM'));  				//担当者名
			$aParaD[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_LIMIT_YMD'));    				//期限
			$aParaD[$i][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_OPERATION_MATTERS'));		//実施内容
			$aParaD[$i][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_OPERATION_YMD'));				//実施日
			$aParaD[$i][7] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_RESULT'));  					//結果
			$aParaD[$i][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_ACTION_VALIDITY'));    		//有効性

			//日付類は0を置換
			if($aParaD[$i][4] == 0){
				$aParaD[$i][4] == "";
			}
			if($aParaD[$i][6] == 0){
				$aParaD[$i][6] == "";
			}


			$i++;
		}



		oci_free_statement($stmt);
		oci_close($conn);

		return $aParaD;
	}


	//不具合統計資料検索処理
	//引数
	public function fFLK0050_1(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		$aPara[0][0] = "N006";

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		//$conn->setOption( 'optimize', 'portability' );


		//SQL取得
		$sql = "select KM.V2_KBN_MEI_NM AS FLAW_NM ";
		$sql = $sql." ,count(FL.C_FLAW_KBN) AS CNT ";
		$sql = $sql." from T_TR_FLAW FL,T_MS_FL_KBN KM ";
		$sql = $sql." where FL.C_FLAW_KBN = KM.V2_KBN_MEI_CD ";
		$sql = $sql." and KM.V2_KBN_CD = 'C15' ";
		$sql = $sql." and FL.N_DEL_FLG = 0 ";
		$sql = $sql." GROUP BY FL.C_FLAW_KBN,KM.V2_KBN_MEI_NM ";
		$sql = $sql." ORDER BY count(FL.C_FLAW_KBN) desc ";


		//取引先CD
//		if($aJoken[0] <> ""){
//			$sql = $sql." AND T1.C_CUST_CD LIKE '%' || :strCustCd || '%'" ;
//		}
		//取引先名
//		if($aJoken[1] <> ""){
//			$sql = $sql." AND T1.V2_CUST_NM LIKE '%' || :strCustNm || '%'" ;
//		}
		//取引先名カナ
//		if($aJoken[2] <> ""){
//			$sql = $sql." AND T1.V2_CUST_NM_K LIKE '%' || :strCustNmK || '%'" ;
//		}
		//$sql = $sql." ORDER BY T1.C_CUST_CD ";


		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[$i][0]  = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_NM'));
			$aPara[$i][1]  = $module_cmn->fChangUTF8(oci_result($stmt, 'CNT'));

			$i = $i + 1;
		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}

	//部門別不具合統計資料検索処理
	//引数
	public function fFLK0050_2(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		$aPara[0][0] = "N006";

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		//$conn->setOption( 'optimize', 'portability' );


		//SQL取得
		$sql = "select KM.V2_KBN_MEI_NM AS SECTION_NM ";
		$sql = $sql." ,count(FL.C_TARGET_SECTION_KBN) AS CNT ";
		$sql = $sql." from T_TR_FLAW FL,T_MS_FL_KBN KM ";
		$sql = $sql." where FL.C_TARGET_SECTION_KBN = KM.V2_KBN_MEI_CD ";
		$sql = $sql." and KM.V2_KBN_CD = 'C04' ";
		$sql = $sql." and FL.N_DEL_FLG = 0 ";
		$sql = $sql." GROUP BY FL.C_TARGET_SECTION_KBN,KM.V2_KBN_MEI_NM ";
		$sql = $sql." ORDER BY count(FL.C_TARGET_SECTION_KBN) desc ";


		//取引先CD
		//		if($aJoken[0] <> ""){
		//			$sql = $sql." AND T1.C_CUST_CD LIKE '%' || :strCustCd || '%'" ;
		//		}
		//取引先名
		//		if($aJoken[1] <> ""){
		//			$sql = $sql." AND T1.V2_CUST_NM LIKE '%' || :strCustNm || '%'" ;
		//		}
		//取引先名カナ
		//		if($aJoken[2] <> ""){
		//			$sql = $sql." AND T1.V2_CUST_NM_K LIKE '%' || :strCustNmK || '%'" ;
		//		}
		//$sql = $sql." ORDER BY T1.C_CUST_CD ";


		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		while (oci_fetch($stmt)) {

			$aPara[$i][0]  = $module_cmn->fChangUTF8(oci_result($stmt, 'SECTION_NM'));
			$aPara[$i][1]  = $module_cmn->fChangUTF8(oci_result($stmt, 'CNT'));

			$i = $i + 1;
		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}




	//ロット有効性評価通知情報検索
	public function fFlawValidityNotice(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);

		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}

		set_time_limit(600);

		//SQL取得
		$sql = "SELECT ";
		$sql = $sql."MIN(C_VALI_KBN) AS C_VALI_KBN, C_REFERENCE_NO, C_PROGRES_STAGE, C_CUST_CD, ";
		$sql = $sql."V2_CUST_OFFICER, V2_CUST_MANAGE_NO, C_PROD_CD, V2_PROD_NM, V2_DRW_NO, ";
		$sql = $sql."V2_MODEL, C_DIE_NO, V2_LOT_NO, C_FLAW_KBN,N_RETURN_YMD, ";
		$sql = $sql."N_SHIP_YMD, N_REEL_QTY ";
		$sql = $sql."FROM V_FL_VALI_INFO ";
		$sql = $sql."GROUP BY C_REFERENCE_NO, C_PROGRES_STAGE, C_CUST_CD, ";
		$sql = $sql."V2_CUST_OFFICER, V2_CUST_MANAGE_NO, C_PROD_CD, V2_PROD_NM, V2_DRW_NO, ";
		$sql = $sql."V2_MODEL, C_DIE_NO, V2_LOT_NO, C_FLAW_KBN,N_RETURN_YMD, ";
		$sql = $sql."N_SHIP_YMD, N_REEL_QTY ";
		$sql = $sql."ORDER BY C_REFERENCE_NO ";
		//	error_log($aJoken[3], 3, "out.log");
		//		echo $aJoken[6];

		//SQLの解析
		$stmt = oci_parse($conn, $sql);

		//SQL実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;

		while (oci_fetch($stmt)){
			$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));
			$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_VALI_KBN'));
			$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD'));
			$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));
			$aPara[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));
			$aPara[$i][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));
			$aPara[$i][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_MODEL'));
			$aPara[$i][7] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_DIE_NO'));
			$aPara[$i][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_LOT_NO'));
			$aPara[$i][9] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_SHIP_YMD'));
			$aPara[$i][10] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_REEL_QTY'));
			$aPara[$i][11] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_YMD'));

			$i++;

		}

		//リソース開放
		oci_free_statement($stmt);

		//Oracle接続切断
		oci_close($conn);

		return $aPara;

	}


	//主要顧客データ取得処理
	//引数	$sTaishoBumon:対象部門
	//
	//
	public function fGetPrimeCustData($sTaishoBumon){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//データ検索SQL
		$sql = "SELECT ";
		$sql = $sql." T1.C_TAISHO_SECTION AS TAISHO_SECTION ";
		$sql = $sql." ,T2.V2_KBN_MEI_NM AS SECTION_NM ";
		$sql = $sql." ,T1.C_CUST_CD AS CUST_CD ";
		$sql = $sql." ,V1.V2_CUST_NM_R AS CUST_NM_R ";
		$sql = $sql." ,T1.V2_BIKO AS BIKO ";
		$sql = $sql." ,T1.N_UPDATE_COUNT AS UPDATE_COUNT ";
		$sql = $sql." FROM T_MS_PRIME_CUST T1,T_MS_FL_KBN T2, V_FL_CUST_INFO V1 ";
		$sql = $sql." WHERE TRIM(T1.C_CUST_CD) = TRIM(V1.C_CUST_CD)  ";
		$sql = $sql." AND T1.C_TAISHO_SECTION = T2.V2_KBN_MEI_CD  ";
		$sql = $sql." AND T2.V2_KBN_CD = 'C04' ";
		$sql = $sql." AND T1.C_TAISHO_SECTION = :sTaishoBumon ";

		//SQLの分析
		$stmt = oci_parse($conn, $sql);
		//バインド変数のセット
		oci_bind_by_name($stmt, ":sTaishoBumon", $sTaishoBumon, -1);
		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);



		$i = 0;

		while (oci_fetch($stmt)) {


			$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'TAISHO_SECTION'));

			$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'CUST_CD'));
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'CUST_NM_R'));
			$aPara[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'BIKO'));
			$aPara[$i][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'UPDATE_COUNT'));
			$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'SECTION_NM'));

			$i = $i + 1;



		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}
	
	
	//環境紛争鉱物情報データ一覧表示処理
	public function fEnvSearch($session,$aJoken,$intJudgeDate){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);

		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}

		$iRowNo = 0;

		//Oracleのバージョン取得
		$strOraVer = oci_server_version($conn);
		//9iかそれ以外かで判断
		if(preg_match("/9i/",$strOraVer)){

			$bSearchOk = false;
		}else{
			$bSearchOk = true;
		}


		//SQL取得
		$sql = "SELECT ";
		$sql = $sql."TRIM(ENV.C_REFERENCE_NO) AS RRCE_NO ";
		$sql = $sql.",MSK1.V2_KBN_MEI_NM AS PGRS_STATUS ";
		$sql = $sql.",VCI.V2_CUST_NM AS CUST_NM ";
		$sql = $sql.",MSK2.V2_KBN_MEI_NM AS SURVEY_KBN ";
		$sql = $sql.",ENV.V2_ENV_CONTENTS AS ENV_CONTENTS ";
		$sql = $sql.",ENV.V2_TARGET_ITEM AS TARGET_ITEM ";
		$sql = $sql.",ENV.N_CUST_AP_ANS_YMD AS CUST_AP_ANS_YMD ";
		$sql = $sql.",SHAIN.V2_SHAIN_NM AS SHAIN_NM ";
		$sql = $sql.",ENV.N_PC_AP_ANS_YMD AS PC_AP_ANS_YMD ";
		$sql = $sql.",ENV.N_ANS_DOC1 AS ANS_DOC1 ";
		$sql = $sql.",ENV.N_ANS_DOC2 AS ANS_DOC2 ";
		$sql = $sql.",ENV.N_ANS_DOC3 AS ANS_DOC3 ";
		$sql = $sql.",ENV.N_ANS_DOC4 AS ANS_DOC4 ";
		$sql = $sql.",ENV.N_ANS_DOC5 AS ANS_DOC5 ";
		$sql = $sql.",ENV.N_ANS_DOC6 AS ANS_DOC6 ";
		$sql = $sql.",ENV.N_ANS_DOC7 AS ANS_DOC7 ";
		$sql = $sql.",ENV.N_ANS_DOC8 AS ANS_DOC8 ";
		$sql = $sql.",ENV.N_ANS_DOC9 AS ANS_DOC9 ";
		$sql = $sql.",ENV.N_ANS_DOC10 AS ANS_DOC10 ";
		$sql = $sql.",ENV.N_ANS_DOC11 AS ANS_DOC11 ";
		$sql = $sql.",ENV.N_ANS_DOC12 AS ANS_DOC12 ";
		$sql = $sql.",ENV.N_ANS_DOC13 AS ANS_DOC13 ";
		$sql = $sql.",ENV.N_ANS_DOC14 AS ANS_DOC14 ";
		$sql = $sql.",ENV.N_ANS_DOC15 AS ANS_DOC15 ";
		$sql = $sql.",ENV.V2_ANS_DOC15 AS V2_ANS_DOC15 ";
		$sql = $sql.",ENV.N_ANS_YMD AS ANS_YMD ";
		$sql = $sql." FROM  ";
		$sql = $sql." T_TR_ENV ENV ";
		$sql = $sql." ,T_MS_FL_KBN MSK1 ";
		$sql = $sql." ,T_MS_FL_KBN MSK2 ";
		$sql = $sql." ,V_FL_CUST_INFO VCI ";
		$sql = $sql." ,T_MS_SHAIN SHAIN ";
		$sql = $sql." WHERE ENV.C_PROGRES_STAGE = MSK1.V2_KBN_MEI_CD ";
		$sql = $sql." AND MSK1.V2_KBN_CD = 'C29' ";
		$sql = $sql." AND TRIM(ENV.C_SURVEY_KBN) = MSK2.V2_KBN_MEI_CD ";
		$sql = $sql." AND MSK2.V2_KBN_CD = 'C31' ";
		$sql = $sql." AND ENV.C_CUST_CD = VCI.C_CUST_CD(+) ";
		$sql = $sql." AND VCI.C_CUST_CLS(+) = 'C' ";
		$sql = $sql." AND ENV.C_INS_SHAIN_CD = SHAIN.C_SHAIN_CD ";
		$sql = $sql." AND ENV.N_DEL_FLG = 0 ";



		if($aJoken[0] <> "-1" && $aJoken[0] <> ""){
			$sql = $sql." AND ENV.C_PROGRES_STAGE =  :sPgrsStage  ";
		}
		if($aJoken[1] <> "-1" && $aJoken[1] <> ""){
			$sql = $sql." AND ENV.C_REFERENCE_NO LIKE '%' || :sRrceNo || '%' ";
		}
		if($aJoken[2] <> "-1" && $aJoken[2] <> ""){
			$sql = $sql." AND ENV.C_CUST_CD LIKE '%' || :sCustCd || '%' ";
		}
		if($aJoken[3] <> "-1" && $aJoken[3] <> ""){
			$sql = $sql." AND TRIM(ENV.C_SURVEY_KBN) = :sSurveyKbn ";
		}
//2019/04/01 AD START T.FUJITA
		if($aJoken[4] <> "-1" && $aJoken[4] <> ""){
			$sql = $sql." AND TRIM(VCI.V2_CUST_NM) LIKE '%' || :sCustNm || '%' ";
		}
		if($aJoken[5] <> "-1" && $aJoken[5] <> ""){
			$sql = $sql." AND TRIM(ENV.V2_ENV_CONTENTS) LIKE '%' || :sContents || '%' ";
		}
//2019/04/01 AD END T.FUJITA
		
//2019/04/01 ED START T.FUJITA
		//$sql = $sql." ORDER BY ENV.C_REFERENCE_NO ";
		$sql = $sql." ORDER BY ENV.C_REFERENCE_NO DESC ";
		//$sql = $sql." ORDER BY ENV.N_UPD_YMD DESC ";
//2019/04/01 ED END T.FUJITA

//	error_log($aJoken[3], 3, "out.log");
//		echo $aJoken[6];

		//SQLの解析
		$stmt = oci_parse($conn, $sql);

		//進捗状態
		if($aJoken[0] <> "-1" && $aJoken[0] <> ""){
			oci_bind_by_name($stmt, ":sPgrsStage", $aJoken[0], -1);
		}
		if($aJoken[1] <> "-1" && $aJoken[1] <> ""){
			oci_bind_by_name($stmt, ":sRrceNo", $aJoken[1], -1);
		}
		if($aJoken[2] <> "-1" && $aJoken[2] <> ""){
			oci_bind_by_name($stmt, ":sCustCd", $aJoken[2], -1);
		}
		if($aJoken[3] <> "-1" && $aJoken[3] <> ""){
			oci_bind_by_name($stmt, ":sSurveyKbn", $aJoken[3], -1);
		}
//2019/04/01 AD START T.FUJITA
		if($aJoken[4] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS(trim($aJoken[4]));
			oci_bind_by_name($stmt, ":sCustNm", $sTmpJoken, -1);
		}
		if($aJoken[5] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS(trim($aJoken[5]));
			oci_bind_by_name($stmt, ":sContents", $sTmpJoken, -1);
		}
//2019/04/01 AD END T.FUJITA

		//初期に検索結果無しのパラメータを設定しておく(検索結果があれば上書き)
		$aPara[0][0] = "N006";

		//SQL実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		//当日
		$today = date("Ymd");
		
		while (oci_fetch($stmt)){


			$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'RRCE_NO'));
			$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'PGRS_STATUS'));
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'CUST_NM'));
			$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'ENV_CONTENTS'));
			$aPara[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'TARGET_ITEM'));
			$aPara[$i][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'CUST_AP_ANS_YMD'));
			$aPara[$i][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'SHAIN_NM'));
			$aPara[$i][7] = $module_cmn->fChangUTF8(oci_result($stmt, 'PC_AP_ANS_YMD'));
			$aPara[$i][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC1'));
			$aPara[$i][9] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC2'));
			$aPara[$i][10] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC3'));
			$aPara[$i][11] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC4'));
			$aPara[$i][12] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC5'));
			$aPara[$i][13] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC6'));
			$aPara[$i][14] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC7'));
			$aPara[$i][15] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC8'));
			$aPara[$i][16] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC9'));
			$aPara[$i][17] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC10'));
			$aPara[$i][18] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC11'));
			$aPara[$i][19] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC12'));
			$aPara[$i][20] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC13'));
			$aPara[$i][21] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC14'));
			$aPara[$i][22] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_DOC15'));
			$aPara[$i][23] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_ANS_DOC15'));
			$aPara[$i][24] = $module_cmn->fChangUTF8(oci_result($stmt, 'ANS_YMD'));


			//セル色
			//進捗状態が解決済の場合はグレー
			if($aPara[$i][1] == "調査完了済" ){
				$aPara[$i][50] = "gray";
			}else{
				$aPara[$i][50] = "";
			}

			//顧客指定回答日が入っていて回答日が未入力
			if($aPara[$i][5] <> 0 && $aPara[$i][24] == 0 ){

				
				//顧客指定回答日が本日を過ぎている場合はピンク
				if($today > $aPara[$i][5]){
					$aPara[$i][51] = "limit";
				}
				//顧客指定回答日が翌稼働日以降の場合はイエロー
				elseif($aPara[$i][5] <= $intJudgeDate){
					$aPara[$i][51] = "near"; 
				}				
			}

			

			//$aPara[$i][34] = $module_cmn->fChangUTF8(oci_result($stmt, 'MEASURES_YMD'));


			$i = $i + 1;

		}

		//1000件以上あった
		if(count($aPara) > 1000){
			$aPara[0][0] = "E016";
			return $aPara;
		}

		//リソース開放
		oci_free_statement($stmt);

		//Oracle接続切断
		oci_close($conn);

		return $aPara;

	}

	//環境紛争鉱物明細データ取得処理
	public function fGetEnvData($Reference_No){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql."select TRIM(ENV.C_REFERENCE_NO) AS C_REFERENCE_NO,";			//整理NO
		$sql = $sql."       ENV.C_PROGRES_STAGE,";			//進捗状態
		$sql = $sql."       ENV.N_CONTACT_ACCEPT_YMD,";		//連絡受理日
		$sql = $sql."       ENV.C_GET_INFO_KBN,";  			//情報入手先
		$sql = $sql."       ENV.C_CUST_CD,";     			//顧客CD
		$sql = $sql."       TRIM(CUST1.V2_CUST_NM) AS V2_CUST_NM,";    			//顧客名
		$sql = $sql."       ENV.V2_INFO_OFFICER,";			//情報提供者
		$sql = $sql."       ENV.C_SURVEY_KBN,";				//調査区分
		$sql = $sql."       ENV.C_TARGET_SECTION_KBN,";		//対象部門
		$sql = $sql."       ENV.V2_ENV_CONTENTS,";			//内容
		$sql = $sql."       ENV.V2_TARGET_ITEM,";			//対象製品
		$sql = $sql."       ENV.N_CUST_AP_ANS_YMD,";		//顧客指定回答日
		$sql = $sql."       ENV.N_ANS_YMD,";     			//回答日
		$sql = $sql."       ENV.N_ANS_DOC1,";     			//提出要求書類1
		$sql = $sql."       ENV.N_ANS_DOC2,";     			//提出要求書類2
		$sql = $sql."       ENV.N_ANS_DOC3,";     			//提出要求書類3
		$sql = $sql."       ENV.N_ANS_DOC4,";     			//提出要求書類4
		$sql = $sql."       ENV.N_ANS_DOC5,";     			//提出要求書類5
		$sql = $sql."       ENV.N_ANS_DOC6,";     			//提出要求書類6
		$sql = $sql."       ENV.N_ANS_DOC7,";     			//提出要求書類7
		$sql = $sql."       ENV.N_ANS_DOC8,";     			//提出要求書類8
		$sql = $sql."       ENV.N_ANS_DOC9,";     			//提出要求書類9
		$sql = $sql."       ENV.N_ANS_DOC10,";     			//提出要求書類10
		$sql = $sql."       ENV.N_ANS_DOC11,";     			//提出要求書類11
		$sql = $sql."       ENV.N_ANS_DOC12,";     			//提出要求書類12
		$sql = $sql."       ENV.N_ANS_DOC13,";     			//提出要求書類13
		$sql = $sql."       ENV.N_ANS_DOC14,";     			//提出要求書類14
		$sql = $sql."       ENV.N_ANS_DOC15,";     			//提出要求書類15
		$sql = $sql."       ENV.V2_ANS_DOC15,";     		//提出要求書類15入力欄
		$sql = $sql."       ENV.C_ANS_TANTO_CD,";			//回答者CD
		$sql = $sql."       TANTO1.V2_TANTO_NM as KAITAN,"; //回答者名		
		$sql = $sql."       ENV.C_MAKER_SURVEY_KBN,";  		//メーカ調査
		$sql = $sql."       ENV.N_PC_AP_ANS_YMD,";			//品証指定回答日
		$sql = $sql."       ENV.N_UPDATE_COUNT, ";			//更新回数
		$sql = $sql."       ENV.C_INS_SHAIN_CD ";			//登録者
		
		$sql = $sql."  from T_TR_ENV ENV,";
		$sql = $sql."       V_FL_CUST_INFO CUST1,";
		$sql = $sql."       V_FL_TANTO_INFO TANTO1 ";
		$sql = $sql." where ENV.C_CUST_CD = CUST1.C_CUST_CD(+)";
		$sql = $sql."   and ENV.C_ANS_TANTO_CD = TANTO1.C_TANTO_CD(+)";
		$sql = $sql." AND ENV.C_REFERENCE_NO = '".$Reference_No."' ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$aPara[0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));			//整理NO
			$aPara[1] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROGRES_STAGE'));			//進捗状態
			$aPara[2] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_CONTACT_ACCEPT_YMD'));   	//連絡受理日
			$aPara[3] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_GET_INFO_KBN'));   		//情報入手先
			$aPara[4] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD'));     			//顧客CD
			$aPara[5] = trim(mb_convert_kana($module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM')),"s"));	//顧客名
			$aPara[6] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INFO_OFFICER'));			//情報提供者			
			$aPara[7] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_SURVEY_KBN'));				//調査区分
			$aPara[8] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_TARGET_SECTION_KBN'));		//対象部門
			$aPara[9] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_ENV_CONTENTS'));  		//内容
			$aPara[10] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_TARGET_ITEM'));    		//対象製品
			$aPara[11] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_CUST_AP_ANS_YMD'));		//顧客指定回答日
			$aPara[12] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_YMD'));     			//回答日
			$aPara[13] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC1'));  			//提出要求書類1
			$aPara[14] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC2'));  			//提出要求書類2
			$aPara[15] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC3'));  			//提出要求書類3
			$aPara[16] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC4'));  			//提出要求書類4
			$aPara[17] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC5'));  			//提出要求書類5
			$aPara[18] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC6'));  			//提出要求書類6
			$aPara[19] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC7'));  			//提出要求書類7
			$aPara[20] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC8'));  			//提出要求書類8
			$aPara[21] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC9'));  			//提出要求書類9
			$aPara[22] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC10'));  			//提出要求書類10
			$aPara[23] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC11'));  			//提出要求書類11
			$aPara[24] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC12'));  			//提出要求書類12
			$aPara[25] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC13'));  			//提出要求書類13
			$aPara[26] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC14'));  			//提出要求書類14
			$aPara[27] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_ANS_DOC15'));  			//提出要求書類15
			$aPara[28] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_ANS_DOC15'));  			//提出要求書類15入力欄
			$aPara[29] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_ANS_TANTO_CD'));			//回答者CD
			$aPara[30] = $module_cmn->fChangUTF8(oci_result($stmt, 'KAITAN')); 					//回答者名
			$aPara[31] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_MAKER_SURVEY_KBN')); 		//メーカ調査
			$aPara[32] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PC_AP_ANS_YMD'));			//品証指定回答日
			$aPara[33] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_UPDATE_COUNT'));     		//更新回数
			$aPara[34] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_INS_SHAIN_CD'));     		//登録者CD
			
		
			
		}


		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}
	
	
	//メーカー調査依頼データ検索
	public function fGetMakerSurveyDData($Reference_No){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aPara = array();

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);

		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			//echo htmlentities($e['message']);
			session_destroy();
			die("データベースに接続できません");
		}

		
		//SQL取得
		$sql = "SELECT ";
		$sql = $sql." ENVD.* ";
		$sql = $sql." ,CUST.V2_CUST_NM_R AS C_CUST_NM ";
		$sql = $sql." FROM T_TR_ENV_D ENVD,V_FL_CUST_INFO CUST ";
		$sql = $sql." WHERE ENVD.C_REFERENCE_NO ='".$Reference_No."'" ;
		$sql = $sql." AND ENVD.N_DEL_FLG = 0";
		$sql = $sql." AND TRIM(ENVD.C_CUST_CD) = TRIM(CUST.C_CUST_CD(+)) ";
		$sql = $sql." ORDER BY ENVD.N_SEQ_NO ";

		//SQLの解析
		$stmt = oci_parse($conn, $sql);

		//SQL実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;

		while (oci_fetch($stmt)){

			$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD'));
			$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_NM'));
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_MAKER_ANS_YMD'));
			$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_ENV_MAKER_CONTENTS'));
			
			$i++;

		}

		//リソース開放
		oci_free_statement($stmt);

		//Oracle接続切断
		oci_close($conn);

		return $aPara;

	}
	
	//不具合対策明細データ回答日期限切れ情報取得処理
	public function fGetActionAleartData(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		$aParaD = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//不具合対策明細データ検索
		$sql = "";
		$sql = $sql." select T1.C_REFERENCE_NO AS C_REFERENCE_NO ";
		$sql = $sql." ,T2.V2_PROD_NM AS V2_PROD_NM ";
		$sql = $sql." ,T2.V2_DRW_NO AS V2_DRW_NO ";
		$sql = $sql." ,T2.V2_FLAW_CONTENTS AS V2_FLAW_CONTENTS ";
		$sql = $sql." ,T1.V2_REQUEST_MATTERS AS V2_REQUEST_MATTERS ";
		$sql = $sql." ,T1.N_LIMIT_YMD AS N_LIMIT_YMD ";
		$sql = $sql." ,T2.C_INS_SHAIN_CD AS C_INS_SHAIN_CD ";
		$sql = $sql." ,T1.C_ACTION_OFFIER_CD AS C_ACTION_OFFIER_CD ";
		$sql = $sql." ,T2.C_CUST_CD AS C_CUST_CD ";
		$sql = $sql." ,T2.C_TARGET_SECTION_KBN AS C_TARGET_SECTION_KBN ";
		$sql = $sql." ,KBN1.V2_KBN_MEI_NM AS FLAW_NM ";
		$sql = $sql." from T_TR_ACTION_D T1, T_TR_FLAW T2, T_MS_FL_KBN KBN1";
		$sql = $sql." where T1.C_REFERENCE_NO = T2.C_REFERENCE_NO ";
		$sql = $sql." and T2.N_DEL_FLG = 0 ";
		$sql = $sql." and T1.N_LIMIT_YMD > 0 ";		//期限が設定済
		$sql = $sql." and T1.N_OPERATION_YMD = 0 ";	//実施日が未設定
		$sql = $sql." and T2.C_FLAW_KBN = KBN1.V2_KBN_MEI_CD ";
		$sql = $sql." and KBN1.V2_KBN_CD = 'C15' ";
		$sql = $sql." order by T1.C_REFERENCE_NO, T1.N_ACTION_NO ";

		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);
		$i = 0;
		while (oci_fetch($stmt)) {
			
			//期限当日、または期限をオーバーした場合、発報対象
			if($module_cmn->fChangUTF8(oci_result($stmt, 'N_LIMIT_YMD')) <= date("Ymd")){
				$aParaD[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));			//整理NO
				$aParaD[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));				//製品名
				$aParaD[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));				//仕様番号
				$aParaD[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_NM'));					//不具合区分
				$aParaD[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_FLAW_CONTENTS'));		//不具合内容
				$aParaD[$i][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_REQUEST_MATTERS'));		//トレース内容			
				$aParaD[$i][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_LIMIT_YMD'));    			//実施期限
				//期限状態
				if($aParaD[$i][6] == date("Ymd")){
					//期限当日
					$aParaD[$i][7] = "△期限間近";
				}else{
					//期限切れ
					$aParaD[$i][7] = "×期限切";
				}

				$aParaD[$i][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_INS_SHAIN_CD'));    		//不具合登録者CD
				$aParaD[$i][9] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_ACTION_OFFIER_CD'));  	//トレース担当者CD
				$aParaD[$i][10] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_CUST_CD'));  			//得意先CD
				$aParaD[$i][11] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_TARGET_SECTION_KBN'));  	//対象部門CD

				$i++;
				
			}

			
		}



		oci_free_statement($stmt);
		oci_close($conn);

		return $aParaD;
	}

	
	
	//環境紛争鉱物品証指定回答日期限切れ情報取得処理
	public function fGetEnvPcAleartData(){

		require_once("module_common.php");

		$module_cmn = new module_common;

		//当日
		$today = date("Ymd");
		//前日
		$daybefore = date("Ymd", strtotime("-1 day", time()));
		
		
		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql." select TRIM(ENV.C_REFERENCE_NO) AS C_REFERENCE_NO, "; 
		$sql = $sql." CUST1.V2_CUST_NM AS CUST_NM, ";
		$sql = $sql." ENV.V2_ENV_CONTENTS, ";
		$sql = $sql." ENV.V2_TARGET_ITEM, ";
		$sql = $sql." ENV.N_PC_AP_ANS_YMD, ";
		$sql = $sql." CUST2.V2_CUST_NM AS MAKER_NM ";
		$sql = $sql." from T_TR_ENV ENV,T_TR_ENV_D ENVD  ";
		$sql = $sql." ,V_FL_CUST_INFO CUST1 ";
		$sql = $sql." ,V_FL_CUST_INFO CUST2 ";
		$sql = $sql." where TRIM(ENV.C_CUST_CD) = TRIM(CUST1.C_CUST_CD(+)) ";
		$sql = $sql." and TRIM(ENVD.C_CUST_CD) = TRIM(CUST2.C_CUST_CD(+))  ";
		$sql = $sql." and ENV.C_REFERENCE_NO = ENVD.C_REFERENCE_NO ";
		$sql = $sql." and ENV.N_PC_AP_ANS_YMD > 0  ";
		$sql = $sql." and ENVD.N_MAKER_ANS_YMD = 0 ";
		$sql = $sql." and ENV.N_DEL_FLG= 0 ";	//削除されていないものを対象
		$sql = $sql." ORDER BY ENV.C_REFERENCE_NO,ENVD.N_SEQ_NO ";

		
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		
		while (oci_fetch($stmt)) {
			$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));							//整理NO
			$aPara[$i][1] = trim(mb_convert_kana($module_cmn->fChangUTF8(oci_result($stmt, 'CUST_NM')),"s"));		//顧客名
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_ENV_CONTENTS'));  						//内容
			$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_TARGET_ITEM'));    						//対象製品
			$aPara[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PC_AP_ANS_YMD'));							//品証指定回答日
			$aPara[$i][5] = trim(mb_convert_kana($module_cmn->fChangUTF8(oci_result($stmt, 'MAKER_NM')),"s"));		//メーカー名			

			//品証指定回答日が本日を過ぎている場合
			if($today > $aPara[$i][4]){
				$aPara[$i][10] = "limit";
			}
			//客先指定回答日が当日の場合ー
			elseif($aPara[$i][4] == $today ){
				$aPara[$i][10] = "near"; 
			}				
			$i++;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}


//2019/04/01 AD START T.FUJITA
	//SMART2保留品情報データ存在確認処理
	//引数
	//$refference_no		伝票NO
	public function fchkTrblSonzaiS2($refference_no){

		require_once("module_common.php");
		$module_cmn = new module_common;
		$blnRtn = false;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gNUserID, $this->gNPass, $this->gNDB);
		if (!$conn) {
			$e = oci_error();	// oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//取引先データ検索
		$sql = "";
		$sql = $sql."SELECT COUNT(保留伝票_NO) AS CNT";
		$sql = $sql."  FROM J_保留品情報 ";
		$sql = $sql."　WHERE 削除日_YMD = 0 ";
		$sql = $sql."   AND 保留伝票_NO = '".trim($refference_no)."'";
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			//1件以上の場合True
			if(oci_result($stmt, 'CNT') >= 1){
				$blnRtn = true;
			}
			break;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $blnRtn;
	}

	//赤伝緑伝情報データ存在確認処理
	//引数	$refference_no		伝票NO
	//		$iSeq				件数
	public function fchkTrblDataDupli($refference_no,&$iSeq){

		require_once("module_common.php");
		$module_cmn = new module_common;
		$blnRtn = false;
		$iSeq = 1;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();	// oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//取引先データ検索
		$sql = "";
		$sql = $sql."SELECT MAX(N_REFERENCE_SEQ) AS MAX";
		$sql = $sql."  FROM T_TR_TRBL ";
		$sql = $sql." WHERE C_REFERENCE_NO = '".trim($refference_no)."'";
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			//0件の場合True
			if(oci_result($stmt, 'MAX') == ""){
				$blnRtn = true;
			}else{
				//既に存在する場合シーケンスセット
				$iSeq = $module_cmn->fChangUTF8(oci_result($stmt, 'MAX'));
				$iSeq = $iSeq + 1;
			}
			break;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $blnRtn;
	}

	//SMART2保留品情報データ取得処理
	//引数	$refference_no		伝票NO
	public function fGetTrblDataS2($refference_no){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aPara = array();

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gNUserID, $this->gNPass, $this->gNDB);
		if (!$conn) {
			$e = oci_error();	//oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//保留品情報データ検索
		$sql = "";
		$sql = $sql."SELECT JHH.保留伝票_NO			AS REFERENCE_NO";
		$sql = $sql."	   ,JHH.保留伝票_KU			AS REFERENCE_KBN";
		$sql = $sql."	   ,JHH.発行日_YMD			AS INCIDENT_YMD";
		$sql = $sql."	   ,MH1.取引先略称_KJ		AS PROD_GRP_NM";
		$sql = $sql."	   ,MT1.担当者略名_KJ		AS PROD_TANTO_NM1";
		$sql = $sql."	   ,MT2.担当者略名_KJ		AS PROD_TANTO_NM2";
		$sql = $sql."	   ,MT3.担当者略名_KJ		AS PROD_TANTO_NM3";
		$sql = $sql."	   ,MH2.取引先略称_KJ		AS EXAM_GRP_NM";
		$sql = $sql."	   ,MT4.担当者略名_KJ		AS HINGI_TANTO_NM";
		$sql = $sql."	   ,MH3.取引先略称_KJ		AS CUST_NM";
		$sql = $sql."	   ,JHH.製品_CD				AS PROD_CD";
		$sql = $sql."	   ,JHH.金型_CD				AS DIE_NO";
		$sql = $sql."	   ,MSH.製品名				AS PROD_NM";
		$sql = $sql."	   ,MSH.型式				AS DRW_NO";
		$sql = $sql."	   ,TRIM(JHH.保留ロット_NO)	AS FLAW_LOT_NO";
		$sql = $sql."	   ,JHH.保留数量				AS FLAW_LOT_QTY";
		$sql = $sql."	   ,JHH.不具合単価			AS UNIT_PRICE";
		$sql = $sql."	   ,JHH.不具合金額			AS FLAW_PRICE";
		$sql = $sql."	   ,TRIM(JHH.めっき取引先_CD)	AS PLATING_CD";
		$sql = $sql."	   ,TRIM(MH4.取引先略称_KJ)	AS PLATING_NM";
		$sql = $sql."	   ,MZ1.規格				AS MATERIAL_SPEC1";
		$sql = $sql."	   ,MZ2.規格				AS MATERIAL_SPEC2";
		$sql = $sql."	   ,TRIM(JHH.保留理由_KU)	AS FLAW_KBN";
		$sql = $sql."	   ,TRIM(JHH.不具合内容1_KJ)	AS FLAW_CONTENTS1";
		$sql = $sql."	   ,TRIM(JHH.不具合内容2_KJ)	AS FLAW_CONTENTS2";
		$sql = $sql."	   ,TRIM(JHH.不具合内容3_KJ)	AS FLAW_CONTENTS3";
		$sql = $sql."	   ,TRIM(JHH.不具合内容4_KJ)	AS FLAW_CONTENTS4";
		$sql = $sql."	   ,TRIM(JHH.不具合内容5_KJ)	AS FLAW_CONTENTS5";
		$sql = $sql."	   ,TRIM(JHH.不具合内容6_KJ)	AS FLAW_CONTENTS6";
		$sql = $sql."	   ,TRIM(JHH.計画_NO)		AS PLAN_NO";
		$sql = $sql."	   ,TRIM(JHH.計画_SEQ)		AS PLAN_SEQ";
		$sql = $sql."	   ,TRIM(MSH.製品_KU)		AS PROD_KBN";
		$sql = $sql."  FROM J_保留品情報 JHH";
		$sql = $sql."	   ,M_製品   MSH";
		$sql = $sql."	   ,M_取引先 MH1";
		$sql = $sql."	   ,M_取引先 MH2";
		$sql = $sql."	   ,M_取引先 MH3";
		$sql = $sql."	   ,M_取引先 MH4";
		$sql = $sql."	   ,M_担当者 MT1";
		$sql = $sql."	   ,M_担当者 MT2";
		$sql = $sql."	   ,M_担当者 MT3";
		$sql = $sql."	   ,M_担当者 MT4";
		$sql = $sql."	   ,M_製品材料構成 MK1";
		$sql = $sql."	   ,M_製品材料構成 MK2";
		$sql = $sql."	   ,M_材料 MZ1";
		$sql = $sql."	   ,M_材料 MZ2";
		$sql = $sql."	   ,M_製品ブランク材構成 MSB";
		$sql = $sql." WHERE JHH.プレス取引先_CD  = MH1.取引先_CD(+)";
		$sql = $sql."   AND JHH.検査グループ_CD  = MH2.取引先_CD(+)";
		$sql = $sql."   AND JHH.検査者_CD	   = MT4.担当者_CD(+)";
		$sql = $sql."   AND JHH.得意先_CD	   = MH3.取引先_CD(+)";
		$sql = $sql."   AND JHH.製品_CD		   = MSH.製品_CD (+)";
		$sql = $sql."   AND JHH.めっき取引先_CD  = MH4.取引先_CD(+)";
		$sql = $sql."   AND JHH.作業者1_CD	   = MT1.担当者_CD(+)";
		$sql = $sql."   AND JHH.作業者2_CD	   = MT2.担当者_CD(+)";
		$sql = $sql."   AND JHH.作業者3_CD	   = MT3.担当者_CD(+)";
		$sql = $sql."   AND JHH.製品_CD		   = MK1.製品_CD (+)";
		$sql = $sql."   AND MK1.材料_CD		   = MZ1.材料_CD (+)";
		$sql = $sql."   AND JHH.製品_CD		   = MSB.製品_CD (+)";
		$sql = $sql."   AND MSB.ブランク材製品_CD = MK2.製品_CD (+)";
		$sql = $sql."   AND MK2.材料_CD		   = MZ2.材料_CD (+)";
		$sql = $sql."   AND JHH.削除日_YMD	   = 0";
		$sql = $sql."   AND JHH.保留伝票_NO	   = '".trim($refference_no)."'";

		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$aPara[0]  = $module_cmn->fChangUTF8(oci_result($stmt, 'REFERENCE_NO'));											//伝票NO
			$aPara[1]  = str_replace("0","",$module_cmn->fChangUTF8(oci_result($stmt, 'REFERENCE_KBN')));						//伝票種別
			$aPara[2]  = $this->fDispKbn("C39",$module_cmn->fChangUTF8(oci_result($stmt, 'REFERENCE_KBN'))-1);					//伝票種別名
			$aPara[3]  = $module_cmn->fChangUTF8($module_cmn->fChangDateFormat(oci_result($stmt, 'INCIDENT_YMD')));				//伝票発行日
			$aPara[4]  = "0";																									//進捗状態
			$aPara[5]  = $this->fDispKbn("C38","0");																			//進捗状態名
			$aPara[6]  = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD_GRP_NM'));												//生産グループ名
			$aPara[7]  = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD_TANTO_NM1'));											//生産担当者名1
			$aPara[8]  = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD_TANTO_NM2'));											//生産担当者名2
			$aPara[9]  = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD_TANTO_NM3'));											//生産担当者名3
			$aPara[10]  = $module_cmn->fChangUTF8(oci_result($stmt, 'EXAM_GRP_NM'));											//検査グループ名
			$aPara[11]  = $module_cmn->fChangUTF8(oci_result($stmt, 'HINGI_TANTO_NM'));											//品技担当者名
			$aPara[12]  = $module_cmn->fChangUTF8(oci_result($stmt, 'CUST_NM'));												//得意先名
			$aPara[13] = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD_CD'));													//製品CD
			$aPara[14] = $module_cmn->fChangUTF8(oci_result($stmt, 'DIE_NO'));													//金型番号
			$aPara[15] = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD_NM'));													//製品名
			$aPara[16] = $module_cmn->fChangUTF8(oci_result($stmt, 'DRW_NO'));													//仕様番号
			$aPara[17] = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_LOT_NO'));												//不具合ロットNO
			$aPara[18] = number_format($module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_LOT_QTY')));								//不具合数量
			$aPara[19] = preg_replace("/\.?0+$/","",number_format($module_cmn->fChangUTF8(oci_result($stmt, 'UNIT_PRICE')),5));	//単価
			$aPara[20] = number_format($module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_PRICE')));								//不具合金額
			$aPara[21] = $module_cmn->fChangUTF8(oci_result($stmt, 'PLATING_CD'));												//めっき先CD
			$aPara[22] = $module_cmn->fChangUTF8(oci_result($stmt, 'PLATING_NM'));												//めっき先
			//材料仕様
			if (trim($module_cmn->fChangUTF8(oci_result($stmt, 'MATERIAL_SPEC1'))) == ""){
				$aPara[23] = $module_cmn->fChangUTF8(oci_result($stmt, 'MATERIAL_SPEC2'));
			}else {
				$aPara[23] = $module_cmn->fChangUTF8(oci_result($stmt, 'MATERIAL_SPEC1'));
			}
			$aPara[24] = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_KBN'));												//不具合区分1
			//不具合内容
			$aPara[25] = "";
			$sTmp = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_CONTENTS1'));
			if ($sTmp <> ""){$aPara[25] = $sTmp;}
			$sTmp = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_CONTENTS2'));
			if ($sTmp <> ""){if ($aPara[25] <> ""){$aPara[25] = $aPara[25]."\n".$sTmp;}else{$aPara[25] = $sTmp;}}
			$sTmp = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_CONTENTS3'));
			if ($sTmp <> ""){if ($aPara[25] <> ""){$aPara[25] = $aPara[25]."\n".$sTmp;}else{$aPara[25] = $sTmp;}}
			$sTmp = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_CONTENTS4'));
			if ($sTmp <> ""){if ($aPara[25] <> ""){$aPara[25] = $aPara[25]."\n".$sTmp;}else{$aPara[25] = $sTmp;}}
			$sTmp = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_CONTENTS5'));
			if ($sTmp <> ""){if ($aPara[25] <> ""){$aPara[25] = $aPara[25]."\n".$sTmp;}else{$aPara[25] = $sTmp;}}
			$sTmp = $module_cmn->fChangUTF8(oci_result($stmt, 'FLAW_CONTENTS6'));
			if ($sTmp <> ""){if ($aPara[25] <> ""){$aPara[25] = $aPara[25]."\n".$sTmp;}else{$aPara[25] = $sTmp;}}
			$aPara[26] = $module_cmn->fChangUTF8(oci_result($stmt, 'PLAN_NO'));													//計画NO
			$aPara[27] = $module_cmn->fChangUTF8(oci_result($stmt, 'PLAN_SEQ'));												//計画SEQ
			$aPara[28] = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD_KBN'));												//製品区分

			break;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}

	//赤伝緑伝情報データ取得処理
	//引数	$Reference_NO		伝票NO
	//		$Reference_SEQ		伝票SEQ
	public function fGetTrblData($Reference_NO,$Reference_SEQ){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql."SELECT TRIM(TRB.C_REFERENCE_NO) AS C_REFERENCE_NO";		//伝票NO
		$sql = $sql."	   ,N_REFERENCE_SEQ AS N_REFERENCE_SEQ";				//伝票SEQ
		$sql = $sql."	   ,TRIM(TRB.C_REFERENCE_KBN) AS C_REFERENCE_KBN";		//伝票種別
		$sql = $sql."	   ,TRIM(TRB.C_TARGET_SECTION_KBN) AS C_TARGET_SECTION_KBN";	//対象部門
		$sql = $sql."	   ,TRIM(TRB.C_POINTREF_NO) AS C_POINTREF_NO";			//代表伝票NO
		$sql = $sql."	   ,TRB.C_BUSYO_CD";									//起因部署CD
		$sql = $sql."	   ,TRB.V2_SUMBIKOU";									//集計用備考欄
		$sql = $sql."	   ,TRB.N_INCIDENT_YMD";								//伝票発行日
		$sql = $sql."	   ,TRIM(TRB.C_PROGRES_STAGE) AS C_PROGRES_STAGE";		//進捗状態
		$sql = $sql."	   ,TRB.V2_PROD_GRP_NM";								//生産グループ名
		$sql = $sql."	   ,TRB.V2_CUST_NM";									//得意先名
		$sql = $sql."	   ,TRB.V2_PROD_TANTO_NM1";								//生産担当者1
		$sql = $sql."	   ,TRB.V2_PROD_TANTO_NM2";								//生産担当者2
		$sql = $sql."	   ,TRB.V2_PROD_TANTO_NM3";								//生産担当者3
		$sql = $sql."	   ,TRB.V2_EXAM_GRP_NM";								//検査グループ名
		$sql = $sql."	   ,TRB.V2_HINGI_TANTO_NM";								//品技担当者
		$sql = $sql."	   ,TRIM(TRB.C_PROD_CD) AS C_PROD_CD";					//製品CD
		$sql = $sql."	   ,TRB.C_DIE_NO";										//金型番号
		$sql = $sql."	   ,TRB.V2_PROD_NM";									//製品名
		$sql = $sql."	   ,TRB.V2_DRW_NO";										//仕様番号
		$sql = $sql."	   ,TRIM(TRB.C_FLAW_LOT_NO) AS C_FLAW_LOT_NO";			//不具合ロットNO
		$sql = $sql."	   ,TRB.N_FLAW_LOT_QTY";								//不具合数量（個）
		$sql = $sql."	   ,TRB.N_UNIT_PRICE";									//単価（円）
		$sql = $sql."	   ,TRB.N_FLAW_PRICE";									//不具合金額（円）
		$sql = $sql."	   ,TRB.C_PLATING_CD";									//めっき先CD
		$sql = $sql."	   ,TRB.V2_PLATING_NM";									//めっき先名
		$sql = $sql."	   ,TRB.C_KBN";											//区分
		$sql = $sql."	   ,TRB.V2_MATERIAL_SPEC";								//材料仕様
		$sql = $sql."	   ,TRIM(TRB.C_FLAW_KBN1) AS C_FLAW_KBN1";				//不具合区分1
		$sql = $sql."	   ,TRIM(TRB.C_FLAW_KBN2) AS C_FLAW_KBN2";				//不具合区分2
		$sql = $sql."	   ,TRIM(TRB.C_FLAW_KBN3) AS C_FLAW_KBN3";				//不具合区分3
		$sql = $sql."	   ,TRB.V2_FLAW_CONTENTS";								//不具合内容
		$sql = $sql."	   ,TRB.N_SPECIAL_YMD";									//特別作業記録発行日
		$sql = $sql."	   ,TRB.N_SPECIAL";										//特別作業記録チェック
		$sql = $sql."	   ,TRB.N_PROCESS_PERIOD_YMD";							//処理期限
		$sql = $sql."	   ,TRB.V2_STRETCH_REASON";								//初期期限延伸理由
		// 2019/05/13 ADD START
		$sql = $sql."	   ,TRB.N_SUBMIT_YMD1";									//特別作業払い出し日1
		$sql = $sql."	   ,TRB.N_SUBMIT_YMD2";									//特別作業払い出し日2
		$sql = $sql."	   ,TRB.N_SUBMIT_YMD3";									//特別作業払い出し日3
		$sql = $sql."	   ,TRB.N_BACK_YMD1";									//特別作業戻り日1
		$sql = $sql."	   ,TRB.N_BACK_YMD2";									//特別作業戻り日2
		$sql = $sql."	   ,TRB.N_BACK_YMD3";									//特別作業戻り日3
		// 2019/05/13 ADD END
		$sql = $sql."	   ,TRB.N_INITIAL_PROCESS_PERIOD_YMD";					//初期処理期限
		$sql = $sql."	   ,TRB.C_TANTO_CD";									//品証担当者CD
		$sql = $sql."	   ,TRB.N_NON_ISSUE";									//発行不要
		$sql = $sql."	   ,TRB.C_INCIDENT_CD";									//報告書発行先部署・協力会社CD
		$sql = $sql."	   ,TRB.N_PROCESS_LIMIT_YMD";							//報告書処理期限
		$sql = $sql."	   ,TRB.N_RETURN_YMD";									//返却日
		$sql = $sql."	   ,TRB.N_COMP_YMD";									//完結日
		$sql = $sql."	   ,TRB.N_DECISION_YMD";								//処理判定日
		$sql = $sql."	   ,TRB.N_APPROVAL_YMD";								//製造部長承認日
		$sql = $sql."	   ,TRB.N_EXCLUDED";									//不良集計対象外
		$sql = $sql."	   ,TRB.N_SELECTION";									//選別工数（h）
		$sql = $sql."	   ,TRB.C_DUE_PROCESS";									//起因工程
		$sql = $sql."	   ,TRB.V2_COMENTS";									//その他コメント
		$sql = $sql."	   ,TRB.C_PARTNER_CD";									//起因部署・協力会社CD
		$sql = $sql."	   ,TRB.C_PROCESS";										//処理
		$sql = $sql."	   ,TRB.N_FAILURE_QTY";									//納入数量（個）
		$sql = $sql."	   ,TRB.N_DISPOSAL_QTY";								//廃棄数量（個）
		$sql = $sql."	   ,TRB.N_RETURN_QTY";									//返却数量（個）
		$sql = $sql."	   ,TRB.N_LOSS_QTY";									//調整ﾛｽ数量（個）
		$sql = $sql."	   ,TRB.N_EXCLUD_QTY";									//対象外数量（個）
		$sql = $sql."	   ,TRB.N_FAILURE_PRICE";								//納入金額（円）
		$sql = $sql."	   ,TRB.N_DISPOSAL_PRICE";								//廃棄金額（円）
		$sql = $sql."	   ,TRB.N_RETURN_PRICE";								//返却金額（円）
		$sql = $sql."	   ,TRB.N_LOSS_PRICE";									//調整ﾛｽ金額（円）
		$sql = $sql."	   ,TRB.N_EXCLUD_PRICE";								//対象外金額（円）
		$sql = $sql."	   ,TRB.N_UPDATE_COUNT ";								//更新回数
		$sql = $sql."	   ,VC3.V2_CUST_NM AS V2_BUSYO_NM";						//起因部署名
		$sql = $sql."	   ,VTT.V2_TANTO_NM AS V2_HINTAN_NM";					//品証担当者名
		$sql = $sql."	   ,VC1.V2_CUST_NM AS V2_INCIDENT_NM";					//報告書発行先部署・協力会社名
		$sql = $sql."	   ,VC2.V2_CUST_NM AS V2_PARTNER_NM";					//起因部署・協力会社名
		$sql = $sql."	   ,TRB.C_PLAN_NO";										//計画NO
		$sql = $sql."	   ,TRB.C_PLAN_SEQ";									//計画SEQ
		$sql = $sql."	   ,MS.製品_KU AS PROD_KBN";							//製品区分
		$sql = $sql."  FROM T_TR_TRBL TRB";
		$sql = $sql." 	   ,V_FL_CUST_INFO VC1";
		$sql = $sql."  	   ,V_FL_CUST_INFO VC2";
		$sql = $sql."  	   ,V_FL_CUST_INFO VC3";
		$sql = $sql." 	   ,V_FL_TANTO_INFO VTT";
		$sql = $sql." 	   ,M_製品@NF.US.ORACLE.COM@NF MS";
		$sql = $sql." WHERE TRIM(TRB.C_INCIDENT_CD) = VC1.C_CUST_CD(+)";
		$sql = $sql."   AND TRIM(TRB.C_PARTNER_CD) = VC2.C_CUST_CD(+)";
		$sql = $sql."   AND TRIM(TRB.C_BUSYO_CD) = VC3.C_CUST_CD(+)";
		$sql = $sql."   AND TRIM(TRB.C_TANTO_CD) = VTT.C_TANTO_CD(+)";
		$sql = $sql."   AND TRIM(TRB.C_PROD_CD) = TRIM(MS.製品_CD(+))";
		$sql = $sql."   AND TRB.C_REFERENCE_NO = '".trim($Reference_NO)."' ";
		$sql = $sql."   AND TRB.N_REFERENCE_SEQ = '".$Reference_SEQ."' ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$aPara[0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));					//伝票NO
			$aPara[1] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_REFERENCE_SEQ'));					//伝票SEQ
			$aPara[2] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_KBN'));					//伝票種別
			$aPara[3] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_TARGET_SECTION_KBN'));				//対象部門
			$aPara[4] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_POINTREF_NO'));					//代表伝票NO
			$aPara[5] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_BUSYO_CD'));						//起因部署CD
			$aPara[6] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_SUMBIKOU'));						//集計用備考欄
			$aPara[7] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_INCIDENT_YMD'));					//伝票発行日
			$aPara[8] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROGRES_STAGE'));					//進捗状態
			$aPara[9] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_GRP_NM'));					//生産グループ名
			$aPara[10] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM'));						//得意先名
			$aPara[11] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM1'));				//生産担当者1
			$aPara[12] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM2'));				//生産担当者2
			$aPara[13] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM3'));				//生産担当者3
			$aPara[14] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_EXAM_GRP_NM'));					//検査グループ名
			$aPara[15] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_HINGI_TANTO_NM'));				//品技担当者
			$aPara[16] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));						//製品CD
			$aPara[17] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_DIE_NO'));						//金型番号
			$aPara[18] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));						//製品名
			$aPara[19] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));						//仕様番号
			$aPara[20] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_LOT_NO'));					//不具合ロットNO
			$aPara[21] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_FLAW_LOT_QTY'));					//不具合数量（個）
			$aPara[22] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_UNIT_PRICE'));					//単価（円）
			$aPara[23] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_FLAW_PRICE'));					//不具合金額（円）
			$aPara[24] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PLATING_CD'));					//めっき先CD
			$aPara[25] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PLATING_NM'));					//めっき先名
			$aPara[26] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_KBN'));							//区分
			$aPara[27] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_MATERIAL_SPEC'));				//材料仕様
			$aPara[28] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN1'));						//不具合区分1
			$aPara[29] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN2'));						//不具合区分2
			$aPara[30] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN3'));						//不具合区分3
			$aPara[31] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_FLAW_CONTENTS'));				//不具合内容
			$aPara[32] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_SPECIAL_YMD'));					//特別作業記録発行日
			$aPara[33] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PROCESS_PERIOD_YMD'));			//処理期限
			$aPara[34] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_STRETCH_REASON'));				//初期期限延伸理由
			$aPara[35] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_INITIAL_PROCESS_PERIOD_YMD'));	//初期処理期限
			$aPara[36] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_TANTO_CD'));						//品証担当者CD
			$aPara[37] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_NON_ISSUE'));						//発行不要
			$aPara[38] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_INCIDENT_CD'));					//報告書発行先部署・協力会社CD
			$aPara[39] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PROCESS_LIMIT_YMD'));				//報告書処理期限
			$aPara[40] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_YMD'));					//返却日
			$aPara[41] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_COMP_YMD'));						//完結日
			$aPara[42] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_DECISION_YMD'));					//処理判定日
			$aPara[43] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_APPROVAL_YMD'));					//製造部長承認日
			$aPara[44] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_EXCLUDED'));						//不良集計対象外
			$aPara[45] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_SELECTION'));						//選別工数（h）
			$aPara[46] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_DUE_PROCESS'));					//起因工程
			$aPara[47] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_COMENTS'));						//その他コメント
			$aPara[48] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PARTNER_CD'));					//起因部署・協力会社CD
			$aPara[49] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROCESS'));						//処理
			$aPara[50] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_FAILURE_QTY'));					//納入数量（個）
			$aPara[51] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_DISPOSAL_QTY'));					//廃棄数量（個）
			$aPara[52] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_QTY'));					//返却数量（個）
			$aPara[53] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_LOSS_QTY'));						//調整ﾛｽ数量（個）
			$aPara[54] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_EXCLUD_QTY'));					//対象外数量（個）
			$aPara[55] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_FAILURE_PRICE'));					//納入金額（円）
			$aPara[56] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_DISPOSAL_PRICE'));				//廃棄金額（円）
			$aPara[57] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_PRICE'));					//返却金額（円）
			$aPara[58] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_LOSS_PRICE'));					//調整ﾛｽ金額（円）
			$aPara[59] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_EXCLUD_PRICE'));					//対象外金額（円）
			$aPara[60] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_UPDATE_COUNT'));					//更新回数
			$aPara[61] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_BUSYO_NM'));						//起因部署名
			$aPara[62] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_HINTAN_NM'));					//品証担当者名
			$aPara[63] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INCIDENT_NM'));					//報告書発行先部署・協力会社名
			$aPara[64] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PARTNER_NM'));					//起因部署・協力会社名
			$aPara[65] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PLAN_NO'));						//計画NO
			$aPara[66] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PLAN_SEQ'));						//計画SEQ
			// 2019/05/13 ADD START
			$aPara[67] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_SUBMIT_YMD1'));					//特別作業払い出し日1
			$aPara[68] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_SUBMIT_YMD2'));					//特別作業払い出し日2
			$aPara[69] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_SUBMIT_YMD3'));					//特別作業払い出し日3
			$aPara[70] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_BACK_YMD1'));						//特別作業戻り日1
			$aPara[71] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_BACK_YMD2'));						//特別作業戻り日2
			$aPara[72] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_BACK_YMD3'));						//特別作業戻り日13
			// 2019/05/13 ADD END
			$aPara[73] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_SPECIAL'));						//特別作業記録チェック
			$aPara[74] = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD_KBN'));						//製品区分
		}
		
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}

	//異常品（赤伝・緑伝）処理状況表示処理
	public function fTrblStatsSearch(){

		require_once("module_common.php");
		$module_cmn = new module_common;
		$aPara = array();

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//SQL取得
		$sql = "";
		$sql = $sql."SELECT N_LAST_SUM_0 ";
		$sql = $sql."	   ,N_LAST_SUM_1 ";
		$sql = $sql."	   ,N_LAST_SUM_2 ";
		$sql = $sql."	   ,N_LAST_SUM_3 ";
		$sql = $sql."	   ,N_LAST_SUM_4 ";
		$sql = $sql."	   ,N_LAST_SUM_5 ";
		$sql = $sql."	   ,N_THIS_SUM_0 ";
		$sql = $sql."	   ,N_THIS_SUM_1 ";
		$sql = $sql."	   ,N_THIS_SUM_2 ";
		$sql = $sql."	   ,N_THIS_SUM_3 ";
		$sql = $sql."	   ,N_THIS_SUM_4 ";
		$sql = $sql."	   ,N_THIS_SUM_5 ";
		$sql = $sql."	   ,N_TDAY_SUM_0 ";
		$sql = $sql."	   ,N_TDAY_SUM_1 ";
		$sql = $sql."	   ,N_TDAY_SUM_2 ";
		$sql = $sql."	   ,N_TDAY_SUM_3 ";
		$sql = $sql."	   ,N_TDAY_SUM_4 ";
		$sql = $sql."	   ,N_TDAY_SUM_5 ";
		$sql = $sql."  FROM V_FL_TRBL_STATUS ";

		//SQLの分析
		$stmt = oci_parse($conn, $sql);

		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$aPara[0][0]  = oci_result($stmt, 'N_LAST_SUM_0');
			$aPara[0][1]  = oci_result($stmt, 'N_LAST_SUM_1');
			$aPara[0][2]  = oci_result($stmt, 'N_LAST_SUM_2');
			$aPara[0][3]  = oci_result($stmt, 'N_LAST_SUM_3');
			$aPara[0][4]  = oci_result($stmt, 'N_LAST_SUM_4');
			$aPara[0][5]  = oci_result($stmt, 'N_LAST_SUM_5');
			$aPara[1][0]  = oci_result($stmt, 'N_THIS_SUM_0');
			$aPara[1][1]  = oci_result($stmt, 'N_THIS_SUM_1');
			$aPara[1][2]  = oci_result($stmt, 'N_THIS_SUM_2');
			$aPara[1][3]  = oci_result($stmt, 'N_THIS_SUM_3');
			$aPara[1][4]  = oci_result($stmt, 'N_THIS_SUM_4');
			$aPara[1][5]  = oci_result($stmt, 'N_THIS_SUM_5');
			$aPara[2][0]  = oci_result($stmt, 'N_TDAY_SUM_0');
			$aPara[2][1]  = oci_result($stmt, 'N_TDAY_SUM_1');
			$aPara[2][2]  = oci_result($stmt, 'N_TDAY_SUM_2');
			$aPara[2][3]  = oci_result($stmt, 'N_TDAY_SUM_3');
			$aPara[2][4]  = oci_result($stmt, 'N_TDAY_SUM_4');
			$aPara[2][5]  = oci_result($stmt, 'N_TDAY_SUM_5');
		}
		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}

	//赤伝緑伝情報データ一覧取得処理
	//引数	$aJoken			検索条件
	//		$aKbn			処理区分（0:検索 1:台帳 2:メール配信用期限切れチェック）
	public function fTrblSearch($aJoken,$aKbn = 0){

		require_once("module_common.php");
		$module_cmn = new module_common;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);

		if (!$conn) {
			$e = oci_error();	// oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}
		$iRowNo = 0;

		//Oracleのバージョン取得
		$strOraVer = oci_server_version($conn);
		//9iかそれ以外かで判断
		if(preg_match("/9i/",$strOraVer)){
			$bSearchOk = false;
		}else{
			$bSearchOk = true;
		}

		//SQL取得
		$sql = "";
		if ($aKbn == "0"){
			//検索表示
			$sql = $sql."SELECT TRBL.C_PROGRES_STAGE AS C_PROGRES_STAGE ";				//進捗状態
			$sql = $sql."	   ,TRIM(TRBL.C_REFERENCE_NO) AS C_REFERENCE_NO ";			//伝票NO
			$sql = $sql."	   ,TRBL.N_REFERENCE_SEQ AS N_REFERENCE_SEQ ";				//伝票SEQ
			$sql = $sql."	   ,TRIM(TRBL.C_PROCESS) AS C_PROCESS ";					//処理
			$sql = $sql."	   ,TRBL.N_INCIDENT_YMD AS N_INCIDENT_YMD ";				//伝票発行日
			$sql = $sql."	   ,TRBL.V2_CUST_NM AS V2_CUST_NM ";						//得意先名
			$sql = $sql."	   ,TRBL.V2_PROD_NM AS V2_PROD_NM ";						//製品名
			$sql = $sql."	   ,TRBL.V2_DRW_NO AS V2_DRW_NO ";							//仕様番号
			$sql = $sql."	   ,TRBL.C_FLAW_KBN1 AS C_FLAW_KBN1 ";						//不具合区分1
			$sql = $sql."	   ,TRBL.C_FLAW_KBN2 AS C_FLAW_KBN2 ";						//不具合区分2
			$sql = $sql."	   ,TRBL.C_FLAW_KBN3 AS C_FLAW_KBN3 ";						//不具合区分3
			$sql = $sql."	   ,MK1.区分明細名称_KJ AS C_FLAW_KBN_NM1 ";					//不具合区分名1
			$sql = $sql."	   ,MK2.区分明細名称_KJ AS C_FLAW_KBN_NM2 ";					//不具合区分名2
			$sql = $sql."	   ,MK3.区分明細名称_KJ AS C_FLAW_KBN_NM3 ";					//不具合区分名3
			$sql = $sql."	   ,TRBL.N_PROCESS_PERIOD_YMD AS N_PROCESS_PERIOD_YMD ";	//処理期限
			$sql = $sql."	   ,CST.V2_CUST_NM AS V2_INCIDENT_NM ";						//報告書発行先名
			$sql = $sql."	   ,TRBL.N_PROCESS_LIMIT_YMD AS N_PROCESS_LIMIT_YMD ";		//報告書処理期限
			$sql = $sql."	   ,TRBL.C_DUE_PROCESS ";									//起因工程
			$sql = $sql."	   ,TRBL.C_REFERENCE_KBN AS C_REFERENCE_KBN ";				//伝票種別
			$sql = $sql."	   ,CST2.V2_CUST_NM AS V2_BUSYO_NM ";						//起因部署
			$sql = $sql."	   ,TRBL.N_FLAW_LOT_QTY AS N_FLAW_LOT_QTY ";				//不具合数量
			$sql = $sql."	   ,TRBL.N_FLAW_PRICE AS N_FLAW_PRICE ";					//不具合金額
			$sql = $sql."	   ,TRBL.N_DISPOSAL_QTY AS N_DISPOSAL_QTY ";				//廃棄数量
			$sql = $sql."	   ,TRBL.N_DISPOSAL_PRICE AS N_DISPOSAL_PRICE ";			//廃棄金額
			$sql = $sql."	   ,TRBL.N_RETURN_QTY AS N_RETURN_QTY ";					//返却数量
			$sql = $sql."	   ,TRBL.N_RETURN_PRICE AS N_RETURN_PRICE ";				//返却金額
			$sql = $sql."	   ,TRBL.N_DECISION_YMD AS N_DECISION_YMD ";				//処理判定日
			$sql = $sql."	   ,TRBL.N_RETURN_YMD AS N_RETURN_YMD ";					//返却日
			$sql = $sql."	   ,TRBL.N_COMP_YMD AS N_COMP_YMD ";						//完結日
			$sql = $sql."	   ,TRIM(TRBL.C_FLAW_LOT_NO) AS C_FLAW_LOT_NO ";			//不具合ロットNO		// 2019/09/20 ADD END
		}elseif ($aKbn == "1"){
			//台帳出力
			$sql = $sql."SELECT TRIM(TRBL.C_REFERENCE_NO) AS C_REFERENCE_NO ";			//伝票NO
			$sql = $sql."	   ,TRBL.N_REFERENCE_SEQ AS N_REFERENCE_SEQ ";				//伝票SEQ
			$sql = $sql."	   ,TRIM(TRBL.C_REFERENCE_KBN) AS C_REFERENCE_KBN ";		//伝票区分
			$sql = $sql."	   ,TRIM(TRBL.C_POINTREF_NO) AS C_POINTREF_NO ";			//代表伝票NO
			$sql = $sql."	   ,TRBL.C_PROGRES_STAGE AS C_PROGRES_STAGE ";				//進捗状態
			$sql = $sql."	   ,TRBL.N_INCIDENT_YMD AS N_INCIDENT_YMD ";				//伝票発行日
			$sql = $sql."	   ,TRBL.V2_CUST_NM AS V2_CUST_NM ";						//得意先名
			$sql = $sql."	   ,TRIM(TRBL.C_PROD_CD) AS C_PROD_CD ";					//製品CD
			$sql = $sql."	   ,TRBL.V2_DRW_NO AS V2_DRW_NO ";							//仕様番号
			$sql = $sql."	   ,TRBL.V2_PROD_NM AS V2_PROD_NM ";						//製品名
			$sql = $sql."	   ,TRIM(TRBL.C_DIE_NO) AS C_DIE_NO ";						//金型番号
			$sql = $sql."	   ,TRIM(TRBL.C_FLAW_LOT_NO) AS C_FLAW_LOT_NO ";			//不具合ロットNO
			$sql = $sql."	   ,TRIM(TRBL.C_FLAW_KBN1) AS C_FLAW_KBN1 ";				//不具合区分1
			$sql = $sql."	   ,TRIM(TRBL.C_FLAW_KBN2) AS C_FLAW_KBN2 ";				//不具合区分2
			$sql = $sql."	   ,TRIM(TRBL.C_FLAW_KBN3) AS C_FLAW_KBN3 ";				//不具合区分3
			$sql = $sql."	   ,MK1.区分明細名称_KJ AS C_FLAW_KBN_NM1 ";					//不具合区分名1
			$sql = $sql."	   ,MK2.区分明細名称_KJ AS C_FLAW_KBN_NM2 ";					//不具合区分名2
			$sql = $sql."	   ,MK3.区分明細名称_KJ AS C_FLAW_KBN_NM3 ";					//不具合区分名3
			$sql = $sql."	   ,TRBL.V2_FLAW_CONTENTS ";								//不具合内容
			$sql = $sql."	   ,TRBL.C_KBN ";											//区分
			$sql = $sql."	   ,TRBL.V2_PROD_TANTO_NM1 ";								//生産担当者1
			$sql = $sql."	   ,TRBL.V2_PROD_TANTO_NM2 ";								//生産担当者2
			$sql = $sql."	   ,TRBL.V2_PROD_TANTO_NM3 ";								//生産担当者3
			$sql = $sql."	   ,TRBL.V2_PROD_GRP_NM ";									//生産グループ名
			$sql = $sql."	   ,TRBL.N_FLAW_LOT_QTY AS N_FLAW_LOT_QTY ";				//不具合数量
			$sql = $sql."	   ,TRBL.N_UNIT_PRICE ";									//単価（円）
			$sql = $sql."	   ,TRBL.N_FLAW_PRICE AS N_FLAW_PRICE ";					//不具合金額
			$sql = $sql."	   ,'' ";													//状況
			$sql = $sql."	   ,TRBL.N_SPECIAL_YMD";									//特別作業記録発行日
			$sql = $sql."	   ,TRBL.N_PROCESS_PERIOD_YMD ";							//処理期限
			$sql = $sql."	   ,TRIM(TNT.V2_TANTO_NM) AS C_TANTO_CD ";					//品証担当者
			//$sql = $sql."	   ,TRBL.V2_HINGI_TANTO_NM ";								//品技担当者
			$sql = $sql."	   ,TRIM(TRBL.C_PROCESS) AS C_PROCESS ";					//処理
			$sql = $sql."	   ,TRBL.N_DECISION_YMD ";									//処理判定日
			$sql = $sql."	   ,TRBL.N_FAILURE_QTY ";									//納入数量（個）
			$sql = $sql."	   ,TRBL.N_DISPOSAL_QTY ";									//廃棄数量（個）
			$sql = $sql."	   ,TRBL.N_RETURN_QTY";										//返却数量（個）
			$sql = $sql."	   ,TRBL.N_LOSS_QTY ";										//調整ﾛｽ数量（個）
			$sql = $sql."	   ,TRBL.N_EXCLUD_QTY ";									//対象外数量（個）
			$sql = $sql."	   ,TRBL.N_SELECTION ";										//選別工数（h）
			$sql = $sql."	   ,TRBL.N_DISPOSAL_PRICE ";								//廃棄金額（円）
			$sql = $sql."	   ,TRBL.N_LOSS_PRICE ";									//調整ﾛｽ金額（円）
			$sql = $sql."	   ,CST2.V2_CUST_NM AS V2_BUSYO_NM ";						//起因部署名
			$sql = $sql."	   ,CST.V2_CUST_NM AS V2_INCIDENT_NM ";						//発行先名
			$sql = $sql."	   ,'' ";													//発行日
			$sql = $sql."	   ,TRBL.N_PROCESS_LIMIT_YMD";								//指定回答日
			//2019/05/13 ADD START
			$sql = $sql."	   ,TRBL.N_RETURN_YMD";										//返却日
			//2019/05/13 ADD END
			$sql = $sql."	   ,TRBL.N_COMP_YMD";										//完結日
			$sql = $sql."	   ,'' ";													//発行日（特別作業記録管理台帳）
			$sql = $sql."	   ,'' ";													//処理期限
			$sql = $sql."	   ,TRBL.N_SUBMIT_YMD1 ";									//払い出し日1
			$sql = $sql."	   ,TRBL.N_SUBMIT_YMD2 ";									//払い出し日2
			$sql = $sql."	   ,TRBL.N_SUBMIT_YMD3 ";									//払い出し日3
			$sql = $sql."	   ,TRBL.N_BACK_YMD1 ";										//特別作業戻り日1
			$sql = $sql."	   ,TRBL.N_BACK_YMD2 ";										//特別作業戻り日2
			$sql = $sql."	   ,TRBL.N_BACK_YMD3 ";										//特別作業戻り日3
			$sql = $sql."	   ,TRBL.N_INS_YMD ";										//登録日
			$sql = $sql."	   ,TRBL.N_EXCLUDED";										//不良集計対象外
			$sql = $sql."	   ,TRBL.C_INCIDENT_CD";									//報告書発行先部署・協力会社CD 
			$sql = $sql."	   ,TRBL.N_SPECIAL";										//特別作業記録チェック
		}elseif ($aKbn == "2"){
			//品質改善報告書・協力工場不良品連絡書の期限切れメール通知
			$sql = $sql."SELECT TRIM(TRBL.C_REFERENCE_NO) AS C_REFERENCE_NO ";			//伝票NO
			$sql = $sql."	   ,TRBL.N_REFERENCE_SEQ AS N_REFERENCE_SEQ ";				//伝票SEQ
			$sql = $sql."	   ,TRIM(TRBL.C_TARGET_SECTION_KBN) AS C_TARGET_SECTION_KBN ";	//対象部門
			$sql = $sql."	   ,TRIM(TRBL.C_REFERENCE_KBN) AS C_REFERENCE_KBN ";		//伝票区分
			$sql = $sql."	   ,TRIM(TRBL.C_POINTREF_NO) AS C_POINTREF_NO ";			//代表伝票NO
			$sql = $sql."	   ,TRBL.C_PROGRES_STAGE AS C_PROGRES_STAGE ";				//進捗状態
			$sql = $sql."	   ,TRBL.V2_PROD_NM AS V2_PROD_NM ";						//製品名
			$sql = $sql."	   ,TRBL.V2_DRW_NO AS V2_DRW_NO ";							//仕様番号
			$sql = $sql."	   ,TRBL.V2_CUST_NM AS V2_CUST_NM ";						//得意先名
			$sql = $sql."	   ,CST.V2_CUST_NM AS V2_INCIDENT_NM ";						//報告書発行先部署・協力会社CD
			$sql = $sql."	   ,TRBL.N_PROCESS_LIMIT_YMD AS N_PROCESS_LIMIT_YMD ";		//報告書処理期限
			$sql = $sql."	   ,TRIM(TRBL.C_FLAW_LOT_NO) AS C_FLAW_LOT_NO ";			//不具合ロットNO
			$sql = $sql."	   ,TRBL.N_FLAW_LOT_QTY ";									//不具合数量（個）
			$sql = $sql."	   ,TRBL.N_FLAW_PRICE ";									//不具合金額（円）
			$sql = $sql."	   ,TRBL.V2_FLAW_CONTENTS ";								//不具合内容
			$sql = $sql."	   ,TRIM(TRBL.C_INCIDENT_CD) AS C_INCIDENT_CD ";			//報告書発行先部署・協力会社CD 
		}
		$sql = $sql."  FROM T_TR_TRBL TRBL ";
		$sql = $sql."	   ,V_FL_CUST_INFO CST ";
		$sql = $sql."	   ,V_FL_CUST_INFO CST2 ";
		$sql = $sql."	   ,V_FL_TANTO_INFO TNT ";
		$sql = $sql."	   ,(SELECT * FROM M_区分@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM WHERE 区分_CD = '085') MK1 ";
		$sql = $sql."	   ,(SELECT * FROM M_区分@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM WHERE 区分_CD = '085') MK2 ";
		$sql = $sql."	   ,(SELECT * FROM M_区分@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM WHERE 区分_CD = '085') MK3 ";
		$sql = $sql." WHERE TRIM(TRBL.C_INCIDENT_CD) = CST.C_CUST_CD(+) ";
		$sql = $sql."   AND TRIM(TRBL.C_BUSYO_CD) = CST2.C_CUST_CD(+) ";
		$sql = $sql."   AND TRIM(TRBL.C_TANTO_CD) = TNT.C_TANTO_CD(+) ";
		$sql = $sql."   AND TRBL.C_FLAW_KBN1 = MK1.区分明細_CD(+) ";
		$sql = $sql."   AND TRBL.C_FLAW_KBN2 = MK2.区分明細_CD(+) ";
		$sql = $sql."   AND TRBL.C_FLAW_KBN3 = MK3.区分明細_CD(+) ";
		//対象部門
		if($aJoken[0] <> "-1" && $aJoken[0] <> ""){
			$sql = $sql." AND TRIM(TRBL.C_TARGET_SECTION_KBN) = :sTargetSectionKbn ";
		}
		//進捗状態
		if($aJoken[1] <> "-1" && $aJoken[1] <> ""){
			$sql = $sql." AND TRIM(TRBL.C_PROGRES_STAGE) = :sPgrsStage ";
		}
		//伝票NO
		if($aJoken[2] <> ""){
			$sql = $sql." AND TRIM(TRBL.C_REFERENCE_NO) LIKE '%' || :sRrceNo || '%' ";
		}
		//起因部署
		if($aJoken[3] <> ""){
			//$sql = $sql." AND TRIM(CST2.V2_CUST_NM) LIKE '%' || :sBusyoNm || '%' ";
			$sql = $sql." AND TRIM(CST2.V2_CUST_NM) LIKE '%".trim($aJoken[3])."%' ";
		}
		//製品CD
		if($aJoken[4] <> ""){
			$sql = $sql." AND TRIM(TRBL.C_PROD_CD) LIKE '%' || :sProdCd || '%' ";
		}
		//仕様番号
		if($aJoken[5] <> ""){
			$sql = $sql." AND TRBL.V2_DRW_NO LIKE '%' || :sDrwNo || '%' ";
		}
		//製品名
		if($aJoken[6] <> ""){
			$sql = $sql." AND TRIM(TRBL.V2_PROD_NM) LIKE '%' || :sProdNm || '%' ";
		}
		//得意先名
		if($aJoken[7] <> ""){
			//$sql = $sql." AND TRIM(TRBL.V2_CUST_NM) LIKE '%' || :sCustNm || '%' ";
			$sql = $sql." AND TRIM(TRBL.V2_CUST_NM) LIKE '%".trim($aJoken[7])."%' ";
		}
		//不具合区分
		if($aJoken[8] <> "-1" && $aJoken[8] <> ""){
			$sql = $sql." AND (TRIM(TRBL.C_FLAW_KBN1) = :sFlawKbn ";
			$sql = $sql."	   OR TRIM(TRBL.C_FLAW_KBN2) = :sFlawKbn ";
			$sql = $sql."	   OR TRIM(TRBL.C_FLAW_KBN3) = :sFlawKbn) ";
		}
		//伝票発行日（開始）
		if($aJoken[9] <> ""){
			$sql = $sql." AND N_INCIDENT_YMD >= ".str_replace("/","",$module_cmn->fChangSJIS(trim($aJoken[9])))." ";
		}
		//伝票発行日（終了）
		if($aJoken[10] <> ""){
			$sql = $sql." AND N_INCIDENT_YMD <= ".str_replace("/","",$module_cmn->fChangSJIS(trim($aJoken[10])))." ";
		}
		//処理期限
		if($aJoken[11] <> ""){
			$sql = $sql." AND N_PROCESS_PERIOD_YMD >= ".str_replace("/","",$module_cmn->fChangSJIS(trim($aJoken[11])))."";
		}
		if($aJoken[12] <> ""){
			$sql = $sql." AND N_PROCESS_PERIOD_YMD <= ".str_replace("/","",$module_cmn->fChangSJIS(trim($aJoken[12])))." ";
		}
		//廃棄数量／金額有のみチェック
		if($aJoken[13] <> "-1" && $aJoken[13] <> ""){
			$sql = $sql." AND N_DISPOSAL_QTY <> 0 ";
			$sql = $sql." AND N_DISPOSAL_PRICE <> 0 ";
		}
		//処理判定日
		if($aJoken[14] <> "-1" && $aJoken[14] <> ""){
			//$sql = $sql." AND N_DECISION_YMD BETWEEN :sDecisionF AND :sDecisionT ";
			$sql = $sql." AND N_DECISION_YMD >= ".date('Ymd', strtotime('first day of'.$aJoken[14].'01'))." ";
			$sql = $sql." AND N_DECISION_YMD <= ".date('Ymd', strtotime('last day of'.$aJoken[14].'01'))." ";
			
		}
		//報告書
		if($aJoken[15] <> "-1" && $aJoken[15] <> ""){
			switch ($aJoken[15]){
				case "0":	//未返却（返却日･完了日が空欄の場合）
					$sql = $sql." AND N_RETURN_YMD = 0 ";
					$sql = $sql." AND N_COMP_YMD = 0 ";
					// 2019/05/13 ADD START
					//発行不要も除外
					$sql = $sql." AND (N_NON_ISSUE = 0 OR N_NON_ISSUE IS NULL) ";
					// 2019/05/13 ADD END
					break;
				case "1":	//有効性評価中（返却日のみ入力の場合）
					$sql = $sql." AND N_RETURN_YMD <> 0 ";
					$sql = $sql." AND N_COMP_YMD = 0 ";
					break;
				case "2":	//完了（返却日･完了日入力の場合）
					//$sql = $sql." AND N_RETURN_YMD <> 0 ";
					$sql = $sql." AND N_COMP_YMD <> 0 ";
					break;
				 default:
					break;
			}
		}
		//2019/08/01 ADD START
		//発行先区分
		if($aJoken[16] <> "-1" && $aJoken[16] <> ""){
			if($aJoken[16] == "0"){
				$sql = $sql." AND (C_PARTNER_CD LIKE 'K%' OR C_PARTNER_CD LIKE 'F%') ";
			}elseif($aJoken[16] == "1"){
				$sql = $sql." AND (C_PARTNER_CD NOT LIKE 'K%' OR C_PARTNER_CD NOT LIKE 'F%') ";
			//2:社内/協力工場は絞込みなし
			}elseif($aJoken[16] == "3"){
				$sql = $sql." AND TRIM(C_PARTNER_CD) = '' ";
			}
		}
		//期限切れチェック
		if($aJoken[100] <> ""){
			$sql = $sql." AND N_PROCESS_LIMIT_YMD = ".$aJoken[100]." ";
			$sql = $sql." AND N_RETURN_YMD = 0 ";
		}
		//2019/08/01 ADD END
		
		//2019/09/20 ADD START
		//不具合ロットNO
		if($aJoken[17] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS(trim($aJoken[17]));
			$sql = $sql." AND TRIM(TRBL.C_FLAW_LOT_NO) LIKE '%".$module_cmn->fChangSJIS(trim($aJoken[17]))."%' ";
		}
		//2019/09/20 ADD END
		
		$sql = $sql." ORDER BY TRBL.N_INCIDENT_YMD DESC,TRBL.C_REFERENCE_NO DESC ,TRBL.N_REFERENCE_SEQ ";

		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの解析
		$stmt = oci_parse($conn, $sql);

		
		//初期に検索結果無しのパラメータを設定しておく(検索結果があれば上書き)
		$aPara[0][0] = "N006";

		//検索条件
		//対象部門
		if($aJoken[0] <> "-1" && $aJoken[0] <> ""){
			oci_bind_by_name($stmt, ":sTargetSectionKbn", $aJoken[0], -1);
		}
		//進捗状態
		if($aJoken[1] <> "-1" && $aJoken[1] <> ""){
			oci_bind_by_name($stmt, ":sPgrsStage", $aJoken[1], -1);
		}
		//伝票NO
		if($aJoken[2] <> ""){
			$sTmpJoken = trim($aJoken[2]);
			oci_bind_by_name($stmt, ":sRrceNo", $sTmpJoken, -1);
		}
		//起因部署
/* 		if($aJoken[3] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS(trim($aJoken[3]));
			oci_bind_by_name($stmt, ":sBusyoNm", $sTmpJoken, -1);
		} */
		//製品CD
		if($aJoken[4] <> ""){
			$sTmpJoken = trim($aJoken[4]);
			oci_bind_by_name($stmt, ":sProdCd", $sTmpJoken, -1);
		}
		//仕様番号
		if($aJoken[5] <> ""){
			$sTmpJoken = trim($aJoken[5]);
			oci_bind_by_name($stmt, ":sDrwNo", $sTmpJoken, -1);
		}
		//製品名
		if($aJoken[6] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS(trim($aJoken[6]));
			oci_bind_by_name($stmt, ":sProdNm", $sTmpJoken, -1);
		}
		//得意先名
/* 		if($aJoken[7] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS(trim($aJoken[7]));
			oci_bind_by_name($stmt, ":sCustNm", $sTmpJoken, -1);
		} */
		//不具合区分
		if($aJoken[8] <> "-1" && $aJoken[8] <> ""){
			oci_bind_by_name($stmt, ":sFlawKbn", $aJoken[8], -1);
		}
		//伝票発行日（開始）
/* 		if($aJoken[9] <> ""){
			$sTmpJoken = str_replace("/","",$module_cmn->fChangSJIS(trim($aJoken[9])));
			oci_bind_by_name($stmt, ":sIncidentF", $sTmpJoken, -1);
			
		}
		//伝票発行日（終了）
		if($aJoken[10] <> ""){
			$sTmpJoken = str_replace("/","",$module_cmn->fChangSJIS(trim($aJoken[10])));
			oci_bind_by_name($stmt, ":sIncidentT", $sTmpJoken, -1);
		} */
/* 		//処理期限（開始）
		if($aJoken[11] <> ""){
			$sTmpJoken = str_replace("/","",$module_cmn->fChangSJIS(trim($aJoken[11])));
			oci_bind_by_name($stmt, ":sProcessPeriodF", $sTmpJoken, -1);
		}
		//処理期限（終了）
		if($aJoken[12] <> ""){
			$sTmpJoken = str_replace("/","",$module_cmn->fChangSJIS(trim($aJoken[12])));
			oci_bind_by_name($stmt, ":sProcessPeriodT", $sTmpJoken, -1);
		} */
		//処理判定日
/* 		if($aJoken[14] <> "-1" && $aJoken[14] <> ""){
			oci_bind_by_name($stmt, ":sDecisionF", date('Ymd', strtotime('first day of'.$aJoken[14].'01')), -1);
			oci_bind_by_name($stmt, ":sDecisionT", date('Ymd', strtotime('last day of'.$aJoken[14].'01')), -1);
		} */
		
		//2019/09/20 ADD START
		//不具合ロットNO
/* 		if($aJoken[17] <> ""){
			$sTmpJoken = $module_cmn->fChangSJIS(trim($aJoken[17]));
			oci_bind_by_name($stmt, ":sFlawLotNo", $sTmpJoken, -1);
		} */
		//2019/09/20 ADD END
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		
		//SQL実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		//当日
		$today = date("Ymd");
		
		while (oci_fetch($stmt)){
			if ($aKbn == "0"){
				$sTmpFlaw = "";
				$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));
				$aPara[$i][1] = $this->fDispKbn("C38",$module_cmn->fChangUTF8(oci_result($stmt, 'C_PROGRES_STAGE')));
				$aPara[$i][2] = $this->fDispKbn("C37",$module_cmn->fChangUTF8(oci_result($stmt, 'C_PROCESS')));
				$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_INCIDENT_YMD'));
				$aPara[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_CUST_NM'));
				$aPara[$i][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));
				$aPara[$i][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));
				$aPara[$i][7] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_REFERENCE_SEQ'));
				$aPara[$i][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_DECISION_YMD'));
				if($module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN1'))<>-1){
					$sTmpFlaw = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN_NM1'));
				}
				if($module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN2'))<>-1){
					if($sTmpFlaw <> ""){
						$sTmpFlaw = $sTmpFlaw."／".$module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN_NM2'));
					}else{
						$sTmpFlaw = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN_NM2'));
					}
				}
				if($module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN3'))<>-1){
					if($sTmpFlaw <> ""){
						$sTmpFlaw = $sTmpFlaw."／".$module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN_NM3'));
					}else{
						$sTmpFlaw = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN_NM3'));
					}
				}
				$aPara[$i][9] = $sTmpFlaw;
				$aPara[$i][10] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PROCESS_PERIOD_YMD'));
				$aPara[$i][11] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_FLAW_LOT_QTY'));
				$aPara[$i][12] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_FLAW_PRICE'));
				$aPara[$i][13] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_DISPOSAL_QTY'));
				$aPara[$i][14] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_DISPOSAL_PRICE'));
				$aPara[$i][15] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_QTY'));
				$aPara[$i][16]  = $module_cmn->fChangUTF8(oci_result($stmt, 'N_RETURN_PRICE'));
				$aPara[$i][17] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_INCIDENT_NM'));
				$aPara[$i][18] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_PROCESS_LIMIT_YMD'));
				$aPara[$i][19] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROGRES_STAGE'));
				$aPara[$i][20] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_REFERENCE_KBN')));
				//廃棄数量チェック
				$aPara[$i][21] = $aJoken[11];
				//処理：「一部納品廃棄」「一部納品返却」「調整ﾛｽ」は「選別」表記
				switch ($module_cmn->fChangUTF8(oci_result($stmt,'C_PROCESS'))){
					case "-1":
						$aPara[$i][20] = "保留";
						break;
					case "3":
						$aPara[$i][20] = "選別";
						break;
					case "4":
						$aPara[$i][20] = "選別";
						break;
					case "5":
						$aPara[$i][20] = "選別";
						break;
					 default:
						$aPara[$i][20] = $this->fDispKbn("C37",$module_cmn->fChangUTF8(oci_result($stmt, 'C_PROCESS')));
						break;
				}
				//報告書完結日
				$aPara[$i][22] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_COMP_YMD')));
				//不具合ロットNO
				$aPara[$i][23] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_FLAW_LOT_NO')));		// 2019/09/20 ADD END
				
				$aPara[$i][50] = "";
				$aPara[$i][51] = "";
				$aPara[$i][52] = "";

				//セル色
				//進捗状態が処理承認済の場合はグレー
				if(trim($aPara[$i][19]) == "2" ){
					$aPara[$i][50] = "gray";
				}

				//処理期限が本日を過ぎている場合はピンク
				if($today > $aPara[$i][10]){
					// 2019/05/13 ADD END
					//かつ処理判定日なし、処理「保留」はピンク表示
					if($aPara[$i][8] == 0 && $module_cmn->fChangUTF8(oci_result($stmt,'C_PROCESS')) == -1){
						
						$aPara[$i][51] = "limit";
					}
					// 2019/05/13 ADD END
				}

				//報告書処理期限が入っていて本日を過ぎているかつ返却日に日付が未入力場合はピンク
				if($aPara[$i][18] <> 0 && $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_RETURN_YMD'))) == 0){
					if($today > $aPara[$i][18]){
						$aPara[$i][52] = "limit";
					}
				}
			}elseif ($aKbn == "1"){
				$aPara[$i][0] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_REFERENCE_NO')));
				$aPara[$i][1] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_REFERENCE_SEQ')));
				$aPara[$i][2] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_REFERENCE_KBN')));
				$aPara[$i][3] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_PROGRES_STAGE')));
				$aPara[$i][4] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_INCIDENT_YMD')));
				$aPara[$i][5] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_CUST_NM')));
				$aPara[$i][6] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_PROD_CD')));
				$aPara[$i][7] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_DRW_NO')));
				$aPara[$i][8] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_PROD_NM')));
				$aPara[$i][9] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_DIE_NO')));
				$aPara[$i][10] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_FLAW_LOT_NO')));
				$aPara[$i][11] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_FLAW_KBN_NM1')));
				$aPara[$i][12] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_FLAW_KBN_NM2')));
				$aPara[$i][13] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_FLAW_KBN_NM3')));
				$aPara[$i][14] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_FLAW_CONTENTS')));
				$aPara[$i][15] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_KBN')));
				$aPara[$i][16] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_PROD_TANTO_NM1')));
				$aPara[$i][17] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_PROD_TANTO_NM2')));
				$aPara[$i][18] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_PROD_TANTO_NM3')));
				$aPara[$i][19] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_PROD_GRP_NM')));
				$aPara[$i][20] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_FLAW_LOT_QTY')));
				$aPara[$i][21] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_UNIT_PRICE')));
				$aPara[$i][22] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_FLAW_PRICE')));
				$aPara[$i][23] = "";
				$aPara[$i][24] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_SPECIAL_YMD')));
				$aPara[$i][25] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_PROCESS_PERIOD_YMD')));
				$aPara[$i][26] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_TANTO_CD')));
				$aPara[$i][27] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_PROCESS')));
				$aPara[$i][28] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_DECISION_YMD')));
				$aPara[$i][29] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_FAILURE_QTY')));
				$aPara[$i][30] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_DISPOSAL_QTY')));
				$aPara[$i][31] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_RETURN_QTY')));
				$aPara[$i][32] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_LOSS_QTY')));
				$aPara[$i][33] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_EXCLUD_QTY')));
				$aPara[$i][34] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_SELECTION')));
				$aPara[$i][35] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_DISPOSAL_PRICE')));
				$aPara[$i][36] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_LOSS_PRICE')));
				$aPara[$i][37] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_BUSYO_NM')));
				$aPara[$i][38] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_INCIDENT_NM')));
				$aPara[$i][39] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_PROCESS_LIMIT_YMD')));
				$aPara[$i][40] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_RETURN_YMD')));
				$aPara[$i][41] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_COMP_YMD')));
				$aPara[$i][42] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_SUBMIT_YMD1')));
				$aPara[$i][43] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_SUBMIT_YMD2')));
				$aPara[$i][44] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_SUBMIT_YMD3')));
				$aPara[$i][45] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_BACK_YMD1')));
				$aPara[$i][46] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_BACK_YMD2')));
				$aPara[$i][47] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_BACK_YMD3')));
				$aPara[$i][48] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_POINTREF_NO')));
				$aPara[$i][49] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_INS_YMD')));
				$aPara[$i][50] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_EXCLUDED')));
				$aPara[$i][51] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_INCIDENT_CD')));
				$aPara[$i][52] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_SPECIAL')));
			}elseif ($aKbn == "2"){
				$aPara[$i][0] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_REFERENCE_NO')));
				$aPara[$i][1] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_REFERENCE_SEQ')));
				$aPara[$i][2] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_TARGET_SECTION_KBN')));
				$aPara[$i][3] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_REFERENCE_KBN')));
				$aPara[$i][4] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_POINTREF_NO')));
				$aPara[$i][5] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_PROGRES_STAGE')));
				$aPara[$i][6] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_PROD_NM')));
				$aPara[$i][7] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_DRW_NO')));
				$aPara[$i][8] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_CUST_NM')));
				$aPara[$i][9] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_INCIDENT_NM')));
				$aPara[$i][10] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_PROCESS_LIMIT_YMD')));
				$aPara[$i][11] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_FLAW_LOT_NO')));
				$aPara[$i][12] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_FLAW_LOT_QTY')));
				$aPara[$i][13] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'N_FLAW_PRICE')));
				$aPara[$i][14] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'V2_FLAW_CONTENTS')));
				$aPara[$i][15] = $module_cmn->fChangUTF8(trim(oci_result($stmt, 'C_INCIDENT_CD')));
			}
			$i = $i + 1;
		}

		//1000件以上あった
		if(count($aPara) > 1000){
			$aPara[0][0] = "E016";
			return $aPara;
		}

		//リソース開放
		oci_free_statement($stmt);

		//Oracle接続切断
		oci_close($conn);

		return $aPara;

	}

 	//eValueNSから集計担当者グループ所属有無確認
	//引数	$strUserCd		参照者CD
	public function fChkMstUserNS($strUserCd){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$strGrpCd = "NS_W02100";
		$blnRtn = 0;

		try{
			$conn_ms = odbc_connect("Driver={SQL Server};Server=".$this->gNSServer.";Database=".$this->gNSDbName
									,$this->gNSUserid
									,$this->gNSPasswd);
			
			if (!$conn_ms) {
				$e = odbc_errormsg();
				session_destroy();
				die("データベースに接続できません");
			}
			
			//検索SQL作成
			$sql = "";
			$sql = $sql."SELECT COUNT(UMS.USER_UserNum) CNT ";
			$sql = $sql."  FROM eValue.GROUP_MST GMS ";
			$sql = $sql."	   ,eValue.USER_MST UMS" ;
			$sql = $sql."	   ,eValue.GUSER_MST GUM" ;
			$sql = $sql." WHERE GMS.GROUP_GroupNum = GUM.GUSER_GroupNum ";
			$sql = $sql."   AND UMS.USER_UserNum = GUM.GUSER_UserNum ";
			$sql = $sql."   AND GMS.GROUP_GroupNum = '".$strGrpCd."'";
			$sql = $sql."   AND UMS.USER_LoginName = '".$strUserCd."'";

			
			//SQL実行
			$res = NULL;
			$res = odbc_prepare($conn_ms, $sql);
			odbc_execute($res);
			
			while(odbc_fetch_row($res)){
				if(odbc_result($res,"CNT") > 0){
					$blnRtn = 1;
				}
			}
			
			//クエリー結果の開放
			odbc_free_result($res);
			//コネクションのクローズ
			odbc_close($conn_ms);
			
			return $blnRtn;

		}catch(Exception $e){
			return false;
		}
	}

	//概要：SMART2区分マスタコンボボックス作成処理
	//処理内容：SMART2区分マスタの値でリストボックスを作成する
	//引数
	//		$strKbnCd(区分コード)
	//		$strKbnMeiCd(区分明細コード)
	public function fMakeComboS2($strKbnCd,$strKbnMeiCd){

		require_once("module_common.php");
		$module_cmn = new module_common;

//		$strKbnCd = str_pad($strKbnCd, 3, 0, STR_PAD_LEFT);
//		$strKbnMeiCd = str_pad($strKbnMeiCd, 6, 0, STR_PAD_LEFT);

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gNUserID, $this->gNPass, $this->gNDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//区分マスタ検索SQL
		$sql = "";
		$sql = $sql."SELECT 区分明細_CD AS V2_KBN_MEI_CD ";
		$sql = $sql."	   ,区分明細名称_KJ AS V2_KBN_MEI_NM ";
		$sql = $sql."  FROM M_区分";
		$sql = $sql." WHERE 区分_CD = '".$strKbnCd."'";
		$sql = $sql."   AND 削除日_YMD = '0'";
		$sql = $sql." ORDER BY 区分明細_CD ";

		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			if(trim($strKbnMeiCd) == trim(oci_result($stmt, 'V2_KBN_MEI_CD'))){
				echo "<option selected value=".oci_result($stmt, 'V2_KBN_MEI_CD').">".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_KBN_MEI_NM'))."</option>";
			}else{
				echo "<option value=".oci_result($stmt, 'V2_KBN_MEI_CD').">".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_KBN_MEI_NM'))."</option>";
			}
		}

		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);
	}

	//概要：SMART2区分マスタ名称取得処理
	//処理内容：SMART2区分マスタの値を返す
	//引数
	//		$strKbnCd(区分コード)
	//		$strKbnMeiCd(区分明細コード)
	public function fDispKbnS2($strKbnCd,$strKbnMeiCd){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$strMeisho = "";
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gNUserID, $this->gNPass, $this->gNDB);

		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//組織マスタ検索SQL
		$sql = "";
		$sql = $sql."SELECT 区分明細_CD	   AS V2_KBN_MEI_CD ";
		$sql = $sql."      ,区分明細名称_KJ AS V2_KBN_MEI_NM ";
		$sql = $sql."  FROM M_区分 ";
		$sql = $sql." WHERE 区分_CD = '".$strKbnCd."'";
		$sql = $sql."   AND 区分明細_CD = '".trim($strKbnMeiCd)."'";
		$sql = $sql."   AND 削除日_YMD = '0'";
		$sql = $sql." ORDER BY V2_KBN_MEI_CD";

		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		if(oci_fetch($stmt)) {
			$strMeisho = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_KBN_MEI_NM'));
		}
		//リソースの開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);

		return $strMeisho;
	}
//2019/04/01 AD END T.FUJITA

//2019/05/13 AD START T.FUJITA
	//SMART2カレンダマスタから対象日からの稼働日を取得
	//引数	$piDate		対象日付
	//		$pintDay	稼働日数
	public function fGetCalendar($piDate,$pintDay){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$i = 0;

		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gNUserID, $this->gNPass, $this->gNDB);
		if (!$conn) {
			$e = oci_error();	//oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		$sql = "";
		$sql = $sql."SELECT カレンダ_YMD AS N_YMD ";
		$sql = $sql."  FROM M_カレンダ ";
		$sql = $sql." WHERE 削除日_YMD = 0 ";
		$sql = $sql."   AND 稼働日_KU = 1 ";
		if($pintDay>=0){
			$sql = $sql."   AND カレンダ_YMD BETWEEN ".$piDate." AND TO_NUMBER(TO_CHAR(TO_DATE(TO_CHAR(".$piDate."))+30,'YYYYMMDD')) ";
			$sql = $sql." ORDER BY カレンダ_YMD ";
			
			$iCntDay = $pintDay;
			
		}else{
			$sql = $sql."   AND カレンダ_YMD BETWEEN TO_NUMBER(TO_CHAR(TO_DATE(TO_CHAR(".$piDate."))-30,'YYYYMMDD')) AND ".$piDate." ";
			$sql = $sql." ORDER BY カレンダ_YMD DESC ";
			//正負反転
			$iCntDay = -$pintDay;
			
		}

		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			if($i == $iCntDay){
				$intLimitDate = oci_result($stmt, 'N_YMD');
				break;
			}
			$i++;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $intLimitDate;
	}
//2019/05/13 AD END T.FUJITA

//2019/08/01 AD START T.FUJITA
	//赤伝緑伝情報データ取得処理
	//引数	$Reference_NO		伝票NO
	//		$Reference_SEQ		伝票SEQ
	public function fChkTrblDecision($Reference_NO,$Reference_SEQ){

		require_once("module_common.php");
		$module_cmn = new module_common;
		
		$iRtn = -1;

		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			return $iRtn;
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql."SELECT N_DECISION_YMD";
		$sql = $sql."  FROM T_TR_TRBL ";
		$sql = $sql." WHERE C_REFERENCE_NO = '".trim($Reference_NO)."' ";
		$sql = $sql."   AND N_REFERENCE_SEQ = '".$Reference_SEQ."' ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt,OCI_DEFAULT);

		while (oci_fetch($stmt)) {
			$iRtn = oci_result($stmt, 'N_YMD');
			break;
		}
		
		oci_free_statement($stmt);
		oci_close($conn);

		return $iRtn;
	}

	//品質評価集計表集計データチェック
	//引数	$paPara		パラメータ
	public function fChkTrblDataWk($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;
		
		$iRtn = -1;
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		
		if (!$conn) {
			$e = oci_error();	// oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			return $iRtn;
		}
		
		//SQL取得
		$sql = "";
		$sql = $sql."SELECT C01.CNT AS C01 ";
		//$sql = $sql."      ,C02.CNT AS C02 ";
		$sql = $sql."      ,C03.CNT AS C03 ";
		$sql = $sql."  FROM (SELECT COUNT(*) AS CNT FROM T_TR_DOCU_HYOUKA WHERE N_YM = ".$paPara[2].") C01 ";
		//$sql = $sql."      ,(SELECT COUNT(*) AS CNT FROM T_TR_TRBL_HORYU WHERE N_YM = ".$paPara[2].") C02 ";
		$sql = $sql."      ,(SELECT COUNT(*) AS CNT FROM W_PTS0150@NF.US.ORACLE.COM@NF WHERE CMP_NAME = '".$paPara[3]."') C03 ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの解析
		$stmt = oci_parse($conn, $sql);
		//SQL実行
		oci_execute($stmt,OCI_DEFAULT);
		
		while (oci_fetch($stmt)){
			$iRtn = 0;
			if($module_cmn->fChangUTF8(oci_result($stmt, 'C01')) == 0){
				$iRtn = -2;
			}
/* 			if($module_cmn->fChangUTF8(oci_result($stmt, 'C02')) == 0){
				$iRtn = -2;
			} */
			if($module_cmn->fChangUTF8(oci_result($stmt, 'C03')) == 0){
				$iRtn = -2;
			}
			break;
		}
		
		//リソース開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);
		
		return $iRtn;

	}
	
	//品質評価集計表ワークテーブルデータ取得
	//引数	$paPara		パラメータ
	public function fGetTrblHyoka($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;
		
		$aRes = Array();
		$aRes[0][0] = 0;
		
		//対象範囲
		$iDateMF = ($paPara[2]*100)+1;
		$iDateMT = date('Ymd', strtotime('last day of'.$iDateMF));
		
		if($paPara[2]-(floor($paPara[2]/100)*100) >= 7){
			$iDateF = (floor($paPara[2]/100)*100)+7;
		}else{
			$iDateF = (floor($paPara[2]/100)-1)*100+7;
		}
		$iDateT = $paPara[2];
		
		$iDateDF = ($iDateF*100)+1;
		$iDateDT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		
		if (!$conn) {
			$e = oci_error();	// oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $paRes;
		}
		
		//SQL取得
		$sql = "";
		$sql = $sql."SELECT N_YM																							AS N_YM ";
		$sql = $sql."      ,N_ALL_SALE_PRICE																				AS H01 ";
		$sql = $sql."      ,N_ALL_PROCESS_QTY																				AS H02 ";
		$sql = $sql."      ,N_ALL_DISPOSAL_QTY																				AS H03 ";
		$sql = $sql."      ,ROUND((100-(1-(ROUND(N_ALL_DISPOSAL_QTY / N_ALL_PROCESS_QTY,4)))*100)*(N_BAD_COST/100),0)		AS H05 ";
		$sql = $sql."      ,N_ALL_IND_PRICE																					AS H06 ";
		$sql = $sql."      ,N_ALL_STAND_PROCESS_QTY																			AS H07 ";
		$sql = $sql."      ,N_ALL_STAND_PROCESS_PRICE																		AS H08 ";
		$sql = $sql."      ,N_DISPOSAL_S_QTY																				AS H10 ";
		$sql = $sql."      ,N_DISPOSAL_S_PRICE																				AS H11 ";
		$sql = $sql."      ,N_DISPOSAL_M_QTY																				AS H12 ";
		$sql = $sql."      ,N_DISPOSAL_M_PRICE																				AS H13 ";
		$sql = $sql."      ,N_DISPOSAL_K_QTY																				AS H14 ";
		$sql = $sql."      ,N_DISPOSAL_K_PRICE																				AS H15 ";
		$sql = $sql."      ,N_SPECIAL_COST																					AS H99 ";
		$sql = $sql."   FROM ";
		$sql = $sql."(SELECT TH.N_YM ";
		$sql = $sql."       ,TRUNC(TH.N_ALL_SALE_PRICE/1000,0) AS N_ALL_SALE_PRICE ";
		$sql = $sql."       ,TRUNC((TH.N_PROCESS_QTY1+TH.N_PROCESS_QTY2+TH.N_PROCESS_QTY3+TH.N_PROCESS_QTY4+TH.N_PROCESS_QTY5)/1000,0) AS N_ALL_PROCESS_QTY ";
		$sql = $sql."       ,TRUNC((NVL(B00.QTY,0)+NVL(B01.QTY,0)+NVL(B02.QTY,0))/1000,0) AS N_ALL_DISPOSAL_QTY  ";
		$sql = $sql."       ,TRUNC(TH.N_BAD_COST/60)+MOD(TH.N_BAD_COST,60)/100 AS N_BAD_COST ";
		$sql = $sql."       ,TRUNC((TH.N_IND_PRICE1+TH.N_IND_PRICE2+TH.N_IND_PRICE3+TH.N_IND_PRICE4+TH.N_IND_PRICE5)/1000) AS N_ALL_IND_PRICE ";
		$sql = $sql."       ,ROUND(TH.N_ALL_STAND_PROCESS_QTY/1000) AS N_ALL_STAND_PROCESS_QTY ";
		$sql = $sql."       ,ROUND(TH.N_ALL_STAND_PROCESS_PRICE/1000) AS N_ALL_STAND_PROCESS_PRICE ";
		$sql = $sql."       ,CASE WHEN TRUNC((NVL(B00.QTY,0)+NVL(B01.QTY,0)+NVL(B02.QTY,0))/1000,0) = TRUNC(NVL(B00.QTY,0)/1000,0)+TRUNC(NVL(B01.QTY,0)/1000,0)+TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B00.QTY,0)/1000,0) ";
		$sql = $sql."             ELSE CASE WHEN (NVL(B00.QTY,0)/1000)-TRUNC(NVL(B00.QTY,0)/1000,0) > (NVL(B01.QTY,0)/1000)-TRUNC(NVL(B01.QTY,0)/1000,0) THEN ";
		$sql = $sql."                       CASE WHEN (NVL(B00.QTY,0)/1000)-TRUNC(NVL(B00.QTY,0)/1000,0) > (NVL(B02.QTY,0)/1000)-TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B00.QTY,0)/1000,0)+1 ";
		$sql = $sql."                            ELSE TRUNC(NVL(B00.QTY,0)/1000,0) END ";
		$sql = $sql."                       ELSE TRUNC(NVL(B00.QTY,0)/1000,0) END ";
		$sql = $sql."        END AS N_DISPOSAL_S_QTY ";
		$sql = $sql."       ,CASE WHEN TRUNC((NVL(B00.PRICE,0)+NVL(B01.PRICE,0)+NVL(B02.PRICE,0))/1000,0) = TRUNC(NVL(B00.PRICE,0)/1000,0)+TRUNC(NVL(B01.PRICE,0)/1000,0)+TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B00.PRICE,0)/1000,0) ";
		$sql = $sql."             ELSE CASE WHEN (NVL(B00.PRICE,0)/1000)-TRUNC(NVL(B00.PRICE,0)/1000,0) > (NVL(B01.PRICE,0)/1000)-TRUNC(NVL(B01.PRICE,0)/1000,0) THEN ";
		$sql = $sql."                       CASE WHEN (NVL(B00.PRICE,0)/1000)-TRUNC(NVL(B00.PRICE,0)/1000,0) > (NVL(B02.PRICE,0)/1000)-TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B00.PRICE,0)/1000,0)+1 ";
		$sql = $sql."                            ELSE TRUNC(NVL(B00.PRICE,0)/1000,0) END ";
		$sql = $sql."                       ELSE TRUNC(NVL(B00.PRICE,0)/1000,0) END ";
		$sql = $sql."        END AS N_DISPOSAL_S_PRICE ";
		$sql = $sql."      ,CASE WHEN TRUNC((NVL(B00.QTY,0)+NVL(B01.QTY,0)+NVL(B02.QTY,0))/1000,0) = TRUNC(NVL(B00.QTY,0)/1000,0)+TRUNC(NVL(B01.QTY,0)/1000,0)+TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B01.QTY,0)/1000,0) ";
		$sql = $sql."            ELSE CASE WHEN (NVL(B01.QTY,0)/1000)-TRUNC(NVL(B01.QTY,0)/1000,0) > (NVL(B00.QTY,0)/1000)-TRUNC(NVL(B00.QTY,0)/1000,0) THEN ";
		$sql = $sql."                      CASE WHEN (NVL(B01.QTY,0)/1000)-TRUNC(NVL(B01.QTY,0)/1000,0) > (NVL(B02.QTY,0)/1000)-TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B01.QTY,0)/1000,0)+1 ";
		$sql = $sql."                           ELSE TRUNC(NVL(B01.QTY,0)/1000,0) END ";
		$sql = $sql."                      ELSE TRUNC(NVL(B01.QTY,0)/1000,0) END ";
		$sql = $sql."       END AS N_DISPOSAL_M_QTY ";
		$sql = $sql."      ,CASE WHEN TRUNC((NVL(B00.PRICE,0)+NVL(B01.PRICE,0)+NVL(B02.PRICE,0))/1000,0) = TRUNC(NVL(B00.PRICE,0)/1000,0)+TRUNC(NVL(B01.PRICE,0)/1000,0)+TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B01.PRICE,0)/1000,0) ";
		$sql = $sql."            ELSE CASE WHEN (NVL(B01.PRICE,0)/1000)-TRUNC(NVL(B01.PRICE,0)/1000,0) > (NVL(B00.PRICE,0)/1000)-TRUNC(NVL(B00.PRICE,0)/1000,0) THEN ";
		$sql = $sql."                      CASE WHEN (NVL(B01.PRICE,0)/1000)-TRUNC(NVL(B01.PRICE,0)/1000,0) > (NVL(B02.PRICE,0)/1000)-TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B01.PRICE,0)/1000,0)+1 ";
		$sql = $sql."                           ELSE TRUNC(NVL(B01.PRICE,0)/1000,0) END ";
		$sql = $sql."                      ELSE TRUNC(NVL(B01.PRICE,0)/1000,0) END ";
		$sql = $sql."       END AS N_DISPOSAL_M_PRICE ";
		$sql = $sql."      ,CASE WHEN TRUNC((NVL(B00.QTY,0)+NVL(B01.QTY,0)+NVL(B02.QTY,0))/1000,0) = TRUNC(NVL(B00.QTY,0)/1000,0)+TRUNC(NVL(B01.QTY,0)/1000,0)+TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B02.QTY,0)/1000,0) ";
		$sql = $sql."            ELSE CASE WHEN (NVL(B02.QTY,0)/1000)-TRUNC(NVL(B02.QTY,0)/1000,0) > (NVL(B00.QTY,0)/1000)-TRUNC(NVL(B00.QTY,0)/1000,0) THEN ";
		$sql = $sql."                      CASE WHEN (NVL(B02.QTY,0)/1000)-TRUNC(NVL(B02.QTY,0)/1000,0) > (NVL(B01.QTY,0)/1000)-TRUNC(NVL(B01.QTY,0)/1000,0) THEN TRUNC(NVL(B02.QTY,0)/1000,0)+1 ";
		$sql = $sql."                           ELSE TRUNC(NVL(B02.QTY,0)/1000,0) END ";
		$sql = $sql."                      ELSE TRUNC(NVL(B02.QTY,0)/1000,0) END ";
		$sql = $sql."       END AS N_DISPOSAL_K_QTY ";
		$sql = $sql."      ,CASE WHEN TRUNC((NVL(B00.PRICE,0)+NVL(B01.PRICE,0)+NVL(B02.PRICE,0))/1000,0) = TRUNC(NVL(B00.PRICE,0)/1000,0)+TRUNC(NVL(B01.PRICE,0)/1000,0)+TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B02.PRICE,0)/1000,0) ";
		$sql = $sql."            ELSE CASE WHEN (NVL(B02.PRICE,0)/1000)-TRUNC(NVL(B02.PRICE,0)/1000,0) > (NVL(B00.PRICE,0)/1000)-TRUNC(NVL(B00.PRICE,0)/1000,0) THEN ";
		$sql = $sql."                      CASE WHEN (NVL(B02.PRICE,0)/1000)-TRUNC(NVL(B02.PRICE,0)/1000,0) > (NVL(B01.PRICE,0)/1000)-TRUNC(NVL(B01.PRICE,0)/1000,0) THEN TRUNC(NVL(B02.PRICE,0)/1000,0)+1 ";
		$sql = $sql."                           ELSE TRUNC(NVL(B02.PRICE,0)/1000,0) END ";
		$sql = $sql."                      ELSE TRUNC(NVL(B02.PRICE,0)/1000,0) END ";
		$sql = $sql."       END AS N_DISPOSAL_K_PRICE ";
		$sql = $sql."       ,FT.TIME AS N_SPECIAL_COST ";
		$sql = $sql."  FROM T_TR_DOCU_HYOUKA TH ";
		//社内検査（廃棄数量・金額）
		$sql = $sql."      ,(SELECT SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
		$sql = $sql."              ,SUM(N_DISPOSAL_QTY) AS QTY ";
		$sql = $sql."              ,SUM(N_DISPOSAL_PRICE) AS PRICE ";
		$sql = $sql."          FROM T_TR_TRBL ";
		$sql = $sql."         WHERE N_DECISION_YMD BETWEEN ".$iDateDF." AND ".$iDateDT." ";
		$sql = $sql."           AND C_TARGET_SECTION_KBN = 'F' ";
		$sql = $sql."           AND (C_BUSYO_CD LIKE 'K%' OR C_BUSYO_CD LIKE 'F%') ";
		$sql = $sql."           AND TRIM(C_KBN) = 0 ";
		$sql = $sql."           AND N_EXCLUDED = 0 ";
		$sql = $sql."         GROUP BY SUBSTR(N_DECISION_YMD,1,6)) B00 ";
		//製造工程（廃棄数量・金額）
		$sql = $sql."      ,(SELECT SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
		$sql = $sql."              ,SUM(N_DISPOSAL_QTY) AS QTY ";
		$sql = $sql."              ,SUM(N_DISPOSAL_PRICE) AS PRICE ";
		$sql = $sql."          FROM T_TR_TRBL ";
		$sql = $sql."         WHERE N_DECISION_YMD BETWEEN ".$iDateDF." AND ".$iDateDT." ";
		$sql = $sql."           AND C_TARGET_SECTION_KBN = 'F' ";
		$sql = $sql."           AND (C_BUSYO_CD LIKE 'K%' OR C_BUSYO_CD LIKE 'F%') ";
		$sql = $sql."           AND TRIM(C_KBN) = 1 ";
		$sql = $sql."           AND N_EXCLUDED = 0 ";
		$sql = $sql."         GROUP BY SUBSTR(N_DECISION_YMD,1,6)) B01 ";
		//客先返品（廃棄数量・金額）
		$sql = $sql."      ,(SELECT SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
		$sql = $sql."              ,SUM(N_DISPOSAL_QTY) AS QTY ";
		$sql = $sql."              ,SUM(N_DISPOSAL_PRICE) AS PRICE ";
		$sql = $sql."          FROM T_TR_TRBL ";
		$sql = $sql."         WHERE N_DECISION_YMD BETWEEN ".$iDateDF." AND ".$iDateDT." ";
		$sql = $sql."           AND C_TARGET_SECTION_KBN = 'F' ";
		$sql = $sql."           AND (C_BUSYO_CD LIKE 'K%' OR C_BUSYO_CD LIKE 'F%') ";
		$sql = $sql."           AND TRIM(C_KBN) = 2 ";
		$sql = $sql."           AND N_EXCLUDED = 0 ";
		$sql = $sql."         GROUP BY SUBSTR(N_DECISION_YMD,1,6)) B02 ";
		//特別作業時間
		$sql = $sql."      ,(SELECT NVL(SUM(N_SELECTION),0) AS TIME ";
		$sql = $sql."          FROM T_TR_TRBL TRBL ";
		$sql = $sql."         WHERE N_DECISION_YMD BETWEEN ".$iDateMF." AND ".$iDateMT." ";
		$sql = $sql."           AND N_EXCLUDED = 0 ";
		$sql = $sql."           AND C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
		$sql = $sql."           AND (C_BUSYO_CD LIKE 'K%' OR C_BUSYO_CD LIKE 'F%')) FT ";
		$sql = $sql." WHERE TH.C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
		$sql = $sql."   AND TH.N_YM BETWEEN ".$iDateF." AND ".$iDateT." ";
		$sql = $sql."   AND TH.N_YM = B00.N_YM(+) ";
		$sql = $sql."   AND TH.N_YM = B01.N_YM(+) ";
		$sql = $sql."   AND TH.N_YM = B02.N_YM(+)) ";
		//前期計算は別途対応
/* 		if($paPara[2] >= 202007){
			//前期平均
			$sql = $sql."UNION ";
			$sql = $sql."SELECT ".(floor($paPara[2]/100)*100)."																				AS N_YM ";
			$sql = $sql."      ,ROUND(AVG(N_ALL_SALE_PRICE))																				AS H01 ";
			$sql = $sql."      ,ROUND(AVG(N_ALL_PROCESS_QTY))																				AS H02 ";
			$sql = $sql."      ,ROUND(AVG(N_ALL_DISPOSAL_QTY))																				AS H03 ";
			$sql = $sql."      ,ROUND((100-(1-(ROUND(N_ALL_DISPOSAL_QTY / N_ALL_PROCESS_QTY,4)))*100)*(N_BAD_COST/100),0)					AS H05 ";
			$sql = $sql."      ,ROUND(AVG(TRUNC(((ROUND(100-(N_ALL_DISPOSAL_QTY / N_ALL_PROCESS_QTY)*100,2)/100)/100 * N_BAD_COST) / 60)))	AS H05 ";
			$sql = $sql."      ,ROUND(AVG(N_ALL_IND_PRICE))																					AS H06 ";
			$sql = $sql."      ,ROUND(AVG(N_ALL_STAND_PROCESS_QTY))																			AS H07 ";
			$sql = $sql."      ,ROUND(AVG(N_ALL_STAND_PROCESS_PRICE))																		AS H08 ";
			$sql = $sql."      ,ROUND(AVG(N_DISPOSAL_S_QTY))																				AS H10 ";
			$sql = $sql."      ,ROUND(AVG(N_DISPOSAL_S_PRICE))																				AS H11 ";
			$sql = $sql."      ,ROUND(AVG(N_DISPOSAL_M_QTY))																				AS H12 ";
			$sql = $sql."      ,ROUND(AVG(N_DISPOSAL_M_PRICE))																				AS H13 ";
			$sql = $sql."      ,ROUND(AVG(N_DISPOSAL_K_QTY))																				AS H14 ";
			$sql = $sql."      ,ROUND(AVG(N_DISPOSAL_K_PRICE))																				AS H15 ";
			$sql = $sql."      ,0																											AS H99 ";
			$sql = $sql."   FROM ";
			$sql = $sql."(SELECT TH.N_YM ";
			$sql = $sql."       ,TRUNC(TH.N_ALL_SALE_PRICE/1000,0) AS N_ALL_SALE_PRICE ";
			$sql = $sql."       ,TRUNC((TH.N_PROCESS_QTY1+TH.N_PROCESS_QTY2+TH.N_PROCESS_QTY3+TH.N_PROCESS_QTY4+TH.N_PROCESS_QTY5)/1000,0) AS N_ALL_PROCESS_QTY ";
			$sql = $sql."       ,TRUNC((NVL(B00.QTY,0)+NVL(B01.QTY,0)+NVL(B02.QTY,0))/1000,0) AS N_ALL_DISPOSAL_QTY  ";
			$sql = $sql."       ,TH.N_BAD_COST ";
			$sql = $sql."       ,TRUNC((TH.N_IND_PRICE1+TH.N_IND_PRICE2+TH.N_IND_PRICE3+TH.N_IND_PRICE4+TH.N_IND_PRICE5)/1000) AS N_ALL_IND_PRICE ";
			$sql = $sql."       ,ROUND(TH.N_ALL_STAND_PROCESS_QTY/1000) AS N_ALL_STAND_PROCESS_QTY ";
			$sql = $sql."       ,ROUND(TH.N_ALL_STAND_PROCESS_PRICE/1000) AS N_ALL_STAND_PROCESS_PRICE ";
			$sql = $sql."       ,CASE WHEN TRUNC((NVL(B00.QTY,0)+NVL(B01.QTY,0)+NVL(B02.QTY,0))/1000,0) = TRUNC(NVL(B00.QTY,0)/1000,0)+TRUNC(NVL(B01.QTY,0)/1000,0)+TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B00.QTY,0)/1000,0) ";
			$sql = $sql."             ELSE CASE WHEN (NVL(B00.PRICE,0)/1000)-TRUNC(NVL(B00.QTY,0)/1000,0) > (NVL(B01.QTY,0)/1000)-TRUNC(NVL(B01.QTY,0)/1000,0) THEN ";
			$sql = $sql."                       CASE WHEN (NVL(B00.PQTYRICE,0)/1000)-TRUNC(NVL(B00.QTY,0)/1000,0) > (NVL(B02.QTY,0)/1000)-TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B00.QTY,0)/1000,0)+1 ";
			$sql = $sql."                            ELSE TRUNC(NVL(B00.QTY,0)/1000,0) END ";
			$sql = $sql."                       ELSE TRUNC(NVL(B00.QTY,0)/1000,0) END ";
			$sql = $sql."        END AS N_DISPOSAL_S_QTY ";
			$sql = $sql."       ,CASE WHEN TRUNC((NVL(B00.PRICE,0)+NVL(B01.PRICE,0)+NVL(B02.PRICE,0))/1000,0) = TRUNC(NVL(B00.PRICE,0)/1000,0)+TRUNC(NVL(B01.PRICE,0)/1000,0)+TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B00.PRICE,0)/1000,0) ";
			$sql = $sql."             ELSE CASE WHEN (NVL(B00.PRICE,0)/1000)-TRUNC(NVL(B00.PRICE,0)/1000,0) > (NVL(B01.PRICE,0)/1000)-TRUNC(NVL(B01.PRICE,0)/1000,0) THEN ";
			$sql = $sql."                       CASE WHEN (NVL(B00.PRICE,0)/1000)-TRUNC(NVL(B00.PRICE,0)/1000,0) > (NVL(B02.PRICE,0)/1000)-TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B00.PRICE,0)/1000,0)+1 ";
			$sql = $sql."                            ELSE TRUNC(NVL(B00.PRICE,0)/1000,0) END ";
			$sql = $sql."                       ELSE TRUNC(NVL(B00.PRICE,0)/1000,0) END ";
			$sql = $sql."        END AS N_DISPOSAL_S_PRICE ";
			$sql = $sql."      ,CASE WHEN TRUNC((NVL(B00.QTY,0)+NVL(B01.QTY,0)+NVL(B02.QTY,0))/1000,0) = TRUNC(NVL(B00.QTY,0)/1000,0)+TRUNC(NVL(B01.QTY,0)/1000,0)+TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B01.QTY,0)/1000,0) ";
			$sql = $sql."            ELSE CASE WHEN (NVL(B01.QTY,0)/1000)-TRUNC(NVL(B01.QTY,0)/1000,0) > (NVL(B00.QTY,0)/1000)-TRUNC(NVL(B00.QTY,0)/1000,0) THEN ";
			$sql = $sql."                      CASE WHEN (NVL(B01.QTY,0)/1000)-TRUNC(NVL(B01.QTY,0)/1000,0) > (NVL(B02.QTY,0)/1000)-TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B01.QTY,0)/1000,0)+1 ";
			$sql = $sql."                           ELSE TRUNC(NVL(B01.QTY,0)/1000,0) END ";
			$sql = $sql."                      ELSE TRUNC(NVL(B01.QTY,0)/1000,0) END ";
			$sql = $sql."       END AS N_DISPOSAL_M_QTY ";
			$sql = $sql."      ,CASE WHEN TRUNC((NVL(B00.PRICE,0)+NVL(B01.PRICE,0)+NVL(B02.PRICE,0))/1000,0) = TRUNC(NVL(B00.PRICE,0)/1000,0)+TRUNC(NVL(B01.PRICE,0)/1000,0)+TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B01.PRICE,0)/1000,0) ";
			$sql = $sql."            ELSE CASE WHEN (NVL(B01.PRICE,0)/1000)-TRUNC(NVL(B01.PRICE,0)/1000,0) > (NVL(B00.PRICE,0)/1000)-TRUNC(NVL(B00.PRICE,0)/1000,0) THEN ";
			$sql = $sql."                      CASE WHEN (NVL(B01.PRICE,0)/1000)-TRUNC(NVL(B01.PRICE,0)/1000,0) > (NVL(B02.PRICE,0)/1000)-TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B01.PRICE,0)/1000,0)+1 ";
			$sql = $sql."                           ELSE TRUNC(NVL(B01.PRICE,0)/1000,0) END ";
			$sql = $sql."                      ELSE TRUNC(NVL(B01.PRICE,0)/1000,0) END ";
			$sql = $sql."       END AS N_DISPOSAL_M_PRICE ";
			$sql = $sql."      ,CASE WHEN TRUNC((NVL(B00.QTY,0)+NVL(B01.QTY,0)+NVL(B02.QTY,0))/1000,0) = TRUNC(NVL(B00.QTY,0)/1000,0)+TRUNC(NVL(B01.QTY,0)/1000,0)+TRUNC(NVL(B02.QTY,0)/1000,0) THEN TRUNC(NVL(B02.QTY,0)/1000,0) ";
			$sql = $sql."            ELSE CASE WHEN (NVL(B02.QTY,0)/1000)-TRUNC(NVL(B02.QTY,0)/1000,0) > (NVL(B00.QTY,0)/1000)-TRUNC(NVL(B00.QTY,0)/1000,0) THEN ";
			$sql = $sql."                      CASE WHEN (NVL(B02.QTY,0)/1000)-TRUNC(NVL(B02.QTY,0)/1000,0) > (NVL(B01.QTY,0)/1000)-TRUNC(NVL(B01.QTY,0)/1000,0) THEN TRUNC(NVL(B02.QTY,0)/1000,0)+1 ";
			$sql = $sql."                           ELSE TRUNC(NVL(B02.QTY,0)/1000,0) END ";
			$sql = $sql."                      ELSE TRUNC(NVL(B02.QTY,0)/1000,0) END ";
			$sql = $sql."       END AS N_DISPOSAL_K_QTY ";
			$sql = $sql."      ,CASE WHEN TRUNC((NVL(B00.PRICE,0)+NVL(B01.PRICE,0)+NVL(B02.PRICE,0))/1000,0) = TRUNC(NVL(B00.PRICE,0)/1000,0)+TRUNC(NVL(B01.PRICE,0)/1000,0)+TRUNC(NVL(B02.PRICE,0)/1000,0) THEN TRUNC(NVL(B02.PRICE,0)/1000,0) ";
			$sql = $sql."            ELSE CASE WHEN (NVL(B02.PRICE,0)/1000)-TRUNC(NVL(B02.PRICE,0)/1000,0) > (NVL(B00.PRICE,0)/1000)-TRUNC(NVL(B00.PRICE,0)/1000,0) THEN ";
			$sql = $sql."                      CASE WHEN (NVL(B02.PRICE,0)/1000)-TRUNC(NVL(B02.PRICE,0)/1000,0) > (NVL(B01.PRICE,0)/1000)-TRUNC(NVL(B01.PRICE,0)/1000,0) THEN TRUNC(NVL(B02.PRICE,0)/1000,0)+1 ";
			$sql = $sql."                           ELSE TRUNC(NVL(B02.PRICE,0)/1000,0) END ";
			$sql = $sql."                      ELSE TRUNC(NVL(B02.PRICE,0)/1000,0) END ";
			$sql = $sql."       END AS N_DISPOSAL_K_PRICE ";
			$sql = $sql."  FROM T_TR_DOCU_HYOUKA TH ";
			//社内検査（廃棄数量・金額）
			$sql = $sql."      ,(SELECT SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
			$sql = $sql."              ,SUM(N_DISPOSAL_QTY) AS QTY ";
			$sql = $sql."              ,SUM(N_DISPOSAL_PRICE) AS PRICE ";
			$sql = $sql."          FROM T_TR_TRBL ";
			$sql = $sql."         WHERE N_DECISION_YMD BETWEEN ".($iDateDF-10000)." AND ".($iDateDT-10000)." ";
			$sql = $sql."           AND C_TARGET_SECTION_KBN = 'F' ";
			$sql = $sql."           AND (C_BUSYO_CD LIKE 'K%' OR C_BUSYO_CD LIKE 'F%') ";
			$sql = $sql."           AND TRIM(C_KBN) = 0 ";
			$sql = $sql."           AND N_EXCLUDED = 0 ";
			$sql = $sql."         GROUP BY SUBSTR(N_DECISION_YMD,1,6)) B00 ";
			//製造工程（廃棄数量・金額）
			$sql = $sql."      ,(SELECT SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
			$sql = $sql."              ,SUM(N_DISPOSAL_QTY) AS QTY ";
			$sql = $sql."              ,SUM(N_DISPOSAL_PRICE) AS PRICE ";
			$sql = $sql."          FROM T_TR_TRBL ";
			$sql = $sql."         WHERE N_DECISION_YMD BETWEEN ".($iDateDF-10000)." AND ".($iDateDT-10000)." ";
			$sql = $sql."           AND C_TARGET_SECTION_KBN = 'F' ";
			$sql = $sql."           AND (C_BUSYO_CD LIKE 'K%' OR C_BUSYO_CD LIKE 'F%') ";
			$sql = $sql."           AND TRIM(C_KBN) = 1 ";
			$sql = $sql."           AND N_EXCLUDED = 0 ";
			$sql = $sql."         GROUP BY SUBSTR(N_DECISION_YMD,1,6)) B01 ";
			//客先返品（廃棄数量・金額）
			$sql = $sql."      ,(SELECT SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
			$sql = $sql."              ,SUM(N_DISPOSAL_QTY) AS QTY ";
			$sql = $sql."              ,SUM(N_DISPOSAL_PRICE) AS PRICE ";
			$sql = $sql."          FROM T_TR_TRBL ";
			$sql = $sql."         WHERE N_DECISION_YMD BETWEEN ".($iDateDF-10000)." AND ".($iDateDT-10000)." ";
			$sql = $sql."           AND C_TARGET_SECTION_KBN = 'F' ";
			$sql = $sql."           AND (C_BUSYO_CD LIKE 'K%' OR C_BUSYO_CD LIKE 'F%') ";
			$sql = $sql."           AND TRIM(C_KBN) = 2 ";
			$sql = $sql."           AND N_EXCLUDED = 0 ";
			$sql = $sql."         GROUP BY SUBSTR(N_DECISION_YMD,1,6)) B02 ";
			$sql = $sql." WHERE TH.C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
			$sql = $sql."   AND TH.N_YM BETWEEN ".($iDateF-100)." AND ".($iDateT-100)." ";
			$sql = $sql."   AND TH.N_YM = B00.N_YM(+) ";
			$sql = $sql."   AND TH.N_YM = B01.N_YM(+) ";
			$sql = $sql."   AND TH.N_YM = B02.N_YM(+)) ";
		}else{ */
			//51期はデータが存在しないので固定値
			$sql = $sql."UNION ";
			$sql = $sql."SELECT ".(floor($paPara[2]/100)*100)."		AS N_YM ";
			$sql = $sql."      ,720460								AS H01 ";
			$sql = $sql."      ,2960933								AS H02 ";
			$sql = $sql."      ,3689								AS H03 ";
			$sql = $sql."      ,32									AS H05 ";
			$sql = $sql."      ,695621								AS H06 ";
			$sql = $sql."      ,3097685								AS H07 ";
			$sql = $sql."      ,729176								AS H08 ";
			$sql = $sql."      ,1249								AS H10 ";
			$sql = $sql."      ,311									AS H11 ";
			$sql = $sql."      ,122									AS H12 ";
			$sql = $sql."      ,41									AS H13 ";
			$sql = $sql."      ,2318								AS H14 ";
			$sql = $sql."      ,492									AS H15 ";
			$sql = $sql."      ,0									AS H99 ";
			$sql = $sql."   FROM DUAL ";
		//}
		
		//SQLの解析
		$stmt = oci_parse($conn, $sql);
		//SQL実行
		oci_execute($stmt,OCI_DEFAULT);
		
		$iRow = 0;
		while (oci_fetch($stmt)){
			$aRes[$iRow][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_YM'));
			$aRes[$iRow][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'H01'));
			$aRes[$iRow][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'H02'));
			$aRes[$iRow][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'H03'));
			$aRes[$iRow][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'H05'));
			$aRes[$iRow][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'H06'));
			$aRes[$iRow][7] = $module_cmn->fChangUTF8(oci_result($stmt, 'H07'));
			$aRes[$iRow][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'H08'));
			$aRes[$iRow][9] = $module_cmn->fChangUTF8(oci_result($stmt, 'H10'));
			$aRes[$iRow][10] = $module_cmn->fChangUTF8(oci_result($stmt, 'H11'));
			$aRes[$iRow][11] = $module_cmn->fChangUTF8(oci_result($stmt, 'H12'));
			$aRes[$iRow][12] = $module_cmn->fChangUTF8(oci_result($stmt, 'H13'));
			$aRes[$iRow][13] = $module_cmn->fChangUTF8(oci_result($stmt, 'H14'));
			$aRes[$iRow][14] = $module_cmn->fChangUTF8(oci_result($stmt, 'H15'));
			$aRes[$iRow][15] = $module_cmn->fChangUTF8(oci_result($stmt, 'H99'));
			$iRow = $iRow + 1;
		}
		
		//リソース開放
		oci_free_statement($stmt);
		//Oracle接続切断
		oci_close($conn);
		
		return $aRes;

	}

	//当月廃棄分ランキング表取得
	//引数	$paPara		パラメータ
	public function fGetTrblDisposalRnk($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;
		
		//対象範囲
		$iDateDF = ($paPara[2]*100)+1;
		$iDateDT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql."SELECT TR_RNK.RANK AS RANK ";
		$sql = $sql."      ,TRIM(TRBL.C_REFERENCE_NO) AS C_REFERENCE_NO ";
		$sql = $sql."      ,TRBL.N_INCIDENT_YMD AS N_INCIDENT_YMD2 ";
		$sql = $sql."      ,SUBSTR(N_INCIDENT_YMD,0,4) || '/' || SUBSTR(N_INCIDENT_YMD,5,2) || '/' || SUBSTR(N_INCIDENT_YMD,7,2) AS N_INCIDENT_YMD ";
		$sql = $sql."      ,TRIM(TRBL.C_PROD_CD) AS C_PROD_CD ";
		$sql = $sql."      ,TRBL.V2_DRW_NO AS V2_DRW_NO ";
		$sql = $sql."      ,TRBL.V2_PROD_NM AS V2_PROD_NM ";
		$sql = $sql."      ,MK1.区分明細名称_KJ AS C_FLAW_KBN_NM ";
		$sql = $sql."      ,MKBN.V2_KBN_MEI_NM AS V2_KBN_MEI_NM ";
		$sql = $sql."      ,TRIM(TMS.V2_SHAIN_NM) AS V2_SHAIN_NM ";
		$sql = $sql."      ,TRIM(TRBL.C_PROCESS) AS C_PROCESS ";
		$sql = $sql."      ,CASE WHEN TRIM(TRBL.C_PROCESS) = '-1' THEN '保留' ";
		$sql = $sql."            WHEN TRIM(TRBL.C_PROCESS) = '3' THEN '選別' ";
		$sql = $sql."            WHEN TRIM(TRBL.C_PROCESS) = '4' THEN '選別' ";
		$sql = $sql."            WHEN TRIM(TRBL.C_PROCESS) = '5' THEN '選別' ";
		$sql = $sql."            ELSE MKBN2.V2_KBN_MEI_NM ";
		$sql = $sql."       END AS PROCESS_NM ";
		$sql = $sql."      ,TRBL.N_FLAW_LOT_QTY ";
		$sql = $sql."      ,TRBL.N_FAILURE_QTY ";
		$sql = $sql."      ,TRBL.N_DISPOSAL_QTY ";
		$sql = $sql."      ,TRIM(TRBL.V2_PROD_TANTO_NM1) AS V2_PROD_TANTO_NM1 ";
		$sql = $sql."      ,TRIM(TRBL.V2_PROD_TANTO_NM2) AS V2_PROD_TANTO_NM2 ";
		$sql = $sql."      ,TRIM(TRBL.V2_PROD_TANTO_NM3) AS V2_PROD_TANTO_NM3 ";
		$sql = $sql."      ,TRBL.V2_PROD_GRP_NM ";
		$sql = $sql."      ,CASE WHEN TRBL.C_BUSYO_CD IN ('K01001','K01002') THEN '1' ";
		$sql = $sql."            WHEN TRBL.C_BUSYO_CD IN ('K01003','K01004') THEN '2' ";
		$sql = $sql."            WHEN TRBL.C_BUSYO_CD IN ('K01005','K01010','K01011') THEN '3' ";
		$sql = $sql."            WHEN TRBL.C_BUSYO_CD IN ('K01006','K01007') THEN '4' ";
		$sql = $sql."            WHEN TRBL.C_BUSYO_CD IN ('K01008') THEN '5' ";
		$sql = $sql."            ELSE '-' END AS KA ";
		$sql = $sql."      ,CASE WHEN TRBL.C_BUSYO_CD IN ('K01001','K01003','K01006') THEN '1' ";
		$sql = $sql."            WHEN TRBL.C_BUSYO_CD IN ('K01002','K01004','K01007','K01010','K01011') THEN '2' ";
		$sql = $sql."            ELSE '' END AS G ";
		$sql = $sql."      ,TRBL.N_DISPOSAL_PRICE ";
		$sql = $sql."      ,TRBL.N_UNIT_PRICE AS N_UNIT_PRICE ";
		$sql = $sql."      ,TR_RNK.TOTAL_DISPOSAL_PRICE AS TOTAL_DISPOSAL_PRICE ";
		$sql = $sql."      ,TRIM(TRBL.C_BUSYO_CD) AS C_BUSYO_CD ";
		$sql = $sql."  FROM T_TR_TRBL TRBL ";
		$sql = $sql."      ,(SELECT V2_KBN_MEI_CD,V2_KBN_MEI_NM FROM T_MS_FL_KBN WHERE V2_KBN_CD = 'C34') MKBN ";
		$sql = $sql."      ,(SELECT V2_KBN_MEI_CD,V2_KBN_MEI_NM FROM T_MS_FL_KBN WHERE V2_KBN_CD = 'C37') MKBN2 ";
		$sql = $sql."      ,(SELECT * FROM M_区分@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM WHERE 区分_CD = '085') MK1 ";
		$sql = $sql."      ,T_MS_SHAIN TMS ";
		$sql = $sql."      ,(SELECT RANK() OVER(ORDER BY SUM(N_DISPOSAL_PRICE) DESC) AS RANK ";
		$sql = $sql."              ,MIN(C_REFERENCE_NO) AS C_REFERENCE_NO ";
		$sql = $sql."              ,C_PROD_CD ";
		$sql = $sql."              ,C_FLAW_KBN1 AS C_FLAW_KBN1 ";
		$sql = $sql."              ,SUM(N_DISPOSAL_PRICE) AS TOTAL_DISPOSAL_PRICE ";
		$sql = $sql."          FROM T_TR_TRBL ";
		$sql = $sql."         WHERE N_DECISION_YMD BETWEEN ".$iDateDF." AND ".$iDateDT." ";
		$sql = $sql."           AND C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
		$sql = $sql."           AND N_EXCLUDED = 0 ";
		$sql = $sql."           AND N_DISPOSAL_QTY <> 0 ";
		$sql = $sql."        HAVING SUM(N_DISPOSAL_PRICE) > 0 ";
		$sql = $sql."         GROUP BY C_FLAW_KBN1,C_BUSYO_CD,C_PROD_CD ";
		$sql = $sql.") TR_RNK ";
		$sql = $sql." WHERE N_DECISION_YMD BETWEEN ".$iDateDF." AND ".$iDateDT." ";
		$sql = $sql."   AND TRIM(TRBL.C_KBN) = MKBN.V2_KBN_MEI_CD(+) ";
		$sql = $sql."   AND TRIM(TRBL.C_PROCESS) = MKBN2.V2_KBN_MEI_CD(+) ";
		$sql = $sql."   AND TRBL.C_TANTO_CD = TMS.C_SHAIN_CD(+) ";
		$sql = $sql."   AND TRBL.C_FLAW_KBN1 = MK1.区分明細_CD(+)";
		$sql = $sql."   AND TRBL.C_PROD_CD = TR_RNK.C_PROD_CD ";
		$sql = $sql."   AND TRBL.C_FLAW_KBN1 = TR_RNK.C_FLAW_KBN1 ";
		$sql = $sql."   AND TRBL.N_DISPOSAL_QTY <> 0 ";
		$sql = $sql." ORDER BY TR_RNK.RANK,TRBL.C_REFERENCE_NO ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);

		$iRow = 0;
		while (oci_fetch($stmt)) {
			$aRes[$iRow][1] = oci_result($stmt, 'RANK');
			$aRes[$iRow][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));
			$aRes[$iRow][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_INCIDENT_YMD'));
			$aRes[$iRow][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));
			$aRes[$iRow][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));
			$aRes[$iRow][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));
			$aRes[$iRow][7] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN_NM'));
			$aRes[$iRow][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_KBN_MEI_NM'));
			$aRes[$iRow][9] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_SHAIN_NM'));
			$aRes[$iRow][10] = substr($paPara[2],0,4).".".substr($paPara[2],4,2);	//集計月
			$aRes[$iRow][11] = $module_cmn->fChangUTF8(oci_result($stmt, 'PROCESS_NM'));
			$aRes[$iRow][12] = oci_result($stmt, 'N_FLAW_LOT_QTY');	//不具合数量
			//納入数量
			if(oci_result($stmt, 'N_FAILURE_QTY') == 0){
				$aRes[$iRow][13] = "";
			}else{
				$aRes[$iRow][13] = oci_result($stmt, 'N_FAILURE_QTY');
			}
			
			$aRes[$iRow][14] = oci_result($stmt, 'N_DISPOSAL_QTY');	//廃棄数量
			//作業者
			if($module_cmn->fChangUTF8(oci_result($stmt, 'C_BUSYO_CD')) == 'F01999'){
				$aRes[$iRow][15] = "その他";
			}elseif($module_cmn->fChangUTF8(oci_result($stmt, 'C_BUSYO_CD')) == 'F01008'){
				$aRes[$iRow][15] = "客先";
			}else{
				$sTmpTanto = "";
				if($module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM1'))<>""){
					$sTmpTanto = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM1'));
				}
				if($module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM2'))<>""){
					if($sTmpTanto <> ""){
						$sTmpTanto = $sTmpTanto.",".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM2'));
					}else{
						$sTmpTanto = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM2'));
					}
				}
				if($module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM3'))<>""){
					if($sTmpTanto <> ""){
						$sTmpTanto = $sTmpTanto.",".$module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM3'));
					}else{
						$sTmpTanto = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_TANTO_NM3'));
					}
				}
				$aRes[$iRow][15] = $sTmpTanto;
			}
			$aRes[$iRow][16] = $module_cmn->fChangUTF8(oci_result($stmt, 'KA'));	//課
			$aRes[$iRow][17] = $module_cmn->fChangUTF8(oci_result($stmt, 'G'));	//グループ
			$aRes[$iRow][18] = oci_result($stmt, 'N_UNIT_PRICE');
			$aRes[$iRow][19] = oci_result($stmt, 'N_DISPOSAL_PRICE');
			$aRes[$iRow][20] = oci_result($stmt, 'TOTAL_DISPOSAL_PRICE');
			$iRow = $iRow + 1;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}
	
	//不良内訳（社内起因）取得
	//引数	$paPara		パラメータ
	public function fGetTrblBatMonth($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;

		//対象範囲
		$iDateDF = ($paPara[2]*100)+1;
		$iDateDT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql."SELECT * ";
		$sql = $sql."  FROM ";
		$sql = $sql."(SELECT TRIM(TRBL.C_REFERENCE_NO) AS C_REFERENCE_NO ";
		$sql = $sql."      ,TRIM(TRBL.N_REFERENCE_SEQ) AS N_REFERENCE_SEQ ";
		$sql = $sql."      ,CASE WHEN TRIM(TRBL.C_POINTREF_NO) <> '' OR TRIM(TRBL.C_POINTREF_NO) IS NOT NULL THEN TRIM(TRBL.C_POINTREF_NO) ";
		$sql = $sql."            ELSE TRIM(TRBL.C_REFERENCE_NO) END AS C_POINTREF_NO ";
		$sql = $sql."      ,SUBSTR(TRBL.N_INCIDENT_YMD,0,4) || '/' || SUBSTR(TRBL.N_INCIDENT_YMD,5,2) || '/' || SUBSTR(TRBL.N_INCIDENT_YMD,7,2) AS N_INCIDENT_YMD ";
		$sql = $sql."      ,TRIM(TRBL.C_PROD_CD) AS C_PROD_CD ";
		$sql = $sql."      ,TRBL.V2_DRW_NO ";
		$sql = $sql."      ,TRBL.V2_PROD_NM ";
		$sql = $sql."      ,MK1.区分明細名称_KJ AS C_FLAW_KBN_NM ";
		$sql = $sql."      ,MKBN.V2_KBN_MEI_NM AS KBN_NM ";
		$sql = $sql."      ,CASE WHEN TRIM(TRBL.C_PROCESS) = '-1' THEN '保留' ";
		$sql = $sql."            ELSE MKBN2.V2_KBN_MEI_NM ";
		$sql = $sql."       END AS PROCESS_NM ";
		$sql = $sql."      ,TRBL.N_FLAW_LOT_QTY ";
		$sql = $sql."      ,TRBL.N_FAILURE_QTY ";
		$sql = $sql."      ,TRBL.N_DISPOSAL_QTY ";
		$sql = $sql."      ,TRBL.N_UNIT_PRICE ";
		$sql = $sql."      ,TRBL.N_DISPOSAL_PRICE ";
		$sql = $sql."      ,TRBL.N_FLAW_PRICE-N_FAILURE_PRICE-N_DISPOSAL_PRICE-N_RETURN_PRICE-N_LOSS_PRICE-N_EXCLUD_PRICE AS N_HOLD_PRICE ";
		$sql = $sql."      ,NVL(TRBL.V2_PROD_TANTO_NM1,'') AS TANTO_NM1 ";
		$sql = $sql."      ,NVL(TRBL.V2_PROD_TANTO_NM2,'') AS TANTO_NM2 ";
		$sql = $sql."      ,NVL(TRBL.V2_PROD_TANTO_NM3,'') AS TANTO_NM3 ";
		$sql = $sql."      ,CASE WHEN C_BUSYO_CD IN ('K01001','K01002') THEN '1' ";
		$sql = $sql."            WHEN C_BUSYO_CD IN ('K01003','K01004') THEN '2' ";
		$sql = $sql."            WHEN C_BUSYO_CD IN ('K01005','K01010','K01011') THEN '3' ";
		$sql = $sql."            WHEN C_BUSYO_CD IN ('K01006','K01007') THEN '4' ";
		$sql = $sql."            WHEN C_BUSYO_CD IN ('K01008') THEN '5' ";
		$sql = $sql."            WHEN C_BUSYO_CD IN ('F01999') THEN '-' ";
		$sql = $sql."            ELSE '' ";
		$sql = $sql."       END AS GRP ";
		$sql = $sql."      ,TRBL.N_EXCLUDED ";
		$sql = $sql."      ,TRIM(TRBL.C_FLAW_KBN1) AS C_FLAW_KBN1 ";
		$sql = $sql." FROM T_TR_TRBL TRBL ";
		$sql = $sql."     ,(SELECT * FROM M_区分@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM WHERE 区分_CD = '085') MK1 ";
		$sql = $sql."     ,(SELECT V2_KBN_MEI_CD,V2_KBN_MEI_NM FROM T_MS_FL_KBN WHERE V2_KBN_CD = 'C34') MKBN ";
		$sql = $sql."     ,(SELECT V2_KBN_MEI_CD,V2_KBN_MEI_NM FROM T_MS_FL_KBN WHERE V2_KBN_CD = 'C37') MKBN2 ";
		$sql = $sql."     ,V_FL_CUST_INFO CST ";
		$sql = $sql."WHERE (N_DECISION_YMD BETWEEN ".$iDateDF." AND ".$iDateDT." OR N_DECISION_YMD = 0) ";
		$sql = $sql."  AND C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
		$sql = $sql."  AND (C_BUSYO_CD LIKE 'K%' OR C_BUSYO_CD LIKE 'F%') ";
		$sql = $sql."  AND C_BUSYO_CD <> 'F01008' ";	//客先は除く
		$sql = $sql."  AND TRBL.C_FLAW_KBN1 = MK1.区分明細_CD(+) ";
		$sql = $sql."  AND TRIM(TRBL.C_KBN) = MKBN.V2_KBN_MEI_CD(+) ";
		$sql = $sql."  AND TRIM(TRBL.C_PROCESS) = MKBN2.V2_KBN_MEI_CD(+) ";
		$sql = $sql."  AND TRIM(TRBL.C_BUSYO_CD) = CST.C_CUST_CD(+)) BAT ";
		$sql = $sql."ORDER BY C_POINTREF_NO,C_REFERENCE_NO DESC,N_REFERENCE_SEQ ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);

		$iRow = 0;

		while (oci_fetch($stmt)) {
			$aRes[$iRow][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));
			$aRes[$iRow][2] = $module_cmn->fChangDateFormat4(oci_result($stmt, 'N_INCIDENT_YMD'));
			$aRes[$iRow][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));
			$aRes[$iRow][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));
			$aRes[$iRow][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));
			$aRes[$iRow][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN_NM'));
			$aRes[$iRow][7] = $module_cmn->fChangUTF8(oci_result($stmt, 'KBN_NM'));
			$aRes[$iRow][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'PROCESS_NM'));


				
			$aRes[$iRow][9] = oci_result($stmt, 'N_FLAW_LOT_QTY');		//不具合数量
			$aRes[$iRow][12] = oci_result($stmt, 'N_UNIT_PRICE');		//単価
			if(oci_result($stmt, 'N_EXCLUDED') == 1){
				$aRes[$iRow][10] = "-";		//納入数量
				$aRes[$iRow][11] = "-";		//廃棄数量
				$aRes[$iRow][13] = "-";		//廃棄金額
				$aRes[$iRow][14] = "-";		//保留金額
			}else{
				$aRes[$iRow][10] = oci_result($stmt, 'N_FAILURE_QTY');		//納入数量
				$aRes[$iRow][11] = oci_result($stmt, 'N_DISPOSAL_QTY');		//廃棄数量
				$aRes[$iRow][13] = oci_result($stmt, 'N_DISPOSAL_PRICE');	//廃棄金額
				$aRes[$iRow][14] = oci_result($stmt, 'N_HOLD_PRICE');		//保留金額
			}

			//担当者
			$sTmpTanto = "";
			if($module_cmn->fChangUTF8(oci_result($stmt,'GRP')) == "-"){
				$sTmpTanto = "-";
			}else{
				if($module_cmn->fChangUTF8(oci_result($stmt,'TANTO_NM1'))<>""){
					$sTmpTanto = $module_cmn->fChangUTF8(oci_result($stmt,'TANTO_NM1'));
				}
				if($module_cmn->fChangUTF8(oci_result($stmt,'TANTO_NM2'))<>""){
					if($sTmpTanto <> ""){
						$sTmpTanto = $sTmpTanto.",".$module_cmn->fChangUTF8(oci_result($stmt,'TANTO_NM2'));
					}else{
						$sTmpTanto = $module_cmn->fChangUTF8(oci_result($stmt,'TANTO_NM2'));
					}
				}
				if($module_cmn->fChangUTF8(oci_result($stmt, 'TANTO_NM3'))<>""){
					if($sTmpTanto <> ""){
						$sTmpTanto = $sTmpTanto.",".$module_cmn->fChangUTF8(oci_result($stmt,'TANTO_NM3'));
					}else{
						$sTmpTanto = $module_cmn->fChangUTF8(oci_result($stmt,'TANTO_NM3'));
					}
				}
			}
			$aRes[$iRow][15] = $sTmpTanto;
			$aRes[$iRow][16] = $module_cmn->fChangUTF8(oci_result($stmt,'GRP'));
			$aRes[$iRow][17] = oci_result($stmt,'N_EXCLUDED');
			$aRes[$iRow][18] = oci_result($stmt,'C_FLAW_KBN1');
			$aRes[$iRow][19] = oci_result($stmt,'C_POINTREF_NO');
			$iRow = $iRow + 1;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}

	//不良内訳（協力会社起因）取得
	//引数	$paPara		パラメータ
	public function fGetTrblBatMonthG($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;
		
		//対象範囲
		if($paPara[2]-(floor($paPara[2]/100)*100) >= 7){
			$iDateF = (floor($paPara[2]/100)*100)+7;
		}else{
			$iDateF = (floor($paPara[2]/100)-1)*100+7;
		}
		$iDateT = $paPara[2];
		
		$iDateDF = ($iDateF*100)+1;
		$iDateDT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql."SELECT * ";
		$sql = $sql."  FROM ";
		$sql = $sql."(SELECT TRIM(TRBL.C_REFERENCE_NO) AS C_REFERENCE_NO ";
		$sql = $sql."      ,TRIM(TRBL.N_REFERENCE_SEQ) AS N_REFERENCE_SEQ ";
		$sql = $sql."      ,CASE WHEN TRIM(TRBL.C_POINTREF_NO) <> '' OR TRIM(TRBL.C_POINTREF_NO) IS NOT NULL THEN TRIM(TRBL.C_POINTREF_NO) ";
		$sql = $sql."            ELSE TRIM(TRBL.C_REFERENCE_NO) END AS C_POINTREF_NO ";
		$sql = $sql."      ,SUBSTR(N_INCIDENT_YMD,0,4) || '/' || SUBSTR(N_INCIDENT_YMD,5,2) || '/' || SUBSTR(N_INCIDENT_YMD,7,2) AS N_INCIDENT_YMD ";
		$sql = $sql."      ,TRIM(TRBL.C_PROD_CD) AS C_PROD_CD ";
		$sql = $sql."      ,TRBL.V2_DRW_NO ";
		$sql = $sql."      ,TRBL.V2_PROD_NM ";
		$sql = $sql."      ,MK1.区分明細名称_KJ AS C_FLAW_KBN_NM ";
		$sql = $sql."      ,MKBN.V2_KBN_MEI_NM AS KBN_NM ";
		$sql = $sql."      ,CASE WHEN TRIM(TRBL.C_PROCESS) = '-1' THEN '保留' ";
		$sql = $sql."            ELSE MKBN2.V2_KBN_MEI_NM ";
		$sql = $sql."       END AS PROCESS_NM ";
		$sql = $sql."      ,TRBL.N_FLAW_LOT_QTY ";
		$sql = $sql."      ,TRBL.N_FAILURE_QTY ";
		$sql = $sql."      ,TRBL.N_RETURN_QTY ";
		$sql = $sql."      ,TRBL.N_UNIT_PRICE ";
		$sql = $sql."      ,TRBL.N_RETURN_PRICE ";
		$sql = $sql."      ,TRBL.N_FLAW_PRICE-N_FAILURE_PRICE-N_DISPOSAL_PRICE-N_RETURN_PRICE-N_LOSS_PRICE-N_EXCLUD_PRICE AS N_HOLD_PRICE ";
		$sql = $sql."      ,CST.V2_CUST_NM AS CORP_NM ";
		$sql = $sql."      ,TRBL.N_EXCLUDED ";
		$sql = $sql."      ,TRIM(TRBL.C_FLAW_KBN1) AS C_FLAW_KBN1 ";
		$sql = $sql." FROM T_TR_TRBL TRBL ";
		$sql = $sql."     ,(SELECT * FROM M_区分@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM WHERE 区分_CD = '085') MK1 ";
		$sql = $sql."     ,(SELECT V2_KBN_MEI_CD,V2_KBN_MEI_NM FROM T_MS_FL_KBN WHERE V2_KBN_CD = 'C34') MKBN ";
		$sql = $sql."     ,(SELECT V2_KBN_MEI_CD,V2_KBN_MEI_NM FROM T_MS_FL_KBN WHERE V2_KBN_CD = 'C37') MKBN2 ";
		$sql = $sql."     ,V_FL_CUST_INFO CST ";
		$sql = $sql."WHERE ((N_DECISION_YMD BETWEEN ".$iDateDF." AND ".$iDateDT.") OR N_DECISION_YMD = 0) ";
		$sql = $sql."  AND C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
		$sql = $sql."  AND (C_BUSYO_CD NOT LIKE 'K%' AND (C_BUSYO_CD NOT LIKE 'F%' OR C_BUSYO_CD = 'F01008')) "; //Fの客先のみ含む
		$sql = $sql."  AND TRBL.C_FLAW_KBN1 = MK1.区分明細_CD(+) ";
		$sql = $sql."  AND TRIM(TRBL.C_KBN) = MKBN.V2_KBN_MEI_CD(+) ";
		$sql = $sql."  AND TRIM(TRBL.C_PROCESS) = MKBN2.V2_KBN_MEI_CD(+) ";
		$sql = $sql."  AND TRIM(TRBL.C_BUSYO_CD) = CST.C_CUST_CD(+)) BAT ";
		$sql = $sql."ORDER BY C_POINTREF_NO,C_REFERENCE_NO DESC,N_REFERENCE_SEQ ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);

		$iRow = 0;

		while (oci_fetch($stmt)) {
			$aRes[$iRow][0] = 0;
			$aRes[$iRow][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_REFERENCE_NO'));
			$aRes[$iRow][2] = $module_cmn->fChangDateFormat4(oci_result($stmt, 'N_INCIDENT_YMD'));
			$aRes[$iRow][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));
			$aRes[$iRow][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));
			$aRes[$iRow][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));
			$aRes[$iRow][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_FLAW_KBN_NM'));
			$aRes[$iRow][7] = $module_cmn->fChangUTF8(oci_result($stmt, 'KBN_NM'));
			$aRes[$iRow][8] = $module_cmn->fChangUTF8(oci_result($stmt, 'PROCESS_NM'));
			$aRes[$iRow][9] = oci_result($stmt, 'N_FLAW_LOT_QTY');			//不具合数量
			$aRes[$iRow][12] = oci_result($stmt, 'N_UNIT_PRICE');			//単価
			if(oci_result($stmt, 'N_EXCLUDED') == 1){
				$aRes[$iRow][10] = "-";		//納入数量
				$aRes[$iRow][11] = "-";		//返却数量
				$aRes[$iRow][13] = "-";		//返却金額
				$aRes[$iRow][14] = "-";		//保留金額
			}else{
				$aRes[$iRow][10] = oci_result($stmt, 'N_FAILURE_QTY');		//納入数量
				$aRes[$iRow][11] = oci_result($stmt, 'N_RETURN_QTY');		//返却数量
				$aRes[$iRow][13] = oci_result($stmt, 'N_RETURN_PRICE');		//返却金額
				$aRes[$iRow][14] = oci_result($stmt, 'N_HOLD_PRICE');		//保留金額
			}
			$aRes[$iRow][15] = $module_cmn->fChangUTF8(oci_result($stmt, 'CORP_NM'));
			$aRes[$iRow][16] = oci_result($stmt, 'N_EXCLUDED');
			$aRes[$iRow][17] = oci_result($stmt, 'C_FLAW_KBN1');
			$aRes[$iRow][18] = oci_result($stmt, 'C_POINTREF_NO');
			$iRow = $iRow + 1;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}

	//計算シートデータ取得
	//引数	$paPara		パラメータ
	public function fGetTrblSumSheet($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;

		//対象日
		if($paPara[2]-(floor($paPara[2]/100)*100) >= 7){
			$iDateF = (floor($paPara[2]/100)*100)+7;
		}else{
			$iDateF = (floor($paPara[2]/100)-1)*100+7;
		}
		$iDateT = $paPara[2];
		
		$iDateDF = ($iDateF*100)+1;
		$iDateDT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}

		//データ検索
		$sql = "";
		$sql = $sql."SELECT YM.N_YM ";
		$sql = $sql."      ,NVL(W01.PRICE,0)/1000	AS IND_P01 ";
		$sql = $sql."      ,NVL(F01.PRICE,0)/1000	AS DIS_P01 ";
		$sql = $sql."      ,NVL(W01.QTY,0)/1000		AS IND_Q01 ";
		$sql = $sql."      ,NVL(F01.QTY,0)/1000		AS DIS_Q01 ";
		$sql = $sql."      ,NVL(W02.PRICE,0)/1000	AS IND_P02 ";
		$sql = $sql."      ,NVL(F02.PRICE,0)/1000	AS DIS_P02 ";
		$sql = $sql."      ,NVL(W02.QTY,0)/1000		AS IND_Q02 ";
		$sql = $sql."      ,NVL(F02.QTY,0)/1000		AS DIS_Q02 ";
		$sql = $sql."      ,NVL(W03.PRICE,0)/1000	AS IND_P03 ";
		$sql = $sql."      ,NVL(F03.PRICE,0)/1000	AS DIS_P03 ";
		$sql = $sql."      ,NVL(W03.QTY,0)/1000		AS IND_Q03 ";
		$sql = $sql."      ,NVL(F03.QTY,0)/1000		AS DIS_Q03 ";
		$sql = $sql."      ,NVL(W04.PRICE,0)/1000	AS IND_P04 ";
		$sql = $sql."      ,NVL(F04.PRICE,0)/1000	AS DIS_P04 ";
		$sql = $sql."      ,NVL(W04.QTY,0)/1000		AS IND_Q04 ";
		$sql = $sql."      ,NVL(F04.QTY,0)/1000		AS DIS_Q04 ";
		$sql = $sql."      ,NVL(W05.PRICE,0)/1000	AS IND_P05 ";
		$sql = $sql."      ,NVL(F05.PRICE,0)/1000	AS DIS_P05 ";
		$sql = $sql."      ,NVL(W05.QTY,0)/1000		AS IND_Q05 ";
		$sql = $sql."      ,NVL(F05.QTY,0)/1000		AS DIS_Q05 ";
		$sql = $sql."      ,NVL(W06.PRICE,0)/1000	AS IND_P06 ";
		$sql = $sql."      ,NVL(T06.QTY,0)/1000		AS RET_Q06 ";
		$sql = $sql."      ,NVL(W07.PRICE,0)/1000	AS IND_P07 ";
		$sql = $sql."      ,NVL(T07.QTY,0)/1000		AS RET_Q07 ";
		$sql = $sql."  FROM ";
		//年月
		$sql = $sql." (SELECT DISTINCT N_YM ";
		$sql = $sql."    FROM T_TR_DOCU_HYOUKA ";
		$sql = $sql."   WHERE N_YM BETWEEN ".$iDateF." AND ".$iDateT.") YM  ";
		//生産一課（製造金額／数量）
		$sql = $sql.",(SELECT SUM(N_IND_PRICE1) AS PRICE ";
		$sql = $sql."        ,SUM(N_PROCESS_QTY1) AS QTY ";
		$sql = $sql."        ,N_YM ";
		$sql = $sql."    FROM T_TR_DOCU_HYOUKA ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."   GROUP BY N_YM) W01 ";
		//生産二課（製造金額／数量）
		$sql = $sql.",(SELECT SUM(N_IND_PRICE2) AS PRICE  ";
		$sql = $sql."        ,SUM(N_PROCESS_QTY2) AS QTY  ";
		$sql = $sql."        ,N_YM ";
		$sql = $sql."    FROM T_TR_DOCU_HYOUKA ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."   GROUP BY N_YM) W02 ";
		//生産三課（製造金額／数量）
		$sql = $sql.",(SELECT SUM(N_IND_PRICE3) AS PRICE  ";
		$sql = $sql."        ,SUM(N_PROCESS_QTY3) AS QTY  ";
		$sql = $sql."        ,N_YM ";
		$sql = $sql."    FROM T_TR_DOCU_HYOUKA ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."   GROUP BY N_YM) W03 ";
		//生産四課（製造金額／数量）
		$sql = $sql.",(SELECT SUM(N_IND_PRICE4) AS PRICE ";
		$sql = $sql."        ,SUM(N_PROCESS_QTY4) AS QTY ";
		$sql = $sql."        ,N_YM ";
		$sql = $sql."    FROM T_TR_DOCU_HYOUKA ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."   GROUP BY N_YM) W04 ";
		//生産五課（製造金額／数量）
		$sql = $sql.",(SELECT SUM(N_IND_PRICE5) AS PRICE ";
		$sql = $sql."        ,SUM(N_PROCESS_QTY5) AS QTY ";
		$sql = $sql."        ,N_YM ";
		$sql = $sql."    FROM T_TR_DOCU_HYOUKA ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."   GROUP BY N_YM) W05 ";
		//プレス協力会社（製造金額）
		$sql = $sql.",(SELECT SUM(N_IND_PRICE6) AS PRICE ";
		$sql = $sql."        ,N_YM ";
		$sql = $sql."    FROM T_TR_DOCU_HYOUKA ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."   GROUP BY N_YM) W06 ";
		//プレス協力会社（返却数量）
		$sql = $sql.",(SELECT SUBSTR(TRBL.N_DECISION_YMD,0,6) AS N_YM ";
		$sql = $sql."        ,SUM(TRBL.N_RETURN_PRICE) AS QTY ";
		$sql = $sql."    FROM T_TR_TRBL TRBL ";
		$sql = $sql."        ,NF.M_取引先@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM CST ";
		$sql = $sql."   WHERE (N_DECISION_YMD BETWEEN ".$iDateDF." AND ".$iDateDT.") ";
		$sql = $sql."     AND C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
		$sql = $sql."     AND (C_BUSYO_CD NOT LIKE 'K%' AND (C_BUSYO_CD NOT LIKE 'F%' OR C_BUSYO_CD = 'F01008')) ";
		$sql = $sql."     AND TRIM(TRBL.C_BUSYO_CD) = TRIM(CST.取引先_CD) ";
		$sql = $sql."     AND CST.工程区分_KU = '10' ";
		$sql = $sql."   GROUP BY SUBSTR(TRBL.N_DECISION_YMD,0,6)) T06 ";
		//めっき協力会社（製造金額）
		$sql = $sql.",(SELECT SUM(N_IND_PRICE7) AS PRICE ";
		$sql = $sql."        ,N_YM ";
		$sql = $sql."    FROM T_TR_DOCU_HYOUKA ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."   GROUP BY N_YM) W07 ";
		//めっき協力会社（返却数量）
		$sql = $sql.",(SELECT SUBSTR(TRBL.N_DECISION_YMD,0,6) AS N_YM ";
		$sql = $sql."        ,SUM(TRBL.N_RETURN_PRICE) AS QTY ";
		$sql = $sql."    FROM T_TR_TRBL TRBL ";
		$sql = $sql."        ,NF.M_取引先@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM CST ";
		$sql = $sql."   WHERE (N_DECISION_YMD BETWEEN ".$iDateDF." AND ".$iDateDT.") ";
		$sql = $sql."     AND C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
		$sql = $sql."     AND (C_BUSYO_CD NOT LIKE 'K%' AND (C_BUSYO_CD NOT LIKE 'F%' OR C_BUSYO_CD = 'F01008')) ";
		$sql = $sql."     AND TRIM(TRBL.C_BUSYO_CD) = TRIM(CST.取引先_CD) ";
		$sql = $sql."     AND CST.工程区分_KU = '50' ";
		$sql = $sql."   GROUP BY SUBSTR(TRBL.N_DECISION_YMD,0,6)) T07 ";
		//生産一課（廃棄金額／数量）
		$sql = $sql.",(SELECT SUM(N_DISPOSAL_PRICE) AS PRICE ";
		$sql = $sql."        ,SUM(N_DISPOSAL_QTY) AS QTY ";
		$sql = $sql."        ,SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
		$sql = $sql."    FROM T_TR_TRBL ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."     AND N_DECISION_YMD <> 0 ";
		$sql = $sql."     AND N_EXCLUDED = 0 ";
		$sql = $sql."     AND TRIM(C_BUSYO_CD) IN ('K01001','K01002') ";
		$sql = $sql."   GROUP BY SUBSTR(N_DECISION_YMD,1,6)) F01 ";
		//生産二課（廃棄金額／数量）
		$sql = $sql.",(SELECT SUM(N_DISPOSAL_PRICE) AS PRICE ";
		$sql = $sql."        ,SUM(N_DISPOSAL_QTY) AS QTY ";
		$sql = $sql."        ,SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
		$sql = $sql."    FROM T_TR_TRBL ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."     AND N_DECISION_YMD <> 0 ";
		$sql = $sql."     AND N_EXCLUDED = 0 ";
		$sql = $sql."     AND TRIM(C_BUSYO_CD) IN ('K01003','K01004') ";
		$sql = $sql."   GROUP BY SUBSTR(N_DECISION_YMD,1,6)) F02 ";
		//生産三課（廃棄金額／数量）
		$sql = $sql.",(SELECT SUM(N_DISPOSAL_PRICE) AS PRICE ";
		$sql = $sql."        ,SUM(N_DISPOSAL_QTY) AS QTY ";
		$sql = $sql."        ,SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
		$sql = $sql."    FROM T_TR_TRBL ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."     AND N_DECISION_YMD <> 0 ";
		$sql = $sql."     AND N_EXCLUDED = 0 ";
		$sql = $sql."     AND TRIM(C_BUSYO_CD) IN ('K01005','K01010','K01011') ";
		$sql = $sql."   GROUP BY SUBSTR(N_DECISION_YMD,1,6)) F03 ";
		//生産四課（廃棄金額／数量）
		$sql = $sql.",(SELECT SUM(N_DISPOSAL_PRICE) AS PRICE ";
		$sql = $sql."        ,SUM(N_DISPOSAL_QTY) AS QTY ";
		$sql = $sql."        ,SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
		$sql = $sql."    FROM T_TR_TRBL ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."     AND N_DECISION_YMD <> 0 ";
		$sql = $sql."     AND N_EXCLUDED = 0 ";
		$sql = $sql."     AND TRIM(C_BUSYO_CD) IN ('K01006','K01007') ";
		$sql = $sql."   GROUP BY SUBSTR(N_DECISION_YMD,1,6)) F04 ";
		//生産五課（廃棄金額／数量）
		$sql = $sql.",(SELECT SUM(N_DISPOSAL_PRICE) AS PRICE ";
		$sql = $sql."        ,SUM(N_DISPOSAL_QTY) AS QTY ";
		$sql = $sql."        ,SUBSTR(N_DECISION_YMD,1,6) AS N_YM ";
		$sql = $sql."    FROM T_TR_TRBL ";
		$sql = $sql."   WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."     AND N_DECISION_YMD <> 0 ";
		$sql = $sql."     AND N_EXCLUDED = 0 ";
		$sql = $sql."     AND TRIM(C_BUSYO_CD) IN ('K01008') ";
		$sql = $sql."   GROUP BY SUBSTR(N_DECISION_YMD,1,6)) F05 ";
		$sql = $sql."  WHERE YM.N_YM = W01.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = W02.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = W03.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = W04.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = W05.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = W06.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = T06.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = W07.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = T07.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = F01.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = F02.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = F03.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = F04.N_YM(+) ";
		$sql = $sql."    AND YM.N_YM = F05.N_YM(+) ";
		$sql = $sql."  ORDER BY YM.N_YM ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);
		
		$iRow = 0;
		while (oci_fetch($stmt)) {
			$aRes[$iRow][1] = oci_result($stmt, 'N_YM');
			$aRes[$iRow][2] = oci_result($stmt, 'IND_P01');
			$aRes[$iRow][3] = oci_result($stmt, 'DIS_P01');
			$aRes[$iRow][4] = oci_result($stmt, 'IND_Q01');
			$aRes[$iRow][5] = oci_result($stmt, 'DIS_Q01');
			$aRes[$iRow][6] = oci_result($stmt, 'IND_P02');
			$aRes[$iRow][7] = oci_result($stmt, 'DIS_P02');
			$aRes[$iRow][8] = oci_result($stmt, 'IND_Q02');
			$aRes[$iRow][9] = oci_result($stmt, 'DIS_Q02');
			$aRes[$iRow][10] = oci_result($stmt, 'IND_P03');
			$aRes[$iRow][11] = oci_result($stmt, 'DIS_P03');
			$aRes[$iRow][12] = oci_result($stmt, 'IND_Q03');
			$aRes[$iRow][13] = oci_result($stmt, 'DIS_Q03');
			$aRes[$iRow][14] = oci_result($stmt, 'IND_P04');
			$aRes[$iRow][15] = oci_result($stmt, 'DIS_P04');
			$aRes[$iRow][16] = oci_result($stmt, 'IND_Q04');
			$aRes[$iRow][17] = oci_result($stmt, 'DIS_Q04');
			$aRes[$iRow][18] = oci_result($stmt, 'IND_P05');
			$aRes[$iRow][19] = oci_result($stmt, 'DIS_P05');
			$aRes[$iRow][20] = oci_result($stmt, 'IND_Q05');
			$aRes[$iRow][21] = oci_result($stmt, 'DIS_Q05');
			$aRes[$iRow][22] = oci_result($stmt, 'IND_P06');
			$aRes[$iRow][23] = oci_result($stmt, 'RET_Q06');
			$aRes[$iRow][24] = oci_result($stmt, 'IND_P07');
			$aRes[$iRow][25] = oci_result($stmt, 'RET_Q07');
			$iRow = $iRow + 1;
		}
		
		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}
	
	//客先不良件数
	//引数	$paPara		パラメータ
	public function fGetFlawQty($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;
		
		//対象範囲
		if($paPara[2]-(floor($paPara[2]/100)*100)>= 7){
			$iDateF = (floor($paPara[2]/100)*100)+7;
		}else{
			$iDateF = ((floor($paPara[2]/100)-1)*100)+7;
		}
		$iDateT = $paPara[2];
		
		$sKi = $paPara[4].$paPara[1];			//当期
		$sKi1 = ($paPara[4]-1).$paPara[1];		//前期
		$sKi2 = ($paPara[4]-2).$paPara[1];		//前前期
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}
		
		//データ検索
		$sql = "";
		$sql = $sql."SELECT YM.N_YM AS N_YM ";
		$sql = $sql."      ,NVL(Q03.QTY,0) AS N_CLAIM_QTY ";
		$sql = $sql."      ,NVL(Q01.QTY,0) AS N_FLAW_QTY ";
		$sql = $sql."      ,NVL(Q00.QTY,0) AS N_COMP_QTY ";
		$sql = $sql."      ,NVL(Q04.QTY,0) AS N_REQ_QTY ";
		$sql = $sql."      ,NVL(Q05.QTY,0) AS N_INVEST_QTY ";
		$sql = $sql."FROM ";
		$sql = $sql."(SELECT DISTINCT ".floor($paPara[2]/100)." || SUBSTR(C_REFERENCE_NO,5,2) AS N_YM  ";
		$sql = $sql."    FROM T_TR_FLAW FLAW ";
		$sql = $sql."   WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi."%' ";
		$sql = $sql."     AND FLAW.N_DEL_FLG = 0) YM ";
		$sql = $sql.",(SELECT SUBSTR(FLAW.N_ISSUE_YMD1,1,6) AS N_YM ";
		$sql = $sql."        ,COUNT(*) AS QTY ";
		$sql = $sql."     FROM T_TR_FLAW FLAW ";
		$sql = $sql."    WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi."%' ";
		$sql = $sql."      AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."      AND FLAW.C_RESULT_KBN = 0 ";
		$sql = $sql."    GROUP BY SUBSTR(FLAW.N_ISSUE_YMD1,1,6)) Q00 ";
		$sql = $sql.",(SELECT SUBSTR(FLAW.N_ISSUE_YMD1,1,6) AS N_YM ";
		$sql = $sql."        ,COUNT(*) AS QTY ";
		$sql = $sql."     FROM T_TR_FLAW FLAW ";
		$sql = $sql."    WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi."%' ";
		$sql = $sql."      AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."      AND FLAW.C_RESULT_KBN = 1 ";
		$sql = $sql."    GROUP BY SUBSTR(FLAW.N_ISSUE_YMD1,1,6)) Q01 ";
		$sql = $sql.",(SELECT SUBSTR(FLAW.N_ISSUE_YMD1,1,6) AS N_YM ";
		$sql = $sql."        ,COUNT(*) AS QTY ";
		$sql = $sql."     FROM T_TR_FLAW FLAW ";
		$sql = $sql."    WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi."%' ";
		$sql = $sql."      AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."      AND FLAW.C_RESULT_KBN = 3 ";
		$sql = $sql."    GROUP BY SUBSTR(FLAW.N_ISSUE_YMD1,1,6)) Q03 ";
		$sql = $sql.",(SELECT SUBSTR(FLAW.N_ISSUE_YMD1,1,6) AS N_YM ";
		$sql = $sql."        ,COUNT(*) AS QTY ";
		$sql = $sql."     FROM T_TR_FLAW FLAW ";
		$sql = $sql."    WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi."%' ";
		$sql = $sql."      AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."      AND FLAW.C_RESULT_KBN = 4 ";
		$sql = $sql."    GROUP BY SUBSTR(FLAW.N_ISSUE_YMD1,1,6)) Q04 ";
		$sql = $sql.",(SELECT SUBSTR(FLAW.N_ISSUE_YMD1,1,6) AS N_YM ";
		$sql = $sql."        ,COUNT(*) AS QTY ";
		$sql = $sql."     FROM T_TR_FLAW FLAW ";
		$sql = $sql."    WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi."%' ";
		$sql = $sql."      AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."      AND FLAW.C_RESULT_KBN = -1 ";
		$sql = $sql."    GROUP BY SUBSTR(FLAW.N_ISSUE_YMD1,1,6)) Q05 ";
		$sql = $sql."WHERE YM.N_YM = Q00.N_YM(+) ";
		$sql = $sql."  AND YM.N_YM = Q01.N_YM(+) ";
		$sql = $sql."  AND YM.N_YM = Q03.N_YM(+) ";
		$sql = $sql."  AND YM.N_YM = Q04.N_YM(+) ";
		$sql = $sql."  AND YM.N_YM = Q05.N_YM(+) ";
		//前前期平均
		$sql = $sql."UNION ";
		$sql = $sql."SELECT '".((floor($iDateF/100)-2)*100)."' AS N_YM ";
		$sql = $sql."      ,NVL(Y203.QTY,0) AS N_CLAIM_QTY ";
		$sql = $sql."      ,NVL(Y201.QTY,0) AS N_FLAW_QTY ";
		$sql = $sql."      ,NVL(Y200.QTY,0) AS N_COMP_QTY ";
		$sql = $sql."      ,NVL(Y204.QTY,0) AS N_REQ_QTY ";
		$sql = $sql."      ,NVL(Y205.QTY,0) AS N_INVEST_QTY ";
		$sql = $sql."FROM ";
		$sql = $sql."(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."   FROM T_TR_FLAW FLAW ";
		$sql = $sql."  WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi2."%' ";
		$sql = $sql."    AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."    AND FLAW.C_RESULT_KBN = 0) Y200 ";
		$sql = $sql.",(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."    FROM T_TR_FLAW FLAW ";
		$sql = $sql."   WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi2."%' ";
		$sql = $sql."     AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."     AND FLAW.C_RESULT_KBN = 1) Y201 ";
		$sql = $sql.",(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."    FROM T_TR_FLAW FLAW ";
		$sql = $sql."   WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi2."%' ";
		$sql = $sql."     AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."     AND FLAW.C_RESULT_KBN = 3) Y203 ";
		$sql = $sql.",(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."    FROM T_TR_FLAW FLAW ";
		$sql = $sql."   WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi2."%' ";
		$sql = $sql."     AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."     AND FLAW.C_RESULT_KBN = 4) Y204 ";
		$sql = $sql.",(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."    FROM T_TR_FLAW FLAW ";
		$sql = $sql."   WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi2."%' ";
		$sql = $sql."     AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."     AND FLAW.C_RESULT_KBN = -1) Y205 ";
		//前期平均
		$sql = $sql."UNION ";
		$sql = $sql."SELECT '".((floor($iDateF/100)-1)*100)."' AS N_YM ";
		$sql = $sql."      ,NVL(Y103.QTY,0) AS N_CLAIM_QTY ";
		$sql = $sql."      ,NVL(Y101.QTY,0) AS N_FLAW_QTY ";
		$sql = $sql."      ,NVL(Y100.QTY,0) AS N_COMP_QTY ";
		$sql = $sql."      ,NVL(Y104.QTY,0) AS N_REQ_QTY ";
		$sql = $sql."      ,NVL(Y105.QTY,0) AS N_INVEST_QTY ";
		$sql = $sql."FROM ";
		$sql = $sql."(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."   FROM T_TR_FLAW FLAW ";
		$sql = $sql."  WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi1."%' ";
		$sql = $sql."    AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."    AND FLAW.C_RESULT_KBN = 0) Y100 ";
		$sql = $sql.",(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."    FROM T_TR_FLAW FLAW ";
		$sql = $sql."   WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi1."%' ";
		$sql = $sql."     AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."     AND FLAW.C_RESULT_KBN = 1) Y101 ";
		$sql = $sql.",(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."    FROM T_TR_FLAW FLAW ";
		$sql = $sql."   WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi1."%' ";
		$sql = $sql."     AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."     AND FLAW.C_RESULT_KBN = 3) Y103 ";
		$sql = $sql.",(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."    FROM T_TR_FLAW FLAW ";
		$sql = $sql."   WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi1."%' ";
		$sql = $sql."     AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."     AND FLAW.C_RESULT_KBN = 4) Y104 ";
		$sql = $sql.",(SELECT ROUND(COUNT(*)/12) AS QTY ";
		$sql = $sql."    FROM T_TR_FLAW FLAW ";
		$sql = $sql."   WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi1."%' ";
		$sql = $sql."     AND FLAW.N_DEL_FLG = 0 ";
		$sql = $sql."     AND FLAW.C_RESULT_KBN = -1) Y105 ";
		$sql = $sql."ORDER BY N_YM ";

		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);
		
		$iRow = 0;
		while (oci_fetch($stmt)) {
			$aRes[$iRow][1] = oci_result($stmt, 'N_YM');
			$aRes[$iRow][2] = oci_result($stmt, 'N_CLAIM_QTY');
			$aRes[$iRow][3] = oci_result($stmt, 'N_FLAW_QTY');
			$aRes[$iRow][4] = oci_result($stmt, 'N_COMP_QTY');
			$aRes[$iRow][5] = oci_result($stmt, 'N_REQ_QTY');
			$aRes[$iRow][6] = oci_result($stmt, 'N_INVEST_QTY');
			$iRow = $iRow + 1;
		}
		
		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}
	
	//客先不良内容取得
	//引数	$paPara		パラメータ
	public function fGetFlawDesc($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;

		//対象範囲
		if($paPara[2]-(floor($paPara[2]/100)*100) >= 7){
			$iDateF = (floor($paPara[2]/100)*100)+7;
		}else{
			$iDateF = (floor($paPara[2]/100)-1)*100+7;
		}
		$iDateT = $paPara[2];
		
		//当期
		$sKi = $paPara[4].$paPara[1];			//当期
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}
		
		//データ検索
		$sql = "";
		$sql = $sql."SELECT N_M";
		$sql = $sql."      ,CASE WHEN C_CUST_CD IN ('C8256','C4754','C7446','C7451','C7996','C8660','C9443','C9459','C9464','C9679','C9946','C1088','C1090','C2495','C2590','C7446','C8036','C5061','C5082','C7121','C7137','C8806') ";
		$sql = $sql."            THEN USER_NO || '_' || DRW_NO || '_' || KBN_NM || '_' || CORP_NM ";
		$sql = $sql."            ELSE USER_NO || '_' || PROD_NM || '_' || KBN_NM || '_' || CORP_NM END AS MEMO ";
		$sql = $sql."      ,MD_KBN ";
		$sql = $sql."      ,C_RESULT_KBN ";
		$sql = $sql."FROM ";
		$sql = $sql."(SELECT SUBSTR(FLAW.C_REFERENCE_NO,5,2) AS N_M ";
		$sql = $sql."       ,TRIM(FLAW.C_RESULT_KBN) AS C_RESULT_KBN ";
		$sql = $sql."       ,FLAW.C_REFERENCE_NO ";
		$sql = $sql."       ,SUBSTR(FLAW.C_PROD_CD,0,3) AS USER_NO ";
		$sql = $sql."       ,MK.区分明細名称_KJ AS KBN_NM ";
		$sql = $sql."       ,TRIM(FLAW.V2_PROD_NM) AS PROD_NM ";
		$sql = $sql."       ,TRIM(FLAW.V2_DRW_NO) AS DRW_NO ";
		$sql = $sql."       ,CASE WHEN C_INCIDENT_KBN = 0 THEN MT1.V2_CUST_NM_R ";
		$sql = $sql."             WHEN C_INCIDENT_KBN = 1 THEN MT2.V2_CUST_NM_R ";
		$sql = $sql."             WHEN C_INCIDENT_KBN = 2 THEN CASE WHEN NVL(MT2.V2_CUST_NM_R,' ') <> ' ' THEN MT2.V2_CUST_NM_R ELSE MT1.V2_CUST_NM_R END ";
		$sql = $sql."             WHEN C_INCIDENT_KBN = 3 THEN '発行先なし' END AS CORP_NM ";
		$sql = $sql."       ,FLAW.C_CUST_CD AS C_CUST_CD ";
		$sql = $sql."       ,MS.製品_KU AS MD_KBN ";
		$sql = $sql."  FROM T_TR_FLAW FLAW ";
		$sql = $sql."      ,V_FL_CUST_INFO MT1 ";
		$sql = $sql."      ,V_FL_CUST_INFO MT2 ";
		$sql = $sql."      ,M_製品@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM MS ";
		$sql = $sql."      ,(SELECT * FROM M_区分@NF.REGRESS.RDBMS.DEV.US.ORACLE.COM WHERE 区分_CD = '085') MK ";
		$sql = $sql." WHERE FLAW.C_REFERENCE_NO LIKE '%".$sKi."%' ";
		$sql = $sql."   AND FLAW.V2_INCIDENT_CD1 = TRIM(MT1.C_CUST_CD(+) ) ";
		$sql = $sql."   AND FLAW.V2_INCIDENT_CD2 = TRIM(MT2.C_CUST_CD(+) ) ";
		$sql = $sql."   AND TRIM(FLAW.C_PROD_CD) = TRIM(MS.製品_CD(+) ) ";
		$sql = $sql."   AND FLAW.C_FLAW_KBN = MK.区分明細_CD(+) ";
		$sql = $sql."   AND FLAW.N_DEL_FLG = 0 ";
		//2019/09/20 ADD START
		//$sql = $sql."   AND FLAW.C_RESULT_KBN IN (1,3)) ";
		$sql = $sql."   AND FLAW.C_RESULT_KBN IN (-1,1,3)) ";
		//2019/09/20 ADD END
		$sql = $sql." ORDER BY N_M,C_RESULT_KBN,C_REFERENCE_NO ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);
		
		$iRow = 0;
		while (oci_fetch($stmt)) {
			$aRes[$iRow][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'N_M'));
			$aRes[$iRow][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'MEMO'));
			$aRes[$iRow][3] = oci_result($stmt, 'MD_KBN');
			$aRes[$iRow][4] = oci_result($stmt, 'C_RESULT_KBN');
			$iRow = $iRow + 1;
		}
		
		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}
	
	//当月材料歩留表取得
	//引数	$paPara		パラメータ
	public function fGetTrblHoryu($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;

		
		//対象日
		$iDateF = $paPara[2]."01";
		$iDateT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql."SELECT C_PROD_CD ";
 		$sql = $sql."      ,V2_PROD_NM ";
		$sql = $sql."      ,V2_DRW_NO ";
		$sql = $sql."      ,C_MATERIAL_CD ";
		$sql = $sql."      ,N_MAT_WGT ";
		$sql = $sql."      ,N_MANUFA_QTY ";
		$sql = $sql."      ,N_STAND_WGT ";
		$sql = $sql."      ,N_STAND_PRICE ";
		$sql = $sql."      ,N_ACTUAL_WGT ";
		$sql = $sql."      ,N_SJ ";
		$sql = $sql."      ,N_MAT_UNIT ";
		$sql = $sql."      ,N_DIF_PRICE ";
		$sql = $sql."      ,N_YIELD ";
		$sql = $sql."      ,N_MANU_UNIT ";
		$sql = $sql."      ,N_MANU_PRICE ";
		$sql = $sql."      ,N_STAND_PROCESS_QTY ";
		$sql = $sql."      ,N_STAND_PROCESS_PRICE ";
		$sql = $sql."      ,N_ACTUAL_HOLD ";
		$sql = $sql."  FROM T_TR_TRBL_HORYU ";
		$sql = $sql." WHERE TRIM(C_TARGET_SECTION_KBN) = '".$paPara[1]."' ";
		$sql = $sql."   AND N_YM = ".$paPara[2]." ";
		$sql = $sql." ORDER BY C_PROD_CD,C_MATERIAL_CD ";
		
		//SQLをSJISに変換(DB)
		//$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);

		$iRow = 0;
		while (oci_fetch($stmt)) {
			$aRes[$iRow][1] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_PROD_CD'));
			$aRes[$iRow][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_PROD_NM'));
			$aRes[$iRow][3] = $module_cmn->fChangUTF8(oci_result($stmt, 'V2_DRW_NO'));
			$aRes[$iRow][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'C_MATERIAL_CD'));
			$aRes[$iRow][5] = oci_result($stmt, 'N_MAT_WGT');
			$aRes[$iRow][6] = oci_result($stmt, 'N_MANUFA_QTY');
			$aRes[$iRow][7] = oci_result($stmt, 'N_STAND_WGT');
			$aRes[$iRow][8] = oci_result($stmt, 'N_STAND_PRICE');
			$aRes[$iRow][9] = oci_result($stmt, 'N_ACTUAL_WGT');
			$aRes[$iRow][10] = oci_result($stmt, 'N_SJ');
			$aRes[$iRow][11] = oci_result($stmt, 'N_MAT_UNIT');
			$aRes[$iRow][12] = oci_result($stmt, 'N_DIF_PRICE');
			$aRes[$iRow][13] = oci_result($stmt, 'N_YIELD');
			$aRes[$iRow][14] = oci_result($stmt, 'N_MANU_UNIT');
			$aRes[$iRow][15] = oci_result($stmt, 'N_MANU_PRICE');
			$aRes[$iRow][16] = oci_result($stmt, 'N_STAND_PROCESS_QTY');
			$aRes[$iRow][17] = oci_result($stmt, 'N_STAND_PROCESS_PRICE');
			$aRes[$iRow][18] = oci_result($stmt, 'N_ACTUAL_HOLD');
			$iRow = $iRow + 1;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}

	//集計履歴データ取得
	//引数	$strRenkeiPg(連携PG)
	public function fGetRenkeiRireki($sRenkeiPg,$psBuKbn){

		require_once("module_common.php");
		$module_cmn = new module_common;

		//対象データ取得
		$aYm = $this->fChkHyokaCu($sRenkeiPg,$psBuKbn);
		
		$aPara = array();
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//データ検索SQL
		$sql = "";
		$sql = $sql."SELECT * FROM ";
		$sql = $sql." (SELECT T1.N_INS_YMD AS INS_YMD ";
		$sql = $sql."        ,T4.V2_KBN_MEI_NM AS DOCU_NM ";
		$sql = $sql."        ,T3.V2_KBN_MEI_NM AS BUMON_NM ";
		$sql = $sql."        ,T1.N_YM AS YM ";
		$sql = $sql."        ,T2.V2_SHAIN_NM AS SHAIN_NM ";
		$sql = $sql." FROM T_TR_DOCU_RIREKI T1 ";
		$sql = $sql.",T_MS_SHAIN T2 ";
		$sql = $sql.",T_MS_FL_KBN T3 ";
		$sql = $sql.",T_MS_FL_KBN T4 ";
		$sql = $sql." WHERE T1.C_INS_SHAIN_CD = T2.C_SHAIN_CD(+) ";
		$sql = $sql."   AND T3.V2_KBN_CD = 'C04' ";
		$sql = $sql."   AND T4.V2_KBN_CD = 'C40' ";
		$sql = $sql."   AND TRIM(T1.C_TARGET_SECTION_KBN) = T3.V2_KBN_MEI_CD(+) ";
		$sql = $sql."   AND T1.V2_DOCU_CD = T4.V2_KBN_MEI_CD(+) ";
		$sql = $sql."   AND T1.N_DEL_FLG = 0 ";
		$sql = $sql."   AND T1.N_YM IN (".date("Ym").",".date("Ym",mktime(0,0,0,date("m")-1,1,date("Y"))).") ";
		$sql = $sql."   AND T1.C_TARGET_SECTION_KBN = '".$psBuKbn."' ";
		$sql = $sql."   AND T1.V2_INS_PG = '".$sRenkeiPg."' ";
		$sql = $sql." ORDER BY T1.N_INS_YMD DESC ";
		$sql = $sql." ) WT WHERE ROWNUM <= 30 ";
		
		//SQLの分析
		$stmt = oci_parse($conn, $sql);
		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		while (oci_fetch($stmt)){
			$aPara[$i][0] = $module_cmn->fChangUTF8(oci_result($stmt,'INS_YMD'));
			$aPara[$i][1] = $module_cmn->fChangUTF8(oci_result($stmt,'DOCU_NM'));
			$aPara[$i][2] = $module_cmn->fChangUTF8(oci_result($stmt,'BUMON_NM'));
			$aPara[$i][3] = $module_cmn->fChangUTF8(oci_result($stmt,'YM'));
			$aPara[$i][4] = $module_cmn->fChangUTF8(oci_result($stmt,'SHAIN_NM'));
			if($module_cmn->fChangUTF8(oci_result($stmt, 'INS_YMD')) == $aYm[0]){
				$aPara[$i][5] = "対象";
			}elseif($module_cmn->fChangUTF8(oci_result($stmt, 'INS_YMD')) == $aYm[1]){
				$aPara[$i][5] = "対象";
			}else{
				$aPara[$i][5] = "";
			}
			
			$i = $i + 1;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}
	
//2019/08/01 AD END T.FUJITA

//2019/09/20 AD START T.FUJITA
	//適用データ取得
	//引数	$strRenkeiPg(連携PG)
	public function fChkHyokaCu($sRenkeiPg,$psBuKbn){

		require_once("module_common.php");
		$module_cmn = new module_common;
		
		$aPara = array();
		$aPara[0] = 0;
		$aPara[1] = 0;
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
		}

		//データ検索SQL
		$sql = "";
		$sql = $sql."SELECT N_YM ";
		$sql = $sql."      ,N_INS_YMD ";
		$sql = $sql."  FROM T_TR_DOCU_HYOUKA ";
		$sql = $sql." WHERE N_YM IN (".date("Ym").",".date("Ym",mktime(0,0,0,date("m")-1,1,date("Y"))).") ";
		$sql = $sql."   AND C_TARGET_SECTION_KBN = '".$psBuKbn."' ";
		$sql = $sql."   AND V2_INS_PG = '".$sRenkeiPg."' ";
		$sql = $sql." ORDER BY N_YM DESC ";
		//SQLの分析
		$stmt = oci_parse($conn, $sql);
		//SQLの実行
		oci_execute($stmt,OCI_DEFAULT);

		$i = 0;
		while (oci_fetch($stmt)){
			if(oci_result($stmt,'N_YM') == date("Ym")){
				$aPara[0] = oci_result($stmt,'N_INS_YMD');
			}elseif(oci_result($stmt,'N_YM') == date("Ym",mktime(0,0,0,date("m")-1,1,date("Y")))){
				$aPara[1] = oci_result($stmt,'N_INS_YMD');
			}
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aPara;
	}

	//協力工場品質評価データ取得
	//引数	$paPara		パラメータ
	public function fGetKyoData($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;

		//対象範囲
		if($paPara[2]-(floor($paPara[2]/100)*100)>= 7){
			$iDateF = (floor($paPara[2]/100)*100)+7;
		}else{
			$iDateF = ((floor($paPara[2]/100)-1)*100)+7;
		}
		$iDateT = $paPara[2];
		$iDateDF = ($iDateF*100)+1;
		$iDateDT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		$iDateF = 201903;
		$iDateDF = ($iDateF*100)+1;
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql."SELECT CD || SUBSTR(YM,5,2) AS KEY ";
		$sql = $sql."      ,CD ";
		$sql = $sql."      ,NM ";
		$sql = $sql."      ,YM ";
		$sql = $sql."      ,UKE_QTY ";
		$sql = $sql."      ,KYA_QTY ";
		$sql = $sql."      ,SYA_QTY ";
		$sql = $sql."      ,GOU_QTY ";
		$sql = $sql."      ,FUG_QTY ";
		$sql = $sql."      ,KYAGEN_QTY ";
		$sql = $sql."      ,SYAGEN_QTY ";
		$sql = $sql."      ,GOU_TOKU ";
		$sql = $sql."      ,CASE WHEN GOU_TOKU>=95 THEN 'Ａ' ";
		$sql = $sql."            WHEN GOU_TOKU>=84 THEN 'Ｂ' ";
		$sql = $sql."            WHEN GOU_TOKU>=70 THEN 'Ｃ' ";
		$sql = $sql."            WHEN GOU_TOKU>=55 THEN 'Ｄ' ";
		$sql = $sql."            WHEN GOU_TOKU>0 THEN 'Ｅ' ";
		$sql = $sql."       ELSE '' END AS RNK ";
		$sql = $sql."      ,RANK() OVER (PARTITION BY YM,KBN ORDER BY GOU_TOKU*100000+UKE_QTY DESC) JUN ";
		$sql = $sql."FROM ";
		$sql = $sql."( ";
		$sql = $sql."SELECT UKE.KEY ";
		$sql = $sql."      ,UKE.CD ";
		$sql = $sql."      ,CT.取引先略称_KJ AS NM ";
		$sql = $sql."      ,UKE.YM ";
		$sql = $sql."      ,NVL(UKE.UKE_QTY,0) AS UKE_QTY ";
		$sql = $sql."      ,NVL(FL.KYAKU_QTY,0) AS KYA_QTY ";
		$sql = $sql."      ,NVL(FL.SYANA_QTY,0) AS SYA_QTY ";
		$sql = $sql."      ,NVL(UKE.UKE_QTY,0)-NVL(FL.KYAKU_QTY,0)-NVL(FL.SYANA_QTY,0) AS GOU_QTY ";
		$sql = $sql."      ,CASE WHEN NVL(UKE.UKE_QTY,0)=0 THEN 0 ELSE ROUND(((NVL(FL.KYAKU_QTY,0)+NVL(FL.SYANA_QTY,0))/NVL(UKE.UKE_QTY,0))*100,1) END AS FUG_QTY ";
		$sql = $sql."      ,NVL(FL.KYAKU_QTY,0)*4 AS KYAGEN_QTY ";
		$sql = $sql."      ,NVL(FL.SYANA_QTY,0)*4 AS SYAGEN_QTY ";
		$sql = $sql."      ,CASE WHEN NVL(UKE.UKE_QTY,0)=0 THEN 0 ";
		$sql = $sql."            ELSE ROUND((100-ROUND(((NVL(FL.KYAKU_QTY,0)+NVL(FL.SYANA_QTY,0))/NVL(UKE.UKE_QTY,0))*100,1))-NVL(FL.KYAKU_QTY,0)*6-NVL(FL.SYANA_QTY,0)*4,1) ";
		$sql = $sql."       END AS GOU_TOKU ";
		$sql = $sql."      ,CT.工程区分_KU AS KBN ";
		$sql = $sql."FROM ";
		$sql = $sql."(SELECT TRIM(JG.手配先_CD) || SUBSTR(TO_CHAR(検収日_YMD),0,6) AS KEY ";
		$sql = $sql."      ,TRIM(JG.手配先_CD) AS CD ";
		$sql = $sql."      ,SUBSTR(TO_CHAR(検収日_YMD),0,6) AS YM ";
		$sql = $sql."      ,COUNT(*) AS UKE_QTY ";
		$sql = $sql." FROM J_外注受入検収@NF.US.ORACLE.COM@NF JG ";
		$sql = $sql."WHERE JG.管理部署 = 1 ";
		$sql = $sql."  AND JG.削除日_YMD = 0 ";
		$sql = $sql."  AND JG.検収日_YMD BETWEEN '".$iDateDF."' AND '".$iDateDT."' ";
		$sql = $sql."GROUP BY JG.手配先_CD,SUBSTR(TO_CHAR(検収日_YMD),0,6) ";
		$sql = $sql."UNION  ";
		$sql = $sql."SELECT TRIM(T2.C_SHIIRE_CD) || SUBSTR(TO_CHAR(T1.N_KENSHU_YMD),0,6) AS KEY ";
		$sql = $sql."      ,TRIM(T2.C_SHIIRE_CD) AS CD ";
		$sql = $sql."      ,SUBSTR(TO_CHAR(T1.N_KENSHU_YMD),0,6) AS YM ";
		$sql = $sql."      ,COUNT(*) AS UKE_QTY ";
		$sql = $sql."FROM PAPS.T_TR_UKEIRE_D T1 ";
		$sql = $sql."    ,PAPS.T_TR_CHUMON T2 ";
		$sql = $sql."WHERE T1.C_CHUMON_NO = T2.C_CHUMON_NO ";
		$sql = $sql."  AND T1.C_CHUMON_EDA_NO = T2.C_CHUMON_EDA_NO ";
		$sql = $sql."  AND T1.N_DEL_FLG = 0 ";
		$sql = $sql."  AND T2.N_DEL_FLG = 0 ";
		//B コネクタ
		$sql = $sql."  AND T2.V2_SHUKEI_BUMON_CD = 'B'  ";
		//検索条件(注文区分) 外注仕入れ
		$sql = $sql."  AND T2.V2_CHUMON_KBN = '30' ";
		//検索条件(検収日)
		$sql = $sql."  AND T1.N_KENSHU_YMD BETWEEN 20180701 AND 20190831  ";
		//検索条件(取引先ｺｰﾄﾞ) (有)クラール,(株)ハイライト,(株)カンドリ工業のみ
		$sql = $sql."  AND T2.C_SHIIRE_CD IN ('S7514','S1132','S1108')  ";
		$sql = $sql."GROUP BY T2.C_SHIIRE_CD ,SUBSTR(TO_CHAR(T1.N_KENSHU_YMD),0,6) ";
		$sql = $sql.") UKE ";
		$sql = $sql.",(SELECT CASE WHEN TO_NUMBER(SUBSTR(FL.NO,5,2)) >= 7 THEN FL.V2_INCIDENT_CD2  || (SUBSTR(FL.NO,0,2) + 1968) || SUBSTR(FL.NO,5,2) ";
		$sql = $sql."            ELSE FL.V2_INCIDENT_CD2 || (SUBSTR(FL.NO,0,2) + 1969) || SUBSTR(FL.NO,5,2) END AS KEY  ";
		$sql = $sql."      ,FL.V2_INCIDENT_CD2 AS CD ";
		$sql = $sql."      ,CASE WHEN TO_NUMBER(SUBSTR(FL.NO,5,2)) >= 7 THEN (SUBSTR(FL.NO,0,2) + 1968) || SUBSTR(FL.NO,5,2) ";
		$sql = $sql."            ELSE (SUBSTR(FL.NO,0,2) + 1969) || SUBSTR(FL.NO,5,2) END AS YM  ";
		$sql = $sql."      ,NVL(FL1.QTY,0) AS KYAKU_QTY  ";
		$sql = $sql."      ,NVL(FL2.QTY,0) AS SYANA_QTY  ";
		$sql = $sql."  FROM  ";
		//当期（客先）
		$sql = $sql."(SELECT SUBSTR(FL.C_REFERENCE_NO,0,6) AS NO  ";
		$sql = $sql."      ,FL.V2_INCIDENT_CD2  ";
		$sql = $sql." FROM T_TR_FLAW FL  ";
		$sql = $sql."WHERE FL.C_REFERENCE_NO LIKE '".$aPara[4].$aPara[1]."%'  ";
		$sql = $sql."  AND FL.V2_INCIDENT_CD2 IS NOT NULL  ";
		$sql = $sql."GROUP BY SUBSTR(C_REFERENCE_NO,0,6),V2_INCIDENT_CD2  ";
		//前期（客先）
		$sql = $sql."UNION  ";
		$sql = $sql."SELECT SUBSTR(FL.C_REFERENCE_NO,0,6) AS NO  ";
		$sql = $sql."      ,FL.V2_INCIDENT_CD2  ";
		$sql = $sql." FROM T_TR_FLAW FL  ";
		$sql = $sql."WHERE FL.C_REFERENCE_NO LIKE '".($aPara[4]-1).$aPara[1]."%'  ";
  		$sql = $sql."AND FL.V2_INCIDENT_CD2 IS NOT NULL  ";
		$sql = $sql."GROUP BY SUBSTR(C_REFERENCE_NO,0,6),V2_INCIDENT_CD2  ";
		//当期（赤伝）
		$sql = $sql."UNION  ";
		$sql = $sql."SELECT '51F' || '-' || SUBSTR(TO_CHAR(N_INCIDENT_YMD),5,2) AS NO  ";
		$sql = $sql."      ,TRIM(C_INCIDENT_CD) AS V2_INCIDENT_CD2  ";
		$sql = $sql." FROM T_TR_TRBL  ";
		$sql = $sql."WHERE C_TARGET_SECTION_KBN = '".$aPara[1]."' ";
		$sql = $sql."  AND (SUBSTR(C_INCIDENT_CD,0,1) = 'G'  ";
		$sql = $sql."   OR C_INCIDENT_CD IN ('S7514','S1132','S1108'))  ";
		$sql = $sql."  AND C_INCIDENT_CD IS NOT NULL  ";
		$sql = $sql."  AND N_INCIDENT_YMD >= ".$iDateDF." ";
		$sql = $sql."  AND N_INCIDENT_YMD <= ".$iDateDT."  ";
		$sql = $sql."GROUP BY '51F' || '-' || SUBSTR(TO_CHAR(N_INCIDENT_YMD),5,2),C_INCIDENT_CD  ";
		//前期（赤伝）
		$sql = $sql."UNION  ";
		$sql = $sql."SELECT '50F' || '-' || SUBSTR(TO_CHAR(N_INCIDENT_YMD),5,2) AS NO  ";
		$sql = $sql."      ,TRIM(C_INCIDENT_CD) AS V2_INCIDENT_CD2  ";
		$sql = $sql." FROM T_TR_TRBL  ";
		$sql = $sql."WHERE C_TARGET_SECTION_KBN = '".$aPara[1]."'  ";
		$sql = $sql."  AND (SUBSTR(C_INCIDENT_CD,0,1) = 'G'  ";
		$sql = $sql."   OR C_INCIDENT_CD IN ('S7514','S1132','S1108'))  ";
		$sql = $sql."  AND C_INCIDENT_CD IS NOT NULL  ";
		$sql = $sql."  AND N_INCIDENT_YMD >= ".$iDateDF." ";
		$sql = $sql."  AND N_INCIDENT_YMD <= ".$iDateDT." ";
		$sql = $sql."GROUP BY '50F' || '-' || SUBSTR(TO_CHAR(N_INCIDENT_YMD),5,2),C_INCIDENT_CD ";
		$sql = $sql.") FL  ";
		//当期不具合（客先）
		$sql = $sql.",(SELECT SUBSTR(C_REFERENCE_NO,0,6) AS NO  ";
		$sql = $sql."      ,V2_INCIDENT_CD2  ";
		$sql = $sql."      ,COUNT(*) AS QTY  ";
		$sql = $sql." FROM T_TR_FLAW  ";
		$sql = $sql."WHERE C_REFERENCE_NO LIKE '".$aPara[4].$aPara[1]."' || '%'  ";
		$sql = $sql."  AND C_RESULT_KBN IN('0','1','3')  ";
		$sql = $sql."  AND V2_INCIDENT_CD2 IS NOT NULL  ";
		$sql = $sql."GROUP BY SUBSTR(C_REFERENCE_NO,0,6),V2_INCIDENT_CD2,C_RESULT_KBN  ";
		//前期不具合（客先）
		$sql = $sql."UNION  ";
		$sql = $sql."SELECT SUBSTR(C_REFERENCE_NO,0,6) AS NO  ";
		$sql = $sql."      ,V2_INCIDENT_CD2  ";
		$sql = $sql."      ,COUNT(*) AS QTY  ";
		$sql = $sql." FROM T_TR_FLAW  ";
		$sql = $sql."WHERE C_REFERENCE_NO LIKE '".($aPara[4]-1).$aPara[1]."' || '%'  ";
		$sql = $sql."  AND C_RESULT_KBN IN('0','1','3')  ";
		$sql = $sql."  AND V2_INCIDENT_CD2 IS NOT NULL  ";
		$sql = $sql."GROUP BY SUBSTR(C_REFERENCE_NO,0,6),V2_INCIDENT_CD2,C_RESULT_KBN ";
		$sql = $sql.") FL1  ";
		//当期不具合（赤伝）
		$sql = $sql.",(SELECT '".$aPara[4].$aPara[1]."' || '-' || SUBSTR(TO_CHAR(N_INCIDENT_YMD),5,2) AS NO  ";
		$sql = $sql."      ,TRIM(C_PARTNER_CD) AS V2_INCIDENT_CD2  ";
		$sql = $sql."      ,COUNT(*) AS QTY  ";
		$sql = $sql." FROM T_TR_TRBL  ";
		$sql = $sql."WHERE C_TARGET_SECTION_KBN = 'F'  ";
		$sql = $sql."  AND (SUBSTR(C_INCIDENT_CD,0,1) = 'G'  ";
		$sql = $sql."   OR C_PARTNER_CD IN ('S7514','S1132','S1108'))  ";
		$sql = $sql."  AND C_PARTNER_CD IS NOT NULL  ";
		$sql = $sql."  AND N_INCIDENT_YMD >= ".$iDateDF." ";
		$sql = $sql."  AND N_INCIDENT_YMD <= ".$iDateDT." ";
		$sql = $sql."GROUP BY '".$aPara[4].$aPara[1]."' || '-' || SUBSTR(TO_CHAR(N_INCIDENT_YMD),5,2),C_PARTNER_CD  ";
		//前期不具合（赤伝）
		$sql = $sql."UNION  ";
		$sql = $sql."SELECT '".($aPara[4]-1).$aPara[1]."' || '-' || SUBSTR(TO_CHAR(N_INCIDENT_YMD),5,2) AS NO  ";
		$sql = $sql."      ,TRIM(C_PARTNER_CD) AS V2_INCIDENT_CD2  ";
		$sql = $sql."      ,COUNT(*) AS QTY  ";
		$sql = $sql." FROM T_TR_TRBL  ";
		$sql = $sql."WHERE C_TARGET_SECTION_KBN = 'F'  ";
		$sql = $sql."  AND (SUBSTR(C_INCIDENT_CD,0,1) = 'G'  ";
		$sql = $sql."   OR C_PARTNER_CD IN ('S7514','S1132','S1108'))  ";
		$sql = $sql."  AND C_PARTNER_CD IS NOT NULL  ";
		$sql = $sql."  AND N_INCIDENT_YMD >= ".$iDateDF." ";
		$sql = $sql."  AND N_INCIDENT_YMD <= ".$iDateDT." ";
		$sql = $sql."GROUP BY '".($aPara[4]-1).$aPara[1]."' || '-' || SUBSTR(TO_CHAR(N_INCIDENT_YMD),5,2),C_PARTNER_CD  ";
		$sql = $sql.") FL2  ";
		$sql = $sql."WHERE FL.NO = FL1.NO(+)  ";
		$sql = $sql."  AND FL.NO = FL2.NO(+)  ";
		$sql = $sql."  AND FL.V2_INCIDENT_CD2 = FL1.V2_INCIDENT_CD2(+)  ";
		$sql = $sql."  AND FL.V2_INCIDENT_CD2 = FL2.V2_INCIDENT_CD2(+) ) FL ";
		$sql = $sql.",M_取引先@NF.US.ORACLE.COM@NF CT ";
		$sql = $sql."WHERE UKE.KEY = FL.KEY(+) ";
		$sql = $sql."  AND TRIM(CT.取引先_CD) = UKE.CD ";
		$sql = $sql.") FLAW ";
		$sql = $sql."WHERE YM >= ".$iDateF." ";
		$sql = $sql."  AND CD IN ('G1058','G1078','G1073','G1100','G1142','G3771','G5460','G5653','G5758','G6327','6327','G7619','G7755','G7760','G8015','G8319','G8408','G8654','S1171','G5255','G5276','G7273','G7446','G7520','G8612','S1108','S1132','S7514') ";
		$sql = $sql."ORDER BY KBN DESC, KEY ";
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);
		
		$iRow = 0;
		while (oci_fetch($stmt)) {
			$aRes[1][$iRow] = oci_result($stmt, 'KEY');
			$aRes[2][$iRow] = oci_result($stmt, 'CD');
			$aRes[3][$iRow] = $module_cmn->fChangUTF8(oci_result($stmt, 'NM'));
			$aRes[4][$iRow] = oci_result($stmt, 'YM');
			$aRes[5][$iRow] = oci_result($stmt, 'UKE_QTY');
			$aRes[6][$iRow] = oci_result($stmt, 'KYA_QTY');
			$aRes[7][$iRow] = oci_result($stmt, 'SYA_QTY');
			$aRes[8][$iRow] = oci_result($stmt, 'GOU_QTY');
			$aRes[9][$iRow] = oci_result($stmt, 'FUG_QTY');
			$aRes[10][$iRow] = oci_result($stmt, 'KYAGEN_QTY');
			$aRes[11][$iRow] = oci_result($stmt, 'SYAGEN_QTY');
			$aRes[12][$iRow] = oci_result($stmt, 'GOU_TOKU');
			$aRes[13][$iRow] = $module_cmn->fChangUTF8(oci_result($stmt, 'RNK'));
			$aRes[14][$iRow] = oci_result($stmt, 'JUN');
			$iRow = $iRow + 1;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}
	
	//協力工場不良内容取得
	//引数	$paPara		パラメータ
	public function fGetKyoDet($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;

		//対象範囲
		$iDateDF = ($paPara[2]*100)+1;
		$iDateDT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		//客先
		$sql = $sql."SELECT FL.V2_INCIDENT_CD2  AS CD ";
		$sql = $sql."      ,CT.V2_CUST_NM_R AS NM ";
		$sql = $sql."      ,TRIM(FL.C_REFERENCE_NO) AS NO ";
		$sql = $sql."      ,KB.V2_KBN_DETAIL_NM AS DET ";
		$sql = $sql."      ,FL.V2_DRW_NO AS DRW ";
		$sql = $sql."      ,FL.V2_PROD_NM AS PROD ";
		$sql = $sql." FROM T_TR_FLAW FL ";
		$sql = $sql."     ,V_FL_CUST_INFO CT ";
		$sql = $sql."     ,V_FL_FLAW_INFO KB ";
		$sql = $sql."WHERE FL.C_REFERENCE_NO LIKE '".$paPara[4].$paPara[1]."-".substr($paPara[2],4,2)."%' ";
		$sql = $sql."  AND (SUBSTR(FL.V2_INCIDENT_CD2,0,1) = 'G' ";
		$sql = $sql."   OR FL.V2_INCIDENT_CD2 IN ('S7514','S1132','S1108','S1108','S1171')) ";
		$sql = $sql."  AND FL.V2_INCIDENT_CD2 IS NOT NULL ";
		$sql = $sql."  AND FL.V2_INCIDENT_CD2 = CT.C_CUST_CD(+) ";
		$sql = $sql."  AND FL.C_RESULT_KBN IN('0','1','3')";
		$sql = $sql."  AND KB.C_KBN_CD = '085' ";
		$sql = $sql."  AND TRIM(FL.C_FLAW_KBN) = KB.C_KBN_DETAIL_CD(+) ";
		//赤伝
		$sql = $sql."UNION ";
		$sql = $sql."SELECT TRIM(TRB.C_INCIDENT_CD) AS CD ";
		$sql = $sql."      ,CT.V2_CUST_NM_R AS NM ";
		$sql = $sql."      ,TRIM(TRB.C_REFERENCE_NO) AS NO ";
		$sql = $sql."      ,KB.V2_KBN_DETAIL_NM AS DET ";
		$sql = $sql."      ,TRIM(TRB.V2_DRW_NO) AS DRW ";
		$sql = $sql."      ,TRIM(TRB.V2_PROD_NM) AS PROD ";
		$sql = $sql." FROM T_TR_TRBL TRB ";
		$sql = $sql."     ,V_FL_CUST_INFO CT ";
		$sql = $sql."     ,V_FL_FLAW_INFO KB ";
		$sql = $sql."WHERE TRB.C_TARGET_SECTION_KBN = '".$paPara[1]."' ";
		$sql = $sql."  AND (SUBSTR(TRB.C_INCIDENT_CD,0,1) = 'G' ";
		$sql = $sql."   OR TRB.C_INCIDENT_CD IN ('S7514','S1132','S1108','S1108','S1171')) ";
		$sql = $sql."  AND TRB.C_INCIDENT_CD IS NOT NULL ";
		$sql = $sql."  AND TRB.N_INCIDENT_YMD >= ".$iDateDF." ";
		$sql = $sql."  AND TRB.N_INCIDENT_YMD <= ".$iDateDT." ";
		$sql = $sql."  AND TRIM(TRB.C_INCIDENT_CD) = CT.C_CUST_CD(+) ";
		$sql = $sql."  AND KB.C_KBN_CD = '085' ";
		$sql = $sql."  AND TRIM(TRB.C_FLAW_KBN1) = KB.C_KBN_DETAIL_CD(+) ";
		$sql = $sql."ORDER BY CD,NO ";
		
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);
		
		$iRow = 0;
		while (oci_fetch($stmt)) {
			$aRes[$iRow][1] = oci_result($stmt,'CD');
			$aRes[$iRow][2] = $module_cmn->fChangUTF8(oci_result($stmt, 'NM'));
			$aRes[$iRow][3] = oci_result($stmt,'NO');
			$aRes[$iRow][4] = $module_cmn->fChangUTF8(oci_result($stmt, 'DET'));
			$aRes[$iRow][5] = $module_cmn->fChangUTF8(oci_result($stmt, 'DRW'));
			$aRes[$iRow][6] = $module_cmn->fChangUTF8(oci_result($stmt, 'PROD'));
			$iRow = $iRow + 1;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}
	
	//品質評価集計表データの履歴適用
	//引数	$paPara		パラメータ
	public function fUpdHyokaRireki($paPara){

		require_once("module_common.php");
		$module_cmn = new module_common;

		$aRes = array();
		$aRes[0][0] = 0;

		//対象範囲
/* 		if($paPara[2]-(floor($paPara[2]/100)*100)>= 7){
			$iDateF = (floor($paPara[2]/100)*100)+7;
		}else{
			$iDateF = ((floor($paPara[2]/100)-1)*100)+7;
		}
		if($iDateF < 201707){
			$iDateF  = 201707;
		} */
		$iDateF  = 201807;
		$iDateT = $paPara[2];
		$iDateDF = ($iDateF*100)+1;
		$iDateDT = $paPara[2].date("d",mktime(0, 0, 0, substr($paPara[2],4,2)+1, 0, substr($paPara[2],0,4)));
		
		//Oracleへの接続の確立
		//OCILogon(ユーザ名,パスワード,データベース名)
		$conn = oci_connect($this->gUserID, $this->gPass, $this->gDB);
		if (!$conn) {
			$e = oci_error();   // oci_connect のエラーの場合、ハンドルを渡さない
			session_destroy();
			die("データベースに接続できません");
			$aRes[0][0] = -1;
			return $aRes;
		}
		$strMsg = "";

		//データ検索
		$sql = "";
		$sql = $sql."SELECT SUBSTR(TO_CHAR(検収日_YMD),0,6) || TRIM(JG.手配先_CD) AS KEY ";
		$sql = $sql."      ,COUNT(*) AS QTY ";
		$sql = $sql." FROM J_外注受入検収@NF.US.ORACLE.COM@NF JG ";
		$sql = $sql."WHERE JG.管理部署 = 1 ";
		$sql = $sql."  AND JG.削除日_YMD = 0 ";
		$sql = $sql."  AND JG.検収日_YMD BETWEEN '".$iDateDF."' AND '".$iDateDT."' ";
		$sql = $sql."GROUP BY JG.手配先_CD,SUBSTR(TO_CHAR(検収日_YMD),0,6) ";
		$sql = $sql."UNION ";
		$sql = $sql."SELECT SUBSTR(TO_CHAR(T1.N_KENSHU_YMD),0,6) || TRIM(T2.C_SHIIRE_CD) AS KEY ";
		$sql = $sql."      ,COUNT(*) AS QTY ";
		$sql = $sql."FROM PAPS.T_TR_UKEIRE_D T1 ";
		$sql = $sql."    ,PAPS.T_TR_CHUMON T2 ";
		$sql = $sql."WHERE T1.C_CHUMON_NO = T2.C_CHUMON_NO ";
		$sql = $sql."  AND T1.C_CHUMON_EDA_NO = T2.C_CHUMON_EDA_NO ";
		$sql = $sql."  AND T1.N_DEL_FLG = 0 ";
		$sql = $sql."  AND T2.N_DEL_FLG = 0 ";
		//B コネクタ
		$sql = $sql."  AND T2.V2_SHUKEI_BUMON_CD = 'B' ";
		//検索条件(注文区分) 外注仕入れ
		$sql = $sql."  AND T2.V2_CHUMON_KBN = '30' ";
		//検索条件(検収日)
		$sql = $sql."  AND T1.N_KENSHU_YMD BETWEEN ".$iDateDF." AND ".$iDateDT." ";
		//検索条件(取引先ｺｰﾄﾞ) (有)クラール,(株)ハイライト,(株)カンドリ工業のみ
		$sql = $sql."  AND T2.C_SHIIRE_CD IN ('S7514','S1132','S1108') ";
		$sql = $sql."GROUP BY T2.C_SHIIRE_CD ,SUBSTR(TO_CHAR(T1.N_KENSHU_YMD),0,6) ";
		$sql = $sql."ORDER BY KEY ";
		
		//SQLをSJISに変換(DB)
		$sql = $module_cmn->fChangSJIS_SQL($sql);
		//SQLの実行
		$stmt = oci_parse($conn,$sql);
		oci_execute($stmt,OCI_DEFAULT);
		
		$iRow = 0;
		while (oci_fetch($stmt)) {
			$aRes[1][$iRow] = oci_result($stmt, 'KEY');
			$aRes[2][$iRow] = oci_result($stmt, 'QTY');
			$iRow = $iRow + 1;
		}

		oci_free_statement($stmt);
		oci_close($conn);

		return $aRes;
	}
//2019/09/20 AD END T.FUJITA
}
?>