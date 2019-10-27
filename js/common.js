
/* 画面読み込み時処理 */
function fLoadDisplay(){
	//フレーム構成が不正なら初期画面へ遷移
	if(!top.head){
		location.href = "http://" + location.host +"/FL/";
	}

}
/* 画面読み込み時処理(フレーム分割用) */
function fLoadDisplayP(){
	//フレーム構成が不正なら初期画面へ遷移
	if(!top.head){
		parent.location.href = "http://" + location.host +"/FL/";
	}

}



/* システム日付をセット */
function fGetSysDate(){
	now=new Date();
	yymmdd=now.getYear().toString().substr(0,4);
		if(now.getMonth()<9){yymmdd+="0";}
	yymmdd+=(now.getMonth()+1).toString();
		if(now.getDate()<10){yymmdd+="0";}
	yymmdd+=now.getDate().toString();
	return yymmdd;
}


/* カレンダー画面表示  */
function fCalOpen(strObj){
	var wPopup;
	//子画面表示用位置パラメータ
	w = 250; // 横幅
	h = 190; // 縦幅
	x = (screen.width  - w) / 2;
	y = (screen.height - h) / 2;

	wPopup = window.open("parts/calendar.php?obj=" + strObj,"child","width="+w+",height="+h+",left="+x+",top="+y+",toolbar=no,resizable=no,location=no,menubar=no");
	//showModalDialog("parts/calendar.php",window,"status:false;dialogWidth:220px;dialogHeight:160px");
}

/* 検索子画面 */
function fOpenSearch(strTable,strSetPara1,strSetPara2,strSetPara3,strSetPara4,strSetPara5,strSetPara6,strSetPara7,strKbn){
	var objChild;
	//子画面表示用位置パラメータ
	if(strTable == 'F_MSK0010'){
		//品目マスタの場合はサイズ変更
		w = 1000; // 横幅
		h = 500; // 縦幅
	}else{
		//品目マスタ以外
		//w = 600; // 横幅
		w = 630; // 横幅
		h = 500; // 縦幅
	}

	x = (screen.width  - w) / 2;
	y = (screen.height - h) / 2;

	//objChild = window.open(strTable + ".php?para1=" + strSetPara1 + "&para2=" + strSetPara2 + "&para3=" + strSetPara3 + "&para4=" + strSetPara4 + "&para5=" + strSetPara5 + "&para6=" + strSetPara6 + "&para7=" + strSetPara7 + "&para8=" + strKbn ,"search","menubar=no,width="+w+",height="+h+",left="+x+",top="+y+",scrollbars=yes");
	objChild = window.showModalDialog(strTable + ".php?para1=" + strSetPara1 + "&para2=" + strSetPara2 + "&para3=" + strSetPara3 + "&para4=" + strSetPara4 + "&para5=" + strSetPara5 + "&para6=" + strSetPara6 + "&para7=" + strSetPara7 + "&para8=" + strKbn ,window,"status:false;dialogWidth:"+w+"px;dialogHeight:"+h+"px");


}

/* 検索子画面 (配列用)*/
function fOpenSearchArray(strTable,strSetPara1,strSetPara2,strSetPara3,strSetPara4,strKbn,obj){
	var objChild;
	//子画面表示用位置パラメータ
	//w = 600; // 横幅
	w = 630; // 横幅
	h = 500; // 縦幅
	x = (screen.width  - w) / 2;
	y = (screen.height - h) / 2;

	var i = 0;
	//要素数
	var intHit = 0;
	//var objBtn = document.getElementsByName("btnToriC");
	var objBtn = document.getElementsByName("btnIraiC");



	//押されたボタンの要素数を取得する
	for ( i=0; i < objBtn.length; i++)
	{
		//選択されたボタンが同じだった場合、要素番号をセット
		if ( obj == objBtn[i]){
			intHit = i;
			break;
		}
	}
	
	//検索子画面のオープン
//	objChild = window.open(strTable + ".php?para1=" + strSetPara1 + "&para2=" + strSetPara2 + "&para3=" + strSetPara3 + "&para4=" + strKbn + "&para5=" + intHit ,"search","menubar=no,width="+w+",height="+h+",left="+x+",top="+y+",scrollbars=yes");
//	objChild = window.showModalDialog(strTable + ".php?para1=" + strSetPara1 + "&para2=" + strSetPara2 + "&para3=" + strSetPara3 + "&para4=" + strSetPara4 + "&para5=" + strKbn + "&para6=" + obj,window,"status:false;dialogWidth:"+w+"px;dialogHeight:"+h+"px");
	objChild = window.showModalDialog(strTable + ".php?para1=" + strSetPara1 + "&para2=" + strSetPara2 + "&para3=" + strSetPara3 + "&para4=" + strSetPara4 + "&para5=" + intHit  + "&para8=" + strKbn,window,"status:false;dialogWidth:"+w+"px;dialogHeight:"+h+"px");

}

