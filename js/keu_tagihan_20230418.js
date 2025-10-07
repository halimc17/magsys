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

/* Search
 * Filtering Data
 */
function searchTrans() {
    var notrans = document.getElementById('sNoTrans'),
		jenis = document.getElementById('sJenis'),
		where = '[["'+jenis.options[jenis.selectedIndex].value+'","'+notrans.value+'"]]';
    
    goToPages(1,showPerPage,where);
}

/* Paging
 * Paging Data
 */
function defaultList() {
    goToPages(1,showPerPage);
}

function goToPages(page,shows,where) {
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
                    alert(con.responseText);
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
    post_response_text('keu_slave_tagihan.php?proses=showHeadList', param, respon);
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
                    alert(con.responseText);
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
	post_response_text('keu_slave_tagihan.php?proses=showAdd', param, respon);
}

function showEditFromAdd() {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('noinvoice');
    var param = "noinvoice="+trans.value;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
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
    post_response_text('keu_slave_tagihan.php?proses=showEdit', param, respon);
}

function showEdit(num) {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('noinvoice_'+num);
    var param = "numRow="+num+"&noinvoice="+trans.innerHTML;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
					//showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_tagihan.php?proses=showEdit', param, respon);
}

/* Manipulasi Data
 * add, edit, delete
 */
function addDataTable() {
    if(getValue('nopo')=='') {
        alert('No PO harus dipilih');
        return;
    }
    var param = "noinvoice="+getValue('noinvoice')+"&noinvoicesupplier="+getValue('noinvoicesupplier')+"&tanggal="+getValue('tanggal')+"&tipeinvoice="+getValue('tipeinvoice');
    param += "&nopo="+getValue('nopo')+"&keterangan="+getValue('keterangan')+"&nilaiinvoice="+getValue('nilaiinvoice');
    param += "&jatuhtempo="+getValue('jatuhtempo')+"&nofp="+getValue('nofp');
    param += "&noakun="+getValue('noakun')+"&uangmuka="+getValue('uangmuka');
    param += "&matauang="+getValue('matauang')+"&kodeorg="+getValue('kodeorg');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    //alert('Added Data Header');
                    //showEditFromAdd();
					defaultList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_tagihan.php?proses=add', param, respon);
}

function editDataTable() {
    var param = "noinvoice="+getValue('noinvoice')+"&noinvoicesupplier="+getValue('noinvoicesupplier')+"&tanggal="+getValue('tanggal')+"&tipeinvoice="+getValue('tipeinvoice');
    param += "&nopo="+getValue('nopo')+"&keterangan="+getValue('keterangan')+"&nilaiinvoice="+getValue('nilaiinvoice');
    param += "&jatuhtempo="+getValue('jatuhtempo')+"&nofp="+getValue('nofp');
    param += "&noakun="+getValue('noakun')+"&uangmuka="+getValue('uangmuka');
    param += "&matauang="+getValue('matauang')+"&kodeorg="+getValue('kodeorg');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    defaultList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_tagihan.php?proses=edit', param, respon);
}

/*
 * Detail
 */

function showDetail() {
    var detailField = document.getElementById('detailField');
    var notrans = document.getElementById('noinvoice').value;
    var param = "noinvoice="+notrans+"&nopo="+getValue('nopo');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    detailField.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_tagihan_detail.php?proses=showDetail', param, respon);
}

function deleteData(num) {
    var notrans = document.getElementById('noinvoice_'+num).innerHTML;
    var param = "noinvoice="+notrans;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
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
	if(confirm("Deleting, are you sure ?!!"))
    post_response_text('keu_slave_tagihan.php?proses=delete', param, respon);
}

