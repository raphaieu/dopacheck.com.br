<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\Team;
use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\TeamResource\Pages\EditTeam;
use App\Filament\Resources\TeamResource\Pages\ListTeams;

final class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Onboarding';

    protected static ?int $navigationSort = 0;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && mb_strtolower((string) $user->email) === 'rapha@raphael-martins.com';
    }

    public static function canViewAny(): bool
    {
        // Menu/CRUD de Teams no Filament apenas para superadmin.
        return self::shouldRegisterNavigation();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();
        if (! $user instanceof User) {
            return $query->whereRaw('1=0');
        }

        // Superadmin vê todos os teams
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

        return $query->whereIn('id', $teamIds->all());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Team')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('slug')
                            ->helperText('Usado no link público: /join/{slug}')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('whatsapp_join_url')
                            ->label('Link do grupo (WhatsApp)')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://chat.whatsapp.com/...'),

                        Forms\Components\TextInput::make('whatsapp_group_jid')
                            ->label('JID do grupo (WhatsApp)')
                            ->helperText('Identificador do grupo vindo no webhook (remoteJid), ex: 120363404774829500@g.us')
                            ->maxLength(64)
                            ->unique(ignoreRecord: true)
                            ->placeholder('1203...@g.us'),

                        Forms\Components\TextInput::make('whatsapp_group_name')
                            ->label('Nome do grupo (opcional)')
                            ->helperText('Apenas para visualização. O que realmente identifica o grupo é o JID.')
                            ->maxLength(255)
                            ->placeholder('Nome do grupo no WhatsApp'),

                        Forms\Components\TextInput::make('onboarding_title')
                            ->label('Título do onboarding (opcional)')
                            ->maxLength(255)
                            ->placeholder('🎈 Formulário de Inscrição | SalvaDopamina 🎈'),

                        Forms\Components\RichEditor::make('onboarding_body')
                            ->label('Texto de apresentação (opcional)')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                                'blockquote',
                                'undo',
                                'redo',
                            ])
                            ->helperText('Esse texto aparece no /join/{slug}. Se ficar vazio, usamos um texto padrão.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('whatsapp_join_url')
                    ->label('WhatsApp')
                    ->boolean(fn (Team $record): bool => is_string($record->whatsapp_join_url) && $record->whatsapp_join_url !== ''),
                Tables\Columns\IconColumn::make('whatsapp_group_jid')
                    ->label('Grupo/JID')
                    ->boolean(fn (Team $record): bool => is_string($record->whatsapp_group_jid) && $record->whatsapp_group_jid !== ''),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('openJoin')
                    ->label('Abrir /join')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Team $record): string => url('/join/' . $record->slug))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeams::route('/'),
            'edit' => EditTeam::route('/{record}/edit'),
        ];
    }
}

