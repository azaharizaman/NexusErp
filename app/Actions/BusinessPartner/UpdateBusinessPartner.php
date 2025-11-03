<?php

namespace App\Actions\BusinessPartner;

use App\Models\BusinessPartner;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateBusinessPartner
{
    use AsAction;

    public function handle(BusinessPartner $partner, array $data): BusinessPartner
    {
        return DB::transaction(function () use ($partner, $data) {
            $parentId = $data['parent_business_partner_id'] ?? null;

            if ($parentId) {
                if ($partner->id === (int) $parentId) {
                    throw ValidationException::withMessages([
                        'parent_business_partner_id' => 'A business partner cannot link to itself.',
                    ]);
                }

                $descendantIds = $partner->getDescendants()
                    ->pluck('id')
                    ->push($partner->id);

                if ($descendantIds->contains((int) $parentId)) {
                    throw ValidationException::withMessages([
                        'parent_business_partner_id' => 'A business partner cannot link to one of its descendants.',
                    ]);
                }
            }

            $partner->fill([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'is_supplier' => $data['is_supplier'] ?? false,
                'is_customer' => $data['is_customer'] ?? false,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'website' => $data['website'] ?? null,
                'notes' => $data['notes'] ?? null,
                'parent_business_partner_id' => $parentId,
            ]);

            $partner->save();

            return $partner->refresh();
        });
    }

    public function rules(BusinessPartner $partner): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:business_partners,code,'.$partner->id],
            'is_supplier' => ['boolean'],
            'is_customer' => ['boolean'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'parent_business_partner_id' => ['nullable', 'integer', 'exists:business_partners,id'],
        ];
    }

    public function authorize(BusinessPartner $partner): bool
    {
        return auth()->check() && auth()->user()->can('update_business_partners');
    }

    public function asController(BusinessPartner $partner): BusinessPartner
    {
        $validated = request()->validate($this->rules($partner));

        return $this->handle($partner, $validated);
    }
}