/* カンマ削除 */
function removeComma(value) {
   	return value.split(",").join("")
}


/* カンマ編集 */
function faddComma(value){
    var i;
    var strValue;
    strValue = String(value);
    for(i = 0; i < strValue.length/3; i++){
        strValue = strValue.replace(/^([+-]?\d+)(\d\d\d)/,"$1,$2");
    }
    return strValue;
}
//数値チェック

function fCheckNumberFormat(obj,msg,nullcheck,numcheck,doubcheck,zerocheck,seisuketa,shosuketa){

	var vObj;
	vObj = document.form.elements[obj].value;
	//カンマを置換
	vObj = vObj.replace(/,/g,"");
	//必須チェック
	if(nullcheck)
	{
		if(vObj=="")
		{
			alert(msg+"の入力は必須です");
			document.form.elements[obj].focus();
			return false;
		}
	}


	//ZERO許可チェック
	if(zerocheck)
	{

		if(eval(vObj)=="0")
		{
			alert(msg+"に0は入力できません");
			document.form.elements[obj].focus();
			return false;
		}
	}

	if(vObj!="")
	{

		if(numcheck)
		{
			if(escape(vObj).match(/[^0-9]+/)){
				document.form.elements[obj].focus();
				alert(msg + "に数字以外が入力されています。");
				return false;
			}
		}
		if(doubcheck)
		{
			sepstr = vObj.split(".");
			if(sepstr.length > 2) {
				document.form.elements[obj].focus();
				alert(msg + "にコロンが２つ以上入力されています。");
				return false;
			}
			if(sepstr[0].length > seisuketa) {
				document.form.elements[obj].focus();
				alert(msg + "の小数点以上桁数は" + seisuketa + "以下で入力してください。");
				return false;
			}

			if(escape(sepstr[0]).match(/[^0-9]+/)){
				document.form.elements[obj].focus();
				alert(msg + "に数字以外が入力されています。");
				return false;
			}
			if(sepstr.length==2) {
				if(sepstr[1].length > shosuketa) {
					document.form.elements[obj].focus();
					alert(msg + "の小数点以下桁数は" + shosuketa + "以下で入力してください。");
					return false;
				}
				if(escape(sepstr[1]).match(/[^0-9]+/)){
					document.form.elements[obj].focus();
					alert(msg + "に数字以外が入力されています。");
					return false;
				}
			}
		}
	}
	return true;
}

//数値チェック(DOM用)
function fCheckNumberFormatDOM(obj,i,msg,nullcheck,numcheck,doubcheck,zerocheck,seisuketa,shosuketa){

	//必須チェック
	if(nullcheck)
	{
		if(document.getElementsByName(obj)[i].value=="")
		{
			alert(msg+"の入力は必須です");

			return false;
		}
	}
	//ZERO許可チェック
	if(zerocheck)
	{

		if(document.getElementsByName(obj)[i].value=="0")
		{
			alert(msg+"に0は入力できません");

			return false;
		}
	}


	if(document.getElementsByName(obj)[i].value != "")
	{

		if(numcheck)
		{

			if(isNaN(removeComma(document.getElementsByName(obj)[i].value))){

				alert(msg + "に数字以外が入力されています。");
				return false;
			}
		}
		if(doubcheck)
		{
			sepstr = removeComma(document.getElementsByName(obj)[i].value);
			sepstr = sepstr.split(".");
			if(sepstr.length > 2) {

				alert(msg + "にコロンが２つ以上入力されています。");
				return false;
			}
			if(sepstr[0].length > seisuketa) {
				alert(msg + "の小数点以上桁数は" + seisuketa + "以下で入力してください。");
				return false;
			}
			if(escape(sepstr[0]).match(/[^0-9]+/)){

				alert(msg + "に数字以外が入力されています。");
				return false;
			}
			if(sepstr.length==2) {
				if(sepstr[1].length > shosuketa) {

					alert(msg + "の小数点以下桁数は" + shosuketa + "以下で入力してください。");
					return false;
				}
				if(escape(sepstr[1]).match(/[^0-9]+/)){

					alert(msg + "に数字以外が入力されています。");
					return false;
				}
			}
		}
	}
	return true;
}


//必須チェック関数
//引数…strObjNm(オブジェクト名),strObjTl(項目タイトル)
function fNCheck(strObj,strObjTl){
	//クラス名を取得
	//var obj = document.getElementsByName(strObjNm)[0];


	var msg = "";
	//alert(strObjNm);
	//alert(obj.className);

	if(document.form.elements[strObj].value == "" || document.form.elements[strObj].value == "-1" ){
		msg = strObjTl + "は必須項目です";
		document.form.elements[strObj].focus();
		alert(msg);
		return false;
	}


	return true;
}

