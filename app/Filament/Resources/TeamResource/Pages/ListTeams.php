<?php

declare(strict_types=1);

namespace App\Filament\Resources\TeamResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TeamResource;

final class ListTeams extends ListRecords
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $isSuperadmin = $user instanceof User && mb_strtolower((string) $user->email) === 'rapha@raphael-martins.com';

        if (! $isSuperadmin) {
            return [];
        }

        // Teams são criados via Jetstream; aqui só redirecionamos para o fluxo oficial.
        return [
            Actions\Action::make('createTeam')
                ->label('Novo team')
                ->icon('heroicon-o-plus')
                ->url(url('/teams/create')),
        ];
    }
}

