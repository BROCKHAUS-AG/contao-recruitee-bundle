<?php

declare(strict_types=1);

/*
 * This file is part of Contao Recruitee Bundle.
 *
 * (c) BROCKHAUS AG 2021 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-recruitee-bundle
 */

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