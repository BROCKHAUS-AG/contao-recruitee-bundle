<?php

namespace BrockhausAg\ContaoRecruiteeBundle\Models;

use JsonSerializable;

class Field implements JsonSerializable {
    public $label;
    public $values;

    function __construct($name, $values = [])
    {
        $this->label = $name;
        $this->values = $values;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}
