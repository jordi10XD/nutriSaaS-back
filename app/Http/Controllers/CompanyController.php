<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();

        // Transform to include full logo URL
        $companies->transform(function ($company) {
            $company->logo_url = $company->logo_path ? asset('storage/' . $company->logo_path) : null;
            return $company;
        });

        return response()->json(['data' => $companies], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $company = new Company();
        $company->nombre = $request->nombre;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $company->logo_path = $path;
        }

        // Auto-generate a DB name like clinic_myclinicname_1
        $company->save();
        $safeName = \Illuminate\Support\Str::slug($request->nombre, '_');
        $dbName = $safeName;
        $company->nombre_bd = $dbName;
        $company->save();

        // Dynamically create the new tenant database
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}`");

        // Set the dynamic connection config
        config(['database.connections.tenant.database' => $dbName]);

        // Run the tenant migrations on the new database
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        $company->logo_url = $company->logo_path ? asset('storage/' . $company->logo_path) : null;

        return response()->json([
            'message' => 'Empresa creada exitosamente',
            'data' => $company
        ], 201);
    }
}
