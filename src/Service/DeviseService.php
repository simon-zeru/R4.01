<?php
// src/Service/DeviseService.php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeviseService
{
    private const API_URL = 'http://api.exchangeratesapi.io/v1/latest?access_key=0946b723d26049b0c5a7b36aa7863775&format=1';

    // Clés de session
    private const SESSION_KEY_RATES = 'exchange_rates';
    private const SESSION_KEY_CURRENCY = 'current_currency';
    private const DEFAULT_CURRENCY = 'EUR';
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Récupère l'objet Session à partir de RequestStack.
     */
    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    /**
     * Donne la devise actuelle (ex: 'EUR', 'USD').
     * Initialise la devise par défaut si elle n'existe pas en session.
     */
    public function getDevise(): string
    {
        $session = $this->getSession();
        if (!$session->has(self::SESSION_KEY_CURRENCY)) {
            $session->set(self::SESSION_KEY_CURRENCY, self::DEFAULT_CURRENCY);
        }
        return $session->get(self::SESSION_KEY_CURRENCY, self::DEFAULT_CURRENCY);
    }

    /**
     * Change la devise sélectionnée dans la session.
     */
    public function setDevise(string $devise): void
    {
        $session = $this->getSession();
        $session->set(self::SESSION_KEY_CURRENCY, strtoupper($devise));
        // Vide le cache des taux pour forcer le rechargement lors du prochain besoin
        $session->remove(self::SESSION_KEY_RATES);
    }

    /**
     * Récupère les taux: session d'abord, API (apilayer) sinon.
     * @return array<string, float>|null Tableau des taux ou null si erreur.
     */
    private function getRates(): ?array
    {
        $session = $this->getSession();

        $rates = $session->get(self::SESSION_KEY_RATES);

        if (is_array($rates)) {
            return $rates; // Taux trouvés en session
        } else {
            $res = @file_get_contents(self::API_URL);
            $data = $res ? json_decode($res, true) : [];
            $rates = $data['rates'] ?? [];
            $session->set(self::SESSION_KEY_RATES, $rates);
            return $rates;
        }

    }

    /**
     * Convertit un montant EUR vers la devise actuelle.
     * @param float $amountInEur Montant en Euros.
     * @return float Montant converti
     */
    public function convert(float $amountInEur): ?float
    {
        $targetCurrency = $this->getDevise();
        if ($targetCurrency === self::DEFAULT_CURRENCY) {
            return $amountInEur; // Pas de conversion si déjà en EUR
        }

        $rates = $this->getRates();

        // Effectue la conversion
        return $amountInEur * $rates[$targetCurrency];
    }
}