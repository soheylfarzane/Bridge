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

        // ارسال درخواست به OpenAI با استفاده از GPT-4 و endpoint chat/completions
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',  // استفاده از gpt-4
            'messages' => [
                [
                    'role' => 'system', // شما می‌توانید راهنمایی یا اطلاعاتی برای مدل در این بخش وارد کنید
                    'content' => 'You are a helpful assistant.',
                ],
                [
                    'role' => 'user',  // پیام کاربر
                    'content' => $prompt,
                ]
            ],
            'max_tokens' => 150,  // حداکثر تعداد توکن‌ها
            'temperature' => 0.7,  // سطح خلاقیت
        ]);

        // پاسخ از API OpenAI
        $data = $response->json();

        // برگرداندن پاسخ به کاربر
        return response()->json([
            'prompt' => $prompt,
            'response' => $data['choices'][0]['message']['content'] ?? 'No response from GPT',
        ]);
    }
}
