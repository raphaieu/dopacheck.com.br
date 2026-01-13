<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Models\TeamApplication;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\TeamApplicationResource\Pages\EditTeamApplication;
use App\Filament\Resources\TeamApplicationResource\Pages\ListTeamApplications;
use App\Filament\Resources\TeamApplicationResource\Pages\ViewTeamApplication;

final class TeamApplicationResource extends Resource
{
    protected static ?string $model = TeamApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Onboarding';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['team', 'approvedBy', 'user']);

        $user = Auth::user();
        if (! $user instanceof User) {
            return $query->whereRaw('1=0');
        }

        // Superadmin vê tudo
        if (mb_strtolower((string) $user->email) === 'rapha@raphael-martins.com') {
            return $query;
        }

        $teamIds = collect()
            ->merge($user->ownedTeams()->where('personal_team', false)->pluck('id'))
            ->merge($user->teams()->where('personal_team', false)->wherePivot('role', 'admin')->pluck('teams.id'))
            ->unique()
            ->values();

        if ($teamIds->isEmpty()) {
            return $query->whereRaw('1=0');
        }

        return $query->whereIn('team_id', $teamIds->all());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados da inscrição')
                ->schema([
                    Forms\Components\Select::make('team_id')
                        ->relationship('team', 'name')
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'pending',
                            'approved' => 'approved',
                            'rejected' => 'rejected',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('birthdate')
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('whatsapp_number')
                        ->required()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('city')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('neighborhood')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('circle_url')
                        ->url()
                        ->required()
                        ->maxLength(2048),
                ])
                ->columns(2),

            Forms\Components\Section::make('Aprovação / Claim')
                ->schema([
                    Forms\Components\Placeholder::make('approved_by_display')
                        ->label('Aprovado por')
                        ->content(function (?TeamApplication $record): string {
                            if (! $record || ! $record->approved_by) {
                                return '—';
                            }
                            $u = $record->approvedBy;
                            if (! $u) {
                                return (string) $record->approved_by;
                            }
                            return trim(($u->name ?? '') . ' <' . ($u->email ?? '') . '>') ?: (string) $record->approved_by;
                        }),
                    Forms\Components\DateTimePicker::make('approved_at')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Placeholder::make('claimed_user_display')
                        ->label('Usuário (claim)')
                        ->content(function (?TeamApplication $record): string {
                            if (! $record || ! $record->user_id) {
                                return '—';
                            }
                            $u = $record->user;
                            if (! $u) {
                                return (string) $record->user_id;
                            }
                            return trim(($u->name ?? '') . ' <' . ($u->email ?? '') . '>') ?: (string) $record->user_id;
                        }),
                    Forms\Components\Textarea::make('meta')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(4)
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : (string) $state),
                ])
                ->columns(2)
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('whatsapp_number')
                    ->label('WhatsApp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('neighborhood')
                    ->label('Bairro')
                    ->searchable(),
                Tables\Columns\TextColumn::make('circle_url')
                    ->label('Circle')
                    ->url(fn (TeamApplication $record) => $record->circle_url)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approvedBy.email')
                    ->label('Aprovado por')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Claim (user)')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'pending',
                        'approved' => 'approved',
                        'rejected' => 'rejected',
                    ]),
                Tables\Filters\TernaryFilter::make('claimed')
                    ->label('Claim')
                    ->placeholder('Todos')
                    ->trueLabel('Claimed (user_id preenchido)')
                    ->falseLabel('Unclaimed (user_id null)')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('user_id'),
                        false: fn (Builder $query) => $query->whereNull('user_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn (TeamApplication $record): bool => $record->status === 'pending')
                    ->action(function (TeamApplication $record): void {
                        $user = Auth::user();
                        if (! $user instanceof User) {
                            abort(403);
                        }

                        $isSuperadmin = mb_strtolower((string) $user->email) === 'rapha@raphael-martins.com';
                        if (! $isSuperadmin) {
                            $allowedTeamIds = collect()
                                ->merge($user->ownedTeams()->where('personal_team', false)->pluck('id'))
                                ->merge($user->teams()->where('personal_team', false)->wherePivot('role', 'admin')->pluck('teams.id'))
                                ->unique();
                            abort_unless($allowedTeamIds->contains($record->team_id), 403);
                        }

                        $record->forceFill([
                            'status' => 'approved',
                            'approved_by' => $user->id,
                            'approved_at' => now(),
                        ])->save();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (TeamApplication $record): bool => $record->status === 'pending')
                    ->action(function (TeamApplication $record): void {
                        $user = Auth::user();
                        if (! $user instanceof User) {
                            abort(403);
                        }

                        $isSuperadmin = mb_strtolower((string) $user->email) === 'rapha@raphael-martins.com';
                        if (! $isSuperadmin) {
                            $allowedTeamIds = collect()
                                ->merge($user->ownedTeams()->where('personal_team', false)->pluck('id'))
                                ->merge($user->teams()->where('personal_team', false)->wherePivot('role', 'admin')->pluck('teams.id'))
                                ->unique();
                            abort_unless($allowedTeamIds->contains($record->team_id), 403);
                        }

                        $record->forceFill([
                            'status' => 'rejected',
                            'approved_by' => null,
                            'approved_at' => null,
                        ])->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeamApplications::route('/'),
            'view' => ViewTeamApplication::route('/{record}'),
            'edit' => EditTeamApplication::route('/{record}/edit'),
        ];
    }
}

