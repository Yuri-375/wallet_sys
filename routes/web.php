<?php

use App\Http\Controllers\Web\Admin\AgentController as AdminAgentController;
use App\Http\Controllers\Web\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Web\Admin\BillerController as AdminBillerController;
use App\Http\Controllers\Web\Admin\BillPaymentController as AdminBillPaymentController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\FraudController as AdminFraudController;
use App\Http\Controllers\Web\Admin\FraudRuleController as AdminFraudRuleController;
use App\Http\Controllers\Web\Admin\KycController as AdminKycController;
use App\Http\Controllers\Web\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Web\Admin\StatementController as AdminStatementController;
use App\Http\Controllers\Web\Admin\TopUpController as AdminTopUpController;
use App\Http\Controllers\Web\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Web\Admin\UserController as AdminUserController;
use App\Http\Controllers\Web\Admin\WalletController as AdminWalletController;
use App\Http\Controllers\Web\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Web\Agent\KycController as AgentKycController;
use App\Http\Controllers\Web\Agent\NotificationController as AgentNotificationController;
use App\Http\Controllers\Web\Agent\TopUpController as AgentTopUpController;
use App\Http\Controllers\Web\Agent\UserController as AgentUserController;
use App\Http\Controllers\Web\Auth\AuthController;
use App\Http\Controllers\Web\User\BillPaymentController as UserBillPaymentController;
use App\Http\Controllers\Web\User\DashboardController as UserDashboardController;
use App\Http\Controllers\Web\User\KycController as UserKycController;
use App\Http\Controllers\Web\User\NotificationController as UserNotificationController;
use App\Http\Controllers\Web\User\ProfileController;
use App\Http\Controllers\Web\User\StatementController as UserStatementController;
use App\Http\Controllers\Web\User\TopUpController as UserTopUpController;
use App\Http\Controllers\Web\User\TransferController as UserTransferController;
use App\Http\Controllers\Web\User\WalletController as UserWalletController;
use Illuminate\Support\Facades\Route;

// Authentication

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.store');

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.attempt');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// USER ROUTES

Route::middleware([
    'auth',
    'account.active',
    'role:user',
])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        // Dashboard

        Route::get('/dashboard', [UserDashboardController::class, 'index'])
            ->name('dashboard');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        // KYC
        Route::get('/kyc', [UserKycController::class, 'index'])
            ->name('kyc.index');

        Route::post('/kyc', [UserKycController::class, 'store'])
            ->name('kyc.store');

        // KYC APPROVED ROUTES
        Route::middleware('kyc.approved')->group(function () {
            // Wallet
            Route::get('/wallet', [UserWalletController::class, 'show'])
                ->name('wallet.show');

            Route::get('/transactions', [UserWalletController::class, 'transactions'])
                ->name('transactions.index');

            // Transfers
            Route::get('/transfers', [UserTransferController::class, 'index'])
                ->name('transfers.index');

            Route::get('/transfers/create', [UserTransferController::class, 'create'])
                ->name('transfers.create');

            Route::post('/transfers', [UserTransferController::class, 'store'])
                ->middleware('throttle:5,1')
                ->name('transfers.store');

            Route::get('/transfers/{transfer}/otp', [UserTransferController::class, 'showOtp'])
                ->name('transfers.otp');

            Route::post('/transfers/{transfer}/otp', [UserTransferController::class, 'verifyOtp'])
                ->middleware('throttle:5,1')
                ->name('transfers.otp.verify');

            // Top Ups
            Route::get('/topups', [UserTopUpController::class, 'index'])
                ->name('topups.index');

            // Bill Payments
            Route::get('/bills', [UserBillPaymentController::class, 'index'])
                ->name('bills.index');

            Route::get('/bills/create', [UserBillPaymentController::class, 'create'])
                ->name('bills.create');

            Route::post('/bills', [UserBillPaymentController::class, 'store'])
                ->middleware('throttle:5,1')
                ->name('bills.store');

            // Statements
            Route::get('/statements', [UserStatementController::class, 'index'])
                ->name('statements.index');

            Route::get('/statements/{statement}/download', [UserStatementController::class, 'download'])
                ->name('statements.download');
        });

        // Notifications
        Route::get('/notifications', [UserNotificationController::class, 'index'])
            ->name('notifications.index');

        Route::post('/notifications/{id}/read', [UserNotificationController::class, 'markRead'])
            ->name('notifications.read');

        Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])
            ->name('notifications.read-all');
    });

