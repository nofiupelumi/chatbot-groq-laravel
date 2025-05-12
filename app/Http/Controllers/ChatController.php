<?php






//New code working perfectly with prevalent risk factor and neighborhood by nofiupelumi
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    // Knowledge Base and Configuration
    private $knowledgeBase = [
        // "hello" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
        // "hi" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
        // "good morning" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
        "what is nigeria risk index?" => "The Nigeria Risk Index (NRI) is a security intelligence platform assessing and visualizing risks across Nigeria’s 36 states.",
        "what is the interactive risk map?" => "The Interactive Risk Map provides real-time insights into security threats across different regions in Nigeria.",
        "how often is nri updated?" => "NRI provides daily security updates, weekly trend analyses, and real-time risk alerts.",
        "how can i contact nri for support?" => "You can contact NRI via the 'Contact Us' page or by emailing info@riskcontrolnigeria.com."
    ];

    private $validYears = ["2018", "2019", "2020", "2021", "2022", "2023", "2024", "2025"];
    private $validRiskFactors = ["Personal Threats", "Political Threats", "Property Threats", "Safety", "Violent Threats"];
    private $validStates = [
        "abia", "adamawa", "akwa ibom", "anambra", "bauchi", "bayelsa", "benue", "borno",
        "cross river", "delta", "ebonyi", "edo", "ekiti", "enugu", "gombe", "imo", "jigawa",
        "kaduna", "kano", "katsina", "kebbi", "kogi", "kwara", "lagos", "nasarawa", "niger",
        "ogun", "ondo", "osun", "oyo", "plateau", "rivers", "sokoto", "taraba", "yobe", "zamfara"
    ];

    private $riskIndicators = [
        "Violent Threats" => ["Terrorism", "Kidnapping", "Armed Robbery", "Homicide", "Insurgency"],
        "Safety" => ["Natural_Disasters", "Fire Outbreak", "Epidemic", "Unsafe Route/Violent Attacks"],
        "Property Threats" => ["Burglary", "Theft", "Fraud", "Arson", "Cyber Crime"],
        "Political Threats" => ["Political Protest", "Political Corruption", "Electoral Violence"],
        "Personal Threats" => ["Assaults", "Rape", "Firearms Trafficking", "Human Trafficking"]
    ];
    // Number words mapping for limit parameter (one to forty-five)
    private $numberWords = [
        'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
        'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10,
        'eleven' => 11, 'twelve' => 12, 'thirteen' => 13, 'fourteen' => 14, 'fifteen' => 15,
        'sixteen' => 16, 'seventeen' => 17, 'eighteen' => 18, 'nineteen' => 19, 'twenty' => 20,
        'twenty-one' => 21, 'twenty-two' => 22, 'twenty-three' => 23, 'twenty-four' => 24, 'twenty-five' => 25,
        'twenty-six' => 26, 'twenty-seven' => 27, 'twenty-eight' => 28, 'twenty-nine' => 29, 'thirty' => 30,
        'thirty-one' => 31, 'thirty-two' => 32, 'thirty-three' => 33, 'thirty-four' => 34, 'thirty-five' => 35,
        'thirty-six' => 36, 'thirty-seven' => 37, 'thirty-eight' => 38, 'thirty-nine' => 39, 'forty' => 40,
        'forty-one' => 41, 'forty-two' => 42, 'forty-three' => 43, 'forty-four' => 44, 'forty-five' => 45
    ];

    // *** CHANGE 1: Added property to store valid neighborhoods ***
    // This will hold an array of neighborhoods per state, loaded from CSV, e.g., ['lagos' => ['epe', 'ikeja', ...]]
    private $neighborhoods;

    // *** CHANGE 2: Added constructor to initialize neighborhoods ***
    public function __construct()
    {
        $this->loadNeighborhoods();
    }

    // *** CHANGE 3: Added method to load neighborhoods from CSV ***
    // Loads state_neighbourhoods.csv and populates $this->neighborhoods with lowercase state and neighborhood names
    private function loadNeighborhoods()
    {
        $this->neighborhoods = [];
        $csvPath = storage_path('app/state_neighbourhoods.csv');
        if (file_exists($csvPath)) {
            $csv = array_map('str_getcsv', file($csvPath));
            array_shift($csv); // Remove header row assuming format: [column0, neighbourhood_name, state]
            foreach ($csv as $row) {
                $state = trim($row[2]); // Assuming state is in 3rd column (index 2)
                $neighborhood = trim($row[1]); // Assuming neighborhood_name is in 2nd column (index 1)
                $stateLower = strtolower($state);
                $neighborhoodLower = strtolower($neighborhood);
                if (!isset($this->neighborhoods[$stateLower])) {
                    $this->neighborhoods[$stateLower] = [];
                }
                $this->neighborhoods[$stateLower][] = $neighborhoodLower;
            }
        } else {
            Log::error("Neighborhood CSV file not found at $csvPath");
        }
    }

    // *** CHANGE 4: Added method to validate neighborhood names ***
    // Checks if a neighborhood is valid for a given state by comparing lowercase strings
    private function isValidNeighborhood($name, $state)
    {
        $stateLower = strtolower($state);
        $nameLower = strtolower($name);
        return isset($this->neighborhoods[$stateLower]) && in_array($nameLower, $this->neighborhoods[$stateLower]);
    }

    /**
     * Handle the chat request from the user
     */
    public function chat(Request $request)
    {
        $userQuery = strtolower(trim($request->input('message')));

        // Check knowledge base first with typing indicator simulation
        foreach ($this->knowledgeBase as $question => $answer) {
            if (strpos($userQuery, $question) !== false) {
                $this->simulateTypingDelay();
                return response()->json(['response' => $answer]);
            }
        }

        // Validate query parameters
        $validationResult = $this->validateQuery($userQuery);
        if ($validationResult) {
            return response()->json(['response' => $validationResult]);
        }

        // Process endpoint request
        list($endpoint, $params, $error) = $this->buildEndpoint($userQuery);
        if ($error) return response()->json(['response' => $error]);

        if ($endpoint) {
            return $this->fetchRiskData($endpoint, $params, $userQuery);
        }

        // Final Groq fallback with knowledge base recheck
        return $this->askGroqAPI($userQuery, null, null, true);
    }

    // new code implementation
    //* Handle the chat request from the user
     


    /**
     * Validate the user's query for consistency
     */
    private function validateQuery($query)
    {
        // Check for multiple years
        $foundYears = [];
        foreach ($this->validYears as $year) {
            if (strpos($query, $year) !== false) $foundYears[] = $year;
        }
        if (count($foundYears) > 1) {
            return "Please specify only one year between 2018-2025.";
        }

        // Check for invalid year
        if (!empty($foundYears)) {
            $year = $foundYears[0];
            if (!in_array($year, $this->validYears)) {
                return "Please enter a valid year between 2018-2025.";
            }
        }

        // Check for multiple endpoints
        $endpoints = ["top-five-state", "lowest-five-state", "low-state", "top-state", "incident-count", "prevalent-risk-factor","risk-indicator-linked-to-fatality",'primary-risk-factor-affecting-neighbourhood', "neighbourhood-incidents","highest-day-period-for-neighbourhood","high-risk-neighbourhood"];
        $foundEndpoints = [];
        foreach ($endpoints as $ep) {
            if (strpos($query, str_replace("-", " ", $ep)) !== false) {
                $foundEndpoints[] = $ep;
            }
        }
        if (count($foundEndpoints) > 1) {
            return "Please specify only one endpoint. Available options: "
                . implode(", ", array_map('ucfirst', $foundEndpoints));
        }

        return null;
    }

    /**
     * Build the API endpoint and parameters based on the query
     */
    private function buildEndpoint($query)
    {
        $endpoints = ["top-five-state", "lowest-five-state", "low-state", "top-state", "incident-count", "prevalent-risk-factor", "risk-indicator-linked-to-fatality", "primary-risk-factor-affecting-neighbourhood", "primary-risk-indicator-affecting-neighbourhood", "neighbourhood-incidents","highest-day-period-for-neighbourhood","high-risk-neighbourhood", "risk-index"];
        $selectedEndpoint = null;
        $params = [];
        $error = null;
    
        // Endpoint detection
        foreach ($endpoints as $ep) {
            if (strpos($query, str_replace("-", " ", $ep)) !== false) {
                $selectedEndpoint = $ep;
                break;
            }
        }
        



                // Detect risk-factor-affecting-neighbourhood based on keywords
        if ($selectedEndpoint === null) {
            if (
                (strpos($query, 'risk factor') !== false || strpos($query, 'risk') !== false) &&
                (strpos($query, 'neighbourhood') !== false || strpos($query, 'neighborhood') !== false) &&
                strpos($query, 'indicator') === false &&
                strpos($query, 'high risk') === false &&
                strpos($query, 'day period') === false &&
                strpos($query, 'time of day') === false
            ) {
                $selectedEndpoint = 'primary-risk-factor-affecting-neighbourhood';
            }
        }
    
        // Detect risk-indicator-affecting-neighbourhood based on keywords
        if ($selectedEndpoint === null) {
            if (
                (strpos($query, 'risk indicator') !== false || strpos($query, 'indicator') !== false) &&
                (strpos($query, 'neighbourhood') !== false || strpos($query, 'neighborhood') !== false) &&
                strpos($query, 'high risk') === false &&
                strpos($query, 'day period') === false &&
                strpos($query, 'time of day') === false
            ) {
                $selectedEndpoint = 'primary-risk-indicator-affecting-neighbourhood';
            }
        }

        // Detect highest-day-period-for-neighbourhood based on keywords
        if ($selectedEndpoint === null) {
            if (
                (strpos($query, 'day period') !== false || strpos($query, 'time of day') !== false || strpos($query, 'day of week') !== false || strpos($query, 'most frequent') !== false) &&
                (strpos($query, 'neighbourhood') !== false || strpos($query, 'neighborhood') !== false)
            ) {
                $selectedEndpoint = 'highest-day-period-for-neighbourhood';
            }
        }

        // Detect list-incidents based on keywords
        // Detect neighbourhood-incidents (tightened to avoid risk factor queries)
        // Detect neighbourhood-incidents (prioritized for "incident" and "neighbourhood")
        if ($selectedEndpoint === null) {
            if (
                (strpos($query, 'incident') !== false || strpos($query, 'incidents') !== false || strpos($query, 'cases') !== false || strpos($query, 'case') !== false ) &&
                (strpos($query, 'neighbourhood') !== false || strpos($query, 'neighborhood') !== false) &&
                strpos($query, 'risk factor') === false &&
                strpos($query, 'risk indicator') === false &&
                strpos($query, 'day period') === false &&
                strpos($query, 'time of day') === false &&
                strpos($query, 'most frequent') === false &&
                strpos($query, 'high risk') === false
            ) {
                $selectedEndpoint = 'neighbourhood-incidents';
            }
        }
    

    
        // Detect riskIndicatorLinkedToFatality based on keywords
        if ($selectedEndpoint === null) {
            if (
                (strpos($query, 'fatality') !== false || strpos($query, 'fatalities') !== false) &&
                (strpos($query, 'risk indicator') !== false || strpos($query, 'riskindicator') !== false)
            ) {
                $selectedEndpoint = 'risk-indicator-linked-to-fatality';
            }
        }
    
        // Check for prevalent-risk-factor based on keywords
        if ($selectedEndpoint === null) {
            if (
                (strpos($query, 'prevalent') !== false || strpos($query, 'common') !== false) &&
                (strpos($query, 'riskfactor') !== false || strpos($query, 'risk factor') !== false)
            ) {
                $selectedEndpoint = 'prevalent-risk-factor';
            }
        }
        
        // New detection for risk-index
        if ($selectedEndpoint === null) {
            if (
                strpos($query, 'risk index') !== false || strpos($query, 'state risk') !== false ||
                strpos($query, 'risk data') !== false
            ) {
                $selectedEndpoint = 'risk-index';
            }
        }
    

        // New detection for high-risk-neighborhoods
        if ($selectedEndpoint === null) {
            if (
                (strpos($query, 'high risk') !== false || strpos($query, 'dangerous') !== false ||
                 strpos($query, 'top risk') !== false || strpos($query, 'most incidents') !== false) &&
                (strpos($query, 'neighborhood') !== false || strpos($query, 'neighbourhood') !== false ||
                 strpos($query, 'areas') !== false || strpos($query, 'zones') !== false)
            ) {
                $selectedEndpoint = 'high-risk-neighbourhood';
            }
        }
        
        // Detect incident-count (tightened to exclude "neighbourhood")
        if ($selectedEndpoint === null) {
            if (
                preg_match('/\b(?:incidents?|cases?)\b/i', $query) &&
                (strpos($query, 'neighbourhood') === false && strpos($query, 'neighborhood') === false)
            ) {
                foreach ($this->validStates as $state) {
                    if (stripos($query, $state) !== false) {
                        $selectedEndpoint = 'incident-count';
                        $params['state'] = $state;
                        break;
                    }
                }
            }
        }

    
        if ($selectedEndpoint) {
            // Define allowed parameters for each endpoint
            $allowedParams = [
                'top-five-state' => ['year', 'riskfactor', 'riskindicator'],
                'lowest-five-state' => ['year', 'riskfactor', 'riskindicator'],
                'low-state' => ['year', 'riskfactor', 'riskindicator'],
                'top-state' => ['year', 'riskfactor', 'riskindicator'],
                'incident-count' => ['state', 'year', 'riskfactor', 'riskindicator'],
                'prevalent-risk-factor' => ['state', 'year', 'limit'],
                'risk-indicator-linked-to-fatality' => ['state', 'year', 'limit'],
                'primary-risk-factor-affecting-neighbourhood' => ['state', 'neighbourhood', 'days'],
                'primary-risk-indicator-affecting-neighbourhood' => ['state', 'neighbourhood', 'days'],
                'neighbourhood-incidents' => ['state', 'neighbourhood', 'days'],
                'highest-day-period-for-neighbourhood' => ['state', 'neighbourhood', 'days'],
                'high-risk-neighbourhood' => ['state', 'riskfactors', 'riskindicators', 'days'],
                'risk-index' => ['year', 'risk-indicator']
            ];
    
            // Extract state if allowed
            if (in_array('state', $allowedParams[$selectedEndpoint])) {
                foreach ($this->validStates as $state) {
                    if (stripos($query, $state) !== false) {
                        $params['state'] = $state;
                        break;
                    }
                }
            }

            // *** CHANGE 5: Modified neighborhood extraction to validate against CSV ***
            // Now only sets $params['neighbourhood'] if the extracted name is valid for the specified state
            if (in_array('neighbourhood', $allowedParams[$selectedEndpoint])) {
                $patterns = [
                    '/\b(?:in|for|at|affecting|of)\s+(?:the\s+)?([a-z\s\-]+?)(?:\s+(?:in|for|at|affecting|of|area|neighbourhood|neighborhood)\s|$)/i',
                    '/\bneighbourhood\s+([a-z\s\-]+?)(?:in|for|at|affecting|of\s|$)/i'
                ];
                $potentialNeighborhood = null;
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $query, $matches)) {
                        $potentialNeighborhood = trim($matches[1]);
                        if (in_array(strtolower($potentialNeighborhood), ['risk factor', 'risk indicator', 'incident', 'incidents'])) {
                            continue;
                        }
                        foreach ($this->validStates as $state) {
                            $potentialNeighborhood = str_ireplace($state, '', $potentialNeighborhood);
                        }
                        $potentialNeighborhood = trim($potentialNeighborhood);
                        if (!empty($potentialNeighborhood)) {
                            break;
                        }
                    }
                }
                // Only set neighborhood if a state is specified and the neighborhood is valid for that state
                if ($potentialNeighborhood && isset($params['state']) && $this->isValidNeighborhood($potentialNeighborhood, $params['state'])) {
                    $params['neighbourhood'] = $potentialNeighborhood;
                }
            }
    
            // Extract neighbourhood if allowed
            // if (in_array('neighbourhood', $allowedParams[$selectedEndpoint])) {
            //     $patterns = [
            //         '/\b(?:in|for|at|affecting|of)\s+(?:the\s+)?([a-z\s\-]+?)(?:\s+(?:in|for|at|affecting|of|area|neighbourhood|neighborhood)\s|$)/i',
            //         '/\bneighbourhood\s+([a-z\s\-]+?)(?:in|for|at|affecting|of\s|$)/i'
            //     ];
            //     foreach ($patterns as $pattern) {
            //         if (preg_match($pattern, $query, $matches)) {
            //             $neighbourhood = trim($matches[1]);
            //             // Exclude common non-neighborhood terms
            //             if (in_array(strtolower($neighbourhood), ['risk factor', 'risk indicator', 'incident', 'incidents'])) {
            //                 continue;
            //             }
            //             foreach ($this->validStates as $state) {
            //                 if (strtolower($neighbourhood) === strtolower($state)) {
            //                     $neighbourhood = '';
            //                     break;
            //                 }
            //                 $neighbourhood = str_ireplace($state, '', $neighbourhood);
            //             }
            //             $neighbourhood = trim($neighbourhood);
            //             if (!empty($neighbourhood)) {
            //                 $params['neighbourhood'] = $neighbourhood;
            //                 break;
            //             }
            //         }
            //     }
            // }
    
            // Extract days if allowed
            if (in_array('days', $allowedParams[$selectedEndpoint])) {
                foreach ($this->numberWords as $word => $value) {
                    if (strpos($query, $word) !== false) {
                        $params['days'] = $value;
                        break;
                    }
                }
                if (!isset($params['days'])) {
                    preg_match('/\b(?:in the last|over the past)\s+(\d+)\s+days?\b/i', $query, $matches) ||
                    preg_match('/\b([1-9][0-9]?|100)\b/', $query, $matches);
                    if (!empty($matches)) {
                        $params['days'] = (int)$matches[1];
                    }
                }
            }
    
            // Extract year if allowed
            if (in_array('year', $allowedParams[$selectedEndpoint])) {
                foreach ($this->validYears as $year) {
                    if (strpos($query, $year) !== false) {
                        $params['year'] = $year;
                        break;
                    }
                }
            }
    
            // Extract limit if allowed
            if (in_array('limit', $allowedParams[$selectedEndpoint])) {
                foreach ($this->numberWords as $word => $value) {
                    if (strpos($query, $word) !== false) {
                        $params['limit'] = $value;
                        break;
                    }
                }
                if (!isset($params['limit'])) {
                    preg_match('/\b([1-9]|[1-3][0-9]|4[0-5])\b/', $query, $matches);
                    if (!empty($matches)) {
                        $params['limit'] = (int)$matches[0];
                    }
                }
            }
    
            // Extract riskfactor if allowed
            if (in_array('riskfactor', $allowedParams[$selectedEndpoint])) {
                foreach ($this->validRiskFactors as $factor) {
                    if (strpos($query, strtolower($factor)) !== false) {
                        $params['riskfactor'] = $factor;
                        break;
                    }
                }
            }
    
            // Extract riskindicator if allowed
            if (in_array('riskindicator', $allowedParams[$selectedEndpoint])) {
                foreach ($this->riskIndicators as $category => $indicators) {
                    foreach ($indicators as $indicator) {
                        $cleanIndicator = str_replace(['_', '/'], ' ', $indicator);
                        if (strpos($query, strtolower($cleanIndicator)) !== false) {
                            $params['riskindicator'] = $indicator;
                            $params['category'] = $category;
                            break 2;
                        }
                    }
                }
            }
            // Extract riskfactors if allowed (for high-risk-neighborhoods)
            if (in_array('riskfactors', $allowedParams[$selectedEndpoint])) {
                $mentionedFactors = [];
                foreach ($this->validRiskFactors as $factor) {
                    if (strpos($query, strtolower($factor)) !== false) {
                        $mentionedFactors[] = $factor;
                    }
                }
                if (!empty($mentionedFactors)) {
                    $params['riskfactors'] = implode(',', $mentionedFactors);
                }
            }
            // Extract riskindicators if allowed (for high-risk-neighborhoods)
            if (in_array('riskindicators', $allowedParams[$selectedEndpoint])) {
                $mentionedIndicators = [];
                foreach ($this->riskIndicators as $category => $indicators) {
                    foreach ($indicators as $indicator) {
                        $cleanIndicator = str_replace(['_', '/'], ' ', $indicator);
                        if (strpos($query, strtolower($cleanIndicator)) !== false) {
                            $mentionedIndicators[] = $indicator;
                        }
                    }
                }
                if (!empty($mentionedIndicators)) {
                    $params['riskindicators'] = implode(',', $mentionedIndicators);
                }
            }

            // Validate incident-count requirements
            if ($selectedEndpoint === "incident-count") {
                if (!isset($params['state'])) {
                    $error = "Please specify a state for incident count queries (e.g., 'incident count in Lagos').";
                }
            }
    
            //Validate neighbourhood-related endpoints
            // if (in_array($selectedEndpoint, ['primary-risk-factor-affecting-neighbourhood', 'primary-risk-indicator-affecting-neighbourhood', 'neighbourhood-incidents','highest-day-period-for-neighbourhood'])) {
            //     if (!isset($params['neighbourhood'])) {
            //         $error = "Please specify a neighbourhood for risk or incident queries (e.g., 'list neighborhood incident in Aba North').";
            //     }
            // }

    
            return [$selectedEndpoint, $params, $error];
        }
    
        return [null, [], "Note: I’m currently trained to answer questions about the <strong>Nigeria Risk Index</strong> only. For neighbourhood queries, always include the state name.<br><br>
        Try asking things like:<br>
        1. What are the top five states for Homicide in 2024?<br>
        2. Give me the lowest five states for Kidnapping.<br>
        3. Tell me the incident count for Lagos in 2023.<br>
        4. What’s the kidnapping incident count in Lagos in 2024?<br>
        5. What are the prevalent risk factors in Oyo?<br>
        6. What are the risk indicators linked to fatality in Gombe?<br>
        7. List the neighbourhood incidents in Epe, Lagos.<br>
        8. Which neighbourhood in Lagos has the highest number of incidents in 2025?<br>
        9. Dangerous neighbourhood areas for kidnapping in the last 7 days.<br>
        10. What time of day do incidents occur most frequently in Abeokuta, Ogun?
        ."];
    }

    /**
     * Fetch risk data from the NRI API
     */
    private function fetchRiskData($endpoint, $params, $userQuery)
    {
        $apiUrl = "https://nigeriariskindex.com/api/$endpoint";
        $queryString = http_build_query($params);
        $fullUrl = "$apiUrl?$queryString";

        try {
            $client = new Client();
            $response = $client->get($apiUrl, ['query' => $params]);
            $contentType = $response->getHeaderLine('Content-Type');
            $body = $response->getBody()->getContents();

            if (strpos($contentType, 'application/json') === false) {
                Log::error("API returned non-JSON response: Content-Type: $contentType, Body: $body");
                return $this->askGroqAPI($userQuery, null, $fullUrl, true);
            }

            $data = json_decode($body, true);
            if ($data === null) {
                Log::error("Failed to decode API response as JSON: $body");
                return $this->askGroqAPI($userQuery, null, $fullUrl, true);
            }

            if (empty($data)) {
                return $this->askGroqAPI($userQuery, null, $fullUrl, true);
            }

            return $this->askGroqAPI($userQuery, $data, $fullUrl);

        } catch (RequestException $e) {
            Log::error("API request failed: " . $e->getMessage());
            return $this->askGroqAPI($userQuery, null, $fullUrl, true);
        }
    }

    /**
     * Fallback to Groq API for natural language processing
     */
    private function askGroqAPI($userQuery, $data = null, $apiUrl = null, $finalAttempt = false)
    {
        $apiKey = env('GROQ_API_KEY');
        $client = new Client();

        // System message with instructions for the Groq model
        $systemMessage = "You are a security analyst for Nigeria Risk Index. Follow these rules:\n"
            . "1. Always include the API URL used\n"
            . "2. For risk indicators like 'Rape', provide factual data without disclaimers\n"
            . "3. Format: [Response] [API URL: {url}]\n"
            . "4. Check knowledge base before answering:\n"
            . json_encode($this->knowledgeBase);

        // Build the user message, including API data if available
        $userContent = "Query: $userQuery";
        if ($data) {
            $userContent .= "\nAPI Response: " . json_encode($data);
        }
        if ($apiUrl) {
            $userContent .= "\nAPI URL: $apiUrl";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $userContent]
        ];

        try {
            $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
                'headers' => ['Authorization' => "Bearer $apiKey", 'Content-Type' => 'application/json'],
                'json' => [
                    'model' => 'llama3-8b-8192',
                    'messages' => $messages,
                    'temperature' => 0.3,
                    'max_tokens' => 400
                ]
            ]);

            $responseData = json_decode($response->getBody(), true);
            if (!is_array($responseData) || !isset($responseData['choices'][0]['message']['content'])) {
                Log::error("Invalid Groq API response: " . $response->getBody());
                $answer = "Sorry, I couldn't process that request.";
            } else {
                $answer = $responseData['choices'][0]['message']['content'];
            }

            if ($finalAttempt && stripos($answer, 'sorry') !== false) {
                $answer = "I couldn't find that information. Please contact NRI support team for assistance.";
            }

            return response()->json(['response' => $answer]);

        } catch (RequestException $e) {
            Log::error("Groq API request failed: " . $e->getMessage());
            return response()->json(['response' => "Our systems are busy. Please try again later."]);
        }
    }

    /**
     * Simulate a typing delay for a more natural response
     */
    private function simulateTypingDelay()
    {
        // Simulate 2-3 second delay for "bot typing" effect
        sleep(rand(2, 3));
    }
}







