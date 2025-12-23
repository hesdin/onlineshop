<?php

namespace App\Http\Requests;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SellerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('seller') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $storeId = $this->route('store')?->id;
        $provinceTable = config('laravolt.indonesia.table_prefix').'provinces';
        $cityTable = config('laravolt.indonesia.table_prefix').'cities';
        $districtTable = config('laravolt.indonesia.table_prefix').'districts';

        if (! $storeId && $this->user()) {
            $storeId = Store::where('user_id', $this->user()->id)->value('id');
        }

        return [
            // Store Identity - Required
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('stores', 'slug')->ignore($storeId),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'tagline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'string', Rule::in(['umkm', 'vendor', 'koperasi', 'premium'])],
            'tax_status' => ['required', 'string', Rule::in(['pkp', 'non_pkp'])],

            // Optional
            'bumn_partner' => ['nullable', 'string', 'max:255'],
            'is_umkm' => ['sometimes', 'boolean'],

            // Address - Required
            'province_id' => ['required', 'integer', Rule::exists($provinceTable, 'id')],
            'city_id' => ['required', 'integer', Rule::exists($cityTable, 'id')],
            'district_id' => ['required', 'integer', Rule::exists($districtTable, 'id')],
            'postal_code' => ['required', 'string', 'max:10'],
            'address_line' => ['required', 'string', 'max:500'],
            'response_time_label' => ['required', 'string', 'max:255'],

            // Bank - Required
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:255'],

            // Files - Optional (required on submit, checked in controller)
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],

            // Documents - Optional during save (required on submit, checked in controller)
            'ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'npwp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'nib' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'company_statement' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $nullableFields = [
            'phone',
            'tagline',
            'description',
            'bumn_partner',
            'province_id',
            'city_id',
            'district_id',
            'postal_code',
            'address_line',
            'response_time_label',
            'bank_name',
            'bank_account_number',
            'bank_account_name',
        ];

        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
        $data['is_umkm'] = (bool) ($data['is_umkm'] ?? true);

        return $data;
    }
}
