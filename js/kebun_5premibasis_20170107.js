// JavaScript Document
function cariBast(num)
{
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'kebun_slave_5premibasis.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }
}

function getBasis(){
	kelaspohon = document.getElementById('kelaspohon').value;
	param='method=getBasis&kelaspohon='+kelaspohon;
    tujuan = 'kebun_slave_5premibasis.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					document.getElementById('basis').value=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }
}


function simpan(fileTarget,passParam) {
    param='';
    var passP = passParam.split('##');
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    loadData();
                    cancelIsi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);
}

function loadData()
{
    param='method=loadData';
    tujuan='kebun_slave_5premibasis';
    post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function fillField(afd,jenispremi,kelaspohon,basis,premilebih,premilibur,premiliburbasis,topografi,premitopografi,premibrondolan)
{
	setValue('afd',afd);
	setValue('jenispremi',jenispremi);
	setValue('kelaspohon',kelaspohon);
	setValue('topografi',topografi);
	
	document.getElementById('afd').disabled = true;
	document.getElementById('jenispremi').disabled = true;
	document.getElementById('kelaspohon').disabled = true;
	document.getElementById('topografi').disabled = true;
	
    document.getElementById('basis').value=basis;
    document.getElementById('premilebih').value=premilebih;
    
    document.getElementById('premilibur').value=premilibur;
	document.getElementById('premiliburcapaibasis').value=premiliburbasis;
    document.getElementById('topografi').value=topografi;
    document.getElementById('premitopografi').value=premitopografi;
	document.getElementById('premibrondolan').value=premibrondolan;
    
    document.getElementById('method').value="update";
}

function del(afd,jenispremi,kelaspohon,topografi)
{
    param='afd='+afd+'&jenispremi='+jenispremi+'&kelaspohon='+kelaspohon+'&topografi='+topografi+'&method=deletedata';
    tujuan='kebun_slave_5premibasis.php';
    if(confirm("Are You Sure Want Delete Data?"))
        post_response_text(tujuan, param, respog);
				
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    loadData();
					cancelIsi();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

function cancelIsi()
{
    document.getElementById('afd').selectedIndex=0;
	document.getElementById('afd').disabled = false;
    document.getElementById('jenispremi').selectedIndex=0;
	document.getElementById('jenispremi').disabled = false;
	document.getElementById('kelaspohon').selectedIndex=0;
	document.getElementById('kelaspohon').disabled = false;
    document.getElementById('basis').value=0;
    document.getElementById('premilebih').value=0;
    document.getElementById('premilibur').value=0;
	document.getElementById('premiliburcapaibasis').value=0;
    document.getElementById('topografi').selectedIndex=0;
	document.getElementById('topografi').disabled = false;
    document.getElementById('premitopografi').value=0;
    document.getElementById('method').value="insert";
}






