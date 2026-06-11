<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    // GET /api/companies
    public function index()
    {
        $companies = Company::all();

        return response()->json([
            'success' => true,
            'data'    => $companies,
        ]);
    }

    // POST /api/companies
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'rfc'           => 'required|string|max:13|unique:companies',
            'contact_email' => 'required|email|unique:companies',
            'phone'         => 'nullable|string|max:20',
            'plan' => 'nullable|string|max:50',
            'active'        => 'boolean',
        ]);

        $company = Company::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $company,
        ], 201);
    }

    // GET /api/companies/{id}
    public function show(Company $company)
    {
        return response()->json([
            'success' => true,
            'data'    => $company,
        ]);
    }

    // PUT /api/companies/{id}
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'rfc'           => 'sometimes|string|max:13|unique:companies,rfc,' . $company->id,
            'contact_email' => 'sometimes|email|unique:companies,contact_email,' . $company->id,
            'phone'         => 'nullable|string|max:20',
            'plan' => 'nullable|string|max:50',
            'active'        => 'boolean',
        ]);

        $company->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $company,
        ]);
    }

    // DELETE /api/companies/{id}
    public function destroy(Company $company)
    {
        $company->delete();

        return response()->json([
            'success' => true,
            'message' => 'Empresa eliminada correctamente',
        ]);
    }
}