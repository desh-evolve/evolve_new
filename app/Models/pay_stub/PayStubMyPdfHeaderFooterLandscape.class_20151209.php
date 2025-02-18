<?php


class PayStubMyPdfHeaderFooterLandscape extends TTPDF{


    // Page footer
    public function Footer() {

        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        
        $this->SetFont('','', 6);

		$this->setXY( Misc::AdjustXY(130, 0), Misc::AdjustXY(0, -15) );
        $this->Cell(0, 10, TTi18n::gettext('Pay Slip Summary Generated by').' '. APPLICATION_NAME, 0, false, 'L', 0, '', 0, false, 'T', 'M');

    }
    
    
    
    //Page header
    public function Header() {
	

        $adjust_x = 10;//20
	$adjust_y = 5;//10                
        $border = 0;
                
		//Company Logo                 
		$this->Image( $_SESSION['header_data']['image_path'],Misc::AdjustXY(0, $adjust_x+0 ),Misc::AdjustXY(1, $adjust_y+0 ), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);
                

		//Company name
		$this->SetFont('','B',14);

		$this->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

		$this->Cell(160,5, $_SESSION['header_data']['company_name'], $border, 0, 'C');


                
        //Company address
		$this->SetFont('','',10);

		$this->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(6, $adjust_y) );

		$this->Cell(160,5,$_SESSION['header_data']['address1'].' '.$_SESSION['header_data']['address2'], $border, 0, 'C');


        //Company city
		$this->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

		$this->Cell(160,5,$_SESSION['header_data']['city'].', '.$_SESSION['header_data']['province'].' '. strtoupper($_SESSION['header_data']['postal_code']), $border, 0, 'C');
		
		
		
		if($_SESSION['header_data']['start_date'] != "" && $_SESSION['header_data']['end_date'] != "")
		{
			//Pay Period infomation only text
			$this->SetFont('','',10);
			//from
			$this->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(0, $adjust_y) );
	
			$this->Cell(115,5,TTi18n::gettext('From:').' ', $border, 0, 'R');
			//to
			$this->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(5, $adjust_y) );
	
			$this->Cell(115,5,TTi18n::gettext('To:').' ', $border, 0, 'R');	
					
					
			//Pay Period infomation Values
			$this->SetFont('','B',10);
			//from
			$this->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(0, $adjust_y) );
	
			$this->Cell(105,5, TTDate::getDate('DATE', $_SESSION['header_data']['start_date'] ) , $border, 0, 'R');
	
			//to
			$this->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(5, $adjust_y) );
	
			$this->Cell(105,5, TTDate::getDate('DATE', $_SESSION['header_data']['end_date'] ) , $border, 0, 'R');			
		}
		
		
		
		
		
		if($_SESSION['header_data']['payperiod_string'] != "")//This code use to hide the values
		{
			//Payperiod 
			$this->setLineWidth( 0 );
	
			$this->SetFont('','',10);			
	
			$this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(13, $adjust_y) );
	
			$this->Cell(10, 5, 'Pay Period(s) :', $border, 0, 'L');  
					
					
			$this->SetFont('','',8);
			//payperiod value
			$this->setXY( Misc::AdjustXY(24, $adjust_x), Misc::AdjustXY(13, $adjust_y) );
	
			$this->Cell(20,5, $_SESSION['header_data']['payperiod_string'], $border, 0, 'L'); 			
		}
                        
                
               
                
                
                                

                
        //CONFIDENTIAL text
		$this->setLineWidth( 0 );

		$this->SetFont('','B',12);				

		$this->setXY( Misc::AdjustXY(184, $adjust_x), Misc::AdjustXY(13, $adjust_y) );//165

		$this->Cell(95, 5, 'CONFIDENTIAL', $border, 0, 'R');     
                
                            
		//Line - start line
		$this->setLineWidth( 1 );

		$this->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(19, $adjust_y), Misc::AdjustXY(284, $adjust_y), Misc::AdjustXY(19, $adjust_y) );//270

          
        // Line text "Pay Slip Summary Report"
		$this->SetFont('','B',14);

		$this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(21, $adjust_y) );

		$this->Cell(260, 5, TTi18n::gettext('Pay Slip Summary Report'), $border, 0, 'C', 0);



		//Line - end line
		$this->setLineWidth( 1 );     
                
        $this->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(29, $adjust_y), Misc::AdjustXY(284, $adjust_y), Misc::AdjustXY(29, $adjust_y) );//270

        
    }
    
}




?>