//New powerful code working perfectly with extra functionalities by nofiupelumi
// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use GuzzleHttp\Client;
// use GuzzleHttp\Exception\RequestException;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Str;
// use Carbon\Carbon;

// class ChatController extends Controller
// {
//     /**
//      * Knowledge Base Configuration
//      * Comprehensive information about Nigeria Risk Index
//      */
//     private $knowledgeBase = [
//         // Basic greetings with variations
//         "greetings" => [
//             "hello", "hi", "hey", "good morning", "good afternoon", 
//             "good evening", "greetings", "howdy", "hola"
//         ],
        
//         // Standard greeting response with more engaging content
//         "greeting_response" => "Hello! I'm the NRI Security Intelligence Assistant. I can help you with security risk information across Nigeria, including state risk assessments, incident reports, neighborhood safety, and security trends. How can I assist you today?",
        
//         // About NRI - expanded information
//         "about_nri" => [
//             "what is nigeria risk index" => "The Nigeria Risk Index (NRI) is a comprehensive security intelligence platform that assesses and visualizes risks across Nigeria's 36 states. Developed by Risk Control Services, NRI provides data-driven insights on terrorism, kidnapping, crime, and other security threats to help businesses, organizations, and individuals make informed security decisions.",
//             "what does nri do" => "NRI tracks, analyzes, and visualizes security incidents across Nigeria. The platform provides risk scores for each state, identifies high-risk neighborhoods, reports on prevalent threats, and offers security trend analysis to help users mitigate risks.",
//             "who is behind nri" => "The Nigeria Risk Index is powered by Risk Control Services, a leading security consulting firm offering security consulting, background screening, due diligence, corporate investigation, electronic security, and security training services."
//         ],
        
//         // Features and tools
//         "features" => [
//             "interactive risk map" => "The Interactive Risk Map provides real-time, visual insights into security threats across all 36 Nigerian states. Users can filter by risk factors, risk indicators, and time periods to visualize security hotspots and identify safer regions.",
//             "risk databases" => "NRI maintains extensive risk databases including the Nigeria Kidnapping Index (NKI), Nigeria Terrorism Index (NTI), and Nigeria Crime Index (NCI), with historical data from 2018 onwards.",
//             "neighborhood analysis" => "Our neighborhood-level risk analysis identifies high-risk areas within states and tracks specific incidents, risk factors, and patterns to provide hyperlocal security intelligence.",
//             "trend analysis" => "NRI's trend analysis tools identify emerging threats, track seasonal patterns, and highlight significant changes in security conditions to help anticipate future risks."
//         ],
        
//         // Updates and timing
//         "updates" => [
//             "how often is nri updated" => "NRI provides daily security updates, weekly trend analyses, and real-time risk alerts. Historical data is available from 2018 to present.",
//             "data sources" => "NRI utilizes trusted open source intelligence (OSINT), on-the-ground reporting, and proprietary data sources, with all information verified by in-country analysts."
//         ],
        
//         // Contact and support
//         "contact" => [
//             "how can i contact nri" => "You can contact the NRI team through the 'Contact Us' page on the website or by emailing info@riskcontrolnigeria.com. For premium support, business subscribers can access dedicated customer service.",
//             "support" => "For technical support with the NRI platform or this chatbot, please contact support@nigeriariskindex.com.",
//             "subscription" => "NRI offers both free and business subscription plans. The business plan provides access to more detailed risk analyses, neighborhood-level data, and advanced metrics. Visit nigeriariskindex.com for more details."
//         ]
//     ];

//     /**
//      * Expanded valid parameters lists
//      */
//     private $validYears = ["2018", "2019", "2020", "2021", "2022", "2023", "2024", "2025"];
    
//     private $validRiskFactors = [
//         "Personal Threats", "Political Threats", "Property Threats", "Safety", "Violent Threats"
//     ];
    
//     // Include common misspellings and variations of state names
//     private $validStates = [
//         "abia", "adamawa", "akwa ibom", "akwaibom", "anambra", "bauchi", "bayelsa", 
//         "benue", "borno", "cross river", "crossriver", "delta", "ebonyi", "edo", 
//         "ekiti", "enugu", "gombe", "imo", "jigawa", "kaduna", "kano", "katsina", 
//         "kebbi", "kogi", "kwara", "lagos", "nasarawa", "niger", "ogun", "ondo", 
//         "osun", "oyo", "plateau", "rivers", "sokoto", "taraba", "yobe", "zamfara",
//         "fct", "abuja", "federal capital territory"
//     ];
    
//     // State correction map for common misspellings
//     private $stateCorrections = [
//         "akwaibom" => "akwa ibom",
//         "crossriver" => "cross river",
//         "abj" => "abuja",
//         "fct" => "abuja",
//         "federal capital territory" => "abuja"
//     ];

//     // Expanded risk indicators with more detail
//     private $riskIndicators = [
//         "Violent Threats" => [
//             "Terrorism", "Kidnapping", "Armed Robbery", "Homicide", "Insurgency", 
//             "Banditry", "Cult Violence", "Communal Clashes", "Gang Violence"
//         ],
//         "Safety" => [
//             "Natural_Disasters", "Fire Outbreak", "Epidemic", "Unsafe Route/Violent Attacks",
//             "Transportation Accidents", "Industrial Accidents", "Flooding", "Building Collapse"
//         ],
//         "Property Threats" => [
//             "Burglary", "Theft", "Fraud", "Arson", "Cyber Crime", "Vandalism", 
//             "Looting", "Property Destruction", "Counterfeiting"
//         ],
//         "Political Threats" => [
//             "Political Protest", "Political Corruption", "Electoral Violence",
//             "Civil Unrest", "Government Instability", "Policy Changes", "Regulatory Risks"
//         ],
//         "Personal Threats" => [
//             "Assaults", "Rape", "Firearms Trafficking", "Human Trafficking",
//             "Domestic Violence", "Drug-Related Crime", "Child Abuse", "Extortion"
//         ]
//     ];
    
