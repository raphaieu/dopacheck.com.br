<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Service para normalização e variações de números de telefone brasileiros.
 * 
 * Resolve o problema de números com/sem o 9 após o DDD, que ocorre
 * quando o WhatsApp/Evolution entrega JIDs em formatos diferentes
 * (especialmente em contatos antigos/migrados/portabilidade).
 */
class PhoneNumberService
{
    /**
     * Normaliza um número de telefone removendo caracteres não numéricos
     * e adicionando o código do país (55) se necessário para números brasileiros.
     * 
     * Padroniza sempre no formato 55DDDNXXXXXXXX (com o 9 após o DDD).
     * Se o número vier sem o 9, adiciona automaticamente.
     */
    public function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?: '';
        if ($digits === '') {
            return $phone;
        }

        // Normaliza Brasil: se vier 11 dígitos sem 55, prefixa.
        if (strlen($digits) === 11 && !str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        // Padroniza sempre no formato 55DDDNXXXXXXXX (com o 9 após o DDD)
        // Se for número brasileiro no formato 55 + DDD + 8 dígitos (total 12), adiciona o 9
        if (strlen($digits) === 12 && str_starts_with($digits, '55')) {
            $dd = substr($digits, 2, 2); // DDD (2 dígitos)
            $rest = substr($digits, 4); // Resto (8 dígitos)
            
            // Adiciona o 9 após o DDD para padronizar
            $digits = '55' . $dd . '9' . $rest;
        }

        return $digits;
    }

    /**
     * Gera variações de um número de telefone brasileiro.
     * 
     * Para números brasileiros, gera variações com e sem o 9 após o DDD,
     * pois o WhatsApp/Evolution pode entregar números em ambos os formatos.
     * 
     * @param string $phone Número de telefone (pode estar em qualquer formato)
     * @return array<string> Array com as variações possíveis do número
     */
    public function generateVariations(string $phone): array
    {
        $normalized = $this->normalize($phone);
        $variations = [$normalized];

        // Apenas para números brasileiros (começam com 55)
        if (!str_starts_with($normalized, '55')) {
            return $variations;
        }

        $length = strlen($normalized);

        // Se for número no formato 55 + DDD + 8 dígitos (total 12)
        // gera também a variação com o 9 após o DDD (55 + DDD + 9 + 8 dígitos = 13)
        if ($length === 12) {
            $dd = substr($normalized, 2, 2); // DDD (2 dígitos)
            $rest = substr($normalized, 4); // Resto (8 dígitos)
            
            // Adiciona variação com 9 após o DDD
            $withNine = '55' . $dd . '9' . $rest;
            $variations[] = $withNine;
        }
        // Se for número no formato 55 + DDD + 9 + 8 dígitos (total 13)
        // gera também a variação sem o 9 após o DDD (55 + DDD + 8 dígitos = 12)
        elseif ($length === 13) {
            $dd = substr($normalized, 2, 2); // DDD (2 dígitos)
            $rest = substr($normalized, 4); // Resto (9 + 8 dígitos)
            
            // Se o terceiro dígito após o DDD é 9, remove para gerar variação sem 9
            if (strlen($rest) >= 1 && $rest[0] === '9') {
                $withoutNine = '55' . $dd . substr($rest, 1); // Remove o 9
                $variations[] = $withoutNine;
            }
        }

        // Remove duplicatas e retorna
        return array_values(array_unique($variations));
    }

    /**
     * Busca um usuário usando variações do número de telefone.
     * 
     * Gera todas as variações possíveis do número e busca no banco
     * usando os campos whatsapp_number e phone.
     * 
     * O campo whatsapp_number geralmente tem o formato com 55,
     * enquanto phone pode ter ou não o 55.
     * 
     * @param string $phone Número de telefone a buscar
     * @return array{user: \App\Models\User|null, query_info: array{whatsapp_matches: int, phone_matches: int, total_variations: int}}
     */
    public function findUserByPhoneWithInfo(string $phone): array
    {
        $variations = $this->generateVariations($phone);
        
        if (empty($variations)) {
            return [
                'user' => null,
                'query_info' => [
                    'whatsapp_matches' => 0,
                    'phone_matches' => 0,
                    'total_variations' => 0,
                ],
            ];
        }

        // Gera também variações sem o 55 para buscar no campo phone
        $variationsWithout55 = [];
        foreach ($variations as $variation) {
            if (str_starts_with($variation, '55') && strlen($variation) > 2) {
                $variationsWithout55[] = substr($variation, 2);
            }
        }
        
        $allVariations = array_merge($variations, $variationsWithout55);
        $allVariations = array_values(array_unique($allVariations));

        // Conta matches em cada campo
        $whatsappMatches = \App\Models\User::query()
            ->whereIn('whatsapp_number', $allVariations)
            ->count();
        
        $phoneMatches = \App\Models\User::query()
            ->whereIn('phone', $allVariations)
            ->count();

        $user = \App\Models\User::query()
            ->where(function ($query) use ($allVariations) {
                $query->whereIn('whatsapp_number', $allVariations)
                      ->orWhereIn('phone', $allVariations);
            })
            ->first();

        return [
            'user' => $user,
            'query_info' => [
                'whatsapp_matches' => $whatsappMatches,
                'phone_matches' => $phoneMatches,
                'total_variations' => count($allVariations),
            ],
        ];
    }

    /**
     * Busca um usuário usando variações do número de telefone.
     * 
     * Método simplificado que retorna apenas o usuário (para compatibilidade).
     * 
     * @param string $phone Número de telefone a buscar
     * @return \App\Models\User|null Usuário encontrado ou null
     */
    public function findUserByPhone(string $phone): ?\App\Models\User
    {
        $result = $this->findUserByPhoneWithInfo($phone);
        return $result['user'];
    }
}
