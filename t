[1mdiff --git a/src/Controller/IndexController.php b/src/Controller/IndexController.php[m
[1mindex 6d96bf4..65e2da0 100644[m
[1m--- a/src/Controller/IndexController.php[m
[1m+++ b/src/Controller/IndexController.php[m
[36m@@ -4,6 +4,7 @@[m [mnamespace App\Controller;[m
 [m
 use App\Entity\Product;[m
 use App\Entity\Category;[m
[32m+[m[32muse Symfony\Component\HttpFoundation\Request;[m
 use Symfony\Component\HttpFoundation\Response;[m
 use Symfony\Component\Routing\Annotation\Route;[m
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;[m
[36m@@ -32,4 +33,37 @@[m [mclass IndexController extends AbstractController[m
             'categories' => $categories[m
         ]);[m
     }[m
[32m+[m
[32m+[m[32m    #[Route('/product/create', name: 'product_create')][m
[32m+[m[32m    public function bulletinCreate(Request $request): Response[m
[32m+[m[32m    {[m
[32m+[m[32m        // Cete fonction nous servira à afficher un formulaire capable de créer un nouveau bulletin[m
[32m+[m[32m        // Tout d'abord, nous appelons l'Entity Manager pour communiquer avec norre BDD[m
[32m+[m[32m        $entityManager = $this->getDoctrine()->getManager();[m
[32m+[m[32m        // ensuite, nous créons un nouveau bulletin que nous lions à notre formulaire$bulletin = new Bulletin;[m
[32m+[m[32m        $product = new Product;[m
[32m+[m[32m        $productForm = $this->createForm(\App\Form\ProductType::class, $product);[m
[32m+[m[32m        // NOus transmetons la requête client à notre formulaire[m
[32m+[m[32m        $productForm->handleRequest($request);[m
[32m+[m[32m        // Si le formulaire a été validé[m
[32m+[m[32m        if ($request->isMethod('post') && $productForm->isValid()) {[m
[32m+[m[32m            // handleRequest ayant passé les infos à notre objet bulletin, nous avons juste à le persisterO[m
[32m+[m[32m            $entityManager = $this->getDoctrine()->getManager();[m
[32m+[m[32m            $productRepository = $entityManager->getRepository(Bulletin::class);[m
[32m+[m[32m            $productDuplicate = $productRepository->findOneBy(['name' => $product->getName()]);[m
[32m+[m[32m            if (!$productDuplicate) {[m
[32m+[m[32m                $entityManager->persist($product);[m
[32m+[m[32m                $entityManager->flush();[m
[32m+[m[32m                return $this->redirect($this->generateUrl('index'));[m
[32m+[m[32m            } else {[m
[32m+[m[32m                return new Response("<h1>Opération impossible. Ce produit existe déjà dans la base de données.</h1>");[m
[32m+[m[32m            }[m
[32m+[m[32m        }[m
[32m+[m[41m      [m
[32m+[m[32m        // Si le formulaire n'a pas été validé, nous l'affichons pour l'utilisateur[m
[32m+[m[32m        return $this->render('index/dataform.html.twig', [[m
[32m+[m[32m            'formName' => 'Création de produit',[m
[32m+[m[32m            'dataForm' => $productForm->createView() // createView prépare l'affichage de notre formulaire[m
[32m+[m[32m        ]);[m
[32m+[m[32m    }[m
 }[m
[1mdiff --git a/src/Form/ProductType.php b/src/Form/ProductType.php[m
[1mindex 8ec86c8..8e5e9c3 100644[m
[1m--- a/src/Form/ProductType.php[m
[1m+++ b/src/Form/ProductType.php[m
[36m@@ -2,6 +2,7 @@[m
 [m
 namespace App\Form;[m
 [m
[32m+[m[32muse App\Entity\Tag;[m
 use App\Entity\Product;[m
 use Symfony\Component\Form\AbstractType;[m
 use Symfony\Component\Form\FormBuilderInterface;[m
[36m@@ -9,6 +10,8 @@[m [muse Symfony\Bridge\Doctrine\Form\Type\EntityType;[m
 use Symfony\Component\OptionsResolver\OptionsResolver;[m
 use Symfony\Component\Form\Extension\Core\Type\TextType;[m
 use Symfony\Component\Form\Extension\Core\Type\MoneyType;[m
[32m+[m[32muse Symfony\Component\Form\Extension\Core\Type\ChoiceType;[m
[32m+[m[32muse Symfony\Component\Form\Extension\Core\Type\SubmitType;[m
 use Symfony\Component\Form\Extension\Core\Type\IntegerType;[m
 use Symfony\Component\Form\Extension\Core\Type\TextareaType;[m
 [m
[36m@@ -18,26 +21,38 @@[m [mclass ProductType extends AbstractType[m
     {[m
         $builder[m
             ->add('name', TextType::class, [[m
[31m-                'label' => 'Name:',[m
[32m+[m[32m                'label' => 'Name: ',[m
[32m+[m[32m                'attr' => [[m
[32m+[m[32m                    'class' => 'mb-5'[m
[32m+[m[32m                ][m
             ])[m
             ->add('description', TextareaType::class, [[m
[31m-                'label' => 'Description:',[m
[32m+[m[32m                'label' => 'Description: ',[m
[32m+[m[32m                'attr' => [[m
[32m+[m[32m                    'class' => 'mb-5'[m
[32m+[m[32m                ][m
             ])[m
             ->add('price', MoneyType::class, [[m
[31m-                'label' => 'Price:',[m
[31m-            ])[m
[31m-            ->add('stock', IntegerType::class, [[m
[31m-                'stock' => 'Stock:',[m
[32m+[m[32m                'label' => 'Price: ',[m
[32m+[m[32m                'attr' => [[m
[32m+[m[32m                    'class' => 'mb-5'[m
[32m+[m[32m                ][m
             ])[m
[32m+[m[32m            // ->add('stock', IntegerType::class, [[m
[32m+[m[32m            //     'stock' => 'Stock:',[m
[32m+[m[32m            // ])[m
             ->add('tags', EntityType::class, [[m
[31m-                'label' => 'Tags',[m
[32m+[m[32m                'label' => 'Tags: ',[m
                 'class' => \App\Entity\Tag::class, // Le nom de l'Entity que nous voulons attribuer à ce champ[m
                 'choice_label' => 'name', // L'attribut de notre Entity que nous voulons utiliser comme label pour chaque choix[m
                 'expanded' => true, // change l'affichage en checks plutôt que list[m
                 'multiple' => true, // permet de faire un choix multiple, renvoie ici une erreur si false, parce que nous sommes en ManyToMany (nous récupérons un TABLEAU de tags, même si choix unique)[m
[32m+[m[32m                'attr' => [[m
[32m+[m[32m                    'class' => 'mb-5'[m
[32m+[m[32m                ][m
             ])[m
             ->add('category', ChoiceType::class, [[m
[31m-                'label' => 'Catégorie:',[m
[32m+[m[32m                'label' => 'Catégorie: ',[m
                 'choices' => [[m
                     'Tables' => 'Tables',[m
                     'Chaises' => 'Chaises',[m
[36m@@ -48,9 +63,16 @@[m [mclass ProductType extends AbstractType[m
                 ],[m
                 'expanded' => false, // change l'affichage en boutons plutôt que liste[m
                 'multiple' => false, // permet de faire un choix multiple[m
[32m+[m[32m                'attr' => [[m
[32m+[m[32m                    'class' => 'mb-5'[m
[32m+[m[32m                ][m
             ])[m
             ->add('valider', SubmitType::class, [[m
                 'label' => 'Valider',[m
[32m+[m[32m                'attr' => [[m
[32m+[m[32m                    'class' => 'btn btn-success btn-lg px-5',[m
[32m+[m[32m                    'style' => 'margin-top: 5px;'[m
[32m+[m[32m                ][m
             ]);[m
     }[m
 [m
[1mdiff --git a/templates/base.html.twig b/templates/base.html.twig[m
[1mindex 94a6cc5..cb3f523 100644[m
[1m--- a/templates/base.html.twig[m
[1m+++ b/templates/base.html.twig[m
[36m@@ -10,6 +10,7 @@[m
 		{% block stylesheets %}[m
 			<meta charset="UTF-8">[m
 			<meta name="viewport" content="width=device-width, initial-scale=1">[m
[32m+[m				[32m<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">[m
 			{# <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css"> #}[m
 			 <link rel="stylesheet" href="{{ asset('assets/css/w3.css') }}">[m
 			<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">[m