//     // Keywords that map to risk indicators for better query matching
//     private $riskIndicatorKeywords = [
//         "terror" => "Terrorism",
//         "bomb" => "Terrorism",
//         "suicide attack" => "Terrorism",
//         "kidnap" => "Kidnapping",
//         "abduction" => "Kidnapping",
//         "hostage" => "Kidnapping",
//         "robbery" => "Armed Robbery",
//         "stealing" => "Theft",
//         "murder" => "Homicide",
//         "killing" => "Homicide",
//         "insurgent" => "Insurgency",
//         "bandit" => "Banditry",
//         "cult" => "Cult Violence",
//         "gang" => "Gang Violence",
//         "community clash" => "Communal Clashes",
//         "tribal clash" => "Communal Clashes",
//         "flood" => "Flooding",
//         "fire" => "Fire Outbreak",
//         "disease outbreak" => "Epidemic",
//         "pandemic" => "Epidemic",
//         "unsafe road" => "Unsafe Route/Violent Attacks",
//         "accident" => "Transportation Accidents",
//         "collapse" => "Building Collapse",
//         "burglary" => "Burglary",
//         "break-in" => "Burglary",
//         "theft" => "Theft",
//         "stealing" => "Theft",
//         "fraud" => "Fraud",
//         "scam" => "Fraud",
//         "arson" => "Arson",
//         "cyber" => "Cyber Crime",
//         "hacking" => "Cyber Crime",
//         "online fraud" => "Cyber Crime",
//         "vandalism" => "Vandalism",
//         "loot" => "Looting",
//         "property damage" => "Property Destruction",
//         "fake" => "Counterfeiting",
//         "counterfeit" => "Counterfeiting",
//         "protest" => "Political Protest",
//         "demonstration" => "Political Protest",
//         "corruption" => "Political Corruption",
//         "bribery" => "Political Corruption",
//         "election violence" => "Electoral Violence",
//         "vote rigging" => "Electoral Violence",
//         "unrest" => "Civil Unrest",
//         "riot" => "Civil Unrest",
//         "government instability" => "Government Instability",
//         "coup" => "Government Instability",
//         "policy change" => "Policy Changes",
//         "regulation risk" => "Regulatory Risks",
//         "assault" => "Assaults",
//         "attack" => "Assaults",
//         "beating" => "Assaults",
//         "rape" => "Rape",
//         "sexual assault" => "Rape",
//         "gun running" => "Firearms Trafficking",
//         "illegal weapons" => "Firearms Trafficking",
//         "human trafficking" => "Human Trafficking",
//         "domestic violence" => "Domestic Violence",
//         "family abuse" => "Domestic Violence",
//         "drug" => "Drug-Related Crime",
//         "narcotics" => "Drug-Related Crime",
//         "child abuse" => "Child Abuse",
//         "extortion" => "Extortion",
//         "blackmail" => "Extortion"
//     ];
    
//     // Number words mapping for limit parameter (expanded to more variations)
//     private $numberWords = [
//         'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
//         'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10,
//         'eleven' => 11, 'twelve' => 12, 'thirteen' => 13, 'fourteen' => 14, 'fifteen' => 15,
//         'sixteen' => 16, 'seventeen' => 17, 'eighteen' => 18, 'nineteen' => 19, 'twenty' => 20,
//         'twenty-one' => 21, 'twenty-two' => 22, 'twenty-three' => 23, 'twenty-four' => 24, 'twenty-five' => 25,
//         'twenty-six' => 26, 'twenty-seven' => 27, 'twenty-eight' => 28, 'twenty-nine' => 29, 'thirty' => 30,
//         'thirty-one' => 31, 'thirty-two' => 32, 'thirty-three' => 33, 'thirty-four' => 34, 'thirty-five' => 35,
//         'thirty-six' => 36, 'thirty-seven' => 37, 'thirty-eight' => 38, 'thirty-nine' => 39, 'forty' => 40,
//         'forty-one' => 41, 'forty-two' => 42, 'forty-three' => 43, 'forty-four' => 44, 'forty-five' => 45,
//         'first' => 1, 'second' => 2, 'third' => 3, 'fourth' => 4, 'fifth' => 5,
//         'top' => 5, 'few' => 3, 'several' => 5, 'many' => 10
//     ];
    
//     // Time periods for the "days" parameter
//     private $timePeriods = [
//         'today' => 1,
//         'yesterday' => 2,
//         'this week' => 7,
//         'last week' => 14,
//         'this month' => 30,
//         'last month' => 60,
//         'this year' => 365,
//         'last year' => 730,
//         'recent' => 30,
//         'recently' => 30,
//         'latest' => 15,
//         'past few days' => 5,
//         'past week' => 7,
//     ];
    
//     // API Endpoint Configuration - Mapped to the actual endpoints in your documentation
//     private $apiEndpoints = [
//         'incident-count' => '/api/incidentCountFilter',
//         'top-state' => '/api/getHighestStateData',
//         'top-five-state' => '/api/getTopFiveStateData',
//         'low-state' => '/api/getLowestRiskState',
//         'lowest-five-state' => '/api/getLowestFiveState',
//         'prevalent-risk-factor' => '/api/prevalentRiskFactor',
//         'prevalent-risk-indicator' => '/api/prevalentRiskIndicator',
//         'risk-indicator-linked-to-fatality' => '/api/riskIndicatorLinkedToFatality',
//         'risk-factor-linked-to-fatality' => '/api/riskFactorLinkedToFatality',
//         'neighbourhood-incidents' => '/api/listIncidents',
//         'primary-risk-factor-affecting-neighbourhood' => '/api/riskFactorAffectingNeighbourhood',
//         'primary-risk-indicator-affecting-neighbourhood' => '/api/riskIndicatorAffectingNeighbourhood',
//         'highest-day-period-for-neighbourhood' => '/api/highestDayPeriodForNeighbourhood',
//         'high-risk-neighbourhood' => '/api/highRiskNeighborhoods',
//         'risk-index' => '/api/riskIndexAPI'
//     ];
    
//     // User context storage for maintaining conversation state
//     private $userContext = [];
    
//     /**
//      * Enhanced endpoint keywords for better query matching
//      */
//     private $endpointKeywords = [
//         "incident-count" => [
//             "how many incidents", "number of incidents", "incident count", "incident statistics", 
//             "crime count", "total incidents", "reported incidents", "incident reports", 
//             "how many crimes", "incidents reported", "incidents occurred", "incidents recorded"
//         ],
//         "top-state" => [
//             "riskiest state", "most dangerous state", "highest risk state", "worst state",
//             "top state", "state with most incidents", "state with highest risk",
//             "worst affected state", "most incidents", "most dangerous"
//         ],
//         "top-five-state" => [
//             "top five states", "top 5 states", "highest risk states", "most dangerous states", 
//             "riskiest states", "states with highest", "most affected states", "worst states", 
//             "highest ranking states", "worst affected states", "top 5"
//         ],
//         "low-state" => [
//             "safest state", "lowest risk state", "least dangerous state", "most secure state",
//             "state with least incidents", "safest place", "best state", "state with lowest risk"
//         ],
//         "lowest-five-state" => [
//             "lowest five states", "lowest 5 states", "safest states", "least dangerous states", 
//             "states with lowest", "least affected states", "best states", "lowest ranking states",
//             "bottom five states", "bottom 5"
//         ],
//         "prevalent-risk-factor" => [
//             "prevalent risk factor", "common risk", "main risk", "primary risk factor", 
//             "dominant risk", "major risk", "key risk factor", "main security concern",
//             "common risk factors", "main security issues", "top risk factors", "top risks"
//         ],
//         "prevalent-risk-indicator" => [
//             "prevalent risk indicator", "common indicator", "main indicator", "key indicator",
//             "dominant indicator", "major indicator", "top indicator", "main risk indicator",
//             "most common indicator"
//         ],
//         "risk-indicator-linked-to-fatality" => [
//             "fatality indicators", "deadly indicators", "indicators causing death", "fatal risk indicators", 
//             "indicators linked to deaths", "deadly risks", "indicators with fatalities",
//             "risk indicators fatalities", "risk indicators deaths"
//         ],
//         "risk-factor-linked-to-fatality" => [
//             "fatality risk factors", "deadly risk factors", "factors causing death", "fatal risk factors",
//             "risk factors linked to deaths", "deadly risks factors", "factors with fatalities",
//             "risk factors fatalities", "risk factors deaths"
//         ],
//         "neighbourhood-incidents" => [
//             "neighbourhood incidents", "local incidents", "incidents in area", "area crime reports", 
//             "community incidents", "incidents near", "neighborhood crime", "local crime reports",
//             "list incidents in neighborhood", "show incidents", "incidents in locality",
//             "crimes in area"
//         ],
//         "primary-risk-factor-affecting-neighbourhood" => [
//             "risk factor neighbourhood", "neighbourhood risk factor", "local risks", 
//             "community risk", "area risks", "neighbourhood threat", "main risk in area",
//             "primary risk in neighborhood", "biggest threat to neighborhood",
//             "main risk factor in area"
//         ],
//         "primary-risk-indicator-affecting-neighbourhood" => [
//             "risk indicator neighbourhood", "specific neighbourhood risk", "local risk indicator", 
//             "community threat indicator", "area specific risks", "neighborhood indicators",
//             "main indicator in area", "specific threats in neighborhood",
//             "primary indicator in neighborhood"
//         ],
//         "highest-day-period-for-neighbourhood" => [
//             "dangerous time", "risky period", "high risk time", "peak incident time", 
//             "when incidents occur", "time of day risk", "risk hours", "dangerous hours",
//             "highest risk period", "when are incidents most common", "riskiest time",
//             "when should I be careful"
//         ],
//         "high-risk-neighbourhood" => [
//             "dangerous neighbourhood", "unsafe area", "high risk area", "crime hotspot", 
//             "dangerous district", "risk zones", "unsafe neighborhood", "danger areas",
//             "areas to avoid", "worst neighborhoods", "risky areas", "high crime areas"
//         ],
//         "risk-index" => [
//             "risk index", "risk score", "risk rating", "security score", "risk assessment", 
//             "security index", "safety rating", "risk level", "threat level",
//             "overall risk", "security rating", "risk measurement"
//         ]
//     ];

//     /**
//      * Response templates for formatting API responses
//      */
//     private $responseTemplates = [
//         "incident-count" => [
//             "title" => "Incident Report for {state}",
//             "content" => "Based on our records, there {verb} {count} incident{plural} reported in {state} {period}.",
//             "breakdown" => "### Security Incident Breakdown:\n\n{breakdown}",
//             "victims" => "Total number of victims: **{victims}**",
//             "casualties" => "Total number of casualties: **{casualties}**",
//             "addon" => "These incidents encompass various security threats including {factors}."
//         ],
//         "top-state" => [
//             "title" => "Highest Risk State in Nigeria",
//             "content" => "Based on our analysis, **{state}** has the highest security risk {period} with **{count}** reported incidents.",
//             "victims" => "Total number of victims: **{victims}**",
//             "casualties" => "Total number of casualties: **{casualties}**",
//             "recommendation" => "### Security Recommendation\n\nTravelers and businesses in {state} should exercise heightened security awareness and implement robust security measures."
//         ],
//         "top-five-state" => [
//             "title" => "Top 5 Highest-Risk States in Nigeria",
//             "intro" => "Based on our analysis, these are the five states with the highest security risk {period}:",
//             "item" => "**{rank}. {state}** - **{count}** incidents, {victims} victims, {casualties} casualties",
//             "recommendation" => "### Security Advisory\n\nThese states require enhanced security protocols and regular risk assessment for both individuals and organizations operating within them."
//         ],
//         "low-state" => [
//             "title" => "Lowest Risk State in Nigeria",
//             "content" => "Based on our analysis, **{state}** has the lowest security risk {period} with **{count}** reported incidents.",
//             "empty" => "Based on our analysis, we haven't recorded any security incidents in any state matching your query parameters.",
//             "victims" => "Total number of victims: **{victims}**",
//             "casualties" => "Total number of casualties: **{casualties}**",
//             "recommendation" => "While {state} shows the lowest risk profile among Nigerian states, standard security precautions are still recommended."
//         ],
//         "lowest-five-state" => [
//             "title" => "5 Safest States in Nigeria",
//             "intro" => "Based on our analysis, these are the five states with the lowest security risk {period}:",
//             "item" => "**{rank}. {state}** - **{count}** incidents, {victims} victims, {casualties} casualties",
//             "recommendation" => "### Security Note\n\nEven in states with lower risk profiles, it's advisable to stay vigilant and maintain basic security awareness."
//         ],
//         "prevalent-risk-factor" => [
//             "title" => "Prevalent Risk Factor{plural} in {state}",
//             "intro" => "Based on our analysis of security incidents {period}, the most common risk factor{plural} {verb}:",
//             "item" => "**{rank}. {factor}** - {count} incidents ({percentage}%)",
//             "recommendation" => "### Security Implication\n\nSecurity strategies in this region should prioritize mitigation measures against {mainFactor}, which represents the most significant threat based on incident frequency."
//         ],
//         "prevalent-risk-indicator" => [
//             "title" => "Prevalent Risk Indicator{plural} in {state}",
//             "intro" => "Based on our analysis of security incidents {period}, the most common risk indicator{plural} {verb}:",
//             "item" => "**{rank}. {indicator}** - {count} incidents ({percentage}%)",
//             "recommendation" => "### Security Implication\n\nSpecific security protocols addressing {mainIndicator} should be prioritized in this region, as it represents the most frequent specific threat."
//         ],
//         "risk-indicator-linked-to-fatality" => [
//             "title" => "Risk Indicators Linked to Fatalities in {state}",
//             "intro" => "Based on our analysis {period}, these risk indicators are associated with the highest number of deaths:",
//             "item" => "**{rank}. {indicator}** - {deaths} fatalities",
//             "recommendation" => "### Critical Security Advisory\n\nIncidents involving {topIndicator} have proven to be the deadliest and require specialized security protocols and heightened vigilance."
//         ],
//         "risk-factor-linked-to-fatality" => [
//             "title" => "Risk Factors Linked to Fatalities in {state}",
//             "intro" => "Based on our analysis {period}, these risk categories are associated with the highest number of deaths:",
//             "item" => "**{rank}. {factor}** - {deaths} fatalities",
//             "recommendation" => "### Critical Security Advisory\n\nIncidents categorized under {topFactor} have resulted in the highest number of casualties and should be treated with the utmost security concern."
//         ],
//         "neighbourhood-incidents" => [
//             "title" => "Recent Incidents in {neighbourhood}, {state}",
//             "intro" => "In the past {days} days, the following security incidents have been reported in {neighbourhood}:",
//             "item" => "**{date}**: {caption} ({riskFactor} - {riskIndicator})\n  - Victims: {victims}, Casualties: {casualties}\n  - Business impact: {businessImpact}\n",
//             "empty" => "No security incidents have been reported in {neighbourhood}, {state} within the past {days} days.",
//             "recommendation" => "### Area Security Assessment\n\nBased on recent incidents, {neighbourhood} has experienced {incidentCount} security events, primarily related to {mainRiskFactor}. {securityLevel} security measures are recommended."
//         ],
//         "primary-risk-factor-affecting-neighbourhood" => [
//             "title" => "Primary Risk Factor in {neighbourhood}, {state}",
//             "content" => "Based on incidents from the past {days} days, the main security threat in {neighbourhood} is **{riskFactor}** with **{count}** occurrences.",
//             "empty" => "We don't have sufficient data on security incidents in {neighbourhood}, {state} from the past {days} days to determine the primary risk factor.",
//             "recommendation" => "Residents and businesses in {neighbourhood} should particularly focus on security measures against {riskFactor}-related threats."
//         ],
//         "primary-risk-indicator-affecting-neighbourhood" => [
//             "title" => "Primary Risk Indicator in {neighbourhood}, {state}",
//             "content" => "Based on incidents from the past {days} days, the specific security threat most affecting {neighbourhood} is **{riskIndicator}** with **{count}** occurrences.",
//             "empty" => "We don't have sufficient data on security incidents in {neighbourhood}, {state} from the past {days} days to determine the primary risk indicator.",
//             "recommendation" => "Security protocols in {neighbourhood} should specifically address {riskIndicator}, which is the most frequent specific threat in this area."
//         ],
//         "highest-day-period-for-neighbourhood" => [
//             "title" => "Highest Risk Time in {neighbourhood}, {state}",
//             "content" => "Based on our analysis of incidents from the past {days} days, most security incidents in {neighbourhood} occur during the **{period}** period ({count} incidents).",
//             "empty" => "We don't have sufficient data on security incidents in {neighbourhood}, {state} from the past {days} days to determine the highest risk time period.",
//             "recommendation" => "### Security Advisory\n\nExtra vigilance is recommended during the {period} hours in {neighbourhood}, as this is when security incidents are most likely to occur."
//         ],
//         "high-risk-neighbourhood" => [
//             "title" => "High-Risk Areas in {state}",
//             "intro" => "Based on security incidents from the past {days} days, these neighborhoods have the highest security risk in {state}:",
//             "item" => "**{rank}. {neighbourhood}** - {count} incidents",
//             "empty" => "We don't have sufficient data on security incidents in {state} from the past {days} days to identify high-risk neighborhoods.",
//             "recommendation" => "### Security Advisory\n\nThese areas require enhanced security measures. Travelers and businesses operating in these neighborhoods should implement comprehensive security protocols."
//         ],
//         "risk-index" => [
//             "title" => "Risk Index Analysis for {state}",
//             "content" => "The overall security risk index for {state} is **{score}/10** based on our comprehensive assessment.",
//             "factor" => "Risk is particularly elevated for {indicator} with a score of {indicatorScore}/10.",
//             "comparison" => "This places {state} among the {level} risk states in Nigeria.",
//             "recommendation" => "### Security Implication\n\nBased on this risk profile, {securityLevel} security measures are recommended for operations in {state}."
//         ],
//         "general" => [
//             "error" => "I couldn't find specific information about that. Could you please rephrase your question or provide more details?",
//             "missing_data" => "We don't have enough data to answer that question completely. Here's what I can tell you: {partial_info}",
//             "clarification" => "To help you better, could you specify which {parameter} you're interested in?",
//             "suggestion" => "You might also be interested in knowing that {related_info}"
//         ]
//     ];
    
