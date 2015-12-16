/**
 * @author repindra.ginting
 */

function getUsiaHutang()
{
	pt=document.getElementById('pt');
	statuspo=document.getElementById('statuspo');
	gudang  =document.getElementById('gudang');
	tanggalpivot =document.getElementById('tanggalpivot').value;
		ptV		=pt.options[pt.selectedIndex].value;
		gudangV	=gudang.options[gudang.selectedIndex].value;
		statuspoV	=statuspo.options[statuspo.selectedIndex].value;
                
            supkontran=document.getElementById('supkontran').value;

	param='pt='+ptV+'&gudang='+gudangV+'&tanggalpivot='+tanggalpivot+'&statuspo='+statuspoV+'&supkontran='+supkontran;
	tujuan='keu_laporanUsiaHutang.php';
	post_response_text(tujuan, param, respog);
//	alert(tujuan+param);
	
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
						showById('printPanel');
						document.getElementById('container').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}		
}

function fisikKeExcel(ev,tujuan)
{
	pt	=document.getElementById('pt');
	gudang  =document.getElementById('gudang');
	tanggalpivot =document.getElementById('tanggalpivot').value;
		pt		=pt.options[pt.selectedIndex].value;
		gudang	=gudang.options[gudang.selectedIndex].value;
                
        statuspo=document.getElementById('statuspo').value;       
        supkontran=document.getElementById('supkontran').value;        
                
//		periode	=periode.options[periode.selectedIndex].value;
	judul='Report Ms.Excel';	
	param='pt='+pt+'&gudang='+gudang+'&tanggalpivot='+tanggalpivot+'&statuspo='+statuspo+'&supkontran='+supkontran;
	printFile(param,tujuan,judul,ev)	
}

function fisikKePDF(ev,tujuan)
{
	pt		=document.getElementById('pt');
	gudang  =document.getElementById('gudang');
	tanggalpivot =document.getElementById('tanggalpivot').value;
		pt		=pt.options[pt.selectedIndex].value;
		gudang	=gudang.options[gudang.selectedIndex].value;
                 statuspo=document.getElementById('statuspo').value;       
        supkontran=document.getElementById('supkontran').value;        
//		periode	=periode.options[periode.selectedIndex].value;
	judul='Report PDF';	
	param='pt='+pt+'&gudang='+gudang+'&tanggalpivot='+tanggalpivot+'&statuspo='+statuspo+'&supkontran='+supkontran;
	printFile(param,tujuan,judul,ev)	
}

function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}

function lihattagihan(noinvoice,ev)
{
   param='noinvoice='+noinvoice;
   tujuan='keu_slave_laporanusiahutang.php'+"?"+param;  
   width='600';
   height='100';
  
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2('Data Tagihan '+noinvoice,content,width,height,ev); 
	
}


function ambilAnak(pt)
{
	param='pt='+pt;
	tujuan='keu_slave_getUnit.php';
	post_response_text(tujuan, param, respog);
	
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
						document.getElementById('gudang').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
	
}
