<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RateComparisonType;
use App\Service\RateComparisonService;

class RateComparisonController extends AbstractController
{
    #[Route('/compare', name: 'compare_rates', methods: ['GET', 'POST'])]
    public function compare(Request $request, RateComparisonService $rateService): Response
    {
        $form = $this->createForm(RateComparisonType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $offers = $rateService->getOffers($data);
            return $this->render('rate_comparison/results.html.twig', [
                'offers' => $offers,
            ]);
        }

        return $this->render('rate_comparison/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/api/compare', name: 'api_rate_comparison', methods: ['POST'])]
    public function apiCompare(Request $request, RateComparisonService $rateService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['loan_amount']) || !isset($data['loan_duration'])) {
            return new JsonResponse(['error' => 'Invalid data: loan_amount and loan_duration are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Get by using service
        $offers = $rateService->getOffers($data);

        return new JsonResponse($offers, JsonResponse::HTTP_OK);
    }
}
