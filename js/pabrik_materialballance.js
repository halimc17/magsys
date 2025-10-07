/*
 * Material Ballance
 */
  
function loadData(num){
	kodeorg =document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param='method=loadData'+'&kodeorg='+kodeorg;
	param+='&page='+num;
	tujuan='pabrik_materialballance_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('page').value=num;
					document.getElementById('container').innerHTML=con.responseText;
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
	kodeblok 	=document.getElementById('kodeblok').options[document.getElementById('kodeblok').selectedIndex].value;
	tanggal  	=document.getElementById('tanggal').value;
	//UnRipe
	berattbs_ur2=document.getElementById('berattbs_ur2').value;
	berattbs_ur4=document.getElementById('berattbs_ur4').value;
	berattbs_ur5=document.getElementById('berattbs_ur5').value;
	tbsrebus_ur2=document.getElementById('tbsrebus_ur2').value;
	tbsrebus_ur4=document.getElementById('tbsrebus_ur4').value;
	tbsrebus_ur5=document.getElementById('tbsrebus_ur5').value;

	condensate_ur2=document.getElementById('condensate_ur2').value;
	condensate_ur4=document.getElementById('condensate_ur4').value;
	condensate_ur5=document.getElementById('condensate_ur5').value;
	brondolluar_ur2=document.getElementById('brondolluar_ur2').value;
	brondolluar_ur4=document.getElementById('brondolluar_ur4').value;
	brondolluar_ur5=document.getElementById('brondolluar_ur5').value;
	brondoldalam_ur2=document.getElementById('brondoldalam_ur2').value;
	brondoldalam_ur4=document.getElementById('brondoldalam_ur4').value;
	brondoldalam_ur5=document.getElementById('brondoldalam_ur5').value;
	abn_ur2=document.getElementById('abn_ur2').value;
	abn_ur4=document.getElementById('abn_ur4').value;
	abn_ur5=document.getElementById('abn_ur5').value;
	calix_ur2=document.getElementById('calix_ur2').value;
	calix_ur4=document.getElementById('calix_ur4').value;
	calix_ur5=document.getElementById('calix_ur5').value;
	jangkos_ur2=document.getElementById('jangkos_ur2').value;
	jangkos_ur4=document.getElementById('jangkos_ur4').value;
	jangkos_ur5=document.getElementById('jangkos_ur5').value;
	totaltbs_ur2=document.getElementById('totaltbs_ur2').value;
	totaltbs_ur4=document.getElementById('totaltbs_ur4').value;
	totaltbs_ur5=document.getElementById('totaltbs_ur5').value;
	sampel_ur2=document.getElementById('sampel_ur2').value;
	sampel_ur4=document.getElementById('sampel_ur4').value;
	sampel_ur5=document.getElementById('sampel_ur5').value;

	brondolan_ur2=document.getElementById('brondolan_ur2').value;
	brondolan_ur4=document.getElementById('brondolan_ur4').value;
	brondolan_ur5=document.getElementById('brondolan_ur5').value;
	evaporation_ur2=document.getElementById('evaporation_ur2').value;
	evaporation_ur4=document.getElementById('evaporation_ur4').value;
	evaporation_ur5=document.getElementById('evaporation_ur5').value;
	brondoldry_ur2=document.getElementById('brondoldry_ur2').value;
	brondoldry_ur4=document.getElementById('brondoldry_ur4').value;
	brondoldry_ur5=document.getElementById('brondoldry_ur5').value;
	fiber_ur2=document.getElementById('fiber_ur2').value;
	fiber_ur4=document.getElementById('fiber_ur4').value;
	fiber_ur5=document.getElementById('fiber_ur5').value;
	nut_ur2=document.getElementById('nut_ur2').value;
	nut_ur4=document.getElementById('nut_ur4').value;
	nut_ur5=document.getElementById('nut_ur5').value;
	shell_ur2=document.getElementById('shell_ur2').value;
	shell_ur4=document.getElementById('shell_ur4').value;
	shell_ur5=document.getElementById('shell_ur5').value;
	kernel_ur2=document.getElementById('kernel_ur2').value;
	kernel_ur4=document.getElementById('kernel_ur4').value;
	kernel_ur5=document.getElementById('kernel_ur5').value;
	kerneldry_ur2=document.getElementById('kerneldry_ur2').value;
	kerneldry_ur4=document.getElementById('kerneldry_ur4').value;
	kerneldry_ur5=document.getElementById('kerneldry_ur5').value;
	lossestbs_ur2=document.getElementById('lossestbs_ur2').value;
	lossestbs_ur4=document.getElementById('lossestbs_ur4').value;
	lossestbs_ur5=document.getElementById('lossestbs_ur5').value;
	sttotal_ur2=document.getElementById('sttotal_ur2').value;
	sttotal_ur4=document.getElementById('sttotal_ur4').value;
	sttotal_ur5=document.getElementById('sttotal_ur5').value;

	oilinfiber_ur2=document.getElementById('oilinfiber_ur2').value;
	oilinfiber_ur4=document.getElementById('oilinfiber_ur4').value;
	oilinfiber_ur5=document.getElementById('oilinfiber_ur5').value;
	oilinshell_ur2=document.getElementById('oilinshell_ur2').value;
	oilinshell_ur4=document.getElementById('oilinshell_ur4').value;
	oilinshell_ur5=document.getElementById('oilinshell_ur5').value;
	totaloil_ur2=document.getElementById('totaloil_ur2').value;
	totaloil_ur4=document.getElementById('totaloil_ur4').value;
	totaloil_ur5=document.getElementById('totaloil_ur5').value;
	lossesoil_ur2=document.getElementById('lossesoil_ur2').value;
	lossesoil_ur4=document.getElementById('lossesoil_ur4').value;
	lossesoil_ur5=document.getElementById('lossesoil_ur5').value;
	gttotal_ur2=document.getElementById('gttotal_ur2').value;
	gttotal_ur4=document.getElementById('gttotal_ur4').value;
	gttotal_ur5=document.getElementById('gttotal_ur5').value;
	hasil_ur2=document.getElementById('hasil_ur2').value;
	hasil_ur4=document.getElementById('hasil_ur4').value;
	hasil_ur5=document.getElementById('hasil_ur5').value;

	//Normal Ripe
	berattbs_nr2=document.getElementById('berattbs_nr2').value;
	berattbs_nr4=document.getElementById('berattbs_nr4').value;
	berattbs_nr5=document.getElementById('berattbs_nr5').value;
	tbsrebus_nr2=document.getElementById('tbsrebus_nr2').value;
	tbsrebus_nr4=document.getElementById('tbsrebus_nr4').value;
	tbsrebus_nr5=document.getElementById('tbsrebus_nr5').value;

	condensate_nr2=document.getElementById('condensate_nr2').value;
	condensate_nr4=document.getElementById('condensate_nr4').value;
	condensate_nr5=document.getElementById('condensate_nr5').value;
	brondolluar_nr2=document.getElementById('brondolluar_nr2').value;
	brondolluar_nr4=document.getElementById('brondolluar_nr4').value;
	brondolluar_nr5=document.getElementById('brondolluar_nr5').value;
	brondoldalam_nr2=document.getElementById('brondoldalam_nr2').value;
	brondoldalam_nr4=document.getElementById('brondoldalam_nr4').value;
	brondoldalam_nr5=document.getElementById('brondoldalam_nr5').value;
	abn_nr2=document.getElementById('abn_nr2').value;
	abn_nr4=document.getElementById('abn_nr4').value;
	abn_nr5=document.getElementById('abn_nr5').value;
	calix_nr2=document.getElementById('calix_nr2').value;
	calix_nr4=document.getElementById('calix_nr4').value;
	calix_nr5=document.getElementById('calix_nr5').value;
	jangkos_nr2=document.getElementById('jangkos_nr2').value;
	jangkos_nr4=document.getElementById('jangkos_nr4').value;
	jangkos_nr5=document.getElementById('jangkos_nr5').value;
	totaltbs_nr2=document.getElementById('totaltbs_nr2').value;
	totaltbs_nr4=document.getElementById('totaltbs_nr4').value;
	totaltbs_nr5=document.getElementById('totaltbs_nr5').value;
	sampel_nr2=document.getElementById('sampel_nr2').value;
	sampel_nr4=document.getElementById('sampel_nr4').value;
	sampel_nr5=document.getElementById('sampel_nr5').value;

	brondolan_nr2=document.getElementById('brondolan_nr2').value;
	brondolan_nr4=document.getElementById('brondolan_nr4').value;
	brondolan_nr5=document.getElementById('brondolan_nr5').value;
	evaporation_nr2=document.getElementById('evaporation_nr2').value;
	evaporation_nr4=document.getElementById('evaporation_nr4').value;
	evaporation_nr5=document.getElementById('evaporation_nr5').value;
	brondoldry_nr2=document.getElementById('brondoldry_nr2').value;
	brondoldry_nr4=document.getElementById('brondoldry_nr4').value;
	brondoldry_nr5=document.getElementById('brondoldry_nr5').value;
	fiber_nr2=document.getElementById('fiber_nr2').value;
	fiber_nr4=document.getElementById('fiber_nr4').value;
	fiber_nr5=document.getElementById('fiber_nr5').value;
	nut_nr2=document.getElementById('nut_nr2').value;
	nut_nr4=document.getElementById('nut_nr4').value;
	nut_nr5=document.getElementById('nut_nr5').value;
	shell_nr2=document.getElementById('shell_nr2').value;
	shell_nr4=document.getElementById('shell_nr4').value;
	shell_nr5=document.getElementById('shell_nr5').value;
	kernel_nr2=document.getElementById('kernel_nr2').value;
	kernel_nr4=document.getElementById('kernel_nr4').value;
	kernel_nr5=document.getElementById('kernel_nr5').value;
	kerneldry_nr2=document.getElementById('kerneldry_nr2').value;
	kerneldry_nr4=document.getElementById('kerneldry_nr4').value;
	kerneldry_nr5=document.getElementById('kerneldry_nr5').value;
	lossestbs_nr2=document.getElementById('lossestbs_nr2').value;
	lossestbs_nr4=document.getElementById('lossestbs_nr4').value;
	lossestbs_nr5=document.getElementById('lossestbs_nr5').value;
	sttotal_nr2=document.getElementById('sttotal_nr2').value;
	sttotal_nr4=document.getElementById('sttotal_nr4').value;
	sttotal_nr5=document.getElementById('sttotal_nr5').value;

	oilinfiber_nr2=document.getElementById('oilinfiber_nr2').value;
	oilinfiber_nr4=document.getElementById('oilinfiber_nr4').value;
	oilinfiber_nr5=document.getElementById('oilinfiber_nr5').value;
	oilinshell_nr2=document.getElementById('oilinshell_nr2').value;
	oilinshell_nr4=document.getElementById('oilinshell_nr4').value;
	oilinshell_nr5=document.getElementById('oilinshell_nr5').value;
	totaloil_nr2=document.getElementById('totaloil_nr2').value;
	totaloil_nr4=document.getElementById('totaloil_nr4').value;
	totaloil_nr5=document.getElementById('totaloil_nr5').value;
	lossesoil_nr2=document.getElementById('lossesoil_nr2').value;
	lossesoil_nr4=document.getElementById('lossesoil_nr4').value;
	lossesoil_nr5=document.getElementById('lossesoil_nr5').value;
	gttotal_nr2=document.getElementById('gttotal_nr2').value;
	gttotal_nr4=document.getElementById('gttotal_nr4').value;
	gttotal_nr5=document.getElementById('gttotal_nr5').value;
	hasil_nr2=document.getElementById('hasil_nr2').value;
	hasil_nr4=document.getElementById('hasil_nr4').value;
	hasil_nr5=document.getElementById('hasil_nr5').value;

	//Over Ripe
	berattbs_or2=document.getElementById('berattbs_or2').value;
	berattbs_or4=document.getElementById('berattbs_or4').value;
	berattbs_or5=document.getElementById('berattbs_or5').value;
	tbsrebus_or2=document.getElementById('tbsrebus_or2').value;
	tbsrebus_or4=document.getElementById('tbsrebus_or4').value;
	tbsrebus_or5=document.getElementById('tbsrebus_or5').value;

	condensate_or2=document.getElementById('condensate_or2').value;
	condensate_or4=document.getElementById('condensate_or4').value;
	condensate_or5=document.getElementById('condensate_or5').value;
	brondolluar_or2=document.getElementById('brondolluar_or2').value;
	brondolluar_or4=document.getElementById('brondolluar_or4').value;
	brondolluar_or5=document.getElementById('brondolluar_or5').value;
	brondoldalam_or2=document.getElementById('brondoldalam_or2').value;
	brondoldalam_or4=document.getElementById('brondoldalam_or4').value;
	brondoldalam_or5=document.getElementById('brondoldalam_or5').value;
	abn_or2=document.getElementById('abn_or2').value;
	abn_or4=document.getElementById('abn_or4').value;
	abn_or5=document.getElementById('abn_or5').value;
	calix_or2=document.getElementById('calix_or2').value;
	calix_or4=document.getElementById('calix_or4').value;
	calix_or5=document.getElementById('calix_or5').value;
	jangkos_or2=document.getElementById('jangkos_or2').value;
	jangkos_or4=document.getElementById('jangkos_or4').value;
	jangkos_or5=document.getElementById('jangkos_or5').value;
	totaltbs_or2=document.getElementById('totaltbs_or2').value;
	totaltbs_or4=document.getElementById('totaltbs_or4').value;
	totaltbs_or5=document.getElementById('totaltbs_or5').value;
	sampel_or2=document.getElementById('sampel_or2').value;
	sampel_or4=document.getElementById('sampel_or4').value;
	sampel_or5=document.getElementById('sampel_or5').value;

	brondolan_or2=document.getElementById('brondolan_or2').value;
	brondolan_or4=document.getElementById('brondolan_or4').value;
	brondolan_or5=document.getElementById('brondolan_or5').value;
	evaporation_or2=document.getElementById('evaporation_or2').value;
	evaporation_or4=document.getElementById('evaporation_or4').value;
	evaporation_or5=document.getElementById('evaporation_or5').value;
	brondoldry_or2=document.getElementById('brondoldry_or2').value;
	brondoldry_or4=document.getElementById('brondoldry_or4').value;
	brondoldry_or5=document.getElementById('brondoldry_or5').value;
	fiber_or2=document.getElementById('fiber_or2').value;
	fiber_or4=document.getElementById('fiber_or4').value;
	fiber_or5=document.getElementById('fiber_or5').value;
	nut_or2=document.getElementById('nut_or2').value;
	nut_or4=document.getElementById('nut_or4').value;
	nut_or5=document.getElementById('nut_or5').value;
	shell_or2=document.getElementById('shell_or2').value;
	shell_or4=document.getElementById('shell_or4').value;
	shell_or5=document.getElementById('shell_or5').value;
	kernel_or2=document.getElementById('kernel_or2').value;
	kernel_or4=document.getElementById('kernel_or4').value;
	kernel_or5=document.getElementById('kernel_or5').value;
	kerneldry_or2=document.getElementById('kerneldry_or2').value;
	kerneldry_or4=document.getElementById('kerneldry_or4').value;
	kerneldry_or5=document.getElementById('kerneldry_or5').value;
	lossestbs_or2=document.getElementById('lossestbs_or2').value;
	lossestbs_or4=document.getElementById('lossestbs_or4').value;
	lossestbs_or5=document.getElementById('lossestbs_or5').value;
	sttotal_or2=document.getElementById('sttotal_or2').value;
	sttotal_or4=document.getElementById('sttotal_or4').value;
	sttotal_or5=document.getElementById('sttotal_or5').value;

	oilinfiber_or2=document.getElementById('oilinfiber_or2').value;
	oilinfiber_or4=document.getElementById('oilinfiber_or4').value;
	oilinfiber_or5=document.getElementById('oilinfiber_or5').value;
	oilinshell_or2=document.getElementById('oilinshell_or2').value;
	oilinshell_or4=document.getElementById('oilinshell_or4').value;
	oilinshell_or5=document.getElementById('oilinshell_or5').value;
	totaloil_or2=document.getElementById('totaloil_or2').value;
	totaloil_or4=document.getElementById('totaloil_or4').value;
	totaloil_or5=document.getElementById('totaloil_or5').value;
	lossesoil_or2=document.getElementById('lossesoil_or2').value;
	lossesoil_or4=document.getElementById('lossesoil_or4').value;
	lossesoil_or5=document.getElementById('lossesoil_or5').value;
	gttotal_or2=document.getElementById('gttotal_or2').value;
	gttotal_or4=document.getElementById('gttotal_or4').value;
	gttotal_or5=document.getElementById('gttotal_or5').value;
	hasil_or2=document.getElementById('hasil_or2').value;
	hasil_or4=document.getElementById('hasil_or4').value;
	hasil_or5=document.getElementById('hasil_or5').value;

	if(kodeorg=='' ||  tanggal==''  || kodeblok==''){
		alert('All fields are required');
	}else{
		param='kodeorg='+kodeorg+'&kodeblok='+kodeblok+'&tanggal='+tanggal;
		//Unripe
		param+='&berattbs_ur2='+berattbs_ur2+'&berattbs_ur4='+berattbs_ur4+'&berattbs_ur5='+berattbs_ur5;
		param+='&tbsrebus_ur2='+tbsrebus_ur2+'&tbsrebus_ur4='+tbsrebus_ur4+'&tbsrebus_ur5='+tbsrebus_ur5;

		param+='&condensate_ur2='+condensate_ur2+'&condensate_ur4='+condensate_ur4+'&condensate_ur5='+condensate_ur5;
		param+='&brondolluar_ur2='+brondolluar_ur2+'&brondolluar_ur4='+brondolluar_ur4+'&brondolluar_ur5='+brondolluar_ur5;
		param+='&brondoldalam_ur2='+brondoldalam_ur2+'&brondoldalam_ur4='+brondoldalam_ur4+'&brondoldalam_ur5='+brondoldalam_ur5;
		param+='&abn_ur2='+abn_ur2+'&abn_ur4='+abn_ur4+'&abn_ur5='+abn_ur5;
		param+='&calix_ur2='+calix_ur2+'&calix_ur4='+calix_ur4+'&calix_ur5='+calix_ur5;
		param+='&jangkos_ur2='+jangkos_ur2+'&jangkos_ur4='+jangkos_ur4+'&jangkos_ur5='+jangkos_ur5;
		param+='&totaltbs_ur2='+totaltbs_ur2+'&totaltbs_ur4='+totaltbs_ur4+'&totaltbs_ur5='+totaltbs_ur5;
		param+='&sampel_ur2='+sampel_ur2+'&sampel_ur4='+sampel_ur4+'&sampel_ur5='+sampel_ur5;

		param+='&brondolan_ur2='+brondolan_ur2+'&brondolan_ur4='+brondolan_ur4+'&brondolan_ur5='+brondolan_ur5;
		param+='&evaporation_ur2='+evaporation_ur2+'&evaporation_ur4='+evaporation_ur4+'&evaporation_ur5='+evaporation_ur5;
		param+='&brondoldry_ur2='+brondoldry_ur2+'&brondoldry_ur4='+brondoldry_ur4+'&brondoldry_ur5='+brondoldry_ur5;
		param+='&fiber_ur2='+fiber_ur2+'&fiber_ur4='+fiber_ur4+'&fiber_ur5='+fiber_ur5;
		param+='&nut_ur2='+nut_ur2+'&nut_ur4='+nut_ur4+'&nut_ur5='+nut_ur5;
		param+='&shell_ur2='+shell_ur2+'&shell_ur4='+shell_ur4+'&shell_ur5='+shell_ur5;
		param+='&kernel_ur2='+kernel_ur2+'&kernel_ur4='+kernel_ur4+'&kernel_ur5='+kernel_ur5;
		param+='&kerneldry_ur2='+kerneldry_ur2+'&kerneldry_ur4='+kerneldry_ur4+'&kerneldry_ur5='+kerneldry_ur5;
		param+='&lossestbs_ur2='+lossestbs_ur2+'&lossestbs_ur4='+lossestbs_ur4+'&lossestbs_ur5='+lossestbs_ur5;
		param+='&sttotal_ur2='+sttotal_ur2+'&sttotal_ur4='+sttotal_ur4+'&sttotal_ur5='+sttotal_ur5;

		param+='&oilinfiber_ur2='+oilinfiber_ur2+'&oilinfiber_ur4='+oilinfiber_ur4+'&oilinfiber_ur5='+oilinfiber_ur5;
		param+='&oilinshell_ur2='+oilinshell_ur2+'&oilinshell_ur4='+oilinshell_ur4+'&oilinshell_ur5='+oilinshell_ur5;
		param+='&totaloil_ur2='+totaloil_ur2+'&totaloil_ur4='+totaloil_ur4+'&totaloil_ur5='+totaloil_ur5;
		param+='&lossesoil_ur2='+lossesoil_ur2+'&lossesoil_ur4='+lossesoil_ur4+'&lossesoil_ur5='+lossesoil_ur5;
		param+='&gttotal_ur2='+gttotal_ur2+'&gttotal_ur4='+gttotal_ur4+'&gttotal_ur5='+gttotal_ur5;
		param+='&hasil_ur2='+hasil_ur2+'&hasil_ur4='+hasil_ur4+'&hasil_ur5='+hasil_ur5;

		//Normal Ripe
		param+='&berattbs_nr2='+berattbs_nr2+'&berattbs_nr4='+berattbs_nr4+'&berattbs_nr5='+berattbs_nr5;
		param+='&tbsrebus_nr2='+tbsrebus_nr2+'&tbsrebus_nr4='+tbsrebus_nr4+'&tbsrebus_nr5='+tbsrebus_nr5;

		param+='&condensate_nr2='+condensate_nr2+'&condensate_nr4='+condensate_nr4+'&condensate_nr5='+condensate_nr5;
		param+='&brondolluar_nr2='+brondolluar_nr2+'&brondolluar_nr4='+brondolluar_nr4+'&brondolluar_nr5='+brondolluar_nr5;
		param+='&brondoldalam_nr2='+brondoldalam_nr2+'&brondoldalam_nr4='+brondoldalam_nr4+'&brondoldalam_nr5='+brondoldalam_nr5;
		param+='&abn_nr2='+abn_nr2+'&abn_nr4='+abn_nr4+'&abn_nr5='+abn_nr5;
		param+='&calix_nr2='+calix_nr2+'&calix_nr4='+calix_nr4+'&calix_nr5='+calix_nr5;
		param+='&jangkos_nr2='+jangkos_nr2+'&jangkos_nr4='+jangkos_nr4+'&jangkos_nr5='+jangkos_nr5;
		param+='&totaltbs_nr2='+totaltbs_nr2+'&totaltbs_nr4='+totaltbs_nr4+'&totaltbs_nr5='+totaltbs_nr5;
		param+='&sampel_nr2='+sampel_nr2+'&sampel_nr4='+sampel_nr4+'&sampel_nr5='+sampel_nr5;

		param+='&brondolan_nr2='+brondolan_nr2+'&brondolan_nr4='+brondolan_nr4+'&brondolan_nr5='+brondolan_nr5;
		param+='&evaporation_nr2='+evaporation_nr2+'&evaporation_nr4='+evaporation_nr4+'&evaporation_nr5='+evaporation_nr5;
		param+='&brondoldry_nr2='+brondoldry_nr2+'&brondoldry_nr4='+brondoldry_nr4+'&brondoldry_nr5='+brondoldry_nr5;
		param+='&fiber_nr2='+fiber_nr2+'&fiber_nr4='+fiber_nr4+'&fiber_nr5='+fiber_nr5;
		param+='&nut_nr2='+nut_nr2+'&nut_nr4='+nut_nr4+'&nut_nr5='+nut_nr5;
		param+='&shell_nr2='+shell_nr2+'&shell_nr4='+shell_nr4+'&shell_nr5='+shell_nr5;
		param+='&kernel_nr2='+kernel_nr2+'&kernel_nr4='+kernel_nr4+'&kernel_nr5='+kernel_nr5;
		param+='&kerneldry_nr2='+kerneldry_nr2+'&kerneldry_nr4='+kerneldry_nr4+'&kerneldry_nr5='+kerneldry_nr5;
		param+='&lossestbs_nr2='+lossestbs_nr2+'&lossestbs_nr4='+lossestbs_nr4+'&lossestbs_nr5='+lossestbs_nr5;
		param+='&sttotal_nr2='+sttotal_nr2+'&sttotal_nr4='+sttotal_nr4+'&sttotal_nr5='+sttotal_nr5;

		param+='&oilinfiber_nr2='+oilinfiber_nr2+'&oilinfiber_nr4='+oilinfiber_nr4+'&oilinfiber_nr5='+oilinfiber_nr5;
		param+='&oilinshell_nr2='+oilinshell_nr2+'&oilinshell_nr4='+oilinshell_nr4+'&oilinshell_nr5='+oilinshell_nr5;
		param+='&totaloil_nr2='+totaloil_nr2+'&totaloil_nr4='+totaloil_nr4+'&totaloil_nr5='+totaloil_nr5;
		param+='&lossesoil_nr2='+lossesoil_nr2+'&lossesoil_nr4='+lossesoil_nr4+'&lossesoil_nr5='+lossesoil_nr5;
		param+='&gttotal_nr2='+gttotal_nr2+'&gttotal_nr4='+gttotal_nr4+'&gttotal_nr5='+gttotal_nr5;
		param+='&hasil_nr2='+hasil_nr2+'&hasil_nr4='+hasil_nr4+'&hasil_nr5='+hasil_nr5;

		//Over Ripe
		param+='&berattbs_or2='+berattbs_or2+'&berattbs_or4='+berattbs_or4+'&berattbs_or5='+berattbs_or5;
		param+='&tbsrebus_or2='+tbsrebus_or2+'&tbsrebus_or4='+tbsrebus_or4+'&tbsrebus_or5='+tbsrebus_or5;

		param+='&condensate_or2='+condensate_or2+'&condensate_or4='+condensate_or4+'&condensate_or5='+condensate_or5;
		param+='&brondolluar_or2='+brondolluar_or2+'&brondolluar_or4='+brondolluar_or4+'&brondolluar_or5='+brondolluar_or5;
		param+='&brondoldalam_or2='+brondoldalam_or2+'&brondoldalam_or4='+brondoldalam_or4+'&brondoldalam_or5='+brondoldalam_or5;
		param+='&abn_or2='+abn_or2+'&abn_or4='+abn_or4+'&abn_or5='+abn_or5;
		param+='&calix_or2='+calix_or2+'&calix_or4='+calix_or4+'&calix_or5='+calix_or5;
		param+='&jangkos_or2='+jangkos_or2+'&jangkos_or4='+jangkos_or4+'&jangkos_or5='+jangkos_or5;
		param+='&totaltbs_or2='+totaltbs_or2+'&totaltbs_or4='+totaltbs_or4+'&totaltbs_or5='+totaltbs_or5;
		param+='&sampel_or2='+sampel_or2+'&sampel_or4='+sampel_or4+'&sampel_or5='+sampel_or5;

		param+='&brondolan_or2='+brondolan_or2+'&brondolan_or4='+brondolan_or4+'&brondolan_or5='+brondolan_or5;
		param+='&evaporation_or2='+evaporation_or2+'&evaporation_or4='+evaporation_or4+'&evaporation_or5='+evaporation_or5;
		param+='&brondoldry_or2='+brondoldry_or2+'&brondoldry_or4='+brondoldry_or4+'&brondoldry_or5='+brondoldry_or5;
		param+='&fiber_or2='+fiber_or2+'&fiber_or4='+fiber_or4+'&fiber_or5='+fiber_or5;
		param+='&nut_or2='+nut_or2+'&nut_or4='+nut_or4+'&nut_or5='+nut_or5;
		param+='&shell_or2='+shell_or2+'&shell_or4='+shell_or4+'&shell_or5='+shell_or5;
		param+='&kernel_or2='+kernel_or2+'&kernel_or4='+kernel_or4+'&kernel_or5='+kernel_or5;
		param+='&kerneldry_or2='+kerneldry_or2+'&kerneldry_or4='+kerneldry_or4+'&kerneldry_or5='+kerneldry_or5;
		param+='&lossestbs_or2='+lossestbs_or2+'&lossestbs_or4='+lossestbs_or4+'&lossestbs_or5='+lossestbs_or5;
		param+='&sttotal_or2='+sttotal_or2+'&sttotal_or4='+sttotal_or4+'&sttotal_or5='+sttotal_or5;

		param+='&oilinfiber_or2='+oilinfiber_or2+'&oilinfiber_or4='+oilinfiber_or4+'&oilinfiber_or5='+oilinfiber_or5;
		param+='&oilinshell_or2='+oilinshell_or2+'&oilinshell_or4='+oilinshell_or4+'&oilinshell_or5='+oilinshell_or5;
		param+='&totaloil_or2='+totaloil_or2+'&totaloil_or4='+totaloil_or4+'&totaloil_or5='+totaloil_or5;
		param+='&lossesoil_or2='+lossesoil_or2+'&lossesoil_or4='+lossesoil_or4+'&lossesoil_or5='+lossesoil_or5;
		param+='&gttotal_or2='+gttotal_or2+'&gttotal_or4='+gttotal_or4+'&gttotal_or5='+gttotal_or5;
		param+='&hasil_or2='+hasil_or2+'&hasil_or4='+hasil_or4+'&hasil_or5='+hasil_or5;

		param+='&method=simpanData';
		tujuan='pabrik_materialballance_slave.php';
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
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 		
}

function fillfield(kodeorg,kodeblok,tanggal
	,berattbs_ur,tbsrebus_ur,brondolluar_ur,brondoldalam_ur,abn_ur,calix_ur,jangkos_ur
	,brondolan_ur,brondoldry_ur,nut_ur,shell_ur,kernel_ur,kerneldry_ur,lossestbs_ur,oilinfiber_ur,oilinshell_ur,lossesoil_ur
	,berattbs_nr,tbsrebus_nr,brondolluar_nr,brondoldalam_nr,abn_nr,calix_nr,jangkos_nr
	,brondolan_nr,brondoldry_nr,nut_nr,shell_nr,kernel_nr,kerneldry_nr,lossestbs_nr,oilinfiber_nr,oilinshell_nr,lossesoil_nr
	,berattbs_or,tbsrebus_or,brondolluar_or,brondoldalam_or,abn_or,calix_or,jangkos_or
	,brondolan_or,brondoldry_or,nut_or,shell_or,kernel_or,kerneldry_or,lossestbs_or,oilinfiber_or,oilinshell_or,lossesoil_or
	){

	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('kodeblok').value=kodeblok;
	document.getElementById('tanggal').value=tanggal;

	//UnRipe
	document.getElementById('berattbs_ur2').value=berattbs_ur;
	document.getElementById('tbsrebus_ur2').value=tbsrebus_ur;

	document.getElementById('brondolluar_ur2').value=brondolluar_ur;
	document.getElementById('brondoldalam_ur2').value=brondoldalam_ur;
	document.getElementById('abn_ur2').value=abn_ur;
	document.getElementById('calix_ur2').value=calix_ur;
	document.getElementById('jangkos_ur2').value=jangkos_ur;

	document.getElementById('brondolan_ur2').value=brondolan_ur;
	document.getElementById('brondoldry_ur2').value=brondoldry_ur;
	document.getElementById('nut_ur2').value=nut_ur;
	document.getElementById('shell_ur2').value=shell_ur;
	document.getElementById('kernel_ur2').value=kernel_ur;
	document.getElementById('kerneldry_ur2').value=kerneldry_ur;
	document.getElementById('lossestbs_ur5').value=lossestbs_ur;

	document.getElementById('oilinfiber_ur2').value=oilinfiber_ur;
	document.getElementById('oilinshell_ur2').value=oilinshell_ur;
	document.getElementById('lossesoil_ur5').value=lossesoil_ur;
	hitung_ur();

	//Normal Ripe
	document.getElementById('berattbs_nr2').value=berattbs_nr;
	document.getElementById('tbsrebus_nr2').value=tbsrebus_nr;

	document.getElementById('brondolluar_nr2').value=brondolluar_nr;
	document.getElementById('brondoldalam_nr2').value=brondoldalam_nr;
	document.getElementById('abn_nr2').value=abn_nr;
	document.getElementById('calix_nr2').value=calix_nr;
	document.getElementById('jangkos_nr2').value=jangkos_nr;

	document.getElementById('brondolan_nr2').value=brondolan_nr;
	document.getElementById('brondoldry_nr2').value=brondoldry_nr;
	document.getElementById('nut_nr2').value=nut_nr;
	document.getElementById('shell_nr2').value=shell_nr;
	document.getElementById('kernel_nr2').value=kernel_nr;
	document.getElementById('kerneldry_nr2').value=kerneldry_nr;
	document.getElementById('lossestbs_nr5').value=lossestbs_nr;

	document.getElementById('oilinfiber_nr2').value=oilinfiber_nr;
	document.getElementById('oilinshell_nr2').value=oilinshell_nr;
	document.getElementById('lossesoil_nr5').value=lossesoil_nr;
	hitung_nr();

	//Over Ripe
	document.getElementById('berattbs_or2').value=berattbs_or;
	document.getElementById('tbsrebus_or2').value=tbsrebus_or;

	document.getElementById('brondolluar_or2').value=brondolluar_or;
	document.getElementById('brondoldalam_or2').value=brondoldalam_or;
	document.getElementById('abn_or2').value=abn_or;
	document.getElementById('calix_or2').value=calix_or;
	document.getElementById('jangkos_or2').value=jangkos_or;

	document.getElementById('brondolan_or2').value=brondolan_or;
	document.getElementById('brondoldry_or2').value=brondoldry_or;
	document.getElementById('nut_or2').value=nut_or;
	document.getElementById('shell_or2').value=shell_or;
	document.getElementById('kernel_or2').value=kernel_or;
	document.getElementById('kerneldry_or2').value=kerneldry_or;
	document.getElementById('lossestbs_or5').value=lossestbs_or;

	document.getElementById('oilinfiber_or2').value=oilinfiber_or;
	document.getElementById('oilinshell_or2').value=oilinshell_or;
	document.getElementById('lossesoil_or5').value=lossesoil_or;
	hitung_or();
}

function bersihkanForm(){
	document.getElementById('kodeblok').value='';
	document.getElementById('tanggal').value='';
	nol=0;
	//UnRipe
	document.getElementById('berattbs_ur2').value=0;
	document.getElementById('berattbs_ur4').value='';
	document.getElementById('berattbs_ur5').value=nol.toFixed(2);
	document.getElementById('tbsrebus_ur2').value=0;
	document.getElementById('tbsrebus_ur4').value='';
	document.getElementById('tbsrebus_ur5').value=nol.toFixed(2);

	document.getElementById('condensate_ur2').value=0;
	document.getElementById('condensate_ur4').value='';
	document.getElementById('condensate_ur5').value=nol.toFixed(2);
	document.getElementById('brondolluar_ur2').value=0;
	document.getElementById('brondolluar_ur4').value='';
	document.getElementById('brondolluar_ur5').value=nol.toFixed(2);
	document.getElementById('brondoldalam_ur2').value=0;
	document.getElementById('brondoldalam_ur4').value='';
	document.getElementById('brondoldalam_ur5').value=nol.toFixed(2);
	document.getElementById('abn_ur2').value=0;
	document.getElementById('abn_ur4').value='';
	document.getElementById('abn_ur5').value=nol.toFixed(2);
	document.getElementById('calix_ur2').value=0;
	document.getElementById('calix_ur4').value='';
	document.getElementById('calix_ur5').value=nol.toFixed(2);
	document.getElementById('jangkos_ur2').value=0;
	document.getElementById('jangkos_ur4').value='';
	document.getElementById('jangkos_ur5').value=nol.toFixed(2);
	document.getElementById('totaltbs_ur2').value=0;
	document.getElementById('totaltbs_ur4').value='';
	document.getElementById('totaltbs_ur5').value=nol.toFixed(2);
	document.getElementById('sampel_ur2').value=0;
	document.getElementById('sampel_ur4').value='';
	document.getElementById('sampel_ur5').value=nol.toFixed(2);

	document.getElementById('brondolan_ur2').value=0;
	document.getElementById('brondolan_ur4').value='';
	document.getElementById('brondolan_ur5').value='';
	document.getElementById('evaporation_ur2').value=0;
	document.getElementById('evaporation_ur4').value=nol.toFixed(2);
	document.getElementById('evaporation_ur5').value=nol.toFixed(2);
	document.getElementById('brondoldry_ur2').value=0;
	document.getElementById('brondoldry_ur4').value='';
	document.getElementById('brondoldry_ur5').value='';
	document.getElementById('fiber_ur2').value=0;
	document.getElementById('fiber_ur4').value=nol.toFixed(2);
	document.getElementById('fiber_ur5').value=nol.toFixed(2);
	document.getElementById('nut_ur2').value=0;
	document.getElementById('nut_ur4').value=nol.toFixed(2);
	document.getElementById('nut_ur5').value=nol.toFixed(2);
	document.getElementById('shell_ur2').value=0;
	document.getElementById('shell_ur4').value=nol.toFixed(2);
	document.getElementById('shell_ur5').value=nol.toFixed(2);
	document.getElementById('kernel_ur2').value=0;
	document.getElementById('kernel_ur4').value=nol.toFixed(2);
	document.getElementById('kernel_ur5').value=nol.toFixed(2);
	document.getElementById('kerneldry_ur2').value=0;
	document.getElementById('kerneldry_ur4').value=nol.toFixed(2);
	document.getElementById('kerneldry_ur5').value=nol.toFixed(2);
	document.getElementById('lossestbs_ur2').value='';
	document.getElementById('lossestbs_ur4').value='';
	document.getElementById('lossestbs_ur5').value=nol.toFixed(2);
	document.getElementById('sttotal_ur2').value='';
	document.getElementById('sttotal_ur4').value='';
	document.getElementById('sttotal_ur5').value=nol.toFixed(2);

	document.getElementById('oilinfiber_ur2').value=0;
	document.getElementById('oilinfiber_ur4').value=nol.toFixed(2);
	document.getElementById('oilinfiber_ur5').value=nol.toFixed(2);
	document.getElementById('oilinshell_ur2').value=0;
	document.getElementById('oilinshell_ur4').value=nol.toFixed(2);
	document.getElementById('oilinshell_ur5').value=nol.toFixed(2);
	document.getElementById('totaloil_ur2').value=0;
	document.getElementById('totaloil_ur4').value=nol.toFixed(2);
	document.getElementById('totaloil_ur5').value=nol.toFixed(2);
	document.getElementById('lossesoil_ur2').value='';
	document.getElementById('lossesoil_ur4').value='';
	document.getElementById('lossesoil_ur5').value=nol.toFixed(2);
	document.getElementById('gttotal_ur2').value='';
	document.getElementById('gttotal_ur4').value='';
	document.getElementById('gttotal_ur5').value=nol.toFixed(2);
	document.getElementById('hasil_ur2').value=nol.toFixed(4);
	document.getElementById('hasil_ur4').value='';
	document.getElementById('hasil_ur5').value='';

	//Normal Ripe
	document.getElementById('berattbs_nr2').value=0;
	document.getElementById('berattbs_nr4').value='';
	document.getElementById('berattbs_nr5').value=nol.toFixed(2);
	document.getElementById('tbsrebus_nr2').value=0;
	document.getElementById('tbsrebus_nr4').value='';
	document.getElementById('tbsrebus_nr5').value=nol.toFixed(2);

	document.getElementById('condensate_nr2').value=0;
	document.getElementById('condensate_nr4').value='';
	document.getElementById('condensate_nr5').value=nol.toFixed(2);
	document.getElementById('brondolluar_nr2').value=0;
	document.getElementById('brondolluar_nr4').value='';
	document.getElementById('brondolluar_nr5').value=nol.toFixed(2);
	document.getElementById('brondoldalam_nr2').value=0;
	document.getElementById('brondoldalam_nr4').value='';
	document.getElementById('brondoldalam_nr5').value=nol.toFixed(2);
	document.getElementById('abn_nr2').value=0;
	document.getElementById('abn_nr4').value='';
	document.getElementById('abn_nr5').value=nol.toFixed(2);
	document.getElementById('calix_nr2').value=0;
	document.getElementById('calix_nr4').value='';
	document.getElementById('calix_nr5').value=nol.toFixed(2);
	document.getElementById('jangkos_nr2').value=0;
	document.getElementById('jangkos_nr4').value='';
	document.getElementById('jangkos_nr5').value=nol.toFixed(2);
	document.getElementById('totaltbs_nr2').value=0;
	document.getElementById('totaltbs_nr4').value='';
	document.getElementById('totaltbs_nr5').value=nol.toFixed(2);
	document.getElementById('sampel_nr2').value=0;
	document.getElementById('sampel_nr4').value='';
	document.getElementById('sampel_nr5').value=nol.toFixed(2);

	document.getElementById('brondolan_nr2').value=0;
	document.getElementById('brondolan_nr4').value='';
	document.getElementById('brondolan_nr5').value='';
	document.getElementById('evaporation_nr2').value=0;
	document.getElementById('evaporation_nr4').value=nol.toFixed(2);
	document.getElementById('evaporation_nr5').value=nol.toFixed(2);
	document.getElementById('brondoldry_nr2').value=0;
	document.getElementById('brondoldry_nr4').value='';
	document.getElementById('brondoldry_nr5').value='';
	document.getElementById('fiber_nr2').value=0;
	document.getElementById('fiber_nr4').value=nol.toFixed(2);
	document.getElementById('fiber_nr5').value=nol.toFixed(2);
	document.getElementById('nut_nr2').value=0;
	document.getElementById('nut_nr4').value=nol.toFixed(2);
	document.getElementById('nut_nr5').value=nol.toFixed(2);
	document.getElementById('shell_nr2').value=0;
	document.getElementById('shell_nr4').value=nol.toFixed(2);
	document.getElementById('shell_nr5').value=nol.toFixed(2);
	document.getElementById('kernel_nr2').value=0;
	document.getElementById('kernel_nr4').value=nol.toFixed(2);
	document.getElementById('kernel_nr5').value=nol.toFixed(2);
	document.getElementById('kerneldry_nr2').value=0;
	document.getElementById('kerneldry_nr4').value=nol.toFixed(2);
	document.getElementById('kerneldry_nr5').value=nol.toFixed(2);
	document.getElementById('lossestbs_nr2').value='';
	document.getElementById('lossestbs_nr4').value='';
	document.getElementById('lossestbs_nr5').value=nol.toFixed(2);
	document.getElementById('sttotal_nr2').value='';
	document.getElementById('sttotal_nr4').value='';
	document.getElementById('sttotal_nr5').value=nol.toFixed(2);

	document.getElementById('oilinfiber_nr2').value=0;
	document.getElementById('oilinfiber_nr4').value=nol.toFixed(2);
	document.getElementById('oilinfiber_nr5').value=nol.toFixed(2);
	document.getElementById('oilinshell_nr2').value=0;
	document.getElementById('oilinshell_nr4').value=nol.toFixed(2);
	document.getElementById('oilinshell_nr5').value=nol.toFixed(2);
	document.getElementById('totaloil_nr2').value=0;
	document.getElementById('totaloil_nr4').value=nol.toFixed(2);
	document.getElementById('totaloil_nr5').value=nol.toFixed(2);
	document.getElementById('lossesoil_nr2').value='';
	document.getElementById('lossesoil_nr4').value='';
	document.getElementById('lossesoil_nr5').value=nol.toFixed(2);
	document.getElementById('gttotal_nr2').value='';
	document.getElementById('gttotal_nr4').value='';
	document.getElementById('gttotal_nr5').value=nol.toFixed(2);
	document.getElementById('hasil_nr2').value=nol.toFixed(4);
	document.getElementById('hasil_nr4').value='';
	document.getElementById('hasil_nr5').value='';

	//Over Ripe
	document.getElementById('berattbs_or2').value=0;
	document.getElementById('berattbs_or4').value='';
	document.getElementById('berattbs_or5').value=nol.toFixed(2);
	document.getElementById('tbsrebus_or2').value=0;
	document.getElementById('tbsrebus_or4').value='';
	document.getElementById('tbsrebus_or5').value=nol.toFixed(2);

	document.getElementById('condensate_or2').value=0;
	document.getElementById('condensate_or4').value='';
	document.getElementById('condensate_or5').value=nol.toFixed(2);
	document.getElementById('brondolluar_or2').value=0;
	document.getElementById('brondolluar_or4').value='';
	document.getElementById('brondolluar_or5').value=nol.toFixed(2);
	document.getElementById('brondoldalam_or2').value=0;
	document.getElementById('brondoldalam_or4').value='';
	document.getElementById('brondoldalam_or5').value=nol.toFixed(2);
	document.getElementById('abn_or2').value=0;
	document.getElementById('abn_or4').value='';
	document.getElementById('abn_or5').value=nol.toFixed(2);
	document.getElementById('calix_or2').value=0;
	document.getElementById('calix_or4').value='';
	document.getElementById('calix_or5').value=nol.toFixed(2);
	document.getElementById('jangkos_or2').value=0;
	document.getElementById('jangkos_or4').value='';
	document.getElementById('jangkos_or5').value=nol.toFixed(2);
	document.getElementById('totaltbs_or2').value=0;
	document.getElementById('totaltbs_or4').value='';
	document.getElementById('totaltbs_or5').value=nol.toFixed(2);
	document.getElementById('sampel_or2').value=0;
	document.getElementById('sampel_or4').value='';
	document.getElementById('sampel_or5').value=nol.toFixed(2);

	document.getElementById('brondolan_or2').value=0;
	document.getElementById('brondolan_or4').value='';
	document.getElementById('brondolan_or5').value='';
	document.getElementById('evaporation_or2').value=0;
	document.getElementById('evaporation_or4').value=nol.toFixed(2);
	document.getElementById('evaporation_or5').value=nol.toFixed(2);
	document.getElementById('brondoldry_or2').value=0;
	document.getElementById('brondoldry_or4').value='';
	document.getElementById('brondoldry_or5').value='';
	document.getElementById('fiber_or2').value=0;
	document.getElementById('fiber_or4').value=nol.toFixed(2);
	document.getElementById('fiber_or5').value=nol.toFixed(2);
	document.getElementById('nut_or2').value=0;
	document.getElementById('nut_or4').value=nol.toFixed(2);
	document.getElementById('nut_or5').value=nol.toFixed(2);
	document.getElementById('shell_or2').value=0;
	document.getElementById('shell_or4').value=nol.toFixed(2);
	document.getElementById('shell_or5').value=nol.toFixed(2);
	document.getElementById('kernel_or2').value=0;
	document.getElementById('kernel_or4').value=nol.toFixed(2);
	document.getElementById('kernel_or5').value=nol.toFixed(2);
	document.getElementById('kerneldry_or2').value=0;
	document.getElementById('kerneldry_or4').value=nol.toFixed(2);
	document.getElementById('kerneldry_or5').value=nol.toFixed(2);
	document.getElementById('lossestbs_or2').value='';
	document.getElementById('lossestbs_or4').value='';
	document.getElementById('lossestbs_or5').value=nol.toFixed(2);
	document.getElementById('sttotal_or2').value='';
	document.getElementById('sttotal_or4').value='';
	document.getElementById('sttotal_or5').value=nol.toFixed(2);

	document.getElementById('oilinfiber_or2').value=0;
	document.getElementById('oilinfiber_or4').value=nol.toFixed(2);
	document.getElementById('oilinfiber_or5').value=nol.toFixed(2);
	document.getElementById('oilinshell_or2').value=0;
	document.getElementById('oilinshell_or4').value=nol.toFixed(2);
	document.getElementById('oilinshell_or5').value=nol.toFixed(2);
	document.getElementById('totaloil_or2').value=0;
	document.getElementById('totaloil_or4').value=nol.toFixed(2);
	document.getElementById('totaloil_or5').value=nol.toFixed(2);
	document.getElementById('lossesoil_or2').value='';
	document.getElementById('lossesoil_or4').value='';
	document.getElementById('lossesoil_or5').value=nol.toFixed(2);
	document.getElementById('gttotal_or2').value='';
	document.getElementById('gttotal_or4').value='';
	document.getElementById('gttotal_or5').value=nol.toFixed(2);
	document.getElementById('hasil_or2').value=nol.toFixed(4);
	document.getElementById('hasil_or4').value='';
	document.getElementById('hasil_or5').value='';
}

function deldata(kodeorg,kodeblok,tanggal){
	param='kodeorg='+kodeorg+'&kodeblok='+kodeblok+'&tanggal='+tanggal;
	param+='&method=deldata';
	if (confirm('Delete ..?')) {
		tujuan = 'pabrik_materialballance_slave.php';
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

function hitung_ur(){
	//UnRipe
	berattbs_ur2=parseInt(document.getElementById('berattbs_ur2').value);
	tbsrebus_ur2=parseInt(document.getElementById('tbsrebus_ur2').value);
	if(berattbs_ur2<tbsrebus_ur2){
		alert('TBS Rebus Unripe tidak boleh lebih besar dari Berat TBS...!');
		exit;
	}
	if(berattbs_ur2>0 && berattbs_ur2>tbsrebus_ur2){
		berattbs_ur5=berattbs_ur2/berattbs_ur2*100;
		document.getElementById('berattbs_ur5').value=berattbs_ur5.toFixed(2);
		tbsrebus_ur5=tbsrebus_ur2/berattbs_ur2*100;
		document.getElementById('tbsrebus_ur5').value=tbsrebus_ur5.toFixed(2);
		document.getElementById('condensate_ur2').value=berattbs_ur2-tbsrebus_ur2;
		condensate_ur5=document.getElementById('condensate_ur2').value/berattbs_ur2*100;
		document.getElementById('condensate_ur5').value=condensate_ur5.toFixed(2);
		document.getElementById('brondolluar_ur5').value=(document.getElementById('brondolluar_ur2').value/berattbs_ur2*100).toFixed(2);
		document.getElementById('brondoldalam_ur5').value=(document.getElementById('brondoldalam_ur2').value/berattbs_ur2*100).toFixed(2);
		document.getElementById('abn_ur5').value=(document.getElementById('abn_ur2').value/berattbs_ur2*100).toFixed(2);
		document.getElementById('calix_ur5').value=(document.getElementById('calix_ur2').value/berattbs_ur2*100).toFixed(2);
		document.getElementById('jangkos_ur5').value=(document.getElementById('jangkos_ur2').value/berattbs_ur2*100).toFixed(2);
		totaltbs_ur2=parseInt(document.getElementById('condensate_ur2').value)+parseInt(document.getElementById('brondolluar_ur2').value)+parseInt(document.getElementById('brondoldalam_ur2').value)+parseInt(document.getElementById('abn_ur2').value)+parseInt(document.getElementById('calix_ur2').value)+parseInt(document.getElementById('jangkos_ur2').value);
		document.getElementById('totaltbs_ur2').value=totaltbs_ur2;
		document.getElementById('totaltbs_ur5').value=(totaltbs_ur2/berattbs_ur2*100).toFixed(2);
		document.getElementById('sampel_ur2').value=parseInt(document.getElementById('brondolluar_ur2').value)+parseInt(document.getElementById('brondoldalam_ur2').value);
		document.getElementById('sampel_ur5').value=(document.getElementById('sampel_ur2').value/berattbs_ur2*100).toFixed(2);

		document.getElementById('evaporation_ur2').value=(document.getElementById('brondolan_ur2').value-document.getElementById('brondoldry_ur2').value).toFixed(4);
		document.getElementById('evaporation_ur4').value=(document.getElementById('evaporation_ur2').value/document.getElementById('brondolan_ur2').value*100).toFixed(2);
		document.getElementById('evaporation_ur5').value=((document.getElementById('sampel_ur2').value/berattbs_ur2*100)*(document.getElementById('evaporation_ur2').value/document.getElementById('brondolan_ur2').value*100)/100).toFixed(2);
		document.getElementById('fiber_ur2').value=(document.getElementById('brondoldry_ur2').value-document.getElementById('nut_ur2').value).toFixed(4);
		document.getElementById('fiber_ur4').value=(document.getElementById('fiber_ur2').value/document.getElementById('brondolan_ur2').value*100).toFixed(2);
		document.getElementById('fiber_ur5').value=((document.getElementById('sampel_ur2').value/berattbs_ur2*100)*(document.getElementById('fiber_ur2').value/document.getElementById('brondolan_ur2').value*100)/100).toFixed(2);
		document.getElementById('nut_ur4').value=(document.getElementById('nut_ur2').value/document.getElementById('brondolan_ur2').value*100).toFixed(2);
		document.getElementById('nut_ur5').value=((document.getElementById('sampel_ur2').value/berattbs_ur2*100)*(document.getElementById('nut_ur2').value/document.getElementById('brondolan_ur2').value*100)/100).toFixed(2);
		document.getElementById('shell_ur4').value=(document.getElementById('shell_ur2').value/document.getElementById('brondolan_ur2').value*100).toFixed(2);
		document.getElementById('shell_ur5').value=((document.getElementById('sampel_ur2').value/berattbs_ur2*100)*(document.getElementById('shell_ur2').value/document.getElementById('brondolan_ur2').value*100)/100).toFixed(2);
		document.getElementById('kernel_ur4').value=(document.getElementById('kernel_ur2').value/document.getElementById('brondolan_ur2').value*100).toFixed(2);
		document.getElementById('kernel_ur5').value=((document.getElementById('sampel_ur2').value/berattbs_ur2*100)*(document.getElementById('kernel_ur2').value/document.getElementById('brondolan_ur2').value*100)/100).toFixed(2);
		document.getElementById('kerneldry_ur4').value=(document.getElementById('kerneldry_ur2').value/document.getElementById('brondolan_ur2').value*100).toFixed(2);
		document.getElementById('kerneldry_ur5').value=((document.getElementById('sampel_ur2').value/berattbs_ur2*100)*(document.getElementById('kerneldry_ur2').value/document.getElementById('brondolan_ur2').value*100)/100).toFixed(2);
		document.getElementById('sttotal_ur5').value=(document.getElementById('kerneldry_ur5').value-document.getElementById('lossestbs_ur5').value).toFixed(2);

		document.getElementById('oilinfiber_ur4').value=(document.getElementById('oilinfiber_ur2').value/document.getElementById('brondolan_ur2').value*100).toFixed(2);
		document.getElementById('oilinfiber_ur5').value=((document.getElementById('sampel_ur2').value/berattbs_ur2*100)*(document.getElementById('oilinfiber_ur2').value/document.getElementById('brondolan_ur2').value*100)/100).toFixed(2);
		document.getElementById('oilinshell_ur4').value=(document.getElementById('oilinshell_ur2').value/document.getElementById('brondolan_ur2').value*100).toFixed(2);
		document.getElementById('oilinshell_ur5').value=((document.getElementById('sampel_ur2').value/berattbs_ur2*100)*(document.getElementById('oilinshell_ur2').value/document.getElementById('brondolan_ur2').value*100)/100).toFixed(2);
		document.getElementById('totaloil_ur2').value=(parseFloat(document.getElementById('oilinfiber_ur2').value)+parseFloat(document.getElementById('oilinshell_ur2').value));
		document.getElementById('totaloil_ur4').value=(document.getElementById('totaloil_ur2').value/document.getElementById('brondolan_ur2').value*100).toFixed(2);
		document.getElementById('totaloil_ur5').value=((document.getElementById('sampel_ur2').value/berattbs_ur2*100)*(document.getElementById('totaloil_ur2').value/document.getElementById('brondolan_ur2').value*100)/100).toFixed(2);
		document.getElementById('gttotal_ur5').value=(document.getElementById('totaloil_ur5').value-document.getElementById('lossesoil_ur5').value).toFixed(2);
		document.getElementById('hasil_ur2').value=(document.getElementById('brondolan_ur2').value/(document.getElementById('sampel_ur2').value/berattbs_ur2*100)*100).toFixed(4);
	}
}

function hitung_nr(){
	//Normal Ripe
	berattbs_nr2=parseInt(document.getElementById('berattbs_nr2').value);
	tbsrebus_nr2=parseInt(document.getElementById('tbsrebus_nr2').value);
	if(berattbs_nr2<tbsrebus_nr2){
		alert('TBS Rebus Normal Ripe tidak boleh lebih besar dari Berat TBS...!');
		exit;
	}
	if(berattbs_nr2>0 && berattbs_nr2>tbsrebus_nr2){
		berattbs_nr5=berattbs_nr2/berattbs_nr2*100;
		document.getElementById('berattbs_nr5').value=berattbs_nr5.toFixed(2);
		tbsrebus_nr5=tbsrebus_nr2/berattbs_nr2*100;
		document.getElementById('tbsrebus_nr5').value=tbsrebus_nr5.toFixed(2);
		document.getElementById('condensate_nr2').value=berattbs_nr2-tbsrebus_nr2;
		condensate_nr5=document.getElementById('condensate_nr2').value/berattbs_nr2*100;
		document.getElementById('condensate_nr5').value=condensate_nr5.toFixed(2);
		document.getElementById('brondolluar_nr5').value=(document.getElementById('brondolluar_nr2').value/berattbs_nr2*100).toFixed(2);
		document.getElementById('brondoldalam_nr5').value=(document.getElementById('brondoldalam_nr2').value/berattbs_nr2*100).toFixed(2);
		document.getElementById('abn_nr5').value=(document.getElementById('abn_nr2').value/berattbs_nr2*100).toFixed(2);
		document.getElementById('calix_nr5').value=(document.getElementById('calix_nr2').value/berattbs_nr2*100).toFixed(2);
		document.getElementById('jangkos_nr5').value=(document.getElementById('jangkos_nr2').value/berattbs_nr2*100).toFixed(2);
		totaltbs_nr2=parseInt(document.getElementById('condensate_nr2').value)+parseInt(document.getElementById('brondolluar_nr2').value)+parseInt(document.getElementById('brondoldalam_nr2').value)+parseInt(document.getElementById('abn_nr2').value)+parseInt(document.getElementById('calix_nr2').value)+parseInt(document.getElementById('jangkos_nr2').value);
		document.getElementById('totaltbs_nr2').value=totaltbs_nr2;
		document.getElementById('totaltbs_nr5').value=(totaltbs_nr2/berattbs_nr2*100).toFixed(2);
		document.getElementById('sampel_nr2').value=parseInt(document.getElementById('brondolluar_nr2').value)+parseInt(document.getElementById('brondoldalam_nr2').value);
		document.getElementById('sampel_nr5').value=(document.getElementById('sampel_nr2').value/berattbs_nr2*100).toFixed(2);

		document.getElementById('evaporation_nr2').value=(document.getElementById('brondolan_nr2').value-document.getElementById('brondoldry_nr2').value).toFixed(4);
		document.getElementById('evaporation_nr4').value=(document.getElementById('evaporation_nr2').value/document.getElementById('brondolan_nr2').value*100).toFixed(2);
		document.getElementById('evaporation_nr5').value=((document.getElementById('sampel_nr2').value/berattbs_nr2*100)*(document.getElementById('evaporation_nr2').value/document.getElementById('brondolan_nr2').value*100)/100).toFixed(2);
		document.getElementById('fiber_nr2').value=(document.getElementById('brondoldry_nr2').value-document.getElementById('nut_nr2').value).toFixed(4);
		document.getElementById('fiber_nr4').value=(document.getElementById('fiber_nr2').value/document.getElementById('brondolan_nr2').value*100).toFixed(2);
		document.getElementById('fiber_nr5').value=((document.getElementById('sampel_nr2').value/berattbs_nr2*100)*(document.getElementById('fiber_nr2').value/document.getElementById('brondolan_nr2').value*100)/100).toFixed(2);
		document.getElementById('nut_nr4').value=(document.getElementById('nut_nr2').value/document.getElementById('brondolan_nr2').value*100).toFixed(2);
		document.getElementById('nut_nr5').value=((document.getElementById('sampel_nr2').value/berattbs_nr2*100)*(document.getElementById('nut_nr2').value/document.getElementById('brondolan_nr2').value*100)/100).toFixed(2);
		document.getElementById('shell_nr4').value=(document.getElementById('shell_nr2').value/document.getElementById('brondolan_nr2').value*100).toFixed(2);
		document.getElementById('shell_nr5').value=((document.getElementById('sampel_nr2').value/berattbs_nr2*100)*(document.getElementById('shell_nr2').value/document.getElementById('brondolan_nr2').value*100)/100).toFixed(2);
		document.getElementById('kernel_nr4').value=(document.getElementById('kernel_nr2').value/document.getElementById('brondolan_nr2').value*100).toFixed(2);
		document.getElementById('kernel_nr5').value=((document.getElementById('sampel_nr2').value/berattbs_nr2*100)*(document.getElementById('kernel_nr2').value/document.getElementById('brondolan_nr2').value*100)/100).toFixed(2);
		document.getElementById('kerneldry_nr4').value=(document.getElementById('kerneldry_nr2').value/document.getElementById('brondolan_nr2').value*100).toFixed(2);
		document.getElementById('kerneldry_nr5').value=((document.getElementById('sampel_nr2').value/berattbs_nr2*100)*(document.getElementById('kerneldry_nr2').value/document.getElementById('brondolan_nr2').value*100)/100).toFixed(2);
		document.getElementById('sttotal_nr5').value=(document.getElementById('kerneldry_nr5').value-document.getElementById('lossestbs_nr5').value).toFixed(2);

		document.getElementById('oilinfiber_nr4').value=(document.getElementById('oilinfiber_nr2').value/document.getElementById('brondolan_nr2').value*100).toFixed(2);
		document.getElementById('oilinfiber_nr5').value=((document.getElementById('sampel_nr2').value/berattbs_nr2*100)*(document.getElementById('oilinfiber_nr2').value/document.getElementById('brondolan_nr2').value*100)/100).toFixed(2);
		document.getElementById('oilinshell_nr4').value=(document.getElementById('oilinshell_nr2').value/document.getElementById('brondolan_nr2').value*100).toFixed(2);
		document.getElementById('oilinshell_nr5').value=((document.getElementById('sampel_nr2').value/berattbs_nr2*100)*(document.getElementById('oilinshell_nr2').value/document.getElementById('brondolan_nr2').value*100)/100).toFixed(2);
		document.getElementById('totaloil_nr2').value=(parseFloat(document.getElementById('oilinfiber_nr2').value)+parseFloat(document.getElementById('oilinshell_nr2').value)).toFixed(4);
		document.getElementById('totaloil_nr4').value=(document.getElementById('totaloil_nr2').value/document.getElementById('brondolan_nr2').value*100).toFixed(2);
		document.getElementById('totaloil_nr5').value=((document.getElementById('sampel_nr2').value/berattbs_nr2*100)*(document.getElementById('totaloil_nr2').value/document.getElementById('brondolan_nr2').value*100)/100).toFixed(2);
		document.getElementById('gttotal_nr5').value=(document.getElementById('totaloil_nr5').value-document.getElementById('lossesoil_nr5').value).toFixed(2);
		document.getElementById('hasil_nr2').value=(document.getElementById('brondolan_nr2').value/(document.getElementById('sampel_nr2').value/berattbs_nr2*100)*100).toFixed(4);
	}
}

function hitung_or(){
	//Over Ripe
	berattbs_or2=parseInt(document.getElementById('berattbs_or2').value);
	tbsrebus_or2=parseInt(document.getElementById('tbsrebus_or2').value);
	if(berattbs_or2<tbsrebus_or2){
		alert('TBS Rebus Over Ripe tidak boleh lebih besar dari Berat TBS...!');
		exit;
	}
	if(berattbs_or2>0 && berattbs_or2>tbsrebus_or2){
		berattbs_or5=berattbs_or2/berattbs_or2*100;
		document.getElementById('berattbs_or5').value=berattbs_or5.toFixed(2);
		tbsrebus_or5=tbsrebus_or2/berattbs_or2*100;
		document.getElementById('tbsrebus_or5').value=tbsrebus_or5.toFixed(2);
		document.getElementById('condensate_or2').value=berattbs_or2-tbsrebus_or2;
		condensate_or5=document.getElementById('condensate_or2').value/berattbs_or2*100;
		document.getElementById('condensate_or5').value=condensate_or5.toFixed(2);
		document.getElementById('brondolluar_or5').value=(document.getElementById('brondolluar_or2').value/berattbs_or2*100).toFixed(2);
		document.getElementById('brondoldalam_or5').value=(document.getElementById('brondoldalam_or2').value/berattbs_or2*100).toFixed(2);
		document.getElementById('abn_or5').value=(document.getElementById('abn_or2').value/berattbs_or2*100).toFixed(2);
		document.getElementById('calix_or5').value=(document.getElementById('calix_or2').value/berattbs_or2*100).toFixed(2);
		document.getElementById('jangkos_or5').value=(document.getElementById('jangkos_or2').value/berattbs_or2*100).toFixed(2);
		totaltbs_or2=parseInt(document.getElementById('condensate_or2').value)+parseInt(document.getElementById('brondolluar_or2').value)+parseInt(document.getElementById('brondoldalam_or2').value)+parseInt(document.getElementById('abn_or2').value)+parseInt(document.getElementById('calix_or2').value)+parseInt(document.getElementById('jangkos_or2').value);
		document.getElementById('totaltbs_or2').value=totaltbs_or2;
		document.getElementById('totaltbs_or5').value=(totaltbs_or2/berattbs_or2*100).toFixed(2);
		document.getElementById('sampel_or2').value=parseInt(document.getElementById('brondolluar_or2').value)+parseInt(document.getElementById('brondoldalam_or2').value);
		document.getElementById('sampel_or5').value=(document.getElementById('sampel_or2').value/berattbs_or2*100).toFixed(2);

		document.getElementById('evaporation_or2').value=(document.getElementById('brondolan_or2').value-document.getElementById('brondoldry_or2').value).toFixed(4);
		document.getElementById('evaporation_or4').value=(document.getElementById('evaporation_or2').value/document.getElementById('brondolan_or2').value*100).toFixed(2);
		document.getElementById('evaporation_or5').value=((document.getElementById('sampel_or2').value/berattbs_or2*100)*(document.getElementById('evaporation_or2').value/document.getElementById('brondolan_or2').value*100)/100).toFixed(2);
		document.getElementById('fiber_or2').value=(document.getElementById('brondoldry_or2').value-document.getElementById('nut_or2').value).toFixed(4);
		document.getElementById('fiber_or4').value=(document.getElementById('fiber_or2').value/document.getElementById('brondolan_or2').value*100).toFixed(2);
		document.getElementById('fiber_or5').value=((document.getElementById('sampel_or2').value/berattbs_or2*100)*(document.getElementById('fiber_or2').value/document.getElementById('brondolan_or2').value*100)/100).toFixed(2);
		document.getElementById('nut_or4').value=(document.getElementById('nut_or2').value/document.getElementById('brondolan_or2').value*100).toFixed(2);
		document.getElementById('nut_or5').value=((document.getElementById('sampel_or2').value/berattbs_or2*100)*(document.getElementById('nut_or2').value/document.getElementById('brondolan_or2').value*100)/100).toFixed(2);
		document.getElementById('shell_or4').value=(document.getElementById('shell_or2').value/document.getElementById('brondolan_or2').value*100).toFixed(2);
		document.getElementById('shell_or5').value=((document.getElementById('sampel_or2').value/berattbs_or2*100)*(document.getElementById('shell_or2').value/document.getElementById('brondolan_or2').value*100)/100).toFixed(2);
		document.getElementById('kernel_or4').value=(document.getElementById('kernel_or2').value/document.getElementById('brondolan_or2').value*100).toFixed(2);
		document.getElementById('kernel_or5').value=((document.getElementById('sampel_or2').value/berattbs_or2*100)*(document.getElementById('kernel_or2').value/document.getElementById('brondolan_or2').value*100)/100).toFixed(2);
		document.getElementById('kerneldry_or4').value=(document.getElementById('kerneldry_or2').value/document.getElementById('brondolan_or2').value*100).toFixed(2);
		document.getElementById('kerneldry_or5').value=((document.getElementById('sampel_or2').value/berattbs_or2*100)*(document.getElementById('kerneldry_or2').value/document.getElementById('brondolan_or2').value*100)/100).toFixed(2);
		document.getElementById('sttotal_or5').value=(document.getElementById('kerneldry_or5').value-document.getElementById('lossestbs_or5').value).toFixed(2);

		document.getElementById('oilinfiber_or4').value=(document.getElementById('oilinfiber_or2').value/document.getElementById('brondolan_or2').value*100).toFixed(2);
		document.getElementById('oilinfiber_or5').value=((document.getElementById('sampel_or2').value/berattbs_or2*100)*(document.getElementById('oilinfiber_or2').value/document.getElementById('brondolan_or2').value*100)/100).toFixed(2);
		document.getElementById('oilinshell_or4').value=(document.getElementById('oilinshell_or2').value/document.getElementById('brondolan_or2').value*100).toFixed(2);
		document.getElementById('oilinshell_or5').value=((document.getElementById('sampel_or2').value/berattbs_or2*100)*(document.getElementById('oilinshell_or2').value/document.getElementById('brondolan_or2').value*100)/100).toFixed(2);
		document.getElementById('totaloil_or2').value=(parseFloat(document.getElementById('oilinfiber_or2').value)+parseFloat(document.getElementById('oilinshell_or2').value)).toFixed(4);
		document.getElementById('totaloil_or4').value=(document.getElementById('totaloil_or2').value/document.getElementById('brondolan_or2').value*100).toFixed(2);
		document.getElementById('totaloil_or5').value=((document.getElementById('sampel_or2').value/berattbs_or2*100)*(document.getElementById('totaloil_or2').value/document.getElementById('brondolan_or2').value*100)/100).toFixed(2);
		document.getElementById('gttotal_or5').value=(document.getElementById('totaloil_or5').value-document.getElementById('lossesoil_or5').value).toFixed(2);
		document.getElementById('hasil_or2').value=(document.getElementById('brondolan_or2').value/(document.getElementById('sampel_or2').value/berattbs_or2*100)*100).toFixed(4);
	}
}

function showpopup(kodeorg,kodeblok,tanggal,type,ev){
	param='kodeorg='+kodeorg+'&kodeblok='+kodeblok+'&tanggal='+tanggal+'&type='+type;
	tujuan='pabrik_materialballance_showpopup.php'+"?"+param;
	width='320';
	height='240';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1('Material Ballance '+kodeorg+' '+kodeblok+' '+tanggal,content,width,height,ev); 
}
