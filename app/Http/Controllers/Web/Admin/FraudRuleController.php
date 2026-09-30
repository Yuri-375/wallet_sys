<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\RespondsWithFlash;
use App\Models\FraudRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FraudRuleController extends Controller
{
    use RespondsWithFlash;

    public function index()
    {
        $rules = FraudRule::latest()->paginate(20);

        return view('admin.fraud-rules.index', compact('rules'));
    }

    public function create()
    {
        return view('admin.fraud-rules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        FraudRule::create($data);

        return $this->success('Rule created.', 'admin.fraud-rules.index');
    }

    public function show(FraudRule $fraudRule)
    {
        return view('admin.fraud-rules.show', ['rule' => $fraudRule]);
    }

    public function edit(FraudRule $fraudRule)
    {
        return view('admin.fraud-rules.edit', ['rule' => $fraudRule]);
    }

    public function update(Request $request, FraudRule $fraudRule)
    {
        $data = $request->validate($this->rules($fraudRule->id));

        $fraudRule->update($data);

        return $this->success('Rule updated.', 'admin.fraud-rules.index');
    }

    public function destroy(FraudRule $fraudRule)
    {
        $fraudRule->update(['is_active' => false]);

        return $this->success('Rule deactivated.', 'admin.fraud-rules.index');
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('fraud_rules')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'configuration_type' => ['required', 'in:transfer_frequency,large_transfer_otp,new_receiver_high_amount,new_account_transfer,amount,large_debit'],
            'threshold' => ['nullable', 'integer', 'min:0'],
            'severity' => ['nullable', 'in:low,medium,high'],
            'is_active' => ['boolean'],
        ];
    }
}
