<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user');
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $attendances = $query->orderBy('date', 'desc')->get();
        
        return response()->json($attendances);
    }

    public function checkIn(Request $request)
    {
        $userId = Auth::id() ?? 1; // Fallback for testing
        $today = now()->toDateString();
        
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();
            
        if ($attendance && $attendance->check_in_time) {
            return response()->json(['error' => 'Đã chấm công hôm nay'], 400);
        }
        
        $location = $request->input('location', 'Không xác định');
        
        $attendance = Attendance::updateOrCreate(
            ['user_id' => $userId, 'date' => $today],
            [
                'check_in_time' => now()->toTimeString(),
                'check_in_location' => $location
            ]
        );
        
        return response()->json([
            'message' => 'Chấm công thành công',
            'attendance' => $attendance
        ]);
    }
    
    public function checkOut(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $today = now()->toDateString();
        
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();
            
        if (!$attendance || !$attendance->check_in_time) {
            return response()->json(['error' => 'Chưa chấm công vào'], 400);
        }
        
        if ($attendance->check_out_time) {
            return response()->json(['error' => 'Đã chấm công ra'], 400);
        }
        
        $location = $request->input('location', 'Không xác định');
        
        $attendance->update([
            'check_out_time' => now()->toTimeString(),
            'check_out_location' => $location
        ]);
        
        return response()->json([
            'message' => 'Kết thúc chấm công thành công',
            'attendance' => $attendance
        ]);
    }
    
    public function getTodayStatus()
    {
        $userId = Auth::id() ?? 1;
        $today = now()->toDateString();
        
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();
            
        return response()->json([
            'attendance' => $attendance,
            'can_check_in' => !$attendance || !$attendance->check_in_time,
            'can_check_out' => $attendance && $attendance->check_in_time && !$attendance->check_out_time
        ]);
    }
}
