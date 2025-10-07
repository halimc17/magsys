function getkeg() {
    var kodeasset = document.getElementById('kodeasset').value;
    var param = "kodeasset="+kodeasset;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
					document.getElementById('kodekegiatan').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    
	post_response_text('keu_slave_jurnal_header.php?proses=getkeg', param, respon);
    
} 
 



var showPerPage = 10;

function getValue(id) {
    var tmp = document.getElementById(id);
    
    if(tmp) {
        if(tmp.options) {
            return tmp.options[tmp.selectedIndex].value;
        } else if(tmp.nodeType=='checkbox') {
            if(tmp.checked==true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return tmp.value;
        }
    } else {
        return false;
    }
}












function getsup(){
	tipeinv=document.getElementById('tipeinv').options[document.getElementById('tipeinv').selectedIndex].value;
	param='tipeinv='+tipeinv;
    tujuan='keu_slave_kasbank_detail.php';

	post_response_text(tujuan+'?'+'proses=getsup', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('supplierIdcr').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}











function getKurs2(){
	tanggal=document.getElementById('tanggal').value;	
	matauang = getById('ftPrestasi_matauang').firstChild.value;
	
	if (matauang!='IDR') {
		if(tanggal=='' || matauang=='')
		{
			alert("Date or Currency empty");
			document.getElementById('kurs').value=''; 
			return;
		}
		param='proses=getKurs'+'&matauang='+matauang+'&tanggal='+tanggal;
		tujuan='keu_slave_kasbank_kurs.php';
		post_response_text(tujuan, param, respog);
	} else {
		getById('ftPrestasi_kurs').firstChild.value='1';
		getById('ftPrestasi_kurs').firstChild.setAttribute('disabled','disabled');
	}
	
    function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
						
				} else {
					if(con.responseText=='') {
						getById('ftPrestasi_kurs').firstChild.value='0';
						alert("Please input rate Currency");
						return;
					} else {
						getById('ftPrestasi_kurs').firstChild.value=con.responseText;
					}
				}
			}
			else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}

function getKurs()
{
    matauang=document.getElementById('matauang').value;
    tanggal=document.getElementById('tanggal').value;
	
	// document.getElementById('addHead').setAttribute('disabled','disabled');
	// if (document.getElementById('editHead')) {
	// 	document.getElementById('editHead').setAttribute('disabled','disabled');
	// }
	
	if (matauang!='IDR') {
		if(tanggal=='' || matauang=='')
		{
			alert("Date or Currency empty");
			document.getElementById('kurs').value=''; 
			return;
		}
		param='proses=getKurs'+'&matauang='+matauang+'&tanggal='+tanggal;
		
		//alert(param);
		tujuan='keu_slave_kasbank_kurs.php';
		
		post_response_text(tujuan, param, respog);
	} else {
		document.getElementById('kurs').value='1';
		document.getElementById('kurs').setAttribute('disabled','disabled');
		// document.getElementById('addHead').removeAttribute('disabled');
		// if (document.getElementById('editHead')) {
		// 	document.getElementById('editHead').removeAttribute('disabled');
		// }
	}
    function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
						
				} else {
					//document.getElementById('kurs').removeAttribute('disabled');
					if(con.responseText=='') {
						document.getElementById('kurs').value='';
						// document.getElementById('addHead').setAttribute('disabled','disabled');
						// if (document.getElementById('editHead')) {
						// 	document.getElementById('editHead').setAttribute('disabled','disabled');
						// }
						alert("Please input rate Currency");
						return;
					} else {
						document.getElementById('kurs').value=con.responseText;
						// document.getElementById('addHead').removeAttribute('disabled');
						// if (document.getElementById('editHead')) {
						// 	document.getElementById('editHead').removeAttribute('disabled');
						// }
					}
				}
			}
			else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}

//




/* Search
 * Filtering Data
 */
