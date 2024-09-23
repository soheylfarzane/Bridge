<?php

namespace App\Http\Controllers;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class GoogleVisionController extends Controller
{
    public function analyzeImage()
    {
        // مسیر فایل تصویری که در پوشه public قرار دارد
        $imagePath = public_path('ax.jpg');

        // بررسی اینکه آیا فایل کلید Google Cloud JSON در مسیر وجود دارد یا نه
        $credentialsPath = storage_path('app/google-cloud-keys/google-cloud-key.json');

        if (!file_exists($credentialsPath)) {
            return response()->json([
                'message' => 'Google Cloud JSON credentials not found. Please upload the file first.'
            ], 404);
        }

        // ایجاد کلاینت برای اتصال به Vision API با استفاده از فایل JSON ذخیره شده
        $imageAnnotator = new ImageAnnotatorClient([
            'credentials' => $credentialsPath,
        ]);

        // خواندن محتوای تصویر
        $imageData = file_get_contents($imagePath);

        // ارسال درخواست به Google Vision API برای شناسایی لیبل‌ها
        $response = $imageAnnotator->labelDetection($imageData);
        $labels = $response->getLabelAnnotations();

        // بستن کلاینت بعد از اتمام کار
        $imageAnnotator->close();

        // چک کردن و نمایش نتایج
        if ($labels) {
            $labelDescriptions = [];
            foreach ($labels as $label) {
                $labelDescriptions[] = $label->getDescription();
            }

            // برگرداندن نتایج به صورت JSON
            return response()->json([
                'labels' => $labelDescriptions
            ], 200);
        } else {
            return response()->json([
                'message' => 'No labels found'
            ], 200);
        }
    }
}
