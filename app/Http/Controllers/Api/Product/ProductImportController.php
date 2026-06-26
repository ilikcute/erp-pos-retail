<?php

namespace App\Http\Controllers\Api\Product;

use App\Actions\Product\ImportProductsFromCsvAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ImportProductCsvRequest;
use App\Services\Product\ProductImportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductImportController extends Controller
{
    public function __construct(
        private readonly ProductImportService $importService,
        private readonly ImportProductsFromCsvAction $importAction,
    ) {}

    public function downloadTemplate(): BinaryFileResponse
    {
        $this->authorize('product.product.create');

        $zipPath = $this->importService->buildTemplateZipPath();

        return response()->download(
            $zipPath,
            'product_import_template.zip',
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    public function import(ImportProductCsvRequest $request): JsonResponse
    {
        $results = $this->importAction->execute(
            file: $request->file('file'),
            userId: auth()->id(),
        );

        $status = $results['failed'] > 0 && $results['success'] === 0 ? 422 : 200;

        return response()->json([
            'success' => $results['failed'] === 0,
            'message' => $this->buildResultMessage($results),
            'data' => $results,
        ], $status);
    }

    /**
     * @param  array{total: int, success: int, failed: int}  $results
     */
    private function buildResultMessage(array $results): string
    {
        if ($results['success'] === 0 && $results['failed'] > 0) {
            return 'Import gagal. Periksa error pada setiap baris.';
        }

        if ($results['failed'] > 0) {
            return "Import selesai: {$results['success']} berhasil, {$results['failed']} gagal.";
        }

        return "Import berhasil: {$results['success']} produk ditambahkan.";
    }
}