function searchTrans() {
    var notrans = document.getElementById('sNoTrans');
    var rupiah = document.getElementById('sRupiah');
    var tanggal = getValue('sTanggal');
    
    var noakun = getValue('sAkun');
    var tanggal2 = getValue('sTanggal2');
    var tipetransaksi=getValue('sTipe');
    
    
    if(tanggal!='') {
        var tmpTanggal = tanggal.split('-');
        var tanggalR = tmpTanggal[2]+"-"+tmpTanggal[1]+"-"+tmpTanggal[0];
    } else {
        var tanggalR = '';
    }
    
     if(tanggal2!='') {
        var tmpTanggal2 = tanggal2.split('-');
        var tanggalR2 = tmpTanggal2[2]+"-"+tmpTanggal2[1]+"-"+tmpTanggal2[0];
    } else {
        var tanggalR2 = '';
    }
    
    var where = '[["notransaksi","'+notrans.value+'"],["tanggal","'+tanggalR+'"],["jumlah","'+remove_comma_var(rupiah.value)+'"],["noakun","'+noakun+'"],["tanggal2","'+tanggalR2+'"],["tipetransaksi","'+tipetransaksi+'"]]';
    
    goToPages(1,showPerPage,where);
}


/* Paging
 * Paging Data
 */
function defaultList() {

	
	
	
	
	
	goToPages(1,showPerPage);
}

function goToPages(page,shows,where) {
	//ini datanya
	var notrans = document.getElementById('sNoTrans');
    var rupiah = document.getElementById('sRupiah');
    var tanggal = getValue('sTanggal');

    var noakun = getValue('sAkun');
    var tipetransaksi=getValue('sTipe');
	var tanggal2 = getValue('sTanggal2');
    
    if(tanggal!='') {
        var tmpTanggal = tanggal.split('-');
        var tanggalR = tmpTanggal[2]+"-"+tmpTanggal[1]+"-"+tmpTanggal[0];
    } else {
        var tanggalR = '';
    }
	
	 if(tanggal2!='') {
        var tmpTanggal2 = tanggal2.split('-');
        var tanggalR2 = tmpTanggal2[2]+"-"+tmpTanggal2[1]+"-"+tmpTanggal2[0];
    } else {
        var tanggalR2 = '';
    }
    
    var where = '[["notransaksi","'+notrans.value+'"],["tanggal","'+tanggalR+'"],["tanggal2","'+tanggalR2+'"],["jumlah","'+remove_comma_var(rupiah.value)+'"],["noakun","'+noakun+'"],["tipetransaksi","'+tipetransaksi+'"]]';
  
	
    if(typeof where != 'undefined') {
        var newWhere = where.replace(/'/g,'"');
    }
    var workField = document.getElementById('workField');
    var param = "page="+page;
    param += "&shows="+shows+"&tipe=KB";
    if(typeof where != 'undefined') {
        param+="&where="+newWhere;
    }
    


    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=showHeadList', param, respon);
}


function choosePage(obj,shows,where) {
    var pageVal = obj.options[obj.selectedIndex].value;
    goToPages(pageVal,shows,where);
}

/* Halaman Manipulasi Data
 * Halaman add, edit, delete
 */
function showAdd() {
    var workField = document.getElementById('workField');
    var param = "";
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;

				
				
				}
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=showAdd', param, respon);
}

function showEditFromAdd() {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('notransaksi');
    var param = "notransaksi="+trans.value+"&kodeorg="+getValue('kodeorg')+
        "&noakun="+getValue('noakun2a')+"&tipetransaksi="+getValue('tipetransaksi');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                    showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=showEdit', param, respon);
}

function showEdit(num) {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('notransaksi_'+num).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+num).getAttribute('value');
    var noakun = document.getElementById('noakun_'+num).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+num).getAttribute('value');
    var param = "numRow="+num+"&notransaksi="+trans+"&kodeorg="+
        kodeorg+"&noakun="+noakun+"&tipetransaksi="+tipetransaksi;



    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                    showDetail();



                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=showEdit', param, respon);
}

/* Manipulasi Data
 * add, edit, delete
 */
