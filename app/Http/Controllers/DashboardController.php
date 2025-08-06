<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        $today = Carbon::today();
        
        // Thống kê cơ bản
        $totalProjects = Project::count();
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'Done')->count();
        $inProgressTasks = Task::where('status', 'In Progress')->count();
        
        // Người đang chấm công hôm nay
        $todayAttendances = Attendance::where('date', $today)
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->with('user')
            ->get();
            
        // Người nghỉ (không chấm công hôm nay)
        $totalUsers = User::count();
        $checkedInUsers = Attendance::where('date', $today)
            ->whereNotNull('check_in_time')
            ->count();
        $absentUsers = $totalUsers - $checkedInUsers;
        
        // Sinh nhật trong 3 tháng tới
        $threeMonthsLater = $today->copy()->addMonths(3);
        $upcomingBirthdays = User::whereRaw("
            (MONTH(birthday) = ? AND DAY(birthday) >= ?) OR
            (MONTH(birthday) > ? AND MONTH(birthday) <= ?) OR
            (MONTH(birthday) = ? AND DAY(birthday) <= ?)
        ", [
            $today->month, $today->day,
            $today->month, $threeMonthsLater->month,
            $threeMonthsLater->month, $threeMonthsLater->day
        ])->get();
        
        return response()->json([
            'projects' => [
                'total' => $totalProjects,
                'active' => Project::where('status', 'In Progress')->count(),
                'completed' => Project::where('status', 'Completed')->count(),
            ],
            'tasks' => [
                'total' => $totalTasks,
                'completed' => $completedTasks,
                'in_progress' => $inProgressTasks,
                'todo' => Task::where('status', 'Todo')->count(),
            ],
            'attendance' => [
                'present' => $checkedInUsers,
                'absent' => $absentUsers,
                'working_now' => $todayAttendances->count(),
                'current_attendees' => $todayAttendances
            ],
            'birthdays' => $upcomingBirthdays
        ]);
    }
}