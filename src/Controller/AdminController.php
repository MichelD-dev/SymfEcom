<?php

namespace App\Controller;

use App\Entity\Tag;
<<<<<<< HEAD
use App\Form\TagType;
use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
=======
use App\Entity\User;
use App\Form\TagType;
use App\Entity\Command;
use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use App\Entity\Reservation;
>>>>>>> develop
use App\Repository\TagRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
#[Security("is_granted('ROLE_ADMIN')")]
class AdminController extends AbstractController
{
    // #[Route('/admin', name: 'admin')]
    // public function index(): Response
    // {
    //     return $this->render('admin/index.html.twig', [
    //         'controller_name' => 'AdminController',
    //     ]);
    // }

    #[Route('/', name: 'backoffice')]
    public function adminBackoffice(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $productRepository = $entityManager->getRepository(Product::class);
        $tagRepository = $entityManager->getRepository(Tag::class);
        $categoryRepository = $entityManager->getRepository(Category::class);

        $categories = $categoryRepository->findAll();
        $products = $productRepository->findAll();
        $tags = $tagRepository->findAll();

        return $this->render('admin/admin-backoffice.html.twig', [
            'products' => $products,
            'tags' => $tags,
            'categories' => $categories,
        ]);
    }

    #[Route('/backoffice-command', name: 'command_backoffice_admin')]
    public function adminCommandeBackoffice(): Response
    {
        //Nous récupérons l'Utilisateur
        $user = $this->getUser();
        //Cette fonction a pour but de récupérer toutes les Commandes et de les présenter par Utilisateur, en offrant uniquement les fonctions de Validation et de Suppression de Commande/Reservation à la commande Active
        //Pour obtenir la liste des Utilisateurs, nous devons faire appel à l'Entity Manager ainsi qu'aux Repository pertinent
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(\App\Entity\User::class);
<<<<<<< HEAD
=======
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
>>>>>>> develop
        //Nous récupérons la totalité des Users
        $users = $userRepository->findAll();
        //Il s'agit ici de préparer un tableau de paires de commandes User
        //Tableau des utilisateurs: commandeUsers
        //Tableau des deux types des commandes: commandeArray
        //Deux tableaux de Commande: activeArray et pastArray
        $commandUsers = [];
        foreach ($users as $userUnit) {
            //On récupère les commandes de l'utilisateur:
            $userCommands = $userUnit->getCommands();
            //On initialise $activeCommande et $pastCommandes afin de les utiliser pendant et après la boucle
            $activeCommands = [];
            $pastCommands = [];
            foreach ($userCommands as $command) {
                if ($command->getStatus() == 'panier') {
                    array_push($activeCommands, $command);
                } else {
                    array_push($pastCommands, $command); //array_push est une commande PHP qui place une entrée dans un tableau (ici, $commande dans le tableau $pastCommandes)
                }
            }
            //On crée le tableau qui contiendra les deux types de commandes avant de le placer dans notre tableau de tableaux
            $commandArray = [$activeCommands, $pastCommands];
            array_push($commandUsers, $commandArray);
        }
        //Nous transmettons les deux résultats à notre vue Twig:
        return $this->render('admin/command-backoffice-admin.html.twig', [
            'user' => $user,
            'commands' => $commandUsers,
<<<<<<< HEAD
=======
            'categories' =>$categories
>>>>>>> develop
        ]);
    }

    #[Route('/product/create', name: 'product_create')]
    public function createProduct(Request $request): Response
    {
        // Cete fonction nous servira à afficher un formulaire capable de créer un nouveau produi
        // Tout d'abord, nous appelons l'Entity Manager pour communiquer avec norre BDD
        $entityManager = $this->getDoctrine()->getManager();
        // ensuite, nous créons un nouveau produit que nous lions à notre formulaire$produit = new produit;
        $product = new Product;
        $productForm = $this->createForm(ProductType::class, $product);
        // NOus transmetons la requête client à notre formulaire
        $productForm->handleRequest($request);
        // Si le formulaire a été validé
        if ($request->isMethod('post') && $productForm->isValid()) {
            // handleRequest ayant passé les infos à notre objet product, nous avons juste à le persister
            $productRepository = $entityManager->getRepository(Product::class);
            $productDuplicate = $productRepository->findOneBy(['name' => $product->getName()]);
            if (!$productDuplicate) {
                $entityManager->persist($product);
                $entityManager->flush();
                return $this->redirect($this->generateUrl('backoffice'));
            } else {
                return new Response("<h1>Opération impossible. Ce produit existe déjà dans la base de données.</h1>");
            }
        }
        // Si le formulaire n'a pas été validé, nous l'affichons pour l'utilisateur
        return $this->render('index/dataform.html.twig', [
            'formName' => 'Création de produit',
            'dataForm' => $productForm->createView() // createView prépare l'affichage de notre formulaire
        ]);
    }


