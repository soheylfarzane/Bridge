<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage; // Import the Storage facade

class CleanerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileUrl;

    /**
     * Create a new job instance.
     *
     * @param string $fileUrl The URL of the file to be removed
     * @return void
     */
    public function __construct($fileUrl)
    {
        $this->fileUrl = $fileUrl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Use the Storage facade to delete the file
        // Assuming the file URL is a relative path within the storage disk
        Storage::delete($this->fileUrl);

        // Log or perform any additional actions if needed
        // For example, you can use Laravel's built-in logging:
        // \Illuminate\Support\Facades\Log::info('File deleted: ' . $this->fileUrl);
    }
}
