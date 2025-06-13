<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\Portfolio\CreatePortfolioJob;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PortfolioController extends Controller
{
    public function store(Request $request, Dispatcher $dispatcher): Response
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'rebalance_threshold_percent' => 'required|numeric',
            'last_rebalanced_at' => 'nullable|date',
        ]);

        $job = new CreatePortfolioJob(
            $data['user_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['is_active'],
            $data['rebalance_threshold_percent'],
            $data['last_rebalanced_at'] ?? null
        );

        $dispatcher->dispatch($job);

        return response()->noContent();
    }
}
