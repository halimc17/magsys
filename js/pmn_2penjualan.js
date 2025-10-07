// apr 03, 2012

function bersih_1(){
    document.getElementById('printContainer1').innerHTML=''; 
}

function zDetailBASPK(ev,tujuan,passParam)
{
	var passP = passParam.split('##');
	var param = "";
	 for(i=0;i<passP.length;i++) {
       // var tmp = document.getElementById(passP[i]);
	   	a=i;
        param += "&"+passP[a]+"="+passP[i+1];
    }
	param+='&proses=getDetailBASPK';
	judul="Detail";
	//alert(param);
	printFile(param,tujuan,judul,ev)
}
