<?php

namespace App\Service;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;

class ArticleService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveArticle(array $articleData)
    {
        $article = new Article();
        $article->setTitle($articleData['title']);
        $article->setContent($articleData['content']);
        $article->setImage($articleData['image']);
        $article->setSummary($this->generateSummary($articleData['content']));

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    private function generateSummary($content, $maxLength = 100)
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);

        if (strlen($content) > $maxLength) {
            $content = substr($content, 0, $maxLength);
            $lastSpacePos = strrpos($content, ' ');
            if ($lastSpacePos !== false) {
                $content = substr($content, 0, $lastSpacePos);
            }
            $content .= '...';
        }

        return $content;
    }
}
