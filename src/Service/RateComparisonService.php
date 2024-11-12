<?php

namespace App\Service;

class RateComparisonService
{
    public function getOffers(array $data): array
    {
        // Mappage des clés possibles vers les clés standardisées
        $keyMapping = [
            'loan_amount' => ['montant_pret', 'montant', 'amount'],
            'loan_duration' => ['duree_pret', 'duree', 'duration'],
            'loan_rate' => ['taux', 'taux_pret', 'rate'],
        ];

        // Fonction pour normaliser les clés de l'offre
        $normalizeOfferKeys = function ($offer, $bankName) use ($keyMapping) {
            $normalizedOffer = [];

            foreach ($keyMapping as $standardKey => $possibleKeys) {
                foreach ($possibleKeys as $key) {
                    if (isset($offer[$key])) {
                        $normalizedOffer[$standardKey] = $offer[$key];
                        break; // Nous avons trouvé une correspondance, on s'arrête ici
                    }
                }
            }

            // Ajouter le nom de la banque à l'offre
            $normalizedOffer['bank_name'] = $bankName;

            return $normalizedOffer;
        };

        // Charge toutes les offres
        $allOffers = $this->loadOffers();

        // Normaliser les clés de chaque offre et ajouter le nom de la banque
        $normalizedOffers = [];
        foreach ($allOffers as $file => $offers) {
            $bankName = pathinfo($file, PATHINFO_FILENAME); // Extraire le nom de la banque (nom du fichier sans l'extension)

            foreach ($offers as $offer) {
                $normalizedOffers[] = $normalizeOfferKeys($offer, $bankName);
            }
        }

        // Filtrer les offres en fonction des données entrées
        $filteredOffers = array_filter($normalizedOffers, function ($offer) use ($data) {
            return isset($offer['loan_amount'], $offer['loan_duration']) &&
                $offer['loan_amount'] == $data['loan_amount'] &&
                $offer['loan_duration'] == $data['loan_duration'];
        });

        // Trier les offres par taux de prêt
        usort($filteredOffers, fn($a, $b) => $a['loan_rate'] <=> $b['loan_rate']);

        return $filteredOffers;
    }

    private function loadOffers(): array
    {
        $files = ['CARREFOURBANK.json', 'BNP.json', 'SG.json'];
        $offers = [];

        // Utilisation de DIRECTORY_SEPARATOR pour obtenir le bon séparateur de dossier
        foreach ($files as $file) {
            // Construction du chemin avec DIRECTORY_SEPARATOR
            $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $file;

            // Vérifiez si le fichier existe
            if (!file_exists($path)) {
                throw new \Exception("Le fichier $file est introuvable au chemin : $path");
            }

            // Lecture du fichier JSON
            $content = json_decode(file_get_contents($path), true);

            // Vérification du décodage JSON
            if (is_null($content)) {
                throw new \Exception("Erreur de décodage JSON dans le fichier : $file");
            }

            // Ajouter les offres du fichier avec le nom de la banque
            $offers[$file] = $content; // Associée à chaque fichier JSON pour avoir le nom de la banque
        }

        return $offers;
    }
}
