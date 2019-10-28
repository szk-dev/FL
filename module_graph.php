<?php
//****************************************************************************
//プログラム名：共通関数用モジュール郡(グラフ描画部)
//プログラムID：module_graph
//作成者　　　：㈱鈴木　久米
//作成日　　　：2012/08/20
//履歴　　　　：
//
//
//****************************************************************************
class module_graph{

	//コンストラクタ
	function module_graph(){

	}

	//不具合統計資料(不具合区分統計)
	function fFLK0050_1($aPara){

		/* CAT:Pie charts */
		/* pChart library inclusions */

		include("./pChart/class/pData.class.php");
		include("./pChart/class/pDraw.class.php");
		include("./pChart/class/pPie.class.php");
		include("./pChart/class/pImage.class.php");


		$i = 0;
		$aData = array();
		$aHead = array();
		while($i < count($aPara)){
			$aData[$i] = $aPara[$i][1];
			$aHead[$i] = $aPara[$i][0];
			$i++;

		}



		/* Create and populate the pData object */
		$MyData = new pData();
		//$MyData->addPoints(array(40,30,20),"ScoreA");
		$MyData->addPoints($aData,"ScoreA");


		$MyData->setSerieDescription("ScoreA","Application A");

		/* Define the absissa serie */
		//$MyData->addPoints(array("A","B","C"),"Labels");
		$MyData->addPoints($aHead,"Labels");

		$MyData->setAbscissa("Labels");

		/* Create the pChart object */
		$myPicture = new pImage(480,360,$MyData,TRUE);

		/* Set the default font properties */
		$myPicture->setFontProperties(array("FontName"=>"d:/php5/font/takao-fonts/TakaoGothic.ttf","FontSize"=>10,"R"=>80,"G"=>80,"B"=>80));


		/* Write the picture title */
		$myPicture->setFontProperties(array("FontName"=>"d:/php5/font/takao-fonts/TakaoGothic.ttf","FontSize"=>16));
		$myPicture->drawText(150,20,"部門別不具合報告状況",array("R"=>0,"G"=>0,"B"=>0));


		/* Create the pPie object */
		$PieChart = new pPie($myPicture,$MyData);

		/* Enable shadow computing */
		$myPicture->setShadow(TRUE,array("X"=>3,"Y"=>3,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

		/* Draw a splitted pie chart */
		$PieChart->draw3DPie(240,180,array("Radius"=>200,"DataGapAngle"=>12,"DataGapRadius"=>10,"Border"=>TRUE));

		/* Write the legend box */
		$myPicture->setFontProperties(array("FontName"=>"d:/php5/font/takao-fonts/TakaoGothic.ttf","FontSize"=>14,"R"=>0,"G"=>0,"B"=>0));
		$PieChart->drawPieLegend(120,320,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

		/* Render the picture (choose the best way) */
		$myPicture->autoOutput("pictures/example.draw3DPie.transparent.png");






	}

}


?>