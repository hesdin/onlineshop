<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId = $this->route('store')?->id;
        $currentStoreUserId = $this->route('store')?->user_id;
        $provinceTable = config('laravolt.indonesia.table_prefix').'provinces';
        $cityTable = config('laravolt.indonesia.table_prefix').'cities';
        $districtTable = config('laravolt.indonesia.table_prefix').'districts';
        $isCreate = ! $storeId;
        $requiredDoc = $isCreate ? 'required' : 'nullable';
        $docRules = ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']; // max 5MB

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('stores', 'slug')->ignore($storeId),
            ],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(['umkm', 'vendor', 'koperasi', 'premium'])],
            'tax_status' => ['required', 'string', Rule::in(['pkp', 'non_pkp'])],
            'bumn_partner' => ['nullable', 'string', 'max:255'],
            'province_id' => ['nullable', 'integer', Rule::exists($provinceTable, 'id')],
            'city_id' => ['nullable', 'integer', Rule::exists($cityTable, 'id')],
            'district_id' => ['nullable', 'integer', Rule::exists($districtTable, 'id')],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address_line' => ['nullable', 'string'],
            'is_verified' => ['boolean'],
            'is_umkm' => ['boolean'],
            'response_time_label' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'transactions_count' => ['nullable', 'integer', 'min:0'],
            'user_id' => [
                'required',
                'integer',
                Rule::exists(User::class, 'id')->where(function ($query) use ($currentStoreUserId) {
                    $modelHasRoles = config('permission.table_names.model_has_roles', 'model_has_roles');
                    $rolesTable = config('permission.table_names.roles', 'roles');

                    $query->where(function ($verified) use ($modelHasRoles, $rolesTable) {
                        $verified->whereNotNull('email_verified_at')
                            ->whereIn('id', function ($sub) use ($modelHasRoles, $rolesTable) {
                                $sub->select('model_id')
                                    ->from($modelHasRoles)
                                    ->where('model_type', User::class)
                                    ->whereIn('role_id', function ($roleSub) use ($rolesTable) {
                                        $roleSub->select('id')
                                            ->from($rolesTable)
                                            ->where('name', 'seller');
                                    });
                            });
                    });

                    if ($currentStoreUserId) {
                        $query->orWhere('id', $currentStoreUserId); // izinkan owner lama meski belum verified/role
                    }
                }),
            ],
            'owner_id_card' => [$requiredDoc, ...$docRules],
            'nib_document' => [$requiredDoc, ...$docRules],
            'npwp_document' => ['nullable', ...$docRules],
            'business_license' => ['nullable', ...$docRules],
            'pkp_document' => ['nullable', ...$docRules],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_verified' => filter_var($this->input('is_verified', false), FILTER_VALIDATE_BOOLEAN),
            'is_umkm' => filter_var($this->input('is_umkm', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
