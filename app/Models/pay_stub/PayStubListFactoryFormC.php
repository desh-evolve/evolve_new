<?php

	/*
	 *ARSP EDIT -->ADD NEW CLASS FOR CREATE FORM C HEADER AND FOOTER
 	 *
	 *
	 */		
  
namespace App\Models\PayStub;

use App\Models\Core\TTPDF;

class PayStubListFactoryFormC extends TTPDF{


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
	

        $adjust_x = 10;
	$adjust_y = 13;                
        $border = 0;

        
        $this->SetFont('times','',11);
        
        // set some text for example
        $txt .= '   '.$_SESSION['header_data']['company_name']."\n";
        $txt .= '   '.$_SESSION['header_data']['address1']."\n";
        $txt .= '   '.$_SESSION['header_data']['address2']."\n";
        $txt .= '   '.$_SESSION['header_data']['city'].','. strtoupper($_SESSION['header_data']['postal_code']);//ARSP NOTE --> I REMOVE THE PROVINCE FOR THUNDER & NEON


        
        // Address box
        // MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
        $this->MultiCell(75, 30, "\n".$txt, 1, 'L', 0, 1, '10', '25', true);
        
	//Form
	$this->SetFont('times','',10);

	$this->setXY( Misc::AdjustXY(90, 6), Misc::AdjustXY(0, 4) );

	$this->Cell(75,5, ('FORM '), $border, 0, 'C');                
        
        
	//print bold C
	$this->SetFont('times','',16);

	$this->setXY( Misc::AdjustXY(98, 6), Misc::AdjustXY(0, 2.5) );

	$this->Cell(75,5, ('C'), $border, 0, 'C');      
        
        
	//EPF Act No
	$this->SetFont('times','',9);

	$this->setXY( Misc::AdjustXY(135, 6), Misc::AdjustXY(0, 4) );

	$this->Cell(75,5, ('EPF Act No. 15 of 1958'), $border, 0, 'C');          
        
        
        
        //table position
        $this->setXY( Misc::AdjustXY(88, 7), Misc::AdjustXY(0, 12) );
        
      $this->SetFont('times','',10);
      
      
      //html table  
      $html = '<table border="1" cellspacing="0" cellpadding="4" width="88%">
               <tr style="text-align:left;">';
      
      //E.P.F Registration No.
      $html = $html.'<td width="48%">E.P.F Registration No</td>';    
      $html = $html.'<td style="font-family:times new roman;font-weight:bold;" width="52%"></td>'; 
      $html=  $html.'</tr>';
      
      //Contributions for the Month of
      $html=  $html.'<tr style="text-align:left;">';
      $html=  $html.'<td width="48%">Contributions for the Month of</td>';    
      $html = $html.'<td style="font-family:times new roman;font-weight:bold;" width="52%">'.$_SESSION['header_data']['payperiod_string'].'</td>'; 
      $html=  $html.'</tr>';
      
      //Contributions
      $html=  $html.'<tr style="text-align:left;">';
      $html=  $html.'<td width="53%">Contributions</td>';    
      $html = $html.'<td style="font-family:times new roman;font-weight:bold;" width="47%">'.$_SESSION['header_data'][0].'</td>'; 
      $html=  $html.'</tr>';
      
      //Surcharges
      $html=  $html.'<tr style="text-align:left;">';
      $html=  $html.'<td width="53%">Surcharges</td>';    
      $html = $html.'<td style="font-family:times new roman;font-weight:bold;" width="47%"></td>'; 
      $html=  $html.'</tr>';
      
      //Total Remittance
      $html=  $html.'<tr style="text-align:left;">';
      $html=  $html.'<td width="53%">Total Remittance</td>';    
      $html = $html.'<td style="font-family:times new roman;font-weight:bold;" width="47%"></td>'; 
      $html=  $html.'</tr>';      
      
      $html=  $html.'</table>';   
      $this->writeHTML($html, true, false, true, false, '');      
      
      
	//Cheque No      
	$this->SetFont('times','',10);

	$this->setXY( Misc::AdjustXY(94, $adjust_x), Misc::AdjustXY(33, $adjust_y) );

	$this->Cell(75,5, ('Cheque No : '), $border, 0, 'L');    
        
        
        
	//Cheque No  Values     
	$this->SetFont('times','B',10);

	$this->setXY( Misc::AdjustXY(116, 6), Misc::AdjustXY(33, $adjust_y) );

	$this->Cell(65,5, '', $border, 0, 'L');          
        
        
        
        
	//Bank Name and Branch
	$this->SetFont('times','',10);

	$this->setXY( Misc::AdjustXY(94, $adjust_x), Misc::AdjustXY(38, $adjust_y) );

	$this->Cell(75,5, ('Bank Name and Branch : '), $border, 0, 'L');      
        
        
	//Bank Name and Branch Values     
	$this->SetFont('times','B',10);

	$this->setXY( Misc::AdjustXY(134, 6), Misc::AdjustXY(38, $adjust_y) );

	//$this->Cell(65,5, ('Sampath City Office'), $border, 0, 'L');  
	$this->Cell(65,5, '', $border, 0, 'L');           
        
        

        
        	//Bank Name and Branch
	$this->SetFont('times','',10);

	$this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(42, $adjust_y) );

	$this->Cell(5,5, ('This form should be returned duly completed along with the contributions to the Superintendent / EPF in the envelope provided. '), $border, 0, 'L');   
        



        //table position
        $this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(49, $adjust_y) );
      $this->SetFont('times','B',10);  

/*$html = '<table border="1" width="100%">
  <tr style ="background-color:#CCCCCC;">
    <th rowspan="2" width= "28%"><div align="center" >Employee’s Name</div></th>
    <th rowspan="2" width= "11%"><div align="center">NIC No</div></th>
    <th rowspan="2"><p align="center">Member</p></th>
    <th colspan="3" width= "38%"><div align="center">Contributions</div></th>
    <th rowspan="2" width= "15%"><p align="center">Total Earnings</p>    </th>
  </tr>
  <tr style ="background-color:#CCCCCC;">
    <th width= "14%"><div align="center" >Total</div></th>
    <th width= "12%"><div align="center">Employer</div></th>
    <th width= "12%"><div align="center">Employee</div></th>
  </tr>
</table>';*/




$html = '<table border="1" width="100%">
  <tr style ="background-color:#CCCCCC;">
    <th rowspan="2" width= "28%"><div align="center" >Employee’s Name</div></th>
    <th rowspan="2" width= "11%"><div align="center">NIC No</div></th>
    <th rowspan="2" width= "7%"><p align="center">Member</p></th>
    <th colspan="3" width= "38%"><div align="center">Contributions</div></th>
    <th rowspan="2" width= "15%"><p align="center">Total Earnings</p>    </th>
  </tr>
  <tr style ="background-color:#CCCCCC;">
    <th width= "14%"><div align="center" >Total</div></th>
    <th width= "12%"><div align="center">Employer</div></th>
    <th width= "12%"><div align="center">Employee</div></th>
  </tr>
</table>';




$this->writeHTML($html, true, false, false, false, '');



     
    }  
    
    
    


    

    
}




?>