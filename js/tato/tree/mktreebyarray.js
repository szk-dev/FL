/* YAHOO.tato.tree @see http://allabout.co.jp/internet/javascript/closeup/CU20060515A/index.htm 
   This script used Yahoo! User Interface Library http://developer.yahoo.com/yui/ */
if(!YAHOO.tato)YAHOO.namespace('tato');
if(!YAHOO.tato.tree)YAHOO.tato.tree = function(id) {
  this.tree = new YAHOO.widget.TreeView(id);
}

//Tree描画  by Array
YAHOO.tato.tree.prototype.mkTreeByArray = function (treeData,treeNode){
    if(!treeNode)treeNode = this.tree.getRoot(); 
    for(var i in treeData){
      if(!(treeData[i][0]=="_open"||treeData[i][0]=="_close")){
        var tmpNode = new YAHOO.widget.TextNode("" + treeData[i][0],treeNode, false);
        if(typeof treeData[i][1] == "string"){ tmpNode.href= treeData[i][1]; tmpNode.target= "sample"; }
        if(typeof treeData[i][1] == "object"){
          if(treeData[i][1][0]=="_open")tmpNode.expand();
          this.mkTreeByArray(treeData[i][1],tmpNode); 
        }
      }
    }
    this.tree.draw();
  }
