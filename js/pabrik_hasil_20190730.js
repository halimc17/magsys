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
    var notrans = document.getElementById('sNoTrans');
    var where = '[["notransaksi","'+notrans.value+'"]]';
    
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
    
    post_response_text('pabrik_slave_hasil.php?proses=showHeadList', param, respon);
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
    
    post_response_text('pabrik_slave_hasil.php?proses=showAdd', param, respon);
}

function showEdit(num) {
    var workField = document.getElementById('workField');
    var trans = document.getElementById('notransaksi_'+num);
    var param = "numRow="+num+"&notransaksi="+trans.innerHTML;
    
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
    
    post_response_text('pabrik_slave_hasil.php?proses=showEdit', param, respon);
}

/* Manipulasi Data
 * add, edit, delete
 */
function addDataTable() {
    var param = "notransaksi="+getValue('notransaksi')+"&tanggal="+getValue('tanggal');
    param += "&kodeorg="+getValue('kodeorg')+"&kodetangki="+getValue('kodetangki');
    param += "&kuantitas="+getValue('kuantitas')+"&suhu="+getValue('suhu')+"&tinggi="+getValue('tinggi');
    param += "&cpoffa="+getValue('cpoffa');
//    param += "&cporendemen="+getValue('cporendemen')+"&cpoffa="+getValue('cpoffa');
    param += "&cpokdair="+getValue('cpokdair')+"&cpokdkot="+getValue('cpokdkot')+"&dobi="+getValue('dobi');
    param += "&kernelquantity="+getValue('kernelquantity');
//    param += "&kernelquantity="+getValue('kernelquantity')+"&kernelrendemen="+getValue('kernelrendemen');
    param += "&kernelkdair="+getValue('kernelkdair')+"&kernelkdkot="+getValue('kernelkdkot');
    param += "&kernelffa="+getValue('kernelffa');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    alert('Added Data Header');
                    defaultList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('pabrik_slave_hasil.php?proses=add', param, respon);
}

function editDataTable() {
    var param = "notransaksi="+getValue('notransaksi')+"&tanggal="+getValue('tanggal');
    param += "&kodeorg="+getValue('kodeorg')+"&kodetangki="+getValue('kodetangki');
    param += "&kuantitas="+getValue('kuantitas')+"&suhu="+getValue('suhu')+"&tinggi="+getValue('tinggi');
    param += "&cpoffa="+getValue('cpoffa');
//    param += "&cporendemen="+getValue('cporendemen')+"&cpoffa="+getValue('cpoffa');
    param += "&cpokdair="+getValue('cpokdair')+"&cpokdkot="+getValue('cpokdkot')+"&dobi="+getValue('dobi');
    param += "&kernelquantity="+getValue('kernelquantity');
//    param += "&kernelquantity="+getValue('kernelquantity')+"&kernelrendemen="+getValue('kernelrendemen');
    param += "&kernelkdair="+getValue('kernelkdair')+"&kernelkdkot="+getValue('kernelkdkot');
    param += "&kernelffa="+getValue('kernelffa');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
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
    
    post_response_text('pabrik_slave_hasil.php?proses=edit', param, respon);
}

function deleteData(num) {
    var notrans = document.getElementById('notransaksi_'+num).innerHTML;
    var param = "notransaksi="+notrans;
    
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
    
    post_response_text('pabrik_slave_hasil.php?proses=delete', param, respon);
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

function printPDF(ev) {
    // Prep Param
    param = "proses=pdf";
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='pabrik_slave_hasil_print.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
function getVol(){
	kdTangki=document.getElementById('kodetangki');
	kdTangki=kdTangki.options[kdTangki.selectedIndex].value;
	kdPabrik=document.getElementById('kodeorg');
	kdPabrik=kdPabrik.options[kdPabrik.selectedIndex].value;
	sh=document.getElementById('suhu').value;
	tnggi=document.getElementById('tinggi').value;
	param='kodetangki='+kdTangki+'&kodeorg='+kdPabrik+'&suhu='+sh+'&tinggi='+tnggi;
	post_response_text('pabrik_slave_hasil.php?proses=getVol', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    document.getElementById('kuantitas').value=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    
}