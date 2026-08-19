<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Tpp;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
{
    $bulan = $request->bulan ?? now()->month;
    $tahun = $request->tahun ?? now()->year;

    // Total Pegawai
    $totalPegawai = Pegawai::count();

    // Query TPP berdasarkan filter
    $q = Tpp::query()
        ->where('bulan', $bulan)
        ->where('tahun', $tahun);

    $jumlahPerhitungan = (int) $q->count();
    $totalTppKotor     = (float) $q->sum('tpp_kotor');
    $totalPajak        = (float) $q->sum('pajak');
    $totalZakat        = (float) $q->sum('zakat');
    $totalDiterima     = (float) $q->sum('total_diterima');
    $rataDiterima = $jumlahPerhitungan > 0 
    ? $totalDiterima / $jumlahPerhitungan 
    : 0;
    $top5 = Tpp::with('pegawai')
    ->where('bulan',$bulan)->where('tahun',$tahun)
    ->orderByDesc('total_diterima')
     ->take(5)->get();
    // (Opsional) statistik user per role
    $userAdmin    = class_exists(User::class) ? User::where('role', 'admin')->count() : 0;
    $userOperator = class_exists(User::class) ? User::where('role', 'operator')->count() : 0;
    $userViewer   = class_exists(User::class) ? User::where('role', 'viewer')->count() : 0;

    // Grafik total diterima per bulan (tahun berjalan)
    $perBulan = Tpp::selectRaw('bulan, SUM(total_diterima) as total')
        ->where('tahun', $tahun)
        ->groupBy('bulan')
        ->orderBy('bulan')
        ->get();

    $namaBulan = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

    $labels = [];
    $values = [];
    for ($i=1; $i<=12; $i++) {
        $labels[] = $namaBulan[$i];
        $found = $perBulan->firstWhere('bulan', $i);
        $values[] = $found ? (float) $found->total : 0;
    }

    return view('dashboard.index', compact(
        'bulan','tahun',
        'totalPegawai',
        'jumlahPerhitungan',
        'totalTppKotor','totalPajak','totalZakat','totalDiterima',
        'rataDiterima', 
        'userAdmin','userOperator','userViewer',
        'labels','values'
    ));
}
}