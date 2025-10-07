<?php
require_once('master_validation.php');
$param=$_GET['form'];
if($param=='ACCBAL'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=expampleaccbal.csv");
        echo "kodeorg,periode,noakun,saldo\n";
        echo "SOGE,201304,1110001,190000\n";
        echo "SOGE,201304,2110004,40000000\n";
        echo "SOGE,201304,1150001,2550500\n";
        echo "SOGE,201304,3110002,3000000\n";
        echo "SOGE,201304,1260001,10500\n";
        exit();
}
if($param=='JOURNAL'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=expamplejournalhistory.csv");
        echo "nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,kodekegiatan,kodeasset,kodebarang,nik,kodecustomer,kodesupplier,noreferensi,kodevhc,kodeblok,revisi,kodesegment\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,1,0,Histori hutang spl,1000000,IDR,1,SOGE,,,,,,,,,,0,0000000001\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,2,2111101,Histori hutang spl,-300000,IDR,1,SOGE,,,,,,S001000001,,,,0,0000000001\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,3,2111101,Histori hutang spl,-200000,IDR,1,SOGE,,,,,,S001000079,,,,0,0000000001\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,4,2111101,Histori hutang spl,-250000,IDR,1,SOGE,,,,,,S001000602,,,,0,0000000001\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,5,2111101,Histori hutang spl,-250000,IDR,1,SOGE,,,,,,S001000101,,,,0,0000000001\n";
        exit();        
}
if($param=='INV'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=saldomaterial.csv");
            echo "kodeorg,kodebarang,saldoakhirqty,hargarata,periode,kodegudang\n";
            echo "NFS,31200026,1,275000,2013-07,LGRM22\n";
            echo "NFS,32100001,6,1856500.667,2013-07,LGRM22\n";
            echo "NFS,32100003,7.5,170375.0667,2013-07,LGRM22\n";
            echo "NFS,32100005,2,37000,2013-07,LGRM22\n";
            echo "NFS,32100008,4,32500,2013-07,LGRM22\n";
            echo "NFS,32100009,5,53000,2013-07,LGRM22\n";
            echo "NFS,32100013,1,132500,2013-07,LGRM22\n";
            echo "NFS,32100014,3,65556,2013-07,LGRM22\n";
            echo "NFS,32100018,6,20500,2013-07,LGRM22\n";
            exit();        
}       
if($param=='PO'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=listpomanual.csv");
            echo "nopo,kodeorg,tanggal,kodesupplier,matauang,kurs,diskonpersen,nilaidiskon,ppn,subtotal,nilaipo,kodebarang,satuan,jumlahpesan,hargasatuan\n";
            echo "612/08/2013/PO/MA/NFS,NFS,2013-08-02,S001110341,IDR,1,0,0,50650,506500,557150,32102901,ROLL,1,270000\n";
            echo "612/08/2013/PO/MA/NFS,,,,,,,,,,,32102902,PCS,2,20000\n";
            echo "612/08/2013/PO/MA/NFS,,,,,,,,,,,32103182,PCS,2,5500\n";
            echo "612/08/2013/PO/MA/NFS,,,,,,,,,,,32201055,PCS,7,26500\n";
            echo "987/12/2012/PO/MA/NFS,NFS,2012-12-17,S001110070,IDR,1,0,0,0,25720000,25720000,37701061,BUKU,120,29000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701269,BUKU,120,10000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701270,BUKU,120,10000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701271,LEMBAR,500,2600\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701272,BUKU,1200,5000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701273,BUKU,240,11000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701274,BUKU,120,11000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701275,BUKU,120,14000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701276,BUKU,300,23000\n";
            exit();        
}
if($param=='ABSENSI'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=absensi.csv");
            echo "tanggal (yyyy-mm-dd),nik,shift,absensi,jam,jamPlg,keterangan\n";
            echo "2013-08-01,xxxx,1,H,07:00:00,15:00:00,datang terlambat\n";
            echo "2013-08-02,xxxx,1,H,07:00:00,15:00:00,datang terlambat\n";
            echo "2013-08-03,xxxx,1,H,07:00:00,15:00:00,datang terlambat\n";
            echo "2013-08-04,xxxx,1,H,07:00:00,15:00:00,datang terlambat\n";
            echo "2013-08-05,xxxx,1,H,07:00:00,15:00:00,datang terlambat\n";
            echo "2013-08-06,xxxx,1,H,07:00:00,15:00:00,datang terlambat\n";
            exit();        
}

