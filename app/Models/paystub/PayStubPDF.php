<?php

namespace App\Models\PayStub;

use App\Models\Core\Environment;

class PayStubPDF {
	var $pdf = NULL;
	var $page = NULL;

	var $margin = 10;

	var $pay_stub_id = NULL;

	var $pay_stub_entries = NULL;
	var $pay_stub = NULL;
	var $pay_period = NULL;

	var $user_obj = NULL;
	var $company_obj = NULL;

	function __construct() {
		require_once(Environment::getBasePath().'classes/pdflib/phppdflib.class.php');

        //2.835 pdf units = 1mm  ---- (0,0) = 0,792.0990 (MAX,0) = 611.5095,0
		$this->pdf = new pdffile;

		$this->pdf->debug = 10;

		$this->pdf->set_default('margin', $this->margin);
		//$this->page = $this->pdf->new_page("letter");
		$this->page = $this->pdf->new_page("611.5095x792.0990");

		return TRUE;
	}

	function setPayStubId($id) {
		$this->pay_stub_id = $id;

		$this->getData();

		return TRUE;
	}

	function getData() {
		$psenlf = new PayStubEntryNameListFactory();

		$pslf = new PayStubListFactory();

		//$pslf->getByIdAndUserId($id, $current_user->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, array($sort_column => $sort_order) );
		$pslf->getById( $this->pay_stub_id );

		$pager = new Pager($pslf);

		foreach ($pslf as $pay_stub_obj) {

			//Get pay stub entries.
			$pself = new PayStubEntryListFactory();
			$pself->getByPayStubId( $pay_stub_obj->getId() );

			$prev_type = NULL;
			$description_subscript_counter = 1;
			foreach ($pself as $pay_stub_entry) {
				$description_subscript = NULL;

				$pay_stub_entry_name_obj = $psenlf->getById( $pay_stub_entry->getPayStubEntryNameId() ) ->getCurrent();

				if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {
					$type = $pay_stub_entry_name_obj->getType();
				}

				if ( $pay_stub_entry->getDescription() !== NULL AND $pay_stub_entry->getDescription() !== FALSE ) {
					$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,
															'description' => $pay_stub_entry->getDescription() );

					$description_subscript = $description_subscript_counter;

					$description_subscript_counter++;
				}

				$this->pay_stub_entries[$type][] = array(
											'id' => $pay_stub_entry->getId(),
											'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),
											'type' => $pay_stub_entry_name_obj->getType(),
											'name' => $pay_stub_entry_name_obj->getName(),
											'display_name' => $pay_stub_entry_name_obj->getDescription(),
											'rate' => $pay_stub_entry->getRate(),
											'units' => $pay_stub_entry->getUnits(),
											'ytd_units' => $pay_stub_entry->getYTDUnits(),
											'amount' => $pay_stub_entry->getAmount(),
											'ytd_amount' => $pay_stub_entry->getYTDAmount(),

											'description' => $pay_stub_entry->getDescription(),
											'description_subscript' => $description_subscript,

											'created_date' => $pay_stub_entry->getCreatedDate(),
											'created_by' => $pay_stub_entry->getCreatedBy(),
											'updated_date' => $pay_stub_entry->getUpdatedDate(),
											'updated_by' => $pay_stub_entry->getUpdatedBy(),
											'deleted_date' => $pay_stub_entry->getDeletedDate(),
											'deleted_by' => $pay_stub_entry->getDeletedBy()
											);

				$prev_type = $pay_stub_entry_name_obj->getType();
			}

			//'entries' => $pay_stub_entries,
			$this->pay_stub = array(
								'id' => $pay_stub_obj->getId(),
								'user_id' => $pay_stub_obj->getUser(),
								'pay_period_id' => $pay_stub_obj->getPayPeriod(),
								'advance' => $pay_stub_obj->getAdvance(),
								'status' => $pay_stub_obj->getStatus(),


								'created_date' => $pay_stub_obj->getCreatedDate(),
								'created_by' => $pay_stub_obj->getCreatedBy(),
								'updated_date' => $pay_stub_obj->getUpdatedDate(),
								'updated_by' => $pay_stub_obj->getUpdatedBy(),
								'deleted_date' => $pay_stub_obj->getDeletedDate(),
								'deleted_by' => $pay_stub_obj->getDeletedBy()
							);

			//Get Pay Period information
			$pplf = new PayPeriodListFactory();
			$pay_period_obj = $pplf->getById( $pay_stub_obj->getPayPeriod() )->getCurrent();

			if ( $pay_stub_obj->getAdvance() == TRUE ) {
				$pp_start_date = $pay_period_obj->getStartDate();
				$pp_end_date = $pay_period_obj->getAdvanceEndDate();
				$pp_transaction_date = $pay_period_obj->getAdvanceTransactionDate();
			} else {
				$pp_start_date = $pay_period_obj->getStartDate();
				$pp_end_date = $pay_period_obj->getEndDate();
				$pp_transaction_date = $pay_period_obj->getTransactionDate();
			}

			$this->pay_period = array(
									'start_date' => TTDate::getDate('DATE', $pp_start_date ),
									'end_date' => TTDate::getDate('DATE', $pp_end_date ),
									'transaction_date' => TTDate::getDate('DATE', $pp_transaction_date ),
									);

			//Get User information
			$ulf = new UserListFactory();
			$this->user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();

			//Get company information
			$clf = new CompanyListFactory();
			$this->company_obj = $clf->getById( $this->user_obj->getCompany() )->getCurrent();

		}

	}

	function getPageLeft() {
		return 1;
	}

	function getPageTop() {
		//return 792.0990 - $this->margin;
		return 792.0990 - ($this->margin * 2) - 1;
	}

	function getPageRight() {
		return 611.5095 - ($this->margin * 2) - 1;
	}

	function getPageBottom() {
		return 1;
	}


	function getTopAttributes() {
		$this->top_attributes = array(
										'height' => 40,
										'width' => ( $this->getPageRight() - $this->getPageLeft() ) / 3

										);
		//var_dump($this->top_attributes);
		echo "<Br>\n";
		return TRUE;
	}

	function topLeft() {
		$this->getTopAttributes();

		$param["width"] = 0.5;
		$this->pdf->draw_rectangle(
									$this->getPageTop(),
									$this->getPageLeft(),
									$this->getPageTop() - $this->top_attributes['height'],
									$this->getPageLeft() + $this->top_attributes['width'],
									$this->page,
									$param
								);
		unset($param);

		$param["height"] = 25;
		$param["align"] = "center";
		//$param["fillcolor"] = $this->pdf->get_color('#ff3333');

		$this->pdf->draw_one_paragraph(
									$this->getPageTop(),
									$this->getPageLeft(),
									$this->getPageTop() - $this->top_attributes['height'],
									$this->getPageLeft() + $this->top_attributes['width'],
									"TimeTrex",
									$this->page,
									$param
						);

		return TRUE;
	}

	function topMiddle() {
		$param["width"] = 0.5;

		$this->pdf->draw_rectangle(
									$this->getPageTop(),
									$this->getPageLeft() + $this->top_attributes['width'],
									$this->getPageTop() - $this->top_attributes['height'],
									$this->getPageLeft() + ($this->top_attributes['width'] * 2),
									$this->page,
									$param
								);
		unset($param);
/*
		$company_name = $this->company_obj->getName()."\n";
		$company_name .= $this->company_obj->getAddress1()."\n";
		if ( $this->company_obj->getAddress2() ) {
			$company_name .= $this->company_obj->getAddress2()."\n";
		}
		$company_name .= $this->company_obj->getCity().', '.$this->company_obj->getProvince().' '.$this->company_obj->getPostalCode();
*/
		$param["align"] = "center";
		$param["height"] = 10;
		$param["font"] = 'Helvetica-Bold';

		$this->pdf->draw_paragraph(
									$this->getPageTop(),
									$this->getPageLeft() + $this->top_attributes['width'],
									$this->getPageTop() - $this->top_attributes['height'],
									$this->getPageLeft() + ($this->top_attributes['width'] * 2),
									$company_name,
									$this->page,
									$param
						);

		return TRUE;
	}

	function topRight() {
		$param["width"] = 0.5;

		$this->pdf->draw_rectangle(
									$this->getPageTop(),
									$this->getPageLeft() + ($this->top_attributes['width'] * 2),
									$this->getPageTop() - $this->top_attributes['height'],
									$this->getPageLeft() + ($this->top_attributes['width'] * 3),
									$this->page,
									$param
								);
		unset($param);

		$param["align"] = "right";
		$param["height"] = 10;
		$param["font"] = 'Helvetica-Bold';


		$text = ('Start Date:').' '. $this->pay_period['start_date']."\n";
		$text .= ('End Date:').' '. $this->pay_period['start_date']."\n";
		$text .= ('Payment Date:').' '. $this->pay_period['transaction_date']."\n";

		$this->pdf->draw_paragraph(
									$this->getPageTop(),
									$this->getPageLeft() + ( $this->top_attributes['width'] * 2),
									$this->getPageTop() - $this->top_attributes['height'],
									$this->getPageLeft() + ($this->top_attributes['width'] * 3) - 3,
									$text,
									$this->page,
									$param
						);


		return TRUE;
	}

	function title() {

		$param["width"] = 0.5;
		$this->pdf->draw_rectangle(
									$this->getPageTop() - ($this->top_attributes['height'] + 2),
									$this->getPageLeft(),
									$this->getPageTop() - ($this->top_attributes['height'] + 15),
									$this->getPageRight(),
									$this->page,
									$param
								);
		unset($param);

		$param["align"] = "center";
		$param["height"] = 12;
		$param["font"] = 'Helvetica-Bold';

		$this->pdf->draw_one_paragraph(
									$this->getPageTop() - ($this->top_attributes['height'] ),
									$this->getPageLeft(),
									$this->getPageTop() - ($this->top_attributes['height'] + 15),
									$this->getPageRight(),
									'STATEMENT OF EARNINGS AND DEDUCTIONS',
									$this->page,
									$param
						);

		return TRUE;
	}

	//Converts horizontal percent to units.
	function XPercentToUnits($percent) {
		return ( $this->getPageRight() - $this->getPageLeft() ) * ($percent / 100);
	}

	function Earnings() {
		$start_top = $this->getPageTop() - ($this->top_attributes['height'] + 20);
		$start_left = $this->getPageLeft();

		$height = 20;

		$columns = array(
							'name' => array('value' => 'Earning', 'width' => 16.6),
							'rate' => array('value' => 'Rate', 'width' => 16.6),
							'units' => array('value' => 'Hours', 'width' => 16.6),
							'amount' => array('value' => 'Amount', 'width' => 16.6),
							'ytd_units' => array('value' => 'YTD Hours', 'width' => 16.6),
							'ytd_amount' => array('value' => 'YTD Amount', 'width' => 16.6)
						);

		$param["width"] = 0.5;

		$i=0;
		foreach($columns as $key => $column_data) {
			if ($i == 0) {
				$top = $start_top;
			}

			if ($i == 0) {
				$left = $start_left;
			} else {
				$left = $prev_right;
			}

			$bottom = $top - $height;


			if ( $i == 0) {
				$right = $start_left + $this->XPercentToUnits( $column_data['width'] );
			} else {
				$right = $prev_right + $this->XPercentToUnits( $column_data['width'] );
			}

			//$right = $prev_right + 40;
			/*
					echo "Top: $top<br>\n";
			echo "Left: $left<br>\n";
			echo "Bottom: $bottom<br>\n";
			echo "Right: $right<br>\n";
			*/

			$this->pdf->draw_rectangle(
										$top,
										$left,
										$bottom,
										$right,
										$this->page,
										$param
									);

			$prev_right = $right;

			$i++;

			if ($i == 2) {
				//return true;
			}
		}

		unset($param);

	}

	function test() {
		$this->pdf->draw_rectangle(
									$this->getPageTop(),
									$this->getPageLeft(),
									$this->getPageBottom(),
									$this->getPageRight(),
									$this->page

								);

	}

	function done() {

		$this->topLeft();
		$this->topMiddle();
		$this->topRight();

		$this->title();

		$this->Earnings();

		//$this->test();
		//var_dump($this->pdf->dbs);

		return $this->pdf->generate();
	}
}
?>
