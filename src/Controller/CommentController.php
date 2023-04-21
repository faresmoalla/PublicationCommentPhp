<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }
    #[Route("/afficherComment",name :"afficherComment")]

    public function Affiche(Request $request,CommentRepository $repository,CommentRepository $commentRepository,PaginatorInterface $paginator){
        $tableComment=$repository->listCommentByDate();
        $comment = $commentRepository->findBy(["id" => $request->get("id")]);
        $tableComment = $paginator->paginate(
            $tableComment,
            $request->query->getInt('page', 1),
            3
        );
        return $this->render('Comment/afficherComment.html.twig'
            ,['tableComment'=>$tableComment,
                'comment'=>$comment]);
    }

    #[Route("/afficherCommentback",name :"afficherCommentback")]

    public function AfficheBack(Request $request,CommentRepository $repository,CommentRepository $commentRepository,PaginatorInterface $paginator){
        $tableComment=$repository->listCommentByDate();

        $tableComment = $paginator->paginate(
            $tableComment,
            $request->query->getInt('page', 1),
            3
        );
        $comment = $commentRepository->findBy(["id" => $request->get("id")]);

        return $this->render('Comment/afficherCommentback.html.twig'
            ,['tableComment'=>$tableComment,
                'comment'=>$comment]);
    }

    #[Route("/ajoutercomment",name:"ajoutercomment")]

    public function ajouterComment(EntityManagerInterface $em,Request $request ){
        $Comment= new Comment();
        $form= $this->createForm(CommentType::class,$Comment);
        $Comment->setDate(new \DateTimeImmutable());
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $new=$form->getData();

            $em->persist($Comment);
            $em->flush();

            return $this->redirectToRoute("afficherCommentback");
        }
        return $this->render("Comment/ajouterComment.html.twig",array("form"=>$form->createView()));

    }



    #[Route("/supprimerComment/{id}",name:"supprimerComment")]

    public function delete($id,EntityManagerInterface $em ,CommentRepository $repository){
        $rec=$repository->find($id);
        $em->remove($rec);
        $em->flush();

        return $this->redirectToRoute('afficherComment');
    }

    #[Route("/supprimerCommentback/{id}",name:"supprimerCommentback")]

    public function delete2($id,EntityManagerInterface $em ,CommentRepository $repository){
        $rec=$repository->find($id);
        $em->remove($rec);
        $em->flush();

        return $this->redirectToRoute('afficherCommentback');
    }


    #[Route("/{id}/modifierComment", name:"modifierComment")]

    public function edit(Request $request, Comment $Comment): Response
    {
        $form = $this->createForm(CommentType::class, $Comment);
        $form->add('Confirmer',SubmitType::class);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $this->getDoctrine()->getManager()->flush();


            return $this->redirectToRoute('afficherComment');
        }

        return $this->render('Comment/modifierComment.html.twig', [
            'Commentmodif' => $Comment,
            'form' => $form->createView(),
        ]);
    }





    #[Route("/pdfComment/{id}",name:"pdfComment", methods: ['GET'])]
    public function pdf($id,CommentRepository $repository): Response{

        $Comment=$repository->find($id);
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('Comment/pdfComment.html.twig', [
            'pdf' => $Comment,

        ]);
        $dompdf->loadHtml($html);
        //  $dompdf->loadHtml('<h1>Hello, World!</h1>');

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        //  $dompdf->stream();
        // Output the generated PDF to Browser (force download)
        /* $dompdf->stream($Comment->getType(), [
             "Attachment" => false
         ]);*/
        $pdfOutput = $dompdf->output();
        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Comment.pdf"'
        ]);

    }


}
