<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BillPaymentController;
use App\Http\Controllers\Api\FraudController;
use App\Http\Controllers\Api\FraudRuleController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\StatementController;
use App\Http\Controllers\Api\TopUpController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

// Authentication

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthApiController::class, 'me']);
        Route::post('/logout', [AuthApiController::class, 'logout']);
    });
});

// User Routes

Route::middleware([
    'auth:api',
    'role:user',
])->prefix('user')->group(function () {

    // Submit KYC document
    Route::post('/kyc', [KycController::class, 'store'])
        ->middleware('permission:upload kyc documents');
    // Show own KYC documents
    Route::get('/kyc', [KycController::class, 'index'])
        ->middleware('permission:view kyc');
    // Show own KYC status
    Route::get('/kyc/status', [KycController::class, 'status'])
        ->middleware('permission:view kyc');

    Route::middleware([
        'account.active',
        'kyc.verified',
    ])->group(function () {

        // Wallet
        // Show own wallet
        Route::get('/wallet', [WalletController::class, 'show'])
            ->middleware('permission:view wallet');
        // Top Up
        // View own top-up history
        Route::get('/topups', [TopUpController::class, 'index'])
            ->middleware('permission:view top ups');
        // Transfer
        // Create transfer
        Route::post('/transfers', [TransferController::class, 'store'])
            ->middleware(['permission:create transfer', 'throttle:5,1']);
        // Verify OTP / complete transfer
        Route::post('/transfers/{transfer}/verify-otp', [
            TransferController::class,
            'verifyOTP',
        ])->middleware(['permission:create transfer', 'throttle:5,1']);

        // Transaction
        // Own transactions
        Route::get('/transactions', [TransactionController::class, 'index'])
            ->middleware('permission:view transactions');

        // Statement
        // Own statements
        Route::get('/statements', [StatementController::class, 'index'])
            ->middleware('permission:view statements');

        // Bill Payment
        // Pay bill
        Route::post('/bill-payments', [BillPaymentController::class, 'store'])
            ->middleware(['permission:pay bills', 'throttle:5,1']);
        // Bill payment history
        Route::get('/bill-payments', [BillPaymentController::class, 'index'])
            ->middleware('permission:view bill payments');
    });
});

// Agent Routes

Route::middleware([
    'auth:api',
    'role:agent',
])->prefix('agent')->group(function () {

    // KYC

    // Pending KYC users
    Route::get('/kyc/pending', [KycController::class, 'pending'])
        ->middleware('permission:view kyc');

    // Specific user's KYC documents
    Route::get('/users/{user}/kyc', [KycController::class, 'show'])
        ->middleware('permission:view kyc');

    // Approve KYC
    Route::post('/kyc/{user}/approve', [KycController::class, 'approve'])
        ->middleware('permission:approve kyc');

    // Reject KYC
    Route::post('/kyc/{user}/reject', [KycController::class, 'reject'])
        ->middleware('permission:reject kyc');

    // View Assigned Users
    Route::get('/users/assigned', [UserController::class, 'assignedUsers'])
        ->middleware('permission:view users');

    // //Wallet

    // // View user's wallet
    // Route::get('/users/{user}/wallet', [
    //     WalletController::class,
    //     'show'
    // ])
    //     ->middleware('permission:view wallet')
    //     ->name('agent.wallet.show');

    // Top Up

    // Top-up history
    Route::get('/topups', [
        TopUpController::class,
        'agentHistory',
    ])
        ->middleware('permission:view top ups');

    // Agent creates top-up
    Route::post('/topups', [TopUpController::class, 'agentTopUp'])
        ->middleware(['permission:create top up', 'throttle:5,1']);
});

// Admin Route

Route::middleware([
    'auth:api',
    'role:admin',
])->prefix('admin')->group(function () {

    // Agent Management
    Route::apiResource('agents', AgentController::class)
        ->middleware('permission:manage agents');

    // User Management
    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/assign-agent', [UserController::class, 'assignAgent']);

    // AuditLogs
    Route::prefix('audit-logs')->group(function () {

        // List audit logs
        Route::get('/', [AuditLogController::class, 'index']);
        // Show one audit log
        Route::get('/{auditLog}', [AuditLogController::class, 'show']);
    });

    // KYC Management
    Route::prefix('kyc')->group(function () {

        // Show user KYC status
        Route::get('/users/{user}/status', [KycController::class, 'status'])
            ->middleware('permission:view kyc');
        // Pending KYC
        Route::get('/pending', [KycController::class, 'pending'])
            ->middleware('permission:view kyc');
        // Specific user's KYC documents
        Route::get('/users/{user}', [KycController::class, 'show'])
            ->middleware('permission:view kyc');
        // Approve KYC
        Route::post('/{user}/approve', [KycController::class, 'approve'])
            ->middleware('permission:approve kyc');
        // Reject KYC
        Route::post('/{user}/reject', [KycController::class, 'reject'])
            ->middleware('permission:reject kyc');
    });

    // Wallet Management
    Route::prefix('wallets')->group(function () {

        // Show user's wallet
        Route::get('/users/{user}', [WalletController::class, 'showUserWallet'])
            ->middleware('permission:view wallet');
        // Freeze wallet
        Route::post('/{wallet}/freeze', [WalletController::class, 'freeze'])
            ->middleware('permission:freeze wallet');
        // Unfreeze wallet
        Route::post('/{wallet}/unfreeze', [WalletController::class, 'unfreeze'])
            ->middleware('permission:unfreeze wallet');
    });

    // Transaction
    // View all transactions
    Route::get('/transactions', [TransactionController::class, 'all'])
        ->middleware('permission:view all transactions');

    // Statements
    // All statements
    Route::get('/statements', [StatementController::class, 'all'])
        ->middleware('permission:view statements');

    // Generate statement
    // Route::post('/statements/generate', [
    //     StatementController::class,
    //     'generate'
    // ])
    //     ->middleware('permission:generate statements')
    //     ->name('admin.statements.generate');

    // Fraud rules

    Route::get('/fraud-rules', [FraudRuleController::class, 'index']);
    Route::get('/fraud-rules/{fraudRule}', [FraudRuleController::class, 'show']);
    Route::post('/fraud-rules', [FraudRuleController::class, 'store']);
    Route::put('/fraud-rules/{fraudRule}', [FraudRuleController::class, 'update']);
    Route::delete('/fraud-rules/{fraudRule}', [FraudRuleController::class, 'destroy']);

    // Fraud Management
    Route::prefix('fraud')->group(function () {

        // List fraud cases
        Route::get('/', [FraudController::class, 'index'])
            ->middleware('permission:view fraud reports');
        // Show fraud case
        Route::get('/{fraud}', [FraudController::class, 'show'])
            ->middleware('permission:view fraud reports');
        // Investigate fraud
        Route::post('/{fraud}/investigate', [FraudController::class, 'investigate'])
            ->middleware('permission:investigate fraud');
        // Resolve fraud
        Route::post('/{fraud}/resolve', [FraudController::class, 'resolve'])
            ->middleware('permission:resolve fraud');
    });
});
