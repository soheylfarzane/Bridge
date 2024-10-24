<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    public function askGPT4(Request $request)
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
                'model' => 'gpt-4',  // استفاده از gpt-4
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
        $imageUrl = $request->input('image_url', 'https://storyyar.studiomoon.site/results/StoryYar2024101718127287.jpg');

        // ایجاد یک نام فایل جدید برای تصویر
        $imageName = Str::random(40) . '.jpg';

        // دانلود تصویر و ذخیره آن در پوشه public
        $imageContent = file_get_contents($imageUrl);
        Storage::disk('public')->put($imageName, $imageContent);

        // URL جدید تصویر که در سرور ذخیره شده است
        $newImageUrl = asset('storage/' . $imageName);

        $client = new Client();

        // ارسال درخواست به OpenAI API با استفاده از تصویر ذخیره شده در سرور
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "$prompt"
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "$newImageUrl"
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 300,
            ]
        ]);

        $result = json_decode($response->getBody(), true);

        // استخراج محتوای پیام
        $message = $result['choices'][0]['message']['content'];

        // حذف تصویر از سرور پس از دریافت نتیجه
        Storage::disk('public')->delete($imageName);

        return response()->json([
            'message' => $message
        ]);
    }



}