if($param=='DATAKARYAWAN'){
		header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=datakaryawan.csv");
            echo "nik,namakaryawan,tempatlahir,tanggallahir,warganegara,jeniskelamin,statusperkawinan,tanggalmenikah,agama,golongandarah,levelpendidikan,alamataktif,provinsi,kota,kodepos,noteleponrumah,nohp,norekeningbank,namabank,sistemgaji,nopaspor,no_keluarga,noktp,notelpdarurat,tglmasuk,tglpengangkatan,tglkeluar,tipekaryawan,jumlahanak,jumlahtanggungan,statuspajak,npwp,bpjs,lokasipenerimaah,kodeorganisasi,bagian,kodejabatan,kodegolongan,lokasitugas,photo,email,alokasi,subbagian,jms,kodecatu,statpremi,statusakad\n";
            echo "XXXXXX,Lionel Messi,Jakarta,0000-00-00,ID,L,Menikah,0000-00-00,Islam,AB,S1,Jl. A No 21 Jakarta Timur,DKI,Jakarta,XXXXXX,XXXXXX,XXXXXX,XXXXXX,BCA,Bulanan,XXXXXX,XXX.XXX.XXX.XXX,XXXXXXXXX,XXXXXXXXX,0000-00-00,0000-00-00,0000-00-00,0,3,4,K1,XX.XXX.XXX.X-XXX.XXX,0,Jakarta,AMP,HR,0,0,AMHO,0,email@email.com,0,AMHO,XXXXXX,0,0,0\n";
			echo "XXXXXX,Ahmad Dani,Jakarta,0000-00-00,ID,L,Menikah,0000-00-00,Islam,AB,S1,Jl. A No 21 Jakarta Timur,DKI,Jakarta,XXXXXX,XXXXXX,XXXXXX,XXXXXX,BCA,Bulanan,XXXXXX,XXX.XXX.XXX.XXX,XXXXXXXXX,XXXXXXXXX,0000-00-00,0000-00-00,0000-00-00,0,3,4,K1,XX.XXX.XXX.X-XXX.XXX,0,Jakarta,AMP,HR,0,0,AMHO,0,email@email.com,0,AMHO,XXXXXX,0,0,0\n";
            exit();	
}

if($param=='HARGAHARIANPASAR'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=HARGAHARIANPASAR.csv");
            echo "tahun,bulan,tanggal,kodeproduk,pasar,satuan,harga,matauang,statusharga,ffa\n";
            echo "2014,12,1,40000001,Rotterdam,KG,6000,IDR,Best Bidder,2,6\n";
            echo "2014,12,2,40000001,Astra - Dumai,KG,7000,IDR,Price Idea,3,0\n";
            echo "2014,12,3,40000001,ID - Indonesia,KG,8000,IDR,Traded,4,6.5\n";
            echo "2014,12,12,40000001,Medco - Papua,KG,5000,IDR,Best Bidder,5,8\n";
            echo "2014,12,14,40000001,Medco - Papua,KG,5000,IDR,Best Bidder,5,0\n";
            echo "2014,12,15,40000001,Rotterdam,KG,5000,IDR,Best Bidder,5,4\n";
            exit();        
}
if($param=='AWS'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=AWS_sample.csv");
            echo "tanggal(dd-mm-yyyy),waktu,temp_out,hi_temp,low_temp,out_hum,dew_pt,wind_speed,wind_dir,wind_run,hi_speed,hi_dir,wind_chill,heat_index,thw_index,thsw_index,bar,rain,rain_rate,solar_rad,solar_energy,hi_solar_rad,uv_index,uv_dose,hi_uv,heat_dd,cool_dd,in_temp,in_hum,in_dew,in_heat,in_emc,in_air_density,et,wind_samp,wind_tx,iss_recept,arc_int\n";
            echo "19-07-2020,13:00,25.2,27.2,25,92,23.8,6.4,SSW,6.44,24.1,0,25.2,27.7,27.7,29.7,1009.1,4.6,79.4,187,16.08,624,2.9,1.24,6.7,0,0.287,27.3,82,24,30.8,16.63,0.0709,0.13,1399,1,100,60\n";
            echo "19-07-2020,13:30,23,25.7,22.8,93,21.8,8,WNW,8.05,30.6,0,23,24.5,24.5,25.2,1008,20.2,164.6,96,8.26,257,1.2,0.51,3.1,0,0.194,26.5,81,23,29,16.36,0.0712,0.08,1402,1,100,60\n";
            echo "19-07-2020,14:00,25.4,26.8,23,91,23.8,4.8,SE,4.83,19.3,0,25.4,28,28,32.4,1007.7,0.2,9,333,28.64,726,3.4,1.46,5.8,0,0.294,27.2,83,24.1,30.7,17.01,0.0708,0.23,1398,1,100,60\n";
            echo "19-07-2020,14:30,23.3,26.1,23.3,95,22.5,8,SE,8.05,30.6,0,23.3,25,25,27.1,1008.1,2,23.4,134,11.53,346,0.9,0.39,2.8,0,0.208,26.7,82,23.3,29.4,16.65,0.0711,0.1,1398,1,100,60\n";
            echo "19-07-2020,15:00,23.9,23.9,23.2,93,22.7,3.2,NNW,3.22,8,0,23.9,25.9,25.9,27,1007.6,7.6,53.8,67,5.76,127,0.3,0.13,0.7,0,0.231,26.1,84,23.1,28.6,17.52,0.0712,0.05,1399,1,100,60\n";
            exit();        
}
