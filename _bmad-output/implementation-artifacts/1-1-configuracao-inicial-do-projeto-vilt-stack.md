# Story 1.1: Configuração Inicial do Projeto (VILT Stack)

Status: ready-for-dev

## Story

As a desenvolvedor,
I want configurar o ambiente base usando o template VILT (Laravel 12 + Vue 3.5),
so that eu tenha uma estrutura de código consistente e moderna seguindo a arquitetura.

## Acceptance Criteria

1. **VILT Core Setup**: Inicializar projeto com Laravel 12, Inertia 2.0 e Vue 3.5.
2. **Styling Engine**: Configurar Tailwind CSS 4.0 conforme definido na arquitetura.
3. **Strict Typing**: Garantir que PHP 8.3 Strict Types (`declare(strict_types=1)`) e TypeScript estejam configurados em todo o projeto.
4. **Development Server**: O comando `php artisan serve` e `npm run dev` devem rodar simultaneamente sem erros de compilação.
5. **Base Infrastructure**: Instalar e configurar as bases para Redis e Laravel Horizon.

## Tasks / Subtasks

- [ ] **Infrastructure Setup** (AC: #1, #5)
  - [ ] Verificar versões de PHP (8.3+) e Node.js.
  - [ ] Instalar Laravel 12 (se necessário/upgrade).
  - [ ] Configurar `.env` inicial com Redis e Database.
  - [ ] Instalar Laravel Horizon.
- [ ] **Frontend Foundation** (AC: #1, #2)
  - [ ] Configurar Inertia 2.0.
  - [ ] Instalar Vue 3.5.
  - [ ] Configurar Tailwind 4.0 (Alpha/Beta se necessário para L12/T4).
- [ ] **Type Safety Configuration** (AC: #3)
  - [ ] Configurar `tsconfig.json` para modo estrito.
  - [ ] Garantir que novos controllers/models usem `strict_types=1`.

## Dev Notes

- **Tech Stack**: Laravel 12, Inertia 2.0, Vue 3.5, Tailwind 4.0.
- **Reference**: [Architecture: Tech Stack](file:///home/raphael/www/dopacheck.com.br/_bmad-output/planning-artifacts/architecture.md#Tech%20Stack)
- **Reference**: [Architecture: Project Structure](file:///home/raphael/www/dopacheck.com.br/_bmad-output/planning-artifacts/architecture.md#Project%20Structure%20&%20Boundaries)

### Project Structure Notes

- Devem ser criadas as pastas bases: `app/Http/Controllers`, `app/Models`, `resources/js/Pages`, `resources/js/components`.
- O layout principal deve ser `AppLayout.vue`.

### References

- [PRD: Goals](file:///home/raphael/www/dopacheck.com.br/docs/PRD.md#5.%20Funcionalidades%20MVP%20(Core%20Web))
- [Architecture Pattern: Monolito VILT](file:///home/raphael/www/dopacheck.com.br/_bmad-output/planning-artifacts/architecture.md#Architectural%20Patterns)

## Dev Agent Record

### Agent Model Used

### Debug Log References

### Completion Notes List

### File List
