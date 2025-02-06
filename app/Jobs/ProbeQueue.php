<?php

namespace App\Jobs;

use App\Contracts\JobHandlers\SendMessageHandlerInterface;
use App\JobsHandlers\ProbeQueueHandler;
use App\Services\Notification\Contracts\Objects\MessageInterface;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProbeQueue implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct(
        $message
    ){
        $this->message = $message;
    }
    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle(): void
    {

    }


}
