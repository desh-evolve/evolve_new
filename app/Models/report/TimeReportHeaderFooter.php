<?php

namespace App\Models\Report;

use App\Models\Core\TTPDF;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;

class TimeReportHeaderFooter extends TTPDF {

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        $this->SetFont('','', 6);
        $this->setXY(Misc::AdjustXY(75, 0), Misc::AdjustXY(0, -15));
        $this->Cell(0, 10, 'Generated on '. date('Y/M/d - h:i'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }
    
    // Page header
    public function Header() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Safely get header data with defaults
        $headerData = $_SESSION['header_data'] ?? [];
        $defaults = [
            'image_path' => '',
            'company_name' => '',
            'address1' => '',
            'address2' => '',
            'city' => '',
            'province' => '',
            'postal_code' => '',
            'start_date' => '',
            'end_date' => '',
            'payperiod_string' => '',
            'payperiod_end_date' => '',
            'department_list' => '',
            'line_width' => 200, // Default line width
            'heading' => 'Report'
        ];
        
        // Merge with defaults
        $headerData = array_merge($defaults, $headerData);

        $adjust_x = 15;
        $adjust_y = 10;                
        $border = 0;
        
        // Company Logo (only if image path exists)
        if (!empty($headerData['image_path'])) {
            $this->Image(
                $headerData['image_path'],
                Misc::AdjustXY(0, $adjust_x + 0),
                Misc::AdjustXY(1, $adjust_y + 0),
                50, 12, '', '', '', false, 300, '', false, false, 0, true
            );
        }

        // Company name
        $this->SetFont('', 'B', 14);
        $this->setXY(Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(0, $adjust_y));
        $this->Cell(5, 5, $headerData['company_name'], $border, 0, 'L');

        // Company address
        $this->SetFont('', '', 10);
        $this->setXY(Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(6, $adjust_y));
        $this->Cell(5, 5, $headerData['address1'].' '.$headerData['address2'], $border, 0, 'L');

        // Company city
        $this->setXY(Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(10, $adjust_y));
        $this->Cell(5, 5, $headerData['city'].', '.$headerData['province'].' '.strtoupper($headerData['postal_code']), $border, 0, 'L');
        
        // Date range (only if both dates exist)
        if (!empty($headerData['start_date']) && !empty($headerData['end_date'])) {
            // Pay Period information only text
            $this->SetFont('', '', 10);
            // from
            $this->setXY(Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(0, $adjust_y));
            $this->Cell(30, 5, 'From: ', $border, 0, 'R');
            // to
            $this->setXY(Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(5, $adjust_y));
            $this->Cell(30, 5, 'To: ', $border, 0, 'R');
            
            // Pay Period information Values
            $this->SetFont('', 'B', 10);
            // from
            $this->setXY(Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(0, $adjust_y));
            $this->Cell(20, 5, TTDate::getDate('DATE', $headerData['start_date']), $border, 0, 'R');
            // to
            $this->setXY(Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(5, $adjust_y));
            $this->Cell(20, 5, TTDate::getDate('DATE', $headerData['end_date']), $border, 0, 'R');
        }
        
        // Payperiod string
        if (!empty($headerData['payperiod_string'])) {
            $this->setLineWidth(0);
            $this->SetFont('', '', 10);
            $this->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(13, $adjust_y));
            $this->Cell(10, 5, 'Pay Period(s):', $border, 0, 'L');
            
            $this->SetFont('', '', 8);
            $this->setXY(Misc::AdjustXY(24, $adjust_x), Misc::AdjustXY(13, $adjust_y));
            $this->Cell(20, 5, $headerData['payperiod_string'], $border, 0, 'L');
        }

        // Line - start line
        $this->setLineWidth(0.5);
        $this->Line(
            Misc::AdjustXY(0, $adjust_x),
            Misc::AdjustXY(19, $adjust_y),
            Misc::AdjustXY($headerData['line_width'], $adjust_y),
            Misc::AdjustXY(19, $adjust_y)
        );

        // Report heading
        $this->SetFont('', 'B', 14);
        $this->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(21, $adjust_y));
        $this->Cell(175, 5, $headerData['heading'], $border, 0, 'L', 0);
        
        // Payperiod end date
        if (!empty($headerData['payperiod_end_date'])) {
            $this->SetFont('', 'B', 8);
            $this->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(23, $adjust_y));
            $this->Cell(250, 5, $headerData['payperiod_end_date'], $border, 0, 'R', 0);
        }
        
        // Department list
        if (!empty($headerData['department_list'])) {
            $this->SetFont('', 'B', 8);
            $this->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(28, $adjust_y));
            $this->MultiCell(250, 5, 'Department(s): '.$headerData['department_list'], $border, 'R', 'R', 0);
        }
        
        // Line - end line
        $this->setLineWidth(0.20);
        $this->Line(
            Misc::AdjustXY(0, $adjust_x),
            Misc::AdjustXY(40, $adjust_y),
            Misc::AdjustXY($headerData['line_width'], $adjust_y),
            Misc::AdjustXY(40, $adjust_y)
        );
    }
}