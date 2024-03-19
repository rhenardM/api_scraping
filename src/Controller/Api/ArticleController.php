<?php

namespace App\Controller\Api;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Summarizer\Summarizer;

class ArticleController extends AbstractController
{
    public function generateSummary($content, $maxLength = 100)
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

    private $entityManager;

        public function __construct(EntityManagerInterface $entityManager)
        {
            $this->entityManager = $entityManager;
        }
        
    #[Route('/api/article', name: 'api_article', methods: ['GET', 'POST'])]
    public function ApiArticle()
    {
        try {
    $urls = [
        'https://www.radiookapi.net/politique',
        'https://fr.wikipedia.org/wiki/Article_de_presse',
        'https://www.radiookapi.net/'
    ];

    $httpClient = HttpClient::create();

    foreach ($urls as $url) {
        $response = $httpClient->request('GET', $url);
        $content = $response->getContent();

        $crawler = new Crawler($content);

        $articles = $crawler->filter('#main-content .post');

        foreach ($articles as $article) {
            $articleCrawler = new Crawler($article); 

            $titleElement = $articleCrawler->filter('h1.entry-title a');
            $contentElement = $articleCrawler->filter('.entry-content');
            $imageElement = $articleCrawler->filter('img.attachment-post-thumbnail');

            if ($titleElement->count() > 0 && $contentElement->count() > 0 && $imageElement->count() > 0) {
                $title = $titleElement->text();
                $content = $contentElement->text();
                $image = $imageElement->attr('src');

                // Générer un résumé automatique
                $summary = $this->generateSummary($content);

                // Enregistrement dans la base de données avec le résumé automatique
                $articleEntity = new Article();
                $articleEntity->setTitle($title);
                $articleEntity->setContent($content);
                $articleEntity->setImage($image);
                $articleEntity->setSummary($summary);

                $this->entityManager->persist($articleEntity);
            }
        }                
    }

    $this->entityManager->flush();
            return new JsonResponse(['message' => 'Articles récupérés et enregistrés avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
