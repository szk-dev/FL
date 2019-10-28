
<?php
	//****************************************************************************
	//プログラム名：リンク先用ログイン画面
	//プログラムID：F_CMN0010
	//作成者　　　：㈱鈴木　久米
	//作成日　　　：2017/09/14
	//履歴　　　　：
	//
	//
	//****************************************************************************

	
	session_start();

	//echo session_id();

	//ログイン失敗後
	if(isset($_GET['err']) == true && $_GET['err'] == 1 ){
		$errmsg = "※ログインＩＤまたはパスワードが不正です";
	}else{
		$errmsg = "";
	}

	//ログアウト後
	if(isset($_GET['out']) == true && $_GET['out'] == 1 ){
		// セッション変数を全て解除する
		$_SESSION = array();
		//セッションクッキーを削除
		if(isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		// 最終的に、セッションを破壊する
		session_destroy();
	}

	//引数取得
	$strPage = $_GET['page'];
	$strMode = $_GET['mode'];
	$strRrcno = $_GET['rrcno'];
	
//	list($domain,$uid) = split("[\]",$_SERVER['AUTH_USER']);

//echo "domain=".$domain."<br>";
//echo "uid=".$uid;

?>
<html>
<head>
<title>品質管理システム</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" >
<LINK REL="SHORTCUT ICON" HREF="gif/favicon.ico">
<LINK Type="text/css" Rel="stylesheet" Href="./css/common.css">
<script language="JavaScript">
	/* IEを使用しているかチェック */
	function fBrowserCheck(){
		if (!window.ActiveXObject){
			if(!document.documentMode){
				alert("IE以外のブラウザは使用しないで下さい");
			}
		}else{
			document.form.id.focus();
		}
	}
</script>

</head>

<body onload="fBrowserCheck()">
<br><br><br><br><br><br><br><br>

<center>
<form name="form" method="post" action="loginCheck.php?page=<?php echo $strPage ?>&rrcno=<?php echo $strRrcno ?>" autocomplete="off">

	<TABLE borderColor="#999999" cellSpacing="0" cellPadding="0" rules="all" border="2">
		<TR>
			<TD class="textboxdisp" rowSpan="2" height="256"  width="256">
				<img src="./gif/topk.png" id="PAPS1"  alt="PAPS1" border="0" />
			</TD>
		</TR>
		<TR>
			<TD vAlign="bottom" align="right" class="textboxdisp" style="border-top:2px solid gray">
				<TABLE cellPadding="2" border="0">
					<TR class="textboxdisp">
						<TD class="textboxdisp" align="center" colspan="2">
						<IMG alt="株式会社鈴木" src="./gif/suzukiani2.gif" border="0">
						</TD>
					</TR>
					<TR class="textboxdisp" >
						<TD class="textboxdisp" vAlign="bottom" align="left" colspan="2"><FONT color="#333333">ユーザーＩＤ</FONT><BR>
							<input type="text" name="id" size="17" maxlength="5" style="ime-mode: disabled;" >
						</TD>
					</TR>
					<TR>
						<TD class="textboxdisp" vAlign="bottom" height="53" colspan="2"><FONT color="#333333">パスワード</FONT><BR>
							<input type="password" name="pass" size="19" maxlength="30">
						</TD>
					</TR>
					<TR>
						<TD class="textboxdisp" align="right" colspan="2"><input type="submit" name="login" value="ログイン" ></TD>
					</TR>
					<tr>
						<td class="textboxdisp" align="left">
							<font color="#000066" size="3">
								<B>品質管理システム</B>
							</font>
						</td>
						<td class="textboxdisp" align="right"><font color="#009900" size="2">Version
								1.0.0
								<br>
								for PHP
							</font>
						</td>
					</tr>
				</TABLE>
			</TD>
		</TR>
	</TABLE>

</form>

<br>
<center>
<font color="red"><?php echo $errmsg; ?>
</font>
</center>

</center>
</body>
</html>
