-- Update ingredients for existing Repas records
USE SportFuel;

-- Add ingredients column if it doesn't exist
ALTER TABLE Repas ADD COLUMN IF NOT EXISTS ingredients TEXT DEFAULT NULL AFTER description;

-- Update existing meals with ingredients
UPDATE Repas SET ingredients = 'Flocons d\'avoine 80g, Banane 1, Whey protéine 30g, Amandes 20g' WHERE id_repas = 1;
UPDATE Repas SET ingredients = 'Poulet 200g, Riz basmati 100g, Brocolis 150g, Huile d\'olive 10ml' WHERE id_repas = 2;
UPDATE Repas SET ingredients = 'Saumon 180g, Patate douce 150g, Haricots verts 100g, Avocat 50g' WHERE id_repas = 3;
UPDATE Repas SET ingredients = 'Yaourt grec 200g, Fruits rouges 80g, Miel 10g' WHERE id_repas = 4;
UPDATE Repas SET ingredients = 'Œufs 3, Pain complet 40g, Tomate 1' WHERE id_repas = 5;
UPDATE Repas SET ingredients = 'Dinde 150g, Quinoa 80g, Courgettes 200g, Citron 1' WHERE id_repas = 6;
UPDATE Repas SET ingredients = 'Cabillaud 160g, Carottes 100g, Haricots verts 100g, Salade verte 50g' WHERE id_repas = 7;
UPDATE Repas SET ingredients = 'Farine complète 60g, Œufs 2, Whey 20g, Sirop d\'érable 15ml, Myrtilles 50g' WHERE id_repas = 8;
UPDATE Repas SET ingredients = 'Bœuf maigre 180g, Pâtes complètes 100g, Tomates 200g, Ail 2 gousses' WHERE id_repas = 9;
UPDATE Repas SET ingredients = 'Flocons d\'avoine 80g, Banane 1, Beurre de cacahuète 20g, Cannelle 2g' WHERE id_repas = 10;
UPDATE Repas SET ingredients = 'Thon 150g, Riz complet 120g, Avocat 80g, Salade mixte 100g' WHERE id_repas = 11;
UPDATE Repas SET ingredients = 'Banane 1, Whey 30g, Lait d\'amande 200ml, Épinards 50g' WHERE id_repas = 12;
UPDATE Repas SET ingredients = 'Pain complet 60g, Œufs 3, Saumon fumé 40g' WHERE id_repas = 13;
UPDATE Repas SET ingredients = 'Poulet 220g, Riz basmati 120g, Poivrons 100g, Oignons 50g, Curry 5g' WHERE id_repas = 14;