//     /**
//      * Handle the chat request from the user
//      * Enhanced with NLP, context awareness, and improved response formatting
//      */
//     public function chat(Request $request)
//     {
//         // Get user query and session ID for context tracking
//         $userQuery = strtolower(trim($request->input('message')));
//         $sessionId = $request->session()->getId();
        
//         // Initialize context if it doesn't exist
//         if (!isset($this->userContext[$sessionId])) {
//             $this->userContext[$sessionId] = [
//                 'last_query' => '',
//                 'last_state' => '',
//                 'last_year' => '',
//                 'last_risk_factor' => '',
//                 'last_neighbourhood' => '',
//                 'conversation_count' => 0
//             ];
//         }
        
//         // Update conversation count
//         $this->userContext[$sessionId]['conversation_count']++;
        
//         // Log the incoming query for debugging
//         Log::info("Received query: '$userQuery' [Session: $sessionId]");
        
//         // Check for greetings first (enhanced to avoid returning just greetings for substantive questions)
//         if ($this->userContext[$sessionId]['conversation_count'] <= 1 && $this->isGreeting($userQuery)) {
//             return response()->json(['response' => $this->knowledgeBase["greeting_response"]]);
//         }
        
//         // Check expanded knowledge base
//         $knowledgeResponse = $this->checkKnowledgeBase($userQuery);
//         if ($knowledgeResponse) {
//             Log::info("Responding from knowledge base for: '$userQuery'");
//             return response()->json(['response' => $knowledgeResponse]);
//         }
        
//         // Validate and enhance query
//         $enhancedQuery = $this->enhanceQuery($userQuery, $this->userContext[$sessionId]);
//         Log::info("Enhanced query: '$enhancedQuery'");
        
//         $validationResult = $this->validateQuery($enhancedQuery);
//         if ($validationResult) {
//             return response()->json(['response' => $validationResult]);
//         }
        
//         // Process endpoint request with enhanced NLP detection
//         list($endpoint, $params, $error) = $this->buildEndpoint($enhancedQuery, $this->userContext[$sessionId]);
        
//         // Log the selected endpoint and parameters for debugging
//         Log::info("Selected endpoint: '$endpoint', Parameters: " . json_encode($params));
        
//         // Update user context with the current query information
//         $this->updateUserContext($sessionId, $enhancedQuery, $params);
        
//         if ($error) {
//             return response()->json(['response' => $this->formatErrorResponse($error, $enhancedQuery)]);
//         }

//         if ($endpoint) {
//             return $this->fetchRiskData($endpoint, $params, $enhancedQuery);
//         }
        
//         // Final Groq fallback with knowledge base recheck and better prompt engineering
//         return $this->askGroqAPI($enhancedQuery, null, null, true);
//     }
    
//     /**
//      * Check if the query is a simple greeting
//      */
//     private function isGreeting($query)
//     {
//         $query = trim($query);
//         if (empty($query)) return true;
        
//         // Check if query contains only greeting words
//         $words = explode(' ', $query);
//         if (count($words) <= 2) {
//             foreach ($words as $word) {
//                 if (in_array(strtolower($word), $this->knowledgeBase["greetings"])) {
//                     return true;
//                 }
//             }
//         }
        
//         return false;
//     }
    
//     /**
//      * Check knowledge base with improved matching
//      */
//     private function checkKnowledgeBase($query)
//     {
//         // Check direct matches in the knowledge base sections
//         foreach ($this->knowledgeBase as $section => $content) {
//             if ($section === 'greetings' || $section === 'greeting_response') {
//                 continue;
//             }
            
//             if (is_array($content)) {
//                 foreach ($content as $question => $answer) {
//                     // Check for exact matches or if the query contains the knowledge base question
//                     if ($query === $question || stripos($query, $question) !== false) {
//                         return $this->formatKnowledgeResponse($answer, $section);
//                     }
                    
//                     // Check for semantic similarity with key phrases
//                     $similarity = $this->calculateSimilarity($query, $question);
//                     if ($similarity > 0.7) { // Threshold for semantic similarity
//                         return $this->formatKnowledgeResponse($answer, $section);
//                     }
//                 }
//             }
//         }
        
//         return null;
//     }
//         /**
//      * Calculate semantic similarity between two strings (simple implementation)
//      */
//     private function calculateSimilarity($str1, $str2)
//     {
//         $words1 = explode(' ', strtolower($str1));
//         $words2 = explode(' ', strtolower($str2));
        
//         $intersection = array_intersect($words1, $words2);
//         $union = array_unique(array_merge($words1, $words2));
        
//         if (count($union) === 0) return 0;
//         return count($intersection) / count($union);
//     }
//     // * Format knowledge base responses with enhanced presentation
//     // */
//    private function formatKnowledgeResponse($answer, $section)
//    {
//        $formattedAnswer = $answer;
       
//        // Add formatting based on the section
//        switch ($section) {
//            case 'about_nri':
//                $formattedAnswer = "📊 **About Nigeria Risk Index**\n\n" . $formattedAnswer;
//                break;
//            case 'features':
//                $formattedAnswer = "🔍 **NRI Feature Information**\n\n" . $formattedAnswer;
//                break;
//            case 'updates':
//                $formattedAnswer = "🔄 **NRI Updates**\n\n" . $formattedAnswer;
//                break;
//            case 'contact':
//                $formattedAnswer = "📞 **Contact & Support**\n\n" . $formattedAnswer;
//                break;
//        }
       
//        return $formattedAnswer;
//    }
   
//    /**
//     * Enhance query with context awareness and spelling corrections
//     */
//    private function enhanceQuery($query, $context)
//    {
//        $enhancedQuery = $query;
       
//        // Apply spelling corrections for state names
//        foreach ($this->stateCorrections as $misspelled => $correct) {
//            $enhancedQuery = str_replace($misspelled, $correct, $enhancedQuery);
//        }
       
//        // Fill in context from previous queries if applicable
//        if (strpos($enhancedQuery, 'there') !== false && !empty($context['last_state'])) {
//            $enhancedQuery = str_replace('there', $context['last_state'], $enhancedQuery);
//        }
       
//        if (strpos($enhancedQuery, 'that year') !== false && !empty($context['last_year'])) {
//            $enhancedQuery = str_replace('that year', $context['last_year'], $enhancedQuery);
//        }
       
//        if (strpos($enhancedQuery, 'that risk') !== false && !empty($context['last_risk_factor'])) {
//            $enhancedQuery = str_replace('that risk', $context['last_risk_factor'], $enhancedQuery);
//        }
       
//        if (strpos($enhancedQuery, 'that neighborhood') !== false && !empty($context['last_neighbourhood'])) {
//            $enhancedQuery = str_replace('that neighborhood', $context['last_neighbourhood'], $enhancedQuery);
//        }
       
//        if (strpos($enhancedQuery, 'that area') !== false && !empty($context['last_neighbourhood'])) {
//            $enhancedQuery = str_replace('that area', $context['last_neighbourhood'], $enhancedQuery);
//        }
       
//        // Add default values for common queries when missing critical parameters
//        if (strpos($enhancedQuery, 'risk index') !== false && !$this->containsYear($enhancedQuery)) {
//            $enhancedQuery .= ' ' . date('Y');
//        }
       
//        return $enhancedQuery;
//    }
   
//    /**
//     * Update user context with current query information
//     */
//    private function updateUserContext($sessionId, $query, $params)
//    {
//        $this->userContext[$sessionId]['last_query'] = $query;
       
//        if (isset($params['state'])) {
//            $this->userContext[$sessionId]['last_state'] = $params['state'];
//        }
       
//        if (isset($params['year'])) {
//            $this->userContext[$sessionId]['last_year'] = $params['year'];
//        }
       
//        if (isset($params['riskfactor'])) {
//            $this->userContext[$sessionId]['last_risk_factor'] = $params['riskfactor'];
//        }
       
//        if (isset($params['neighbourhood'])) {
//            $this->userContext[$sessionId]['last_neighbourhood'] = $params['neighbourhood'];
//        }
//    }
   
//    /**
//     * Check if query contains a year
//     */
//    private function containsYear($query)
//    {
//        foreach ($this->validYears as $year) {
//            if (strpos($query, $year) !== false) {
//                return true;
//            }
//        }
//        return false;
//    }
   
//    /**
//     * Validate the user's query for consistency with enhanced error messaging
//     */
//    private function validateQuery($query)
//    {
//        // Check for multiple years
//        $foundYears = [];
//        foreach ($this->validYears as $year) {
//            if (strpos($query, $year) !== false) $foundYears[] = $year;
//        }
//        if (count($foundYears) > 1) {
//            return "I notice you've mentioned multiple years (" . implode(', ', $foundYears) . "). To provide accurate information, please specify only one year between 2018-2025.";
//        }

//        // Check for invalid year
//        if (!empty($foundYears)) {
//            $year = $foundYears[0];
//            if (!in_array($year, $this->validYears)) {
//                return "I can only provide data between 2018-2025. Please specify a year within this range.";
//            }
//        }

//        // Check for multiple endpoints
//        $foundEndpoints = $this->detectEndpoints($query);
//        if (count($foundEndpoints) > 1) {
//            return "Your question seems to cover multiple types of information (" 
//                . implode(", ", array_map(function($ep) { return str_replace("-", " ", $ep); }, $foundEndpoints)) 
//                . "). To provide the most accurate answer, please focus on one specific question at a time.";
//        }

//        return null;
//    }
   
//    /**
//     * Detect which endpoints match the query using improved scoring algorithm
//     */
//    private function detectEndpoints($query)
//    {
//        $scores = [];
       
//        // Score each endpoint based on keyword matches
//        foreach ($this->endpointKeywords as $endpoint => $keywords) {
//            $scores[$endpoint] = 0;
//            foreach ($keywords as $keyword) {
//                // Exact match gets higher score
//                if (stripos($query, $keyword) !== false) {
//                    $scores[$endpoint] += 2;
                   
//                    // Bonus points for exact keyword at beginning of query
//                    if (stripos($query, $keyword) === 0) {
//                        $scores[$endpoint] += 1;
//                    }
//                }
               
//                // Partial matches get lower score
//                similar_text(strtolower($query), strtolower($keyword), $percent);
//                if ($percent > 70) {
//                    $scores[$endpoint] += $percent / 100;
//                }
//            }
//        }
       
//        // Special case handling for query types
       
//        // Special case for incident-count - if asking about number of incidents in a state
//        if ((stripos($query, 'how many') !== false || stripos($query, 'number of') !== false) && 
//            (stripos($query, 'incident') !== false || stripos($query, 'crime') !== false)) {
//            foreach ($this->validStates as $state) {
//                if (stripos($query, $state) !== false) {
//                    $scores['incident-count'] += 3;
//                    break;
//                }
//            }
//        }
       
//        // Special case for prevalent-risk-factor - if asking about main risk factors
//        if (stripos($query, 'prevalent') !== false && 
//            (stripos($query, 'risk factor') !== false || stripos($query, 'risk factors') !== false)) {
//            $scores['prevalent-risk-factor'] += 3;
//        }
       
//        // Special case for prevalent-risk-indicator - if asking about specific risk indicators
//        if (stripos($query, 'prevalent') !== false && 
//            (stripos($query, 'indicator') !== false || stripos($query, 'indicators') !== false)) {
//            $scores['prevalent-risk-indicator'] += 3;
//        }
       
//        // Special case for neighborhood queries - if asking about specific neighborhood
//        if (stripos($query, 'neighbourhood') !== false || stripos($query, 'neighborhood') !== false || 
//            stripos($query, 'area') !== false || stripos($query, 'community') !== false) {
           
//            if (stripos($query, 'incident') !== false || stripos($query, 'crime') !== false || 
//                stripos($query, 'event') !== false || stripos($query, 'report') !== false) {
//                $scores['neighbourhood-incidents'] += 3;
//            }
           
//            if (stripos($query, 'risk factor') !== false || stripos($query, 'main risk') !== false ||
//                stripos($query, 'primary risk') !== false || stripos($query, 'biggest threat') !== false) {
//                $scores['primary-risk-factor-affecting-neighbourhood'] += 3;
//            }
           
//            if (stripos($query, 'risk indicator') !== false || stripos($query, 'specific risk') !== false ||
//                stripos($query, 'specific threat') !== false) {
//                $scores['primary-risk-indicator-affecting-neighbourhood'] += 3;
//            }
           
//            if (stripos($query, 'time') !== false || stripos($query, 'period') !== false ||
//                stripos($query, 'when') !== false || stripos($query, 'hours') !== false) {
//                $scores['highest-day-period-for-neighbourhood'] += 3;
//            }
//        }
       
//        // Special case for risk-index - if asking about risk scores or assessments
//        if (stripos($query, 'risk index') !== false || stripos($query, 'risk score') !== false || 
//            stripos($query, 'risk assessment') !== false || stripos($query, 'risk rating') !== false) {
//            $scores['risk-index'] += 3;
//        }
       
//        // Sort by score and get top matches
//        arsort($scores);
       
//        // Log the scores for debugging
//        Log::info("Endpoint detection scores: " . json_encode($scores));
       
//        $topEndpoints = [];
//        foreach ($scores as $endpoint => $score) {
//            if ($score > 1) { // Only consider significant matches
//                $topEndpoints[] = $endpoint;
//            }
//            if (count($topEndpoints) >= 2) break;
//        }
       
//        return $topEndpoints;
//    }
   
//    /**
//     * Enhanced endpoint builder with improved parameter extraction and NLP
//     */
//    private function buildEndpoint($query, $context = [])
//    {
//        $selectedEndpoint = null;
//        $params = [];
//        $error = null;
       
