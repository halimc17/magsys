// JavaScript Document

function add_new_data(){
	document.getElementById('headher').style.display="block";
	document.getElementById('listData').style.display="none";
	//document.getElementById('detailEntry').style.display="none";
	//document.getElementById('tmbLheader').innerHTML='<button class=mybutton id=dtlAbn onclick=loadDetailData()>'+nmTmblSave+'</button><button class=mybutton id=cancelAbn onclick=cancelAbsn()>'+nmTmblCancel+'</button>';
	//document.getElementById('contentDetail').innerHTML='';
	bersihkanForm();
	statFrm=0;
}

function displayList(){
	document.getElementById('listData').style.display='block';
	document.getElementById('headher').style.display='none';
	//document.getElementById('detailEntry').style.display='none';
	document.getElementById('kdOrgCr').value='';
	loadData();
}

function loadData(num){
	kdorg=document.getElementById('kdOrgCr').value;
	param='proses=loadNewData';
	param+='&stasiuncr='+kdorg;
	param+='&page='+num;
	tujuan='pabrik_machinery_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('contain').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function getStasiun(){
	kodeorg=document.getElementById('kodeorg').value; 
    param='kodeorg='+kodeorg;
    param+='&proses=getStasiun';
	tujuan='pabrik_machinery_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('stasiun').innerHTML=con.responseText;
					getMesin();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getMesin(){
	kodeorg=document.getElementById('kodeorg').value; 
    stasiun=document.getElementById('stasiun').value; 
    param='kodeorg='+kodeorg;
    param+='&stasiun='+stasiun;
    param+='&proses=getMesin';
    tujuan='pabrik_machinery_slave.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('mesin').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function loadDetailData(){
	mesin=document.getElementById('mesin').value;
	param='proses=loadDetailData'+'&mesin='+mesin;
	//alert(param);
	tujuan='pabrik_machinery_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('contentDetail').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function showpopup(kdorg,stasiun,kodemesin,type,ev){
   param='kdorg='+kdorg+'&stasiun='+stasiun+'&kodemesin='+kodemesin+'&type='+type;
   tujuan='pabrik_lapmachinery_showpopup.php'+"?"+param;
   width='800';
   height='470';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Data Machine '+kdorg+' '+stasiun+' '+kodemesin,content,width,height,ev); 
}

function simpanData(){
	kodeorg 	=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	stasiun		=document.getElementById('stasiun').options[document.getElementById('stasiun').selectedIndex].value;
	mesin 		=document.getElementById('mesin').options[document.getElementById('mesin').selectedIndex].value;
	submesin 	=document.getElementById('submesin').options[document.getElementById('submesin').selectedIndex].value;
	stsmesin  	=document.getElementById('stsmesin').value;
	unit1		=document.getElementById('unit1').value;
	stsunit1	=document.getElementById('stsunit1').value;
	unit2		=document.getElementById('unit2').value;
	stsunit2	=document.getElementById('stsunit2').value;
	merk		=document.getElementById('merk').value;
	model		=document.getElementById('model').value;
	ratio		=document.getElementById('ratio').value;
	rpm			=document.getElementById('rpm').value;
	kw			=document.getElementById('kw').value;
	ampere		=document.getElementById('ampere').value;
	tahunbuat	=document.getElementById('tahunbuat').value;
	sn			=document.getElementById('sn').value;
	sproket1	=document.getElementById('sproket1').value;
	sproket2	=document.getElementById('sproket2').value;
	sproket3	=document.getElementById('sproket3').value;
	stssproket	=document.getElementById('stssproket').value;
	chain1		=document.getElementById('chain1').value;
	chain2		=document.getElementById('chain2').value;
	stschain	=document.getElementById('stschain').value;
	pully1		=document.getElementById('pully1').value;
	pully2		=document.getElementById('pully2').value;
	vbelt		=document.getElementById('vbelt').value;
	coupling	=document.getElementById('coupling').value;
	bearing1	=document.getElementById('bearing1').value;
	bearing2	=document.getElementById('bearing2').value;
	bearing3	=document.getElementById('bearing3').value;
	merkhm		=document.getElementById('merkhm').value;
	addedit		=document.getElementById('addedit').value;
	if(kodeorg=='' ||  stasiun=='' ||  mesin=='' ||  submesin==''){
		alert('All fields are required');
	}else{
		param='kodeorg='+kodeorg+'&stasiun='+stasiun+'&mesin='+mesin+'&submesin='+submesin+'&stsmesin='+stsmesin;
		param+='&unit1='+unit1+'&stsunit1='+stsunit1+'&unit2='+unit2+'&stsunit2='+stsunit2+'&merk='+merk+'&model='+model+'&ratio='+ratio+'&rpm='+rpm+'&kw='+kw;
		param+='&ampere='+ampere+'&sn='+sn+'&tahunbuat='+tahunbuat+'&sproket1='+sproket1+'&sproket2='+sproket2+'&sproket3='+sproket3+'&stssproket='+stssproket;
		param+='&chain1='+chain1+'&chain2='+chain2+'&stschain='+stschain+'&pully1='+pully1+'&pully2='+pully2+'&vbelt='+vbelt+'&coupling='+coupling;
		param+='&bearing1='+bearing1+'&bearing2='+bearing2+'&bearing3='+bearing3+'&merkhm='+merkhm+'&addedit='+addedit+'&proses=saveData';
		tujuan='pabrik_machinery_slave.php';
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//document.getElementById('container').innerHTML=con.responseText;
					bersihkanForm();
					loadDetailData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 		
}

function bersihkanForm(){
	document.getElementById('stasiun').value='';
	//document.getElementById('submesin').value='1';
	document.getElementById('stsmesin').value='0';
	document.getElementById('unit1').value='';
	document.getElementById('stsunit1').value='0';
	document.getElementById('unit2').value='';
	document.getElementById('stsunit2').value='0';
	document.getElementById('merk').value='';
	document.getElementById('model').value='';
	document.getElementById('ratio').value='';
	document.getElementById('rpm').value='';
	document.getElementById('kw').value='';
	document.getElementById('ampere').value='';
	document.getElementById('tahunbuat').value='';
	document.getElementById('sn').value='';
	document.getElementById('sproket1').value='';
	document.getElementById('sproket2').value='';
	document.getElementById('sproket3').value='';
	document.getElementById('stssproket').value='0';
	document.getElementById('chain1').value='';
	document.getElementById('chain2').value='';
	document.getElementById('stschain').value='0';
	document.getElementById('pully1').value='';
	document.getElementById('pully2').value='';
	document.getElementById('vbelt').value='';
	document.getElementById('coupling').value='';
	document.getElementById('bearing1').value='';
	document.getElementById('bearing2').value='';
	document.getElementById('bearing3').value='';
	document.getElementById('merkhm').value='';
	document.getElementById('contentDetail').innerHTML='';
	document.getElementById('addedit').value='insert';
	getMesin();
}

function fillfield(kodeorg,mesin,submesin,stsmesin,unit1,stsunit1,unit2,stsunit2,merk,model,ratio,rpm,kw,ampere,tahunbuat,sn
				,sproket1,sproket2,sproket3,stssproket,chain1,chain2,stschain,pully1,pully2,vbelt,coupling,bearing1,bearing2,bearing3,merkhm){
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('stasiun').value=mesin.substr(0, 6);
	document.getElementById('mesin').value=mesin;
	document.getElementById('submesin').value=submesin;
	document.getElementById('stsmesin').value=stsmesin;
	document.getElementById('unit1').value=unit1;
	document.getElementById('stsunit1').value=stsunit1;
	document.getElementById('unit2').value=unit2;
	document.getElementById('stsunit2').value=stsunit2;
	document.getElementById('merk').value=merk;
	document.getElementById('model').value=model;
	document.getElementById('ratio').value=ratio;
	document.getElementById('rpm').value=rpm;
	document.getElementById('kw').value=kw;
	document.getElementById('ampere').value=ampere;
	document.getElementById('tahunbuat').value=tahunbuat;
	document.getElementById('sn').value=sn;
	document.getElementById('sproket1').value=sproket1;
	document.getElementById('sproket2').value=sproket2;
	document.getElementById('sproket3').value=sproket3;
	document.getElementById('stssproket').value=stssproket;
	document.getElementById('chain1').value=chain1;
	document.getElementById('chain2').value=chain2;
	document.getElementById('stschain').value=stschain;
	document.getElementById('pully1').value=pully1;
	document.getElementById('pully2').value=pully2;
	document.getElementById('vbelt').value=vbelt;
	document.getElementById('coupling').value=coupling;
	document.getElementById('bearing1').value=bearing1;
	document.getElementById('bearing2').value=bearing2;
	document.getElementById('bearing3').value=bearing3;
	document.getElementById('merkhm').value=merkhm;
	document.getElementById('addedit').value='update';
}

function delDetail(kodeorg,mesin,submesin,namasubmesin){
	param='kodeorg='+kodeorg+'&mesin='+mesin+'&submesin='+submesin;
	param+='&proses=delDetail';
	if (confirm('Delete '+namasubmesin+' Mesin '+mesin+'..?')) {
		tujuan = 'pabrik_machinery_slave.php';
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//document.getElementById('container').innerHTML=con.responseText;
					loadDetailData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function editData(kodeorg,mesin){
	document.getElementById('headher').style.display="block";
	document.getElementById('listData').style.display="none";
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('stasiun').value=mesin.substr(0, 6);
	document.getElementById('mesin').value=mesin;
	document.getElementById('submesin').value='1';
	document.getElementById('stsmesin').value='0';
	document.getElementById('unit1').value='';
	document.getElementById('stsunit1').value='0';
	document.getElementById('unit2').value='';
	document.getElementById('stsunit2').value='0';
	document.getElementById('merk').value='';
	document.getElementById('model').value='';
	document.getElementById('ratio').value='';
	document.getElementById('rpm').value='';
	document.getElementById('kw').value='';
	document.getElementById('ampere').value='';
	document.getElementById('tahunbuat').value='';
	document.getElementById('sn').value='';
	document.getElementById('sproket1').value='';
	document.getElementById('sproket2').value='';
	document.getElementById('sproket3').value='';
	document.getElementById('stssproket').value='0';
	document.getElementById('chain1').value='';
	document.getElementById('chain2').value='';
	document.getElementById('stschain').value='0';
	document.getElementById('pully1').value='';
	document.getElementById('pully2').value='';
	document.getElementById('vbelt').value='';
	document.getElementById('coupling').value='';
	document.getElementById('bearing1').value='';
	document.getElementById('bearing2').value='';
	document.getElementById('bearing3').value='';
	document.getElementById('merkhm').value='';
	document.getElementById('contentDetail').innerHTML='';
	document.getElementById('addedit').value='insert';
	loadDetailData();
}

function delData(kodeorg,mesin){
	param='kodeorg='+kodeorg+'&mesin='+mesin;
	param+='&proses=delData';
	if (confirm('Delete Mesin '+mesin+'..?')) {
		tujuan = 'pabrik_machinery_slave.php';
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//document.getElementById('container').innerHTML=con.responseText;
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}
