@extends('layouts.app')

@section('title', 'Notifikasi - SiStock')
@section('page-title', 'Notifikasi')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-800">
                Pusat Notifikasi
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Informasi dan pemberitahuan aktivitas inventory.
            </p>
        </div>

        @if (auth()->user()->unreadNotifications()->exists())
            <form action="{{ route('admin.notifications.mark-all-read') }}"
                method="POST">
                @csrf

                <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm
                           font-medium text-white hover:bg-blue-700">
                    <i class="fas fa-check-double mr-1"></i>
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    {{-- Notifikasi --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);

                $colorClasses = match ($data['color'] ?? 'blue') {
                    'red' => 'bg-red-100 text-red-600',
                    'yellow' => 'bg-yellow-100 text-yellow-600',
                    'green' => 'bg-green-100 text-green-600',
                    default => 'bg-blue-100 text-blue-600',
                };
            @endphp

            <form action="{{ route('admin.notifications.read', $notification->id) }}"
                method="POST">
                @csrf

                <button type="submit"
                    class="flex w-full items-start gap-4 border-b p-4 text-left
                           transition hover:bg-gray-50
                           {{ $isUnread ? 'bg-blue-50/50' : 'bg-white' }}">

                    <div class="flex h-11 w-11 shrink-0 items-center
                                justify-center rounded-full {{ $colorClasses }}">
                        <i class="fas {{ $data['icon'] ?? 'fa-bell' }} text-lg"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-semibold text-gray-800">
                                {{ $data['title'] ?? 'Notifikasi' }}
                            </h3>

                            <span class="text-xs text-gray-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm leading-relaxed text-gray-600">
                            {{ $data['message'] ?? '-' }}
                        </p>

                        <div class="mt-2 flex items-center gap-2">
                            @if ($isUnread)
                                <span class="rounded-full bg-blue-100 px-2.5 py-1
                                             text-xs font-medium text-blue-700">
                                    Belum dibaca
                                </span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-1
                                             text-xs font-medium text-gray-500">
                                    Sudah dibaca
                                </span>
                            @endif

                            <span class="text-xs text-blue-600">
                                Lihat detail
                                <i class="fas fa-arrow-right ml-1"></i>
                            </span>
                        </div>
                    </div>
                </button>
            </form>

        @empty
            <div class="px-4 py-16 text-center">
                <i class="fas fa-bell-slash text-4xl text-gray-300"></i>

                <h3 class="mt-4 font-semibold text-gray-700">
                    Belum Ada Notifikasi
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Semua pemberitahuan akan ditampilkan di halaman ini.
                </p>
            </div>
        @endforelse

        @if ($notifications->hasPages())
            <div class="border-t px-4 py-4">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</div>

@endsection