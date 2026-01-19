<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get query parameters for filtering
        $search = $request->query('search', '');
        $role = $request->query('role', 'all');
        $status = $request->query('status', 'all'); // 'all', 'active', 'inactive'
        $perPage = $request->query('per_page', 15);

        $query = User::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($role !== 'all') {
            $query->where('role', $role);
        }

        // Apply status filter
        if ($status !== 'all') {
            $query->where('is_active', $status === 'active');
        }

        // Order by latest
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $users = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengambil data users',
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:user,admin',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            
            // Set default is_active jika tidak disertakan
            if (!isset($data['is_active'])) {
                $data['is_active'] = true;
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('users', 'public');
                $data['photo'] = Storage::url($photoPath);
            }

            // Hash password
            $data['password'] = Hash::make($data['password']);

            // Create user
            $user = User::create($data);

            // Remove password from response
            unset($user->password);

            return response()->json([
                'status' => true,
                'message' => 'User berhasil dibuat',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Prevent updating self if trying to change role or email (for safety)
        $currentUser = $request->user();
        $isUpdatingSelf = $currentUser->id === $user->id;

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:6',
            'role' => 'required|in:user,admin',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_photo' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Handle photo
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->photo) {
                    $oldPhoto = str_replace('/storage/', '', $user->photo);
                    Storage::disk('public')->delete($oldPhoto);
                }
                
                $photoPath = $request->file('photo')->store('users', 'public');
                $data['photo'] = Storage::url($photoPath);
            } elseif ($request->boolean('remove_photo') && $user->photo) {
                // Remove existing photo
                $oldPhoto = str_replace('/storage/', '', $user->photo);
                Storage::disk('public')->delete($oldPhoto);
                $data['photo'] = null;
            } else {
                // Keep existing photo
                unset($data['photo']);
            }

            // Update password only if provided
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Update user
            $user->update($data);

            // Refresh user to get updated data
            $user->refresh();

            // Remove password from response
            unset($user->password);

            return response()->json([
                'status' => true,
                'message' => 'User berhasil diperbarui',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        // Prevent user from deleting themselves
        $currentUser = $request->user();
        
        if ($currentUser->id === $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak dapat menghapus akun sendiri'
            ], 422);
        }

        try {
            // Delete user's photo if exists
            if ($user->photo) {
                $photoPath = str_replace('/storage/', '', $user->photo);
                Storage::disk('public')->delete($photoPath);
            }

            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'User berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengambil data user',
            'data' => $user->makeHidden(['password'])
        ]);
    }

    /**
     * Toggle status aktif user
     */
    public function toggleStatus(Request $request, User $user)
    {
        // Prevent user from deactivating themselves
        $currentUser = $request->user();
        
        if ($currentUser->id === $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak dapat menonaktifkan akun sendiri'
            ], 422);
        }

        try {
            $user->update([
                'is_active' => !$user->is_active
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Status user berhasil diubah',
                'data' => [
                    'is_active' => $user->is_active,
                    'status_text' => $user->is_active ? 'aktif' : 'nonaktif'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah status user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}