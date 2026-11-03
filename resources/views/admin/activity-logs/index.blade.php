@extends('layouts.app')

@section('content')
<div class="p-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            Log Aktivitas
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Riwayat aktivitas pengguna dalam sistem inventory.
        </p>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Pengguna --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Pengguna
                    </label>

                    <select
                        name="user_id"
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Pengguna</option>

                        @foreach ($users as $user)
                            <option
                                value="{{ $user->id }}"
                                @selected(request('user_id') == $user->id)
                            >
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Aktivitas --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Aktivitas
                    </label>

                    <select
                        name="action"
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Aktivitas</option>

                        @foreach ($actions as $action)
                            <option
                                value="{{ $action }}"
                                @selected(request('action') == $action)
                            >
                                {{ ucwords(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal Mulai --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Dari Tanggal
                    </label>

                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                {{-- Tanggal Akhir --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Sampai Tanggal
                    </label>

                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

            </div>

            <div class="flex flex-wrap gap-2 mt-4">

                <button
                    type="submit"
                    class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-medium hover:bg-slate-700"
                >
                    Filter
                </button>

                <a
                    href="{{ route('admin.activity-logs.index') }}"
                    class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-sm font-medium hover:bg-slate-200"
                >
                    Reset
                </a>

            </div>
        </form>
    </div>

    {{-- Tabel Log Aktivitas --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

        <div class="px-5 py-4 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800">
                Riwayat Aktivitas
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Waktu
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Pengguna
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Aktivitas
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Deskripsi
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Detail
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            IP Address
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($activityLogs as $log)

                        <tr class="hover:bg-slate-50">

                            <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                {{ $log->created_at?->format('d/m/Y H:i:s') }}
                            </td>

                            <td class="px-5 py-4">

                                @if ($log->user)
                                    <div class="font-medium text-slate-800">
                                        {{ $log->user->name }}
                                    </div>

                                    @if ($log->user->email)
                                        <div class="text-xs text-slate-500">
                                            {{ $log->user->email }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400">
                                        Sistem
                                    </span>
                                @endif

                            </td>

                            <td class="px-5 py-4">

                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>

                            </td>

                            <td class="px-5 py-4 text-slate-600 min-w-[300px]">
                                {{ $log->description }}
                            </td>

                            <td class="px-5 py-4 min-w-[200px]">

                            @if (!empty($log->properties))

                                <details class="group">
                                    <summary class="cursor-pointer text-indigo-600 hover:text-indigo-800 font-medium">
                                        Lihat Detail
                                    </summary>

                                    <div class="mt-3 p-3 bg-slate-50 rounded-lg border border-slate-200">

                                        <pre class="text-xs text-slate-600 whitespace-pre-wrap break-words">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

                                    </div>
                                </details>

                            @else

                                <span class="text-slate-400">-</span>

                            @endif

                        </td>

                            <td class="px-5 py-4 text-slate-500 whitespace-nowrap">
                                {{ $log->ip_address ?? '-' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="px-5 py-10 text-center text-slate-500"
                            >
                                Belum ada aktivitas yang tercatat.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($activityLogs->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $activityLogs->links() }}
            </div>
        @endif

    </div>

</div>
@endsection