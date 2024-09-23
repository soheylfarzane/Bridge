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

        // ارسال درخواست به OpenAI با استفاده از GPT-4
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/completions', [
            'model' => 'gpt-4',  // تغییر مدل به gpt-4
            'prompt' => $prompt,
            'max_tokens' => 150,  // حداکثر تعداد توکن‌ها
            'temperature' => 0.7,  // سطح خلاقیت
        ]);

        // پاسخ از API OpenAI
        $data = $response->json();

        // برگرداندن پاسخ به کاربر
        return response()->json([
            'prompt' => $prompt,
            'response' => $data['choices'][0]['text'] ?? 'No response from GPT',
        ]);
    }
}
