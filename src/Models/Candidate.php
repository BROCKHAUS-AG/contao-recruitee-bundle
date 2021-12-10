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

class Candidate implements JsonSerializable {
    public string $name;
    public array $sources;
    public array $custom_fields;
    public array $emails;
    public array $phones;
    public ?string $cover_letter;

    function __construct(string $name, array $sources = [], array $customFields = [], $emails,
                         array $phones = array(), ?string $coverLetter)
    {
        $this->name = $name;
        $this->sources = $sources;
        $this->custom_fields = $customFields;
        $this->emails = $emails;
        $this->phones = $phones;
        $this->cover_letter = $coverLetter;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}