<?php

namespace App\Http\Controllers;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;

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
        $image = (new Image())->setContent($imageData);

        // تنظیم ویژگی‌ها (لیبل، چهره، و اشیاء) با استفاده از مقادیر عددی
        $features = [
            (new Feature())->setType(1)->setMaxResults(10), // 1: تشخیص لیبل‌ها (LABEL_DETECTION)
            (new Feature())->setType(2)->setMaxResults(10), // 2: تشخیص چهره‌ها (FACE_DETECTION)
            (new Feature())->setType(16)->setMaxResults(10), // 16: تشخیص اشیاء (OBJECT_LOCALIZATION)
        ];

        // ایجاد درخواست با استفاده از AnnotateImageRequest
        $request = (new AnnotateImageRequest())
            ->setImage($image)
            ->setFeatures($features);

        // ارسال درخواست به Google Vision API
        $response = $imageAnnotator->batchAnnotateImages([$request]);

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
