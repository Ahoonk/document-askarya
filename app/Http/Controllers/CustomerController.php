<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    use ResolvesCompanyId;

    private function companyCustomers(int $companyId): Builder
    {
        return Customer::query()->where('company_id', $companyId);
    }

    private function serializeCustomer(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'company_id' => $customer->company_id,
            'nama' => $customer->nama,
            'alamat' => $customer->alamat,
            'no_hp' => $customer->no_hp,
            'email' => $customer->email,
            'created_at' => optional($customer->created_at)->toISOString(),
            'updated_at' => optional($customer->updated_at)->toISOString(),
        ];
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $query = $this->companyCustomers($companyId);

        $customers = (clone $query)
            ->orderBy('nama')
            ->get()
            ->map(fn (Customer $customer) => $this->serializeCustomer($customer))
            ->values();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'stats' => [
                'total' => (clone $query)->count(),
                'with_email' => (clone $query)->whereNotNull('email')->count(),
                'with_phone' => (clone $query)->whereNotNull('no_hp')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->getCompanyIdOrRedirect();

        return Inertia::render('Customers/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer = Customer::create([
            'company_id' => $companyId,
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'email' => $validated['email'] ?? null,
        ]);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer berhasil dibuat.');
    }

    public function show(Customer $customer): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($customer->company_id !== $companyId, 403);

        return Inertia::render('Customers/Show', [
            'customer' => $this->serializeCustomer($customer),
        ]);
    }

    public function edit(Customer $customer): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($customer->company_id !== $companyId, 403);

        return Inertia::render('Customers/Edit', [
            'customer' => $this->serializeCustomer($customer),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($customer->company_id !== $companyId, 403);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer->update([
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'email' => $validated['email'] ?? null,
        ]);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($customer->company_id !== $companyId, 403);

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus.');
    }
}
