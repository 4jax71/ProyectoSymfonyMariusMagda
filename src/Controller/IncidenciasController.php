<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Cliente;
use App\Entity\Incidencia;

class IncidenciasController extends AbstractController {

    /**
     * @Route("/incidencias", name="app_incidencias")
     */
    public function index(ManagerRegistry $doctrine): Response {
        $repositorio = $doctrine->getRepository(Incidencia::class);
        $incidencias = $repositorio->findBy([], ['fecha' => 'DESC']);
        return $this->render("incidencias/index.html.twig", ["incidencias" => $incidencias]);
    }

    /**
     * @Route("/incidencias/ver/{id<\d+>}", name="app_incidencias_ver")
     */
    public function verIncidencia(Incidencia $incidencia, Request $request, ManagerRegistry $doctrine): Response {
        $cliente = $incidencia->getCliente();
        return $this->render("incidencias/ver.html.twig", ["cliente" => $cliente, "incidencia" => $incidencia]);
    }
    
    /**
     * @Route("/incidencias/insertar", name="app_incidencias_insertar")
     */
    public function insertar(Request $request, ManagerRegistry $doctrine): Response {
        $repositorio = $doctrine->getRepository(Cliente::class);
        if ($request->isMethod('POST')) {
            $titulo = filter_var($request->request->get('titulo'), FILTER_SANITIZE_SPECIAL_CHARS);
            $estado = filter_var($request->request->get('estado'), FILTER_SANITIZE_SPECIAL_CHARS);
            $cliente = filter_var($request->request->get('cliente'), FILTER_SANITIZE_NUMBER_INT);

            $incidencia = new Incidencia();
            $incidencia->setCliente($repositorio->find($cliente));
            $incidencia->setUsuario($this->getUser());
            $incidencia->setTitulo($titulo);
            $incidencia->setFecha(new \DateTime());
            $incidencia->setEstado($estado);

            $em = $doctrine->getManager();
            $em->persist($incidencia);
            $em->flush();

            $this->addFlash('aviso', 'alertify.success("Incidencia INSERTADA");');
            return $this->redirectToRoute("app_incidencias");
        } else {
            $clientes = $repositorio->findBy([], ['nombre' => 'ASC']);
            return $this->render("incidencias/insertar.html.twig", ["clientes" => $clientes]);
        }
    }

    /**
     * @Route("/incidencias/borrar/{id<\d+>}", name="app_incidencias_borrar")
     */
    public function borrarIncidencia(Incidencia $incidencia, ManagerRegistry $doctrine): Response {
        $em = $doctrine->getManager();
        $em->remove($incidencia);
        $em->flush();

        $this->addFlash('aviso', 'alertify.success("Incidencia BORRADA");');
        return $this->redirectToRoute("app_incidencias");
    }

}
