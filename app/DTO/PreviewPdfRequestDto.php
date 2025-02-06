<?php

namespace App\DTO;

use App\Http\Requests\ShowPreviewPdfRequest;

class PreviewPdfRequestDto
{
    public function __construct(
        public int $templateId,
        public int $productId,
        public bool $download,
    )
    {
    }

    public static function fromRequest(ShowPreviewPdfRequest $request): self
    {
        $isDownload = !empty($request->query('download'));
        return new self(
            $request->template_id,
            $request->product_id,
            $isDownload,
        );
    }
}