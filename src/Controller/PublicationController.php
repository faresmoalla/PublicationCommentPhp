<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Publication;
use App\Form\CommentType;
use App\Form\PublicationType;
use App\Repository\CommentRepository;
use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
class PublicationController extends AbstractController
{
    #[Route('/publication', name: 'app_publication')]
    public function index(): Response
    {
        return $this->render('publication/index.html.twig', [
            'controller_name' => 'PublicationController',
        ]);
    }
    #[Route("/afficherpublication",name :"afficherpublication")]

    public function Affiche(EntityManagerInterface $em,Request $request,PublicationRepository $repository,CommentRepository $commentRepository,PaginatorInterface $paginator){
        $pub = $repository->findOneBy(["id" => $request->get("id")]);


        $Comment= new Comment();
        $Comment->setPublication($pub);

        $form2= $this->createForm(CommentType::class,$Comment);
        $Comment->setDate(new \DateTimeImmutable());
        $form2->handleRequest($request);

        if($form2->isSubmitted() && $form2->isValid()){
            $new=$form2->getData();

            $em->persist($Comment);
            $em->flush();
            return $this->redirectToRoute("afficherpublication");
        }
        $publication= new Publication();
        $form= $this->createForm(PublicationType::class,$publication);
        $publication->setDate(new \DateTimeImmutable());
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $new=$form->getData();

            $em->persist($publication);
            $em->flush();

            return $this->redirectToRoute("afficherpublication");
        }


        $tablepublication=$repository->listPubByDate();
        $comment = $commentRepository->findBy(["id" => $request->get("id")]);
        $tablepublication = $paginator->paginate(
            $tablepublication,
            $request->query->getInt('page', 1),
            3
        );


        return $this->render('publication/frontpublication.html.twig'
            ,['tablepublication'=>$tablepublication,
                'comment'=>$comment,"form"=>$form->createView(),
                "form2"=>$form2->createView()]);
    }

    #[Route("/afficherpublicationback",name :"afficherpublicationback")]

    public function AfficheBack(Request $request,PublicationRepository $repository,CommentRepository $commentRepository,PaginatorInterface $paginator){
        $tablepublication=$repository->listPubByDate();

        $tablepublication = $paginator->paginate(
            $tablepublication,
            $request->query->getInt('page', 1),
            3
        );
        $comment = $commentRepository->findBy(["id" => $request->get("id")]);

        return $this->render('publication/afficherpublicationback.html.twig'
            ,['tablepublication'=>$tablepublication,
                'comment'=>$comment]);
    }

    #[Route("/ajouterpublication",name:"ajouterpublication")]

    public function ajouterpublication(EntityManagerInterface $em,Request $request ){
        $publication= new Publication();
        $form= $this->createForm(PublicationType::class,$publication);
        $publication->setDate(new \DateTimeImmutable());
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $new=$form->getData();

            $em->persist($publication);
            $em->flush();

            return $this->redirectToRoute("afficherpublicationback");
        }
        return $this->render("publication/ajouterpublication.html.twig",array("form"=>$form->createView()));

    }


    #[Route("/ajouterpublication2",name:"ajouterpublication2")]

    public function ajouterpublication2(EntityManagerInterface $em,Request $request ){
        $publication= new Publication();
        $form= $this->createForm(PublicationType::class,$publication);
        $publication->setDate(new \DateTimeImmutable());
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $new=$form->getData();

            $em->persist($publication);
            $em->flush();

            return $this->redirectToRoute("afficherpublication");
        }
        return $this->render("publication/ajouterpublication.html.twig",array("form"=>$form->createView()));

    }
    #[Route("/supprimerpublication/{id}",name:"supprimerpublication")]

    public function delete($id,EntityManagerInterface $em ,PublicationRepository $repository){
        $rec=$repository->find($id);
        $em->remove($rec);
        $em->flush();

        return $this->redirectToRoute('afficherpublication');
    }

    #[Route("/supprimerpublicationback/{id}",name:"supprimerpublicationback")]

    public function delete2($id,EntityManagerInterface $em ,PublicationRepository $repository){
        $rec=$repository->find($id);
        $em->remove($rec);
        $em->flush();

        return $this->redirectToRoute('afficherpublicationback');
    }

    #[Route("/deletecomment/{id}",name:"deletecomment")]

    public function deletecomment($id,EntityManagerInterface $em ,CommentRepository $repository){
        $rec=$repository->find($id);
        $em->remove($rec);
        $em->flush();

        return $this->redirectToRoute('afficherpublicationback');
    }
    #[Route("/{id}/modifierpublication", name:"modifierpublication")]

    public function edit(Request $request, Publication $publication): Response
    {
        $form = $this->createForm(PublicationType::class, $publication);
        $form->add('Confirmer',SubmitType::class);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $this->getDoctrine()->getManager()->flush();


            return $this->redirectToRoute('afficherpublication');
        }

        return $this->render('publication/modifierpublication.html.twig', [
            'publicationmodif' => $publication,
            'form' => $form->createView(),
        ]);
    }





    #[Route("/pdfpublication/{id}",name:"pdfpublication", methods: ['GET'])]
    public function pdf($id,PublicationRepository $repository): Response{

        $publication=$repository->find($id);
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('publication/pdfpublication.html.twig', [
            'pdf' => $publication,

        ]);
        $dompdf->loadHtml($html);
        //  $dompdf->loadHtml('<h1>Hello, World!</h1>');

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        //  $dompdf->stream();
        // Output the generated PDF to Browser (force download)
        /* $dompdf->stream($publication->getType(), [
             "Attachment" => false
         ]);*/
        $pdfOutput = $dompdf->output();
        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="publication.pdf"'
        ]);

    }


    #[Route("/statpublication",name:"statpublication")]

    public function statAction(PublicationRepository $test)
    {


        $coursss= $test->findAll();
        $nbrCours=[];
        foreach($coursss as $cours){
            $coursnom[]=$cours->getContent();
            $coursprix[]=sizeof($cours->getComment());
        }






        return $this->render('publication/stat.html.twig',
            [

                'coursnom'=> json_encode($coursnom),
                'coursprix'=> json_encode($coursprix),


            ]);


    }

}
