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

        // تنظیم درخواست به Vision API
        $requests = [
            [
                'image' => ['content' => $imageData],
                'features' => [
                    ['type' => 'LABEL_DETECTION', 'maxResults' => 10], // تشخیص لیبل‌ها
                    ['type' => 'FACE_DETECTION', 'maxResults' => 10],  // تشخیص چهره‌ها
                    ['type' => 'OBJECT_LOCALIZATION', 'maxResults' => 10],  // تشخیص اشیاء
                ]
            ]
        ];

        // ارسال درخواست به Google Vision API
        $response = $imageAnnotator->batchAnnotateImages($requests);

        // گرفتن نتایج از پاسخ API
        $labelAnnotations = $response->getResponses()[0]->getLabelAnnotations();
        $faceAnnotations = $response->getResponses()[0]->getFaceAnnotations();
        $objectAnnotations = $response->getResponses()[0]->getLocalizedObjectAnnotations();

        // بستن کلاینت بعد از اتمام کار
        $imageAnnotator->close();

        // ایجاد آرایه برای نتایج
        $results = [];

        // چک کردن و نمایش لیبل‌ها
        if ($labelAnnotations) {
            $labelDescriptions = [];
            foreach ($labelAnnotations as $label) {
                $labelDescriptions[] = $label->getDescription();
            }
            $results['labels'] = $labelDescriptions;
        }

        // چک کردن و نمایش چهره‌ها
        if ($faceAnnotations) {
            $results['faces'] = count($faceAnnotations) . ' faces detected';
        }

        // چک کردن و نمایش اشیاء
        if ($objectAnnotations) {
            $objectDescriptions = [];
            foreach ($objectAnnotations as $object) {
                $objectDescriptions[] = $object->getName();
            }
            $results['objects'] = $objectDescriptions;
        }

        // برگرداندن نتایج به صورت JSON
        if (!empty($results)) {
            return response()->json($results, 200);
        } else {
            return response()->json([
                'message' => 'No significant labels, faces, or objects found.'
            ], 200);
        }
    }
}
