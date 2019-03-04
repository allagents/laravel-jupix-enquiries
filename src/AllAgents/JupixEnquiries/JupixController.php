<?php

namespace AllAgents\JupixEnquiries;

use Illuminate\Http\Request;

class JupixController
{
    /**
     * Run the deployment.
     *
     * @param Request $request
     */
    public function now(Request $request)
    {
        $client = new JupixClient($request);
        $client->postEnquiry();
    }
}