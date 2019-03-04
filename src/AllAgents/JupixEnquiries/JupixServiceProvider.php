<?php

namespace AllAgents\JupixEnquiries;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class JupixServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
       // Log::debug('Booted JupixServiceProvider');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
