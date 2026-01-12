<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $admins = User::where('role', 'admin')->paginate(10, ['*'], 'admin_page');
        $teachers = User::where('role', 'guru')->paginate(10, ['*'], 'guru_page');
        
        $query = User::where('role', 'siswa');

        if ($request->has('class_filter') && $request->class_filter != '') {
            $query->where('rombongan_belajar', $request->class_filter);
        }

        $students = $query->paginate(10, ['*'], 'student_page');
        

        $classes = User::where('role', 'siswa')
                    ->whereNotNull('rombongan_belajar')
                    ->distinct()
                    ->pluck('rombongan_belajar')
                    ->sort()
                    ->values();

        // Get total count of all students (unfiltered)
        $totalStudents = User::where('role', 'siswa')->count();

        return view('users.index', compact('admins', 'teachers', 'students', 'classes', 'totalStudents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,guru,siswa',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,guru,siswa',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->role === 'siswa') {
            if ($request->filled('nisn')) {
                $updateData['nisn'] = $request->nisn;
            }
            if ($request->filled('rombongan_belajar')) {
                $updateData['rombongan_belajar'] = $request->rombongan_belajar;
            }
        }

        $user->update($updateData);

        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($request->password)]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'User berhasil diupdate']);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate');
    }

    public function destroy(Request $request, User $user)
    {
        if (auth()->check() && (int) auth()->id() === (int) $user->id) {
            $message = 'Tidak bisa menghapus akun yang sedang login.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }

            return redirect()->route('users.index')->with('error', $message);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            $message = 'User tidak bisa dihapus karena masih dipakai oleh data lain.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 409);
            }

            return redirect()->route('users.index')->with('error', $message);
        } catch (\Throwable $e) {
            report($e);
            $message = 'Terjadi kesalahan saat menghapus user.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }

            return redirect()->route('users.index')->with('error', $message);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'User berhasil dihapus']);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $ids = array_map('intval', $request->ids ?? []);
        if (auth()->check() && in_array((int) auth()->id(), $ids, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus akun yang sedang login dari bulk delete.'
            ], 400);
        }

        User::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus.']);
    }
}
