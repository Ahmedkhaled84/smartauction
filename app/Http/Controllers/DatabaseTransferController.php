<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DatabaseTransferController extends Controller
{
    public function export()
    {
        $path = database_path('database.sqlite');
        if (!File::exists($path)) {
            abort(404, 'Database file not found.');
        }

        $filename = 'smartauction-' . now()->format('Ymd-Hi') . '.sqlite';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/x-sqlite3',
        ]);
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'database_file' => ['required', 'file', 'max:102400'], // 100MB
        ]);

        $uploaded = $data['database_file'];
        $ext = strtolower($uploaded->getClientOriginalExtension());
        if (!in_array($ext, ['sqlite', 'db', 'sqlite3'], true)) {
            return back()->withErrors(['database_file' => 'Invalid database file extension.'])->withInput();
        }

        $path = database_path('database.sqlite');
        $backupPath = database_path('backup-' . now()->format('Ymd-Hi') . '-' . Str::random(6) . '.sqlite');

        if (File::exists($path)) {
            File::copy($path, $backupPath);
        }

        $uploaded->move(database_path(), 'database.sqlite');

        return redirect()->back()->with('status', 'Database imported successfully.');
    }
}
