<?php
// src/Twig/CurrencyExtension.php

namespace App\Twig;

use App\Service\DeviseService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use NumberFormatter; // Requis pour le formatage

class CurrencyExtension extends AbstractExtension
{
    private DeviseService $deviseService;
    private RequestStack $requestStack;

    public function __construct(DeviseService $deviseService, RequestStack $requestStack)
    {
        $this->deviseService = $deviseService;
        $this->requestStack = $requestStack;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('currency_convert', [$this, 'convertAndFormat']),
        ];
    }

    /**
     * Filtre 'currency_convert' : Convertit et formate un prix EUR.
     * Gère les erreurs de conversion.
     *
     * @param float $amountInEur Montant en EUR.
     * @return string Le prix formaté ou une indication d'erreur.
     */
    public function convertAndFormat(?float $amountInEur): string
    {

        $session = $this->requestStack->getSession();
        $targetCurrency = $session->get('current_currency', 'EUR');
        $convertedAmount = $this->deviseService->convert($amountInEur);

        $locale = ($targetCurrency === 'EUR') ? 'fr_FR' : 'en_US';

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);


        return $formatter->formatCurrency($convertedAmount, $targetCurrency);
    }
}