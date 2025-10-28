<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * 🟢 Tampilkan semua kategori
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }

    /**
     * 🟠 Tambah kategori baru (Admin)
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name'
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $category
        ], 201);
    }

    /**
     * 🟡 Edit kategori (Admin)
     */
    public function update(Request $request, Category $category)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil diperbarui',
            'data' => $category
        ]);
    }

    /**
     * 🔴 Hapus kategori (Admin)
     */
    public function destroy(Category $category)
    {
        $this->authorizeAdmin();

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil dihapus'
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
