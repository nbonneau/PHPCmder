<?php

namespace Primer\Console\Output;

class StepsManager {
    
    protected $currentStep;
    protected $totalStep;
    
    function __construct($totalStep, $currentStep = 1) {
        $this->currentStep = $currentStep;
        $this->totalStep = $totalStep;
    }
    
    public function incrementStep($number = 1){
        $this->currentStep += $number;
    }
    
    /**
     * @return string
     */
    public function getStepPrefixOutput() {
        return " [Step " . $this->currentStep . "/" . $this->totalStep . "] ";
    }
            
    function getCurrentStep() {
        return $this->currentStep;
    }

    function getTotalStep() {
        return $this->totalStep;
    }

    function setCurrentStep($currentStep) {
        $this->currentStep = $currentStep;
    }

    function setTotalStep($totalStep) {
        $this->totalStep = $totalStep;
    }

}
