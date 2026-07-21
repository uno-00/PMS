<?php

use App\Livewire\Portal\AwardIndex;
use App\Livewire\Portal\BidIndex;
use App\Livewire\Portal\Dashboard;
use App\Livewire\Portal\Login;
use App\Livewire\Portal\OpportunityIndex;
use App\Livewire\Portal\OpportunityShow;
use App\Livewire\Portal\OrderIndex;
use App\Livewire\Portal\Profile;
use App\Livewire\Portal\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Phase 9-17: Bidder / Supplier Portal
|--------------------------------------------------------------------------
| A deliberately separate route file (and Livewire namespace/layout) from
| the internal agency app in routes/web.php, per the "Separate Portal"
| requirement. Bidders authenticate against the same `users` table/`web`
| guard (role = Bidder) but only ever see these routes.
*/

Route::prefix('bidder')->name('bidder.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', Login::class)->name('login');
        Route::get('/register', Register::class)->name('register');
    });

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('bidder.login');
    })->middleware('auth')->name('logout');

    Route::middleware(['auth', 'role:Bidder'])->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/profile', Profile::class)->name('profile');

        Route::prefix('opportunities')->name('opportunities.')->group(function () {
            Route::get('/', OpportunityIndex::class)->name('index');
            Route::get('/{procurement}', OpportunityShow::class)->name('show');
        });

        Route::get('/orders', OrderIndex::class)->name('orders.index');
        Route::get('/bids', BidIndex::class)->name('bids.index');
        Route::get('/awards', AwardIndex::class)->name('awards.index');
    });
});
