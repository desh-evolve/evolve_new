<?php

class RateLimit {
	protected $sleep = FALSE; //When rate limit is reached, do we sleep or return FALSE?


	protected $id = 1;
	protected $group = 'rate_limit';

	protected $allowed_calls = 25;
	protected $time_frame = 60; //1 minute.

	protected $memory = NULL;

	function __construct() {
		$this->memory = new SharedMemory();

		return TRUE;
	}

	function getID() {
		return $this->id;
	}
	function setID($value) {
		if ( $value != '' ) {
			$this->id = $value;

			return TRUE;
		}

		return FALSE;
	}

	//Define the number of calls to check() allowed over a given time frame.
	function getAllowedCalls() {
		return $this->allowed_calls;
	}
	function setAllowedCalls($value) {
		if ( $value != '' ) {
			$this->allowed_calls = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeFrame() {
		return $this->time_frame;
	}
	function setTimeFrame($value) {
		if ( $value != '' ) {
			$this->time_frame = $value;

			return TRUE;
		}

		return FALSE;
	}

	function setRateData( $data ) {
		return $this->memory->set( $this->group.$this->getID(), $data );
	}
	function getRateData() {
		return $this->memory->get( $this->group.$this->getID() );
	}

	function getAttempts() {
		$rate_data = $this->getRateData();
		if ( isset($rate_data['attempts']) ) {
			return $rate_data['attempts'];
		}

		return FALSE;
	}

	function check() {
		if ( $this->getID() != '' ) {
			$rate_data = $this->getRateData();
			//Debug::Arr($rate_data, 'Failed Attempt Data: ', __FILE__, __LINE__, __METHOD__,10);
			if ( !isset($rate_data['attempts']) ) {
				$rate_data = array(
											'attempts' => 0,
											'first_date' => microtime(TRUE),
											 );
			} elseif ( isset($rate_data['attempts']) ) {
				if ( $rate_data['attempts'] > $this->getAllowedCalls() AND $rate_data['first_date'] >= ( microtime(TRUE)-$this->getTimeFrame() ) ) {
					return FALSE;
				} elseif ( $rate_data['first_date'] < ( microtime(TRUE)-$this->getTimeFrame() ) ) {
					$rate_data['attempts'] = 0;
					$rate_data['first_date'] = microtime(TRUE);
				}
			}

			$rate_data['attempts']++;
			return $this->setRateData( $rate_data );
		}

		return FALSE;
	}

	function delete() {
		return $this->memory->delete( $this->group.$this->getID() );
	}
}
?>