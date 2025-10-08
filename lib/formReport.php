<?php
include_once('lib/uElement.php');
//include_once('conf/connection.php');
class formReport {
    /** Attribute **/
    private $_id;
    public $_name;
    public $_primeEls;
    public $_advanceEls;
    public $_page;
    public $_workField;
    public $_detailHeight;
	public $_noPdf;
   
	
    /** Constructor **/
    function formReport($cId,$cPage,$cName=null,$pEls=null,$aEls=null) {
        $this->_id = $cId;
		$this->_page = $cPage;
        $this->_workField = 'workField';
		$this->_detailHeight = 70;
		$this->_noPdf = false;
        is_null($cName) ? $this->_name = ucfirst($cId) : $this->_name = $cName;
        is_null($pEls) ? $this->_primeEls = array() : $this->_primeEls = $pEls;
        is_null($aEls) ? $this->_advanceEls = array() : $this->_advanceEls = $aEls;
    }
    
    /* Add Primary Filter */
    function addPrime($cId,$cName,$cCont=null,$cType=null,$cAlign=null,$cLength=null,$cRefer=null) {
	$this->_primeEls[] = new uElement($cId,$cName,$cCont,$cType,$cAlign,$cLength,$cRefer);
    }
    
    /* Add Advance Filter */
    function addAdvance($cId,$cName,$cCont=null,$cType=null,$cAlign=null,$cLength=null,$cRefer=null) {
	$this->_advanceEls[] = new uElement($cId,$cName,$cCont,$cType,$cAlign,$cLength,$cRefer);
    }
    
    function prep() {
    global $dbname;
	##=== Prep
	# Field
	$primeStr = "";
	$advanceStr = "";
	foreach($this->_primeEls as $els) {
	    switch($els->_type) {
		case 'bulantahun':
		    $primeStr .= "##".$els->_id."_bulan##".$els->_id."_tahun";
		    break;
		default:
		    $primeStr .= "##".$els->_id;
	    }
	}
	foreach($this->_advanceEls as $els) {
	    switch($els->_type) {
		case 'bulantahun':
		    $advanceStr .= "##".$els->_id."_bulan##".$els->_id."_tahun";
		    break;
		default:
		    $advanceStr .= "##".$els->_id;
	    }
	}
	
        ##=== Form
        $fReport = "";
        $fReport .= "<div class='card border-0 shadow-sm mb-3' style='max-width:800px;'>";
        $fReport .= "<div class='card-header bg-primary text-white'>";
        $fReport .= "<h5 class='mb-0'><i class='bi bi-file-text-fill me-2'></i>".$this->_name."</h5>";
        $fReport .= "</div>";
        $fReport .= "<div class='card-body'>";
        $fReport .= "<div class='row g-3'>";

        // Generate form fields
        $fieldCount = count($this->_primeEls);
        $colClass = 'col-md-12'; // Default full width

        foreach($this->_primeEls as $index => $els) {
            // For period type, use two columns layout
            if($els->_type === 'period' || $els->_type === 'periode') {
                $colClass = 'col-md-12';
            } else {
                // For other fields, use single column
                $colClass = 'col-md-12';
            }

            $fReport .= "<div class='".$colClass."'>";
            $fReport .= "<label class='form-label fw-semibold'>".makeElement($els->_id,'label',$els->_name)."</label>";
            $fReport .= $els->genEls();
            $fReport .= "</div>";
        }

        // Buttons
        $fReport .= "<div class='col-md-12'>";
        $fReport .= "<button class='btn btn-info btn-sm me-2' onclick=\"formPrint('preview',0,'".$primeStr."','".$advanceStr."','".$this->_page."',event)\">";
        $fReport .= "<i class='bi bi-eye-fill me-1'></i>Preview</button>";

        if(!$this->_noPdf) {
            $fReport .= "<button class='btn btn-danger btn-sm me-2' onclick=\"formPrint('pdf',0,'".$primeStr."','".$advanceStr."','".$this->_page."',event)\">";
            $fReport .= "<i class='bi bi-file-earmark-pdf-fill me-1'></i>PDF</button>";
        }

        if(!$this->_noExcel) {
            $fReport .= "<button class='btn btn-success btn-sm' onclick=\"formPrint('excel',0,'".$primeStr."','".$advanceStr."','".$this->_page."',event)\">";
            $fReport .= "<i class='bi bi-file-earmark-excel-fill me-1'></i>Excel</button>";
        }

        $fReport .= "</div>";
        $fReport .= "</div></div></div>";

        ##=== Work Field
        $fReport .= "<div class='card border-0 shadow-sm'>";
        $fReport .= "<div class='card-header bg-primary text-white'>";
        $fReport .= "<i class='bi bi-eye-fill me-2'></i><b>Preview</b>";
        $fReport .= "</div>";
        $fReport .= "<div class='card-body'>";
        $fReport .= "<div id='".$this->_workField."' style='overflow:auto;height:".$this->_detailHeight."%'></div>";
        $fReport .= "</div></div>";
        
        return $fReport;
    }
    
    function render() {
        echo $this->prep();
    }
}
?>