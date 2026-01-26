<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * 🟢 Tampilkan semua laporan (Admin)
     */
    public function index()
    {
        $this->authorizeAdmin();

        $reports = Report::with(['user', 'category'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reports
        ]);
    }

    /**
     * 🟡 Tampilkan laporan user yang login
     */
    public function myReports(Request $request)
    {
        $reports = Report::with('category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reports
        ]);
    }

    /**
     * 🟠 Tambah laporan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reports', 'public');
        }

        $report = Report::create([
            'user_id' => $request->user()->id,
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image' => $imagePath,
            'location' => $validated['location'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'status' => 'baru',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Laporan berhasil dibuat',
            'data' => $report->load('category'),
        ], 201);
    }

    /**
     * 🔵 Edit laporan (hanya bisa jika status masih 'baru')
     */
    public function update(Request $request, Report $report)
    {
        // Cek kepemilikan (user hanya bisa edit laporannya sendiri)
        if ($request->user()->role !== 'admin' && $report->user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengedit laporan ini.'
            ], 403);
        }

        // Cek status laporan
        if ($report->status !== 'baru') {
            return response()->json([
                'status' => false,
                'message' => 'Laporan tidak dapat diedit karena status sudah ' . $report->status . '.'
            ], 422);
        }

        // Validasi data
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Handle image update
        $imageData = [];
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($report->image) {
                Storage::disk('public')->delete($report->image);
            }
            $imageData['image'] = $request->file('image')->store('reports', 'public');
        }

        // Persiapkan data untuk update
        $updateData = [
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'] ?? $report->location,
        ];

        // Handle nullable fields
        $updateData['latitude'] = isset($validated['latitude']) && $validated['latitude'] !== '' 
            ? $validated['latitude'] 
            : null;
        
        $updateData['longitude'] = isset($validated['longitude']) && $validated['longitude'] !== '' 
            ? $validated['longitude'] 
            : null;

        // Tambahkan image jika ada
        if (!empty($imageData)) {
            $updateData['image'] = $imageData['image'];
        }

        // Update report
        $report->update($updateData);

        // Load fresh data with category
        $report->load('category');

        return response()->json([
            'status' => true,
            'message' => 'Laporan berhasil diperbarui',
            'data' => $report
        ]);
    }

    /**
     * 🟣 Detail laporan tertentu
     */
    public function show(Report $report)
    {
        $report->load(['user', 'category']);

        return response()->json([
            'status' => true,
            'data' => $report
        ]);
    }

    /**
     * 🟤 Ubah status laporan (Admin)
     */
    public function updateStatus(Request $request, Report $report)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'status' => 'required|in:baru,proses,selesai',
        ]);

        $report->update(['status' => $validated['status']]);

        return response()->json([
            'status' => true,
            'message' => 'Status laporan diperbarui',
            'data' => $report
        ]);
    }

    /**
     * 🔴 Hapus laporan (user boleh hapus miliknya, admin bisa hapus semua)
     */
    public function destroy(Request $request, Report $report)
    {
        if ($request->user()->role !== 'admin' && $report->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($report->image) {
            Storage::disk('public')->delete($report->image);
        }

        $report->delete();

        return response()->json([
            'status' => true,
            'message' => 'Laporan berhasil dihapus'
        ]);
    }

    /**
     * 🧩 Helper: cek role admin
     */
    private function authorizeAdmin()
    {
        if (auth()->user()->role !== 'admin') {
            abort(response()->json(['message' => 'Akses ditolak.'], 403));
        }
    }
}