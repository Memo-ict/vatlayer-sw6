<?php declare(strict_types=1);

namespace Memo\Vatlayer\Controllers;

use Memo\Vatlayer\Services\VatlayerService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Storefront\Controller\StorefrontController as SwStorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class StorefrontController extends SwStorefrontController
{
    /** @var VatlayerService */
    private $vatlayerService;

    public function __construct(VatlayerService $vatlayerService)
    {
        $this->vatlayerService = $vatlayerService;
    }

    /**
     * @Route("widget/memo/vatlayer/check-id}",
     *         name="storefront.memo.vatlayer.validate", methods={"POST"},
     *         defaults={"auth_required"=false})
     */
    public function validate(RequestDataBag $request, Context $context): JsonResponse
    {
        $vatId = $request->get("vatId");

        $response = [];

        try {
            $response = $this->vatlayerService->validate($vatId);

            if ($response['database'] !== "ok") {
                $message = [
                    "type" => "warning",
                    "message" => $this->trans('memo-vatlayer.error.database')
                ];
            } elseif ($response['format_valid'] == false) {
                $message = [
                    "type" => "danger",
                    "message" => $this->trans('memo-vatlayer.error.format')
                ];
            } elseif ($response['valid'] == false) {
                $message = [
                    "type" => "danger",
                    "message" => $this->trans('memo-vatlayer.error.invalid')
                ];
            } else {
                $message = [
                    "type" => "success",
                    "message" => $this->trans('memo-vatlayer.valid', [
                        '%company%' => $response['company_name']
                    ])
                ];
            }
        } catch (\Exception $exception) {
            $message = ["type" => "warning", "message" => $exception->getMessage()];
        }

        $response['message'] = $message;

        return new JsonResponse($response);
    }
}
