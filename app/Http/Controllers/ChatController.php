<?php





// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use GuzzleHttp\Client;
// use GuzzleHttp\Exception\RequestException;

// class ChatController extends Controller
// {
//     private $knowledgeBase = [
//         // GREETING RESPONSES
//         "hello" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "hi" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "hey" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "good morning" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "good afternoon" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "good evening" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",

//         // NRI Knowledge Base
//         "what is nigeria risk index?" => "The Nigeria Risk Index (NRI) is a security intelligence platform assessing and visualizing risks across Nigeria’s 36 states.",
//         "what does nri do?" => "NRI provides security insights, risk data, and advisory services to help businesses and individuals understand security conditions in Nigeria.",
//         "what is the interactive risk map?" => "The Interactive Risk Map provides real-time insights into security threats across different regions in Nigeria.",
//         "what is the risk and crime database?" => "The Risk and Crime Database includes statistics on crimes such as kidnapping, terrorism, and general crime rates across Nigeria.",
//         "how can i compare security risks between states?" => "You can use the State Comparison Tool on the NRI platform to analyze strengths and risks across Nigerian states.",
//         "what are protection advisory services?" => "Protection Advisory Services include security consulting, background screening, polygraph tests, computer forensics, and security training.",
//         "what is location intelligence?" => "Location Intelligence provides region-specific security insights, crime trends, and risk factors across Nigerian states.",
//         "what is business risk insights?" => "Business Risk Insights evaluate risks like political stability, security threats, and economic conditions for businesses in Nigeria.",
//         "how often is nri updated?" => "NRI provides daily security updates, weekly trend analyses, periodic risk landscape reports, and real-time risk alerts.",
//         "how can i get real-time risk alerts?" => "Real-time risk alerts are available on the NRI platform. Users can enable notifications for instant security updates.",
//         "how can i contact nri for support?" => "You can contact NRI via the 'Contact Us' page on the platform or by emailing info@nigeriariskindex.com."
//     ];

//     private $validYears = ["2018", "2019", "2020", "2021", "2022", "2023", "2024"];
//     private $validRiskFactors = ["Personal Threats", "Political Threats", "Property Threats", "Safety", "Violent Threats"];
//     private $riskIndicators = [
//         "Violent Threats" => ["Terrorism", "Kidnapping", "Armed Robbery", "Homicide", "Insurgency"],
//         "Safety" => ["Natural_Disasters", "Fire Outbreak", "Epidemic", "Unsafe Route/Violent Attacks"],
//         "Property Threats" => ["Burglary", "Theft", "Fraud", "Arson", "Cyber Crime"],
//         "Political Threats" => ["Political Protest", "Political Corruption", "Electoral Violence"],
//         "Personal Threats" => ["Assaults", "Rape", "Firearms Trafficking", "Human Trafficking"]
//     ];
    
//     public function chat(Request $request)
//     {
//         $userQuery = strtolower(trim($request->input('message')));

//         // Step 1: Check if it's a predefined question
//         foreach ($this->knowledgeBase as $question => $answer) {
//             if (strpos($userQuery, $question) !== false) {
//                 return response()->json(['response' => $answer]);
//             }
//         }

//         // Step 2: Check if it's a risk-related query
//         list($endpoint, $params) = $this->buildEndpoint($userQuery);
//         if ($endpoint) {
//             return $this->fetchRiskData($endpoint, $params, $userQuery);
//         }

//         // Step 3: If no match, call Groq API with context
//         return $this->askGroqAPI($userQuery);
//     }

//     private function buildEndpoint($query)
//     {
//         $endpoints = ["top-five-state", "lowest-five-state", "low-state", "top-state", "incident-count"];
//         $selectedEndpoint = null;

//         foreach ($endpoints as $ep) {
//             if (strpos($query, str_replace("-", " ", $ep)) !== false) {
//                 $selectedEndpoint = $ep;
//                 break;
//             }
//         }

//         if (!$selectedEndpoint) {
//             return [null, "Please include one of the valid endpoints: top-five-state, lowest-five-state, low-state, top-state, or incident-count."];
//         }

//         $params = [];
//         foreach ($this->validYears as $year) {
//             if (strpos($query, $year) !== false) {
//                 $params["year"] = $year;
//                 break;
//             }
//         }

//         foreach ($this->validRiskFactors as $riskFactor) {
//             if (strpos($query, strtolower($riskFactor)) !== false) {
//                 $params["riskfactor"] = $riskFactor;
//                 break;
//             }
//         }