function addDataTable() {
    var hutangunit='';
    var pemilikhutang=getValue('pemilikhutang');
    var noakunhutang=getValue('noakunhutang');
    if(document.getElementById("hutangunit").checked==true){
        hutangunit='1';
    }else{
        pemilikhutang='';
        noakunhutang='';
    }
    
    if(getValue('kurs')=='' || getValue('kurs')==0)
    {
        alert("field kurs empty");return;
    }
    
    var param = "notransaksi="+getValue('notransaksi')+"&noakun="+getValue('noakun2a');
    param += "&tanggal="+getValue('tanggal')+"&matauang="+getValue('matauang');
    param += "&kurs="+getValue('kurs')+"&tipetransaksi="+getValue('tipetransaksi');
    param += "&jumlah="+getValue('jumlah')+"&cgttu="+getValue('cgttu');
    param += "&keterangan="+getValue('keterangan')+"&yn="+getValue('yn')+"&kodeorg="+getValue('kodeorg');
     param += "&nocek="+getValue('nocek');
    param+= "&hutangunit="+hutangunit;
    param+= "&pemilikhutang="+pemilikhutang;
    param+= "&noakunhutang="+noakunhutang;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('notransaksi').value = con.responseText;
                    showEditFromAdd();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=add', param, respon);
}


//indra
function editDataTable() {
    var hutangunit='';
    var pemilikhutang=getValue('pemilikhutang');
    var noakunhutang=getValue('noakunhutang');
    if(document.getElementById("hutangunit").checked==true){
        hutangunit='1';
    }else{
        pemilikhutang='';
        noakunhutang='';
    }
    var param = "notransaksi="+getValue('notransaksi')+"&noakun="+getValue('noakun2a');
    param += "&tanggal="+getValue('tanggal')+"&matauang="+getValue('matauang');
    param += "&kurs="+getValue('kurs')+"&tipetransaksi="+getValue('tipetransaksi');
    param += "&jumlah="+getValue('jumlah')+"&cgttu="+getValue('cgttu');
    param += "&keterangan="+getValue('keterangan')+"&yn="+getValue('yn')+"&kodeorg="+getValue('kodeorg');
    param+= "&oldNoakun="+getValue('oldNoakun');
    param += "&nocek="+getValue('nocek');
    param+= "&hutangunit="+hutangunit;
    param+= "&pemilikhutang="+pemilikhutang;
    param+= "&noakunhutang="+noakunhutang;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    alert(con.responseText);
                    defaultList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=edit', param, respon);
}

/*
 * Detail
 */

function showDetail() {
    var detailField = document.getElementById('detailField');
    var notrans = document.getElementById('notransaksi').value;
    var param = "notransaksi="+notrans+"&kodeorg="+getValue('kodeorg')+"&tipetransaksi="+
        getValue('tipetransaksi')+"&noakun="+getValue('noakun2a');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
					var res = con.responseText;
					res = res.split('<script>');
                    detailField.innerHTML = res[0];
					if(res.length>1) {
						res[1] = res[1].replace('</script></fieldset>','');
						console.log(res[1]);
						eval(res[1]);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank_detail.php?proses=showDetail', param, respon);
}

function pilihhutang(){
//    var kodeorg=getValue('kodeorg');
//    if(kodeorg.substring(2, 4)=='HO'){
//        
//    }else{
//        alert('Pilihan hanya untuk HO');
//        document.getElementById('hutangunit').checked=false;
//        document.getElementById('pemilikhutang').disabled=true;
//        document.getElementById('noakunhutang').disabled=true;
//        exit();
//    }
    var centang = document.getElementById('hutangunit');
    if(centang.checked!=true){
        document.getElementById('pemilikhutang').disabled=true;
        document.getElementById('noakunhutang').disabled=true;
    }else{
        document.getElementById('pemilikhutang').disabled=false;
        document.getElementById('noakunhutang').disabled=false;        
    }
}

//function gantiValue(obj){
//    if(obj.value==1)
//        obj.value=0; else obj.value=1;
//}

function deleteData(num) {
    var notrans = document.getElementById('notransaksi_'+num).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+num).getAttribute('value');
    var noakun = document.getElementById('noakun_'+num).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+num).getAttribute('value');
    var param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+
        "&tipetransaksi="+tipetransaksi;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    var tmp = document.getElementById('tr_'+num);
                    tmp.parentNode.removeChild(tmp);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank.php?proses=delete', param, respon);
}

/* Posting Data
 */