    #[Route('/product/update/{productId}', name: 'product_update')]
    public function updateProduct(ProductRepository $productRepository, Request $request, $productId = false): Response
    {
        // Cette fonction a pour but de récupérer un produit et d'en modifier le contenu
        // Nous commençons par récuperer l'Entity Manager et le Repository de Produit
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        // Nous récupérons également le Repository de Category afin de renseigner notre Navbar
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        // NOus récupérons le produit dont l'Id nous a été communiqué
        $product = $productRepository->find($productId);
        // Si aucun produit n'est retrouvé, nous retournons vers l'index
        if (!$product) {
            return $this->redirect($this->generateUrl('backoffice'));
        }
        // Si un produit existe, la fonction continue et nous lions ce produit à un formulaire
        // Etant donné que $produit est déjà renseigné, les champs du formulaire seront préremplis

        $productForm = $this->createForm(\App\Form\ProductType::class, $product);
        // NOus appliquons la Request si celle ci est pertinente
        $productForm->handleRequest($request);
        // Si notre produit est valide et rempli, nous l'envoyons vers notre BDD
        if ($request->isMethod('post') && $productForm->isValid()) {

            // Nous vérifions s'il existe un produit au même titre EN PLUS de notre produit
            // Nous récupérons le nom de notre produit actuel
            // Nous récupérons tous les produits dont le nom est identique
            // $products = $productRepository->findBy(['name' => $product->getName()]);
            // return $this->render('index/dump.html.twig', ['variable' => $product]);
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('backoffice'));
        }
        // Si le produit n'a pas été rempli, nous affichons le formulaire
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Modification de produit',
            'dataForm' => $productForm->createView() // createView prépare l'affichage de notre formulaire
        ]);
    }

    #[Route('/product/delete/{productId}', name: 'product_delete')]
    public function deleteProduct(Request $request, $productId = false): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $entityManager->getRepository(Product::class);

        $product = $productRepository->find($productId);

        if (!$product) {
            return $this->redirect($this->generateUrl('backoffice'));
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('backoffice'));
    }

    #[Route('/tag/create', name: 'tag_create')]
    public function createTag(Request $request): Response
    {
        // Cete fonction nous servira à afficher un formulaire capable de créer un nouveau produi
        // Tout d'abord, nous appelons l'Entity Manager pour communiquer avec norre BDD
        $entityManager = $this->getDoctrine()->getManager();
        // ensuite, nous créons un nouveau tag que nous lions à notre formulaire
        $tag = new Tag;
        $tagForm = $this->createForm(TagType::class, $tag);
        // Nous transmettons la requête client à notre formulaire
        $tagForm->handleRequest($request);
        // Si le formulaire a été validé
        if ($request->isMethod('post') && $tagForm->isValid()) {
            // handleRequest ayant passé les infos à notre objet product, nous avons juste à le persister
            $entityManager->persist($tag);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('backoffice'));
        }

        // Si le formulaire n'a pas été validé, nous l'affichons pour l'utilisateur
        return $this->render('index/dataform.html.twig', [
            'formName' => 'Création de tag',
            'dataForm' => $tagForm->createView() // createView prépare l'affichage de notre formulaire
        ]);
    }

    #[Route('/tag/update/{tagId}', name: 'tag_update')]
    public function updateTag(TagRepository $tagRepository, Request $request, $tagId = false): Response
    {
        // Cette fonction a pour but de récupérer un produit et d'en modifier le contenu
        // Nous commençons par récuperer l'Entity Manager et le Repository de Produit
        $entityManager = $this->getDoctrine()->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        // Nous récupérons également le Repository de Category afin de renseigner notre Navbar
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        // NOus récupérons le produit dont l'Id nous a été communiqué
        $tag = $tagRepository->find($tagId);
        // Si aucun produit n'est retrouvé, nous retournons vers l'index
        if (!$tag) {
            return $this->redirect($this->generateUrl('backoffice'));
        }
        // Si un produit existe, la fonction continue et nous lions ce produit à un formulaire
        // Etant donné que $produit est déjà renseigné, les champs du formulaire seront préremplis

        $tagForm = $this->createForm(\App\Form\TagType::class, $tag);
        // NOus appliquons la Request si celle ci est pertinente
        $tagForm->handleRequest($request);
        // Si notre produit est valide et rempli, nous l'envoyons vers notre BDD
        if ($request->isMethod('post') && $tagForm->isValid()) {

            // Nous vérifions s'il existe un produit au même titre EN PLUS de notre produit
            // Nous récupérons le nom de notre produit actuel
            // Nous récupérons tous les produits dont le nom est identique
            // $tags = $tagRepository->findBy(['name' => $tag->getName()]);
            // return $this->render('index/dump.html.twig', ['variable' => $product]);
            $entityManager->persist($tag);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('backoffice'));
        }
        // Si le produit n'a pas été rempli, nous affichons le formulaire
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Modification de tag',
            'dataForm' => $tagForm->createView() // createView prépare l'affichage de notre formulaire
        ]);
    }


    #[Route('/tag/delete/{tagId}', name: 'tag_delete')]
    public function deleteTag(Request $request, $tagId = false): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);

        $tag = $tagRepository->find($tagId);
        if (!$tag) {
            return $this->redirect($this->generateUrl('backoffice'));
        }

        $entityManager->remove($tag);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('backoffice'));
    }