//         foreach ($this->riskIndicators as $factor => $indicators) {
//             foreach ($indicators as $indicator) {
//                 if (strpos($query, strtolower($indicator)) !== false) {
//                     $params["riskindicator"] = $indicator;
//                     break;
//                 }
//             }
//         }

//         if ($selectedEndpoint === "incident-count" && !isset($params["state"])) {
//             return [null, "The 'incident-count' endpoint requires a state."];
//         }

//         return [$selectedEndpoint, $params];
//     }

//     private function fetchRiskData($endpoint, $params, $userQuery)
//     {
//         $apiUrl = "https://nigeriariskindex.com/api/$endpoint";

//         try {
//             $client = new Client();
//             $response = $client->get($apiUrl, ['query' => $params]);
//             $data = json_decode($response->getBody(), true);

//             return $this->askGroqAPI($userQuery, $data);
//         } catch (RequestException $e) {
//             return response()->json([
//                 'error' => 'Failed to fetch risk data: ' . $e->getMessage(),
//             ], 500);
//         }
//     }

//     private function askGroqAPI($userQuery, $data = null)
//     {
//         $apiKey = env('GROQ_API_KEY');
//         $url = "https://api.groq.com/openai/v1/chat/completions";

//         $client = new Client();

//         try {
//             $response = $client->post($url, [
//                 'headers' => [
//                     'Authorization' => "Bearer {$apiKey}",
//                     'Content-Type' => 'application/json',
//                 ],
//                 'json' => [
//                     'model' => 'llama3-8b-8192',
//                     'messages' => [
//                         ['role' => 'system', 'content' => 'You are an AI assistant focused on Nigeria Risk Index.'],
//                         ['role' => 'user', 'content' => "User Query: '{$userQuery}'. Here is the related data: " . json_encode($data)],
//                     ],
//                     'temperature' => 0.3,
//                     'max_tokens' => 200,
//                 ],
//             ]);

//             $data = json_decode($response->getBody(), true);
//             return response()->json([
//                 'response' => $data['choices'][0]['message']['content'] ?? "Sorry, I don't have that information.",
//             ]);
//         } catch (RequestException $e) {
//             return response()->json([
//                 'error' => 'Failed to connect to Groq API: ' . $e->getMessage(),
//             ], 500);
//         }
//     }
// }









// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use GuzzleHttp\Client;
// use GuzzleHttp\Exception\RequestException;

// class ChatController extends Controller
// {
//     private $knowledgeBase = [
//         "hello" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "hi" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "good morning" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "what is nigeria risk index?" => "The Nigeria Risk Index (NRI) is a security intelligence platform assessing and visualizing risks across Nigeria’s 36 states.",
//         "what is the interactive risk map?" => "The Interactive Risk Map provides real-time insights into security threats across different regions in Nigeria.",
//         "how often is nri updated?" => "NRI provides daily security updates, weekly trend analyses, and real-time risk alerts.",
//         "how can i contact nri for support?" => "You can contact NRI via the 'Contact Us' page or by emailing info@riskcontrolnigeria.com."
//     ];

//     private $validYears = ["2018", "2019", "2020", "2021", "2022", "2023", "2024"];
//     private $validStates = [
//         "abia", "adamawa", "akwa ibom", "anambra", "bauchi", "bayelsa", "benue", "borno",
//         "cross river", "delta", "ebonyi", "edo", "ekiti", "enugu", "gombe", "imo", "jigawa",
//         "kaduna", "kano", "katsina", "kebbi", "kogi", "kwara", "lagos", "nasarawa", "niger",
//         "ogun", "ondo", "osun", "oyo", "plateau", "rivers", "sokoto", "taraba", "yobe", "zamfara"
//     ];
//     private $validRiskFactors = ["Personal Threats", "Political Threats", "Property Threats", "Safety", "Violent Threats"];
//     private $riskIndicators = [
//         "Violent Threats" => ["Terrorism", "Kidnapping", "Armed Robbery", "Homicide", "Insurgency"],
//         "Safety" => ["Natural Disasters", "Fire Outbreak", "Epidemic", "Unsafe Route", "Transportation Accident"],
//         "Property Threats" => ["Burglary", "Theft", "Fraud", "Arson", "Cyber Crime"],
//         "Political Threats" => ["Political Protest", "Political Corruption", "Electoral Violence"],
//         "Personal Threats" => ["Assaults", "Rape", "Firearms Trafficking", "Human Trafficking"]
//     ];

//     public function chat(Request $request)
//     {
//         $userQuery = strtolower(trim($request->input('message')));

