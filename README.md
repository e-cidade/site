# Site da comunidade e-Cidade

Site estático da comunidade e-Cidade, construído com [Jigsaw](https://jigsaw.tighten.com/) e publicado por GitHub Actions.

O conteúdo institucional e as notícias são mantidos no próprio repositório. A estrutura substitui a dependência do WordPress e preserva o histórico editorial do site público.

## Desenvolvimento local

Requisitos:

- PHP 8.2+
- Composer
- Node.js 20+
- npm 9+

```bash
composer install
npm run watch
```

O Jigsaw recompila as páginas e o Laravel Mix recompila os assets.

## Build de produção

```bash
composer prod
```

O resultado é gerado em `build_production/`.

## Conteúdo

- páginas institucionais: `source/*.blade.php`;
- notícias: `source/_posts/*.md`;
- estilos: `source/_assets/css/`;
- JavaScript: `source/_assets/js/`.

Para adicionar uma notícia, crie um arquivo Markdown em `source/_posts/` com título, data, descrição e layout `post`.

## Licença

Consulte [LICENSE](LICENSE).