// AGENT ROUTES
Route::middleware([
    'auth',
    'account.active',
    'role:agent',
])
    ->prefix('agent')
    ->name('agent.')
    ->group(function () {

        // Dashboard
        Route::get('/', [AgentDashboardController::class, 'index'])
            ->name('dashboard');

        // Users
        Route::get('/users', [AgentUserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/{user}', [AgentUserController::class, 'show'])
            ->name('users.show');

        // KYC
        Route::get('/kyc/pending', [AgentKycController::class, 'pending'])
            ->name('kyc.pending');

        Route::get('/kyc/{user}/documents/{document}', [AgentKycController::class, 'document'])
            ->name('kyc.document');

        Route::get('/kyc/{user}', [AgentKycController::class, 'show'])
            ->name('kyc.show');

        Route::post('/kyc/{user}/approve', [AgentKycController::class, 'approve'])
            ->name('kyc.approve');

        Route::post('/kyc/{user}/reject', [AgentKycController::class, 'reject'])
            ->name('kyc.reject');

        // Cash Top Ups
        Route::get('/topups', [AgentTopUpController::class, 'index'])
            ->name('topups.index');

        Route::get('/topups/create', [AgentTopUpController::class, 'create'])
            ->name('topups.create');

        Route::post('/topups', [AgentTopUpController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('topups.store');

        // Notifications
        Route::get('/notifications', [AgentNotificationController::class, 'index'])
            ->name('notifications.index');

        Route::post('/notifications/{id}/read', [AgentNotificationController::class, 'markRead'])
            ->name('notifications.read');
        Route::post('/notifications/read-all', [AgentNotificationController::class, 'markAllRead'])
            ->name('notifications.read-all');
    });

// ADMIN ROUTES
Route::middleware([
    'auth',
    'account.active',
    'role:admin',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // User Management
        Route::resource('users', AdminUserController::class);

        Route::post('/users/{user}/suspend', [AdminUserController::class, 'suspend'])
            ->name('users.suspend');

        Route::post('/users/{user}/activate', [AdminUserController::class, 'activate'])
            ->name('users.activate');

        // Agent Management
        Route::resource('agents', AdminAgentController::class);

        // KYC

        Route::get('/kyc', [AdminKycController::class, 'index'])
            ->name('kyc.index');

        Route::get('/kyc/{user}', [AdminKycController::class, 'show'])
            ->name('kyc.show');

        Route::get('/kyc/{user}/documents/{document}', [AdminKycController::class, 'document'])
            ->name('kyc.document');

        Route::post('/kyc/{user}/approve', [AdminKycController::class, 'approve'])
            ->name('kyc.approve');

        Route::post('/kyc/{user}/reject', [AdminKycController::class, 'reject'])
            ->name('kyc.reject');

        // Wallet Management
        Route::get('/wallets', [AdminWalletController::class, 'index'])
            ->name('wallets.index');

        Route::get('/wallets/{wallet}', [AdminWalletController::class, 'show'])
            ->name('wallets.show');

        Route::post('/wallets/{wallet}/freeze', [AdminWalletController::class, 'freeze'])
            ->name('wallets.freeze');

        Route::post('/wallets/{wallet}/unfreeze', [AdminWalletController::class, 'unfreeze'])
            ->name('wallets.unfreeze');

        // Top Ups
        Route::get('/topups', [AdminTopUpController::class, 'index'])
            ->name('topups.index');

        // Transactions
        Route::get('/transactions', [AdminTransactionController::class, 'index'])
            ->name('transactions.index');

        // Bill Payments
        Route::get('/bill-payments', [AdminBillPaymentController::class, 'index'])
            ->name('bill-payments.index');

        // Billers
        Route::resource('billers', AdminBillerController::class);

        // Statements

        Route::get('/statements', [AdminStatementController::class, 'index'])
            ->name('statements.index');

        Route::post('/statements/generate', [AdminStatementController::class, 'generate'])
            ->name('statements.generate');

        // Fraud Reports
        Route::get('/fraud', [AdminFraudController::class, 'index'])
            ->name('fraud.index');

        Route::get('/fraud/{report}', [AdminFraudController::class, 'show'])
            ->name('fraud.show');

        Route::post('/fraud/{report}/investigate', [AdminFraudController::class, 'investigate'])
            ->name('fraud.investigate');

        Route::post('/fraud/{report}/resolve', [AdminFraudController::class, 'resolve'])
            ->name('fraud.resolve');

        // Fraud Rules
        Route::resource('fraud-rules', AdminFraudRuleController::class);

        // Audit Logs
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])
            ->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AdminAuditLogController::class, 'show'])
            ->name('audit-logs.show');


        // Notifications

        Route::get('/notifications', [AdminNotificationController::class, 'index'])
            ->name('notifications.index');
    });
