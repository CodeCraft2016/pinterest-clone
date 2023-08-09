<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PinsController extends AbstractController
{
    private $em;

    // Doctrine ORM EntityManager Constructor
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    //Home page
    #[Route('/', name: 'app_home', methods:'GET')]
    public function index(PinRepository $pinRepository): Response
    {
        $pins = $pinRepository->findBy([], ['createdAt'=>'DESC']);   

        return $this->render('pins/index.html.twig',['pins'=>$pins]);
    }

    // Show Pins
    #[Route('/pins/{id<[0-9]+>}', name: 'app_pins_show', methods:'GET')]
    public function show(Pin $pin): Response
    {
        
        return $this->render('pins/show.html.twig',compact('pin'));
    }

    //Edit Pins
    #[Route('/pins/{id<[0-9]+>}/edit', name: 'app_pins_edit', methods : ['GET','POST','PUT'])]
    public function edit(Request $request,Pin $pin): Response
    {
        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {

            $this->em->flush($pin);
            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/edit.html.twig',['pin'=>$pin,'form'=>$form->createView()]);
    }

    //Delete Pins
    #[Route('/pins/{id<[0-9]+>}/delete', name: 'app_pins_delete', methods : ['POST','DELETE'])]
    public function delete(Request $request,Pin $pin): Response
    {
        
        if($this->isCsrfTokenValid('pin.delete',$request->request->get('csrf_token')))
        {
            $this->em->remove($pin);
            $this->em->flush();
            return $this->redirectToRoute('app_home');

        }else
        {
            dd('return  Expired page');
        }
            
        //return $this->render('pins/edit.html.twig',['pin'=>$pin,'form'=>$form->createView()]);
    }
    
    // Create Pins
    #[Route('/pins/create', name:'app_pins_create', methods : ['GET','POST'])]
    public function create(Request $request): Response
    {
       
        $pin = new Pin;
        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->em->persist($pin);
            $this->em->flush();

            return $this->redirectToRoute('app_home');



        }

        return $this->render('pins/create.html.twig', ['form'=>$form->createView()]);
    }
}
