# Auto News

Blog sobre carros elétricos e mobilidade, inspirado no estilo InsideEVs, com identidade visual vermelho, branco e preto.

## Stack

- Laravel 13
- Inertia.js v3 + Vue 3
- Tailwind CSS v4
- Laravel Fortify (autenticação)

## Funcionalidades

- Blog público com home, categorias e páginas de notícias
- Painel admin para CRUD de posts
- Rascunhos, publicados e lixeira (soft delete) no estilo WordPress
- Cache do blog e dashboard com estatísticas

## Instalação local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Com [Laravel Herd](https://herd.laravel.com/), o site fica disponível em `https://auto-news.test`.

## Desenvolvimento

```bash
composer run dev
# ou separadamente:
php artisan serve
npm run dev
```

## Testes

```bash
php artisan test
```

## Licença

MIT
