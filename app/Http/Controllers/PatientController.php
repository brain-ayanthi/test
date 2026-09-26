<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%");
            });
        }

        $patients = $query->select(['id','patient_code','name','email','phone','age','gender','last_visit','created_at'])
            ->latest('id')
            ->paginate(20);
        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:0',
            'age_type' => 'nullable|in:year,month,day',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $patient = Patient::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'patient' => $patient]);
        }

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient created successfully.');
    }

    public function show(Patient $patient)
    {
        $patient->load([
            'prescriptions:id,prescription_number,patient_id,prescription_date,medicine_cost,total_fee,status,doctor_id',
            'prescriptions.doctor:id,name',
            'prescriptions.items:id,prescription_id,product_id,drug_name,quantity,total',
            'sales:id,invoice_number,patient_id,sale_date,total,payment_status',
            'sales.items:id,sale_id,product_name,quantity,total',
        ]);
        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:0',
            'age_type' => 'nullable|in:year,month,day',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
        ]);

        $patient->update($data);
        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient updated.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return redirect()->route('patients.index')
            ->with('success', 'Patient deleted.');
    }

    /**
     * JSON endpoint for the patient picker in prescription builder.
     */
    public function searchJson(Request $request)
    {
        $term = $request->input('q', '');
        $patients = Patient::select(['id','patient_code','name','phone','age','gender','email'])
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%")
                  ->orWhere('patient_code', 'like', "%{$term}%");
            })
            ->limit(20)
            ->get();

        return response()->json($patients);
    }
}