//         // Step 1: Check Knowledge Base
//         foreach ($this->knowledgeBase as $question => $answer) {
//             if (strpos($userQuery, strtolower($question)) !== false) {
//                 return response()->json(['response' => $answer]);
//             }
//         }

//         // Step 2: Validate Year
//         private function extractYear($query)
//         {
//             $matches = [];
//             preg_match('/\b(20\d{2})\b/', $query, $matches);
//             return $matches[1] ?? null;
//         }
//         if ($year && !in_array($year, $this->validYears)) {
//             return response()->json([
//                 'response' => "Please include a year within the range 2018 to 2024."
//             ]);
//         }

//         // Step 3: Handle Incident Count Queries
//         if (strpos($userQuery, "incident count") !== false) {
//             return $this->handleIncidentCount($userQuery, $year);
//         }

//         // Step 4: If no match, call Groq API with context
//         return $this->askGroqAPI($userQuery);
//     }

//     private function handleIncidentCount($query, $year)
//     {
//         $state = $this->extractState($query);
//         if (!$state) {
//             return response()->json(['response' => "The 'incident count' endpoint requires a valid state. Please specify a state in your query."]);
//         }

//         $params = ["state" => $state];
//         if ($year) {
//             $params["year"] = $year;
//         }

//         if ($riskFactor = $this->extractRiskFactor($query)) {
//             $params["riskfactor"] = $riskFactor;
//         }
//         if ($riskIndicator = $this->extractRiskIndicator($query)) {
//             $params["riskindicator"] = $riskIndicator;
//         }

//         return $this->fetchRiskData("incident-count", $params, $query);
//     }

//     private function fetchRiskData($endpoint, $params, $userQuery)
//     {
//         $apiUrl = "https://nigeriariskindex.com/api/$endpoint?" . http_build_query($params);

//         try {
//             $client = new Client();
//             $response = $client->get($apiUrl);
//             $data = json_decode($response->getBody(), true);

//             if (empty($data)) {
//                 return response()->json([
//                     'response' => "Sorry, but I don't know the answer to your question.\n\nPlease send an email to info@riskcontrolnigeria.com and someone from our team can answer it for you."
//                 ]);
//             }

//             return $this->askGroqAPI($userQuery, $data, $apiUrl);
//         } catch (RequestException $e) {
//             return response()->json([
//                 'error' => 'Failed to fetch risk data: ' . $e->getMessage(),
//             ], 500);
//         }
//     }

//     private function askGroqAPI($userQuery, $data = null, $apiUrl = null)
//     {
//         $apiKey = env('GROQ_API_KEY');
//         $url = "https://api.groq.com/openai/v1/chat/completions";

//         $client = new Client();

//         try {
//             $systemPrompt = "You are an AI assistant for the Nigeria Risk Index. Answer only based on the provided data. If the data is not available, respond with:\n'Sorry, but I don't know the answer to your question.\n\nPlease send an email to info@riskcontrolnigeria.com and someone from our team can answer it for you.'\n\nDo NOT redirect the user to any other platform or website.";

//             $response = $client->post($url, [
//                 'headers' => [
//                     'Authorization' => "Bearer {$apiKey}",
//                     'Content-Type' => 'application/json',
//                 ],
//                 'json' => [
//                     'model' => 'llama3-8b-8192',
//                     'messages' => [
//                         ['role' => 'system', 'content' => $systemPrompt],
//                         ['role' => 'user', 'content' => "User Query: '{$userQuery}'.\nAPI Used: {$apiUrl}\nHere is the related data: " . json_encode($data)],
//                     ],
//                     'temperature' => 0.3,
//                     'max_tokens' => 200,
//                 ],
//             ]);

//             $data = json_decode($response->getBody(), true);
//             return response()->json([
//                 'response' => $data['choices'][0]['message']['content'] ?? "Sorry, I don't have that information.",
//             ]);
//         } catch (RequestException $e) {
//             return response()->json([
//                 'error' => 'Failed to connect to Groq API: ' . $e->getMessage(),
//             ], 500);
//         }
//     }
// }


//BELOW IS WORKING PERFECTLY
// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use GuzzleHttp\Client;
// use GuzzleHttp\Exception\RequestException;

// class ChatController extends Controller
// {
//     // Existing knowledge base and configuration arrays remain the same
    
