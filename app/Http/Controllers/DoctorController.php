<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::select(['id','name','email','phone','specialization','chamber','visit_fee','doctor_fee','is_active'])
            ->latest('id')->paginate(20);
        return view('doctors.index', compact('doctors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:doctors',
            'phone' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'chamber' => 'nullable|string|max:255',
            'visit_fee' => 'nullable|numeric|min:0',
            'doctor_fee' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
        ]);

        Doctor::create($data);
        return back()->with('success', 'Doctor added.');
    }

    public function update(Request $request, Doctor $doctor)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:doctors,email,' . $doctor->id,
            'phone' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'chamber' => 'nullable|string|max:255',
            'visit_fee' => 'nullable|numeric|min:0',
            'doctor_fee' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $doctor->update($data);
        return back()->with('success', 'Doctor updated.');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return back()->with('success', 'Doctor deleted.');
    }
}