function postingData(numRow) {
    var notrans = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var param = "notransaksi="+notrans+"&kodeorg="+kodeorg+"&noakun="+noakun+
        "&tipetransaksi="+tipetransaksi;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    //alert('Posting Berhasil');
                    x=document.getElementById('tr_'+numRow);
                    x.cells[9].innerHTML='';
                    x.cells[10].innerHTML='';
                    x.cells[11].innerHTML="<img class=\"zImgOffBtn\" title=\"Posting\" src=\"images/skyblue/posted.png\">";
                    //javascript:location.reload(true);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    if(confirm('Posting '+notrans+'\nThis transaction will released. are you sure?')) {
        post_response_text('keu_slave_kasbank_posting.php', param, respon);
    }
}

function printPDF(ev) {
    // Prep Param
    param = "proses=pdf";
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function printXLS(ev) {
    // Prep Param
    param = "proses=excel";
    
    showDialog1('Print Excel',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function detailPDF(numRow,ev) {
    // Prep Param
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    param = "proses=pdf&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function detailPDF2(numRow,ev) {
    // Prep Param
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    param = "proses=pdf2&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;



    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}



function tampilDetail(numRow,ev)
{
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
   param = "proses=html&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
        title="Data Detail";
        showDialog1(title,"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);	
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}
/* Update No Urut di halaman absensi
 */
function updNoUrut() {
    var tabBody = document.getElementById('mTabBody');
    var nourut = document.getElementById('nourut');
    var maxNum = 0;
    
    if(tabBody.childNodes.length>0) {
        for(i=0;i<tabBody.childNodes.length;i++) {
            var tmp = document.getElementById('nourut_'+i);
            if(tmp.innerHTML > maxNum) {
                maxNum = tmp.innerHTML;
            }
        }
    }
    nourut.value = parseInt(maxNum)+1;
}

/* Update Field Aktif berdasarkan akun yang dipilih
 */
function updFieldAktif() {
    var id='ftPrestasi_';
    var noakun = document.getElementById(id+'noakun').childNodes;
    var kodekegiatan = document.getElementById(id+'kodekegiatan').childNodes;
    var kodeasset = document.getElementById(id+'kodeasset').childNodes;
    var kodebarang = document.getElementById(id+'kodebarang').childNodes;
    var nik = document.getElementById(id+'nik').childNodes;
    var kodecustomer = document.getElementById(id+'kodecustomer').childNodes;
    var kodesupplier = document.getElementById(id+'kodesupplier').childNodes;
    var kodevhc = document.getElementById(id+'kodevhc').childNodes;
    var param = "noakun="+noakun[0].options[noakun[0].selectedIndex].value;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    var res = con.responseText;
                    
                    // Kegiatan
                    if(res[0]==0) {
                        kodekegiatan[0].setAttribute('disabled','disabled');
                        kodekegiatan[0].selectedIndex=0;
                    } else {
                        kodekegiatan[0].removeAttribute('disabled');
                    }
                    
                    // Asset
                    if(res[1]==0) {
                        kodeasset[0].setAttribute('disabled','disabled');
                        kodeasset[0].selectedIndex=0;
                    } else {
                        kodeasset[0].removeAttribute('disabled');
                    }
                    
                    // Barang
                    if(res[2]==0) {
                        kodebarang[0].setAttribute('disabled','disabled');
                        kodebarang[2].setAttribute('disabled','disabled');
                        kodebarang[3].setAttribute('disabled','disabled');
                        kodebarang[0].value='';
                        kodebarang[2].value='';
                    } else {
                        kodebarang[0].removeAttribute('disabled');
                        kodebarang[2].removeAttribute('disabled');
                        kodebarang[3].removeAttribute('disabled');
                    }
                    
                    // Karyawan
                    if(res[3]==0) {
                        nik[0].setAttribute('disabled','disabled');
                        nik[0].selectedIndex=0;
                    } else {
                        nik[0].removeAttribute('disabled');
                    }
                    
                    // Customer
                    if(res[4]==0) {
                        kodecustomer[0].setAttribute('disabled','disabled');
                        kodecustomer[0].selectedIndex=0;
                    } else {
                        kodecustomer[0].removeAttribute('disabled');
                    }
                    
                    // Supplier
                    if(res[5]==0) {
                        kodesupplier[0].setAttribute('disabled','disabled');
                        kodesupplier[0].selectedIndex=0;
                    } else {
                        kodesupplier[0].removeAttribute('disabled');
                    }
                    
                    // Kendaraan
                    if(res[6]==0) {
                        kodevhc[0].setAttribute('disabled','disabled');
                        kodevhc[0].selectedIndex=0;
                    } else {
                        kodevhc[0].removeAttribute('disabled');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('keu_slave_kasbank_detail.php?proses=updField', param, respon);
}

//jamhari
function searchNopo(title,content,ev)
{
        //isi=document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].value;
        //content=content+"<input type='hidden' id='jnsInvoice' value="+isi+">";
	width='850';
	height='620';
	showDialog1(title,content,width,height,ev);
        getForminvoice(0);
	//alert('asdasd');
}

function searchKontrak(title,content,ev)
{
    width='850';
	height='620';
	showDialog1(title,content,width,height,ev);
    getForminvoice(1);
}

function searchMemo(title,content,ev)
{
    width='850';
	height='620';
	showDialog1(title,content,width,height,ev);
    getForminvoice(2);
	//alert('asdasd');
}

function searchPerdin(title,content,ev)
{
    width='850';
	height='620';
	showDialog1(title,content,width,height,ev);
    getForminvoice(3);
	//alert('asdasd');
}

function getForminvoice(tipe)
{
	param='';
	tujuan='keu_slave_kasbank_detail.php';
	if(tipe==0) {
		post_response_text(tujuan+'?'+'proses=getForminvoice', param, respog);
	} else if (tipe==1) {
		post_response_text(tujuan+'?'+'proses=getFormInvoiceAR', param, respog);
	} else if (tipe==3) {
		post_response_text(tujuan+'?'+'proses=getFormPerdin', param, respog);
	} else {
		post_response_text(tujuan+'?'+'proses=getFormMemo', param, respog);
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
							document.getElementById('formPencariandata').innerHTML=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }
	 }
} 
function findNoinvoice(tipe)
{
	txt=trim(document.getElementById('no_brg').value);
        idSupplier=document.getElementById('supplierIdcr').options[document.getElementById('supplierIdcr').selectedIndex].value;
	param='txtfind='+txt;
    if(idSupplier!='')
    {
        param+='&idSupplier='+idSupplier
    }
    param += '&sNopo='+getValue('sNopo')+'&sInvSupp='+getValue('sInvSupp');
    param += '&sNilai='+getValue('sNilai')+'&sYm='+getValue('sYm');
	param += '&matauang='+getValue('matauang');
    tujuan='keu_slave_kasbank_detail.php';
    if((txt=='')&&(idSupplier==''))
    {
        alert("Supplier Code is obligatory");
    } else {
		if(tipe==0)
			post_response_text(tujuan+'?'+'proses=getInvoice', param, respog);
		else
			post_response_text(tujuan+'?'+'proses=getInvoiceAR', param, respog);
	}
        
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function findMemo()
{
	var param='nojurnal='+getValue('sNojurnal')+'&periode='+getValue('sYm')+
			'&tipetransaksi='+getValue('tipetransaksi'),
		tujuan='keu_slave_kasbank_detail.php?proses=getMemo';
	var hutangunit=0;
        if(document.getElementById('hutangunit').checked==true){
            hutangunit=1;
        }
        pemilikHutang=document.getElementById('pemilikhutang');
        pemilikHutang=pemilikHutang.options[pemilikHutang.selectedIndex].value;
        noakunHutang=document.getElementById('noakunhutang');
        noakunHutang=noakunHutang.options[noakunHutang.selectedIndex].value;
        param+='&hutangunit='+hutangunit+'&pemilikhutang='+pemilikHutang+'&noakunhutang='+noakunHutang;
	post_response_text(tujuan, param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function findPerdin()
{
	var param='notransaksi='+getValue('sNotransaksi')+'&periode='+getValue('sYm')+
			'&tipetransaksi='+getValue('tipetransaksi')+'&kodeorg='+getValue('kodeorg')+'&pemilikhutang='+getValue('pemilikhutang'),
		tujuan='keu_slave_kasbank_detail.php?proses=getPerdin';
	var hutangunit=0;
        if(document.getElementById('hutangunit').checked==true){
            hutangunit=1;
        }
        pemilikHutang=document.getElementById('pemilikhutang');
        pemilikHutang=pemilikHutang.options[pemilikHutang.selectedIndex].value;
        noakunHutang=document.getElementById('noakunhutang');
        noakunHutang=noakunHutang.options[noakunHutang.selectedIndex].value;
        param+='&hutangunit='+hutangunit+'&pemilikhutang='+pemilikHutang+'&noakunhutang='+noakunHutang;
	post_response_text(tujuan, param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('container2').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function setPo(np,nilai,akn,ket,supp,nopo)
{
    document.getElementById('keterangan1').value=np;
//    document.getElementById('jumlah').value='';
    ds=document.getElementById('ftPrestasi_jumlah');
    ds.childNodes[0].value=nilai;
   // document.getElementById('noakun').value=akn;
    document.getElementById('keterangan2').value=nopo;
    l=document.getElementById('noakun');
    document.getElementById('nodok').value=nopo;
    
    for(a=0;a<l.length;a++)
        {
            if(l.options[a].value==akn)
                {
                    l.options[a].selected=true;
                }
        }
  l2=document.getElementById('kodesupplier');  
    for(a2=0;a2<l2.length;a2++)
        {
            if(l2.options[a2].value==supp)
                {
                    l2.options[a2].selected=true;
                }
        }
    closeDialog();
}

//function checkAll() {
//    var els = document.getElementById('invTbody').getElementsByClassName('inv-chk');
//    for(var i=0;i<els.length;i++) {
//       els[i].checked = true;
//    }
//}

function checkAll() {
    var els = document.getElementById('invTbody').getElementsByClassName('inv-chk');
    if (els[1].checked == true)
    {
        chk = false;
    }
    else
    {
        chk = true;
    }
    for(var i=0;i<els.length;i++) {
       els[i].checked = chk;
    }
}

/*
function checkAll()
{
    drt = document.getElementById('btnAllInvoice');
    if (drt.checked == true)
    {
        chk = true;
    }
    else
    {
        chk = false;
    }
    var tbl = document.getElementById("invTbody");
    var row = tbl.rows.length;
    row = row - 1;
    for (i = 0; i <= row; i++)
    {
        document.getElementById('inv_' + i).checked = chk;
    }
}
*/

function getMemo(nojurnal) {
	var param='nojurnal='+nojurnal;
    param += '&notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+getValue('hutangunit');
	param += '&pemilikhutang='+getValue('pemilikhutang')+'&tanggal='+getValue('tanggal')+'&noakunhutang='+getValue('noakunhutang');
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=addFromMemo', param, respog);
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showDetail();
                    closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getPerdin(noperdin) {
	var param='noperdin='+noperdin;
    param += '&notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+getValue('hutangunit');
	param += '&pemilikhutang='+getValue('pemilikhutang')+'&tanggal='+getValue('tanggal')+'&noakunhutang='+getValue('noakunhutang');
    tujuan='keu_slave_kasbank_detail.php';
	post_response_text(tujuan+'?'+'proses=addFromPerdin', param, respog);
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showDetail();
                    closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function add2detail() {
    var els = document.getElementById('invTbody').getElementsByClassName('inv-chk'),
        invNo = [],param='';
        var isihtng=0;
    htng=document.getElementById('hutangunit');
    if(htng.checked==true){
        var isihtng=1;
    }
    param += 'notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+isihtng;
	param += '&pemilikhutang='+getValue('pemilikhutang');


    for(var i=0;i<els.length;i++) {
        if(els[i].checked) {
            invNo.push(els[i].getAttribute('invNo'));
            param+='&invoice[]='+els[i].getAttribute('invNo');
            param+='&sisa[]='+els[i].getAttribute('sisa');
        }
    }
    
    //alert(param);return;
	if(invNo.length>0) {
		tujuan='keu_slave_kasbank_detail.php';
		post_response_text(tujuan+'?'+'proses=addFromInvoice', param, respog);
	} else {
		alert('Tidak ada No Invoice yang dipilih');
        return;
	}
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showDetail();
                    closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function add2detailAR() {
    var els = document.getElementById('invTbody').getElementsByClassName('inv-chk'),
        invNo = [],param='';
    
    param += 'notransaksi='+getValue('notransaksi')+'&kodeorg='+getValue('kodeorg');
    param += '&noakun='+getValue('noakun2a')+'&tipetransaksi='+getValue('tipetransaksi');
    param += '&kode='+getValue('kode')+'&matauang='+getValue('matauang');
    param += '&kurs='+getValue('kurs')+'&hutangunit='+getValue('hutangunit');
	param += '&pemilikhutang='+getValue('pemilikhutang')+'&jumlah='+getValue('jumlah');
	param += '&tanggal='+getValue('tanggal');
    for(var i=0;i<els.length;i++) {
        if(els[i].checked) {
            invNo.push(els[i].getAttribute('invNo'));
            param+='&invoice[]='+els[i].getAttribute('invNo');
            param+='&sisa[]='+els[i].getAttribute('sisa');
        }
    }
    
    if(invNo.length>0) {
		tujuan='keu_slave_kasbank_detail.php';
		post_response_text(tujuan+'?'+'proses=addFromInvoiceAR', param, respog);
	} else {
		alert('Tidak ada No Invoice yang dipilih');
	}
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    showDetail();
                    closeDialog();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/**
 * searchDok
 * Search Dokumen PO / Kontrak
 */
function searchDok(ev) {
	var notransaksi = getValue('notransaksi'),
		noakun = getValue('noakun'),
		tipetransaksi = getValue('tipetransaksi'),
		nik = getValue('nik');
	if(noakun=='') {
		alert("No Akun harus dipilih");
	} else {
		param = "notransaksi="+notransaksi+"&noakun="+noakun+"&nik="+nik;
		tujuan='keu_slave_kasbank.php';
		post_response_text(tujuan+'?'+'proses=getUangMuka', param, respog);
	}
	
	function respog() {
        if(con.readyState==4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    width='310';
					height='300';
					showDialog1("Daftar Transaksi Uang Muka",con.responseText,width,height,ev);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/**
 * setNodok
 * Set NoDokumen, NIK dan Jumlah
 */
function setNodok(notransaksi,nik,jumlah) {
	setValue('nodok',notransaksi);
	setValue('nik',nik);
	getById('ftPrestasi_jumlah').firstChild.value = jumlah;
	closeDialog();
}

function cekKurs() {
	var kurs = getValue('kurs');
	if (kurs > 0) {
		document.getElementById('addHead').removeAttribute('disabled');
		if (document.getElementById('editHead')) {
			document.getElementById('editHead').removeAttribute('disabled');
		}	
	} else {
		document.getElementById('addHead').setAttribute('disabled','disabled');
		if (document.getElementById('editHead')) {
			document.getElementById('editHead').setAttribute('disabled','disabled');
		}
	}
}

function getAkun()
{
    tipetransaksi=document.getElementById('tipetransaksi').value;
	if(tipetransaksi=='K'){
		//document.getElementById('lhutangnit').innerHTML=$_SESSION['lang']['hutangnit'];
		//document.getElementById('lpemilikhutang').innerHTML=$_SESSION['lang']['pemilikhutang'];
		document.getElementById('lhutangunit').innerHTML='Hutang Unit';
		document.getElementById('lpemilikhutang').innerHTML='Pemilik Hutang';
		document.getElementById('lnoakunhutang').innerHTML='No Akun Hutang';
	}else{
		//document.getElementById('lhutangnit').innerHTML=$_SESSION['lang']['piutangnit'];
		//document.getElementById('lpemilikhutang').innerHTML=$_SESSION['lang']['pemilikpiutang'];
		document.getElementById('lhutangunit').innerHTML='Piutang Unit';
		document.getElementById('lpemilikhutang').innerHTML='Pemilik Piutang';
		document.getElementById('lnoakunhutang').innerHTML='No Akun Piutang';
	}
    param='proses=getAkun'+'&tipetransaksi='+tipetransaksi;
    tujuan='keu_slave_kasbank_kurs.php';
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
					document.getElementById('noakunhutang').innerHTML=con.responseText;  
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    } 
}
