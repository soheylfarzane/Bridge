<?php

namespace App\Http\Controllers;



use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;


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

        // ارسال درخواست به Google Vision API برای شناسایی لیبل‌ها و چهره‌ها
        $response = $imageAnnotator->annotateImage([
            'image' => ['content' => $imageData],
            'features' => [
                ['type' => 'LABEL_DETECTION', 'maxResults' => 10], // تشخیص لیبل‌ها
                ['type' => 'FACE_DETECTION', 'maxResults' => 10],  // تشخیص چهره‌ها
                ['type' => 'OBJECT_LOCALIZATION', 'maxResults' => 10],  // تشخیص اشیاء
            ]
        ]);

        $labels = $response->getLabelAnnotations();
        $faces = $response->getFaceAnnotations();
        $objects = $response->getLocalizedObjectAnnotations();

        // بستن کلاینت بعد از اتمام کار
        $imageAnnotator->close();

        // ایجاد آرایه برای نتایج
        $results = [];

        // چک کردن و نمایش لیبل‌ها
        if ($labels) {
            $labelDescriptions = [];
            foreach ($labels as $label) {
                $labelDescriptions[] = $label->getDescription();
            }
            $results['labels'] = $labelDescriptions;
        }

        // چک کردن و نمایش چهره‌ها
        if ($faces) {
            $results['faces'] = count($faces) . ' faces detected';
        }

        // چک کردن و نمایش اشیاء
        if ($objects) {
            $objectDescriptions = [];
            foreach ($objects as $object) {
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
