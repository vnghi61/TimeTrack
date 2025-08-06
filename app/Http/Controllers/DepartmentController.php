<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('manager', 'like', "%{$search}%");
        }
        
        $departments = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json($departments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive'
        ]);

        $department = Department::create($request->all());
        
        return response()->json($department, 201);
    }

    public function show(Department $department)
    {
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive'
        ]);

        $department->update($request->all());
        
        return response()->json($department);
    }

    public function destroy(Department $department)
    {
        $department->delete();
        
        return response()->json(['message' => 'Department deleted successfully']);
    }
}
