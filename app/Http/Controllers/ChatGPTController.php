<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatGPTController extends Controller
{
    public function askGPT(Request $request)
    {
        // پیام درخواست کاربر (prompt)
        $prompt = $request->input('prompt', 'What is artificial intelligence?');

        try {
            // ارسال درخواست به OpenAI با استفاده از GPT-4 و endpoint chat/completions
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->timeout(120) // تنظیم زمان انتظار به 120 ثانیه
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',  // استفاده از gpt-4
                'messages' => [
                    [
                        'role' => 'user',  // پیام کاربر
                        'content' => $prompt,
                    ]
                ],
                'max_tokens' => 1000,  // حداکثر تعداد توکن‌ها
                'temperature' => 0.4,  // سطح خلاقیت
            ]);

            // بررسی پاسخ از API OpenAI
            if ($response->successful()) {
                $data = $response->json();

                // برگرداندن پاسخ به کاربر
                return response()->json([
                    'prompt' => $prompt,
                    'response' => $data['choices'][0]['message']['content'] ?? 'No response from GPT',
                ]);
            } else {
                return response()->json(['error' => 'Failed to communicate with OpenAI API'], 500);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // مدیریت خطای تایم‌اوت یا مشکلات ارتباطی
            return response()->json(['error' => 'Request timed out or another connection error occurred'], 500);
        }
    }

    public function askGPTWithImage(Request $request)
    {
        // پیام درخواست کاربر (prompt) و URL تصویر
        $prompt = $request->input('prompt', 'Describe the image');
        $imageUrl = $request->input('image_url', 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Gfp-wisconsin-madison-the-nature-boardwalk.jpg/2560px-Gfp-wisconsin-madison-the-nature-boardwalk.jpg');

        try {
            // ارسال درخواست به OpenAI با استفاده از GPT و endpoint chat/completions
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->timeout(120) // تنظیم زمان انتظار به 120 ثانیه
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4-turbo',  // استفاده از gpt-4
                'messages' => [
                    [
                        'role' => 'user',  // پیام کاربر
                        'content' => json_encode([
                            'type' => 'text',
                            'text' => $prompt
                        ]),
                    ],
                    [
                        'role' => 'user',  // پیام کاربر
                        'content' => json_encode([
                            'type' => 'image_url',
                            'image_url' => ['url' => $imageUrl]
                        ]),
                    ],
                ],
                'max_tokens' => 1000,  // حداکثر تعداد توکن‌ها
                'temperature' => 0.4,  // سطح خلاقیت
            ]);

            // بررسی پاسخ از API OpenAI
            if ($response->successful()) {
                $data = $response->json();

                // برگرداندن پاسخ به کاربر
                return response()->json([
                    'prompt' => $prompt,
                    'image_url' => $imageUrl,
                    'response' => $data['choices'][0]['message']['content'] ?? 'No response from GPT',
                ]);
            } else {
                return response()->json(['error' => 'Failed to communicate with OpenAI API'], 500);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // مدیریت خطای تایم‌اوت یا مشکلات ارتباطی
            return response()->json(['error' => 'Request timed out or another connection error occurred'], 500);
        }
    }


}
