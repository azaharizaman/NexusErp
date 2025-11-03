<?php

namespace App\Actions\BusinessPartner;

use App\Models\BusinessPartner;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateBusinessPartner
{
    use AsAction;

    public function handle(array $data): BusinessPartner
    {
        return DB::transaction(function () use ($data) {
            return BusinessPartner::create([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'is_supplier' => $data['is_supplier'] ?? false,
                'is_customer' => $data['is_customer'] ?? false,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'website' => $data['website'] ?? null,
                'notes' => $data['notes'] ?? null,
                'parent_business_partner_id' => $data['parent_business_partner_id'] ?? null,
            ]);
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:business_partners,code'],
            'is_supplier' => ['boolean'],
            'is_customer' => ['boolean'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'parent_business_partner_id' => ['nullable', 'integer', 'exists:business_partners,id'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('create_business_partners');
    }

    public function asController(): BusinessPartner
    {
        $validated = request()->validate($this->rules());

        return $this->handle($validated);
    }
}
