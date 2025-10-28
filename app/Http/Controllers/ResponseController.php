<?php

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\Report;
use App\Models\Notification; // ✅ Tambahkan
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    /**
     * 🟢 Admin menambahkan tanggapan untuk laporan
     */
    public function store(Request $request, Report $report)
    {
        // Pastikan hanya admin yang bisa menanggapi
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Validasi input
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        // Simpan tanggapan baru
        $response = Response::create([
            'report_id' => $report->id,
            'admin_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        // Jika laporan masih "baru", ubah ke "proses"
        if ($report->status === 'baru') {
            $report->update(['status' => 'proses']);
        }

        // 🔔 Buat notifikasi untuk user pelapor
        Notification::create([
            'user_id' => $report->user_id,
            'report_id' => $report->id,
            'message' => 'Laporan kamu telah ditanggapi oleh admin.',
        ]);

        // Kembalikan respon sukses
        return response()->json([
            'status' => true,
            'message' => 'Tanggapan berhasil ditambahkan dan notifikasi dikirim.',
            'data' => $response,
        ]);
    }

    /**
     * 🟡 Menampilkan semua tanggapan untuk 1 laporan
     */
    public function show(Report $report)
    {
        // Ambil semua tanggapan beserta nama admin yang menanggapi
        $responses = $report->responses()->with('admin')->get();

        return response()->json([
            'status' => true,
            'data' => $responses
        ]);
    }
}