//        // Detect endpoints with advanced scoring
//        $detectedEndpoints = $this->detectEndpoints($query);
//        if (!empty($detectedEndpoints)) {
//            $selectedEndpoint = $detectedEndpoints[0];
//        } else {
//            // Default handling for queries without clear endpoint match
//            if ($this->hasStateReference($query)) {
//                // If query mentions a state but no specific endpoint is detected,
//                // default to incident-count as the most general state-level query
//                $selectedEndpoint = 'incident-count';
//            }
//        }
       
//        // If we still don't have an endpoint, we can't process this query
//        if (!$selectedEndpoint) {
//            return [null, [], "I'm not sure what specific information you're looking for. Could you rephrase your question to ask about specific risk data, incidents in a particular state, or safety information for a neighborhood?"];
//        }
       
//        // Define allowed parameters for each endpoint based on the API documentation
//        $allowedParams = [
//            'incident-count' => ['state', 'year', 'riskfactor', 'riskindicator'],
//            'top-state' => ['year', 'riskfactor', 'riskindicator'],
//            'top-five-state' => ['year', 'riskfactor', 'riskindicator'],
//            'low-state' => ['year', 'riskfactor', 'riskindicator'],
//            'lowest-five-state' => ['year', 'riskfactor', 'riskindicator'],
//            'prevalent-risk-factor' => ['state', 'year', 'limit'],
//            'prevalent-risk-indicator' => ['state', 'year', 'limit'],
//            'risk-indicator-linked-to-fatality' => ['state', 'year', 'limit'],
//            'risk-factor-linked-to-fatality' => ['state', 'year', 'limit'],
//            'neighbourhood-incidents' => ['state', 'neighbourhood', 'days'],
//            'primary-risk-factor-affecting-neighbourhood' => ['state', 'neighbourhood', 'days'],
//            'primary-risk-indicator-affecting-neighbourhood' => ['state', 'neighbourhood', 'days'],
//            'highest-day-period-for-neighbourhood' => ['state', 'neighbourhood', 'days'],
//            'high-risk-neighbourhood' => ['state', 'riskfactors', 'riskindicators', 'days'],
//            'risk-index' => ['year', 'risk-indicator']
//        ];
       
//        if (isset($allowedParams[$selectedEndpoint])) {
//            // Extract parameters based on the selected endpoint's requirements
           
//            // Extract state parameter when allowed
//            if (in_array('state', $allowedParams[$selectedEndpoint])) {
//                $state = $this->extractState($query);
//                if ($state) {
//                    $params['state'] = $this->formatStateParam($state);
//                } elseif (!empty($context['last_state'])) {
//                    // Use context if no state is specified in the current query
//                    $params['state'] = $this->formatStateParam($context['last_state']);
//                }
//            }
           
//            // Extract neighbourhood parameter when allowed
//            if (in_array('neighbourhood', $allowedParams[$selectedEndpoint])) {
//                $neighbourhood = $this->extractNeighbourhood($query, isset($params['state']) ? $params['state'] : '');
//                if ($neighbourhood) {
//                    $params['neighbourhood'] = $neighbourhood;
//                } elseif (!empty($context['last_neighbourhood'])) {
//                    // Use context if no neighbourhood is specified in the current query
//                    $params['neighbourhood'] = $context['last_neighbourhood'];
//                }
//            }
           
//            // Extract days parameter when allowed
//            if (in_array('days', $allowedParams[$selectedEndpoint])) {
//                $days = $this->extractDays($query);
//                if ($days) {
//                    $params['days'] = $days;
//                } else {
//                    // Default to 30 days if no specific period mentioned
//                    $params['days'] = 30;
//                }
//            }
           
//            // Extract year parameter when allowed
//            if (in_array('year', $allowedParams[$selectedEndpoint])) {
//                $year = $this->extractYear($query);
//                if ($year) {
//                    $params['year'] = $year;
//                } elseif (!empty($context['last_year'])) {
//                    // Use context if no year is specified in the current query
//                    $params['year'] = $context['last_year'];
//                } else {
//                    // Default to current year if no year specified
//                    $params['year'] = date('Y');
//                }
//            }
           
//            // Extract limit parameter when allowed
//            if (in_array('limit', $allowedParams[$selectedEndpoint])) {
//                $limit = $this->extractLimit($query);
//                if ($limit) {
//                    $params['limit'] = $limit;
//                } else {
//                    // Default limits for different endpoints
//                    if (in_array($selectedEndpoint, ['prevalent-risk-factor', 'prevalent-risk-indicator'])) {
//                        // Check if query suggests multiple results
//                        if (stripos($query, 'factors') !== false || stripos($query, 'indicators') !== false ||
//                            stripos($query, 'top') !== false || stripos($query, 'main') !== false) {
//                            $params['limit'] = 5; // Show multiple results
//                        } else {
//                            $params['limit'] = 1; // Show just the top one
//                        }
//                    } else {
//                        $params['limit'] = 5; // Default for other endpoints
//                    }
//                }
//            }
           
//            // Extract riskfactor parameter when allowed
//            if (in_array('riskfactor', $allowedParams[$selectedEndpoint])) {
//                $riskFactor = $this->extractRiskFactor($query);
//                if ($riskFactor) {
//                    $params['riskfactor'] = $riskFactor;
//                } elseif (!empty($context['last_risk_factor'])) {
//                    // Use context if no risk factor is specified in the current query
//                    $params['riskfactor'] = $context['last_risk_factor'];
//                }
//            }
           
//            // Extract riskindicator parameter when allowed
//            if (in_array('riskindicator', $allowedParams[$selectedEndpoint])) {
//                list($indicator, $category) = $this->extractRiskIndicator($query);
//                if ($indicator) {
//                    $params['riskindicator'] = $indicator;
//                }
//            }
           
//            // Extract multiple risk factors for high-risk-neighbourhood
//            if (in_array('riskfactors', $allowedParams[$selectedEndpoint])) {
//                $riskFactors = $this->extractMultipleRiskFactors($query);
//                if (!empty($riskFactors)) {
//                    $params['riskfactors'] = implode(',', $riskFactors);
//                }
//            }
           
//            // Extract multiple risk indicators for high-risk-neighbourhood
//            if (in_array('riskindicators', $allowedParams[$selectedEndpoint])) {
//                $riskIndicators = $this->extractMultipleRiskIndicators($query);
//                if (!empty($riskIndicators)) {
//                    $params['riskindicators'] = implode(',', $riskIndicators);
//                }
//            }
           
//            // Extract risk-indicator parameter for risk-index
//            if (in_array('risk-indicator', $allowedParams[$selectedEndpoint])) {
//                list($indicator, $category) = $this->extractRiskIndicator($query);
//                if ($indicator) {
//                    $params['risk-indicator'] = $indicator;
//                }
//            }
           
//            // Validate required parameters for specific endpoints
//            $error = $this->validateRequiredParameters($selectedEndpoint, $params);
           
//            Log::info("Built endpoint: $selectedEndpoint with params: " . json_encode($params));
//            return [$selectedEndpoint, $params, $error];
//        }
       
//        return [null, [], "I'm not sure what specific information you're looking for. Could you rephrase your question to ask about specific risk data, incidents in a particular state, or safety information for a neighborhood?"];
//    }
   
//    /**
//     * Format state parameter according to what the API expects
//     */
//    private function formatStateParam($state)
//    {
//        // Based on API documentation, we can infer if it expects:
//        // - lowercase: "lagos"
//        // - uppercase: "LAGOS"
//        // - title case: "Lagos"
       
//        // Title case is the most common convention, so using that:
//        return ucfirst(strtolower($state));
//    }
   
//    /**
//     * Check if query refers to any state
//     */
//    private function hasStateReference($query)
//    {
//        foreach ($this->validStates as $state) {
//            if (stripos($query, $state) !== false) {
//                return true;
//            }
//        }
       
//        // Also check for common state reference phrases
//        $stateReferences = ['state', 'states', 'province', 'region', 'location', 'area'];
//        foreach ($stateReferences as $ref) {
//            if (stripos($query, $ref) !== false) {
//                return true;
//            }
//        }
       
//        return false;
//    }
   
//    /**
//     * Validate that required parameters are present for specific endpoints
//     */
//    private function validateRequiredParameters($endpoint, $params)
//    {
//        // Define required parameters for each endpoint
//        $requiredParams = [
//            "incident-count" => ['state'],
//            "prevalent-risk-factor" => ['state'],  // State is required for prevalent risk factor
//            "prevalent-risk-indicator" => ['state'],  // State is required for prevalent risk indicator
//            "risk-indicator-linked-to-fatality" => ['state'], // State is required for fatality information
//            "risk-factor-linked-to-fatality" => ['state'], // State is required for fatality information
//            "neighbourhood-incidents" => ['state', 'neighbourhood'],
//            "primary-risk-factor-affecting-neighbourhood" => ['state', 'neighbourhood'],
//            "primary-risk-indicator-affecting-neighbourhood" => ['state', 'neighbourhood'],
//            "highest-day-period-for-neighbourhood" => ['state', 'neighbourhood'],
//            "high-risk-neighbourhood" => ['state']
//        ];
       
//        // If endpoint has required parameters defined
//        if (isset($requiredParams[$endpoint])) {
//            foreach ($requiredParams[$endpoint] as $param) {
//                if (!isset($params[$param])) {
//                    switch ($param) {
//                        case 'state':
//                            return "Please specify which state you're interested in. For example, 'in Lagos' or 'for Kaduna state'.";
//                        case 'neighbourhood':
//                            return "Please specify which neighborhood you'd like information about. For example, 'in Ikeja' or 'for Wuse area'.";
//                        default:
//                            return "Please provide the $param parameter for this query.";
//                    }
//                }
//            }
//        }
       
//        return null;
//    }
   
//    /**
//     * Extract state name from query with fuzzy matching
//     */
//    private function extractState($query)
//    {
//        // Check for exact matches first
//        foreach ($this->validStates as $state) {
//            if (stripos($query, $state) !== false) {
//                return strtolower($state);
//            }
//        }
       
//        // Try correction map for common misspellings
//        foreach ($this->stateCorrections as $misspelled => $correct) {
//            if (stripos($query, $misspelled) !== false) {
//                return $correct;
//            }
//        }
       
//        // Try fuzzy matching with state names
//        $words = explode(' ', $query);
//        foreach ($words as $word) {
//            $word = trim($word);
//            if (strlen($word) < 3) continue;
           
//            foreach ($this->validStates as $state) {
//                // Calculate similarity between word and state name
//                similar_text($word, $state, $percent);
//                if ($percent > 80) {  // 80% similarity threshold
//                    return $state;
//                }
//            }
//        }
       
//        return null;
//    }
   
//    /**
//     * Extract neighbourhood name from query
//     */
//    private function extractNeighbourhood($query, $state = '')
//    {
//        // Remove state name from query to avoid confusion
//        if (!empty($state)) {
//            $query = str_ireplace($state, '', $query);
//        }
       
//        // Various patterns to extract neighbourhood names
//        $patterns = [
//            // After location prepositions
//            '/\b(?:in|at|for|around|near)\s+([A-Za-z0-9\s\-\'\.]+?)(?:\s+(?:area|neighbourhood|neighborhood|district|zone|community|locality|vicinity|region))?(?:\s+(?:in|at|for|of)\s|$)/i',
           
//            // Before area/neighbourhood keywords
//            '/\b([A-Za-z0-9\s\-\'\.]+?)\s+(?:area|neighbourhood|neighborhood|district|zone|community|locality|vicinity|region)\b/i',
           
//            // After specific area indicators
//            '/\barea\s+(?:of|called|named)\s+([A-Za-z0-9\s\-\'\.]+)\b/i',
           
//            // After "neighbourhood" keyword
//            '/\b(?:neighbourhood|neighborhood)\s+(?:of|called|named)?\s*([A-Za-z0-9\s\-\'\.]+)\b/i'
//        ];
       
//        foreach ($patterns as $pattern) {
//            if (preg_match($pattern, $query, $matches)) {
//                $neighbourhood = trim($matches[1]);
               
//                // Clean up the neighbourhood name
//                $neighbourhood = preg_replace('/\b(?:the|area|of|in|and|or)\b/i', '', $neighbourhood);
//                $neighbourhood = preg_replace('/\s+/', ' ', $neighbourhood);
//                $neighbourhood = trim($neighbourhood);
               
//                if (!empty($neighbourhood) && strlen($neighbourhood) > 2) {
//                    return $neighbourhood;
//                }
//            }
//        }
       
//        return null;
//    }
   
//    /**
//     * Extract days parameter from time period phrases or explicit numbers
//     */
//    private function extractDays($query)
//    {
//        // Check for time period phrases first
//        foreach ($this->timePeriods as $phrase => $days) {
//            if (stripos($query, $phrase) !== false) {
//                return $days;
//            }
//        }
       
//        // Check for number words with day/week references
//        foreach ($this->numberWords as $word => $value) {
//            if (stripos($query, $word . ' day') !== false || stripos($query, $word . ' days') !== false) {
//                return $value;
//            }
//            if (stripos($query, $word . ' week') !== false || stripos($query, $word . ' weeks') !== false) {
//                return $value * 7;  // Convert weeks to days
//            }
//            if (stripos($query, $word . ' month') !== false || stripos($query, $word . ' months') !== false) {
//                return $value * 30;  // Approximate months as 30 days
//            }
//        }
       
//        // Check for numeric days/weeks/months
//        $patterns = [
//            '/\b(?:in the last|over the past|for the past|in past|last|past)\s+(\d+)\s+days?\b/i' => 1,
//            '/\b(?:in the last|over the past|for the past)\s+(\d+)\s+weeks?\b/i' => 7,
//            '/\b(?:in the last|over the past|for the past)\s+(\d+)\s+months?\b/i' => 30,
//            '/\b([1-9][0-9]?)\s+days?\b/i' => 1,
//            '/\b([1-9])\s+weeks?\b/i' => 7,
//            '/\b([1-9])\s+months?\b/i' => 30
//        ];
       
//        foreach ($patterns as $pattern => $multiplier) {
//            if (preg_match($pattern, $query, $matches)) {
//                $value = (int)$matches[1];
//                return $value * $multiplier;
//            }
//        }
       
//        return null;
//    }
   
//    /**
//     * Extract year from query
//     */
//    private function extractYear($query)
//    {
//        foreach ($this->validYears as $year) {
//            if (strpos($query, $year) !== false) {
//                return $year;
//            }
//        }
       
//        // Check for relative year references
//        if (stripos($query, 'this year') !== false || stripos($query, 'current year') !== false) {
//            return date('Y');
//        }
       
//        if (stripos($query, 'last year') !== false || stripos($query, 'previous year') !== false) {
//            return (string)(date('Y') - 1);
//        }
       
//        if (stripos($query, 'two years ago') !== false) {
//            return (string)(date('Y') - 2);
//        }
       
//        if (stripos($query, 'next year') !== false) {
//            return (string)(date('Y') + 1);
//        }
       
//        return null;
//    }
   
//    /**
//     * Extract limit parameter from query
//     */
//    private function extractLimit($query)
//    {
//        // Check for explicit "top N" or "limit to N" patterns
//        if (preg_match('/\btop\s+([1-9]|[1-4][0-9]|5[0-5])\b/i', $query, $matches)) {
//            return (int)$matches[1];
//        }
       
//        if (preg_match('/\blimit(?:\s+to)?\s+([1-9]|[1-4][0-9]|5[0-5])\b/i', $query, $matches)) {
//            return (int)$matches[1];
//        }
       
//        // Check for number words ("give me five risk factors")
//        foreach ($this->numberWords as $word => $value) {
//            if (stripos($query, $word) !== false) {
//                // Confirm it's related to a limit context
//                if (stripos($query, 'top') !== false || stripos($query, 'limit') !== false ||
//                    stripos($query, 'show me') !== false || stripos($query, 'give me') !== false) {
//                    return $value;
//                }
               
//                // Also match "five risk factors" pattern
//                if (stripos($query, $word . ' risk factor') !== false) {
//                    return $value;
//                }
//            }
//        }
       
