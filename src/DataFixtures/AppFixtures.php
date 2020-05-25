<?php

namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\AssocierCategorie;
use App\Entity\Categorie;
use App\Entity\Genre;
use App\Entity\Magasin;
use App\Entity\Produit;
use App\Entity\Role;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurConfirmation;
use App\Entity\Ville;
use App\Service\UtilisateurService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    const LOREM = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.';

    private $utilisateurService;

    public function __construct(UtilisateurService $utilisateurService) {
        $this->utilisateurService = $utilisateurService;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        // Sample data settings fixtures................................................................................

        foreach ([[ 'meta' => 'ROLE_USER', 'libelle' => 'Utilisateur' ], [ 'meta' => 'ROLE_ADMIN', 'libelle' => 'Administrateur' ]] as $_role) {
            $role = new Role();
            $role->setMeta($_role['meta']);
            $role->setLibelle($_role['libelle']);

            $manager->persist($role);
        }

        foreach ([ 'femme', 'homme', 'non spécifier' ] as $_genre) {
            $genre = new Genre();
            $genre->setLibelle($_genre);

            $manager->persist($genre);
        }

        $manager->flush();

        // User Default
        foreach ([
                    ['nom' => 'totoadmin', 'prenom' => 'toto', 'email' => 'toto.admin@gmail.com', 'tel' => '0758555555', 'role' => 'ROLE_ADMIN'],
                    ['nom' => 'totouser', 'prenom' => 'toto', 'email' => 'toto.user@gmail.com', 'tel' => '0758555555', 'role' => 'ROLE_USER']
                 ] as $_user) {

            $user = new Utilisateur();
            $user->setNom($_user['nom']);
            $user->setPrenom($_user['prenom']);
            $user->setCourriel($_user['email']);
            $user->setTelephone($_user['tel']);
            $user->setDateNaissance(new \DateTime());
            $user->setIdGenre($manager->getRepository(Genre::class)->findOneBy([ 'libelle' => 'homme']));
            $user->setIdRole($manager->getRepository(Role::class)->findOneBy([ 'meta' => $_user['role']]));

            $user->setPlainPassword('toto');

            // Ici je sauvegarde avec le cryptage l'utilisateur
            $this->utilisateurService->save($user);

            // Bypass la verification par mail
            $comfirmation = new UtilisateurConfirmation();
            $comfirmation->setToken(null)
                ->setCreatedat(new \DateTime())
                ->setIdUtilisateur($user);

            $manager->persist($comfirmation);
        }

        $manager->flush();


        foreach ([
            ['ville' => 'perpignan', 'cp' => '66000', 'pays' => 'france'],
            ['ville' => 'Claret', 'cp' => '05110', 'pays' => 'france'],
            ['ville' => 'Curbans', 'cp' => '05110', 'pays' => 'france'],
            ['ville' => 'Piégut', 'cp' => '05130', 'pays' => 'france'],
            ['ville' => 'Pontis', 'cp' => '05160', 'pays' => 'france'],
            ['ville' => 'Lajoux', 'cp' => '01410', 'pays' => 'france'],
            ['ville' => 'Chancia', 'cp' => '01590', 'pays' => 'france'],
         ] as $_ville) {
            $ville = new Ville();

            $ville->setLibelle($_ville['ville'])
                ->setCodePostal($_ville['cp'])
                ->setPays($_ville['pays']);

            $manager->persist($ville);
        }

        foreach ([
            ['libelle' => 'Figurine', 'color' => randColor()],
            ['libelle' => 'Livre', 'color' => randColor()],
            ['libelle' => 'Date Book', 'color' => randColor()],
            ['libelle' => 'One Piece Item', 'color' => randColor()],
            ['libelle' => 'Spécial', 'color' => randColor()],
            ['libelle' => 'Anime', 'color' => randColor()],
            ['libelle' => 'Manga', 'color' => randColor()],
            ['libelle' => 'NSFW', 'color' => randColor()],
                 ] as $_categorie) {

            $categorie = new Categorie();
            $categorie->setLibelle($_categorie['libelle']);
            $categorie->setColor($_categorie['color']);

            $manager->persist($categorie);
        }
        $manager->flush();

        /*----------[Génération de donnée pertinante]----------*/

        $villes = $manager->getRepository(Ville::class)->findAll();

        foreach ($villes as $ville) {
            $adresse = new Adresse();
            $adresse->setLibelle('Adresse, '  . $ville->getLibelle())
                ->setAdr(rand(1, 50) . ' avenue ' . $ville->getLibelle())
                ->setCompl('location vendor')
                ->setIdVille($ville)
            ;

            $manager->persist($adresse);
        }
        $manager->flush();

        $adresses = $manager->getRepository(Adresse::class)->findAll();

        foreach ([
            ['nom' => 'AniManga'],
            ['nom' => 'MangaShop'],
            ['nom' => 'TheAnimation'],
            ['nom' => 'MangaStory'],
            ['nom' => 'EcoManga'],
                 ] as $_mgasin) {

            $magasin = new Magasin();
            $magasin->setNom($_mgasin['nom'])
                ->setCourriel('contact.' . mb_strtolower($_mgasin['nom']) . '@emanga.com')
                ->setTelephone('+' . rand(0, 9999999999))
                ->setIdAdresse($adresses[rand(0, count($adresses) - 1)])
                ->setIdImage(null)
                ->setLatitude("42.691217")
                ->setLongitude("2.908672")
                ;

            $manager->persist($magasin);
        }
        $manager->flush();

        $magasins = $manager->getRepository(Magasin::class)->findAll();

        for ($i = 0; $i < 30; $i++) {
            $produit = new Produit();
            $produit->setLibelle("Produit " . $i)
                ->setDescription(AppFixtures::LOREM . " " . $i)
                ->setPrixHt(rand(25, 30 + $i))
                ->setStock(rand(0, 500))
                ->setIdMagasin($magasins[rand(0, count($magasins) - 1)]);
            ;
            $manager->persist($produit);
        }
        $manager->flush();

        $produits = $manager->getRepository(Produit::class)->findAll();
        $categories = $manager->getRepository(Categorie::class)->findAll();

        for ($i = 0; $i < 30; $i++) {
            $i_p = $produits[rand(0, count($produits) - 1)];
            $i_c = $categories[rand(0, count($categories) - 1)];
            if (
                !empty($i_p) &&
                !empty($i_c) &&
                empty($manager->getRepository(AssocierCategorie::class)->findOneBy([ 'idProduit' => $i_p->getId(), 'idCategorie' => $i_c->getId() ]))) {
                $associerCegorie = new AssocierCategorie();
                $associerCegorie->setIdProduit($i_p)
                    ->setIdCategorie($i_c);

                $manager->persist($associerCegorie);
            }
        }

        $manager->flush();

    }
}
