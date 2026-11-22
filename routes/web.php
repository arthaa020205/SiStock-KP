<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Petugas\HomeController;
use App\Http\Controllers\Petugas\CategoryController;
use App\Http\Controllers\Petugas\SupplierController;
use App\Http\Controllers\Petugas\CustomerController;
use App\Http\Controllers\Petugas\UnitController;
use App\Http\Controllers\Petugas\ProductController;
use App\Http\Controllers\Petugas\ProductUnitConversionController;
use App\Http\Controllers\Petugas\InboundController;
use App\Http\Controllers\Petugas\RackLocationController;
use App\Http\Controllers\Petugas\StockController;
use App\Http\Controllers\Petugas\OutboundController;
use App\Http\Controllers\Petugas\TransferController;
use App\Http\Controllers\Petugas\StockOpnameController;
use App\Http\Controllers\Admin\StockOpnameController as AdminStockOpnameController;
use App\Http\Controllers\Admin\StockMovementController as AdminStockMovementController;
use App\Http\Controllers\Admin\StockController as AdminStockController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminNotificationController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');
});

// Halaman utama
Route::get('/', function () {

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->hasRole('Admin')) {
        return redirect()->route('admin.dashboard');
    }

    if (auth()->user()->hasRole('Petugas Gudang')) {
        return redirect()->route('petugas.home');
    }

    abort(403);

});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');
});

