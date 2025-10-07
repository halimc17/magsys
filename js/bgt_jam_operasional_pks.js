function simpanpks()
{
	
	tahunbudget=document.getElementById('tahunbudget').value;
	kodeorg=document.getElementById('kodeorg');
	kodeorg=kodeorg.options[kodeorg.selectedIndex].value;

	jamo=document.getElementById('jamo').value;	
	jamo01=document.getElementById('jamo01').value;	
	jamo02=document.getElementById('jamo02').value;	
	jamo03=document.getElementById('jamo03').value;	
	jamo04=document.getElementById('jamo04').value;	
	jamo05=document.getElementById('jamo05').value;	
	jamo06=document.getElementById('jamo06').value;	
	jamo07=document.getElementById('jamo07').value;	
	jamo08=document.getElementById('jamo08').value;	
	jamo09=document.getElementById('jamo09').value;	
	jamo10=document.getElementById('jamo10').value;	
	jamo11=document.getElementById('jamo11').value;	
	jamo12=document.getElementById('jamo12').value;	
	jamb=document.getElementById('jamb').value;
	jamb01=document.getElementById('jamb01').value;
	jamb02=document.getElementById('jamb02').value;
	jamb03=document.getElementById('jamb03').value;
	jamb04=document.getElementById('jamb04').value;
	jamb05=document.getElementById('jamb05').value;
	jamb06=document.getElementById('jamb06').value;
	jamb07=document.getElementById('jamb07').value;
	jamb08=document.getElementById('jamb08').value;
	jamb09=document.getElementById('jamb09').value;
	jamb10=document.getElementById('jamb10').value;
	jamb11=document.getElementById('jamb11').value;
	jamb12=document.getElementById('jamb12').value;
	met=document.getElementById('method').value;
	
	oldtahunbudget=document.getElementById('oldtahunbudget').value;
	oldkodeorg=document.getElementById('oldkodeorg').value;
	
	if(trim(tahunbudget)=='')
	{
		alert('Tahun masih kosong');
		return;
		//document.getElementById('thnbudget').focus();
	}	
	else if(tahunbudget.length<4) 
    {
        alert('Karakter Tahun Budget Tidak Tepat');
        return;
    }
	else if(trim(kodeorg)=='')
	{
		alert('Kode Afdeling masi kosong');
		return;
	}
	
	
	else if(trim(jamo)=='')
	{
		alert('Jam Olah/Tahun Masih Kosong');
		return;
	}
	else if(trim(jamb)=='')
	{
		alert('Jam Breakdown/Tahun Masih Kosong');
		return;
	}
	else
	{
		tahunbudget=trim(tahunbudget);
		kodeorg=trim(kodeorg);
		jamo=trim(jamo);
		jamo01=trim(jamo01);
		jamo02=trim(jamo02);
		jamo03=trim(jamo03);
		jamo04=trim(jamo04);
		jamo05=trim(jamo05);
		jamo06=trim(jamo06);
		jamo07=trim(jamo07);
		jamo08=trim(jamo08);
		jamo09=trim(jamo09);
		jamo10=trim(jamo10);
		jamo11=trim(jamo11);
		jamo12=trim(jamo12);
		jamb=trim(jamb);
		jamb01=trim(jamb01);
		jamb02=trim(jamb02);
		jamb03=trim(jamb03);
		jamb04=trim(jamb04);
		jamb05=trim(jamb05);
		jamb06=trim(jamb06);
		jamb07=trim(jamb07);
		jamb08=trim(jamb08);
		jamb09=trim(jamb09);
		jamb10=trim(jamb10);
		jamb11=trim(jamb11);
		jamb12=trim(jamb12);
	
	param='tahunbudget='+tahunbudget+'&kodeorg='+kodeorg+'&jamo='+jamo+'&jamb='+jamb+'&method='+met;
	param+='&jamo01='+jamo01+'&jamb01='+jamb01;
	param+='&jamo02='+jamo02+'&jamb02='+jamb02;
	param+='&jamo03='+jamo03+'&jamb03='+jamb03;
	param+='&jamo04='+jamo04+'&jamb04='+jamb04;
	param+='&jamo05='+jamo05+'&jamb05='+jamb05;
	param+='&jamo06='+jamo06+'&jamb06='+jamb06;
	param+='&jamo07='+jamo07+'&jamb07='+jamb07;
	param+='&jamo08='+jamo08+'&jamb08='+jamb08;
	param+='&jamo09='+jamo09+'&jamb09='+jamb09;
	param+='&jamo10='+jamo10+'&jamb10='+jamb10;
	param+='&jamo11='+jamo11+'&jamb11='+jamb11;
	param+='&jamo12='+jamo12+'&jamb12='+jamb12;
	param+='&oldtahunbudget='+oldtahunbudget+'&oldkodeorg='+oldkodeorg;
	//alert(param);
	tujuan='bgt_slave_save_jam_operasional_pks.php';
	post_response_text(tujuan, param, respog);		
	}
	function respog()
	{
			  if(con.readyState==4)
			  {
					if (con.status == 200) 
					{
						busy_off();
						if (!isSaveResponse(con.responseText)) 
						{
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}
						else {
							batalpks();						
							loadData();
							//document.getElementById('container').innerHTML=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
			  }	
	 }

}

function loadData () 
{
	param='method=loadData';
	tujuan='bgt_slave_save_jam_operasional_pks.php';
    post_response_text(tujuan, param, respog);
	function respog()
	{
              if(con.readyState==4)
              {
                    if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert('ERROR TRANSACTION,\n' + con.responseText);
                                }
                                else {
                                   // alert(con.responseText);
                                    document.getElementById('containerData').innerHTML=con.responseText;
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}
	 
		

function fillField(tahunbudget,millcode
	,jamolah,jamolah01,jamolah02,jamolah03,jamolah04,jamolah05,jamolah06,jamolah07,jamolah08,jamolah09,jamolah10,jamolah11,jamolah12
	,breakdown,breakdown01,breakdown02,breakdown03,breakdown04,breakdown05,breakdown06,breakdown07,breakdown08,breakdown09,breakdown10,breakdown11,breakdown12)
{
	document.getElementById('tahunbudget').value=tahunbudget;
	document.getElementById('oldtahunbudget').value=tahunbudget;
	document.getElementById('kodeorg').value=millcode;
	document.getElementById('oldkodeorg').value=millcode;
	
	document.getElementById('jamo').value=jamolah;
	document.getElementById('jamo01').value=jamolah01;
	document.getElementById('jamo02').value=jamolah02;
	document.getElementById('jamo03').value=jamolah03;
	document.getElementById('jamo04').value=jamolah04;
	document.getElementById('jamo05').value=jamolah05;
	document.getElementById('jamo06').value=jamolah06;
	document.getElementById('jamo07').value=jamolah07;
	document.getElementById('jamo08').value=jamolah08;
	document.getElementById('jamo09').value=jamolah09;
	document.getElementById('jamo10').value=jamolah10;
	document.getElementById('jamo11').value=jamolah11;
	document.getElementById('jamo12').value=jamolah12;
   // document.getElementById('').disabled=true;
	document.getElementById('jamb').value=breakdown;
	document.getElementById('jamb01').value=breakdown01;
	document.getElementById('jamb02').value=breakdown02;
	document.getElementById('jamb03').value=breakdown03;
	document.getElementById('jamb04').value=breakdown04;
	document.getElementById('jamb05').value=breakdown05;
	document.getElementById('jamb06').value=breakdown06;
	document.getElementById('jamb07').value=breakdown07;
	document.getElementById('jamb08').value=breakdown08;
	document.getElementById('jamb09').value=breakdown09;
	document.getElementById('jamb10').value=breakdown10;
	document.getElementById('jamb11').value=breakdown11;
	document.getElementById('jamb12').value=breakdown12;
	//document.getElementById('method').value='update';
}

function batalpks()
{
    //document.getElementById('').disabled=false;
	document.getElementById('tahunbudget').value='';
	document.getElementById('kodeorg').value='';
	document.getElementById('jamo').value='';
	document.getElementById('jamo01').value='';
	document.getElementById('jamo02').value='';
	document.getElementById('jamo03').value='';
	document.getElementById('jamo04').value='';
	document.getElementById('jamo05').value='';
	document.getElementById('jamo06').value='';
	document.getElementById('jamo07').value='';
	document.getElementById('jamo08').value='';
	document.getElementById('jamo09').value='';
	document.getElementById('jamo10').value='';
	document.getElementById('jamo11').value='';
	document.getElementById('jamo12').value='';
	document.getElementById('jamb').value='';
	document.getElementById('jamb01').value='';
	document.getElementById('jamb02').value='';
	document.getElementById('jamb03').value='';
	document.getElementById('jamb04').value='';
	document.getElementById('jamb05').value='';
	document.getElementById('jamb06').value='';
	document.getElementById('jamb07').value='';
	document.getElementById('jamb08').value='';
	document.getElementById('jamb09').value='';
	document.getElementById('jamb10').value='';
	document.getElementById('jamb11').value='';
	document.getElementById('jamb12').value='';
	document.getElementById('method').value='insert';			
}





function angka (b,ainput)
{
	var goodInput = ainput;
	var evt = (b)?b:window.event;
	var key_code = (document.all)?evt.keyCode:evt.which;
	if (key_code == 0 || key_code == 8) return true;
	if (goodInput.indexOf(String.fromCharCode(key_code)) == -1)
	{
		return false;
	}
	else
	return true;
} 

function calcJam(num){
	if(num==1){
		document.getElementById('jamo').value=
		Number(document.getElementById('jamo01').value)+
		Number(document.getElementById('jamo02').value)+
		Number(document.getElementById('jamo03').value)+
		Number(document.getElementById('jamo04').value)+
		Number(document.getElementById('jamo05').value)+
		Number(document.getElementById('jamo06').value)+
		Number(document.getElementById('jamo07').value)+
		Number(document.getElementById('jamo08').value)+
		Number(document.getElementById('jamo09').value)+
		Number(document.getElementById('jamo10').value)+
		Number(document.getElementById('jamo11').value)+
		Number(document.getElementById('jamo12').value);
	}else{
		document.getElementById('jamb').value=
		Number(document.getElementById('jamb01').value)+
		Number(document.getElementById('jamb02').value)+
		Number(document.getElementById('jamb03').value)+
		Number(document.getElementById('jamb04').value)+
		Number(document.getElementById('jamb05').value)+
		Number(document.getElementById('jamb06').value)+
		Number(document.getElementById('jamb07').value)+
		Number(document.getElementById('jamb08').value)+
		Number(document.getElementById('jamb09').value)+
		Number(document.getElementById('jamb10').value)+
		Number(document.getElementById('jamb11').value)+
		Number(document.getElementById('jamb12').value);
	}
	//alert(Number(document.getElementById('jamo01').value)+Number(document.getElementById('jamo02').value));
} 
