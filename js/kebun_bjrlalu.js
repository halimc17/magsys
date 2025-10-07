maxf=0
sekarang=1;
function saveAll(maxRow){
	maxf=maxRow;
	loopsave(1,maxRow);
}

function batal(){
	//document.getElementById('per').value='';
    document.getElementById('printContainer').innerHTML='';	
}

function simpan(maxRow){
    unit=trim(document.getElementById('unit').value);
    per=document.getElementById('per').value;

	param='proses=simpan'+'&unit='+unit+'&per='+per;
	tujuan='kebun_slave_save_bjrlalu.php';
	post_response_text(tujuan, param, respog);	
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					// document.getElementById('container').innerHTML=con.responseText;
					//saveAll(maxRow);
					currRow=1;
					loopsave(currRow,maxRow);	
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function loopsave(currRow,maxRow){
    unit=trim(document.getElementById('unit').value);
    per=document.getElementById('per').value;
    bjrset=document.getElementById('bjrset'+currRow).value;
    kodeblok=trim(document.getElementById('kodeblok'+currRow).innerHTML);
    if(per=='' || unit=='' || bjrset=='' || kodeblok==''){
		alert("Data tidak lengkap");
		return;
    }else{  
        param='unit='+unit+'&per='+per+'&bjrset='+bjrset;
        param+="&proses=savedata"+'&kodeblok='+kodeblok;
		tujuan = 'kebun_slave_save_bjrlalu.php';
		post_response_text(tujuan, param, respog);
		document.getElementById('row'+currRow).style.backgroundColor='cyan';
		//lockScreen('wait');
    }

    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
					document.getElementById('row'+currRow).style.backgroundColor='red';
					unlockScreen();
                }else{
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
						//document.location.reload();
						//alert('Done');
						calcrestan();
                    }else{
						loopsave(currRow,maxRow);
                    }
                }
            }else{
                busy_off();
                error_catch(con.status);
                //unlockScreen();
            }
        }
    }
}

function calcrestan(){
    unit=trim(document.getElementById('unit').value);
    per=document.getElementById('per').value;
	param='proses=calcrestan'+'&unit='+unit+'&per='+per;
	tujuan='kebun_slave_save_bjrlalu.php';
	post_response_text(tujuan, param, respog);	

	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					alert('Done');
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}
