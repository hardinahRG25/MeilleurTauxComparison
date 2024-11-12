<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\RateComparisonService;

class RateComparisonControllerTest extends WebTestCase
{
    public function testComparePageRenders(): void
    {
        $client = static::createClient();
        
        // Mock du service RateComparisonService
        $rateServiceMock = $this->createMock(RateComparisonService::class);
        $rateServiceMock->method('getOffers')->willReturn([
            ['bank' => 'Bank A', 'rate' => 3.5],
            ['bank' => 'Bank B', 'rate' => 3.0]
        ]);

        $client->getContainer()->set(RateComparisonService::class, $rateServiceMock);

        // Accède à la page de comparaison (GET)
        $client->request(Request::METHOD_GET, '/compare');
        
        // Vérifie le statut de la réponse et le rendu du formulaire
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="rate_comparison"]');
    }

    public function testComparePageFormSubmission(): void
    {
        $client = static::createClient();
        
        // Mock du service RateComparisonService pour retourner des offres fictives
        $rateServiceMock = $this->createMock(RateComparisonService::class);
        $rateServiceMock->method('getOffers')->willReturn([
            ['bank' => 'Bank A', 'rate' => 3.5],
            ['bank' => 'Bank B', 'rate' => 3.0]
        ]);

        $client->getContainer()->set(RateComparisonService::class, $rateServiceMock);

        // Soumettre le formulaire avec des données de test
        $crawler = $client->request(Request::METHOD_GET, '/compare');
        
        // Sélectionne le bouton de soumission avec le texte exact "Comparer les offres"
        $form = $crawler->selectButton('Comparer les offres')->form();

        // Remplit les champs du formulaire avec des valeurs valides
        $form['rate_comparison[loan_amount]'] = 100000;
        $form['rate_comparison[loan_duration]'] = 20;
        $form['rate_comparison[name]'] = 'John Doe';
        $form['rate_comparison[email]'] = 'john.doe@example.com';
        $form['rate_comparison[phone]'] = '+1234567890';

        // Soumet le formulaire
        $client->submit($form);

        // Vérifie que la page s'est bien chargée et qu'elle contient des offres
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.offer');
    }

    public function testApiCompare(): void
    {
        $client = static::createClient();

        // Mock du service RateComparisonService pour retourner des offres fictives
        $rateServiceMock = $this->createMock(RateComparisonService::class);
        $rateServiceMock->method('getOffers')->willReturn([
            ['bank' => 'Bank A', 'rate' => 3.5],
            ['bank' => 'Bank B', 'rate' => 3.0]
        ]);

        $client->getContainer()->set(RateComparisonService::class, $rateServiceMock);

        // Requête POST à l'API
        $client->request(Request::METHOD_POST, '/api/compare', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'loan_amount' => 100000,
            'loan_duration' => 20
        ]));

        // Vérifie le statut de la réponse et la structure JSON
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($client->getResponse()->getContent());

        // Vérifie les données dans la réponse JSON
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals('Bank A', $responseData[0]['bank']);
        $this->assertEquals(3.5, $responseData[0]['rate']);
    }

    public function testApiCompareInvalidData(): void
    {
        $client = static::createClient();

        // Requête POST avec des données invalides
        $client->request(Request::METHOD_POST, '/api/compare', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        // Vérifie le statut de la réponse et l'erreur JSON
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid data: loan_amount and loan_duration are required', $responseData['error']);
    }
}
