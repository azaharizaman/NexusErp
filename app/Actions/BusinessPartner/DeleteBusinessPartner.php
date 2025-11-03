<?php

namespace App\Actions\BusinessPartner;

use App\Models\BusinessPartner;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteBusinessPartner
{
    use AsAction;

    public function handle(BusinessPartner $partner): void
    {
        DB::transaction(function () use ($partner) {
            $partner->delete();
        });
    }

    public function authorize(BusinessPartner $partner): bool
    {
        return auth()->check() && auth()->user()->can('delete_business_partners');
    }

    public function asController(BusinessPartner $partner): void
    {
        $this->handle($partner);
    }
}
