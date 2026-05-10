<?php
/**
 * Canonical BackOffice model for repas.
 */
class Repas {
    private $id_repas;
    private $id_plan;
    private $jour;
    private $type_repas;
    private $description;
    private $kcal;

    public function __construct($id_repas, $id_plan, $jour, $type_repas, $description, $kcal) {
        $this->id_repas = $id_repas;
        $this->id_plan = $id_plan;
        $this->jour = $jour;
        $this->type_repas = $type_repas;
        $this->description = $description;
        $this->kcal = $kcal;
    }

    public function getIdRepas() { return $this->id_repas; }
    public function getIdPlan() { return $this->id_plan; }
    public function getJour() { return $this->jour; }
    public function getTypeRepas() { return $this->type_repas; }
    public function getDescription() { return $this->description; }
    public function getKcal() { return $this->kcal; }

    public function setIdRepas($id_repas) { $this->id_repas = $id_repas; }
    public function setIdPlan($id_plan) { $this->id_plan = $id_plan; }
    public function setJour($jour) { $this->jour = $jour; }
    public function setTypeRepas($type_repas) { $this->type_repas = $type_repas; }
    public function setDescription($description) { $this->description = $description; }
    public function setKcal($kcal) { $this->kcal = $kcal; }

    public function ajouterRepas() {
        return "Repas ajoute : {$this->type_repas} du {$this->jour} - {$this->kcal} kcal";
    }

    public function modifierRepas() {
        return "Repas modifie : {$this->type_repas} du {$this->jour}";
    }

    public function supprimerRepas() {
        return "Repas supprime : {$this->type_repas} du {$this->jour}";
    }
}
