<?php

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Article;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;



// class ArticleController extends AbstractController
// {
//     private $entityManager;

//     public function __construct(EntityManagerInterface $entityManager)
//     {
//         $this->entityManager = $entityManager;
//     }
    
//     #[Route('/api/article', name:'api_article', methods:['GET', 'POST'])]
//     public function ApiArticle()
//     {
//         $httpClient = HttpClient::create();
//         $response = $httpClient->request('GET', 'https://exemple.com');
        
//         $content = $response->getContent();
//         $crawler = new Crawler($content);

//         $title = $crawler->filter('h1')->text();
//         $content = $crawler->filter(' .article-content')->text();
//         $image = $crawler->filter('img')->attr('src');
        
//         // Enregistrement de l'article dans la base de donné
//         $article = new Article();
//         $article->setTitle($title);
//         $article->setContent($content);
//         $article->setImage($image);

//         $entityManager = $this->entityManager;
//         $entityManager->persist($article);
//         $entityManager->flush();

//         return new JsonResponse(['message' => 'Article scraping and saved successfully']);
        
//     }
// }
class ArticleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[Route('/api/article', name: 'api_article', methods: ['GET', 'POST'])]
    public function ApiArticle()
    {
        try {
            $httpClient = HttpClient::create();
            $response = $httpClient->request('GET', 'https://www.mediacongo.net/articles.html');
            $response = $httpClient->request('GET', 'https://fr.wikipedia.org/wiki/Article_de_presse');
            $response = $httpClient->request('GET', 'https://www.radiookapi.net/');
            
            $content = $response->getContent();
            $crawler = new Crawler($content);

            $titleNode = $crawler->filter('h1');
            $contentNode = $crawler->filter('.article-content');
            $imageNode = $crawler->filter('img');
            
            // Vérifier si les nœuds sont vides
            if ($titleNode->count() === 0 || $contentNode->count() === 0 || $imageNode->count() === 0) {
                throw new \Exception('Aucun élément trouvé en fonction du sélecteur spécifié.');
            }

            $title = $titleNode->text();
            $content = $contentNode->text();
            $image = $imageNode->attr('src');
            
            // Enregistrement de l'article dans la base de données
            $article = new Article();
            $article->setTitle($title);
            $article->setContent($content);
            $article->setImage($image);

            $entityManager = $this->entityManager;
            $entityManager->persist($article);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Article scraping and saved successfully']);
        } catch (\Exception $e) {
            // Gestion de l'erreur
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