<<<<<<< HEAD
=======

    #[Route('/validate/{commandId}', name: 'command_validate_admin')]
    public function adminValidateCommand(Request $request, $commandId): Response
    { // Cette route récupère une commande et si cette dernière est en mode "panier", passe son statut à "validée"
        // NOus récupérons l'utilisateur
        $entityManager = $this->getDoctrine()->getManager();
        $commandRepository = $entityManager->getRepository(Command::class);
        // Nous récupérons la command eà valider selin l'Id indiqué
        $command = $commandRepository->find($commandId);
        // Si la commande" n'est pas trouvée OU son statut n'est as en mode "panier", nous revenons au backoffice
        if (!$command || ($command->getStatus() != "panier")) {
            return $this->redirect($this->generateUrl('command_backoffice_admin'));
        }
        //Si nous possédons une commande et son statut est bel et bien en mode "panier"
        $command->setStatus("validee");
        $entityManager->persist($command);
        $entityManager->flush();
        // Une fois que notre fonction a remli son rôle, nous retournons au Backoffice
        return $this->redirect($this->generateUrl('command_backoffice_admin'));
    }

    #[Route('/delete/{commandId}', name: 'command_delete_admin')]
    public function adminDeleteCommand(Request $request, $commandId = false): Response
    {
        // Cette fonction a pour but de supprimer une commande ainsi que toutes les réservations liées
        // Nous récupérons l'utilisateur en cours
        $entityManager = $this->getDoctrine()->getManager();
        $commandRepository = $entityManager->getRepository(Command::class);

        $command = $commandRepository->find($commandId);

        if (!$command || ($command->getStatus() != "panier")) {
            return $this->redirect($this->generateUrl('command_backoffice_admin'));
        }
        // Si notre commande' est bien valide, nous spouvons supprimer les reservations après restitution de leur quantity
        foreach ($command->getReservations() as $reservation) {
            $product = $reservation->getProduct();
            $product->setStock($product->getStock() + $reservation->getQuantity());
            $entityManager->persist($product);
            $entityManager->remove($reservation);
        }
        $entityManager->remove($command);
        $entityManager->flush();
        return $this->redirect($this->generateUrl('command_backoffice_admin'));
    }

    #[Route('/reservation/delete/{reservationId}', name: 'reservation_delete_admin')]
    public function adminDeleteReservation(Request $request, $reservationId): Response
    {
        // Cette fonction a pour but de supprimer une réservation et, si en conséquance la commande est vide, cette dernière également
        $entityManager = $this->getDoctrine()->getManager();
        $reservationRepository = $entityManager->getRepository(Reservation::class);

        $reservation = $reservationRepository->find($reservationId);
        // Si la réservation est inexistante ou ne possède pas de commande, nous revenos au backoffice
        if (!$reservation || !($reservation->getCommand()) || ($reservation->getCommand()->getStatus() != "panier")) {
            return $this->redirect($this->generateUrl('command_backoffice_admin'));
        }
        // Nous pouvons supprimer la réservation si celle ci existe et est en mode "panier"
        //Avant de supprimer la réservation, nous restituons la quantity réservée au stock de la référence Product
        $product = $reservation->getProduct();
        $product->setStock($product->getStock() + $reservation->getQuantity());
        // NOus la retirons tout d'abord de la commande
        $command = $reservation->getCommand();
        $command->removeReservation($reservation);
        //Nous effectuons ensuite une requête de suppression de notre reservation
        $entityManager->persist($product);
        $entityManager->remove($reservation);
        // Nous vérifions si la commande est à présent vide. Si oui, nous procédons à sa suppression
        if ($command->getReservations()->isEmpty()) {
            $entityManager->remove($command);
        }

        $entityManager->flush();
        return $this->redirect($this->generateUrl('command_backoffice_admin'));
    }
>>>>>>> develop
}
