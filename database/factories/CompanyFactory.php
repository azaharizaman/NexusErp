<?php

namespace Database\Factories;

use App\Models\Company;
use AzahariZaman\BackOffice\Database\Factories\CompanyFactory as BaseCompanyFactory;

/**
 * @extends BaseCompanyFactory
 */
class CompanyFactory extends BaseCompanyFactory
{
    protected $model = Company::class;
}
