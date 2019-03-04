<?php

namespace AllAgents\JupixEnquiries;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class JupixClient
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * The Jupix Enquiries API endpoint
     *
     * @var string
     */
    private $endpoint = 'http://services.jupix.co.uk/api/enquiries/post_enquiry.php';

    /**
     * Create the base query (API key, agent/branch ID's etc)
     *
     * @var array
     */
    private $baseQuery = array(
        'apiKey' => null,
        'agentCode' => null,
        'branchCode' => null,
        'version' => null,
        'requestDetails' => '1',
    );

    /**
     * This variable will contain the query array. This data will be saved here so we
     * can check that all of the required fields have been filled. If they haven't
     * been filled, we will not send the request.
     *
     * @var string
     */
    private $query;

    /**
     * Set all of the required fields here so we can loop through the fields later
     * and check if they have been filled.
     *
     * @var array
     */
    private $required = array(
        'firstName',
        // 'lastName',
        'from',
        'phoneDay',
        'comment',
    );

    /**
     * DeploymentController constructor.
     * @param Request $request
     */
    public function __construct()
    {
        // Set the private variables.
        $this->baseQuery['apiKey'] = env('JUPIX_ENQUIRY_KEY', null);
        $this->baseQuery['agentCode'] = env('JUPIX_ENQUIRY_AGENT_CODE', null);
        $this->baseQuery['branchCode'] = env('JUPIX_ENQUIRY_BRANCH_CODE', null);
        $this->baseQuery['version'] = env('JUPIX_ENQUIRY_KEY', '1.1');
    }

    /**
     * Set a query
     *
     * @param $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Return a proper query to send to the endpoint.
     *
     * This function will get the preset values as well as get the enquiry-specific
     * values and merge them together, package them up in a HTTP query and return
     * it to the previous function. This function will also check if all of the
     *
     * @return string
     */
    public function createQuery()
    {
        return http_build_query(array_merge($this->baseQuery, $this->query));
    }

    /**
     * This function will be used to check if all of the required fields have been
     * filled.
     *
     * @return boolean
     */
    public function allRequiredFieldsFilled()
    {
        foreach ($this->required as $field) {
            if (empty($this->query[$field])) {
                Log::error("'$field' is not filled.");
                return false;
            }
        }

        return true;
    }

    /**
     * Add Enquiry to Jupix
     *
     * @param $query
     * @throws \ErrorException
     * @return bool
     */
    public function addEnquiry($query)
    {
        // Check the application environment
        if (App::environment('local') || env('APP_DEBUG') === true) {
            // $query['testing'] = 1;
        }

        $this->setQuery($query);

        if (!$this->allRequiredFieldsFilled()) {
            throw new \ErrorException("Some of the required fields haven't been filled.");
        }

        // Create the parameter array that will be sent to the endpoint.
        $params = array(
            'http' => array(
                'method' => 'POST',
                'content' => $this->createQuery()
            )
        );

        Log::debug(print_r($params, true));

        $ctx = stream_context_create($params);

        $fp = @fopen($this->endpoint, 'rb', false, $ctx);

        if (!$fp) {
            Log::error("Problem with {$this->endpoint}");

            return false;
        }

        $response = @stream_get_contents($fp);

        if ($response === false) {
            Log::error("Problem reading data from {$this->endpoint}");

            return false;
        }

        $response = new \SimpleXMLElement($response);

        Log::debug(print_r($response, true));

        return true;
    }
}