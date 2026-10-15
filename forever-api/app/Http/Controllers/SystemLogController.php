<?php
namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index()
    {
        // Trae los últimos 500 registros con el nombre del usuario
        $logs = SystemLog::with('user:id,name')->latest()->take(500)->get();
        return response()->json(['data' => $logs]);
    }
}
