<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('guard_name', 'web')
            ->whereIn('name', ['Admin', 'Petugas Gudang'])
            ->orderBy('name')
            ->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
        ]);

        if (!in_array($validated['role'], ['Admin', 'Petugas Gudang'], true)) {
            abort(422, 'Role tidak diizinkan.');
        }

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_active' => true,
            ]);

            $user->assignRole($validated['role']);

            ActivityLogger::log(
                'user_created',
                'Admin menambahkan pengguna baru: ' . $user->name,
                $user,
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $validated['role'],
                ]
            );

            return $user;
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::where('guard_name', 'web')
            ->whereIn('name', ['Admin', 'Petugas Gudang'])
            ->orderBy('name')
            ->get();

        $currentRole = $user->getRoleNames()->first();

        return view('admin.users.edit', compact(
            'user',
            'roles',
            'currentRole'
        ));
    }

    public function update(Request $request, User $user)
    {
        $oldRole = $user->getRoleNames()->first();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ];

        // Role tidak boleh diubah sendiri.
        if (auth()->id() !== $user->id) {
            $rules['role'] = [
                'required',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ];
        }

        $validated = $request->validate($rules);

        $newRole = auth()->id() === $user->id
            ? $oldRole
            : $validated['role'];

        if (!in_array($newRole, ['Admin', 'Petugas Gudang'], true)) {
            abort(422, 'Role tidak diizinkan.');
        }

        $result = DB::transaction(function () use (
            $user,
            $validated,
            $oldRole,
            $newRole
        ) {
            // Kunci seluruh Admin aktif sebelum pemeriksaan perubahan role.
            $activeAdmins = User::role('Admin')
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // Kunci akun yang sedang diperbarui.
            $targetUser = User::whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Pastikan role lama masih sesuai dengan data yang dibuka.
            $currentRole = $targetUser->getRoleNames()->first();

            if ($currentRole !== $oldRole) {
                return [
                    'error' => 'Role pengguna telah berubah. Muat ulang halaman dan coba kembali.',
                ];
            }

            // Cegah penghapusan Admin aktif terakhir.
            if (
                $currentRole === 'Admin' &&
                $newRole !== 'Admin' &&
                $targetUser->is_active &&
                $activeAdmins->count() <= 1
            ) {
                return [
                    'error' => 'Admin terakhir yang aktif tidak boleh diubah rolenya.',
                ];
            }

            // Ambil kondisi data sebelum diperbarui.
            $oldName = $targetUser->name;
            $oldEmail = $targetUser->email;

            $targetUser->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if ($currentRole !== $newRole) {
                $targetUser->syncRoles([$newRole]);
            }

            // Catat aktivitas dalam transaksi yang sama.
            ActivityLogger::log(
                'user_updated',
                'Admin memperbarui data pengguna: ' . $targetUser->name,
                $targetUser,
                [
                    'old_name' => $oldName,
                    'new_name' => $targetUser->name,
                    'old_email' => $oldEmail,
                    'new_email' => $targetUser->email,
                    'old_role' => $currentRole,
                    'new_role' => $newRole,
                ]
            );

            return [
                'user' => $targetUser,
            ];
        });

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function editPassword(User $user)
    {
        return view('admin.users.reset-password', compact('user'));
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($request, $user, $validated) {

            // Kunci akun pengguna yang password-nya direset.
            $targetUser = User::whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Perbarui password pengguna.
            $targetUser->update([
                'password' => $validated['password'],
            ]);

            // Hapus seluruh sesi login pengguna.
            $sessions = DB::table('sessions')
                ->where('user_id', $targetUser->id);

            // Jika Admin mereset password sendiri,
            // pertahankan sesi yang sedang digunakan.
            if (auth()->id() === $targetUser->id) {
                $sessions->where(
                    'id',
                    '!=',
                    $request->session()->getId()
                );
            }

            $sessions->delete();

            // Catat aktivitas dalam transaksi yang sama.
            ActivityLogger::log(
                'user_password_reset',
                'Admin melakukan reset password pengguna: ' . $targetUser->name,
                $targetUser,
                [
                    'user_id' => $targetUser->id,
                    'email' => $targetUser->email,
                    'sessions_revoked' => true,
                ]
            );
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Password berhasil direset dan sesi login lama telah dicabut.');
    }

    public function toggleStatus(User $user)
    {
        $result = DB::transaction(function () use ($user) {

            // Kunci seluruh Admin aktif agar proses perubahan status
            // tidak berjalan bersamaan.
            $activeAdmins = User::role('Admin')
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // Ambil ulang pengguna dengan row lock.
            $targetUser = User::whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $newStatus = !$targetUser->is_active;

            // Admin tidak boleh menonaktifkan akunnya sendiri.
            if (!$newStatus && auth()->id() === $targetUser->id) {
                return [
                    'error' => 'Anda tidak dapat menonaktifkan akun sendiri.',
                ];
            }

            // Admin terakhir tidak boleh dinonaktifkan.
            if (
                !$newStatus &&
                $targetUser->hasRole('Admin') &&
                $activeAdmins->count() <= 1
            ) {
                return [
                    'error' => 'Admin terakhir yang aktif tidak boleh dinonaktifkan.',
                ];
            }

            $oldStatus = $targetUser->is_active;

            $targetUser->update([
                'is_active' => $newStatus,
            ]);

            // Catat aktivitas dalam transaksi yang sama.
            ActivityLogger::log(
                $newStatus ? 'user_activated' : 'user_deactivated',
                $newStatus
                    ? 'Admin mengaktifkan akun pengguna: ' . $targetUser->name
                    : 'Admin menonaktifkan akun pengguna: ' . $targetUser->name,
                $targetUser,
                [
                    'user_id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                    'old_status' => $oldStatus ? 'Aktif' : 'Nonaktif',
                    'new_status' => $newStatus ? 'Aktif' : 'Nonaktif',
                ]
            );

            return [
                'user' => $targetUser,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ];
        });

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        $newStatus = $result['new_status'];

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                $newStatus
                    ? 'Akun pengguna berhasil diaktifkan.'
                    : 'Akun pengguna berhasil dinonaktifkan.'
            );
    }
}