//        // Check for explicit numbers in a limit context
//        if (preg_match('/\b([1-9]|[1-4][0-9]|5[0-5])\b/', $query, $matches)) {
//            // Confirm it's related to a limit context
//            if (stripos($query, 'top') !== false || stripos($query, 'limit') !== false ||
//                stripos($query, 'show me') !== false || stripos($query, 'give me') !== false) {
//                return (int)$matches[0];
//            }
//        }
       
//        return null;
//    }
   
//    /**
//     * Extract risk factor from query with better matching
//     */
//    private function extractRiskFactor($query)
//    {
//        foreach ($this->validRiskFactors as $factor) {
//            if (stripos($query, strtolower($factor)) !== false) {
//                return $factor;
//            }
//        }
       
//        // Try partial matching for compound risk factors
//        $partialMatches = [
//            'personal' => 'Personal Threats',
//            'political' => 'Political Threats',
//            'property' => 'Property Threats',
//            'safety' => 'Safety',
//            'violent' => 'Violent Threats'
//        ];
       
//        foreach ($partialMatches as $partial => $full) {
//            if (stripos($query, $partial) !== false) {
//                // Verify it's related to risk context
//                if (stripos($query, 'risk') !== false || stripos($query, 'threat') !== false || 
//                    stripos($query, 'danger') !== false || stripos($query, 'security') !== false) {
//                    return $full;
//                }
//            }
//        }
       
//        return null;
//    }
//    //* Extract risk indicator and its category from query
   
//     private function extractRiskIndicator($query)
//     {
//         // First check for direct mentions of risk indicators
//         foreach ($this->riskIndicators as $category => $indicators) {
//             foreach ($indicators as $indicator) {
//                 $cleanIndicator = str_replace(['_', '/'], ' ', $indicator);
//                 if (stripos($query, strtolower($cleanIndicator)) !== false) {
//                     return [$indicator, $category];
//                 }
//             }
//         }
//         // Then check for keywords that map to risk indicators
//         foreach ($this->riskIndicatorKeywords as $keyword => $indicator) {
//             if (stripos($query, $keyword) !== false) {
//                 // Find which category this indicator belongs to
//                 foreach ($this->riskIndicators as $category => $indicators) {
//                     if (in_array($indicator, $indicators)) {
//                         return [$indicator, $category];
//                     }
//                 }
//             }
//         }
        
//         return [null, null];
//     }
//     private function extractMultipleRiskFactors($query)
//     {
//         $foundFactors = [];
//         foreach ($this->validRiskFactors as $factor) {
//             if (stripos($query, strtolower($factor)) !== false) {
//                 $foundFactors[] = $factor;
//             }
//         }
        
//         // Check for partial matches as well
//         $partialMatches = [
//             'personal' => 'Personal Threats',
//             'political' => 'Political Threats',
//             'property' => 'Property Threats',
//             'safety' => 'Safety',
//             'violent' => 'Violent Threats'
//         ];
        
//         foreach ($partialMatches as $partial => $full) {
//             if (stripos($query, $partial) !== false && !in_array($full, $foundFactors)) {
//                 // Verify it's related to risk context
//                 if (stripos($query, 'risk') !== false || stripos($query, 'threat') !== false || 
//                     stripos($query, 'danger') !== false || stripos($query, 'security') !== false) {
//                     $foundFactors[] = $full;
//                 }
//             }
//         }
        
//         return $foundFactors;
//     }
    
//     /**
//      * Extract multiple risk indicators from query
//      */
//     private function extractMultipleRiskIndicators($query)
//     {
//         $foundIndicators = [];
        
//         // First check for direct mentions
//         foreach ($this->riskIndicators as $category => $indicators) {
//             foreach ($indicators as $indicator) {
//                 $cleanIndicator = str_replace(['_', '/'], ' ', $indicator);
//                 if (stripos($query, strtolower($cleanIndicator)) !== false) {
//                     $foundIndicators[] = $indicator;
//                 }
//             }
//         }
        
//         // Then check for keywords
//         foreach ($this->riskIndicatorKeywords as $keyword => $indicator) {
//             if (stripos($query, $keyword) !== false && !in_array($indicator, $foundIndicators)) {
//                 $foundIndicators[] = $indicator;
//             }
//         }
        
//         return $foundIndicators;
//     }
    
//     /**
//      * Format error responses to be more helpful
//      */
//     private function formatErrorResponse($error, $query)
//     {
//         // Basic error with additional helpful information
//         $response = "Sorry, I couldn't process your request properly. $error";
        
//         // Add suggestions based on the query content
//         if (stripos($query, 'state') !== false || stripos($query, 'location') !== false) {
//             $response .= "\n\nYou can ask me about specific states like Lagos, Abuja, or Kano. For example, 'What's the risk index for Lagos in 2023?' or 'How many incidents were reported in Kaduna last year?'";
//         }
        
//         if (stripos($query, 'neighbourhood') !== false || stripos($query, 'area') !== false) {
//             $response .= "\n\nFor neighborhood queries, please specify both the state and the neighborhood name. For example, 'What are the high-risk areas in Lagos?' or 'List incidents in Wuse, Abuja in the past 30 days.'";
//         }
        
//         if (stripos($query, 'risk') !== false) {
//             $response .= "\n\nI can provide information on different risk factors including Violent Threats, Safety, Property Threats, Political Threats, and Personal Threats. Try asking something like 'What are the prevalent risk factors in Lagos?' or 'Which states have the highest Violent Threats risk?'";
//         }
        
//         return $response;
//     }
    
//     /**
//      * Enhanced API data fetching with proper error handling and better response formatting
//      */
//     private function fetchRiskData($endpoint, $params, $userQuery)
//     {
//         // Map the logical endpoint to the actual API endpoint
//         $apiEndpoint = $this->apiEndpoints[$endpoint] ?? null;
//         if (!$apiEndpoint) {
//             Log::error("No API endpoint mapping found for: $endpoint");
//             return response()->json([
//                 'response' => "I'm sorry, but I don't have access to that type of information at the moment."
//             ]);
//         }
        
//         $apiUrl = "https://nigeriariskindex.com" . $apiEndpoint;
//         $queryString = http_build_query($params);
//         $fullUrl = "$apiUrl?$queryString";
        
//         // Cache key for this specific request
//         $cacheKey = "nri_" . md5($fullUrl);
        
//         // Use caching to improve response time for identical queries
//         if (Cache::has($cacheKey)) {
//             $data = Cache::get($cacheKey);
//             return $this->formatApiResponse($endpoint, $data, $params, $fullUrl, $userQuery);
//         }

//         try {
//             // Log the API call for debugging
//             Log::info("Making API call to: $fullUrl");
            
//             $client = new Client();
//             $response = $client->get($apiUrl, ['query' => $params]);
//             $statusCode = $response->getStatusCode();
//             $contentType = $response->getHeaderLine('Content-Type');
//             $body = $response->getBody()->getContents();
            
//             // Log the raw response for debugging
//             Log::info("API Response ($statusCode): $body");

//             if (strpos($contentType, 'application/json') === false) {
//                 Log::error("API returned non-JSON response: Content-Type: $contentType, Body: $body");
//                 return $this->askGroqAPI(
//                     $userQuery, 
//                     null, 
//                     $fullUrl, 
//                     true, 
//                     "The API returned a non-JSON response, which may indicate an issue with the request."
//                 );
//             }

//             $data = json_decode($body, true);
//             if ($data === null) {
//                 Log::error("Failed to decode API response as JSON: $body");
//                 return $this->askGroqAPI(
//                     $userQuery, 
//                     null, 
//                     $fullUrl, 
//                     true, 
//                     "The API response couldn't be parsed as valid JSON data."
//                 );
//             }

//             // Handle error responses from API
//             if (isset($data['error'])) {
//                 Log::error("API returned error: " . $data['error']);
//                 return response()->json([
//                     'response' => "I'm sorry, but there was an issue retrieving the data: " . $data['error']
//                 ]);
//             }

//             // Handle empty data sets appropriately
//             if ($this->isEmptyResponse($data, $endpoint)) {
//                 return $this->handleEmptyApiResponse($endpoint, $params, $userQuery, $fullUrl);
//             }

//             // Cache successful responses for 1 hour
//             Cache::put($cacheKey, $data, 3600);
            
//             // Format the API response according to endpoint type
//             return $this->formatApiResponse($endpoint, $data, $params, $fullUrl, $userQuery);

//         } catch (RequestException $e) {
//             Log::error("API request failed: " . $e->getMessage() . " for URL: $fullUrl");
//             return $this->handleApiError($e, $userQuery, $fullUrl);
//         }
//     }
    
//     /**
//      * Check if the API response is effectively empty
//      */
//     private function isEmptyResponse($data, $endpoint)
//     {
//         // Check for completely empty data
//         if (empty($data)) {
//             return true;
//         }
        
//         // Different endpoints might represent "empty" in different ways
//         switch ($endpoint) {
//             case 'incident-count':
//                 return isset($data['incident_count']) && $data['incident_count'] === 0;
                
//             case 'top-state':
//             case 'low-state':
//                 return isset($data['state']) && $data['state'] === 'No data available';
                
//             case 'top-five-state':
//             case 'lowest-five-state':
//                 return !isset($data['states']) || empty($data['states']);
                
//             case 'prevalent-risk-factor':
//             case 'prevalent-risk-indicator':
//                 return !isset($data['prevalentRiskFactors']) || empty($data['prevalentRiskFactors']);
                
//             case 'risk-indicator-linked-to-fatality':
//                 return !isset($data['riskIndicatorsFatalities']) || empty($data['riskIndicatorsFatalities']);
                
//             case 'risk-factor-linked-to-fatality':
//                 return !isset($data['riskFactorFatalities']) || empty($data['riskFactorFatalities']);
                
//             case 'neighbourhood-incidents':
//                 // For neighborhood incidents, an empty array is still valid data (just no incidents)
//                 return $data === [];
                
//             case 'primary-risk-factor-affecting-neighbourhood':
//             case 'primary-risk-indicator-affecting-neighbourhood':
//                 return isset($data['riskFactor']) && $data['riskFactor'] === 'No data available';
                
//             case 'highest-day-period-for-neighbourhood':
//                 return isset($data['dayPeriod']) && $data['dayPeriod'] === 'No data available';
                
//             case 'high-risk-neighbourhood':
//                 return empty($data);
                
//             default:
//                 return empty($data);
//         }
//     }
    
//     /**
//      * Format API responses based on endpoint type with improved presentation
//      */
//     private function formatApiResponse($endpoint, $data, $params, $apiUrl, $userQuery)
//     {
//         // Use the response templates for consistent formatting
//         $templates = $this->responseTemplates;
//         $formattedResponse = "";
        
//         // Include state in title/content when available
//         $state = isset($params['state']) ? ucfirst($params['state']) : 'Nigeria';
        
//         // Include time period in descriptions when available
//         $period = "";
//         if (isset($params['year'])) {
//             $period = "in " . $params['year'];
//         } else {
//             $period = "in the recorded period";
//         }
        
//         // Include debug information during development
//         $debug = env('APP_DEBUG', false);
        
//         switch ($endpoint) {
//             case 'incident-count':
//                 $count = $data['incident_count'] ?? 0;
//                 $victims = $data['victim_count'] ?? 0;
//                 $casualties = $data['casualties_count'] ?? 0;

//                 // Create title
//                 $formattedResponse = "## " . str_replace('{state}', $state, $templates[$endpoint]['title']) . "\n\n";
                
//                 // Create main content with proper verb (is/are) and plural (incident/incidents)
//                 $verb = $count == 1 ? "has been" : "have been";
//                 $plural = $count == 1 ? "" : "s";
                
//                 $formattedResponse .= str_replace(
//                     ['{verb}', '{count}', '{plural}', '{state}', '{period}'],
//                     [$verb, $count, $plural, $state, $period],
//                     $templates[$endpoint]['content']
//                 ) . "\n\n";
                
//                 // Add victim and casualty information if available
//                 if ($victims > 0) {
//                     $formattedResponse .= str_replace('{victims}', $victims, $templates[$endpoint]['victims']) . "\n\n";
//                 }
                
//                 if ($casualties > 0) {
//                     $formattedResponse .= str_replace('{casualties}', $casualties, $templates[$endpoint]['casualties']) . "\n\n";
//                 }
                
//                 // Add risk factor context if available
//                 if (isset($params['riskfactor'])) {
//                     $formattedResponse .= "These incidents specifically relate to **" . $params['riskfactor'] . "**.\n\n";
//                 }
                
//                 if (isset($params['riskindicator'])) {
//                     $formattedResponse .= "The specific risk indicator queried was **" . $params['riskindicator'] . "**.\n\n";
//                 }
                
//                 break;
                
//             case 'top-state':
//                 $state = ucfirst($data['state'] ?? 'No data available');
//                 $count = $data['total_incidents'] ?? 0;
//                 $victims = $data['total_victims'] ?? 0;
//                 $casualties = $data['total_deaths'] ?? 0;
                
//                 // Create title and main content
//                 $formattedResponse = "## " . $templates[$endpoint]['title'] . "\n\n";
//                 $formattedResponse .= str_replace(
//                     ['{state}', '{period}', '{count}'],
//                     [$state, $period, $count],
//                     $templates[$endpoint]['content']
//                 ) . "\n\n";
                
//                 // Add victim and casualty information if available
//                 if ($victims > 0) {
//                     $formattedResponse .= str_replace('{victims}', $victims, $templates[$endpoint]['victims']) . "\n\n";
//                 }
                
//                 if ($casualties > 0) {
//                     $formattedResponse .= str_replace('{casualties}', $casualties, $templates[$endpoint]['casualties']) . "\n\n";
//                 }
                
//                 // Add recommendation
//                 $formattedResponse .= str_replace('{state}', $state, $templates[$endpoint]['recommendation']) . "\n\n";
                
//                 break;
                
//             case 'top-five-state':
//                 // Handle the top five states endpoint
//                 if (isset($data['states']) && is_array($data['states'])) {
//                     $formattedResponse = "## " . $templates[$endpoint]['title'] . "\n\n";
//                     $formattedResponse .= str_replace('{period}', $period, $templates[$endpoint]['intro']) . "\n\n";
                    
//                     foreach ($data['states'] as $index => $stateData) {
//                         $rank = $index + 1;
//                         $stateName = ucfirst($stateData['state'] ?? 'Unknown');
//                         $count = $stateData['count'] ?? $stateData['total_incidents'] ?? 0;
//                         $victims = $stateData['total_victims'] ?? 0;
//                         $casualties = $stateData['total_deaths'] ?? 0;
                        
//                         $formattedResponse .= str_replace(
//                             ['{rank}', '{state}', '{count}', '{victims}', '{casualties}'],
//                             [$rank, $stateName, $count, $victims, $casualties],
//                             $templates[$endpoint]['item']
//                         ) . "\n";
//                     }
                    
//                     // Add recommendation
//                     $formattedResponse .= "\n" . $templates[$endpoint]['recommendation'] . "\n\n";
//                 } else {
//                     $formattedResponse = "## No High-Risk States Identified\n\nBased on your query parameters, we couldn't identify any states that match the criteria.\n\n";
//                 }
                
//                 break;
                
//             case 'low-state':
//                 $state = ucfirst($data['state'] ?? 'No data available');
//                 $count = $data['total_incidents'] ?? 0;
//                 $victims = $data['total_victims'] ?? 0;
//                 $casualties = $data['total_deaths'] ?? 0;
                
//                 // Create title
//                 $formattedResponse = "## " . $templates[$endpoint]['title'] . "\n\n";
                
//                 // If no data available, use empty template
//                 if ($state === 'No data available') {
//                     $formattedResponse .= $templates[$endpoint]['empty'] . "\n\n";
//                 } else {
//                     // Create main content
//                     $formattedResponse .= str_replace(
//                         ['{state}', '{period}', '{count}'],
//                         [$state, $period, $count],
//                         $templates[$endpoint]['content']
//                     ) . "\n\n";
                    
