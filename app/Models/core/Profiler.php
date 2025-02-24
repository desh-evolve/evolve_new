<?php

namespace App\Models\Core;

class Profiler {
    var $description;
    var $startTime;
    var $endTime;
    var $initTime;
    var $cur_timer;
    var $stack;
    var $trail;
    var $trace;
    var $count;
    var $running;
    var $output_enabled;
    var $trace_enabled;

    /**
    * Initialise the timer. with the current micro time
    */
    function Profiler( $output_enabled=false, $trace_enabled=false)
    {
        $this->description = array();
        $this->startTime = array();
        $this->endTime = array();
        $this->initTime = 0;
        $this->cur_timer = "";
        $this->stack = array();
        $this->trail = "";
        $this->trace = "";
        $this->count = array();
        $this->running = array();
        $this->initTime = $this->getMicroTime();
        $this->output_enabled = $output_enabled;
        $this->trace_enabled = $trace_enabled;
        $this->startTimer('unprofiled');
    }

    // Public Methods

    /**
    *   Start an individual timer
    *   This will pause the running timer and place it on a stack.
    *   @param string $name name of the timer
    *   @param string optional $desc description of the timer
    */
    function startTimer($name, $desc = "") {
        $this->trace .= "start $name\n";
    
        // Initialize stack if not already
        if (!isset($this->stack)) {
            $this->stack = [];
        }
    
        // Push current timer onto stack
        $n = array_push($this->stack, $this->cur_timer ?? null);
    
        // Suspend previous timer if applicable
        if ($n > 1) {
            $this->__suspendTimer($this->stack[$n - 1]);
        }
    
        // Start new timer
        $this->startTime[$name] = $this->getMicroTime();
        $this->cur_timer = $name;
        $this->description[$name] = $desc;
    
        // Increment count or initialize if first time
        $this->count[$name] = ($this->count[$name] ?? 0) + 1;
    }
    

    /**
    *   Stop an individual timer
    *   Restart the timer that was running before this one
    *   @param string $name name of the timer
    */
    function stopTimer($name){
        $this->trace.="stop    $name\n";
        $this->endTime[$name] = $this->getMicroTime();
        if (!is_array($this->running)) {
            $this->running = [];
        }
        if (!array_key_exists($name, $this->running))
            $this->running[$name] = $this->elapsedTime($name);
        else
            $this->running[$name] += $this->elapsedTime($name);
        $this->cur_timer=array_pop($this->stack);
        $this->__resumeTimer($this->cur_timer);
    }

    /**
    *   measure the elapsed time of a timer without stoping the timer if
    *   it is still running
    */
    function elapsedTime($name){
        // This shouldn't happen, but it does once.
        if (!array_key_exists($name,$this->startTime))
            return 0;

        if(array_key_exists($name,$this->endTime)){
            return ($this->endTime[$name] - $this->startTime[$name]);
        } else {
            $now=$this->getMicroTime();
            return ($now - $this->startTime[$name]);
        }
    }//end start_time

    /**
    *   Measure the elapsed time since the profile class was initialised
    *
    */
    function elapsedOverall(){
        $oaTime = $this->getMicroTime() - $this->initTime;
        return($oaTime);
    }//end start_time

    /**
    *   print out a log of all the timers that were registered
    *
    */
    function printTimers($enabled=false)
    {
        if($this->output_enabled||$enabled){
            $TimedTotal = 0;
            $tot_perc = 0;
            ksort($this->description);
            print("<pre>\n");
            $oaTime = $this->getMicroTime() - $this->initTime;
            echo"============================================================================\n";
            echo "                              PROFILER OUTPUT\n";
            echo"============================================================================\n";
            print( "Calls                    Time  Routine\n");
            echo"-----------------------------------------------------------------------------\n";
            while (list ($key, $val) = each($this->description)) {
                $t = $this->elapsedTime($key);
                if ( isset($this->running[$key]) ) {
                    $total = $this->running[$key];
                } else {
                    $total = 0;
                }
                $count = $this->count[$key];
                $TimedTotal += $total;
                $perc = ($total/$oaTime)*100;
                $tot_perc+=$perc;
                // $perc=sprintf("%3.2f", $perc );
                printf( "%3d    %3.4f ms (%3.2f %%)  %s\n", $count, $total*1000, $perc, $key);
            }

            echo "\n";

            $missed=$oaTime-$TimedTotal;
            $perc = ($missed/$oaTime)*100;
            $tot_perc+=$perc;
            // $perc=sprintf("%3.2f", $perc );
            printf( "       %3.4f ms (%3.2f %%)  %s\n", $missed*1000,$perc, "Missed");

            echo"============================================================================\n";

            printf( "       %3.4f ms (%3.2f %%)  %s\n", $oaTime*1000,$tot_perc, "OVERALL TIME");

            echo"============================================================================\n";

            print("</pre>");
        }
    }

    function printTrace( $enabled=false )
    {
        if($this->trace_enabled||$enabled){
            print("<pre>");
            print("Trace\n$this->trace\n\n");
            print("</pre>");
        }
    }

    /// Internal Use Only Functions

    /**
    * Get the current time as accuratly as possible
    *
    */
    function getMicroTime(){
	return microtime(TRUE);
    }

    /**
    * resume  an individual timer
    *
    */
    function __resumeTimer($name){
        $this->trace.="resume  $name\n";
        $this->startTime[$name] = $this->getMicroTime();
    }

    /**
    *   suspend  an individual timer
    *
    */
    function __suspendTimer($name) {
        if (!isset($name) || $name === null) {
            return; // Prevent errors if an invalid name is passed
        }
    
        $this->trace .= "suspend $name\n";
    
        // Record end time
        $this->endTime[$name] = $this->getMicroTime();
    
        // Calculate elapsed time once to avoid multiple calls
        $elapsed = $this->elapsedTime($name);
    
        // Update running time
        $this->running[$name] = ($this->running[$name] ?? 0) + $elapsed;
    }
    
}

function profiler_start($name) {
    if (array_key_exists("midcom_profiler",$GLOBALS))
      $GLOBALS["midcom_profiler"]->startTimer ($name);
}

function profiler_stop($name) {
    if (array_key_exists("midcom_profiler",$GLOBALS))
      $GLOBALS["midcom_profiler"]->stopTimer ($name);
}
?>
