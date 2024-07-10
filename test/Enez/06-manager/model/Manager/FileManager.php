<?php

namespace model\Manager ;

use Exception;
use model\Abstract\AbstractMapping;
use model\Interface\InterfaceManager;
use model\Mapping\FileMapping;
use PDO;


class FileManager implements InterfaceManager{

    // On va stocker la connexion dans une propriété privée
    private ?PDO $connect = null;

    // on va passer notre connexion PDO
    // à notre manager lors de son instanciation
    public function __construct(PDO $db){
        $this->connect = $db;
    }

    // sélection de tous les articles
    public function selectAll(): ?array
    {
        // requête SQL
        $sql = "SELECT * FROM `file` -- WHERE `file_id`=999";
        // query car pas d'entrées d'utilisateur
        $select = $this->connect->query($sql);

        // si on ne récupère rien, on quitte avec un message d'erreur
        if($select->rowCount()===0) return null;

        // on transforme nos résultats en tableau associatif
        $array = $select->fetchAll(PDO::FETCH_ASSOC);

        // on ferme le curseur
        $select->closeCursor();

        // on va stocker les fichiers dans un tableau
        $arrayFile = [];

        /* pour chaque valeur, on va créer une instance de classe
        FileMapping, liée à la table qu'on va manager
        */
        foreach($array as $value){
            // on remplit un nouveau tableau contenant les fichiers
            $arrayFile[] = new FileMapping($value);
        }

        // on retourne le tableau
        return $arrayFile;
    }

    // récupération d'un fichier via son id
    public function selectOneById(int $id): null|string|FileMapping
    {

        // requête préparée
        $sql = "SELECT * FROM `file` WHERE `file_id`= ?";
        $prepare = $this->connect->prepare($sql);

        try{
            $prepare->bindValue(1,$id, PDO::PARAM_INT);
            $prepare->execute();

            // pas de résultat = null
            if($prepare->rowCount()===0) return null;

            // récupération des valeurs en tableau associatif
            $result = $prepare->fetch(PDO::FETCH_ASSOC);

            // création de l'instance FileMapping
            $result = new FileMapping($result);

            $prepare->closeCursor();
            
            return $result;


        }catch(Exception $e){
            return $e->getMessage();
        }
        
    }

    // mise à jour d'un fichier
    public function update(AbstractMapping $mapping): bool|string
    {

        if (!($mapping instanceof FileMapping)) {                    
            throw new Exception('L\'objet doit être une instance de FileMapping'); 
        }
        // requête préparée
        $sql = "UPDATE `file` SET `file_text`=?, `file_date_update`=? WHERE `file_id`=?";
        // mise à jour de la date de modification
        $mapping->setFileDateUpdate(date("Y-m-d H:i:s"));
        $prepare = $this->connect->prepare($sql);

        try{
            $prepare->bindValue(1,$mapping->getFileText());
            $prepare->bindValue(2,$mapping->getFileDateUpdate());
            $prepare->bindValue(3,$mapping->getFileId(), PDO::PARAM_INT);

            $prepare->execute();

            $prepare->closeCursor();

            return true;

        }catch(Exception $e){
            return $e->getMessage();
        }
        
    }


    // insertion d'un fichier - À modifier !
    public function insert(AbstractMapping $mapping): bool|string
    {
        if (!($mapping instanceof FileMapping)) {                    
            throw new Exception('L\'objet doit être une instance de FileMapping'); 
        }

        // requête préparée
        $sql = "INSERT INTO `file`(`file_url`, `file_type`)  VALUES (?,'.png')";
        $prepare = $this->connect->prepare($sql);

        try{
            $prepare->bindValue(1,$mapping->getFileUrl());

            $prepare->execute();

            $prepare->closeCursor();

            return true;

        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    // suppression d'un fichier
    public function delete(int $id): bool|string
    {
        // requête préparée
        $sql = "DELETE FROM `file` WHERE `file_id`=?";
        $prepare = $this->connect->prepare($sql);

        try{
            $prepare->bindValue(1,$id, PDO::PARAM_INT);

            $prepare->execute();

            $prepare->closeCursor();

            return true;

        }catch(Exception $e){
            return $e->getMessage();
        }
        
    }

}
