<?php

namespace BrockhausAg\ContaoRecruiteeBundle\Models;

use JsonSerializable;

class CandidatePost implements JsonSerializable {
    public $candidate = '';
    public $offers = array();

    function __construct($candidateData, $offerData)
    {
        $this->candidate = $candidateData;
        $this->offers = $offerData;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}