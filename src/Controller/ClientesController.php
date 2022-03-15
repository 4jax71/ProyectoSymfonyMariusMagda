<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Cliente;
use App\Entity\Incidencia;

class ClientesController extends AbstractController {

    /**
     * @Route("/clientes", name="app_clientes")
     */
    public function index(ManagerRegistry $doctrine): Response {
        $repositorio = $doctrine->getRepository(Cliente::class);
        $clientes = $repositorio->findAll();
        return $this->render("clientes/index.html.twig", ["clientes" => $clientes]);
    }

    /**
     * @Route("/clientes/ver/{id<\d+>}", name="app_clientes_ver")
     */
    public function ver(Cliente $cliente, Request $request, ManagerRegistry $doctrine): Response {
        $repositorio = $doctrine->getRepository(Incidencia::class);
        $incidencias = $repositorio->findBy(['cliente' => $cliente->getId()], ['fecha' => 'DESC']);
        return $this->render("clientes/ver.html.twig", ["cliente" => $cliente, "incidencias" => $incidencias]);
    }

    /**
     * @Route("/clientes/insertar", name="app_clientes_insertar")
     */
    public function insertarCliente(Request $request, ManagerRegistry $doctrine): Response {
        if ($request->isMethod('POST')) {
            $nombre = filter_var($request->request->get('nombre'), FILTER_SANITIZE_SPECIAL_CHARS);
            $apellidos = filter_var($request->request->get('apellidos'), FILTER_SANITIZE_SPECIAL_CHARS);
            $telefono = filter_var($request->request->get('telefono'), FILTER_SANITIZE_SPECIAL_CHARS);
            $direccion = filter_var($request->request->get('direccion'), FILTER_SANITIZE_SPECIAL_CHARS);

            $cliente = new Cliente();
            $cliente->setNombre($nombre);
            $cliente->setApellidos($apellidos);
            $cliente->setTelefono($telefono);
            $cliente->setDireccion($direccion);

            $em = $doctrine->getManager();
            $em->persist($cliente);
            $em->flush();

            $this->addFlash('aviso', "alertify.success('Cliente INSERTADO');");
            return $this->redirectToRoute('app_clientes');
        } else {
            return $this->render("clientes/insertar_cliente.html.twig");
        }
    }

    /**
     * @Route("/clientes/borrar/{id<\d+>}", name="app_clientes_borrar")
     */
    public function borrarCliente(Cliente $cliente, ManagerRegistry $doctrine): Response {
        $em = $doctrine->getManager();
        $em->remove($cliente);
        $em->flush();
        $this->addFlash('aviso', 'alertify.success("Cliente BORRADO");');
        return $this->redirectToRoute("app_clientes");
    }

    /**
     * @Route("/clientes/incidencias/ver/{id<\d+>}", name="app_clientes_incidencias_ver")
     */
    public function verIncidencia(Incidencia $incidencia, Request $request, ManagerRegistry $doctrine): Response {
        $cliente = $incidencia->getCliente();
        return $this->render("clientes/ver_incidencia.html.twig", ["cliente" => $cliente, "incidencia" => $incidencia]);
    }

    /**
     * @Route("/clientes/incidencias/insertar/{id<\d+>}", name="app_clientes_incidencias_insertar")
     */
    public function insertarIncidencia(Cliente $cliente, Request $request, ManagerRegistry $doctrine): Response {
        if ($request->isMethod('POST')) {
            $titulo = filter_var($request->request->get('titulo'), FILTER_SANITIZE_SPECIAL_CHARS);
            $estado = filter_var($request->request->get('estado'), FILTER_SANITIZE_SPECIAL_CHARS);

            $incidencia = new Incidencia();
            $incidencia->setCliente($cliente);
            $incidencia->setUsuario($this->getUser());
            $incidencia->setTitulo($titulo);
            $incidencia->setFecha(new \DateTime());
            $incidencia->setEstado($estado);

            $em = $doctrine->getManager();
            $em->persist($incidencia);
            $em->flush();

            $this->addFlash('aviso', "alertify.success('Incidencia INSERTADA');");
            $repositorio = $doctrine->getRepository(Incidencia::class);
            $incidencias = $repositorio->findBy(['cliente' => $cliente->getId()], ['fecha' => 'DESC']);
            return $this->render("clientes/ver.html.twig", ["cliente" => $cliente, "incidencias" => $incidencias]);
        } else {
            return $this->render("clientes/insertar_incidencia.html.twig", ["cliente" => $cliente]);
        }
    }

    /**
     * @Route("/clientes/incidencias/editar/{id<\d+>}", name="app_clientes_incidencias_editar")
     */
    public function editarIncidencia(Incidencia $incidencia, Request $request, ManagerRegistry $doctrine): Response {
        $cliente = $incidencia->getCliente();
        if ($request->isMethod('POST')) {
            $titulo = filter_var($request->request->get('titulo'), FILTER_SANITIZE_SPECIAL_CHARS);
            $estado = filter_var($request->request->get('estado'), FILTER_SANITIZE_SPECIAL_CHARS);

            $incidencia->setTitulo($titulo);
            $incidencia->setEstado($estado);

            $em = $doctrine->getManager();
            $em->flush();

            $this->addFlash('aviso', "alertify.success('Incidencia EDITADA');");
            $repositorio = $doctrine->getRepository(Incidencia::class);
            $incidencias = $repositorio->findBy(['cliente' => $cliente->getId()], ['fecha' => 'DESC']);
            return $this->render("clientes/ver.html.twig", ["cliente" => $cliente, "incidencias" => $incidencias]);
        } else {
            return $this->render("clientes/editar_incidencia.html.twig", ["cliente" => $cliente, "incidencia" => $incidencia]);
        }
    }

    /**
     * @Route("/clientes/incidencias/borrar/{id<\d+>}", name="app_clientes_incidencias_borrar")
     */
    public function borrarIncidencia(Incidencia $incidencia, ManagerRegistry $doctrine): Response {
        $cliente = $incidencia->getCliente();
        $em = $doctrine->getManager();
        $em->remove($incidencia);
        $em->flush();

        $this->addFlash('aviso', 'alertify.success("Incidencia BORRADA");');
        $repositorio = $doctrine->getRepository(Incidencia::class);
        $incidencias = $repositorio->findBy(['cliente' => $cliente->getId()], ['fecha' => 'DESC']);
        return $this->render("clientes/ver.html.twig", ["cliente" => $cliente, "incidencias" => $incidencias]);
    }

}
