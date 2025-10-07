maxf=0
sekarang=1;
function saveAll(maxRow)
{     

      	 maxf=maxRow;
	    loopsave(1,maxRow);
}


function batal()
{
    document.getElementById('per').value='';	
    document.getElementById('makan').value=0;
    document.getElementById('printContainer').innerHTML='';	
}



function del(maxRow)
{
    
    //jenissave=trim(document.getElementById('jenissave'+currRow).innerHTML);
    //per=document.getElementById('per').value;
    //karyawanidsave=trim(document.getElementById('karyawanidsave'+currRow).innerHTML);
    //jumlahsave=trim(document.getElementById('jumlahsave'+currRow).innerHTML);
    //kdorgsave=trim(document.getElementById('kdorgsave'+currRow).innerHTML);
    
    unit=trim(document.getElementById('unit').value);
    per=document.getElementById('per').value;
    jenis=trim(document.getElementById('jenis').value);
    
	param='proses=del'+'&unit='+unit+'&per='+per+'&jenis='+jenis;
	tujuan='sdm_slave_save_3tunjangan.php';
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
					else 
					{
						// document.getElementById('container').innerHTML=con.responseText;
						//saveAll(maxRow);
                                                currRow=1;
                                            loopsave(currRow,maxRow);	
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
	//alert("Data telah terhapus !!!");	
}




function loopsave(currRow,maxRow)
{
    //alert(currRow);return;
    jenissave=trim(document.getElementById('jenissave'+currRow).innerHTML);
    per=document.getElementById('per').value;
    karyawanidsave=trim(document.getElementById('karyawanidsave'+currRow).innerHTML);
    
    jumlahsave=document.getElementById('jumlahsave'+currRow).value;
    //jumlahsave=trim(document.getElementById('jumlahsave'+currRow).innerHTML);
    kdorgsave=trim(document.getElementById('kdorgsave'+currRow).innerHTML);
    

    if(per=='' || karyawanidsave=='' || jumlahsave=='' || kdorgsave=='')
    {
            alert("Data tidak lengkap");return;
    }	
    else
    {  
        param='jenissave='+jenissave+'&per='+per+'&karyawanidsave='+karyawanidsave+'&jumlahsave='+jumlahsave;
        param+="&proses=savedata"+'&kdorgsave='+kdorgsave;

            //alert(param);
            tujuan = 'sdm_slave_save_3tunjangan.php';
            post_response_text(tujuan, param, respog);
            document.getElementById('row'+currRow).style.backgroundColor='cyan';
            //lockScreen('wait');
    }
    function respog(){
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                        document.getElementById('row'+currRow).style.backgroundColor='red';
                   unlockScreen();
                }
                else {
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow)
                    {
                            alert('Done');
                           // document.location.reload();
                            //document.getElementById('infoDisplay').innerHTML='';
                    }  
                    else
                    {
                            loopsave(currRow,maxRow);
                    }
                }
            }
            else {
                busy_off();
                error_catch(con.status);
               // document.getElementById('lanjut').style.display='';
                //unlockScreen();
            }
        }
    }		
	
}


function getPerhitungan(no)
{
     x=trim(document.getElementById('tanpapengali'+no).innerHTML);
     y=trim(document.getElementById('pengalibawah'+no).value);
     z=x*y;
     document.getElementById('jumlahsave'+no).value=z;
     
}

function hide()
{
    jenis=trim(document.getElementById('jenis').value);
    if(jenis!=46)
    {
        document.getElementById('pengali').value=1;
       document.getElementById('pengali').disabled=true;
    }
    else
    {
       document.getElementById('pengali').disabled=false;
    }
}





function uang()
{
    unit=document.getElementById('unit').value; 
    param='unit='+unit;
    param+='&proses=uang';
    tujuan='sdm_slave_3uangmakan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    document.getElementById('makan').value=con.responseText;   
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

