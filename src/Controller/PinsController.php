<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Service\ImageManager;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



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
    public function edit(Request $request,Pin $pin,SluggerInterface $slugger): Response
    {
        
        $form = $this->createForm(PinType::class, $pin);
        $form->handleRequest($request);

    
        
        if($form->isSubmitted() && $form->isValid())
        {
            
            $imageFile = $form->get('imageName')->getData();
            
            if ($imageFile) { 
                // Delete old image
                $this->deleteImage($pin->getImageName());

                // save image on public/uploads folder
                $newFilename = $this->saveImage($imageFile,$slugger);

                $pin->setImageName($newFilename);

            }
            $this->em->flush($pin);
            $this->addFlash('success', 'Pin successfully updated!');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/edit.html.twig',['pin'=>$pin,'form'=>$form->createView(),'existingImage' => $pin->getImageName()]);
    }

    //Delete Pins
    #[Route('/pins/{id<[0-9]+>}/delete', name: 'app_pins_delete', methods : ['POST','DELETE'])]
    public function delete(Request $request,Pin $pin): Response
    {
        
        if($this->isCsrfTokenValid('pin.delete',$request->request->get('csrf_token')))
        {
            // Delete image from upload folder
            $this->deleteImage($pin->getImageName());

            // remove pin form database
            $this->em->remove($pin);
            $this->em->flush();
            $this->addFlash('info', 'Pin successfully deleted');
            return $this->redirectToRoute('app_home');

        }else
        {
            dd('return  Expired page');
        }
            
    }
    
    // Create Pins
    #[Route('/pins/create', name:'app_pins_create', methods : ['GET','POST'])]
    public function create(Request $request, SluggerInterface $slugger): Response
    {
       
        $pin = new Pin;
        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
                
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageName')->getData();
            // this condition is needed because the 'imageName' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                
                //Save image on public/Uploads Folder
                $newFilename = $this->saveImage($imageFile,$slugger);
                $pin->setImageName($newFilename);
               
            }
            // Set current auth user
            $pin->setUser($this->getUser());

            $this->em->persist($pin);
            $this->em->flush();
            $this->addFlash('success', 'Pin Successfully Created! ');
            return $this->redirectToRoute('app_home');




        }

        return $this->render('pins/create.html.twig', ['form'=>$form->createView()]);
    }


    /**
     * Private Function Helper
     */

     //Get Full image Path
     private function getImagePath($path)
     {
        return $this->getParameter('kernel.project_dir').'/public/uploads/'.$path;

     }
     // Delete image from public/Uploads Folder
     private function deleteImage($image): void
     {
        if ($image) {
            $filesystem = new ImageManager();
            $filesystem->removeImage($this->getImagePath($image));  
        }
     }

     // Save Image on public/Uploads Folder
     private function saveImage($imageFile,$slugger)
     {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('uploads'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

        return $newFilename;
     }

     
}
