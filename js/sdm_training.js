// JavaScript Document

function getKary(){
    idkary=document.getElementById('idkary').value; 
    param='idkary='+idkary;
    param+='&proses=getKary';
    tujuan='sdm_training_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
					arr=con.responseText.split("###");
					document.getElementById('nikary').value=arr[0];
					document.getElementById('nmkary').value=arr[1];
                    document.getElementById('jbkary').value=arr[2];
                    document.getElementById('lkkary').value=arr[3];
                    document.getElementById('mkkary').value=arr[4];
					document.getElementById('contain').innerHTML='';
					loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getBerlaku(){
	if(document.getElementById('sertifikat').value==2){
		document.getElementById('berlaku').style.display = 'block';
	}else{
		document.getElementById('berlakudari').value='';
		document.getElementById('berlakusampai').value='';
		//document.getElementById('scansertifikat').value='';
		document.getElementById('berlaku').style.display = 'none';
	}
}

function viewscan(nomor,namafile,ev){
   //frame.location=namafile;
   param='proses=viewfile'+'&nomor='+nomor+'&namafile='+namafile;
   tujuan='sdm_training_slave.php'+"?"+param;
   width='1200';
   height='600';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   //alert(content);
   showDialog1('File '+namafile,content,width,height,ev); 
}

function bersih(num){
	if(num!=2){
		document.getElementById('idkary').value='';
		document.getElementById('nokary').value='';
		document.getElementById('nikary').value='';
		document.getElementById('nmkary').value='';
		document.getElementById('lkkary').value='';
		document.getElementById('jbkary').value='';
		document.getElementById('mkkary').value='';
	}
	document.getElementById('judultraining').value='';
	document.getElementById('jenistraining').value='1';
	document.getElementById('tanggal1').value='';
	document.getElementById('tanggal2').value='';
	document.getElementById('penyelenggara').value='';
	document.getElementById('sertifikat').value='0';
	document.getElementById('biayatraining').value=0;
	document.getElementById('contain').innerHTML='';
	document.getElementById('berlakudari').value='';
	document.getElementById('berlakusampai').value='';
	//document.getElementById('scansertifikat').value='';
	document.getElementById('berlaku').style.display = 'none';
	document.getElementById('proses').value='insert';
}

function cancelSave(){
	bersih(0);
	//loadData();
}

function loadData(){
    idkary=document.getElementById('idkary').value; 
	param='proses=LoadData';
    param+='&idkary='+idkary;
	tujuan='sdm_training_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//alert(con.responseText);
					document.getElementById('contain').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function cariBast(num){
    idkary=document.getElementById('idkary').value; 
	param='proses=LoadData';
	param+='&idkary='+idkary;
	param+='&page='+num;
	tujuan = 'sdm_training_slave.php';
	post_response_text(tujuan, param, respog);			
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
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

function saveData(passParam){
	//alert(passParam)
	var passP =  passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
	nomor=document.getElementById('nokary').value;
	pros=document.getElementById('proses').value;
	param+='&proses='+pros;
	param+='&nomor='+nomor;
	tujuan='sdm_training_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//alert(con.responseText);
					//document.getElementById('sertUpload').action='sdm_training_slave_savesertifikat.php';	
					//document.getElementById('sertUpload').submit();
					//eval('1'+".document.getElementById('sertUpload').action='sdm_training_slave_savesertifikat.php'");	
					//eval('1'+".document.getElementById('sertUpload').submit()");
					//simpanscan();
					loadData();
					bersih(2);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function fillField(nomor){
	document.getElementById('nokary').value=nomor;
	document.getElementById('proses').value='update';
	param='nomor='+nomor+'&proses=showData';
	tujuan='sdm_training_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//alert(con.responseText);
					//loadData();
					ar=con.responseText.split("###");
					document.getElementById('proses').value='update';
					//document.getElementById('idkary').value=ar[0];
					document.getElementById('judultraining').value=ar[1];
					document.getElementById('tanggal1').value=ar[2];
					document.getElementById('tanggal2').value=ar[3];
					document.getElementById('jenistraining').value=ar[4];
					document.getElementById('penyelenggara').value=ar[5];
					document.getElementById('sertifikat').value=ar[6];
					document.getElementById('biayatraining').value=ar[7];
					document.getElementById('berlakudari').value=ar[8];
					document.getElementById('berlakusampai').value=ar[9];
					if(document.getElementById('sertifikat').value==2 || ar[6]==2){
						document.getElementById('berlaku').style.display = 'block';
					}
					//document.getElementById('scansertifikat').value=ar[10];
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function deldata(nomor){
	param='nomor='+nomor+'&proses=delData';
	tujuan='sdm_training_slave.php';
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//alert(con.responseText);
					loadData();
					bersih(2);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
	if(confirm("Are You Sure Want Delete This Data"))
		post_response_text(tujuan, param, respog);
}

function printPDF(nomor,ev){
	param='notransaksi='+nomor;
    param+="&proses=pdf";
	//alert(param);
    showDialog1('Rekomendasi Program Pelatihan',"<iframe frameborder=0 width=100% height=100% src='sdm_training_slave_pdf.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

nameV='winsertifikat';
x=0;
function editscan(nomor,ev){
	x+=1;
	nx=nameV+x;
	nmkary=document.getElementById('nmkary').value;
	tujuan='sdm_training_slave_sertifikat.php?nomor='+nomor;
    content="<iframe name="+nx+" src="+tujuan+" frameborder=0 width=640px height=175px></iframe>";   
    showDialog1("Edit Sertifikat : "+nmkary+' '+nomor,content,'675','200',ev);
}

function savescan(){
	nx=nameV+x;
	nomor=eval(nx+".document.getElementById('nomorx').value");
	simpan(nomor,nx);
	loadData();

	function simpan(nomor,nx){
		eval(nx+".document.getElementById('scanupload').action='sdm_training_slave_save_sertifikat.php'");	
		eval(nx+".document.getElementById('scanupload').submit()");
		loadData();
	}
}

function simpanscan(){
	nx='winsertifikat';
	idkary=eval(nx+".document.getElementById('idkaryx').value");
	penyelenggara=eval(nx+".document.getElementById('penyelenggarax').value");
	tanggal1=eval(nx+".document.getElementById('tanggal1x').value");
	alert('penyelenggara '+penyelenggara);
	simpan(idkary,penyelenggara,tanggal1,nx);
	loadData();

	function simpan(idkary,penyelenggara,tanggal1,nx){
		eval(nx+".document.getElementById('scanupload').action='sdm_training_slave_savesertifikat.php'");	
		eval(nx+".document.getElementById('scanupload').submit()");
		loadData();
	}
}
