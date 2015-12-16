function tambahBarang(title,ev)
{
    
    content= "<div id=formBarang style=\"height:250px;width:350;overflow:scroll;\"></div>";
    title='Add Material';
    height='250';
    width='350';
    showDialog1(title,content,width,height,ev);	
    getListBarang();
}



function getListBarang()
{
    
    param='proses=getListBarang';
    tujuan = 'log_slave_2pemakaianbarang.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formBarang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}

function cariListBarang()
{
    namaBarangCari=document.getElementById('namaBarangCari').value;
    param='proses=getListBarang'+'&namaBarangCari='+namaBarangCari;
  
    tujuan = 'log_slave_2pemakaianbarang.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formBarang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}



function moveDataBarang(kodebarang,namabarang,satuanbarang)
{
    document.getElementById('barang').value=kodebarang;
    document.getElementById('namaBarang').value=namabarang;
    document.getElementById('listCariBarang').style.display='none';
    closeDialog();
}



function batalLaporan()
{
    document.getElementById('unit').value='';
    document.getElementById('barang').value='';
    document.getElementById('namaBarang').value='';
    document.getElementById('tgl2').value='';	
    document.getElementById('tgl1').value='';
    document.getElementById('printContainer').innerHTML='';	
}