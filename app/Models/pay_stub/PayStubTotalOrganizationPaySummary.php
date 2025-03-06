<?php


namespace App\Models\PayStub;

use App\Models\Core\TTPDF;

class PayStubTotalOrganizationPaySummary extends TTPDF{


    // Page footer
    public function Footer() {

        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        
        $this->SetFont('','', 6);

	$this->setXY( Misc::AdjustXY(85, 0), Misc::AdjustXY(0, -15) );
        
        $this->Cell(0, 10, ('Total Organization Pay Summary Generated By').' '. APPLICATION_NAME, 0, false, 'L', 0, '', 0, false, 'T', 'M');

    }
    
    
    
    //Page header
    public function Header() {


        $adjust_x = 10;
		$adjust_y = 10;                
        $border = 0;
                
		//Company Logo                 
		$this->Image( $_SESSION['header_data']['image_path'],Misc::AdjustXY(0, $adjust_x+ 0 ),Misc::AdjustXY(1, $adjust_y+0 ), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);
                

		//Company name
		$this->SetFont('','B',14);

		$this->setXY( Misc::AdjustXY(60, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

		$this->Cell(75,5, $_SESSION['header_data']['company_name'], $border, 0, 'C');


                
        //Company address
		$this->SetFont('','',10);

		$this->setXY( Misc::AdjustXY(60, $adjust_x), Misc::AdjustXY(6, $adjust_y) );

		$this->Cell(75,5,$_SESSION['header_data']['address1'].' '.$_SESSION['header_data']['address2'], $border, 0, 'C');


        //Company city
		$this->setXY( Misc::AdjustXY(60, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

		$this->Cell(75,5,$_SESSION['header_data']['city'].', '.$_SESSION['header_data']['province'].' '. strtoupper($_SESSION['header_data']['postal_code']), $border, 0, 'C');

                
                
                
                
                
		//Payperiod 
		$this->setLineWidth( 0 );
	
		$this->SetFont('','B',12);			
	
		$this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(14, $adjust_y) );
	
		$this->Cell(10, 5, 'Date :', $border, 0, 'L');  
					
				
		$this->SetFont('','B',10);
		//from
		$this->setXY( Misc::AdjustXY(14, $adjust_x), Misc::AdjustXY(14, $adjust_y) );
	
		$this->Cell(20,5,  $_SESSION['header_data']['payperiod_string'] , $border, 0, 'L'); 		
        
                
               
                
                
                                

                
                //CONFIDENTIAL text
		$this->setLineWidth( 0 );

		$this->SetFont('','B',12);				

		$this->setXY( Misc::AdjustXY(185, $adjust_x), Misc::AdjustXY(14, $adjust_y) );

		$this->Cell(10, 5, 'CONFIDENTIAL', $border, 0, 'R');     
                
                            
		//Line - start line
		$this->setLineWidth( 1 );

		$this->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(20, $adjust_y), Misc::AdjustXY(195, $adjust_y), Misc::AdjustXY(20, $adjust_y) );

          
                // Line text "Pay Slip Summary Report"
		$this->SetFont('','B',14);

		$this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(22, $adjust_y) );

		$this->Cell(185, 5, ('Total Organization Pay Summary'), $border, 0, 'C', 0);



		//Line - end line
		$this->setLineWidth( 1 );     
                
                $this->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(30, $adjust_y), Misc::AdjustXY(195, $adjust_y), Misc::AdjustXY(30, $adjust_y) );
                
                
                
                $this->setLineWidth( 0.20 );   
              

        
    }
    
}




?>