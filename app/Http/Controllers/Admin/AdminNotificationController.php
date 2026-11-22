<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class AdminNotificationController extends Controller
{
    /**
     * Menampilkan seluruh notifikasi Admin.
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Tandai satu notifikasi sudah dibaca,
     * kemudian arahkan ke halaman tujuan.
     */
    public function read(DatabaseNotification $notification)
    {
        $user = auth()->user();

        // Pastikan notifikasi memang milik Admin yang login.
        $userNotification = $user->notifications()
            ->whereKey($notification->id)
            ->firstOrFail();

        $userNotification->markAsRead();

        $url = $userNotification->data['url'] ?? null;

        if (!$url) {
            return redirect()
                ->route('admin.notifications.index');
        }

        return redirect()->to($url);
    }

    /**
     * Tandai semua notifikasi sudah dibaca.
     */
    public function markAllRead()
    {
        auth()->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return back()->with(
            'success',
            'Semua notifikasi berhasil ditandai sudah dibaca.'
        );
    }
}
