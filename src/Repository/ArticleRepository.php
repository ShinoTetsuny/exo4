<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

 /**
 * Récupère les articles groupés par catégorie.
 *
 * @return array
 */
public function findArticlesGroupedByCategory(): array
{
    $result = $this->createQueryBuilder('a')
        ->leftJoin('a.category', 'c')
        ->select('a', 'c') // Sélectionnez à la fois l'article et la catégorie
        ->orderBy('c.title', 'ASC')
        ->getQuery()
        ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

    // Réorganisez les données pour avoir des catégories avec clé "title".
    $groupedArticles = [];
    foreach ($result as $item) {
        $categoryTitle = $item['category']['title'];

        if (!isset($groupedArticles[$categoryTitle])) {
            $groupedArticles[$categoryTitle] = [];
        }

        $groupedArticles[$categoryTitle][] = $item;
    }

    return $groupedArticles;
}
}
