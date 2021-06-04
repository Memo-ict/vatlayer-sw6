<?php declare(strict_types=1);

namespace Memo\Vatlayer\Controllers;

use Memo\Vatlayer\Services\VatlayerService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class ApiController extends AbstractController
{
    /** @var VatlayerService  */
    private $vatlayerService;

    public function __construct(VatlayerService $vatlayerService)
    {
        $this->vatlayerService = $vatlayerService;
    }

    /**
     * @Route("api/memo/vatlayer/check-credentials",
     *         name="api.action.memo.vatlayer.check-credentials", methods={"POST"})
     */
    public function checkCredentials(RequestDataBag $request): JsonResponse
    {
        $apiKey = $request->getAlnum('apiKey');

        return $this->checkCredentialsResponse($apiKey);
    }

    /**
     * @Route("api/v{version}/memo/vatlayer/check-credentials",
     *         name="api.action.memo.vatlayer.check-credentials.legacy", methods={"POST"})
     */
    public function checkCredentialsLegacy(RequestDataBag $request): JsonResponse
    {
        $apiKey = $request->getAlnum('apiKey');

        return $this->checkCredentialsResponse($apiKey);
    }

    public function checkCredentialsResponse($apiKey): JsonResponse
    {
        try {
            $this->vatlayerService->setApiKey($apiKey);
            $this->vatlayerService->rateList();

            return $this->json([
                'valid' => true,
                'message' => 'memo-vatlayer.api.credentials.valid'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'valid' => false,
                'message' => 'memo-vatlayer.api.credentials.invalid'
            ]);
        }
    }
}
