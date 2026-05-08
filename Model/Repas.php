<?php
/**
 * Modèle Repas
 * Représente un repas dans un plan alimentaire
 */
class Repas {
    private $id_repas;
    private $id_plan;
    private $jour;
    private $type_repas;
    private $description;
    private $kcal;

    /**
     * Constructeur avec tous les paramètres
     */
    public function __construct($id_repas, $id_plan, $jour, $type_repas, $description, $kcal) {
        $this->id_repas = $id_repas;
        $this->id_plan = $id_plan;
        $this->jour = $jour;
        $this->type_repas = $type_repas;
        $this->description = $description;
        $this->kcal = $kcal;
    }

    // Getters
    public function getIdRepas() { return $this->id_repas; }
    public function getIdPlan() { return $this->id_plan; }
    public function getJour() { return $this->jour; }
    public function getTypeRepas() { return $this->type_repas; }
    public function getDescription() { return $this->description; }
    public function getKcal() { return $this->kcal; }

    // Setters
    public function setIdRepas($id_repas) { $this->id_repas = $id_repas; }
    public function setIdPlan($id_plan) { $this->id_plan = $id_plan; }
    public function setJour($jour) { $this->jour = $jour; }
    public function setTypeRepas($type_repas) { $this->type_repas = $type_repas; }
    public function setDescription($description) { $this->description = $description; }
    public function setKcal($kcal) { $this->kcal = $kcal; }

    /**
     * Retourne une confirmation d'ajout
     * @return string
     */
    public function ajouterRepas() {
        return "Repas ajouté : {$this->type_repas} du {$this->jour} - {$this->kcal} kcal";
    }

    /**
     * Retourne une confirmation de modification
     * @return string
     */
    public function modifierRepas() {
        return "Repas modifié : {$this->type_repas} du {$this->jour}";
    }

    /**
     * Retourne une confirmation de suppression
     * @return string
     */
    public function supprimerRepas() {
        return "Repas supprimé : {$this->type_repas} du {$this->jour}";
    }
}
?>
