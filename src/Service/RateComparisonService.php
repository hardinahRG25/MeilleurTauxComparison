<?php

namespace App\Service;

class RateComparisonService
{
    public function getOffers(array $data): array
    {
        $keyMapping = [
            'loan_amount' => ['montant_pret', 'montant', 'amount'],
            'loan_duration' => ['duree_pret', 'duree', 'duration'],
            'loan_rate' => ['taux', 'taux_pret', 'rate'],
        ];

        $normalizeOfferKeys = function ($offer, $bankName) use ($keyMapping) {
            $normalizedOffer = [];

            foreach ($keyMapping as $standardKey => $possibleKeys) {
                foreach ($possibleKeys as $key) {
                    if (isset($offer[$key])) {
                        $normalizedOffer[$standardKey] = $offer[$key];
                        break;
                    }
                }
            }

            $normalizedOffer['bank_name'] = $bankName;

            return $normalizedOffer;
        };

        $allOffers = $this->loadOffers();

        $normalizedOffers = [];
        foreach ($allOffers as $file => $offers) {
            $bankName = pathinfo($file, PATHINFO_FILENAME);

            foreach ($offers as $offer) {
                $normalizedOffers[] = $normalizeOfferKeys($offer, $bankName);
            }
        }

        $filteredOffers = array_filter($normalizedOffers, function ($offer) use ($data) {
            return isset($offer['loan_amount'], $offer['loan_duration']) &&
                $offer['loan_amount'] == $data['loan_amount'] &&
                $offer['loan_duration'] == $data['loan_duration'];
        });

        usort($filteredOffers, fn($a, $b) => $a['loan_rate'] <=> $b['loan_rate']);

        return $filteredOffers;
    }

    private function loadOffers(): array
    {
        $files = ['CARREFOURBANK.json', 'BNP.json', 'SG.json'];
        $offers = [];


        foreach ($files as $file) {

            $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $file;


            if (!file_exists($path)) {
                throw new \Exception("Le fichier $file est introuvable au chemin : $path");
            }


            $content = json_decode(file_get_contents($path), true);


            if (is_null($content)) {
                throw new \Exception("Erreur de décodage JSON dans le fichier : $file");
            }

            $offers[$file] = $content;
        }

        return $offers;
    }
}
