<?php
// tests/Service/RateComparisonServiceTest.php

namespace App\Tests\Service;

use App\Service\RateComparisonService;
use PHPUnit\Framework\TestCase;

class RateComparisonServiceTest extends TestCase
{
    private $rateComparisonService;

    protected function setUp(): void
    {
        $this->rateComparisonService = new RateComparisonService();
    }

    public function testGetOffers(): void
    {
        $data = [
            'loan_amount' => 100000,
            'loan_duration' => 15,
        ];

        // Call the getOffers method
        $offers = $this->rateComparisonService->getOffers($data);

        $this->assertNotEmpty($offers, "Les offres ne doivent pas être vides");

        foreach ($offers as $offer) {
            $this->assertEquals($data['loan_amount'], $offer['loan_amount'], "Le montant du prêt ne correspond pas");
            $this->assertEquals($data['loan_duration'], $offer['loan_duration'], "La durée du prêt ne correspond pas");
        }

        // Extract loan rates and check if they are sorted
        $loanRates = array_map(fn($offer) => $offer['loan_rate'], $offers);

        // Create a sorted copy of the rates and compare
        $sortedLoanRates = $loanRates;
        sort($sortedLoanRates);

        $this->assertEquals($sortedLoanRates, $loanRates, "Les offres ne sont pas triées par taux");
    }

    public function testLoadOffers(): void
    {
        // Use Reflection to access the private method
        $reflection = new \ReflectionMethod(RateComparisonService::class, 'loadOffers');
        $reflection->setAccessible(true); // Make it accessible

        $offers = $reflection->invoke(new RateComparisonService()); // Call the method

        $this->assertNotEmpty($offers, "Les offres chargées ne doivent pas être vides");
        $this->assertArrayHasKey('CARREFOURBANK.json', $offers, "Le fichier 'CARREFOURBANK.json' doit être présent");
    }
}
