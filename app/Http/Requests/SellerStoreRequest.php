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
            'response_time_label' => ['nullable', 'string', 'max:255'],
            'is_umkm' => ['sometimes', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $nullableFields = [
            'tagline',
            'description',
            'bumn_partner',
            'province_id',
            'city_id',
            'district_id',
            'postal_code',
            'address_line',
            'response_time_label',
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