//         private $knowledgeBase = [
//         "hello" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "hi" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "good morning" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
//         "what is nigeria risk index?" => "The Nigeria Risk Index (NRI) is a security intelligence platform assessing and visualizing risks across Nigeria’s 36 states.",
//         "what is the interactive risk map?" => "The Interactive Risk Map provides real-time insights into security threats across different regions in Nigeria.",
//         "how often is nri updated?" => "NRI provides daily security updates, weekly trend analyses, and real-time risk alerts.",
//         "how can i contact nri for support?" => "You can contact NRI via the 'Contact Us' page or by emailing info@riskcontrolnigeria.com."
//     ];

//     private $validYears = ["2018", "2019", "2020", "2021", "2022", "2023", "2024"];
//     private $validStates = [
//         "abia", "adamawa", "akwa ibom", "anambra", "bauchi", "bayelsa", "benue", "borno",
//         "cross river", "delta", "ebonyi", "edo", "ekiti", "enugu", "gombe", "imo", "jigawa",
//         "kaduna", "kano", "katsina", "kebbi", "kogi", "kwara", "lagos", "nasarawa", "niger",
//         "ogun", "ondo", "osun", "oyo", "plateau", "rivers", "sokoto", "taraba", "yobe", "zamfara"
//     ];
//     private $validRiskFactors = ["Personal Threats", "Political Threats", "Property Threats", "Safety", "Violent Threats"];
//     private $riskIndicators = [
//         "Violent Threats" => ["Terrorism", "Kidnapping", "Armed Robbery", "Homicide", "Insurgency"],
//         "Safety" => ["Natural Disasters", "Fire Outbreak", "Epidemic", "Unsafe Route", "Transportation Accident"],
//         "Property Threats" => ["Burglary", "Theft", "Fraud", "Arson", "Cyber Crime"],
//         "Political Threats" => ["Political Protest", "Political Corruption", "Electoral Violence"],
//         "Personal Threats" => ["Assaults", "Rape", "Firearms Trafficking", "Human Trafficking"]
//     ];
    


//     public function chat(Request $request)
//     {
//         // Existing chat method remains the same
//         $userQuery = strtolower(trim($request->input('message')));

//         foreach ($this->knowledgeBase as $question => $answer) {
//             if (strpos($userQuery, $question) !== false) {
//                 return response()->json(['response' => $answer]);
//             }
//         }

//         list($endpoint, $params) = $this->buildEndpoint($userQuery);
//         if ($endpoint) {
//             return $this->fetchRiskData($endpoint, $params, $userQuery);
//         }

//         if (is_string($params)) {
//             return response()->json(['response' => $params]);
//         }

//         return $this->askGroqAPI($userQuery);
//     }

//     private function buildEndpoint($query)
//     {
//         $endpoints = ["top-five-state", "lowest-five-state", "low-state", "top-state", "incident-count"];
//         $selectedEndpoint = null;

//         foreach ($endpoints as $ep) {
//             if (strpos($query, str_replace("-", " ", $ep)) !== false) {
//                 $selectedEndpoint = $ep;
//                 break;
//             }
//         }

//         if (!$selectedEndpoint) return [null, null];

//         $params = [];
        
//         // Extract state
//         foreach ($this->validStates as $state) {
//             if (stripos($query, $state) !== false) {
//                 $params['state'] = $state;
//                 break;
//             }
//         }

//         // Mandatory state check for incident-count
//         if ($selectedEndpoint === "incident-count" && !isset($params['state'])) {
//             return [null, "Please specify a state to get incident count details (e.g., 'incident count in Lagos')."];
//         }

//         // Existing parameter extraction for year/risk factors/indicators
//         foreach ($this->validYears as $year) {
//             if (strpos($query, $year) !== false) {
//                 $params["year"] = $year;
//                 break;
//             }
//         }

//         foreach ($this->validRiskFactors as $riskFactor) {
//             if (strpos($query, strtolower($riskFactor)) !== false) {
//                 $params["riskfactor"] = $riskFactor;
//                 break;
//             }
//         }

//         foreach ($this->riskIndicators as $factor => $indicators) {
//             foreach ($indicators as $indicator) {
//                 $cleanIndicator = str_replace(['_', '/'], ' ', $indicator);
//                 if (strpos($query, strtolower($cleanIndicator)) !== false) {
//                     $params["riskindicator"] = $indicator;
//                     break 2;
//                 }
//             }
//         }

//         return [$selectedEndpoint, $params];
//     }

//     private function fetchRiskData($endpoint, $params, $userQuery)
//     {
//         $apiUrl = "https://nigeriariskindex.com/api/$endpoint";
//         $queryString = http_build_query($params);
//         $fullUrl = "$apiUrl?$queryString";

