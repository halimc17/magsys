function excel(ev,tujuan)
{
    thn = document.getElementById('thnex').value;
    judul='Report Ms.Excel';	
    param = 'method=excel' + '&thn=' + thn;
    printFile(param,tujuan,judul,ev);	
}


function detail(per,divisi,ev)
{
    param='method=detail'+'&per='+per+'&divisi='+divisi;
    title="Data Detail";
     showDialog1(title,"<iframe frameborder=0 style='width:845px;height:395px'"+
    " src='kebun_slave_restanv.php?"+param+"'></iframe>",'850','400',ev);	
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function posting(per,divisi)
{
    param='method=posting'+'&per='+per+'&divisi='+divisi;
    tujuan='kebun_slave_restanv.php';
    if(confirm('Anda yakin ingin memposting ??? \nData yang sudah diposting tidak bisa di edit dan di delete.'))
    {
        post_response_text(tujuan, param, respog);	
    }
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
                    document.getElementById('contain').innerHTML=con.responseText;
                    loaddata();	
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function del(per,divisi)
{
    param='method=delete'+'&per='+per+'&divisi='+divisi;
    tujuan='kebun_slave_restanv.php';
    if(confirm('Anda yakin ingin menghapus data??'))
    {
        post_response_text(tujuan, param, respog);	
    }
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
					document.getElementById('contain').innerHTML=con.responseText;
					loaddata();	
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function displaylist()
{
   
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display='none';
	
	document.getElementById('persch').value='';
	document.getElementById('divisisch').value='';
    loaddata(0);
}


function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}


function loaddata(num)
{
	persch = document.getElementById('persch');
		persch = persch.options[persch.selectedIndex].value;
	divisisch = document.getElementById('divisisch');
		divisisch = divisisch.options[divisisch.selectedIndex].value;
	
    param = 'method=loaddata&page=' + num;
	if (persch != '') {
        param += '&persch=' + persch;
    }
	if (divisisch != '') {
        param += '&divisisch=' + divisisch;
    }
    
    tujuan = 'kebun_slave_restanv.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];

                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function edit(per,divisi)
{
	document.getElementById('listdata').style.display = 'none';
    document.getElementById('header').style.display = 'block';
	document.getElementById('per').value=per;
	document.getElementById('divisi').value=divisi;
	savehead(per,divisi);
}


function cekthn()
{
	thn=document.getElementById('thn').value;
	
	var today = new Date();
	thns = today.getFullYear();
	
	thnl=parseFloat(thns)-1;
	thnd=parseFloat(thns)+1;
	
	val=Math.abs(thns-thn);
	
	if(val>1)
	{
		alert('Tahun yang diizinkan hanya : '+ thnl +' , '+ thns +' dan '+ thnd +'  ');
		document.getElementById('thn').value='';
	}
}



function newdata()//indra
{
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display='none';
	document.getElementById('per').value='';
	document.getElementById('divisi').value='';
	document.getElementById('divisi').disabled=false;
	document.getElementById('per').disabled=false;
	document.getElementById('savehead').disabled=false;
}

function cancel()
{
	newdata();	
}

function savehead(per,divisi)
{
	divisi=document.getElementById('divisi').value;
	per=document.getElementById('per').value;
	
	if(divisi=='' || per=='')
	{
		alert('Lengkapi terlebih dahulu pengisian form diatas.');return;
	}
    param = 'method=detailinput' + '&per=' + per+ '&divisi=' + divisi;
    tujuan = 'kebun_slave_restanv.php';
    post_response_text(tujuan, param, respon);
    function respon()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else
                {
					
					document.getElementById('per').disabled=true;
					document.getElementById('divisi').disabled=true;
					document.getElementById('savehead').disabled=true;
                    document.getElementById('detail').style.display='block';
					document.getElementById('detailinput').innerHTML = con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function hitungbjr(no,sms)
{
	x=trim(document.getElementById('jjg'+no).value);
	y=trim(document.getElementById('kg'+no).value);
	z=y/x;
	if(isNaN(z))
	{
		z=0;
	}
	if(x==0 || x=='')
	{
		z=0;
	}
	
	document.getElementById('bjr'+no).value=parseFloat(z).toFixed(2);
	
}




////////////////////////////////////////////
function sebarjjg(no,sms,row)	 
{
	
	jjg=trim(document.getElementById('jjg'+no).value);
	proporsi=jjg/6;
	
	if(sms==1)
	{
		for(i=1;i<=6;i++)
		{
			  
                    document.getElementById('sebaranjjg'+no+'#'+i).value=parseFloat(proporsi).toFixed(0);	
		}
		
	}
	else
	{
		for(i=7;i<=12;i++)
		{
                    document.getElementById('sebaranjjg'+no+'#'+i).value=parseFloat(proporsi).toFixed(0);
		}
	}
	sebaranpersenjjg(row,sms);

}

function sebar(no,sms,row)	 
{
	
	kg=trim(document.getElementById('kg'+no).value);
	proporsi=kg/6;
	
	if(sms==1)
	{
		for(i=1;i<=6;i++)
		{
			  
			document.getElementById('sebaran'+no+'#'+i).value=parseFloat(proporsi).toFixed(2);
		}
		
	}
	else
	{
		for(i=7;i<=12;i++)
		{
			document.getElementById('sebaran'+no+'#'+i).value=parseFloat(proporsi).toFixed(2);	
		}
	}
	sebaranpersen(row,sms);
}
////////////////////////////////////////////





////////////////////////////////////////////
function totpersenjjg(sms)
{
	if(sms==1)
	{
		mulai=1;
		selesai=6;
	}
	else
	{
		mulai=7;
		selesai=12;
	}
	
	totaljjg=0;
	for(i=mulai;i<=selesai;i++)
	{
		persenjjg=trim(document.getElementById('persenjjg'+i).value);
		// if(isNaN(persen))
		// {
			// persen=0;
		// }
		totaljjg+=parseFloat(persenjjg);
		
	}
	document.getElementById('totalpersenjjg').value=parseFloat(totaljjg).toFixed(2);	
}


function totpersen(sms)
{
	if(sms==1)
	{
		mulai=1;
		selesai=6;
	}
	else
	{
		mulai=7;
		selesai=12;
	}
	
	total=0;
	for(i=mulai;i<=selesai;i++)
	{
		persen=trim(document.getElementById('persen'+i).value);
		// if(isNaN(persen))
		// {
			// persen=0;
		// }
		total+=parseFloat(persen);
		
	}
	document.getElementById('totalpersen').value=parseFloat(total).toFixed(2);	
}
////////////////////////////////////////////


function sebaranpersen(row,sms)
{
	totalpersen=trim(document.getElementById('totalpersen').value);
	if(sms==1)
	{
		mulai=1;
		selesai=6;
	}
	else
	{
		mulai=7;
		selesai=12;
	}

	for(no=1;no<=row;no++)
	{
		kg=trim(document.getElementById('kg'+no).value);
		if(isNaN(kg))
		{
			kg=0;
		}
		for(i=mulai;i<=selesai;i++)
		{ 
			persen=trim(document.getElementById('persen'+i).value);
			nilai=persen/totalpersen*kg;
			document.getElementById('sebaran'+no+'#'+i).value=parseFloat(nilai).toFixed(2);	
		}
		//document.getElementById('sebaran'+no+'#'+i).value=parseFloat(proporsi,2);	
	}
}




function sebaranpersenjjg(row,sms)
{
	totalpersenjjg=trim(document.getElementById('totalpersenjjg').value);
	if(sms==1)
	{
		mulai=1;
		selesai=6;
	}
	else
	{
		mulai=7;
		selesai=12;
	}

	for(no=1;no<=row;no++)
	{
		jjg=trim(document.getElementById('jjg'+no).value);
		if(isNaN(jjg))
		{
			jjg=0;
		}
		for(i=mulai;i<=selesai;i++)
		{ 
			persenjjg=trim(document.getElementById('persenjjg'+i).value);
			nilaijjg=persenjjg/totalpersenjjg*jjg;
			document.getElementById('sebaranjjg'+no+'#'+i).value=parseFloat(nilaijjg).toFixed();	
		}
	}
}





maxf = 0
sekarang = 1;
function saveall(maxRow) {
    maxf = maxRow;
    loopsave(1, maxRow);
}
function loopsave(currRow, maxRow)
{
	per = trim(document.getElementById('per').value);
	blok = trim(document.getElementById('blok' + currRow).innerHTML);
	jjg = trim(document.getElementById('jjg' + currRow).value);
	
	
    param ='per=' + per + '&blok=' + blok + '&jjg=' + jjg;
    param += "&method=savedata";
    tujuan = 'kebun_slave_restanv.php';
    post_response_text(tujuan, param, respog);
    document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
    //lockScreen('wait');

    function respog() {
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                }
                else {
                    document.getElementById('row' + currRow).style.display = 'none';
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow)
                    {
                        alert('Done');
						cancel();
                    }
                    else
                    {
                        loopsave(currRow, maxRow);
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


















