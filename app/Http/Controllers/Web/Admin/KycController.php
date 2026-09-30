<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\KycDocument;
use App\Models\User;
use App\Services\KycService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class KycController extends Controller
{
    use RespondsWithFlash;

    public function __construct(
        protected KycService $kyc
    ) {}

    // KYC Lists

    public function index()
    {
        $users = User::whereIn(
            'kyc_status',
            ['pending', 'approved', 'rejected']
        )
            ->latest()
            ->paginate(20);

        return view(
            'admin.kyc.index',
            compact('users')
        );
    }

    // View KYC Documents
    public function document(
        User $user,
        KycDocument $document
    ) {
        /*
        | Security Check
        | Make sure this document actually belongs to this user.
        */

        abort_unless(
            $document->user_id === $user->id,
            404
        );


        // Storage Disk

        $disk = Storage::disk('local');

        // Check File Exists

        if (! $disk->exists($document->document_path)) {
            abort(404, 'KYC document file not found.');
        }

        // Get Physical File Path

        $path = $disk->path(
            $document->document_path
        );

        /*
        | Return File
        | Do not use is_readable() here.
        | response()->file() will let PHP/Laravel handle the file.
        */

        return response()->file($path);
    }

    // Show User KYC

    public function show(User $user)
    {
        $documents = KycDocument::where(
            'user_id',
            $user->id
        )->latest()
            ->get();

        return view('admin.kyc.show', compact('user', 'documents'));
    }

    // Approve KYC

    public function approve(
        Request $request,
        User $user
    ) {
        try {

            $this->kyc->approve(
                user: $user,
                verifier: $request->user()
            );

            return $this->success(
                'KYC approved.',
                'admin.kyc.index'
            );
        } catch (ValidationException $e) {

            return back()->withErrors(
                $e->errors()
            );
        } catch (Throwable $e) {

            return $this->error(
                $e->getMessage()
            );
        }
    }

    // Reject KYC

    public function reject(
        Request $request,
        User $user
    ) {
        $data = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        try {

            $this->kyc->reject(
                user: $user,
                verifier: $request->user(),
                reason: $data['reason']
            );

            return $this->success(
                'KYC rejected.',
                'admin.kyc.index'
            );
        } catch (ValidationException $e) {

            return back()->withErrors(
                $e->errors()
            );
        } catch (Throwable $e) {

            return $this->error(
                $e->getMessage()
            );
        }
    }
}
