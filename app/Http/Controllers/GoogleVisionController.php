<?php

namespace App\Http\Controllers;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;


class GoogleVisionController extends Controller
{
    public function analyzeImage()
    {
        // مسیر فایل تصویری که در پوشه public قرار دارد
        $imagePath = public_path('ax.jpg');

        // کلید API گوگل که در فایل .env ذخیره شده است
        $apiKey = env('GOOGLE_CLOUD_API_KEY');

        // ایجاد کلاینت برای اتصال به Vision API
        $imageAnnotator = new ImageAnnotatorClient([
            'credentials' => json_decode(file_get_contents(storage_path('app/google-cloud-key.json')), true),
        ]);

        // خواندن محتوای تصویر
        $imageData = file_get_contents($imagePath);

        // ارسال درخواست به Google Vision API برای شناسایی لیبل‌ها
        $response = $imageAnnotator->labelDetection($imageData);
        $labels = $response->getLabelAnnotations();

        // چک کردن و نمایش نتایج
        if ($labels) {
            foreach ($labels as $label) {
                echo 'Label: ' . $label->getDescription() . PHP_EOL;
            }
        } else {
            echo 'No labels found' . PHP_EOL;
        }

        // بستن کلاینت
        $imageAnnotator->close();

    }

}
