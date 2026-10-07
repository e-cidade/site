# Site da comunidade e-Cidade

Site estático da comunidade e-Cidade, construído com [Jigsaw](https://jigsaw.tighten.com/) e Vite e publicado por GitHub Actions.

O conteúdo institucional, as notícias e o acervo de imagens migrado do antigo WordPress são mantidos no próprio repositório.

## Desenvolvimento local

Requisitos:

- PHP 8.3+
- Composer
- Node.js 20.19+
- npm

```bash
composer install
npm ci
npm run dev
```

## Build de produção

```bash
composer build
```

O resultado é gerado em `build_production/`.

## Conteúdo

- páginas institucionais: `source/*.blade.php`;
- notícias: `source/_posts/*.md`;
- estilos: `source/_assets/css/`;
- JavaScript: `source/_assets/js/`;
- mídia editorial migrada: `source/assets/images/migrated/`.

Para adicionar uma notícia, crie um arquivo Markdown em `source/_posts/` com título, data, descrição e layout `post`.

## Publicação

Pull requests executam build e smoke tests. A branch `main` é publicada por GitHub Actions.

## Licença

Consulte [LICENSE](LICENSE).
