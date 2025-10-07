<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatBotController extends Controller
{
    public function index()
    {
        // Sample conversation history
        $conversations = [
            [
                'id' => 1,
                'title' => 'Welcome to Lodge Ease AI Assistant!',
                'timestamp' => '2025-08-29 09:00:00',
                'messages' => [
                    [
                        'type' => 'assistant',
                        'content' => 'Welcome to Lodge Ease AI Assistant!',
                        'timestamp' => '2025-08-29 09:00:00'
                    ]
                ]
            ]
        ];

        // Predefined question categories
        $questionCategories = [
            'Occupancy' => [
                'What is our occupancy trend?',
                'Provide an occupancy forecast'
            ],
            'Sales' => [
                'How has our sales changed in the last quarter?',
                'What is our total sales this month?'
            ],
            'Bookings' => [
                'What are our booking patterns this month?',
                'When is our peak booking season?'
            ],
            'Performance' => [
                'What is our current occupancy rate?',
                'What is our total sales this month?'
            ]
        ];

        return view('admin.chatbot.chatbot', compact('conversations', 'questionCategories'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = $request->input('message');
        
        // Simple AI response logic (you can integrate with OpenAI or other AI services)
        $response = $this->generateAIResponse($message);

        return response()->json([
            'success' => true,
            'message' => $message,
            'response' => $response,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    public function newConversation(Request $request)
    {
        // Logic to start a new conversation
        return response()->json([
            'success' => true,
            'conversation_id' => rand(1000, 9999),
            'message' => 'New conversation started!'
        ]);
    }

    public function getChatHistory(Request $request)
    {
        // Sample chat history
        $history = [
            [
                'id' => 1,
                'title' => 'Occupancy Analysis',
                'last_message' => 'What is our current occupancy rate?',
                'timestamp' => '2025-08-29 14:30:00'
            ],
            [
                'id' => 2,
                'title' => 'Sales Performance',
                'last_message' => 'How has our sales changed in the last quarter?',
                'timestamp' => '2025-08-29 13:15:00'
            ],
            [
                'id' => 3,
                'title' => 'Booking Trends',
                'last_message' => 'What are our booking patterns this month?',
                'timestamp' => '2025-08-29 12:00:00'
            ]
        ];

        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    private function generateAIResponse($message)
    {
        // Simple response logic - you can integrate with actual AI services
        $responses = [
            'occupancy' => [
                'Based on current data, your occupancy rate is at 78% this month, which is 5% higher than last month.',
                'Your occupancy trend shows steady growth with peak seasons in summer and winter months.',
                'Current occupancy forecast suggests 85% for the next month based on booking patterns.'
            ],
            'sales' => [
                'Your sales have increased by 12% compared to last quarter, totaling $145,000 this month.',
                'Revenue performance shows strong growth in premium room bookings and extended stays.',
                'Total sales this month: $145,230 with average daily rate of $178.'
            ],
            'booking' => [
                'Booking patterns show highest demand on weekends and holiday periods.',
                'Peak booking season typically occurs in June-August and December-January.',
                'Current booking trends indicate 65% advance bookings for the next 30 days.'
            ],
            'performance' => [
                'Overall business performance is strong with 78% occupancy and $145k monthly revenue.',
                'Key performance indicators show positive trends across all metrics.',
                'Your lodge is performing 15% better than industry average in your region.'
            ]
        ];

        // Simple keyword matching
        $message = strtolower($message);
        
        if (str_contains($message, 'occupancy')) {
            return $responses['occupancy'][array_rand($responses['occupancy'])];
        } elseif (str_contains($message, 'sales') || str_contains($message, 'revenue')) {
            return $responses['sales'][array_rand($responses['sales'])];
        } elseif (str_contains($message, 'booking')) {
            return $responses['booking'][array_rand($responses['booking'])];
        } elseif (str_contains($message, 'performance')) {
            return $responses['performance'][array_rand($responses['performance'])];
        } else {
            return "I can help you analyze your hotel data and provide insights about occupancy trends, sales performance, booking patterns, and overall business performance. What specific information would you like to know?";
        }
    }
}
