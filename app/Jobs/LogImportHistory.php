<?php

namespace App\Jobs;

use App\Models\ImportHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogImportHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $totalRow;
    protected $successRow;
    protected $errorRow;
    protected $executionTime;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $totalRow, $successRow, $errorRow, $executionTime)
    {
        $this->userId = $userId;
        $this->totalRow = $totalRow;
        $this->successRow = $successRow;
        $this->errorRow = $errorRow;
        $this->executionTime = $executionTime;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Calculate success rate
            $successRate = $this->totalRow > 0 
                ? round(($this->successRow / $this->totalRow) * 100, 2) 
                : 0;
            
            ImportHistory::create([
                'user_id' => $this->userId,
                'total_row' => $this->totalRow,
                'success_row' => $this->successRow,
                'error_row' => $this->errorRow,
                'success_rate' => $successRate,
                'executed_date' => now(),
                'execution_time' => round($this->executionTime, 2),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log import history', [
                'user_id' => $this->userId,
                'total_row' => $this->totalRow,
                'error' => $e->getMessage()
            ]);
        }
    }
}