Route::middleware(['auth', 'active'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('Petugas Gudang')) {
            return redirect()->route('petugas.home');
        }

        abort(403, 'Anda tidak memiliki hak akses.');
    })->name('dashboard');

    Route::middleware('role:Admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');

            // Kelola Pengguna
            Route::get('/users', [UserController::class, 'index'])
                ->name('users.index');

            Route::get('/users/create', [UserController::class, 'create'])
                ->name('users.create');

            Route::post('/users', [UserController::class, 'store'])
                ->name('users.store');

            Route::get('/users/{user}/edit', [UserController::class, 'edit'])
                ->name('users.edit');

            Route::put('/users/{user}', [UserController::class, 'update'])
                ->name('users.update');

            Route::get('/users/{user}/reset-password', [UserController::class, 'editPassword'])
                ->name('users.reset-password');

            Route::put('/users/{user}/reset-password', [UserController::class, 'updatePassword'])
                ->name('users.update-password');

            Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
                ->name('users.toggle-status');

            Route::get('/stock-opnames', [AdminStockOpnameController::class, 'index'])
                ->name('stock-opnames.index');

            Route::get('/stock-opnames/{stockOpname}', [AdminStockOpnameController::class, 'show'])
                ->name('stock-opnames.show');

            Route::post('/stock-adjustments/{adjustmentRequest}/approve', [AdminStockOpnameController::class, 'approveAdjustment'])
                ->name('stock-adjustments.approve');

            Route::post('/stock-adjustments/{adjustmentRequest}/reject', [AdminStockOpnameController::class, 'rejectAdjustment'])
                ->name('stock-adjustments.reject');

            // Notifikasi Admin
            Route::get('/notifications', [AdminNotificationController::class, 'index'])
                ->name('notifications.index');

            Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'read'])
                ->name('notifications.read');

            Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])
                ->name('notifications.mark-all-read');

            // Riwayat Pergerakan Stok
            Route::get('/stock-movements', [AdminStockMovementController::class, 'index'])
                ->name('stock-movements.index');

            Route::get('/stock-movements/export-excel', [AdminStockMovementController::class, 'exportExcel'])
                ->name('stock-movements.export-excel');

            Route::get('/stock-movements/export-pdf', [AdminStockMovementController::class, 'exportPdf'])
                ->name('stock-movements.export-pdf');

            // Monitoring Stok Barang
            Route::get('/stocks', [AdminStockController::class, 'index'])
                ->name('stocks.index');

            // Log Aktivitas
            Route::get('/activity-logs', [ActivityLogController::class, 'index'])
                ->name('activity-logs.index');
        });

    Route::middleware('role:Petugas Gudang')
        ->prefix('petugas')
        ->name('petugas.')
        ->group(function () {

            Route::get('/home', [HomeController::class, 'index'])
                ->name('home');

            
            Route::resource('categories', CategoryController::class)
                ->except(['show']);

            Route::resource('suppliers', SupplierController::class)
                ->except(['show']);

            Route::resource('customers', CustomerController::class)
                ->except(['show']);
            
            Route::resource('units', UnitController::class)
                ->except(['show']);

            Route::resource('rack-locations', RackLocationController::class)
                ->except(['show']);

            Route::resource('products', ProductController::class)
                ->except(['show']);

            Route::resource('unit-conversions', ProductUnitConversionController::class)
                ->except(['show']);

            // Inbound
            Route::get('/inbounds', [InboundController::class, 'index'])
                ->name('inbounds.index');

            Route::get('/inbounds/create', [InboundController::class, 'create'])
                ->name('inbounds.create');

            Route::post('/inbounds', [InboundController::class, 'store'])
                ->name('inbounds.store');

            Route::post('/inbounds/{inbound}/post', [InboundController::class, 'post'])
                ->name('inbounds.post');

            Route::get('/inbounds/{inbound}', [InboundController::class, 'show'])
                ->name('inbounds.show');

            Route::get('/inbounds/{inbound}/edit', [InboundController::class, 'edit'])
                ->name('inbounds.edit');

            Route::put('/inbounds/{inbound}', [InboundController::class, 'update'])
                ->name('inbounds.update');

            Route::delete('/inbounds/{inbound}', [InboundController::class, 'destroy'])
                ->name('inbounds.destroy');

            Route::get('/stocks', [StockController::class, 'index'])
                ->name('stocks.index');


            // Outbound
            Route::get('/outbounds', [OutboundController::class, 'index'])
                ->name('outbounds.index');

            Route::get('/outbounds/create', [OutboundController::class, 'create'])
                ->name('outbounds.create');

            Route::post('/outbounds', [OutboundController::class, 'store'])
                ->name('outbounds.store');

            // Edit dan Update Draft
            Route::get('/outbounds/{outbound}/edit', [OutboundController::class, 'edit'])
                ->name('outbounds.edit');

            Route::put('/outbounds/{outbound}', [OutboundController::class, 'update'])
                ->name('outbounds.update');

            Route::get('/outbounds/{outbound}', [OutboundController::class, 'show'])
                ->name('outbounds.show');

            Route::post('/outbounds/{outbound}/post', [OutboundController::class, 'post'])
                ->name('outbounds.post');
                

            // Transfer Lokasi Rak
            Route::get('/transfers', [TransferController::class, 'index'])
                ->name('transfers.index');

            Route::get('/transfers/create', [TransferController::class, 'create'])
                ->name('transfers.create');

            Route::post('/transfers', [TransferController::class, 'store'])
                ->name('transfers.store');

            Route::get('/transfers/{transfer}', [TransferController::class, 'show'])
                ->name('transfers.show');

            Route::post('/transfers/{transfer}/post', [TransferController::class, 'post'])
                ->name('transfers.post');

            // Stock Opname
            Route::get('/stock-opnames', [StockOpnameController::class, 'index'])
                ->name('stock-opnames.index');

            Route::get('/stock-opnames/create', [StockOpnameController::class, 'create'])
                ->name('stock-opnames.create');

            Route::post('/stock-opnames', [StockOpnameController::class, 'store'])
                ->name('stock-opnames.store');

            Route::get('/stock-opnames/{stockOpname}', [StockOpnameController::class, 'show'])
                ->name('stock-opnames.show');

            Route::put('/stock-opnames/{stockOpname}/details', [StockOpnameController::class, 'updateDetails'])
                ->name('stock-opnames.details.update');

            Route::post('/stock-opnames/{stockOpname}/submit', [StockOpnameController::class, 'submit'])
                ->name('stock-opnames.submit');
            }); 
    });