//         try {
//             $client = new Client();
//             $response = $client->get($apiUrl, ['query' => $params]);
//             $data = json_decode($response->getBody(), true);

//             return $this->askGroqAPI($userQuery, $data, $fullUrl);
//         } catch (RequestException $e) {
//             return response()->json(['error' => 'Failed to fetch data: ' . $e->getMessage()], 500);
//         }
//     }

//     private function askGroqAPI($userQuery, $data = null, $apiUrl = null)
//     {
//         $apiKey = env('GROQ_API_KEY');
//         $client = new Client();

//         $messages = [
//             [
//                 'role' => 'system',
//                 'content' => "You are a security analyst for Nigeria Risk Index. Always include the API URL used and explain parameters. Format: [Response] [API URL: {url}]"
//             ],
//             [
//                 'role' => 'user',
//                 'content' => "Query: $userQuery" . ($apiUrl ? "\nAPI URL: $apiUrl" : "") . ($data ? "\nData: " . json_encode($data) : "")
//             ]
//         ];

//         try {
//             $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
//                 'headers' => [
//                     'Authorization' => "Bearer $apiKey",
//                     'Content-Type' => 'application/json',
//                 ],
//                 'json' => [
//                     'model' => 'llama3-8b-8192',
//                     'messages' => $messages,
//                     'temperature' => 0.3,
//                     'max_tokens' => 300
//                 ]
//             ]);

//             $responseData = json_decode($response->getBody(), true);
//             $answer = $responseData['choices'][0]['message']['content'] ?? "Sorry, I couldn't process that request.";

//             // Ensure URL is always included
//             if ($apiUrl && strpos($answer, $apiUrl) === false) {
//                 $answer .= "\n[API URL: $apiUrl]";
//             }

//             return response()->json(['response' => $answer]);

//         } catch (RequestException $e) {
//             return response()->json(['error' => 'Groq API error: ' . $e->getMessage()], 500);
//         }
//     }
// }


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    // Knowledge Base and Configuration
    private $knowledgeBase = [
                "hello" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
                "hi" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
                "good morning" => "Hey, I'm your Digital NRI BOT. HOW CAN I HELP YOU?",
                "what is nigeria risk index?" => "The Nigeria Risk Index (NRI) is a security intelligence platform assessing and visualizing risks across Nigeria’s 36 states.",
                "what is the interactive risk map?" => "The Interactive Risk Map provides real-time insights into security threats across different regions in Nigeria.",
                "how often is nri updated?" => "NRI provides daily security updates, weekly trend analyses, and real-time risk alerts.",
                "how can i contact nri for support?" => "You can contact NRI via the 'Contact Us' page or by emailing info@riskcontrolnigeria.com."
            ];
    
    private $validYears = ["2018", "2019", "2020", "2021", "2022", "2023", "2024","2025"];
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
        $endpoints = ["top-five-state", "lowest-five-state", "low-state", "top-state", "incident-count"];
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

    private function buildEndpoint($query)
    {
        $endpoints = ["top-five-state", "lowest-five-state", "low-state", "top-state", "incident-count"];
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

        // State detection
        foreach ($this->validStates as $state) {
            if (stripos($query, $state) !== false) {
                $params['state'] = $state;
                break;
            }
        }

        // Year detection
        foreach ($this->validYears as $year) {
            if (strpos($query, $year) !== false) {
                $params["year"] = $year;
                break;
            }
        }

        // Risk factor detection
        foreach ($this->validRiskFactors as $factor) {
            if (strpos($query, strtolower($factor)) !== false) {
                $params["riskfactor"] = $factor;
                break;
            }
        }

        // Risk indicator detection (including rape)
        foreach ($this->riskIndicators as $category => $indicators) {
            foreach ($indicators as $indicator) {
                $cleanIndicator = str_replace(['_', '/'], ' ', $indicator);
                if (strpos($query, strtolower($cleanIndicator)) ){
                    $params["riskindicator"] = $indicator;
                    break 2;
                }
            }
        }

        // Validate incident-count requirements
        if ($selectedEndpoint === "incident-count") {
            if (!isset($params['state'])) {
                $error = "Please specify a state for incident count queries (e.g., 'incident count in Lagos').";
            }
        }

        return [$selectedEndpoint, $params, $error];
    }

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

    private function simulateTypingDelay()
    {
        // Simulate 2-3 second delay for "bot typing" effect
        sleep(rand(2, 3));
    }
}