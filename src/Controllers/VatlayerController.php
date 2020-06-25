<?php declare(strict_types=1);

namespace Memo\Vatlayer\Controllers;

use Memo\Vatlayer\Services\VatlayerService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Shopware\Core\Defaults;

/**
 * @RouteScope(scopes={"api"})
 */
class VatlayerController extends AbstractController
{
    /**
     * @var \Memo\Vatlayer\Services\VatlayerService
     */
    private $vatlayerService;

    public function __construct(VatlayerService $vatlayerService)
    {
        $this->vatlayerService = $vatlayerService;
    }

    /**
     * @Route(
     *     "api/v{version}/memo/vatlayer/check-credentials",
     *     name="storefront.memo.vatlayer.check-credentials",
     *     methods={"GET"}
     *     )
     */
    public function checkCredentials(Request $request, Context $context): JsonResponse
    {
        try {
            $response = $this->vatlayerService->rateList();

            return new JsonResponse([
                'valid' => true,
                'message' => "Your credentials are valid"
            ]);
        }
        catch(\Exception $e) {
            return new JsonResponse([
                'valid' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @Route("api/v{version}/memo/vatlayer/check-id/{id}", name="storefront.memo.vatlayer.vatid-check", methods={"GET"}, defaults={"auth_required"=false})
     */
    public function vatIdCheck(Request $request, Context $context): JsonResponse
    {
        $id = $request->get("id");

        $response = [];
        $message = [];
        try {
            $response = $this->vatlayerService->validate($id);

            // Dit nog omzetten naar vertaalbare snippets, but how?
            if($response['database'] !== "ok") {
                $message = ["type" => "warning", "message" => "We were unable to validate your VAT ID at this time"];
            } elseif($response['format_valid'] == false) {
                $message = ["type" => "danger", "message" => "Invalid VAT ID format"];
            } elseif($response['valid'] == false) {
                $message = ["type" => "danger", "message" => "Your VAT ID is invalid"];
            } else {
                $message = ["type" => "success", "message" => sprintf("Validated as \"%s\"", $response['company_name'])];
            }
        } catch(\Exception $exception) {
            $message = ["type" => "warning", "message" => $exception->getMessage()];
        }

        $response['message'] = $message;

        return new JsonResponse($response);
    }
}
