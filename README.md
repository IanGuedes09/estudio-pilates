# Estúdio Pilates — Sistema de Gestão (Laravel)

Sistema interno para gestão de estúdio (alunos, agenda, pagamentos/competência, comprovantes, professores e dashboard).

## Módulos

- **Dashboard**: resumo de aulas do dia e indicadores do mês
- **Alunos**: cadastro/edição/exclusão (com preservação de histórico)
- **Agenda**: agendamentos (API interna usada pela tela)
- **Pagamentos**: financeiro mensal por competência + upload/remoção de comprovantes
- **Usuários/Perfis**: `Administrador` e `Professor` (escopo do professor é restrito às próprias alunas)

## Requisitos

- PHP + Composer
- (Opcional) Node/npm para assets
- Banco: SQLite (local) ou MySQL (homolog/prod)

## Setup local (Laragon / Windows)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

## Testes

```bash
php artisan test
```

## Upload de comprovantes

- Arquivos são gravados em `storage/app/public/comprovantes`
- Exige link público:

```bash
php artisan storage:link
```

## Variáveis de ambiente importantes

- **`APP_DEBUG`**: nunca deixe `true` em ambiente público
- **`APP_ALLOW_REGISTRATION`**: controla o cadastro público em `/register`
  - recomendado `false` em homolog/prod (usuários são criados/seed/admin)

Exemplo em `.env`:

```env
APP_ALLOW_REGISTRATION=false
APP_DEBUG=false
```

## Rotas principais

- Web (UI): `/dashboard`, `/agenda`, `/alunos`, `/pagamentos`, `/professores` (admin)
- API interna (sessão do usuário): prefixo `/api/v1/*`

## Segurança (nota rápida)

- Perfis são aplicados no backend; endpoints `api/v1` também respeitam escopo de `Professor`.
- Login tem rate limit (`throttle`).

