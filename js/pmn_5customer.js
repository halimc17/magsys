// JavaScript Document

//search kelompok pelanggan
function searchGruop(title,content,ev)
{
        width='500';
        height='400';
        showDialog1(title,content,width,height,ev);
        //alert('asdasd');
}
function findGroup()
{
        txt_grp=trim(document.getElementById('group_name').value);
        if(txt_grp=='')
        {
                alert('Text is obligatory');
        }
        else
        {
                param='txtfind_klp='+txt_grp;
                tujuan='log_slave_get_grp_cus.php';
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
                                                else {
                                                        //alert(con.responseText);
                                                        document.getElementById('container_cari').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  	
}
function setGroup(kode,kelompok)
{
         document.getElementById('nama_group').value=kelompok;
         document.getElementById('klcustomer_code').value=kode;
         closeDialog();
}

////search kelompok akun
function searchAkun(title,content,ev)
{
        width='500';
        height='400';
        showDialog1(title,content,width,height,ev);
        //alert('asdasd');
}
function findAkun()
{
        txt=trim(document.getElementById('no_akun').value);
        if(txt=='')
        {
                alert('Text is obligatory');
        }
        else
        {
                param='txtfind='+txt;
                tujuan='log_slave_get_grp_cus.php';
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
                                                else {
                                                        //alert(con.responseText);
                                                        document.getElementById('container_cari_akun').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  	
}
function setNoakun(no_akun,namaakun)
{
         document.getElementById('nama_akun').value=namaakun;
         document.getElementById('akun_cust').value=no_akun;
         closeDialog();
}

////end dari search

////fungsi menghapus isi form-->reset
function batalPlgn()
{
		document.getElementById('kode_cus').value='';
        document.getElementById('kode_cus').disabled=false;
        document.getElementById('klcustomer_code').value='';
        document.getElementById('nama_group').value='';
        document.getElementById('akun_cust').value='';
        document.getElementById('nama_akun').value='';
        document.getElementById('cust_nm').value='';
        document.getElementById('kta').value='';
        document.getElementById('tlp_cust').value='';
        document.getElementById('kntk_person').value='';
        document.getElementById('plafon_cus').value='0';
        document.getElementById('n_hutang').value='0';
        document.getElementById('toleransipenyusutan').value='';
        document.getElementById('npwp_no').value='';
        document.getElementById('npwp_alamat').value='';
        document.getElementById('penandatangan').value='';
        document.getElementById('jabatan').value='';
        document.getElementById('seri_no').value='';
        document.getElementById('almt').value='';
		document.getElementById("statusinteks").selectedIndex = "0";
		document.getElementById('ketBerikat').value='';
		document.getElementById('ketBerikat').disabled=true;
        document.getElementById('method').value='insert';
		document.getElementById('chkBerikat').checked=false;
		chkKomoditi=document.getElementsByName('chkKomoditi[]');
		for (i = 0; i < chkKomoditi.length; i++){
			chkKomoditi[i].checked = false ;
		}
		loadKontakPerson();
}

function checkChkBerikat(){
	chkBerikat=document.getElementById('chkBerikat').checked;
	if(chkBerikat==true){
		document.getElementById('ketBerikat').value='';
		document.getElementById('ketBerikat').disabled=false;
	}else{
		document.getElementById('ketBerikat').value='';
		document.getElementById('ketBerikat').disabled=true;
	}
}

////simpan data
function simpanPlgn()
{
		chkKomoditi=document.getElementsByName('chkKomoditi[]');
		var vals = "";
		var countKomoditi=0;
		for (var i=0;i<chkKomoditi.length;i++) {
			if (chkKomoditi[i].checked) {
				vals += ","+chkKomoditi[i].value;
				countKomoditi=countKomoditi+1;
			}
		}
		komoditi=vals.substring(1);
		if(document.getElementById('chkBerikat').checked==true){
			berikat=1;
		}else{
			berikat=0;
		}
		ketBerikat=trim(document.getElementById('ketBerikat').value);
		toleransipenyusutan=trim(document.getElementById('toleransipenyusutan').value);
        kodecustomer=trim(document.getElementById('kode_cus').value);
        namacustomer=trim(document.getElementById('cust_nm').value);
        alamat=trim(document.getElementById('almt').value);
        kota=trim(document.getElementById('kta').value);
        telepon=trim(document.getElementById('tlp_cust').value);
        kontakperson=trim(document.getElementById('kntk_person').value);
        akun=trim(document.getElementById('akun_cust').value);
        plafon=trim(document.getElementById('plafon_cus').value);
		statusinteks=trim(document.getElementById('statusinteks').value);
        nilaihutang=trim(document.getElementById('n_hutang').value);
        npwp=trim(document.getElementById('npwp_no').value);
        npwpalamat=trim(document.getElementById('npwp_alamat').value);
        penandatangan=trim(document.getElementById('penandatangan').value);
        jabatan=trim(document.getElementById('jabatan').value);
        noseri=trim(document.getElementById('seri_no').value);
        klcustomer=trim(document.getElementById('klcustomer_code').value);
        method=document.getElementById('method').value;
                param='kodecustomer='+kodecustomer+'&namacustomer='+namacustomer+'&alamat='+alamat+'&kota='+kota+'&telepon='+telepon+'&kontakperson='+kontakperson;
                param+='&akun='+akun+'&plafon='+plafon+'&nilaihutang='+nilaihutang+'&npwp='+npwp+'&npwpalamat='+npwpalamat+'&penandatangan='+penandatangan+'&jabatan='+jabatan+'&noseri='+noseri+'&klcustomer='+klcustomer+'&method='+method;
				param+='&komoditi='+komoditi+'&berikat='+berikat+'&ketBerikat='+ketBerikat+'&toleransipenyusutan='+toleransipenyusutan+'&statusinteks='+statusinteks;
                tujuan='log_slave_save_cust.php';

        if (kodecustomer == '' || namacustomer == '' || alamat=='' || kota=='' || telepon=='' || penandatangan=='' || countKomoditi == 0 || (berikat==1 && ketBerikat=='')) 
        {
                alert('Data inconsistent');
        }
        else {
				if(confirm('Are you sure?'))
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
                                                else {
                                                        //alert(con.responseText);
                                                        document.getElementById('container').innerHTML=con.responseText;
                                                        batalPlgn();
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
}

//get data from database terus ditampilkan ke dalam form
function fillField(kodecustomer,namacustomer,alamat,kota,telepon,kontakperson,akun,plafon,nilaihutang,npwp,npwpalamat,penandatangan,jabatan,noseri,klcustomer,namaakun,kelompok,toleransipenyusutan,berikat,ketBerikat,hasilKomoditi,statusinteks)
{       //alert(kodecustomer);
        kode_cus		=document.getElementById('kode_cus');
        kode_cus.value	=kodecustomer;
        kode_cus.disabled=true;
        cust_nm			=document.getElementById('cust_nm');
        cust_nm.value	=namacustomer;
        almt		    =document.getElementById('almt');
        almt.value		=alamat;
        kta			=document.getElementById('kta');
        kta.value=kota;
        tlp_cust			=document.getElementById('tlp_cust');
        tlp_cust.value=telepon;
        kntk_person			=document.getElementById('kntk_person');
        kntk_person.value=kontakperson;
        akun_cust			=document.getElementById('akun_cust');
        akun_cust.value		=akun;
        plafon_cus			=document.getElementById('plafon_cus');
        plafon_cus.value=plafon;
        n_hutang			=document.getElementById('n_hutang');
        n_hutang.value=nilaihutang;
        npwp_no			=document.getElementById('npwp_no');
        npwp_no.value=npwp;
		npwp_alamat			=document.getElementById('npwp_alamat');
        npwp_alamat.value=npwpalamat;
		penandatanganx			=document.getElementById('penandatangan');
        penandatanganx.value=penandatangan;
		jabatanx			=document.getElementById('jabatan');
        jabatanx.value=jabatan;
        seri_no			=document.getElementById('seri_no');
        seri_no.value=noseri;
        klcustomer_code			=document.getElementById('klcustomer_code');
        klcustomer_code.value=klcustomer;
        nama_akun			=document.getElementById('nama_akun');
        nama_akun.value=namaakun;
        nama_group			=document.getElementById('nama_group');
        nama_group.value=kelompok;
		toleransi_penyusutan=document.getElementById('toleransipenyusutan');
        toleransi_penyusutan.value=toleransipenyusutan;
		ket_Berikat			=document.getElementById('ketBerikat');
        ket_Berikat.value=ketBerikat;
		if(berikat==1){
			document.getElementById('chkBerikat').checked=true;
			document.getElementById('ketBerikat').disabled=false;
		}else{
			document.getElementById('chkBerikat').disabled=false;
			document.getElementById('ketBerikat').disabled=true;
		}
		
		chkKomoditi=document.getElementsByName('chkKomoditi[]');
		var myarray = hasilKomoditi.split(',');

		for(var i = 0; i < myarray.length; i++)
		{
			for (j = 0; j < chkKomoditi.length; j++){
				if(chkKomoditi[j].value==myarray[i]){
					chkKomoditi[j].checked = true ;
				}
			}
		   // alert(myarray[i]);
		}
		
		objstatusintext=document.getElementById('statusinteks');
           // kel=idsupplier.substring(0,4);
		for(x=0;x<objstatusintext.length;x++)
		{
			if(objstatusintext.options[x].value==statusinteks)
			{
				objstatusintext.options[x].selected=true;
			}
		}        	 
		
		cat=0;
        document.getElementById('method').value='update';
		loadKontakPerson();
}

function delPlgn(kodecustomer)
{
        param='kodecustomer='+kodecustomer;
                param+='&method=delete';
                tujuan='log_slave_save_cust.php';
                if(confirm('Deleting, Are you sure?'))
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
                                                        //alert(con.responseText);
                                                        document.getElementById('container').innerHTML=con.responseText;
														batalPlgn();
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
}

function loadKontakPerson(){
	kode_cus = document.getElementById('kode_cus').value;
	param = 'kode_cus='+kode_cus+'&proses=loadKontakPerson';
	post_response_text('pmn_slave_5kontakperson.php', param, respon);
	
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                   document.getElementById('listKontakPerson').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function addKontakPerson(id){
	kode_cus = document.getElementById('kode_cus').value;
	nama = document.getElementById('nama_'+id).value;
	telepon = document.getElementById('telepon_'+id).value;
	email = document.getElementById('email_'+id).value;
	
	param = 'kode_cus='+kode_cus+'&nama='+nama+'&telepon='+telepon+'&email='+email+'&proses=addKontakPerson';
	post_response_text('pmn_slave_5kontakperson.php', param, respon);
	
	function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
				   document.getElementById('kode_cus').disabled=true;
                   document.getElementById('listKontakPerson').innerHTML=con.responseText;
				   loadKontakPerson();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	// alert(id);
	// switchEditAdd(id,'detail');
	// addNewRow('detailBody',true);
}

function deleteKontakPerson(id){
	param = 'idkontak='+id+'&proses=deleteKontakPerson';
	post_response_text('pmn_slave_5kontakperson.php', param, respon);
	
	function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
				   document.getElementById('listKontakPerson').innerHTML=con.responseText;
				   loadKontakPerson();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}