
<?php
	//共通モジュールの読み込み
	include("module_sel.php");
	$module_sel = new module_sel;
	
	//2019/05/13 ADD START T.FUJITA
	//ログイン処理
	//$module_sel->flogin($_POST['id'],$_POST['pass'],$_GET['page'],$_GET['rrcno']);
	if(isset($_GET['rrcseq'])){
		$module_sel->flogin($_POST['id'],$_POST['pass'],$_GET['page'],$_GET['rrcno'],$_GET['rrcseq']);
	}else{
		$module_sel->flogin($_POST['id'],$_POST['pass'],$_GET['page'],$_GET['rrcno']);
	}
	//2019/05/13 ADD END T.FUJITA

?>
