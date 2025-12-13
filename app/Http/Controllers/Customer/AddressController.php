<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function index(Request $request): Response
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Address $address) => [
                'id' => $address->id,
                'label' => $address->label,
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'province_id' => $address->province_id,
                'city_id' => $address->city_id,
                'district_id' => $address->district_id,
                'province' => $address->province,
                'city' => $address->city,
                'district' => $address->district,
                'postal_code' => $address->postal_code,
                'address_line' => $address->address_line,
                'is_default' => $address->is_default,
                'note' => $address->note,
            ]);

        return Inertia::render('Customer/Address', [
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['user_id'] = $request->user()->id;

        if ($data['is_default']) {
            $this->unsetDefault($request->user()->id);
        }

        Address::create($data);

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeOwner($address);

        $data = $this->validateData($request);

        if ($data['is_default']) {
            $this->unsetDefault($request->user()->id, $address->id);
        }

        $address->update($data);

        return Redirect::route('customer.dashboard.address')->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        $this->authorizeOwner($address);

        $address->delete();

        return Redirect::route('customer.dashboard.address')->with('success', 'Alamat berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        $provinceTable = config('laravolt.indonesia.table_prefix').'provinces';
        $cityTable = config('laravolt.indonesia.table_prefix').'cities';
        $districtTable = config('laravolt.indonesia.table_prefix').'districts';

        return $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'province_id' => ['nullable', 'integer', Rule::exists($provinceTable, 'id')],
            'city_id' => ['nullable', 'integer', Rule::exists($cityTable, 'id')],
            'district_id' => ['nullable', 'integer', Rule::exists($districtTable, 'id')],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address_line' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_default' => ['boolean'],
            'note' => ['nullable', 'string'],
        ]);
    }

    private function unsetDefault(int $userId, ?int $exceptId = null): void
    {
        Address::where('user_id', $userId)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }

    private function authorizeOwner(Address $address): void
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
