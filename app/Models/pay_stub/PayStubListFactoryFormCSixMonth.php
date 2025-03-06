<?php


/*
 * ARSP ADD NEW CODE --> THIS CLASS ADDED BY ME
 * 
 * FormC 6 Months report
 * ARSP EDIT -->ADD NEW CLASS FOR CREATE FORM C HEADER AND FOOTER
 * This Class use to creae 6 months formC report header and footer
 *
 */	

 namespace App\Models\PayStub;

use App\Models\Core\TTPDF;

class PayStubListFactoryFormCSixMonth extends TTPDF{


    // Page footer
    public function Footer() {

        
        $this->SetFont('','B',7);
        
	$this->setXY( Misc::AdjustXY(10, 0), Misc::AdjustXY(0, -40) );
        
        $this->Cell(0, 10, 'TO BE TAKEN TO THE SUMMARY SHEET', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
	//PAGE TOTAL
        $this->setXY( Misc::AdjustXY(70, 0), Misc::AdjustXY(0, -40) );
        $this->Cell(0, 10, ('PAGE TOTAL : ').$this->getAliasNbPages(), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        //EMPLOYER'S REGISTRATION NO
	$this->setXY( Misc::AdjustXY(10, 0), Misc::AdjustXY(0, -36) );
        $this->Cell(0, 10, ('EMPLOYER\'S REGISTRATION NO : ').$_SESSION['header_data']['epf_registration_no'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        //EMPLOYER NAME
	$this->setXY( Misc::AdjustXY(10, 0), Misc::AdjustXY(0, -32) );
        $this->Cell(0, 10, ('NAME & ADDRESS OF EMPLOYER : ').$_SESSION['header_data']['employer_name'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        
        //EMPLOYER ADDRESS LINE 1
	$this->setXY( Misc::AdjustXY(53, 0), Misc::AdjustXY(0, -29) );
        $this->Cell(0, 10,$_SESSION['header_data']['company_name'], 0, false, 'L', 0, '', 0, false, 'T', 'M');

        
        //EMPLOYER ADDRESS LINE 2
	$this->setXY( Misc::AdjustXY(53, 0), Misc::AdjustXY(0, -26) );
        $this->Cell(0, 10,$_SESSION['header_data']['address1'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
       
        
        //EMPLOYER ADDRESS LINE 3
	$this->setXY( Misc::AdjustXY(53, 0), Misc::AdjustXY(0, -23) );
        $this->Cell(0, 10,$_SESSION['header_data']['address2'].$_SESSION['header_data']['city'].', '.$_SESSION['header_data']['province'].' '. strtoupper($_SESSION['header_data']['postal_code']), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        
        //TELEPHONE NO
	$this->setXY( Misc::AdjustXY(10, 0), Misc::AdjustXY(0, -20) );
        $this->Cell(0, 10, ('TELEPHONE NO : ').$_SESSION['header_data']['phone'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        
        //FAX NO
	$this->setXY( Misc::AdjustXY(10, 0), Misc::AdjustXY(0, -16) );
        $this->Cell(0, 10, ('FAX NO : ').$_SESSION['header_data']['fax'], 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        
        //Duly completed returns should...
	$this->setXY( Misc::AdjustXY(10, 0), Misc::AdjustXY(0, -12) );
        $this->Cell(0, 10, ('Duly completed returns should be sent to :  Manager - Member Accounts, '), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        
        //Employees Trust Fund Board
	$this->setXY( Misc::AdjustXY(61.5, 0), Misc::AdjustXY(0, -9) );
        $this->Cell(0, 10, ('Employees\' Trust Fund Board'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        
        //Date Line
	$this->setXY( Misc::AdjustXY(117, 0), Misc::AdjustXY(0, -21) );
        
        $this->Cell(0, 10, ('_ _ _ _ _ _ _ _ _ _ _ _ _ _ '), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        //Date
	$this->setXY( Misc::AdjustXY(128, 0), Misc::AdjustXY(0, -18) );        
        
        $this->Cell(0, 10, ('Date'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        

        //Signature Line
	$this->setXY( Misc::AdjustXY(177, 0), Misc::AdjustXY(0, -21) );
        
        $this->Cell(0, 10, ('_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _  '), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        //Signature
	$this->setXY( Misc::AdjustXY(205, 0), Misc::AdjustXY(0, -18) );        
        
        $this->Cell(0, 10, ('Signature of Employer'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        

        //I certify thatall the particulars...
        $this->SetFont('','',7);
        
	$this->setXY( Misc::AdjustXY(160, 0), Misc::AdjustXY(0, -40) );        
        
        $this->Cell(0, 10, ('I certify thatall the particulars given above are correct and that no part of the contributions that should be'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        
        
        //paid by us has...
	$this->setXY( Misc::AdjustXY(160, 0), Misc::AdjustXY(0, -35) );        
        
        $this->Cell(0, 10, ('paid by us has been deducted from any employee\'s earnings.'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        

    }
    
    
    
    //Page header
    public function Header() {
        
        
        $adjust_x = 10;
	$adjust_y = 5;                
        $border = 0;
        
        
        //6 month form c Image        
        // Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)        
        $this->Image( Environment::getBasePath().'interface/images/formc.JPG',Misc::AdjustXY(0, $adjust_x ),Misc::AdjustXY(1, $adjust_y+0 ), 24, 18, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);
        
        
	// EMPLOYEES TRUST FUND BOARD
	$this->SetFont('','B',15);

	$this->setXY( Misc::AdjustXY(100, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

	$this->Cell(75,5, ('EMPLOYEES\' TRUST FUND BOARD'), $border, 0, 'C');    
        
        
	// FORM II RETURN
	$this->SetFont('','B',11);

	$this->setXY( Misc::AdjustXY(25, $adjust_x), Misc::AdjustXY(8, $adjust_y) );

	$this->Cell(75,5, ('FORM II RETURN'), $border, 0, 'L');    
        
        
	//Return for the period
	$this->SetFont('','',7);

	$this->setXY( Misc::AdjustXY(85, $adjust_x), Misc::AdjustXY(10, $adjust_y) );
        
        if($_SESSION['header_data']['set'] == 1)
        {
            $set = "Jan to June ".$_SESSION['header_data']['pay_period_year'];
        }
        else
        {
            $set = "July to Dec ".$_SESSION['header_data']['pay_period_year'];
        }

	$this->Cell(75,5, ('Return for the period '.$set), $border, 0, 'L');    
        
        
        
	// Please read instructions before...
	$this->SetFont('','B',8);

	$this->setXY( Misc::AdjustXY(85, $adjust_x), Misc::AdjustXY(15, $adjust_y) );

	$this->Cell(75,5, ('Please read instructions before completing this form'), $border, 0, 'L');    
        
     
	// Delete inapplicable words
	$this->SetFont('','',7);

	$this->setXY( Misc::AdjustXY(185, $adjust_x), Misc::AdjustXY(15, $adjust_y) );

	$this->Cell(75,5, ('Delete inapplicable words'), $border, 0, 'L');    
        
        
	// TOTAL NO OF EMPLOYEE
	$this->SetFont('','',7);

	$this->setXY( Misc::AdjustXY(235, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

	$this->Cell(75,5, ('TOTAL NO OF EMPLOYEE : '.$_SESSION['header_data']['total_employee']), $border, 0, 'L');            
        
        

	// Page No
	$this->SetFont('','',7);

	$this->setXY( Misc::AdjustXY(235, $adjust_x), Misc::AdjustXY(15, $adjust_y) );

	$this->Cell(75,5, ('Page No : '.$this->getAliasNumPage().'/'.$this->getAliasNbPages()), $border, 0, 'L');    
        
        
        //
        // Table code start here
        //
         
        //table position
        $this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(20, $adjust_y) );
        
        $this->SetFont('','B',7);
        
        
        $column_month = array();
        if($_SESSION['header_data']['set'] == 1)
        {
            $column_month[0] = "JAN";
            $column_month[1] = "FEB";
            $column_month[2] = "MAR";
            $column_month[3] = "APRI";
            $column_month[4] = "MAY";
            $column_month[5] = "JUNE";
            
        }
        else
        {
            $column_month[0] = "JULY";
            $column_month[1] = "AUG";
            $column_month[2] = "SEP";
            $column_month[3] = "OCT";
            $column_month[4] = "NOV";
            $column_month[5] = "DEC";            
            
        }


$html = '<table border="1"  width="97%">
  
  <tr>
  
    <td width="14%" rowspan="3" style ="font-weight:bold; font-size:6pt;" ><div align="center">1</div>
      <div align="center" style ="font-weight:bold; font-size:7pt;">Name of Member</div>
    <div align="center" style ="font-size:6pt;">(Surname first followed by initial)</div></td>
    
    <td width="4.5%" rowspan="3" style ="font-size:6pt;"><div align="center">2</div>
    <div align="center">Member\'s</div>
    <div align="center">Number</div></td>
      
    <td width="6.5%" rowspan="3" style ="font-size:6pt;"><div align="center">3</div>
      <div align="center">National</div>
      <div align="center">Identy card no</div></td>
      
    <td width="6%" rowspan="3" style ="font-size:6pt;"><div align="center">4</div>
    <div align="center">Total</div>
    <div align="center">contributions</div></td>
      
    <td colspan="12" width="72%"><div align="center" style ="font-weight:bold; font-size:7pt;">5.  Total gross wages and contribution</div></td>
  </tr>
  
  
  
  <tr>
    <td colspan="2" width="12%"><div align="center">'.$column_month[0].'</div></td>
    <td colspan="2" width="12%"><div align="center">'.$column_month[1].'</div></td>
    <td colspan="2" width="12%"><div align="center">'.$column_month[2].'</div></td>
    <td colspan="2" width="12%"><div align="center">'.$column_month[3].'</div></td>
    <td colspan="2" width="12%"><div align="center">'.$column_month[4].'</div></td>
    <td colspan="2" width="12%"><div align="center">'.$column_month[5].'</div></td>
  </tr>
  <tr>
    <td width="6.5%"><div align="center" style ="font-size:6pt;">Total Earnings</div></td>
    <td width="5.5%"> <div align="center" style ="font-size:6pt;">Contributions</div></td>
    <td width="6.5%"><div align="center" style ="font-size:6pt;">Total Earnings</div></td>
    <td width="5.5%"><div align="center" style ="font-size:6pt;">Contributions</div></td>
    <td width="6.5%"><div align="center" style ="font-size:6pt;">Total Earnings</div></td>
    <td width="5.5%"><div align="center" style ="font-size:6pt;">Contributions</div></td>
    <td width="6.5%"><div align="center" style ="font-size:6pt;">Total Earnings</div></td>
    <td width="5.5%"><div align="center" style ="font-size:6pt;">Contributions</div></td>
    <td width="6.5%"><div align="center" style ="font-size:6pt;">Total Earnings</div></td>
    <td width="5.5%"><div align="center" style ="font-size:6pt;">Contributions</div></td>
    <td width="6.5%"><div align="center" style ="font-size:6pt;">Total Earnings</div></td>
    <td width="5.5%"><div align="center" style ="font-size:6pt;">Contributions</div></td>
  </tr>
  



</table>';


$this->writeHTML($html, true, false, false, false, '');        
        
        
        


        
    }  
    
    
}




?>