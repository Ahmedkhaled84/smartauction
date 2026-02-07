<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuctionSessionController;
use App\Http\Controllers\DatabaseTransferController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/sellers', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    Route::get('/auction', [AuctionController::class, 'index'])->name('auction.index');
    Route::post('/auction', [AuctionController::class, 'store'])->name('auction.store');
    Route::get('/auction/{auction}/edit', [AuctionController::class, 'edit'])->name('auction.edit');
    Route::put('/auction/{auction}', [AuctionController::class, 'update'])->name('auction.update');

    Route::view('/receipt', 'pages.receipt')->name('receipt');
    Route::view('/after', 'pages.after')->name('after');
    Route::view('/report', 'pages.report')->name('report');
    Route::view('/analysis', 'pages.analysis')->name('analysis');

    Route::post('/auctions', [AuctionSessionController::class, 'store'])->name('auctions.store');
    Route::post('/auctions/load', [AuctionSessionController::class, 'load'])->name('auctions.load');
    Route::get('/auctions/codes', [AuctionSessionController::class, 'codes'])->name('auctions.codes');

    Route::get('/export/subjects', [ExportController::class, 'subjectsCsv'])->name('export.subjects');
    Route::get('/export/auction', [ExportController::class, 'auctionsCsv'])->name('export.auctions');

    Route::get('/database/export', [DatabaseTransferController::class, 'export'])->name('database.export');
    Route::post('/database/import', [DatabaseTransferController::class, 'import'])->name('database.import');
});
