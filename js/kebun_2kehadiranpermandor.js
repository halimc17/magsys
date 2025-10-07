// JavaScript Document

function tampilhilang(idnya){
//    showById(idnya);
}

function mandor()
{
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	param='&kebun='+kebun;
	tujuan='kebun_slave_getmandor';
	post_response_text(tujuan+'.php?proses=divisi', param, respon);
//	alert(param);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    pisah=con.responseText.split('###');
					document.getElementById('divisi').innerHTML=pisah[0];
					document.getElementById('mandor').innerHTML=pisah[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getMandor()
{
	kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
	divisi=document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
	param='&kebun='+kebun+'&divisi='+divisi;
	tujuan='kebun_slave_getmandor';
	post_response_text(tujuan+'.php?proses=getMandor', param, respon);
//	alert(param);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    document.getElementById('mandor').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
//function getAfd()
//{
//	kdOrg=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
////	kdUnit=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
//	param='&kdOrg='+kdOrg;
//	tujuan='kebun_slave_2pemeliharaan';
//	post_response_text(tujuan+'.php?proses=getAfdAll', param, respon);
////	alert(tujuan+'.php?proses=getAfd'+param);
//	function respon() {
//        if (con.readyState == 4) {
//            if (con.status == 200) {
//                busy_off();
//                if (!isSaveResponse(con.responseText)) {
//                    alert('ERROR TRANSACTION,\n' + con.responseText);
//                } else {
//                    // Success Response
//						document.getElementById('kdAfd').innerHTML=con.responseText;
//                }
//            } else {
//                busy_off();
//                error_catch(con.status);
//            }
//        }
//    }
//    //
//  //  alert(fileTarget+'.php?proses=preview', param, respon);
//}
//
function Clear1()
{
	document.getElementById('kebun').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tanggal2').value='';
	document.getElementById('printContainer').innerHTML='';
	mandor();
}
//
//function viewDetail(notransaksi,ev)
//{
//   param='notransaksi='+notransaksi;
//   tujuan='kebun_slave_2pemeliharaandetail.php'+"?"+param;  
//   width='400';
//   height='200';
//  
//   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
//   showDialog1('Detail Pemeliharaan '+notransaksi,content,width,height,ev); 
//	
//}
//
//function viewDetail2(notransaksi,kdOrg,tanggal,ev)
//{
//   param='notransaksi='+notransaksi+'&kdOrg='+kdOrg+'&tanggal='+tanggal;
//   tujuan='kebun_slave_2pemeliharaanbarang.php'+"?"+param;  
//   width='400';
//   height='200';
//  
//   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
//   showDialog1('Detail Pemeliharaan '+notransaksi,content,width,height,ev); 
//	
//}
//
//function detailExcel(ev,tujuan)
//{
//    width='200';
//   height='100';
////   alert(tujuan);
//   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
//   showDialog1('Detail Pemeliharaan Excel',content,width,height,ev); 
//}