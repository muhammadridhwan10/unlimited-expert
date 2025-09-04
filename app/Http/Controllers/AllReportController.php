<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Timesheet;
use App\Models\TimeTracker;
use App\Models\Leave;
use App\Models\AttendanceEmployee;
use App\Models\UserOvertime;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AllReportController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $employees = Employee::with(['branch', 'user'])
            ->whereHas('user', function($query) {
                $query->where('is_active', 1)
                      ->whereNotIn('type', ['admin', 'company']);
            })
            ->orderBy('name', 'asc')
            ->get();
        return view('all-report.index', compact('branches', 'employees'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'employee_id' => 'nullable|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $branchId = $request->branch_id;
        $employeeId = $request->employee_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Validate date range (max 365 days)
        $daysDiff = $startDate->diffInDays($endDate);
        if ($daysDiff > 365) {
            return back()->with('error', __('Date range cannot exceed 365 days'));
        }

        // Get employees based on branch and employee filter (only active users, exclude admin/company)
        $employees = Employee::with(['branch', 'user'])
            ->whereHas('user', function($query) {
                $query->where('is_active', 1)
                      ->whereNotIn('type', ['admin', 'company']);
            })
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($employeeId, function ($query) use ($employeeId) {
                return $query->where('id', $employeeId);
            })
            ->orderBy('name', 'asc')
            ->get();

        if ($employees->isEmpty()) {
            return back()->with('error', __('No employees found with the selected criteria'));
        }

        $reportData = [];
        
        // Generate report data for each employee and each day in the date range
        foreach ($employees as $employee) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                
                // Skip if employee doesn't have user_id (can't track time)
                if (!$employee->user_id) {
                    $currentDate->addDay();
                    continue;
                }

                // Get attendance data
                $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                    ->whereDate('date', $dateStr)
                    ->first();

                // Get timesheet data
                $timesheets = Timesheet::where('created_by', $employee->user_id)
                    ->whereDate('date', $dateStr)
                    ->get();

                // Get time tracker data
                $timeTrackers = TimeTracker::where('created_by', $employee->user_id)
                    ->whereDate('start_time', $dateStr)
                    ->get();

                // Get leave/sick letter data
                $leave = Leave::where('employee_id', $employee->id)
                    ->whereDate('start_date', '<=', $dateStr)
                    ->whereDate('end_date', '>=', $dateStr)
                    ->where('status', 'Approve')
                    ->first();

                // Get overtime data from UserOvertime model
                $overtimeData = UserOvertime::where('user_id', $employee->user_id)
                    ->whereDate('start_date', $dateStr)
                    ->where('status', 'Approve')
                    ->first();

                // Calculate total working hours from timesheet
                $totalTimesheetSeconds = 0;
                foreach ($timesheets as $timesheet) {
                    $totalTimesheetSeconds += $this->timeToSeconds($timesheet->time);
                }
                $totalTimesheetFormatted = $this->secondsToHoursMinutes($totalTimesheetSeconds);

                // Get branch working hours and calculate lateness
                $branchWorkingHours = $this->getBranchWorkingHours($employee->branch->name ?? '');
                $lateInfo = $this->calculateLateness($attendance, $branchWorkingHours);

                // Calculate working hours shortage based on 9 hours standard
                $standardWorkTime = 9 * 3600; // 9 hours in seconds
                $shortageSeconds = max(0, $standardWorkTime - $totalTimesheetSeconds);
                $shortageFormatted = $this->secondsToHoursMinutes($shortageSeconds);

                // Get overtime hours from UserOvertime model
                $overtimeFormatted = '00:00';
                if ($overtimeData) {
                    $overtimeFormatted = $overtimeData->total_time ?: '00:00';
                }

                // Determine work status
                $workStatus = $this->determineWorkStatus($attendance, $leave, $totalTimesheetSeconds, $currentDate);

                $reportData[] = [
                    'employee_name' => $employee->name,
                    'branch' => $employee->branch->name ?? '-',
                    'date' => $currentDate->format('Y-m-d'),
                    'day_name' => $currentDate->format('l'),
                    'work_status' => $workStatus,
                    'attendance_status' => $attendance ? $attendance->status : 'Absent',
                    'clock_in' => $attendance ? Carbon::parse($attendance->clock_in)->format('H:i:s') : '-',
                    'clock_out' => $attendance ? Carbon::parse($attendance->clock_out)->format('H:i:s') : '-',
                    'late_duration' => $lateInfo['late_duration'],
                    'has_tracker' => $timeTrackers->count() > 0 ? 'Yes' : 'No',
                    'tracker_count' => $timeTrackers->count(),
                    'total_timesheet_hours' => $totalTimesheetFormatted,
                    'work_shortage' => $shortageFormatted,
                    'overtime_hours' => $overtimeFormatted,
                    'is_on_leave' => $leave ? 'Yes' : 'No',
                    'leave_type' => $leave ? ($leave->leaveType->title ?? 'Unknown') : '-',
                    'leave_reason' => $leave ? $leave->leave_reason : '-',
                    'sick_letter_status' => $leave && $leave->sick_letter ? 'Available' : 'Not Available',
                    'absence_type' => $leave ? $leave->absence_type : '-',
                ];

                $currentDate->addDay();
            }
        }

        if (empty($reportData)) {
            return back()->with('error', __('No data found for the selected period'));
        }

        return $this->generateExcel($reportData, $startDate, $endDate, $branchId, $employeeId);
    }

    public function getEmployeesByBranch(Request $request)
    {
        $branchId = $request->branch_id;
        
        $employees = Employee::with('branch')
            ->whereHas('user', function($query) {
                $query->where('is_active', 1)
                      ->whereNotIn('type', ['admin', 'company']);
            })
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->select('id', 'name', 'employee_id', 'branch_id')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'display_name' => $employee->name . ' (' . $employee->employee_id . ')',
                    'branch_id' => $employee->branch_id
                ];
            });

        return response()->json([
            'success' => true,
            'employees' => $employees
        ]);
    }

    private function getBranchWorkingHours($branchName)
    {
        // Define working hours per branch
        $workingHours = [
            'Pusat' => ['start' => '09:00:00', 'end' => '18:00:00'],
            'Malang' => ['start' => '08:00:00', 'end' => '17:00:00'],
            'Bekasi' => ['start' => '08:30:00', 'end' => '17:30:00'],
        ];

        // Normalize branch name (lowercase, remove spaces)
        $normalizedBranchName = strtolower(trim($branchName));
        
        // Check for exact match first
        if (isset($workingHours[$normalizedBranchName])) {
            return $workingHours[$normalizedBranchName];
        }
        
        // Check for partial matches
        foreach ($workingHours as $key => $hours) {
            if (strpos($normalizedBranchName, $key) !== false) {
                return $hours;
            }
        }
        
        // Default to pusat if no match found
        return $workingHours['Pusat'];
    }

    private function calculateLateness($attendance, $branchWorkingHours)
    {
        if (!$attendance || !$attendance->clock_in) {
            return ['is_late' => false, 'late_duration' => '00:00'];
        }

        $clockIn = Carbon::parse($attendance->clock_in);
        $expectedStart = Carbon::parse($branchWorkingHours['start']);
        
        if ($clockIn->gt($expectedStart)) {
            $lateDurationSeconds = $clockIn->diffInSeconds($expectedStart);
            $lateDurationFormatted = $this->secondsToHoursMinutes($lateDurationSeconds);
            
            return [
                'is_late' => true, 
                'late_duration' => $lateDurationFormatted
            ];
        }
        
        return ['is_late' => false, 'late_duration' => '00:00'];
    }

    private function timeToSeconds($time)
    {
        if (empty($time)) {
            return 0;
        }

        // Handle format HH:MM:SS or HH:MM
        $parts = explode(':', $time);
        
        if (count($parts) == 3) {
            return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
        } elseif (count($parts) == 2) {
            return ($parts[0] * 3600) + ($parts[1] * 60);
        }
        
        return 0;
    }

    private function secondsToHoursMinutes($seconds)
    {
        if ($seconds <= 0) {
            return '00:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function determineWorkStatus($attendance, $leave, $totalWorkSeconds, $date)
    {
        // Check if it's weekend
        $dayOfWeek = $date->dayOfWeek;
        if ($dayOfWeek == 0 || $dayOfWeek == 6) { // Sunday = 0, Saturday = 6
            return 'Weekend';
        }

        // Check if on leave
        if ($leave) {
            return 'On Leave';
        }

        // Check attendance and work hours
        if (!$attendance) {
            return 'Absent';
        }

        if ($attendance->status == 'Present') {
            // Updated to 9 hours standard
            if ($totalWorkSeconds >= (9 * 3600)) { // 9 hours or more
                return 'Full Day';
            } elseif ($totalWorkSeconds >= (4.5 * 3600)) { // 4.5-9 hours
                return 'Partial Day';
            } elseif ($totalWorkSeconds > 0) {
                return 'Minimal Work';
            } else {
                return 'Present - No Work';
            }
        }

        return $attendance->status ?? 'Unknown';
    }

    private function generateExcel($data, $startDate, $endDate, $branchId, $employeeId)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('HR System')
            ->setTitle('HR Comprehensive Report')
            ->setSubject('Employee Attendance and Time Tracking Report')
            ->setDescription('Generated on ' . now()->format('Y-m-d H:i:s'));

        // Set headers (Updated structure - removed Tracker Hours and Hours Difference)
        $headers = [
            'A1' => 'Employee Name',
            'B1' => 'Branch',
            'C1' => 'Date',
            'D1' => 'Day',
            'E1' => 'Work Status',
            'F1' => 'Attendance',
            'G1' => 'Clock In',
            'H1' => 'Clock Out',
            'I1' => 'Late Duration',
            'J1' => 'Has Tracker',
            'K1' => 'Tracker Count',
            'L1' => 'Timesheet Hours',
            'M1' => 'Work Shortage',
            'N1' => 'Overtime Hours',
            'O1' => 'On Leave',
            'P1' => 'Leave Type',
            'Q1' => 'Leave Reason',
            'R1' => 'Sick Letter',
            'S1' => 'Absence Type',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ];
        $sheet->getStyle('A1:S1')->applyFromArray($headerStyle);

        // Set header row height
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Fill data
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item['employee_name']);
            $sheet->setCellValue('B' . $row, $item['branch']);
            $sheet->setCellValue('C' . $row, $item['date']);
            $sheet->setCellValue('D' . $row, $item['day_name']);
            $sheet->setCellValue('E' . $row, $item['work_status']);
            $sheet->setCellValue('F' . $row, $item['attendance_status']);
            $sheet->setCellValue('G' . $row, $item['clock_in']);
            $sheet->setCellValue('H' . $row, $item['clock_out']);
            $sheet->setCellValue('I' . $row, $item['late_duration']);
            $sheet->setCellValue('J' . $row, $item['has_tracker']);
            $sheet->setCellValue('K' . $row, $item['tracker_count']);
            $sheet->setCellValue('L' . $row, $item['total_timesheet_hours']);
            $sheet->setCellValue('M' . $row, $item['work_shortage']);
            $sheet->setCellValue('N' . $row, $item['overtime_hours']);
            $sheet->setCellValue('O' . $row, $item['is_on_leave']);
            $sheet->setCellValue('P' . $row, $item['leave_type']);
            $sheet->setCellValue('Q' . $row, $item['leave_reason']);
            $sheet->setCellValue('R' . $row, $item['sick_letter_status']);
            $sheet->setCellValue('S' . $row, $item['absence_type']);

            // Apply conditional formatting based on work status
            $this->applyConditionalFormatting($sheet, $row, $item);

            $row++;
        }

        // Auto-fit columns
        foreach (range('A', 'S') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Apply borders to data
        $dataStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A1:S' . ($row - 1))->applyFromArray($dataStyle);

        // Apply special styling to frozen columns (Employee info)
        $frozenColumnsStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA'] // Light gray background
            ],
            'font' => [
                'bold' => true
            ]
        ];
        // Apply to frozen columns (A, B, C) for data rows only (skip summary rows)
        $sheet->getStyle('A7:B' . ($row - 1))->applyFromArray($frozenColumnsStyle);

        // Add summary information at the top (this will also set freeze panes)
        $this->addSummaryInfo($sheet, $startDate, $endDate, $branchId, $employeeId, count($data));

        // Generate filename
        $branchName = $branchId ? Branch::find($branchId)->name : 'All_Branches';
        $employeeName = $employeeId ? Employee::find($employeeId)->name : 'All_Employees';
        
        // Clean filename
        $branchName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $branchName);
        $employeeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $employeeName);
        
        $filename = 'HR_Report_' . $branchName . '_' . $employeeName . '_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx';

        // Create writer and save to multiple possible locations
        $writer = new Xlsx($spreadsheet);
        
        // Try multiple methods to create temporary file
        $tempFile = $this->createTempFile($filename);
        
        try {
            $writer->save($tempFile);
            
            // Verify file was created successfully
            if (!file_exists($tempFile) || filesize($tempFile) == 0) {
                throw new \Exception('Failed to create Excel file');
            }
            
            return response()->download($tempFile, $filename)
                ->deleteFileAfterSend(true);
                
        } catch (\Exception $e) {
            // Cleanup temp file if it exists
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
            
            // Log the error
            \Log::error('Excel generation failed: ' . $e->getMessage());
            
            return back()->with('error', __('Failed to generate Excel file. Please try again or contact administrator.'));
        }
    }

    private function createTempFile($filename = 'hr_report')
    {
        // Method 1: Try Laravel storage path first
        $storagePath = storage_path('app/temp');
        if (!is_dir($storagePath)) {
            @mkdir($storagePath, 0755, true);
        }
        
        if (is_writable($storagePath)) {
            $tempFile = $storagePath . '/' . uniqid('hr_report_') . '.xlsx';
            return $tempFile;
        }
        
        // Method 2: Try public storage
        $publicPath = storage_path('app/public/temp');
        if (!is_dir($publicPath)) {
            @mkdir($publicPath, 0755, true);
        }
        
        if (is_writable($publicPath)) {
            $tempFile = $publicPath . '/' . uniqid('hr_report_') . '.xlsx';
            return $tempFile;
        }
        
        // Method 3: Try upload directory
        $uploadPath = public_path('uploads/temp');
        if (!is_dir($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }
        
        if (is_writable($uploadPath)) {
            $tempFile = $uploadPath . '/' . uniqid('hr_report_') . '.xlsx';
            return $tempFile;
        }
        
        // Method 4: Fallback to system temp (with error suppression)
        $tempFile = @tempnam(sys_get_temp_dir(), 'hr_report_');
        if ($tempFile === false) {
            // Method 5: Last resort - use current directory
            $tempFile = base_path('hr_report_' . uniqid() . '.xlsx');
        }
        
        return $tempFile;
    }

    private function applyConditionalFormatting($sheet, $row, $data)
    {
        $workStatusColors = [
            'Full Day' => 'D4EDDA',      // Light green
            'Partial Day' => 'FFF3CD',   // Light yellow  
            'Minimal Work' => 'F8D7DA',  // Light red
            'Absent' => 'F5C6CB',        // Red
            'On Leave' => 'D1ECF1',      // Light blue
            'Weekend' => 'E2E3E5',       // Light gray
            'Present - No Work' => 'FADBD8' // Light orange
        ];

        $workStatus = $data['work_status'];
        if (isset($workStatusColors[$workStatus])) {
            $sheet->getStyle('F' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($workStatusColors[$workStatus]);
        }

        // Highlight lateness
        if ($data['late_duration'] != '00:00') {
            $sheet->getStyle('J' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFE6E6'); // Light red for lateness
        }

        // Highlight overtime
        if ($data['overtime_hours'] != '00:00') {
            $sheet->getStyle('O' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E1F5FE'); // Light blue for overtime
        }

        // Highlight work shortage
        if ($data['work_shortage'] != '00:00') {
            $sheet->getStyle('N' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFEBEE'); // Light red for shortage
        }
    }

    private function addSummaryInfo($sheet, $startDate, $endDate, $branchId, $employeeId, $recordCount)
    {
        // Insert rows at the top for summary
        $sheet->insertNewRowBefore(1, 5);

        // Add summary information
        $sheet->setCellValue('A1', 'HR COMPREHENSIVE REPORT');
        $sheet->setCellValue('A2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
        $sheet->setCellValue('A3', 'Period: ' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'));
        
        $branchName = $branchId ? Branch::find($branchId)->name : 'All Branches';
        $employeeName = $employeeId ? Employee::find($employeeId)->name : 'All Employees';
        
        $sheet->setCellValue('A4', 'Branch: ' . $branchName);
        $sheet->setCellValue('A5', 'Employee: ' . $employeeName . ' | Total Records: ' . $recordCount);

        // Style summary
        $summaryStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ];
        $sheet->getStyle('A1:A5')->applyFromArray($summaryStyle);

        // Make title larger
        $sheet->getStyle('A1')->getFont()->setSize(16);

        // Set freeze panes after adding summary rows
        // freezePane('C7') means:
        // - Freeze columns A, B, C (Employee Name, ID, Branch) - will stay visible when scrolling horizontally
        // - Freeze rows 1-6 (Summary + Headers) - will stay visible when scrolling vertically
        $sheet->freezePane('C7');
    }
}