//                     // Add victim and casualty information if available
//                     if ($victims > 0) {
//                         $formattedResponse .= str_replace('{victims}', $victims, $templates[$endpoint]['victims']) . "\n\n";
//                     }
                    
//                     if ($casualties > 0) {
//                         $formattedResponse .= str_replace('{casualties}', $casualties, $templates[$endpoint]['casualties']) . "\n\n";
//                     }
                    
//                     // Add recommendation
//                     $formattedResponse .= str_replace('{state}', $state, $templates[$endpoint]['recommendation']) . "\n\n";
//                 }
                
//                 break;
                
//             case 'lowest-five-state':
//                 // Handle the lowest five states endpoint
//                 if (isset($data['states']) && is_array($data['states'])) {
//                     $formattedResponse = "## " . $templates[$endpoint]['title'] . "\n\n";
//                     $formattedResponse .= str_replace('{period}', $period, $templates[$endpoint]['intro']) . "\n\n";
                    
//                     foreach ($data['states'] as $index => $stateData) {
//                         $rank = $index + 1;
//                         $stateName = ucfirst($stateData['state'] ?? 'Unknown');
//                         $count = $stateData['count'] ?? $stateData['total_incidents'] ?? 0;
//                         $victims = $stateData['total_victims'] ?? 0;
//                         $casualties = $stateData['total_deaths'] ?? 0;
                        
//                         $formattedResponse .= str_replace(
//                             ['{rank}', '{state}', '{count}', '{victims}', '{casualties}'],
//                             [$rank, $stateName, $count, $victims, $casualties],
//                             $templates[$endpoint]['item']
//                         ) . "\n";
//                     }
                    
//                     // Add recommendation
//                     $formattedResponse .= "\n" . $templates[$endpoint]['recommendation'] . "\n\n";
//                 } else {
//                     $formattedResponse = "## No Low-Risk States Identified\n\nBased on your query parameters, we couldn't identify any states that match the criteria.\n\n";
//                 }
                
//                 break;
                
//             case 'prevalent-risk-factor':
//                 // Handle prevalent risk factors
//                 if (isset($data['prevalentRiskFactors']) && is_array($data['prevalentRiskFactors'])) {
//                     $factors = $data['prevalentRiskFactors'];
//                     $limitParam = $params['limit'] ?? 1;
//                     $count = count($factors);
//                     $plural = $count > 1 ? "s" : "";
//                     $verb = $count > 1 ? "are" : "is";
                    
//                     // Calculate total incidents for percentage
//                     $totalIncidents = 0;
//                     foreach ($factors as $factor) {
//                         $totalIncidents += $factor['count'];
//                     }
                    
//                     // Create title
//                     $formattedResponse = "## " . str_replace(
//                         ['{plural}', '{state}'],
//                         [$plural, $state],
//                         $templates[$endpoint]['title']
//                     ) . "\n\n";
                    
//                     // Create intro
//                     $formattedResponse .= str_replace(
//                         ['{period}', '{plural}', '{verb}'],
//                         [$period, $plural, $verb],
//                         $templates[$endpoint]['intro']
//                     ) . "\n\n";
                    
//                     // List the risk factors
//                     foreach ($factors as $index => $factor) {
//                         $rank = $index + 1;
//                         $factorName = $factor['riskfactors'] ?? 'Unknown';
//                         $factorCount = $factor['count'] ?? 0;
                        
//                         // Calculate percentage
//                         $percentage = $totalIncidents > 0 ? round(($factorCount / $totalIncidents) * 100, 1) : 0;
                        
//                         $formattedResponse .= str_replace(
//                             ['{rank}', '{factor}', '{count}', '{percentage}'],
//                             [$rank, $factorName, $factorCount, $percentage],
//                             $templates[$endpoint]['item']
//                         ) . "\n";
                        
//                         // Store main factor for recommendation
//                         if ($index === 0) {
//                             $mainFactor = $factorName;
//                         }
//                     }
                    
//                     // Add recommendation if we have at least one factor
//                     if (isset($mainFactor)) {
//                         $formattedResponse .= "\n" . str_replace('{mainFactor}', $mainFactor, $templates[$endpoint]['recommendation']) . "\n\n";
//                     }
//                 } else {
//                     $formattedResponse = "## No Risk Factor Data Available\n\nBased on your query parameters, we couldn't identify prevalent risk factors that match the criteria.\n\n";
//                 }
                
//                 break;
                
//             case 'prevalent-risk-indicator':
//                 // Handle prevalent risk indicators
//                 if (isset($data['prevalentRiskFactors']) && is_array($data['prevalentRiskFactors'])) {
//                     $indicators = $data['prevalentRiskFactors'];
//                     $limitParam = $params['limit'] ?? 1;
//                     $count = count($indicators);
//                     $plural = $count > 1 ? "s" : "";
//                     $verb = $count > 1 ? "are" : "is";
                    
//                     // Calculate total incidents for percentage
//                     $totalIncidents = 0;
//                     foreach ($indicators as $indicator) {
//                         $totalIncidents += $indicator['count'];
//                     }
                    
//                     // Create title
//                     $formattedResponse = "## " . str_replace(
//                         ['{plural}', '{state}'],
//                         [$plural, $state],
//                         $templates[$endpoint]['title']
//                     ) . "\n\n";
                    
//                     // Create intro
//                     $formattedResponse .= str_replace(
//                         ['{period}', '{plural}', '{verb}'],
//                         [$period, $plural, $verb],
//                         $templates[$endpoint]['intro']
//                     ) . "\n\n";
                    
//                     // List the risk indicators
//                     foreach ($indicators as $index => $indicator) {
//                         $rank = $index + 1;
//                         $indicatorName = $indicator['riskindicators'] ?? 'Unknown';
//                         $indicatorCount = $indicator['count'] ?? 0;
                        
//                         // Calculate percentage
//                         $percentage = $totalIncidents > 0 ? round(($indicatorCount / $totalIncidents) * 100, 1) : 0;
                        
//                         $formattedResponse .= str_replace(
//                             ['{rank}', '{indicator}', '{count}', '{percentage}'],
//                             [$rank, $indicatorName, $indicatorCount, $percentage],
//                             $templates[$endpoint]['item']
//                         ) . "\n";
                        
//                         // Store main indicator for recommendation
//                         if ($index === 0) {
//                             $mainIndicator = $indicatorName;
//                         }
//                     }
                    
//                     // Add recommendation if we have at least one indicator
//                     if (isset($mainIndicator)) {
//                         $formattedResponse .= "\n" . str_replace('{mainIndicator}', $mainIndicator, $templates[$endpoint]['recommendation']) . "\n\n";
//                     }
//                 } else {
//                     $formattedResponse = "## No Risk Indicator Data Available\n\nBased on your query parameters, we couldn't identify prevalent risk indicators that match the criteria.\n\n";
//                 }
                
//                 break;
                
//             case 'risk-indicator-linked-to-fatality':
//                 // Handle risk indicators linked to fatality
//                 if (isset($data['riskIndicatorsFatalities']) && is_array($data['riskIndicatorsFatalities'])) {
//                     $indicators = $data['riskIndicatorsFatalities'];
                    
//                     // Create title
//                     $formattedResponse = "## " . str_replace('{state}', $state, $templates[$endpoint]['title']) . "\n\n";
                    
//                     // Create intro
//                     $formattedResponse .= str_replace('{period}', $period, $templates[$endpoint]['intro']) . "\n\n";
                    
//                     // List the risk indicators
//                     foreach ($indicators as $index => $indicator) {
//                         $rank = $index + 1;
//                         $indicatorName = $indicator['riskindicators'] ?? 'Unknown';
//                         $deaths = $indicator['total_deaths'] ?? 0;
                        
//                         $formattedResponse .= str_replace(
//                             ['{rank}', '{indicator}', '{deaths}'],
//                             [$rank, $indicatorName, $deaths],
//                             $templates[$endpoint]['item']
//                         ) . "\n";
                        
//                         // Store top indicator for recommendation
//                         if ($index === 0) {
//                             $topIndicator = $indicatorName;
//                         }
//                     }
                    
//                     // Add recommendation if we have at least one indicator
//                     if (isset($topIndicator)) {
//                         $formattedResponse .= "\n" . str_replace('{topIndicator}', $topIndicator, $templates[$endpoint]['recommendation']) . "\n\n";
//                     }
//                 } else {
//                     $formattedResponse = "## No Fatality Data Available\n\nBased on your query parameters, we couldn't identify risk indicators linked to fatalities that match the criteria.\n\n";
//                 }
                
//                 break;
                
//             case 'risk-factor-linked-to-fatality':
//                 // Handle risk factors linked to fatality
//                 if (isset($data['riskFactorFatalities']) && is_array($data['riskFactorFatalities'])) {
//                     $factors = $data['riskFactorFatalities'];
                    
//                     // Create title
//                     $formattedResponse = "## " . str_replace('{state}', $state, $templates[$endpoint]['title']) . "\n\n";
                    
//                     // Create intro
//                     $formattedResponse .= str_replace('{period}', $period, $templates[$endpoint]['intro']) . "\n\n";
                    
//                     // List the risk factors
//                     foreach ($factors as $index => $factor) {
//                         $rank = $index + 1;
//                         $factorName = $factor['riskfactors'] ?? 'Unknown';
//                         $deaths = $factor['total_deaths'] ?? 0;
                        
//                         $formattedResponse .= str_replace(
//                             ['{rank}', '{factor}', '{deaths}'],
//                             [$rank, $factorName, $deaths],
//                             $templates[$endpoint]['item']
//                         ) . "\n";
                        
//                         // Store top factor for recommendation
//                         if ($index === 0) {
//                             $topFactor = $factorName;
//                         }
//                     }
                    
//                     // Add recommendation if we have at least one factor
//                     if (isset($topFactor)) {
//                         $formattedResponse .= "\n" . str_replace('{topFactor}', $topFactor, $templates[$endpoint]['recommendation']) . "\n\n";
//                     }
//                 } else {
//                     $formattedResponse = "## No Fatality Data Available\n\nBased on your query parameters, we couldn't identify risk factors linked to fatalities that match the criteria.\n\n";
//                 }
                
//                 break;
                
//             case 'neighbourhood-incidents':
//                 // Handle neighborhood incidents
//                 $neighbourhood = ucfirst($params['neighbourhood'] ?? 'the area');
//                 $state = ucfirst($params['state'] ?? 'the state');
//                 $days = $params['days'] ?? 30;
                
//                 // Create title
//                 $formattedResponse = "## " . str_replace(
//                     ['{neighbourhood}', '{state}'],
//                     [$neighbourhood, $state],
//                     $templates[$endpoint]['title']
//                 ) . "\n\n";
                
//                 if (empty($data)) {
//                     // Use empty template if no incidents
//                     $formattedResponse .= str_replace(
//                         ['{neighbourhood}', '{state}', '{days}'],
//                         [$neighbourhood, $state, $days],
//                         $templates[$endpoint]['empty']
//                     ) . "\n\n";
//                 } else {
//                     // Create intro
//                     $formattedResponse .= str_replace(
//                         ['{days}', '{neighbourhood}'],
//                         [$days, $neighbourhood],
//                         $templates[$endpoint]['intro']
//                     ) . "\n\n";
                    
//                     // Count risk factors for recommendation
//                     $riskFactorCounts = [];
                    
//                     // List the incidents
//                     foreach ($data as $incident) {
//                         $date = isset($incident['eventdate']) ? Carbon::parse($incident['eventdate'])->format('Y-m-d') : 'Unknown date';
//                         $caption = $incident['caption'] ?? 'Incident details not available';
//                         $riskFactor = $incident['riskfactors'] ?? 'Unknown risk factor';
//                         $riskIndicator = $incident['riskindicators'] ?? 'Unknown risk indicator';
//                         $victims = $incident['victim'] ?? 0;
//                         $casualties = $incident['Casualties_count'] ?? 0;
//                         $businessImpact = $incident['business_report'] ?? 'No business impact reported';
                        
//                         // Count risk factors for recommendation
//                         if (!isset($riskFactorCounts[$riskFactor])) {
//                             $riskFactorCounts[$riskFactor] = 0;
//                         }
//                         $riskFactorCounts[$riskFactor]++;
                        
//                         $formattedResponse .= str_replace(
//                             ['{date}', '{caption}', '{riskFactor}', '{riskIndicator}', '{victims}', '{casualties}', '{businessImpact}'],
//                             [$date, $caption, $riskFactor, $riskIndicator, $victims, $casualties, $businessImpact],
//                             $templates[$endpoint]['item']
//                         );
//                     }
                    
//                     // Find main risk factor for recommendation
//                     $mainRiskFactor = 'various security threats';
//                     $maxCount = 0;
//                     foreach ($riskFactorCounts as $factor => $count) {
//                         if ($count > $maxCount) {
//                             $maxCount = $count;
//                             $mainRiskFactor = $factor;
//                         }
//                     }
                    
//                     // Determine security level based on incident count
//                     $incidentCount = count($data);
//                     $securityLevel = $incidentCount > 10 ? 'Advanced' : ($incidentCount > 5 ? 'Enhanced' : 'Standard');
                    
//                     // Add recommendation
//                     $formattedResponse .= "\n" . str_replace(
//                         ['{neighbourhood}', '{incidentCount}', '{mainRiskFactor}', '{securityLevel}'],
//                         [$neighbourhood, $incidentCount, $mainRiskFactor, $securityLevel],
//                         $templates[$endpoint]['recommendation']
//                     ) . "\n\n";
//                 }
                
//                 break;
                
//             case 'primary-risk-factor-affecting-neighbourhood':
//                 // Handle primary risk factor affecting neighborhood
//                 $neighbourhood = ucfirst($params['neighbourhood'] ?? 'the area');
//                 $state = ucfirst($params['state'] ?? 'the state');
//                 $days = $params['days'] ?? 30;
                
//                 // Create title
//                 $formattedResponse = "## " . str_replace(
//                     ['{neighbourhood}', '{state}'],
//                     [$neighbourhood, $state],
//                     $templates[$endpoint]['title']
//                 ) . "\n\n";
                
//                 if (isset($data['riskFactor']) && $data['riskFactor'] !== 'No data available') {
//                     $riskFactor = $data['riskFactor'];
//                     $count = $data['occurrences'] ?? 0;
                    
//                     // Create content
//                     $formattedResponse .= str_replace(
//                         ['{days}', '{neighbourhood}', '{riskFactor}', '{count}'],
//                         [$days, $neighbourhood, $riskFactor, $count],
//                         $templates[$endpoint]['content']
//                     ) . "\n\n";
                    
//                     // Add recommendation
//                     $formattedResponse .= str_replace('{riskFactor}', $riskFactor, $templates[$endpoint]['recommendation']) . "\n\n";
//                 } else {
//                     // Use empty template if no data
//                     $formattedResponse .= str_replace(
//                         ['{neighbourhood}', '{state}', '{days}'],
//                         [$neighbourhood, $state, $days],
//                         $templates[$endpoint]['empty']
//                     ) . "\n\n";
//                 }
                
//                 break;
                
//             case 'primary-risk-indicator-affecting-neighbourhood':
//                 // Handle primary risk indicator affecting neighborhood
//                 $neighbourhood = ucfirst($params['neighbourhood'] ?? 'the area');
//                 $state = ucfirst($params['state'] ?? 'the state');
//                 $days = $params['days'] ?? 30;
                
//                 // Create title
//                 $formattedResponse = "## " . str_replace(
//                     ['{neighbourhood}', '{state}'],
//                     [$neighbourhood, $state],
//                     $templates[$endpoint]['title']
//                 ) . "\n\n";
                
//                 if (isset($data['riskFactor']) && $data['riskFactor'] !== 'No data available') {
//                     $riskIndicator = $data['riskFactor']; // Field name is 'riskFactor' but it contains the risk indicator
//                     $count = $data['occurrences'] ?? 0;
                    
