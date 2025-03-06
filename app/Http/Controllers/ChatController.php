<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $userQuery = $request->input('message');
        $apiKey = env('GROQ_API_KEY');  // Make sure your .env has the correct key
        $url = "https://api.groq.com/openai/v1/chat/completions"; // ✅ Corrected URL

        $client = new Client();

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama3-8b-8192',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful AI assistant.'],
                        ['role' => 'user', 'content' => $userQuery],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 150,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return response()->json([
                'response' => $data['choices'][0]['message']['content'] ?? 'No response from AI',
            ]);
        } catch (RequestException $e) {
            return response()->json([
                'error' => 'Failed to connect to Groq API: ' . $e->getMessage(),
            ], 500);
        }
    }
}
