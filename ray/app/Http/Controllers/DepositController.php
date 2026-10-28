<?php

namespace App\Http\Controllers;

use App\Models\DepositAddress;
use App\Services\SolanaWallet;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    /** Chains that accept a deposit address. */
    private const CHAINS = ['sol', 'eth', 'base', 'bnb', 'ink', 'hype', 'robinhood', 'arc'];

    /** GET /api/deposit/addresses */
    public function index(Request $request)
    {
        $rows = $request->user()->depositAddresses()->latest()->get()->map(fn ($d) => $this->row($d));

        return response()->json(['addresses' => $rows]);
    }

    /** POST /api/deposit/addresses */
    public function store(Request $request, SolanaWallet $sol)
    {
        $data = $request->validate([
            'chain'   => ['required', 'string', 'in:' . implode(',', self::CHAINS)],
            'address' => ['required', 'string', 'max:120'],
            'label'   => ['nullable', 'string', 'max:80'],
        ]);

        $address = trim($data['address']);

        if (! $this->validAddress($data['chain'], $address, $sol)) {
            return response()->json([
                'error' => $data['chain'] === 'sol'
                    ? 'Enter a valid Solana address.'
                    : 'Enter a valid 0x address for this network.',
            ], 422);
        }

        $row = $request->user()->depositAddresses()->firstOrCreate(
            ['chain' => $data['chain'], 'address' => $address],
            ['label' => $data['label'] ?? null]
        );

        return response()->json(['address' => $this->row($row)]);
    }

    /** DELETE /api/deposit/addresses/{depositAddress} */
    public function destroy(Request $request, DepositAddress $depositAddress)
    {
        abort_unless($depositAddress->user_id === $request->user()->id, 403);
        $depositAddress->delete();

        return response()->json(['ok' => true]);
    }

    private function row(DepositAddress $d): array
    {
        return [
            'id'      => $d->id,
            'chain'   => $d->chain,
            'address' => $d->address,
            'label'   => $d->label,
        ];
    }

    private function validAddress(string $chain, string $address, SolanaWallet $sol): bool
    {
        if ($chain === 'sol') {
            return $sol->isValidAddress($address);
        }

        return (bool) preg_match('/^0x[a-fA-F0-9]{40}$/', $address);
    }
}
