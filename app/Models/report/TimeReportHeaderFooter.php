<?php

namespace App\Models\Report;


class TimeReportHeaderFooter extends TTPDF{


    // Page footer
    public function Footer() {

       
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
         $this->SetFont('','', 6);
         $this->setXY( Misc::AdjustXY(75, 0), Misc::AdjustXY(0, -15) );
        $this->Cell(0, 10, TTi18n::gettext('Generated on').' '. Date('Y/M/d - h:i'), 0, false, 'L', 0, '', 0, false, 'T', 'M');


    }
    
    
    
    //Page header
    public function Header() {
	

        $adjust_x = 15;
		$adjust_y = 10;                
        $border = 0;
                
		//Company Logo                 
		$this->Image( $_SESSION['header_data']['image_path'],Misc::AdjustXY(0, $adjust_x+0 ),Misc::AdjustXY(1, $adjust_y+0 ), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);
                

		//Company name
		$this->SetFont('','B',14);

		$this->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

		$this->Cell(5,5, $_SESSION['header_data']['company_name'], $border, 0, 'L');


                
        //Company address
		$this->SetFont('','',10);

		$this->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(6, $adjust_y) );

		$this->Cell(5,5,$_SESSION['header_data']['address1'].' '.$_SESSION['header_data']['address2'], $border, 0, 'L');


        //Company city
		$this->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

		$this->Cell(5,5,$_SESSION['header_data']['city'].', '.$_SESSION['header_data']['province'].' '. strtoupper($_SESSION['header_data']['postal_code']), $border, 0, 'L');
		
		
		if($_SESSION['header_data']['start_date'] != "" && $_SESSION['header_data']['end_date'] != "")
		{
			//Pay Period infomation only text
			$this->SetFont('','',10);
			//from
			$this->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(0, $adjust_y) );
	
			$this->Cell(30,5,TTi18n::gettext('From:').' ', $border, 0, 'R');
			//to
			$this->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(5, $adjust_y) );
	
			$this->Cell(30,5,TTi18n::gettext('To:').' ', $border, 0, 'R');
	
					
					
			//Pay Period infomation Values
			$this->SetFont('','B',10);
			//from
			$this->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(0, $adjust_y) );
	
			$this->Cell(20,5, TTDate::getDate('DATE', $_SESSION['header_data']['start_date'] ) , $border, 0, 'R');
	
			//to
			$this->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(5, $adjust_y) );
	
			$this->Cell(20,5, TTDate::getDate('DATE', $_SESSION['header_data']['end_date'] ) , $border, 0, 'R'); 		
		}
		
                
                
  
                
        if($_SESSION['header_data']['payperiod_string'] != "")//This code use to hide the values
		{
			//Payperiod 
			$this->setLineWidth( 0 );
	
			$this->SetFont('','',10);			
	
			$this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(13, $adjust_y) );
	
			$this->Cell(10, 5, 'Pay Period(s) :', $border, 0, 'L');  
					
					
			$this->SetFont('','',8);
			//from
			$this->setXY( Misc::AdjustXY(24, $adjust_x), Misc::AdjustXY(13, $adjust_y) );
	
			$this->Cell(20,5, $_SESSION['header_data']['payperiod_string'], $border, 0, 'L'); 		
		}        

                
        //CONFIDENTIAL text
		$this->setLineWidth( 0 );

		$this->SetFont('','B',12);				

		$this->setXY( Misc::AdjustXY(165, $adjust_x), Misc::AdjustXY(13, $adjust_y) );

//		$this->Cell(10, 5, 'CONFIDENTIAL', $border, 0, 'R');     
                

                    //Line - start line
                    $this->setLineWidth( 0.5 );

                    $this->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(19, $adjust_y), Misc::AdjustXY($_SESSION['header_data']['line_width'], $adjust_y), Misc::AdjustXY(19, $adjust_y) );

		          
        // Line text "OT Report"
		$this->SetFont('','B',14);

		$this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(21, $adjust_y) );

		$this->Cell(175, 5, TTi18n::gettext($_SESSION['header_data']['heading']), $border, 0, 'L', 0); 
//                echo '<pre>';                print_r($_SESSION['header_data']); die;
                
                 if($_SESSION['header_data']['payperiod_end_date']!= ""){
                    $this->SetFont('','B',8); 
                    $this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(23, $adjust_y) ); 
                    $this->Cell(250, 5, TTi18n::gettext("".$_SESSION['header_data']['payperiod_end_date']), $border, 0, 'R', 0); 
                 }
                 
                 if($_SESSION['header_data']['department_list']!= ""){
                    $this->SetFont('','B',8); 
                    $this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(28, $adjust_y) ); 
                    $this->MultiCell(250, 5, TTi18n::gettext("Department(s): ".$_SESSION['header_data']['department_list']), $border, 'R', 'R', 0); 
                }
                
                    
		//Line - end line
		$this->setLineWidth( 0.20 );     
                
        $this->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(40, $adjust_y), Misc::AdjustXY($_SESSION['header_data']['line_width'], $adjust_y), Misc::AdjustXY(40, $adjust_y) );

        //$this->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(60, $adjust_y) );

        
	     /*if(isset($_SESSION['header_data']['footer_FDHDSL']) && $_SESSION['header_data']['footer_FDHDSL']=='FDHDSL')
		{
			$adjust_x = 20;
			$adjust_y = 150;                
	        $border = 0;

			$this->SetFont('','',8);

			$this->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(21, $adjust_y) );

			$html=  $html.'<table width="100%">'; 
            $html=  $html.'<tr align="center">'; 
            $html= $html.'<td>FD - Fullday Leave / HD - Halfday Leave / SL - Short Leave </td>'; 
            $html=  $html.'</tr>'; 
            $html=  $html.'</table>';

			$this->writeHTML($html, true, false, true, false, ''); 
			

			
		}*/
    
    }

	
}




?>