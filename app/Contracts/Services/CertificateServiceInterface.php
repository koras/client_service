<?php

namespace App\Contracts\Services;

use App\Http\Resources\ShowCertificateResource;

interface CertificateServiceInterface
{
    /**
     * @param string $id
     * @return ShowCertificateResource|null
     */
    public function getDataForShowCertificate(string $id): ?ShowCertificateResource;
}
