/**
 * @author {nangkoel@gmail.com}
 * jakarta indonesia
 */
//ffa
function getData(){
	kodeorg=document.getElementById('kodeorg').value;
    tanggal=document.getElementById('tanggal').value;
    param='kodeorg='+kodeorg+'&method=getData'+'&tanggal='+tanggal;
    tujuan='pabrik_slave_datapress.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					arr=con.responseText.split("###");
					document.getElementById('airkemarin').value=arr[0];
					hitungsisaair();
					//document.getElementById('airkemarin').value=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function simpanData(){
	kodeorg 	=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	tanggal  	=document.getElementById('tanggal').value;

	tekpressp1	=document.getElementById('tekpressp1').value;
	tekpressp2	=document.getElementById('tekpressp2').value;
	tekpressp3	=document.getElementById('tekpressp3').value;
	tekpressp4	=document.getElementById('tekpressp4').value;
	suhud1		=document.getElementById('suhud1').value;
	suhud2		=document.getElementById('suhud2').value;
	suhud3		=document.getElementById('suhud3').value;
	suhud4		=document.getElementById('suhud4').value;
	jampressp1	=document.getElementById('jampressp1').value;
	jampressp2	=document.getElementById('jampressp2').value;
	jampressp3	=document.getElementById('jampressp3').value;
	jampressp4	=document.getElementById('jampressp4').value;

	airkemarin	=document.getElementById('airkemarin').value;
	airclarifier=document.getElementById('airclarifier').value;
	airboiler	=document.getElementById('airboiler').value;
	airproduksi	=document.getElementById('airproduksi').value;	
	airpembersihan=document.getElementById('airpembersihan').value;	
	airdomestik	=document.getElementById('airdomestik').value;	
	airsisa	=document.getElementById('airsisa').value;
	if(kodeorg=='' ||  tanggal==''  || airsisa=='' || airsisa==null){
		alert('All fields are required');
	}else{
		param='kodeorg='+kodeorg+'&tanggal='+tanggal;
		param+='&tekpressp1='+tekpressp1+'&tekpressp2='+tekpressp2+'&tekpressp3='+tekpressp3+'&tekpressp4='+tekpressp4;
		param+='&suhud1='+suhud1+'&suhud2='+suhud2+'&suhud3='+suhud3+'&suhud4='+suhud4;
		param+='&jampressp1='+jampressp1+'&jampressp2='+jampressp2+'&jampressp3='+jampressp3+'&jampressp4='+jampressp4;
		param+='&airclarifier='+airclarifier+'&airboiler='+airboiler;
		param+='&airproduksi='+airproduksi+'&airpembersihan='+airpembersihan;
		param+='&airdomestik='+airdomestik+'&airsisa='+airsisa;
		tujuan='pabrik_slave_save_datapress.php';
		post_response_text(tujuan, param, respog);		
	}

	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
					bersihkanForm();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 		
}

function fillfield(kodeorg,tanggal,tekpressp1,tekpressp2,tekpressp3,tekpressp4,suhud1,suhud2,suhud3,suhud4,jampressp1,jampressp2,jampressp3,jampressp4
		,airkemarin,airclarifier,airboiler,airproduksi,airpembersihan,airdomestik,airsisa){
        document.getElementById('kodeorg').value=kodeorg;
        document.getElementById('tanggal').value=tanggal;

        document.getElementById('tekpressp1').value=tekpressp1;
        document.getElementById('tekpressp2').value=tekpressp2;
        document.getElementById('tekpressp3').value=tekpressp3;
        document.getElementById('tekpressp4').value=tekpressp4;
        document.getElementById('suhud1').value=suhud1;
        document.getElementById('suhud2').value=suhud2;
        document.getElementById('suhud3').value=suhud3;
        document.getElementById('suhud4').value=suhud4;
        document.getElementById('jampressp1').value=jampressp1;
        document.getElementById('jampressp2').value=jampressp2;
        document.getElementById('jampressp3').value=jampressp3;
        document.getElementById('jampressp4').value=jampressp4;

        document.getElementById('airkemarin').value=airkemarin;
        document.getElementById('airclarifier').value=airclarifier;
        document.getElementById('airboiler').value=airboiler;
        document.getElementById('airproduksi').value=airproduksi;
        document.getElementById('airpembersihan').value=airpembersihan;
        document.getElementById('airdomestik').value=airdomestik;
        document.getElementById('airsisa').value=airsisa;
}

function bersihkanForm()
{
        document.getElementById('tanggal').value='';

        document.getElementById('tekpressp1').value=0;
        document.getElementById('tekpressp2').value=0;
        document.getElementById('tekpressp3').value=0;
        document.getElementById('tekpressp4').value=0;
        document.getElementById('suhud1').value=0;
        document.getElementById('suhud2').value=0;
        document.getElementById('suhud3').value=0;
        document.getElementById('suhud4').value=0;
        document.getElementById('jampressp1').value=0;
        document.getElementById('jampressp2').value=0;
        document.getElementById('jampressp3').value=0;
        document.getElementById('jampressp4').value=0;

        document.getElementById('airkemarin').value=0;
        document.getElementById('airclarifier').value=0;
        document.getElementById('airboiler').value=0;
        document.getElementById('airproduksi').value=0;
        document.getElementById('airpembersihan').value=0;
        document.getElementById('airdomestik').value=0;
        document.getElementById('airsisa').value=0;
}

function deldata(kodeorg,tanggal)
{
                param='kodeorg='+kodeorg+'&tanggal='+tanggal;
                param+='&del=true';
                if (confirm('Delete ..?')) {
                        tujuan = 'pabrik_slave_save_datapress.php';
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
                                                else {;
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

function hitungsisaair()
{
        airkemarin=parseFloat(document.getElementById('airkemarin').value);
        airclarifier=parseFloat(document.getElementById('airclarifier').value);
        airboiler=parseFloat(document.getElementById('airboiler').value);
        airproduksi=parseFloat(document.getElementById('airproduksi').value);
        airpembersihan=parseFloat(document.getElementById('airpembersihan').value);
        airdomestik=parseFloat(document.getElementById('airdomestik').value);
        airsisa=(airkemarin+airclarifier)-(airboiler+airproduksi+airpembersihan+airdomestik);
        if (airsisa >= 0) {
                document.getElementById('airsisa').value = airsisa;
        }
        else
        {
                //alert('Invalid character');
                document.getElementById('airsisa').value=0;
        }	
}

function showDetail(tgl,kdorg,ev)
{
        title="Data Detail";
        content="<fieldset><legend>Unit : "+kdorg+", Date "+tgl+"</legend><div id=contDetail style='overflow:auto; width:890px; height:320px;' ></div></fieldset>";
        width='920';
        height='370';
        showDialog1(title,content,width,height,ev);	
}

function previewDetail(tgl,kdorg,ev)
{
        showDetail(tgl,kdorg,ev);
        param='kdorg='+kdorg+'&method=getDetailPA'+'&tgl='+tgl;
        tujuan='pabrik_slave_datapress.php';
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
                                                        document.getElementById('contDetail').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  

}
