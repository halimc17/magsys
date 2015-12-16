
function cariNama(){
	nama=document.getElementById('nama').value;
	param='nama='+nama;
	tujuan='latihan_slave_herry_latihan1.php';
	post_response_text(tujuan, param, respog);
		function respog()
			{
			 if (con.readyState = 4) 
				{
			   		if (con.status = 200)
					 {
				
						busy_off();
			   			if (!isSaveResponse(co.responseText)) 
						{
							alert('ERROR TRANSACTION,\n' +con.responseText);
			   }
			   else {

				 document.getElementById('container') .innerHTML=con.responseText;
			         }
			   }
			   else {
					busy_off();
					error_catch (con.status);
				}
			   }
			}
		}