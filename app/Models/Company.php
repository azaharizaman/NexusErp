<?php

namespace App\Models;

use AzahariZaman\BackOffice\Models\Company as BaseCompany;

class Company extends BaseCompany
{
    public function getCompanyRecordTitleAttribute(): string
    {
        return ' (' . $this->code . ') ' . $this->name;
    }
}