<?php

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->first() ?? User::factory()->create([
            'name' => 'Redação Auto News',
            'email' => 'redacao@autonews.test',
        ]);

        $categories = [
            ['name' => 'Veículos Elétricos', 'slug' => 'veiculos-eletricos'],
            ['name' => 'Mercado', 'slug' => 'mercado'],
            ['name' => 'Tecnologia', 'slug' => 'tecnologia'],
            ['name' => 'Lançamentos', 'slug' => 'lancamentos'],
            ['name' => 'Baterias', 'slug' => 'baterias'],
            ['name' => 'Mobilidade Elétrica', 'slug' => 'mobilidade-eletrica'],
        ];

        $categoryModels = collect($categories)->map(function (array $category) {
            return Category::query()->firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']],
            );
        });

        $posts = [
            [
                'title' => 'Brasil bate recorde e eletrificados já são quase 20% do mercado',
                'slug' => 'brasil-recorde-eletrificados-20-mercado',
                'excerpt' => 'Participação dos eletrificados chega a 19,2% em maio, com modelos compactos puxando os elétricos.',
                'category' => 'mercado',
                'is_trending' => true,
                'image' => 'https://images.unsplash.com/photo-1593941707882-a5bba14938c7?w=1200&h=675&fit=crop',
            ],
            [
                'title' => 'BYD sofre falta de baterias após boom dos elétricos de recarga rápida',
                'slug' => 'byd-falta-baterias-recarga-rapida',
                'excerpt' => 'Demanda por modelos de entrada pressiona cadeia de suprimentos e amplia filas de entrega.',
                'category' => 'veiculos-eletricos',
                'is_trending' => true,
                'image' => 'https://images.unsplash.com/photo-1617788138017-80ad40651399?w=1200&h=675&fit=crop',
            ],
            [
                'title' => 'Chinesas já começam a mudar o mercado de seminovos no Brasil',
                'slug' => 'chinesas-mercado-seminovos-brasil',
                'excerpt' => 'Marcas asiáticas aceleram revenda e alteram a dinâmica de precificação dos elétricos usados.',
                'category' => 'mercado',
                'is_trending' => true,
                'image' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=1200&h=675&fit=crop',
            ],
            [
                'title' => 'Volkswagen volta atrás nos elétricos: ID.3 recupera botões físicos',
                'slug' => 'volkswagen-id3-botoes-fisicos',
                'excerpt' => 'Após críticas, hatch elétrico recebe comandos tradicionais e melhora acabamento.',
                'category' => 'veiculos-eletricos',
                'image' => 'https://images.unsplash.com/photo-1619767886555-ef6d06b37b77?w=1200&h=675&fit=crop',
            ],
            [
                'title' => 'Stellantis prepara carro elétrico barato para rivalizar com BYD',
                'slug' => 'stellantis-eletrico-barato-byd',
                'excerpt' => 'Projeto de compacto elétrico europeu começa em 2028 e pode usar tecnologia chinesa.',
                'category' => 'lancamentos',
                'image' => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=1200&h=675&fit=crop',
            ],
            [
                'title' => 'Bateria sólida segue funcionando após ser cortada, mas há um porém',
                'slug' => 'bateria-solida-funciona-apos-corte',
                'excerpt' => 'Startup chinesa mostrou célula operando após teste extremo, mas adoção em carros ainda levará tempo.',
                'category' => 'baterias',
                'is_featured' => true,
                'image' => 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?w=1200&h=675&fit=crop',
            ],
            [
                'title' => 'Porsche Cayenne EV estreia no Brasil com preço de até R$ 1,4 milhão',
                'slug' => 'porsche-cayenne-ev-brasil',
                'excerpt' => 'SUV elétrico aparece no configurador brasileiro antes do lançamento europeu.',
                'category' => 'lancamentos',
                'is_featured' => true,
                'image' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1200&h=675&fit=crop',
            ],
            [
                'title' => 'Brasil ganha órgão federal dedicado a carros elétricos e recarga',
                'slug' => 'brasil-orgao-carros-eletricos-recarga',
                'excerpt' => 'Novo departamento no MME cuidará de recarga, baterias e integração dos elétricos à rede elétrica.',
                'category' => 'mobilidade-eletrica',
                'image' => 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=1200&h=675&fit=crop',
            ],
        ];

        foreach ($posts as $index => $data) {
            $category = $categoryModels->firstWhere('slug', $data['category']);

            Post::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $category->id,
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'body' => $this->bodyFor($data['title']),
                    'featured_image' => $data['image'],
                    'status' => PostStatus::Published,
                    'is_featured' => $data['is_featured'] ?? false,
                    'is_trending' => $data['is_trending'] ?? false,
                    'published_at' => now()->subDays(count($posts) - $index),
                ],
            );
        }

        Post::factory()
            ->count(12)
            ->for($user)
            ->recycle($categoryModels)
            ->create();
    }

    private function bodyFor(string $title): string
    {
        $paragraphs = [
            "O cenário da mobilidade elétrica no Brasil segue em aceleração, e a notícia sobre \"{$title}\" reforça a transformação do setor automotivo.",
            'Analistas apontam que a combinação de incentivos, novos lançamentos e maior oferta de recarga tem impulsionado a adoção de veículos eletrificados nas principais regiões do país.',
            'Para o consumidor, a disputa entre montadoras tradicionais e novos players internacionais tende a ampliar opções, reduzir prazos de entrega e estimular inovações em baterias, software e experiência de condução.',
            'Acompanhe o Auto News para entender como essas mudanças impactam preços, seminovos e a infraestrutura de recarga no Brasil.',
        ];

        return collect($paragraphs)
            ->map(fn (string $paragraph) => '<p>'.$paragraph.'</p>')
            ->implode('');
    }
}
