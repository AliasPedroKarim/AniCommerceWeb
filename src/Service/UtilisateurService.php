<?php


namespace App\Service;


use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UtilisateurService {

    protected $entityManager;
    protected $repository;
    protected $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->repository = $this->entityManager->getRepository(Utilisateur::class);
    }

    /**
     * @param Utilisateur $utilisateur
     * @return Utilisateur
     */
    private function encodePassword(Utilisateur $utilisateur) {
        if(!empty($utilisateur->getPlainPassword())) {
            $utilisateur->setMotDePasse($this->passwordEncoder->encodePassword(
                $utilisateur,
                $utilisateur->getPlainPassword()
            ));
        }

        return $utilisateur;
    }

    /**
     * @param Utilisateur $utilisateur
     * @return UtilisateurService
     */
    public function delete(Utilisateur $utilisateur) :self {
        $this->entityManager->remove($utilisateur);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * @param Utilisateur $utilisateur
     * @return UtilisateurService
     */
    public function save(Utilisateur $utilisateur) :self {
        $utilisateur = $this->encodePassword($utilisateur);
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return UtilisateurRepository|ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

}