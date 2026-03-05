# TACHES — Collection L'Original (Manzili repo)

## CONTEXTE PROJET

- **Site** : collectionloriginal.com
- **Hebergeur** : O2Switch (SSH user: zajr1824)
- **CMS** : WordPress + WooCommerce
- **Theme** : Flatsome + child theme (flatsome-child) → ce repo
- **Repo** : FaroukHUB/Manzili, branche `claude/saas-architecture-design-6lk1Y`
- **Chemin serveur** : `/home/zajr1824/collectionloriginal.com/wp-content/themes/flatsome-child`

## WORKFLOW

```
Claude (ce repo) → git push → GitHub → toi (serveur) → git pull
```

Avant chaque pull sur le serveur :
```bash
git add .
git commit -m "mes modifs du [date]"
git pull origin claude/saas-architecture-design-6lk1Y
```

---

## STRUCTURE DU SITE

### Menu
1. **Collection Privee L'Original**
   - Parfums 50ml (promo 9,99€ / initial 15€)
   - Sprays d'interieur & textiles (7,99€)
   - Brumes corps & cheveux (8,99€)
   - Gels douche (8,99€)
   - Diffuseurs de parfum pour voiture (4,99€)
   - Coffrets (promo 19,99€ / initial 25€)
   - Packs revendeurs

2. **Collection Privee Intense**
   - Parfums 50ml (promo 8,99€ / initial 12,99€)
   - Sprays d'interieur & textiles (6,99€)
   - Packs revendeurs

3. **Collection Privee Paris**
   - Parfums 50ml (promo 6,99€ / initial 10€)
   - Packs revendeurs

4. **Vente en gros**
   - Collection Privee L'Original
   - Collection Privee Intense
   - Collection Privee Paris

---

## TARIFS PACKS REVENDEURS

### L'Original
| Produit | 10pcs | 20pcs | 35pcs | 40pcs | 50pcs | 60pcs | 100pcs |
|---|---|---|---|---|---|---|---|
| Parfums 50ml | 62€ | 116€ | 192,5€ | — | 255€ | — | 470€ |
| Sprays interieur | 42€ | 76€ | — | 136€ | — | 186€ | 280€ |
| Brumes corps | 52€ | 96€ | — | 176€ | — | 246€ | 380€ |
| Gels douche | 58€ | 108€ | — | 200€ | — | 282€ | 430€ |

| Diffuseurs voiture | 2 boites (28pcs) | 4 boites | 8 boites |
|---|---|---|---|
| | 173,60€ | 313,60€ | 560€ |

| Coffrets (100ml+Brume) | 12pcs | 24pcs | 48pcs | 96pcs |
|---|---|---|---|---|
| | 154,80€ | 264€ | 504€ | 950,40€ |

### Intense
| Produit | 10pcs | 20pcs | 35pcs | 50pcs | 100pcs |
|---|---|---|---|---|---|
| Parfums 50ml | 58€ | 108€ | 178,5€ | 235€ | 430€ |
| Sprays interieur | 39€ | 72€ | — | — | 270€ |

(Sprays : 10→39€, 20→72€, 40→128€, 60→180€, 100→270€)

### Paris
| 10pcs | 20pcs | 35pcs | 50pcs | 100pcs |
|---|---|---|---|---|
| 49€ | 90€ | 147€ | 195€ | 360€ |

---

## LOGIQUE WOOCOMMERCE — PACKS REVENDEURS

- **Type produit** : Produit variable
- **Attribut global** : "Quantite" (creé une seule fois dans Produits > Attributs)
- **Valeurs** : 10 pcs / 20 pcs / 35 pcs / 40 pcs / 48 pcs / 50 pcs / 60 pcs / 96 pcs / 100 pcs / 2 boites / 4 boites / 8 boites / 12 pcs / 24 pcs
- **Choix des parfums** : cases a cocher (le client melange les references) → via snippet PHP/JS
- **1 produit variable par type de produit par collection**

---

## CATALOGUE PARFUMS

### Collection Intense Paris — EDP 50ml (25 ref.)
AISHA · BAKARA · BLACK OP · BLUE MAGIC · CHERRY'S · CREME BRULEE · ERBA · INTERDIT · INV · KRYPTO · MADAWI · MARSHMALLOW · MILLIONAIRE · MULA · MUSC BLANC · OUD MARACUJA · PISTACHIO · PRINCE D'ARABIE · PRINCESSE D'ARABIE · RAYA · ROSE VANILLE · SAVANE · SUCRE · VAILLANT · VIE

### Collection Intense Paris — Spray 250ml (26 ref. uniques)
ABSOLU · AFRICA UNITED · ANTHRACITE · AZUR · BOIS INTENSE · BOURGEOISE · CHERRY · DIMA MAGHREB · DREAM · DZ POWER · GOURMAND · ILLEGAL · INFINI · KHAMRAH · KIRKE · LVB · MADAME · MONEY · NOIR DE NOIR · OUD INTENSE · PETIT SUCRE · REBEL · RED INTENSE · SEDUCTION · VANILLA POWER · YARA

### Collection Privee L'Original — Spray 250ml (3 ref. exclusives)
AMEERAT · CANDY ECLAIR · ROSE

### 4eme collection (9 ref.)
COCO VANILLE · COTON FRAIS · DOUCEUR · EMIRATS · FIGUE · FLORAL · FRIANDISE · MALIKI · WHITE MUSC

---

## ETAT ACTUEL DU REPO

| Fichier | Statut | Description |
|---|---|---|
| `style.css` | OK | Child theme Flatsome de base |
| `functions.php` | OK | Badge personnalise par produit (admin + affichage boutique + CSS) |
| `TACHES.md` | OK | Ce fichier |

---

## TACHES

### Terminees
- [x] Achat Flatsome
- [x] Installation WooCommerce
- [x] Creation child theme Flatsome
- [x] Connexion repo GitHub ↔ serveur O2Switch
- [x] Push initial du child theme
- [x] Ajout fonctionnalite badge personnalise (functions.php)
- [x] Creation categories et sous-categories WooCommerce
- [x] Definition logique packs revendeurs (attribut Quantite + variations)
- [x] Creation fichier TACHES.md
- [x] Confirmation : Collection Privee L'Original existe en Parfum 50ml — OUI
- [x] Attributs et termes WooCommerce ajoutes (Quantite + valeurs)
- [x] Plugin Code Snippets installe et actif
- [x] Selecteur de references parfums code dans functions.php (cases a cocher + validation + sauvegarde commande)

### A faire — WooCommerce (toi)
- [ ] Sur chaque produit pack variable : remplir le champ "Parfums disponibles dans ce pack" (admin > produit > onglet General)
- [ ] Creer les 9 produits variables packs revendeurs avec leurs variations de prix
- [ ] Saisir les parfums par collection avec leurs images

### A faire — Developpement (Claude)
- [ ] Personnalisation CSS Collection L'Original (couleurs, typographie)
- [ ] Template page d'accueil
- [ ] Integration Stripe (paiement CB)
- [ ] Integration PayPal
- [ ] Integration livraison (Colissimo / Mondial Relay)

### A faire — A definir
- [ ] Liste complete des parfums L'Original (noms/references) pour le champ "Parfums disponibles"
- [ ] La 4eme collection sans nom — quelle collection exactement ?
