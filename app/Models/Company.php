<?php

namespace App\Models;

use AzahariZaman\BackOffice\Models\Company as BaseCompany;

class Company extends BaseCompany
{
    protected static function newFactory(): \AzahariZaman\BackOffice\Database\Factories\CompanyFactory
    {
        return \Database\Factories\CompanyFactory::new();
    }

    public function getCompanyRecordTitleAttribute(): string
    {
        return ' (' . $this->code . ') ' . $this->name;
    }
}
