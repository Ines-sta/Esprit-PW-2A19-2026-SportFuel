<?php
/**
 * Modèle PlanAlimentaire
 * Représente un plan alimentaire personnalisé
 */
class PlanAlimentaire {
    private $id_plan;
    private $id_utilisateur;
    private $nom;
    private $type;
    private $kcal_cibles;
    private $semaine;
    private $date_debut;
    private $date_fin;

    /**
     * Constructeur avec tous les paramètres
     */
    public function __construct($id_plan, $id_utilisateur, $nom, $type, $kcal_cibles, $semaine, $date_debut, $date_fin) {
        $this->id_plan = $id_plan;
        $this->id_utilisateur = $id_utilisateur;
        $this->nom = $nom;
        $this->type = $type;
        $this->kcal_cibles = $kcal_cibles;
        $this->semaine = $semaine;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
    }

    // Getters
    public function getIdPlan() { return $this->id_plan; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getNom() { return $this->nom; }
    public function getType() { return $this->type; }
    public function getKcalCibles() { return $this->kcal_cibles; }
    public function getSemaine() { return $this->semaine; }
    public function getDateDebut() { return $this->date_debut; }
    public function getDateFin() { return $this->date_fin; }

    // Setters
    public function setIdPlan($id_plan) { $this->id_plan = $id_plan; }
    public function setIdUtilisateur($id_utilisateur) { $this->id_utilisateur = $id_utilisateur; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setType($type) { $this->type = $type; }
    public function setKcalCibles($kcal_cibles) { $this->kcal_cibles = $kcal_cibles; }
    public function setSemaine($semaine) { $this->semaine = $semaine; }
    public function setDateDebut($date_debut) { $this->date_debut = $date_debut; }
    public function setDateFin($date_fin) { $this->date_fin = $date_fin; }

    /**
     * Génère un résumé du plan alimentaire
     * @return string
     */
    public function genererPlan() {
        return "Plan '{$this->nom}' - Type: {$this->type} - Objectif: {$this->kcal_cibles} kcal/jour - Semaine {$this->semaine}";
    }

    /**
     * Calcule et formate les calories cibles
     * @return string
     */
    public function calculerKcal() {
        return $this->kcal_cibles . " kcal/jour";
    }

    /**
     * Affiche la période du plan
     * @return string
     */
    public function afficherPlanJour() {
        return "Semaine {$this->semaine} — du {$this->date_debut} au {$this->date_fin}";
    }
}
?>