//                     // Create content
//                     $formattedResponse .= str_replace(
//                         ['{days}', '{neighbourhood}', '{riskIndicator}', '{count}'],
//                         [$days, $neighbourhood, $riskIndicator, $count],
//                         $templates[$endpoint]['content']
//                     ) . "\n\n";
                    
//                     // Add recommendation
//                     $formattedResponse .= str_replace('{riskIndicator}', $riskIndicator, $templates[$endpoint]['recommendation']) . "\n\n";
//                 } else {
//                     // Use empty template if no data
//                     $formattedResponse .= str_replace(
//                         ['{neighbourhood}', '{state}', '{days}'],
//                         [$neighbourhood, $state, $days],
//                         $templates[$endpoint]['empty']
//                     ) . "\n\n";
//                 }
                
//                 break;
                
//             case 'highest-day-period-for-neighbourhood':
//                 // Handle highest day period for neighborhood
//                 $neighbourhood = ucfirst($params['neighbourhood'] ?? 'the area');
//                 $state = ucfirst($params['state'] ?? 'the state');
//                 $days = $params['days'] ?? 30;
                
//                 // Create title
//                 $formattedResponse = "## " . str_replace(
//                     ['{neighbourhood}', '{state}'],
//                     [$neighbourhood, $state],
//                     $templates[$endpoint]['title']
//                 ) . "\n\n";
                
//                 if (isset($data['dayPeriod']) && $data['dayPeriod'] !== 'No data available') {
//                     $period = $data['dayPeriod'];
//                     $count = $data['occurrences'] ?? 0;
                    
//                     // Create content
//                     $formattedResponse .= str_replace(
//                         ['{days}', '{neighbourhood}', '{period}', '{count}'],
//                         [$days, $neighbourhood, $period, $count],
//                         $templates[$endpoint]['content']
//                     ) . "\n\n";
                    
//                     // Add recommendation
//                     $formattedResponse .= str_replace('{period}', $period, $templates[$endpoint]['recommendation']) . "\n\n";
//                 } else {
//                     // Use empty template if no data
//                     $formattedResponse .= str_replace(
//                         ['{neighbourhood}', '{state}', '{days}'],
//                         [$neighbourhood, $state, $days],
//                         $templates[$endpoint]['empty']
//                     ) . "\n\n";
//                 }
                
//                 break;
                
//             case 'high-risk-neighbourhood':
//                 // Handle high-risk neighborhoods
//                 $state = ucfirst($params['state'] ?? 'the specified state');
//                 $days = $params['days'] ?? 30;
                
//                 // Create title
//                 $formattedResponse = "## " . str_replace('{state}', $state, $templates[$endpoint]['title']) . "\n\n";
                
//                 if (!empty($data)) {
//                     // Create intro
//                     $formattedResponse .= str_replace(
//                         ['{days}', '{state}'],
//                         [$days, $state],
//                         $templates[$endpoint]['intro']
//                     ) . "\n\n";
                    
//                     // List the high-risk neighborhoods
//                     foreach ($data as $index => $neighborhood) {
//                         $rank = $index + 1;
//                         $neighbourhoodName = $neighborhood['neighbourhood_name'] ?? 'Unknown';
//                         $count = $neighborhood['incident_count'] ?? 0;
                        
//                         $formattedResponse .= str_replace(
//                             ['{rank}', '{neighbourhood}', '{count}'],
//                             [$rank, $neighbourhoodName, $count],
//                             $templates[$endpoint]['item']
//                         ) . "\n";
//                     }
                    
//                     // Add recommendation
//                     $formattedResponse .= "\n" . $templates[$endpoint]['recommendation'] . "\n\n";
//                 } else {
//                     // Use empty template if no data
//                     $formattedResponse .= str_replace(
//                         ['{state}', '{days}'],
//                         [$state, $days],
//                         $templates[$endpoint]['empty']
//                     ) . "\n\n";
//                 }
                
//                 break;
                
//             case 'risk-index':
//                 // Handle risk index
//                 $state = $params['state'] ?? 'Nigeria';
                
//                 if (isset($data['indicatorName']) && isset($data['currentData'])) {
//                     $indicator = $data['indicatorName'] ?? 'Overall Risk';
//                     $score = 5; // Default midpoint score if not available
                    
//                     // Calculate risk score based on the data
//                     if (isset($data['currentData']['dataByState'])) {
//                         foreach ($data['currentData']['dataByState'] as $stateData) {
//                             if (strtolower($stateData['state']) === strtolower($state)) {
//                                 $score = $stateData['score'] ?? 5;
//                                 break;
//                             }
//                         }
//                     }
                    
//                     // Create title
//                     $formattedResponse = "## " . str_replace('{state}', $state, $templates[$endpoint]['title']) . "\n\n";
                    
//                     // Create content
//                     $formattedResponse .= str_replace(
//                         ['{state}', '{score}'],
//                         [ucfirst($state), $score],
//                         $templates[$endpoint]['content']
//                     ) . "\n\n";
                    
//                     // Add indicator-specific information if available
//                     if ($indicator !== 'Overall Risk') {
//                         $indicatorScore = $score; // Use same score if no specific indicator score
//                         $formattedResponse .= str_replace(
//                             ['{indicator}', '{indicatorScore}'],
//                             [$indicator, $indicatorScore],
//                             $templates[$endpoint]['factor']
//                         ) . "\n\n";
//                     }
                    
//                     // Determine risk level
//                     $level = 'moderate';
//                     if ($score <= 3) $level = 'lower';
//                     if ($score >= 7) $level = 'higher';
                    
//                     // Add comparison
//                     $formattedResponse .= str_replace(
//                         ['{state}', '{level}'],
//                         [ucfirst($state), $level],
//                         $templates[$endpoint]['comparison']
//                     ) . "\n\n";
                    
//                     // Determine security level for recommendation
//                     $securityLevel = 'standard';
//                     if ($score <= 3) $securityLevel = 'basic';
//                     if ($score > 3 && $score < 7) $securityLevel = 'moderate';
//                     if ($score >= 7) $securityLevel = 'enhanced';
                    
//                     // Add recommendation
//                     $formattedResponse .= str_replace(
//                         ['{securityLevel}', '{state}'],
//                         [$securityLevel, ucfirst($state)],
//                         $templates[$endpoint]['recommendation']
//                     ) . "\n\n";
//                 } else {
//                     $formattedResponse = "## Risk Index Information Not Available\n\nThe risk index data for your query is not available at this time.\n\n";
//                 }
                
//                 break;
                
//             default:
//                 // For endpoints without specific formatters, use Groq API to generate a response
//                 return $this->askGroqAPI($userQuery, $data, $apiUrl);
//         }
        
//         // Add the API URL reference and debug info if in debug mode
//         if ($debug) {
//             $formattedResponse .= "\n\n*Data source: Nigeria Risk Index API*";
//             $formattedResponse .= "\n\n*DEBUG INFO*\n";
//             $formattedResponse .= "- Endpoint: $endpoint\n";
//             $formattedResponse .= "- API URL: $apiUrl\n";
//             $formattedResponse .= "- Parameters: " . json_encode($params) . "\n";
//         } else {
//             $formattedResponse .= "\n\n*Data source: Nigeria Risk Index API*";
//         }
        
//         return response()->json(['response' => $formattedResponse]);
//     }
    
//     /**
//      * Handle empty API responses with helpful suggestions
//      */
//     private function handleEmptyApiResponse($endpoint, $params, $userQuery, $apiUrl)
//     {
//         // Prepare a helpful response for empty data
//         $state = isset($params['state']) ? ucfirst($params['state']) : 'the requested state';
//         $year = $params['year'] ?? 'the specified year';
//         $period = isset($params['days']) ? "the past {$params['days']} days" : $year;
//         $neighbourhood = isset($params['neighbourhood']) ? ucfirst($params['neighbourhood']) : 'the specified neighbourhood';
        
//         $response = "## No Data Available\n\n";
        
//         switch ($endpoint) {
//             case 'incident-count':
//                 $response .= "There are no recorded incidents in $state for $period.";
//                 break;
                
//             case 'top-state':
//             case 'low-state':
//                 $response .= "We don't have sufficient data to determine the highest/lowest risk state for $period.";
//                 break;
                
//             case 'top-five-state':
//             case 'lowest-five-state':
//                 $response .= "We don't have sufficient data to rank states by risk level for $period.";
//                 break;
                
//             case 'prevalent-risk-factor':
//             case 'prevalent-risk-indicator':
//                 $response .= "We don't have sufficient data to identify prevalent risk factors in $state for $period.";
//                 break;
                
//             case 'risk-indicator-linked-to-fatality':
//             case 'risk-factor-linked-to-fatality':
//                 $response .= "We don't have any fatality data for $state during $period.";
//                 break;
                
//             case 'neighbourhood-incidents':
//                 $response .= "No incidents have been reported in $neighbourhood, $state during $period.";
//                 break;
                
//             case 'primary-risk-factor-affecting-neighbourhood':
//             case 'primary-risk-indicator-affecting-neighbourhood':
//                 $response .= "We don't have sufficient data to identify the main security concerns in $neighbourhood, $state during $period.";
//                 break;
                
//             case 'highest-day-period-for-neighbourhood':
//                 $response .= "We don't have sufficient data to determine when incidents are most likely to occur in $neighbourhood, $state.";
//                 break;
                
//             case 'high-risk-neighbourhood':
//                 $response .= "We don't have sufficient data to identify high-risk neighborhoods in $state during $period.";
//                 break;
                
//             default:
//                 $response .= "We don't have data matching your query parameters at this time.";
//         }
        
//         $response .= "\n\n### Suggestions:\n\n";
//         $response .= "1. Try extending the time period (e.g., use an earlier year or more days)\n";
//         $response .= "2. Check if you've specified the correct location name\n";
//         $response .= "3. Try a broader query that might capture more data points\n\n";
        
//         if ($endpoint === 'risk-index' || $endpoint === 'incident-count') {
//             $response .= "For general security advice about $state, you can ask: 'What are the security considerations for $state?'";
//         }
        
//         return response()->json(['response' => $response]);
//     }
    
//     /**
//      * Handle API errors with appropriate messaging
//      */
//     private function handleApiError($error, $userQuery, $apiUrl)
//     {
//         Log::error("API error: " . $error->getMessage() . " for URL: $apiUrl");
        
//         $errorMessage = "## Service Temporarily Unavailable\n\n";
//         $errorMessage .= "I'm sorry, but I encountered a technical issue while retrieving the requested information. ";
        
//         // Provide more context based on error type
//         $statusCode = $error->getCode();
        
//         if ($statusCode == 404) {
//             $errorMessage .= "The specific data you requested couldn't be found. This might be because:\n\n";
//             $errorMessage .= "1. The location or time period you specified doesn't have recorded data\n";
//             $errorMessage .= "2. There might be a typo in your query\n\n";
//             $errorMessage .= "Please try rephrasing your question or asking about a different location or time period.";
//         } elseif ($statusCode >= 500) {
//             $errorMessage .= "Our data service is currently experiencing technical difficulties. Please try again in a few moments.";
//         } else {
//             $errorMessage .= "Please try rephrasing your question or asking something different.";
//         }
        
//         // In debug mode, include the error message
//         if (env('APP_DEBUG', false)) {
//             $errorMessage .= "\n\n*DEBUG: Error message: " . $error->getMessage() . "*";
//         }
        
//         return response()->json(['response' => $errorMessage]);
//     }
    
//     /**
//      * Enhanced Groq API integration with better context and prompt engineering
//      */
//     private function askGroqAPI($userQuery, $data = null, $apiUrl = null, $finalAttempt = false, $additionalContext = null)
//     {
//         $apiKey = env('GROQ_API_KEY');
//         if (!$apiKey) {
//             Log::error("GROQ_API_KEY is not set in the environment.");
//             return response()->json([
//                 'response' => "I'm sorry, but I'm having trouble accessing my language processing capabilities right now. Please try again later."
//             ]);
//         }
        
//         $client = new Client();

//         // System message with enhanced instructions for the Groq model
//         $systemMessage = "You are a security intelligence analyst for Nigeria Risk Index, a platform that provides risk assessment and security intelligence for Nigeria. Follow these guidelines:\n\n"
//             . "1. Provide clear, concise, and actionable security intelligence\n"
//             . "2. Format responses with markdown headings and bullet points for readability\n"
//             . "3. Address sensitive topics professionally without disclaimers\n"
//             . "4. Maintain a helpful but authoritative tone as a security expert\n"
//             . "5. When discussing risk data, explain implications and provide context\n"
//             . "6. For location-specific queries, focus on factual security assessment\n"
//             . "7. When lacking specific data, provide general security advice for Nigeria\n"
//             . "8. Include suggestions for risk mitigation when appropriate\n"
//             . "9. Avoid mentioning the API or technical details in your response\n\n"
//             . "Knowledge base: " . json_encode($this->knowledgeBase);

//         // Build a more structured user message
//         $userContent = "User query: $userQuery\n\n";
        
//         if ($data) {
//             $userContent .= "API data: " . json_encode($data) . "\n\n";
//         }
        
//         if ($apiUrl) {
//             $userContent .= "API endpoint: $apiUrl\n\n";
//         }
        
//         if ($additionalContext) {
//             $userContent .= "Additional context: $additionalContext\n\n";
//         }
        
//         if ($finalAttempt) {
//             $userContent .= "This is a final attempt. If no specific data is available, provide general security advice for Nigeria based on typical patterns, focusing on the user's query topics.\n\n";
//         }

//         $messages = [
//             ['role' => 'system', 'content' => $systemMessage],
//             ['role' => 'user', 'content' => $userContent]
//         ];

//         try {
//             Log::info("Sending request to Groq API");
            
//             $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
//                 'headers' => ['Authorization' => "Bearer $apiKey", 'Content-Type' => 'application/json'],
//                 'json' => [
//                     'model' => 'llama3-8b-8192',
//                     'messages' => $messages,
//                     'temperature' => 0.3,
//                     'max_tokens' => 600  // Increased token limit for more detailed responses
//                 ],
//                 'timeout' => 30  // Increase timeout to handle potential delays
//             ]);

//             $responseData = json_decode($response->getBody(), true);
//             if (!is_array($responseData) || !isset($responseData['choices'][0]['message']['content'])) {
//                 Log::error("Invalid Groq API response: " . $response->getBody());
//                 $answer = "I apologize, but I couldn't process that request due to a technical issue. Please try asking your question in a different way.";
//             } else {
//                 $answer = $responseData['choices'][0]['message']['content'];
                
//                 // Post-process the response to improve formatting
//                 $answer = $this->postProcessGroqResponse($answer);
//             }

//             return response()->json(['response' => $answer]);

//         } catch (RequestException $e) {
//             Log::error("Groq API request failed: " . $e->getMessage());
//             return response()->json([
//                 'response' => "I'm sorry, but our intelligence system is temporarily unavailable. Please try again in a few moments."
//             ]);
//         }
//     }
    
//     /**
//      * Post-process Groq API response to improve formatting and readability
//      */
//     private function postProcessGroqResponse($response)
//     {
//         // Remove API URL references that might have been included
//         $response = preg_replace('/\[API URL:.*?\]/', '', $response);
        
//         // Ensure proper markdown formatting
//         $response = preg_replace('/(?<!#)# (?!#)/', '## ', $response);  // Convert single # to ##
        
//         // Fix common formatting issues
//         $response = str_replace('\\n', "\n", $response);
//         $response = preg_replace('/\n{3,}/', "\n\n", $response);
        
//         // Add emphasis to key statistical information
//         $response = preg_replace('/(\d+(?:\.\d+)?\s*%)/', '**$1**', $response);
//         $response = preg_replace('/(\d+\s+incidents)/', '**$1**', $response);
        
//         // Improve risk factor formatting
//         foreach ($this->validRiskFactors as $factor) {
//             $response = preg_replace('/\b' . preg_quote($factor, '/') . '\b(?!\*\*)/', "**$factor**", $response);
//         }
        
//         return $response;
//     }
// }
    
