<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\KycDocument;
use App\Services\KycService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class KycController extends Controller
{
    use RespondsWithFlash;

    public function __construct(protected KycService $kyc) {}

    public function index()
    {
        $documents = KycDocument::where('user_id', Auth::id())->latest()->get();

        return view('user.kyc.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'document_type' => ['required', 'in:national_id,passport,driving_license'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        try {
            $this->kyc->submit(
                user: Auth::user(),
                documentType: $data['document_type'],
                file: $request->file('document'),
            );

            return $this->success('KYC document submitted.', 'user.kyc.index');
        } catch (Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
