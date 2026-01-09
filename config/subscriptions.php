<?php

declare(strict_types=1);

return [
    'subscriptions' => [
        [
            'plan' => 'DOPA Check PRO ✨ (Mensal)',
            'description' => 'Desbloqueie recursos PRO e aumente seus limites no DOPA Check.',
            'price_id' => env('STRIPE_PRICE_PRO_MONTHLY', ''),
            'price' => (float) env('DOPA_PRO_PRICE_MONTHLY', 19.90),
            'billing_period' => 'Cobrança mensal',
            'features' => [
                'Criação ilimitada de desafios',
                'Relatórios e insights avançados',
                'Recursos PRO desbloqueados no produto',
                'Suporte prioritário',
            ],
        ],
        [
            'plan' => 'DOPA Check PRO ✨ (Anual)',
            'description' => 'Economize no plano anual e mantenha acesso ao PRO o ano todo.',
            'price_id' => env('STRIPE_PRICE_PRO_YEARLY', ''),
            'price' => (float) env('DOPA_PRO_PRICE_YEARLY', 99.90),
            'billing_period' => 'Cobrança anual',
            'features' => [
                'Criação ilimitada de desafios',
                'Relatórios e insights avançados',
                'Recursos PRO desbloqueados no produto',
                'Suporte prioritário',
            ],
        ],
    ],
];