function printPDF(ev) {
    // Prep Param
    param = "proses=pdf";
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_tagihan_print.php?"+param+"'></iframe>",'800','400',ev);
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

function updPO() {
    document.getElementById('nopo').value='';
    document.getElementById('supplier').value='';
	document.getElementById('matauang').value='IDR';
	document.getElementById('kurs').value='1';
}

function updInvoice() {
    var invoice = document.getElementById('nilaiinvoice');
    var param = "nopo="+getValue('nopo');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    if(con.responseText!='') {
                        invoice.value = con.responseText;
                        invoice.value = _formatted(invoice);
                        invoice.setAttribute('disabled','disabled');
                    } else {
                        invoice.value = 0;
                        invoice.removeAttribute('disabled');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('keu_slave_tagihan.php?proses=updInvoice', param, respon);
}

//jamhari
function searchNopo(title,ev,langCari)
{
	isi=document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].value;
	tanggal=document.getElementById('tanggal').value;
	if(isi=='po' && tanggal=='') {
		alert('Tanggal terima harus diisi terlebih dahulu');return;
	}
	if(isi=='po') {
		tipe='PO';
		doc	='No. PO';
	} else if(isi=='sj') {
		tipe='Surat Jalan';
		doc	='No. Surat Jalan';
	} else if(isi=='nm') {
		tipe='Konosemen';
		doc	='No. Konosemen';
    } else if(isi=='bykrm') {
		tipe='Biaya Kirim';
		doc	='No. PO';
    } else if(isi=='trading') {
		tipe='Trading';
		doc	='No. Kontrak Ext';
	} else {
		tipe='SPK';
		doc='No. SPK';
	}
	content = "<fieldset><legend>"+langCari+" "+tipe+"</legend>"+langCari+
		" "+doc+"<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findNopo()>Find</button></fieldset><div id=container2></div>";
	content=content+"<input type='hidden' id='jnsInvoice' value="+isi+">";
	width='500';
	height='400';
	showDialog1(title+tipe,content,width,height,ev);
    //findNopo();
}

function findNopo()
{
	txt=trim(document.getElementById('no_brg').value);
	
	jnsInvoice=document.getElementById('tipeinvoice').value;
	tanggal=document.getElementById('tanggal').value;
	
	//document.getElementById('tipeinvoice').disabled=true;
	param='txtfind='+txt+'&jnsInvoice='+jnsInvoice+'&tanggal='+tanggal;
	tujuan='keu_slave_tagihan.php';
	post_response_text(tujuan+'?'+'proses=getPo', param, respog);
	
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
					//alert(con.responseText);
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

function setPo(np,nilai,jns,ppn,namasupplier,matauang,kurs)
{
    document.getElementById('nopo').value=np;
    document.getElementById('nilaiinvoice').value=nilai;
    document.getElementById('tipeinvoice').disabled=false;
	document.getElementById('supplier').value=namasupplier;
	if(typeof matauang!='undefined') {
		document.getElementById('matauang').value=matauang;
	}
	if(typeof kurs!='undefined') {
		document.getElementById('kurs').value=kurs;
	}
    closeDialog();
}

function postingData(row)
{
    noinvoice=document.getElementById('noinvoice_'+row).innerHTML;
    nopo=document.getElementById('nopo_'+row).innerHTML;
    nofp=document.getElementById('nofp_'+row).innerHTML;
    param='noinvoice='+noinvoice+'&nopo='+nopo+'&nofp='+nofp;
	tujuan='keu_slave_tagihanPosting.php';
	if(confirm('Anda yakin dokumen telah lengkap..?'))
		post_response_text(tujuan+'?'+'proses=getPo', param, respog);
	
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
					//alert(con.responseText);
					x=document.getElementById('tr_'+row);
					//x.cells[6].innerHTML=''
					x.cells[13].innerHTML="<img class='zImgBtn' title=Lengkap' src='images/skyblue/posted.png'>";
					x.cells[12].innerHTML='';
					x.cells[11].innerHTML=''
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}    
}

function detailPDF(numRow,ev) {
    // Prep Param
    var noinvoice = document.getElementById('noinvoice_'+numRow).getAttribute('value');
    param = "proses=pdf&noinvoice="+noinvoice;
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_tagihan_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
