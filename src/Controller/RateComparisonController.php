<?php

namespace App\Controller;

use App\Form\RateComparisonType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
}
