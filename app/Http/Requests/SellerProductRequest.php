<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerProductRequest extends FormRequest
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
        $productId = $this->route('product')?->id;
        $isUpdate = (bool) $productId;
        $storeId = Store::where('user_id', $this->user()?->id)->value('id') ?? $this->route('product')?->store_id;
        $provinceTable = config('laravolt.indonesia.table_prefix').'provinces';
        $cityTable = config('laravolt.indonesia.table_prefix').'cities';
        $districtTable = config('laravolt.indonesia.table_prefix').'districts';

        $visibilityValues = Product::visibilityScopeValues();

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'slug')
                    ->ignore($productId)
                    ->when($storeId, fn ($query) => $query->where('store_id', $storeId))
                    ->whereNull('deleted_at'),
            ],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lte:price'],
            'min_order' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight' => ['required', 'integer', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'item_type' => ['required', Rule::in([
                Product::ITEM_TYPE_PRODUCT,
                Product::ITEM_TYPE_SERVICE,
            ])],
            'status' => ['required', Rule::in([
                Product::STATUS_READY,
                Product::STATUS_PRE_ORDER,
                Product::STATUS_INACTIVE,
            ])],
            'visibility_scope' => ['required', Rule::in($visibilityValues)],
            'location_province_id' => ['required', 'integer', Rule::exists($provinceTable, 'id')],
            'location_city_id' => ['required', 'integer', Rule::exists($cityTable, 'id')],
            'location_district_id' => ['required', 'integer', Rule::exists($districtTable, 'id')],
            'location_postal_code' => ['required', 'string', 'max:10'],
            'is_pdn' => ['sometimes', 'boolean'],
            'shipping_pickup' => ['nullable'],
            'shipping_delivery' => ['nullable'],
            'images' => [$isUpdate ? 'sometimes' : 'required', 'array', 'min:1'],
            'images.*' => ['file', 'image', 'max:5120'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = str()->slug($data['name']);
        }

        $nullableFields = [
            'sale_price',
            'length',
            'width',
            'height',
            'brand',
        ];

        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $integerFields = [
            'category_id',
            'price',
            'sale_price',
            'min_order',
            'stock',
            'weight',
            'location_province_id',
            'location_city_id',
            'location_district_id',
        ];

        foreach ($integerFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $data[$field] = (int) $data[$field];
            }
        }

        $decimalFields = ['length', 'width', 'height'];

        foreach ($decimalFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $data[$field] = (float) $data[$field];
            }
        }

        $booleanFields = ['is_pdn', 'shipping_pickup', 'shipping_delivery'];

        foreach ($booleanFields as $field) {
            if (!array_key_exists($field, $data)) {
                $data[$field] = false;
                continue;
            }

            $value = $data[$field];

            // Convert to boolean
            // Handle: "1", "0", 1, 0, true, false, "true", "false"
            if (is_string($value)) {
                $data[$field] = in_array($value, ['1', 'true', 'yes', 'on'], true);
            } else {
                $data[$field] = (bool) $value;
            }
        }

        return $data;
    }
}
