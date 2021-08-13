<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Command;
use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/command')]
#[Security("is_granted('ROLE_USER')")]
class CommandController extends AbstractController
{
    #[Route('/backoffice', name: 'command_backoffice')]
    public function commandBackoffice(): Response
    {
        // Nous récupérons l'utilisateur
        $user = $this->getUser();
        //Cette fonction a pour but de nous aider à traiter les différentes commandes de Product passées en tant qu'utilisateur
        //Pour obtenir la liste des commandes et reservations, nous devons faire appel à l'Entity Manager ainsi qu'au Repository pertinent
        $entityManager = $this->getDoctrine()->getManager();
        $commandRepository = $entityManager->getRepository(Command::class);
        //Nous récupérons la totalité des commandes avant de déceler celle en mode Panier via une boucle
        $commands = $commandRepository->findByUser($user);

        // On initialise $activeCommand et $pastCommands afin de les utiliser pendant et après la boucle
        $activeCommand = null;
        $pastCommands = [];
        foreach ($commands as $command) {
            if ($command->getStatus() == 'panier') {
                $activeCommand = $command;
            } else {
                array_push($pastCommands, $command);
            }
        }
        //Nous transmettons les deux résultats à notre vue Twig:
        return $this->render('command/command-backoffice.html.twig', [
            'user' => $user,
            'activeCommand' => $activeCommand,
            'commands' => $pastCommands,
        ]);
    }

    #[Route('/validate/{commandId}', name: 'command_validate')]
    public function validateCommand(Request $request, $commandId): Response
    { // Cette route récupère une commande et si cette dernière est en mode "panier", passe son statut à "validée"
        // NOus récupérons l'utilisateur
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $commandRepository = $entityManager->getRepository(Command::class);
        // Nous récupérons la command eà valider selin l'Id indiqué
        $command = $commandRepository->find($commandId);
        // Si la commande" n'est pasd trouvée OU son statut n'est as en mode "panier", nous revenons au backoffice
        if (!$command || ($command->getUser() != $user) || ($command->getStatus() != "panier")) {
            return $this->redirect($this->generateUrl('command_backoffice'));
        }
        //Si nous possédons une commande et son statut est bel et bien en mode "panier"
        $command->setStatus("validee");
        $entityManager->persist($command);
        $entityManager->flush();
        // Une fois que notre fonction a remli son rôle, nous retournons au Backoffice
        return $this->redirect($this->generateUrl('command_backoffice'));
    }

    #[Route('/delete/{commandId}', name: 'command_delete')]
    public function deleteCommand(Request $request, $commandId = false): Response
    {
        // Cette fonction a pour but de supprimer une commande ainsi que toutes les réservations liées
        // Nous récupérons l'utilisateur en cours
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $commandRepository = $entityManager->getRepository(Command::class);

        $command = $commandRepository->find($commandId);

        if (!$command || ($command->getUser() != $user) || ($command->getStatus() != "panier")) {
            return $this->redirect($this->generateUrl('command_backoffice'));
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
        return $this->redirect($this->generateUrl('command_backoffice'));
    }

    #[Route('/reservation/delete/{reservationId}', name: 'reservation_delete')]
    public function deleteReservation(Request $request, $reservationId): Response
    {
        // Cette fonction a pour but de supprimer une réservation et, si en conséquance la commande est vide, cette dernière également
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $reservationRepository = $entityManager->getRepository(Reservation::class);

        $reservation = $reservationRepository->find($reservationId);
        // Si la réservation est inexistante ou ne possède pas de commande, nous revenos au backoffice
        if (!$reservation || ($reservation->getCommand()->getUser() != $user) || !($reservation->getCommand()) || ($reservation->getCommand()->getStatus() != "panier")) {
            return $this->redirect($this->generateUrl('command_backoffice'));
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
        return $this->redirect($this->generateUrl('command_backoffice'));
    }
}