//日付整合性チェック
function fCheckDateMatch(startDate,endDate,msg1,msg2){
	var strStartDate;
	var strEndDate;

	//両方値が入っていたら
	if(document.form.elements[startDate].value != "" && document.form.elements[endDate].value != ""){

		//スラッシュを除去
		strStartDate = document.form.elements[startDate].value.split("/").join("");
		strEndDate   = document.form.elements[endDate].value.split("/").join("");
		//終了日が開始日より前の場合はエラー
		if(strStartDate > strEndDate){
			document.form.elements[endDate].focus();
			alert(msg2 + "は" + msg1 + "以降の日付を指定して下さい。");
			return false;
		}
	}
	return true;
}


//翌月の15日を取得
function fncGetNext15(year, month, day, addMonths) {
    month += addMonths;
    var endDay = fGetMonthEndDay(year, month);//ここで、前述した月末日を求める関数を使用します
    if(day > endDay) day = endDay;
    var dt = new Date(year, month - 1, day);
    return dt;
}

//
//年月を指定して月末日を求める関数
//year 年
//month 月
//
function fGetMonthEndDay(year, month) {
    //日付を0にすると前月の末日を指定したことになります
    //指定月の翌月の0日を取得して末日を求めます
    //そのため、ここでは month - 1 は行いません
    var dt = new Date(year, month, 0);
    return dt.getDate();
}

//空白の除去(半角と全角)
//------------------------------------------------------------------
//[解説] 指定文字列から前後の空白文字列除去
//[関数] SpaceTrim( nowstr )
//[引数] 対象文字列
//[戻り値] 前後の空白文字列が除去された文字列
//------------------------------------------------------------------
function fSpaceTrim(nowstr) {
	var i = 0;
	var j = 0;
	var checkFlg = 0;	// 複合文字列かどうかの判定のフラグ
	var codecnt = 0;	// 複合文字列かどうかの判定に使用
	var setFlg = 0;		// 空白文字かどうかのフラグ
	var EscapeChar;		// エスケープ化
	var Code16Char;		// エスケープ変換したものの二桁目を１６進数変換
	var chkchar;		// 検査する１文字（何らかの文字列かどうかの判定時）
	var workstr = "";	// ワークスペースとしての文字列
	var setchar;		// 検査する１文字（空白文字かどうかの判定時）
	var convstr = "";	// 戻り値

	for(i=0;i<nowstr.length;i++) {
		chkchar = nowstr.charAt(i);// 1文字ずつ抜き出して検査する
		EscapeChar = escape(nowstr.charAt(i));// 文字列をエスケープ変換
		//* ￥ｒ　または　￥ｎ　だったら
		if (EscapeChar=="%0D" || EscapeChar=="%0A") {
			setFlg = 2;
			convstr = convstr + chkchar;
			workstr = workstr + chkchar;
			continue;
		}
		//▼２バイト文字
		//* Unicodeエスケープ　かつ　｡　や　ﾟ　の外側の文字だった場合
		if (EscapeChar.charAt(1) == "u" || EscapeChar.charAt(1) == "U") {
			if (nowstr.charAt(i) >= "｡" && nowstr.charAt(i) <= "ﾟ") {
				setFlg = 1;
			}
		} else {
			Code16Char = parseInt(EscapeChar.charAt(1),16);// エスケープ変換したものの二桁目を16進数変換
			if ((Code16Char == 8) || (Code16Char == 9) || (Code16Char == 14) || (Code16Char == 15)) {
				checkFlg = 0;// %があるかどうかフラグ
				codecnt = 0;// エスケープ文字の桁数
				for(j=0;j<EscapeChar.length;j++) {
					if (EscapeChar.charAt(j) == "%") {// エスケープの一文字目、%だったら
						codecnt = 0;
						checkFlg++;
					} else {
						codecnt++;
					}
				}
				if (checkFlg == 1 && codecnt <= 2) {// %xx以下の桁数だったら
					EscapeChar = EscapeChar + escape(nowstr.charAt(i+1));// 次の文字をエスケープしたものをコンバインして二バイト文字として扱う
					i++;// これ以降のチェックは次の文字番号として扱う　二バイト文字だから
				}
			}
		}
		//* 取得文字が?ではない　しかし　エスケープしたものをアンエスケースしたら?に化けた場合の対処
		if (chkchar != "?" && unescape(EscapeChar) == "?") {
			setchar = chkchar;
		} else {
			setchar = unescape(EscapeChar);
		}

		//* setFlgが0のまま（上記の検査で何も引っかからなかった場合）
		if (setFlg == 0) {
			if (!(setchar == " " || setchar == "　")) {
				setFlg = 2;
			}
		} else {
			if (setchar == " " || setchar == "　") {
				setFlg = 1;// 空白文字として扱う
			} else {
				setFlg = 2;
			}
		}
		//* setFlgによる分岐　setFlgが2以外だったら戻り値には含めない
		if (setFlg > 0) {
			if (setFlg == 2) {
				workstr = workstr + setchar;
				convstr = workstr;
			} else {
				workstr = workstr + setchar;
			}
		}
	}
	return(convstr);
}

