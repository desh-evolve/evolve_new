<?php

	/*
	 *ARSP EDIT -->ADD NEW CLASS FOR CREATE FORM C HEADER AND FOOTER
 	 *
	 *
	 */		
class PayStubListFactoryFormE_epf extends TTPDF{


    // Page footer
    public function Footer() {
        
        $this->SetY(-15);
        
        $this->Cell(0, 10, '_ _ _ _ _ _ _ _ _ _ _ _ _ _ ', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        

        // Position at 15 mm from bottom
        $this->SetY(-12);
        // Set font
        //$this->SetFont('helvetica', '', 8);
        $this->SetFont('times','',10);
        // Page number
        $this->Cell(0, 10, 'Signature of Employer ', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        $this->SetFont('helvetica', 'I', 5);
        // Page number
        $this->Cell(0, 10, 'page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        
        
        //$this->SetFont('','', 8);
        $this->SetFont('times','',10);

	$this->setXY( Misc::AdjustXY(80, 0), Misc::AdjustXY(0, -12) );
        $this->Cell(0, 10, TTi18n::gettext('Tel: ').$_SESSION['header_data']['phone'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        $this->setXY( Misc::AdjustXY(107, 0), Misc::AdjustXY(0, -12) );
        $this->Cell(0, 10, TTi18n::gettext('Fax: ').$_SESSION['header_data']['fax'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        $this->setXY( Misc::AdjustXY(135, 0), Misc::AdjustXY(0, -12) );
        $this->Cell(0, 10, TTi18n::gettext('Email: ').$_SESSION['header_data']['email'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        

    }
    
    
    
    //Page header
    public function Header() {
	
          //6 month form c Image        
        // Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)        
        $this->Image( Environment::getBasePath().'interface/images/formc.JPG',Misc::AdjustXY(0, 10 ),Misc::AdjustXY(1, $adjust_y+0 ), 24, 18, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);
        
	// FORM II RETURN
	$this->SetFont('','B',11);
        $this->setXY( Misc::AdjustXY(25, 12), Misc::AdjustXY(8, $adjust_y) );
        $this->Cell(75,5, TTi18n::gettext('e-Returns'), $border, 0, 'L'); 
      
// TOTAL NO OF EMPLOYEE
	$this->SetFont('','',7);
        $this->setXY( Misc::AdjustXY(235, $adjust_x), Misc::AdjustXY(10, $adjust_y) );
        $this->Cell(75,5, TTi18n::gettext('TOTAL NO OF EMPLOYEE : '.$_SESSION['header_data']['num_emp']), $border, 0, 'L');            
        
// TOTAL NO OF EMPLOYEE
	$this->SetFont('','',7);
        $this->setXY( Misc::AdjustXY(235, $adjust_x), Misc::AdjustXY(14, $adjust_y) );
        $this->Cell(75,5, TTi18n::gettext('The Report returns on : '.date('Y-M-d')), $border, 0, 'L');            
        
        
        
        $adjust_x = 10;
	$adjust_y = 13;                
        $border = 0;

        
        $this->SetFont('times','',11);
      //Form
        $this->SetFont('','B',15);

	$this->setXY( Misc::AdjustXY(90, 6), Misc::AdjustXY(0, 4) );

	$this->Cell(75,5, TTi18n::gettext('COUNTRIBUTION DETAILS '), $border, 0, 'C');                
        
         
      
         
      $this->writeHTML($html, true, false, true, false, '');      
      
       

        //table position
        $this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(10, $adjust_y) );
      $this->SetFont('times','B',10);  

 



$html = '<font size="8"><table border="1" width="100%">
  <tr style ="background-color:#CCCCCC;">
    <th width= "8%"><div align="center" >NIC Number</div></th>
    <th width= "9%"><div align="center">Surname</div></th>
    <th width= "6%"><p align="center">Initials</p></th>
    <th width= "7%"><div align="center">Member Number</div></th>
    <th width= "9%"><p align="center">Total Contribution</p>    </th>
    <th width= "7%"><p align="center">Employer`s Contribution</p>    </th>
    <th width= "7%"><p align="center">Member`s Contribution</p>    </th>
    <th width= "7%"><div align="center" >Total Earning</div></th>
    <th width= "5%"><div align="center" >Member Status</div></th>
    <th width= "5%"><div align="center" >Zone</div></th>
    <th width= "6%"><div align="center">Employer Number</div></th>
    <th width= "6%"><div align="center">Contribution Period</div></th>
    <th width= "6%"><div align="center">Data Submission Number</div></th>
    <th width= "6%"><div align="center">Number of days worked</div></th>
    <th width= "6%"><div align="center">Occupation Clasification Grade</div></th>
  </tr>
</table></font>';




$this->writeHTML($html, true, false, false, false, '');



     
    }  
    
    
    


    

    
}




?>