<?php

	/*
	 *ARSP EDIT -->ADD NEW CLASS FOR CREATE FORM C HEADER AND FOOTER
 	 *
	 *
	 */
        
namespace App\Models\PayStub;

use App\Models\Core\TTPDF;

class PayStubListFactoryForm_ETF extends TTPDF{


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
        $this->Cell(0, 10, ('Tel: ').$_SESSION['header_data']['phone'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        $this->setXY( Misc::AdjustXY(107, 0), Misc::AdjustXY(0, -12) );
        $this->Cell(0, 10, ('Fax: ').$_SESSION['header_data']['fax'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        $this->setXY( Misc::AdjustXY(135, 0), Misc::AdjustXY(0, -12) );
        $this->Cell(0, 10, ('Email: ').$_SESSION['header_data']['email'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        

    }
    
    
    
    //Page header
    public function Header() {
	
          //6 month form c Image        
        // Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)        
        $this->Image( Environment::getBasePath().'interface/images/formc.JPG',Misc::AdjustXY(0, 10 ),Misc::AdjustXY(1, $adjust_y+0 ), 24, 18, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);
        
	// FORM II RETURN
	$this->SetFont('','B',11);
        $this->setXY( Misc::AdjustXY(25, 12), Misc::AdjustXY(8, $adjust_y) );
        $this->Cell(75,5, ("ETF Report "), $border, 0, 'L'); 
      
// TOTAL NO OF EMPLOYEE
	$this->SetFont('','',7);
        $this->setXY( Misc::AdjustXY(163, $adjust_x), Misc::AdjustXY(10, $adjust_y) );
        $this->Cell(75,5, ('TOTAL NO OF EMPLOYEE : '.$_SESSION['header_data']['num_emp']), $border, 0, 'L');            
        
// TOTAL NO OF EMPLOYEE
	$this->SetFont('','',7);
        $this->setXY( Misc::AdjustXY(163, $adjust_x), Misc::AdjustXY(14, $adjust_y) );
        $this->Cell(75,5, ('The Report returns on : '.date('Y-M-d')), $border, 0, 'L');            
        
        
        
        $adjust_x = 10;
	$adjust_y = 13;                
        $border = 0;

        
        $this->SetFont('times','',11);
      //Form
        $this->SetFont('','B',15);

	$this->setXY( Misc::AdjustXY(90, 6), Misc::AdjustXY(0, 4) );

	$this->Cell(20,5, ($_SESSION['header_data']['company_name']), $border, 0, 'C');                
        
         
      
         
      $this->writeHTML($html, true, false, true, false, '');      
      
       

        //table position
        $this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(13, $adjust_y) );
      $this->SetFont('times','B',10);  

 



$html = '<font size="9"><table border="1" width="100%">
  <tr style ="background-color:#CCCCCC;">
    <th width= "10%"><div align="center" >Employer Number</div></th>
    <th width= "10%"><div align="center">ETF No</div></th>
    <th width= "10%"><p align="center">Initials</p></th>
    <th width= "20%"><div align="center">Surname</div></th>
    <th width= "15%"><p align="center">NIC Number</p>    </th>
    <th width= "10%"><p align="center">Period From</p>    </th>
    <th width= "10%"><p align="center">Period To</p>    </th>
    <th width= "13%"><div align="center" >Contribution</div></th>
    
  </tr>
</table></font>';




$this->writeHTML($html, true, false, false, false, '');



     
    }  
    
    
    


    

    
}




?>