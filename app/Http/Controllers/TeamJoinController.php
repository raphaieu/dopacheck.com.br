<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

final class TeamJoinController extends Controller
{
    public function show(Team $team): Response
    {
        return Inertia::render('Join/TeamJoin', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'whatsapp_join_url' => $team->whatsapp_join_url,
                'onboarding_title' => $team->onboarding_title,
                'onboarding_body' => $team->onboarding_body,
            ],
        ]);
    }

    public function store(Request $request, Team $team): RedirectResponse
    {
        $data = $request->all();

        // Normalização leve para consistência/claim futuro
        if (isset($data['email']) && is_string($data['email'])) {
            $data['email'] = mb_strtolower(trim($data['email']));
        }
        if (isset($data['whatsapp_number']) && is_string($data['whatsapp_number'])) {
            $data['whatsapp_number'] = preg_replace('/\D+/', '', $data['whatsapp_number']) ?? '';
        }
        if (isset($data['circle_url']) && is_string($data['circle_url'])) {
            $data['circle_url'] = trim($data['circle_url']);
        }

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('team_applications', 'email')->where(fn ($q) => $q->where('team_id', $team->id)),
            ],
            'whatsapp_number' => [
                'required',
                'string',
                'min:10',
                'max:20',
                Rule::unique('team_applications', 'whatsapp_number')->where(fn ($q) => $q->where('team_id', $team->id)),
            ],
            'city' => ['required', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'circle_url' => ['required', 'url', 'max:2048'],
        ], [
            'circle_url.url' => 'Informe um link válido (com http/https).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        TeamApplication::create([
            'team_id' => $team->id,
            'name' => $data['name'],
            'birthdate' => $data['birthdate'],
            'email' => $data['email'],
            'whatsapp_number' => $data['whatsapp_number'],
            'city' => $data['city'],
            'neighborhood' => $data['neighborhood'],
            'circle_url' => $data['circle_url'],
            'status' => 'pending',
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ],
        ]);

        return back()->with('success', 'Inscrição enviada! Agora é só aguardar a aprovação do time.');
    }
}

