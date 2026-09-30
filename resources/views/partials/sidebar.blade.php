@php $role = auth()->user()->getRoleNames()->first(); @endphp
<div class="p-3">
    <div class="fw-bold fs-5 mb-3">{{ ucfirst($role) }} Panel</div>

    @if ($role === 'user')
        <a href="{{ route('user.dashboard') }}" class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('user.wallet.show') }}" class="{{ request()->routeIs('user.wallet.*') ? 'active' : '' }}">Wallet</a>
        <a href="{{ route('user.transfers.index') }}" class="{{ request()->routeIs('user.transfers.*') ? 'active' : '' }}">Transfers</a>
        <a href="{{ route('user.topups.index') }}" class="{{ request()->routeIs('user.topups.*') ? 'active' : '' }}">Top-Ups</a>
        <a href="{{ route('user.bills.index') }}" class="{{ request()->routeIs('user.bills.*') ? 'active' : '' }}">Bills</a>
        <a href="{{ route('user.statements.index') }}" class="{{ request()->routeIs('user.statements.*') ? 'active' : '' }}">Statements</a>
        <a href="{{ route('user.transactions.index') }}" class="{{ request()->routeIs('user.transactions.*') ? 'active' : '' }}">Transactions</a>
        <a href="{{ route('user.kyc.index') }}" class="{{ request()->routeIs('user.kyc.*') ? 'active' : '' }}">KYC</a>
        <a href="{{ route('user.profile.edit') }}" class="{{ request()->routeIs('user.profile.*') ? 'active' : '' }}">Profile</a>

    @elseif ($role === 'agent')
        <a href="{{ route('agent.dashboard') }}" class="{{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('agent.users.index') }}" class="{{ request()->routeIs('agent.users.*') ? 'active' : '' }}">Assigned Users</a>
        <a href="{{ route('agent.kyc.pending') }}" class="{{ request()->routeIs('agent.kyc.*') ? 'active' : '' }}">Pending KYC</a>
        <a href="{{ route('agent.topups.index') }}" class="{{ request()->routeIs('agent.topups.*') ? 'active' : '' }}">Cash Top-Ups</a>

    @elseif ($role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Users</a>
        <a href="{{ route('admin.agents.index') }}" class="{{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">Agents</a>
        <a href="{{ route('admin.kyc.index') }}" class="{{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">KYC Queue</a>
        <a href="{{ route('admin.wallets.index') }}" class="{{ request()->routeIs('admin.wallets.*') ? 'active' : '' }}">Wallets</a>
        <a href="{{ route('admin.transactions.index') }}" class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">Transactions</a>
        <a href="{{ route('admin.topups.index') }}" class="{{ request()->routeIs('admin.topups.*') ? 'active' : '' }}">Top-Ups</a>
        <a href="{{ route('admin.bill-payments.index') }}" class="{{ request()->routeIs('admin.bill-payments.*') ? 'active' : '' }}">Bill Payments</a>
        <a href="{{ route('admin.billers.index') }}" class="{{ request()->routeIs('admin.billers.*') ? 'active' : '' }}">Billers</a>
        <a href="{{ route('admin.statements.index') }}" class="{{ request()->routeIs('admin.statements.*') ? 'active' : '' }}">Statements</a>
        <a href="{{ route('admin.fraud.index') }}" class="{{ request()->routeIs('admin.fraud.*') ? 'active' : '' }}">Fraud Reports</a>
        <a href="{{ route('admin.fraud-rules.index') }}" class="{{ request()->routeIs('admin.fraud-rules.*') ? 'active' : '' }}">Fraud Rules</a>
        <a href="{{ route('admin.audit-logs.index') }}" class="{{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">Audit Logs</a>
    @endif
</div>