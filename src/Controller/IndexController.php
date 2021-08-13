<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Command;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        // NOus récupérons l'utilisateur
        $user = $this->getUser();

        // Notre fonction index() nous présente tous les produits de notre application.
        // A cette fin, nous récupérons l'Entity Manager et le Repository Product
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $entityManager->getRepository(Product::class);

        // Nous récupérons la liste des catégories à transmettre au header
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        // Nous récupérons tous les éléments de la table Product
        $products = $productRepository->findAll();
        shuffle($products);

        // Nous transmettons les variables pertinentes à notre vue index
        return $this->render('index/index.html.twig', [
            'user' => $user,
            'products' => $products,
            'categories' => $categories
        ]);
    }

    #[Route('/taglist', name: 'tag_list')]
    public function tagList(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);

        $tags = $tagRepository->findAll();

        $categoryRepository = $entityManager->getRepository(Category::class);

        $categories = $categoryRepository->findAll();

        return $this->render('index/taglist.html.twig', [
            'tags' => $tags,
            'categories' => $categories
        ]);
    }

    #[Route('/product/{productId}', name: 'product')]
    public function ficheProduit(Request $request, $productId = false): Response
    {
        // Nous récupérons l'utilisateur en cours
        $user = $this->getUser();
        // Cette fonction a pour but d'afficher les informations d'un produit et de le commander
        // A cette fin, nous récupérons l'Entity Manager et le Repository Product
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        $categoryRepository = $entityManager->getRepository(Category::class);
        // $userRepository = $entityManager->getRepository(User::class);

        $categories = $categoryRepository->findAll();
        // Nous récupérons le Repository des Commandes en cas de réservation d'un product
        $commandRepository =  $entityManager->getRepository(Command::class);
        // Nous recherchons le product désiré selon le paramêtre de route transmis
        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->redirect($this->generateUrl('index'));
        }

        // Nous créons un formulaire afin de pouvoir communiquer la quantité que nous désirons acheter
        $buyForm = $this->createFormBuilder()
            ->Add('quantite', IntegerType::class,  [
                'label' => 'quantité',
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('valider', SubmitType::class, [
                'label' => 'Acheter',
                'attr' => [
                    'class' => 'w3-button w3-black w3-margin-bottom',
                    'style' => 'margin-top: 5px;'
                ]
            ])
            ->getForm();

        //NOus appliquons la Request sur notre formulaire
        $buyForm->handleRequest($request);

        //Si notre formulaire est validé, nous appliquons l'achat
        // 3 conditions: user connecté, formulaire validé et stock supérieur à 0
        if ($request->isMethod('post')  && $buyForm->isValid() && ($product->getStock() > 0) && $user) {
            $data = $buyForm->getData();
            // Nous récupérons les valeurs du formulaire sous forme de tableau associatif
            $quantity = $data['quantite'];
            //Nous créons la réservation qui va archiver l'achat de ce produit
            $reservation = new Reservation;
            $reservation->setProduct($product);
            // Si le produit existe, nous procédons à une décrémentation de la valeur de sa variable $stock
            //Mais seulement si le stock n'est pas inférieur à la quantité demandée
            if ($product->getStock() > $quantity) {
                $product->setStock($product->getStock() - $quantity);
                $reservation->setQuantity($quantity); //La $quantity sera équivalente à celle demandée via le formulaire car le $stock de product peut la fournir
            } else {
                $reservation->setQuantity($product->getStock()); // La $quantity sera la totalité du stock (insuffisant)
                $product->setStock(0);
            }
            // Nous ajoutons notre réservation à la commande. Si absente, nous la créons.
            // Nous effectuons d'abord une recherche pour un tableau de chaque commande en statut panier
            $activeCommands = $commandRepository->findByStatus('panier');
            // $userCommand = $commandRepository->findOneBy(['status' => 'panier', 'user' =>$user]); à favoriser
            $userCommand = null;
            foreach ($activeCommands as $activeCommand) {
                if ($activeCommand->getUser() == $user) {
                    $userCommand = $activeCommand;
                }
            }

            if ($userCommand) { // La commande existe
                $reservation->setCommand($userCommand);
            } else { // La commande n'existe pas
                $userCommand = new Command; // Nous créons la commande
                $userCommand->setUser($user); // Nous lui attribuons l'utilisateur actuellement connecté
                $reservation->setCommand($userCommand);
            }
            // Une fois que la décrémentation a eu lieu, nous effectuons une demande de persistance pour enregistrer la nouvelle valeur au sein de notre BDD
            $entityManager->persist($reservation);
            $entityManager->persist($userCommand);
            $entityManager->persist($product);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('product', [
                'productId' => $productId
            ]));
        }

        // Nous transmettons les variables pertinentes à notre template de fiche Twig
        return $this->render('index/fiche-produit.html.twig', [
            'product' => $product,
            'categories' => $categories,
            'buyForm' => $buyForm->createView(),
        ]);
    }

    #[Route('/category/{categoryId}', name: 'category')]
    public function indexCategory(Request $request, $categoryId = false): Response
    {
        // Notre fonction nous présente tous les produits d'une catégorie donnée.
        // A cette fin, nous récupérons l'Entity Manager et le Repository Product
        $entityManager = $this->getDoctrine()->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $category = $categoryRepository->find($categoryId);
        if (!$category) {
            return $this->redirect($this->generateUrl('index'));
        }

        $products = $category->getProducts()->toArray();

        shuffle($products);

        // Nous transmettons les variables pertinentes à notre vue index
        return $this->render('index/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    #[Route('/tag/{tagId}', name: 'tag')]
    public function indexTag(Request $request, $tagId = false): Response
    {
        // Notre fonction nous présente tous les produits d'une catégorie donnée.
        // A cette fin, nous récupérons l'Entity Manager et le Repository Product
        $entityManager = $this->getDoctrine()->getManager();

        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $tagRepository = $entityManager->getRepository(Tag::class);
        $tag = $tagRepository->find($tagId);
        if (!$tag) {
            return $this->redirect($this->generateUrl('index'));
        }

        $products = $tag->getProducts()->toArray();

        shuffle($products);

        // Nous transmettons les variables pertinentes à notre vue index
        return $this->render('index/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
